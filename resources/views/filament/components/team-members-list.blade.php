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

<div class="space-y-3">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($students as $s)
            @php
                $avatar = $s->photo 
                    ? asset('storage/' . $s->photo) 
                    : 'https://ui-avatars.com/api/?name=' . urlencode($s->name) . '&background=0D8ABC&color=fff';
                $cleanPhone = $s->phone ? preg_replace('/[^0-9]/', '', $s->phone) : null;
                if ($cleanPhone && str_starts_with($cleanPhone, '0')) {
                    $cleanPhone = '62' . substr($cleanPhone, 1);
                }
            @endphp
            <div class="flex items-center gap-3.5 p-3.5 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:border-primary-400 transition-colors">
                <img src="{{ $avatar }}" alt="{{ $s->name }}" class="w-14 h-14 rounded-xl object-cover border border-gray-200 dark:border-gray-700 flex-shrink-0 shadow-xs">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-1">
                        <a href="{{ \App\Filament\Resources\UserResource::getUrl('view', ['record' => $s->id]) }}" 
                           target="_blank" 
                           class="text-sm font-bold text-primary-600 dark:text-primary-400 hover:underline truncate">
                            {{ $s->name }} ↗
                        </a>
                        @if($record && $record->student_id === $s->id && $record->participation_type === 'beregu')
                            <span class="text-[10px] font-extrabold bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300 px-1.5 py-0.5 rounded">Pendaftar</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                        Kelas: {{ $s->schoolClass?->name ?? '—' }} @if($s->nisn) · NISN: {{ $s->nisn }} @endif
                    </p>
                    @if($cleanPhone)
                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline mt-1">
                            💬 WhatsApp ({{ $s->phone }})
                        </a>
                    @else
                        <span class="text-[11px] text-gray-400 block mt-1">No HP belum diisi</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
