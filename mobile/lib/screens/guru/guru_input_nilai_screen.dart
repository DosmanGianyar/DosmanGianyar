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
  List<Map<String, dynamic>> _classes        = [];
  List<SubjectItem>          _subjects       = [];
  List<String>               _academicYears  = [];
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
  bool _loadingInit     = true;
  bool _loadingStudents = false;
  bool _savingBatch     = false;

  // batch input controllers
  final Map<int, TextEditingController> _scoreControllers = {};
  final Map<int, TextEditingController> _notesControllers = {};
  final Map<int, FocusNode>             _focusNodes       = {};
  final Map<int, String>                _initialScores    = {};

  @override
  void initState() {
    super.initState();
    _loadInit();
  }

  @override
  void dispose() {
    for (final c in _scoreControllers.values) {
      c.dispose();
    }
    for (final c in _notesControllers.values) {
      c.dispose();
    }
    for (final f in _focusNodes.values) {
      f.dispose();
    }
    super.dispose();
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
          _classes              = (classResp['classes'] as List<dynamic>? ?? []).cast<Map<String, dynamic>>();
          _academicYears        = (classResp['academic_years'] as List<dynamic>? ?? []).cast<String>();
          _currentSemester      = classResp['current_semester'] as int? ?? 1;
          _currentAcademicYear  = classResp['current_year'] as String?;
          _selectedSemester     = _currentSemester;
          _selectedAcademicYear = _currentAcademicYear;
          _subjects             = subjects;
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
      if (mounted) {
        setState(() {
          _students = result.students;
          _loadingStudents = false;
        });
        _initControllers();
      }
    } catch (e) {
      if (mounted) {
        setState(() => _loadingStudents = false);
        _snack(e.toString(), AppColors.red500);
      }
    }
  }

  String _formatScore(double? score) {
    if (score == null) return '';
    if (score % 1 == 0) return score.toInt().toString();
    String s = score.toStringAsFixed(2);
    if (s.endsWith('.00')) return s.substring(0, s.length - 3);
    if (s.endsWith('0')) return s.substring(0, s.length - 1);
    return s;
  }

  void _initControllers() {
    for (final c in _scoreControllers.values) {
      c.dispose();
    }
    for (final c in _notesControllers.values) {
      c.dispose();
    }
    for (final f in _focusNodes.values) {
      f.dispose();
    }
    _scoreControllers.clear();
    _notesControllers.clear();
    _focusNodes.clear();
    _initialScores.clear();

    if (_selectedSubjectId == null) return;

    for (final s in _students) {
      final cell = s.gradeFor(_selectedSubjectId!, _selectedType);
      final formatted = _formatScore(cell?.score);
      _initialScores[s.studentId] = formatted;

      final scoreCtrl = TextEditingController(text: formatted);
      scoreCtrl.addListener(() {
        if (mounted) setState(() {});
      });

      _scoreControllers[s.studentId] = scoreCtrl;
      _notesControllers[s.studentId] = TextEditingController(text: cell?.notes ?? '');
      _focusNodes[s.studentId]       = FocusNode();
    }
  }

  int get _filledCount {
    int count = 0;
    for (final c in _scoreControllers.values) {
      if (c.text.trim().isNotEmpty) count++;
    }
    return count;
  }

  int get _modifiedCount {
    int count = 0;
    for (final s in _students) {
      final ctrl = _scoreControllers[s.studentId];
      if (ctrl == null) continue;
      final current = ctrl.text.trim().replaceAll(',', '.');
      final initial = (_initialScores[s.studentId] ?? '').replaceAll(',', '.');
      if (current != initial) {
        count++;
      }
    }
    return count;
  }

  Future<void> _saveAllGrades() async {
    if (_selectedSubjectId == null || _selectedAcademicYear == null) return;

    FocusScope.of(context).unfocus();

    final List<Map<String, dynamic>> toSave = [];

    for (final s in _students) {
      final ctrl = _scoreControllers[s.studentId];
      if (ctrl == null) continue;
      final raw = ctrl.text.trim().replaceAll(',', '.');
      if (raw.isEmpty) continue;

      final score = double.tryParse(raw);
      if (score == null || score < 0 || score > 100) {
        _snack('Nilai untuk "${s.studentName}" harus antara 0 - 100', AppColors.red500);
        _focusNodes[s.studentId]?.requestFocus();
        return;
      }

      final rounded = double.parse(score.toStringAsFixed(2));
      final notes   = _notesControllers[s.studentId]?.text.trim();

      toSave.add({
        'student_id': s.studentId,
        'score':      rounded,
        if (notes != null && notes.isNotEmpty) 'notes': notes,
      });
    }

    if (toSave.isEmpty) {
      _snack('Belum ada nilai yang diinput untuk disimpan', AppColors.orange500);
      return;
    }

    setState(() => _savingBatch = true);
    try {
      final msg = await GuruService.storeGradesBatch(
        subjectId:    _selectedSubjectId!,
        type:         _selectedType,
        semester:     _selectedSemester,
        academicYear: _selectedAcademicYear!,
        grades:       toSave,
      );
      _snack(msg, AppColors.emerald600);
      await _loadStudents();
    } catch (e) {
      _snack(e.toString(), AppColors.red500);
    } finally {
      if (mounted) setState(() => _savingBatch = false);
    }
  }

  void _showNoteDialog(StudentGradeRow student) {
    final noteCtrl = _notesControllers[student.studentId] ?? TextEditingController();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        titlePadding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
        contentPadding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
        title: Row(
          children: [
            const Icon(Icons.edit_note_rounded, size: 20, color: AppColors.blue600),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                student.studentName,
                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              '${_selectedSubjectName ?? ''} · $_selectedType · Semester $_selectedSemester',
              style: const TextStyle(fontSize: 11, color: AppColors.gray500),
            ),
            const SizedBox(height: 10),
            TextField(
              controller: noteCtrl,
              maxLines: 3,
              style: const TextStyle(fontSize: 12),
              decoration: InputDecoration(
                hintText: 'Tulis catatan penilaian (opsional)...',
                hintStyle: const TextStyle(fontSize: 11.5, color: AppColors.gray400),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                contentPadding: const EdgeInsets.all(10),
              ),
              autofocus: true,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              setState(() {});
            },
            child: const Text('Simpan Catatan', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
          ),
        ],
      ),
    );
  }

  void _snack(String msg, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg, style: const TextStyle(fontSize: 12)),
      backgroundColor: color,
      behavior: SnackBarBehavior.floating,
      duration: const Duration(seconds: 3),
    ));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Input Nilai Siswa', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
        actions: [
          if (_selectedClassId != null && _selectedAcademicYear != null)
            IconButton(
              icon: const Icon(Icons.download_rounded, size: 20),
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
                  classes:              _classes,
                  subjects:             _subjects,
                  academicYears:        _academicYears,
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
                    _initControllers();
                  },
                  onTypeChanged: (t) {
                    setState(() => _selectedType = t);
                    _initControllers();
                  },
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
      bottomNavigationBar: (_students.isNotEmpty && !_loadingStudents && !_loadingInit)
          ? _buildBottomBar()
          : null,
    );
  }

  Widget _buildBody() {
    if (_selectedClassId == null) {
      return const Center(
        child: Text('Pilih kelas untuk melihat siswa', style: TextStyle(fontSize: 12, color: AppColors.gray400)),
      );
    }
    if (_loadingStudents) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_students.isEmpty) {
      return const Center(
        child: Text('Tidak ada data siswa', style: TextStyle(fontSize: 12, color: AppColors.gray400)),
      );
    }

    return Column(
      children: [
        // Sub-header kolom tabel ringkas
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
          color: AppColors.slate100,
          child: const Row(
            children: [
              SizedBox(
                width: 22,
                child: Text('NO', style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.w700, color: AppColors.gray500)),
              ),
              Expanded(
                child: Text('NAMA SISWA', style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.w700, color: AppColors.gray500)),
              ),
              SizedBox(width: 26),
              SizedBox(
                width: 66,
                child: Text('NILAI', textAlign: TextAlign.center, style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.w700, color: AppColors.gray500)),
              ),
            ],
          ),
        ),
        // Daftar siswa inline input ringkas
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.fromLTRB(10, 2, 10, 80),
            itemCount: _students.length,
            itemBuilder: (_, i) {
              final s    = _students[i];
              final ctrl = _scoreControllers[s.studentId];
              final focus = _focusNodes[s.studentId];
              if (ctrl == null || focus == null) return const SizedBox.shrink();

              final currentVal = ctrl.text.trim().replaceAll(',', '.');
              final initVal    = (_initialScores[s.studentId] ?? '').replaceAll(',', '.');
              final isModified = currentVal != initVal;
              final hasNote    = (_notesControllers[s.studentId]?.text.trim() ?? '').isNotEmpty;
              final isLast     = i == _students.length - 1;

              return _CompactStudentNilaiRow(
                no:         i + 1,
                name:       s.studentName,
                nis:        s.nis,
                controller: ctrl,
                focusNode:  focus,
                isModified: isModified,
                hasNote:    hasNote,
                isLast:     isLast,
                onNoteTap:  () => _showNoteDialog(s),
                onNext: isLast
                    ? null
                    : () {
                        final nextId = _students[i + 1].studentId;
                        _focusNodes[nextId]?.requestFocus();
                      },
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildBottomBar() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            offset: const Offset(0, -2),
            blurRadius: 8,
          ),
        ],
        border: const Border(top: BorderSide(color: AppColors.gray200, width: 0.8)),
      ),
      child: SafeArea(
        top: false,
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.check_circle_outline_rounded, size: 13, color: AppColors.emerald600),
                      const SizedBox(width: 4),
                      Text(
                        'Terisi: $_filledCount / ${_students.length} siswa',
                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.gray700),
                      ),
                    ],
                  ),
                  if (_modifiedCount > 0)
                    Padding(
                      padding: const EdgeInsets.only(top: 1),
                      child: Text(
                        '$_modifiedCount nilai diubah (belum disimpan)',
                        style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.w600, color: AppColors.blue600),
                      ),
                    ),
                ],
              ),
            ),
            FilledButton.icon(
              onPressed: (_savingBatch || _students.isEmpty) ? null : _saveAllGrades,
              style: FilledButton.styleFrom(
                backgroundColor: AppColors.blue600,
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                elevation: 0,
              ),
              icon: _savingBatch
                  ? const SizedBox(
                      width: 14,
                      height: 14,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                    )
                  : const Icon(Icons.save_rounded, size: 15),
              label: Text(
                _savingBatch ? 'Menyimpan...' : 'Simpan Semua',
                style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700),
              ),
            ),
          ],
        ),
      ),
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
      padding: const EdgeInsets.fromLTRB(10, 6, 10, 6),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Row 1: Kelas + Tahun Ajaran
          Row(
            children: [
              Expanded(
                flex: 5,
                child: _CompactDropdown<int?>(
                  label: 'Kelas',
                  value: selectedClassId,
                  items: [
                    const DropdownMenuItem<int?>(
                      value: null,
                      child: Text('Pilih kelas', style: TextStyle(fontSize: 11.5)),
                    ),
                    ...classes.map((c) => DropdownMenuItem<int?>(
                      value: c['id'] as int,
                      child: Text(c['name'] as String, style: const TextStyle(fontSize: 11.5)),
                    )),
                  ],
                  onChanged: onClassChanged,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                flex: 4,
                child: _CompactDropdown<String?>(
                  label: 'T.A.',
                  value: selectedAcademicYear,
                  items: academicYears.map((y) => DropdownMenuItem<String?>(
                    value: y,
                    child: Text(y, style: const TextStyle(fontSize: 11.5)),
                  )).toList(),
                  onChanged: (v) { if (v != null) onYearChanged(v); },
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          // Row 2: Mapel
          _CompactDropdown<int?>(
            label: 'Mata Pelajaran',
            value: selectedSubjectId,
            items: subjects.map((s) => DropdownMenuItem<int?>(
              value: s.id,
              child: Text(s.name, style: const TextStyle(fontSize: 11.5)),
            )).toList(),
            onChanged: (id) {
              if (id == null) return;
              final sub = subjects.firstWhere((s) => s.id == id);
              onSubjectChanged(sub.id, sub.name);
            },
          ),
          const SizedBox(height: 5),
          // Row 3: Semester chips + Tipe chips
          Row(
            children: [
              const Text('Semester:', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.gray500)),
              const SizedBox(width: 5),
              _SmallChip(label: '1', selected: selectedSemester == 1, onTap: () => onSemesterChanged(1)),
              const SizedBox(width: 4),
              _SmallChip(label: '2', selected: selectedSemester == 2, onTap: () => onSemesterChanged(2)),
              const SizedBox(width: 14),
              const Text('Tipe:', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.gray500)),
              const SizedBox(width: 5),
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
        Text(label, style: const TextStyle(fontSize: 9, fontWeight: FontWeight.w600, color: AppColors.gray500)),
        const SizedBox(height: 2),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 7),
          height: 33,
          decoration: BoxDecoration(
            color: AppColors.gray50,
            borderRadius: BorderRadius.circular(6),
            border: Border.all(color: AppColors.gray200),
          ),
          child: DropdownButtonHideUnderline(
            child: DropdownButton<T>(
              value: value,
              isExpanded: true,
              style: const TextStyle(fontSize: 11.5, color: AppColors.gray800),
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
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2.5),
        decoration: BoxDecoration(
          color: selected ? AppColors.blue600 : AppColors.gray100,
          borderRadius: BorderRadius.circular(5),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 10,
            fontWeight: FontWeight.w600,
            color: selected ? Colors.white : AppColors.gray600,
          ),
        ),
      ),
    );
  }
}

// ─── Compact Student Nilai Row ────────────────────────────────────────────────

class _CompactStudentNilaiRow extends StatelessWidget {
  final int no;
  final String name;
  final String? nis;
  final TextEditingController controller;
  final FocusNode focusNode;
  final bool isModified;
  final bool hasNote;
  final bool isLast;
  final VoidCallback onNoteTap;
  final VoidCallback? onNext;

  const _CompactStudentNilaiRow({
    required this.no,
    required this.name,
    this.nis,
    required this.controller,
    required this.focusNode,
    required this.isModified,
    required this.hasNote,
    required this.isLast,
    required this.onNoteTap,
    this.onNext,
  });

  Color _getScoreColor(double? score) {
    if (score == null) return AppColors.gray400;
    if (score >= 80) return AppColors.emerald600;
    if (score >= 60) return AppColors.orange500;
    return AppColors.red500;
  }

  @override
  Widget build(BuildContext context) {
    final rawText = controller.text.trim().replaceAll(',', '.');
    final parsedScore = double.tryParse(rawText);

    Color textColor;
    Color borderColor;
    Color bgColor;

    if (isModified) {
      textColor = AppColors.blue700;
      borderColor = AppColors.blue600;
      bgColor = AppColors.blue600.withValues(alpha: 0.08);
    } else if (parsedScore != null) {
      final sc = _getScoreColor(parsedScore);
      textColor = sc;
      borderColor = sc.withValues(alpha: 0.35);
      bgColor = sc.withValues(alpha: 0.05);
    } else {
      textColor = AppColors.gray800;
      borderColor = AppColors.gray200;
      bgColor = Colors.white;
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 3),
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(6),
        border: Border.all(
          color: isModified ? AppColors.blue600.withValues(alpha: 0.5) : AppColors.gray100,
          width: isModified ? 1 : 0.8,
        ),
      ),
      child: Row(
        children: [
          // Nomor Urut (font ringkas)
          SizedBox(
            width: 20,
            child: Text(
              '$no.',
              style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.gray400),
            ),
          ),
          const SizedBox(width: 4),
          // Nama & NIS Siswa (font ringkas)
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  name,
                  style: const TextStyle(
                    fontSize: 11.5,
                    fontWeight: FontWeight.w600,
                    color: AppColors.gray800,
                    height: 1.15,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                if (nis != null)
                  Text(
                    nis!,
                    style: const TextStyle(fontSize: 9.5, color: AppColors.gray400, height: 1.1),
                  ),
              ],
            ),
          ),
          // Tombol Catatan Opsional
          GestureDetector(
            onTap: onNoteTap,
            behavior: HitTestBehavior.opaque,
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 4),
              child: Icon(
                hasNote ? Icons.sticky_note_2_rounded : Icons.notes_rounded,
                size: 15,
                color: hasNote ? AppColors.blue600 : AppColors.gray300,
              ),
            ),
          ),
          const SizedBox(width: 4),
          // Input Nilai (mendukung 2 angka di belakang koma)
          SizedBox(
            width: 66,
            height: 32,
            child: TextField(
              controller: controller,
              focusNode: focusNode,
              textAlign: TextAlign.center,
              keyboardType: const TextInputType.numberWithOptions(decimal: true),
              textInputAction: isLast ? TextInputAction.done : TextInputAction.next,
              onSubmitted: (_) {
                if (onNext != null) onNext!();
              },
              inputFormatters: [
                // Mengizinkan angka 0-100 dengan hingga 2 angka desimal menggunakan titik atau koma
                FilteringTextInputFormatter.allow(RegExp(r'^\d{0,3}([.,]\d{0,2})?')),
              ],
              style: TextStyle(
                fontSize: 12.5,
                fontWeight: FontWeight.w700,
                color: textColor,
              ),
              decoration: InputDecoration(
                hintText: '—',
                hintStyle: const TextStyle(fontSize: 12, color: AppColors.gray300, fontWeight: FontWeight.normal),
                filled: true,
                fillColor: bgColor,
                isDense: true,
                contentPadding: const EdgeInsets.symmetric(vertical: 6, horizontal: 4),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(6),
                  borderSide: BorderSide(color: borderColor, width: 1),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(6),
                  borderSide: const BorderSide(color: AppColors.blue600, width: 1.5),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
