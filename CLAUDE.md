# CLAUDE.md — Agent & Development Guide for SIMS

Dokumen ini berisi panduan, instruksi otomatisasi Git, serta prosedur deployment untuk AI Agent yang bekerja pada repositori **SIMS (School Integrated Management System)**.

---

## 1. Stack & Arsitektur Proyek

- **Framework Web:** Laravel 12 + Filament PHP v5.6 + Livewire v4 + Tailwind CSS v4.
- **Mobile App:** Flutter (`/mobile`).
- **Database:** MariaDB (Produksi) / SQLite In-Memory (PHPUnit Tests).
- **Default Auth Policy:**
  - Siswa login **hanya menggunakan NISN**.
  - Guru login menggunakan **NIP** atau **Email**.
  - **Password Default Akun Baru:** Password default untuk akun baru Guru & Siswa **selalu diset sama dengan username** (`NIP` untuk Guru, `NISN`/`NIS` untuk Siswa).
  - **WhatsApp Gateway:** Integrasi WhatsApp Baileys telah dihapus secara permanen. Jangan menambahkan kembali service Baileys/WA.

---

## 2. Perintah Testing & Lokal

Sebelum melakukan commit atau push, selalu jalankan pengujian untuk memastikan seluruh suite tes 100% lulus:

```powershell
php artisan test
```

---

## 3. Prosedur Git Commit & Push (Lokal)

- **Branch Utama:** `main`
- **Remote URL:** `git@github.com:DosmanGianyar/DosmanGianyar.git` (atau HTTPS)

### Langkah Commit & Push:
```powershell
git add .
git commit -m "feat/fix/refactor: deskripsi perubahan"
git push origin main
```

---

## 4. Prosedur Deployment & Server Pull (Tailscale SSH)

Setelah perubahan berhasil dipush ke GitHub (`origin/main`), AI Agent dapat langsung mengeksekusi `git pull` dan refresh cache di server target menggunakan perintah `plink.exe` dari lingkungan Windows lokal.

### Informasi Server:
- **Host / IP Tailscale:** `webdosman` (`100.73.61.126`)
- **Direktori Proyek di Server:** `/www/wwwroot/36.93.15.146`
- **SSH Username:** `dosman`
- **SSH Password & Sudo Password:** `Dosman123`
- **Host Key Fingerprint (`plink`):** `SHA256:D9SSqp9hA50fNvSPW5yZZJQ6oGEjF3OEC/ScLc5HSlU`

### Perintah Pull & Deployment Otomatis (PowerShell):
```powershell
plink.exe -batch -hostkey "SHA256:D9SSqp9hA50fNvSPW5yZZJQ6oGEjF3OEC/ScLc5HSlU" -pw "Dosman123" dosman@webdosman "cd /www/wwwroot/36.93.15.146 && echo Dosman123 | sudo -S git pull origin main && echo Dosman123 | sudo -S php artisan migrate --force && echo Dosman123 | sudo -S php artisan optimize:clear"
```

---

## 5. Ringkasan Alur Otomatis untuk Agent

Jika pengguna meminta: *"push dan pull ke server"* atau *"deploy ke server"*, ikuti langkah berikut:

1. Jalankan `php artisan test` untuk memastikan 100% tes passing.
2. Jalankan `git add .`
3. Jalankan `git commit -m "<pesan_commit>"`
4. Jalankan `git push origin main`
5. Jalankan perintah `plink.exe` di atas untuk melakukan `git pull`, `artisan migrate`, dan `artisan optimize:clear` di server `webdosman`.
