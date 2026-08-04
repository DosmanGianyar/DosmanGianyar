import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../models/guru_dashboard.dart';
import '../../providers/auth_provider.dart';
import '../../services/guru_service.dart';
import '../../theme/app_colors.dart';
import 'guru_conduct_screen.dart';
import 'guru_input_nilai_screen.dart';
import 'guru_permit_screen.dart';
import 'guru_sarpras_screen.dart';
import 'guru_teaching_session_screen.dart';
import 'guru_tp_screen.dart';

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
    final fullName  = user?.name ?? 'Guru';

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
              'Selamat $_greeting, $fullName 👋',
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
              _buildPembinaBanner(_dashboard!),
              _buildStatGrid(_dashboard!),
              const SizedBox(height: 16),
              _buildQuickActions(),
              const SizedBox(height: 16),
              _buildAlertList(_dashboard!),
              const SizedBox(height: 20),
              _buildWeeklyJournals(_dashboard!),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildPembinaBanner(GuruDashboard data) {
    if (data.myExtracurriculars.isEmpty) return const SizedBox.shrink();

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF312E81), Color(0xFF1E40AF)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(color: Colors.indigo.withValues(alpha: 0.2), blurRadius: 8, offset: const Offset(0, 4))
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Text('🎗️', style: TextStyle(fontSize: 18)),
              const SizedBox(width: 8),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'PEMBINA EKSTRAKURIKULER',
                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.extrabold, color: Color(0xFFC7D2FE), letterSpacing: 0.5),
                    ),
                    Text(
                      'Anda bertugas membina ${data.myExtracurriculars.length} Ekstrakurikuler',
                      style: const TextStyle(fontSize: 11, color: Colors.white70),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Wrap(
            spacing: 6, runSpacing: 6,
            children: data.myExtracurriculars.map((e) => Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: Colors.white.withValues(alpha: 0.25)),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Text('🏆 ', style: TextStyle(fontSize: 12)),
                  Text(e.name, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.white)),
                  const SizedBox(width: 4),
                  Text('(${e.membersCount} siswa)', style: const TextStyle(fontSize: 10, color: Colors.white70)),
                ],
              ),
            )).toList(),
          ),
        ],
      ),
    );
  }

  // ─── Histori Jurnal Mengajar (Per Minggu) ───────────────────────────────────
  Widget _buildWeeklyJournals(GuruDashboard data) {
    if (data.weeklyJournals.isEmpty) {
      return Container(
        width: double.infinity,
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(AppRadius.xl),
          border: Border.all(color: AppColors.gray100),
          boxShadow: AppShadow.sm,
        ),
        child: const Center(
          child: Text(
            'Belum ada histori jurnal mengajar yang dibuat.',
            style: TextStyle(fontSize: 12, color: AppColors.gray400),
          ),
        ),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Row(
          children: [
            Icon(Icons.history_edu_rounded, size: 18, color: AppColors.blue600),
            SizedBox(width: 8),
            Text(
              'Histori Jurnal Mengajar (Per Minggu)',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w700,
                color: AppColors.gray800,
              ),
            ),
          ],
        ),
        const SizedBox(height: 10),
        ListView.separated(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: data.weeklyJournals.length,
          separatorBuilder: (_, __) => const SizedBox(height: 12),
          itemBuilder: (_, i) {
            final group = data.weeklyJournals[i];
            return Container(
              decoration: BoxDecoration(
                color: AppColors.white,
                borderRadius: BorderRadius.circular(AppRadius.xl),
                border: Border.all(color: AppColors.gray100),
                boxShadow: AppShadow.sm,
              ),
              clipBehavior: Clip.antiAlias,
              child: ExpansionTile(
                initiallyExpanded: i == 0,
                tilePadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                childrenPadding: const EdgeInsets.all(12),
                backgroundColor: AppColors.white,
                collapsedBackgroundColor: AppColors.white,
                shape: const Border(),
                collapsedShape: const Border(),
                title: Row(
                  children: [
                    Container(
                      width: 8, height: 8,
                      decoration: const BoxDecoration(
                        color: AppColors.blue600,
                        shape: BoxShape.circle,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      'Minggu (${group.weekRange})',
                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.gray800),
                    ),
                  ],
                ),
                trailing: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: AppColors.blue50,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    '${group.count} jurnal',
                    style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.blue600),
                  ),
                ),
                children: group.journals.map((j) {
                  return Container(
                    margin: const EdgeInsets.only(bottom: 8),
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppColors.gray50,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: AppColors.gray200),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Text(
                              j.dateFormatted,
                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.gray800),
                            ),
                            const Spacer(),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: AppColors.blue100,
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                '${j.className} • Jam ${j.period}',
                                style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.blue800),
                              ),
                            ),
                          ],
                        ),
                        if (j.tpCode != null || j.tpDescription != null) ...[
                          const SizedBox(height: 6),
                          Container(
                            width: double.infinity,
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: AppColors.indigo700.withOpacity(0.08),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              'TP: ${j.tpCode != null ? "[${j.tpCode}] " : ""}${j.tpDescription ?? ""}',
                              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.indigo700),
                            ),
                          ),
                        ],
                        const SizedBox(height: 6),
                        Text('Materi: ${j.material}', style: const TextStyle(fontSize: 11, color: AppColors.gray800)),
                        Text('Aktivitas: ${j.activity}', style: const TextStyle(fontSize: 11, color: AppColors.gray600)),
                        if (j.notes != null && j.notes!.isNotEmpty)
                          Text('Catatan: ${j.notes}', style: const TextStyle(fontSize: 11, fontStyle: FontStyle.italic, color: AppColors.gray500)),
                        if (j.absentStudents.isNotEmpty) ...[
                          const SizedBox(height: 6),
                          Wrap(
                            spacing: 4,
                            children: j.absentStudents.map((abs) => Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: AppColors.red50,
                                borderRadius: BorderRadius.circular(4),
                                border: Border.all(color: AppColors.red100),
                              ),
                              child: Text(
                                '${abs['student_name']} (${abs['status']?.toUpperCase()})',
                                style: const TextStyle(fontSize: 10, color: AppColors.red500, fontWeight: FontWeight.w600),
                              ),
                            )).toList(),
                          ),
                        ],
                      ],
                    ),
                  );
                }).toList(),
              ),
            );
          },
        ),
      ],
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
            label:     'Catatan Negatif',
            value:     alertCount.toString(),
            subtitle:  'catat perilaku',
            icon:      Icons.warning_amber_rounded,
            iconColor: AppColors.orange600,
            iconBg:    AppColors.orange100,
            highlight: alertCount > 0,
          ),
        ),
      ],
    );
  }

  // ─── Quick Action Grid ────────────────────────────────────────────────────────

  // ─── Quick Action Grid ────────────────────────────────────────────────────────
  Widget _buildQuickActions() {
    final menuItems = [
      {
        'title': 'Sesi Mengajar',
        'subtitle': 'Absensi & Jurnal',
        'icon': Icons.timer_outlined,
        'color': AppColors.blue600,
        'bg': AppColors.blue50,
        'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruTeachingSessionScreen())),
      },
      {
        'title': 'Tujuan Pembelajaran',
        'subtitle': 'Kelola & Share TP',
        'icon': Icons.checklist_rounded,
        'color': AppColors.purple600,
        'bg': const Color(0xFFF3E8FF),
        'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruTpScreen())),
      },
      {
        'title': 'Input Nilai',
        'subtitle': 'Penilaian Siswa',
        'icon': Icons.assignment_turned_in_rounded,
        'color': AppColors.emerald600,
        'bg': const Color(0xFFECFDF5),
        'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruInputNilaiScreen())),
      },
      {
        'title': 'Kedisiplinan',
        'subtitle': 'Poin Pelanggaran',
        'icon': Icons.warning_amber_rounded,
        'color': AppColors.orange600,
        'bg': AppColors.orange50,
        'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruConductScreen())),
      },
      {
        'title': 'Persetujuan Izin',
        'subtitle': 'Izin / Dispensasi',
        'icon': Icons.mark_email_unread_rounded,
        'color': AppColors.teal600,
        'bg': const Color(0xFFCCFBF1),
        'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruPermitScreen())),
      },
      {
        'title': 'Sarana Prasarana',
        'subtitle': 'Peminjaman Asset',
        'icon': Icons.inventory_2_rounded,
        'color': AppColors.indigo600,
        'bg': AppColors.indigo50,
        'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruSarprasScreen())),
      },
    ];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Menu Utama Guru',
          style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.gray800),
        ),
        const SizedBox(height: 10),
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            childAspectRatio: 2.3,
            crossAxisSpacing: 10,
            mainAxisSpacing: 10,
          ),
          itemCount: menuItems.length,
          itemBuilder: (context, i) {
            final item = menuItems[i];
            final color = item['color'] as Color;
            final bg = item['bg'] as Color;
            return GestureDetector(
              onTap: item['onTap'] as VoidCallback,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                decoration: BoxDecoration(
                  color: AppColors.white,
                  borderRadius: BorderRadius.circular(AppRadius.xl),
                  border: Border.all(color: AppColors.gray100),
                  boxShadow: AppShadow.sm,
                ),
                child: Row(
                  children: [
                    Container(
                      width: 36,
                      height: 38,
                      decoration: BoxDecoration(
                        color: bg,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Icon(item['icon'] as IconData, color: color, size: 20),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            item['title'] as String,
                            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.gray800),
                            maxLines: 1, overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 2),
                          Text(
                            item['subtitle'] as String,
                            style: const TextStyle(fontSize: 10, color: AppColors.gray400),
                            maxLines: 1, overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
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
                  'Siswa Catatan Negatif Terbanyak',
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
                          'catatan negatif',
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
