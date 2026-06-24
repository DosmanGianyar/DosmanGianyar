@extends('layouts.guru')
@section('title', $contextMeta['label'])
@section('page-title', $contextMeta['label'])

@section('content')
<div class="max-w-xl mx-auto space-y-4">

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-3">
        <ul class="text-sm text-red-700 space-y-1">
            @foreach($errors->all() as $error)<li>• {{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    @php
        $borderColor = match(true) {
            str_contains($context, 'prestasi'), $context === 'akademik', $context === 'lomba' => 'border-green-400',
            $context === 'kelas'  => 'border-yellow-400',
            default               => 'border-red-400',
        };
        $badgeBg = match(true) {
            $context === 'akademik', $context === 'lainnya_prestasi' => 'bg-green-50 text-green-700',
            $context === 'lomba'    => 'bg-blue-50 text-blue-700',
            $context === 'kelas'   => 'bg-yellow-50 text-yellow-700',
            default                => 'bg-red-50 text-red-700',
        };
    @endphp

    <form action="{{ route('guru.conduct.store') }}" method="POST" enctype="multipart/form-data"
        class="bg-white rounded-2xl shadow-sm border-2 {{ $borderColor }} p-5 space-y-5">
        @csrf
        <input type="hidden" name="context" value="{{ $context }}">

        {{-- Context badge --}}
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $badgeBg }}">
                {{ $contextMeta['label'] }}
            </span>
            <span class="text-xs text-gray-400">{{ $contextMeta['desc'] }}</span>
        </div>

        {{-- Pilih Kelas & Siswa --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Siswa</label>
            <div class="flex gap-2 mb-2">
                <select id="class-filter" onchange="filterStudents()"
                    class="flex-1 px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">— Semua Kelas —</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <select name="student_id" id="student-select" required
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">— Pilih Siswa —</option>
                @foreach($classes as $class)
                    @foreach($class->students->sortBy('name') as $student)
                    <option value="{{ $student->id }}"
                        data-class="{{ $class->id }}"
                        {{ old('student_id', $preselectedStudentId) == $student->id ? 'selected' : '' }}>
                        {{ $student->name }} ({{ $class->name }})
                    </option>
                    @endforeach
                @endforeach
            </select>
            @error('student_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if($isLainnya)
        {{-- Input bebas untuk Lainnya --}}
        <div>
            <label for="note" class="block text-sm font-medium text-gray-700 mb-1">
                Deskripsi <span class="text-red-500">*</span>
            </label>
            <textarea id="note" name="note" rows="3" required
                placeholder="Tuliskan deskripsi lengkap kejadian atau pencapaian..."
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('note') }}</textarea>
            @error('note')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        @else
        {{-- Pilih Kategori dari daftar --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
            @if($categories->isEmpty())
            <p class="text-sm text-gray-400 py-3 text-center">
                Belum ada kategori untuk konteks ini.
                <br><span class="text-xs">Tambahkan kategori melalui panel admin.</span>
            </p>
            @else
            <div class="space-y-2" id="category-list">
                @foreach($categories as $cat)
                <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all
                    {{ old('category_id') == $cat->id ? $borderColor . ' ' . $badgeBg : 'border-gray-100 hover:border-gray-200' }}">
                    <input type="radio" name="category_id" value="{{ $cat->id }}" class="sr-only"
                        {{ old('category_id') == $cat->id ? 'checked' : '' }}
                        onchange="highlightCategory(this)">
                    <span class="text-sm font-medium text-gray-700">{{ $cat->name }}</span>
                </label>
                @endforeach
            </div>
            @endif
            @error('category_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Catatan (opsional untuk mode kategori) --}}
        <div>
            <label for="note" class="block text-sm font-medium text-gray-700 mb-1">
                Catatan <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <textarea id="note" name="note" rows="2"
                placeholder="Deskripsi singkat kejadian..."
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('note') }}</textarea>
        </div>
        @endif

        {{-- Foto Bukti --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Foto Bukti <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <label for="photo"
                class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all">
                <div id="photo-placeholder" class="flex flex-col items-center gap-1">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-xs text-gray-400">Upload / ambil foto</span>
                </div>
                <p id="photo-name" class="hidden text-xs text-blue-600 font-medium px-2 text-center"></p>
                <input type="file" id="photo" name="photo" accept="image/*" capture="environment"
                    class="sr-only" onchange="showPhotoName(this)">
            </label>
        </div>

        <div class="flex gap-3 pt-1">
            <a href="{{ route('guru.conduct.choose') }}"
                class="flex-1 py-3 text-center rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">
                Batal
            </a>
            <button type="submit"
                class="flex-1 py-3 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">
                Simpan
            </button>
        </div>
    </form>
</div>

<script>
function filterStudents() {
    const classId = document.getElementById('class-filter').value;
    const select  = document.getElementById('student-select');
    [...select.options].forEach(opt => {
        if (!opt.value) return;
        opt.style.display = (!classId || opt.dataset.class === classId) ? '' : 'none';
    });
    if (select.selectedOptions[0]?.style.display === 'none') select.value = '';
}

@if(!$isLainnya)
function highlightCategory(radio) {
    document.querySelectorAll('#category-list label').forEach(l => {
        l.className = l.className
            .replace(/border-(green|blue|yellow|red)-400/g, 'border-gray-100')
            .replace(/bg-(green|blue|yellow|red)-50\s*/g, '')
            .replace(/text-(green|blue|yellow|red)-700\s*/g, '');
        if (!l.className.includes('hover:border-gray-200')) {
            l.className += ' hover:border-gray-200';
        }
    });
    const label = radio.closest('label');
    label.classList.remove('border-gray-100', 'hover:border-gray-200');
    @switch($context)
        @case('akademik') label.classList.add('border-green-400',  'bg-green-50',  'text-green-700');  @break
        @case('lomba')    label.classList.add('border-blue-400',   'bg-blue-50',   'text-blue-700');   @break
        @case('kelas')    label.classList.add('border-yellow-400', 'bg-yellow-50', 'text-yellow-700'); @break
        @case('sidak')    label.classList.add('border-red-400',    'bg-red-50',    'text-red-700');    @break
    @endswitch
}
@endif

function showPhotoName(input) {
    if (input.files?.[0]) {
        document.getElementById('photo-placeholder').classList.add('hidden');
        const n = document.getElementById('photo-name');
        n.classList.remove('hidden');
        n.textContent = input.files[0].name;
    }
}
</script>
@endsection
