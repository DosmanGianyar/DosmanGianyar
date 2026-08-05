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

    @if(session('error'))
        <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl p-4 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <form id="bulkForm" method="POST" action="">
        @csrf
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Header Toolbar -->
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <h2 class="font-bold text-gray-800 text-base">Daftar Pengajuan</h2>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-blue-100 text-blue-800">
                        Total: {{ $pendingMembers->total() }} Pengajuan
                    </span>
                </div>

                <!-- Bulk Action Buttons -->
                <div class="flex items-center gap-2">
                    <button type="button" onclick="submitBulk('{{ route('admin.extracurriculars.members.bulk-approve') }}', 'Setujui semua pengajuan yang dipilih?')" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        Setujui Terpilih
                    </button>
                    <button type="button" onclick="submitBulk('{{ route('admin.extracurriculars.members.bulk-reject') }}', 'Tolak semua pengajuan yang dipilih?')" class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Tolak Terpilih
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-gray-700 font-semibold uppercase text-xs tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3.5 text-center w-10">
                                <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                            </th>
                            <th class="px-3 py-3.5 text-center w-12">No.</th>
                            <th class="px-6 py-3.5">Nama Siswa</th>
                            <th class="px-6 py-3.5">Kelas</th>
                            <th class="px-6 py-3.5">Ekstrakurikuler</th>
                            <th class="px-6 py-3.5">Jenis Pengajuan</th>
                            <th class="px-6 py-3.5 text-center">Aksi Individual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($pendingMembers as $member)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-4 py-4 text-center">
                                    <input type="checkbox" name="member_ids[]" value="{{ $member->id }}" class="member-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                                </td>
                                <td class="px-3 py-4 text-xs font-bold text-gray-400 text-center">
                                    {{ $loop->iteration + ($pendingMembers->currentPage() - 1) * $pendingMembers->perPage() }}
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
                                        <button type="button" onclick="singleAction('{{ route('admin.extracurriculars.members.approve', $member->id) }}')" title="Setujui Pengajuan" class="p-2 bg-emerald-100 hover:bg-emerald-600 text-emerald-700 hover:text-white rounded-lg transition shadow-xs cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>

                                        <button type="button" onclick="singleAction('{{ route('admin.extracurriculars.members.reject', $member->id) }}', 'Yakin ingin menolak pengajuan ini?')" title="Tolak Pengajuan" class="p-2 bg-rose-100 hover:bg-rose-600 text-rose-700 hover:text-white rounded-lg transition shadow-xs cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>

                                        <button type="button" onclick="singleDeleteAction('{{ route('admin.extracurriculars.members.cancel', $member->id) }}')" title="Batalkan / Hapus Kepesertaan" class="p-2 bg-gray-100 hover:bg-gray-700 text-gray-600 hover:text-white rounded-lg transition shadow-xs cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400">
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
    </form>
</div>

<!-- Hidden Action Forms for Individual Buttons -->
<form id="actionForm" method="POST" action="" style="display:none;">
    @csrf
    <input type="hidden" name="_method" id="actionMethod" value="POST">
</form>

<script>
function toggleSelectAll(master) {
    const checkboxes = document.querySelectorAll('.member-checkbox');
    checkboxes.forEach(cb => cb.checked = master.checked);
}

function submitBulk(url, confirmMsg) {
    const checked = document.querySelectorAll('.member-checkbox:checked');
    if (checked.length === 0) {
        alert('Silakan pilih minimal satu pengajuan terlebih dahulu.');
        return;
    }
    if (confirmMsg && !confirm(confirmMsg)) {
        return;
    }
    const form = document.getElementById('bulkForm');
    form.action = url;
    form.submit();
}

function singleAction(url, confirmMsg) {
    if (confirmMsg && !confirm(confirmMsg)) {
        return;
    }
    const form = document.getElementById('actionForm');
    form.action = url;
    document.getElementById('actionMethod').value = 'POST';
    form.submit();
}

function singleDeleteAction(url) {
    if (!confirm('Yakin ingin membatalkan/menghapus kepesertaan siswa ini?')) {
        return;
    }
    const form = document.getElementById('actionForm');
    form.action = url;
    document.getElementById('actionMethod').value = 'DELETE';
    form.submit();
}
</script>
@endsection
