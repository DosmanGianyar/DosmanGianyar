import 'package:flutter/material.dart';
import '../../models/guru_models.dart';
import '../../theme/app_colors.dart';
import 'guru_conduct_screen.dart';

class GuruKesiswaanScreen extends StatelessWidget {
  final List<GuruClass> classes;
  final int? homeroomClassId;

  const GuruKesiswaanScreen({
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
            'Kesiswaan',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w700,
              color: AppColors.gray800,
            ),
          ),
          const SizedBox(height: 16),
          _buildMenuCard(
            context,
            icon: Icons.gavel_rounded,
            label: 'Pelanggaran & Prestasi',
            subtitle: 'Catatan pelanggaran dan prestasi siswa',
            color: AppColors.orange600,
            bg: AppColors.orange50,
            onTap: classes.isNotEmpty
                ? () => Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => GuruConductScreen(
                      classes: classes,
                      initialClassId: homeroomClassId,
                    )),
                  )
                : null,
          ),
          _buildMenuCard(
            context,
            icon: Icons.emoji_events_rounded,
            label: 'Prestasi Akademik',
            subtitle: 'Pencapaian siswa di bidang akademik',
            color: AppColors.yellow600,
            bg: AppColors.yellow50,
            onTap: null,
            comingSoon: true,
          ),
          _buildMenuCard(
            context,
            icon: Icons.record_voice_over_rounded,
            label: 'Bimbingan Wali Kelas',
            subtitle: 'Konsultasi dan pembimbingan siswa',
            color: AppColors.violet600,
            bg: AppColors.violet50,
            onTap: null,
            comingSoon: true,
          ),
          _buildMenuCard(
            context,
            icon: Icons.sports_soccer_rounded,
            label: 'Ekstrakurikuler',
            subtitle: 'Data kegiatan ekstra siswa',
            color: AppColors.emerald600,
            bg: AppColors.emerald50,
            onTap: null,
            comingSoon: true,
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
    required VoidCallback? onTap,
    bool comingSoon = false,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Opacity(
        opacity: comingSoon ? 0.5 : 1.0,
        child: Container(
          margin: const EdgeInsets.only(bottom: 10),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppColors.white,
            borderRadius: BorderRadius.circular(AppRadius.xl),
            border: Border.all(color: AppColors.gray100),
            boxShadow: comingSoon ? [] : AppShadow.sm,
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
                    Row(children: [
                      Text(label, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: AppColors.gray800)),
                      if (comingSoon) ...[
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(color: AppColors.gray100, borderRadius: BorderRadius.circular(6)),
                          child: const Text('Segera', style: TextStyle(fontSize: 9, fontWeight: FontWeight.w600, color: AppColors.gray500)),
                        ),
                      ],
                    ]),
                    const SizedBox(height: 2),
                    Text(subtitle, style: const TextStyle(fontSize: 12, color: AppColors.gray500)),
                  ],
                ),
              ),
              if (!comingSoon)
                const Icon(Icons.chevron_right_rounded, color: AppColors.gray300, size: 20),
            ],
          ),
        ),
      ),
    );
  }
}
