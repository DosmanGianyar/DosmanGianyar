@extends('layouts.guru')

@section('title', 'KOP Surat Resmi')
@section('page-title', 'KOP Surat Resmi Sekolah')

@section('content')
<div class="space-y-5 max-w-6xl mx-auto" x-data="{
    activeTab: 'portrait',
    isTransparent: false,
    copied: false,
    portraitImg: '{{ asset('img/kop-surat-portrait.png') }}',
    portraitTransImg: '{{ asset('img/kop-surat-portrait-transparan.png') }}',
    landscapeImg: '{{ asset('img/kop-surat-landscape.png') }}',
    landscapeTransImg: '{{ asset('img/kop-surat-landscape-transparan.png') }}',

    get currentImg() {
        if (this.activeTab === 'portrait') {
            return this.isTransparent ? this.portraitTransImg : this.portraitImg;
        } else {
            return this.isTransparent ? this.landscapeTransImg : this.landscapeImg;
        }
    },

    get downloadUrl() {
        if (this.activeTab === 'portrait') {
            return this.isTransparent ? '{{ route('guru.kop-surat.download', 'portrait-transparan') }}' : '{{ route('guru.kop-surat.download', 'portrait') }}';
        } else {
            return this.isTransparent ? '{{ route('guru.kop-surat.download', 'landscape-transparan') }}' : '{{ route('guru.kop-surat.download', 'landscape') }}';
        }
    },

    async copyImage() {
        try {
            const res = await fetch(this.currentImg);
            const blob = await res.blob();
            await navigator.clipboard.write([
                new ClipboardItem({ 'image/png': blob })
            ]);
            this.copied = true;
            setTimeout(() => { this.copied = false; }, 3000);
        } catch (e) {
            console.error(e);
            alert('Gambar berhasil disalin atau silakan gunakan tombol Download PNG.');
        }
    }
}">

    {{-- ─── 1. Header Card (Tabler Accent Line) ─────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-slate-200 border-t-[3px] border-t-[#206bc4] shadow-2xs p-4 sm:p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        <svg style="width: 14px; height: 14px; min-width: 14px; min-height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Standar Resmi Pemprov Bali &amp; Disdikpora
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                        ✂️ Crop Pas (Hanya Banner KOP)
                    </span>
                </div>
                <h1 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">KOP Surat Resmi SMAN 1 Gianyar</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl leading-relaxed">
                    Halaman unduh KOP surat sekolah format Portrait &amp; Landscape. Gambar dipotong rapi sesuai tinggi KOP sehingga saat disisipkan ke Microsoft Word atau Google Docs tidak menimbulkan ruang kosong ke bawah.
                </p>
            </div>

            {{-- Tabs Orientasi Kertas --}}
            <div class="flex bg-slate-100 p-1 rounded-xl self-start md:self-auto shrink-0 border border-slate-200/60">
                <button type="button"
                    class="px-3.5 py-2 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5"
                    :class="activeTab === 'portrait' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    @click="activeTab = 'portrait'">
                    <svg style="width: 16px; height: 16px; min-width: 16px; min-height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    📄 A4 Portrait
                </button>
                <button type="button"
                    class="px-3.5 py-2 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5"
                    :class="activeTab === 'landscape' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    @click="activeTab = 'landscape'">
                    <svg style="width: 16px; height: 16px; min-width: 16px; min-height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    📜 A4 Landscape
                </button>
            </div>
        </div>

        {{-- Action Bar --}}
        <div class="mt-4 pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            {{-- Toggle Latar Belakang --}}
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-700">Latar:</span>
                <div class="inline-flex rounded-lg border border-slate-200 p-0.5 bg-slate-50">
                    <button type="button"
                        class="px-2.5 py-1 text-xs font-semibold rounded-md transition-all"
                        :class="!isTransparent ? 'bg-white text-blue-600 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-900'"
                        @click="isTransparent = false">
                        ⚪ Latar Putih
                    </button>
                    <button type="button"
                        class="px-2.5 py-1 text-xs font-semibold rounded-md transition-all"
                        :class="isTransparent ? 'bg-white text-blue-600 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-900'"
                        @click="isTransparent = true">
                        ✨ Transparan (PNG)
                    </button>
                </div>
            </div>

            {{-- Download & Copy Action Buttons --}}
            <div class="flex flex-wrap items-center gap-2">
                {{-- Copy Image to Clipboard --}}
                <button type="button" @click="copyImage()"
                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-all shadow-xs active:scale-95">
                    <template x-if="!copied">
                        <span class="inline-flex items-center gap-1.5">
                            <svg style="width: 16px; height: 16px; min-width: 16px; min-height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                            Salin Gambar (Ctrl+V)
                        </span>
                    </template>
                    <template x-if="copied">
                        <span class="inline-flex items-center gap-1.5 text-white">
                            <svg style="width: 16px; height: 16px; min-width: 16px; min-height: 16px;" class="text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Tersalin ke Clipboard!
                        </span>
                    </template>
                </button>

                {{-- Direct File Download --}}
                <a :href="downloadUrl"
                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-[#206bc4] hover:bg-[#1a569d] transition-all shadow-xs active:scale-95">
                    <svg style="width: 16px; height: 16px; min-width: 16px; min-height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span x-text="isTransparent ? 'Download PNG Transparan' : 'Download PNG Latar Putih'"></span>
                </a>
            </div>
        </div>
    </div>

    {{-- ─── 2. Pratinjau KOP Surat (Stage Card) ─────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-4 sm:p-5">
        <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-800" x-text="activeTab === 'portrait' ? 'Pratinjau KOP Portrait (Lebar A4 210mm)' : 'Pratinjau KOP Landscape (Lebar A4 297mm)'"></span>
                <span class="text-[11px] font-mono text-slate-400 bg-slate-50 px-2 py-0.5 rounded border border-slate-200"
                    x-text="activeTab === 'portrait' ? '2960 × 652 px (Ultra HD)' : '3280 × 712 px (Ultra HD)'">
                </span>
            </div>
            <span class="text-[11px] font-medium text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                ✓ Siap Cetak Resolusi Tinggi
            </span>
        </div>

        {{-- Preview Container with Checkerboard if transparent --}}
        <div class="w-full overflow-x-auto bg-slate-100/70 border border-slate-200/80 rounded-xl p-3 sm:p-6 flex items-center justify-center min-h-[180px]">
            <div class="bg-white rounded-lg p-2 sm:p-3 shadow-md border border-slate-200/60 max-w-full"
                :class="isTransparent ? 'bg-[linear-gradient(45deg,#e2e8f0_25%,transparent_25%),linear-gradient(-45deg,#e2e8f0_25%,transparent_25%),linear-gradient(45deg,transparent_75%,#e2e8f0_75%),linear-gradient(-45deg,transparent_75%,#e2e8f0_75%)] [background-size:16px_16px] [background-position:0_0,0_8px,8px_-8px,-8px_0px]' : ''">
                <img :src="currentImg" alt="KOP Surat Resmi SMAN 1 Gianyar" class="max-w-full h-auto block rounded mx-auto" style="min-width: 280px; max-width: 820px;">
            </div>
        </div>
    </div>

    {{-- ─── 3. Panduan Penggunaan di Microsoft Word / Dokumen ───────────────────── --}}
    <div class="bg-gradient-to-br from-blue-50/70 to-indigo-50/50 rounded-xl border border-blue-200/70 p-4 sm:p-5">
        <h3 class="text-xs sm:text-sm font-bold text-blue-950 flex items-center gap-2 mb-3">
            <svg style="width: 18px; height: 18px; min-width: 18px; min-height: 18px;" class="text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Panduan Memasang KOP di Microsoft Word &amp; Google Docs
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs text-slate-700">
            <div class="bg-white p-3.5 rounded-xl border border-blue-100 shadow-2xs">
                <div class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 font-black flex items-center justify-center text-xs mb-2">1</div>
                <p class="font-bold text-slate-900 mb-1">Metode Praktis (Copy-Paste)</p>
                <p class="text-slate-600 leading-relaxed">
                    Klik tombol <strong>"Salin Gambar"</strong> di atas. Buka Word atau Google Docs, lalu tekan <strong>Ctrl + V</strong> (atau klik kanan &gt; Paste). Gambar KOP langsung menempel sempurna.
                </p>
            </div>

            <div class="bg-white p-3.5 rounded-xl border border-blue-100 shadow-2xs">
                <div class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 font-black flex items-center justify-center text-xs mb-2">2</div>
                <p class="font-bold text-slate-900 mb-1">Pengaturan Gambar di Word</p>
                <p class="text-slate-600 leading-relaxed">
                    Klik kanan gambar &gt; pilih <strong>Wrap Text</strong> &gt; pilih <strong>Top and Bottom</strong> atau <strong>In Line with Text</strong> agar gambar KOP tetap rapi dan tidak mengganggu teks surat di bawahnya.
                </p>
            </div>

            <div class="bg-white p-3.5 rounded-xl border border-blue-100 shadow-2xs">
                <div class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 font-black flex items-center justify-center text-xs mb-2">3</div>
                <p class="font-bold text-slate-900 mb-1">Pengaturan Margin Dokumen</p>
                <p class="text-slate-600 leading-relaxed">
                    Pada Word, atur di tab <em>Layout &gt; Margins &gt; Custom Margins</em>: <strong>Top: 1.2 cm</strong>, <strong>Left: 1.5 cm</strong>, <strong>Right: 1.5 cm</strong> untuk hasil cetak yang proporsional.
                </p>
            </div>
        </div>
    </div>

</div>
@endsection

