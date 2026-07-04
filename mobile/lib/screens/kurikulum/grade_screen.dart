import 'package:flutter/material.dart';
import '../../models/student_grade.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';

class GradeScreen extends StatefulWidget {
  const GradeScreen({super.key});

  @override
  State<GradeScreen> createState() => _GradeScreenState();
}

class _GradeScreenState extends State<GradeScreen> {
  List<GradeSummarySubject> _subjects = [];
  double?  _classAvg;
  String?  _academicYear;
  int      _semester = 1;
  bool     _isLoading = true;
  String?  _error;

  static String get _currentYear {
    final now = DateTime.now();
    final y = now.month >= 7 ? now.year : now.year - 1;
    return '$y/${y + 1}';
  }

  static int get _currentSemester => DateTime.now().month >= 7 ? 1 : 2;

  @override
  void initState() {
    super.initState();
    _academicYear = _currentYear;
    _semester     = _currentSemester;
    _load();
  }

  Future<void> _load() async {
    setState(() { _isLoading = true; _error = null; });
    try {
      final body = await ApiClient.get('/grades/summary', params: {
        'academic_year': _academicYear,
        'semester':      _semester,
      });
      setState(() {
        _classAvg  = (body['class_average'] as num?)?.toDouble();
        _subjects  = (body['subjects'] as List)
            .map((e) => GradeSummarySubject.fromJson(e as Map<String, dynamic>))
            .toList();
      });
    } catch (e) {
      setState(() => _error = ApiClient.extractError(e));
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _changeSemester(int sem) {
    if (_semester == sem) return;
    setState(() => _semester = sem);
    _load();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Nilai Rapor',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 16)),
        backgroundColor: const Color(0xFF0F2460),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: Column(children: [
        // ─── Semester Picker ─────────────────────────────────────────────
        Container(
          color: const Color(0xFF0F2460),
          padding: const EdgeInsets.fromLTRB(16, 0, 16, 14),
          child: Column(children: [
            Text(_academicYear ?? '', style: const TextStyle(color: Color(0xFFBFDBFE), fontSize: 11)),
            const SizedBox(height: 8),
            Row(children: [
              Expanded(child: _SemChip(
                label: 'Semester 1',
                selected: _semester == 1,
                onTap: () => _changeSemester(1),
              )),
              const SizedBox(width: 8),
              Expanded(child: _SemChip(
                label: 'Semester 2',
                selected: _semester == 2,
                onTap: () => _changeSemester(2),
              )),
            ]),
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
                      child: _subjects.isEmpty
                          ? const Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
                              Icon(Icons.assignment_outlined, size: 56, color: AppColors.gray300),
                              SizedBox(height: 12),
                              Text('Belum ada nilai untuk semester ini',
                                style: TextStyle(fontSize: 13, color: AppColors.gray400)),
                            ]))
                          : CustomScrollView(
                              slivers: [
                                if (_classAvg != null)
                                  SliverToBoxAdapter(child: _AverageCard(avg: _classAvg!)),
                                const SliverToBoxAdapter(child: _TableHeader()),
                                SliverPadding(
                                  padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
                                  sliver: SliverList.builder(
                                    itemCount: _subjects.length,
                                    itemBuilder: (_, i) => Padding(
                                      padding: const EdgeInsets.only(bottom: 8),
                                      child: _SubjectCard(subject: _subjects[i]),
                                    ),
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

// ─── Semester Chip ────────────────────────────────────────────────────────────

class _SemChip extends StatelessWidget {
  final String label; final bool selected; final VoidCallback onTap;
  const _SemChip({required this.label, required this.selected, required this.onTap});

  @override
  Widget build(BuildContext context) => GestureDetector(
    onTap: onTap,
    child: AnimatedContainer(
      duration: const Duration(milliseconds: 150),
      padding: const EdgeInsets.symmetric(vertical: 8),
      decoration: BoxDecoration(
        color: selected ? Colors.white : Colors.white.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
          color: selected ? Colors.white : Colors.white.withValues(alpha: 0.25)),
      ),
      child: Text(label, textAlign: TextAlign.center,
        style: TextStyle(
          fontSize: 12, fontWeight: FontWeight.w600,
          color: selected ? const Color(0xFF0F2460) : Colors.white)),
    ),
  );
}

// ─── Average Card ─────────────────────────────────────────────────────────────

class _AverageCard extends StatelessWidget {
  final double avg;
  const _AverageCard({required this.avg});

  @override
  Widget build(BuildContext context) {
    final color = avg >= 80
        ? AppColors.green600
        : avg >= 65
            ? AppColors.amber500
            : AppColors.red500;
    return Container(
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
      decoration: BoxDecoration(
        color: Colors.white, borderRadius: AppRadius.card,
        boxShadow: AppShadow.sm,
      ),
      child: Row(children: [
        const Icon(Icons.bar_chart_rounded, color: AppColors.blue600, size: 20),
        const SizedBox(width: 10),
        const Text('Rata-rata Keseluruhan',
          style: TextStyle(fontSize: 13, color: AppColors.gray600)),
        const Spacer(),
        Text(avg.toStringAsFixed(1),
          style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: color)),
      ]),
    );
  }
}

// ─── Table Header ─────────────────────────────────────────────────────────────

class _TableHeader extends StatelessWidget {
  const _TableHeader();

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 0, 16, 6),
      child: Row(children: const [
        Expanded(flex: 3, child: Text('Mata Pelajaran',
          style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.gray500))),
        SizedBox(width: 4),
        SizedBox(width: 46, child: Text('UH', textAlign: TextAlign.center,
          style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.gray500))),
        SizedBox(width: 4),
        SizedBox(width: 46, child: Text('UTS', textAlign: TextAlign.center,
          style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.blue600))),
        SizedBox(width: 4),
        SizedBox(width: 46, child: Text('UAS', textAlign: TextAlign.center,
          style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.violet600))),
        SizedBox(width: 4),
        SizedBox(width: 50, child: Text('Akhir', textAlign: TextAlign.center,
          style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.gray700))),
      ]),
    );
  }
}

// ─── Subject Card ─────────────────────────────────────────────────────────────

class _SubjectCard extends StatelessWidget {
  final GradeSummarySubject subject;
  const _SubjectCard({required this.subject});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white, borderRadius: AppRadius.card,
        border: Border.all(color: AppColors.gray100), boxShadow: AppShadow.sm,
      ),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      child: Row(children: [
        Expanded(flex: 3, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(subject.subjectName,
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.gray800),
            maxLines: 2, overflow: TextOverflow.ellipsis),
          Text(subject.subjectCode,
            style: const TextStyle(fontSize: 10, color: AppColors.gray400)),
        ])),
        const SizedBox(width: 4),
        // UH avg
        SizedBox(width: 46, child: _ScoreBox(
          score: subject.uhAvg,
          fmtScore: subject.fmtScore(subject.uhAvg),
          color: subject.scoreColor(subject.uhAvg),
          bg: subject.scoreBg(subject.uhAvg),
        )),
        const SizedBox(width: 4),
        // UTS
        SizedBox(width: 46, child: _ScoreBox(
          score: subject.uts,
          fmtScore: subject.fmtScore(subject.uts),
          color: subject.scoreColor(subject.uts),
          bg: subject.scoreBg(subject.uts),
          borderColor: AppColors.blue50,
        )),
        const SizedBox(width: 4),
        // UAS
        SizedBox(width: 46, child: _ScoreBox(
          score: subject.uas,
          fmtScore: subject.fmtScore(subject.uas),
          color: subject.scoreColor(subject.uas),
          bg: subject.scoreBg(subject.uas),
          borderColor: AppColors.violet50,
        )),
        const SizedBox(width: 4),
        // Final
        SizedBox(width: 50, child: _ScoreBox(
          score: subject.finalScore,
          fmtScore: subject.fmtScore(subject.finalScore),
          color: subject.scoreColor(subject.finalScore),
          bg: subject.scoreBg(subject.finalScore),
          bold: true,
        )),
      ]),
    );
  }
}

class _ScoreBox extends StatelessWidget {
  final double? score;
  final String  fmtScore;
  final Color   color;
  final Color   bg;
  final Color?  borderColor;
  final bool    bold;
  const _ScoreBox({
    required this.score, required this.fmtScore,
    required this.color, required this.bg,
    this.borderColor, this.bold = false,
  });

  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.symmetric(vertical: 6),
    decoration: BoxDecoration(
      color: score != null ? bg : AppColors.gray50,
      borderRadius: BorderRadius.circular(6),
      border: borderColor != null ? Border.all(color: borderColor!) : null,
    ),
    child: Text(fmtScore, textAlign: TextAlign.center,
      style: TextStyle(
        fontSize: bold ? 13 : 12,
        fontWeight: bold ? FontWeight.bold : FontWeight.w500,
        color: score != null ? color : AppColors.gray400,
      )),
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
