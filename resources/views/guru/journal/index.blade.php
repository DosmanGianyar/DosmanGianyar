@extends('layouts.guru')
@section('title', 'Jurnal Mengajar')
@section('page-title', 'Jurnal Mengajar')

@section('content')
@php
    $months = ['', 'Januari','Februari','Maret','April','Mei','Juni',
               'Juli','Agustus','September','Oktober','November','Desember'];
@endphp
<div class="space-y-4 pb-12">

    {{-- ─── 1. Filter & Actions Control Bar ────────────────────────────── --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-4 sm:p-5">
        <form method="GET" action="{{ route('guru.journal.index') }}" class="space-y-4">
            {{-- Filter Selectors --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Bulan</label>
                    <select name="month" onchange="this.form.submit()"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50/50 hover:bg-white transition-all cursor-pointer">
                        @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $months[$m] }}</option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Tahun</label>
                    <select name="year" onchange="this.form.submit()"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50/50 hover:bg-white transition-all cursor-pointer">
                        @for($y = now()->year; $y >= now()->year - 3; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="col-span-2 sm:col-span-1 md:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 mb-1">Kelas</label>
                    <select name="class_id" onchange="this.form.submit()"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50/50 hover:bg-white transition-all cursor-pointer">
                        <option value="">— Semua Kelas —</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Action Buttons Bar --}}
            <div class="flex flex-wrap items-center justify-between gap-2.5 pt-3 border-t border-slate-100">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('guru.journal.print', ['month' => $month, 'year' => $year, 'class_id' => $classId]) }}"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200/80 text-slate-700 text-xs font-bold rounded-xl transition-all shadow-2xs"
                        title="Cetak Jurnal (Perbulan / Rentang Tanggal - A4 Landscape)">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <span>Cetak Jurnal (A4)</span>
                    </a>

                    <a href="{{ route('guru.journal.print-weekly-attendance', ['month' => $month, 'year' => $year, 'class_id' => $classId]) }}"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold rounded-xl transition-all shadow-2xs"
                        title="Cetak Rekap Absensi Siswa Bulanan (Tgl 1 s/d Tanggal Terakhir)">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Cetak Absen Bulanan</span>
                    </a>
                </div>

                <a href="{{ route('guru.journal.create') }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-xl transition-all shadow-xs shadow-blue-500/20 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>+ Buat Jurnal Baru</span>
                </a>
            </div>
        </form>
    </div>

    {{-- ─── 2. Summary Badge ────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between px-1">
        <span class="text-xs font-semibold text-slate-500">
            Ditemukan <strong class="text-slate-900 font-black">{{ $total }}</strong> jurnal mengajar pada bulan <strong class="text-blue-600">{{ $months[$month] }} {{ $year }}</strong>
        </span>
    </div>

    {{-- ─── 3. Journal List ─────────────────────────────────────────────── --}}
    <div class="space-y-3.5">
        @forelse($journals as $journal)
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden transition-all hover:border-blue-200">

            {{-- Header Pertemuan --}}
            <div class="flex items-center justify-between px-4 sm:px-5 py-3.5 bg-slate-50/80 border-b border-slate-100 flex-wrap gap-2.5">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shrink-0 shadow-2xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm sm:text-base font-black text-slate-900 truncate">Kelas {{ $journal->schoolClass?->name ?? '—' }}</p>
                        <div class="flex items-center gap-2 flex-wrap mt-0.5 text-xs text-slate-500">
                            <span class="font-medium text-slate-700">
                                📅 {{ $journal->date?->isoFormat('dddd, D MMMM Y') }}
                            </span>
                            @if($journal->period)
                            <span class="inline-flex items-center px-2 py-0.2 rounded-md bg-blue-600 text-white text-[10px] font-black shadow-2xs">
                                Jam ke-{{ $journal->period }}{{ $journal->period_end && $journal->period_end > $journal->period ? '–'.$journal->period_end : '' }}
                            </span>
                            @endif
                            @if($journal->subject)
                            <span class="text-slate-400">·</span>
                            <span class="font-semibold text-blue-700 truncate">📚 {{ $journal->subject->name }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Action Controls: Salin WA, Absen, Edit, Hapus --}}
                <div class="flex items-center gap-1.5 shrink-0 ml-auto sm:ml-0">
                    @php
                        $totalStudents = $journal->schoolClass?->students?->count() ?? 0;
                        $absentCount   = $journal->absences->count();
                        $presentCount  = max(0, $totalStudents - $absentCount);

                        $absencesList = $journal->absences->map(function($abs) {
                            $statusLabel = match(strtolower($abs->status)) {
                                'sakit'               => 'Sakit',
                                'izin'                => 'Izin',
                                'dispensasi', 'dispen'=> 'Dispensasi',
                                'alpa', 'tidak_hadir' => 'Alpa',
                                default               => ucfirst($abs->status),
                            };
                            return [
                                'student_name' => $abs->student?->name ?? 'Siswa',
                                'status_label' => $statusLabel,
                                'note'         => $abs->note ?? '',
                            ];
                        })->values();

                        $waPayload = [
                            'teacher_name'   => $journal->teacher?->name ?? auth()->user()->name,
                            'date_formatted' => $journal->date?->isoFormat('dddd, D MMMM Y') ?? '-',
                            'class_name'     => $journal->schoolClass?->name ?? '-',
                            'period'         => $journal->period ? ($journal->period . ($journal->period_end && $journal->period_end > $journal->period ? '–'.$journal->period_end : '')) : null,
                            'subject_name'   => $journal->subject?->name ?? null,
                            'total_students' => $totalStudents,
                            'present_count'  => $presentCount,
                            'absent_count'   => $absentCount,
                            'absences'       => $absencesList,
                            'notes'          => $journal->notes ?? '',
                        ];
                    @endphp

                    {{-- Tombol Salin WA --}}
                    <button type="button"
                        onclick="copyWaReport(this)"
                        data-journal="{{ json_encode($waPayload) }}"
                        title="Salin Laporan WA Kehadiran Siswa"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-2xs transition-all cursor-pointer">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l.199.317-1.157 4.226 4.323-1.134.378.258z"/>
                        </svg>
                        <span>Salin WA</span>
                    </button>

                    @if($journal->absences->count() > 0)
                    <span class="px-2 py-0.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs">
                        {{ $journal->absences->count() }} absen
                    </span>
                    @endif

                    {{-- Edit --}}
                    <a href="{{ route('guru.journal.edit', $journal) }}" title="Edit Jurnal & Absensi"
                        class="w-8 h-8 flex items-center justify-center rounded-xl bg-white border border-slate-200 hover:bg-blue-50 text-slate-500 hover:text-blue-600 hover:border-blue-300 transition-all shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>

                    {{-- Hapus --}}
                    <form method="POST" action="{{ route('guru.journal.destroy', $journal) }}"
                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus jurnal ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" title="Hapus Jurnal"
                            class="w-8 h-8 flex items-center justify-center rounded-xl bg-white border border-slate-200 hover:bg-rose-50 text-slate-400 hover:text-rose-600 hover:border-rose-300 transition-all shadow-2xs cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Body Pertemuan --}}
            <div class="p-4 sm:p-5 space-y-3">
                {{-- Tujuan Pembelajaran (Callout Box Halus) --}}
                @if($journal->tp)
                <div class="bg-indigo-50/70 border border-indigo-100 rounded-2xl p-3 sm:p-3.5">
                    <p class="text-[10px] font-extrabold text-indigo-900 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                        <span>🎯</span>
                        <span>Tujuan Pembelajaran</span>
                    </p>
                    <p class="text-xs sm:text-sm text-slate-800 leading-relaxed">
                        @if($journal->tp->code)
                        <span class="inline-block px-1.5 py-0.5 rounded-md text-[11px] font-black bg-indigo-100 text-indigo-800 border border-indigo-200 mr-1 shadow-2xs">{{ $journal->tp->code }}</span>
                        @endif
                        {{ $journal->tp->description }}
                    </p>
                </div>
                @endif

                {{-- Materi & Aktivitas --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="bg-slate-50/80 rounded-2xl p-3 border border-slate-200/70">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Materi Pokok</p>
                        <p class="text-xs sm:text-sm text-slate-800 font-medium">{{ $journal->material }}</p>
                    </div>
                    <div class="bg-slate-50/80 rounded-2xl p-3 border border-slate-200/70">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Aktivitas Pembelajaran</p>
                        <p class="text-xs sm:text-sm text-slate-800 font-medium">{{ $journal->activity }}</p>
                    </div>
                </div>

                @if($journal->notes)
                <div class="bg-amber-50/50 rounded-2xl p-3 border border-amber-200/60">
                    <p class="text-[10px] font-bold text-amber-800 uppercase tracking-wider mb-0.5">Catatan Tambahan</p>
                    <p class="text-xs text-slate-700 italic">{{ $journal->notes }}</p>
                </div>
                @endif

                {{-- Siswa Tidak Hadir --}}
                @if($journal->absences->count() > 0)
                <div class="pt-2 border-t border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Siswa Tidak Hadir ({{ $journal->absences->count() }})</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($journal->absences as $abs)
                        @php
                            $statusConfig = [
                                'tidak_hadir' => ['label' => 'Alpa', 'bg' => 'bg-rose-50 text-rose-700 border border-rose-200'],
                                'alpa'        => ['label' => 'Alpa', 'bg' => 'bg-rose-50 text-rose-700 border border-rose-200'],
                                'izin'        => ['label' => 'Izin', 'bg' => 'bg-sky-50 text-sky-700 border border-sky-200'],
                                'sakit'       => ['label' => 'Sakit', 'bg' => 'bg-purple-50 text-purple-700 border border-purple-200'],
                                'dispensasi'  => ['label' => 'Dispen', 'bg' => 'bg-teal-50 text-teal-700 border border-teal-200'],
                            ];
                            $cfg = $statusConfig[$abs->status] ?? ['label' => ucfirst($abs->status), 'bg' => 'bg-slate-50 text-slate-700 border border-slate-200'];
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs {{ $cfg['bg'] }} font-semibold shadow-2xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            <span>{{ $abs->student?->name ?? '—' }}</span>
                            <span class="text-[10px] font-black opacity-75">({{ $cfg['label'] }})</span>
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-10 text-center">
            <div class="w-16 h-16 rounded-3xl bg-blue-50 text-blue-600 flex items-center justify-center text-3xl mx-auto mb-3 shadow-2xs">
                📖
            </div>
            <h3 class="text-base font-bold text-slate-800">Belum Ada Jurnal Mengajar</h3>
            <p class="text-slate-400 text-xs sm:text-sm mt-1 max-w-sm mx-auto">
                Belum ada jurnal mengajar yang tercatat pada bulan {{ $months[$month] }} {{ $year }}.
            </p>
            <a href="{{ route('guru.journal.create') }}"
                class="inline-flex items-center gap-1.5 mt-4 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-2xl transition-all shadow-xs shadow-blue-500/20 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>+ Buat Jurnal Pertama</span>
            </a>
        </div>
        @endforelse
    </div>

</div>

<script>
function copyWaReport(btn) {
    let data;
    try {
        data = JSON.parse(btn.dataset.journal);
    } catch(e) {
        alert('Gagal membaca data jurnal.');
        return;
    }

    let text = `📚 *LAPORAN KEHADIRAN SISWA*\n`;
    text += `🏫 *SMA Negeri 1 Gianyar*\n\n`;
    text += `👤 *Guru Pengajar:* ${data.teacher_name}\n`;
    text += `📅 *Hari/Tgl:* ${data.date_formatted}\n`;
    text += `🏫 *Kelas:* ${data.class_name}\n`;
    if (data.period) {
        text += `⏰ *Jam Ke:* ${data.period}\n`;
    }
    if (data.subject_name) {
        text += `📖 *Mata Pelajaran:* ${data.subject_name}\n`;
    }

    text += `\n📊 *Ringkasan Kehadiran:*\n`;
    if (data.total_students > 0) {
        text += `• Total Siswa: ${data.total_students} Siswa\n`;
        text += `• Hadir: ${data.present_count} Siswa\n`;
        text += `• Tidak Hadir: ${data.absent_count} Siswa\n`;
    } else {
        text += `• Tidak Hadir: ${data.absent_count} Siswa\n`;
    }

    text += `\n❌ *Daftar Siswa Tidak Hadir:*\n`;
    if (data.absences && data.absences.length > 0) {
        data.absences.forEach((abs, idx) => {
            let noteStr = (abs.note && abs.note.trim() !== '') ? ` - ${abs.note.trim()}` : '';
            text += `${idx + 1}. ${abs.student_name} (${abs.status_label}${noteStr})\n`;
        });
    } else if (data.absent_count > 0) {
        text += `• ${data.absent_count} Siswa Tidak Hadir\n`;
    } else {
        text += `✅ Hadir Lengkap (NIHIL)\n`;
    }

    if (data.notes && data.notes.trim() !== '') {
        text += `\n📝 *Catatan Khusus:*\n${data.notes.trim()}\n`;
    }

    text += `\n--\n_Dikirim via SIMAK DOSMAN_`;

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Laporan WA berhasil disalin ke clipboard!');
        }).catch(err => {
            fallbackCopyText(text);
        });
    } else {
        fallbackCopyText(text);
    }
}

function fallbackCopyText(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
        document.execCommand('copy');
        showToast('Laporan WA berhasil disalin!');
    } catch (err) {
        alert('Gagal menyalin. Silakan salin secara manual.');
    }
    document.body.removeChild(textArea);
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-5 right-5 z-50 bg-slate-900 text-white px-4 py-3 rounded-2xl shadow-xl flex items-center gap-2 text-xs sm:text-sm font-semibold transition-all duration-300';
    toast.innerHTML = `<svg class="w-4.5 h-4.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> <span>${message}</span>`;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.remove();
    }, 3500);
}
</script>
@endsection
