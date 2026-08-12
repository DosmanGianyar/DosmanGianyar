# ==============================================================================
# Script Running Server Lokal - SIMS SMAN 1 Gianyar
# ==============================================================================

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host " SIMS SMAN 1 Gianyar - Starting Local Development Server" -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

$rootDir = $PSScriptRoot
Set-Location $rootDir

# Cari PHP
$phpCmd = "php"
if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    $possiblePhp = @(
        (Get-ChildItem -Path "C:\laragon\bin\php" -Filter "php.exe" -Recurse -ErrorAction SilentlyContinue | Select-Object -First 1).FullName,
        "C:\xampp\php\php.exe",
        "$env:USERPROFILE\.config\herd-lite\bin\php.exe"
    ) | Where-Object { $_ -and (Test-Path $_) }

    if ($possiblePhp.Count -gt 0) {
        $phpCmd = $possiblePhp[0]
    } else {
        Write-Host "[!] ERROR: PHP tidak ditemukan. Silakan tambahkan PHP ke System PATH." -ForegroundColor Red
        Exit 1
    }
}

Write-Host "[+] Menjalankan Composer Dev Server (PHP Artisan Serve + Queue + Vite)..." -ForegroundColor Green
Write-Host "    Aplikasi akan berjalan di: http://127.0.0.1:8000" -ForegroundColor Yellow
Write-Host "    Tekan Ctrl+C untuk menghentikan server.`n" -ForegroundColor Gray

# Menjalankan php artisan serve secara langsung atau via composer dev
& $phpCmd artisan serve --host=127.0.0.1 --port=8000
