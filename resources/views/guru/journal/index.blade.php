@extends('layouts.guru')
@section('title', 'Jurnal Mengajar')
@section('page-title', 'Jurnal Mengajar')

@section('content')
@php
    $months = ['', 'Januari','Februari','Maret','April','Mei','Juni',
               'Juli','Agustus','September','Oktober','November','Desember'];
@endphp
<div class="space-y-4">

    {{-- ─── Filter Bar --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <form method="GET" action="{{ route('guru.journal.index') }}"
            class="flex flex-wrap gap-3 items-end">

            <div class="w-28">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Bulan</label>
                <select name="month" onchange="this.form.submit()"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $months[$m] }}</option>
                    @endfor
                </select>
            </div>

            <div class="w-24">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Tahun</label>
                <select name="year" onchange="this.form.submit()"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    @for($y = now()->year; $y >= now()->year - 3; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Kelas</label>
                <select name="class_id" onchange="this.form.submit()"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">— Semua Kelas —</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <a href="{{ route('guru.journal.print', ['month' => $month, 'year' => $year, 'class_id' => $classId]) }}"
                target="_blank"
                class="flex items-center gap-1.5 px-3 py-2.5 bg-gray-100 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-200 transition-colors shrink-0"
                title="Cetak Jurnal (Perbulan / Rentang Tanggal - A4 Landscape)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak Jurnal (A4 Landscape)
            </a>
            <a href="{{ route('guru.journal.print-weekly-attendance', ['month' => $month, 'year' => $year, 'class_id' => $classId]) }}"
                target="_blank"
                class="flex items-center gap-1.5 px-3 py-2.5 bg-emerald-50 text-emerald-700 border border-emerald-200 text-sm font-semibold rounded-xl hover:bg-emerald-100 transition-colors shrink-0"
                title="Cetak Rekap Absensi Siswa Bulanan (Tgl 1 s/d Tanggal Terakhir)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Cetak Absen Bulanan (PDF)
            </a>
            <a href="{{ route('guru.journal.create') }}"
                class="flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Jurnal
            </a>
        </form>
    </div>

    {{-- ─── Summary --}}
    <div class="flex items-center gap-2 px-1">
        <span class="text-sm text-gray-500">
            <span class="font-semibold text-gray-800">{{ $total }}</span> jurnal
            di {{ $months[$month] }} {{ $year }}
        </span>
    </div>

    {{-- ─── Journal List --}}
    @forelse($journals as $journal)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 bg-blue-50 border-b border-blue-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ $journal->schoolClass?->name ?? '—' }}</p>
                    <div class="flex items-center gap-2 flex-wrap mt-0.5">
                        <span class="text-xs text-gray-500">
                            {{ $journal->date?->isoFormat('ddd, D MMM Y') }}
                        </span>
                        @if($journal->period)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-600 text-white text-[11px] font-bold shadow-xs">
                            Jam ke-{{ $journal->period }}{{ $journal->period_end && $journal->period_end > $journal->period ? '–'.$journal->period_end : '' }}
                        </span>
                        @endif
                        @if($journal->subject)
                        <span class="text-xs text-gray-500">· {{ $journal->subject->name }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                @php
                    $totalStudents = $journal->schoolClass?->students?->count() ?? 0;
                    $absentCount   = $journal->absences->count();
                    $presentCount  = max(0, $totalStudents - $absentCount);

                    $absencesList = $journal->absences->map(function($abs) {
                        $statusLabel = match($abs->status) {
                            'sakit'      => 'Sakit',
                            'izin'       => 'Izin',
                            'dispensasi' => 'Dispensasi',
                            'alpa', 'tidak_hadir' => 'Alpa',
                            default      => ucfirst($abs->status),
                        };
                        return [
                            'student_name' => $abs->student?->name ?? 'Siswa',
                            'status_label' => $statusLabel,
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
                <button type="button"
                    onclick="copyWaReport(this)"
                    data-journal="{{ json_encode($waPayload) }}"
                    title="Salin Laporan WA Kehadiran Siswa"
                    class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition-colors shrink-0">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l.199.317-1.157 4.226 4.323-1.134.378.258z"/>
                    </svg>
                    <span>Salin WA</span>
                </button>
                @if($journal->absences->count() > 0)
                <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-red-100 text-red-600">
                    {{ $journal->absences->count() }} absen
                </span>
                @endif
                <a href="{{ route('guru.journal.edit', $journal) }}" title="Edit Jurnal & Absensi"
                    class="w-8 h-8 flex items-center justify-center rounded-xl bg-white border border-gray-200 hover:bg-blue-50 text-gray-500 hover:text-blue-600 hover:border-blue-200 transition-colors shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </a>
                <form method="POST" action="{{ route('guru.journal.destroy', $journal) }}"
                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus jurnal ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" title="Hapus Jurnal"
                        class="w-8 h-8 flex items-center justify-center rounded-xl bg-white border border-gray-200 hover:bg-red-50 text-gray-400 hover:text-red-600 hover:border-red-200 transition-colors shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        {{-- Body --}}
        <div class="px-4 py-3.5 space-y-2.5">
            @if($journal->tp)
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-0.5">Tujuan Pembelajaran</p>
                <p class="text-sm text-gray-700">
                    @if($journal->tp->code)
                    <span class="inline-block px-1.5 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-700 mr-1">{{ $journal->tp->code }}</span>
                    @endif
                    {{ $journal->tp->description }}
                </p>
            </div>
            @endif
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-0.5">Materi</p>
                <p class="text-sm text-gray-700">{{ $journal->material }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-0.5">Aktivitas Pembelajaran</p>
                <p class="text-sm text-gray-700">{{ $journal->activity }}</p>
            </div>
            @if($journal->notes)
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-0.5">Catatan</p>
                <p class="text-sm text-gray-700">{{ $journal->notes }}</p>
            </div>
            @endif
            @if($journal->absences->count() > 0)
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1">Siswa Tidak Hadir</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($journal->absences as $abs)
                    @php
                        $statusConfig = [
                            'tidak_hadir' => ['label' => 'A', 'bg' => 'bg-red-100',    'text' => 'text-red-600'],
                            'alpa'        => ['label' => 'A', 'bg' => 'bg-red-100',    'text' => 'text-red-600'],
                            'izin'       => ['label' => 'I', 'bg' => 'bg-sky-100',    'text' => 'text-sky-600'],
                            'sakit'      => ['label' => 'S', 'bg' => 'bg-purple-100', 'text' => 'text-purple-600'],
                            'dispensasi' => ['label' => 'D', 'bg' => 'bg-teal-100',   'text' => 'text-teal-600'],
                        ];
                        $cfg = $statusConfig[$abs->status] ?? ['label' => '?', 'bg' => 'bg-gray-100', 'text' => 'text-gray-500'];
                    @endphp
                    <span class="flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs {{ $cfg['bg'] }} {{ $cfg['text'] }} font-medium">
                        <span class="font-bold">{{ $cfg['label'] }}</span>
                        {{ $abs->student?->name ?? '—' }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center">
        <svg class="w-10 h-10 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        <p class="text-gray-400 text-sm">Belum ada jurnal di {{ $months[$month] }} {{ $year }}.</p>
        <a href="{{ route('guru.journal.create') }}"
            class="inline-flex items-center gap-1.5 mt-3 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Jurnal Pertama
        </a>
    </div>
    @endforelse

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
            text += `${idx + 1}. ${abs.student_name} (${abs.status_label})\n`;
        });
    } else {
        text += `✅ Hadir Lengkap (NIHIL)\n`;
    }

    if (data.notes && data.notes.trim() !== '') {
        text += `\n📝 *Catatan Khusus:*\n${data.notes.trim()}\n`;
    }

    text += `\n--\n_Dikirim via SIMS SMAN 1 Gianyar_`;

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
    toast.className = 'fixed bottom-5 right-5 z-50 bg-slate-900 text-white px-4 py-3 rounded-2xl shadow-xl flex items-center gap-2 text-sm font-semibold transition-all duration-300';
    toast.innerHTML = `<svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> <span>${message}</span>`;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.remove();
    }, 3500);
}
</script>
@endsection
