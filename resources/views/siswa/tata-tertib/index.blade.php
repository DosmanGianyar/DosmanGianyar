@extends('layouts.siswa')

@section('title', 'Tata Tertib Sekolah')
@section('page-title', 'Tata Tertib Sekolah')

@section('content')
<div class="max-w-4xl mx-auto space-y-5">

    {{-- Banner Header --}}
    <div class="bg-gradient-to-r from-blue-700 via-indigo-800 to-slate-900 rounded-3xl p-6 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
            <svg class="w-64 h-64 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
        </div>
        <div class="relative z-10 space-y-3">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur-md rounded-full text-xs font-medium text-blue-200 border border-white/20">
                <svg class="w-4 h-4 text-amber-300" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                SMA Negeri 1 Gianyar
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Intisari Tata Tertib Peserta Didik</h1>
            <p class="text-blue-100 text-sm max-w-xl leading-relaxed">
                Panduan resmi hak, kewajiban, pakaian seragam, dan tata perilaku peserta didik SMAN 1 Gianyar.
            </p>
            <div class="pt-2 flex flex-wrap gap-3">
                <a href="{{ $pdfUrl }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold text-xs md:text-sm rounded-xl shadow-md transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Lihat / Unduh Document PDF Resmi
                </a>
            </div>
        </div>
    </div>

    {{-- Filter & Search --}}
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3">
        <form method="GET" action="{{ route('siswa.tata-tertib') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari aturan, misal: seragam, rambut, HP, bolos..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>
            @if($selectedCategory)
                <input type="hidden" name="category" value="{{ $selectedCategory }}">
            @endif
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-xl transition-colors">
                Cari
            </button>
            @if($search || $selectedCategory)
                <a href="{{ route('siswa.tata-tertib') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium text-sm rounded-xl text-center transition-colors">
                    Reset
                </a>
            @endif
        </form>

        {{-- Category Pills --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none text-xs">
            <a href="{{ route('siswa.tata-tertib', array_filter(['q' => $search])) }}"
               class="px-3.5 py-1.5 rounded-full font-medium whitespace-nowrap transition-colors {{ !$selectedCategory ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua Kategori
            </a>
            @foreach($categories as $catKey => $catLabel)
                <a href="{{ route('siswa.tata-tertib', array_filter(['category' => $catKey, 'q' => $search])) }}"
                   class="px-3.5 py-1.5 rounded-full font-medium whitespace-nowrap transition-colors {{ $selectedCategory === $catKey ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ $catLabel }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Regulations Content List --}}
    @if($regulations->isEmpty())
        <div class="bg-white rounded-2xl p-10 text-center border border-slate-100 shadow-sm space-y-3">
            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-slate-600 font-semibold text-base">Peraturan Tidak Ditemukan</p>
            <p class="text-slate-400 text-xs max-w-md mx-auto">
                Coba gunakan kata kunci pencarian lain atau pilih kategori berbeda.
            </p>
        </div>
    @else
        @foreach($categories as $catKey => $catLabel)
            @if(isset($regulations[$catKey]) && $regulations[$catKey]->isNotEmpty())
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="bg-slate-50 px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="font-bold text-slate-800 text-base flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                            {{ $catLabel }}
                        </h2>
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                            {{ $regulations[$catKey]->count() }} Aturan
                        </span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach($regulations[$catKey] as $index => $reg)
                            <div class="p-4 md:p-5 hover:bg-slate-50/60 transition-colors space-y-1.5">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="font-semibold text-slate-900 text-sm md:text-base leading-snug flex items-center gap-2">
                                        <span class="text-xs font-bold text-blue-600 bg-blue-50 w-6 h-6 rounded-full inline-flex items-center justify-center shrink-0">
                                            {{ $index + 1 }}
                                        </span>
                                        {{ $reg->title }}
                                    </h3>
                                </div>
                                <div class="pl-8 text-slate-600 text-xs md:text-sm leading-relaxed whitespace-pre-line">
                                    {{ $reg->content }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    @endif

</div>
@endsection
