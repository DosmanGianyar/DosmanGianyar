@extends('layouts.siswa')
@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi')

@section('content')

<div class="space-y-3">

    {{-- Header aksi --}}
    @if($notifications->total() > 0)
    <div class="flex justify-end">
        <form method="POST" action="{{ route('siswa.notifications.read-all') }}">
            @csrf @method('PATCH')
            <button type="submit" class="text-xs text-blue-600 font-semibold hover:underline">
                Tandai semua dibaca
            </button>
        </form>
    </div>
    @endif

    {{-- List --}}
    @forelse($notifications as $notif)
    @php
        $iconSvg = match($notif->type) {
            'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
            default   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        };
    @endphp
    <div class="bg-white rounded-2xl shadow-sm border {{ $notif->isRead() ? 'border-gray-100' : 'border-blue-200' }} px-4 py-3 flex items-start gap-3">
        <div class="w-9 h-9 rounded-full shrink-0 flex items-center justify-center {{ $notif->iconClass() }}">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $iconSvg !!}
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
                <p class="text-sm font-semibold {{ $notif->isRead() ? 'text-gray-700' : 'text-gray-900' }}">
                    {{ $notif->title }}
                </p>
                @if(!$notif->isRead())
                <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0 mt-1"></span>
                @endif
            </div>
            @if($notif->body)
            <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">{{ $notif->body }}</p>
            @endif
            <p class="text-[10px] text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-16 text-center">
        <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <p class="text-sm font-semibold text-gray-500">Belum ada notifikasi</p>
        <p class="text-xs text-gray-400 mt-1">Notifikasi akan muncul di sini</p>
    </div>
    @endforelse

    @if($notifications->hasPages())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3">
        {{ $notifications->links() }}
    </div>
    @endif

</div>
@endsection
