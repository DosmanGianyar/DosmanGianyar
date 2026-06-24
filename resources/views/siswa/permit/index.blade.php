@extends('layouts.siswa')
@section('title', 'Riwayat Pengajuan')
@section('page-title', 'Izin, Sakit & Dispensasi')

@section('content')
<div class="space-y-4">

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Riwayat pengajuan kamu</p>
        <a href="{{ route('siswa.permit.create') }}"
            class="flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-xl">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajukan
        </a>
    </div>

    @forelse($permits as $permit)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">

        {{-- Header: tipe + status + tanggal --}}
        <div class="flex items-start justify-between mb-2">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $permit->typeBadgeClass() }}">
                    {{ $permit->typeLabel() }}
                </span>
                @php
                    $statusBadge = ['pending' => 'bg-yellow-100 text-yellow-700', 'approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700'];
                @endphp
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusBadge[$permit->status] }}">
                    {{ $permit->status === 'pending' ? 'Menunggu' : ($permit->status === 'approved' ? 'Disetujui' : 'Ditolak') }}
                </span>
            </div>
            <span class="text-xs text-gray-400 text-right leading-tight">
                {{ $permit->start_date->isoFormat('D MMM') }}
                @if(!$permit->start_date->isSameDay($permit->end_date))
                    —<br>{{ $permit->end_date->isoFormat('D MMM Y') }}
                @else
                    {{ $permit->start_date->isoFormat('Y') }}
                @endif
            </span>
        </div>

        {{-- Alasan --}}
        <p class="text-sm text-gray-700 leading-snug">{{ $permit->reason }}</p>

        {{-- Catatan penolakan --}}
        @if($permit->rejection_note)
            <div class="mt-2 flex items-start gap-1.5 text-xs text-red-600">
                <svg class="w-3.5 h-3.5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ $permit->rejection_note }}</span>
            </div>
        @endif

        {{-- Lampiran --}}
        @if($permit->file)
            <a href="{{ Storage::url($permit->file) }}" target="_blank"
                class="inline-flex items-center gap-1 text-xs text-blue-600 mt-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                </svg>
                Lihat lampiran
            </a>
        @endif

        {{-- Edit / Hapus (hanya pending) --}}
        @if($permit->isPending())
        <div class="flex gap-2 mt-3 pt-3 border-t border-gray-50">
            <a href="{{ route('siswa.permit.edit', $permit) }}"
                class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl border border-blue-200 text-blue-600 text-xs font-semibold hover:bg-blue-50 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
            <button type="button"
                onclick="confirmDelete('{{ $permit->id }}', '{{ $permit->typeLabel() }}', '{{ $permit->start_date->isoFormat('D MMM Y') }}')"
                class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl border border-red-200 text-red-500 text-xs font-semibold hover:bg-red-50 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus
            </button>
        </div>
        @endif

    </div>
    @empty
    <div class="bg-white rounded-2xl border border-gray-100 py-12 text-center">
        <p class="text-gray-400 text-sm">Belum ada pengajuan</p>
        <a href="{{ route('siswa.permit.create') }}"
            class="inline-block mt-3 px-5 py-2 bg-blue-600 text-white text-sm rounded-xl">
            Ajukan Sekarang
        </a>
    </div>
    @endforelse

    {{ $permits->links() }}
</div>

{{-- ─── Delete Confirmation Modal ───────────────────────────────────────── --}}
<div id="delete-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm px-4"
    onclick="cancelDelete()">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-5 space-y-4" onclick="event.stopPropagation()">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-red-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-gray-800 text-sm">Hapus Pengajuan?</p>
                <p id="delete-desc" class="text-xs text-gray-500"></p>
            </div>
        </div>
        <p class="text-xs text-gray-400">
            Pengajuan yang dihapus tidak dapat dikembalikan. Kamu bisa mengajukan ulang jika diperlukan.
        </p>
        <div class="flex gap-3">
            <button onclick="cancelDelete()"
                class="flex-1 py-3 rounded-xl border border-gray-200 text-sm font-medium text-gray-600">
                Batal
            </button>
            <form id="delete-form" method="POST" class="flex-1">
                @csrf @method('DELETE')
                <button type="submit"
                    class="w-full py-3 bg-red-600 text-white rounded-xl text-sm font-semibold">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, type, date) {
    document.getElementById('delete-desc').textContent = type + ' — ' + date;
    document.getElementById('delete-form').action = '/siswa/permits/' + id;
    const modal = document.getElementById('delete-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function cancelDelete() {
    const modal = document.getElementById('delete-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endsection
