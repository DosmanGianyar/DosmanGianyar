# SIMS — School Integrated Management System
### Project Guide for Development Team

> **Stack:** Laravel 12 · Filament PHP · Livewire · Tailwind CSS · PWA · MariaDB · Docker  
> **Version:** 2.8 — Sprint 7: Ekstrakurikuler & Tata Tertib Sekolah  
> **Last Updated:** 2026-06-15

---

## Daftar Isi
1. [Visi Produk](#1-visi-produk)
2. [Role Pengguna (RBAC)](#2-role-pengguna-rbac)
3. [Arsitektur & Tech Stack](#3-arsitektur--tech-stack)
4. [Schema Database Utama](#4-schema-database-utama)
5. [Roadmap Fase Pengerjaan](#5-roadmap-fase-pengerjaan)
   - Fase 1–8 (Fondasi s/d Testing & Deployment)
   - Fase 9–10 (Kurikulum & Humas)
   - **Sprint 1** (Rekap Presensi · Galeri↔Event · SLA Badge) ✅
   - **Sprint 2** (Dashboard Prasarana · Detail Event · Tren Poin) ✅
   - **Sprint 3** (Notifikasi In-App · Katalog Aset · Rekap Nilai) ✅
   - **Sprint 4** (BK Dashboard · Rekap Absensi Bulanan · Export Nilai) ✅
   - **Sprint 5** (Dashboard Nyata · Guru Input Nilai) ✅
   - **Sprint 6** (Guru Nav Update · Rapor PDF Siswa · Absensi Manual Guru) ✅
   - **Sprint 7** (Ekstrakurikuler Flutter · Tata Tertib Sekolah · KesiswaanScreen Hub) ✅
6. [Alur Kerja Tim](#6-alur-kerja-tim)
7. [Aturan Branching Git](#7-aturan-branching-git)
8. [Perintah Penting](#8-perintah-penting)
9. [Integrasi Lintas Modul](#9-integrasi-lintas-modul)
10. [Checklist Go-Live](#10-checklist-go-live)

---

## 1. Visi Produk

SIMS adalah platform ekosistem sekolah digital yang mengintegrasikan **pengawasan perilaku siswa (Kesiswaan)** dan **akuntabilitas aset sekolah (Sarpras)** secara real-time, transparan, dan akurat.

---

## 2. Role Pengguna (RBAC)

| # | Role | Slug | Deskripsi Akses |
|---|------|------|-----------------|
| 1 | **Admin** | `admin` | Superuser — kelola semua data, user, konfigurasi sistem |
| 2 | **Guru** | `guru` | Input absensi, poin, dispensasi, laporan kelas |
| 3 | **Siswa** | `siswa` | Selfie attendance, cek poin, pengajuan izin mandiri |
| 4 | **Siswa Pengelola** | `siswa_pengelola` | Semua akses Siswa + kelola voting & pengumuman |

> **Catatan:** Role dikelola menggunakan package **Spatie Laravel Permission**.  
> Satu user hanya boleh memiliki satu role aktif.

---

## 3. Arsitektur & Tech Stack

```
┌─────────────────────────────────────────────────────────────┐
│                     CLIENT (Browser/PWA)                    │
│              Tailwind CSS + Livewire Components             │
└───────────────────────────┬─────────────────────────────────┘
                            │ HTTPS
┌───────────────────────────▼─────────────────────────────────┐
│                    LARAVEL 11 BACKEND                       │
│   ┌──────────────┐  ┌─────────────┐  ┌──────────────────┐  │
│   │  Filament PHP│  │  Livewire   │  │  Laravel Sanctum │  │
│   │  (Admin UI)  │  │  (UI Komp.) │  │  (API Auth/JWT)  │  │
│   └──────────────┘  └─────────────┘  └──────────────────┘  │
│   ┌──────────────────────────────────────────────────────┐  │
│   │  Spatie Permission · Model Observers · Scheduler     │  │
│   └──────────────────────────────────────────────────────┘  │
└───────────────────────────┬─────────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────────┐
│                  MariaDB (Database)                         │
│         + Redis (Queue/Cache) + Storage (S3/Local)          │
└─────────────────────────────────────────────────────────────┘
```

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 12 |
| Admin Panel | Filament PHP v5.6 |
| Frontend Komponen | Livewire v4 + Tailwind CSS v4 |
| Auth API | Laravel Sanctum |
| RBAC | Kolom `role` di tabel `users` (Spatie di-skip, PHP 8.2 tidak kompatibel) |
| Database | MariaDB |
| Geofencing | Haversine Formula (server-side, radius 50m) |
| Image Processing | Intervention Image (auto-compress sebelum simpan) |
| PWA | Laravel PWA / manual manifest + service worker |
| Queue/Jobs | Laravel Queue (database driver) |
| Containerisasi | Docker + Docker Compose |

---

## 4. Schema Database Utama

```sql
users          (id, name, email, role, class_id, parent_phone)
classes        (id, name, grade, homeroom_teacher_id)
holidays       (id, date, description)

-- Kesiswaan
attendances    (id, user_id, date, time, lat, lng, photo, status)
permits        (id, student_id, type, start_date, end_date, file, status, approved_by)
dispensations  (id, requester_id, activity_name, file, status)
disp_students  (dispensation_id, student_id)  -- pivot
conduct_logs   (id, student_id, teacher_id, category_id, point, photo, note)
conduct_cats   (id, name, point_value, type)  -- type: prestasi | pelanggaran
bk_logs        (id, student_id, counselor_id, coaching_note, date)  -- PRIVATE
exit_passes    (id, student_id, reason, out_time, in_time, status)

-- Sarpras
assets         (id, qr_code, name, category, room_id, condition, photo)
rooms          (id, name, building, capacity)
asset_loans    (id, asset_id, user_id, start_date, end_date, status, purpose)
damage_reports (id, asset_id, reporter_id, photo, description, status)
maint_logs     (id, asset_id, tech_name, date, cost, note)

-- Voting
voting_sessions (id, title, description, start_time, end_time, status, created_by)
candidates      (id, voting_session_id, name, vision, photo, order)
votes           (id, voting_session_id, voter_id, candidate_id)  -- unique(session,voter)

-- Pengumuman & Audit
announcements  (id, title, body, target, is_pinned, published_at, author_id)
activity_logs  (id, user_id, action, subject_type, subject_id, changes, ip_address, user_agent, created_at)

-- Kurikulum
subjects        (id, name, code, description)
schedules       (id, class_id, subject_id, teacher_id, day[1-5], period, start_time, end_time, room, academic_year)
academic_events (id, title, description, start_date, end_date, type, color, created_by)

-- Humas
school_events   (id, title, description, event_date, end_date, location, cover_photo, type, is_published, created_by, gallery_id)
galleries       (id, title, description, event_date, cover_photo, is_published, created_by)
gallery_photos  (id, gallery_id, photo, caption, sort_order)

-- Notifikasi (Sprint 3)
app_notifications (id, user_id FK, title[120], body[500], type[20] default 'info', url nullable, read_at nullable, timestamps)

-- Nilai (Sprint 3)
student_grades  (id, student_id FK users, subject_id FK, score decimal(5,2), type[10] default 'UH', semester tinyint default 1, academic_year varchar(9), notes[200] nullable, recorded_by FK users nullable, timestamps)
```

---

## 5. Roadmap Fase Pengerjaan

> Legend: `[ ]` = Belum · `[~]` = In Progress · `[x]` = Selesai

---

### FASE 1 — Fondasi Project
**Target:** Minggu 1–2 | **PIC:** Lead Dev / DevOps

```
[x] Install Laravel 12 fresh project
[x] Install Filament PHP v5.6
[x] Install Livewire v4.3
[x] Tailwind CSS v4 (sudah terpasang via Vite)
[x] Aktifkan PHP ext-intl dan ext-zip (di php.ini XAMPP)
[x] Konfigurasi .env (DB: SQLite untuk dev, siap ganti MariaDB untuk prod)
[x] Migration: users (extended) + classes + holidays
[x] Seeder: 4 akun dummy (Admin, Guru, Siswa, Siswa Pengelola)
[ ] Setup Docker Compose (PHP, Nginx, MariaDB, Redis)   ← Fase 8
[ ] Pastikan login bisa masuk ke dashboard masing-masing role ← Fase 2

CATATAN: Spatie Laravel Permission di-skip — PHP 8.2 tidak kompatibel
dengan versi terbaru (butuh PHP 8.3+). Menggunakan kolom `role` di
tabel users sebagai pengganti. Bisa di-upgrade saat pindah ke PHP 8.3.
```

**Output:** Package terinstall, database siap, 4 akun dummy tersedia.

---

### FASE 2 — Autentikasi & Manajemen User
**Target:** Minggu 2–3 | **PIC:** Backend Dev

```
[x] Custom login page (mobile-first, bukan default Filament)
[x] Redirect dashboard berdasarkan role setelah login
[x] Layout Guru (sidebar desktop + hamburger mobile)
[x] Layout Siswa (bottom navigation mobile-first)
[x] Dashboard Guru (stats, rekap absensi, alert poin)
[x] Dashboard Siswa (status presensi, ringkasan poin, pengumuman)
[x] Profile Guru (edit NIP, mapel, foto, password)
[x] Profile Siswa (E-Kartu Pelajar, data ortu, edit profil)
[x] Middleware: blokir akses jika role tidak sesuai
[x] Logout & session management
[x] Filament Resource: CRUD User oleh Admin (UserResource)
[x] Manajemen Kelas (CRUD + assign wali kelas — SchoolClassResource)
[x] Import user massal via Excel (maatwebsite/excel)   ← selesai di v1.8
```

**Output:** Admin bisa tambah/edit/hapus user dan kelas. Setiap role lihat dashboard-nya sendiri.

---

### FASE 3 — Modul Presensi ✅ SELESAI
**Target:** Minggu 3–5 | **PIC:** Frontend + Backend Dev

```
-- Siswa --
[x] Halaman selfie (akses kamera depan via browser API)
[x] Kirim foto + koordinat GPS ke server
[x] Server validasi geolocation (Haversine, radius 50m dari titik sekolah)
[x] Deteksi & blokir Mock Location / Fake GPS (accuracy < 5m → reject)
[x] Simpan record attendance (status: Hadir / Terlambat)
[x] Form pengajuan Izin & Sakit (upload file dokumen)
[x] Exit Pass: form keluar sementara + countdown timer

-- Guru --
[x] Rekap absensi per kelas per hari (tabel + filter tanggal/kelas)
[x] Verifikasi / approval izin siswa (approve + reject dengan catatan)
[x] Input dispensasi kolektif (pilih siswa per kelas, upload SK)
[x] Export rekap absensi ke PDF/Excel   ← selesai di v1.8

-- Otomatis (Scheduler) --
[x] Command: php artisan attendance:auto-alpa (skip weekends + holidays)
[x] Scheduler: weekdays 08:00 WIB (01:00 UTC) via routes/console.php
[x] Logic: permit/dispensasi approved → attendance di-sync otomatis
[ ] Jalankan: php artisan schedule:run setiap menit via cron   ← setup di server

-- File Baru Fase 3 --
app/Http/Controllers/Siswa/AttendanceController.php
app/Http/Controllers/Siswa/PermitController.php
app/Http/Controllers/Siswa/ExitPassController.php
app/Http/Controllers/Guru/AttendanceController.php
app/Http/Controllers/Guru/DispensationController.php
app/Console/Commands/AutoAlpa.php
app/Services/GeofenceService.php
resources/views/siswa/attendance/selfie.blade.php
resources/views/siswa/permit/index.blade.php
resources/views/siswa/permit/create.blade.php
resources/views/siswa/exit-pass.blade.php
resources/views/guru/attendance/index.blade.php
resources/views/guru/attendance/permits.blade.php
resources/views/guru/attendance/dispensation-create.blade.php
```

**Output:** Siswa bisa presensi selfie. Guru lihat rekap & approve izin. Alpa otomatis berjalan tiap hari.

---

### FASE 4 — Modul Poin & Perilaku ✅ SELESAI
**Target:** Minggu 5–7 | **PIC:** Backend Dev

```
[x] CRUD Kategori Poin (nama, nilai, tipe: prestasi/pelanggaran) — Filament Admin
[x] Form input poin oleh Guru (pilih siswa, kategori, wajib upload foto bukti)
[x] Dashboard siswa: riwayat poin + total poin aktif + progress bar berwarna
[x] Observer: jika total poin ≤ -75 → buat entri otomatis di BK Log (1x/hari)
[x] BK Log: dicatat otomatis + visible di halaman detail siswa (Guru)
[x] Halaman detail siswa (Guru): rekap log + BK log + tombol catat poin
[ ] Mekanisme Point Redemption (pemutihan poin)   ← opsional, ditunda
[ ] Notifikasi WhatsApp ke orang tua saat poin dicatat (via queue job)   ← Fase 7
[x] Laporan poin per siswa & per kelas (export PDF/Excel)   ← selesai di v1.8

-- File Baru Fase 4 --
database/migrations/2026_05_18_*_create_conduct_categories_table.php
database/migrations/2026_05_18_*_create_conduct_logs_table.php
database/migrations/2026_05_18_*_create_bk_logs_table.php
app/Models/ConductCategory.php
app/Models/ConductLog.php
app/Models/BkLog.php
app/Observers/ConductLogObserver.php
app/Filament/Resources/ConductCategoryResource.php
app/Http/Controllers/Guru/ConductController.php
app/Http/Controllers/Siswa/ConductController.php
resources/views/guru/conduct/index.blade.php
resources/views/guru/conduct/create.blade.php
resources/views/guru/conduct/student-detail.blade.php
resources/views/siswa/conduct/index.blade.php

-- Catatan Teknis --
• ConductLogObserver terdaftar di AppServiceProvider::boot()
• BK threshold: -75 poin → auto BkLog, 1 entri per hari per siswa
• Foto bukti disimpan ke storage/conduct/ (wajib saat input poin)
• Foto diakses via route signed URL untuk keamanan (tidak publik langsung)
• Filament v5 API: Form → Schema, icons sebagai string 'heroicon-o-...'
```

**Output:** Guru input poin dengan bukti foto. BK otomatis mendapat alert via observer. Siswa lihat total & riwayat poin dari bottom nav.

---

### FASE 5 — Modul Sarpras ✅ SELESAI
**Target:** Minggu 7–9 | **PIC:** Backend Dev

```
[x] CRUD Aset (nama, kategori, ruangan, kondisi, foto) — Filament Admin
[x] CRUD Ruangan (nama, gedung, kapasitas) — Filament Admin
[x] Generate QR Code unik per aset (SVG, disimpan ke storage/public/qrcodes/)
[x] Halaman scan QR → tampil detail aset (html5-qrcode CDN + manual input fallback)
[x] Form lapor kerusakan via scan QR (upload foto wajib + redirect ke detail aset)
[x] Alur booking/pinjam aset (request → approval Guru) + riwayat pinjaman siswa
[x] Maintenance log: catat servis, teknisi, biaya — Filament Admin
[x] Dashboard Guru Sarpras: stats kondisi aset + laporan menunggu + pinjaman pending
[x] Guru kelola damage reports (tangani → selesaikan + update kondisi aset)
[x] Guru approve/reject/return loan requests
[x] Bridge: dari damage report, bisa link ke conduct_log siswa   ← selesai di v2.1
[x] Export laporan aset & kerusakan (PDF)   ← selesai di v2.1

-- Package Baru --
chillerlan/php-qrcode (^5.0, pure PHP SVG, tidak perlu ext-gd)

-- File Baru Fase 5 --
database/migrations/2026_05_18_080001_create_rooms_table.php
database/migrations/2026_05_18_080002_create_assets_table.php
database/migrations/2026_05_18_080003_create_asset_loans_table.php
database/migrations/2026_05_18_080004_create_damage_reports_table.php
database/migrations/2026_05_18_080005_create_maintenance_logs_table.php
app/Models/Room.php
app/Models/Asset.php
app/Models/AssetLoan.php
app/Models/DamageReport.php
app/Models/MaintenanceLog.php
app/Console/Commands/GenerateAssetQr.php
app/Filament/Resources/RoomResource.php (+ Pages/)
app/Filament/Resources/AssetResource.php (+ Pages/)
app/Filament/Resources/DamageReportResource.php (+ Pages/)
app/Filament/Resources/AssetLoanResource.php (+ Pages/)
app/Filament/Resources/MaintenanceLogResource.php (+ Pages/)
app/Http/Controllers/Guru/SarprasController.php
app/Http/Controllers/Siswa/SarprasController.php
resources/views/guru/sarpras/index.blade.php
resources/views/guru/sarpras/damage.blade.php
resources/views/guru/sarpras/loans.blade.php
resources/views/siswa/sarpras/scan.blade.php
resources/views/siswa/sarpras/show.blade.php
resources/views/siswa/sarpras/damage-create.blade.php
resources/views/siswa/sarpras/loan-create.blade.php
resources/views/siswa/sarpras/loans.blade.php

-- Catatan Teknis --
• QR code UUID di-generate otomatis saat Asset dibuat (model boot event)
• QR image di-generate otomatis setelah Asset disimpan (static::created observer)
• QR content = URL /siswa/sarpras/asset/{uuid} → scan langsung buka halaman detail
• Artisan: php artisan assets:generate-qr [--force] → regenerate semua QR
• Siswa akses Sarpras via bottom nav Scan (atau dari dashboard link)
• Filament Sarpras group di Admin: Room, Asset, DamageReport, AssetLoan, MaintenanceLog
• Guru Sidebar group "Sarpras": Dashboard, Laporan Kerusakan, Peminjaman
```

**Output:** Admin input aset + generate QR. Siswa scan QR → lihat detail, lapor kerusakan, pinjam aset. Guru approve laporan & peminjaman.

---

### FASE 6 — Modul E-Voting ✅ SELESAI
**Target:** Minggu 9–10 | **PIC:** Frontend + Backend Dev

```
-- Siswa Pengelola --
[x] Buat sesi voting (judul, deskripsi, waktu mulai & selesai)
[x] Tambah kandidat (nama, visi-misi, foto) — min. 2 kandidat sebelum aktifkan
[x] Aktifkan / tutup sesi manual
[x] Dashboard kelola: perolehan suara real-time per kandidat

-- Siswa --
[x] Halaman daftar sesi voting (aktif dan selesai)
[x] Halaman pilih kandidat (1 siswa = 1 suara, unique constraint DB)
[x] Konfirmasi sebelum submit (HTML <dialog> + vanilla JS, tanpa Alpine)
[x] Halaman hasil: persentase + progress bar (muncul setelah vote atau session closed)
[x] Voting nav item ditambahkan di bottom navigation siswa

-- Otomatis --
[x] Scheduler: auto-tutup sesi sesuai end_time (voting:close-expired tiap menit)
[x] Admin panel: VotingSessionResource (CRUD sesi + status management)

-- File Baru Fase 6 --
database/migrations/2026_05_18_090001_create_voting_sessions_table.php
database/migrations/2026_05_18_090002_create_candidates_table.php
database/migrations/2026_05_18_090003_create_votes_table.php
app/Models/VotingSession.php
app/Models/Candidate.php
app/Models/Vote.php
app/Console/Commands/CloseExpiredVotingSessions.php
app/Filament/Resources/VotingSessionResource.php (+ Pages/)
app/Http/Controllers/Siswa/VotingController.php
app/Http/Controllers/Siswa/VotingManageController.php
resources/views/siswa/voting/index.blade.php
resources/views/siswa/voting/show.blade.php
resources/views/siswa/voting/results.blade.php
resources/views/siswa/voting/manage/index.blade.php
resources/views/siswa/voting/manage/form.blade.php
resources/views/siswa/voting/manage/show.blade.php

-- Catatan Teknis --
• votes table: unique(voting_session_id, voter_id) → 1 suara per siswa per sesi di DB level
• Konfirmasi pilihan menggunakan HTML native <dialog> element (no Alpine.js required)
• Auto-close: voting:close-expired runs every minute via scheduler
• Pengelola check: VotingManageController->checkRole() abort 403 jika bukan siswa_pengelola
• Route manage/* di-prefix /siswa/voting/manage — accessible sesama middleware group siswa
• Hasil voting bisa dilihat setelah user vote atau saat status = closed
```

**Output:** Siswa Pengelola bisa buat voting OSIS. Siswa bisa memilih dari HP.

---

### FASE 6+ — Peningkatan Infrastruktur & Fitur Tambahan ✅ SELESAI
**Target:** v1.8 | **PIC:** Backend Dev

```
-- Keamanan --
[x] Rate limiting login (max 5 percobaan/menit per email+IP via RateLimiter::for)
[x] Custom error pages 403, 404, 419, 500 — branded dengan logo & nama sekolah

-- Performa --
[x] Database performance indexes — attendances, conduct_logs, permits, asset_loans,
    damage_reports, voting_sessions, votes, users (migration 2026_05_18_100001)

-- Media --
[x] ImageService — kompresi otomatis semua upload (Intervention Image v3, max 1280px,
    quality 80, GD driver); foto selfie max 800px quality 75
    Diterapkan di: ConductController, ProfileController (guru+siswa),
                   SarprasController (siswa), VotingManageController

-- Import & Export --
[x] Import user massal via Excel (maatwebsite/excel) — template download tersedia
    Kolom: nama, email, role, nis, nip, kelas, no_hp, nama_ortu, hp_ortu, tgl_lahir, alamat, mapel
    Default password = NIS (siswa) / NIP (guru) / name-slug (fallback)
[x] Export laporan absensi — PDF (landscape A4) + Excel, filter bulan/kelas/status
[x] Export laporan poin perilaku — PDF (landscape A4) + Excel, filter bulan/kelas

-- Konten --
[x] Modul Pengumuman — buat/edit/hapus, target role (all/siswa/guru), pin, jadwal publish
    Pengelola: full CRUD | Siswa: read-only

-- Audit & Monitoring --
[x] Audit Log — mencatat semua aksi login, logout, import ke activity_logs
    Filament Resource: ActivityLogResource (Admin panel → group Sistem → Audit Log)
    Filter by action, sortable, paginated 25/50/100

-- Identitas --
[x] Logo sekolah (public/img/logo_sekolah.png) + nama "SMA Negeri 1 Gianyar"
    di semua halaman: login, dashboard guru, dashboard siswa, PDF export

-- Packages Baru v1.8 --
intervention/image (^3.11)      → kompresi gambar upload (GD driver, aktifkan ext-gd di php.ini)
maatwebsite/excel (^3.1)        → import/export Excel
barryvdh/laravel-dompdf (^3.1)  → generate PDF laporan

-- File Baru v1.8 --
database/migrations/2026_05_18_100001_add_performance_indexes.php
database/migrations/2026_05_18_100002_create_announcements_table.php
database/migrations/2026_05_18_100003_create_activity_logs_table.php
app/Services/ImageService.php
app/Imports/UsersImport.php
app/Exports/UserTemplateExport.php
app/Exports/AttendanceExport.php
app/Exports/ConductLogExport.php
app/Models/Announcement.php
app/Models/ActivityLog.php
app/Filament/Resources/ActivityLogResource.php (+ Pages/)
app/Http/Controllers/Admin/UserImportController.php
app/Http/Controllers/Guru/ExportController.php
app/Http/Controllers/Siswa/AnnouncementController.php
resources/views/admin/import-users.blade.php
resources/views/errors/layout.blade.php (403, 404, 419, 500)
resources/views/guru/exports/attendance-form.blade.php
resources/views/guru/exports/conduct-form.blade.php
resources/views/exports/attendance-pdf.blade.php
resources/views/exports/conduct-pdf.blade.php
resources/views/siswa/announcements/ (index, show, form, manage)

-- Catatan Teknis --
• ext-gd WAJIB aktif di php.ini: hapus ';' dari ';extension=gd'
• Logo di PDF: public_path('img/logo_sekolah.png') — jangan storage symlink path
• ActivityLog::record($action, $subject?, $changes?) → static helper, panggil dari controller
• Rate limit response dikembalikan via closure, bukan default JSON
• Announcements: scopePublished() + scopeForRole() untuk filter di controller
```

**Output:** Login aman dari brute-force. Upload hemat storage. Admin bisa import user massal.
Guru bisa cetak laporan. Siswa bisa baca pengumuman. Admin bisa audit semua aksi.

---

### FASE 9 — Modul Kurikulum ✅ SELESAI
**Target:** v1.9 | **PIC:** Backend Dev

```
Flowchart:
ADMIN (Filament)                      SISTEM                      SISWA
  ├─ Kelola Mata Pelajaran ─────────→ subjects table               │
  ├─ Input Jadwal per Kelas ─────────→ schedules table             │
  │  (kelas, hari, jam ke-, mapel,    │                            │── Buka Kurikulum
  │   guru, waktu, ruangan)           │                            │── Tab "Jadwal Hari Ini"
  └─ Tambah Kalender Akademik ───────→ academic_events table       │   → Query day=ISO weekday + class_id
     (UTS/UAS/Libur/Kegiatan)         │                            │── Tab "Jadwal Mingguan"
                                      │                            │   → Grouped by day 1–5
                                      │                            └── Tab "Kalender Akademik"
                                                                       → Upcoming events grouped by month

[x] Migration: subjects, schedules, academic_events
[x] Models: Subject (hasMany schedules), Schedule (belongsTo class/subject/teacher), AcademicEvent
[x] Filament Admin: SubjectResource, ScheduleResource, AcademicEventResource (group: Kurikulum)
[x] KurikulumController::index() — jadwal hari ini, jadwal mingguan, kalender akademik
[x] View: siswa/kurikulum/index.blade.php — 3-tab UI (Jadwal Hari Ini, Mingguan, Kalender)
    - "Sekarang" badge animasi untuk jam pelajaran yang sedang berlangsung
    - Highlight hari ini pada jadwal mingguan
    - Color-coded event badges pada kalender

-- File Baru Fase 9 --
database/migrations/2026_05_19_000001_create_subjects_table.php
database/migrations/2026_05_19_000002_create_schedules_table.php
database/migrations/2026_05_19_000003_create_academic_events_table.php
app/Models/Subject.php
app/Models/Schedule.php
app/Models/AcademicEvent.php
app/Filament/Resources/SubjectResource.php (+ Pages/)
app/Filament/Resources/ScheduleResource.php (+ Pages/)
app/Filament/Resources/AcademicEventResource.php (+ Pages/)
app/Http/Controllers/Siswa/KurikulumController.php  ← updated
resources/views/siswa/kurikulum/index.blade.php     ← rebuilt

-- Catatan Teknis --
• day: integer 1=Senin … 5=Jumat (ISO weekday, Carbon::dayOfWeekIso)
• "Sekarang" badge: Carbon::parse(start_time) → Carbon::parse(end_time) range check
• Kalender dikelompokkan by isoFormat('MMMM Y') di Blade
• AcademicEvent.color → colorClass() / dotClass() helper untuk Tailwind badge
```

**Output:** Admin input jadwal dan kalender. Siswa lihat jadwal hari ini (dengan penanda jam aktif) dan kalender akademik sekolah.

---

### FASE 10 — Modul Humas ✅ SELESAI
**Target:** v1.9 | **PIC:** Backend Dev + Frontend Dev

```
Flowchart:
ADMIN (Filament)                      SISTEM                      SISWA
  ├─ Kelola Agenda Sekolah ─────────→ school_events table          │
  │  (judul, tgl, lokasi, foto,       │                            │── Buka Humas
  │   tipe, is_published)             │                            │── Aksi Cepat: Pengumuman / Agenda / Galeri
  │                                   │                            │── Daftar Agenda Mendatang
  ├─ Buat Album Galeri ─────────────→ galleries table              │   (card dengan countdown "X hari lagi")
  │  (judul, deskripsi, event_date,   │                            │── Grid Galeri Terbaru (2 kolom)
  │   cover_photo, is_published)      │                            │
  └─ Upload Foto Album ─────────────→ gallery_photos table         │── /humas/gallery → semua album
     (via Filament Repeater)          │                            └── /humas/gallery/{id} → lightbox viewer

[x] Migrations: school_events, galleries, gallery_photos
[x] Models: SchoolEvent, Gallery (hasMany photos), GalleryPhoto
[x] Filament Admin: SchoolEventResource, GalleryResource (group: Humas)
    GalleryResource menggunakan Repeater untuk manage gallery_photos
[x] HumasController: index(), galleryIndex(), galleryShow(Gallery $gallery)
[x] Routes: GET /humas/gallery, GET /humas/gallery/{gallery}
[x] Views:
    - siswa/humas/index.blade.php — agenda + galeri preview
    - siswa/humas/gallery/index.blade.php — grid semua album
    - siswa/humas/gallery/show.blade.php — lightbox dengan keyboard nav

-- File Baru Fase 10 --
database/migrations/2026_05_19_000004_create_school_events_table.php
database/migrations/2026_05_19_000005_create_galleries_table.php  ← includes gallery_photos
app/Models/SchoolEvent.php
app/Models/Gallery.php
app/Models/GalleryPhoto.php
app/Filament/Resources/SchoolEventResource.php (+ Pages/)
app/Filament/Resources/GalleryResource.php (+ Pages/)
app/Http/Controllers/Siswa/HumasController.php  ← updated
resources/views/siswa/humas/index.blade.php     ← rebuilt
resources/views/siswa/humas/gallery/index.blade.php  ← new
resources/views/siswa/humas/gallery/show.blade.php   ← new

-- Catatan Teknis --
• Gallery.coverPhotoUrl() — fallback ke foto pertama jika cover_photo kosong
• SchoolEvent badge "X hari lagi" — ditampilkan jika ≤ 7 hari ke depan
• Lightbox JS: vanilla, keyboard support (←/→/Esc), tanpa Alpine.js
• GalleryResource Repeater: relationship('photos') → auto-save ke gallery_photos
• school_events.type: kegiatan|lomba|rapat|upacara|wisuda|lainnya
```

**Output:** Admin kelola agenda & galeri foto. Siswa melihat agenda mendatang dengan countdown dan galeri foto kegiatan sekolah lengkap dengan lightbox.

---

### FASE 7 — PWA & Optimasi Mobile ✅ SELESAI
**Target:** v2.0 | **PIC:** Frontend Dev

```
[x] Buat manifest.json (nama, icon, theme color, start URL, shortcuts)
[x] Buat service worker public/sw.js (cache-first statis, network-first navigasi, offline fallback)
[x] Halaman offline fallback (resources/views/offline.blade.php — standalone, no Vite)
[x] Install prompt banner di browser Android (beforeinstallprompt, dismissible)
[x] Kompres otomatis gambar upload (Intervention Image v3, selesai di v1.8)
[x] E-Kartu Pelajar: QR code berisi NIS siswa (chillerlan/php-qrcode v5, SVG inline)
[x] manifest link + apple-touch-icon di siswa layout <head>
[ ] Push notification: reminder presensi pagi (via OneSignal/FCM)   ← opsional, ditunda
[ ] Test di berbagai device (Android Chrome, iOS Safari)
```

**Output:** Aplikasi dapat diinstall di HP tanpa App Store. E-Kartu memiliki QR code nyata.

-- File Baru v2.0 --
public/manifest.json              → PWA manifest (standalone, theme #2563eb, shortcuts)
public/sw.js                      → Service worker (cache sims-v1, offline fallback)
resources/views/offline.blade.php → Halaman offline fallback (standalone HTML)
routes/web.php                    → Route GET /offline (cached 24h)
layouts/siswa.blade.php           → manifest link, SW registration, install banner
ProfileController.php             → QR SVG via chillerlan/php-qrcode, compact('qrSvg')

---

### FASE 8 — Testing & Deployment ✅ SELESAI (sebagian)
**Target:** v2.1 | **PIC:** Semua Tim

```
[x] Feature Test: autentikasi (AuthTest — 14 test), presensi (AttendanceTest — 8 test),
    poin (ConductTest — 10 test) → 32 test, 60 assertions, semua PASS
[x] Fix: RedirectIfAuthenticated middleware → redirect ke role-specific dashboard
[ ] UAT: test semua fitur dengan akun 4 role nyata          ← perlu dilakukan di server
[ ] Fix bug hasil UAT
[x] Setup Docker Compose production (PHP-FPM + Nginx + MariaDB + Redis)
    docker-compose.yml + docker/Dockerfile + docker/nginx/default.conf
    docker/php/local.ini + docker/mariadb/my.cnf
[ ] Konfigurasi HTTPS (SSL Let's Encrypt)                   ← setup di server
[x] Setup cron job server: scheduler berjalan via Docker container (sims-scheduler)
[x] Setup Laravel Queue worker → Supervisor config (docker/supervisor/supervisord.conf)
    + Docker container queue (sims-queue, 2 proses)
[x] Konfigurasi backup database otomatis
    Artisan: php artisan db:backup --keep=7 (SQLite + MySQL/MariaDB)
    Scheduler: dailyAt('19:00') WIB, simpan ke storage/app/backups/
[ ] Final deployment ke server                              ← perlu server production
[ ] Monitoring & dokumentasi pasca-deploy
```

-- Opsional yang Diselesaikan --
[x] Export laporan Sarpras: PDF rekap aset + PDF laporan kerusakan (dengan filter)
    Route: /guru/export/sarpras · views: exports/assets-pdf + exports/damage-pdf
[x] Bridge damage report → conduct_log: saat menyelesaikan laporan kerusakan,
    guru dapat memilih kategori poin dan mencatat poin ke siswa pelapor (opsional)

-- File Baru v2.1 --
tests/Feature/AuthTest.php           → 14 tests autentikasi & RBAC
tests/Feature/AttendanceTest.php     → 8 tests presensi, geofence, fake GPS
tests/Feature/ConductTest.php        → 10 tests poin perilaku & BK log auto-trigger
app/Http/Middleware/RedirectIfAuthenticated.php  → redirect ke dashboard by role
docker-compose.yml                   → production stack (app, nginx, db, redis, queue, scheduler)
docker/Dockerfile                    → PHP 8.2-FPM + Node + extensions
docker/nginx/default.conf            → HTTPS redirect + service worker cache control
docker/php/local.ini                 → OPcache, upload 10M, Asia/Makassar
docker/mariadb/my.cnf                → utf8mb4, innodb_buffer_pool_size 256M
docker/supervisor/supervisord.conf   → queue:work (2 procs) + scheduler loop
app/Console/Commands/BackupDatabase.php → db:backup command (SQLite + MySQL/gzip)
routes/console.php                   → Schedule::command('db:backup') daily 02:00 WIB
app/Http/Controllers/Guru/ExportController.php ← sarprasForm, assetsPdf, damagePdf
resources/views/guru/exports/sarpras-form.blade.php  ← form export sarpras
resources/views/exports/assets-pdf.blade.php          ← PDF rekap aset
resources/views/exports/damage-pdf.blade.php          ← PDF laporan kerusakan
app/Http/Controllers/Guru/SarprasController.php ← bridge conduct log on resolveDamage

**Output:** 32 test passing. Docker stack siap deploy. Queue + scheduler berjalan otomatis.
Backup database harian. Export PDF aset & kerusakan. Bridge damage → poin siswa.

---

### SPRINT 1 — Peningkatan Bertahap ✅ SELESAI
**Target:** v2.2 | **Fokus:** Kesiswaan · Humas · Sarpras

```
[x] Rekap Presensi Pribadi Siswa
    Route:  GET /siswa/attendance/history
    Logic:  navigasi bulan (←/→), ringkasan 6 status, bar chart tren 6 bulan terakhir,
            list detail harian dengan foto selfie thumbnail
    Akses:  tombol "Riwayat →" ditambah di header absensi halaman Kesiswaan
    File:   AttendanceController::history() — filter month/year, query per bulan
            resources/views/siswa/attendance/history.blade.php ← new

[x] Hubungan Galeri ↔ Event Sekolah
    Migration: 2026_05_19_100001_add_gallery_id_to_school_events_table.php
               → school_events.gallery_id (nullable FK → galleries)
    Model:  SchoolEvent::gallery() BelongsTo + gallery_id di fillable
    Admin:  SchoolEventResource form tambah Select 'Galeri Terkait' (searchable, opsional)
    View:   HumasController eager-load gallery.photos (4 foto teratas)
            humas/index.blade.php → panel galeri muncul di bawah event card jika terkait

[x] SLA Badge Laporan Kerusakan
    Model:  DamageReport::daysOpen() → int, slaLevel() → 'ok'|'warning'|'critical'
            Threshold: pending ≥3 hari = warning, ≥6 hari = critical
                       in_progress ≥7 hari = warning, ≥14 hari = critical
    View:   guru/sarpras/damage.blade.php — badge oranye/merah di mobile card & tabel
            border card berwarna sesuai level; desktop row tinted background
    Stats:  SarprasController::index() tambah stat overdue_damage
            sarpras/index.blade.php — counter "X terlambat" di bawah stat Laporan Baru

-- File Baru Sprint 1 --
database/migrations/2026_05_19_100001_add_gallery_id_to_school_events_table.php
resources/views/siswa/attendance/history.blade.php ← new

-- File Diubah Sprint 1 --
app/Http/Controllers/Siswa/AttendanceController.php   ← method history()
app/Models/DamageReport.php                           ← daysOpen(), slaLevel()
app/Models/SchoolEvent.php                            ← gallery_id fillable, gallery()
app/Filament/Resources/SchoolEventResource.php        ← gallery select field
app/Http/Controllers/Siswa/HumasController.php        ← eager-load gallery
app/Http/Controllers/Guru/SarprasController.php       ← overdue_damage stat
resources/views/siswa/kesiswaan/index.blade.php       ← "Riwayat →" link
resources/views/siswa/humas/index.blade.php           ← gallery preview on event cards
resources/views/guru/sarpras/damage.blade.php         ← SLA badge mobile + desktop
resources/views/guru/sarpras/index.blade.php          ← overdue counter
routes/web.php                                        ← GET /siswa/attendance/history
```

**Output:** Siswa bisa menelusuri riwayat presensi per bulan. Admin bisa tautkan galeri ke event.
Guru dapat melihat laporan kerusakan mana yang melewati SLA.
Tests: 34 passed (63 assertions). ✅

---

### SPRINT 2 — Pengisian Konten Kosong ✅ SELESAI
**Target:** v2.3 | **Fokus:** Prasarana · Humas · Kesiswaan

```
[x] Dashboard Prasarana Siswa
    Sebelumnya: halaman /siswa/prasarana hanya tampil "Segera Hadir" placeholder
    Sekarang:   PrasaranaController::index() load stats + active loans + recent damage
    Stats grid: pinjaman aktif, sudah dikembalikan, laporan diproses, total laporan
    Aksi cepat: Scan Aset (violet solid) + Semua Pinjaman (white card)
    Daftar:     5 pinjaman aktif terkini + 5 laporan kerusakan terbaru milik siswa
    File:       PrasaranaController.php ← rebuilt
                resources/views/siswa/prasarana/index.blade.php ← rebuilt

[x] Detail Halaman Event Sekolah
    Route:  GET /siswa/humas/events/{event} → HumasController::eventShow()
    View:   hero cover photo (atau gradient header jika tidak ada foto)
            info baris: tanggal + countdown/status + lokasi
            deskripsi lengkap (whitespace-pre-line)
            galeri terkait 3-kolom (jika event.gallery exists)
            panel "Agenda Lainnya" (4 event terdekat selain ini)
    Humas:  event cards di index sekarang clickable → menuju halaman detail
    File:   HumasController::eventShow() ← new method
            resources/views/siswa/humas/event-show.blade.php ← new
            routes/web.php ← GET /siswa/humas/events/{event}

[x] Grafik Tren Poin Perilaku Bulanan
    Logic:  ConductController::index() hitung net poin (sum) per bulan, 6 bulan terakhir
    Chart:  bar chart 6 kolom — batang hijau (positif) / merah (negatif)
            nilai net di atas tiap kolom, label bulan di bawah
            disisipkan antara ringkasan dan riwayat di halaman poin siswa
    Fix:    bg-gradient-to-br → bg-linear-to-br (Tailwind v4 canonical class)
    File:   ConductController.php ← trend data added
            resources/views/siswa/conduct/index.blade.php ← chart section added

-- File Baru Sprint 2 --
resources/views/siswa/humas/event-show.blade.php ← new
resources/views/siswa/prasarana/index.blade.php  ← rebuilt (bukan new, replace placeholder)

-- File Diubah Sprint 2 --
app/Http/Controllers/Siswa/PrasaranaController.php  ← stats + loans + damage queries
app/Http/Controllers/Siswa/HumasController.php      ← eventShow() method added
app/Http/Controllers/Siswa/ConductController.php    ← trend data
resources/views/siswa/conduct/index.blade.php       ← trend chart + Tailwind v4 fix
resources/views/siswa/humas/index.blade.php         ← event cards → clickable links
routes/web.php                                      ← GET /siswa/humas/events/{event}
```

**Output:** Halaman Prasarana siswa kini informatif. Setiap event bisa diklik untuk detail lengkap.
Halaman poin menampilkan grafik tren 6 bulan. Tests: 34 passed (63 assertions). ✅

---

### SPRINT 3 — Notifikasi, Katalog Aset & Rekap Nilai ✅ SELESAI
**Target:** v2.4 | **Fokus:** Kesiswaan · Sarpras · Kurikulum

```
[x] Notifikasi In-App Siswa
    Tabel:   app_notifications (bukan Laravel built-in notifications)
             kolom: user_id, title[120], body[500], type, url, read_at, timestamps
             index composite: (user_id, read_at)
    Model:   AppNotification — scopeUnread(), scopeForUser(), isRead(), iconClass()
    Service: NotificationService::send(userId, title, body, type, url) → AppNotification
    Routes:  GET  /siswa/notifications              → NotificationController::index()
             PATCH /siswa/notifications/read-all    → markAllRead()
             PATCH /siswa/notifications/{n}/read    → markRead() → redirect ke url
    Layout:  Bell icon + badge (9+ if >9) di header siswa — hitung unread via inline Blade
    Hooks:   Guru approve izin   → NotificationService::send() type 'success'
             Guru tolak izin     → NotificationService::send() type 'warning' + rejection_note
             Guru catat poin     → NotificationService::send() type success|warning per poin
             Guru approve peminjaman → NotificationService::send() type 'success'
             Guru tolak peminjaman   → NotificationService::send() type 'warning'
    File:    database/migrations/2026_05_19_200001_create_app_notifications_table.php ← new
             app/Models/AppNotification.php ← new
             app/Services/NotificationService.php ← new
             app/Http/Controllers/Siswa/NotificationController.php ← new
             resources/views/siswa/notifications/index.blade.php ← new
             resources/views/layouts/siswa.blade.php ← bell badge added
             app/Http/Controllers/Guru/AttendanceController.php ← hooks added
             app/Http/Controllers/Guru/ConductController.php ← hook added
             app/Http/Controllers/Guru/SarprasController.php ← hooks added

[x] Katalog Aset Publik Siswa
    Route:   GET /siswa/sarpras/catalog → SarprasController::catalog()
    Filter:  q (name LIKE), category, condition, room_id
    View:    search bar + 3 dropdown filter + 2-kolom grid + paginate(20)
             tiap card: nama, kategori badge, kondisi badge, nomor seri, ruangan
    Akses:   quick action "Katalog Aset" ditambahkan di /siswa/prasarana dashboard
    File:    app/Http/Controllers/Siswa/SarprasController.php ← catalog() added
             resources/views/siswa/sarpras/catalog.blade.php ← new
             resources/views/siswa/prasarana/index.blade.php ← quick action added

[x] Rekap Nilai Siswa
    Tabel:   student_grades (student_id, subject_id, score decimal(5,2), type UH|UTS|UAS,
             semester, academic_year, notes, recorded_by)
    Model:   StudentGrade — typeLabel(), scoreColor(), currentAcademicYear(), currentSemester()
    Admin:   StudentGradeResource (Filament) — group 'Kurikulum'
             form: student, subject, type, score, semester, academic_year, notes
             table: warna score (hijau ≥80, kuning ≥65, merah <65)
    Logic:   currentAcademicYear(): bulan ≥7 → tahun ini/tahun+1, sebaliknya tahun-1/tahun ini
             currentSemester():     bulan ≥7 → semester 1, sebaliknya semester 2
             Weighted avg: UH 40% · UTS 30% · UAS 30% (proporsional jika ada komponen kosong)
    Tab:     Tab "Nilai" ditambahkan ke halaman /siswa/kurikulum
             Per mapel: bar progress per skor, rata UH jika >1 UH, weighted average di header
             Bagian bawah: rata-rata keseluruhan semua mapel
    File:    database/migrations/2026_05_19_200002_create_student_grades_table.php ← new
             app/Models/StudentGrade.php ← new
             app/Filament/Resources/StudentGradeResource.php ← new
             app/Filament/Resources/StudentGradeResource/Pages/{List,Create,Edit}StudentGrade.php
             app/Http/Controllers/Siswa/KurikulumController.php ← grades query added
             resources/views/siswa/kurikulum/index.blade.php ← tab 'Nilai' panel added
```

**Output:** Siswa menerima notifikasi real-time untuk izin, poin, dan peminjaman. Katalog aset
bisa dicari dan difilter. Tab Nilai di kurikulum menampilkan rekap UH/UTS/UAS per mapel
dengan rata-rata tertimbang. Tests: 34 passed (63 assertions). ✅

---

### SPRINT 4 — BK Dashboard, Rekap Absensi & Export Nilai ✅ SELESAI
**Target:** v2.5 | **Fokus:** Kesiswaan Guru · Absensi Guru · Kurikulum Export

```
[x] BK Dashboard Guru
    Route:   GET  /guru/bk              → BkController::index()
             POST /guru/bk/log          → BkController::storeLog()
    View:    Filter per kelas, 4-stat card (total siswa, perlu perhatian, alert hari ini, catatan manual)
             Daftar siswa dengan BK log (nama, NIS, jumlah catatan, total poin, link ke detail)
             Riwayat 30 catatan BK terbaru (auto vs manual badge, tanggal, poin saat itu)
             Modal: tambah catatan manual (pilih siswa, tanggal, uraian pembinaan)
             Toast konfirmasi setelah simpan
    File:    app/Http/Controllers/Guru/BkController.php ← new
             resources/views/guru/bk/index.blade.php ← new
             routes/web.php ← /guru/bk routes added

[x] Rekap Absensi Bulanan Guru
    Route:   GET /guru/attendance/rekap → AttendanceController::rekap()
    Filter:  kelas + bulan + tahun (dropdown)
    Logic:   Query attendance whereHas(user.class_id), groupBy user_id
             Hanya hari kerja Senin–Jumat masuk sebagai kolom
             Per siswa: warna-kode tiap hari (H/T/I/S/A/D) + summary count di akhir baris
    View:    Tabel horizontal scroll — kolom: nama | hari-1 … hari-N | H|T|I|S|A|D count
             Legend warna, info jumlah hari sekolah, link kembali ke absensi harian
    File:    app/Http/Controllers/Guru/AttendanceController.php ← rekap() added
             resources/views/guru/attendance/rekap.blade.php ← new
             routes/web.php ← GET /guru/attendance/rekap

[x] Export Nilai Siswa (Guru)
    Routes:  GET /guru/export/grades          → gradesForm()
             GET /guru/export/grades/pdf      → gradesPdf()
             GET /guru/export/grades/excel    → gradesExcel()
    Filter:  kelas (required), semester (1/2), tahun ajaran
    PDF:     Header sekolah + logo, per siswa: tabel UH1/UH2/UH3/rata-UH/UTS/UAS/Rerata
             Warna score: hijau ≥80, kuning ≥65, merah <65
    Excel:   StudentGradeExport — baris per siswa×mapel, kolom rata-UH/UTS/UAS/semester/TA
    File:    app/Exports/StudentGradeExport.php ← new
             app/Http/Controllers/Guru/ExportController.php ← 3 methods added
             resources/views/guru/exports/grades-form.blade.php ← new
             resources/views/exports/grades-pdf.blade.php ← new
             routes/web.php ← 3 export/grades routes added
```

**Output:** Guru BK punya dashboard khusus siswa bermasalah + catat pembinaan manual.
Rekap absensi bulanan dalam grid visual per hari. Export nilai ke PDF/Excel per kelas.
Tests: 34 passed (63 assertions). ✅

---

### SPRINT 5 — Dashboard Nyata & Guru Input Nilai ✅ SELESAI
**Target:** v2.6 | **Fokus:** Dashboard · Kurikulum Guru

```
[x] Guru Dashboard — Data Nyata (replace dummy hardcoded data)
    Sebelumnya: stats & list pakai data hardcoded (Ahmad Fauzi, Budi Santoso, dll.)
    Sekarang:   query nyata semua statistik dan daftar

    Stats: hadir+terlambat / alpa / izin / sakit — query Attendance.whereDate(today)
           filtered by homeroomClass.id
    Alert Kritis: User.withSum(conductLogs, point).having(≤ -75) per kelas walikelas
    Rekap hari ini: per siswa di kelas, status dari DB atau 'alpa' jika tidak ada record
    Quick Actions bar: Rekap Absensi · Dashboard BK · Izin Pending (badge count) ·
                       Input Nilai · Export Nilai
    File: app/Http/Controllers/Guru/DashboardController.php ← rewritten
          resources/views/guru/dashboard.blade.php ← quick actions bar added

[x] Siswa Dashboard — Data Nyata (replace dummy hardcoded data)
    Sebelumnya: status presensi, poin, riwayat, pengumuman semua hardcoded
    Sekarang:   query nyata semua data + unread notification banner

    Presensi hari ini: Attendance::where(user_id)->whereDate(today)
                       → status label + jam check-in atau 'Belum Presensi'
    Ringkasan Poin: sum(conductLogs.point>0) prestasi, abs(sum<0) pelanggaran, total net
    Riwayat 3 terbaru: conductLogs.with(category).latest().take(3)
    Pengumuman: Announcement::published()->forRole('siswa')->orderByDesc(pinned)->limit(5)
                Cards sekarang clickable → link ke detail pengumuman
    Notif Banner: jika ada unread AppNotification → banner biru dengan count + link
    File: app/Http/Controllers/Siswa/DashboardController.php ← rewritten
          resources/views/siswa/dashboard.blade.php ← notif banner + live links

[x] Guru Input Nilai per Kelas (non-Filament)
    Route:  GET  /guru/grades          → GradeController::index()
            POST /guru/grades          → GradeController::store()
            DELETE /guru/grades/{grade} → GradeController::destroy()
    Logic:  updateOrCreate(student+subject+type+semester+TA) → upsert aman, tidak duplikat
    Filter: kelas + semester + tahun ajaran (default = current)
    Form:   dropdown siswa, mapel, tipe (UH/UTS/UAS), nilai 0–100, catatan opsional
    Rekap:  per siswa → per mapel → badge tipe+nilai berwarna (hijau/kuning/merah) + tombol hapus
    Access: link "Input Nilai" di quick actions dashboard guru
    File:   app/Http/Controllers/Guru/GradeController.php ← new
            resources/views/guru/grades/index.blade.php ← new
            routes/web.php ← /guru/grades routes added
```

**Output:** Kedua dashboard sekarang menampilkan data nyata dari database.
Guru dapat input/update/hapus nilai per kelas langsung dari web app tanpa Filament.
Tests: 34 passed (63 assertions). ✅

---

### SPRINT 6 — Nav Guru · Rapor PDF · Absensi Manual ✅ SELESAI
**Target:** v2.7 | **Fokus:** UX Guru · Kurikulum Siswa · Override Absensi

```
[x] Update Sidebar Guru (layouts/guru.blade.php)
    Perubahan navigasi:
    • Group "Presensi": Absensi Harian (ganti label dari "Rekap Absensi") + Rekap Bulanan (baru)
    • Group "Poin & Perilaku": tambah "Dashboard BK" di bawah "Rekap Poin"
    • Group baru "Kurikulum": Input Nilai
    • Group "Laporan": tambah Export Nilai + Export Sarpras
    File:   resources/views/layouts/guru.blade.php ← nav groups updated

[x] Download Rapor PDF Siswa
    Route:  GET /siswa/kurikulum/rapor → KurikulumController::rapor()
    Logic:  Load student_grades untuk siswa login (semester + academic_year saat ini)
            subjects = Subject::whereIn(id, grades.keys())
            Pdf::loadView('siswa.kurikulum.rapor-pdf', [...]).setPaper('a4').download(filename)
    PDF:    Header sekolah + logo, student info 4-kolom (Nama/NIS/Kelas/Tanggal Cetak)
            Tabel: No | Mata Pelajaran | UH1 | UH2 | UH3 | Rata-UH | UTS | UAS | Nilai Akhir
            Color-coding: ≥80 hijau, 65–79 kuning, <65 merah
            Summary boxes: jumlah mapel ≥80, 65–79, <65, rata-rata keseluruhan
            Tanda tangan Wali Kelas + watermark SIMS di bawah
    Tombol: Muncul di tab "Nilai" kurikulum siswa jika ada data grades
            "Rapor PDF" link (emerald) di kanan header panel nilai
    File:   app/Http/Controllers/Siswa/KurikulumController.php ← rapor() added
            resources/views/siswa/kurikulum/rapor-pdf.blade.php ← new
            resources/views/siswa/kurikulum/index.blade.php ← download button added
            routes/web.php ← GET /siswa/kurikulum/rapor added

[x] Absensi Manual Guru (Override Status)
    Route:  POST /guru/attendance/manual → AttendanceController::manual()
    Logic:  validate: student_id exists, date ≤ today, status in [hadir|terlambat|izin|sakit|alpa|dispensasi]
            Attendance::updateOrCreate([user_id, date], [status, check_in_time])
            check_in_time = now() jika hadir/terlambat, null untuk status lain
    UI:     Tombol "Input Manual" (emerald) di filter bar atas halaman absensi harian
            Tombol "Ubah" (emerald) per baris di tabel desktop + per card di mobile
            Modal form: dropdown siswa (pre-filled saat klik "Ubah"), date (pre-filled), status
            JS openManualModal(studentId, date) / closeManualModal() toggle flex/hidden
            Toast success setelah redirect back (session flash 'success')
    Fix:    Removed unused import App\Models\Dispensation
            $request->get() → $request->input() (deprecated fix)
    File:   app/Http/Controllers/Guru/AttendanceController.php ← manual() added + fixes
            resources/views/guru/attendance/index.blade.php ← modal + buttons + toast

-- File Baru Sprint 6 --
resources/views/siswa/kurikulum/rapor-pdf.blade.php ← new

-- File Diubah Sprint 6 --
app/Http/Controllers/Guru/AttendanceController.php   ← manual(), fix deprecated, remove unused import
app/Http/Controllers/Siswa/KurikulumController.php   ← rapor() method
resources/views/guru/attendance/index.blade.php      ← modal + per-row edit buttons + flash toast
resources/views/siswa/kurikulum/index.blade.php      ← Rapor PDF button in nilai tab
resources/views/layouts/guru.blade.php               ← nav structure updated
routes/web.php                                       ← /guru/attendance/manual + /siswa/kurikulum/rapor

-- Catatan Teknis --
• Modal CSS fix: hidden+flex conflict → remove 'flex' from static classes, toggle via JS classList
• PDF filename: rapor_{name}_{semN}.pdf (spaces → underscores)
• Rapor PDF hanya berisi data semester aktif (currentAcademicYear + currentSemester)
• Manual override bersifat upsert — aman dipanggil berulang untuk hari yang sama
• Tombol "Ubah" di setiap baris preselects student_id di modal dropdown
```

**Output:** Guru dapat override status absensi siswa kapan saja via modal. Siswa dapat unduh
rapor nilai semester berjalan sebagai PDF. Navigasi guru lebih lengkap dan terstruktur.
Tests: 34 passed (63 assertions). ✅

---

## ⚠️ Cara Menampilkan Kembali Sistem Poin

> Sistem poin **disembunyikan sementara** dari UI karena saat ini hanya digunakan
> untuk mencatat prestasi dan pelanggaran (tanpa menampilkan nilai/skor poin).
> Seluruh kode, route, model, dan data tetap ada — hanya tampilan yang dinonaktifkan.

### Langkah Restorasi (4 file)

**1. `resources/views/layouts/guru.blade.php`**
Cari komentar `[SISTEM POIN DISABLED]` pada array `$navGroups` (sekitar baris 88–95).
Hapus `//` dari setiap baris array group "Poin & Perilaku" → grup akan muncul kembali di sidebar.
Lakukan hal yang sama untuk baris "Export Poin" di group "Laporan".

**2. `resources/views/siswa/dashboard.blade.php`**
Cari dua blok `@if(false) {{-- SISTEM POIN: ... --}}` ... `@endif`.
Untuk masing-masing blok: ganti `@if(false)` menjadi `@if(true)`. Konten di dalamnya akan langsung aktif.

**3. `resources/views/siswa/kesiswaan/index.blade.php`**
Dua perubahan:
- Grid kolom: ubah `grid-cols-1` kembali ke `grid-cols-2` (baris tepat setelah komentar disabled)
- Card Poin Perilaku: cari `{{-- [SISTEM POIN DISABLED] Card Poin Perilaku ...` dan hapus
  komentar pembuka + penutup (`--}}`) untuk mengaktifkan kembali card tersebut
- Nilai poin di tab Pelanggaran: cari `{{-- [SISTEM POIN DISABLED] Hapus komentar Blade ...`
  dan hapus pembuka + penutup komentar untuk menampilkan badge angka poin

**4. Verifikasi**
Setelah uncomment, jalankan `php artisan test` untuk memastikan tidak ada regresi.
Route yang terkait (sudah aktif sepanjang waktu, tidak perlu diubah):
- GET  /guru/conduct              → ConductController::index()
- GET  /guru/conduct/create       → ConductController::create()
- POST /guru/conduct              → ConductController::store()
- GET  /guru/bk                   → BkController::index()
- GET  /guru/export/conduct       → ExportController::conductForm()
- GET  /siswa/conduct             → Siswa\ConductController::index()

---

## 6. Alur Kerja Tim

```
1. Setiap developer ambil task dari fase yang sedang berjalan
2. Buat branch baru (lihat aturan branching di bawah)
3. Kerjakan fitur + buat migration jika perlu
4. Test lokal (php artisan test)
5. Push branch & buat Pull Request ke branch develop
6. Code review oleh 1 developer lain
7. Merge ke develop setelah approved
8. Lead Dev merge develop → main setiap akhir fase
```

---

## 7. Aturan Branching Git

```
main          → kode production (stabil, sudah tested)
develop       → integrasi semua fitur sebelum ke main
feat/xxx      → fitur baru          contoh: feat/selfie-attendance
fix/xxx       → perbaikan bug       contoh: fix/auto-alpa-scheduler
chore/xxx     → non-fitur (config)  contoh: chore/docker-setup
```

> **Aturan:** Jangan pernah push langsung ke `main`. Semua perubahan lewat Pull Request.

---

## 8. Perintah Penting

```bash
# Setup awal
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed

# Development
php artisan serve
npm run dev

# Docker
docker compose up -d
docker compose exec app php artisan migrate --seed

# Testing
php artisan test
php artisan test --filter NamaTest

# Scheduler (test manual)
php artisan schedule:run
php artisan attendance:auto-alpa         # command custom Fase 3 (skip weekend+holiday)

# Queue
php artisan queue:work
php artisan queue:failed                 # lihat job yang gagal

# Sarpras — Generate QR (Fase 5)
php artisan assets:generate-qr           # generate QR untuk semua aset
php artisan assets:generate-qr --force   # regenerate ulang semua QR
php artisan storage:link                 # buat symlink public/storage (wajib sekali)

# E-Voting (Fase 6)
php artisan voting:close-expired         # tutup sesi voting yang sudah melewati end_time

# Production cron (tambahkan di crontab server)
# * * * * * cd /var/www/sims && php artisan schedule:run >> /dev/null 2>&1
```

---

## 9. Integrasi Lintas Modul

| Trigger | Aksi Otomatis | Modul |
|---------|---------------|-------|
| Siswa tidak presensi hingga 08:00 | Status berubah jadi **Alpa** | Presensi |
| Izin/Dispensasi diapprove | Alpa pada hari itu **dibatalkan** | Presensi → Izin |
| Poin pelanggaran ≤ -75 | Entry otomatis di **dashboard BK** | Poin → BK |
| Poin dicatat oleh Guru | **Notifikasi WA** dikirim ke orang tua *(planned — Baileys)* | Poin → WA Gateway |
| Damage report dikaitkan ke siswa | **Poin pelanggaran** otomatis dicatat | Sarpras → Poin |
| Damage report pending ≥3 hari | **SLA badge warning** muncul di dashboard guru | Sarpras → SLA |
| Gallery ditautkan ke SchoolEvent | **Foto galeri ditampilkan** di halaman event siswa | Humas → Galeri |
| Voting end_time tercapai | Sesi voting **otomatis ditutup** | Voting → Scheduler |
| User login / logout | Dicatat ke **activity_logs** (IP + user agent) | Auth → Audit Log |
| Admin import Excel | Dicatat ke **activity_logs** dengan jumlah baris | Import → Audit Log |
| Guru approve/tolak izin | **Notifikasi in-app** dikirim ke siswa (type success/warning) | Kesiswaan → Notifikasi |
| Guru catat poin | **Notifikasi in-app** dikirim ke siswa (positif=success, negatif=warning) | Poin → Notifikasi |
| Guru approve/tolak pinjaman | **Notifikasi in-app** dikirim ke siswa | Sarpras → Notifikasi |
| Guru input nilai | **Rekap nilai** tampil di tab Nilai halaman kurikulum siswa | Kurikulum → Nilai |

---

## 10. Checklist Go-Live

```
[ ] Semua fase 1–8 selesai dan di-merge ke main
[ ] Test dengan data nyata (guru & siswa asli)
[ ] HTTPS aktif di domain resmi sekolah
[ ] Backup database terjadwal (harian)
[ ] Queue worker berjalan via Supervisor
[ ] Cron job aktif di server
[ ] Dokumentasi user (manual penggunaan) tersedia
[ ] Nomor kontak support tersedia untuk guru & siswa
```

---

> **Pertanyaan atau kendala?** Diskusikan di channel tim sebelum mengubah logika inti  
> (scheduler, observer, geofencing). Perubahan pada sistem tersebut berdampak ke semua role.

---

## 11. Konsistensi Tampilan Web ↔ Flutter

> **Aturan utama:** Setiap halaman Flutter HARUS terlihat identik dengan halaman web yang setara.  
> Perubahan desain pada web WAJIB diikuti perubahan pada Flutter, dan sebaliknya.

---

### 11.1 Arsitektur Akses Ganda

```
Pengguna
  ├── Browser  → Laravel (Blade/Livewire/Tailwind)  → API sama
  └── Flutter  → Native Android/iOS                 → API sama
```

Kedua akses mengonsumsi API yang sama (`/api/v1/...`). Token JWT digunakan di keduanya.  
Tampilan harus konsisten — siswa/guru tidak boleh merasa UI berbeda tergantung platform.

---

### 11.2 Design System Token

Semua token desain web (Tailwind CSS) dipetakan ke konstanta Dart di `mobile/lib/theme/`.

| Token Tailwind            | Nilai Hex   | Konstanta Dart            | File                 |
|---------------------------|-------------|---------------------------|----------------------|
| `blue-600`                | `#2563EB`   | `AppColors.blue600`       | `app_colors.dart`    |
| `blue-700`                | `#1D4ED8`   | `AppColors.blue700`       | `app_colors.dart`    |
| `indigo-700`              | `#4338CA`   | `AppColors.indigo700`     | `app_colors.dart`    |
| `indigo-800`              | `#3730A3`   | `AppColors.indigo800`     | `app_colors.dart`    |
| `gray-50`                 | `#F9FAFB`   | `AppColors.gray50`        | `app_colors.dart`    |
| `gray-100`                | `#F3F4F6`   | `AppColors.gray100`       | `app_colors.dart`    |
| `gray-200`                | `#E5E7EB`   | `AppColors.gray200`       | `app_colors.dart`    |
| `gray-400`                | `#9CA3AF`   | `AppColors.gray400`       | `app_colors.dart`    |
| `gray-700`                | `#374151`   | `AppColors.gray700`       | `app_colors.dart`    |
| `gray-800`                | `#1F2937`   | `AppColors.gray800`       | `app_colors.dart`    |
| `slate-100`               | `#F1F5F9`   | `AppColors.slate100`      | `app_colors.dart`    |
| `green-500`               | `#22C55E`   | `AppColors.green500`      | `app_colors.dart`    |
| `red-500`                 | `#EF4444`   | `AppColors.red500`        | `app_colors.dart`    |
| `rounded-xl` (12px)       | —           | `AppRadius.button/input`  | `app_colors.dart`    |
| `rounded-2xl` (16px)      | —           | `AppRadius.card`          | `app_colors.dart`    |
| `from-blue-600 to-indigo-700` | —       | `AppColors.primaryGradient` | `app_colors.dart`  |
| `from-blue-600 to-indigo-800` | —       | `AppColors.loginGradient` | `app_colors.dart`    |

**Aturan:** Jangan pernah hard-code warna hex di file screen. Selalu gunakan `AppColors.*`.

---

### 11.3 Perbandingan Halaman per Halaman

#### Halaman Login

| Elemen Web                                    | Status Flutter | Keterangan                                   |
|-----------------------------------------------|:--------------:|----------------------------------------------|
| Panel kiri biru gradient (`blue-600→indigo-800`) | ✅          | `AppColors.loginGradient`                    |
| Logo sekolah dalam lingkaran 80px             | ✅             | `assets/images/logo_sekolah.png`             |
| Nama sekolah uppercase + letter-spacing       | ✅             | `AppConfig.schoolName.toUpperCase()`         |
| Visi 4 baris + moto italic                    | ✅             | Teks lengkap di `_BluePanelTop`              |
| Dekorasi blur circle pojok                    | ✅             | `_BlurCircle` widget                         |
| Panel kanan putih + logo horizontal kecil     | ✅             | `_WhitePanelBottom`                          |
| Input NIS/Email dengan ikon prefix            | ✅             | `_SIMSInput`                                 |
| Input password + toggle visibility            | ✅             | `_SIMSInput` + `suffixIcon`                  |
| Tombol Login gradient biru + loading spinner  | ✅             | `_GradientButton`                            |
| Footer copyright                              | ✅             | Teks statis di bawah form                    |
| Background `slate-100`                        | ✅             | `AppColors.slate100`                         |

**Estimasi kecocokan:** ~95%

---

#### Halaman Dashboard (Beranda)

| Elemen Web                                    | Status Flutter | Keterangan                                   |
|-----------------------------------------------|:--------------:|----------------------------------------------|
| Header putih 56px + border bawah              | ✅             | `_TopHeader` widget                          |
| Logo + nama sekolah di header                 | ✅             | Asset + teks 2 baris                         |
| Judul halaman tengah                          | ✅             | "Beranda"                                    |
| Ikon notifikasi + avatar user                 | ✅             | `_UserAvatar` (inisial atau foto)            |
| Logout via long-press avatar                  | ✅             | `showDialog` konfirmasi                      |
| Greeting card gradient primer                 | ✅             | `AppColors.primaryGradient`                  |
| Foto/inisial user di greeting card            | ✅             | 56px `rounded-2xl`                           |
| Tanggal + sapaan + kelas + NIS                | ✅             | `_GreetingCard`                              |
| Card status Check-in (hijau saat sudah)       | ✅             | `_CheckInCard` / `_AttCard`                  |
| Card status Check-out (hijau saat sudah)      | ✅             | `_CheckOutCard` / `_AttCard`                 |
| Tombol riwayat presensi → halaman history     | ✅             | `_HistoryButton`                             |
| Bottom nav 5 item + tombol presensi melayang  | ✅             | `_BottomNav` + `Transform.translate`         |
| Dot absensi bulanan (kalender mini)           | ✅             | `_MiniCalendar` widget + `fetchCurrentMonthDots()` |
| Foto selfie thumbnail di card check-in/out    | ✅             | `_PhotoBox` + `check_in_photo_url` di API    |
| Badge notifikasi di ikon bel                  | ✅             | `NotificationProvider` + `Consumer` badge    |
| Seksi pengumuman/berita                       | ✅             | `_AnnouncementSection` + `/announcements` API |

**Estimasi kecocokan:** ~95%

---

#### Halaman Presensi (Absensi)

| Elemen Web                                    | Status Flutter | Keterangan                                   |
|-----------------------------------------------|:--------------:|----------------------------------------------|
| Validasi geolokasi sebelum kamera             | ✅             | Phase 1: `_verifyLocation()`                 |
| Loading state saat verifikasi GPS             | ✅             | `_Phase.loading` + spinner                   |
| Error GPS (tidak aktif / ditolak / palsu)     | ✅             | `_Phase.locationError` + pesan spesifik      |
| Error di luar area + jarak actual vs radius   | ✅             | `_locationErrorBody` dengan meter            |
| Halaman konfirmasi lokasi OK (hijau)          | ✅             | `_Phase.locationOk` + `_LocationInfoCard`    |
| Info nama sekolah + radius + akurasi GPS      | ✅             | `_LocationInfoCard`                          |
| Tombol lanjut ke kamera                       | ✅             | Gradient button "Lanjut Ambil Foto →"        |
| Kamera selfie + oval face guide               | ✅             | `_Phase.camera` + `_FaceOvalOverlay`         |
| GPS badge saat kamera aktif                   | ✅             | `_GpsBadge` (pill kecil pojok kiri atas)     |
| Tombol capture + preview foto                 | ✅             | `_CaptureButton` + `_capturedPhoto` state    |
| Tombol "Ulang" dan "Kirim Absensi"            | ✅             | `_ConfirmRow`                                |
| Loading state saat submit                     | ✅             | `provider.isSubmitting`                      |
| Snackbar sukses/gagal setelah submit          | ✅             | `ScaffoldMessenger`                          |

**Estimasi kecocokan:** ~95%

---

#### Halaman Riwayat Presensi

| Elemen Web                                    | Status Flutter | Keterangan                                   |
|-----------------------------------------------|:--------------:|----------------------------------------------|
| Header gradient primer + navigasi bulan       | ✅             | `_GradientHeader` + prev/next button         |
| Judul bulan + tahun di tengah                 | ✅             | Format Indonesia (`MMMM yyyy`)               |
| Grid 6 statistik (Hadir, Terlambat, dst.)     | ✅             | `_SummaryCell` × 6 dalam `GridView`          |
| Daftar record harian dengan dot warna status  | ✅             | `_RecordCard` + status color system          |
| Warna badge per status (hadir/terlambat/dst.) | ✅             | Dart 3 record destructuring di `_statusColors` |
| Waktu check-in + check-out per record         | ✅             | Ditampilkan di `_RecordCard`                 |
| State kosong saat tidak ada data              | ✅             | Empty state widget                           |
| Foto selfie thumbnail per record              | ✅             | `_Thumbnail` widget + `check_in_photo_url` di model |
| Bar chart tren 6 bulan                        | ❌             | Belum ada — perlu package `fl_chart` + endpoint agregat |

**Estimasi kecocokan:** ~90%

---

#### Tab Kesiswaan

| Elemen                                        | Status Flutter | Keterangan                                              |
|-----------------------------------------------|:--------------:|---------------------------------------------------------|
| Hub halaman Kesiswaan (daftar fitur)          | ✅             | `KesiswaanScreen` — SingleChildScrollView + `_FeatureCard` |
| Card Ekstrakurikuler + badge jumlah aktif     | ✅             | `_ExtraBadge` dari `ExtracurricularProvider`            |
| Card Tata Tertib Sekolah                      | ✅             | Push ke `SchoolRegulationScreen`                        |
| Tab "Ekstra Saya" + badge status keanggotaan  | ✅             | `_MyExtrasTab` — warna dari `AppColors.amber/green`    |
| Tab "Jelajahi" + tombol Daftar/Menunggu       | ✅             | `_BrowseTab` — search bar + join button per card        |
| Tab "Sesi" + filter Mendatang / Sudah Lewat   | ✅             | `_SessionsTab` + `SegmentedButton`                     |
| FAB buat sesi (hanya Ketua)                   | ✅             | `_CreateSessionFab` — visible jika `isKetua == true`   |
| Detail sesi + toggle Hadir/Alpa per anggota   | ✅             | `SessionDetailScreen` — `_AttendanceToggle` optimistik  |
| Buka / tutup sesi absen                       | ✅             | `_KetuaActions` — tombol toggle is_open                |
| Form buat sesi (date, time, lokasi)           | ✅             | `CreateSessionScreen` — validasi end > start           |
| Tata tertib per kategori (collapsible)        | ✅             | `_CategorySection` — expanded by default               |
| Detail peraturan (tap to expand)              | ✅             | `_RegulationTile` — accordion per aturan               |
| Warna per kategori tata tertib                | ✅             | kehadiran=blue · berpakaian=amber · perilaku=green · larangan=red |

**Estimasi kecocokan:** ~95%

---

### 11.4 Gap List & Prioritas

| # | Gap                                   | Prioritas | Status | File yang Perlu Diubah                                      |
|---|---------------------------------------|:---------:|:------:|-------------------------------------------------------------|
| 1 | Foto selfie di card dashboard         | Tinggi    | ✅ Done | `AttendanceController.php`, `attendance.dart`, `home_screen.dart` |
| 2 | Dot kalender absensi bulanan          | Sedang    | ✅ Done | `home_screen.dart` `_MiniCalendar`                         |
| 3 | Badge notifikasi di ikon bel          | Sedang    | ✅ Done | `NotificationProvider`, `notification_service.dart`         |
| 4 | Foto selfie thumbnail di history      | Sedang    | ✅ Done | `attendance.dart`, `history_screen.dart` `_Thumbnail`       |
| 5 | Seksi pengumuman di dashboard         | Rendah    | ✅ Done | `AnnouncementController.php`, `announcement.dart`           |
| 6 | Fitur Ekstrakurikuler (Kesiswaan)     | Tinggi    | ✅ Done | Lihat **Section 12** — semua file live                      |
| 7 | Bar chart tren 6 bulan di history     | Rendah    | ❌ Todo | Package `fl_chart` + endpoint `/attendance/summary`         |
| 8 | "Ingat saya" di login                 | Rendah    | ✅ Done | Otomatis via `ApiClient.saveToken()` + `_AppGate`           |
| 9 | Tata Tertib Sekolah (Kesiswaan)       | Sedang    | ✅ Done | Lihat **Section 13** — migration, model, Filament, API, Flutter |

---

### 11.5 Aturan Pengembangan UI

1. **Selalu gunakan token desain** — Tidak boleh ada warna hex literal di file screen.  
   Gunakan `AppColors.*`, `AppRadius.*`, `AppShadow.*`.

2. **Gradient sesuai konteks:**
   - Login panel biru: `AppColors.loginGradient` (`blue-600 → indigo-800`)
   - Dashboard, history header, tombol utama: `AppColors.primaryGradient` (`blue-600 → indigo-700`)

3. **Tambahkan komentar referensi web** pada widget utama:
   ```dart
   // Web: from-blue-600 via-blue-700 to-indigo-800 (login panel kiri)
   decoration: const BoxDecoration(gradient: AppColors.loginGradient),
   ```

4. **Perubahan desain web = PR wajib Flutter** — Setiap merge perubahan CSS/Tailwind di web  
   yang mengubah warna, radius, atau layout HARUS diikuti commit Flutter yang menyinkronkan.

5. **Ukuran referensi:**
   - Header tinggi: 56px
   - Card padding: 16–28px
   - Avatar/logo lingkaran: 32–56px
   - Tombol tinggi: 44px
   - Bottom nav item: 52px (pusat presensi melayang)

---

### 11.6 Pemetaan File Web → Flutter

| Halaman / Backend Web                          | File Flutter                                                    |
|------------------------------------------------|-----------------------------------------------------------------|
| `resources/views/auth/login.blade.php`         | `mobile/lib/screens/login_screen.dart`                          |
| `resources/views/dashboard/index.blade.php`    | `mobile/lib/screens/home_screen.dart`                           |
| `resources/views/attendance/index.blade.php`   | `mobile/lib/screens/attendance/attendance_screen.dart`          |
| `resources/views/attendance/history.blade.php` | `mobile/lib/screens/attendance/history_screen.dart`             |
| `app/Http/Controllers/Api/AttendanceController.php` | `mobile/lib/services/attendance_service.dart`              |
| `app/Http/Controllers/Api/NotificationController.php` | `mobile/lib/services/notification_service.dart`          |
| `app/Http/Controllers/Api/AnnouncementController.php` | `mobile/lib/services/notification_service.dart`          |
| `app/Models/Attendance.php`                    | `mobile/lib/models/attendance.dart`                             |
| `app/Models/AppNotification.php`               | `mobile/lib/models/notification_item.dart`                      |
| `app/Models/Announcement.php`                  | `mobile/lib/models/announcement.dart`                           |
| `app/Models/Extracurricular.php`               | `mobile/lib/models/extracurricular.dart`                        |
| `app/Http/Controllers/Api/ExtracurricularController.php` | `mobile/lib/services/extracurricular_service.dart`    |
| `app/Http/Controllers/Api/ExtracurricularSessionController.php` | `mobile/lib/services/extracurricular_service.dart` |
| `app/Models/SchoolRegulation.php`              | `mobile/lib/models/school_regulation.dart`                      |
| `app/Http/Controllers/Api/SchoolRegulationController.php` | `mobile/lib/services/regulation_service.dart`        |
| `app/Filament/Resources/SchoolRegulationResource.php` | `mobile/lib/screens/kesiswaan/school_regulation_screen.dart` |
| Tailwind CSS config / `app.css`                | `mobile/lib/theme/app_colors.dart`                              |

---

## 12. Fitur Ekstrakurikuler

> **Aturan UI:** Tampilan web (Filament/Blade) dan Flutter HARUS identik dalam hal warna, layout card, status badge, dan alur persetujuan. Gunakan token desain yang sama (`AppColors.*`, `AppRadius.*`).

---

### 12.1 Ringkasan Fitur

| Aktor | Hak Akses |
|-------|-----------|
| **Admin** | Input daftar ekstra, assign guru pembina, tunjuk ketua siswa, approve/reject join & leave |
| **Guru Pembina** | Buat sesi absen, centang kehadiran anggota, approve/reject join & leave, download rekap |
| **Ketua Ekstra** | Buat sesi absen, centang kehadiran anggota, approve/reject join & leave |
| **Siswa** | Browse ekstra, ajukan join, ajukan leave, lihat sesi, tidak bisa absen sendiri |

**Catatan penting:**
- Absen ekstra dilakukan **oleh ketua/pembina** (centang per siswa), bukan self-check-in
- Siswa boleh ikut ekstra **sebanyak-banyaknya** (tidak ada batas)
- Join dan leave **memerlukan persetujuan** dari admin / guru pembina / ketua ekstra
- Rekap dapat didownload dalam format **Excel dan PDF**

---

### 12.2 Database Schema

```sql
-- Daftar ekstrakurikuler yang ada di sekolah
extracurriculars
  id, name, description, logo (nullable, path),
  pembina_id → users (guru pembina),
  max_members (nullable, null = unlimited),
  is_active (bool),
  created_at, updated_at

-- Keanggotaan siswa di ekstra (termasuk status pending)
extracurricular_members
  id,
  extracurricular_id → extracurriculars,
  user_id → users (siswa),
  role: enum('member', 'ketua'),
  status: enum('pending_join', 'active', 'pending_leave'),
  approved_by → users (nullable),
  approved_at (nullable),
  created_at, updated_at
  UNIQUE(extracurricular_id, user_id)

-- Sesi absensi ekstra yang dijadwalkan
extracurricular_sessions
  id,
  extracurricular_id → extracurriculars,
  title,
  session_date (date),
  start_time (time), end_time (time),
  location (nullable),
  notes (nullable),
  created_by → users (ketua/pembina),
  is_open (bool, default false),
  created_at, updated_at

-- Rekap kehadiran per sesi per siswa
extracurricular_attendances
  id,
  session_id → extracurricular_sessions,
  user_id → users (siswa anggota aktif),
  status: enum('hadir', 'alpa'),
  marked_by → users (ketua/pembina yang centang),
  marked_at,
  created_at, updated_at
  UNIQUE(session_id, user_id)
```

---

### 12.3 Alur Persetujuan Join & Leave

```
JOIN:
  Siswa → POST /extracurriculars/{id}/join
    └── extracurricular_members.status = 'pending_join'
    └── Notifikasi ke pembina & ketua
  Admin/Pembina/Ketua → approve → status = 'active'
                       → reject  → record dihapus

LEAVE:
  Siswa → POST /extracurriculars/{id}/leave
    └── extracurricular_members.status = 'pending_leave'
    └── Notifikasi ke pembina & ketua
  Admin/Pembina/Ketua → approve → record dihapus
                       → reject  → status kembali = 'active'
```

---

### 12.4 API Endpoints

```
-- Ekstra
GET  /extracurriculars              → semua ekstra aktif + status user saat ini
GET  /extracurriculars/my           → ekstra yang diikuti user (semua status)
POST /extracurriculars/{id}/join    → ajukan gabung (siswa)
POST /extracurriculars/{id}/leave   → ajukan keluar (siswa)

-- Persetujuan (admin/pembina/ketua)
GET  /extracurriculars/{id}/requests          → list pending join & leave
POST /extracurriculars/{id}/requests/{memberId}/approve
POST /extracurriculars/{id}/requests/{memberId}/reject
POST /extracurriculars/{id}/members/{memberId}/set-ketua  → tunjuk/cabut ketua

-- Sesi
GET  /extracurricular-sessions               → sesi dari ekstra yang diikuti user
POST /extracurricular-sessions               → buat sesi (ketua/pembina)
GET  /extracurricular-sessions/{id}          → detail sesi + daftar anggota + status absen
PATCH /extracurricular-sessions/{id}         → edit sesi (ketua/pembina)
DELETE /extracurricular-sessions/{id}        → hapus sesi (ketua/pembina)
POST /extracurricular-sessions/{id}/open     → buka absen
POST /extracurricular-sessions/{id}/close    → tutup absen
POST /extracurricular-sessions/{id}/mark     → centang hadir/alpa (body: [{user_id, status}])

-- Export (pembina/admin saja)
GET  /extracurricular-sessions/{id}/export/excel
GET  /extracurricular-sessions/{id}/export/pdf
```

---

### 12.5 Filament Admin Dashboard

**Resource `Ekstrakurikuler`** (`ExtracurricularResource`):
- Form: nama, deskripsi, logo, pembina (relasi User guru), kuota, aktif/nonaktif
- Tab **Anggota Aktif** — tabel nama/kelas/NIS + tombol "Jadikan Ketua" / "Cabut Ketua"
- Tab **Permintaan** — list pending_join + pending_leave + tombol Approve / Reject
- Tab **Sesi** — list sesi + link ke detail + tombol download rekap

**Export di halaman Sesi:**
- Tombol Download Excel → `ExtracurricularExport` (Maatwebsite)
- Tombol Download PDF → Blade view → DomPDF

---

### 12.6 Halaman Flutter — Tab Kesiswaan

**Entry point:** `mobile/lib/screens/kesiswaan/kesiswaan_screen.dart`  
**Ekstrakurikuler:** `mobile/lib/screens/extracurricular/extracurricular_screen.dart`

```
KesiswaanScreen  (embedded di tab Kesiswaan HomeScreen)
  ├── _FeatureCard "Ekstrakurikuler" → push ExtracurricularScreen
  └── _FeatureCard "Tata Tertib Sekolah" → push SchoolRegulationScreen

ExtracurricularScreen  (Scaffold + NestedScrollView + SliverAppBar)
  └── TabBar: ["Ekstra Saya", "Jelajahi", "Sesi"]

Tab 1 — Ekstra Saya (_MyExtrasTab)
  ├── Card per ekstra:
  │     logo, nama, pembina, badge peran (Ketua / Anggota)
  │     badge status (Aktif / Menunggu Persetujuan / Mengajukan Keluar)
  └── Tombol "Ajukan Keluar" → dialog konfirmasi → provider.leaveExtra()

Tab 2 — Jelajahi (_BrowseTab)
  ├── Search bar (filter nama ekstra lokal)
  ├── Card per ekstra:
  │     logo, nama, pembina, deskripsi singkat
  │     tombol: "Daftar" / "Menunggu" / "Sudah Bergabung"
  └── provider.joinExtra() saat tombol ditekan

Tab 3 — Sesi (_SessionsTab)
  ├── SegmentedButton: Mendatang / Sudah Lewat
  ├── Card sesi: nama ekstra, judul, tanggal, waktu, lokasi, badge status
  ├── [Jika isKetua] _CreateSessionFab "+" → push CreateSessionScreen
  └── Tap sesi → push SessionDetailScreen
        ├── Info lengkap sesi (gradient header)
        ├── Summary row: total anggota, hadir, alpa
        ├── [Jika isKetua && !isPast] _KetuaActions toggle buka/tutup
        ├── List anggota + _AttendanceToggle (optimistik: hadir/alpa)
        └── _SaveButton → provider.saveAttendance(sessionId)

CreateSessionScreen  (Scaffold form)
  ├── Dropdown ekstra (hanya yang user adalah ketua)
  ├── Title, date picker, start time, end time, location, notes
  └── Validasi end > start, submit via provider.createSession()
```

---

### 12.7 Perbandingan Web ↔ Flutter

| Elemen                                        | Web (Filament/Blade) | Flutter              | Status |
|-----------------------------------------------|----------------------|----------------------|:------:|
| Daftar semua ekstra aktif                     | ✅ Admin resource     | Tab "Jelajahi"       | ✅ Done |
| Browse + tombol daftar/status                 | ✅ Livewire component | Tab "Jelajahi"       | ✅ Done |
| Ekstra yang diikuti + badge status            | ✅ Profil siswa       | Tab "Ekstra Saya"    | ✅ Done |
| Approve / reject join & leave                 | ✅ Filament action    | Via admin/pembina web | ✅ Done (web only) |
| Buat sesi absen                               | ✅ Filament form      | FAB + CreateSessionScreen | ✅ Done |
| Centang kehadiran per anggota                 | ✅ Livewire toggle    | _AttendanceToggle optimistik | ✅ Done |
| Buka / tutup sesi absen                       | ✅ Filament action    | _KetuaActions di SessionDetailScreen | ✅ Done |
| Download rekap Excel                          | ✅ Export button      | Tombol → buka URL    | ✅ Done |
| Download rekap PDF                            | ✅ Export button      | Tombol → buka URL    | ✅ Done |
| Warna badge status (pending/aktif)            | Tailwind `yellow/green` | `AppColors.amber500 / green500` | ✅ Done |

---

### 12.8 Warna Status Badge Ekstrakurikuler

| Status | Web Tailwind | Flutter `AppColors` |
|--------|-------------|---------------------|
| `active` / Aktif | `bg-green-100 text-green-800` | `green100` / `green900` |
| `pending_join` / Menunggu | `bg-yellow-100 text-yellow-800` | `amber100` / `Color(0xFF78350F)` |
| `pending_leave` / Mengajukan Keluar | `bg-orange-100 text-orange-800` | `Color(0xFFFED7AA)` / `Color(0xFF7C2D12)` |
| Ketua | `bg-blue-100 text-blue-800` | `blue100` / `Color(0xFF1E40AF)` |
| Anggota | `bg-gray-100 text-gray-600` | `gray100` / `gray500` |

---

### 12.9 Package yang Dibutuhkan

**Backend (Laravel):**
```bash
composer require maatwebsite/excel          # Export Excel
composer require barryvdh/laravel-dompdf   # Export PDF
```

**Flutter:**
```yaml
# pubspec.yaml — tidak perlu package baru
# Download file menggunakan url_launcher untuk buka URL export
dependencies:
  url_launcher: ^6.2.0
```

---

### 12.10 File yang Dibuat (Sprint 7)

**Backend:**
```
database/migrations/
  xxxx_create_extracurriculars_table.php           ✅
  xxxx_create_extracurricular_members_table.php    ✅
  xxxx_create_extracurricular_sessions_table.php   ✅
  xxxx_create_extracurricular_attendances_table.php ✅

app/Models/
  Extracurricular.php              ✅
  ExtracurricularMember.php        ✅
  ExtracurricularSession.php       ✅
  ExtracurricularAttendance.php    ✅

app/Http/Controllers/Api/
  ExtracurricularController.php        ✅
  ExtracurricularSessionController.php ✅

app/Filament/Resources/
  ExtracurricularResource.php          ✅
  ExtracurricularResource/Pages/
    ListExtracurriculars.php           ✅
    CreateExtracurricular.php          ✅
    EditExtracurricular.php            ✅
  ExtracurricularResource/RelationManagers/
    MembersRelationManager.php         ✅
    SessionsRelationManager.php        ✅

app/Exports/
  ExtracurricularAttendanceExport.php  ✅

resources/views/exports/
  extracurricular_attendance_pdf.blade.php  ✅
```

**Flutter:**
```
mobile/lib/
  models/
    extracurricular.dart             ✅
  services/
    extracurricular_service.dart     ✅
  providers/
    extracurricular_provider.dart    ✅
  screens/
    extracurricular/
      extracurricular_screen.dart    ✅  ← TabBar utama (Scaffold + NestedScrollView)
      session_detail_screen.dart     ✅
      create_session_screen.dart     ✅
    kesiswaan/
      kesiswaan_screen.dart          ✅  ← Hub (embedded di HomeScreen tab)
```

---

## 13. Fitur Tata Tertib Sekolah

> **Aturan UI:** Tampilan identik antara Filament admin (CRUD) dan Flutter (baca saja).  
> Kategori dan urutan peraturan dikontrol penuh oleh admin tanpa deploy ulang.

---

### 13.1 Ringkasan Fitur

| Aktor | Hak Akses |
|-------|-----------|
| **Admin** | Tambah, edit, hapus, reorder, aktif/nonaktif peraturan via Filament |
| **Siswa** | Baca daftar peraturan aktif — dikelompokkan per kategori |
| **Guru** | Baca daftar peraturan aktif |

**Kategori peraturan:**

| Nilai DB     | Label Tampilan        | Warna UI          | Ikon Flutter                   |
|--------------|-----------------------|-------------------|--------------------------------|
| `kehadiran`  | Kehadiran & Presensi  | `blue-600`        | `Icons.calendar_today_rounded` |
| `berpakaian` | Tata Cara Berpakaian  | `amber-500`       | `Icons.checkroom_rounded`      |
| `perilaku`   | Tata Perilaku         | `green-500`       | `Icons.thumb_up_alt_rounded`   |
| `larangan`   | Larangan              | `red-500`         | `Icons.block_rounded`          |

---

### 13.2 Database Schema

```sql
school_regulations
  id
  category    ENUM('kehadiran', 'berpakaian', 'perilaku', 'larangan')
  title       VARCHAR(200)      -- judul singkat peraturan
  content     TEXT              -- penjelasan detail
  sort_order  SMALLINT UNSIGNED DEFAULT 0   -- urutan tampil
  is_active   BOOLEAN           DEFAULT true
  created_at, updated_at

  INDEX (category, is_active, sort_order)
```

**Seeder:** `database/seeders/SchoolRegulationSeeder.php` — 21 contoh peraturan awal  
(5 kehadiran, 5 berpakaian, 5 perilaku, 6 larangan)

---

### 13.3 API Endpoint

```
GET /api/v1/school-regulations
  → Response:
    {
      "regulations": [
        {
          "category": "kehadiran",
          "category_label": "Kehadiran & Presensi",
          "items": [
            { "id": 1, "title": "Jam Masuk Sekolah", "content": "..." },
            ...
          ]
        },
        ...
      ]
    }

  Hanya mengembalikan is_active = true, diurutkan sort_order ASC lalu id ASC.
  Tidak memerlukan autentikasi (read-only public data).
```

---

### 13.4 Filament Admin Dashboard

**Resource `SchoolRegulationResource`:**
- `navigationGroup = 'Kesiswaan'`, `navigationSort = 20`
- `navigationIcon = 'heroicon-o-scale'`
- Form fields: category (Select), sort_order (TextInput), title, content (Textarea), is_active (Toggle)
- Table columns: category badge (info/warning/success/danger), sort_order, title, content (limit 60), is_active
- `->reorderable('sort_order')` — drag-and-drop reorder
- Semua icon dideklarasi sebagai `string|\BackedEnum|null` (Filament v5 kompatibel)

---

### 13.5 Halaman Flutter — SchoolRegulationScreen

**File:** `mobile/lib/screens/kesiswaan/school_regulation_screen.dart`

```
SchoolRegulationScreen  (Scaffold + CustomScrollView)
  ├── SliverAppBar expandedHeight=120, pinned, gradient header
  │     ← "Tata Tertib Sekolah" + "SMA Negeri 1 Gianyar"
  │
  ├── [isLoading] CircularProgressIndicator
  ├── [error]     _ErrorView + tombol "Coba Lagi"
  ├── [empty]     _EmptyView
  └── [data]      SliverList of _CategorySection
        └── _CategorySection  (collapsible, expanded by default)
              ├── Header: ikon + label + jumlah peraturan + chevron
              └── [expanded] ListView of _RegulationTile
                    └── _RegulationTile  (tap to expand content)
                          ├── Nomor badge (warna kategori)
                          ├── Title
                          └── [expanded] Content teks
```

**Provider:** `mobile/lib/providers/regulation_provider.dart`  
**Service:** `mobile/lib/services/regulation_service.dart`  
**Model:** `mobile/lib/models/school_regulation.dart` (`RegulationItem`, `RegulationGroup`)

---

### 13.6 File yang Dibuat

**Backend:**
```
database/migrations/
  2026_06_15_000006_create_school_regulations_table.php  ✅

database/seeders/
  SchoolRegulationSeeder.php  ✅

app/Models/
  SchoolRegulation.php        ✅  (scopeActive, scopeOrdered, categoryLabel, categories)

app/Http/Controllers/Api/
  SchoolRegulationController.php  ✅

app/Filament/Resources/
  SchoolRegulationResource.php    ✅
  SchoolRegulationResource/Pages/
    ListSchoolRegulations.php     ✅
    CreateSchoolRegulation.php    ✅
    EditSchoolRegulation.php      ✅

routes/api.php  ✅  (GET /school-regulations ditambahkan)
```

**Flutter:**
```
mobile/lib/
  models/
    school_regulation.dart                              ✅
  services/
    regulation_service.dart                             ✅
  providers/
    regulation_provider.dart                            ✅
  screens/
    kesiswaan/
      school_regulation_screen.dart                     ✅
```

**main.dart:** `ChangeNotifierProvider(create: (_) => RegulationProvider())` ditambahkan ✅
