@extends('layouts.guru')
@section('title', 'Laporan Kerusakan')
@section('page-title', 'Laporan Kerusakan')

@section('content')
<div class="space-y-4">

    {{-- Filter --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <select name="status" class="flex-1 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                <option value="">Semua Status</option>
                <option value="pending"     {{ request('status') === 'pending'     ? 'selected' : '' }}>Menunggu</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Ditangani</option>
                <option value="resolved"    {{ request('status') === 'resolved'    ? 'selected' : '' }}>Selesai</option>
            </select>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white text-sm rounded-xl hover:bg-blue-700 transition-colors">Filter</button>
            @if(request('status'))
            <a href="{{ route('guru.sarpras.damage') }}" class="px-5 py-2.5 bg-gray-100 text-gray-700 text-sm rounded-xl hover:bg-gray-200 transition-colors text-center">Reset</a>
            @endif
        </form>
    </div>

    {{-- Mobile Cards --}}
    <div class="md:hidden space-y-3">
        @forelse($reports as $report)
        @php
            $sla     = $report->slaLevel();
            $slaDays = $report->daysOpen();
        @endphp
        <div class="bg-white rounded-2xl shadow-sm border {{ $sla === 'critical' ? 'border-red-300' : ($sla === 'warning' ? 'border-orange-200' : 'border-gray-100') }} p-4">
            <div class="flex items-start gap-3">
                @if($report->photo)
                <img src="{{ Storage::url($report->photo) }}" class="w-14 h-14 rounded-xl object-cover shrink-0">
                @else
                <div class="w-14 h-14 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/>
                    </svg>
                </div>
                @endif
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $report->asset->name }}</p>
                        <div class="flex items-center gap-1 shrink-0">
                            @if($sla === 'critical')
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-red-100 text-red-700">
                                {{ $slaDays }}h terlambat!
                            </span>
                            @elseif($sla === 'warning')
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-orange-100 text-orange-700">
                                {{ $slaDays }}h belum ditangani
                            </span>
                            @endif
                            <span class="text-xs px-2 py-0.5 rounded-full
                                {{ $report->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : ($report->status === 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                                {{ $report->statusLabel() }}
                            </span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ $report->description }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $report->reporter->name }} · {{ $report->created_at->isoFormat('D MMM Y') }}</p>
                </div>
            </div>

            @if($report->status !== 'resolved')
            <div class="mt-3 pt-3 border-t border-gray-100 flex flex-wrap gap-2">
                @if($report->status === 'pending')
                <form method="POST" action="{{ route('guru.sarpras.damage.progress', $report) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="text-xs px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Tandai Ditangani
                    </button>
                </form>
                @endif
                <button onclick="document.getElementById('resolve-{{ $report->id }}').classList.toggle('hidden')"
                    class="text-xs px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    Selesaikan
                </button>
            </div>
            <div id="resolve-{{ $report->id }}" class="hidden mt-3">
                <form method="POST" action="{{ route('guru.sarpras.damage.resolve', $report) }}" class="space-y-2">
                    @csrf @method('PATCH')
                    <select name="new_condition" class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2">
                        <option value="">— Kondisi aset setelah penanganan —</option>
                        <option value="baik">Baik</option>
                        <option value="rusak_ringan">Rusak Ringan</option>
                        <option value="rusak_berat">Rusak Berat</option>
                    </select>
                    <textarea name="resolution_note" rows="2" placeholder="Catatan penanganan..."
                        class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 resize-none focus:outline-none focus:ring-2 focus:ring-green-300"></textarea>
                    @if(in_array($report->reporter?->role ?? '', ['siswa','pengelola']))
                    <details class="border border-orange-200 rounded-lg p-2">
                        <summary class="text-xs font-medium text-orange-600 cursor-pointer">Catat Poin Siswa (opsional)</summary>
                        <div class="mt-2 space-y-1.5">
                            <select name="conduct_category_id" class="w-full text-xs border border-gray-200 rounded px-2 py-1.5">
                                <option value="">— Pilih kategori poin —</option>
                                @foreach($conductCategories as $cat)
                                <option value="{{ $cat->id }}">[{{ $cat->point_value > 0 ? '+' : '' }}{{ $cat->point_value }}] {{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="conduct_note" placeholder="Catatan poin (opsional)..."
                                class="w-full text-xs border border-gray-200 rounded px-2 py-1.5">
                        </div>
                    </details>
                    @endif
                    <button type="submit" class="w-full text-xs py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Simpan</button>
                </form>
            </div>
            @else
            @if($report->resolution_note)
            <p class="mt-2 text-xs text-gray-500 italic">{{ $report->resolution_note }}</p>
            @endif
            @endif
        </div>
        @empty
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center text-gray-400 text-sm">
            Tidak ada laporan kerusakan
        </div>
        @endforelse
    </div>

    {{-- Desktop Table --}}
    <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Foto</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aset</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Deskripsi</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pelapor</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($reports as $report)
                @php
                    $sla     = $report->slaLevel();
                    $slaDays = $report->daysOpen();
                @endphp
                <tr class="hover:bg-gray-50 transition-colors {{ $sla === 'critical' ? 'bg-red-50/50' : ($sla === 'warning' ? 'bg-orange-50/30' : '') }}">
                    <td class="px-4 py-3">
                        @if($report->photo)
                        <img src="{{ Storage::url($report->photo) }}" class="w-12 h-12 rounded-lg object-cover">
                        @else
                        <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/>
                            </svg>
                        </div>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $report->asset->name }}</td>
                    <td class="px-4 py-3 text-gray-600 max-w-xs truncate">{{ $report->description }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $report->reporter->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $report->created_at->isoFormat('D MMM Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-col gap-1">
                            <span class="text-xs px-2 py-1 rounded-full font-medium w-fit
                                {{ $report->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : ($report->status === 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                                {{ $report->statusLabel() }}
                            </span>
                            @if($sla === 'critical')
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-red-100 text-red-700 w-fit">
                                {{ $slaDays }} hari terlambat!
                            </span>
                            @elseif($sla === 'warning')
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-orange-100 text-orange-700 w-fit">
                                {{ $slaDays }} hari belum ditangani
                            </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        @if($report->status !== 'resolved')
                        <div class="flex gap-2">
                            @if($report->status === 'pending')
                            <form method="POST" action="{{ route('guru.sarpras.damage.progress', $report) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-xs px-2 py-1 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100">Tangani</button>
                            </form>
                            @endif
                            <button onclick="document.getElementById('resolve-d-{{ $report->id }}').classList.toggle('hidden')"
                                class="text-xs px-2 py-1 bg-green-50 text-green-700 rounded-lg hover:bg-green-100">Selesai</button>
                        </div>
                        <div id="resolve-d-{{ $report->id }}" class="hidden mt-2">
                            <form method="POST" action="{{ route('guru.sarpras.damage.resolve', $report) }}" class="space-y-1.5">
                                @csrf @method('PATCH')
                                <select name="new_condition" class="w-full text-xs border border-gray-200 rounded px-2 py-1.5">
                                    <option value="">— Kondisi baru —</option>
                                    <option value="baik">Baik</option>
                                    <option value="rusak_ringan">Rusak Ringan</option>
                                    <option value="rusak_berat">Rusak Berat</option>
                                </select>
                                <input type="text" name="resolution_note" placeholder="Catatan..."
                                    class="w-full text-xs border border-gray-200 rounded px-2 py-1.5">
                                @if(in_array($report->reporter?->role ?? '', ['siswa','pengelola']))
                                <details class="border border-orange-200 rounded p-1.5">
                                    <summary class="text-xs font-medium text-orange-600 cursor-pointer">Catat Poin Siswa (opsional)</summary>
                                    <div class="mt-1.5 space-y-1">
                                        <select name="conduct_category_id" class="w-full text-xs border border-gray-200 rounded px-2 py-1">
                                            <option value="">— Kategori poin —</option>
                                            @foreach($conductCategories as $cat)
                                            <option value="{{ $cat->id }}">[{{ $cat->point_value > 0 ? '+' : '' }}{{ $cat->point_value }}] {{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="conduct_note" placeholder="Catatan poin..."
                                            class="w-full text-xs border border-gray-200 rounded px-2 py-1">
                                    </div>
                                </details>
                                @endif
                                <button type="submit" class="w-full text-xs py-1.5 bg-green-600 text-white rounded">Simpan</button>
                            </form>
                        </div>
                        @else
                        <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">Tidak ada laporan kerusakan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($reports->hasPages())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3">{{ $reports->links() }}</div>
    @endif

</div>
@endsection
