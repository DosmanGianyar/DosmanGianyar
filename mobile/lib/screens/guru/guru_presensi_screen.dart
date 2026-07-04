import 'package:flutter/material.dart';
import '../../models/guru_models.dart';
import '../../theme/app_colors.dart';
import 'guru_absensi_harian_screen.dart';
import 'guru_rekap_screen.dart';
import 'guru_permit_screen.dart';
import 'guru_forgot_attendance_screen.dart';
import 'guru_early_checkout_screen.dart';

class GuruPresensiScreen extends StatelessWidget {
  final List<GuruClass> classes;
  final int? homeroomClassId;

  const GuruPresensiScreen({
    super.key,
    required this.classes,
    this.homeroomClassId,
  });

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const SizedBox(height: 4),
          const Text(
            'Presensi Siswa',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w700,
              color: AppColors.gray800,
            ),
          ),
          const SizedBox(height: 16),
          _buildMenuCard(
            context,
            icon: Icons.today_rounded,
            label: 'Absensi Harian',
            subtitle: 'Lihat kehadiran siswa per hari',
            color: AppColors.blue600,
            bg: AppColors.blue50,
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => GuruAbsensiHarianScreen(
                classes: classes,
                initialClassId: homeroomClassId,
              )),
            ),
          ),
          _buildMenuCard(
            context,
            icon: Icons.bar_chart_rounded,
            label: 'Rekap Absensi',
            subtitle: 'Ringkasan kehadiran bulanan',
            color: AppColors.emerald600,
            bg: AppColors.emerald50,
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => GuruRekapScreen(
                classes: classes,
                initialClassId: homeroomClassId,
              )),
            ),
          ),
          _buildMenuCard(
            context,
            icon: Icons.event_busy_rounded,
            label: 'Ajuan Izin / Sakit / Dispen',
            subtitle: 'Proses pengajuan izin siswa',
            color: AppColors.sky600,
            bg: AppColors.sky50,
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const GuruPermitScreen()),
            ),
          ),
          _buildMenuCard(
            context,
            icon: Icons.history_rounded,
            label: 'Lupa Absen',
            subtitle: 'Koreksi absen siswa yang terlupa',
            color: AppColors.teal600,
            bg: AppColors.emerald50,
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const GuruForgotAttendanceScreen()),
            ),
          ),
          _buildMenuCard(
            context,
            icon: Icons.exit_to_app_rounded,
            label: 'Pulang Lebih Awal',
            subtitle: 'Izin pulang sebelum jadwal',
            color: AppColors.orange600,
            bg: AppColors.orange50,
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const GuruEarlyCheckoutScreen()),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMenuCard(
    BuildContext context, {
    required IconData icon,
    required String label,
    required String subtitle,
    required Color color,
    required Color bg,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(AppRadius.xl),
          border: Border.all(color: AppColors.gray100),
          boxShadow: AppShadow.sm,
        ),
        child: Row(
          children: [
            Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(10)),
              child: Icon(icon, color: color, size: 20),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(label, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: AppColors.gray800)),
                  const SizedBox(height: 2),
                  Text(subtitle, style: const TextStyle(fontSize: 12, color: AppColors.gray500)),
                ],
              ),
            ),
            const Icon(Icons.chevron_right_rounded, color: AppColors.gray300, size: 20),
          ],
        ),
      ),
    );
  }
}
