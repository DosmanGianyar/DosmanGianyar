import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../models/guru_dashboard.dart';
import '../../providers/auth_provider.dart';
import '../../services/guru_service.dart';
import '../../theme/app_colors.dart';

class GuruHomeScreen extends StatefulWidget {
  const GuruHomeScreen({super.key});

  @override
  State<GuruHomeScreen> createState() => _GuruHomeScreenState();
}

class _GuruHomeScreenState extends State<GuruHomeScreen> {
  GuruDashboard? _dashboard;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final data = await GuruService.getDashboard();
      if (mounted) setState(() { _dashboard = data; _loading = false; });
    } catch (e) {
      if (mounted) setState(() { _error = e.toString(); _loading = false; });
    }
  }

  String get _greeting {
    final h = DateTime.now().hour;
    if (h < 11) return 'Pagi';
    if (h < 15) return 'Siang';
    return 'Sore';
  }

  @override
  Widget build(BuildContext context) {
    final user      = context.watch<AuthProvider>().user;
    final firstName = user?.name.split(' ').first ?? 'Guru';

    return RefreshIndicator(
      onRefresh: _load,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(16, 20, 16, 24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ─── Greeting ───────────────────────────────────────────────
            Text(
              'Selamat $_greeting, $firstName 👋',
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w700,
                color: AppColors.gray800,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              DateFormat('EEEE, d MMMM y', 'id_ID').format(DateTime.now()),
              style: const TextStyle(fontSize: 13, color: AppColors.gray500),
            ),
            const SizedBox(height: 20),

            // ─── Stat Cards / Loading / Error ────────────────────────────
            if (_loading)
              const Center(
                child: Padding(
                  padding: EdgeInsets.all(48),
                  child: CircularProgressIndicator(),
                ),
              )
            else if (_error != null)
              _buildError()
            else if (_dashboard != null) ...[
              _buildStatGrid(_dashboard!),
              const SizedBox(height: 16),
              _buildQuickActions(),
              const SizedBox(height: 16),
              _buildAlertList(_dashboard!),
            ],
          ],
        ),
      ),
    );
  }

  // ─── Stat Cards ─────────────────────────────────────────────────────────────
  Widget _buildStatGrid(GuruDashboard data) {
    final alertCount = data.recentAlerts.length;
    return Row(
      children: [
        Expanded(
          child: _StatCard(
            label:     'Total Siswa',
            value:     data.totalStudents.toString(),
            subtitle:  'siswa di kelas wali',
            icon:      Icons.groups_rounded,
            iconColor: AppColors.blue600,
            iconBg:    AppColors.blue100,
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _StatCard(
            label:     'Siswa Pelanggaran',
            value:     alertCount.toString(),
            subtitle:  'catat pelanggaran',
            icon:      Icons.warning_amber_rounded,
            iconColor: AppColors.orange600,
            iconBg:    AppColors.orange100,
            highlight: alertCount > 0,
          ),
        ),
      ],
    );
  }

  // ─── Quick Action Bar ────────────────────────────────────────────────────────
  Widget _buildQuickActions() {
    return Row(
      children: [
        _QuickActionChip(
          label:     'Input Nilai',
          icon:      Icons.assignment_rounded,
          color:     AppColors.emerald900,
          bg:        AppColors.emerald50,
          comingSoon: true,
        ),
        const SizedBox(width: 8),
        _QuickActionChip(
          label:     'Export Nilai',
          icon:      Icons.download_rounded,
          color:     AppColors.teal700,
          bg:        AppColors.teal50,
          comingSoon: true,
        ),
      ],
    );
  }

  // ─── Alert List ──────────────────────────────────────────────────────────────
  Widget _buildAlertList(GuruDashboard data) {
    final hasAlerts = data.recentAlerts.isNotEmpty;
    return Container(
      clipBehavior: Clip.antiAlias,
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppRadius.xl2),
        border: Border.all(color: AppColors.gray100),
        boxShadow: AppShadow.sm,
      ),
      child: Column(
        children: [
          // Header
          Container(
            padding: const EdgeInsets.fromLTRB(16, 14, 16, 12),
            decoration: BoxDecoration(
              color: hasAlerts ? AppColors.orange50 : AppColors.gray50,
            ),
            child: Row(
              children: [
                Icon(
                  Icons.warning_amber_rounded,
                  size: 16,
                  color: hasAlerts ? AppColors.orange600 : AppColors.gray400,
                ),
                const SizedBox(width: 8),
                Text(
                  'Siswa Pelanggaran Terbanyak',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: hasAlerts ? const Color(0xFF9A3412) : AppColors.gray500,
                  ),
                ),
              ],
            ),
          ),
          const Divider(height: 1, color: AppColors.gray100),

          // Content
          if (!hasAlerts)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 24),
              child: Center(
                child: Text(
                  'Tidak ada alert poin kritis',
                  style: TextStyle(fontSize: 13, color: AppColors.gray400),
                ),
              ),
            )
          else
            ...data.recentAlerts.asMap().entries.map((e) {
              final isLast  = e.key == data.recentAlerts.length - 1;
              final alert   = e.value;
              return Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                decoration: BoxDecoration(
                  border: isLast
                      ? null
                      : const Border(bottom: BorderSide(color: AppColors.gray100, width: 0.5)),
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            alert.name,
                            style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                              color: AppColors.gray800,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            alert.schoolClass,
                            style: const TextStyle(fontSize: 11, color: AppColors.gray500),
                          ),
                        ],
                      ),
                    ),
                    Row(
                      children: [
                        Text(
                          '${alert.pelanggaranCount}',
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w800,
                            color: AppColors.red500,
                          ),
                        ),
                        const SizedBox(width: 4),
                        const Text(
                          'pelanggaran',
                          style: TextStyle(fontSize: 11, color: AppColors.gray400),
                        ),
                      ],
                    ),
                  ],
                ),
              );
            }),
        ],
      ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Column(
        children: [
          const SizedBox(height: 48),
          const Icon(Icons.cloud_off_rounded, size: 48, color: AppColors.gray400),
          const SizedBox(height: 12),
          const Text('Gagal memuat data', style: TextStyle(fontSize: 14, color: AppColors.gray600)),
          const SizedBox(height: 8),
          TextButton(onPressed: _load, child: const Text('Coba Lagi')),
        ],
      ),
    );
  }
}

// ─── Stat Card ───────────────────────────────────────────────────────────────

class _StatCard extends StatelessWidget {
  final String  label;
  final String  value;
  final String  subtitle;
  final IconData icon;
  final Color   iconColor;
  final Color   iconBg;
  final bool    highlight;

  const _StatCard({
    required this.label,
    required this.value,
    required this.subtitle,
    required this.icon,
    required this.iconColor,
    required this.iconBg,
    this.highlight = false,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: highlight ? AppColors.orange50 : AppColors.white,
        borderRadius: BorderRadius.circular(AppRadius.xl),
        border: Border.all(
          color: highlight
              ? AppColors.orange500.withValues(alpha: 0.4)
              : AppColors.gray100,
        ),
        boxShadow: AppShadow.sm,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Flexible(
                child: Text(
                  label,
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w500,
                    color: AppColors.gray500,
                  ),
                ),
              ),
              Container(
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  color: iconBg,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(icon, color: iconColor, size: 16),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            value,
            style: const TextStyle(
              fontSize: 26,
              fontWeight: FontWeight.w800,
              color: AppColors.gray800,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            subtitle,
            style: TextStyle(fontSize: 11, color: iconColor),
          ),
        ],
      ),
    );
  }
}

// ─── Quick Action Chip ────────────────────────────────────────────────────────

class _QuickActionChip extends StatelessWidget {
  final String   label;
  final IconData icon;
  final Color    color;
  final Color    bg;
  final bool     comingSoon;

  const _QuickActionChip({
    required this.label,
    required this.icon,
    required this.color,
    required this.bg,
    this.comingSoon = false,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: null,
      child: Opacity(
        opacity: comingSoon ? 0.55 : 1.0,
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          decoration: BoxDecoration(
            color: bg,
            borderRadius: BorderRadius.circular(AppRadius.xl),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, size: 14, color: color),
              const SizedBox(width: 6),
              Text(
                label,
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: color,
                ),
              ),
              if (comingSoon) ...[
                const SizedBox(width: 6),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    'Segera',
                    style: TextStyle(fontSize: 9, fontWeight: FontWeight.w700, color: color),
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
