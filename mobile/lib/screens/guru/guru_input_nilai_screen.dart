import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../models/guru_models.dart';
import '../../services/guru_service.dart';
import '../../theme/app_colors.dart';

class GuruInputNilaiScreen extends StatefulWidget {
  const GuruInputNilaiScreen({super.key});

  @override
  State<GuruInputNilaiScreen> createState() => _GuruInputNilaiScreenState();
}

class _GuruInputNilaiScreenState extends State<GuruInputNilaiScreen> {
  // filter state
  List<Map<String, dynamic>> _classes      = [];
  List<SubjectItem>          _subjects     = [];
  List<String>               _academicYears = [];
  int?     _selectedClassId;
  int?     _selectedSubjectId;
  String?  _selectedSubjectName;
  String   _selectedType          = 'UH';
  int      _selectedSemester      = 1;
  String?  _selectedAcademicYear;
  int      _currentSemester       = 1;
  String?  _currentAcademicYear;

  // data state
  List<StudentGradeRow> _students = [];
  bool   _loadingInit    = true;
  bool   _loadingStudents = false;

  @override
  void initState() {
    super.initState();
    _loadInit();
  }

  Future<void> _loadInit() async {
    try {
      final results = await Future.wait([
        GuruService.getGradeClasses(),
        GuruService.getGradeSubjects(),
      ]);
      final classResp = results[0] as Map<String, dynamic>;
      final subjects  = results[1] as List<SubjectItem>;

      if (mounted) {
        setState(() {
          _classes           = (classResp['classes'] as List<dynamic>? ?? []).cast<Map<String, dynamic>>();
          _academicYears     = (classResp['academic_years'] as List<dynamic>? ?? []).cast<String>();
          _currentSemester   = classResp['current_semester'] as int? ?? 1;
          _currentAcademicYear = classResp['current_year'] as String?;
          _selectedSemester  = _currentSemester;
          _selectedAcademicYear = _currentAcademicYear;
          _subjects          = subjects;
          if (subjects.isNotEmpty) {
            _selectedSubjectId   = subjects.first.id;
            _selectedSubjectName = subjects.first.name;
          }
          _loadingInit = false;
        });
      }
    } catch (e) {
      if (mounted) setState(() => _loadingInit = false);
    }
  }

  Future<void> _loadStudents() async {
    if (_selectedClassId == null || _selectedSubjectId == null || _selectedAcademicYear == null) return;
    setState(() => _loadingStudents = true);
    try {
      final result = await GuruService.getGrades(
        classId:      _selectedClassId!,
        semester:     _selectedSemester,
        academicYear: _selectedAcademicYear!,
      );
      if (mounted) setState(() { _students = result.students; _loadingStudents = false; });
    } catch (e) {
      if (mounted) {
        setState(() => _loadingStudents = false);
        _snack(e.toString(), AppColors.red500);
      }
    }
  }

  void _openGradeDialog(StudentGradeRow student) {
    if (_selectedSubjectId == null) return;
    final cell = student.gradeFor(_selectedSubjectId!, _selectedType);
    _showGradeInputDialog(
      studentId:   student.studentId,
      studentName: student.studentName,
      subjectId:   _selectedSubjectId!,
      type:        _selectedType,
      currentScore: cell?.score,
    );
  }

  Future<void> _showGradeInputDialog({
    required int studentId,
    required String studentName,
    required int subjectId,
    required String type,
    double? currentScore,
  }) async {
    final controller = TextEditingController(
      text: currentScore != null ? currentScore.toStringAsFixed(currentScore % 1 == 0 ? 0 : 1) : '',
    );
    final notesCtrl = TextEditingController();
    bool saving = false;

    await showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setD) => AlertDialog(
          title: Text(studentName, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                '${_selectedSubjectName ?? ''} · $type · Sem $_selectedSemester',
                style: const TextStyle(fontSize: 12, color: AppColors.gray500),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: controller,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                inputFormatters: [FilteringTextInputFormatter.allow(RegExp(r'^\d{0,3}(\.\d{0,1})?'))],
                decoration: InputDecoration(
                  labelText: 'Nilai (0–100)',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                ),
                autofocus: true,
              ),
              const SizedBox(height: 12),
              TextField(
                controller: notesCtrl,
                decoration: InputDecoration(
                  labelText: 'Catatan (opsional)',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: saving ? null : () => Navigator.pop(ctx),
              child: const Text('Batal'),
            ),
            FilledButton(
              onPressed: saving ? null : () async {
                final scoreStr = controller.text.trim();
                if (scoreStr.isEmpty) { Navigator.pop(ctx); return; }
                final score = double.tryParse(scoreStr);
                if (score == null || score < 0 || score > 100) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Nilai harus 0–100'), backgroundColor: AppColors.red500),
                  );
                  return;
                }
                setD(() => saving = true);
                try {
                  await GuruService.storeGrade(
                    studentId:    studentId,
                    subjectId:    subjectId,
                    type:         type,
                    score:        score,
                    semester:     _selectedSemester,
                    academicYear: _selectedAcademicYear!,
                    notes: notesCtrl.text.trim().isEmpty ? null : notesCtrl.text.trim(),
                  );
                  if (ctx.mounted) Navigator.pop(ctx);
                  _snack('Nilai disimpan', AppColors.emerald600);
                  _loadStudents();
                } catch (e) {
                  if (ctx.mounted) setD(() => saving = false);
                  _snack(e.toString(), AppColors.red500);
                }
              },
              child: saving
                  ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : const Text('Simpan'),
            ),
          ],
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        ),
      ),
    );
    controller.dispose();
    notesCtrl.dispose();
  }

  void _snack(String msg, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: color,
      behavior: SnackBarBehavior.floating,
    ));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Input Nilai Siswa'),
        actions: [
          if (_selectedClassId != null && _selectedAcademicYear != null)
            IconButton(
              icon: const Icon(Icons.download_rounded),
              tooltip: 'Export CSV',
              onPressed: _exportCsv,
            ),
        ],
      ),
      body: _loadingInit
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                _FilterBar(
                  classes:       _classes,
                  subjects:      _subjects,
                  academicYears: _academicYears,
                  selectedClassId:      _selectedClassId,
                  selectedSubjectId:    _selectedSubjectId,
                  selectedType:         _selectedType,
                  selectedSemester:     _selectedSemester,
                  selectedAcademicYear: _selectedAcademicYear,
                  onClassChanged: (id) {
                    setState(() { _selectedClassId = id; _students = []; });
                    _loadStudents();
                  },
                  onSubjectChanged: (id, name) {
                    setState(() { _selectedSubjectId = id; _selectedSubjectName = name; });
                  },
                  onTypeChanged: (t) => setState(() => _selectedType = t),
                  onSemesterChanged: (s) {
                    setState(() { _selectedSemester = s; _students = []; });
                    if (_selectedClassId != null) _loadStudents();
                  },
                  onYearChanged: (y) {
                    setState(() { _selectedAcademicYear = y; _students = []; });
                    if (_selectedClassId != null) _loadStudents();
                  },
                ),
                Expanded(child: _buildBody()),
              ],
            ),
    );
  }

  Widget _buildBody() {
    if (_selectedClassId == null) {
      return const Center(
        child: Text('Pilih kelas untuk melihat siswa', style: TextStyle(color: AppColors.gray400)),
      );
    }
    if (_loadingStudents) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_students.isEmpty) {
      return const Center(
        child: Text('Tidak ada siswa', style: TextStyle(color: AppColors.gray400)),
      );
    }
    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      itemCount: _students.length,
      itemBuilder: (_, i) {
        final s    = _students[i];
        final cell = _selectedSubjectId != null
            ? s.gradeFor(_selectedSubjectId!, _selectedType)
            : null;
        final score = cell?.score;
        return _StudentNilaiRow(
          no:        i + 1,
          name:      s.studentName,
          nis:       s.nis,
          score:     score,
          type:      _selectedType,
          onTap:     () => _openGradeDialog(s),
        );
      },

    );
  }

  Future<void> _exportCsv() async {
    if (_selectedClassId == null || _selectedAcademicYear == null) return;
    try {
      final result = await GuruService.exportGrades(
        classId: _selectedClassId!,
        semester: _selectedSemester,
        academicYear: _selectedAcademicYear!,
      );
      final rows    = result['rows'] as List<dynamic>;
      final filename = result['filename'] as String;
      final csv = rows.map((row) =>
        (row as List<dynamic>).map((cell) => '"$cell"').join(',')
      ).join('\n');

      _showCsvDialog(filename, csv);
    } catch (e) {
      _snack(e.toString(), AppColors.red500);
    }
  }

  void _showCsvDialog(String filename, String csv) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: Text(filename, style: const TextStyle(fontSize: 13)),
        content: SingleChildScrollView(
          child: SelectableText(
            csv,
            style: const TextStyle(fontSize: 10, fontFamily: 'monospace'),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () {
              Clipboard.setData(ClipboardData(text: csv));
              Navigator.pop(context);
              _snack('CSV disalin ke clipboard', AppColors.blue600);
            },
            child: const Text('Salin'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Tutup'),
          ),
        ],
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      ),
    );
  }
}

// ─── Filter Bar ───────────────────────────────────────────────────────────────

class _FilterBar extends StatelessWidget {
  final List<Map<String, dynamic>> classes;
  final List<SubjectItem>          subjects;
  final List<String>               academicYears;
  final int?     selectedClassId;
  final int?     selectedSubjectId;
  final String   selectedType;
  final int      selectedSemester;
  final String?  selectedAcademicYear;
  final void Function(int?)    onClassChanged;
  final void Function(int, String) onSubjectChanged;
  final void Function(String)  onTypeChanged;
  final void Function(int)     onSemesterChanged;
  final void Function(String)  onYearChanged;

  const _FilterBar({
    required this.classes,
    required this.subjects,
    required this.academicYears,
    required this.selectedClassId,
    required this.selectedSubjectId,
    required this.selectedType,
    required this.selectedSemester,
    required this.selectedAcademicYear,
    required this.onClassChanged,
    required this.onSubjectChanged,
    required this.onTypeChanged,
    required this.onSemesterChanged,
    required this.onYearChanged,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.fromLTRB(12, 8, 12, 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Row 1: Kelas + Tahun Ajaran
          Row(
            children: [
              Expanded(child: _CompactDropdown<int?>(
                label: 'Kelas',
                value: selectedClassId,
                items: [
                  const DropdownMenuItem<int?>(value: null, child: Text('Pilih kelas')),
                  ...classes.map((c) => DropdownMenuItem<int?>(
                    value: c['id'] as int,
                    child: Text(c['name'] as String),
                  )),
                ],
                onChanged: onClassChanged,
              )),
              const SizedBox(width: 8),
              Expanded(child: _CompactDropdown<String?>(
                label: 'T.A.',
                value: selectedAcademicYear,
                items: academicYears.map((y) => DropdownMenuItem<String?>(
                  value: y, child: Text(y),
                )).toList(),
                onChanged: (v) { if (v != null) onYearChanged(v); },
              )),
            ],
          ),
          const SizedBox(height: 6),
          // Row 2: Mapel
          _CompactDropdown<int?>(
            label: 'Mata Pelajaran',
            value: selectedSubjectId,
            items: subjects.map((s) => DropdownMenuItem<int?>(
              value: s.id, child: Text(s.name),
            )).toList(),
            onChanged: (id) {
              if (id == null) return;
              final sub = subjects.firstWhere((s) => s.id == id);
              onSubjectChanged(sub.id, sub.name);
            },
          ),
          const SizedBox(height: 8),
          // Row 3: Semester chips + Tipe chips
          Row(
            children: [
              const Text('Semester:', style: TextStyle(fontSize: 11, color: AppColors.gray500)),
              const SizedBox(width: 6),
              _SmallChip(label: '1', selected: selectedSemester == 1, onTap: () => onSemesterChanged(1)),
              const SizedBox(width: 4),
              _SmallChip(label: '2', selected: selectedSemester == 2, onTap: () => onSemesterChanged(2)),
              const SizedBox(width: 16),
              const Text('Tipe:', style: TextStyle(fontSize: 11, color: AppColors.gray500)),
              const SizedBox(width: 6),
              for (final t in ['UH', 'UTS', 'UAS']) ...[
                _SmallChip(label: t, selected: selectedType == t, onTap: () => onTypeChanged(t)),
                const SizedBox(width: 4),
              ],
            ],
          ),
        ],
      ),
    );
  }
}

class _CompactDropdown<T> extends StatelessWidget {
  final String label;
  final T value;
  final List<DropdownMenuItem<T>> items;
  final void Function(T?) onChanged;

  const _CompactDropdown({
    required this.label,
    required this.value,
    required this.items,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontSize: 10, color: AppColors.gray500)),
        const SizedBox(height: 2),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 8),
          height: 36,
          decoration: BoxDecoration(
            color: AppColors.gray50,
            borderRadius: BorderRadius.circular(8),
            border: Border.all(color: AppColors.gray200),
          ),
          child: DropdownButtonHideUnderline(
            child: DropdownButton<T>(
              value: value,
              isExpanded: true,
              style: const TextStyle(fontSize: 13, color: AppColors.gray800),
              items: items,
              onChanged: onChanged,
            ),
          ),
        ),
      ],
    );
  }
}

class _SmallChip extends StatelessWidget {
  final String label;
  final bool selected;
  final VoidCallback onTap;

  const _SmallChip({required this.label, required this.selected, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
        decoration: BoxDecoration(
          color: selected ? AppColors.blue600 : AppColors.gray100,
          borderRadius: BorderRadius.circular(6),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w600,
            color: selected ? Colors.white : AppColors.gray600,
          ),
        ),
      ),
    );
  }
}

// ─── Student Nilai Row ────────────────────────────────────────────────────────

class _StudentNilaiRow extends StatelessWidget {
  final int no;
  final String name;
  final String? nis;
  final double? score;
  final String type;
  final VoidCallback onTap;

  const _StudentNilaiRow({
    required this.no,
    required this.name,
    this.nis,
    this.score,
    required this.type,
    required this.onTap,
  });

  Color get _scoreColor {
    if (score == null) return AppColors.gray300;
    if (score! >= 80)  return AppColors.emerald600;
    if (score! >= 60)  return AppColors.orange500;
    return AppColors.red500;
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.only(bottom: 6),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: AppColors.gray100),
        ),
        child: Row(
          children: [
            SizedBox(
              width: 24,
              child: Text(
                '$no.',
                style: const TextStyle(fontSize: 12, color: AppColors.gray400),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                  if (nis != null)
                    Text(nis!, style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
                ],
              ),
            ),
            Container(
              width: 52,
              height: 36,
              decoration: BoxDecoration(
                color: _scoreColor.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: _scoreColor.withValues(alpha: 0.3)),
              ),
              alignment: Alignment.center,
              child: Text(
                score != null
                    ? (score! % 1 == 0 ? score!.toInt().toString() : score!.toStringAsFixed(1))
                    : '—',
                style: TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                  color: _scoreColor,
                ),
              ),
            ),
            const SizedBox(width: 8),
            const Icon(Icons.edit_outlined, size: 16, color: AppColors.gray400),
          ],
        ),
      ),
    );
  }
}
