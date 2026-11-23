# 📜 PEDOMAN & ATURAN RESMI PENGGUNAAN KOP SURAT
## SMAN 1 GIANYAR (DOSMAN)
*Sesuai Standar Tata Naskah Dinas Terbaru Pemprov Bali & Pergub Bali No. 80 Tahun 2018*

---

## 1. Landasan Hukum & Filosofi Desain

1. **Pergub Bali No. 80 Tahun 2018**:
   - Mewajibkan penempatan **Aksara Bali di atas huruf Latin** pada kop naskah dinas dan identitas resmi instansi di wilayah Provinsi Bali.
   - Menggunakan font Unicode resmi **`Noto Sans Balinese`** (`public/fonts/NotoSansBalinese-Regular.ttf`) dengan shaping OpenType penuh.
2. **Format Terbaru (Tanpa Baris Disdikpora & Garis Tunggal)**:
   - Hanya menggunakan 3 baris Aksara Bali + 5 baris teks Latin dengan font **Arial**.
   - Menggunakan **garis pembatas tunggal hitam tebal** (`3.5px` di web/PNG, `3px` di DomPDF).
3. **Proporsi Simetris & Berwibawa**:
   - Diapit oleh dua lambang resmi Ultra-HD: **Lambang Daerah Provinsi Bali** di sisi kiri (`logo-provinsi.png`) dan **Lambang SMAN 1 Gianyar (Widya Wahana Bhakti)** di sisi kanan (`logo-dosman.png`).

---

## 2. Aturan Batasan Lebar & Tata Letak Logo (Portrait vs Landscape)

### ⚠️ Aturan Kritis Mode Landscape:
> **PANTANGAN UTAMA:** Pada dokumen berorientasi **Landscape (Mendatar / A4 297×210 mm)**, KOP **TIDAK BOLEH melebar 100% mengikuti lebar kertas**. Lebar dibatasi dan dipusatkan agar kedua logo tetap merangkul blok teks tengah secara proporsional.

### Standar Tata Letak:
| Parameter | A4 Portrait (Tegak / 210×297 mm) | A4 Landscape (Mendatar / 297×210 mm) |
|---|---|---|
| **Lebar Maksimal KOP** | `740px` (`2960 × 772 px` saat 4× render) | **`820px` (`3280 × 832 px` saat 4× render)** |
| **Grid Proporsi Kolom** | `96px` (Logo Kiri) - `1fr` (Teks) - `96px` (Logo Kanan) | `104px` (Logo Kiri) - `1fr` (Teks) - `104px` (Logo Kanan) |
| **Jarak Kolom Logo ke Teks** | `12px` | `16px` |
| **Tinggi Maksimal Logo** | `114px` (lebar maks `94px`) | `120px` (lebar maks `100px`) |

---

## 3. Hierarki Tipografi & Susunan Baris Resmi

Font Latin utama: **`Arial, 'Helvetica Neue', Helvetica, sans-serif`**
Font Aksara Bali: **`Noto Sans Balinese`**

| Baris | Konten Teks | Portrait | Landscape | PDF (DomPDF) | Gaya Huruf |
|:---:|---|:---:|:---:|:---:|:---:|
| **1a** | `ᬧᭂᬫᭂᬭᬶᬦ᭄ᬢᬄᬧ᭄ᬭᭀᬧᬶᬦ᭄ᬲᬶᬩᬮᬶ` | `16.5px` | `18px` | `height: 24px` (`aksara-bali-1.png`) | Normal, Noto Sans Balinese |
| **1b** | PEMERINTAH PROVINSI BALI | `16px` | `17.5px` | `13pt` | Regular (`400`), All-Caps |
| **2a** | `᭞ᬏᬲ᭄ ᬏᬫ᭄ ᬅ᭞ ᬦᭂᬕᭂᬭᬶ ᭞᭑᭞ ᬕ᭄ᬬᬜᬃ` | `16.5px` | `18px` | `height: 24px` (`aksara-bali-2.png`) | Normal, Noto Sans Balinese |
| **2b** | **SMA NEGERI 1 GIANYAR** | `22px` | `24px` | `17.5pt` | **Bold (`700`)**, All-Caps, Dominan |
| **3a** | `ᬚᬮᬦ᭄‌ᬭᬢ᭄ᬦᬢᭂᬕᬮ᭄ᬢᬸᬕᬸᬕ᭄ᬬᬜᬃ᭞᭘᭐᭕᭑᭑᭞ᬢᬾᬮᬾᬧᭀᬦ᭄:(᭐᭓᭖᭑)᭙᭔᭓᭐᭓᭔` | `13px` | `14px` | `height: 19px` (`aksara-bali-3.png`) | Normal, Noto Sans Balinese (dengan ZWNJ `U+200C` setelah `ᬦ᭄`) |
| **3b** | Jalan Ratna, Tegal Tugu Gianyar (80511), Telepon : (0361) 943034 | `12px` | `13px` | `9.5pt` | Regular (`400`) |
| **4** | Laman : www.sman1-gianyar.sch.id, Pos-el : sman1.gianyar1963@gmail.com | `11.5px` | `12.5px` | `9pt` | Regular (`400`) |
| **5** | NPSN. 50102079 | `12px` | `13px` | `9.5pt` | Regular (`400`) |

---

## 4. Standar Aset Visual & Arsip

### A. Aset Aktif (`public/img/`)
- Logo Kiri: `public/img/logo-provinsi.png` (PNG-24 Transparan)
- Logo Kanan: `public/img/logo-dosman.png` & `logo-dosman-hd.png` (PNG-24 Transparan, 300 DPI)
- Banner KOP siap pakai (4× scale, 300+ DPI):
  - `public/img/kop-surat-portrait.png` & `kop-surat-portrait-transparan.png`
  - `public/img/kop-surat-landscape.png` & `kop-surat-landscape-transparan.png`
  - `public/img/kop-surat-sman1-gianyar-hires.png`
- Potongan Aksara Bali untuk DomPDF (`300+ DPI` render Chromium/Puppeteer):
  - `public/img/aksara-bali-1.png`, `aksara-bali-2.png`, `aksara-bali-3.png`

### B. Garis Pembatas Bawah
- **Garis Tunggal Hitam Tebal**: `border-top: 3.5px solid #000000;` (web/PNG) dan `3px solid #000000;` (PDF).

### C. Arsip KOP Lama (`public/img/arsip-kop-lama/` & `templates/kop/arsip-kop-lama/`)
- Seluruh file KOP format lama (yang mencantumkan Disdikpora, font Bali Simbar ANSI, Times New Roman, dan garis ganda) **diarsipkan** di folder tersebut tanpa dihapus, dan tetap tersedia di bagian **Arsip KOP Surat Lama** pada halaman Download KOP Admin & Guru.

---

## 5. Panduan Pemanggilan & Regenerasi Aset

1. **Di Template Blade Web (HTML Screen)**:
   ```blade
   <x-kop-surat />
   ```
2. **Di Seluruh Ekspor Laporan PDF (DomPDF)**:
   ```blade
   @include('partials.kop-surat-pdf')
   ```
3. **Regenerasi PNG Resolusi Tinggi (300+ DPI)**:
   ```bash
   node generate-kop.cjs
   ```
   Menggunakan template master `templates/kop/kop-surat.html` dan `templates/kop/NotoSansBalinese-Regular.ttf`.
