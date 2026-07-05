import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/attendance.dart';
import '../../providers/attendance_provider.dart';
import '../../providers/extracurricular_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';
import '../attendance/history_screen.dart';
import '../extracurricular/extracurricular_screen.dart';
import 'achievement_screen.dart';
import 'conduct_screen.dart';
import 'early_checkout_screen.dart';
import 'forgot_attendance_screen.dart';
import 'bk_consultation_screen.dart';
import 'homeroom_consultation_screen.dart';
import 'permit_screen.dart';
import 'school_regulation_screen.dart';

class KesiswaanScreen extends StatefulWidget {
  const KesiswaanScreen({super.key});

  @override
  State<KesiswaanScreen> createState() => _KesiswaanScreenState();
}

class _KesiswaanScreenState extends State<KesiswaanScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabCtrl;

  // ── Kesiswaan summary state ────────────────────────────────────────────
  int  _permitPending            = 0;
  int  _earlyCheckoutPending     = 0;
  int  _forgotAttendancePending  = 0;
  int  _unvotedCount             = 0;
  int  _pendingVerify            = 0;
  Map<String, int> _achievementStats = {'pending': 0, 'approved': 0, 'rejected': 0};
  Map<String, dynamic>? _activePermit;
  List<Map<String, dynamic>> _recentViolations   = [];
  List<Map<String, dynamic>> _recentAchievements = [];
  int  _pelanggaranCount = 0;
  bool _summaryLoaded    = false;

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 3, vsync: this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final prov = context.read<AttendanceProvider>();
      if (prov.currentMonthRecords.isEmpty) prov.fetchCurrentMonthDots();
      if (prov.history == null) {
        final now = DateTime.now();
        prov.fetchHistory(month: now.month, year: now.year);
      }
      _loadSummary();
    });
  }

  Future<void> _loadSummary() async {
    try {
      final data = await ApiClient.get('/kesiswaan/summary');
      if (!mounted) return;
      final conduct = data['conduct'] as Map<String, dynamic>? ?? {};
      final stats   = data['achievement_stats'] as Map<String, dynamic>? ?? {};
      setState(() {
        _permitPending            = (data['permit_pending']            ?? 0) as int;
        _earlyCheckoutPending     = (data['early_checkout_pending']    ?? 0) as int;
        _forgotAttendancePending  = (data['forgot_attendance_pending'] ?? 0) as int;
        _unvotedCount             = (data['unvoted_count']             ?? 0) as int;
        _pendingVerify            = (data['pending_verify']            ?? 0) as int;
        _achievementStats = {
          'pending':  (stats['pending']  ?? 0) as int,
          'approved': (stats['approved'] ?? 0) as int,
          'rejected': (stats['rejected'] ?? 0) as int,
        };
        _activePermit       = data['active_permit'] as Map<String, dynamic>?;
        _pelanggaranCount   = (conduct['pelanggaran_count'] ?? 0) as int;
        _recentViolations   = List<Map<String, dynamic>>.from(conduct['recent_violations']    ?? []);
        _recentAchievements = List<Map<String, dynamic>>.from(data['recent_achievements'] ?? []);
        _summaryLoaded      = true;
      });
    } catch (_) {
      // silent – summary is informational, not blocking
    }
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    super.dispose();
  }

  void _showComingSoon(String title) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text('$title — fitur segera hadir.'),
      backgroundColor: AppColors.gray700,
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
    ));
  }

  String get _izinSubtitle {
    if (!_summaryLoaded) return 'Memuat status...';
    if (_activePermit != null) {
      final tipe = _activePermit!['type'] ?? 'Izin';
      return 'Izin $tipe sedang aktif';
    }
    if (_permitPending > 0) return '$_permitPending pengajuan menunggu persetujuan';
    return 'Tidak ada pengajuan aktif';
  }

  String get _votingSubtitle {
    if (!_summaryLoaded) return 'Memuat status...';
    if (_unvotedCount > 0) return '$_unvotedCount sesi voting menunggu suaramu';
    return 'Tidak ada sesi voting aktif';
  }

  @override
  Widget build(BuildContext context) {
    final attProv = context.watch<AttendanceProvider>();
    final records = attProv.currentMonthRecords;

    final summary = <String, int>{
      'hadir': 0, 'terlambat': 0, 'alpa': 0,
      'izin': 0, 'sakit': 0, 'dispensasi': 0,
    };
    for (final r in records) {
      if (summary.containsKey(r.status)) summary[r.status] = summary[r.status]! + 1;
    }

    return RefreshIndicator(
      onRefresh: () async => _loadSummary(),
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // ─── Rekap Absensi Bulan Ini ───────────────────────────────
            Container(
              decoration: BoxDecoration(
                gradient: AppColors.primaryGradient,
                borderRadius: AppRadius.card,
              ),
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(children: [
                    const Expanded(
                      child: Text('Absensi Bulan Ini',
                        style: TextStyle(
                          color: Colors.white, fontWeight: FontWeight.w600, fontSize: 13)),
                    ),
                    GestureDetector(
                      onTap: () => Navigator.push(context,
                        MaterialPageRoute(builder: (_) => const HistoryScreen())),
                      child: Row(children: const [
                        Text('Riwayat',
                          style: TextStyle(color: AppColors.blue200, fontSize: 11)),
                        SizedBox(width: 2),
                        Icon(Icons.chevron_right, color: AppColors.blue200, size: 14),
                      ]),
                    ),
                  ]),
                  const SizedBox(height: 12),
                  GridView.count(
                    shrinkWrap:   true,
                    physics:      const NeverScrollableScrollPhysics(),
                    crossAxisCount: 3,
                    mainAxisSpacing:  8,
                    crossAxisSpacing: 8,
                    childAspectRatio: 1.5,
                    children: [
                      _RekapCell(label: 'Hadir',      count: summary['hadir']!,      color: const Color(0xFF86EFAC)),
                      _RekapCell(label: 'Terlambat',  count: summary['terlambat']!,  color: const Color(0xFFFDE047)),
                      _RekapCell(label: 'Alpa',       count: summary['alpa']!,       color: const Color(0xFFFCA5A5)),
                      _RekapCell(label: 'Izin',       count: summary['izin']!,       color: const Color(0xFF7DD3FC)),
                      _RekapCell(label: 'Sakit',      count: summary['sakit']!,      color: const Color(0xFFD8B4FE)),
                      _RekapCell(label: 'Dispensasi', count: summary['dispensasi']!, color: const Color(0xFFFDBA74)),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),

            // ─── Tab: Presensi | Pelanggaran | Prestasi ────────────────
            Container(
              decoration: BoxDecoration(
                color: AppColors.white,
                borderRadius: AppRadius.card,
                border: Border.all(color: AppColors.gray100),
                boxShadow: AppShadow.sm,
              ),
              clipBehavior: Clip.antiAlias,
              child: Column(
                children: [
                  TabBar(
                    controller: _tabCtrl,
                    labelColor: AppColors.blue600,
                    unselectedLabelColor: AppColors.gray400,
                    indicatorColor: AppColors.blue600,
                    indicatorWeight: 2,
                    labelStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
                    tabs: [
                      const Tab(text: 'Presensi'),
                      Tab(child: _TabWithBadge(
                        label: 'Catatan Negatif',
                        count: _pelanggaranCount,
                        badgeColor: AppColors.red500,
                      )),
                      Tab(child: _TabWithBadge(
                        label: 'Prestasi',
                        count: _achievementStats['pending'] ?? 0,
                        badgeColor: AppColors.yellow600,
                      )),
                    ],
                  ),
                  SizedBox(
                    height: 300,
                    child: TabBarView(
                      controller: _tabCtrl,
                      children: [
                        _PresensiTab(records: records),
                        _PelanggaranTab(
                          violations: _recentViolations,
                          totalCount: _pelanggaranCount,
                          loaded: _summaryLoaded,
                          onViewAll: () => Navigator.push(context,
                            MaterialPageRoute(builder: (_) => const ConductScreen())),
                        ),
                        _PrestasiTab(
                          achievements: _recentAchievements,
                          pendingCount: _achievementStats['pending'] ?? 0,
                          pendingVerify: _pendingVerify,
                          loaded: _summaryLoaded,
                          onViewAll: () => Navigator.push(context,
                            MaterialPageRoute(builder: (_) => const AchievementScreen())),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),

            // ─── Izin, Sakit & Dispensasi ──────────────────────────────
            Container(
              decoration: BoxDecoration(
                color: AppColors.white,
                borderRadius: AppRadius.card,
                border: Border.all(color: AppColors.gray100),
                boxShadow: AppShadow.sm,
              ),
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(children: [
                    Container(
                      width: 36, height: 36,
                      decoration: BoxDecoration(color: AppColors.sky100, borderRadius: BorderRadius.circular(10)),
                      child: const Icon(Icons.description_outlined, color: AppColors.sky600, size: 18),
                    ),
                    const SizedBox(width: 12),
                    Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      const Text('Izin, Sakit & Dispensasi',
                        style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700)),
                      Text(_izinSubtitle,
                        style: TextStyle(
                          fontSize: 11,
                          color: _activePermit != null
                              ? AppColors.sky600
                              : _permitPending > 0
                                  ? AppColors.orange600
                                  : AppColors.gray400,
                        )),
                    ])),
                    if (_permitPending > 0)
                      _Badge(count: _permitPending, color: AppColors.orange500),
                  ]),
                  const SizedBox(height: 12),
                  Row(children: [
                    Expanded(child: _IzinButton(label: 'Izin', color: AppColors.sky50, textColor: AppColors.sky700,
                      onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PermitScreen())))),
                    const SizedBox(width: 8),
                    Expanded(child: _IzinButton(label: 'Dispensasi', color: AppColors.orange50, textColor: AppColors.orange600,
                      onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PermitScreen())))),
                    const SizedBox(width: 8),
                    Expanded(child: _IzinButton(label: 'Riwayat', color: AppColors.gray50, textColor: AppColors.gray600,
                      onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PermitScreen())))),
                  ]),
                ],
              ),
            ),
            const SizedBox(height: 8),

            // ─── Izin Pulang Lebih Awal ────────────────────────────────
            _LinkRow(
              icon: Icons.logout_rounded,
              iconBg: AppColors.emerald100,
              iconColor: AppColors.emerald600,
              title: 'Izin Pulang Lebih Awal',
              subtitle: _earlyCheckoutPending > 0
                  ? '$_earlyCheckoutPending pengajuan menunggu'
                  : 'Ajukan izin pulang sebelum jam normal',
              subtitleColor: _earlyCheckoutPending > 0 ? AppColors.orange600 : null,
              trailing: _earlyCheckoutPending > 0
                  ? _Badge(count: _earlyCheckoutPending, color: AppColors.orange500)
                  : null,
              onTap: () => Navigator.push(context,
                MaterialPageRoute(builder: (_) => const EarlyCheckoutScreen())),
            ),
            const SizedBox(height: 8),

            // ─── Lupa Absen ────────────────────────────────────────────
            _LinkRow(
              icon: Icons.schedule_rounded,
              iconBg: AppColors.amber100,
              iconColor: AppColors.amber500,
              title: 'Lupa Absen',
              subtitle: _forgotAttendancePending > 0
                  ? '$_forgotAttendancePending pengajuan menunggu'
                  : 'Ajukan koreksi presensi ke wali kelas',
              subtitleColor: _forgotAttendancePending > 0 ? AppColors.orange600 : null,
              trailing: _forgotAttendancePending > 0
                  ? _Badge(count: _forgotAttendancePending, color: AppColors.orange500)
                  : null,
              onTap: () => Navigator.push(context,
                MaterialPageRoute(builder: (_) => const ForgotAttendanceScreen())),
            ),
            const SizedBox(height: 8),

            // ─── Bimbingan Wali Kelas ──────────────────────────────────
            _LinkRow(
              icon: Icons.support_agent_rounded,
              iconBg: AppColors.blue50,
              iconColor: AppColors.blue600,
              title: 'Bimbingan Guru Wali',
              subtitle: 'Ajukan konsultasi dengan Guru Wali',
              onTap: () => Navigator.push(context,
                MaterialPageRoute(builder: (_) => const HomeroomConsultationScreen())),
            ),
            const SizedBox(height: 8),

            // ─── Bimbingan BK ──────────────────────────────────────────
            _LinkRow(
              icon: Icons.chat_bubble_outline_rounded,
              iconBg: AppColors.violet100,
              iconColor: AppColors.violet600,
              title: 'Bimbingan BK',
              subtitle: 'Ajukan bimbingan ke Guru BK',
              onTap: () => Navigator.push(context,
                MaterialPageRoute(builder: (_) => const BkConsultationScreen())),
            ),
            const SizedBox(height: 8),

            // ─── Izin Keluar Kelas ─────────────────────────────────────
            _LinkRow(
              icon: Icons.door_front_door_outlined,
              iconBg: AppColors.emerald100,
              iconColor: AppColors.emerald600,
              title: 'Izin Keluar Kelas',
              subtitle: 'Buat izin keluar kelas',
              onTap: () => _showComingSoon('Izin Keluar'),
            ),
            const SizedBox(height: 8),

            // ─── E-Voting ──────────────────────────────────────────────
            _LinkRow(
              icon: Icons.how_to_vote_rounded,
              iconBg: AppColors.violet100,
              iconColor: AppColors.violet600,
              title: 'E-Voting',
              subtitle: _votingSubtitle,
              subtitleColor: _unvotedCount > 0 ? AppColors.violet600 : null,
              trailing: _unvotedCount > 0
                  ? _Badge(count: _unvotedCount, color: AppColors.violet600)
                  : null,
              onTap: () => _showComingSoon('E-Voting'),
            ),
            const SizedBox(height: 12),

            // ─── Prestasi ──────────────────────────────────────────────
            Container(
              decoration: BoxDecoration(
                color: AppColors.white,
                borderRadius: AppRadius.card,
                border: Border.all(color: AppColors.gray100),
                boxShadow: AppShadow.sm,
              ),
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(children: [
                    Container(
                      width: 36, height: 36,
                      decoration: BoxDecoration(color: AppColors.yellow100, borderRadius: BorderRadius.circular(10)),
                      child: const Icon(Icons.workspace_premium_rounded, color: AppColors.yellow600, size: 18),
                    ),
                    const SizedBox(width: 12),
                    Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      const Text('Prestasi',
                        style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700)),
                      Text(
                        (_achievementStats['approved'] ?? 0) > 0
                            ? '${_achievementStats['approved']} prestasi disetujui'
                            : 'Laporkan dan kelola prestasi',
                        style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
                    ])),
                    if ((_achievementStats['pending'] ?? 0) > 0)
                      _Badge(count: _achievementStats['pending']!, color: AppColors.yellow600),
                  ]),
                  const SizedBox(height: 12),
                  Row(children: [
                    Expanded(child: _IzinButton(
                      label: (_achievementStats['pending'] ?? 0) > 0
                          ? 'Laporkan (${_achievementStats['pending']})'
                          : 'Laporkan',
                      color: AppColors.yellow50,
                      textColor: AppColors.yellow600,
                      onTap: () => Navigator.push(context,
                        MaterialPageRoute(builder: (_) => const AchievementScreen())))),
                    const SizedBox(width: 8),
                    Expanded(child: _IzinButton(
                      label: 'Prestasi Saya',
                      color: AppColors.gray50,
                      textColor: AppColors.gray700,
                      onTap: () => Navigator.push(context,
                        MaterialPageRoute(builder: (_) => const AchievementScreen())))),
                  ]),
                  if (_pendingVerify > 0) ...[
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      decoration: BoxDecoration(
                        color: AppColors.yellow50,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: AppColors.yellow100),
                      ),
                      child: Row(children: [
                        const Icon(Icons.verified_outlined, color: AppColors.yellow600, size: 16),
                        const SizedBox(width: 8),
                        Expanded(child: Text(
                          '$_pendingVerify prestasi menunggu verifikasi',
                          style: const TextStyle(
                            fontSize: 11, color: AppColors.yellow600, fontWeight: FontWeight.w500),
                        )),
                      ]),
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 8),

            // ─── Ekstrakurikuler ───────────────────────────────────────
            _LinkRow(
              icon: Icons.school_rounded,
              iconBg: AppColors.blue50,
              iconColor: AppColors.blue600,
              title: 'Ekstrakurikuler',
              subtitle: 'Daftar, kelola, dan catat absensi',
              trailing: Consumer<ExtracurricularProvider>(
                builder: (_, p, __) {
                  final joined = p.myExtras.length;
                  if (joined == 0) return const SizedBox.shrink();
                  return _Badge(count: joined, color: AppColors.blue600);
                },
              ),
              onTap: () => Navigator.push(context,
                MaterialPageRoute(builder: (_) => const ExtracurricularScreen())),
            ),
            const SizedBox(height: 8),

            // ─── Tata Tertib ───────────────────────────────────────────
            _LinkRow(
              icon: Icons.balance_rounded,
              iconBg: AppColors.violet50,
              iconColor: AppColors.violet600,
              title: 'Tata Tertib',
              subtitle: 'Peraturan dan tata tertib sekolah',
              onTap: () => Navigator.push(context,
                MaterialPageRoute(builder: (_) => const SchoolRegulationScreen())),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Tab With Badge ───────────────────────────────────────────────────────────

class _TabWithBadge extends StatelessWidget {
  final String label;
  final int    count;
  final Color  badgeColor;
  const _TabWithBadge({required this.label, required this.count, required this.badgeColor});

  @override
  Widget build(BuildContext context) {
    return Row(mainAxisSize: MainAxisSize.min, children: [
      Text(label),
      if (count > 0) ...[
        const SizedBox(width: 4),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
          decoration: BoxDecoration(color: badgeColor, borderRadius: BorderRadius.circular(10)),
          child: Text('$count',
            style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold)),
        ),
      ],
    ]);
  }
}

// ─── Rekap Cell ───────────────────────────────────────────────────────────────

class _RekapCell extends StatelessWidget {
  final String label;
  final int    count;
  final Color  color;
  const _RekapCell({required this.label, required this.count, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text('$count',
            style: TextStyle(color: color, fontSize: 22, fontWeight: FontWeight.bold, height: 1)),
          const SizedBox(height: 4),
          Text(label,
            style: const TextStyle(color: Color(0xFFBFDBFE), fontSize: 10, fontWeight: FontWeight.w500)),
        ],
      ),
    );
  }
}

// ─── Presensi Tab ─────────────────────────────────────────────────────────────

class _PresensiTab extends StatelessWidget {
  final List<AttendanceRecord> records;
  const _PresensiTab({required this.records});

  @override
  Widget build(BuildContext context) {
    if (records.isEmpty) {
      return const _EmptyTab(icon: Icons.event_note_outlined, message: 'Belum ada data presensi');
    }
    return ListView.separated(
      padding: EdgeInsets.zero,
      itemCount: records.length,
      separatorBuilder: (_, __) => const Divider(height: 1, color: AppColors.gray100),
      itemBuilder: (_, i) {
        final rec  = records[i];
        final meta = _statusMeta(rec.status);
        return Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
          child: Row(children: [
            Container(width: 8, height: 8,
              decoration: BoxDecoration(color: meta.$1, shape: BoxShape.circle)),
            const SizedBox(width: 10),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Row(children: [
                Expanded(child: Text(
                  _formatDate(DateTime.tryParse(rec.date) ?? DateTime.now()),
                  style: const TextStyle(
                    fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.gray800))),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(color: meta.$2, borderRadius: BorderRadius.circular(20)),
                  child: Text(meta.$3,
                    style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: meta.$4)),
                ),
              ]),
              if (rec.checkInTime != null)
                Text(
                  'Masuk ${rec.checkInTime!.substring(0, 5)}'
                  '${rec.checkOutTime != null ? ' · Pulang ${rec.checkOutTime!.substring(0, 5)}' : ''}',
                  style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
            ])),
          ]),
        );
      },
    );
  }

  String _formatDate(DateTime d) {
    const days   = ['', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
    const months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return '${days[d.weekday]}, ${d.day} ${months[d.month]} ${d.year}';
  }

  (Color, Color, String, Color) _statusMeta(String status) {
    return switch (status) {
      'hadir'      => (AppColors.green500,  AppColors.green100,      'Hadir',      const Color(0xFF166534)),
      'terlambat'  => (AppColors.yellow500, AppColors.yellow100,     'Terlambat',  const Color(0xFF854D0E)),
      'izin'       => (AppColors.sky500,    AppColors.sky100,        'Izin',       AppColors.sky700),
      'sakit'      => (AppColors.purple500, const Color(0xFFF3E8FF), 'Sakit',      const Color(0xFF6B21A8)),
      'dispensasi' => (AppColors.orange500, AppColors.orange100,     'Dispensasi', AppColors.orange600),
      _            => (AppColors.red500,    AppColors.red100,        'Alpa',       const Color(0xFF991B1B)),
    };
  }
}

// ─── Pelanggaran Tab ──────────────────────────────────────────────────────────

class _PelanggaranTab extends StatelessWidget {
  final List<Map<String, dynamic>> violations;
  final int          totalCount;
  final bool         loaded;
  final VoidCallback onViewAll;
  const _PelanggaranTab({
    required this.violations,
    required this.totalCount,
    required this.loaded,
    required this.onViewAll,
  });

  @override
  Widget build(BuildContext context) {
    if (!loaded) {
      return const Center(child: SizedBox(width: 24, height: 24,
        child: CircularProgressIndicator(strokeWidth: 2)));
    }
    if (violations.isEmpty) {
      return Column(mainAxisAlignment: MainAxisAlignment.center, children: [
        const Icon(Icons.shield_outlined, size: 36, color: AppColors.gray200),
        const SizedBox(height: 8),
        const Text('Tidak ada catatan negatif',
          style: TextStyle(fontSize: 13, color: AppColors.gray400)),
        const SizedBox(height: 10),
        TextButton.icon(
          onPressed: onViewAll,
          icon: const Icon(Icons.open_in_new, size: 14),
          label: const Text('Buka Detail', style: TextStyle(fontSize: 12)),
          style: TextButton.styleFrom(foregroundColor: AppColors.blue600),
        ),
      ]);
    }
    return Column(children: [
      Expanded(child: ListView.separated(
        padding: EdgeInsets.zero,
        itemCount: violations.length,
        separatorBuilder: (_, __) => const Divider(height: 1, color: AppColors.gray100),
        itemBuilder: (_, i) {
          final v = violations[i];
          return Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 9),
            child: Row(children: [
              Container(width: 8, height: 8,
                decoration: const BoxDecoration(color: AppColors.red500, shape: BoxShape.circle)),
              const SizedBox(width: 10),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(v['category_name'] ?? '',
                  maxLines: 1, overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.gray800)),
                if ((v['note'] as String?)?.isNotEmpty == true)
                  Text(v['note'],
                    maxLines: 1, overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
              ])),
              const SizedBox(width: 8),
              Text(_shortDate(v['date'] ?? ''),
                style: const TextStyle(fontSize: 10, color: AppColors.gray400)),
            ]),
          );
        },
      )),
      if (totalCount > violations.length)
        InkWell(
          onTap: onViewAll,
          child: Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 10),
            decoration: const BoxDecoration(
              border: Border(top: BorderSide(color: AppColors.gray100)),
            ),
            child: Text('Lihat semua $totalCount catatan →',
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 11, color: AppColors.blue600, fontWeight: FontWeight.w600)),
          ),
        ),
    ]);
  }

  String _shortDate(String iso) {
    try {
      final d = DateTime.parse(iso);
      const months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      return '${d.day} ${months[d.month]}';
    } catch (_) {
      return iso;
    }
  }
}

// ─── Prestasi Tab ─────────────────────────────────────────────────────────────

class _PrestasiTab extends StatelessWidget {
  final List<Map<String, dynamic>> achievements;
  final int          pendingCount;
  final int          pendingVerify;
  final bool         loaded;
  final VoidCallback onViewAll;
  const _PrestasiTab({
    required this.achievements,
    required this.pendingCount,
    required this.pendingVerify,
    required this.loaded,
    required this.onViewAll,
  });

  @override
  Widget build(BuildContext context) {
    if (!loaded) {
      return const Center(child: SizedBox(width: 24, height: 24,
        child: CircularProgressIndicator(strokeWidth: 2)));
    }
    if (achievements.isEmpty) {
      return Column(mainAxisAlignment: MainAxisAlignment.center, children: [
        const Icon(Icons.workspace_premium_rounded, size: 36, color: AppColors.gray200),
        const SizedBox(height: 8),
        const Text('Belum ada prestasi disetujui',
          style: TextStyle(fontSize: 13, color: AppColors.gray400)),
        if (pendingCount > 0) ...[
          const SizedBox(height: 4),
          Text('$pendingCount prestasi menunggu verifikasi',
            style: const TextStyle(fontSize: 11, color: AppColors.yellow600)),
        ],
        const SizedBox(height: 10),
        OutlinedButton(
          onPressed: onViewAll,
          style: OutlinedButton.styleFrom(
            foregroundColor: AppColors.yellow600,
            side: const BorderSide(color: AppColors.yellow600),
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
            shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
          ),
          child: const Text('Laporkan Prestasi',
            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
        ),
      ]);
    }
    return Column(children: [
      Expanded(child: ListView.separated(
        padding: EdgeInsets.zero,
        itemCount: achievements.length,
        separatorBuilder: (_, __) => const Divider(height: 1, color: AppColors.gray100),
        itemBuilder: (_, i) {
          final a = achievements[i];
          final catName = a['category_name'] as String? ?? '';
          return Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 9),
            child: Row(children: [
              Container(width: 8, height: 8,
                decoration: const BoxDecoration(color: AppColors.yellow500, shape: BoxShape.circle)),
              const SizedBox(width: 10),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(a['title'] ?? '',
                  maxLines: 1, overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.gray800)),
                Text(
                  '${a['level_label'] ?? a['level'] ?? ''}'
                  '${catName.isNotEmpty ? ' · $catName' : ''}',
                  maxLines: 1, overflow: TextOverflow.ellipsis,
                  style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
              ])),
              const SizedBox(width: 8),
              Text(_shortDate(a['achievement_date'] ?? ''),
                style: const TextStyle(fontSize: 10, color: AppColors.gray400)),
            ]),
          );
        },
      )),
      InkWell(
        onTap: onViewAll,
        child: Container(
          width: double.infinity,
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: const BoxDecoration(
            border: Border(top: BorderSide(color: AppColors.gray100)),
          ),
          child: const Text('Laporkan / Lihat Semua →',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 11, color: AppColors.yellow600, fontWeight: FontWeight.w600)),
        ),
      ),
    ]);
  }

  String _shortDate(String iso) {
    try {
      final d = DateTime.parse(iso);
      const months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      return '${d.day} ${months[d.month]}';
    } catch (_) {
      return iso;
    }
  }
}

// ─── Empty Tab ────────────────────────────────────────────────────────────────

class _EmptyTab extends StatelessWidget {
  final IconData icon;
  final String   message;
  const _EmptyTab({required this.icon, required this.message});

  @override
  Widget build(BuildContext context) {
    return Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
      Icon(icon, size: 36, color: AppColors.gray300),
      const SizedBox(height: 8),
      Text(message, style: const TextStyle(fontSize: 13, color: AppColors.gray400)),
    ]));
  }
}

// ─── Badge ────────────────────────────────────────────────────────────────────

class _Badge extends StatelessWidget {
  final int   count;
  final Color color;
  const _Badge({required this.count, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(20)),
      child: Text('$count',
        style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
    );
  }
}

// ─── Link Row ─────────────────────────────────────────────────────────────────

class _LinkRow extends StatelessWidget {
  final IconData  icon;
  final Color     iconBg;
  final Color     iconColor;
  final String    title;
  final String    subtitle;
  final Color?    subtitleColor;
  final Widget?   trailing;
  final VoidCallback onTap;

  const _LinkRow({
    required this.icon,
    required this.iconBg,
    required this.iconColor,
    required this.title,
    required this.subtitle,
    this.subtitleColor,
    this.trailing,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: AppRadius.card,
          border: Border.all(color: AppColors.gray100),
          boxShadow: AppShadow.sm,
        ),
        padding: const EdgeInsets.all(14),
        child: Row(children: [
          Container(
            width: 36, height: 36,
            decoration: BoxDecoration(color: iconBg, borderRadius: BorderRadius.circular(10)),
            child: Icon(icon, color: iconColor, size: 18),
          ),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(title,
              style: const TextStyle(
                fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700)),
            Text(subtitle,
              style: TextStyle(fontSize: 11, color: subtitleColor ?? AppColors.gray400)),
          ])),
          if (trailing != null) ...[const SizedBox(width: 8), trailing!],
          const SizedBox(width: 4),
          const Icon(Icons.chevron_right, color: AppColors.gray300, size: 18),
        ]),
      ),
    );
  }
}

// ─── Izin Button ──────────────────────────────────────────────────────────────

class _IzinButton extends StatelessWidget {
  final String label;
  final Color  color;
  final Color  textColor;
  final VoidCallback onTap;

  const _IzinButton({
    required this.label, required this.color,
    required this.textColor, required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10),
        decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(12)),
        alignment: Alignment.center,
        child: Text(label,
          style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: textColor)),
      ),
    );
  }
}
