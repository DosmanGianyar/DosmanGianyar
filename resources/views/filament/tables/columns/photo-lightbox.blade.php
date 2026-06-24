@php
    $photoUrl = $record->photo ? asset('storage/' . $record->photo) : null;
@endphp

<div
    x-data="{ open: false }"
    style="display:inline-block;"
>
    @if ($photoUrl)
        <img
            src="{{ $photoUrl }}"
            alt="{{ $record->name }}"
            x-on:click.stop="open = true"
            style="width:2.5rem; height:2.5rem; border-radius:0.375rem; object-fit:cover; cursor:pointer; border:2px solid #334155; transition:border-color 0.15s; display:block;"
            onmouseover="this.style.borderColor='#6366f1'"
            onmouseout="this.style.borderColor='#334155'"
        >
    @else
        <div
            style="width:2.5rem; height:2.5rem; border-radius:0.375rem; background:#1e293b; border:1.5px dashed #475569; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:1px; cursor:default; flex-shrink:0;"
        >
            <svg style="width:1rem; height:1rem; color:#64748b; fill:none; stroke:#64748b; stroke-width:1.5;" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
            </svg>
            <span style="font-size:0.45rem; color:#64748b; font-weight:600; letter-spacing:0.02em;">No Foto</span>
        </div>
    @endif

    @if ($photoUrl)
    <template x-teleport="body">
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-on:click="open = false"
            x-on:keydown.escape.window="open = false"
            style="position:fixed; inset:0; background:rgba(0,0,0,0.75); z-index:99999; display:flex; align-items:center; justify-content:center; padding:1.5rem;"
            tabindex="-1"
        >
            <div
                x-on:click.stop
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                style="position:relative; max-width:22rem; width:100%;"
            >
                <img
                    src="{{ $photoUrl }}"
                    alt="{{ $record->name }}"
                    style="width:100%; border-radius:0.75rem; box-shadow:0 25px 50px rgba(0,0,0,0.6); display:block;"
                >
                <div style="text-align:center; margin-top:0.75rem; color:#f1f5f9; font-size:0.875rem; font-weight:600;">
                    {{ $record->name }}
                </div>
                <button
                    x-on:click="open = false"
                    style="position:absolute; top:-0.6rem; right:-0.6rem; background:#fff; border:none; border-radius:9999px; width:1.75rem; height:1.75rem; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,0.35); font-size:1rem; line-height:1; color:#374151;"
                    aria-label="Tutup"
                >&times;</button>
            </div>
        </div>
    </template>
    @endif
</div>
