import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/extracurricular_provider.dart';
import '../../theme/app_colors.dart';
import '../extracurricular/extracurricular_screen.dart';
import 'school_regulation_screen.dart';

class KesiswaanScreen extends StatelessWidget {
  const KesiswaanScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          const Padding(
            padding: EdgeInsets.only(bottom: 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Kesiswaan',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: AppColors.gray800,
                  ),
                ),
                SizedBox(height: 4),
                Text(
                  'Akses layanan kesiswaan sekolah',
                  style: TextStyle(fontSize: 13, color: AppColors.gray500),
                ),
              ],
            ),
          ),

          // Ekstrakurikuler card
          _FeatureCard(
            icon:     Icons.school_rounded,
            color:    AppColors.blue600,
            bg:       AppColors.blue50,
            title:    'Ekstrakurikuler',
            subtitle: 'Daftar, kelola, dan catat absensi ekstrakurikuler',
            badge:    _ExtraBadge(),
            onTap:    () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const ExtracurricularScreen()),
            ),
          ),
          const SizedBox(height: 12),

          // Tata Tertib card
          _FeatureCard(
            icon:     Icons.balance_rounded,
            color:    const Color(0xFF7C3AED), // violet-600
            bg:       const Color(0xFFF5F3FF), // violet-50
            title:    'Tata Tertib Sekolah',
            subtitle: 'Peraturan kehadiran, berpakaian, perilaku, dan larangan',
            onTap:    () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const SchoolRegulationScreen()),
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Feature card ─────────────────────────────────────────────────────────────

class _FeatureCard extends StatelessWidget {
  final IconData icon;
  final Color    color;
  final Color    bg;
  final String   title;
  final String   subtitle;
  final Widget?  badge;
  final VoidCallback onTap;

  const _FeatureCard({
    required this.icon,
    required this.color,
    required this.bg,
    required this.title,
    required this.subtitle,
    required this.onTap,
    this.badge,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: AppColors.white,
      borderRadius: AppRadius.card,
      child: InkWell(
        onTap: onTap,
        borderRadius: AppRadius.card,
        child: Container(
          decoration: BoxDecoration(
            borderRadius: AppRadius.card,
            boxShadow: AppShadow.sm,
            border: Border.all(color: AppColors.gray100),
          ),
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              // Icon box
              Container(
                width: 52,
                height: 52,
                decoration: BoxDecoration(
                  color: bg,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(icon, size: 26, color: color),
              ),
              const SizedBox(width: 14),
              // Text
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Text(
                          title,
                          style: const TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.w700,
                            color: AppColors.gray800,
                          ),
                        ),
                        if (badge != null) ...[
                          const SizedBox(width: 8),
                          badge!,
                        ],
                      ],
                    ),
                    const SizedBox(height: 3),
                    Text(
                      subtitle,
                      style: const TextStyle(fontSize: 12, color: AppColors.gray500, height: 1.4),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              Icon(Icons.chevron_right_rounded, color: color.withOpacity(0.5), size: 22),
            ],
          ),
        ),
      ),
    );
  }
}

// Badge menampilkan jumlah ekstra yang diikuti
class _ExtraBadge extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Consumer<ExtracurricularProvider>(
      builder: (_, p, __) {
        final active = p.myExtras.where((e) => e.isActive).length;
        if (active == 0) return const SizedBox.shrink();
        return Container(
          padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
          decoration: BoxDecoration(
            color: AppColors.blue600,
            borderRadius: BorderRadius.circular(10),
          ),
          child: Text(
            '$active',
            style: const TextStyle(fontSize: 11, color: Colors.white, fontWeight: FontWeight.bold),
          ),
        );
      },
    );
  }
}
