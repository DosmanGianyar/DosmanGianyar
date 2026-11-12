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

    // 4 Baris Aksara Bali Resolusi Tinggi (300 DPI Transparent)
    $aksara1Data     = $kopReadBase64('img/aksara-bali-1.png');
    $aksara2Data     = $kopReadBase64('img/aksara-bali-2.png');
    $aksara3Data     = $kopReadBase64('img/aksara-bali-3.png');
    $aksara4Data     = $kopReadBase64('img/aksara-bali-4.png');
@endphp
<style>
  .kop-rab-wrapper { 
    width: 100%; 
    margin-bottom: 8px; 
    padding-top: 0; 
  }
  table.kop-rab-table { 
    width: 100%; 
    border-collapse: collapse; 
    margin: 0; 
    border: none !important; 
  }
  table.kop-rab-table td { 
    border: none !important; 
    padding: 0; 
    vertical-align: middle !important; 
    background: transparent !important; 
  }
  td.kop-rab-logo-left { 
    width: 76px; 
    text-align: center; 
    vertical-align: middle !important; 
  }
  td.kop-rab-logo-right { 
    width: 76px; 
    text-align: center; 
    vertical-align: middle !important; 
  }
  td.kop-rab-logo-left img { 
    width: auto; 
    max-width: 68px; 
    height: 76px; 
    object-fit: contain; 
    display: inline-block;
  }
  td.kop-rab-logo-right img { 
    width: auto; 
    max-width: 68px; 
    height: 76px; 
    object-fit: contain; 
    display: inline-block;
  }
  td.kop-rab-center {
    text-align: center;
    vertical-align: middle !important;
    color: #000000;
    font-family: 'Times New Roman', 'Tinos', 'DejaVu Serif', serif;
    padding: 0 4px;
    line-height: 1.05;
  }
  .kop-aksara-row { 
    margin: 0; 
    padding: 0; 
    line-height: 0.95; 
    text-align: center; 
  }
  .kop-aksara-row img { 
    display: inline-block; 
    vertical-align: bottom; 
  }
  .kop-rab-pemprov { 
    font-size: 11pt; 
    font-weight: bold; 
    letter-spacing: 0.5px; 
    color: #000000; 
    margin: 1px 0 2px 0; 
    text-transform: uppercase; 
    line-height: 1.05;
  }
  .kop-rab-disdik { 
    font-size: 10.2pt; 
    font-weight: bold; 
    letter-spacing: 0.3px; 
    color: #000000; 
    margin: 1px 0 2px 0; 
    text-transform: uppercase; 
    line-height: 1.05;
  }
  .kop-rab-sekolah { 
    font-size: 13.8pt; 
    font-weight: bold; 
    letter-spacing: 0.8px; 
    color: #000000; 
    margin: 1px 0 2px 0; 
    text-transform: uppercase; 
    line-height: 1.1;
  }
  .kop-rab-alamat { 
    font-size: 8.5pt; 
    font-weight: bold; 
    color: #000000; 
    margin: 1px 0 1px 0; 
    line-height: 1.1;
  }
  .kop-rab-kontak { 
    font-size: 7.8pt; 
    color: #000000; 
    margin: 0; 
    line-height: 1.1;
  }
  .kop-rab-link { 
    color: #000000; 
    text-decoration: none; 
  }
  .kop-rab-npsn { 
    font-size: 7.8pt; 
    font-weight: bold; 
    color: #000000; 
    letter-spacing: 0.5px; 
    margin-top: 1px; 
    line-height: 1.1;
  }
  
  /* Garis Ganda Resmi KOP Surat */
  .kop-rab-border-line {
    margin-top: 4px;
    border-top: 2.5px solid #000000;
    border-bottom: 0.8px solid #000000;
    height: 2px;
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
          <div class="kop-aksara-row"><img src="{{ $aksara1Data }}" alt="Aksara Bali Pemerintah Provinsi Bali" style="height: 15px; width: auto;"></div>
        @endif
        <div class="kop-rab-pemprov">PEMERINTAH PROVINSI BALI</div>

        @if($aksara2Data)
          <div class="kop-aksara-row"><img src="{{ $aksara2Data }}" alt="Aksara Bali Dinas Pendidikan" style="height: 14.5px; width: auto;"></div>
        @endif
        <div class="kop-rab-disdik">DINAS PENDIDIKAN KEPEMUDAAN DAN OLAHRAGA</div>

        @if($aksara3Data)
          <div class="kop-aksara-row"><img src="{{ $aksara3Data }}" alt="Aksara Bali SMA Negeri 1 Gianyar" style="height: 15px; width: auto;"></div>
        @endif
        <div class="kop-rab-sekolah">SMA NEGERI 1 GIANYAR</div>

        @if($aksara4Data)
          <div class="kop-aksara-row"><img src="{{ $aksara4Data }}" alt="Aksara Bali Alamat dan Telepon" style="height: 13px; width: auto;"></div>
        @endif
        <div class="kop-rab-alamat">Jln. Ratna, Tegal Tugu Gianyar, Telp : (0361) 943034</div>

        <div class="kop-rab-kontak">
          Website : <span class="kop-rab-link">https://sman1-gianyar.sch.id</span>, 
          E-mail : <span class="kop-rab-link">sman1.gianyar1963@gmail.com</span>
        </div>
        <div class="kop-rab-npsn">NPSN : 50102079</div>
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
