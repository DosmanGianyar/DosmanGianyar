import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/guru_models.dart';
import '../../providers/auth_provider.dart';
import '../../services/guru_service.dart';
import '../../theme/app_colors.dart';

class GuruBkScreen extends StatefulWidget {
  const GuruBkScreen({super.key});

  @override
  State<GuruBkScreen> createState() => _GuruBkScreenState();
}

class _GuruBkScreenState extends State<GuruBkScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabCtrl;
  bool _isBk = false;
  bool _tabInited = false;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (!_tabInited) {
      _isBk    = context.read<AuthProvider>().user?.isBk ?? false;
      _tabCtrl = TabController(length: _isBk ? 2 : 1, vsync: this);
      _tabInited = true;
    }
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final tabs = <Tab>[
      const Tab(text: 'Catatan BK'),
      if (_isBk) const Tab(text: 'Tambah Catatan'),
    ];
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Bimbingan Konseling (BK)'),
        bottom: TabBar(
          controller: _tabCtrl,
          tabs: tabs,
          labelColor: AppColors.blue600,
          indicatorColor: AppColors.blue600,
          unselectedLabelColor: AppColors.gray400,
        ),
      ),
      body: TabBarView(
        controller: _tabCtrl,
        children: [
          const _BkLogTab(),
          if (_isBk) const _BkAddTab(),
        ],
      ),
    );
  }
}

// ─── Log Tab ──────────────────────────────────────────────────────────────────

class _BkLogTab extends StatefulWidget {
  const _BkLogTab();

  @override
  State<_BkLogTab> createState() => _BkLogTabState();
}

class _BkLogTabState extends State<_BkLogTab> {
  List<Map<String, dynamic>> _classes = [];
  List<BkLogItem>            _logs    = [];
  int?   _selectedClassId;
  int    _page        = 1;
  int    _lastPage    = 1;
  int    _total       = 0;
  bool   _loading     = false;
  bool   _loadingMore = false;

  @override
  void initState() {
    super.initState();
    _loadClasses();
  }

  Future<void> _loadClasses() async {
    try {
      final classes = await GuruService.getBkClasses();
      if (mounted) {
        setState(() => _classes = classes);
        _loadLogs();
      }
    } catch (_) {
      _loadLogs();
    }
  }

  Future<void> _loadLogs({bool refresh = true}) async {
    if (refresh) {
      setState(() { _loading = true; _page = 1; });
    } else {
      setState(() => _loadingMore = true);
    }
    try {
      final result = await GuruService.getBkLogs(
        classId: _selectedClassId,
        page:    _page,
      );
      if (mounted) {
        setState(() {
          if (refresh) {
            _logs = result.data;
          } else {
            _logs.addAll(result.data);
          }
          _lastPage    = result.lastPage;
          _total       = result.total;
          _loading     = false;
          _loadingMore = false;
        });
      }
    } catch (e) {
      if (mounted) setState(() { _loading = false; _loadingMore = false; });
    }
  }

  void _loadMore() {
    if (_page < _lastPage && !_loadingMore) {
      _page++;
      _loadLogs(refresh: false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // class filter
        Container(
          color: Colors.white,
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          child: Row(
            children: [
              const Text('Kelas:', style: TextStyle(fontSize: 12, color: AppColors.gray500)),
              const SizedBox(width: 8),
              Expanded(
                child: SizedBox(
                  height: 34,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8),
                    decoration: BoxDecoration(
                      color: AppColors.gray50,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: AppColors.gray200),
                    ),
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton<int?>(
                        value: _selectedClassId,
                        isExpanded: true,
                        style: const TextStyle(fontSize: 13, color: AppColors.gray800),
                        items: [
                          const DropdownMenuItem<int?>(value: null, child: Text('Semua kelas')),
                          ..._classes.map((c) => DropdownMenuItem<int?>(
                            value: c['id'] as int,
                            child: Text(c['name'] as String),
                          )),
                        ],
                        onChanged: (id) {
                          setState(() => _selectedClassId = id);
                          _loadLogs();
                        },
                      ),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Text('$_total catatan', style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
            ],
          ),
        ),
        Expanded(child: _buildList()),
      ],
    );
  }

  Widget _buildList() {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_logs.isEmpty) {
      return const Center(
        child: Text('Belum ada catatan BK', style: TextStyle(color: AppColors.gray400)),
      );
    }
    return NotificationListener<ScrollNotification>(
      onNotification: (n) {
        if (n.metrics.pixels >= n.metrics.maxScrollExtent - 100) _loadMore();
        return false;
      },
      child: ListView.builder(
        padding: const EdgeInsets.all(12),
        itemCount: _logs.length + (_loadingMore ? 1 : 0),
        itemBuilder: (_, i) {
          if (i == _logs.length) {
            return const Center(child: Padding(
              padding: EdgeInsets.all(16),
              child: CircularProgressIndicator(strokeWidth: 2),
            ));
          }
          return _BkLogCard(log: _logs[i]);
        },
      ),
    );
  }
}

class _BkLogCard extends StatelessWidget {
  final BkLogItem log;
  const _BkLogCard({required this.log});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.gray100),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(log.studentName,
                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                    Text(
                      '${log.studentNis ?? '—'} · ${log.className ?? ''}',
                      style: const TextStyle(fontSize: 11, color: AppColors.gray400),
                    ),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(log.date, style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
                  if (log.isAuto)
                    Container(
                      margin: const EdgeInsets.only(top: 2),
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.gray100,
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: const Text('Otomatis', style: TextStyle(fontSize: 9, color: AppColors.gray500)),
                    ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: AppColors.gray50,
              borderRadius: BorderRadius.circular(8),
            ),
            child: Text(log.coachingNote, style: const TextStyle(fontSize: 13, color: AppColors.gray700)),
          ),
          if (log.counselorName != null) ...[
            const SizedBox(height: 6),
            Text(
              'Oleh: ${log.counselorName}',
              style: const TextStyle(fontSize: 11, color: AppColors.gray400),
            ),
          ],
        ],
      ),
    );
  }
}

// ─── Add Tab ──────────────────────────────────────────────────────────────────

class _BkAddTab extends StatefulWidget {
  const _BkAddTab();

  @override
  State<_BkAddTab> createState() => _BkAddTabState();
}

class _BkAddTabState extends State<_BkAddTab> {
  List<SimpleStudent> _students     = [];
  SimpleStudent?      _selectedStudent;
  bool   _loadingStudents = false;
  bool   _submitting      = false;
  String _selectedDate    = '';
  final _searchCtrl = TextEditingController();
  final _noteCtrl   = TextEditingController();

  @override
  void initState() {
    super.initState();
    final now = DateTime.now();
    _selectedDate = '${now.year}-${now.month.toString().padLeft(2,'0')}-${now.day.toString().padLeft(2,'0')}';
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    _noteCtrl.dispose();
    super.dispose();
  }

  Future<void> _searchStudents() async {
    final q = _searchCtrl.text.trim();
    if (q.isEmpty) return;
    setState(() { _loadingStudents = true; _students = []; });
    try {
      final list = await GuruService.getBkStudents(q: q);
      if (mounted) setState(() { _students = list; _loadingStudents = false; });
    } catch (_) {
      if (mounted) setState(() => _loadingStudents = false);
    }
  }

  Future<void> _submit() async {
    if (_selectedStudent == null) {
      _snack('Pilih siswa', AppColors.orange500); return;
    }
    final note = _noteCtrl.text.trim();
    if (note.isEmpty) {
      _snack('Isi catatan bimbingan', AppColors.orange500); return;
    }

    setState(() => _submitting = true);
    try {
      final msg = await GuruService.storeBkLog(
        studentId:    _selectedStudent!.id,
        coachingNote: note,
        date:         _selectedDate,
      );
      if (mounted) {
        _snack(msg, AppColors.emerald600);
        setState(() {
          _selectedStudent = null;
          _students        = [];
          _noteCtrl.clear();
          _searchCtrl.clear();
        });
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

  Future<void> _pickDate() async {
    final now  = DateTime.now();
    final init = DateTime.tryParse(_selectedDate) ?? now;
    final picked = await showDatePicker(
      context: context,
      initialDate: init,
      firstDate: DateTime(now.year - 1),
      lastDate: now,
    );
    if (picked != null && mounted) {
      setState(() =>
        _selectedDate = '${picked.year}-${picked.month.toString().padLeft(2,'0')}-${picked.day.toString().padLeft(2,'0')}',
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Cari Siswa
          _label('1. Cari Siswa'),
          const SizedBox(height: 8),
          Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _searchCtrl,
                  decoration: _inputDecoration('Cari nama / NIS...', Icons.search),
                  onSubmitted: (_) => _searchStudents(),
                ),
              ),
              const SizedBox(width: 8),
              ElevatedButton(
                onPressed: _searchStudents,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.blue600,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                ),
                child: const Icon(Icons.search, size: 18),
              ),
            ],
          ),
          const SizedBox(height: 8),
          if (_selectedStudent != null)
            _SelectedStudentChip(
              label: '${_selectedStudent!.name} (${_selectedStudent!.nis ?? '—'})',
              onRemove: () => setState(() { _selectedStudent = null; _students = []; }),
            )
          else if (_loadingStudents)
            const Center(child: Padding(
              padding: EdgeInsets.all(12),
              child: CircularProgressIndicator(strokeWidth: 2),
            ))
          else if (_students.isNotEmpty)
            Container(
              height: 160,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: AppColors.gray200),
              ),
              child: ListView.separated(
                padding: const EdgeInsets.all(4),
                itemCount: _students.length,
                separatorBuilder: (_, __) => const Divider(height: 1),
                itemBuilder: (_, i) {
                  final s = _students[i];
                  return ListTile(
                    dense: true,
                    title: Text(s.name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                    subtitle: Text('${s.nis ?? '—'} · ${s.className ?? ''}',
                        style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
                    onTap: () => setState(() {
                      _selectedStudent = s;
                      _students        = [];
                    }),
                  );
                },
              ),
            ),
          const SizedBox(height: 16),

          // Tanggal
          _label('2. Tanggal'),
          const SizedBox(height: 8),
          GestureDetector(
            onTap: _pickDate,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: AppColors.gray200),
              ),
              child: Row(
                children: [
                  const Icon(Icons.calendar_today_rounded, size: 16, color: AppColors.gray400),
                  const SizedBox(width: 8),
                  Text(_selectedDate, style: const TextStyle(fontSize: 13)),
                  const Spacer(),
                  const Icon(Icons.arrow_drop_down, color: AppColors.gray400),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),

          // Catatan
          _label('3. Catatan Bimbingan'),
          const SizedBox(height: 8),
          TextField(
            controller: _noteCtrl,
            maxLines: 5,
            decoration: _inputDecoration('Tuliskan catatan bimbingan...', null),
          ),
          const SizedBox(height: 24),

          // Tombol simpan
          SizedBox(
            width: double.infinity,
            child: FilledButton.icon(
              onPressed: _submitting ? null : _submit,
              icon: _submitting
                  ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : const Icon(Icons.save_rounded, size: 18),
              label: Text(_submitting ? 'Menyimpan...' : 'Simpan Catatan BK'),
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
    );
  }

  Widget _label(String text) => Text(
    text,
    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.gray700),
  );

  InputDecoration _inputDecoration(String hint, IconData? prefixIcon) => InputDecoration(
    hintText: hint,
    prefixIcon: prefixIcon != null ? Icon(prefixIcon, size: 18, color: AppColors.gray400) : null,
    filled: true,
    fillColor: Colors.white,
    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.gray200)),
    enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.gray200)),
    focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.blue600, width: 1.5)),
  );
}

class _SelectedStudentChip extends StatelessWidget {
  final String label;
  final VoidCallback onRemove;
  const _SelectedStudentChip({required this.label, required this.onRemove});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: AppColors.blue50,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: AppColors.blue200),
      ),
      child: Row(
        children: [
          const Icon(Icons.person_rounded, size: 16, color: AppColors.blue600),
          const SizedBox(width: 8),
          Expanded(child: Text(label,
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.blue600))),
          GestureDetector(
            onTap: onRemove,
            child: const Icon(Icons.close_rounded, size: 16, color: AppColors.blue600),
          ),
        ],
      ),
    );
  }
}
