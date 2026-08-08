import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/guru_models.dart';
import '../../services/guru_service.dart';
import '../../theme/app_colors.dart';
import 'widgets/guru_widgets.dart';
import 'guru_tp_screen.dart';

class GuruTeachingSessionScreen extends StatefulWidget {
  final int? initialClassId;
  final int? initialSubjectId;
  final int? initialPeriod;
  final List<int>? initialPeriods;

  const GuruTeachingSessionScreen({
    super.key,
    this.initialClassId,
    this.initialSubjectId,
    this.initialPeriod,
    this.initialPeriods,
  });

  @override
  State<GuruTeachingSessionScreen> createState() => _GuruTeachingSessionScreenState();
}

class _GuruTeachingSessionScreenState extends State<GuruTeachingSessionScreen>
    with SingleTickerProviderStateMixin {
  late final TabController _tabCtrl;

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Jurnal & Absensi Mengajar'),
        bottom: TabBar(
          controller: _tabCtrl,
          tabs: const [
            Tab(text: 'Buat Jurnal & Absensi'),
            Tab(text: 'Riwayat'),
          ],
          labelColor: AppColors.blue600,
          indicatorColor: AppColors.blue600,
          unselectedLabelColor: AppColors.gray400,
        ),
      ),
      body: TabBarView(
        controller: _tabCtrl,
        children: [
          _CreateSessionTab(
            initialClassId: widget.initialClassId,
            initialSubjectId: widget.initialSubjectId,
            initialPeriod: widget.initialPeriod,
            initialPeriods: widget.initialPeriods,
            onSuccess: () => _tabCtrl.animateTo(1),
          ),
          const _HistoryTab(),
        ],
      ),
    );
  }
}

// ─── Tab: Buat Jurnal & Absensi (Terpadu) ───────────────────────────────────

class _CreateSessionTab extends StatefulWidget {
  final int? initialClassId;
  final int? initialSubjectId;
  final int? initialPeriod;
  final List<int>? initialPeriods;
  final VoidCallback? onSuccess;

  const _CreateSessionTab({
    this.initialClassId,
    this.initialSubjectId,
    this.initialPeriod,
    this.initialPeriods,
    this.onSuccess,
  });

  @override
  State<_CreateSessionTab> createState() => _CreateSessionTabState();
}

class _CreateSessionTabState extends State<_CreateSessionTab> {
  Map<String, dynamic>? _classesData;
  bool _loadingClasses = true;

  int?      _selectedClassId;
  int?      _selectedSubjectId;
  final Set<int> _selectedPeriods = {};

  List<Map<String, dynamic>> _occupiedPeriods = [];
  bool _loadingOccupied = false;

  // Form Jurnal Mengajar
  TujuanPembelajaran? _selectedTp;
  final _materiCtrl = TextEditingController();
  final _aktivCtrl  = TextEditingController();
  final _notesCtrl  = TextEditingController();

  List<SessionStudentRow> _students = [];
  bool _loadingStudents = false;

  final _dateCtrl = TextEditingController();
  DateTime _selectedDate = DateTime.now();

  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    _dateCtrl.text = DateFormat('yyyy-MM-dd').format(_selectedDate);
    if (widget.initialClassId != null) {
      _selectedClassId = widget.initialClassId;
    }
    if (widget.initialSubjectId != null) {
      _selectedSubjectId = widget.initialSubjectId;
    }
    if (widget.initialPeriods != null && widget.initialPeriods!.isNotEmpty) {
      _selectedPeriods.addAll(widget.initialPeriods!);
    } else if (widget.initialPeriod != null) {
      _selectedPeriods.add(widget.initialPeriod!);
    }
    _loadClasses();
    if (_selectedClassId != null) {
      _loadStudents(_selectedClassId!);
      _loadOccupiedPeriods();
    }
  }

  @override
  void dispose() {
    _dateCtrl.dispose();
    _materiCtrl.dispose();
    _aktivCtrl.dispose();
    _notesCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadClasses() async {
    try {
      final data = await GuruService.getTeachingClasses();
      if (mounted) {
        setState(() {
          _classesData = data;
          _loadingClasses = false;
          if (_selectedClassId != null && _selectedSubjectId == null) {
            final teachingClasses = (_classesData?['teaching_classes'] as List<dynamic>? ?? [])
                .cast<Map<String, dynamic>>();
            final matching = teachingClasses.firstWhere(
              (c) => c['id'] == _selectedClassId,
              orElse: () => {},
            );
            if (matching.isNotEmpty && matching['subject_id'] != null) {
              _selectedSubjectId = matching['subject_id'] as int?;
            }
          }
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loadingClasses = false);
    }
  }

  Future<void> _loadOccupiedPeriods() async {
    if (_selectedClassId == null) return;
    setState(() => _loadingOccupied = true);
    try {
      final occupied = await GuruService.getOccupiedPeriods(
        classId: _selectedClassId!,
        date: DateFormat('yyyy-MM-dd').format(_selectedDate),
      );
      if (mounted) {
        setState(() {
          _occupiedPeriods = occupied;
          _loadingOccupied = false;
          // Hapus pilihan jam yang sudah diisi oleh guru lain
          _selectedPeriods.removeWhere((p) {
            final occ = _occupiedPeriods.firstWhere((o) => o['period'] == p, orElse: () => {});
            return occ.isNotEmpty && occ['is_self'] == false;
          });
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loadingOccupied = false);
    }
  }

  Future<void> _loadStudents(int classId) async {
    setState(() { _loadingStudents = true; _students = []; });
    try {
      final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate);
      final list = await GuruService.getSessionClassStudents(classId, date: dateStr);
      if (mounted) {
        setState(() {
          _students = list.map((s) {
            final sug = s.suggestedStatus;
            final initialStatus = (sug != null && sug != 'hadir')
                ? (sug == 'alpa' ? 'tidak_hadir' : sug)
                : 'hadir';
            return SessionStudentRow(
              studentId:          s.id,
              name:               s.name,
              nis:                s.nis,
              status:             initialStatus,
              morningStatus:      s.morningStatus,
              morningStatusLabel: s.morningStatusLabel,
            );
          }).toList();
          _loadingStudents = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loadingStudents = false);
    }
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context:     context,
      initialDate: _selectedDate,
      firstDate:   DateTime.now().subtract(const Duration(days: 90)),
      lastDate:    DateTime.now(),
    );
    if (picked != null) {
      setState(() {
        _selectedDate = picked;
        _dateCtrl.text = DateFormat('yyyy-MM-dd').format(picked);
      });
      if (_selectedClassId != null) {
        _loadOccupiedPeriods();
        _loadStudents(_selectedClassId!);
      }
    }
  }

  Future<void> _pickTp() async {
    final result = await showModalBottomSheet<TujuanPembelajaran>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => TpPickerSheet(
        currentTpId: _selectedTp?.id,
        subjectId: _selectedSubjectId,
      ),
    );
    if (result != null && mounted) setState(() => _selectedTp = result);
  }

  Future<void> _submit() async {
    if (_selectedClassId == null) {
      _showSnack('Pilih kelas terlebih dahulu', AppColors.orange500); return;
    }
    if (_selectedPeriods.isEmpty) {
      _showSnack('Pilih minimal satu jam ke-berapa', AppColors.orange500); return;
    }
    if (_materiCtrl.text.trim().isEmpty) {
      _showSnack('Isi materi pembelajaran', AppColors.orange500); return;
    }
    if (_aktivCtrl.text.trim().isEmpty) {
      _showSnack('Isi aktivitas pembelajaran', AppColors.orange500); return;
    }
    if (_students.isEmpty) {
      _showSnack('Tidak ada siswa pada kelas ini', AppColors.orange500); return;
    }

    setState(() => _submitting = true);
    try {
      final sortedPeriods = _selectedPeriods.toList()..sort();
      final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate);

      // 1. Simpan Absensi Sesi Mengajar
      await GuruService.createTeachingSession(
        classId:     _selectedClassId!,
        subjectId:   _selectedSubjectId,
        date:        dateStr,
        periods:     sortedPeriods,
        attendances: _students.map((s) => {
          'student_id': s.studentId,
          'status':     s.status,
        }).toList(),
      );

      // 2. Simpan Jurnal Mengajar (dengan daftar siswa tidak hadir)
      final absentList = _students
          .where((s) => s.status != 'hadir')
          .map((s) => <String, dynamic>{
            'student_id': s.studentId,
            'status':     s.status,
          })
          .toList();

      await GuruService.createJournal(
        classId:            _selectedClassId!,
        subjectId:          _selectedSubjectId,
        date:               dateStr,
        period:             sortedPeriods.first,
        periodEnd:          sortedPeriods.length > 1 ? sortedPeriods.last : null,
        tpId:               _selectedTp?.id,
        learningObjectives: _selectedTp != null ? '[${_selectedTp!.code ?? ''}] ${_selectedTp!.description}' : null,
        material:           _materiCtrl.text.trim(),
        activity:           _aktivCtrl.text.trim(),
        notes:              _notesCtrl.text.trim().isNotEmpty ? _notesCtrl.text.trim() : null,
        absentStudents:     absentList.isNotEmpty ? absentList : null,
      );

      if (mounted) {
        _showSnack('Jurnal & Absensi Mengajar berhasil disimpan!', AppColors.emerald600);
        setState(() {
          _selectedPeriods.clear();
          _materiCtrl.clear();
          _aktivCtrl.clear();
          _notesCtrl.clear();
          _selectedTp = null;
        });
        _loadOccupiedPeriods();
        widget.onSuccess?.call();
      }
    } catch (e) {
      if (mounted) _showSnack(e.toString(), AppColors.red500);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  void _showSnack(String msg, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: color,
      behavior: SnackBarBehavior.floating,
    ));
  }

  @override
  Widget build(BuildContext context) {
    if (_loadingClasses) return const Center(child: CircularProgressIndicator());

    final teachingClasses = (_classesData?['teaching_classes'] as List<dynamic>? ?? [])
        .cast<Map<String, dynamic>>();
    final homeroom = _classesData?['homeroom_class'] as Map<String, dynamic>?;

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ── Tanggal ────────────────────────────────────────────────
          _label('Tanggal'),
          const SizedBox(height: 8),
          GestureDetector(
            onTap: _pickDate,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              decoration: BoxDecoration(
                color: AppColors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: AppColors.gray200),
              ),
              child: Row(
                children: [
                  const Icon(Icons.calendar_today_rounded, size: 16, color: AppColors.gray400),
                  const SizedBox(width: 10),
                  Text(
                    DateFormat('EEEE, d MMMM y', 'id_ID').format(_selectedDate),
                    style: const TextStyle(fontSize: 13, color: AppColors.gray700),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),

          // ── Pilih Kelas (Dropdown) ──────────────────────────────────
          _label('Kelas'),
          const SizedBox(height: 8),
          _buildClassDropdown(homeroom, teachingClasses),
          const SizedBox(height: 16),

          // ── Pilih Jam (Multi-select) ────────────────────────────────
          Row(
            children: [
              _label('Jam Ke-'),
              if (_selectedPeriods.isNotEmpty) ...[
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(
                    color: AppColors.blue600,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    '${_selectedPeriods.length} jam dipilih',
                    style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.white),
                  ),
                ),
              ],
            ],
          ),
          const SizedBox(height: 8),
          _buildPeriodGrid(),
          const SizedBox(height: 20),

          // ── Jurnal Mengajar (Pembelajaran) ─────────────────────────
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: AppColors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.gray200),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Row(
                  children: [
                    Icon(Icons.edit_note_rounded, color: AppColors.blue600, size: 20),
                    SizedBox(width: 8),
                    Text(
                      'Detail Jurnal Mengajar',
                      style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.gray800),
                    ),
                  ],
                ),
                const SizedBox(height: 12),

                // Tujuan Pembelajaran (TP)
                _label('Tujuan Pembelajaran (TP / Capaian)'),
                const SizedBox(height: 6),
                GestureDetector(
                  onTap: _pickTp,
                  child: Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    decoration: BoxDecoration(
                      color: _selectedTp != null ? AppColors.blue50 : AppColors.gray50,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: _selectedTp != null ? AppColors.blue200 : AppColors.gray200),
                    ),
                    child: _selectedTp != null
                        ? Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              if (_selectedTp!.code != null && _selectedTp!.code!.isNotEmpty)
                                Container(
                                  margin: const EdgeInsets.only(bottom: 4),
                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: AppColors.blue200,
                                    borderRadius: BorderRadius.circular(4),
                                  ),
                                  child: Text(_selectedTp!.code!, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.blue700)),
                                ),
                              Text(_selectedTp!.description, style: const TextStyle(fontSize: 12, color: AppColors.blue700)),
                            ],
                          )
                        : const Row(
                            children: [
                              Icon(Icons.checklist_rounded, size: 16, color: AppColors.gray400),
                              SizedBox(width: 8),
                              Text('Ketuk untuk memilih TP (opsional)', style: TextStyle(fontSize: 12, color: AppColors.gray400)),
                            ],
                          ),
                  ),
                ),
                const SizedBox(height: 12),

                // Materi Pembelajaran
                _label('Materi Pembelajaran *'),
                const SizedBox(height: 6),
                _buildTextField(_materiCtrl, 'Topik / bab / pembahasan yang diajarkan...'),
                const SizedBox(height: 12),

                // Aktivitas Pembelajaran
                _label('Aktivitas Pembelajaran *'),
                const SizedBox(height: 6),
                _buildTextField(_aktivCtrl, 'Metode, latihan soal, ceramah, praktikum...', maxLines: 2),
                const SizedBox(height: 12),

                // Catatan Tambahan
                _label('Catatan Tambahan (opsional)'),
                const SizedBox(height: 6),
                _buildTextField(_notesCtrl, 'Observasi kelas, kendala, dll...'),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // ── Daftar Siswa ───────────────────────────────────────────
          if (_loadingStudents)
            const Center(child: Padding(padding: EdgeInsets.all(24), child: CircularProgressIndicator()))
          else if (_students.isNotEmpty) ...[
            Row(
              children: [
                _label('Daftar Siswa (${_students.length})'),
                const Spacer(),
                _QuickMarkButton(
                  label: 'Semua Hadir',
                  onTap: () => setState(() {
                    for (final s in _students) { s.status = 'hadir'; }
                  }),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Container(
              decoration: BoxDecoration(
                color: AppColors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.gray100),
              ),
              child: ListView.separated(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: _students.length,
                separatorBuilder: (_, __) => const Divider(height: 1, color: AppColors.gray100),
                itemBuilder: (_, i) => _StudentAttRow(
                  student: _students[i],
                  onStatusChanged: (s) => setState(() => _students[i].status = s),
                ),
              ),
            ),
            const SizedBox(height: 20),

            // Summary chips
            _buildSummaryBar(),
            const SizedBox(height: 20),

            SizedBox(
              width: double.infinity,
              child: FilledButton.icon(
                onPressed: _submitting ? null : _submit,
                icon: _submitting
                    ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Icon(Icons.save_rounded, size: 18),
                label: Text(_submitting ? 'Menyimpan...' : 'Simpan Absensi'),
                style: FilledButton.styleFrom(
                  backgroundColor: AppColors.blue600,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
            ),
            const SizedBox(height: 32),
          ],
        ],
      ),
    );
  }

  Widget _buildClassDropdown(
    Map<String, dynamic>? homeroom,
    List<Map<String, dynamic>> teachingClasses,
  ) {
    final allClasses = <Map<String, dynamic>>[];
    final seenIds = <int>{};

    if (homeroom != null && homeroom['id'] != null) {
      final hId = homeroom['id'] as int;
      seenIds.add(hId);
      allClasses.add({
        'id': hId,
        'name': '${homeroom['name']} (Wali Kelas)',
        'subject_id': null,
      });
    }

    for (final c in teachingClasses) {
      final id = c['id'] as int?;
      if (id != null && !seenIds.contains(id)) {
        seenIds.add(id);
        final name = c['name'] as String? ?? '—';
        final subj = c['subject_name'] as String? ?? '';
        allClasses.add({
          'id': id,
          'name': subj.isNotEmpty ? '$name — $subj' : name,
          'subject_id': c['subject_id'],
          'period': c['period'],
        });
      }
    }

    if (allClasses.isEmpty) {
      return const Text('Tidak ada data kelas.', style: TextStyle(color: AppColors.gray400, fontSize: 13));
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: AppColors.gray200),
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<int>(
          isExpanded: true,
          value: _selectedClassId,
          hint: const Text('Pilih Kelas...', style: TextStyle(fontSize: 13, color: AppColors.gray400)),
          icon: const Icon(Icons.keyboard_arrow_down_rounded, color: AppColors.gray500),
          items: allClasses.map((c) {
            return DropdownMenuItem<int>(
              value: c['id'] as int,
              child: Text(
                c['name'] as String,
                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray800),
              ),
            );
          }).toList(),
          onChanged: (id) {
            if (id != null) {
              final selectedObj = allClasses.firstWhere((element) => element['id'] == id, orElse: () => {});
              setState(() {
                _selectedClassId   = id;
                _selectedSubjectId = selectedObj['subject_id'] as int?;
                _selectedPeriods.clear();
              });
              _loadStudents(id);
              _loadOccupiedPeriods();
            }
          },
        ),
      ),
    );
  }

  Widget _buildPeriodGrid() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Wrap(
          spacing: 8, runSpacing: 8,
          children: List.generate(10, (i) {
            final p = i + 1;
            final occ = _occupiedPeriods.firstWhere(
              (o) => o['period'] == p,
              orElse: () => {},
            );
            final isOccupiedByOther = occ.isNotEmpty && occ['is_self'] == false;
            final isOccupiedBySelf  = occ.isNotEmpty && occ['is_self'] == true;
            final isSelected        = _selectedPeriods.contains(p);

            Color bgColor = AppColors.white;
            Color borderColor = AppColors.gray200;
            Color textColor = AppColors.gray700;

            if (isOccupiedByOther) {
              bgColor = AppColors.gray100;
              borderColor = AppColors.gray200;
              textColor = AppColors.gray400;
            } else if (isSelected) {
              bgColor = AppColors.blue600;
              borderColor = AppColors.blue600;
              textColor = Colors.white;
            } else if (isOccupiedBySelf) {
              bgColor = AppColors.emerald500.withOpacity(0.15);
              borderColor = AppColors.emerald500;
              textColor = AppColors.emerald600;
            }

            return GestureDetector(
              onTap: () {
                if (isOccupiedByOther) {
                  final teacherName = occ['teacher_name'] ?? 'Guru lain';
                  _showSnack('Jam ke-$p sudah diisi oleh $teacherName', AppColors.orange500);
                  return;
                }
                setState(() {
                  if (_selectedPeriods.contains(p)) {
                    _selectedPeriods.remove(p);
                  } else {
                    _selectedPeriods.add(p);
                  }
                });
              },
              child: Container(
                width: 44, height: 44,
                decoration: BoxDecoration(
                  color: bgColor,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: borderColor, width: isSelected || isOccupiedBySelf ? 1.5 : 1),
                ),
                child: Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        '$p',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          color: textColor,
                          decoration: isOccupiedByOther ? TextDecoration.lineThrough : null,
                        ),
                      ),
                      if (isOccupiedBySelf)
                        const Text(
                          'Anda',
                          style: TextStyle(fontSize: 8, fontWeight: FontWeight.bold, color: AppColors.emerald600),
                        ),
                    ],
                  ),
                ),
              ),
            );
          }),
        ),
        if (_occupiedPeriods.any((o) => o['is_self'] == false)) ...[
          const SizedBox(height: 8),
          const Text(
            'Keterangan jam terisi guru lain:',
            style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.gray600),
          ),
          ..._occupiedPeriods.where((o) => o['is_self'] == false).map((o) => Padding(
            padding: const EdgeInsets.only(left: 4, top: 2),
            child: Text(
              '• Jam ${o['period']}: Diisi oleh ${o['teacher_name']} (${o['subject_name'] ?? ''})',
              style: const TextStyle(fontSize: 11, color: AppColors.orange600),
            ),
          )),
        ],
      ],
    );
  }

  Widget _buildSummaryBar() {
    final hadir  = _students.where((s) => s.status == 'hadir').length;
    final alpha  = _students.where((s) => s.status == 'tidak_hadir').length;
    final izin   = _students.where((s) => s.status == 'izin').length;
    final sakit  = _students.where((s) => s.status == 'sakit').length;

    return Row(
      children: [
        _SumChip(label: 'Hadir',  count: hadir, color: AppColors.green600),
        const SizedBox(width: 6),
        _SumChip(label: 'Alpha',  count: alpha, color: AppColors.red500),
        const SizedBox(width: 6),
        _SumChip(label: 'Izin',   count: izin,  color: AppColors.sky600),
        const SizedBox(width: 6),
        _SumChip(label: 'Sakit',  count: sakit, color: AppColors.purple500),
      ],
    );
  }

  Widget _label(String t) => Text(t,
    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.gray700));

  Widget _buildTextField(TextEditingController ctrl, String hint, {int maxLines = 1}) {
    return TextField(
      controller: ctrl,
      maxLines: maxLines,
      style: const TextStyle(fontSize: 13, color: AppColors.gray800),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: const TextStyle(color: AppColors.gray400, fontSize: 13),
        filled: true,
        fillColor: AppColors.gray50,
        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: AppColors.gray200),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: AppColors.gray200),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: AppColors.blue600, width: 1.5),
        ),
      ),
    );
  }
}

// ─── Tab: Riwayat ─────────────────────────────────────────────────────────────

class _HistoryTab extends StatefulWidget {
  const _HistoryTab();

  @override
  State<_HistoryTab> createState() => _HistoryTabState();
}

class _HistoryTabState extends State<_HistoryTab> {
  final List<TeachingSession> _sessions = [];
  int _page = 1;
  bool _loading = false;
  bool _hasMore = true;
  String? _error;
  final _scroll = ScrollController();

  int _filterMonth = DateTime.now().month;
  int _filterYear  = DateTime.now().year;

  @override
  void initState() {
    super.initState();
    _load(reset: true);
    _scroll.addListener(() {
      if (_scroll.position.pixels >= _scroll.position.maxScrollExtent - 200
          && !_loading && _hasMore) {
        _load();
      }
    });
  }

  @override
  void dispose() {
    _scroll.dispose();
    super.dispose();
  }

  Future<void> _load({bool reset = false}) async {
    if (_loading) return;
    if (reset) { _page = 1; _hasMore = true; }
    setState(() { _loading = true; _error = null; });
    try {
      final result = await GuruService.getTeachingSessions(
        month: _filterMonth,
        year:  _filterYear,
        page:  _page,
      );
      if (mounted) {
        setState(() {
          if (reset) _sessions.clear();
          _sessions.addAll(result.data);
          _hasMore = result.meta.hasMore;
          _page++;
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) setState(() { _error = e.toString(); _loading = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        _buildFilterBar(),
        Expanded(child: _buildBody()),
      ],
    );
  }

  Widget _buildFilterBar() {
    return Container(
      color: AppColors.white,
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 10),
      child: Row(
        children: [
          const Text('Bulan:', style: TextStyle(fontSize: 12, color: AppColors.gray500)),
          const SizedBox(width: 8),
          _MonthYearPicker(
            month: _filterMonth,
            year:  _filterYear,
            onChanged: (m, y) {
              setState(() { _filterMonth = m; _filterYear = y; });
              _load(reset: true);
            },
          ),
        ],
      ),
    );
  }

  Widget _buildBody() {
    if (_error != null && _sessions.isEmpty) return ErrorRetry(onRetry: () => _load(reset: true));
    if (!_loading && _sessions.isEmpty) {
      return const EmptyState(message: 'Belum ada sesi mengajar bulan ini', icon: Icons.class_outlined);
    }

    return RefreshIndicator(
      onRefresh: () => _load(reset: true),
      child: ListView.separated(
        controller: _scroll,
        padding: const EdgeInsets.all(16),
        itemCount: _sessions.length + (_loading ? 1 : 0),
        separatorBuilder: (_, __) => const SizedBox(height: 10),
        itemBuilder: (_, i) {
          if (i == _sessions.length) {
            return const Center(child: Padding(padding: EdgeInsets.all(12), child: CircularProgressIndicator()));
          }
          return _SessionCard(session: _sessions[i]);
        },
      ),
    );
  }
}

// ─── Session Card ─────────────────────────────────────────────────────────────

class _SessionCard extends StatelessWidget {
  final TeachingSession session;
  const _SessionCard({required this.session});

  @override
  Widget build(BuildContext context) {
    final date = DateFormat('EEE, d MMM y', 'id_ID')
        .format(DateTime.tryParse(session.date) ?? DateTime.now());

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppRadius.xl),
        border: Border.all(color: AppColors.gray100),
        boxShadow: AppShadow.sm,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: AppColors.blue100,
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  session.displayPeriodLabel,
                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.blue600),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  session.subjectName,
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.gray800),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Row(
            children: [
              const Icon(Icons.class_rounded, size: 12, color: AppColors.gray400),
              const SizedBox(width: 4),
              Text(session.className, style: const TextStyle(fontSize: 12, color: AppColors.gray500)),
              const SizedBox(width: 12),
              const Icon(Icons.calendar_today_rounded, size: 12, color: AppColors.gray400),
              const SizedBox(width: 4),
              Text(date, style: const TextStyle(fontSize: 12, color: AppColors.gray500)),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              _StatusPill(label: '${session.hadir} Hadir',              color: AppColors.green600,  bg: AppColors.green100),
              const SizedBox(width: 6),
              _StatusPill(label: '${session.alpha} Alpha',              color: AppColors.red500,    bg: AppColors.red100),
              const SizedBox(width: 6),
              _StatusPill(label: '${session.total} Siswa',              color: AppColors.gray600,   bg: AppColors.gray100),
            ],
          ),
        ],
      ),
    );
  }
}

// ─── Student Attendance Row ───────────────────────────────────────────────────

class _StudentAttRow extends StatelessWidget {
  final SessionStudentRow student;
  final void Function(String) onStatusChanged;

  const _StudentAttRow({required this.student, required this.onStatusChanged});

  static const _statuses = [
    ('hadir',        'H',  AppColors.green600,  AppColors.green100),
    ('tidak_hadir',  'A',  AppColors.red500,    AppColors.red100),
    ('izin',         'I',  AppColors.sky600,    AppColors.sky100),
    ('sakit',        'S',  AppColors.purple500, AppColors.violet100),
    ('dispensasi',   'D',  AppColors.teal600,   Color(0xFFCCFBF1)),
  ];

  @override
  Widget build(BuildContext context) {
    final hasMorningStatus = student.morningStatus != null && student.morningStatus != 'hadir';
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  student.name,
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray800),
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 2),
                Wrap(
                  crossAxisAlignment: WrapCrossAlignment.center,
                  spacing: 6,
                  runSpacing: 2,
                  children: [
                    if (student.nis != null)
                      Text(student.nis!, style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                      decoration: BoxDecoration(
                        color: hasMorningStatus ? AppColors.amber50 : AppColors.gray100,
                        borderRadius: BorderRadius.circular(4),
                        border: Border.all(
                          color: hasMorningStatus ? const Color(0xFFFCD34D) : AppColors.gray200,
                        ),
                      ),
                      child: Text(
                        'Pagi: ${student.morningStatusLabel ?? (student.morningStatus != null ? student.morningStatus! : "Belum Absen Pagi")}',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: hasMorningStatus ? FontWeight.bold : FontWeight.normal,
                          color: hasMorningStatus ? AppColors.amber800 : AppColors.gray600,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          Row(
            children: _statuses.map<Widget>((s) {
              final (status, label, color, bg) = s;
              final selected = student.status == status;
              return Padding(
                padding: const EdgeInsets.only(left: 4),
                child: GestureDetector(
                  onTap: () => onStatusChanged(status),
                  child: Container(
                    width: 30, height: 30,
                    decoration: BoxDecoration(
                      color: selected ? color : AppColors.gray50,
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(color: selected ? color : AppColors.gray200),
                    ),
                    child: Center(
                      child: Text(
                        label,
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w800,
                          color: selected ? Colors.white : AppColors.gray400,
                        ),
                      ),
                    ),
                  ),
                ),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

class _SumChip extends StatelessWidget {
  final String label;
  final int count;
  final Color color;

  const _SumChip({required this.label, required this.count, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text('$count', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: color)),
          const SizedBox(width: 4),
          Text(label, style: TextStyle(fontSize: 11, color: color, fontWeight: FontWeight.w500)),
        ],
      ),
    );
  }
}

class _StatusPill extends StatelessWidget {
  final String label;
  final Color color;
  final Color bg;

  const _StatusPill({required this.label, required this.color, required this.bg});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(6)),
      child: Text(label, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: color)),
    );
  }
}

class _QuickMarkButton extends StatelessWidget {
  final String label;
  final VoidCallback onTap;

  const _QuickMarkButton({required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          color: AppColors.green100,
          borderRadius: BorderRadius.circular(8),
        ),
        child: Text(label, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.green600)),
      ),
    );
  }
}

class _MonthYearPicker extends StatelessWidget {
  final int month;
  final int year;
  final void Function(int, int) onChanged;

  const _MonthYearPicker({required this.month, required this.year, required this.onChanged});

  static const _months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () async {
        int m = month, y = year;
        await showDialog(
          context: context,
          builder: (_) => AlertDialog(
            title: const Text('Pilih Bulan', style: TextStyle(fontSize: 16)),
            content: StatefulBuilder(
              builder: (ctx, setSt) => Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      IconButton(
                        icon: const Icon(Icons.chevron_left),
                        onPressed: () => setSt(() => y--),
                      ),
                      Text('$y', style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 16)),
                      IconButton(
                        icon: const Icon(Icons.chevron_right),
                        onPressed: () => setSt(() => y++),
                      ),
                    ],
                  ),
                  GridView.count(
                    crossAxisCount: 4,
                    shrinkWrap: true,
                    childAspectRatio: 1.5,
                    children: List.generate(12, (i) => GestureDetector(
                      onTap: () {
                        setSt(() => m = i + 1);
                        Navigator.pop(ctx);
                        onChanged(m, y);
                      },
                      child: Container(
                        margin: const EdgeInsets.all(3),
                        decoration: BoxDecoration(
                          color: m == i + 1 ? AppColors.blue600 : AppColors.gray50,
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Center(
                          child: Text(
                            _months[i],
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: m == i + 1 ? Colors.white : AppColors.gray700,
                            ),
                          ),
                        ),
                      ),
                    )),
                  ),
                ],
              ),
            ),
          ),
        );
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: AppColors.blue50,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: AppColors.blue200),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              '${_months[month - 1]} $year',
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.blue600),
            ),
            const SizedBox(width: 4),
            const Icon(Icons.expand_more_rounded, size: 16, color: AppColors.blue600),
          ],
        ),
      ),
    );
  }
}
