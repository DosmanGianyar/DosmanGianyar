<x-filament-panels::page>
    <style>
        /* ═══════════════════════════════════════════════════════════════════════════
           TABLER.IO STYLING — DOWNLOAD KOP SURAT RESMI
           ═══════════════════════════════════════════════════════════════════════════ */
        .kop-page-wrap {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
        }

        .kop-card {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-top: 3px solid #206bc4 !important; /* Tabler Top Accent */
            border-radius: 0.75rem !important;
            padding: 1.25rem 1.5rem !important;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05) !important;
        }

        .kop-tabs {
            display: flex;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 8px;
            gap: 4px;
            width: fit-content;
        }

        .kop-tab-btn {
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            background: transparent;
            color: #64748b;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .kop-tab-btn:hover {
            color: #0f172a;
        }

        .kop-tab-btn.active {
            background: #ffffff;
            color: #206bc4;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .kop-btn {
            padding: 9px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #1e293b;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .kop-btn:hover {
            background: #f8fafc;
            border-color: #94a3b8;
            color: #0f172a;
        }

        .kop-btn-primary {
            background: #206bc4 !important;
            border-color: #206bc4 !important;
            color: #ffffff !important;
        }

        .kop-btn-primary:hover {
            background: #17549c !important;
            border-color: #17549c !important;
        }

        .kop-btn-emerald {
            background: #059669 !important;
            border-color: #059669 !important;
            color: #ffffff !important;
        }

        .kop-btn-emerald:hover {
            background: #047857 !important;
            border-color: #047857 !important;
        }

        .kop-btn-indigo {
            background: #4f46e5 !important;
            border-color: #4f46e5 !important;
            color: #ffffff !important;
        }

        .kop-btn-indigo:hover {
            background: #4338ca !important;
            border-color: #4338ca !important;
        }

        .kop-preview-container {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow-x: auto;
            position: relative;
        }

        .kop-preview-stage {
            background: #ffffff;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            border-radius: 4px;
            padding: 16px;
            max-width: 100%;
            transition: all 0.2s ease;
        }

        .kop-preview-stage.is-transparan {
            background-image: 
                linear-gradient(45deg, #e2e8f0 25%, transparent 25%), 
                linear-gradient(-45deg, #e2e8f0 25%, transparent 25%), 
                linear-gradient(45deg, transparent 75%, #e2e8f0 75%), 
                linear-gradient(-45deg, transparent 75%, #e2e8f0 75%);
            background-size: 16px 16px;
            background-position: 0 0, 0 8px, 8px -8px, -8px 0px;
        }

        .kop-preview-img {
            display: block;
            width: 100%;
            height: auto;
            max-width: 820px;
            border-radius: 2px;
        }

        .guide-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 16px 20px;
        }

        .badge-tight {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 9999px;
            background: #dbeafe;
            color: #1e40af;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* ─── KUNCI UKURAN SVG AGAR TIDAK MELEDAK DI FILAMENT ─── */
        .kop-page-wrap svg {
            width: 16px !important;
            height: 16px !important;
            min-width: 16px !important;
            min-height: 16px !important;
            max-width: 16px !important;
            max-height: 16px !important;
            display: inline-block !important;
            vertical-align: middle !important;
            flex-shrink: 0 !important;
        }

        .badge-tight svg {
            width: 14px !important;
            height: 14px !important;
            min-width: 14px !important;
            min-height: 14px !important;
            max-width: 14px !important;
            max-height: 14px !important;
        }

        .guide-box svg {
            width: 18px !important;
            height: 18px !important;
            min-width: 18px !important;
            min-height: 18px !important;
            max-width: 18px !important;
            max-height: 18px !important;
        }
    </style>

    <div class="kop-page-wrap" x-data="{
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
                alert('Gambar berhasil disalin atau silakan klik tombol Download PNG.');
            }
        }
    }">
        {{-- Header & Action Controls --}}
        <div class="kop-card">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="badge-tight">
                            <svg style="width: 14px; height: 14px; min-width: 14px; min-height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Standar Baku Resmi SMAN 1 Gianyar
                        </span>
                        <span class="badge-tight" style="background: #fef3c7; color: #92400e;">
                            ✂️ Ukuran Pas (Hanya KOP Saja)
                        </span>
                    </div>
                    <h2 class="text-xl font-bold text-slate-800 tracking-tight">KOP Surat Resmi Sekolah</h2>
                    <p class="text-xs text-slate-500 mt-1 max-w-2xl">
                        Gambar KOP surat resmi ini sudah dipotong pas dengan ukuran header (tanpa ruang kosong di bawah garis pembatas ganda). Guru & pegawai dapat langsung menyalin atau mengunduh untuk disisipkan ke Microsoft Word, Google Docs, atau aplikasi administrasi lainnya.
                    </p>
                </div>

                {{-- Tabs Orientasi --}}
                <div class="kop-tabs self-start md:self-auto">
                    <button class="kop-tab-btn" :class="activeTab === 'portrait' ? 'active' : ''" @click="activeTab = 'portrait'">
                        <svg style="width: 16px; height: 16px; min-width: 16px; min-height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        📄 A4 Portrait
                    </button>
                    <button class="kop-tab-btn" :class="activeTab === 'landscape' ? 'active' : ''" @click="activeTab = 'landscape'">
                        <svg style="width: 16px; height: 16px; min-width: 16px; min-height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        📜 A4 Landscape
                    </button>
                </div>
            </div>

            {{-- Action Toolbar --}}
            <div class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                {{-- Toggle Background --}}
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-600">Varian Latar:</label>
                    <div class="inline-flex rounded-lg border border-slate-200 p-0.5 bg-slate-50">
                        <button type="button" 
                            class="px-2.5 py-1 text-xs font-semibold rounded-md transition-all"
                            :class="!isTransparent ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-500 hover:text-slate-900'"
                            @click="isTransparent = false">
                            ⚪ Latar Putih (Standar)
                        </button>
                        <button type="button" 
                            class="px-2.5 py-1 text-xs font-semibold rounded-md transition-all"
                            :class="isTransparent ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-500 hover:text-slate-900'"
                            @click="isTransparent = true">
                            ✨ Transparan (PNG)
                        </button>
                    </div>
                </div>

                {{-- Action Download & Copy Buttons --}}
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Copy to Clipboard Button --}}
                    <button type="button" @click="copyImage()" class="kop-btn kop-btn-indigo">
                        <template x-if="!copied">
                            <span class="inline-flex items-center gap-1.5">
                                <svg style="width: 16px; height: 16px; min-width: 16px; min-height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                📋 Salin Gambar (Ctrl+V di Word)
                            </span>
                        </template>
                        <template x-if="copied">
                            <span class="inline-flex items-center gap-1.5 text-white">
                                <svg style="width: 16px; height: 16px; min-width: 16px; min-height: 16px;" class="text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                ✅ Tersalin ke Clipboard!
                            </span>
                        </template>
                    </button>

                    {{-- Download PNG Button --}}
                    <a :href="downloadUrl" class="kop-btn kop-btn-primary">
                        <svg style="width: 16px; height: 16px; min-width: 16px; min-height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span x-text="isTransparent ? 'Download PNG Transparan' : 'Download PNG Latar Putih'"></span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Preview Stage Card --}}
        <div class="kop-card">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-sm text-slate-800" x-text="activeTab === 'portrait' ? 'Pratinjau KOP Surat (A4 Portrait)' : 'Pratinjau KOP Surat (A4 Landscape)'"></span>
                    <span class="text-xs text-slate-400 font-mono" x-text="activeTab === 'portrait' ? 'Resolusi: 2960 × 652 px (Ultra HD 300+ DPI)' : 'Resolusi: 3280 × 712 px (Ultra HD 300+ DPI)'"></span>
                </div>
                <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-md">
                    Rasio pas tanpa sisa margin bawah
                </span>
            </div>

            <div class="kop-preview-container">
                <div class="kop-preview-stage" :class="isTransparent ? 'is-transparan' : ''">
                    <img :src="currentImg" alt="KOP Surat Resmi SMAN 1 Gianyar" class="kop-preview-img">
                </div>
            </div>
        </div>

        {{-- Panduan Pemasangan di Microsoft Word --}}
        <div class="guide-box">
            <h3 class="text-sm font-bold text-blue-900 flex items-center gap-2 mb-2">
                <svg style="width: 18px; height: 18px; min-width: 18px; min-height: 18px;" class="text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                💡 Panduan Praktis Menempelkan KOP di Microsoft Word / Google Docs
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-slate-700 mt-3">
                <div class="bg-white/80 p-3 rounded-lg border border-blue-200/60 shadow-2xs">
                    <p class="font-bold text-blue-900 mb-1">1. Cara Cepat (Copy-Paste)</p>
                    <p>Klik tombol <strong>"Salin Gambar"</strong> di atas, lalu buka dokumen Word Anda dan tekan tombol keyboard <strong>Ctrl + V</strong> (atau Command + V di Mac). Gambar KOP langsung muncul di dokumen.</p>
                </div>
                <div class="bg-white/80 p-3 rounded-lg border border-blue-200/60 shadow-2xs">
                    <p class="font-bold text-blue-900 mb-1">2. Pengaturan Layout Gambar</p>
                    <p>Klik kanan gambar KOP di Word &gt; pilih <strong>Wrap Text</strong> &gt; pilih <strong>Top and Bottom</strong> atau <strong>In Line with Text</strong>. Gambar tidak akan menggeser paragraf surat di bawahnya.</p>
                </div>
                <div class="bg-white/80 p-3 rounded-lg border border-blue-200/60 shadow-2xs">
                    <p class="font-bold text-blue-900 mb-1">3. Rekomendasi Margin Dokumen</p>
                    <p>Pada menu <em>Layout &gt; Margins</em> di Word, atur <strong>Margin Atas: 1.2 cm</strong>, <strong>Kiri: 1.5 cm</strong>, dan <strong>Kanan: 1.5 cm</strong> untuk hasil cetak yang simetris dan proporsional.</p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>

