@extends('layouts.siswa')

@section('title', 'Kelola Pengumuman')
@section('page-title', 'Kelola Pengumuman')

@section('content')

<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $announcements->total() }} pengumuman</p>
    <a href="{{ route('siswa.announcements.create') }}"
        class="flex items-center gap-1.5 bg-blue-600 text-white text-xs font-semibold px-3 py-2 rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Buat Baru
    </a>
</div>

@if($announcements->isEmpty())
    <p class="text-center text-sm text-gray-400 py-12">Belum ada pengumuman dibuat.</p>
@else
<div class="space-y-2">
    @foreach($announcements as $item)
    <div class="bg-white rounded-xl border border-gray-100 p-4">
        <div class="flex items-start justify-between gap-2 mb-1">
            <p class="font-semibold text-sm text-gray-800 flex-1 leading-snug">
                @if($item->is_pinned)
                    <span class="text-yellow-500 mr-1">📌</span>
                @endif
                {{ $item->title }}
            </p>
            <div class="flex items-center gap-1 shrink-0">
                <a href="{{ route('siswa.announcements.edit', $item) }}"
                    class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-lg">Edit</a>
                <form method="POST" action="{{ route('siswa.announcements.destroy', $item) }}"
                    data-confirm="Hapus pengumuman ini?">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs bg-red-50 text-red-500 px-2 py-1 rounded-lg">Hapus</button>
                </form>
            </div>
        </div>
        <div class="flex items-center gap-3 text-xs text-gray-400">
            @php $targetLabels = ['all' => 'Semua', 'siswa' => 'Siswa', 'guru' => 'Guru']; @endphp
            <span>{{ $targetLabels[$item->target] }}</span>
            <span>·</span>
            @if($item->isPublished())
                <span class="text-green-600">Terbit {{ $item->published_at->diffForHumans() }}</span>
            @else
                <span class="text-orange-500">Terjadwal {{ $item->published_at?->isoFormat('D MMM Y, HH:mm') }}</span>
            @endif
        </div>
    </div>
    @endforeach
</div>
<div class="mt-4">{{ $announcements->links() }}</div>
@endif

@endsection
