# ==============================================================================
# Script Inisialisasi & Setup Lingkungan Lokal - SIMS SMAN 1 Gianyar
# ==============================================================================

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host " SIMS SMAN 1 Gianyar - Local Setup Script" -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

$rootDir = $PSScriptRoot
Set-Location $rootDir

# 1. Cek / Cari PHP
$phpCmd = "php"
if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    # Cari di tempat instalasi umum (Laragon / XAMPP / Herd)
    $possiblePhp = @(
        (Get-ChildItem -Path "C:\laragon\bin\php" -Filter "php.exe" -Recurse -ErrorAction SilentlyContinue | Select-Object -First 1).FullName,
        "C:\xampp\php\php.exe",
        "$env:USERPROFILE\.config\herd-lite\bin\php.exe"
    ) | Where-Object { $_ -and (Test-Path $_) }

    if ($possiblePhp.Count -gt 0) {
        $phpCmd = $possiblePhp[0]
        Write-Host "[+] Ditemukan PHP di: $phpCmd" -ForegroundColor Green
    } else {
        Write-Host "[!] PERINGATAN: PHP tidak ditemukan di system PATH maupun folder standar (Laragon/XAMPP)." -ForegroundColor Yellow
        Write-Host "    Silakan install PHP 8.2+ (atau Laragon/XAMPP) dan tambahkan ke Environment Variables PATH." -ForegroundColor Yellow
    }
} else {
    Write-Host "[+] PHP terdeteksi di System PATH." -ForegroundColor Green
}

# 2. Cek Composer
$composerCmd = "composer"
if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
    $possibleComposer = @(
        "$rootDir\composer.phar",
        "C:\laragon\bin\composer\composer.bat",
        "C:\xampp\php\composer.bat",
        "C:\ProgramData\ComposerSetup\bin\composer.bat"
    ) | Where-Object { $_ -and (Test-Path $_) }

    if ($possibleComposer.Count -gt 0) {
        $composerCmd = $possibleComposer[0]
        Write-Host "[+] Ditemukan Composer di: $composerCmd" -ForegroundColor Green
    }
}

# 3. Cek Node & npm
$nodeCmd = "node"
$npmCmd = "npm"
if (-not (Get-Command node -ErrorAction SilentlyContinue)) {
    Write-Host "[!] PERINGATAN: Node.js / npm tidak ditemukan di system PATH." -ForegroundColor Yellow
    Write-Host "    Silakan install Node.js (v20+) dari https://nodejs.org/" -ForegroundColor Yellow
}

# 4. Pastikan file .env tersedia
if (-not (Test-Path "$rootDir\.env")) {
    Write-Host "[*] Menyalin .env.example -> .env ..." -ForegroundColor Cyan
    Copy-Item "$rootDir\.env.example" "$rootDir\.env"
} else {
    Write-Host "[+] File .env sudah ada." -ForegroundColor Green
}

# 5. Pastikan database.sqlite tersedia
$sqliteDb = "$rootDir\database\database.sqlite"
if (-not (Test-Path $sqliteDb)) {
    Write-Host "[*] Membuat database/database.sqlite ..." -ForegroundColor Cyan
    New-Item -Path $sqliteDb -ItemType File -Force | Out-Null
} else {
    Write-Host "[+] File database/database.sqlite sudah ada." -ForegroundColor Green
}

# 6. Eksekusi setup Laravel jika PHP tersedia
if (Get-Command $phpCmd -ErrorAction SilentlyContinue -or (Test-Path $phpCmd)) {
    Write-Host "`n[*] Menjalankan perintah setup Laravel..." -ForegroundColor Cyan

    # Composer Install
    if (Get-Command $composerCmd -ErrorAction SilentlyContinue -or (Test-Path $composerCmd)) {
        Write-Host "[*] Memasang dependensi PHP (composer install)..." -ForegroundColor Cyan
        & $composerCmd install --no-interaction --prefer-dist
    }

    # APP_KEY Generation
    Write-Host "[*] Generasi APP_KEY..." -ForegroundColor Cyan
    & $phpCmd artisan key:generate --force

    # Migration & Seeder
    Write-Host "[*] Menjalankan Database Migration & Seeder (SQLite)..." -ForegroundColor Cyan
    & $phpCmd artisan migrate:fresh --seed --force

    # Storage Link
    Write-Host "[*] Menghubungkan storage (storage:link)..." -ForegroundColor Cyan
    & $phpCmd artisan storage:link --force

    # Optimize Clear
    & $phpCmd artisan optimize:clear
}

# 7. Eksekusi setup Node / npm jika npm tersedia
if (Get-Command $npmCmd -ErrorAction SilentlyContinue -or (Test-Path $npmCmd)) {
    Write-Host "`n[*] Memasang dependensi Node (npm install)..." -ForegroundColor Cyan
    & $npmCmd install
}

Write-Host "`n==========================================================" -ForegroundColor Green
Write-Host " Setup Lingkungan Lokal Selesai!" -ForegroundColor Green
Write-Host " Jalankan server lokal dengan script: .\run-local.ps1 atau .\run-local.bat" -ForegroundColor Green
Write-Host "==========================================================" -ForegroundColor Green
