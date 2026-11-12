<?php

namespace App\Filament\Pages;

use App\Filament\Support\AdminAccess;
use App\Models\Schedule;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class TeacherSchedulePage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-table-cells';
    protected static string|\UnitEnum|null   $navigationGroup = 'Kurikulum';
    protected static ?string                 $navigationLabel = 'Jadwal Per Guru';
    protected static ?string                 $title            = 'Jadwal Mengajar Guru';
    protected static ?string                 $slug             = 'jadwal-guru';
    protected static ?int                    $navigationSort   = 4;

    public static function canAccess(): bool
    {
        return AdminAccess::can('Kurikulum');
    }

    protected string $view = 'filament.pages.teacher-schedule';

    public ?int $selectedTeacherId = null;
    public string $academicYear = '2026/2027 Ganjil';
    public string $search = '';

    public const TIME_SLOTS = [
        0  => '7:10 - 7:55',
        1  => '7:30 - 8:15',
        2  => '8:15 - 9:00',
        3  => '9:00 - 9:45',
        4  => '10:00 - 10:45',
        5  => '10:45 - 11:30',
        6  => '11:30 - 12:15',
        7  => '12:30 - 13:15',
        8  => '13:15 - 14:00',
        9  => '16:00 - 16:45',
        10 => '17:00 - 17:45',
    ];

    public const DAYS = [
        1 => ['name' => 'SENIN',  'bg' => '#C8E6C9', 'text' => '#1B5E20'],
        2 => ['name' => 'SELASA', 'bg' => '#FFE0B2', 'text' => '#E65100'],
        3 => ['name' => 'RABU',   'bg' => '#C8E6C9', 'text' => '#1B5E20'],
        4 => ['name' => 'KAMIS',  'bg' => '#FFE0B2', 'text' => '#E65100'],
        5 => ['name' => 'JUMAT',  'bg' => '#C8E6C9', 'text' => '#1B5E20'],
        6 => ['name' => 'SABTU',  'bg' => '#FFE0B2', 'text' => '#E65100'],
    ];

    public function mount(): void
    {
        if (request()->has('teacher_id')) {
            $this->selectedTeacherId = (int) request('teacher_id');
        }

        if (request()->has('academic_year')) {
            $this->academicYear = (string) request('academic_year');
        }

        // If no teacher selected, default to the first teacher with schedules
        if (! $this->selectedTeacherId) {
            $firstWithSchedule = Schedule::where('academic_year', $this->academicYear)
                ->whereNotNull('teacher_id')
                ->orderBy('id')
                ->value('teacher_id');

            $this->selectedTeacherId = $firstWithSchedule;
        }
    }

    public function getAcademicYearsProperty(): array
    {
        $years = Schedule::distinct()->pluck('academic_year')->filter()->values()->toArray();
        if (empty($years)) {
            $years = ['2026/2027 Ganjil'];
        }
        return $years;
    }

    public function getTeachersProperty(): Collection
    {
        // Teachers who have schedule in this academic year
        $teacherIdsWithCount = Schedule::where('academic_year', $this->academicYear)
            ->whereNotNull('teacher_id')
            ->selectRaw('teacher_id, count(*) as slot_count')
            ->groupBy('teacher_id')
            ->pluck('slot_count', 'teacher_id');

        $teachers = User::whereIn('id', $teacherIdsWithCount->keys())
            ->orderBy('name')
            ->get(['id', 'name', 'teacher_code']);

        return $teachers->map(function ($t) use ($teacherIdsWithCount) {
            $t->slot_count = $teacherIdsWithCount[$t->id] ?? 0;
            return $t;
        });
    }

    public function getSelectedTeacherProperty(): ?User
    {
        if (! $this->selectedTeacherId) {
            return null;
        }

        return User::find($this->selectedTeacherId);
    }

    public function getStatsProperty(): array
    {
        if (! $this->selectedTeacherId) {
            return [
                'total_hours'   => 0,
                'total_classes' => 0,
                'classes'       => [],
                'subjects'      => [],
            ];
        }

        $schedules = Schedule::with(['schoolClass', 'subject'])
            ->where('teacher_id', $this->selectedTeacherId)
            ->where('academic_year', $this->academicYear)
            ->get();

        $classes  = $schedules->pluck('schoolClass.name')->filter()->unique()->values()->all();
        $subjects = $schedules->pluck('subject.name')->filter()->unique()->values()->all();

        return [
            'total_hours'   => $schedules->count(),
            'total_classes' => count($classes),
            'classes'       => $classes,
            'subjects'      => $subjects,
        ];
    }

    public function getGridDataProperty(): array
    {
        if (! $this->selectedTeacherId) {
            return [];
        }

        $schedules = Schedule::with(['schoolClass', 'subject'])
            ->where('teacher_id', $this->selectedTeacherId)
            ->where('academic_year', $this->academicYear)
            ->get();

        $rawGrid = [];
        foreach ($schedules as $s) {
            $rawGrid[$s->day][$s->period] = $s;
        }

        $periods = array_keys(self::TIME_SLOTS);
        $days    = array_keys(self::DAYS);

        $skip = [];
        $grid = [];

        foreach ($periods as $p) {
            $grid[$p] = [];
            foreach ($days as $d) {
                if (isset($skip[$d][$p])) {
                    $grid[$p][$d] = ['type' => 'skip'];
                    continue;
                }

                $slot = $rawGrid[$d][$p] ?? null;
                if (! $slot) {
                    $grid[$p][$d] = ['type' => 'empty', 'span' => 1];
                    continue;
                }

                // Count consecutive periods with identical class and subject
                $span = 1;
                while (in_array($p + $span, $periods, true)) {
                    $nextSlot = $rawGrid[$d][$p + $span] ?? null;
                    if ($nextSlot && $nextSlot->class_id === $slot->class_id && $nextSlot->subject_id === $slot->subject_id) {
                        $skip[$d][$p + $span] = true;
                        $span++;
                    } else {
                        break;
                    }
                }

                $grid[$p][$d] = [
                    'type'         => 'slot',
                    'span'         => $span,
                    'class_name'   => $slot->schoolClass?->name ?? '—',
                    'subject_code' => $slot->subject?->code ?: ($slot->subject?->name ?? '—'),
                    'subject_name' => $slot->subject?->name ?? '—',
                    'room'         => $slot->room,
                ];
            }
        }

        return $grid;
    }
}
