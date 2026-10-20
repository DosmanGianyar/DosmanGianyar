<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(protected ChatService $chatService)
    {
    }

    /**
     * Tampilkan daftar ruang obrolan sesuai role user
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $allowedTabs = match ($user->role) {
            'siswa'    => ['all', 'class_group', 'trio'],
            'orangtua' => ['all', 'trio', 'parent_teacher'],
            default    => ['all', 'class_group', 'trio', 'parent_teacher'],
        };

        $defaultTab = $user->isSiswa() ? 'all' : 'trio';
        $activeTab = $request->query('tab', $defaultTab);
        if (! in_array($activeTab, $allowedTabs)) {
            $activeTab = $defaultTab;
        }

        $typeFilter = $activeTab === 'all' ? null : $activeTab;
        $rooms = $this->chatService->getRoomsForUser($user, $typeFilter);
        $unreadStats = $this->chatService->getUnreadCountsByType($user);

        // Tentukan layout berdasarkan role
        $layout = match ($user->role) {
            'guru'     => 'layouts.guru',
            'orangtua' => 'layouts.orangtua',
            'siswa'    => 'layouts.siswa',
            default    => 'layouts.app',
        };

        // Siapkan data ringkasan per room
        $roomData = $rooms->map(function (ChatRoom $room) use ($user) {
            return [
                'room'         => $room,
                'student'      => $room->student,
                'class'        => $room->schoolClass,
                'unread_count' => $room->unreadCountFor($user->id),
                'last_message' => $room->last_message_text,
                'last_time'    => $room->last_message_at?->diffForHumans() ?? '',
            ];
        });

        return view('chat.index', [
            'layout'      => $layout,
            'roomData'    => $roomData,
            'user'        => $user,
            'activeTab'   => $activeTab,
            'unreadStats' => $unreadStats,
        ]);
    }

    /**
     * Tampilkan ruang obrolan tertentu
     */
    public function show(ChatRoom $room, Request $request)
    {
        $user = auth()->user();

        // 1. Larangan keras: Siswa SAMA SEKALI TIDAK BOLEH mengakses room 'parent_teacher'
        if ($user->isSiswa() && $room->isParentTeacher()) {
            abort(403, 'Akses ditolak. Ruang obrolan ini bersifat privat khusus Orang Tua dan Wali Kelas.');
        }

        // 2. Orang Tua tidak boleh mengakses grup kelas siswa
        if ($user->isOrangtua() && $room->isClassGroup()) {
            abort(403, 'Akses ditolak. Grup kelas ini dikhususkan untuk siswa dan wali kelas.');
        }

        // Validasi partisipan
        $isParticipant = $room->participants()->where('user_id', $user->id)->exists();
        if (! $isParticipant) {
            // Coba sinkronisasi jika relasi valid
            if ($room->isClassGroup() && $user->isSiswa() && $user->class_id === $room->school_class_id) {
                $this->chatService->syncClassGroupParticipants($room, $room->schoolClass);
            } elseif ($room->isClassGroup() && $user->isGuru() && $user->homeroomClass?->id === $room->school_class_id) {
                $this->chatService->syncClassGroupParticipants($room, $room->schoolClass);
            } elseif ($user->isSiswa() && $room->student_id === $user->id && $room->isTrio()) {
                $this->chatService->syncRoomParticipants($room, $user);
            } elseif ($user->isOrangtua() && $user->children->contains('id', $room->student_id)) {
                $this->chatService->syncRoomParticipants($room, $room->student);
            } elseif ($user->isGuru() && $user->homeroomClass?->id === $room->school_class_id) {
                $this->chatService->syncRoomParticipants($room, $room->student);
            } else {
                abort(403, 'Anda tidak memiliki akses ke ruang konsultasi ini.');
            }
        }

        // Tandai sudah dibaca
        $this->chatService->markAsRead($room, $user);

        // Muat riwayat pesan terakhir (100 pesan)
        $messages = $room->messages()
            ->with(['sender', 'reads'])
            ->orderBy('id', 'asc')
            ->limit(100)
            ->get();

        // Info partisipan
        $participants = $room->participants()->with('user')->get();

        $layout = match ($user->role) {
            'guru'     => 'layouts.guru',
            'orangtua' => 'layouts.orangtua',
            'siswa'    => 'layouts.siswa',
            default    => 'layouts.app',
        };

        $backUrl = match ($user->role) {
            'guru'     => route('guru.chat.index', ['tab' => $room->type]),
            'orangtua' => route('orangtua.chat.index', ['tab' => $room->type]),
            'siswa'    => route('siswa.dashboard'),
            default    => url('/'),
        };

        // Info status terakhir dilihat
        $participantLastSeen = $this->chatService->getParticipantLastSeen($room);

        // Sibling room untuk beralih antara trio dan privat (khusus guru & orangtua)
        $siblingRoom = (! $user->isSiswa()) ? $this->chatService->getSiblingRoom($room) : null;

        return view('chat.show', [
            'layout'              => $layout,
            'room'                => $room,
            'siblingRoom'         => $siblingRoom,
            'messages'            => $messages,
            'participants'        => $participants,
            'participantLastSeen' => $participantLastSeen,
            'user'                => $user,
            'backUrl'             => $backUrl,
        ]);
    }

    /**
     * API JSON: Polling pesan baru & status pembacaan & pesan ditarik
     */
    public function getMessages(ChatRoom $room, Request $request): JsonResponse
    {
        $user = auth()->user();

        $isParticipant = $room->participants()->where('user_id', $user->id)->exists();
        if (! $isParticipant) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $afterId = (int) $request->query('after_id', 0);

        // Tandai sudah dibaca
        $this->chatService->markAsRead($room, $user);

        // Pesan baru setelah after_id
        $newMessages = $room->messages()
            ->with(['sender', 'reads.user'])
            ->when($afterId > 0, fn($q) => $q->where('id', '>', $afterId))
            ->orderBy('id', 'asc')
            ->get();

        $formattedMessages = $newMessages->map(function (ChatMessage $msg) use ($user, $room) {
            return $this->formatMessagePayload($msg, $user, $room);
        });

        // Ambil status pembacaan untuk pesan-pesan yang dikirim oleh $user agar tanda centang bisa terupdate
        $myRecentMessages = $room->messages()
            ->where('sender_id', $user->id)
            ->where('created_at', '>=', now()->subDays(2))
            ->with(['reads.user'])
            ->get();

        $readStatuses = [];
        $readDetails = [];
        $otherParticipantsCount = $room->participants()->where('user_id', '!=', $user->id)->count();

        foreach ($myRecentMessages as $msg) {
            $otherReads = $msg->reads->where('user_id', '!=', $user->id)->count();
            $status = 'sent'; // default centang 1
            if ($otherReads > 0) {
                $status = ($otherParticipantsCount > 0 && $otherReads >= $otherParticipantsCount) ? 'read_all' : 'read';
            }
            $readStatuses[$msg->id] = $status;
            $readDetails[$msg->id] = $msg->read_details;
        }

        // Ambil ID pesan-pesan yang ditarik dalam 2 hari terakhir
        $deletedIds = $room->messages()
            ->where('is_deleted', true)
            ->where('deleted_at', '>=', now()->subDays(2))
            ->pluck('id');

        return response()->json([
            'messages'      => $formattedMessages,
            'read_statuses' => $readStatuses,
            'read_details'  => $readDetails,
            'deleted_ids'   => $deletedIds,
        ]);
    }

    /**
     * API JSON: Tarik / Hapus pesan oleh pengirim
     */
    public function deleteMessage(ChatMessage $message, Request $request): JsonResponse
    {
        $user = auth()->user();

        if (! $message->canBeDeletedBy($user)) {
            return response()->json(['error' => 'Pesan tidak dapat ditarik (hanya pengirim dan maksimal 60 menit setelah terkirim).'], 403);
        }

        $this->chatService->deleteMessage($message, $user);

        return response()->json([
            'success'    => true,
            'message_id' => $message->id,
        ]);
    }

    /**
     * API JSON: Rincian pembaca pesan (siapa saja yang telah membaca)
     */
    public function getReadDetails(ChatMessage $message, Request $request): JsonResponse
    {
        $user = auth()->user();
        $isParticipant = $message->chatRoom->participants()->where('user_id', $user->id)->exists();
        if (! $isParticipant) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'message_id'   => $message->id,
            'read_details' => $message->read_details,
        ]);
    }

    /**
     * Kirim pesan baru (AJAX / POST)
     */
    public function send(ChatRoom $room, Request $request)
    {
        $user = auth()->user();

        $isParticipant = $room->participants()->where('user_id', $user->id)->exists();
        if (! $isParticipant) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'message'    => 'nullable|string|max:3000',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,txt',
        ]);

        if (empty(trim($validated['message'] ?? '')) && ! $request->hasFile('attachment')) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Pesan atau lampiran tidak boleh kosong.'], 422);
            }
            return back()->with('error', 'Pesan atau lampiran tidak boleh kosong.');
        }

        $message = $this->chatService->sendMessage(
            $room,
            $user,
            $validated['message'] ?? '',
            $request->file('attachment')
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $this->formatMessagePayload($message, $user, $room),
            ]);
        }

        return back();
    }

    /**
     * API JSON: Total unread count untuk navbar badge
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['unread' => 0]);
        }

        $count = $this->chatService->getTotalUnreadCount($user);
        return response()->json(['unread' => $count]);
    }

    /**
     * Helper payload format
     */
    protected function formatMessagePayload(ChatMessage $msg, $currentUser, ChatRoom $room): array
    {
        $isMe = $msg->sender_id === $currentUser->id;
        $roleLabel = match ($msg->sender?->role) {
            'guru'     => 'Wali Kelas',
            'orangtua' => 'Orang Tua',
            'siswa'    => 'Siswa',
            default    => '',
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
            'time'            => $msg->created_at->format('H:i'),
            'date'            => $msg->created_at->format('d M Y'),
            'read_status'     => $readStatus, // 'sent', 'read', 'read_all'
            'reads_count'     => $otherReadsCount,
            'read_details'    => $msg->read_details,
        ];
    }
}
