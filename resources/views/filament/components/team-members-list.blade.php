@php
    $record = $getRecord();
    if ($record && $record->participation_type === 'beregu') {
        if (!empty($record->team_code)) {
            $matching = \App\Models\StudentAchievement::where('team_code', $record->team_code)
                ->with('student.schoolClass')
                ->get();
        } else {
            $matching = \App\Models\StudentAchievement::where('participation_type', 'beregu')
                ->where('title', $record->title)
                ->where('achievement_date', $record->achievement_date)
                ->with('student.schoolClass')
                ->get();
        }
        $students = $matching->pluck('student')->filter()->unique('id');
        if ($students->isEmpty() && $record->student) {
            $students = collect([$record->student]);
        }
    } else {
        $students = ($record && $record->student) ? collect([$record->student]) : collect();
    }
@endphp

<div x-data="{ openPreview: false, previewSrc: '', previewTitle: '' }" class="w-full space-y-3">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($students as $s)
            @php
                $avatar = $s->photo 
                    ? asset('storage/' . $s->photo) 
                    : 'https://ui-avatars.com/api/?name=' . urlencode($s->name) . '&background=1d4ed8&color=ffffff&bold=true';
                $cleanPhone = $s->phone ? preg_replace('/[^0-9]/', '', $s->phone) : null;
                if ($cleanPhone && str_starts_with($cleanPhone, '0')) {
                    $cleanPhone = '62' . substr($cleanPhone, 1);
                }
            @endphp
            <div class="flex items-center gap-3 p-3.5 rounded-2xl border border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-800/90 shadow-sm hover:shadow-md transition-all">
                <button type="button" 
                        @click="previewSrc = '{{ $avatar }}'; previewTitle = '{{ addslashes($s->name) }} (Kelas {{ $s->schoolClass?->name ?? '—' }})'; openPreview = true"
                        title="Klik untuk lihat foto lebih besar"
                        class="relative group shrink-0 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-2xl overflow-hidden cursor-pointer">
                    <img src="{{ $avatar }}" 
                         alt="{{ $s->name }}" 
                         width="52" 
                         height="52" 
                         style="width: 52px !important; height: 52px !important; min-width: 52px !important; min-height: 52px !important; max-width: 52px !important; max-height: 52px !important; border-radius: 14px !important; object-fit: cover !important; display: block !important;" 
                         class="border border-gray-200 dark:border-gray-600 shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                    <div class="absolute inset-0 bg-black/35 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-2xl">
                        <span class="text-white text-xs font-bold">🔍</span>
                    </div>
                </button>

                <div class="min-w-0 flex-1 space-y-0.5">
                    <div class="flex items-center justify-between gap-1.5">
                        <a href="{{ \App\Filament\Resources\UserResource::getUrl('view', ['record' => $s->id]) }}" 
                           target="_blank" 
                           class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 hover:underline truncate">
                            {{ $s->name }} ↗
                        </a>
                        @if($record && $record->student_id === $s->id && $record->participation_type === 'beregu')
                            <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-900/60 text-purple-700 dark:text-purple-300 border border-purple-300 dark:border-purple-700 shrink-0">Pendaftar</span>
                        @endif
                    </div>
                    <p class="text-[11px] font-medium text-gray-600 dark:text-gray-400 truncate">
                        Kelas: <span class="font-bold text-gray-800 dark:text-gray-200">{{ $s->schoolClass?->name ?? '—' }}</span>
                        @if($s->nisn) · NISN: <span class="font-mono text-gray-700 dark:text-gray-300">{{ $s->nisn }}</span> @endif
                    </p>
                    <div class="flex items-center justify-between gap-2 pt-0.5">
                        @if($cleanPhone)
                            <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline truncate">
                                💬 WhatsApp ({{ $s->phone }})
                            </a>
                        @else
                            <span class="text-[11px] text-gray-400 dark:text-gray-500 block truncate">No. HP belum diisi</span>
                        @endif

                        @if($s->photo)
                            <form action="{{ route('admin.students.delete-photo', $s->id) }}" method="POST" onsubmit="return confirm('Hapus foto profil {{ addslashes($s->name) }}? Foto yang melanggar aturan akan dihapus dan dikembalikan ke avatar standar.')" class="shrink-0">
                                @csrf
                                <button type="submit" class="text-[10px] font-bold text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 hover:underline bg-red-50 dark:bg-red-950/40 px-2 py-0.5 rounded-full border border-red-200 dark:border-red-800 shrink-0">
                                    🗑️ Hapus Foto
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Lightbox Pop-up Foto Profil Siswa -->
    <template x-teleport="body">
        <div x-show="openPreview" 
             x-transition.opacity 
             x-cloak 
             @keydown.escape.window="openPreview = false"
             class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
            <div @click.away="openPreview = false" class="relative max-w-xl w-full bg-white dark:bg-gray-900 rounded-3xl p-4 shadow-2xl space-y-3 text-center border border-gray-200 dark:border-gray-800">
                <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800 px-2">
                    <h4 class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate" x-text="previewTitle"></h4>
                    <button type="button" @click="openPreview = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg font-bold px-2 py-1 focus:outline-none">
                        ✕
                    </button>
                </div>
                <div class="flex justify-center overflow-hidden rounded-2xl bg-gray-950 p-2">
                    <img :src="previewSrc" :alt="previewTitle" class="max-h-[75vh] w-auto max-w-full object-contain rounded-xl shadow-lg">
                </div>
                <div class="flex justify-end pt-1">
                    <a :href="previewSrc" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-all shadow-sm">
                        🌐 Buka Foto Tab Baru ↗
                    </a>
                </div>
            </div>
        </div>
    </template>
</div>
