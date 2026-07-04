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
    final user = context.watch<AuthProvider>().user;
    final firstName = user?.name.split(' ').first ?? 'Guru';

    return RefreshIndicator(
      onRefresh: _load,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 20),
            _buildGreeting(firstName),
            const SizedBox(height: 20),
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
              _buildPendingSection(_dashboard!),
              const SizedBox(height: 16),
              _buildAlertList(_dashboard!),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildGreeting(String firstName) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppRadius.xl2),
        border: Border.all(color: AppColors.gray100),
        boxShadow: AppShadow.sm,
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Selamat $_greeting, $firstName 👋',
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                    color: AppColors.gray800,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  DateFormat('EEEE, d MMMM y', 'id_ID').format(DateTime.now()),
                  style: const TextStyle(
                    fontSize: 12,
                    color: AppColors.gray500,
                  ),
                ),
              ],
            ),
          ),
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              gradient: AppColors.primaryGradient,
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(Icons.school_rounded, color: Colors.white, size: 22),
          ),
        ],
      ),
    );
  }

  Widget _buildStatGrid(GuruDashboard data) {
    return Row(
      children: [
        Expanded(
          child: _StatCard(
            label: 'Total Siswa',
            value: data.totalStudents.toString(),
            subtitle: 'kelas wali',
            iconColor: AppColors.blue600,
            iconBg: AppColors.blue100,
            icon: Icons.groups_rounded,
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _StatCard(
            label: 'Siswa Pelanggaran',
            value: data.recentAlerts.length.toString(),
            subtitle: 'catat pelanggaran',
            iconColor: AppColors.orange600,
            iconBg: AppColors.orange100,
            icon: Icons.warning_amber_rounded,
            highlight: data.recentAlerts.isNotEmpty,
          ),
        ),
      ],
    );
  }

  Widget _buildPendingSection(GuruDashboard data) {
    final items = [
      _PendingItem(
        label: 'Izin / Sakit / Dispen',
        count: data.pendingPermits,
        color: AppColors.blue600,
        bg:    AppColors.blue50,
        icon:  Icons.event_busy_rounded,
      ),
      _PendingItem(
        label: 'Lupa Absen',
        count: data.pendingForgotAttendances,
        color: AppColors.emerald600,
        bg:    AppColors.emerald50,
        icon:  Icons.history_rounded,
      ),
      _PendingItem(
        label: 'Pulang Lebih Awal',
        count: data.pendingEarlyCheckouts,
        color: AppColors.orange600,
        bg:    AppColors.orange50,
        icon:  Icons.exit_to_app_rounded,
      ),
    ];

    return Container(
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppRadius.xl2),
        border: Border.all(color: AppColors.gray100),
        boxShadow: AppShadow.sm,
      ),
      child: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 14, 16, 10),
            child: Row(
              children: [
                const Icon(Icons.pending_actions_rounded, size: 16, color: AppColors.gray600),
                const SizedBox(width: 6),
                const Expanded(
                  child: Text(
                    'Menunggu Persetujuan',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: AppColors.gray800,
                    ),
                  ),
                ),
                if (data.totalPending > 0)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: AppColors.red500,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      '${data.totalPending}',
                      style: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                      ),
                    ),
                  ),
              ],
            ),
          ),
          const Divider(height: 1, color: AppColors.gray100),
          ...items.map((item) => _buildPendingRow(item)),
        ],
      ),
    );
  }

  Widget _buildPendingRow(_PendingItem item) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      child: Row(
        children: [
          Container(
            width: 32,
            height: 32,
            decoration: BoxDecoration(color: item.bg, borderRadius: BorderRadius.circular(8)),
            child: Icon(item.icon, color: item.color, size: 16),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              item.label,
              style: const TextStyle(fontSize: 13, color: AppColors.gray700),
            ),
          ),
          if (item.count > 0)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
              decoration: BoxDecoration(
                color: item.color,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Text(
                '${item.count}',
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  color: Colors.white,
                ),
              ),
            )
          else
            const Text(
              '0',
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: AppColors.gray400,
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildAlertList(GuruDashboard data) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppRadius.xl2),
        border: Border.all(
          color: data.recentAlerts.isEmpty ? AppColors.gray100 : AppColors.orange500.withValues(alpha: 0.3),
        ),
        boxShadow: AppShadow.sm,
      ),
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.fromLTRB(16, 14, 16, 12),
            decoration: BoxDecoration(
              color: data.recentAlerts.isEmpty ? AppColors.gray50 : AppColors.orange50,
              borderRadius: const BorderRadius.vertical(top: Radius.circular(AppRadius.xl2)),
            ),
            child: Row(
              children: [
                Icon(
                  Icons.warning_amber_rounded,
                  size: 16,
                  color: data.recentAlerts.isEmpty ? AppColors.gray400 : AppColors.orange600,
                ),
                const SizedBox(width: 6),
                Text(
                  'Siswa Pelanggaran Terbanyak',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: data.recentAlerts.isEmpty ? AppColors.gray500 : const Color(0xFF9A3412),
                  ),
                ),
              ],
            ),
          ),
          const Divider(height: 1, color: AppColors.gray100),
          if (data.recentAlerts.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 24),
              child: Center(
                child: Text(
                  'Tidak ada siswa pelanggaran',
                  style: TextStyle(fontSize: 13, color: AppColors.gray400),
                ),
              ),
            )
          else
            ...data.recentAlerts.map((alert) => _buildAlertRow(alert)),
        ],
      ),
    );
  }

  Widget _buildAlertRow(GuruAlert alert) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: const BoxDecoration(
        border: Border(bottom: BorderSide(color: AppColors.gray100, width: 0.5)),
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
                'peln',
                style: TextStyle(fontSize: 11, color: AppColors.gray400),
              ),
            ],
          ),
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
          const Text(
            'Gagal memuat data',
            style: TextStyle(fontSize: 14, color: AppColors.gray600),
          ),
          const SizedBox(height: 8),
          TextButton(onPressed: _load, child: const Text('Coba Lagi')),
        ],
      ),
    );
  }
}

class _StatCard extends StatelessWidget {
  final String label;
  final String value;
  final String subtitle;
  final Color iconColor;
  final Color iconBg;
  final IconData icon;
  final bool highlight;

  const _StatCard({
    required this.label,
    required this.value,
    required this.subtitle,
    required this.iconColor,
    required this.iconBg,
    required this.icon,
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
          color: highlight ? AppColors.orange500.withValues(alpha: 0.4) : AppColors.gray100,
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
                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w500, color: AppColors.gray500),
                ),
              ),
              Container(
                width: 32,
                height: 32,
                decoration: BoxDecoration(color: iconBg, borderRadius: BorderRadius.circular(8)),
                child: Icon(icon, color: iconColor, size: 16),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            value,
            style: const TextStyle(fontSize: 26, fontWeight: FontWeight.w800, color: AppColors.gray800),
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

class _PendingItem {
  final String label;
  final int count;
  final Color color;
  final Color bg;
  final IconData icon;

  const _PendingItem({
    required this.label,
    required this.count,
    required this.color,
    required this.bg,
    required this.icon,
  });
}
