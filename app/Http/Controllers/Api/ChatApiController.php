<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChatApiController extends Controller
{
    public function __construct(protected ChatService $chatService)
    {
    }

    /**
     * GET /api/v1/chat/rooms
     * Daftar ruang obrolan sesuai user login
     */
    public function rooms(Request $request): JsonResponse
    {
        $user = auth()->user();
        $rooms = $this->chatService->getRoomsForUser($user);

        $payload = $rooms->map(function (ChatRoom $room) use ($user) {
            $student = $room->student;
            $class = $room->schoolClass;
            $unread = $room->unreadCountFor($user->id);

            return [
                'id'              => $room->id,
                'name'            => $room->name,
                'student_id'      => $student?->id,
                'student_name'    => $student?->name ?? $room->name,
                'student_nis'     => $student?->nis ?? '',
                'student_photo'   => $student?->photo_url ?? null,
                'class_name'      => $class?->name ?? '',
                'unread_count'    => $unread,
                'last_message'    => $room->last_message_text ?? '',
                'last_message_at' => $room->last_message_at?->toIso8601String() ?? null,
                'last_time_human' => $room->last_message_at?->diffForHumans() ?? '',
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $payload,
        ]);
    }

    /**
     * GET /api/v1/chat/rooms/{room}
     * Detail ruang obrolan, info partisipan, dan riwayat pesan
     */
    public function show(ChatRoom $room, Request $request): JsonResponse
    {
        $user = auth()->user();

        $isParticipant = $room->participants()->where('user_id', $user->id)->exists();
        if (! $isParticipant) {
            // Coba sinkronisasi otomatis jika relasi valid
            if ($user->isSiswa() && $room->student_id === $user->id) {
                $this->chatService->syncRoomParticipants($room, $user);
            } elseif ($user->isOrangtua() && $user->children->contains('id', $room->student_id)) {
                $this->chatService->syncRoomParticipants($room, $room->student);
            } elseif ($user->isGuru() && $user->homeroomClass?->id === $room->school_class_id) {
                $this->chatService->syncRoomParticipants($room, $room->student);
            } else {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        }

        // Tandai sudah dibaca
        $this->chatService->markAsRead($room, $user);

        // Ambil riwayat pesan terakhir
        $messages = $room->messages()
            ->with(['sender', 'reads.user'])
            ->orderBy('id', 'asc')
            ->limit(100)
            ->get();

        $formattedMessages = $messages->map(fn($m) => $this->formatMessagePayload($m, $user, $room));
        $participantsLastSeen = $this->chatService->getParticipantLastSeen($room);

        return response()->json([
            'success' => true,
            'data'    => [
                'room' => [
                    'id'           => $room->id,
                    'name'         => $room->name,
                    'student_name' => $room->student?->name ?? $room->name,
                    'class_name'   => $room->schoolClass?->name ?? '',
                ],
                'participants' => $participantsLastSeen,
                'messages'     => $formattedMessages,
            ],
        ]);
    }

    /**
     * GET /api/v1/chat/rooms/{room}/messages?after_id=...
     * Polling pesan baru
     */
    public function messages(ChatRoom $room, Request $request): JsonResponse
    {
        $user = auth()->user();

        $isParticipant = $room->participants()->where('user_id', $user->id)->exists();
        if (! $isParticipant) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $afterId = (int) $request->query('after_id', 0);

        // Tandai sudah dibaca
        $this->chatService->markAsRead($room, $user);

        $newMessages = $room->messages()
            ->with(['sender', 'reads.user'])
            ->when($afterId > 0, fn($q) => $q->where('id', '>', $afterId))
            ->orderBy('id', 'asc')
            ->get();

        $formattedMessages = $newMessages->map(fn($m) => $this->formatMessagePayload($m, $user, $room));

        // Status centang pesan milik saya
        $myRecent = $room->messages()
            ->where('sender_id', $user->id)
            ->where('created_at', '>=', now()->subDays(2))
            ->with(['reads.user'])
            ->get();

        $readStatuses = [];
        $readDetails = [];
        $otherParticipantsCount = $room->participants()->where('user_id', '!=', $user->id)->count();

        foreach ($myRecent as $m) {
            $otherReads = $m->reads->where('user_id', '!=', $user->id)->count();
            $status = 'sent';
            if ($otherReads > 0) {
                $status = ($otherParticipantsCount > 0 && $otherReads >= $otherParticipantsCount) ? 'read_all' : 'read';
            }
            $readStatuses[$m->id] = $status;
            $readDetails[$m->id] = $m->read_details;
        }

        $deletedIds = $room->messages()
            ->where('is_deleted', true)
            ->where('deleted_at', '>=', now()->subDays(2))
            ->pluck('id');

        return response()->json([
            'success'       => true,
            'messages'      => $formattedMessages,
            'read_statuses' => $readStatuses,
            'read_details'  => $readDetails,
            'deleted_ids'   => $deletedIds,
        ]);
    }

    /**
     * POST /api/v1/chat/rooms/{room}/send
     * Kirim pesan baru
     */
    public function send(ChatRoom $room, Request $request): JsonResponse
    {
        $user = auth()->user();

        $isParticipant = $room->participants()->where('user_id', $user->id)->exists();
        if (! $isParticipant) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'message'    => 'nullable|string|max:3000',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,txt',
        ]);

        if (empty(trim($validated['message'] ?? '')) && ! $request->hasFile('attachment')) {
            return response()->json(['success' => false, 'message' => 'Pesan atau lampiran tidak boleh kosong.'], 422);
        }

        $message = $this->chatService->sendMessage(
            $room,
            $user,
            $validated['message'] ?? '',
            $request->file('attachment')
        );

        return response()->json([
            'success' => true,
            'data'    => $this->formatMessagePayload($message, $user, $room),
        ]);
    }

    /**
     * DELETE /api/v1/chat/messages/{message}
     * Tarik pesan untuk semua orang
     */
    public function deleteMessage(ChatMessage $message, Request $request): JsonResponse
    {
        $user = auth()->user();

        if (! $message->canBeDeletedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Pesan tidak dapat ditarik (hanya pengirim dan maksimal 60 menit setelah dikirim).',
            ], 403);
        }

        $this->chatService->deleteMessage($message, $user);

        return response()->json([
            'success'    => true,
            'message_id' => $message->id,
        ]);
    }

    /**
     * GET /api/v1/chat/unread-count
     * Total badge unread
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['success' => true, 'unread' => 0]);
        }

        $count = $this->chatService->getTotalUnreadCount($user);

        return response()->json([
            'success' => true,
            'unread'  => $count,
        ]);
    }

    /**
     * Format payload pesan
     */
    protected function formatMessagePayload(ChatMessage $msg, $currentUser, ChatRoom $room): array
    {
        $isMe = $msg->sender_id === $currentUser->id;
        $roleLabel = match ($msg->sender?->role) {
            'guru'     => 'Wali Kelas',
            'orangtua' => 'Orang Tua',
            'siswa'    => 'Siswa',
            default    => 'Pengguna',
        };

        $otherParticipantsCount = $room->participants()->where('user_id', '!=', $msg->sender_id)->count();
        $otherReadsCount = $msg->reads->where('user_id', '!=', $msg->sender_id)->count();

        $readStatus = 'sent';
        if ($otherReadsCount > 0) {
            $readStatus = ($otherParticipantsCount > 0 && $otherReadsCount >= $otherParticipantsCount) ? 'read_all' : 'read';
        }

        return [
            'id'              => $msg->id,
            'sender_id'       => $msg->sender_id,
            'sender_name'     => $msg->sender?->name ?? 'Pengguna',
            'sender_role'     => $roleLabel,
            'is_me'           => $isMe,
            'is_deleted'      => (bool) $msg->is_deleted,
            'can_delete'      => $msg->canBeDeletedBy($currentUser),
            'message'         => $msg->is_deleted ? 'Pesan ini telah ditarik' : $msg->message,
            'attachment_url'  => (! $msg->is_deleted && $msg->attachment_path) ? Storage::url($msg->attachment_path) : null,
            'attachment_name' => $msg->attachment_name,
            'attachment_type' => $msg->attachment_type,
            'created_at'      => $msg->created_at->toIso8601String(),
            'time'            => $msg->created_at->format('H:i'),
            'date'            => $msg->created_at->format('d M Y'),
            'read_status'     => $readStatus,
            'reads_count'     => $otherReadsCount,
            'read_details'    => $msg->read_details,
        ];
    }
}
