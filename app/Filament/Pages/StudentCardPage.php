<?php

namespace App\Filament\Pages;

use App\Filament\Support\AdminAccess;
use App\Models\SchoolClass;
use App\Models\User;
use Filament\Pages\Page;

class StudentCardPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-identification';
    protected static string|\UnitEnum|null   $navigationGroup = 'Kesiswaan';
    protected static ?string                 $navigationLabel = 'Download Kartu Siswa';
    protected static ?string                 $title           = 'Download Kartu Pelajar';
    protected static ?int                    $navigationSort  = 20;

    public static function canAccess(): bool { return AdminAccess::can('Kesiswaan'); }

    protected string $view = 'filament.pages.student-card';

    public string $search  = '';
    public string $classId = '';

    public function getClasses(): \Illuminate\Support\Collection
    {
        return SchoolClass::orderByRaw("CASE grade WHEN 'X' THEN 1 WHEN 'XI' THEN 2 WHEN 'XII' THEN 3 ELSE 4 END")
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getStudents(): \Illuminate\Support\Collection
    {
        return User::where('role', 'like', 'siswa%')
            ->when($this->classId, fn($q) => $q->where('class_id', $this->classId))
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('nis', 'like', "%{$this->search}%")
                  ->orWhere('nisn', 'like', "%{$this->search}%");
            }))
            ->with('schoolClass')
            ->orderBy('name')
            ->get(['id', 'name', 'nis', 'nisn', 'photo', 'class_id', 'gender']);
    }
}
