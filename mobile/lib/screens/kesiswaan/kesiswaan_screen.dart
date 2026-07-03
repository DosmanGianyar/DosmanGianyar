import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/attendance.dart';
import '../../providers/attendance_provider.dart';
import '../../providers/extracurricular_provider.dart';
import '../../theme/app_colors.dart';
import '../attendance/history_screen.dart';
import '../extracurricular/extracurricular_screen.dart';
import 'school_regulation_screen.dart';

class KesiswaanScreen extends StatefulWidget {
  const KesiswaanScreen({super.key});

  @override
  State<KesiswaanScreen> createState() => _KesiswaanScreenState();
}

class _KesiswaanScreenState extends State<KesiswaanScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabCtrl;

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 3, vsync: this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final prov = context.read<AttendanceProvider>();
      if (prov.currentMonthRecords.isEmpty) {
        prov.fetchCurrentMonthDots();
      }
      if (prov.history == null) {
        final now = DateTime.now();
        prov.fetchHistory(month: now.month, year: now.year);
      }
    });
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

  @override
  Widget build(BuildContext context) {
    final attProv = context.watch<AttendanceProvider>();
    final records = attProv.currentMonthRecords;

    // Hitung rekap per status
    final summary = <String, int>{
      'hadir': 0, 'terlambat': 0, 'alpa': 0,
      'izin': 0, 'sakit': 0, 'dispensasi': 0,
    };
    for (final r in records) {
      if (summary.containsKey(r.status)) summary[r.status] = summary[r.status]! + 1;
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // ─── Rekap Absensi Bulan Ini ─────────────────────────────────
          Container(
            decoration: BoxDecoration(
              gradient: AppColors.primaryGradient,
              borderRadius: AppRadius.card,
            ),
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
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
                  ],
                ),
                const SizedBox(height: 12),
                GridView.count(
                  shrinkWrap:   true,
                  physics:      const NeverScrollableScrollPhysics(),
                  crossAxisCount: 3,
                  mainAxisSpacing:  8,
                  crossAxisSpacing: 8,
                  childAspectRatio: 1.5,
                  children: [
                    _RekapCell(label: 'Hadir',       count: summary['hadir']!,       color: const Color(0xFF86EFAC)),
                    _RekapCell(label: 'Terlambat',   count: summary['terlambat']!,   color: const Color(0xFFFDE047)),
                    _RekapCell(label: 'Alpa',        count: summary['alpa']!,        color: const Color(0xFFFCA5A5)),
                    _RekapCell(label: 'Izin',        count: summary['izin']!,        color: const Color(0xFF7DD3FC)),
                    _RekapCell(label: 'Sakit',       count: summary['sakit']!,       color: const Color(0xFFD8B4FE)),
                    _RekapCell(label: 'Dispensasi',  count: summary['dispensasi']!,  color: const Color(0xFFFDBA74)),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),

          // ─── Prestasi Card ───────────────────────────────────────────
          GestureDetector(
            onTap: () => _showComingSoon('Prestasi'),
            child: Container(
              decoration: BoxDecoration(
                color: AppColors.white,
                borderRadius: AppRadius.card,
                border: Border.all(color: AppColors.gray100),
                boxShadow: AppShadow.sm,
              ),
              padding: const EdgeInsets.all(16),
              child: Row(children: [
                Container(
                  width: 32, height: 32,
                  decoration: BoxDecoration(
                    color: AppColors.yellow100,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.star_rounded, color: AppColors.yellow600, size: 18),
                ),
                const SizedBox(width: 12),
                Expanded(child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: const [
                    Text('Prestasi', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700)),
                    Text('Lihat & laporkan prestasi', style: TextStyle(fontSize: 11, color: AppColors.gray400)),
                  ],
                )),
                const Icon(Icons.chevron_right, color: AppColors.gray300, size: 18),
              ]),
            ),
          ),
          const SizedBox(height: 12),

          // ─── Tab: Presensi | Pelanggaran | Prestasi ──────────────────
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
                  tabs: const [
                    Tab(text: 'Presensi'),
                    Tab(text: 'Pelanggaran'),
                    Tab(text: 'Prestasi'),
                  ],
                ),
                SizedBox(
                  height: 300,
                  child: TabBarView(
                    controller: _tabCtrl,
                    children: [
                      _PresensiTab(records: records),
                      _EmptyTab(icon: Icons.check_circle_outline, message: 'Tidak ada catatan pelanggaran', iconColor: AppColors.green500),
                      _EmptyTab(icon: Icons.star_outline_rounded, message: 'Belum ada prestasi tercatat', iconColor: AppColors.yellow500),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),

          // ─── Izin, Sakit & Dispensasi ─────────────────────────────────
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
                  const Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Text('Izin, Sakit & Dispensasi',
                      style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700)),
                    Text('Tidak ada pengajuan aktif',
                      style: TextStyle(fontSize: 11, color: AppColors.gray400)),
                  ]),
                ]),
                const SizedBox(height: 12),
                Row(children: [
                  Expanded(child: _IzinButton(label: 'Izin', color: AppColors.sky50, textColor: AppColors.sky700, onTap: () => _showComingSoon('Izin'))),
                  const SizedBox(width: 8),
                  Expanded(child: _IzinButton(label: 'Dispensasi', color: AppColors.orange50, textColor: AppColors.orange600, onTap: () => _showComingSoon('Dispensasi'))),
                  const SizedBox(width: 8),
                  Expanded(child: _IzinButton(label: 'Riwayat', color: AppColors.gray50, textColor: AppColors.gray600, onTap: () => _showComingSoon('Riwayat Izin'))),
                ]),
              ],
            ),
          ),
          const SizedBox(height: 8),

          // ─── Izin Pulang Lebih Awal ───────────────────────────────────
          _LinkRow(
            icon: Icons.logout_rounded,
            iconBg: AppColors.emerald100,
            iconColor: AppColors.emerald600,
            title: 'Izin Pulang Lebih Awal',
            subtitle: 'Ajukan izin pulang sebelum jam normal',
            onTap: () => _showComingSoon('Izin Pulang Lebih Awal'),
          ),
          const SizedBox(height: 8),

          // ─── Lupa Absen ───────────────────────────────────────────────
          _LinkRow(
            icon: Icons.schedule_rounded,
            iconBg: AppColors.amber100,
            iconColor: AppColors.amber500,
            title: 'Lupa Absen',
            subtitle: 'Ajukan koreksi presensi ke wali kelas',
            onTap: () => _showComingSoon('Lupa Absen'),
          ),
          const SizedBox(height: 8),

          // ─── Izin Keluar Kelas ────────────────────────────────────────
          _LinkRow(
            icon: Icons.door_front_door_outlined,
            iconBg: AppColors.emerald100,
            iconColor: AppColors.emerald600,
            title: 'Izin Keluar Kelas',
            subtitle: 'Buat izin keluar kelas',
            onTap: () => _showComingSoon('Izin Keluar'),
          ),
          const SizedBox(height: 8),

          // ─── E-Voting ─────────────────────────────────────────────────
          _LinkRow(
            icon: Icons.how_to_vote_rounded,
            iconBg: AppColors.violet100,
            iconColor: AppColors.violet600,
            title: 'E-Voting',
            subtitle: 'Tidak ada sesi voting aktif',
            onTap: () => _showComingSoon('E-Voting'),
          ),
          const SizedBox(height: 12),

          // ─── Prestasi ─────────────────────────────────────────────────
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
                  const Text('Prestasi',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700)),
                ]),
                const SizedBox(height: 12),
                Row(children: [
                  Expanded(child: _IzinButton(label: 'Laporkan', color: AppColors.yellow50, textColor: AppColors.yellow600, onTap: () => _showComingSoon('Laporkan Prestasi'))),
                  const SizedBox(width: 8),
                  Expanded(child: _IzinButton(label: 'Prestasi Saya', color: AppColors.gray50, textColor: AppColors.gray700, onTap: () => _showComingSoon('Prestasi Saya'))),
                ]),
              ],
            ),
          ),
          const SizedBox(height: 8),

          // ─── Ekstrakurikuler ──────────────────────────────────────────
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
                return Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(color: AppColors.blue600, borderRadius: BorderRadius.circular(20)),
                  child: Text('$joined', style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                );
              },
            ),
            onTap: () => Navigator.push(context,
              MaterialPageRoute(builder: (_) => const ExtracurricularScreen())),
          ),
          const SizedBox(height: 8),

          // ─── Tata Tertib ──────────────────────────────────────────────
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
    );
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
        final rec = records[i];
        final meta = _statusMeta(rec.status);
        return Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
          child: Row(children: [
            Container(width: 8, height: 8, decoration: BoxDecoration(color: meta.$1, shape: BoxShape.circle)),
            const SizedBox(width: 10),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Row(children: [
                Expanded(child: Text(_formatDate(DateTime.tryParse(rec.date) ?? DateTime.now()),
                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.gray800))),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(color: meta.$2, borderRadius: BorderRadius.circular(20)),
                  child: Text(meta.$3, style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: meta.$4)),
                ),
              ]),
              if (rec.checkInTime != null)
                Text('Masuk ${rec.checkInTime!.substring(0, 5)}${rec.checkOutTime != null ? ' · Pulang ${rec.checkOutTime!.substring(0, 5)}' : ''}',
                  style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
            ])),
          ]),
        );
      },
    );
  }

  String _formatDate(DateTime d) {
    const days = ['', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
    const months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return '${days[d.weekday]}, ${d.day} ${months[d.month]} ${d.year}';
  }

  (Color, Color, String, Color) _statusMeta(String status) {
    return switch (status) {
      'hadir'      => (AppColors.green500,  AppColors.green100,  'Hadir',      const Color(0xFF166534)),
      'terlambat'  => (AppColors.yellow500, AppColors.yellow100, 'Terlambat',  const Color(0xFF854D0E)),
      'izin'       => (AppColors.sky500,    AppColors.sky100,    'Izin',       AppColors.sky700),
      'sakit'      => (AppColors.purple500, const Color(0xFFF3E8FF), 'Sakit',  const Color(0xFF6B21A8)),
      'dispensasi' => (AppColors.orange500, AppColors.orange100, 'Dispensasi', AppColors.orange600),
      _            => (AppColors.red500,    AppColors.red100,    'Alpa',       const Color(0xFF991B1B)),
    };
  }
}

// ─── Empty Tab ────────────────────────────────────────────────────────────────

class _EmptyTab extends StatelessWidget {
  final IconData icon;
  final String   message;
  final Color    iconColor;
  const _EmptyTab({required this.icon, required this.message, this.iconColor = AppColors.gray300});

  @override
  Widget build(BuildContext context) {
    return Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
      Icon(icon, size: 36, color: iconColor),
      const SizedBox(height: 8),
      Text(message, style: const TextStyle(fontSize: 13, color: AppColors.gray400)),
    ]));
  }
}

// ─── Link Row ─────────────────────────────────────────────────────────────────

class _LinkRow extends StatelessWidget {
  final IconData icon;
  final Color    iconBg;
  final Color    iconColor;
  final String   title;
  final String   subtitle;
  final Widget?  trailing;
  final VoidCallback onTap;

  const _LinkRow({
    required this.icon,
    required this.iconBg,
    required this.iconColor,
    required this.title,
    required this.subtitle,
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
            Text(title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700)),
            Text(subtitle, style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
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

  const _IzinButton({required this.label, required this.color, required this.textColor, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10),
        decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(12)),
        alignment: Alignment.center,
        child: Text(label, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: textColor)),
      ),
    );
  }
}
