# Histori Pengerjaan & Dokumentasi Teknis KOP Surat SMAN 1 Gianyar

Dokumen ini mencatat seluruh histori pengerjaan, spesifikasi teknis, pengarsipan KOP lama, dan pembaruan KOP terbaru pada project **SIMS** (`C:\Users\dosman\Projects\sims`).

---

## 1. Pembaruan Terbaru (23 November 2026) — KOP Resmi Tanpa Disdikpora

| Komponen | Pembaruan | Keterangan |
|---|---|---|
| **Struktur Teks** | Hapus Disdikpora | Baris `DINAS PENDIDIKAN KEPEMUDAAN DAN OLAHRAGA` (Bali & Latin) dihilangkan sesuai standar KOP baru |
| **Font Aksara Bali** | Unicode OpenType | Beralih dari `Bali Simbar.ttf` (ANSI) ke **`NotoSansBalinese-Regular.ttf`** (Unicode resmi) |
| **Font Teks Latin** | Arial Sans-Serif | Beralih dari Times New Roman ke **`Arial`**. Hanya `SMA NEGERI 1 GIANYAR` yang dicetak **Bold** |
| **Alamat & Kontak** | Format EYD Terbaru | `Jalan Ratna, Tegal Tugu Gianyar (80511), Telepon : (0361) 943034`, `Laman : www.sman1-gianyar.sch.id, Pos-el : sman1.gianyar1963@gmail.com`, `NPSN. 50102079` |
| **Garis Pembatas** | Garis Tunggal Tebal | Menggunakan garis hitam tunggal solid (`3.5px` di web/PNG, `3px` di PDF) |
| **PNG Ultra-HD** | 4× Scale Puppeteer | `kop-surat-portrait.png` (`2960 × 772 px`), `kop-surat-landscape.png` (`3280 × 832 px`), serta varian transparan |
| **Arsip KOP Lama** | Dipindah (Tidak Dihapus) | Seluruh aset KOP lama dipindahkan ke `public/img/arsip-kop-lama/` dan `templates/kop/arsip-kop-lama/` |

---

## 2. Pengkodean Aksara Bali Unicode (Noto Sans Balinese)

Aksara Bali diketik langsung menggunakan karakter Unicode resmi (`U+1B00..U+1B7F`) sehingga tampil konsisten di browser modern maupun saat di-render oleh Chromium/Puppeteer (`generate-kop.cjs`):

1. **Baris 1 (Pemerintah Provinsi Bali)**:
   - Unicode: `ᬧᭂᬫᭂᬭᬶᬦ᭄ᬢᬄᬧ᭄ᬭᭀᬧᬶᬦ᭄ᬲᬶᬩᬮᬶ`
   - Codepoint: `\u1B27\u1B42\u1B2B\u1B42\u1B2D\u1B36\u1B26\u1B44\u1B22\u1B04\u1B27\u1B44\u1B2D\u1B40\u1B27\u1B36\u1B26\u1B44\u1B32\u1B36\u1B29\u1B2E\u1B36`

2. **Baris 2 (SMA Negeri 1 Gianyar)**:
   - Unicode: `᭞ᬏᬲ᭄ ᬏᬫ᭄ ᬅ᭞ ᬦᭂᬕᭂᬭᬶ ᭞᭑᭞ ᬕ᭄ᬬᬜᬃ`
   - Codepoint: `\u1B5E\u1B0F\u1B32\u1B44 \u1B0F\u1B2B\u1B44 \u1B05\u1B5E \u1B26\u1B42\u1B15\u1B42\u1B2D\u1B36 \u1B5E\u1B51\u1B5E \u1B15\u1B44\u1B2C\u1B1C\u1B03`

3. **Baris 3 (Jalan Ratna, Tegal Tugu Gianyar (80511), Telepon : (0361) 943034)**:
   - Unicode: `ᬚᬮᬦ᭄‌ᬭᬢ᭄ᬦᬢᭂᬕᬮ᭄ᬢᬸᬕᬸᬕ᭄ᬬᬜᬃ᭞᭘᭐᭕᭑᭑᭞ᬢᬾᬮᬾᬧᭀᬦ᭄:(᭐᭓᭖᭑)᭙᭔᭓᭐᭓᭔`
   - Catatan teknis: Terdapat **Zero-Width Non-Joiner (`U+200C` / `&#x200C;`)** tepat setelah `ᬚᬮᬦ᭄` agar tanda *adeg-adeg* tetap berdiri jelas dan tidak melebur menjadi *guwung* dengan `ᬭ`.

### Catatan DomPDF (Ekspor PDF):
- DomPDF tidak mendukung *OpenType GSUB/GPOS shaping* untuk Aksara Bali Unicode.
- Oleh karena itu, `generate-kop.cjs` ikut merender 3 potongan gambar transparan beresolusi tinggi (`public/img/aksara-bali-1.png`, `aksara-bali-2.png`, `aksara-bali-3.png`) dari `NotoSansBalinese-Regular.ttf` untuk disisipkan sebagai Base64 PNG di `resources/views/partials/kop-surat-pdf.blade.php`.

---

## 3. Lokasi File & Arsip KOP Lama

### File Aktif:
- Master Template: `templates/kop/kop-surat.html` & `templates/kop/index.html`
- Font Aksara Bali: `public/fonts/NotoSansBalinese-Regular.ttf` & `templates/kop/NotoSansBalinese-Regular.ttf`
- Generator PNG Hi-Res: `generate-kop.cjs`
- Gambar Output: `public/img/kop-surat-{portrait,landscape}{,-transparan}.png`, `public/img/kop-surat-sman1-gianyar-hires.png`, `public/img/aksara-bali-{1,2,3}.png`
- Komponen Web & PDF: `resources/views/components/kop-surat.blade.php` & `resources/views/partials/kop-surat-pdf.blade.php`

### Arsip KOP Lama (Dipindahkan, Tidak Dihapus):
- `public/img/arsip-kop-lama/`: berisi `kop-surat-portrait*.png`, `kop-surat-landscape*.png`, `kop-surat-sman1-gianyar-hires.png`, dan `aksara-bali-1..4.png` lama.
- `templates/kop/arsip-kop-lama/`: berisi `kop-surat-lama.html` dan `Bali Simbar.ttf`.
- Halaman Admin (`KopSuratPage`) dan Guru (`KopSuratController@index`) menyediakan panel lipat **"Arsip KOP Surat Lama (Format Disdikpora)"** untuk pratinjau dan pengunduhan KOP lama.
