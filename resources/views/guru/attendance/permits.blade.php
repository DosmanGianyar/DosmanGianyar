@extends('layouts.guru')
@section('title', 'Approval Pengajuan')
@section('page-title', 'Persetujuan Izin, Sakit & Dispensasi')

@section('content')
<div class="space-y-4">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl p-3">
        <p class="text-sm text-green-700">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Filter status --}}
    <div class="flex flex-wrap gap-2">
        @foreach(['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'all' => 'Semua'] as $val => $label)
        <a href="{{ route('guru.attendance.permits', ['status' => $val]) }}"
            class="px-4 py-2 rounded-xl text-sm font-semibold transition-colors
                {{ $status === $val
                    ? 'bg-blue-600 text-white shadow-sm'
                    : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    @forelse($permits as $permit)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <div class="mb-3">
            <div class="flex items-start justify-between gap-2 mb-1.5">
                <div class="min-w-0">
                    <p class="font-semibold text-gray-800 text-sm truncate">{{ $permit->student->name }}</p>
                    <p class="text-xs text-gray-400">{{ $permit->student->schoolClass?->name }}</p>
                </div>
                <span class="text-xs text-gray-400 shrink-0 text-right">
                    {{ $permit->start_date->isoFormat('D MMM') }}
                    @if(!$permit->start_date->isSameDay($permit->end_date))
                        —<br>{{ $permit->end_date->isoFormat('D MMM Y') }}
                    @else
                        {{ $permit->start_date->isoFormat('Y') }}
                    @endif
                </span>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $permit->typeBadgeClass() }}">
                    {{ $permit->typeLabel() }}
                </span>
                @php
                    $statusMap = ['pending' => 'bg-yellow-100 text-yellow-700', 'approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700'];
                @endphp
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusMap[$permit->status] }}">
                    {{ ucfirst($permit->status) }}
                </span>
            </div>
        </div>

        {{-- Ringkasan Akumulasi Riwayat Siswa --}}
        @if($permit->student)
        @php $stats = $permit->student->getAttendanceStatsSummary(); @endphp
        <div class="bg-indigo-50/70 border border-indigo-100 rounded-xl p-2.5 mb-3">
            <p class="text-[11px] font-bold text-indigo-900 mb-1 flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"/></svg>
                Akumulasi Riwayat Siswa Ini:
            </p>
            <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                <span class="px-2 py-0.5 bg-purple-100 text-purple-800 rounded-md font-semibold" title="Total Pengajuan Lupa Absen Siswa">
                    🟣 Lupa Absen: <strong>{{ $stats['lupa_absen_total'] }}x</strong> (ACC: {{ $stats['lupa_absen_approved'] }})
                </span>
                <span class="px-2 py-0.5 bg-violet-100 text-violet-800 rounded-md font-semibold">
                    🟣 Sakit: <strong>{{ $stats['sakit'] }}x</strong>
                </span>
                <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-md font-semibold">
                    🔵 Izin: <strong>{{ $stats['izin'] }}x</strong>
                </span>
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-md font-semibold">
                    🟢 Dispen: <strong>{{ $stats['dispensasi'] }}x</strong>
                </span>
            </div>
        </div>
        @endif

        <p class="text-sm text-gray-600 mb-3 line-clamp-2" title="{{ $permit->reason }}">{{ $permit->reason }}</p>

        @if($permit->file)
        <div class="mb-3">
            <a href="{{ asset('storage/' . $permit->file) }}" target="_blank"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition-colors">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                </svg>
                <span>📄 Buka & Lihat Surat Bukti (Lampiran)</span>
            </a>
        </div>
        @else
        <div class="mb-3">
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200">
                <span>⚠️ Tidak Ada Lampiran Surat</span>
            </span>
        </div>
        @endif

        @if($permit->approvedBy)
        <div class="mt-2.5 flex items-center gap-1.5 text-xs text-gray-600 bg-gray-50 border border-gray-100 px-3 py-1.5 rounded-xl w-fit">
            <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>{{ $permit->status === 'approved' ? 'Disetujui oleh:' : ($permit->status === 'rejected' ? 'Ditolak oleh:' : 'Diproses oleh:') }} <strong class="text-gray-800 font-semibold">{{ $permit->approvedBy->name }}</strong></span>
        </div>
        @endif

        @if($permit->status === 'pending')
        <div class="flex gap-2 mt-3">
            <form action="{{ route('guru.attendance.permits.approve', $permit) }}" method="POST" class="flex-1">
                @csrf @method('PATCH')
                <button type="submit"
                    class="w-full py-2 bg-green-600 text-white rounded-xl text-xs font-semibold hover:bg-green-700 transition-colors">
                    Setujui
                </button>
            </form>
            <button type="button"
                class="flex-1 py-2 border border-red-300 text-red-600 rounded-xl text-xs font-semibold hover:bg-red-50 transition-colors"
                onclick="toggleRejectForm('{{ $permit->id }}')">
                Tolak
            </button>
        </div>

        <div id="reject-form-{{ $permit->id }}" class="hidden mt-3">
            <form action="{{ route('guru.attendance.permits.reject', $permit) }}" method="POST" class="space-y-2">
                @csrf @method('PATCH')
                <textarea name="rejection_note" rows="2" required
                    placeholder="Alasan penolakan..."
                    class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 resize-none"></textarea>
                <button type="submit"
                    class="w-full py-2 bg-red-600 text-white rounded-xl text-xs font-semibold hover:bg-red-700 transition-colors">
                    Konfirmasi Tolak
                </button>
            </form>
        </div>
        @endif

        @if($permit->rejection_note)
        <p class="text-xs text-red-600 mt-2">Catatan: {{ $permit->rejection_note }}</p>
        @endif
    </div>
    @empty
    <div class="bg-white rounded-2xl border border-gray-100 py-12 text-center">
        <p class="text-gray-400 text-sm">Tidak ada pengajuan {{ $status !== 'all' ? $status : '' }}</p>
    </div>
    @endforelse

    {{ $permits->appends(['status' => $status])->links() }}
</div>

<script>
function toggleRejectForm(id) {
    const el = document.getElementById('reject-form-' + id);
    el.classList.toggle('hidden');
}
</script>
@endsection
