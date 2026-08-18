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
    <div class="max-h-64 overflow-y-auto pr-1 border border-white/10 rounded-2xl p-2" style="background: rgba(15, 29, 51, 0.7); backdrop-filter: blur(10px);">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
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
                <div class="flex items-center gap-3 p-3 rounded-xl border border-white/10 transition-colors" style="background: rgba(26, 44, 76, 0.95);">
                    <img src="{{ $avatar }}" alt="{{ $s->name }}" class="w-12 h-12 rounded-xl object-cover border border-white/15 flex-shrink-0 shadow-sm">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-1">
                            <a href="{{ \App\Filament\Resources\UserResource::getUrl('view', ['record' => $s->id]) }}" 
                               target="_blank" 
                               class="text-xs font-bold hover:underline truncate"
                               style="color: #60a5fa !important;">
                                {{ $s->name }} ↗
                            </a>
                            @if($record && $record->student_id === $s->id && $record->participation_type === 'beregu')
                                <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded border" style="background: rgba(168, 85, 247, 0.25); color: #e9d5ff; border-color: rgba(168, 85, 247, 0.4);">Pendaftar</span>
                            @endif
                        </div>
                        <p class="text-[11px] font-medium mt-0.5" style="color: #94a3b8 !important;">
                            Kelas: {{ $s->schoolClass?->name ?? '—' }} @if($s->nisn) · NISN: {{ $s->nisn }} @endif
                        </p>
                        @if($cleanPhone)
                            <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-semibold hover:underline mt-1" style="color: #34d399 !important;">
                                💬 WhatsApp ({{ $s->phone }})
                            </a>
                        @else
                            <span class="text-[11px] block mt-1" style="color: #64748b !important;">No HP belum diisi</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
