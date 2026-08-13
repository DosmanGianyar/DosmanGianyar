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
        'device_id', 'device_locked_at', 'qr_code_token',
        'hobbies', 'aspirations', 'rt_rw', 'kelurahan', 'kecamatan', 'kabupaten',
        'residence_status', 'transportation', 'distance_km', 'travel_time_minutes',
        'father_name', 'father_phone', 'father_job',
        'mother_name', 'mother_phone', 'mother_job',
        'guardian_name', 'guardian_phone', 'guardian_job',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation',
        'blood_type', 'medical_history', 'height_cm', 'weight_kg',
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
            'distance_km'           => 'float',
            'travel_time_minutes'   => 'integer',
            'height_cm'             => 'integer',
            'weight_kg'             => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->qr_code_token)) {
                $user->qr_code_token = (string) \Illuminate\Support\Str::uuid();
            }
            if (empty($user->password)) {
                $username = match (true) {
                    $user->isGuru()                           => $user->nip ?: ($user->email ?: 'guru123'),
                    $user->isSiswa() || $user->isPengelola()  => $user->nisn ?: ($user->nis ?: ($user->email ?: 'siswa123')),
                    $user->isOrangtua()                       => $user->phone ?: 'orangtua123',
                    default                                   => $user->email ?: 'user123',
                };
                $user->password = $username;
                $user->must_change_password = true;
            }
        });

        static::saving(function (User $user) {
            if ($user->phone) {
                $user->phone = static::formatPhoneNumber($user->phone);
            }
            if ($user->parent_phone) {
                $user->parent_phone = static::formatPhoneNumber($user->parent_phone);
            }
        });

        static::saved(function (User $user) {
            if ($user->isSiswa() && ($user->wasRecentlyCreated || $user->wasChanged('parent_phone'))) {
                \App\Services\OrangtuaSyncService::resyncStudent($user);
            }
        });
    }

    public static function formatPhoneNumber(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $cleaned = preg_replace('/[^0-9]/', '', trim($phone));
        if (empty($cleaned)) {
            return null;
        }

        if (str_starts_with($cleaned, '62')) {
            $cleaned = '0' . substr($cleaned, 2);
        }

        $cleaned = ltrim($cleaned, '0');

        if (empty($cleaned)) {
            return null;
        }

        return '0' . $cleaned;
    }

    public function setPhoneAttribute(?string $value): void
    {
        $this->attributes['phone'] = static::formatPhoneNumber($value);
    }

    public function setParentPhoneAttribute(?string $value): void
    {
        $this->attributes['parent_phone'] = static::formatPhoneNumber($value);
    }

    // ─── Filament ────────────────────────────────────────────────────────────
    public function isPembinaEkstra(): bool
    {
        return Extracurricular::where('pembina_id', $this->id)
            ->orWhereHas('teachers', fn ($q) => $q->where('users.id', $this->id))
            ->exists();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (in_array($this->role, [
            'admin', 'admin_kesiswaan', 'admin_kurikulum', 'admin_sarpras', 'admin_humas',
        ])) {
            return true;
        }

        if ($this->role === 'guru' && $this->isPembinaEkstra()) {
            return true;
        }

        return false;
    }

    // ─── Role Helpers ────────────────────────────────────────────────────────
    public function isAdmin(): bool           { return $this->role === 'admin'; }
    public function getQrTokenAttribute(): string
    {
        if (empty($this->attributes['qr_code_token'])) {
            $token = (string) \Illuminate\Support\Str::uuid();
            $this->attributes['qr_code_token'] = $token;
            if ($this->exists) {
                $this->saveQuietly();
            }
        }
        return $this->attributes['qr_code_token'];
    }

    public function isGuru(): bool            { return $this->role === 'guru'; }
    public function isSiswa(): bool           { return in_array($this->role, ['siswa', 'pengelola']); }
    public function isPengelola(): bool       { return $this->role === 'pengelola'; }
    public function isOrangtua(): bool        { return $this->role === 'orangtua'; }

    /**
     * Ringkasan akumulasi riwayat presensi & pengajuan siswa (Lupa Absen, Sakit, Izin, Dispensasi).
     */
    public function getAttendanceStatsSummary(): array
    {
        $studentId = $this->id;

        $forgotCount    = \App\Models\ForgotAttendanceRequest::where('student_id', $studentId)->count();
        $forgotApproved = \App\Models\ForgotAttendanceRequest::where('student_id', $studentId)->where('status', 'approved')->count();

        $sakitCount      = \App\Models\Attendance::where('user_id', $studentId)->where('status', 'sakit')->count();
        $izinCount       = \App\Models\Attendance::where('user_id', $studentId)->where('status', 'izin')->count();
        $dispensasiCount = \App\Models\Attendance::where('user_id', $studentId)->where('status', 'dispensasi')->count();

        return [
            'lupa_absen_total'    => $forgotCount,
            'lupa_absen_approved' => $forgotApproved,
            'sakit'               => $sakitCount,
            'izin'                => $izinCount,
            'dispensasi'          => $dispensasiCount,
        ];
    }

    public function getAttendanceStatsBadgesHtml(): \Illuminate\Support\HtmlString
    {
        $stats = $this->getAttendanceStatsSummary();

        $html = '<div class="grid grid-cols-2 gap-1 text-[10px] mt-1 max-w-[240px] font-sans">
            <span class="inline-flex items-center justify-between px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-200/60 font-semibold" title="Total Lupa Absen">
                <span>Lupa:</span> <span class="ml-1 font-bold">' . $stats['lupa_absen_total'] . 'x <span class="text-[9px] text-purple-500 font-normal">(ACC:' . $stats['lupa_absen_approved'] . ')</span></span>
            </span>
            <span class="inline-flex items-center justify-between px-1.5 py-0.5 rounded bg-violet-50 text-violet-700 border border-violet-200/60 font-semibold" title="Total Sakit">
                <span>Sakit:</span> <span class="ml-1 font-bold">' . $stats['sakit'] . 'x</span>
            </span>
            <span class="inline-flex items-center justify-between px-1.5 py-0.5 rounded bg-sky-50 text-sky-700 border border-sky-200/60 font-semibold" title="Total Izin">
                <span>Izin:</span> <span class="ml-1 font-bold">' . $stats['izin'] . 'x</span>
            </span>
            <span class="inline-flex items-center justify-between px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200/60 font-semibold" title="Total Dispensasi">
                <span>Dispen:</span> <span class="ml-1 font-bold">' . $stats['dispensasi'] . 'x</span>
            </span>
        </div>';

        return new \Illuminate\Support\HtmlString($html);
    }

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

    public function getAngkatanAttribute(): ?string
    {
        $class = $this->schoolClass;
        if ($class) {
            $grade = (string) ($class->grade ?? '');
            $name  = (string) ($class->name ?? '');

            if ($grade === '10' || str_contains($name, 'X-') || str_starts_with($name, 'X ') || str_starts_with($name, '10') || preg_match('/^X\b/i', $name)) {
                return 'Angkatan 62';
            }

            if ($grade === '11' || str_contains($name, 'XI-') || str_starts_with($name, 'XI ') || str_starts_with($name, '11') || preg_match('/^XI\b/i', $name)) {
                return 'Angkatan 61';
            }

            if ($grade === '12' || str_contains($name, 'XII-') || str_starts_with($name, 'XII ') || str_starts_with($name, '12') || preg_match('/^XII\b/i', $name)) {
                return 'Angkatan 60';
            }
        }

        if ($this->nis && preg_match('/^(\d{2})/', $this->nis, $m)) {
            $yr = (int) $m[1];
            if ($yr === 25) return 'Angkatan 63';
            if ($yr === 24) return 'Angkatan 62';
            if ($yr === 23) return 'Angkatan 61';
            if ($yr === 22) return 'Angkatan 60';
            return 'Angkatan 20' . $yr;
        }

        return 'Angkatan 62';
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

    public function extracurricularsAsTeacher(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Extracurricular::class, 'extracurricular_teachers', 'teacher_id', 'extracurricular_id')
            ->withTimestamps();
    }

    public function extracurricularsAsStudent(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Extracurricular::class, 'extracurricular_students', 'student_id', 'extracurricular_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /** Keanggotaan di ExtracurricularMember (tabel members yg baru) */
    public function memberExtracurriculars(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ExtracurricularMember::class, 'user_id');
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
        return $this->conductLogs()
            ->where(function ($q) {
                $q->where('type', 'pelanggaran')
                  ->orWhereHas('category', fn ($c) => $c->where('type', 'pelanggaran'));
            })
            ->count();
    }
}
