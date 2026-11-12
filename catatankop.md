# Histori Pengerjaan & Dokumentasi Teknis KOP Surat SMAN 1 Gianyar

Dokumen ini mencatat seluruh histori pengerjaan, spesifikasi teknis, pemetaan karakter font, dan aset yang telah dibangun pada project KOP Surat SMAN 1 Gianyar (DOSMAN) sebelum dipindahkan ke project **SIMS** (`C:\Users\dosman\Projects\sims`).

---

## 1. Ringkasan Pengerjaan

| Komponen | Status | Keterangan |
|---|---|---|
| **Font Aksara Bali** | Selesai | Menggunakan font TrueType legendaris `Bali Simbar.ttf` (ANSI-mapped) |
| **Tata Letak KOP** | Selesai | Sesuai Pergub Bali No. 80 Th 2018 (Aksara Bali di atas teks Latin) |
| **Kerapatan Teks** | Selesai | Jarak antar baris dibuat mepet & kompak (`margin-bottom: -1px` s/d `2px`) |
| **Ukuran Huruf Bali** | Selesai | Ditingkatkan proporsional (`26.5px` s/d `29.5px`) agar terbaca tegas |
| **Halaman Cetak A4** | Selesai | Mendukung A4 Portrait (210×297mm) dan Landscape (297×210mm) |
| **Garis Pembatas Ganda**| Selesai | Garis atas tebal (3px) + celah (2px) + garis bawah tipis (1px) |
| **Logo DOSMAN** | Selesai | High-Res (1016×1024 & 2032×2048), transparan luar, putih solid dalam |
| **Logo Pemprov Bali** | Selesai | Ultra-HD (2019×1941) transparan |

---

## 2. Pemetaan Keystroke Font Bali Simbar

Font `Bali Simbar.ttf` adalah font berbasis Windows-1252 (ANSI/ASCII mapped), bukan Unicode raw. Setiap huruf Latin dan simbol tanda baca memetakan langsung ke glif Aksara Bali tertentu:

### A. Teks KOP Lengkap:

1. **Baris 1 (Pemerintah Provinsi Bali)**:
   - **Aksara Bali**: `p;merin\tH p]opinsi bli`
   - **Teks Latin**: `PEMERINTAH PROVINSI BALI`
   - *Penjelasan*: `p` (pa), `;` (pepet), `m` (ma), `e` (taleng), `r` + `i` (ra ulu), `n` + `\` + `t` (na adeg-adeg gantungan ta), `H` (bisah) = *Pemerintah*. `p` + `]` + `o` (pa gantungan ra tedong) + `p` + `i` + `n` + `s` + `i` = *Propinsi*. `b` + `l` + `i` (ba la ulu) = *Bali*.

2. **Baris 2 (Dinas Pendidikan Kepemudaan dan Olahraga)**:
   - **Aksara Bali**: `dinS p;n\didikn\ k;p;mudan\ dn\ aoLHrg`
   - **Teks Latin**: `DINAS PENDIDIKAN KEPEMUDAAN DAN OLAHRAGA`
   - *Penjelasan*: Menggunakan susunan ejaan resmi pasang aksara Bali instansi dinas.

3. **Baris 3 (SMA Negeri 1 Gianyar)**:
   - **Aksara Bali**: `,es,em,a, neg]i ,1, giy/`
   - **Teks Latin**: `SMA NEGERI 1 GIANYAR`
   - *Penjelasan*: Singkatan akronim `SMA` ditulis dieja fonetik `,es,em,a,` diapit tanda carik (`,`). Angka `1` Bali diapit carik `,1,`. Gianyar ditulis `giy/` (surang `/` untuk akhiran r).

4. **Baris 4 (Alamat, Wilayah & Nomor Telepon)**:
   - **Aksara Bali**: `jln\rt\\n, t;gl\tugu giy/, t;l\p : (0361) 943034`
   - **Teks Latin**: `Jln. Ratna, Tegal Tugu Gianyar, Telp : (0361) 943034`
   - *Penjelasan*: Angka Latin `(0361) 943034` pada font Bali Simbar otomatis menghasilkan angka aksara Bali `(᭐᭓᭖᭑) ᭙᭔᭓᭐᭓᭔`.

---

## 3. Spesifikasi Restorasi Logo DOSMAN

- **File Master**: `logo-dosman.png` (1016 × 1024 px, 300 DPI) & `logo-dosman-hd.png` (2032 × 2048 px).
- **Kontur Terluar**: Mengikuti garis hitam terluar perisai dan pita semboyan *"WIDYA WAHANA BHAKTI"*.
- **Pembersihan Fringe/Halo**: Sebanyak ~7.800 pixel abu-abu/putih semi-transparan yang mengotori tepi luar garis hitam telah dibersihkan secara presisi menggunakan algoritma boundary detection.
- **Warna Putih Solid**: Bagian dalam perisai, semboyan pita bawah, buku terbuka, dan 3 figur tetap solid murni `RGB(255, 255, 255)` dengan Opacity 100%.
- **Background**: 100% transparan (`Alpha: 0`) dengan antialiasing tepi hitam yang halus tanpa efek jaggies/gerigi.

---

## 4. Struktur File Saat Ini

```
C:\Users\dosman\Projects\KOP\
├── catatankop.md               # Dokumentasi histori pengerjaan (file ini)
├── index.html                  # File KOP Surat interaktif (Portrait & Landscape)
├── kop-surat.html              # Salinan KOP Surat mandiri
├── logo-dosman.png             # Logo DOSMAN resolusi tinggi transparan (300 DPI)
├── logo-dosman-hd.png          # Logo DOSMAN Ultra-HD 2x (300 DPI)
├── logo-provinsi.png           # Logo Pemprov Bali Ultra-HD transparan
└── fonts\
    └── Bali Simbar.ttf         # Master font TrueType Bali Simbar
```

---

## 5. Rencana Migrasi ke Project SIMS (`C:\Users\dosman\Projects\sims`)

1. **Aset Gambar**:
   - Salin `logo-dosman.png` dan `logo-provinsi.png` ke:
     - `C:\Users\dosman\Projects\sims\public\img\`
     - `C:\Users\dosman\Projects\sims\public\images\`
2. **Aset Font**:
   - Salin `Bali Simbar.ttf` ke `C:\Users\dosman\Projects\sims\public\fonts\`
3. **Template Blade**:
   - Perbarui `resources/views/partials/kop-surat-pdf.blade.php` atau buat komponen `resources/views/components/kop-surat.blade.php`.
   - Menggantikan penggunaan potongan gambar raster lama (`aksara-bali-1.png`, dst.) dengan font `Bali Simbar` berbasis CSS vector atau Base64 font embedding untuk generator PDF (Browsershot / DomPDF).
4. **Dokumentasi Histori**:
   - Salin `catatankop.md` ke root `C:\Users\dosman\Projects\sims\catatankop.md`.
