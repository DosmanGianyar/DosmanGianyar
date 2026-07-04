import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/guru_models.dart';
import '../../services/guru_service.dart';
import '../../theme/app_colors.dart';
import 'widgets/guru_widgets.dart';

class GuruRekapScreen extends StatefulWidget {
  final List<GuruClass> classes;
  final int? initialClassId;

  const GuruRekapScreen({
    super.key,
    required this.classes,
    this.initialClassId,
  });

  @override
  State<GuruRekapScreen> createState() => _GuruRekapScreenState();
}

class _GuruRekapScreenState extends State<GuruRekapScreen> {
  late int _classId;
  late int _month;
  late int _year;
  RekapAbsensi? _data;
  bool _loading = true;
  String? _error;

  static const _statusColors = {
    'hadir':      Color(0xFF22C55E),
    'terlambat':  Color(0xFFEAB308),
    'izin':       Color(0xFF60A5FA),
    'sakit':      Color(0xFFA855F7),
    'dispensasi': Color(0xFF14B8A6),
    'alpa':       Color(0xFFEF4444),
  };

  static const _statusCodes = {
    'hadir':      'H',
    'terlambat':  'T',
    'izin':       'I',
    'sakit':      'S',
    'dispensasi': 'D',
    'alpa':       'A',
    'future':     '·',
  };

  @override
  void initState() {
    super.initState();
    final now = DateTime.now();
    _classId = widget.initialClassId ?? (widget.classes.isNotEmpty ? widget.classes.first.id : 0);
    _month   = now.month;
    _year    = now.year;
    _load();
  }

  Future<void> _load() async {
    if (widget.classes.isEmpty) return;
    setState(() { _loading = true; _error = null; });
    try {
      final data = await GuruService.getAttendanceRekap(
        classId: _classId,
        month:   _month,
        year:    _year,
      );
      if (mounted) setState(() { _data = data; _loading = false; });
    } catch (e) {
      if (mounted) setState(() { _error = e.toString(); _loading = false; });
    }
  }

  void _prevMonth() {
    if (_month == 1) { _month = 12; _year--; } else { _month--; }
    _load();
  }

  void _nextMonth() {
    final now = DateTime.now();
    if (_year > now.year || (_year == now.year && _month >= now.month)) return;
    if (_month == 12) { _month = 1; _year++; } else { _month++; }
    _load();
  }

  bool get _canNext {
    final now = DateTime.now();
    return !(_year == now.year && _month >= now.month);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(title: const Text('Rekap Absensi')),
      body: Column(
        children: [
          _buildFilters(),
          Expanded(child: _buildBody()),
        ],
      ),
    );
  }

  Widget _buildFilters() {
    return Container(
      color: AppColors.white,
      padding: const EdgeInsets.fromLTRB(0, 12, 0, 12),
      child: Column(
        children: [
          ClassFilterBar(
            classes: widget.classes.map((c) => (id: c.id, name: c.name)).toList(),
            selectedId: _classId,
            onChanged: (id) { _classId = id; _load(); },
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                IconButton(
                  onPressed: _prevMonth,
                  icon: const Icon(Icons.chevron_left_rounded, color: AppColors.blue600),
                ),
                Text(
                  DateFormat('MMMM y', 'id_ID').format(DateTime(_year, _month)),
                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.gray800),
                ),
                IconButton(
                  onPressed: _canNext ? _nextMonth : null,
                  icon: Icon(
                    Icons.chevron_right_rounded,
                    color: _canNext ? AppColors.blue600 : AppColors.gray300,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBody() {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_error != null) return ErrorRetry(onRetry: _load);
    if (_data == null) return const SizedBox.shrink();

    return RefreshIndicator(
      onRefresh: _load,
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            _buildLegend(),
            const SizedBox(height: 12),
            _buildSummaryRow(_data!),
            const SizedBox(height: 12),
            _buildGrid(_data!),
          ],
        ),
      ),
    );
  }

  Widget _buildLegend() {
    final items = [
      ('H', 'Hadir',     AppColors.green500),
      ('T', 'Terlambat', AppColors.yellow500),
      ('I', 'Izin',      AppColors.blue500),
      ('S', 'Sakit',     AppColors.purple500),
      ('A', 'Alpa',      AppColors.red500),
      ('D', 'Dispen',    AppColors.teal500),
      ('L', 'Libur',     AppColors.gray300),
    ];
    return Wrap(
      spacing: 12,
      runSpacing: 4,
      children: items.map((item) => Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(width: 14, height: 14, decoration: BoxDecoration(color: item.$3, borderRadius: BorderRadius.circular(3))),
          const SizedBox(width: 4),
          Text(item.$1, style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: item.$3)),
          const SizedBox(width: 2),
          Text(item.$2, style: const TextStyle(fontSize: 10, color: AppColors.gray500)),
        ],
      )).toList(),
    );
  }

  Widget _buildSummaryRow(RekapAbsensi data) {
    final schoolDayCount = data.schoolDays.length;
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppRadius.xl),
        border: Border.all(color: AppColors.gray100),
        boxShadow: AppShadow.sm,
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            '${data.students.length} siswa',
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.gray700),
          ),
          Text(
            '$schoolDayCount hari sekolah',
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.blue600),
          ),
        ],
      ),
    );
  }

  Widget _buildGrid(RekapAbsensi data) {
    final offDaySet = Set<String>.from(data.offDays);
    final allDays   = data.allDays;
    const cellW     = 22.0;
    const nameW     = 120.0;
    const countW    = 24.0;
    const counts    = ['H', 'T', 'I', 'S', 'A', 'D'];

    return Container(
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppRadius.xl2),
        border: Border.all(color: AppColors.gray100),
        boxShadow: AppShadow.sm,
      ),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header row
            Container(
              padding: const EdgeInsets.symmetric(vertical: 8),
              decoration: const BoxDecoration(
                color: AppColors.gray50,
                borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadius.xl2)),
              ),
              child: Row(children: [
                const SizedBox(
                  width: nameW,
                  child: Padding(
                    padding: EdgeInsets.only(left: 12),
                    child: Text('Nama', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.gray600)),
                  ),
                ),
                ...allDays.map((d) {
                  final isOff = offDaySet.contains(d);
                  final day   = int.parse(d.substring(8, 10));
                  return SizedBox(
                    width: cellW,
                    child: Column(children: [
                      Text('$day', style: TextStyle(fontSize: 9, fontWeight: FontWeight.w600, color: isOff ? AppColors.gray300 : AppColors.gray500)),
                    ]),
                  );
                }),
                ...counts.map((c) => SizedBox(
                  width: countW,
                  child: Text(c, textAlign: TextAlign.center,
                      style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.gray500)),
                )),
              ]),
            ),
            const Divider(height: 1, color: AppColors.gray100),
            // Student rows
            ...data.students.asMap().entries.map((entry) {
              final i = entry.key;
              final student = entry.value;
              return Container(
                decoration: BoxDecoration(
                  border: i < data.students.length - 1
                      ? const Border(bottom: BorderSide(color: AppColors.gray100, width: 0.5))
                      : null,
                ),
                padding: const EdgeInsets.symmetric(vertical: 4),
                child: Row(children: [
                  SizedBox(
                    width: nameW,
                    child: Padding(
                      padding: const EdgeInsets.only(left: 12),
                      child: Text(
                        student.name,
                        style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w500, color: AppColors.gray800),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ),
                  ...allDays.map((d) {
                    final isOff = offDaySet.contains(d);
                    if (isOff) {
                      return SizedBox(
                        width: cellW,
                        child: Center(
                          child: Container(
                            width: 14, height: 14,
                            decoration: BoxDecoration(color: AppColors.gray100, borderRadius: BorderRadius.circular(2)),
                            child: const Center(child: Text('L', style: TextStyle(fontSize: 7, fontWeight: FontWeight.w700, color: AppColors.gray400))),
                          ),
                        ),
                      );
                    }
                    final status = student.statuses[d];
                    if (status == null || status == 'future') {
                      return const SizedBox(
                        width: cellW,
                        child: Center(
                          child: Text('·', style: TextStyle(fontSize: 14, color: AppColors.gray200)),
                        ),
                      );
                    }
                    final color = _statusColors[status] ?? AppColors.gray300;
                    final code  = _statusCodes[status] ?? '?';
                    return SizedBox(
                      width: cellW,
                      child: Center(
                        child: Container(
                          width: 14, height: 14,
                          decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(2)),
                          child: Center(child: Text(code, style: const TextStyle(fontSize: 7, fontWeight: FontWeight.w800, color: Colors.white))),
                        ),
                      ),
                    );
                  }),
                  ...[
                    student.counts['hadir']      ?? 0,
                    student.counts['terlambat']  ?? 0,
                    student.counts['izin']       ?? 0,
                    student.counts['sakit']      ?? 0,
                    student.counts['alpa']       ?? 0,
                    student.counts['dispensasi'] ?? 0,
                  ].map((n) => SizedBox(
                    width: countW,
                    child: Text('$n', textAlign: TextAlign.center,
                        style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.gray700)),
                  )),
                ]),
              );
            }),
          ],
        ),
      ),
    );
  }
}
