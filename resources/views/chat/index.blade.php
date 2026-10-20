@extends($layout)

@section('title', 'Ruang Chat Konsultasi')
@section('page-title', 'Ruang Chat Konsultasi')

@section('content')
<div class="max-w-4xl mx-auto space-y-4 pb-12">

    {{-- Banner Header --}}
    <div class="rounded-3xl p-5 text-white shadow-lg"
         style="background: linear-gradient(135deg, #065f46 0%, #059669 50%, #10b981 100%);">
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-bold text-white border border-white/30 mb-2">
                    <span>💬</span> Ruang Konsultasi Segitiga
                </div>
                <h1 class="text-xl font-black tracking-tight">Chat Siswa, Ortu & Wali Kelas</h1>
                <p class="text-emerald-100 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">
                    Forum komunikasi terpadu dan transparan antara Wali Kelas, Orang Tua, dan Siswa. Lengkap dengan indikator centang baca seperti WhatsApp.
                </p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-white/15 border border-white/20 backdrop-blur-md flex items-center justify-center text-3xl shrink-0 shadow-inner">
                👨‍👩‍👧‍🏫
            </div>
        </div>
    </div>

    {{-- Search Bar (Jika banyak siswa / wali kelas) --}}
    @if($roomData->count() > 3)
    <div class="relative">
        <input type="text" id="chatSearchInput" placeholder="Cari nama siswa atau kelas..."
               class="w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-2xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs transition-all">
        <svg class="w-5 h-5 text-gray-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </div>
    @endif

    {{-- Daftar Ruang Chat --}}
    <div class="space-y-3" id="chatRoomList">
        @forelse($roomData as $item)
            @php
                $room    = $item['room'];
                $student = $item['student'];
                $class   = $item['class'];
                $unread  = $item['unread_count'];
                $lastMsg = $item['last_message'];
                $lastTime= $item['last_time'];

                $chatUrl = match(auth()->user()->role) {
                    'guru'     => route('guru.chat.show', $room->id),
                    'orangtua' => route('orangtua.chat.show', $room->id),
                    'siswa'    => route('siswa.chat.show', $room->id),
                    default    => '#',
                };
            @endphp

            <a href="{{ $chatUrl }}"
               class="chat-room-card block bg-white rounded-2xl border {{ $unread > 0 ? 'border-emerald-300 ring-2 ring-emerald-500/20 bg-emerald-50/20' : 'border-gray-100' }} p-4 hover:border-emerald-400 hover:shadow-md transition-all group"
               data-student-name="{{ strtolower($student?->name ?? '') }}"
               data-class-name="{{ strtolower($class?->name ?? '') }}">
                <div class="flex items-center gap-3.5">
                    {{-- Avatar Siswa --}}
                    <div class="relative shrink-0">
                        @if($student?->photo)
                            <img src="{{ $student->photo_url }}" alt="{{ $student->name }}"
                                 class="w-13 h-13 rounded-2xl object-cover ring-2 ring-gray-100 group-hover:ring-emerald-300 transition-all">
                        @else
                            <div class="w-13 h-13 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-600 text-white font-black text-lg flex items-center justify-center shadow-xs">
                                {{ $student?->initials ?? 'S' }}
                            </div>
                        @endif

                        @if($unread > 0)
                            <span class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1.5 bg-rose-500 text-white text-[10px] font-black rounded-full flex items-center justify-center border-2 border-white shadow-xs animate-bounce">
                                {{ $unread }}
                            </span>
                        @endif
                    </div>

                    {{-- Info Siswa & Pesan Terakhir --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <h3 class="font-bold text-gray-900 text-sm sm:text-base truncate group-hover:text-emerald-700 transition-colors">
                                {{ $student?->name ?? $room->name }}
                            </h3>
                            @if($lastTime)
                                <span class="text-[11px] text-gray-400 font-medium shrink-0">
                                    {{ $lastTime }}
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                Kelas {{ $class?->name ?? '—' }}
                            </span>
                            @if(auth()->user()->isOrangtua() && $class?->homeroomTeacher)
                                <span class="text-[11px] text-gray-500 truncate">
                                    Wali: <strong class="text-gray-700">{{ $class->homeroomTeacher->name }}</strong>
                                </span>
                            @endif
                        </div>

                        <p class="text-xs {{ $unread > 0 ? 'text-gray-900 font-bold' : 'text-gray-500' }} truncate flex items-center gap-1.5">
                            @if($unread > 0)
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                            @endif
                            <span>{{ $lastMsg ?: 'Belum ada percakapan. Mulai obrolan sekarang.' }}</span>
                        </p>
                    </div>

                    {{-- Arrow Icon --}}
                    <div class="shrink-0 text-gray-300 group-hover:text-emerald-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        @empty
            <div class="bg-white rounded-3xl p-8 text-center border border-gray-100 space-y-3">
                <div class="w-16 h-16 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-3xl">
                    💬
                </div>
                <h3 class="font-bold text-gray-800 text-base">Belum Ada Ruang Konsultasi</h3>
                <p class="text-xs text-gray-500 max-w-sm mx-auto">
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
