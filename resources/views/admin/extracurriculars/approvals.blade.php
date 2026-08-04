@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-7 h-7 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Persetujuan Pendaftaran Ekstrakurikuler
            </h1>
            <p class="text-sm text-gray-500 mt-1">Kelola persetujuan siswa yang mengajukan masuk atau keluar dari ekstrakurikuler</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <h2 class="font-bold text-gray-800 text-base">Daftar Pengajuan Masuk & Keluar Ekstra</h2>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-blue-100 text-blue-800">
                Total: {{ $pendingMembers->total() }} Pengajuan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 font-semibold uppercase text-xs tracking-wider border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5">Siswa</th>
                        <th class="px-6 py-3.5">Kelas</th>
                        <th class="px-6 py-3.5">Ekstrakurikuler</th>
                        <th class="px-6 py-3.5">Jenis Pengajuan</th>
                        <th class="px-6 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pendingMembers as $member)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                {{ $member->created_at->translatedFormat('d M Y H:i') }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                {{ $member->student?->name ?? '—' }}
                                <div class="text-xs text-gray-400 font-normal">NIS: {{ $member->student?->nis ?? '—' }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-gray-700">
                                {{ $member->student?->schoolClass?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-blue-900">
                                {{ $member->extracurricular?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($member->status === 'pending_join')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Pengajuan Masuk
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Pengajuan Keluar
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <form method="POST" action="{{ route('admin.extracurriculars.members.approve', $member->id) }}">
                                        @csrf
                                        <button type="submit" title="Setujui Pengajuan" class="p-2 bg-emerald-100 hover:bg-emerald-600 text-emerald-700 hover:text-white rounded-lg transition shadow-xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.extracurriculars.members.reject', $member->id) }}">
                                        @csrf
                                        <button type="submit" title="Tolak Pengajuan" onclick="return confirm('Yakin ingin menolak pengajuan ini?')" class="p-2 bg-rose-100 hover:bg-rose-600 text-rose-700 hover:text-white rounded-lg transition shadow-xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.extracurriculars.members.cancel', $member->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Batalkan / Hapus Kepesertaan" onclick="return confirm('Yakin ingin membatalkan/menghapus kepesertaan siswa ini?')" class="p-2 bg-gray-100 hover:bg-gray-700 text-gray-600 hover:text-white rounded-lg transition shadow-xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Tidak ada pengajuan pendaftaran atau keluar ekstra yang menanti persetujuan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pendingMembers->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $pendingMembers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
