@php
    $record = $getRecord();
    if (!$record) return;

    $certUrl = $record->certificate 
        ? (str_starts_with($record->certificate, 'kurasi/') ? asset($record->certificate) : asset('storage/' . $record->certificate)) 
        : null;

    $letterUrl = $record->assignment_letter 
        ? (str_starts_with($record->assignment_letter, 'kurasi/') ? asset($record->assignment_letter) : asset('storage/' . $record->assignment_letter)) 
        : null;

    $photoUrl = $record->photo 
        ? (str_starts_with($record->photo, 'kurasi/') ? asset($record->photo) : asset('storage/' . $record->photo)) 
        : null;

    $levelBadges = [
        'sekolah'       => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-300 dark:border-gray-600',
        'kabupaten'     => 'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border-sky-300 dark:border-sky-700',
        'provinsi'      => 'bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border-amber-300 dark:border-amber-700',
        'nasional'      => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-300 dark:border-emerald-700',
        'internasional' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-300 dark:border-rose-700',
    ];
    $levelClass = $levelBadges[$record->level] ?? 'bg-gray-100 text-gray-700 border-gray-300';

    $statusConfig = match ($record->status) {
        'approved' => [
            'label' => 'Disetujui / Valid (Masuk Rekap Sekolah)',
            'badge' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border-emerald-300 dark:border-emerald-700',
            'icon'  => 'heroicon-o-check-circle',
        ],
        'rejected' => [
            'label' => 'Ditolak / Tidak Valid',
            'badge' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300 border-rose-300 dark:border-rose-700',
            'icon'  => 'heroicon-o-x-circle',
        ],
        default => $record->curation_status === 'revision' ? [
            'label' => 'Perlu Revisi Berkas',
            'badge' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border-amber-300 dark:border-amber-700',
            'icon'  => 'heroicon-o-arrow-path',
        ] : [
            'label' => 'Menunggu Verifikasi Admin',
            'badge' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border-amber-200 dark:border-amber-800',
            'icon'  => 'heroicon-o-clock',
        ],
    };
@endphp

<div class="w-full space-y-4">
    <!-- Status & Catatan Verifikasi Card -->
    <div class="rounded-xl border border-gray-200 dark:border-gray-700/80 bg-gray-50/80 dark:bg-gray-800/60 p-4 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status Verifikasi:</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $statusConfig['badge'] }}">
                    {{ $statusConfig['label'] }}
                </span>
            </div>

            @if($record->verified_by)
                <div class="text-xs text-gray-600 dark:text-gray-400">
                    Diverifikasi oleh: <span class="font-bold text-gray-900 dark:text-gray-200">{{ $record->verifier?->name ?? 'Administrator' }}</span>
                    @if($record->verified_at)
                        <span class="text-gray-400 dark:text-gray-500">({{ $record->verified_at->translatedFormat('d F Y, H:i') }})</span>
                    @endif
                </div>
            @else
                <div class="text-xs text-gray-400 dark:text-gray-500 italic">
                    Belum diverifikasi
                </div>
            @endif
        </div>

        @if(!empty($record->curation_note))
            <div class="mt-3 p-3 rounded-lg border border-amber-300 dark:border-amber-700/80 bg-amber-50/90 dark:bg-amber-950/40 text-xs">
                <div class="font-bold text-amber-900 dark:text-amber-300 flex items-center gap-1.5 mb-1">
                    <span>⚠️</span> Catatan Verifikasi / Alasan Revisi / Penolakan:
                </div>
                <div class="text-amber-800 dark:text-amber-200 pl-5 font-medium whitespace-pre-line">
                    {{ $record->curation_note }}
                </div>
            </div>
        @endif
    </div>

    <!-- Tabel Rincian Tagihan & Input Siswa -->
    <div class="overflow-x-auto rounded-xl border border-gray-300 dark:border-gray-700 shadow-sm bg-white dark:bg-gray-900">
        <table class="min-w-full text-sm border-collapse">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-b border-gray-300 dark:border-gray-700">
                    <th scope="col" class="py-3 px-3 w-14 text-center font-bold uppercase text-xs tracking-wider border-r border-gray-300 dark:border-gray-700">
                        No.
                    </th>
                    <th scope="col" class="py-3 px-4 w-60 sm:w-72 font-bold uppercase text-xs tracking-wider border-r border-gray-300 dark:border-gray-700 text-left">
                        Tagihan
                    </th>
                    <th scope="col" class="py-3 px-4 font-bold uppercase text-xs tracking-wider text-left">
                        Input Siswa
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-300 dark:divide-gray-700">
                <!-- 1. Judul Prestasi -->
                <tr class="hover:bg-blue-50/40 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-3 text-center font-bold text-gray-500 dark:text-gray-400 border-r border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30">
                        1
                    </td>
                    <td class="py-3 px-4 font-semibold text-gray-800 dark:text-gray-200 border-r border-gray-300 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/20">
                        Judul Prestasi / Kejuaraan
                    </td>
                    <td class="py-3 px-4 font-bold text-amber-600 dark:text-amber-400 text-base">
                        🏆 {{ $record->title }}
                    </td>
                </tr>

                <!-- 2. Nama Ajang / Event -->
                <tr class="hover:bg-blue-50/40 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-3 text-center font-bold text-gray-500 dark:text-gray-400 border-r border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30">
                        2
                    </td>
                    <td class="py-3 px-4 font-semibold text-gray-800 dark:text-gray-200 border-r border-gray-300 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/20">
                        Nama Ajang / Event
                    </td>
                    <td class="py-3 px-4 text-gray-900 dark:text-gray-100">
                        {{ $record->event_name ?: '—' }}
                    </td>
                </tr>

                <!-- 3. Penyelenggara -->
                <tr class="hover:bg-blue-50/40 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-3 text-center font-bold text-gray-500 dark:text-gray-400 border-r border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30">
                        3
                    </td>
                    <td class="py-3 px-4 font-semibold text-gray-800 dark:text-gray-200 border-r border-gray-300 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/20">
                        Penyelenggara
                    </td>
                    <td class="py-3 px-4 text-gray-900 dark:text-gray-100">
                        {{ $record->organizer ?: '—' }}
                    </td>
                </tr>

                <!-- 4. Tingkat Kejuaraan -->
                <tr class="hover:bg-blue-50/40 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-3 text-center font-bold text-gray-500 dark:text-gray-400 border-r border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30">
                        4
                    </td>
                    <td class="py-3 px-4 font-semibold text-gray-800 dark:text-gray-200 border-r border-gray-300 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/20">
                        Tingkat Kejuaraan
                    </td>
                    <td class="py-3 px-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold border {{ $levelClass }}">
                            {{ $record->levelLabel() }}
                        </span>
                    </td>
                </tr>

                <!-- 5. Peringkat / Juara -->
                <tr class="hover:bg-blue-50/40 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-3 text-center font-bold text-gray-500 dark:text-gray-400 border-r border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30">
                        5
                    </td>
                    <td class="py-3 px-4 font-semibold text-gray-800 dark:text-gray-200 border-r border-gray-300 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/20">
                        Peringkat / Juara
                    </td>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-gray-100">
                        @if($record->rank)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-black bg-amber-100 text-amber-900 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                                🥇 {{ $record->rank }}
                            </span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                </tr>

                <!-- 6. Rumpun Bidang -->
                <tr class="hover:bg-blue-50/40 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-3 text-center font-bold text-gray-500 dark:text-gray-400 border-r border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30">
                        6
                    </td>
                    <td class="py-3 px-4 font-semibold text-gray-800 dark:text-gray-200 border-r border-gray-300 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/20">
                        Rumpun Bidang
                    </td>
                    <td class="py-3 px-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-300 dark:border-blue-700">
                            {{ $record->fieldCategoryLabel() }}
                        </span>
                    </td>
                </tr>

                <!-- 7. Jenis Partisipasi -->
                <tr class="hover:bg-blue-50/40 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-3 text-center font-bold text-gray-500 dark:text-gray-400 border-r border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30">
                        7
                    </td>
                    <td class="py-3 px-4 font-semibold text-gray-800 dark:text-gray-200 border-r border-gray-300 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/20">
                        Jenis Partisipasi
                    </td>
                    <td class="py-3 px-4">
                        @if($record->isBeregu())
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-300 dark:border-purple-700">
                                👥 Beregu ({{ $record->team_members->count() }} Siswa)
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-300 dark:border-gray-600">
                                👤 Perorangan (Individu)
                            </span>
                        @endif
                    </td>
                </tr>

                <!-- 8. Tanggal Lomba / Capaian -->
                <tr class="hover:bg-blue-50/40 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-3 text-center font-bold text-gray-500 dark:text-gray-400 border-r border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30">
                        8
                    </td>
                    <td class="py-3 px-4 font-semibold text-gray-800 dark:text-gray-200 border-r border-gray-300 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/20">
                        Tanggal Lomba / Capaian
                    </td>
                    <td class="py-3 px-4 text-gray-900 dark:text-gray-100 font-medium">
                        🗓️ {{ $record->achievement_date ? $record->achievement_date->translatedFormat('d F Y') : '—' }}
                    </td>
                </tr>

                <!-- 9. Website Resmi Ajang / Berita -->
                <tr class="hover:bg-blue-50/40 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-3 text-center font-bold text-gray-500 dark:text-gray-400 border-r border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30">
                        9
                    </td>
                    <td class="py-3 px-4 font-semibold text-gray-800 dark:text-gray-200 border-r border-gray-300 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/20">
                        Website Resmi Ajang / Berita
                    </td>
                    <td class="py-3 px-4 text-gray-900 dark:text-gray-100">
                        @if($record->event_url)
                            <a href="{{ $record->event_url }}" target="_blank" class="text-blue-600 dark:text-blue-400 font-bold hover:underline inline-flex items-center gap-1">
                                🔗 {{ $record->event_url }} ↗
                            </a>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                </tr>

                <!-- 10. Deskripsi / Catatan Tambahan Lomba -->
                <tr class="hover:bg-blue-50/40 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-3 text-center font-bold text-gray-500 dark:text-gray-400 border-r border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30">
                        10
                    </td>
                    <td class="py-3 px-4 font-semibold text-gray-800 dark:text-gray-200 border-r border-gray-300 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/20">
                        Deskripsi / Catatan Tambahan
                    </td>
                    <td class="py-3 px-4 text-gray-900 dark:text-gray-100 whitespace-pre-line">
                        {{ $record->description ?: '—' }}
                    </td>
                </tr>

                <!-- 11. Berkas Scan Piagam / Sertifikat -->
                <tr class="hover:bg-blue-50/40 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-3 text-center font-bold text-gray-500 dark:text-gray-400 border-r border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30">
                        11
                    </td>
                    <td class="py-3 px-4 font-semibold text-gray-800 dark:text-gray-200 border-r border-gray-300 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/20">
                        Scan Piagam / Sertifikat (Wajib)
                    </td>
                    <td class="py-3 px-4">
                        @if($certUrl)
                            <a href="{{ $certUrl }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 font-bold text-xs hover:bg-blue-100 dark:hover:bg-blue-900 transition-colors">
                                📄 Buka Berkas Sertifikat Siswa (PDF) ↗
                            </a>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-xs font-bold">
                                ⚠️ Belum Diupload
                            </span>
                        @endif
                    </td>
                </tr>

                <!-- 12. Surat Tugas / Rekomendasi Sekolah -->
                <tr class="hover:bg-blue-50/40 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-3 text-center font-bold text-gray-500 dark:text-gray-400 border-r border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30">
                        12
                    </td>
                    <td class="py-3 px-4 font-semibold text-gray-800 dark:text-gray-200 border-r border-gray-300 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/20">
                        Surat Tugas / Rekomendasi
                    </td>
                    <td class="py-3 px-4">
                        @if($letterUrl)
                            <a href="{{ $letterUrl }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 font-bold text-xs hover:bg-indigo-100 dark:hover:bg-indigo-900 transition-colors">
                                📑 Buka Surat Tugas Sekolah (PDF) ↗
                            </a>
                        @else
                            <span class="text-xs text-gray-400 dark:text-gray-500 italic">
                                Tidak ada surat tugas
                            </span>
                        @endif
                    </td>
                </tr>

                <!-- 13. Foto Kegiatan / Penyerahan Piagam -->
                <tr class="hover:bg-blue-50/40 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-3 text-center font-bold text-gray-500 dark:text-gray-400 border-r border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30">
                        13
                    </td>
                    <td class="py-3 px-4 font-semibold text-gray-800 dark:text-gray-200 border-r border-gray-300 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/20">
                        Foto Kegiatan / Penyerahan Piagam
                    </td>
                    <td class="py-3 px-4">
                        @if($photoUrl)
                            <div class="flex items-center gap-3">
                                <a href="{{ $photoUrl }}" target="_blank" class="block shrink-0 rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600 hover:opacity-90">
                                    <img src="{{ $photoUrl }}" alt="Foto Kegiatan" class="w-16 h-16 object-cover">
                                </a>
                                <a href="{{ $photoUrl }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 font-bold text-xs hover:bg-gray-100">
                                    📷 Buka Foto Kegiatan Ukuran Penuh ↗
                                </a>
                            </div>
                        @else
                            <span class="text-xs text-gray-400 dark:text-gray-500 italic">
                                Belum ada foto
                            </span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
