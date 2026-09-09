# CLAUDE.md — Agent & Development Guide for SIMS

## SIMS - School Integrated Management System SMAN 1 Gianyar
- **Deployment:** Automated SSH Git pull via `webdosman` user (`/www/wwwroot/36.93.15.146`)

---

## ⚡ ATURAN UTAMA: TRIGGER "claude.md" (COMMIT + PUSH + DEPLOY OTOMATIS)

> [!IMPORTANT]
> **KETIKA USER MENGETIK `claude.md`:**
> AI Agent **WAJIB LANGSUNG MENGEKSEKUSI** urutan perintah berikut secara otomatis tanpa menunggu konfirmasi tambahan:
> 1. Stage semua perubahan: `git add .`
> 2. Buat commit: `git commit -m "<deskripsi_perubahan_terbaru>"`
> 3. Push ke GitHub: `git push origin main`
> 4. Deploy/Pull ke server production via `plink`:
>    ```powershell
>    plink -batch -hostkey "SHA256:D9SSqp9hA50fNvSPW5yZZJQ6oGEjF3OEC/ScLc5HSlU" -pw Dosman123 dosman@100.73.61.126 "echo Dosman123 | sudo -S sh -c 'cd /www/wwwroot/36.93.15.146 && git pull origin main && php artisan migrate --force && php artisan optimize:clear && systemctl restart php-fpm-84'"
>    ```

---

## 🔑 Kredensial & Informasi Server Production

| Parameter | Nilai |
| :--- | :--- |
| **Host / IP Tailscale** | `100.73.61.126` (`webdosman`) |
| **Path Proyek di Server** | `/www/wwwroot/36.93.15.146` |
| **SSH Username** | `dosman` |
| **SSH & Sudo Password** | `Dosman123` |
| **Host Key Fingerprint (`plink`)** | `SHA256:D9SSqp9hA50fNvSPW5yZZJQ6oGEjF3OEC/ScLc5HSlU` |
| **Branch Utama** | `main` |
| **Git Remote** | `origin` (`git@github.com:DosmanGianyar/DosmanGianyar.git`) |

---

## 💻 Perintah Sekali Jalan (All-in-One One-Liner PowerShell)

Jika ingin menjalankan secara manual di terminal PowerShell Windows:

```powershell
git add . ; git commit -m "deploy: update aplikasi SIMS" ; git push origin main ; plink -batch -hostkey "SHA256:D9SSqp9hA50fNvSPW5yZZJQ6oGEjF3OEC/ScLc5HSlU" -pw Dosman123 dosman@100.73.61.126 "echo Dosman123 | sudo -S sh -c 'cd /www/wwwroot/36.93.15.146 && git pull origin main && php artisan migrate --force && php artisan optimize:clear && systemctl restart php-fpm-84'"
```

---

## 1. Stack & Arsitektur Proyek

- **Framework Web:** Laravel 12 + Filament PHP v5.6 + Livewire v4 + Tailwind CSS v4.
- **Mobile App:** Flutter (`/mobile`).
- **Database:** MariaDB (Produksi) / SQLite In-Memory (PHPUnit Tests).
- **Default Auth Policy:**
  - Siswa login **hanya menggunakan NISN**.
  - Guru & Pegawai login menggunakan **NIP** atau **Email**.
  - **Password Default Akun Baru:** Password default untuk akun baru Guru, Pegawai, & Siswa **selalu diset sama dengan username / nomor identitas** (`NIP` untuk Guru & Pegawai, `NISN`/`NIS` untuk Siswa).
  - **Import Data Guru & Pegawai:**
    - Mendukung format Excel (`.xlsx`) dan CSV (`.csv`).
    - Pilihan role Guru / Pegawai dapat diisi di kolom file (`Role`/`Jenis`) atau dipilih di formulir import.
    - NIP belum ada: Buat akun baru dengan role (`guru`/`pegawai`) dan password default = NIP.
    - NIP sudah ada: Update data (termasuk pembaruan Nama Lengkap yang berisi gelar akademik baru).
    - Template tersedia di `public/templates/contoh-import-guru-pegawai.csv` dan `.xlsx`.
  - **WhatsApp Gateway:** Integrasi WhatsApp Baileys telah dihapus secara permanen. Jangan menambahkan kembali service Baileys/WA.

---

## 2. Pengujian (Testing)

Jalankan pengujian unit/feature sebelum commit besar untuk memastikan integritas kode:

```powershell
vendor\bin\phpunit
# atau
php artisan test
```
