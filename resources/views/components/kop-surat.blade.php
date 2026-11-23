<div class="kop-resmi-wrapper">
  <style>
    @font-face {
      font-family: 'Vimala';
      src: url('{{ asset("fonts/Vimala.ttf") }}') format('truetype');
      font-weight: normal;
      font-style: normal;
    }
    .kop-resmi-wrapper {
      width: 100%;
      margin-bottom: 12px;
      font-family: 'Arial', 'Helvetica Neue', Helvetica, sans-serif;
      color: #000;
    }
    .kop-resmi-grid {
      display: grid;
      grid-template-columns: 74px 1fr 74px;
      align-items: center;
      column-gap: 10px;
    }
    @media (min-width: 768px) {
      .kop-resmi-grid {
        grid-template-columns: 104px 1fr 104px;
        column-gap: 16px;
      }
    }
    .kop-resmi-logo {
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .kop-resmi-logo img {
      max-width: 100%;
      height: 76px;
      object-fit: contain;
    }
    @media (min-width: 768px) {
      .kop-resmi-logo img {
        height: 110px;
      }
    }
    .kop-resmi-center {
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }
    .kop-resmi-aksara {
      font-family: 'Vimala', 'Arial', sans-serif;
      font-weight: 400;
      font-size: 14.5px;
      line-height: 1.3;
      margin-bottom: 4px;
      -webkit-font-smoothing: antialiased;
    }
    @media (min-width: 768px) {
      .kop-resmi-aksara {
        font-size: 18px;
        margin-bottom: 5px;
        white-space: nowrap;
      }
    }
    .kop-resmi-aksara-sm {
      font-family: 'Vimala', 'Arial', sans-serif;
      font-weight: 400;
      font-size: 11.5px;
      line-height: 1.3;
      margin-top: 1px;
      margin-bottom: 4px;
      -webkit-font-smoothing: antialiased;
    }
    @media (min-width: 768px) {
      .kop-resmi-aksara-sm {
        font-size: 14px;
        margin-bottom: 5px;
        white-space: nowrap;
      }
    }
    .kop-resmi-pemprov {
      font-size: 13px;
      font-weight: 400;
      letter-spacing: 0.4px;
      margin-bottom: 3px;
      line-height: 1.1;
    }
    @media (min-width: 768px) {
      .kop-resmi-pemprov {
        font-size: 16.5px;
      }
    }
    .kop-resmi-sekolah {
      font-size: 17px;
      font-weight: 700;
      letter-spacing: 0.6px;
      margin-bottom: 2px;
      line-height: 1.08;
    }
    @media (min-width: 768px) {
      .kop-resmi-sekolah {
        font-size: 22.5px;
      }
    }
    .kop-resmi-alamat {
      font-size: 9.5px;
      font-weight: 400;
      margin-bottom: 0px;
      line-height: 1.24;
    }
    @media (min-width: 768px) {
      .kop-resmi-alamat {
        font-size: 12.5px;
        white-space: nowrap;
      }
    }
    .kop-resmi-kontak {
      font-size: 9px;
      font-weight: 400;
      margin-bottom: 0px;
      line-height: 1.24;
    }
    @media (min-width: 768px) {
      .kop-resmi-kontak {
        font-size: 12px;
        white-space: nowrap;
      }
    }
    .kop-resmi-npsn {
      font-size: 9.5px;
      font-weight: 400;
      letter-spacing: 0.3px;
      line-height: 1.24;
    }
    @media (min-width: 768px) {
      .kop-resmi-npsn {
        font-size: 12.5px;
      }
    }
    .kop-resmi-border {
      margin-top: 6px;
      width: 100%;
    }
    .kop-resmi-border .line-single {
      border-top: 3.5px solid #000;
      margin: 0;
    }
  </style>

  <div class="kop-resmi-grid">
    <!-- Logo Pemprov Bali (Kiri) -->
    <div class="kop-resmi-logo">
      <img src="{{ asset('img/logo-provinsi.png') }}" alt="Logo Pemprov Bali">
    </div>

    <!-- Teks Tengah -->
    <div class="kop-resmi-center">
      <div class="kop-resmi-aksara">ᬧᭂᬫᭂᬭᬶᬦ᭄ᬢᬄᬧ᭄ᬭᭀᬧᬶᬦ᭄ᬲᬶᬩᬮᬶ</div>
      <div class="kop-resmi-pemprov">PEMERINTAH PROVINSI BALI</div>

      <div class="kop-resmi-aksara">᭞ᬏᬲ᭄ ᬏᬫ᭄ ᬅ᭞ ᬦᭂᬕᭂᬭᬶ ᭞᭑᭞ ᬕ᭄ᬬᬜᬃ</div>
      <div class="kop-resmi-sekolah">SMA NEGERI 1 GIANYAR</div>

      <div class="kop-resmi-aksara-sm">ᬚᬮᬦ᭄&#x200C;ᬭᬢ᭄ᬦᬢᭂᬕᬮ᭄ᬢᬸᬕᬸᬕ᭄ᬬᬜᬃ᭞᭘᭐᭕᭑᭑᭞ᬢᬾᬮᬾᬧᭀᬦ᭄:(᭐᭓᭖᭑)᭙᭔᭓᭐᭓᭔</div>
      <div class="kop-resmi-alamat">Jalan Ratna, Tegal Tugu Gianyar (80511), Telepon : (0361) 943034</div>

      <div class="kop-resmi-kontak">Laman : www.sman1-gianyar.sch.id, Pos-el : sman1.gianyar1963@gmail.com</div>
      <div class="kop-resmi-npsn">NPSN. 50102079</div>
    </div>

    <!-- Logo DOSMAN (Kanan) -->
    <div class="kop-resmi-logo">
      <img src="{{ asset('img/logo-dosman.png') }}" alt="Logo SMAN 1 Gianyar (DOSMAN)">
    </div>
  </div>

  <div class="kop-resmi-border">
    <div class="line-single"></div>
  </div>
</div>
