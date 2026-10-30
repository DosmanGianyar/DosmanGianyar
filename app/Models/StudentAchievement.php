<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAchievement extends Model
{
    protected $fillable = [
        'achievement_code',
        'student_id', 'category_id', 'extracurricular_id', 'pembina_id',
        'title', 'description',
        'event_name', 'organizer', 'field_category', 'participation_type', 'team_code',
        'achievement_date', 'level', 'rank', 'photo', 'certificate',
        'event_url', 'assignment_letter',
        'status', 'curation_status', 'curation_note',
        'verified_by', 'verified_at', 'rejection_reason',
        'is_curation',
        'doc_standard_checklist', 'doc_standard_file', 'doc_standard_url',
        'selection_level', 'selection_level_file', 'selection_level_url',
        'frequency_consistency', 'frequency_consistency_file', 'frequency_consistency_url',
        'infrastructure_type', 'infrastructure_file',
        'reward_types', 'reward_certificate_file', 'reward_photo_file', 'reward_recap_file',
    ];

    protected static function booted(): void
    {
        static::creating(function (StudentAchievement $achievement) {
            if (empty($achievement->achievement_code)) {
                $year = $achievement->achievement_date
                    ? (int) \Carbon\Carbon::parse($achievement->achievement_date)->format('Y')
                    : (int) date('Y');
                $achievement->achievement_code = static::generateUniqueCode($year);
            }

            if (empty($achievement->extracurricular_id) || empty($achievement->pembina_id)) {
                try {
                    $matched = \App\Services\AchievementPembinaMatcher::matchForAchievement(
                        title: $achievement->title,
                        eventName: $achievement->event_name,
                        description: $achievement->description,
                        fieldCategory: $achievement->field_category,
                        studentId: $achievement->student_id ? (int) $achievement->student_id : null,
                        existingExtracurricularId: $achievement->extracurricular_id ? (int) $achievement->extracurricular_id : null,
                        existingPembinaId: $achievement->pembina_id ? (int) $achievement->pembina_id : null,
                    );
                    if (empty($achievement->extracurricular_id) && ! empty($matched['extracurricular_id'])) {
                        $achievement->extracurricular_id = $matched['extracurricular_id'];
                    }
                    if (empty($achievement->pembina_id) && ! empty($matched['pembina_id'])) {
                        $achievement->pembina_id = $matched['pembina_id'];
                    }
                } catch (\Throwable $e) {
                    // Abaikan jika tabel belum siap
                }
            }
        });
    }

    public static function generateUniqueCode(?int $year = null): string
    {
        $year = $year ?: (int) date('Y');
        $chars = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

        do {
            $rand = '';
            for ($i = 0; $i < 4; $i++) {
                $rand .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $code = "PRS-{$year}-{$rand}";
        } while (static::where('achievement_code', $code)->exists());

        return $code;
    }

    protected $casts = [
        'achievement_date'       => 'date',
        'verified_at'            => 'datetime',
        'is_curation'            => 'boolean',
        'doc_standard_checklist' => 'array',
        'reward_types'           => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AchievementCategory::class, 'category_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function extracurricular(): BelongsTo
    {
        return $this->belongsTo(Extracurricular::class, 'extracurricular_id');
    }

    public function pembina(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembina_id');
    }

    /**
     * Daftar seluruh nama guru pembina untuk prestasi ini.
     * Jika ekstrakurikuler terkait memiliki lebih dari satu pembina, kembalikan semua pembina ekstrakurikuler tersebut.
     */
    public function pembinaList(): array
    {
        $extraPembinas = $this->extracurricular ? $this->extracurricular->pembina_list : [];

        if (count($extraPembinas) > 1) {
            if ($this->pembina && ! empty($this->pembina->name) && ! in_array($this->pembina->name, $extraPembinas, true)) {
                return array_values(array_unique(array_merge([$this->pembina->name], $extraPembinas)));
            }
            return $extraPembinas;
        }

        if ($this->pembina && ! empty($this->pembina->name)) {
            return [$this->pembina->name];
        }

        if (count($extraPembinas) > 0) {
            return $extraPembinas;
        }

        return [];
    }

    public function pembinaName(): string
    {
        $list = $this->pembinaList();
        return count($list) > 0 ? implode(', ', $list) : '—';
    }

    public function extracurricularName(): string
    {
        return $this->extracurricular?->name ?? '—';
    }

    public function levelLabel(): string
    {
        if (empty($this->level)) {
            return '—';
        }

        return match ($this->level) {
            'sekolah'       => 'Sekolah',
            'kabupaten'     => 'Kabupaten/Kota',
            'provinsi'      => 'Provinsi',
            'nasional'      => 'Nasional',
            'internasional' => 'Internasional',
            default         => ucfirst((string) $this->level),
        };
    }

    public function fieldCategoryLabel(): string
    {
        if (empty($this->field_category)) {
            return 'Lainnya';
        }

        return match ($this->field_category) {
            'sains_riset'   => 'Sains & Riset',
            'olahraga'      => 'Olahraga',
            'seni_budaya'   => 'Seni & Budaya',
            'bahasa_debat'  => 'Bahasa & Debat',
            'keagamaan'     => 'Keagamaan',
            'akademik'      => 'Akademik',
            default         => 'Lainnya',
        };
    }

    public function participationTypeLabel(): string
    {
        return match ($this->participation_type) {
            'beregu' => 'Beregu (Kelompok)',
            default  => 'Perorangan (Individu)',
        };
    }

    public function isBeregu(): bool
    {
        return $this->participation_type === 'beregu';
    }

    /**
     * Normalisasi judul inti lomba (menghapus kata tambahan seperti "Juara 2", "Lomba", "Tingkat Provinsi Bali", "Tahun 2026", dsb.)
     * agar ajuan lomba yang sama dari beberapa siswa tetap dikenali sebagai 1 lomba.
     */
    public static function normalizeCompetitionCoreTitle(?string $title): string
    {
        $raw = mb_strtolower(trim((string) $title));
        if ($raw === '') {
            return '';
        }

        // Samakan ejaan umum
        $raw = str_replace(['vidio', 'tounament', 'championsip'], ['video', 'tournament', 'championship'], $raw);
        $clean = preg_replace('/[^a-z0-9\s]+/u', ' ', $raw) ?? $raw;

        $stopWords = [
            'juara', 'peringkat', 'rank', 'harapan', 'finalis', 'besar', 'medali', 'emas', 'perak', 'perunggu', 'bersama',
            'lomba', 'kejuaraan', 'kompetisi', 'turnamen', 'tournament', 'competition', 'championship',
            'tingkat', 'provinsi', 'kabupaten', 'kota', 'nasional', 'internasional', 'sekolah', 'regional', 'daerah',
            'bali', 'gianyar', 'denpasar', 'badung', 'se', 'antar', 'kategori', 'siswa', 'siswi',
            'sma', 'smk', 'ma', 'sederajat', 'kreatif', 'edukasi', 'edukatif',
            'tahun', 'thn', '2023', '2024', '2025', '2026', '2027',
            '1', '2', '3', '4', '5', 'i', 'ii', 'iii', 'iv', 'v',
        ];

        $tokens = array_values(array_filter(
            explode(' ', $clean),
            static fn (string $w) => $w !== '' && ! in_array($w, $stopWords, true)
        ));

        if (count($tokens) > 0) {
            return implode(' ', $tokens);
        }

        return trim(preg_replace('/\s+/u', ' ', $clean) ?? $clean);
    }

    /**
     * Normalisasi peringkat (misal "Juara 2", "2", "Juara 2 / Perak" -> "2")
     */
    public static function normalizeRankKey(?string $rank, ?string $title = null): string
    {
        $combined = mb_strtolower(trim((string) $rank . ' ' . (string) $title));

        if (preg_match('/harapan\s*(1|i\b)/u', $combined)) {
            return 'harapan_1';
        }
        if (preg_match('/harapan\s*(2|ii\b)/u', $combined)) {
            return 'harapan_2';
        }
        if (preg_match('/harapan\s*(3|iii\b)/u', $combined)) {
            return 'harapan_3';
        }
        if (preg_match('/\b(juara\s*1|peringkat\s*1|emas)\b/u', $combined) || trim((string) $rank) === '1') {
            return '1';
        }
        if (preg_match('/\b(juara\s*2|peringkat\s*2|perak)\b/u', $combined) || trim((string) $rank) === '2') {
            return '2';
        }
        if (preg_match('/\b(juara\s*3|peringkat\s*3|perunggu)\b/u', $combined) || trim((string) $rank) === '3') {
            return '3';
        }
        if (preg_match('/finalis/u', $combined)) {
            return 'finalis';
        }

        $normRank = mb_strtolower(preg_replace('/\s+/u', ' ', trim((string) $rank)));
        return $normRank !== '' ? $normRank : 'none';
    }

    /**
     * Kunci pengelompokan lomba agar 1 lomba beregu / kelompok juara tampil dalam 1 baris,
     * sekaligus mencegah duplikasi jika beberapa siswa menginput lomba yang sama dengan sedikit perbedaan penulisan judul.
     */
    public function competitionGroupKey(): string
    {
        $coreTitle = static::normalizeCompetitionCoreTitle($this->title);
        $dateStr   = $this->achievement_date ? \Carbon\Carbon::parse($this->achievement_date)->format('Y-m-d') : '';
        $normRank  = static::normalizeRankKey($this->rank, $this->title);
        $normLevel = mb_strtolower(trim((string) $this->level));

        if ($this->isBeregu() || ! empty($this->team_code)) {
            if ($coreTitle !== '') {
                return "beregu:{$dateStr}|{$normLevel}|{$normRank}|{$coreTitle}";
            }
            if (! empty($this->team_code)) {
                return 'team:' . trim((string) $this->team_code);
            }
        }

        if ($coreTitle !== '') {
            return "indiv:{$this->student_id}|{$dateStr}|{$normLevel}|{$normRank}|{$coreTitle}";
        }

        return 'id:' . $this->id;
    }

    public function getTeamMembersAttribute()
    {
        if ($this->isBeregu() || ! empty($this->team_code)) {
            $targetKey = $this->competitionGroupKey();

            $candidates = static::query()
                ->where(function ($q) {
                    if ($this->achievement_date) {
                        $q->whereDate('achievement_date', \Carbon\Carbon::parse($this->achievement_date)->format('Y-m-d'));
                    }
                    if (! empty($this->team_code)) {
                        $q->orWhere('team_code', $this->team_code);
                    }
                })
                ->with('student.schoolClass')
                ->orderBy('id')
                ->get();

            $matched = $candidates
                ->filter(fn (self $item) => $item->competitionGroupKey() === $targetKey || (! empty($this->team_code) && $item->team_code === $this->team_code))
                ->unique('student_id')
                ->values();

            if ($matched->isNotEmpty()) {
                return $matched;
            }
        }

        return collect([$this]);
    }

    public function curationStatusLabel(): string
    {
        return match ($this->curation_status) {
            'curated'       => 'Lolos Kurasi Resmi',
            'not_curatable' => 'Prestasi Internal Sekolah',
            'revision'      => 'Perlu Revisi Berkas',
            'rejected'      => 'Tidak Layak / Ditolak',
            default         => 'Menunggu Penilaian Kurasi',
        };
    }

    public function curationStatusColor(): string
    {
        return match ($this->curation_status) {
            'curated'       => 'success',
            'not_curatable' => 'info',
            'revision'      => 'warning',
            'rejected'      => 'danger',
            default         => 'warning',
        };
    }

    public function curationStatusBadgeClass(): string
    {
        return match ($this->curation_status) {
            'curated'       => 'bg-emerald-100 text-emerald-800 border border-emerald-300',
            'not_curatable' => 'bg-blue-100 text-blue-800 border border-blue-300',
            'revision'      => 'bg-amber-100 text-amber-800 border border-amber-300',
            'rejected'      => 'bg-rose-100 text-rose-800 border border-rose-300',
            default         => 'bg-yellow-100 text-yellow-800 border border-yellow-300',
        };
    }

    public function levelColor(): string
    {
        return match ($this->level) {
            'sekolah'       => 'gray',
            'kabupaten'     => 'info',
            'provinsi'      => 'warning',
            'nasional'      => 'success',
            'internasional' => 'danger',
            default         => 'gray',
        };
    }

    public function levelBadgeClass(): string
    {
        return match ($this->level) {
            'sekolah'       => 'bg-gray-100 text-gray-700',
            'kabupaten'     => 'bg-blue-100 text-blue-700',
            'provinsi'      => 'bg-yellow-100 text-yellow-700',
            'nasional'      => 'bg-green-100 text-green-700',
            'internasional' => 'bg-red-100 text-red-700',
            default         => 'bg-gray-100 text-gray-700',
        };
    }

    public function statusLabel(): string
    {
        return $this->curationStatusLabel();
    }

    public function statusColor(): string
    {
        return $this->curationStatusColor();
    }

    public function statusBadgeClass(): string
    {
        return $this->curationStatusBadgeClass();
    }

    public function photoUrl(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }

    public function certificateUrl(): ?string
    {
        return $this->certificate ? asset('storage/' . $this->certificate) : null;
    }

    public function assignmentLetterUrl(): ?string
    {
        return $this->assignment_letter ? asset('storage/' . $this->assignment_letter) : null;
    }
}
