import 'package:flutter/material.dart';

/// Semua warna diambil langsung dari kelas Tailwind CSS yang dipakai web SIMS.
/// Format komentar: nama-kelas-tailwind = nilai hex
abstract final class AppColors {
  // ─── Primary (blue-600 → indigo-700 gradient web) ──────────────────────
  static const blue600   = Color(0xFF2563EB); // bg-blue-600  · primary utama
  static const blue700   = Color(0xFF1D4ED8); // bg-blue-700
  static const indigo700 = Color(0xFF4338CA); // bg-indigo-700 · ujung gradient dashboard
  static const indigo800 = Color(0xFF3730A3); // bg-indigo-800 · ujung gradient login panel

  /// Dashboard + history header: from-blue-600 to-indigo-700
  static const LinearGradient primaryGradient = LinearGradient(
    begin: Alignment.topLeft,
    end:   Alignment.bottomRight,
    colors: [blue600, blue700, indigo700],
  );

  /// Topbar header: sesuai web — linear-gradient(135deg, #0f2460, #1a3a8f, #1e3fad)
  static const LinearGradient topbarGradient = LinearGradient(
    begin: Alignment.topLeft,
    end:   Alignment.bottomRight,
    colors: [Color(0xFF0F2460), Color(0xFF1A3A8F), Color(0xFF1E3FAD)],
  );

  /// Login panel kiri: from-blue-600 via-blue-700 to-indigo-800
  static const LinearGradient loginGradient = LinearGradient(
    begin: Alignment.topLeft,
    end:   Alignment.bottomRight,
    colors: [blue600, blue700, indigo800],
  );

  // ─── Backgrounds ─────────────────────────────────────────────────────────
  static const gray50  = Color(0xFFF9FAFB); // bg-gray-50  · halaman konten
  static const slate100 = Color(0xFFF1F5F9); // bg-slate-100 · login background

  // ─── Cards ───────────────────────────────────────────────────────────────
  static const white     = Color(0xFFFFFFFF);
  static const gray100   = Color(0xFFF3F4F6); // border-gray-100 · border card
  static const gray200   = Color(0xFFE5E7EB); // border-gray-200 · border nav

  // ─── Text ────────────────────────────────────────────────────────────────
  static const gray800 = Color(0xFF1F2937); // text-gray-800 · teks utama
  static const gray700 = Color(0xFF374151); // text-gray-700 · teks sub-judul
  static const gray500 = Color(0xFF6B7280); // text-gray-500 · teks sekunder
  static const gray400 = Color(0xFF9CA3AF); // text-gray-400 · placeholder / muted

  // ─── Status warna (sesuai card di web) ──────────────────────────────────
  static const green500   = Color(0xFF22C55E); // bg-green-500  · hadir
  static const green100   = Color(0xFFDCFCE7); // bg-green-100  · card hadir
  static const green900   = Color(0xFF14532D); // text-green-900

  static const emerald500 = Color(0xFF10B981); // bg-emerald-500 · pulang
  static const emerald100 = Color(0xFFD1FAE5); // bg-emerald-100
  static const emerald900 = Color(0xFF064E3B);
  static const emerald600 = Color(0xFF059669);

  static const yellow500  = Color(0xFFEAB308); // bg-yellow-500 · terlambat
  static const amber100   = Color(0xFFFEF3C7); // bg-amber-100  · belum buka
  static const amber500   = Color(0xFFF59E0B); // bg-amber-500

  static const red500     = Color(0xFFEF4444); // bg-red-500    · alpa
  static const red100     = Color(0xFFFEE2E2); // bg-red-100

  static const blue50     = Color(0xFFEFF6FF); // bg-blue-50
  static const blue100    = Color(0xFFDBEAFE); // bg-blue-100
  static const blue200    = Color(0xFFBFDBFE); // text-blue-200 · subtitle hero

  static const purple500  = Color(0xFFA855F7); // sakit
  static const teal500    = Color(0xFF14B8A6);  // dispensasi
}

/// Border radius yang dipakai web — rounded-xl = 12, rounded-2xl = 16
abstract final class AppRadius {
  static const double xl  = 12;
  static const double xl2 = 16;
  static const double xl3 = 24;
  static const BorderRadius card     = BorderRadius.all(Radius.circular(xl2));
  static const BorderRadius button   = BorderRadius.all(Radius.circular(xl));
  static const BorderRadius input    = BorderRadius.all(Radius.circular(xl));
  static const BorderRadius avatar   = BorderRadius.all(Radius.circular(xl));
}

/// Shadow sesuai shadow-sm di web
abstract final class AppShadow {
  static List<BoxShadow> get sm => [
    BoxShadow(
      color:  Colors.black.withOpacity(0.06),
      blurRadius: 6,
      offset: const Offset(0, 1),
    ),
  ];
}
