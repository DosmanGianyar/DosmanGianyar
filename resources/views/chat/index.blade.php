@extends($layout)

@section('title', $activeTab === 'parent_teacher' ? 'Chat Privat Ortu & Wali Kelas' : 'Ruang Chat Konsultasi')
@section('page-title', $activeTab === 'parent_teacher' ? 'Chat Privat Ortu & Wali' : 'Ruang Chat Konsultasi')

@section('content')
<div class="max-w-4xl mx-auto space-y-4 pb-12">

    {{-- ─── 1. Banner Header ─────────────────────────────────────────────── --}}
    @if($activeTab === 'parent_teacher')
        {{-- Banner Khusus Privat Ortu & Wali --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 p-4 sm:p-5 text-white shadow-sm">
            <div class="relative z-10 flex items-center justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 backdrop-blur-md text-[11px] font-bold text-indigo-200 border border-white/15 mb-2">
                        <span>🔒</span> Saluran Privat Tertutup
                    </div>
                    <h1 class="text-base sm:text-lg font-bold tracking-tight text-white flex items-center gap-2">
                        Diskusi Privat Orang Tua & Wali Kelas
                    </h1>
                    <p class="text-indigo-200/90 text-xs mt-1 max-w-xl leading-relaxed">
                        Saluran komunikasi tertutup untuk hal-hal yang bersifat sensitif. <strong>Siswa tidak dapat mengakses obrolan ini.</strong>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-white/10 border border-white/15 backdrop-blur-md flex items-center justify-center text-2xl shrink-0 shadow-inner">
                    🔒
                </div>
            </div>
        </div>
    @else
        {{-- Banner Ruang Bersama Siswa --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-teal-950 p-4 sm:p-5 text-white shadow-sm">
            <div class="relative z-10 flex items-center justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 backdrop-blur-md text-[11px] font-bold text-teal-200 border border-white/15 mb-2">
                        <span>💬</span> Ruang Konsultasi Bersama
                    </div>
                    <h1 class="text-base sm:text-lg font-bold tracking-tight text-white">
                        Chat Siswa, Orang Tua & Wali Kelas
                    </h1>
                    <p class="text-teal-200/90 text-xs mt-1 max-w-xl leading-relaxed">
                        Forum komunikasi terpadu dan transparan antara Wali Kelas, Orang Tua, dan Siswa terkait pendampingan belajar.
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-white/10 border border-white/15 backdrop-blur-md flex items-center justify-center text-2xl shrink-0 shadow-inner">
                    👨‍👩‍👧‍🏫
                </div>
            </div>
        </div>
    @endif

    {{-- ─── 2. Tab Switcher (Khusus Wali Kelas & Orang Tua) ──────────────── --}}
    @if(!auth()->user()->isSiswa())
        <div class="bg-white p-1 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-1">
            {{-- Tab 1: Ruang Bersama --}}
            <a href="{{ route(auth()->user()->role . '.chat.index', ['tab' => 'trio']) }}"
               class="flex-1 py-2 px-3 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition-all
                      {{ $activeTab === 'trio' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50' }}">
                <span>👥 Bersama Siswa</span>
                @if(($unreadStats['trio'] ?? 0) > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $activeTab === 'trio' ? 'bg-white text-teal-700' : 'bg-rose-500 text-white' }} animate-pulse">
                        {{ $unreadStats['trio'] }}
                    </span>
                @endif
            </a>

            {{-- Tab 2: Ruang Privat Ortu & Wali --}}
            <a href="{{ route(auth()->user()->role . '.chat.index', ['tab' => 'parent_teacher']) }}"
               class="flex-1 py-2 px-3 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition-all
                      {{ $activeTab === 'parent_teacher' ? 'bg-indigo-700 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50' }}">
                <span>🔒 Privat Ortu & Wali</span>
                @if(($unreadStats['parent_teacher'] ?? 0) > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $activeTab === 'parent_teacher' ? 'bg-white text-indigo-700' : 'bg-rose-500 text-white' }} animate-pulse">
                        {{ $unreadStats['parent_teacher'] }}
                    </span>
                @endif
            </a>
        </div>
    @endif

    {{-- ─── 3. Search Bar (Jika banyak siswa / kelas) ────────────────────── --}}
    @if($roomData->count() > 3)
    <div class="relative">
        <input type="text" id="chatSearchInput" placeholder="Cari nama siswa atau kelas..."
               class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200/80 rounded-2xl text-xs sm:text-sm focus:ring-2 {{ $activeTab === 'parent_teacher' ? 'focus:ring-indigo-500 focus:border-indigo-500' : 'focus:ring-teal-500 focus:border-teal-500' }} shadow-xs transition-all">
        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </div>
    @endif

    {{-- ─── 4. Daftar Ruang Chat ────────────────────────────────────────── --}}
    <div class="space-y-2.5" id="chatRoomList">
        @forelse($roomData as $item)
            @php
                $room      = $item['room'];
                $student   = $item['student'];
                $class     = $item['class'];
                $unread    = $item['unread_count'];
                $lastMsg   = $item['last_message'];
                $lastTime  = $item['last_time'];
                $isPrivate = $room->isParentTeacher();

                $chatUrl = match(auth()->user()->role) {
                    'guru'     => route('guru.chat.show', $room->id),
                    'orangtua' => route('orangtua.chat.show', $room->id),
                    'siswa'    => route('siswa.chat.show', $room->id),
                    default    => '#',
                };
            @endphp

            <a href="{{ $chatUrl }}"
               class="chat-room-card block bg-white rounded-2xl border p-3.5 transition-all group
                      {{ $unread > 0
                         ? ($isPrivate ? 'border-indigo-300 ring-2 ring-indigo-500/20 bg-indigo-50/20 shadow-xs' : 'border-teal-300 ring-2 ring-teal-500/20 bg-teal-50/20 shadow-xs')
                         : 'border-slate-200/80 hover:border-slate-300 shadow-2xs' }}
                      {{ $isPrivate ? 'hover:border-indigo-400 hover:shadow-xs' : 'hover:border-teal-400 hover:shadow-xs' }}"
               data-student-name="{{ strtolower($student?->name ?? '') }}"
               data-class-name="{{ strtolower($class?->name ?? '') }}">
                <div class="flex items-center gap-3">
                    {{-- Avatar Siswa --}}
                    <div class="relative shrink-0">
                        @if($student?->photo)
                            <img src="{{ $student->photo_url }}" alt="{{ $student->name }}"
                                 class="w-11 h-11 rounded-xl object-cover ring-2 {{ $isPrivate ? 'ring-indigo-100 group-hover:ring-indigo-300' : 'ring-slate-100 group-hover:ring-teal-300' }} transition-all">
                        @else
                            <div class="w-11 h-11 rounded-xl bg-gradient-to-tr {{ $isPrivate ? 'from-indigo-600 to-blue-700' : 'from-teal-600 to-emerald-600' }} text-white font-bold text-sm flex items-center justify-center shadow-xs">
                                {{ $student?->initials ?? 'S' }}
                            </div>
                        @endif

                        @if($unread > 0)
                            <span class="absolute -top-1 -right-1 min-w-[18px] h-4.5 px-1 bg-rose-500 text-white text-[9px] font-black rounded-full flex items-center justify-center border-2 border-white shadow-xs animate-bounce">
                                {{ $unread }}
                            </span>
                        @endif
                    </div>

                    {{-- Info Siswa & Pesan Terakhir --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-0.5">
                            <h3 class="font-bold text-slate-800 text-xs sm:text-sm truncate {{ $isPrivate ? 'group-hover:text-indigo-700' : 'group-hover:text-teal-700' }} transition-colors flex items-center gap-1.5">
                                <span>{{ $student?->name ?? $room->name }}</span>
                                @if($isPrivate)
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200 shrink-0">
                                        🔒 Privat
                                    </span>
                                @endif
                            </h3>
                            @if($lastTime)
                                <span class="text-[10px] text-slate-400 font-medium shrink-0">
                                    {{ $lastTime }}
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded-md text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                Kelas {{ $class?->name ?? '—' }}
                            </span>
                            @if(auth()->user()->isOrangtua() && $class?->homeroomTeacher)
                                <span class="text-[10px] text-slate-500 truncate">
                                    Wali: <strong class="text-slate-700">{{ $class->homeroomTeacher->name }}</strong>
                                </span>
                            @endif
                        </div>

                        <p class="text-xs {{ $unread > 0 ? 'text-slate-900 font-bold' : 'text-slate-500' }} truncate flex items-center gap-1.5">
                            @if($unread > 0)
                                <span class="w-1.5 h-1.5 rounded-full {{ $isPrivate ? 'bg-indigo-600' : 'bg-teal-500' }} shrink-0"></span>
                            @endif
                            <span class="truncate">{{ $lastMsg ?: ($isPrivate ? 'Belum ada obrolan privat dengan wali/ortu. Mulai sekarang.' : 'Belum ada percakapan. Mulai obrolan sekarang.') }}</span>
                        </p>
                    </div>

                    {{-- Arrow Icon --}}
                    <div class="shrink-0 text-slate-300 {{ $isPrivate ? 'group-hover:text-indigo-600' : 'group-hover:text-teal-600' }} transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        @empty
            <div class="bg-white rounded-2xl p-8 text-center border border-slate-200/80 shadow-xs space-y-3">
                <div class="w-14 h-14 mx-auto rounded-full {{ $activeTab === 'parent_teacher' ? 'bg-indigo-50 text-indigo-600' : 'bg-teal-50 text-teal-600' }} flex items-center justify-center text-2xl">
                    {{ $activeTab === 'parent_teacher' ? '🔒' : '💬' }}
                </div>
                <h3 class="font-bold text-slate-800 text-sm">
                    {{ $activeTab === 'parent_teacher' ? 'Belum Ada Obrolan Privat' : 'Belum Ada Ruang Konsultasi' }}
                </h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">
                    @if(auth()->user()->isGuru())
                        Anda belum ditetapkan sebagai wali kelas untuk kelas aktif saat ini.
                    @elseif(auth()->user()->isOrangtua())
                        Belum ada data siswa yang terhubung dengan akun orang tua Anda.
                    @else
                        Ruang konsultasi kelas belum dibuat.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('chatSearchInput');
        if (!searchInput) return;

        searchInput.addEventListener('input', function (e) {
            const query = e.target.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.chat-room-card');

            cards.forEach(card => {
                const sName = card.getAttribute('data-student-name') || '';
                const cName = card.getAttribute('data-class-name') || '';
                if (sName.includes(query) || cName.includes(query)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
</script>
@endpush
@endsection
