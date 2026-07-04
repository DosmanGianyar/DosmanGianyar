<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biodata Siswa — {{ $siswa->name }}</title>
    <meta name="robots" content="noindex,nofollow">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f0f4ff;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 16px;
        }
        .card {
            width: 100%;
            max-width: 420px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 40px rgba(29,78,216,.18), 0 2px 8px rgba(0,0,0,.08);
        }
        /* ── Header banner ── */
        .banner {
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #3b82f6 100%);
            padding: 20px 20px 60px;
            position: relative;
            overflow: hidden;
        }
        .banner::before {
            content: '';
            position: absolute;
            top: -40%; right: -20%;
            width: 80%; height: 180%;
            background: rgba(255,255,255,.06);
            border-radius: 50%;
        }
        .banner-school {
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
        }
        .banner-logo {
            width: 44px; height: 44px;
            border-radius: 50%;
            background: white;
            padding: 3px;
            box-shadow: 0 0 0 2px rgba(212,175,55,.7);
            flex-shrink: 0;
        }
        .banner-logo img { width: 100%; height: 100%; object-fit: contain; }
        .banner-name { color: white; }
        .banner-name h1 { font-size: 13px; font-weight: 800; letter-spacing: .03em; }
        .banner-name p { font-size: 11px; color: rgba(186,230,253,.85); margin-top: 2px; }
        .banner-badge {
            display: inline-block;
            background: rgba(253,224,71,.15);
            border: 1px solid rgba(253,224,71,.4);
            color: rgba(253,224,71,1);
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            padding: 3px 10px;
            border-radius: 20px;
            margin-top: 5px;
        }
        /* ── Avatar (overlaps banner) ── */
        .avatar-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: -52px;
            padding: 0 20px 16px;
            position: relative;
            z-index: 1;
        }
        .avatar {
            width: 88px; height: 88px;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 4px 20px rgba(29,78,216,.25);
            overflow: hidden;
            background: linear-gradient(135deg, #1d4ed8, #4f46e5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 700;
            color: white;
        }
        .avatar img { width: 100%; height: 100%; object-fit: cover; object-position: top; }
        .avatar-name {
            font-size: 17px;
            font-weight: 800;
            color: #111827;
            margin-top: 10px;
            text-align: center;
        }
        .avatar-class {
            font-size: 12px;
            color: #6b7280;
            margin-top: 3px;
        }
        /* ── Scan badge ── */
        .scan-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 20px;
            padding: 5px 12px;
            margin-top: 10px;
            font-size: 11px;
            color: #1d4ed8;
            font-weight: 500;
        }
        .scan-badge svg { width: 14px; height: 14px; flex-shrink: 0; }
        /* ── Info grid ── */
        .info-section {
            padding: 4px 16px 16px;
        }
        .info-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .info-row:last-child { border-bottom: none; }
        .info-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .info-icon svg { width: 18px; height: 18px; }
        .info-label { font-size: 10px; color: #9ca3af; font-weight: 500; text-transform: uppercase; letter-spacing: .06em; }
        .info-value { font-size: 14px; color: #111827; font-weight: 600; margin-top: 1px; }
        /* ── Footer ── */
        .footer {
            background: #f9fafb;
            border-top: 1px solid #f3f4f6;
            padding: 14px 20px;
            text-align: center;
        }
        .footer p { font-size: 10px; color: #9ca3af; }
        .footer strong { color: #374151; }
    </style>
</head>
<body>
<div class="card">

    {{-- Banner header --}}
    <div class="banner">
        <div class="banner-school">
            <div class="banner-logo">
                <img src="{{ asset('img/logo_sekolah.png') }}" alt="Logo">
            </div>
            <div class="banner-name">
                <h1>SMA NEGERI 1 GIANYAR</h1>
                <p>Jl. Ratna No.1, Gianyar · NPSN 50102079</p>
                <div class="banner-badge">KARTU PELAJAR</div>
            </div>
        </div>
    </div>

    {{-- Avatar + nama --}}
    <div class="avatar-wrap">
        <div class="avatar">
            @if($siswa->photo)
                <img src="{{ $siswa->photo_url }}" alt="{{ $siswa->name }}">
            @else
                {{ $siswa->initials }}
            @endif
        </div>
        <p class="avatar-name">{{ $siswa->name }}</p>
        <p class="avatar-class">{{ $siswa->schoolClass?->name ?? '' }}</p>
        <div class="scan-badge">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
            </svg>
            Dipindai dari Kartu Pelajar Digital
        </div>
    </div>

    {{-- Info rows --}}
    <div class="info-section">
        <div class="info-row">
            <div class="info-icon" style="background:#eff6ff;">
                <svg fill="none" stroke="#1d4ed8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c0 1.657 1.343 3 3 3s3-1.343 3-3"/>
                </svg>
            </div>
            <div>
                <p class="info-label">NIS</p>
                <p class="info-value">{{ $siswa->nis ?? '—' }}</p>
            </div>
        </div>
        <div class="info-row">
            <div class="info-icon" style="background:#f0fdf4;">
                <svg fill="none" stroke="#16a34a" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <p class="info-label">NISN</p>
                <p class="info-value">{{ $siswa->nisn ?? '—' }}</p>
            </div>
        </div>
        <div class="info-row">
            <div class="info-icon" style="background:#fdf4ff;">
                <svg fill="none" stroke="#9333ea" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <p class="info-label">Kelas</p>
                <p class="info-value">{{ $siswa->schoolClass?->name ?? '—' }}</p>
            </div>
        </div>
        @if($siswa->birth_date)
        <div class="info-row">
            <div class="info-icon" style="background:#fff7ed;">
                <svg fill="none" stroke="#ea580c" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="info-label">Tanggal Lahir</p>
                <p class="info-value">{{ $siswa->birth_date->isoFormat('dddd, D MMMM Y') }}</p>
            </div>
        </div>
        @endif
        @if($siswa->gender)
        <div class="info-row">
            <div class="info-icon" style="background:#fdf2f8;">
                <svg fill="none" stroke="#db2777" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <p class="info-label">Jenis Kelamin</p>
                <p class="info-value">{{ $siswa->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
            </div>
        </div>
        @endif
        <div class="info-row">
            <div class="info-icon" style="background:#f0f9ff;">
                <svg fill="none" stroke="#0284c7" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <p class="info-label">Sekolah</p>
                <p class="info-value">SMA Negeri 1 Gianyar</p>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>Data ini bersumber dari <strong>SIMS — SMA Negeri 1 Gianyar</strong></p>
        <p style="margin-top:4px;">Tahun Ajaran 2025/2026 · NPSN 50102079</p>
    </div>

</div>
</body>
</html>
