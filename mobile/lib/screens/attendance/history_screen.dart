import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../models/attendance.dart';
import '../../providers/attendance_provider.dart';
import '../../theme/app_colors.dart';

class HistoryScreen extends StatefulWidget {
  const HistoryScreen({super.key});

  @override
  State<HistoryScreen> createState() => _HistoryScreenState();
}

class _HistoryScreenState extends State<HistoryScreen> {
  late int _month;
  late int _year;

  @override
  void initState() {
    super.initState();
    _month = DateTime.now().month;
    _year  = DateTime.now().year;
    _load();
  }

  void _load() =>
      context.read<AttendanceProvider>().fetchHistory(month: _month, year: _year);

  bool get _canNext {
    final now = DateTime.now();
    return _year < now.year || (_year == now.year && _month < now.month);
  }

  void _prevMonth() {
    setState(() {
      if (_month == 1) { _month = 12; _year--; }
      else { _month--; }
    });
    _load();
  }

  void _nextMonth() {
    if (!_canNext) return;
    setState(() {
      if (_month == 12) { _month = 1; _year++; }
      else { _month++; }
    });
    _load();
  }

  @override
  Widget build(BuildContext context) {
    final prov    = context.watch<AttendanceProvider>();
    final history = prov.history;
    final title   = DateFormat('MMMM y', 'id_ID').format(DateTime(_year, _month));

    return Scaffold(
      backgroundColor: AppColors.slate100,
      body: SafeArea(
        bottom: false,
        child: Column(
          children: [
            // ── Header: gradient + navigasi bulan + ringkasan ────────────
            _GradientHeader(
              title:    title,
              summary:  history?.summary ?? {},
              canNext:  _canNext,
              onPrev:   _prevMonth,
              onNext:   _nextMonth,
            ),

            // ── Konten list ──────────────────────────────────────────────
            Expanded(
              child: prov.isLoadingHistory
                  ? const Center(child: CircularProgressIndicator())
                  : prov.error != null
                      ? Center(
                          child: Padding(
                            padding: const EdgeInsets.all(24),
                            child: Text(prov.error!,
                              style: const TextStyle(color: AppColors.red500),
                              textAlign: TextAlign.center,
                            ),
                          ),
                        )
                      : history == null
                          ? const Center(
                              child: Text('Tidak ada data',
                                style: TextStyle(color: AppColors.gray400)))
                          : _RecordList(records: history.records),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Gradient Header (bulan + ringkasan) ─────────────────────────────────────

class _GradientHeader extends StatelessWidget {
  final String         title;
  final Map<String, int> summary;
  final bool           canNext;
  final VoidCallback   onPrev;
  final VoidCallback   onNext;

  const _GradientHeader({
    required this.title,
    required this.summary,
    required this.canNext,
    required this.onPrev,
    required this.onNext,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(gradient: AppColors.primaryGradient),
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
      child: Column(
        children: [
          // Navigasi bulan
          Row(
            children: [
              // Tombol kembali
              GestureDetector(
                onTap: () => Navigator.of(context).pop(),
                child: const Icon(Icons.arrow_back, color: Colors.white, size: 22),
              ),
              const SizedBox(width: 8),

              // ← bulan
              _NavBtn(icon: Icons.chevron_left, onTap: onPrev),
              const SizedBox(width: 8),

              // Judul
              Expanded(
                child: Column(
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        color:      Colors.white,
                        fontSize:   17,
                        fontWeight: FontWeight.bold,
                      ),
                      textAlign: TextAlign.center,
                    ),
                    const Text(
                      'Riwayat Presensi',
                      style: TextStyle(color: AppColors.blue200, fontSize: 11),
                      textAlign: TextAlign.center,
                    ),
                  ],
                ),
              ),

              // bulan →
              const SizedBox(width: 8),
              _NavBtn(
                icon:  Icons.chevron_right,
                onTap: canNext ? onNext : null,
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Grid ringkasan 3×2
          GridView.count(
            crossAxisCount: 3,
            shrinkWrap:     true,
            physics:        const NeverScrollableScrollPhysics(),
            crossAxisSpacing: 8,
            mainAxisSpacing:  8,
            childAspectRatio: 2.2,
            children: [
              _SummaryCell(count: summary['hadir']      ?? 0, label: 'Hadir',      valueColor: const Color(0xFF86EFAC)),
              _SummaryCell(count: summary['terlambat']  ?? 0, label: 'Terlambat',  valueColor: const Color(0xFFFDE047)),
              _SummaryCell(count: summary['alpa']       ?? 0, label: 'Alpa',       valueColor: const Color(0xFFFCA5A5)),
              _SummaryCell(count: summary['izin']       ?? 0, label: 'Izin',       valueColor: const Color(0xFF93C5FD)),
              _SummaryCell(count: summary['sakit']      ?? 0, label: 'Sakit',      valueColor: const Color(0xFFD8B4FE)),
              _SummaryCell(count: summary['dispensasi'] ?? 0, label: 'Dispensasi', valueColor: const Color(0xFF6EE7B7)),
            ],
          ),
        ],
      ),
    );
  }
}

class _NavBtn extends StatelessWidget {
  final IconData     icon;
  final VoidCallback? onTap;

  const _NavBtn({required this.icon, this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 32, height: 32,
        decoration: BoxDecoration(
          color:        Colors.white.withOpacity(onTap != null ? 0.20 : 0.08),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Icon(icon,
          color: Colors.white.withOpacity(onTap != null ? 1.0 : 0.30),
          size: 18,
        ),
      ),
    );
  }
}

class _SummaryCell extends StatelessWidget {
  final int    count;
  final String label;
  final Color  valueColor;

  const _SummaryCell({
    required this.count,
    required this.label,
    required this.valueColor,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color:        Colors.white.withOpacity(0.15),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(
            '$count',
            style: TextStyle(
              color:      valueColor,
              fontSize:   20,
              fontWeight: FontWeight.bold,
              height:     1,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: const TextStyle(
              color:    AppColors.blue200,
              fontSize: 10,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Record List ──────────────────────────────────────────────────────────────

class _RecordList extends StatelessWidget {
  final List<AttendanceRecord> records;
  const _RecordList({required this.records});

  @override
  Widget build(BuildContext context) {
    if (records.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(40),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.calendar_today_outlined,
                size: 40, color: AppColors.gray400.withOpacity(0.6)),
              const SizedBox(height: 12),
              const Text(
                'Tidak ada data presensi bulan ini',
                style: TextStyle(color: AppColors.gray400, fontSize: 13),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      );
    }

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
      children: [
        const Padding(
          padding: EdgeInsets.only(bottom: 8),
          child: Text(
            'Detail Presensi',
            style: TextStyle(
              fontWeight: FontWeight.w600,
              fontSize:   13,
              color:      AppColors.gray700,
            ),
          ),
        ),
        ...records.map((r) => _RecordCard(record: r)),
      ],
    );
  }
}

class _RecordCard extends StatelessWidget {
  final AttendanceRecord record;
  const _RecordCard({required this.record});

  @override
  Widget build(BuildContext context) {
    final date    = DateTime.parse(record.date);
    final dayFull = DateFormat('EEEE, d MMM y', 'id_ID').format(date);

    final (dotColor, badgeBg, badgeFg) = _statusColors;

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      decoration: BoxDecoration(
        color:        AppColors.white,
        borderRadius: AppRadius.card,
        border:       Border.all(color: AppColors.gray100),
        boxShadow:    AppShadow.sm,
      ),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Status dot
          Padding(
            padding: const EdgeInsets.only(top: 5, right: 12),
            child: Container(
              width: 8, height: 8,
              decoration: BoxDecoration(shape: BoxShape.circle, color: dotColor),
            ),
          ),

          // Content
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        dayFull,
                        style: const TextStyle(
                          fontSize:   13,
                          fontWeight: FontWeight.w600,
                          color:      AppColors.gray800,
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    // Badge pill
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color:        badgeBg,
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        record.statusLabel,
                        style: TextStyle(
                          fontSize:   11,
                          fontWeight: FontWeight.w600,
                          color:      badgeFg,
                        ),
                      ),
                    ),
                  ],
                ),
                if (record.checkInTime != null || record.checkOutTime != null) ...[
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      if (record.checkInTime != null)
                        Text(
                          'Masuk ${record.checkInTime!.substring(0, 5)}',
                          style: const TextStyle(fontSize: 11, color: AppColors.gray400),
                        ),
                      if (record.checkInTime != null && record.checkOutTime != null)
                        const Text('  ·  ',
                          style: TextStyle(fontSize: 11, color: AppColors.gray400)),
                      if (record.checkOutTime != null)
                        Text(
                          'Pulang ${record.checkOutTime!.substring(0, 5)}',
                          style: const TextStyle(fontSize: 11, color: AppColors.gray400),
                        ),
                      if (record.isFakeGps) ...[
                        const Text('  ·  ',
                          style: TextStyle(fontSize: 11, color: AppColors.gray400)),
                        const Text('Fake GPS',
                          style: TextStyle(
                            fontSize:   11,
                            fontWeight: FontWeight.w600,
                            color:      AppColors.red500,
                          )),
                      ],
                    ],
                  ),
                ],

                // Thumbnail foto selfie
                if (record.checkInPhotoUrl != null || record.checkOutPhotoUrl != null) ...[
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      if (record.checkInPhotoUrl != null)
                        _Thumbnail(url: record.checkInPhotoUrl!, label: 'Masuk'),
                      if (record.checkInPhotoUrl != null && record.checkOutPhotoUrl != null)
                        const SizedBox(width: 8),
                      if (record.checkOutPhotoUrl != null)
                        _Thumbnail(url: record.checkOutPhotoUrl!, label: 'Pulang'),
                    ],
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  (Color, Color, Color) get _statusColors => switch (record.status) {
    'hadir'      => (AppColors.green500,   AppColors.green100,   AppColors.green900),
    'terlambat'  => (AppColors.yellow500,  AppColors.amber100,   const Color(0xFF78350F)),
    'izin'       => (AppColors.blue600,    AppColors.blue100,    const Color(0xFF1E40AF)),
    'sakit'      => (AppColors.purple500,  const Color(0xFFF3E8FF), const Color(0xFF581C87)),
    'dispensasi' => (AppColors.teal500,    const Color(0xFFCCFBF1), const Color(0xFF134E4A)),
    _            => (AppColors.red500,     AppColors.red100,     const Color(0xFF7F1D1D)),
  };
}

class _Thumbnail extends StatelessWidget {
  final String url;
  final String label;
  const _Thumbnail({required this.url, required this.label});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        ClipRRect(
          borderRadius: BorderRadius.circular(8),
          child: Image.network(
            url,
            width: 56, height: 56,
            fit: BoxFit.cover,
            errorBuilder: (_, __, ___) => Container(
              width: 56, height: 56,
              decoration: BoxDecoration(
                color:        AppColors.gray100,
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Icon(Icons.broken_image_outlined,
                size: 20, color: AppColors.gray400),
            ),
          ),
        ),
        const SizedBox(height: 2),
        Text(label, style: const TextStyle(fontSize: 9, color: AppColors.gray400)),
      ],
    );
  }
}
