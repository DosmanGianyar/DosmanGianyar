import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/attendance.dart';
import '../../services/orangtua_service.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';

/// Riwayat presensi anak — versi baca-saja untuk akun orangtua.
class OrangtuaAttendanceScreen extends StatefulWidget {
  final int    studentId;
  final String studentName;
  const OrangtuaAttendanceScreen({super.key, required this.studentId, required this.studentName});

  @override
  State<OrangtuaAttendanceScreen> createState() => _OrangtuaAttendanceScreenState();
}

class _OrangtuaAttendanceScreenState extends State<OrangtuaAttendanceScreen> {
  late int _month;
  late int _year;
  AttendanceHistory? _history;
  bool    _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _month = DateTime.now().month;
    _year  = DateTime.now().year;
    _load();
  }

  Future<void> _load() async {
    setState(() { _isLoading = true; _error = null; });
    try {
      final history = await OrangtuaService.getAttendanceHistory(
        studentId: widget.studentId, month: _month, year: _year,
      );
      if (mounted) setState(() => _history = history);
    } catch (e) {
      if (mounted) setState(() => _error = ApiClient.extractError(e));
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

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
    final title = DateFormat('MMMM y', 'id_ID').format(DateTime(_year, _month));

    return Scaffold(
      backgroundColor: AppColors.slate100,
      body: SafeArea(
        bottom: false,
        child: Column(
          children: [
            _GradientHeader(
              title:       title,
              studentName: widget.studentName,
              summary:     _history?.summary ?? {},
              canNext:     _canNext,
              onPrev:      _prevMonth,
              onNext:      _nextMonth,
            ),
            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _error != null
                      ? Center(
                          child: Padding(
                            padding: const EdgeInsets.all(24),
                            child: Text(_error!,
                              style: const TextStyle(color: AppColors.red500),
                              textAlign: TextAlign.center),
                          ),
                        )
                      : _RecordList(records: _history?.records ?? []),
            ),
          ],
        ),
      ),
    );
  }
}

class _GradientHeader extends StatelessWidget {
  final String            title;
  final String            studentName;
  final Map<String, int>  summary;
  final bool              canNext;
  final VoidCallback      onPrev;
  final VoidCallback      onNext;

  const _GradientHeader({
    required this.title,
    required this.studentName,
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
          Row(
            children: [
              GestureDetector(
                onTap: () => Navigator.of(context).pop(),
                child: const Icon(Icons.arrow_back, color: Colors.white, size: 22),
              ),
              const SizedBox(width: 8),
              _NavBtn(icon: Icons.chevron_left, onTap: onPrev),
              const SizedBox(width: 8),
              Expanded(
                child: Column(
                  children: [
                    Text(title,
                      style: const TextStyle(color: Colors.white, fontSize: 17, fontWeight: FontWeight.bold),
                      textAlign: TextAlign.center),
                    Text('Riwayat Presensi — $studentName',
                      style: const TextStyle(color: AppColors.blue200, fontSize: 11),
                      textAlign: TextAlign.center,
                      maxLines: 1, overflow: TextOverflow.ellipsis),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              _NavBtn(icon: Icons.chevron_right, onTap: canNext ? onNext : null),
            ],
          ),
          const SizedBox(height: 16),
          GridView.count(
            crossAxisCount: 3,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            crossAxisSpacing: 8,
            mainAxisSpacing: 8,
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
  final IconData      icon;
  final VoidCallback? onTap;
  const _NavBtn({required this.icon, this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 32, height: 32,
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(onTap != null ? 0.20 : 0.08),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Icon(icon, color: Colors.white.withOpacity(onTap != null ? 1.0 : 0.30), size: 18),
      ),
    );
  }
}

class _SummaryCell extends StatelessWidget {
  final int    count;
  final String label;
  final Color  valueColor;
  const _SummaryCell({required this.count, required this.label, required this.valueColor});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(color: Colors.white.withOpacity(0.15), borderRadius: BorderRadius.circular(10)),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text('$count', style: TextStyle(color: valueColor, fontSize: 20, fontWeight: FontWeight.bold, height: 1)),
          const SizedBox(height: 2),
          Text(label, style: const TextStyle(color: AppColors.blue200, fontSize: 10, fontWeight: FontWeight.w500)),
        ],
      ),
    );
  }
}

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
              Icon(Icons.calendar_today_outlined, size: 40, color: AppColors.gray400.withOpacity(0.6)),
              const SizedBox(height: 12),
              const Text('Tidak ada data presensi bulan ini',
                style: TextStyle(color: AppColors.gray400, fontSize: 13), textAlign: TextAlign.center),
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
          child: Text('Detail Presensi',
            style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: AppColors.gray700)),
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
        color: AppColors.white, borderRadius: AppRadius.card,
        border: Border.all(color: AppColors.gray100), boxShadow: AppShadow.sm,
      ),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.only(top: 5, right: 12),
            child: Container(width: 8, height: 8, decoration: BoxDecoration(shape: BoxShape.circle, color: dotColor)),
          ),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(child: Text(dayFull,
                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray800))),
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(color: badgeBg, borderRadius: BorderRadius.circular(20)),
                      child: Text(record.statusLabel,
                        style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: badgeFg)),
                    ),
                  ],
                ),
                if (record.checkInTime != null || record.checkOutTime != null) ...[
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      if (record.checkInTime != null)
                        Text('Masuk ${record.checkInTime!.substring(0, 5)}',
                          style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
                      if (record.checkInTime != null && record.checkOutTime != null)
                        const Text('  ·  ', style: TextStyle(fontSize: 11, color: AppColors.gray400)),
                      if (record.checkOutTime != null)
                        Text('Pulang ${record.checkOutTime!.substring(0, 5)}',
                          style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
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
