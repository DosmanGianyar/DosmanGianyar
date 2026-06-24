<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tidak Ada Koneksi — SIMS</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f9fafb;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .card {
            background: #fff;
            border-radius: 1.25rem;
            padding: 2.5rem 2rem;
            text-align: center;
            max-width: 360px;
            width: 100%;
            box-shadow: 0 1px 3px rgba(0,0,0,.08);
        }
        .icon-wrap {
            width: 72px; height: 72px;
            background: #eff6ff;
            border-radius: 1.25rem;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.25rem;
        }
        .icon-wrap svg { width: 36px; height: 36px; color: #3b82f6; }
        h1 { font-size: 1.125rem; font-weight: 700; color: #1f2937; }
        p  { font-size: .875rem; color: #6b7280; margin-top: .5rem; line-height: 1.5; }
        .logo { width: 48px; height: 48px; object-fit: contain; margin: 1.5rem auto .75rem; display: block; }
        .school { font-size: .7rem; font-weight: 600; color: #9ca3af; text-transform: uppercase; letter-spacing: .05em; }
        button {
            margin-top: 1.75rem;
            width: 100%;
            padding: .75rem;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: .75rem;
            font-size: .875rem;
            font-weight: 600;
            cursor: pointer;
        }
        button:active { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
            </svg>
        </div>
        <h1>Tidak Ada Koneksi</h1>
        <p>Periksa koneksi internet kamu, lalu coba lagi.</p>

        <img src="/img/logo_sekolah.png" alt="SIMS" class="logo">
        <p class="school">SMA Negeri 1 Gianyar</p>

        <button onclick="window.location.reload()">Coba Lagi</button>
    </div>
</body>
</html>
