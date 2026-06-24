@extends('layouts.siswa')
@section('title', 'Izin Keluar')
@section('page-title', 'Izin Keluar Kelas')

@section('content')
<div class="max-w-sm mx-auto space-y-4">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl p-3">
        <p class="text-sm text-green-700">{{ session('success') }}</p>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-3">
        <ul class="text-sm text-red-700 space-y-1">
            @foreach($errors->all() as $error)
                <li>• {{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($active)
    {{-- Ada izin keluar aktif → tampilkan timer --}}
    <div class="bg-orange-50 border border-orange-200 rounded-2xl p-5 text-center">
        <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-sm text-orange-600 font-medium mb-1">Kamu sedang di luar kelas</p>
        <p class="text-xs text-orange-500 mb-4">
            Keluar: {{ $active->out_time->format('H:i') }}
            · Alasan: {{ $active->reason_label }}
        </p>

        {{-- Timer --}}
        <div class="bg-white rounded-2xl p-4 mb-4 shadow-sm">
            <p class="text-4xl font-bold text-gray-800 tabular-nums" id="timer">--:--</p>
            <p class="text-xs text-gray-500 mt-1">Durasi keluar</p>
        </div>

        <form action="{{ route('siswa.exit-pass.checkin') }}" method="POST">
            @csrf
            @method('PATCH')
            <button type="submit"
                class="w-full py-3 bg-green-600 text-white rounded-xl text-sm font-semibold hover:bg-green-700 transition-colors">
                Saya Sudah Kembali ke Kelas
            </button>
        </form>
    </div>

    <script>
    const outTime = new Date('{{ $active->out_time->toIso8601String() }}');

    function updateTimer() {
        const now = new Date();
        const diff = Math.floor((now - outTime) / 1000);
        const m = Math.floor(diff / 60).toString().padStart(2, '0');
        const s = (diff % 60).toString().padStart(2, '0');
        document.getElementById('timer').textContent = m + ':' + s;
    }
    updateTimer();
    setInterval(updateTimer, 1000);
    </script>

    @else
    {{-- Form izin keluar baru --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <p class="text-sm text-gray-500 mb-4">Izin ini akan dicatat dan dimonitor guru.</p>

        <form action="{{ route('siswa.exit-pass.store') }}" method="POST" class="space-y-4">
            @csrf

            {{-- Pilih alasan --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Keperluan</label>
                <div class="space-y-2">
                    @foreach(['toilet' => ['label' => 'Toilet / Kamar Mandi', 'icon' => 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-1m6 1l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-1m0-1v-1'], 'uks' => ['label' => 'UKS / Kesehatan', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'], 'other' => ['label' => 'Lainnya', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z']] as $val => $opt)
                    <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all
                        {{ old('reason') === $val ? 'border-blue-500 bg-blue-50' : 'border-gray-100 hover:border-gray-200' }}">
                        <input type="radio" name="reason" value="{{ $val }}" class="sr-only"
                            {{ old('reason') === $val ? 'checked' : '' }}
                            onchange="this.closest('.space-y-2').querySelectorAll('label').forEach(l => {
                                l.classList.remove('border-blue-500','bg-blue-50');
                                l.classList.add('border-gray-100');
                            }); this.closest('label').classList.add('border-blue-500','bg-blue-50'); this.closest('label').classList.remove('border-gray-100');
                            document.getElementById('detail-wrap').style.display = '{{ $val }}' === 'other' ? 'block' : 'none';">
                        <div class="w-9 h-9 bg-blue-50 rounded-full flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $opt['icon'] }}"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700">{{ $opt['label'] }}</span>
                    </label>
                    @endforeach
                </div>
                @error('reason')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Detail (hanya muncul jika other) --}}
            <div id="detail-wrap" style="display: {{ old('reason') === 'other' ? 'block' : 'none' }}">
                <label for="reason_detail" class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <input type="text" id="reason_detail" name="reason_detail"
                    value="{{ old('reason_detail') }}"
                    placeholder="Jelaskan keperluan kamu..."
                    maxlength="100"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit"
                class="w-full py-3 bg-orange-500 text-white rounded-xl text-sm font-semibold hover:bg-orange-600 transition-colors">
                Catat Izin Keluar
            </button>
        </form>
    </div>

    <p class="text-center text-xs text-gray-400">
        Izin keluar akan dicatat dengan waktu sekarang. Segera kembali ke kelas setelah selesai.
    </p>
    @endif

</div>
@endsection
