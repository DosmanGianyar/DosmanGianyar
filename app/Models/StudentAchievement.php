<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAchievement extends Model
{
    protected $fillable = [
        'student_id', 'category_id', 'title', 'description',
        'event_name', 'organizer', 'field_category', 'participation_type',
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

    public function levelLabel(): string
    {
        return match ($this->level) {
            'sekolah'       => 'Sekolah',
            'kabupaten'     => 'Kabupaten/Kota',
            'provinsi'      => 'Provinsi',
            'nasional'      => 'Nasional',
            'internasional' => 'Internasional',
            default         => ucfirst($this->level),
        };
    }

    public function fieldCategoryLabel(): string
    {
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

    public function curationStatusLabel(): string
    {
        return match ($this->curation_status) {
            'curated'       => 'Lolos Kurasi Resmi',
            'not_curatable' => 'Prestasi Internal (Tidak Dikurasi)',
            'revision'      => 'Perlu Revisi Berkas',
            'rejected'      => 'Tidak Layak',
            default         => 'Pengajuan Kurasi',
        };
    }

    public function curationStatusColor(): string
    {
        return match ($this->curation_status) {
            'curated'       => 'success',
            'not_curatable' => 'info',
            'revision'      => 'warning',
            'rejected'      => 'danger',
            default         => 'gray',
        };
    }

    public function curationStatusBadgeClass(): string
    {
        return match ($this->curation_status) {
            'curated'       => 'bg-emerald-100 text-emerald-800 border border-emerald-300',
            'not_curatable' => 'bg-blue-100 text-blue-800 border border-blue-300',
            'revision'      => 'bg-amber-100 text-amber-800 border border-amber-300',
            'rejected'      => 'bg-rose-100 text-rose-800 border border-rose-300',
            default         => 'bg-gray-100 text-gray-800 border border-gray-300',
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
