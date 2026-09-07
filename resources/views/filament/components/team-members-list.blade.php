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
    .sims-student-card {
        background: #0f172a;
        border: 1px solid #334155;
        border-radius: 16px;
        padding: 18px 20px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        transition: all 0.2s ease;
    }
    :root:not(.dark) .sims-student-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .sims-student-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.75rem;
        background: rgba(30, 41, 59, 0.7);
        border: 1px solid rgba(71, 85, 105, 0.5);
        color: #e2e8f0;
    }
    :root:not(.dark) .sims-student-chip {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #334155;
    }

    .btn-action-edit {
        background: #2563eb;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.78rem;
        padding: 8px 16px;
        border-radius: 9px;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);
    }
    .btn-action-edit:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
    }

    .btn-action-profile {
        background: rgba(51, 65, 85, 0.6);
        color: #cbd5e1;
        font-weight: 600;
        font-size: 0.78rem;
        padding: 8px 14px;
        border-radius: 9px;
        border: 1px solid #475569;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .btn-action-profile:hover {
        background: #334155;
        color: #ffffff;
    }
    :root:not(.dark) .btn-action-profile {
        background: #f8fafc;
        color: #475569;
        border: 1px solid #cbd5e1;
    }
    :root:not(.dark) .btn-action-profile:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
</style>

<div x-data="{ openPreview: false, previewSrc: '', previewTitle: '' }" class="w-full space-y-4">
    @if(!$isBeregu && $primaryStudent)
        {{-- TAMPILAN PERORANGAN / INDIVIDU: FULL WIDTH, PROPORSI MEWAH & LENGKAP --}}
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

        <div class="sims-student-card">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                <!-- Kiri: Foto + Detail Utama Siswa -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 flex-1 min-w-0">
                    <!-- Foto Profil Ukuran Proporsional dengan Hover Zoom -->
                    <div class="relative group shrink-0">
                        <button type="button" 
                                @click="previewSrc = '{{ $avatar }}'; previewTitle = '{{ addslashes($s->name) }} (Kelas {{ $s->schoolClass?->name ?? '—' }})'; openPreview = true"
                                title="Klik untuk memperbesar foto siswa"
                                class="relative block rounded-2xl overflow-hidden cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-md">
                            <img src="{{ $avatar }}" 
                                 alt="{{ $s->name }}" 
                                 style="width: 88px !important; height: 88px !important; min-width: 88px !important; min-height: 88px !important; max-width: 88px !important; max-height: 88px !important; border-radius: 16px !important; object-fit: cover !important; display: block !important;" 
                                 class="border-2 border-blue-500/60 dark:border-blue-400/60 group-hover:scale-105 transition-transform duration-200">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-2xl">
                                <span class="text-white text-xs font-bold bg-blue-600/80 px-2 py-1 rounded-lg">🔍 Perbesar</span>
                            </div>
                        </button>
                    </div>

                    <!-- Informasi Siswa -->
                    <div class="space-y-2.5 min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ \App\Filament\Resources\UserResource::getUrl('view', ['record' => $s->id]) }}" 
                               target="_blank" 
                               class="text-base sm:text-lg font-extrabold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 hover:underline">
                                {{ $s->name }} ↗
                            </a>
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 border border-blue-300 dark:border-blue-700">
                                👤 Siswa Utama (Perorangan)
                            </span>
                        </div>

                        <!-- Grid Chip Akademik -->
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="sims-student-chip">
                                <span class="text-gray-400 dark:text-gray-400">Kelas:</span>
                                <strong class="text-blue-600 dark:text-blue-300 font-bold">{{ $s->schoolClass?->name ?? '—' }}</strong>
                            </div>
                            <div class="sims-student-chip">
                                <span class="text-gray-400 dark:text-gray-400">NISN:</span>
                                <strong class="font-mono text-gray-800 dark:text-gray-100">{{ $s->nisn ?? '—' }}</strong>
                            </div>
                            @if($s->nis)
                                <div class="sims-student-chip">
                                    <span class="text-gray-400 dark:text-gray-400">NIS:</span>
                                    <strong class="font-mono text-gray-800 dark:text-gray-100">{{ $s->nis }}</strong>
                                </div>
                            @endif
                            <div class="sims-student-chip">
                                <span class="text-gray-400 dark:text-gray-400">Gender:</span>
                                <strong class="text-gray-800 dark:text-gray-100">{{ $genderLabel }}</strong>
                            </div>
                        </div>

                        <!-- Kontak Siswa & Orang Tua -->
                        <div class="flex flex-wrap items-center gap-4 text-xs pt-1">
                            @if($cleanPhone)
                                <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="inline-flex items-center gap-1.5 font-bold text-emerald-600 dark:text-emerald-400 hover:underline bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-1 rounded-lg border border-emerald-200 dark:border-emerald-800">
                                    <span>💬</span> WhatsApp: {{ $s->phone }} ↗
                                </a>
                            @else
                                <span class="text-gray-400 dark:text-gray-500 italic">No. WhatsApp siswa belum diisi</span>
                            @endif

                            @if($cleanParentPhone)
                                <a href="https://wa.me/{{ $cleanParentPhone }}" target="_blank" class="inline-flex items-center gap-1.5 font-medium text-purple-600 dark:text-purple-400 hover:underline bg-purple-50 dark:bg-purple-950/40 px-2.5 py-1 rounded-lg border border-purple-200 dark:border-purple-800">
                                    <span>👨‍👩‍👧</span> Ortu ({{ $s->parent_name ?: 'Wali' }}): {{ $s->parent_phone }} ↗
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Kanan: Tombol Aksi Langsung Admin -->
                <div class="flex flex-wrap sm:flex-nowrap lg:flex-col items-stretch sm:items-center lg:items-end gap-2 shrink-0 w-full lg:w-auto pt-3 lg:pt-0 border-t lg:border-t-0 border-gray-200 dark:border-gray-700/60">
                    <button type="button" 
                            wire:click="mountAction('edit_achievement')" 
                            class="btn-action-edit justify-center flex-1 sm:flex-none"
                            title="Buka form edit lengkap untuk mengubah siswa utama, kategori, data ajuan, dan berkas">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        Ganti Siswa / Edit Data
                    </button>

                    <a href="{{ \App\Filament\Resources\UserResource::getUrl('view', ['record' => $s->id]) }}" 
                       target="_blank" 
                       class="btn-action-profile justify-center flex-1 sm:flex-none"
                       title="Buka profil lengkap siswa di tab baru">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Profil Siswa ↗
                    </a>

                    @if($s->photo)
                        <form action="{{ route('admin.students.delete-photo', $s->id) }}" method="POST" onsubmit="return confirm('Hapus foto profil {{ addslashes($s->name) }}?')" class="w-full sm:w-auto">
                            @csrf
                            <button type="submit" class="w-full text-center text-[11px] font-bold text-red-600 dark:text-red-400 hover:underline bg-red-50 dark:bg-red-950/40 px-3 py-1.5 rounded-lg border border-red-200 dark:border-red-800">
                                🗑️ Hapus Foto Profil
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

    @else
        {{-- TAMPILAN PRESTASI BEREGU: HEADER TIM + GRID ANGGOTA PROPORSIONAL --}}
        <div class="space-y-3">
            <!-- Header Tim Beregu -->
            <div class="flex flex-wrap items-center justify-between gap-3 p-3.5 rounded-xl bg-purple-500/10 border border-purple-500/30">
                <div class="flex items-center gap-2">
                    <span class="text-xl">👥</span>
                    <div>
                        <h4 class="text-sm font-extrabold text-purple-700 dark:text-purple-300">
                            Prestasi Kategori Beregu / Kelompok ({{ $students->count() }} Anggota Terdaftar)
                        </h4>
                        @if($record && $record->team_code)
                            <p class="text-xs text-purple-600 dark:text-purple-400">
                                Kode Tim: <span class="font-mono font-bold">{{ $record->team_code }}</span>
                            </p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" 
                            wire:click="mountAction('edit_achievement')" 
                            class="btn-action-edit text-xs py-1.5 px-3">
                        ✏️ Edit Data Tim & Prestasi
                    </button>
                </div>
            </div>

            <!-- Grid Anggota Tim -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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

                    <div class="sims-student-card p-3.5 space-y-3">
                        <div class="flex items-start gap-3">
                            <button type="button" 
                                    @click="previewSrc = '{{ $avatar }}'; previewTitle = '{{ addslashes($s->name) }} (Kelas {{ $s->schoolClass?->name ?? '—' }})'; openPreview = true"
                                    title="Klik untuk lihat foto"
                                    class="relative group shrink-0 focus:outline-none rounded-xl overflow-hidden cursor-pointer shadow-sm">
                                <img src="{{ $avatar }}" 
                                     alt="{{ $s->name }}" 
                                     style="width: 56px !important; height: 56px !important; min-width: 56px !important; min-height: 56px !important; max-width: 56px !important; max-height: 56px !important; border-radius: 12px !important; object-fit: cover !important; display: block !important;" 
                                     class="border border-gray-300 dark:border-gray-600 group-hover:scale-105 transition-transform">
                                <div class="absolute inset-0 bg-black/35 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-xl">
                                    <span class="text-white text-[10px] font-bold">🔍</span>
                                </div>
                            </button>

                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex items-center justify-between gap-1.5">
                                    <a href="{{ \App\Filament\Resources\UserResource::getUrl('view', ['record' => $s->id]) }}" 
                                       target="_blank" 
                                       class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline truncate">
                                        {{ $s->name }} ↗
                                    </a>
                                    @if($isSubmitter)
                                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-900/60 text-purple-700 dark:text-purple-300 border border-purple-300 dark:border-purple-700 shrink-0">
                                            Pendaftar
                                        </span>
                                    @else
                                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-700 shrink-0">
                                            Anggota
                                        </span>
                                    @endif
                                </div>

                                <p class="text-[11px] font-medium text-gray-600 dark:text-gray-300 truncate">
                                    Kelas: <span class="font-bold text-blue-600 dark:text-blue-300">{{ $s->schoolClass?->name ?? '—' }}</span>
                                    @if($s->nisn) · NISN: <span class="font-mono text-gray-700 dark:text-gray-300">{{ $s->nisn }}</span> @endif
                                </p>

                                @if($cleanPhone)
                                    <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline truncate">
                                        💬 WA ({{ $s->phone }})
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2 pt-2 border-t border-gray-200 dark:border-gray-700/60">
                            <a href="{{ \App\Filament\Resources\UserResource::getUrl('view', ['record' => $s->id]) }}" 
                               target="_blank" 
                               class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                Buka Profil ↗
                            </a>

                            @if($s->photo)
                                <form action="{{ route('admin.students.delete-photo', $s->id) }}" method="POST" onsubmit="return confirm('Hapus foto profil {{ addslashes($s->name) }}?')" class="shrink-0">
                                    @csrf
                                    <button type="submit" class="text-[10px] font-bold text-red-600 dark:text-red-400 hover:underline bg-red-50 dark:bg-red-950/40 px-2 py-0.5 rounded-full border border-red-200 dark:border-red-800">
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

    <!-- Modal Lightbox Pop-up Foto Profil Siswa -->
    <template x-teleport="body">
        <div x-show="openPreview" 
             x-transition.opacity 
             x-cloak 
             @keydown.escape.window="openPreview = false"
             class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/85 backdrop-blur-sm p-4">
            <div @click.away="openPreview = false" class="relative max-w-xl w-full bg-white dark:bg-gray-900 rounded-3xl p-4 shadow-2xl space-y-3 text-center border border-gray-200 dark:border-gray-800">
                <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800 px-2">
                    <h4 class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate" x-text="previewTitle"></h4>
                    <button type="button" @click="openPreview = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg font-bold px-2 py-1 focus:outline-none">
                        ✕
                    </button>
                </div>
                <div class="flex justify-center overflow-hidden rounded-2xl bg-gray-950 p-2">
                    <img :src="previewSrc" :alt="previewTitle" class="max-h-[75vh] w-auto max-w-full object-contain rounded-xl shadow-lg">
                </div>
                <div class="flex justify-end pt-1">
                    <a :href="previewSrc" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-all shadow-sm">
                        🌐 Buka Foto Tab Baru ↗
                    </a>
                </div>
            </div>
        </div>
    </template>
</div>
