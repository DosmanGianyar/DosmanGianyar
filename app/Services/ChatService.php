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
    /**
     * Dapatkan atau buat ruang konsultasi untuk seorang siswa
     * @param string $type 'trio' (Bersama Siswa, Ortu, Wali) | 'parent_teacher' (Privat Ortu & Wali)
     */
    public function getOrCreateRoomForStudent(User $student, string $type = 'trio'): ?ChatRoom
    {
        if (! $student->isSiswa()) {
            return null;
        }

        $type = in_array($type, ['trio', 'parent_teacher']) ? $type : 'trio';
        $class = $student->schoolClass;
        $className = $class ? ' (' . $class->name . ')' : '';

        $roomName = $type === 'parent_teacher'
            ? 'Privat Ortu & Wali: ' . $student->name . $className
            : 'Konsultasi: ' . $student->name . $className;

        $room = ChatRoom::firstOrCreate(
            ['student_id' => $student->id, 'type' => $type],
            [
                'school_class_id' => $class?->id,
                'name'            => $roomName,
            ]
        );

        if ($class && $room->school_class_id !== $class->id) {
            $room->update([
                'school_class_id' => $class->id,
                'name'            => $roomName,
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
        $isPrivate = ($room->type ?? 'trio') === 'parent_teacher';

        // 1. Siswa: HANYA dimasukkan jika room bertipe 'trio'.
        // Siswa SAMA SEKALI DILARANG masuk ke room 'parent_teacher'.
        if (! $isPrivate) {
            ChatRoomParticipant::firstOrCreate(
                ['chat_room_id' => $room->id, 'user_id' => $student->id],
                ['role' => 'siswa']
            );
        } else {
            // Pastikan jika ada record siswa di room privat sebelumnya, langsung dihapus
            ChatRoomParticipant::where('chat_room_id', $room->id)
                ->where('user_id', $student->id)
                ->delete();
        }

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
     * @param string|null $type 'trio' | 'parent_teacher' | null (semua)
     */
    public function getRoomsForUser(User $user, ?string $type = null): Collection
    {
        if ($user->isSiswa()) {
            // Siswa HANYA memiliki akses ke ruang 'trio'
            if ($type === 'parent_teacher') {
                return collect();
            }
            $room = $this->getOrCreateRoomForStudent($user, 'trio');
            return $room ? collect([$room]) : collect();
        }

        $typesToFetch = $type ? [$type] : ['trio', 'parent_teacher'];

        if ($user->isOrangtua()) {
            $children = $user->children;
            $rooms = collect();
            foreach ($children as $child) {
                foreach ($typesToFetch as $t) {
                    $room = $this->getOrCreateRoomForStudent($child, $t);
                    if ($room) {
                        $rooms->push($room);
                    }
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
                ->orderBy('name')
                ->get();

            $rooms = collect();
            foreach ($students as $student) {
                foreach ($typesToFetch as $t) {
                    $room = $this->getOrCreateRoomForStudent($student, $t);
                    if ($room) {
                        $rooms->push($room);
                    }
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
     * Dapatkan ruang saudara (sibling) untuk beralih antara Trio dan Privat Ortu-Wali
     */
    public function getSiblingRoom(ChatRoom $room): ?ChatRoom
    {
        if (! $room->student) {
            return null;
        }

        $targetType = $room->isParentTeacher() ? 'trio' : 'parent_teacher';
        return $this->getOrCreateRoomForStudent($room->student, $targetType);
    }

    /**
     * Hitung total pesan belum dibaca per tipe (trio dan privat)
     */
    public function getUnreadCountsByType(User $user): array
    {
        if ($user->isSiswa()) {
            $trioCount = $this->getTotalUnreadCount($user, 'trio');
            return [
                'trio'           => $trioCount,
                'parent_teacher' => 0,
                'total'          => $trioCount,
            ];
        }

        $trioRooms = $this->getRoomsForUser($user, 'trio');
        $privateRooms = $this->getRoomsForUser($user, 'parent_teacher');

        $trioCount = 0;
        foreach ($trioRooms as $r) {
            $trioCount += $r->unreadCountFor($user->id);
        }

        $privateCount = 0;
        foreach ($privateRooms as $r) {
            $privateCount += $r->unreadCountFor($user->id);
        }

        return [
            'trio'           => $trioCount,
            'parent_teacher' => $privateCount,
            'total'          => $trioCount + $privateCount,
        ];
    }

    /**
     * Hitung total pesan belum dibaca untuk user di seluruh ruang chat miliknya
     */
    public function getTotalUnreadCount(User $user, ?string $type = null): int
    {
        $rooms = $this->getRoomsForUser($user, $type);
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
        $isPrivate = $room->isParentTeacher();
        $titlePrefix = $isPrivate ? "🔒 [Privat] " : "";
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

            $chatType = match ($sender->role) {
                'orangtua' => 'chat_orangtua',
                'siswa'    => 'chat_siswa',
                'guru'     => 'chat_guru',
                default    => 'chat',
            };

            \App\Services\NotificationService::send(
                $targetUser->id,
                "{$titlePrefix}Pesan Baru dari {$senderTitle}",
                $preview ?: 'Mengirim sebuah lampiran',
                $chatType,
                $route
            );
        }
    }
}
