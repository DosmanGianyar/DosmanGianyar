@extends('layouts.siswa')
@section('title', 'Bimbingan Wali Kelas')
@section('page-title', 'Bimbingan Wali Kelas')

@section('content')
<div class="space-y-4">

    {{-- Info Wali Kelas --}}
    @if($homeroomTeacher)
    <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-4 text-white">
        <p class="text-xs text-blue-200 mb-1">Wali Kelas Anda</p>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-sm">{{ $homeroomTeacher->name }}</p>
                @if($homeroomTeacher->subject)
                    <p class="text-xs text-blue-200">{{ $homeroomTeacher->subject }}</p>
                @endif
            </div>
        </div>
    </div>
    @else
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-amber-700 text-sm">
        Kelas Anda belum memiliki wali kelas. Hubungi admin.
    </div>
    @endif

    {{-- Form Ajukan Bimbingan --}}
    @php
        $hasActive = $consultations->whereIn('status', ['pending','scheduled'])->isNotEmpty();
    @endphp

    @if($homeroomTeacher && !$hasActive)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-sm font-semibold text-gray-700 mb-4">Ajukan Bimbingan</p>
        <form method="POST" action="{{ route('siswa.homeroom-consultation.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Topik / Judul Masalah <span class="text-red-500">*</span></label>
                <input type="text" name="topic" value="{{ old('topic') }}" required
                    placeholder="Contoh: Masalah akademik, kehadiran, dll."
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                        @error('topic') border-red-400 @enderror">
                @error('topic') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Keterangan Tambahan <span class="text-gray-400 font-normal">(opsional)</span></label>
                <textarea name="student_note" rows="3" placeholder="Jelaskan lebih detail jika diperlukan…"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('student_note') }}</textarea>
            </div>
            <button type="submit"
                class="w-full bg-blue-600 text-white font-semibold py-3 rounded-xl text-sm">
                Kirim Pengajuan
            </button>
        </form>
    </div>
    @elseif($hasActive)
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-blue-700 text-sm">
        Anda masih memiliki pengajuan bimbingan aktif. Tunggu hingga selesai sebelum mengajukan yang baru.
    </div>
    @endif

    {{-- Riwayat --}}
    <div>
        <p class="text-sm font-semibold text-gray-700 mb-3">Riwayat Bimbingan</p>

        @forelse($consultations as $c)
        @php
            $colors = [
                'pending'   => ['bg-amber-100 text-amber-700',  'border-amber-100'],
                'scheduled' => ['bg-blue-100 text-blue-700',    'border-blue-100'],
                'completed' => ['bg-green-100 text-green-700',  'border-green-100'],
                'cancelled' => ['bg-gray-100 text-gray-500',    'border-gray-100'],
            ];
            [$badge, $border] = $colors[$c->status] ?? ['bg-gray-100 text-gray-500', 'border-gray-100'];
        @endphp
        <div class="bg-white rounded-2xl border {{ $border }} shadow-sm p-4 mb-3">
            <div class="flex items-start justify-between gap-2 mb-2">
                <p class="text-sm font-semibold text-gray-800">{{ $c->topic }}</p>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full shrink-0 {{ $badge }}">
                    {{ $c->statusLabel() }}
                </span>
            </div>

            <p class="text-xs text-gray-400 mb-2">{{ $c->created_at->isoFormat('D MMMM Y') }}</p>

            @if($c->student_note)
                <p class="text-xs text-gray-600 mb-2">{{ $c->student_note }}</p>
            @endif

            @if($c->isScheduled() && $c->scheduled_date)
            <div class="bg-blue-50 rounded-xl px-3 py-2 text-xs text-blue-700 mb-2">
                Dijadwalkan: <strong>{{ $c->scheduled_date->isoFormat('D MMMM Y') }}</strong>
            </div>
            @endif

            @if($c->isCompleted())
            <div class="bg-green-50 rounded-xl px-3 py-2 space-y-1 text-xs mb-2">
                @if($c->conducted_date)
                    <p class="text-gray-500">Dilaksanakan: <strong class="text-gray-700">{{ $c->conducted_date->isoFormat('D MMMM Y') }}</strong></p>
                @endif
                @if($c->teacher_note)
                    <p class="text-gray-700 font-medium">Catatan Guru:</p>
                    <p class="text-gray-600">{{ $c->teacher_note }}</p>
                @endif
                @if($c->follow_up)
                    <p class="text-gray-700 font-medium mt-1">Tindak Lanjut:</p>
                    <p class="text-gray-600">{{ $c->follow_up }}</p>
                @endif
            </div>
            @endif

            @if($c->isCancelled() && $c->cancelled_reason)
                <p class="text-xs text-gray-400">Alasan: {{ $c->cancelled_reason }}</p>
            @endif

            {{-- Tombol batalkan --}}
            @if($c->isPending())
            <form method="POST" action="{{ route('siswa.homeroom-consultation.cancel', $c) }}"
                data-confirm="Batalkan pengajuan bimbingan ini?" class="mt-2">
                @csrf @method('PATCH')
                <button type="submit" class="text-xs text-red-500 font-medium">Batalkan Pengajuan</button>
            </form>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-gray-100 p-8 text-center">
            <p class="text-gray-400 text-sm">Belum ada riwayat bimbingan.</p>
        </div>
        @endforelse
    </div>

</div>
@endsection
