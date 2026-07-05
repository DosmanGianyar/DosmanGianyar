import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/guru_models.dart';
import '../../services/guru_service.dart';
import '../../theme/app_colors.dart';
import 'widgets/guru_widgets.dart';

class GuruJournalScreen extends StatefulWidget {
  const GuruJournalScreen({super.key});

  @override
  State<GuruJournalScreen> createState() => _GuruJournalScreenState();
}

class _GuruJournalScreenState extends State<GuruJournalScreen> {
  final List<TeacherJournal> _journals = [];
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
      final result = await GuruService.getJournals(
        month: _filterMonth,
        year:  _filterYear,
        page:  _page,
      );
      if (mounted) {
        setState(() {
          if (reset) _journals.clear();
          _journals.addAll(result.data);
          _hasMore = result.meta.hasMore;
          _page++;
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) setState(() { _error = e.toString(); _loading = false; });
    }
  }

  Future<void> _confirmDelete(TeacherJournal journal) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Hapus Jurnal?'),
        content: const Text('Jurnal ini akan dihapus permanen.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Batal')),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            style: FilledButton.styleFrom(backgroundColor: AppColors.red500),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );
    if (ok != true || !mounted) return;
    try {
      await GuruService.deleteJournal(journal.id);
      _load(reset: true);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Jurnal dihapus'),
          backgroundColor: AppColors.gray600,
          behavior: SnackBarBehavior.floating,
        ));
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(e.toString()),
          backgroundColor: AppColors.red500,
          behavior: SnackBarBehavior.floating,
        ));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Jurnal Mengajar'),
        actions: [
          IconButton(
            icon: const Icon(Icons.add_rounded),
            tooltip: 'Buat Jurnal',
            onPressed: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const _JournalFormScreen()),
            ).then((_) => _load(reset: true)),
          ),
        ],
      ),
      body: Column(
        children: [
          _buildFilterBar(),
          Expanded(child: _buildBody()),
        ],
      ),
    );
  }

  Widget _buildFilterBar() {
    final months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
    return Container(
      color: AppColors.white,
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 10),
      child: Row(
        children: [
          const Text('Bulan:', style: TextStyle(fontSize: 12, color: AppColors.gray500)),
          const SizedBox(width: 8),
          GestureDetector(
            onTap: () async {
              int m = _filterMonth, y = _filterYear;
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
                            IconButton(icon: const Icon(Icons.chevron_left), onPressed: () => setSt(() => y--)),
                            Text('$y', style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 16)),
                            IconButton(icon: const Icon(Icons.chevron_right), onPressed: () => setSt(() => y++)),
                          ],
                        ),
                        GridView.count(
                          crossAxisCount: 4, shrinkWrap: true, childAspectRatio: 1.5,
                          children: List.generate(12, (i) => GestureDetector(
                            onTap: () {
                              setSt(() => m = i + 1);
                              Navigator.pop(ctx);
                              setState(() { _filterMonth = m; _filterYear = y; });
                              _load(reset: true);
                            },
                            child: Container(
                              margin: const EdgeInsets.all(3),
                              decoration: BoxDecoration(
                                color: m == i + 1 ? AppColors.blue600 : AppColors.gray50,
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Center(child: Text(
                                months[i],
                                style: TextStyle(
                                  fontSize: 12, fontWeight: FontWeight.w600,
                                  color: m == i + 1 ? Colors.white : AppColors.gray700,
                                ),
                              )),
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
                    '${months[_filterMonth - 1]} $_filterYear',
                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.blue600),
                  ),
                  const SizedBox(width: 4),
                  const Icon(Icons.expand_more_rounded, size: 16, color: AppColors.blue600),
                ],
              ),
            ),
          ),
          const Spacer(),
          Text('${_journals.length} jurnal', style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
        ],
      ),
    );
  }

  Widget _buildBody() {
    if (_error != null && _journals.isEmpty) return ErrorRetry(onRetry: () => _load(reset: true));
    if (!_loading && _journals.isEmpty) {
      return EmptyState(
        message: 'Belum ada jurnal bulan ini\nKetuk + untuk menambah',
        icon: Icons.menu_book_outlined,
        action: TextButton.icon(
          onPressed: () => Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const _JournalFormScreen()),
          ).then((_) => _load(reset: true)),
          icon: const Icon(Icons.add),
          label: const Text('Buat Jurnal'),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: () => _load(reset: true),
      child: ListView.separated(
        controller: _scroll,
        padding: const EdgeInsets.all(16),
        itemCount: _journals.length + (_loading ? 1 : 0),
        separatorBuilder: (_, __) => const SizedBox(height: 10),
        itemBuilder: (_, i) {
          if (i == _journals.length) {
            return const Center(child: Padding(padding: EdgeInsets.all(12), child: CircularProgressIndicator()));
          }
          return _JournalCard(
            journal:  _journals[i],
            onDelete: () => _confirmDelete(_journals[i]),
          );
        },
      ),
    );
  }
}

// ─── Journal Card ─────────────────────────────────────────────────────────────

class _JournalCard extends StatelessWidget {
  final TeacherJournal journal;
  final VoidCallback onDelete;

  const _JournalCard({required this.journal, required this.onDelete});

  @override
  Widget build(BuildContext context) {
    final date = DateFormat('EEE, d MMM y', 'id_ID')
        .format(DateTime.tryParse(journal.date) ?? DateTime.now());

    return Container(
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppRadius.xl),
        border: Border.all(color: AppColors.gray100),
        boxShadow: AppShadow.sm,
      ),
      child: Column(
        children: [
          // Header
          Container(
            padding: const EdgeInsets.fromLTRB(14, 12, 14, 10),
            decoration: const BoxDecoration(
              color: AppColors.blue50,
              borderRadius: BorderRadius.only(
                topLeft: Radius.circular(AppRadius.xl),
                topRight: Radius.circular(AppRadius.xl),
              ),
            ),
            child: Row(
              children: [
                const Icon(Icons.menu_book_rounded, size: 16, color: AppColors.blue600),
                const SizedBox(width: 8),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        journal.className,
                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.gray800),
                      ),
                      Row(
                        children: [
                          Text(date, style: const TextStyle(fontSize: 11, color: AppColors.gray500)),
                          if (journal.period != null) ...[
                            const Text(' · ', style: TextStyle(color: AppColors.gray400)),
                            Text('Jam ${journal.period}', style: const TextStyle(fontSize: 11, color: AppColors.blue600)),
                          ],
                          if (journal.subjectName.isNotEmpty) ...[
                            const Text(' · ', style: TextStyle(color: AppColors.gray400)),
                            Text(journal.subjectName, style: const TextStyle(fontSize: 11, color: AppColors.gray500)),
                          ],
                        ],
                      ),
                    ],
                  ),
                ),
                if (journal.absencesCount > 0)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                    decoration: BoxDecoration(
                      color: AppColors.red100,
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      '${journal.absencesCount} absen',
                      style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.red500),
                    ),
                  ),
                const SizedBox(width: 4),
                GestureDetector(
                  onTap: onDelete,
                  child: const Padding(
                    padding: EdgeInsets.all(4),
                    child: Icon(Icons.delete_outline_rounded, size: 18, color: AppColors.gray400),
                  ),
                ),
              ],
            ),
          ),
          const Divider(height: 1, color: AppColors.gray100),

          // Body
          Padding(
            padding: const EdgeInsets.all(14),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _JournalField(label: 'Tujuan Pembelajaran', value: journal.learningObjectives),
                const SizedBox(height: 8),
                _JournalField(label: 'Materi',              value: journal.material),
                const SizedBox(height: 8),
                _JournalField(label: 'Aktivitas',           value: journal.activity),
                if (journal.notes != null && journal.notes!.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  _JournalField(label: 'Catatan',           value: journal.notes!),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _JournalField extends StatelessWidget {
  final String label;
  final String value;

  const _JournalField({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.gray400, letterSpacing: 0.3)),
        const SizedBox(height: 2),
        Text(value, style: const TextStyle(fontSize: 13, color: AppColors.gray700)),
      ],
    );
  }
}

// ─── Journal Form Screen ──────────────────────────────────────────────────────

class _JournalFormScreen extends StatefulWidget {
  const _JournalFormScreen();

  @override
  State<_JournalFormScreen> createState() => _JournalFormScreenState();
}

class _JournalFormScreenState extends State<_JournalFormScreen> {
  final _tpCtrl       = TextEditingController();
  final _materiCtrl   = TextEditingController();
  final _aktivCtrl    = TextEditingController();
  final _notesCtrl    = TextEditingController();

  Map<String, dynamic>? _classesData;
  bool _loadingClasses = true;

  int?    _selectedClassId;
  int?    _selectedSubjectId;
  int?    _selectedPeriod;

  List<SimpleStudent>    _classStudents   = [];
  List<JournalAbsentStudent> _absentList  = [];
  bool _loadingStudents = false;

  DateTime _date = DateTime.now();
  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    _loadClasses();
  }

  @override
  void dispose() {
    _tpCtrl.dispose();
    _materiCtrl.dispose();
    _aktivCtrl.dispose();
    _notesCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadClasses() async {
    try {
      final data = await GuruService.getTeachingClasses();
      if (mounted) setState(() { _classesData = data; _loadingClasses = false; });
    } catch (_) {
      if (mounted) setState(() => _loadingClasses = false);
    }
  }

  Future<void> _loadClassStudents(int classId) async {
    setState(() { _loadingStudents = true; _classStudents = []; _absentList = []; });
    try {
      final list = await GuruService.getJournalClassStudents(classId);
      if (mounted) setState(() { _classStudents = list; _loadingStudents = false; });
    } catch (_) {
      if (mounted) setState(() => _loadingStudents = false);
    }
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _date,
      firstDate: DateTime.now().subtract(const Duration(days: 90)),
      lastDate: DateTime.now(),
    );
    if (picked != null && mounted) setState(() => _date = picked);
  }

  void _toggleAbsent(SimpleStudent student, String status) {
    setState(() {
      final idx = _absentList.indexWhere((a) => a.studentId == student.id);
      if (idx >= 0) {
        if (_absentList[idx].status == status) {
          _absentList.removeAt(idx); // deselect
        } else {
          _absentList[idx].status = status;
        }
      } else {
        _absentList.add(JournalAbsentStudent(
          studentId: student.id,
          name:      student.name,
          nis:       student.nis,
          status:    status,
        ));
      }
    });
  }

  String? _absentStatus(int studentId) {
    try {
      return _absentList.firstWhere((a) => a.studentId == studentId).status;
    } catch (_) {
      return null;
    }
  }

  Future<void> _submit() async {
    if (_selectedClassId == null) {
      _snack('Pilih kelas', AppColors.orange500); return;
    }
    if (_tpCtrl.text.trim().isEmpty) {
      _snack('Isi Tujuan Pembelajaran', AppColors.orange500); return;
    }
    if (_materiCtrl.text.trim().isEmpty) {
      _snack('Isi Materi', AppColors.orange500); return;
    }
    if (_aktivCtrl.text.trim().isEmpty) {
      _snack('Isi Aktivitas Pembelajaran', AppColors.orange500); return;
    }

    setState(() => _submitting = true);
    try {
      final msg = await GuruService.createJournal(
        classId:              _selectedClassId!,
        subjectId:            _selectedSubjectId,
        date:                 DateFormat('yyyy-MM-dd').format(_date),
        period:               _selectedPeriod,
        learningObjectives:   _tpCtrl.text.trim(),
        material:             _materiCtrl.text.trim(),
        activity:             _aktivCtrl.text.trim(),
        notes:                _notesCtrl.text.trim().isEmpty ? null : _notesCtrl.text.trim(),
        absentStudents:       _absentList.map((a) => {
          'student_id': a.studentId,
          'status':     a.status,
        }).toList(),
      );
      if (mounted) {
        _snack(msg, AppColors.emerald600);
        Navigator.pop(context);
      }
    } catch (e) {
      if (mounted) _snack(e.toString(), AppColors.red500);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
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
      appBar: AppBar(title: const Text('Buat Jurnal Mengajar')),
      body: _loadingClasses
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // ── Tanggal ──────────────────────────────────────────
                  _FormCard(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _lbl('Tanggal'),
                        const SizedBox(height: 8),
                        GestureDetector(
                          onTap: _pickDate,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                            decoration: BoxDecoration(
                              color: AppColors.gray50,
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(color: AppColors.gray200),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.calendar_today_rounded, size: 16, color: AppColors.gray400),
                                const SizedBox(width: 8),
                                Text(
                                  DateFormat('EEEE, d MMMM y', 'id_ID').format(_date),
                                  style: const TextStyle(fontSize: 13, color: AppColors.gray700),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),

                  // ── Kelas & Jam ──────────────────────────────────────
                  _FormCard(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _lbl('Kelas'),
                        const SizedBox(height: 8),
                        _buildClassChips(),
                        const SizedBox(height: 14),
                        _lbl('Jam Ke- (opsional)'),
                        const SizedBox(height: 8),
                        Wrap(
                          spacing: 6, runSpacing: 6,
                          children: List.generate(10, (i) {
                            final p = i + 1;
                            final sel = _selectedPeriod == p;
                            return GestureDetector(
                              onTap: () => setState(() => _selectedPeriod = sel ? null : p),
                              child: Container(
                                width: 40, height: 40,
                                decoration: BoxDecoration(
                                  color: sel ? AppColors.blue600 : AppColors.gray50,
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(color: sel ? AppColors.blue600 : AppColors.gray200),
                                ),
                                child: Center(
                                  child: Text('$p', style: TextStyle(
                                    fontSize: 13, fontWeight: FontWeight.w700,
                                    color: sel ? Colors.white : AppColors.gray700,
                                  )),
                                ),
                              ),
                            );
                          }),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),

                  // ── Isi Jurnal ───────────────────────────────────────
                  _FormCard(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _lbl('Tujuan Pembelajaran (TP) *'),
                        const SizedBox(height: 8),
                        _textField(_tpCtrl, 'Peserta didik mampu...', maxLines: 3),
                        const SizedBox(height: 14),
                        _lbl('Materi *'),
                        const SizedBox(height: 8),
                        _textField(_materiCtrl, 'Topik / bab yang diajarkan...'),
                        const SizedBox(height: 14),
                        _lbl('Aktivitas Pembelajaran *'),
                        const SizedBox(height: 8),
                        _textField(_aktivCtrl, 'Ceramah, diskusi, praktikum...', maxLines: 3),
                        const SizedBox(height: 14),
                        _lbl('Catatan Tambahan (opsional)'),
                        const SizedBox(height: 8),
                        _textField(_notesCtrl, 'Kendala, observasi, dll...', maxLines: 2),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),

                  // ── Siswa Tidak Hadir ────────────────────────────────
                  _FormCard(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            _lbl('Siswa Tidak Hadir'),
                            const Spacer(),
                            if (_absentList.isNotEmpty)
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: AppColors.red100,
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  '${_absentList.length} siswa',
                                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.red500),
                                ),
                              ),
                          ],
                        ),
                        const SizedBox(height: 10),

                        if (_selectedClassId == null)
                          const Text('Pilih kelas terlebih dahulu.',
                            style: TextStyle(fontSize: 12, color: AppColors.gray400))
                        else if (_loadingStudents)
                          const Center(child: Padding(padding: EdgeInsets.all(16), child: CircularProgressIndicator(strokeWidth: 2)))
                        else if (_classStudents.isEmpty)
                          const Text('Tidak ada siswa.', style: TextStyle(fontSize: 12, color: AppColors.gray400))
                        else
                          ..._classStudents.map((s) {
                            final absentStatus = _absentStatus(s.id);
                            return Padding(
                              padding: const EdgeInsets.only(bottom: 6),
                              child: Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      '${s.name}${s.nis != null ? ' (${s.nis})' : ''}',
                                      style: const TextStyle(fontSize: 12, color: AppColors.gray700),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  ...[
                                    ('tidak_hadir', 'A', AppColors.red500),
                                    ('izin',        'I', AppColors.sky600),
                                    ('sakit',       'S', AppColors.purple500),
                                  ].map(((String, String, Color) opt) {
                                    final (status, label, color) = opt;
                                    final selected = absentStatus == status;
                                    return Padding(
                                      padding: const EdgeInsets.only(left: 4),
                                      child: GestureDetector(
                                        onTap: () => _toggleAbsent(s, status),
                                        child: Container(
                                          width: 28, height: 28,
                                          decoration: BoxDecoration(
                                            color: selected ? color : AppColors.gray50,
                                            borderRadius: BorderRadius.circular(6),
                                            border: Border.all(color: selected ? color : AppColors.gray200),
                                          ),
                                          child: Center(
                                            child: Text(label, style: TextStyle(
                                              fontSize: 12, fontWeight: FontWeight.w800,
                                              color: selected ? Colors.white : AppColors.gray400,
                                            )),
                                          ),
                                        ),
                                      ),
                                    );
                                  }),
                                ],
                              ),
                            );
                          }),
                      ],
                    ),
                  ),
                  const SizedBox(height: 24),

                  // ── Tombol Simpan ────────────────────────────────────
                  SizedBox(
                    width: double.infinity,
                    child: FilledButton.icon(
                      onPressed: _submitting ? null : _submit,
                      icon: _submitting
                          ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                          : const Icon(Icons.save_rounded, size: 18),
                      label: Text(_submitting ? 'Menyimpan...' : 'Simpan Jurnal'),
                      style: FilledButton.styleFrom(
                        backgroundColor: AppColors.blue600,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                  ),
                  const SizedBox(height: 32),
                ],
              ),
            ),
    );
  }

  Widget _buildClassChips() {
    final teachingClasses = (_classesData?['teaching_classes'] as List<dynamic>? ?? [])
        .cast<Map<String, dynamic>>();
    final homeroom = _classesData?['homeroom_class'] as Map<String, dynamic>?;

    final allClasses = <Map<String, dynamic>>[
      if (homeroom != null) {...homeroom, 'subject_name': 'Wali Kelas'},
      ...teachingClasses,
    ];

    if (allClasses.isEmpty) {
      return const Text('Tidak ada data kelas.', style: TextStyle(color: AppColors.gray400, fontSize: 12));
    }

    return Wrap(
      spacing: 8, runSpacing: 8,
      children: allClasses.map((c) {
        final id      = c['id'] as int?;
        final name    = c['name'] as String? ?? '—';
        final subject = c['subject_name'] as String? ?? '';
        final sel     = _selectedClassId == id;

        return GestureDetector(
          onTap: () {
            setState(() {
              _selectedClassId    = id;
              _selectedSubjectId  = c['subject_id'] as int?;
              _absentList         = [];
            });
            if (id != null) _loadClassStudents(id);
          },
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: sel ? AppColors.blue600 : AppColors.gray50,
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: sel ? AppColors.blue600 : AppColors.gray200),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(name, style: TextStyle(
                  fontSize: 13, fontWeight: FontWeight.w700,
                  color: sel ? Colors.white : AppColors.gray800,
                )),
                if (subject.isNotEmpty) Text(subject, style: TextStyle(
                  fontSize: 11, color: sel ? Colors.white70 : AppColors.gray400,
                )),
              ],
            ),
          ),
        );
      }).toList(),
    );
  }

  Widget _lbl(String t) => Text(t,
    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.gray700));

  Widget _textField(TextEditingController ctrl, String hint, {int maxLines = 1}) {
    return TextField(
      controller: ctrl,
      maxLines: maxLines,
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: const TextStyle(color: AppColors.gray400, fontSize: 13),
        filled: true,
        fillColor: AppColors.gray50,
        contentPadding: const EdgeInsets.all(12),
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

class _FormCard extends StatelessWidget {
  final Widget child;
  const _FormCard({required this.child});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppRadius.xl),
        border: Border.all(color: AppColors.gray100),
        boxShadow: AppShadow.sm,
      ),
      child: child,
    );
  }
}
