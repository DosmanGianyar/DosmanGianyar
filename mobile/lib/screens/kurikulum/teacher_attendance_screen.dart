import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/teacher_attendance_record.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';

class TeacherAttendanceScreen extends StatefulWidget {
  const TeacherAttendanceScreen({super.key});

  @override
  State<TeacherAttendanceScreen> createState() => _TeacherAttendanceScreenState();
}

class _TeacherAttendanceScreenState extends State<TeacherAttendanceScreen> {
  List<TeacherAttendanceRecord> _records = [];
  String?  _className;
  DateTime _date      = DateTime.now();
  bool     _isLoading = true;
  String?  _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  String get _dateParam => DateFormat('yyyy-MM-dd').format(_date);

  Future<void> _load() async {
    setState(() { _isLoading = true; _error = null; });
    try {
      final body = await ApiClient.get('/teacher-attendance', params: {'date': _dateParam});
      setState(() {
        _className = body['class_name'] as String?;
        _records   = (body['records'] as List)
            .map((e) => TeacherAttendanceRecord.fromJson(e as Map<String, dynamic>))
            .toList();
      });
    } catch (e) {
      setState(() => _error = ApiClient.extractError(e));
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _date,
      firstDate: DateTime(2024),
      lastDate: DateTime.now(),
      locale: const Locale('id', 'ID'),
    );
    if (picked != null && picked != _date) {
      setState(() => _date = picked);
      _load();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Absensi Guru Mengajar',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 16)),
        backgroundColor: const Color(0xFF0F2460),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: Column(children: [
        // ─── Date Picker Header ───────────────────────────────────────────
        Container(
          color: const Color(0xFF0F2460),
          padding: const EdgeInsets.fromLTRB(16, 0, 16, 14),
          child: Row(children: [
            if (_className != null) ...[
              const Icon(Icons.class_outlined, color: Color(0xFFBFDBFE), size: 14),
              const SizedBox(width: 6),
              Text(_className!, style: const TextStyle(color: Color(0xFFBFDBFE), fontSize: 12)),
              const Spacer(),
            ] else
              const Spacer(),
            GestureDetector(
              onTap: _pickDate,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.white.withValues(alpha: 0.3)),
                ),
                child: Row(mainAxisSize: MainAxisSize.min, children: [
                  const Icon(Icons.calendar_today_rounded, color: Colors.white, size: 13),
                  const SizedBox(width: 6),
                  Text(
                    DateFormat('EEEE, d MMM y', 'id_ID').format(_date),
                    style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w500),
                  ),
                  const SizedBox(width: 4),
                  const Icon(Icons.keyboard_arrow_down_rounded, color: Colors.white, size: 16),
                ]),
              ),
            ),
          ]),
        ),

        // ─── Body ────────────────────────────────────────────────────────
        Expanded(
          child: _isLoading
              ? const Center(child: CircularProgressIndicator())
              : _error != null
                  ? _ErrorView(message: _error!, onRetry: _load)
                  : RefreshIndicator(
                      onRefresh: _load,
                      child: _records.isEmpty
                          ? const Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
                              Icon(Icons.school_outlined, size: 56, color: AppColors.gray300),
                              SizedBox(height: 12),
                              Text('Tidak ada data absensi guru\npada tanggal ini',
                                textAlign: TextAlign.center,
                                style: TextStyle(fontSize: 13, color: AppColors.gray400)),
                            ]))
                          : CustomScrollView(
                              slivers: [
                                SliverToBoxAdapter(child: _SummaryBar(records: _records)),
                                SliverPadding(
                                  padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
                                  sliver: SliverList.separated(
                                    itemCount: _records.length,
                                    separatorBuilder: (_, __) => const SizedBox(height: 8),
                                    itemBuilder: (_, i) => _RecordCard(record: _records[i]),
                                  ),
                                ),
                              ],
                            ),
                    ),
        ),
      ]),
    );
  }
}

// ─── Summary Bar ─────────────────────────────────────────────────────────────

class _SummaryBar extends StatelessWidget {
  final List<TeacherAttendanceRecord> records;
  const _SummaryBar({required this.records});

  @override
  Widget build(BuildContext context) {
    final total   = records.length;
    final hadir   = records.where((r) => r.status == 'hadir').length;
    final izin    = records.where((r) => r.status == 'izin').length;
    final sakit   = records.where((r) => r.status == 'sakit').length;
    final absen   = records.where((r) => r.status == 'tidak_hadir').length;

    return Container(
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white, borderRadius: AppRadius.card,
        boxShadow: AppShadow.sm,
      ),
      child: Row(children: [
        _StatItem(label: 'Total', value: '$total', color: AppColors.gray600),
        _vDivider(),
        _StatItem(label: 'Hadir', value: '$hadir', color: AppColors.green600),
        _vDivider(),
        _StatItem(label: 'Izin', value: '$izin', color: AppColors.blue600),
        _vDivider(),
        _StatItem(label: 'Sakit', value: '$sakit', color: AppColors.amber500),
        _vDivider(),
        _StatItem(label: 'Absen', value: '$absen', color: AppColors.red500),
      ]),
    );
  }

  Widget _vDivider() => Container(
    width: 1, height: 30, margin: const EdgeInsets.symmetric(horizontal: 6),
    color: AppColors.gray100,
  );
}

class _StatItem extends StatelessWidget {
  final String label, value; final Color color;
  const _StatItem({required this.label, required this.value, required this.color});
  @override
  Widget build(BuildContext context) => Expanded(
    child: Column(children: [
      Text(value, style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: color)),
      Text(label,  style: const TextStyle(fontSize: 9, color: AppColors.gray400)),
    ]),
  );
}

// ─── Record Card ─────────────────────────────────────────────────────────────

class _RecordCard extends StatelessWidget {
  final TeacherAttendanceRecord record;
  const _RecordCard({required this.record});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white, borderRadius: AppRadius.card,
        border: Border.all(color: AppColors.gray100),
        boxShadow: AppShadow.sm,
      ),
      padding: const EdgeInsets.all(14),
      child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        // Period badge
        Container(
          width: 36, height: 36,
          decoration: BoxDecoration(
            color: const Color(0xFF0F2460).withValues(alpha: 0.08),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Center(
            child: Text('${record.period}',
              style: const TextStyle(
                fontSize: 14, fontWeight: FontWeight.bold,
                color: Color(0xFF0F2460))),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Expanded(
              child: Text(record.subjectName ?? '—',
                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray800)),
            ),
            const SizedBox(width: 8),
            _StatusChip(record: record),
          ]),
          if (record.subjectCode != null) ...[
            const SizedBox(height: 2),
            Text(record.subjectCode!,
              style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
          ],
          const SizedBox(height: 6),
          Row(children: [
            const Icon(Icons.person_outline_rounded, size: 12, color: AppColors.gray400),
            const SizedBox(width: 4),
            Expanded(
              child: Text(record.teacherName ?? '—',
                style: const TextStyle(fontSize: 12, color: AppColors.gray500)),
            ),
            if (record.startTime != null && record.endTime != null) ...[
              const Icon(Icons.access_time_rounded, size: 12, color: AppColors.gray400),
              const SizedBox(width: 4),
              Text('${record.startTime} – ${record.endTime}',
                style: const TextStyle(fontSize: 12, color: AppColors.gray500)),
            ],
          ]),
          if (record.note != null && record.note!.isNotEmpty) ...[
            const SizedBox(height: 6),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: AppColors.gray50, borderRadius: BorderRadius.circular(6),
              ),
              child: Text(record.note!,
                style: const TextStyle(fontSize: 11, color: AppColors.gray500, fontStyle: FontStyle.italic)),
            ),
          ],
        ])),
      ]),
    );
  }
}

class _StatusChip extends StatelessWidget {
  final TeacherAttendanceRecord record;
  const _StatusChip({required this.record});
  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
    decoration: BoxDecoration(
      color: record.statusBg,
      borderRadius: BorderRadius.circular(6),
    ),
    child: Row(mainAxisSize: MainAxisSize.min, children: [
      Icon(record.statusIcon, size: 11, color: record.statusColor),
      const SizedBox(width: 4),
      Text(record.statusLabel,
        style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: record.statusColor)),
    ]),
  );
}

// ─── Error View ───────────────────────────────────────────────────────────────

class _ErrorView extends StatelessWidget {
  final String message; final VoidCallback onRetry;
  const _ErrorView({required this.message, required this.onRetry});
  @override
  Widget build(BuildContext context) => Center(
    child: Column(mainAxisSize: MainAxisSize.min, children: [
      const Icon(Icons.error_outline_rounded, size: 48, color: AppColors.red500),
      const SizedBox(height: 8),
      Text(message, style: const TextStyle(fontSize: 13, color: AppColors.gray500), textAlign: TextAlign.center),
      const SizedBox(height: 12),
      TextButton(onPressed: onRetry, child: const Text('Coba Lagi')),
    ]),
  );
}
