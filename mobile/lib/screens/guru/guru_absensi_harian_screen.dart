import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/guru_models.dart';
import '../../services/guru_service.dart';
import '../../theme/app_colors.dart';
import 'widgets/guru_widgets.dart';

class GuruAbsensiHarianScreen extends StatefulWidget {
  final List<GuruClass> classes;
  final int? initialClassId;

  const GuruAbsensiHarianScreen({
    super.key,
    required this.classes,
    this.initialClassId,
  });

  @override
  State<GuruAbsensiHarianScreen> createState() => _GuruAbsensiHarianScreenState();
}

class _GuruAbsensiHarianScreenState extends State<GuruAbsensiHarianScreen> {
  late int _classId;
  DateTime _date = DateTime.now();
  DailyAttendanceSummary? _data;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _classId = widget.initialClassId ?? (widget.classes.isNotEmpty ? widget.classes.first.id : 0);
    _load();
  }

  Future<void> _load() async {
    if (widget.classes.isEmpty) return;
    setState(() { _loading = true; _error = null; });
    try {
      final data = await GuruService.getAttendanceDaily(
        classId: _classId,
        date:    DateFormat('yyyy-MM-dd').format(_date),
      );
      if (mounted) setState(() { _data = data; _loading = false; });
    } catch (e) {
      if (mounted) setState(() { _error = e.toString(); _loading = false; });
    }
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _date,
      firstDate:   DateTime(2020),
      lastDate:    DateTime.now(),
    );
    if (picked != null && picked != _date) {
      _date = picked;
      _load();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(title: const Text('Absensi Harian')),
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
            child: GestureDetector(
              onTap: _pickDate,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                decoration: BoxDecoration(
                  border: Border.all(color: AppColors.gray200),
                  borderRadius: BorderRadius.circular(10),
                  color: AppColors.gray50,
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.calendar_today_rounded, size: 16, color: AppColors.blue600),
                    const SizedBox(width: 8),
                    Text(
                      DateFormat('EEEE, d MMMM y', 'id_ID').format(_date),
                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray800),
                    ),
                    const SizedBox(width: 6),
                    const Icon(Icons.arrow_drop_down, color: AppColors.gray400),
                  ],
                ),
              ),
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
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _buildSummaryCards(_data!),
          const SizedBox(height: 12),
          _buildStudentList(_data!),
        ],
      ),
    );
  }

  Widget _buildSummaryCards(DailyAttendanceSummary data) {
    final items = [
      ('H', 'Hadir',      data.summary['hadir'] ?? 0,      AppColors.green500),
      ('T', 'Terlambat',  data.summary['terlambat'] ?? 0,  AppColors.yellow500),
      ('I', 'Izin',       data.summary['izin'] ?? 0,        AppColors.blue500),
      ('S', 'Sakit',      data.summary['sakit'] ?? 0,       AppColors.purple500),
      ('A', 'Alpa',       data.summary['alpa'] ?? 0,        AppColors.red500),
      ('D', 'Dispen',     data.summary['dispensasi'] ?? 0,  AppColors.teal500),
    ];
    return GridView.count(
      crossAxisCount: 3,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisSpacing: 8,
      mainAxisSpacing: 8,
      childAspectRatio: 1.6,
      children: items.map((item) => _SummaryCard(
        code:  item.$1,
        label: item.$2,
        count: item.$3,
        color: item.$4,
      )).toList(),
    );
  }

  Widget _buildStudentList(DailyAttendanceSummary data) {
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
            child: Row(children: [
              const Icon(Icons.people_rounded, size: 16, color: AppColors.gray600),
              const SizedBox(width: 6),
              Text(
                '${data.students.length} Siswa',
                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray800),
              ),
            ]),
          ),
          const Divider(height: 1, color: AppColors.gray100),
          ...data.students.asMap().entries.map((entry) {
            final i = entry.key;
            final s = entry.value;
            return Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: BoxDecoration(
                border: i < data.students.length - 1
                    ? const Border(bottom: BorderSide(color: AppColors.gray100, width: 0.5))
                    : null,
              ),
              child: Row(children: [
                SizedBox(
                  width: 24,
                  child: Text(
                    '${i + 1}',
                    style: const TextStyle(fontSize: 11, color: AppColors.gray400),
                    textAlign: TextAlign.center,
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(s.name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray800)),
                      if (s.nis != null)
                        Text(s.nis!, style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
                    ],
                  ),
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    StatusBadge(s.status),
                    if (s.checkInTime != null)
                      Padding(
                        padding: const EdgeInsets.only(top: 2),
                        child: Text(
                          s.checkInTime!.substring(0, 5),
                          style: const TextStyle(fontSize: 10, color: AppColors.gray400),
                        ),
                      ),
                  ],
                ),
              ]),
            );
          }),
        ],
      ),
    );
  }
}

class _SummaryCard extends StatelessWidget {
  final String code;
  final String label;
  final int count;
  final Color color;

  const _SummaryCard({
    required this.code,
    required this.label,
    required this.count,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppRadius.xl),
        border: Border.all(color: AppColors.gray100),
        boxShadow: AppShadow.sm,
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(
            '$count',
            style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: color),
          ),
          Text(
            label,
            style: const TextStyle(fontSize: 10, color: AppColors.gray500),
          ),
        ],
      ),
    );
  }
}
