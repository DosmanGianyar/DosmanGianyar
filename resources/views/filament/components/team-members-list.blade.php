@php
    $record = $getRecord();
    if ($record && $record->participation_type === 'beregu') {
        if (!empty($record->team_code)) {
            $matching = \App\Models\StudentAchievement::where('team_code', $record->team_code)
                ->with('student.schoolClass')
                ->get();
        } else {
            $matching = \App\Models\StudentAchievement::where('participation_type', 'beregu')
                ->where('title', $record->title)
                ->where('achievement_date', $record->achievement_date)
                ->with('student.schoolClass')
                ->get();
        }
        $students = $matching->pluck('student')->filter()->unique('id');
        if ($students->isEmpty() && $record->student) {
            $students = collect([$record->student]);
        }
    } else {
        $students = ($record && $record->student) ? collect([$record->student]) : collect();
    }

    $isBeregu = $record && $record->participation_type === 'beregu';
    $primaryStudent = $record?->student;
@endphp

<style>
    .sims-profile-card {
        width: 100% !important;
        box-sizing: border-box !important;
        background: #0f172a !important;
        border: 1px solid #334155 !important;
        border-radius: 16px !important;
        padding: 18px 22px !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25) !important;
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 20px !important;
    }
    :root:not(.dark) .sims-profile-card {
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06) !important;
    }

    .sims-profile-left {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        gap: 18px !important;
        flex: 1 1 520px !important;
        min-width: 280px !important;
    }

    .sims-profile-avatar-btn {
        position: relative !important;
        display: block !important;
        flex-shrink: 0 !important;
        border-radius: 16px !important;
        overflow: hidden !important;
        cursor: pointer !important;
        border: 2px solid #3b82f6 !important;
        padding: 0 !important;
        background: transparent !important;
        transition: transform 0.15s ease, box-shadow 0.15s ease !important;
    }
    .sims-profile-avatar-btn:hover {
        transform: scale(1.04) !important;
        box-shadow: 0 0 14px rgba(59, 130, 246, 0.5) !important;
    }

    .sims-profile-avatar-img {
        width: 82px !important;
        height: 82px !important;
        min-width: 82px !important;
        min-height: 82px !important;
        max-width: 82px !important;
        max-height: 82px !important;
        border-radius: 14px !important;
        object-fit: cover !important;
        display: block !important;
    }

    .sims-profile-avatar-hint {
        position: absolute !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        background: rgba(0, 0, 0, 0.65) !important;
        color: #ffffff !important;
        font-size: 0.65rem !important;
        font-weight: 700 !important;
        text-align: center !important;
        padding: 2px 0 !important;
        backdrop-filter: blur(2px) !important;
    }

    .sims-profile-info {
        display: flex !important;
        flex-direction: column !important;
        gap: 6px !important;
        flex: 1 !important;
        min-width: 0 !important;
    }

    .sims-pill-badge {
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        padding: 3px 9px !important;
        border-radius: 6px !important;
        font-size: 0.74rem !important;
        font-weight: 600 !important;
        background: rgba(30, 41, 59, 0.8) !important;
        border: 1px solid rgba(71, 85, 105, 0.6) !important;
        color: #e2e8f0 !important;
    }
    :root:not(.dark) .sims-pill-badge {
        background: #f1f5f9 !important;
        border: 1px solid #cbd5e1 !important;
        color: #334155 !important;
    }

    .sims-profile-actions {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        gap: 10px !important;
        flex-wrap: wrap !important;
        flex-shrink: 0 !important;
    }

    .btn-edit-student {
        background: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 0.78rem !important;
        padding: 9px 18px !important;
        border-radius: 10px !important;
        border: none !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.35) !important;
        transition: all 0.15s ease !important;
        text-decoration: none !important;
    }
    .btn-edit-student:hover {
        background: #1d4ed8 !important;
        transform: translateY(-1px) !important;
    }

    .btn-profile-student {
        background: rgba(51, 65, 85, 0.5) !important;
        color: #cbd5e1 !important;
        font-weight: 600 !important;
        font-size: 0.78rem !important;
        padding: 9px 15px !important;
        border-radius: 10px !important;
        border: 1px solid #475569 !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        transition: all 0.15s ease !important;
        text-decoration: none !important;
    }
    .btn-profile-student:hover {
        background: #334155 !important;
        color: #ffffff !important;
    }
    :root:not(.dark) .btn-profile-student {
        background: #f8fafc !important;
        color: #475569 !important;
        border: 1px solid #cbd5e1 !important;
    }
    :root:not(.dark) .btn-profile-student:hover {
        background: #e2e8f0 !important;
        color: #0f172a !important;
    }
</style>

<div style="width: 100% !important; box-sizing: border-box !important;">
    @if(!$isBeregu && $primaryStudent)
        {{-- TAMPILAN SISWA PERORANGAN / TUNGGAL --}}
        @php
            $s = $primaryStudent;
            $avatar = $s->photo 
                ? asset('storage/' . $s->photo) 
                : 'https://ui-avatars.com/api/?name=' . urlencode($s->name) . '&background=1d4ed8&color=ffffff&bold=true';
            $cleanPhone = $s->phone ? preg_replace('/[^0-9]/', '', $s->phone) : null;
            if ($cleanPhone && str_starts_with($cleanPhone, '0')) {
                $cleanPhone = '62' . substr($cleanPhone, 1);
            }
            $cleanParentPhone = $s->parent_phone ? preg_replace('/[^0-9]/', '', $s->parent_phone) : null;
            if ($cleanParentPhone && str_starts_with($cleanParentPhone, '0')) {
                $cleanParentPhone = '62' . substr($cleanParentPhone, 1);
            }
            $genderLabel = match($s->gender) {
                'L', 'male' => 'Laki-laki',
                'P', 'female' => 'Perempuan',
                default => '—',
            };
        @endphp

        <div class="sims-profile-card">
            <!-- Bagian Kiri: Foto Siswa + Identitas Lengkap -->
            <div class="sims-profile-left">
                <!-- Foto Siswa (Bisa Di-Zoom dengan Modal Previewer) -->
                <button type="button" 
                        @click="$dispatch('open-doc-preview', { url: '{{ $avatar }}', title: 'Foto Profil: {{ addslashes($s->name) }}', type: 'image' })"
                        title="Klik untuk membuka & zoom foto siswa"
                        class="sims-profile-avatar-btn">
                    <img src="{{ $avatar }}" 
                         alt="{{ $s->name }}" 
                         class="sims-profile-avatar-img">
                    <span class="sims-profile-avatar-hint">🔍 Zoom</span>
                </button>

                <!-- Identitas Siswa -->
                <div class="sims-profile-info">
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                        <a href="{{ \App\Filament\Resources\UserResource::getUrl('view', ['record' => $s->id]) }}" 
                           target="_blank" 
                           style="font-size: 1.05rem; font-weight: 800; color: #38bdf8; text-decoration: none;"
                           title="Buka profil lengkap siswa di tab baru">
                            {{ $s->name }} ↗
                        </a>
                        <span style="display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 9999px; font-size: 0.7rem; font-weight: 800; background: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.4);">
                            👤 Siswa Utama (Perorangan)
                        </span>
                    </div>

                    <!-- Baris Chips Metadata -->
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 6px;">
                        <span class="sims-pill-badge">
                            <span style="color: #94a3b8;">Kelas:</span>
                            <strong style="color: #60a5fa;">{{ $s->schoolClass?->name ?? '—' }}</strong>
                        </span>
                        <span class="sims-pill-badge">
                            <span style="color: #94a3b8;">NISN:</span>
                            <strong style="font-family: monospace;">{{ $s->nisn ?? '—' }}</strong>
                        </span>
                        @if($s->nis)
                            <span class="sims-pill-badge">
                                <span style="color: #94a3b8;">NIS:</span>
                                <strong style="font-family: monospace;">{{ $s->nis }}</strong>
                            </span>
                        @endif
                        <span class="sims-pill-badge">
                            <span style="color: #94a3b8;">Gender:</span>
                            <strong>{{ $genderLabel }}</strong>
                        </span>
                    </div>

                    <!-- Baris Kontak Siswa & Orang Tua -->
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px; font-size: 0.75rem; margin-top: 2px;">
                        @if($cleanPhone)
                            <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 7px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #6ee7b7; font-weight: 700; text-decoration: none;">
                                💬 WhatsApp Siswa: {{ $s->phone }} ↗
                            </a>
                        @else
                            <span style="color: #64748b; font-style: italic;">No. WA siswa belum diisi</span>
                        @endif

                        @if($cleanParentPhone)
                            <a href="https://wa.me/{{ $cleanParentPhone }}" target="_blank" style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 7px; background: rgba(168, 85, 247, 0.15); border: 1px solid rgba(168, 85, 247, 0.4); color: #d8b4fe; font-weight: 600; text-decoration: none;">
                                👨‍👩‍👧 Ortu ({{ $s->parent_name ?: 'Wali' }}): {{ $s->parent_phone }} ↗
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Bagian Kanan: Tombol Aksi Langsung Admin -->
            <div class="sims-profile-actions">
                <button type="button" 
                        wire:click="mountAction('edit_achievement')" 
                        class="btn-edit-student"
                        title="Buka form edit untuk mengubah data prestasi, ganti siswa, atau perbarui berkas">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Ganti Siswa / Edit Data
                </button>

                <a href="{{ \App\Filament\Resources\UserResource::getUrl('view', ['record' => $s->id]) }}" 
                   target="_blank" 
                   class="btn-profile-student"
                   title="Lihat profil lengkap siswa di tab baru">
                    <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Profil Siswa ↗
                </a>

                @if($s->photo)
                    <form action="{{ route('admin.students.delete-photo', $s->id) }}" method="POST" onsubmit="return confirm('Hapus foto profil {{ addslashes($s->name) }}?')" style="margin: 0;">
                        @csrf
                        <button type="submit" title="Hapus foto profil siswa" style="cursor: pointer; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 8px 12px; border-radius: 9px; font-size: 0.75rem; font-weight: 600;">
                            🗑️ Hapus Foto
                        </button>
                    </form>
                @endif
            </div>
        </div>

    @else
        {{-- TAMPILAN PRESTASI BEREGU: HEADER TIM + GRID ANGGOTA SEIMBANG --}}
        <div style="display: flex; flex-direction: column; gap: 12px; width: 100% !important;">
            <!-- Header Bar Tim -->
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; padding: 14px 18px; border-radius: 14px; background: rgba(168, 85, 247, 0.12); border: 1px solid rgba(168, 85, 247, 0.35); gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.4rem;">👥</span>
                    <div>
                        <h4 style="font-size: 0.95rem; font-weight: 800; color: #d8b4fe; margin: 0;">
                            Prestasi Kategori Beregu / Kelompok ({{ $students->count() }} Anggota Terdaftar)
                        </h4>
                        @if($record && $record->team_code)
                            <p style="font-size: 0.75rem; color: #c084fc; margin: 2px 0 0 0;">
                                Kode Tim: <strong style="font-family: monospace;">{{ $record->team_code }}</strong>
                            </p>
                        @endif
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" 
                            wire:click="mountAction('edit_achievement')" 
                            class="btn-edit-student"
                            style="padding: 7px 14px; font-size: 0.75rem;">
                        ✏️ Edit Data Tim & Prestasi
                    </button>
                </div>
            </div>

            <!-- Grid Anggota Tim -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 14px; width: 100%;">
                @foreach($students as $s)
                    @php
                        $avatar = $s->photo 
                            ? asset('storage/' . $s->photo) 
                            : 'https://ui-avatars.com/api/?name=' . urlencode($s->name) . '&background=7c3aed&color=ffffff&bold=true';
                        $cleanPhone = $s->phone ? preg_replace('/[^0-9]/', '', $s->phone) : null;
                        if ($cleanPhone && str_starts_with($cleanPhone, '0')) {
                            $cleanPhone = '62' . substr($cleanPhone, 1);
                        }
                        $isSubmitter = ($record && $record->student_id === $s->id);
                    @endphp

                    <div style="background: #0f172a; border: 1px solid #334155; border-radius: 14px; padding: 14px; display: flex; flex-direction: column; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <button type="button" 
                                    @click="$dispatch('open-doc-preview', { url: '{{ $avatar }}', title: 'Foto Profil: {{ addslashes($s->name) }}', type: 'image' })"
                                    title="Klik untuk zoom foto"
                                    style="position: relative; border-radius: 12px; overflow: hidden; border: 1.5px solid #475569; padding: 0; background: transparent; cursor: pointer; flex-shrink: 0;">
                                <img src="{{ $avatar }}" 
                                     alt="{{ $s->name }}" 
                                     style="width: 58px; height: 58px; object-fit: cover; display: block;">
                            </button>

                            <div style="min-width: 0; flex: 1;">
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px;">
                                    <a href="{{ \App\Filament\Resources\UserResource::getUrl('view', ['record' => $s->id]) }}" 
                                       target="_blank" 
                                       style="font-size: 0.85rem; font-weight: 700; color: #38bdf8; text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $s->name }} ↗
                                    </a>
                                    @if($isSubmitter)
                                        <span style="font-size: 0.65rem; font-weight: 800; padding: 2px 6px; border-radius: 4px; background: #7c3aed; color: #fff; flex-shrink: 0;">
                                            Pendaftar
                                        </span>
                                    @else
                                        <span style="font-size: 0.65rem; font-weight: 600; padding: 2px 6px; border-radius: 4px; background: #334155; color: #cbd5e1; flex-shrink: 0;">
                                            Anggota
                                        </span>
                                    @endif
                                </div>

                                <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 2px;">
                                    Kelas: <strong style="color: #60a5fa;">{{ $s->schoolClass?->name ?? '—' }}</strong>
                                    @if($s->nisn) · NISN: <span style="font-family: monospace;">{{ $s->nisn }}</span> @endif
                                </div>

                                @if($cleanPhone)
                                    <div style="margin-top: 3px;">
                                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" style="font-size: 0.72rem; font-weight: 700; color: #34d399; text-decoration: none;">
                                            💬 WA: {{ $s->phone }} ↗
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; pt: 6px; border-top: 1px solid #1e293b;">
                            <a href="{{ \App\Filament\Resources\UserResource::getUrl('view', ['record' => $s->id]) }}" 
                               target="_blank" 
                               style="font-size: 0.72rem; font-weight: 600; color: #38bdf8; text-decoration: none;">
                                Buka Profil Siswa ↗
                            </a>

                            @if($s->photo)
                                <form action="{{ route('admin.students.delete-photo', $s->id) }}" method="POST" onsubmit="return confirm('Hapus foto profil {{ addslashes($s->name) }}?')" style="margin: 0;">
                                    @csrf
                                    <button type="submit" style="cursor: pointer; background: transparent; border: none; font-size: 0.68rem; font-weight: 600; color: #f87171;">
                                        🗑️ Hapus Foto
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
