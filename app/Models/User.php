<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'photo', 'phone',
        'nis', 'nisn', 'gender', 'class_id', 'parent_name', 'parent_phone', 'birth_date', 'address',
        'nip', 'subject',
        'device_id', 'device_locked_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date'        => 'date',
            'device_locked_at'  => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ─── Filament ────────────────────────────────────────────────────────────
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin';
    }

    // ─── Role Helpers ────────────────────────────────────────────────────────
    public function isAdmin(): bool           { return $this->role === 'admin'; }
    public function isGuru(): bool            { return $this->role === 'guru'; }
    public function isSiswa(): bool           { return in_array($this->role, ['siswa', 'siswa_pengelola']); }
    public function isSiswaPengelola(): bool  { return $this->role === 'siswa_pengelola'; }
    public function isBk(): bool              { return $this->role === 'guru' && str_contains(strtolower($this->subject ?? ''), 'bk'); }

    public function dashboardRoute(): string
    {
        return match($this->role) {
            'admin'            => '/admin',
            'guru'             => route('guru.dashboard'),
            'siswa'            => route('siswa.dashboard'),
            'siswa_pengelola'  => route('siswa.dashboard'),
            default            => '/',
        };
    }

    // ─── Device Lock ─────────────────────────────────────────────────────────

    public function hasDeviceLocked(): bool
    {
        return $this->device_id !== null;
    }

    public function isDeviceAllowed(string $deviceId): bool
    {
        return $this->device_id === $deviceId;
    }

    public function lockToDevice(string $deviceId): void
    {
        $this->update([
            'device_id'        => $deviceId,
            'device_locked_at' => now(),
        ]);
    }

    public function resetDevice(): void
    {
        $this->update([
            'device_id'        => null,
            'device_locked_at' => null,
        ]);
        // Hapus semua token Flutter agar user wajib login ulang dari HP baru
        $this->tokens()->delete();
    }

    // ─── Photo ───────────────────────────────────────────────────────────────
    public function getPhotoUrlAttribute(): string
    {
        return $this->photo
            ? asset('storage/' . $this->photo)
            : asset('images/default-avatar.png');
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        return strtoupper(implode('', array_map(fn($w) => $w[0], array_slice($words, 0, 2))));
    }

    // ─── Relations ───────────────────────────────────────────────────────────
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function homeroomClass(): HasOne
    {
        return $this->hasOne(SchoolClass::class, 'homeroom_teacher_id');
    }

    public function attendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function todayAttendance(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Attendance::class)->whereDate('date', today());
    }

    public function permits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Permit::class, 'student_id');
    }

    public function exitPasses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExitPass::class, 'student_id');
    }

    public function conductLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ConductLog::class, 'student_id');
    }

    public function bkLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BkLog::class, 'student_id');
    }

    public function getTotalPointAttribute(): int
    {
        return (int) $this->conductLogs()->sum('point');
    }
}
