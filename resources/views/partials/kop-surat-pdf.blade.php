@php
    $kopReadBase64 = static function (string $relPath): ?string {
        $fullPath = public_path($relPath);
        return file_exists($fullPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($fullPath))
            : null;
    };

    // Prioritaskan file resolusi tinggi terbaru
    $logoBaliData    = $kopReadBase64('img/logo-provinsi.png')
                    ?? $kopReadBase64('img/logo-provinsi-bali.png')
                    ?? $kopReadBase64('img/logo-pemprov-bali.png');

    $logoSekolahData = $kopReadBase64('img/logo-dosman.png')
                    ?? $kopReadBase64('img/logo-sman1-gianyar.png')
                    ?? $kopReadBase64('img/logo_sekolah.png');

    // 3 Baris Aksara Bali Resolusi Tinggi (300+ DPI Transparent, Noto Sans Balinese)
    $aksara1Data     = $kopReadBase64('img/aksara-bali-1.png');
    $aksara2Data     = $kopReadBase64('img/aksara-bali-2.png');
    $aksara3Data     = $kopReadBase64('img/aksara-bali-3.png');
@endphp
<style>
  .kop-rab-wrapper {
    width: 100%;
    max-width: 900px;
    margin: 0 auto 8px auto;
    padding-top: 0;
    text-align: center;
  }
  table.kop-rab-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0 auto;
    border: none !important;
  }
  table.kop-rab-table td {
    border: none !important;
    padding: 0;
    vertical-align: middle !important;
    background: transparent !important;
  }
  td.kop-rab-logo-left {
    width: 96px;
    text-align: right;
    padding-right: 12px;
    vertical-align: middle !important;
  }
  td.kop-rab-logo-right {
    width: 96px;
    text-align: left;
    padding-left: 12px;
    vertical-align: middle !important;
  }
  td.kop-rab-logo-left img {
    width: auto;
    max-width: 86px;
    height: 92px;
    object-fit: contain;
    display: inline-block;
  }
  td.kop-rab-logo-right img {
    width: auto;
    max-width: 86px;
    height: 92px;
    object-fit: contain;
    display: inline-block;
  }
  td.kop-rab-center {
    text-align: center;
    vertical-align: middle !important;
    color: #000000;
    font-family: 'Arial', 'Helvetica', 'DejaVu Sans', sans-serif;
    padding: 0 4px;
    line-height: 1.05;
  }
  .kop-aksara-row {
    margin: 1px 0 0 0;
    padding: 0;
    line-height: 1;
    text-align: center;
  }
  .kop-aksara-row img {
    display: inline-block;
    vertical-align: middle;
  }
  .kop-rab-pemprov {
    font-size: 13pt;
    font-weight: normal;
    letter-spacing: 0.4px;
    color: #000000;
    margin: 1px 0 2px 0;
    text-transform: uppercase;
    line-height: 1.05;
  }
  .kop-rab-sekolah {
    font-size: 17.5pt;
    font-weight: bold;
    letter-spacing: 0.6px;
    color: #000000;
    margin: 1px 0 2px 0;
    text-transform: uppercase;
    line-height: 1.05;
  }
  .kop-rab-alamat {
    font-size: 9.5pt;
    font-weight: normal;
    color: #000000;
    margin: 1px 0 0 0;
    line-height: 1.2;
  }
  .kop-rab-kontak {
    font-size: 9pt;
    font-weight: normal;
    color: #000000;
    margin: 1px 0 0 0;
    line-height: 1.2;
  }
  .kop-rab-link {
    color: #000000;
    text-decoration: none;
  }
  .kop-rab-npsn {
    font-size: 9.5pt;
    font-weight: normal;
    color: #000000;
    letter-spacing: 0.3px;
    margin-top: 1px;
    line-height: 1.2;
  }

  /* Garis Tunggal Hitam Tebal Pembatas KOP Surat */
  .kop-rab-border-line {
    margin-top: 5px;
    border-top: 3px solid #000000;
    border-bottom: none;
    height: 0;
    width: 100%;
  }
</style>
<div class="kop-rab-wrapper">
  <table class="kop-rab-table">
    <tr>
      <!-- Logo Kiri: Pemprov Bali -->
      <td class="kop-rab-logo-left">
        @if($logoBaliData)
          <img src="{{ $logoBaliData }}" alt="Lambang Provinsi Bali">
        @endif
      </td>

      <!-- Teks Tengah KOP Surat (Aksara Bali & Latin) -->
      <td class="kop-rab-center">
        @if($aksara1Data)
          <div class="kop-aksara-row"><img src="{{ $aksara1Data }}" alt="Aksara Bali Pemerintah Provinsi Bali" style="height: 24px; width: auto;"></div>
        @endif
        <div class="kop-rab-pemprov">PEMERINTAH PROVINSI BALI</div>

        @if($aksara2Data)
          <div class="kop-aksara-row"><img src="{{ $aksara2Data }}" alt="Aksara Bali SMA Negeri 1 Gianyar" style="height: 24px; width: auto;"></div>
        @endif
        <div class="kop-rab-sekolah">SMA NEGERI 1 GIANYAR</div>

        @if($aksara3Data)
          <div class="kop-aksara-row"><img src="{{ $aksara3Data }}" alt="Aksara Bali Alamat dan Telepon" style="height: 19px; width: auto;"></div>
        @endif
        <div class="kop-rab-alamat">Jalan Ratna, Tegal Tugu Gianyar (80511), Telepon : (0361) 943034</div>

        <div class="kop-rab-kontak">
          Laman : <span class="kop-rab-link">www.sman1-gianyar.sch.id</span>,
          Pos-el : <span class="kop-rab-link">sman1.gianyar1963@gmail.com</span>
        </div>
        <div class="kop-rab-npsn">NPSN. 50102079</div>
      </td>

      <!-- Logo Kanan: DOSMAN SMAN 1 Gianyar -->
      <td class="kop-rab-logo-right">
        @if($logoSekolahData)
          <img src="{{ $logoSekolahData }}" alt="Logo SMA Negeri 1 Gianyar">
        @endif
      </td>
    </tr>
  </table>
  <div class="kop-rab-border-line"></div>
</div>
