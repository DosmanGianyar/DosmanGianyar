<div class="kop-resmi-wrapper">
  <style>
    @font-face {
      font-family: 'Bali Simbar';
      src: local('Bali Simbar'),
           local('Bali Simbar Dwijendra'),
           url('{{ asset("fonts/Bali Simbar.ttf") }}') format('truetype');
      font-weight: normal;
      font-style: normal;
    }
    .kop-resmi-wrapper {
      width: 100%;
      margin-bottom: 12px;
      font-family: 'Times New Roman', Times, Georgia, serif;
      color: #000;
    }
    .kop-resmi-grid {
      display: grid;
      grid-template-columns: 80px 1fr 80px;
      align-items: center;
      column-gap: 12px;
    }
    @media (min-width: 768px) {
      .kop-resmi-grid {
        grid-template-columns: 95px 1fr 95px;
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
      height: 70px;
      object-fit: contain;
    }
    @media (min-width: 768px) {
      .kop-resmi-logo img {
        height: 88px;
      }
    }
    .kop-resmi-center {
      text-align: center;
      line-height: 0.85;
    }
    .kop-resmi-aksara {
      font-family: 'Bali Simbar', 'Bali Simbar Dwijendra', serif;
      font-size: 20px;
      line-height: 0.72;
      margin-bottom: -4px;
      -webkit-font-smoothing: antialiased;
    }
    @media (min-width: 768px) {
      .kop-resmi-aksara {
        font-size: 26px;
      }
    }
    .kop-resmi-aksara-sm {
      font-family: 'Bali Simbar', 'Bali Simbar Dwijendra', serif;
      font-size: 15px;
      line-height: 0.72;
      margin-bottom: -3px;
      -webkit-font-smoothing: antialiased;
    }
    @media (min-width: 768px) {
      .kop-resmi-aksara-sm {
        font-size: 18px;
      }
    }
    .kop-resmi-pemprov {
      font-size: 12px;
      font-weight: bold;
      letter-spacing: 0.5px;
      margin-bottom: 1px;
      line-height: 0.90;
    }
    @media (min-width: 768px) {
      .kop-resmi-pemprov {
        font-size: 15px;
      }
    }
    .kop-resmi-disdik {
      font-size: 11px;
      font-weight: bold;
      letter-spacing: 0.3px;
      margin-bottom: 1px;
      line-height: 0.90;
    }
    @media (min-width: 768px) {
      .kop-resmi-disdik {
        font-size: 14px;
      }
    }
    .kop-resmi-sekolah {
      font-size: 15px;
      font-weight: bold;
      letter-spacing: 0.8px;
      margin-bottom: 1px;
      line-height: 0.92;
    }
    @media (min-width: 768px) {
      .kop-resmi-sekolah {
        font-size: 19px;
      }
    }
    .kop-resmi-alamat {
      font-size: 9.5px;
      font-weight: bold;
      margin-bottom: 0px;
      line-height: 0.95;
    }
    @media (min-width: 768px) {
      .kop-resmi-alamat {
        font-size: 11.5px;
      }
    }
    .kop-resmi-kontak {
      font-size: 8.5px;
      margin-bottom: 0px;
      line-height: 0.95;
    }
    @media (min-width: 768px) {
      .kop-resmi-kontak {
        font-size: 10.5px;
      }
    }
    .kop-resmi-npsn {
      font-size: 9px;
      font-weight: bold;
      letter-spacing: 0.5px;
      line-height: 0.95;
    }
    @media (min-width: 768px) {
      .kop-resmi-npsn {
        font-size: 11px;
      }
    }
    .kop-resmi-border {
      margin-top: 5px;
      width: 100%;
    }
    .kop-resmi-border .line-thick {
      border-top: 2.5px solid #000;
      margin: 0;
    }
    .kop-resmi-border .line-gap {
      height: 2px;
    }
    .kop-resmi-border .line-thin {
      border-top: 0.8px solid #000;
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
      <div class="kop-resmi-aksara">p;merin\tH p]opinsi bli</div>
      <div class="kop-resmi-pemprov">PEMERINTAH PROVINSI BALI</div>

      <div class="kop-resmi-aksara">dinS p;n\didikn\ k;p;mudan\ dn\ aoLHrg</div>
      <div class="kop-resmi-disdik">DINAS PENDIDIKAN KEPEMUDAAN DAN OLAHRAGA</div>

      <div class="kop-resmi-aksara">,es,em,a, neg]i ,1, giy/</div>
      <div class="kop-resmi-sekolah">SMA NEGERI 1 GIANYAR</div>

      <div class="kop-resmi-aksara-sm">jln\rt\\n, t;gl\tugu giy/, t;l\p : (0361) 943034</div>
      <div class="kop-resmi-alamat">Jln. Ratna, Tegal Tugu Gianyar, Telp : (0361) 943034</div>

      <div class="kop-resmi-kontak">Website : https://sman1-gianyar.sch.id, E-mail : sman1.gianyar1963@gmail.com</div>
      <div class="kop-resmi-npsn">NPSN : 50102079</div>
    </div>

    <!-- Logo DOSMAN (Kanan) -->
    <div class="kop-resmi-logo">
      <img src="{{ asset('img/logo-dosman.png') }}" alt="Logo SMAN 1 Gianyar (DOSMAN)">
    </div>
  </div>

  <div class="kop-resmi-border">
    <div class="line-thick"></div>
    <div class="line-gap"></div>
    <div class="line-thin"></div>
  </div>
</div>

