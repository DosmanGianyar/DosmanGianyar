<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\ChatMessageRead;
use App\Models\ChatRoom;
use App\Models\ChatRoomParticipant;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatService
{
    /**
     * Dapatkan atau buat ruang konsultasi segitiga untuk seorang siswa
     */
    public function getOrCreateRoomForStudent(User $student): ?ChatRoom
    {
        if (! $student->isSiswa()) {
            return null;
        }

        $class = $student->schoolClass;
        $className = $class ? ' (' . $class->name . ')' : '';

        $room = ChatRoom::firstOrCreate(
            ['student_id' => $student->id],
            [
                'school_class_id' => $class?->id,
                'name'            => 'Konsultasi: ' . $student->name . $className,
            ]
        );

        if ($class && $room->school_class_id !== $class->id) {
            $room->update([
                'school_class_id' => $class->id,
                'name'            => 'Konsultasi: ' . $student->name . $className,
            ]);
        }

        // Sinkronisasi Partisipan
        $this->syncRoomParticipants($room, $student, $class);

        return $room;
    }

    /**
     * Sinkronkan anggota (Siswa, Orang Tua, dan Wali Kelas)
     */
    public function syncRoomParticipants(ChatRoom $room, User $student, ?SchoolClass $class = null): void
    {
        $class = $class ?? $student->schoolClass;
        $homeroomTeacher = $class?->homeroomTeacher;
        $parents = $student->parentAccounts;

        // 1. Siswa
        ChatRoomParticipant::firstOrCreate(
            ['chat_room_id' => $room->id, 'user_id' => $student->id],
            ['role' => 'siswa']
        );

        // 2. Wali Kelas
        if ($homeroomTeacher) {
            ChatRoomParticipant::firstOrCreate(
                ['chat_room_id' => $room->id, 'user_id' => $homeroomTeacher->id],
                ['role' => 'guru']
            );
        }

        // 3. Orang Tua
        foreach ($parents as $parent) {
            ChatRoomParticipant::firstOrCreate(
                ['chat_room_id' => $room->id, 'user_id' => $parent->id],
                ['role' => 'orangtua']
            );
        }
    }

    /**
     * Ambil daftar ruang obrolan untuk pengguna yang sedang login
     */
    public function getRoomsForUser(User $user): Collection
    {
        if ($user->isSiswa()) {
            $room = $this->getOrCreateRoomForStudent($user);
            return $room ? collect([$room]) : collect();
        }

        if ($user->isOrangtua()) {
            $children = $user->children;
            $rooms = collect();
            foreach ($children as $child) {
                $room = $this->getOrCreateRoomForStudent($child);
                if ($room) {
                    $rooms->push($room);
                }
            }
            return $rooms->sortByDesc(fn($r) => $r->last_message_at ?? $r->updated_at)->values();
        }

        if ($user->isGuru()) {
            // Cek apakah guru adalah wali kelas
            $homeroomClass = $user->homeroomClass;
            if (! $homeroomClass) {
                return collect();
            }

            $students = $homeroomClass->students()
                ->where('status', 'aktif')
                ->orderBy('name')
                ->get();

            $rooms = collect();
            foreach ($students as $student) {
                $room = $this->getOrCreateRoomForStudent($student);
                if ($room) {
                    $rooms->push($room);
                }
            }

            return $rooms->sortByDesc(fn($r) => $r->last_message_at ?? $r->created_at)->values();
        }

        return collect();
    }

    /**
     * Kirim pesan baru ke dalam chat room
     */
    public function sendMessage(ChatRoom $room, User $sender, string $messageText, ?UploadedFile $attachment = null): ChatMessage
    {
        $attachmentPath = null;
        $attachmentName = null;
        $attachmentType = null;

        if ($attachment && $attachment->isValid()) {
            $attachmentName = $attachment->getClientOriginalName();
            $mime = $attachment->getMimeType();
            $attachmentType = str_starts_with($mime, 'image/') ? 'image' : 'file';
            $attachmentPath = $attachment->store('chat-attachments/' . $room->id, 'public');
        }

        $message = DB::transaction(function () use ($room, $sender, $messageText, $attachmentPath, $attachmentName, $attachmentType) {
            $msg = ChatMessage::create([
                'chat_room_id'    => $room->id,
                'sender_id'       => $sender->id,
                'message'         => $messageText,
                'attachment_path' => $attachmentPath,
                'attachment_name' => $attachmentName,
                'attachment_type' => $attachmentType,
            ]);

            $room->update([
                'last_message_at'        => now(),
                'last_message_text'      => Str::limit($messageText ?: ($attachmentName ? '[Lampiran: ' . $attachmentName . ']' : ''), 100),
                'last_message_sender_id' => $sender->id,
            ]);

            // Pengirim otomatis ditandai sudah membaca pesan miliknya
            ChatRoomParticipant::where('chat_room_id', $room->id)
                ->where('user_id', $sender->id)
                ->update(['last_read_at' => now()]);

            ChatMessageRead::create([
                'chat_message_id' => $msg->id,
                'user_id'         => $sender->id,
                'read_at'         => now(),
            ]);

            return $msg;
        });

        // Kirim notifikasi sistem (NotificationService) ke anggota lain di room
        $this->notifyParticipants($room, $sender, $messageText);

        return $message;
    }

    /**
     * Tandai semua pesan di room sebagai sudah dibaca oleh user
     */
    public function markAsRead(ChatRoom $room, User $user): void
    {
        // 1. Update last_read_at di participant
        ChatRoomParticipant::updateOrCreate(
            ['chat_room_id' => $room->id, 'user_id' => $user->id],
            ['last_read_at' => now()]
        );

        // 2. Catat pesan-pesan lawan bicara yang belum tercatat di chat_message_reads
        $unreadMessages = $room->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereDoesntHave('reads', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->pluck('id');

        if ($unreadMessages->isNotEmpty()) {
            $now = now();
            $rows = $unreadMessages->map(fn($id) => [
                'chat_message_id' => $id,
                'user_id'         => $user->id,
                'read_at'         => $now,
                'created_at'      => $now,
                'updated_at'      => $now,
            ])->toArray();

            ChatMessageRead::insertOrIgnore($rows);
        }
    }

    /**
     * Tarik / Hapus pesan oleh pengirimnya (delete for everyone)
     */
    public function deleteMessage(ChatMessage $message, User $user): bool
    {
        if (! $message->canBeDeletedBy($user)) {
            return false;
        }

        // Hapus file attachment jika ada
        if ($message->attachment_path && Storage::disk('public')->exists($message->attachment_path)) {
            Storage::disk('public')->delete($message->attachment_path);
        }

        $message->update([
            'is_deleted'      => true,
            'message'         => 'Pesan ini telah ditarik',
            'attachment_path' => null,
            'attachment_name' => null,
            'attachment_type' => null,
            'deleted_at'      => now(),
        ]);

        // Jika ini pesan terakhir di room, perbarui ringkasan pesan terakhir
        $room = $message->chatRoom;
        $latestMsg = $room->messages()->latest('id')->first();
        if ($latestMsg) {
            $room->update([
                'last_message_text' => $latestMsg->is_deleted ? 'Pesan ini telah ditarik' : Str::limit($latestMsg->message ?: '[Lampiran]', 100),
            ]);
        }

        return true;
    }

    /**
     * Dapatkan status terakhir dilihat (last seen) untuk semua partisipan di room
     */
    public function getParticipantLastSeen(ChatRoom $room): Collection
    {
        return $room->participants()->with('user')->get()->map(function ($p) {
            $lastRead = $p->last_read_at;
            $statusText = 'Belum membuka obrolan';

            if ($lastRead) {
                if ($lastRead->diffInMinutes(now()) <= 2) {
                    $statusText = 'Aktif sekarang';
                } else {
                    $statusText = 'Aktif ' . $lastRead->diffForHumans();
                }
            }

            $roleLabel = match ($p->role) {
                'guru'     => 'Wali Kelas',
                'orangtua' => 'Orang Tua',
                'siswa'    => 'Siswa',
                default    => 'Pengguna',
            };

            return [
                'user_id'     => $p->user_id,
                'name'        => $p->user?->name ?? 'Pengguna',
                'role'        => $roleLabel,
                'last_read'   => $lastRead,
                'status_text' => $statusText,
            ];
        });
    }

    /**
     * Hitung total pesan belum dibaca untuk user di seluruh ruang chat miliknya
     */
    public function getTotalUnreadCount(User $user): int
    {
        $rooms = $this->getRoomsForUser($user);
        $total = 0;
        foreach ($rooms as $room) {
            $total += $room->unreadCountFor($user->id);
        }
        return $total;
    }

    /**
     * Kirim notifikasi internal ke partisipan lain
     */
    protected function notifyParticipants(ChatRoom $room, User $sender, string $text): void
    {
        $roleLabel = match ($sender->role) {
            'guru'     => 'Wali Kelas',
            'orangtua' => 'Orang Tua',
            'siswa'    => 'Siswa',
            default    => 'Pengguna',
        };

        $senderTitle = "{$sender->name} ({$roleLabel})";
        $preview = Str::limit($text, 60);

        // Cari partisipan lain di room
        $otherParticipants = $room->participants()
            ->where('user_id', '!=', $sender->id)
            ->with('user')
            ->get();

        foreach ($otherParticipants as $participant) {
            $targetUser = $participant->user;
            if (! $targetUser) continue;

            $route = match ($targetUser->role) {
                'guru'     => route('guru.chat.show', $room->id),
                'orangtua' => route('orangtua.chat.show', $room->id),
                'siswa'    => route('siswa.chat.show', $room->id),
                default    => url('/'),
            };

            \App\Services\NotificationService::send(
                $targetUser->id,
                "Pesan Baru dari {$senderTitle}",
                $preview ?: 'Mengirim sebuah lampiran',
                'chat',
                $route
            );
        }
    }
}
