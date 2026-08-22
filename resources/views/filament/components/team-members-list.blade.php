@php
    $record = $getRecord();
    if ($record && $record->participation_type === 'beregu') {
        $matching = \App\Models\StudentAchievement::where('participation_type', 'beregu')
            ->where('title', $record->title)
            ->where('achievement_date', $record->achievement_date)
            ->with('student.schoolClass')
            ->get();
        $students = $matching->pluck('student')->filter()->unique('id');
        if ($students->isEmpty() && $record->student) {
            $students = collect([$record->student]);
        }
    } else {
        $students = ($record && $record->student) ? collect([$record->student]) : collect();
    }
@endphp

<div class="w-full space-y-3">
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
                <img src="{{ $avatar }}" alt="{{ $s->name }}" class="w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-gray-600 shrink-0 shadow-sm">
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
                    @if($cleanPhone)
                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline pt-0.5">
                            💬 WhatsApp ({{ $s->phone }})
                        </a>
                    @else
                        <span class="text-[11px] text-gray-400 dark:text-gray-500 block pt-0.5">No. HP belum diisi</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
