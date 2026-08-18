@extends('layouts.siswa')
@section('title', 'Katalog Buku Perpustakaan')
@section('page-title', 'Katalog Buku Perpustakaan')

@section('content')

{{-- ─── Header Banner ────────────────────────────────────────────────── --}}
<div class="bg-gradient-to-r from-indigo-700 via-purple-700 to-blue-800 rounded-3xl p-6 text-white shadow-lg relative overflow-hidden mb-6">
    <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
    <div class="flex items-start justify-between gap-4 relative z-10">
        <div class="flex items-center gap-3.5">
            <div class="bg-white/20 p-3.5 rounded-2xl shrink-0 backdrop-blur-md border border-white/20">
                <svg class="w-7 h-7 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <span class="bg-white/20 text-yellow-200 text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-white/20">E-KATALOG BUKU</span>
                <h2 class="text-xl font-extrabold leading-tight mt-1">Koleksi Buku Perpustakaan</h2>
                <p class="text-xs text-indigo-100 mt-0.5">SMA Negeri 1 Gianyar · Jelajahi Koleksi, Lokasi Rak, & Cek Ketersediaan Stok Buku</p>
            </div>
        </div>
        <a href="{{ route('siswa.library.clearance-card') }}" target="_blank"
            class="hidden md:inline-flex items-center gap-1.5 px-4 py-2 bg-yellow-400 hover:bg-yellow-300 text-gray-900 font-bold rounded-2xl text-xs transition-colors shadow">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Kartu Bebas Perpustakaan
        </a>
    </div>
</div>

{{-- ─── Navigation Tabs ──────────────────────────────────────────────── --}}
<div class="flex items-center gap-2 mb-6 border-b border-gray-200 pb-3 overflow-x-auto">
    <a href="{{ route('siswa.library.index') }}"
        class="px-4 py-2 text-xs font-bold rounded-xl transition-all bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 flex items-center gap-2 shrink-0">
        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        Peminjaman Saya
    </a>
    <a href="{{ route('siswa.library.visit') }}"
        class="px-4 py-2 text-xs font-bold rounded-xl transition-all bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 flex items-center gap-2 shrink-0">
        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Kunjungan Perpustakaan (Baca)
    </a>
    <a href="{{ route('siswa.library.catalog') }}"
        class="px-4 py-2 text-xs font-bold rounded-xl transition-all shadow-sm border border-indigo-600 bg-indigo-600 text-white flex items-center gap-2 shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        Katalog Buku (E-Katalog)
    </a>
</div>

{{-- ─── Search & Filter Controls ────────────────────────────────────── --}}
<div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 mb-6 space-y-4">
    <form method="GET" action="{{ route('siswa.library.catalog') }}" class="flex flex-col md:flex-row gap-3">
        <div class="relative flex-1">
            <svg class="w-5 h-5 absolute left-3.5 top-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ $search }}"
                placeholder="Cari Judul Buku, Pengarang, ISBN, atau Kode Buku..."
                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
        </div>
        <input type="hidden" name="category" value="{{ $category ?: 'all' }}">
        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition">
            Cari Buku
        </button>
        @if($search || ($category && $category !== 'all'))
            <a href="{{ route('siswa.library.catalog') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold text-xs rounded-xl transition text-center">
                Reset Filter
            </a>
        @endif
    </form>

    {{-- Category Pills --}}
    <div class="flex items-center gap-2 overflow-x-auto pt-1 pb-1">
        <a href="{{ route('siswa.library.catalog', ['search' => $search, 'category' => 'all']) }}"
            class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all border whitespace-nowrap {{ !$category || $category === 'all' ? 'bg-indigo-100 text-indigo-800 border-indigo-300' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100' }}">
            Semua Kategori
        </a>
        @foreach($categories as $catKey => $catLabel)
            <a href="{{ route('siswa.library.catalog', ['search' => $search, 'category' => $catKey]) }}"
                class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all border whitespace-nowrap {{ $category === $catKey ? 'bg-indigo-100 text-indigo-800 border-indigo-300' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100' }}">
                {{ $catLabel }}
            </a>
        @endforeach
    </div>
</div>

{{-- ─── Grid Cards Sampul Buku ──────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
    @forelse($books as $book)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col justify-between hover:shadow-md transition-all duration-300 group">
            <div class="relative bg-gray-100 aspect-[3/4] overflow-hidden">
                <img src="{{ $book->cover_url }}" alt="{{ $book->title }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                
                {{-- Category Badge --}}
                <span class="absolute top-2 left-2 bg-black/60 backdrop-blur-md text-white text-[9px] font-bold px-2 py-0.5 rounded-md border border-white/20">
                    {{ $book->category }}
                </span>

                {{-- Stock Status Badge --}}
                <span class="absolute bottom-2 right-2 text-[9px] font-bold px-2 py-0.5 rounded-md shadow {{ $book->available_stock > 0 ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white' }}">
                    {{ $book->available_stock > 0 ? 'Tersedia (' . $book->available_stock . ')' : 'Dipinjam Semua' }}
                </span>
            </div>

            <div class="p-3 flex-1 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-xs text-gray-900 line-clamp-2 group-hover:text-indigo-600 transition-colors" title="{{ $book->title }}">
                        {{ $book->title }}
                    </h3>
                    <p class="text-[11px] text-gray-500 font-medium mt-1 truncate">
                        {{ $book->author ?: 'Penulis tak diketahui' }}
                    </p>
                </div>

                <div class="mt-3 pt-2 border-t border-gray-100 space-y-2">
                    <div class="flex items-center justify-between text-[10px] text-gray-500">
                        <span class="font-mono bg-gray-100 px-1.5 py-0.5 rounded">{{ $book->book_code }}</span>
                        <span class="font-semibold text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded">
                            📍 {{ $book->shelf_location ?: 'Rak Umum' }}
                        </span>
                    </div>

                    <button type="button"
                        onclick="openBookDetailModal({{ json_encode([
                            'title' => $book->title,
                            'author' => $book->author ?: '—',
                            'publisher' => $book->publisher ?: '—',
                            'publish_year' => $book->publish_year ?: '—',
                            'category' => $book->category,
                            'isbn' => $book->isbn ?: '—',
                            'book_code' => $book->book_code,
                            'shelf_location' => $book->shelf_location ?: '—',
                            'total_stock' => $book->total_stock,
                            'borrowed_count' => $book->borrowed_count,
                            'available_stock' => $book->available_stock,
                            'cover_url' => $book->cover_url,
                            'description' => $book->description ?: 'Belum ada deskripsi atau sinopsis untuk buku ini.',
                        ]) }})"
                        class="w-full py-1.5 bg-gray-50 hover:bg-indigo-50 text-indigo-700 font-bold text-[11px] rounded-xl border border-gray-200 hover:border-indigo-200 transition text-center">
                        Lihat Detail
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full bg-white p-12 rounded-2xl border border-gray-200 text-center space-y-3">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto text-gray-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <h3 class="font-bold text-sm text-gray-800">Tidak ada buku ditemukan</h3>
            <p class="text-xs text-gray-500 max-w-md mx-auto">
                Coba ubah kata kunci pencarian atau pilih kategori lain untuk menemukan koleksi buku perpustakaan.
            </p>
        </div>
    @endforelse
</div>

{{-- ─── Modal Detail & Sinopsis Buku ────────────────────────────────── --}}
<div id="bookDetailModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-5 transform transition-all relative overflow-hidden max-h-[90vh] overflow-y-auto">
        <button type="button" onclick="closeBookDetailModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 bg-gray-100 p-2 rounded-full transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <div class="flex flex-col sm:flex-row gap-5 items-start">
            <div class="w-32 shrink-0 aspect-[3/4] bg-gray-100 rounded-2xl overflow-hidden shadow-md border border-gray-200 mx-auto sm:mx-0">
                <img id="modalCover" src="" alt="Cover Buku" class="w-full h-full object-cover">
            </div>
            <div class="flex-1 space-y-2 text-center sm:text-left">
                <span id="modalCategory" class="inline-block bg-indigo-100 text-indigo-800 text-[10px] font-extrabold px-2.5 py-0.5 rounded-md">
                    Kategori
                </span>
                <h3 id="modalTitle" class="text-base font-black text-gray-900 leading-snug">
                    Judul Buku
                </h3>
                <p id="modalAuthor" class="text-xs text-gray-600 font-medium">
                    Penulis
                </p>

                <div class="pt-2 grid grid-cols-2 gap-2 text-[11px] text-gray-700 bg-gray-50 p-3 rounded-xl border border-gray-100">
                    <div><span class="text-gray-400 block text-[9px] uppercase font-bold">Kode Buku</span><strong id="modalCode">—</strong></div>
                    <div><span class="text-gray-400 block text-[9px] uppercase font-bold">ISBN</span><strong id="modalIsbn">—</strong></div>
                    <div><span class="text-gray-400 block text-[9px] uppercase font-bold">Penerbit</span><strong id="modalPublisher">—</strong></div>
                    <div><span class="text-gray-400 block text-[9px] uppercase font-bold">Lokasi Rak</span><strong id="modalShelf" class="text-indigo-600">—</strong></div>
                </div>
            </div>
        </div>

        {{-- Stock Info Badges --}}
        <div class="grid grid-cols-3 gap-2 text-center text-xs">
            <div class="p-2.5 bg-blue-50 border border-blue-200 rounded-xl">
                <span class="text-blue-600 font-bold block text-[9px] uppercase">Total Eksemplar</span>
                <span id="modalTotalStock" class="font-extrabold text-blue-900 text-sm">0</span>
            </div>
            <div class="p-2.5 bg-amber-50 border border-amber-200 rounded-xl">
                <span class="text-amber-600 font-bold block text-[9px] uppercase">Sedang Dipinjam</span>
                <span id="modalBorrowedCount" class="font-extrabold text-amber-900 text-sm">0</span>
            </div>
            <div id="modalStockStatusContainer" class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl">
                <span class="text-emerald-600 font-bold block text-[9px] uppercase">Stok Tersedia</span>
                <span id="modalAvailableStock" class="font-extrabold text-emerald-900 text-sm">0</span>
            </div>
        </div>

        {{-- Description / Synopsis --}}
        <div class="space-y-1">
            <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wide">Sinopsis / Deskripsi Buku:</h4>
            <div id="modalDescription" class="text-xs text-gray-600 leading-relaxed bg-gray-50/70 p-3.5 rounded-xl border border-gray-100 whitespace-pre-line">
                Deskripsi...
            </div>
        </div>

        <div class="pt-2 flex justify-end">
            <button type="button" onclick="closeBookDetailModal()" class="px-5 py-2 bg-gray-900 hover:bg-gray-800 text-white font-bold text-xs rounded-xl transition">
                Tutup Detail
            </button>
        </div>
    </div>
</div>

<script>
    function openBookDetailModal(data) {
        document.getElementById('modalCover').src = data.cover_url;
        document.getElementById('modalTitle').innerText = data.title;
        document.getElementById('modalAuthor').innerText = 'Penulis: ' + data.author + ' (Th. ' + data.publish_year + ')';
        document.getElementById('modalCategory').innerText = data.category;
        document.getElementById('modalCode').innerText = data.book_code;
        document.getElementById('modalIsbn').innerText = data.isbn;
        document.getElementById('modalPublisher').innerText = data.publisher;
        document.getElementById('modalShelf').innerText = data.shelf_location;
        document.getElementById('modalTotalStock').innerText = data.total_stock + ' Buku';
        document.getElementById('modalBorrowedCount').innerText = data.borrowed_count + ' Buku';
        document.getElementById('modalAvailableStock').innerText = data.available_stock + ' Buku';
        document.getElementById('modalDescription').innerText = data.description;

        const modal = document.getElementById('bookDetailModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeBookDetailModal() {
        const modal = document.getElementById('bookDetailModal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }
</script>

@endsection
