<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Guru Piket Bertugas — SIMAK DOSMAN</title>
    <link rel="icon" type="image/png" href="/img/logo_sekolah.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans antialiased text-slate-800 flex flex-col">

    <header class="bg-[#182433] text-white border-b border-slate-700">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="/img/logo_sekolah.png" alt="Logo SMAN 1 Gianyar" class="w-9 h-9 object-contain">
                <div>
                    <p class="text-sm font-extrabold tracking-tight">Meja Piket — SMAN 1 Gianyar</p>
                    <p class="text-[11px] text-amber-300 font-semibold">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($activeTeacherId)
                    <a href="{{ route('piket.dashboard') }}"
                       class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-bold text-white transition">
                        ← Kembali ke Dashboard
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="px-3 py-1.5 rounded-lg bg-rose-500/20 hover:bg-rose-500/30 border border-rose-400/30 text-xs font-bold text-rose-200 transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 py-6 grid grid-cols-1 lg:grid-cols-3 gap-6"
          x-data="{
              search: '',
              selectedId: '{{ old('teacher_id', $activeTeacherId ?? '') }}'
          }">

        {{-- Kolom Kiri (2/3): Pencarian & Pilihan Guru --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 flex flex-col">
            <div class="mb-4">
                <span class="inline-block px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-extrabold uppercase tracking-wider">
                    Identitas Petugas Piket
                </span>
                <h1 class="text-xl font-black text-slate-900 mt-1.5">Pilih Nama Guru yang Sedang Bertugas Piket</h1>
                <p class="text-xs text-slate-500 mt-1">
                    Seluruh verifikasi izin siswa, lupa presensi, dan dispensasi akan otomatis tercatat atas nama guru yang Anda pilih.
                </p>
            </div>

            @if(session('warning'))
                <div class="mb-4 px-3.5 py-2.5 rounded-xl bg-amber-50 border border-amber-200 text-xs font-semibold text-amber-800">
                    {{ session('warning') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 px-3.5 py-2.5 rounded-xl bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="mb-4">
                <input type="text"
                    x-model="search"
                    autofocus
                    placeholder="Ketik nama guru, NIP, atau mata pelajaran untuk mencari cepat..."
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <form method="POST" action="{{ route('piket.select-teacher.store') }}" class="flex-1 flex flex-col">
                @csrf
                <input type="hidden" name="teacher_id" x-model="selectedId">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-[420px] overflow-y-auto pr-1 mb-4">
                    @forelse($teachers as $t)
                        @php $searchKey = strtolower($t->name . ' ' . ($t->nip ?? '') . ' ' . ($t->subject ?? '')); @endphp
                        <button type="button"
                            x-show="!search || {{ json_encode($searchKey) }}.includes(search.toLowerCase())"
                            @click="selectedId = '{{ $t->id }}'"
                            :class="selectedId == '{{ $t->id }}'
                                ? 'border-blue-600 bg-blue-50 ring-2 ring-blue-500/30'
                                : 'border-slate-200 bg-slate-50/70 hover:bg-slate-100'"
                            class="text-left p-3 rounded-xl border transition flex items-center gap-3">
                            <div :class="selectedId == '{{ $t->id }}' ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-700'"
                                 class="w-9 h-9 rounded-lg font-extrabold text-xs flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($t->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-slate-900 truncate">{{ $t->name }}</p>
                                <p class="text-[11px] text-slate-500 truncate">
                                    {{ $t->nip ? 'NIP ' . $t->nip : 'Guru' }}{{ $t->subject ? ' • ' . $t->subject : '' }}
                                </p>
                            </div>
                            <span x-show="selectedId == '{{ $t->id }}'" class="text-blue-600 font-black text-sm shrink-0">✓</span>
                        </button>
                    @empty
                        <div class="col-span-2 text-center py-8 text-xs text-slate-400">
                            Belum ada data guru terdaftar di sistem.
                        </div>
                    @endforelse
                </div>

                <div class="mt-auto pt-3 border-t border-slate-100 flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
                    <input type="text" name="notes" placeholder="Catatan shift (opsional, mis: Piket Pagi)"
                           class="flex-1 rounded-xl border border-slate-300 px-3.5 py-2 text-xs">
                    <button type="submit"
                        :disabled="!selectedId"
                        :class="!selectedId ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                        class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-bold text-sm shadow-xs transition shrink-0">
                        Mulai Bertugas Piket →
                    </button>
                </div>
            </form>
        </div>

        {{-- Kolom Kanan (1/3): Riwayat Shift Terakhir --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex flex-col">
            <h2 class="text-sm font-extrabold text-slate-900 mb-1">Riwayat Shift Meja Piket</h2>
            <p class="text-[11px] text-slate-500 mb-4">5 pergantian petugas piket terakhir pada portal piket.</p>

            @if($activeTeacherName)
                <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                    <p class="text-[10px] font-bold uppercase text-emerald-700">Sedang Bertugas Saat Ini</p>
                    <p class="text-xs font-extrabold text-emerald-900 mt-0.5">{{ $activeTeacherName }}</p>
                </div>
            @endif

            <div class="space-y-2.5">
                @forelse($recentShifts as $shift)
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <p class="text-xs font-bold text-slate-900">{{ $shift->teacher?->name ?? '—' }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Mulai: {{ $shift->started_at?->locale('id')->translatedFormat('d M Y, H:i') }}
                        </p>
                        @if($shift->ended_at)
                            <p class="text-[10.5px] text-slate-400">
                                Selesai: {{ $shift->ended_at->locale('id')->translatedFormat('d M Y, H:i') }}
                            </p>
                        @else
                            <span class="inline-block mt-1 px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 text-[10px] font-bold">
                                Shift Aktif
                            </span>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-slate-400 italic py-4 text-center">Belum ada riwayat shift piket.</p>
                @endforelse
            </div>
        </div>
    </main>
</body>
</html>
