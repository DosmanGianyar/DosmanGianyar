@extends('layouts.siswa')
@section('title', 'Laporan Prestasi Sekolah')
@section('page-title', 'Prestasi Sekolah')

@section('content')
<div class="max-w-lg mx-auto space-y-4">

    {{-- Summary by Level --}}
    <div class="grid grid-cols-5 gap-1.5">
        @php
            $levels = [
                'sekolah'       => ['label' => 'Sekolah',   'class' => 'bg-gray-100 text-gray-700'],
                'kabupaten'     => ['label' => 'Kab/Kota',  'class' => 'bg-blue-100 text-blue-700'],
                'provinsi'      => ['label' => 'Provinsi',  'class' => 'bg-yellow-100 text-yellow-700'],
                'nasional'      => ['label' => 'Nasional',  'class' => 'bg-green-100 text-green-700'],
                'internasional' => ['label' => 'Internl.',  'class' => 'bg-red-100 text-red-700'],
            ];
        @endphp
        @foreach($levels as $key => $info)
        <div class="rounded-xl p-2 text-center {{ $info['class'] }}">
            <p class="text-xl font-bold">{{ $summary[$key] }}</p>
            <p class="text-[10px] font-medium leading-tight mt-0.5">{{ $info['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('siswa.achievements.report') }}"
        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-3">

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Periode</label>
                <select name="period" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm"
                    onchange="this.form.submit()">
                    <option value="this_week"  {{ $period === 'this_week'  ? 'selected' : '' }}>Minggu Ini</option>
                    <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="this_year"  {{ $period === 'this_year'  ? 'selected' : '' }}>Tahun Ini</option>
                    <option value="all"        {{ $period === 'all'        ? 'selected' : '' }}>Semua Waktu</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Tingkat</label>
                <select name="level" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm"
                    onchange="this.form.submit()">
                    <option value="" {{ $level === '' ? 'selected' : '' }}>Semua Tingkat</option>
                    <option value="sekolah"       {{ $level === 'sekolah'       ? 'selected' : '' }}>Sekolah</option>
                    <option value="kabupaten"     {{ $level === 'kabupaten'     ? 'selected' : '' }}>Kabupaten/Kota</option>
                    <option value="provinsi"      {{ $level === 'provinsi'      ? 'selected' : '' }}>Provinsi</option>
                    <option value="nasional"      {{ $level === 'nasional'      ? 'selected' : '' }}>Nasional</option>
                    <option value="internasional" {{ $level === 'internasional' ? 'selected' : '' }}>Internasional</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Kategori</label>
            <select name="category_id" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm"
                onchange="this.form.submit()">
                <option value="" {{ $categoryId === '' ? 'selected' : '' }}>Semua Kategori</option>
                @foreach($categories as $id => $name)
                <option value="{{ $id }}" {{ $categoryId == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </form>

    {{-- Total --}}
    <div class="flex items-center justify-between px-1">
        <p class="text-sm font-semibold text-gray-700">
            {{ $achievements->count() }} prestasi ditemukan
        </p>
        <a href="{{ route('siswa.achievements.index') }}"
            class="text-xs text-blue-600 font-medium">Prestasi Saya →</a>
    </div>

    {{-- Achievement List --}}
    @forelse($achievements as $achievement)
    <a href="{{ route('siswa.achievements.show', $achievement) }}"
        class="block bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:border-blue-200 transition-colors">
        <div class="flex items-start gap-3">
            {{-- Level indicator --}}
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                {{ match($achievement->level) {
                    'nasional'      => 'bg-green-100',
                    'internasional' => 'bg-red-100',
                    'provinsi'      => 'bg-yellow-100',
                    'kabupaten'     => 'bg-blue-100',
                    default         => 'bg-gray-100',
                } }}">
                <svg class="w-5 h-5 {{ match($achievement->level) {
                    'nasional'      => 'text-green-600',
                    'internasional' => 'text-red-600',
                    'provinsi'      => 'text-yellow-600',
                    'kabupaten'     => 'text-blue-600',
                    default         => 'text-gray-600',
                } }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </div>

            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-800 text-sm leading-tight truncate">{{ $achievement->title }}</p>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $achievement->student->name }}
                    @if($achievement->student->schoolClass)
                        · {{ $achievement->student->schoolClass->name }}
                    @endif
                </p>
                <div class="flex flex-wrap gap-1.5 mt-1.5">
                    <span class="text-xs font-medium px-1.5 py-0.5 rounded-full {{ $achievement->levelBadgeClass() }}">
                        {{ $achievement->levelLabel() }}
                    </span>
                    @if($achievement->rank)
                    <span class="text-xs text-purple-700 bg-purple-100 px-1.5 py-0.5 rounded-full font-medium">
                        {{ $achievement->rank }}
                    </span>
                    @endif
                    @if($achievement->category)
                    <span class="text-xs text-gray-500">{{ $achievement->category->name }}</span>
                    @endif
                </div>
            </div>

            <p class="text-xs text-gray-400 shrink-0">
                {{ $achievement->achievement_date->translatedFormat('d M Y') }}
            </p>
        </div>
    </a>
    @empty
    <div class="bg-white rounded-2xl p-8 text-center shadow-sm">
        <p class="text-gray-500 font-medium">Belum ada prestasi pada periode ini</p>
        <p class="text-gray-400 text-xs mt-1">Coba ubah filter periode atau tingkat</p>
    </div>
    @endforelse

</div>
@endsection
