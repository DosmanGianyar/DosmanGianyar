@extends('layouts.guru')
@section('title', 'Peminjaman Aset')
@section('page-title', 'Peminjaman Aset')

@section('content')
<div class="space-y-4">

    {{-- Filter --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <select name="status" class="flex-1 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                <option value="">Semua Status</option>
                <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Menunggu</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Dipinjam</option>
                <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Dikembalikan</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
            </select>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white text-sm rounded-xl hover:bg-blue-700 transition-colors">Filter</button>
            @if(request('status'))
            <a href="{{ route('guru.sarpras.loans') }}" class="px-5 py-2.5 bg-gray-100 text-gray-700 text-sm rounded-xl hover:bg-gray-200 transition-colors text-center">Reset</a>
            @endif
        </form>
    </div>

    {{-- Mobile Cards --}}
    <div class="md:hidden space-y-3">
        @forelse($loans as $loan)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-start justify-between gap-2 mb-3">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $loan->asset->name }}</p>
                    <p class="text-xs text-gray-500">{{ $loan->user->name }}</p>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full shrink-0 font-medium
                    @php
                        $colors = ['pending'=>'bg-yellow-100 text-yellow-700','approved'=>'bg-blue-100 text-blue-700','active'=>'bg-green-100 text-green-700','returned'=>'bg-gray-100 text-gray-600','rejected'=>'bg-red-100 text-red-700'];
                        echo $colors[$loan->status] ?? 'bg-gray-100 text-gray-600';
                    @endphp">
                    {{ $loan->statusLabel() }}
                </span>
            </div>
            <p class="text-xs text-gray-500 mb-1"><span class="font-medium">Keperluan:</span> {{ $loan->purpose }}</p>
            <p class="text-xs text-gray-400">{{ $loan->start_date->format('d M') }} – {{ $loan->end_date->format('d M Y') }}</p>

            @if($loan->status === 'pending')
            <div class="mt-3 pt-3 border-t border-gray-100 flex gap-2">
                <form method="POST" action="{{ route('guru.sarpras.loans.approve', $loan) }}" class="flex-1">
                    @csrf @method('PATCH')
                    <button type="submit" class="w-full text-xs py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors">Setujui</button>
                </form>
                <button onclick="document.getElementById('reject-{{ $loan->id }}').classList.toggle('hidden')"
                    class="flex-1 text-xs py-2 bg-red-50 text-red-700 rounded-xl hover:bg-red-100 transition-colors">Tolak</button>
            </div>
            <div id="reject-{{ $loan->id }}" class="hidden mt-2">
                <form method="POST" action="{{ route('guru.sarpras.loans.reject', $loan) }}" class="flex gap-2">
                    @csrf @method('PATCH')
                    <input type="text" name="rejection_note" placeholder="Alasan penolakan..."
                        class="flex-1 text-xs border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-300">
                    <button type="submit" class="text-xs px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Kirim</button>
                </form>
            </div>
            @elseif($loan->status === 'approved')
            <div class="mt-3 pt-3 border-t border-gray-100">
                <form method="POST" action="{{ route('guru.sarpras.loans.return', $loan) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="w-full text-xs py-2 bg-gray-600 text-white rounded-xl hover:bg-gray-700 transition-colors">Tandai Dikembalikan</button>
                </form>
            </div>
            @endif
            @if($loan->rejection_note)
            <p class="mt-2 text-xs text-red-500 italic">{{ $loan->rejection_note }}</p>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center text-gray-400 text-sm">
            Tidak ada data peminjaman
        </div>
        @endforelse
    </div>

    {{-- Desktop Table --}}
    <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aset</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Peminjam</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Keperluan</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Periode</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($loans as $loan)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $loan->asset->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $loan->user->name }}</td>
                    <td class="px-4 py-3 text-gray-600 max-w-xs truncate">{{ $loan->purpose }}</td>
                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                        {{ $loan->start_date->format('d M') }} – {{ $loan->end_date->format('d M Y') }}
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $colors = ['pending'=>'bg-yellow-100 text-yellow-700','approved'=>'bg-blue-100 text-blue-700','active'=>'bg-green-100 text-green-700','returned'=>'bg-gray-100 text-gray-600','rejected'=>'bg-red-100 text-red-700'];
                        @endphp
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $colors[$loan->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $loan->statusLabel() }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if($loan->status === 'pending')
                        <div class="flex gap-1.5">
                            <form method="POST" action="{{ route('guru.sarpras.loans.approve', $loan) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-xs px-2 py-1 bg-green-50 text-green-700 rounded hover:bg-green-100">Setujui</button>
                            </form>
                            <button onclick="document.getElementById('reject-d-{{ $loan->id }}').classList.toggle('hidden')"
                                class="text-xs px-2 py-1 bg-red-50 text-red-700 rounded hover:bg-red-100">Tolak</button>
                        </div>
                        <div id="reject-d-{{ $loan->id }}" class="hidden mt-1.5">
                            <form method="POST" action="{{ route('guru.sarpras.loans.reject', $loan) }}" class="flex gap-1.5">
                                @csrf @method('PATCH')
                                <input type="text" name="rejection_note" placeholder="Alasan..." class="flex-1 text-xs border border-gray-200 rounded px-2 py-1">
                                <button type="submit" class="text-xs px-2 py-1 bg-red-600 text-white rounded">OK</button>
                            </form>
                        </div>
                        @elseif($loan->status === 'approved')
                        <form method="POST" action="{{ route('guru.sarpras.loans.return', $loan) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">Dikembalikan</button>
                        </form>
                        @else
                        <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">Tidak ada data peminjaman</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($loans->hasPages())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3">{{ $loans->links() }}</div>
    @endif

</div>
@endsection
