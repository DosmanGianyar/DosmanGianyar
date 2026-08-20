<div class="flex flex-col items-center justify-center p-2 space-y-3">
    <div class="rounded-2xl overflow-hidden shadow-2xl border border-gray-200 bg-slate-950 flex items-center justify-center w-full max-h-[70vh]">
        <img src="{{ $imageUrl }}" alt="{{ $title }}" class="max-h-[65vh] w-auto object-contain">
    </div>
    @if(isset($title))
        <h3 class="text-base font-extrabold text-gray-900 text-center leading-snug">{{ $title }}</h3>
    @endif
    @if(isset($body) && filled($body))
        <div class="text-xs text-gray-600 text-center max-h-24 overflow-y-auto leading-relaxed bg-gray-50 p-2.5 rounded-xl border border-gray-100 w-full">
            {!! nl2br(e($body)) !!}
        </div>
    @endif
</div>
