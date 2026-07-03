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

  /// Kurikulum: from-emerald-500 to-teal-600
  static const LinearGradient kurikulumGradient = LinearGradient(
    begin: Alignment.topLeft,
    end:   Alignment.bottomRight,
    colors: [Color(0xFF10B981), Color(0xFF0D9488)],
  );

  /// Prasarana: from-violet-500 to-purple-600
  static const LinearGradient prasaranaGradient = LinearGradient(
    begin: Alignment.topLeft,
    end:   Alignment.bottomRight,
    colors: [Color(0xFF8B5CF6), Color(0xFF9333EA)],
  );

  /// Humas: from-orange-500 to-rose-500
  static const LinearGradient humasGradient = LinearGradient(
    begin: Alignment.topLeft,
    end:   Alignment.bottomRight,
    colors: [Color(0xFFF97316), Color(0xFFF43F5E)],
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
  static const gray300   = Color(0xFFD1D5DB); // border-gray-300

  // ─── Text ────────────────────────────────────────────────────────────────
  static const gray800 = Color(0xFF1F2937); // text-gray-800 · teks utama
  static const gray700 = Color(0xFF374151); // text-gray-700 · teks sub-judul
  static const gray600 = Color(0xFF4B5563); // text-gray-600
  static const gray500 = Color(0xFF6B7280); // text-gray-500 · teks sekunder
  static const gray400 = Color(0xFF9CA3AF); // text-gray-400 · placeholder / muted

  // ─── Status warna (sesuai card di web) ──────────────────────────────────
  static const green500   = Color(0xFF22C55E); // bg-green-500  · hadir
  static const green400   = Color(0xFF4ADE80); // text-green-400
  static const green100   = Color(0xFFDCFCE7); // bg-green-100  · card hadir
  static const green50    = Color(0xFFF0FDF4); // bg-green-50
  static const green600   = Color(0xFF16A34A); // text-green-600
  static const green900   = Color(0xFF14532D); // text-green-900

  static const emerald500 = Color(0xFF10B981); // bg-emerald-500 · pulang
  static const emerald100 = Color(0xFFD1FAE5); // bg-emerald-100
  static const emerald900 = Color(0xFF064E3B);
  static const emerald600 = Color(0xFF059669);

  static const yellow500  = Color(0xFFEAB308); // bg-yellow-500 · terlambat
  static const amber100   = Color(0xFFFEF3C7); // bg-amber-100  · belum buka
  static const amber500   = Color(0xFFF59E0B); // bg-amber-500

  static const red500     = Color(0xFFEF4444); // bg-red-500    · alpa
  static const red300     = Color(0xFFFCA5A5); // text-red-300
  static const red100     = Color(0xFFFEE2E2); // bg-red-100
  static const red50      = Color(0xFFFEF2F2); // bg-red-50

  static const blue50     = Color(0xFFEFF6FF); // bg-blue-50
  static const blue100    = Color(0xFFDBEAFE); // bg-blue-100
  static const blue200    = Color(0xFFBFDBFE); // text-blue-200 · subtitle hero
  static const blue400    = Color(0xFF60A5FA); // text-blue-400
  static const blue500    = Color(0xFF3B82F6); // text-blue-500
  static const blue800    = Color(0xFF1E40AF); // text-blue-800 · banner notif

  static const purple500  = Color(0xFFA855F7); // sakit
  static const teal500    = Color(0xFF14B8A6);  // dispensasi

  // ─── Violet (Prasarana) ──────────────────────────────────────────────────
  static const violet50   = Color(0xFFF5F3FF);
  static const violet100  = Color(0xFFEDE9FE);
  static const violet500  = Color(0xFF8B5CF6);
  static const violet600  = Color(0xFF7C3AED);

  // ─── Orange / Rose (Humas) ───────────────────────────────────────────────
  static const orange50   = Color(0xFFFFF7ED);
  static const orange100  = Color(0xFFFFEDD5);
  static const orange500  = Color(0xFFF97316);
  static const orange600  = Color(0xFFEA580C);
  static const rose500    = Color(0xFFF43F5E);
  static const rose100    = Color(0xFFFFE4E6);
  static const pink100    = Color(0xFFFFE4E6);
  static const pink500    = Color(0xFFEC4899);

  // ─── Sky (Izin) ──────────────────────────────────────────────────────────
  static const sky50      = Color(0xFFF0F9FF);
  static const sky100     = Color(0xFFE0F2FE);
  static const sky500     = Color(0xFF0EA5E9);
  static const sky600     = Color(0xFF0284C7);
  static const sky700     = Color(0xFF0369A1);

  // ─── Emerald extras ──────────────────────────────────────────────────────
  static const emerald50  = Color(0xFFECFDF5);
  static const teal600    = Color(0xFF0D9488);

  // ─── Yellow extras ───────────────────────────────────────────────────────
  static const yellow50   = Color(0xFFFFFBEB);
  static const yellow100  = Color(0xFFFEF3C7);
  static const yellow600  = Color(0xFFCA8A04);
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
