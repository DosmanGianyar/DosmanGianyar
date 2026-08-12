# Panduan Pelaksanaan & Pengoperasian Lokal (SIMS SMAN 1 Gianyar)

Dokumen ini berisi panduan langkah demi langkah untuk menginstalasi dependensi pendukung dan menjalankan aplikasi **SIMS SMAN 1 Gianyar** di komputer lokal.

---

## 1. Requirement & Software Pendukung

Sebelum menjalankan aplikasi, pastikan perangkat lokal Anda telah terinstal software berikut:

| Software | Versi Minimal | Fungsi |
|---|---|---|
| **PHP** | 8.2+ (diutamakan 8.2 / 8.3) | Runtime Backend Laravel 12 & Filament v5.6 |
| **Extension PHP** | `pdo_sqlite`, `fileinfo`, `gd`, `intl`, `zip` | Diaktifkan pada `php.ini` |
| **Composer** | 2.5+ | Package Manager PHP |
| **Node.js & npm** | Node v20+ / npm v10+ | Bundler Asset Frontend (Vite & Tailwind CSS v4) |

> **Rekomendasi Lingkungan di Windows:**
> Anda dapat menggunakan stack portabel seperti **Laragon** (sangat direkomendasikan untuk Windows), **XAMPP**, atau **Laravel Herd**.

---

## 2. Struktur File Pendukung Lokal yang Telah Disiapkan

Proyek ini telah dilindungi dengan skrip & konfigurasi pendukung yang siap pakai:

1. **[.env](file:///.env)** — Konfigurasi environment berbasis **SQLite** untuk eksekusi cepat tanpa ketergantungan MySQL/MariaDB eksternal.
2. **[database/database.sqlite](file:///database/database.sqlite)** — File database SQLite lokal.
3. **[setup-local.ps1](file:///setup-local.ps1)** — Skrip PowerShell otomatis untuk menginisialisasi key, database migration, seeder, storage link, dan dependensi npm.
4. **[run-local.ps1](file:///run-local.ps1)** / **[run-local.bat](file:///run-local.bat)** — Skrip untuk menjalankan web server aplikasi lokal secara instan.

---

## 3. Langkah Inisialisasi & Setup Pertama Kali

### Langkah A: Menggunakan Script Otomatis (PowerShell)
Buka terminal PowerShell pada direktori proyek ini, lalu jalankan:

```powershell
.\setup-local.ps1
```

Skrip ini akan secara otomatis:
- Memeriksa ketersediaan PHP, Composer, Node.js.
- Menyiapkan file `.env` dan `database.sqlite`.
- Menjalankan `composer install` & `npm install`.
- Generasi `APP_KEY`.
- Menjalankan `php artisan migrate:fresh --seed` (mengisi data dummy awal).
- Membuat tautan `storage:link`.

---

### Langkah B: Setup Manual (Jika tanpa PowerShell Script)

Jika Anda ingin menjalankan perintah manual satu per satu:

1. **Pasang Dependensi PHP & Node:**
   ```bash
   composer install
   npm install
   ```

2. **Generasi Key Aplikasi:**
   ```bash
   php artisan key:generate
   ```

3. **Migrasi & Seed Database:**
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Tautan Storage File Upload:**
   ```bash
   php artisan storage:link
   ```

---

## 4. Cara Menjalankan Aplikasi Secara Lokal

### Cara 1: Menggunakan Script Launcher (Rekomendasi)
Cukup klik ganda pada file `run-local.bat` atau jalankan di terminal:

```powershell
.\run-local.ps1
```

### Cara 2: Menjalankan Perintah Manual
Buka dua jendela terminal terpisah:

**Terminal 1 (Backend Server):**
```bash
php artisan serve
```
*(Server akan berjalan di `http://127.0.0.1:8000`)*

**Terminal 2 (Vite Hot-Reload Assets):**
```bash
npm run dev
```

---

## 5. Akun Dummy Default untuk Pengujian Lokal

Password default untuk semua akun dummy adalah: **`Dosman123`**

| Role | Username / Login ID | Password | Akses URL |
|---|---|---|---|
| **Admin System** | `admin@sims.sch.id` | `Dosman123` | `http://127.0.0.1:8000/admin` |
| **Admin Kesiswaan** | `kesiswaan@sims.sch.id` | `Dosman123` | `http://127.0.0.1:8000/admin` |
| **Admin Kurikulum** | `kurikulum@sims.sch.id` | `Dosman123` | `http://127.0.0.1:8000/admin` |
| **Admin Sarpras** | `sarpras@sims.sch.id` | `Dosman123` | `http://127.0.0.1:8000/admin` |
| **Guru** | `guru@sims.sch.id` / NIP: `198501012010011001` | `Dosman123` | `http://127.0.0.1:8000` |
| **Siswa** | `siswa@sims.sch.id` / NIS: `2025001` | `Dosman123` | `http://127.0.0.1:8000` |

---

## 6. Menjalankan Automated Testing

Untuk memastikan seluruh pengujian sistem 100% lulus secara lokal:

```bash
php artisan test
```
