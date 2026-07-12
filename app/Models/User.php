<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'must_change_password', 'role', 'photo', 'phone',
        'nis', 'nisn', 'gender', 'class_id', 'parent_name', 'parent_phone', 'birth_date', 'address',
        'nip', 'subject',
        'device_id', 'device_locked_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'     => 'datetime',
            'birth_date'            => 'date',
            'device_locked_at'      => 'datetime',
            'password'              => 'hashed',
            'must_change_password'  => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (User $user) {
            if ($user->isSiswa() && ($user->wasRecentlyCreated || $user->wasChanged('parent_phone'))) {
                \App\Services\OrangtuaSyncService::resyncStudent($user);
            }
        });
    }

    // ─── Filament ────────────────────────────────────────────────────────────
    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, [
            'admin', 'admin_kesiswaan', 'admin_kurikulum', 'admin_sarpras', 'admin_humas',
        ]);
    }

    // ─── Role Helpers ────────────────────────────────────────────────────────
    public function isAdmin(): bool           { return $this->role === 'admin'; }
    public function isGuru(): bool            { return $this->role === 'guru'; }
    public function isSiswa(): bool           { return in_array($this->role, ['siswa', 'pengelola']); }
    public function isPengelola(): bool       { return $this->role === 'pengelola'; }
    public function isOrangtua(): bool        { return $this->role === 'orangtua'; }
    public function isBk(): bool
    {
        if ($this->role !== 'guru') return false;
        if (str_contains(strtolower($this->subject ?? ''), 'bk')) return true;
        try {
            return $this->subjects()->whereRaw('LOWER(name) LIKE ?', ['%bk%'])->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function dashboardRoute(): string
    {
        return match($this->role) {
            'admin',
            'admin_kesiswaan',
            'admin_kurikulum',
            'admin_sarpras',
            'admin_humas'      => '/admin',
            'guru'             => route('guru.dashboard'),
            'siswa'            => route('siswa.dashboard'),
            'pengelola'        => route('siswa.dashboard'),
            'orangtua'         => route('orangtua.dashboard'),
            default            => '/',
        };
    }

    // ─── Device Lock (multi-device, maks 5) ──────────────────────────────────

    const MAX_DEVICES = 5;

    public function devices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    public function isDeviceRegistered(string $deviceId): bool
    {
        return $this->devices()->where('device_id', $deviceId)->exists();
    }

    public function deviceCount(): int
    {
        return $this->devices()->count();
    }

    /**
     * Daftarkan device baru jika belum ada dan belum melebihi batas.
     * Return true jika berhasil, false jika sudah penuh (>= MAX_DEVICES).
     */
    public function registerDevice(string $deviceId): bool
    {
        // Sudah terdaftar → perbarui last_login_at saja
        $existing = $this->devices()->where('device_id', $deviceId)->first();
        if ($existing) {
            $existing->update(['last_login_at' => now()]);
            return true;
        }

        // Belum terdaftar → cek kuota
        if ($this->deviceCount() >= self::MAX_DEVICES) {
            return false;
        }

        $this->devices()->create([
            'device_id'     => $deviceId,
            'last_login_at' => now(),
        ]);

        return true;
    }

    public function resetDevices(): void
    {
        $this->devices()->delete();
        $this->tokens()->delete();
    }

    public function hasDeviceLocked(): bool
    {
        return $this->devices()->exists();
    }

    // ─── Backward-compat (kolom lama di tabel users, tidak dipakai lagi) ─────

    /** @deprecated Gunakan registerDevice() */
    public function lockToDevice(string $deviceId): void
    {
        $this->registerDevice($deviceId);
    }

    /** @deprecated Gunakan resetDevices() */
    public function resetDevice(): void
    {
        $this->resetDevices();
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

    public function subjects(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'teacher_subjects', 'teacher_id', 'subject_id')
                    ->withTimestamps();
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

    // Guru Wali: record penugasan (untuk siswa — mendapatkan guru wali)
    public function homeroomTeacherRecord(): HasOne
    {
        return $this->hasOne(StudentHomeroomTeacher::class, 'student_id');
    }

    // Guru Wali: daftar siswa yang diampu (untuk guru)
    public function waliStudents(): HasMany
    {
        return $this->hasMany(StudentHomeroomTeacher::class, 'teacher_id');
    }

    // Orangtua: daftar anak (untuk akun orangtua)
    public function children(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_students', 'parent_id', 'student_id')
                    ->withTimestamps();
    }

    // Siswa: daftar akun orangtua yang terhubung (untuk siswa)
    public function parentAccounts(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_students', 'student_id', 'parent_id')
                    ->withTimestamps();
    }

    public function getPelanggaranCountAttribute(): int
    {
        return $this->conductLogs()->whereHas('category', fn($q) => $q->where('type', 'pelanggaran'))->count();
    }
}
