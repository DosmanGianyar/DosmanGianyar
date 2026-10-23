# 📱 PANDUAN LENGKAP RILIS IOS (TESTFLIGHT & APP STORE) - SIMAK DOSMAN

Dokumen ini berisi rangkuman seluruh kredensial penting, arsitektur CI/CD, konfigurasi akun Apple & Codemagic, serta panduan langkah-demi-langkah untuk merilis pembaruan aplikasi **SIMAK DOSMAN** ke **Apple TestFlight** dan **App Store**.

---

## 1. 📋 Ringkasan Identitas Aplikasi & Akun

| Parameter | Nilai / Keterangan |
| :--- | :--- |
| **Nama Aplikasi di HP** | **SIMAK DOSMAN** |
| **iOS Bundle Identifier** | `com.sman1gianyar.simsMobile` |
| **Android Package Name** | `com.sman1gianyar.sims_mobile` |
| **Apple Developer Team Name** | `I WAYAN SUMARTIKA` |
| **Apple Team ID** | `3RQKPHDMC8` |
| **Email Akun Apple & Codemagic** | `tailscaledosman@gmail.com` |
| **Repository GitHub** | `DosmanGianyar/DosmanGianyar` (Branch: `main`) |
| **Folder Aplikasi Mobile** | `/mobile` (Flutter Monorepo) |

> [!IMPORTANT]
> **Bundle Identifier:** Aplikasi ini menggunakan Bundle ID `com.sman1gianyar.simsMobile`. Jangan mengubah Bundle ID ini di project Xcode/Flutter agar pembaruan aplikasi dikenali oleh App Store sebagai aplikasi SIMAK DOSMAN yang sama (pembaruan lancar untuk siswa).

---

## 2. 🔐 Kredensial & Kunci Code Signing (Codemagic CI/CD)

### A. Integrasi App Store Connect API
* **Nama Integrasi di Codemagic:** `Codemagic API Key`
* **Key ID:** `2F9T374JDR`
* **Akses:** Digunakan Codemagic untuk berkomunikasi langsung dengan Apple Developer Portal & App Store Connect API untuk mengunduh profil dan mengunggah IPA ke TestFlight.
* **File Kunci Asli:** `AuthKey_2F9T374JDR.p8` *(diunduh dari Apple Developer)*

#### 🔑 Cadangan Teks Kunci Privat API Apple (`AuthKey_2F9T374JDR.p8`):
```text
-----BEGIN PRIVATE KEY-----
MIGTAgEAMBMGByqGSM49AgEGCCqGSM49AwEHBHkwdwIBAQQgkCsnfXoCBQ97RXTN
GDIUxQ0udlBDQP9DUKDx/ISIuzagCgYIKoZIzj0DAQehRANCAASPhMxh0xYMUXdH
P2e1wzi8HMoJyaULLhr17JjnSbHqpdM0nDpwHOYYgwANb1k/hgvQDjCqSYU/qlBY
NBnHgRVk
-----END PRIVATE KEY-----
```
> [!TIP]
> **Cara Menggunakan Cadangan Ini Jika File Asli Hilang:**
> Apple hanya mengizinkan pengunduhan file `.p8` satu kali saat dibuat. Jika file `AuthKey_2F9T374JDR.p8` di komputer Anda terhapus, Anda cukup membuka Notepad di Windows, tempel teks di atas, lalu simpan dengan nama `AuthKey_2F9T374JDR.p8`. File tersebut langsung bisa diunggah ke Codemagic kapan saja tanpa perlu membuat key baru di Apple!

---


### B. Kunci Privat Sertifikat (`CERTIFICATE_PRIVATE_KEY`)
Kunci ini adalah RSA Private Key 2048-bit yang dipakai Codemagic untuk menandatangani sertifikat `Apple Distribution: I WAYAN SUMARTIKA (3RQKPHDMC8)`.

* **Lokasi di Codemagic:** Tab **Environment variables** > Group **`ios_creds`**
* **Variable Name:** `CERTIFICATE_PRIVATE_KEY` (Centang **Secret**)
* **File Cadangan Lokal:** `ios_distribution_private_key.pem` *(tersimpan di laptop dan ter-gitignored agar aman)*

#### 🔑 Cadangan Teks Kunci Privat (RSA 2048-bit):
```text
-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAr9c1hhYj3zU7x1XC35dzzYrHNdcPCzAZMIBroWBC7rWorvy/
hzJPb4SXaPaS+ZtcKVKHCMkD/GcoiA61UK5P+CUpqGWU1MgryJ9nvu1R7zWzFOWp
/X1LE5FGXOIksRlU0K5jDsGpL/Ahmhm+UXbxUpo/MmgDWq3cFBpP0h3rTUjXCy7f
HAjiRXJ8OrrSeh6NROOdb9m0cUnnQmi0lmKI5HTn24hMH3XuQZLKGpF0lfkb1tGA
uCci+OKAtuAk0LvQt1FvgJBvPK50IBhaMXNUawPjJEZTx0+29HYXhmo5s07FIxpX
rUBXdr2eJKhFPlSONfm3UdddhLJwaWlDigDZWQIDAQABAoIBAB5tEqnc8uAQigN3
53xg7HzIYqgCSmwZKoKl9fm6elprvIBq44k1P2GAg+J5p/zg0RWlXhA7GCF4iB7i
zKTlRJkfdRHaYeJfsJPKYV08G01U8WPQSZW/NFpybMZtqwOp6m8hfap0AB53YpkT
k0btWdx9LCpArTPnmYkw9FbbLnf0tN5wXsN7xKIklCXhG6zi/ECTQhtQPMK6U9le
eA1kMCQpG2NlNu6x09BTukcE4Y/XjUr2QDZo0wkn2slv83HuZOzijnw0upvC+KuZ
vM6CopwmUEjuf8cGCp24eoozpKL7c0FO0GSd+NN7W3LbzKBGAV85Ddqn0mONli7y
cHeX2XECgYEA6XDF/7KW6cdwHgIswnBMbjnkQoqxPgkEw1cGlM+8OkmRDcW5ljbz
AVX/dhHgD4n8MY7rWj+UJUTTXQm72MjCbeg2uHp7sAbwzWmXC6NqZ73ALqUmvP9n
cicWq7bxKjb+HH4HZGyl2edwht5B/2NoYDGUgEeeUyORQ/+Zuq7qFkcCgYEAwNVw
Ec1wtC6y2jzFSoHzJTF56/Lrx4UYp5Vdk7lewUcnaAZxYNkNkcJRnxn1jtbd048Q
ylmrEYZNW3PUc7h4AxuCe9CkK3flGj0WeRSYHWA3xpLWEaUCsIkJROvHGxM0QQ3G
bYf6LPrA+ECMB6MO0VeBubXYTkyKsd1KQ+lCQ18CgYEAuHPrf4dVx5NW+98sDtc9
DTuhKRL9yc7GfLMe+CbMzCGlBATgB9mTWdLh8CFfseeN5nrPbJggLvKbMECotTqs
prt9wXbGlOhnDWgJeaC+Swqb+D92xI/q5UANodED7h9V2p2aqbdPtUYym96yt2GM
77U9ab8tUvQfrGMizWe4f7ECgYB56rgnWUdHdDr6BfAKsmxi+IwIJJbzl2/GDIaU
bl+9PVppx6EJRVHx9PSb7dK2E50+M7a+6MBinRHtnycUXu3psQvncjtJjE0aMMMz
nFSsRfgdoRqdH0G7EXj9zUAeGx8vfiEVgSIoLAWzL0QZU/t7WaMYIiN3JaeO2sUo
eKK/awKBgDiFngCXPeCwQYBogpbrv4l4XIcoGnT5XnN/qWBPaXoprHFGC5I04HLF
IEKPKQMpMNI9vUdAiv25llmpJNs9GnP91K/6g+aD5xL3F85foCwdDGInkcwg24co
K7gkMoWlZl5kFxjFXLynMrlcooGyM6cUAPrWkLpBDHKCIQRjfMZl
-----END RSA PRIVATE KEY-----
```

---

## 3. ⚙️ Arsitektur CI/CD (`codemagic.yaml`)

Proyek ini menggunakan file konfigurasi [codemagic.yaml](codemagic.yaml).
* **Mengapa YAML?** Karena proyek ini berupa monorepo (Laravel di root, Flutter di `/mobile`). Codemagic memerlukan `working_directory: mobile` agar bisa mengompilasi Flutter dengan tepat.
* **Workflow Utama:** `SIMAK DOSMAN iOS Release (TestFlight / App Store)`
* **Urutan Langkah Build:**
  1. `keychain initialize` : Menyiapkan keychain sementara di mesin build macOS.
  2. `app-store-connect fetch-signing-files "$BUNDLE_ID" --type IOS_APP_STORE --create` : Mengambil/membuat sertifikat distribusi dan provisioning profile dari Apple menggunakan `CERTIFICATE_PRIVATE_KEY`.
  3. `keychain add-certificates` : Memasukkan sertifikat ke dalam keychain.
  4. `xcode-project use-profiles --archive-method=app-store` : Menghubungkan profil ke proyek Xcode.
  5. `flutter config`, `clean`, `pub get` : Menyiapkan dependensi Flutter.
  6. `flutter build ipa --release` : Mengompilasi dan menandatangani binary iOS (`.ipa`).
  7. `publishing -> app_store_connect` : Mengunggah hasil build langsung ke TestFlight secara otomatis.

---

## 4. 🚀 Panduan Rilis Versi Baru (Langkah Rutin)

Ketika ada perbaikan bug atau penambahan fitur di aplikasi mobile dan Anda ingin merilis versi baru ke TestFlight:

### Langkah 1: Naikkan Nomor Versi & Build Number
Buka file [mobile/pubspec.yaml](mobile/pubspec.yaml), cari baris `version:`:
```yaml
# Format: version: <versi_aplikasi>+<nomor_build>
# Contoh jika sebelumnya 1.0.0+1, naikkan build number (+2, +3, dst):
version: 1.0.1+2
```
> [!TIP]
> Setiap kali upload baru ke TestFlight / App Store, **nomor build di belakang tanda tambah (`+`) WAJIB bertambah** (misal: `+1` -> `+2` -> `+3`). Jika tidak dinaikkan, Apple akan menolak upload karena duplikasi build number.

### Langkah 2: Commit, Push, dan Deploy
Jalankan perintah one-liner di terminal PowerShell Windows (sesuai panduan [CLAUDE.md](CLAUDE.md)):
```powershell
git add . ; git commit -m "feat(mobile): rilis versi 1.0.1+2 ke TestFlight" ; git push origin main ; plink -batch -hostkey "SHA256:D9SSqp9hA50fNvSPW5yZZJQ6oGEjF3OEC/ScLc5HSlU" -pw Dosman123 dosman@100.73.61.126 "echo Dosman123 | sudo -S sh -c 'cd /www/wwwroot/36.93.15.146 && git pull origin main && php artisan optimize:clear'"
```

### Langkah 3: Jalankan Build di Codemagic
1. Buka dashboard [Codemagic - DosmanGianyar](https://codemagic.io/apps).
2. Klik tombol **`Start new build`**.
3. Pilih workflow: **`SIMAK DOSMAN iOS Release (TestFlight / App Store)`**.
4. Klik **`Start build`**.
5. Tunggu proses build selesai (~10-15 menit). Codemagic akan otomatis mengunggah IPA ke TestFlight.

### Langkah 4: Cek di TestFlight / App Store Connect
1. Buka [appstoreconnect.apple.com](https://appstoreconnect.apple.com) atau aplikasi **TestFlight** di iPhone.
2. Build baru akan berstatus *Processing* selama beberapa menit di Apple, lalu siap diuji coba oleh penguji (*Internal Testing*).
3. Untuk rilis publik ke App Store: Masuk ke halaman aplikasi di App Store Connect, pilih build tersebut, lalu klik **Submit for Review**.

---

## 5. 🛠️ Solusi Kendala Umum (Troubleshooting)

### A. Error: *"You already have a current Distribution certificate (HTTP 409)"*
* **Penyebab:** Apple membatasi maksimal 2-3 sertifikat Apple Distribution aktif per-akun. Jika kuota penuh, Codemagic tidak bisa mendaftarkan sertifikat baru.
* **Solusi:**
  1. Buka portal [developer.apple.com/account/resources/certificates/list](https://developer.apple.com/account/resources/certificates/list).
  2. Klik sertifikat distribusi lama yang tidak terpakai, lalu klik **Revoke**.
  3. Pastikan variabel `CERTIFICATE_PRIVATE_KEY` di Codemagic (grup `ios_creds`) tetap terpasang agar Codemagic memakai kunci yang sama secara konsisten.

### B. Error: *"No matching profiles found for bundle identifier"*
* **Penyebab:** Codemagic salah menggunakan mode pencarian profil lokal atau Bundle ID di Xcode tidak cocok dengan Apple Developer Portal.
* **Solusi:** Pastikan di file [codemagic.yaml](codemagic.yaml) menggunakan perintah CLI `app-store-connect fetch-signing-files` (bukan konfigurasi `environment.ios_signing` yang memerlukan Team berbayar).

### C. Error: *"Redundant Binary Upload"*
* **Penyebab:** Lupa menaikkan build number di `mobile/pubspec.yaml`.
* **Solusi:** Naikkan nomor setelah tanda plus (`+`), misalnya dari `1.0.0+1` menjadi `1.0.0+2`, lalu push dan build ulang.

---

## 6. 🔄 Panduan Setup Lengkap Jika Berganti Akun Codemagic (Dari Nol)

Jika suatu saat Anda mengganti akun Codemagic (misalnya membuat akun baru, berganti email, atau dipindahkan ke akun organisasi sekolah), ikuti 4 langkah mudah berikut untuk mengatur semuanya dari nol:

### Langkah 1: Buat Akun & Tambahkan Repository di Codemagic Baru
1. Buka [codemagic.io](https://codemagic.io) dan daftar / login menggunakan akun GitHub Anda (`DosmanGianyar`).
2. Di halaman utama Applications, klik **`Add application`**.
3. Pilih **GitHub**, lalu pilih repository: **`DosmanGianyar/DosmanGianyar`**.
4. Jika diminta memilih tipe project: Pilih **Flutter App** (atau *Others* jika opsi Flutter tidak muncul karena monorepo).

---

### Langkah 2: Hubungkan Integrasi Apple Developer (App Store Connect API)
Akun Codemagic baru memerlukan otorisasi untuk mengakses akun Apple Developer Anda:
1. Di Codemagic, klik menu profil di pojok kiri bawah atau buka **Settings / Personal Account** > **Team integrations** (atau **Developer Portal**).
2. Di bagian **Developer Portal**, klik **Connect** (atau **Manage keys** > **Add key**).
3. Isi formulir sesuai data berikut:
   * **App Store Connect API key name:** `Codemagic API Key` ⚠️ *(PENTING: Nama ini WAJIB persis sama dengan yang tertulis di `codemagic.yaml`)*.
   * **Key ID:** `2F9T374JDR` *(atau Key ID baru jika Anda membuat API Key baru di Apple)*.
   * **Issuer ID:** Salin nilai Issuer ID dari halaman App Store Connect (*Users and Access* > tab *Integrations*).
   * **API key file (.p8):** Unggah file `.p8` yang diunduh dari App Store Connect saat Key ID tersebut dibuat.
4. Klik **Save**.

> [!TIP]
> **Jika file `.p8` lama hilang atau tidak tersimpan:**
> Anda bisa membuat API Key baru dengan sangat mudah:
> 1. Buka [appstoreconnect.apple.com/access/integrations/api](https://appstoreconnect.apple.com/access/integrations/api).
> 2. Klik tombol tanda tambah (**`+`**) di sebelah judul *Keys*.
> 3. Beri nama (misal: `Codemagic Key`), pilih Access: **App Manager** atau **Admin**.
> 4. Klik **Generate**.
> 5. Unduh file `.p8` (file ini hanya bisa diunduh sekali), lalu catat **Key ID** dan **Issuer ID**-nya untuk dimasukkan ke formulir Codemagic di atas.

---

### Langkah 3: Tambahkan Environment Variable (`CERTIFICATE_PRIVATE_KEY`)
1. Buka aplikasi **DosmanGianyar** di akun Codemagic baru Anda.
2. Masuk ke tab **`Environment variables`** (tab ke-2 di sebelah `codemagic.yaml`).
3. Masukkan data:
   * **Variable name:** `CERTIFICATE_PRIVATE_KEY`
   * **Variable value:** Salin dan tempel seluruh teks RSA Private Key dari **Bagian 2.B** dokumen ini (dari baris `-----BEGIN RSA PRIVATE KEY-----` sampai `-----END RSA PRIVATE KEY-----`).
   * **Group:** Ketik teks **`ios_creds`** pada kotak *Search for group...*, lalu tekan **Enter** di keyboard.
   * Centang opsi: **Secret**.
4. Klik tombol **Add**.

---

### Langkah 4: Jalankan Build Pertama di Akun Baru
1. Masuk ke tab **`codemagic.yaml`**.
2. Jika masih bertuliskan *No configuration file found*, klik tombol biru di kanan atas: **`Check for configuration files`** (atau tekan **F5 / Refresh** browser).
3. Klik tombol **`Start new build`**.
4. Pilih workflow:
   👉 **`SIMAK DOSMAN iOS Release (TestFlight / App Store)`**.
5. Klik **`Start build`**.

Codemagic baru akan langsung membaca seluruh konfigurasi dari file [codemagic.yaml](codemagic.yaml), menarik integrasi `Codemagic API Key`, membaca variabel `CERTIFICATE_PRIVATE_KEY` dari grup `ios_creds`, mengompilasi kode Flutter, menandatangani file IPA, dan mengunggahnya secara otomatis langsung ke **TestFlight**!

---

## 7. 🌐 Tautan Penting

* **Apple Developer Portal:** [developer.apple.com/account](https://developer.apple.com/account)
* **App Store Connect (Manajemen App & API Keys):** [appstoreconnect.apple.com](https://appstoreconnect.apple.com)
* **Codemagic CI/CD Dashboard:** [codemagic.io/apps](https://codemagic.io/apps)
* **Dokumentasi Utama Proyek SIMS:** [CLAUDE.md](CLAUDE.md)

