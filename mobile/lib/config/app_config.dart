class AppConfig {
  // Ganti dengan domain production saat deploy
  static const String baseUrl = 'https://sims.sman1-gianyar.sch.id/api/v1';

  static const Duration connectTimeout = Duration(seconds: 30);
  static const Duration receiveTimeout = Duration(seconds: 30);

  // Akurasi GPS di bawah nilai ini dianggap mencurigakan (mock location)
  static const double minGpsAccuracy = 5.0;

  // Nama aplikasi
  static const String appName = 'SIMS Absensi';
  static const String schoolName = 'SMA Negeri 1 Gianyar';
}
