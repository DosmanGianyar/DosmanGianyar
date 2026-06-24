@extends('layouts.siswa')
@section('title', 'Ajukan Izin Pulang Awal')
@section('page-title', 'Izin Pulang Lebih Awal')

@section('content')

<div class="max-w-sm mx-auto">

    {{-- Info --}}
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 mb-4">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-emerald-800">Ketentuan Pengajuan</p>
                <ul class="text-xs text-emerald-700 mt-1 space-y-0.5 list-disc list-inside leading-relaxed">
                    <li>Maksimal 7 hari ke depan</li>
                    <li>Satu pengajuan per tanggal</li>
                    <li>Disetujui oleh guru / guru piket / admin</li>
                    <li>Wajib tetap absen masuk pada hari tersebut</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ route('siswa.early-checkout.store') }}" method="POST"
        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-4">
        @csrf

        {{-- Tanggal --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Tanggal <span class="text-red-500">*</span>
            </label>
            <input type="date" name="date"
                value="{{ old('date', today()->toDateString()) }}"
                min="{{ today()->toDateString() }}"
                max="{{ now()->addDays(7)->toDateString() }}"
                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 @error('date') border-red-400 @enderror">
            @error('date')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Jam Rencana Pulang --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Rencana Jam Pulang <span class="text-red-500">*</span>
            </label>
            <input type="time" name="requested_time"
                value="{{ old('requested_time') }}"
                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 @error('requested_time') border-red-400 @enderror">
            @error('requested_time')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Alasan --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Alasan / Keterangan <span class="text-red-500">*</span>
            </label>
            <textarea name="reason" rows="4"
                placeholder="Contoh: Mengikuti kegiatan pramuka, ada keperluan keluarga, ikut lomba, dll."
                maxlength="500"
                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 resize-none @error('reason') border-red-400 @enderror">{{ old('reason') }}</textarea>
            @error('reason')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
            <p class="text-[11px] text-gray-400 mt-1">Maks. 500 karakter</p>
        </div>

        <button type="submit"
            class="w-full py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded-xl text-sm transition-colors">
            Kirim Pengajuan
        </button>
        <a href="{{ route('siswa.early-checkout.index') }}"
            class="block w-full py-3 text-center text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
            Batal
        </a>
    </form>

</div>

@endsection
