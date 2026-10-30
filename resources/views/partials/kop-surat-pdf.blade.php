@php
    $kopReadBase64 = static function (string $relPath): ?string {
        $fullPath = public_path($relPath);
        return file_exists($fullPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($fullPath))
            : null;
    };

    $logoBaliData    = $kopReadBase64('img/logo-provinsi-bali.png') ?? $kopReadBase64('img/logo-pemprov-bali.png');
    $logoSekolahData = $kopReadBase64('img/logo-sman1-gianyar.png') ?? $kopReadBase64('img/logo_sekolah.png');
    $aksara1Data     = $kopReadBase64('img/aksara-bali-1.png');
    $aksara2Data     = $kopReadBase64('img/aksara-bali-2.png');
    $aksara3Data     = $kopReadBase64('img/aksara-bali-3.png');
    $aksara4Data     = $kopReadBase64('img/aksara-bali-4.png');
@endphp
<style>
  .kop-rab-wrapper { width: 100%; margin-bottom: 12px; padding-top: 2px; }
  table.kop-rab-table { width: 100%; border-collapse: collapse; margin: 0; border: none !important; }
  table.kop-rab-table td { border: none !important; padding: 0; vertical-align: middle !important; background: transparent !important; }
  td.kop-rab-logo-left { width: 72px; text-align: center; vertical-align: middle !important; }
  td.kop-rab-logo-right { width: 72px; text-align: center; vertical-align: middle !important; }
  td.kop-rab-logo-left img { width: 62px; height: 66px; object-fit: contain; }
  td.kop-rab-logo-right img { width: 64px; height: 68px; object-fit: contain; }
  td.kop-rab-center {
    text-align: center;
    vertical-align: middle !important;
    color: #000000;
    font-family: 'Times New Roman', 'Tinos', 'DejaVu Serif', serif;
    padding: 0 6px;
    line-height: 1.08;
  }
  .kop-aksara-row { margin: 0; padding: 0; line-height: 1; text-align: center; }
  .kop-aksara-row img { display: inline-block; vertical-align: bottom; }
  .kop-rab-pemprov { font-size: 11pt; font-weight: bold; letter-spacing: 0.5px; color: #000000; margin: 0 0 1px 0; text-transform: uppercase; }
  .kop-rab-disdik { font-size: 10.2pt; font-weight: bold; letter-spacing: 0.2px; color: #000000; margin: 0 0 1px 0; text-transform: uppercase; }
  .kop-rab-sekolah { font-size: 13.5pt; font-weight: bold; letter-spacing: 0.8px; color: #000000; margin: 0 0 1px 0; text-transform: uppercase; }
  .kop-rab-alamat { font-size: 8pt; font-weight: bold; color: #000000; margin: 0 0 1px 0; }
  .kop-rab-kontak { font-size: 7.4pt; color: #000000; margin: 0; }
  .kop-rab-link { color: #0000ee; text-decoration: underline; }
  .kop-rab-npsn { font-size: 7.4pt; font-weight: bold; color: #000000; letter-spacing: 0.3px; margin-top: 1px; }
  .kop-rab-border-line {
    margin-top: 4px;
    border-top: 2.2px solid #000000;
    border-bottom: 0.8px solid #000000;
    height: 2px;
    width: 100%;
  }
</style>
<div class="kop-rab-wrapper">
  <table class="kop-rab-table">
    <tr>
      <td class="kop-rab-logo-left">
        @if($logoBaliData)
          <img src="{{ $logoBaliData }}" alt="Lambang Provinsi Bali">
        @endif
      </td>
      <td class="kop-rab-center">
        @if($aksara1Data)
          <div class="kop-aksara-row"><img src="{{ $aksara1Data }}" alt="" style="height: 14px; width: auto;"></div>
        @endif
        <div class="kop-rab-pemprov">PEMERINTAH PROVINSI BALI</div>

        @if($aksara2Data)
          <div class="kop-aksara-row"><img src="{{ $aksara2Data }}" alt="" style="height: 14px; width: auto;"></div>
        @endif
        <div class="kop-rab-disdik">DINAS PENDIDIKAN KEPEMUDAAN DAN OLAHRAGA</div>

        @if($aksara3Data)
          <div class="kop-aksara-row"><img src="{{ $aksara3Data }}" alt="" style="height: 14px; width: auto;"></div>
        @endif
        <div class="kop-rab-sekolah">SMA NEGERI 1 GIANYAR</div>

        @if($aksara4Data)
          <div class="kop-aksara-row"><img src="{{ $aksara4Data }}" alt="" style="height: 12.5px; width: auto;"></div>
        @endif
        <div class="kop-rab-alamat">Jln. Ratna, Tegal Tugu Gianyar, Telp: (0361) 943034</div>

        <div class="kop-rab-kontak">
          <strong>Website:</strong> <span class="kop-rab-link">http://www.dosmangianyar.com</span>,
          <strong>E-mail:</strong> <span class="kop-rab-link">sman1.gianyar1963@gmail.com</span>
        </div>
        <div class="kop-rab-npsn">NPSN : 50102079</div>
      </td>
      <td class="kop-rab-logo-right">
        @if($logoSekolahData)
          <img src="{{ $logoSekolahData }}" alt="Logo SMA Negeri 1 Gianyar">
        @endif
      </td>
    </tr>
  </table>
  <div class="kop-rab-border-line"></div>
</div>

