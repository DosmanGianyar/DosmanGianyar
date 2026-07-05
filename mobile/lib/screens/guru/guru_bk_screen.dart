import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/guru_models.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
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
      _tabCtrl = TabController(length: _isBk ? 3 : 1, vsync: this);
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
      if (_isBk) const Tab(text: 'Bimbingan Siswa'),
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
          isScrollable: _isBk,
          tabAlignment: _isBk ? TabAlignment.start : TabAlignment.fill,
        ),
      ),
      body: TabBarView(
        controller: _tabCtrl,
        children: [
          const _BkLogTab(),
          if (_isBk) const _BkConsultationsTab(),
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

// ─── BK Consultations Tab (Guru BK only) ─────────────────────────────────────

class _BkConsultationsTab extends StatefulWidget {
  const _BkConsultationsTab();
  @override
  State<_BkConsultationsTab> createState() => _BkConsultationsTabState();
}

class _BkConsultationsTabState extends State<_BkConsultationsTab> {
  List<Map<String, dynamic>> _consultations = [];
  Map<String, int>           _counts        = {};
  String                     _status        = '';
  bool                       _loading       = true;

  final _statuses = const [
    ('', 'Semua'),
    ('pending', 'Menunggu'),
    ('scheduled', 'Dijadwalkan'),
    ('completed', 'Selesai'),
    ('cancelled', 'Dibatalkan'),
  ];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final body = await ApiClient.get('/guru/bk-consultations',
        params: _status.isNotEmpty ? {'status': _status} : null);
      setState(() {
        _consultations = List<Map<String, dynamic>>.from(body['consultations'] ?? []);
        final c = body['counts'] as Map<String, dynamic>? ?? {};
        _counts = c.map((k, v) => MapEntry(k, (v as num).toInt()));
      });
    } catch (_) {
      // silent
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  int get _total => _counts.values.fold(0, (a, b) => a + b);

  Future<void> _schedule(int id) async {
    DateTime? picked = DateTime.now().add(const Duration(days: 1));
    picked = await showDatePicker(
      context: context,
      initialDate: picked,
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );
    if (picked == null || !mounted) return;
    final dateStr = '${picked.year}-${picked.month.toString().padLeft(2,'0')}-${picked.day.toString().padLeft(2,'0')}';
    try {
      await ApiClient.patch('/guru/bk-consultations/$id/schedule',
        data: {'scheduled_date': dateStr});
      _showSnack('Bimbingan berhasil dijadwalkan.', success: true);
      _load();
    } catch (e) {
      if (mounted) _showSnack(ApiClient.extractError(e));
    }
  }

  Future<void> _complete(int id) async {
    final noteCtrl  = TextEditingController();
    final followCtrl = TextEditingController();
    DateTime conducted = DateTime.now();
    String conductedStr = '${conducted.year}-${conducted.month.toString().padLeft(2,'0')}-${conducted.day.toString().padLeft(2,'0')}';

    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx2, setSt) => DraggableScrollableSheet(
          initialChildSize: 0.8,
          minChildSize: 0.5,
          maxChildSize: 0.95,
          expand: false,
          builder: (_, sc) => Container(
            decoration: const BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
            ),
            child: Column(children: [
              const SizedBox(height: 8),
              Container(width: 40, height: 4,
                decoration: BoxDecoration(color: AppColors.gray200,
                  borderRadius: BorderRadius.circular(2))),
              const SizedBox(height: 16),
              const Padding(
                padding: EdgeInsets.symmetric(horizontal: 20),
                child: Align(alignment: Alignment.centerLeft,
                  child: Text('Isi Jurnal Bimbingan',
                    style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800))),
              ),
              const SizedBox(height: 12),
              Expanded(child: SingleChildScrollView(
                controller: sc,
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: Column(children: [
                  ListTile(
                    leading: const Icon(Icons.calendar_today_rounded, size: 18),
                    title: Text('Tanggal Pelaksanaan: $conductedStr'),
                    contentPadding: EdgeInsets.zero,
                    onTap: () async {
                      final d = await showDatePicker(
                        context: ctx2,
                        initialDate: conducted,
                        firstDate: DateTime(2020),
                        lastDate: DateTime.now(),
                      );
                      if (d != null) {
                        setSt(() {
                          conducted = d;
                          conductedStr = '${d.year}-${d.month.toString().padLeft(2,'0')}-${d.day.toString().padLeft(2,'0')}';
                        });
                      }
                    },
                  ),
                  const SizedBox(height: 8),
                  TextField(
                    controller: noteCtrl,
                    maxLines: 5,
                    decoration: InputDecoration(
                      labelText: 'Catatan Pembinaan *',
                      hintText: 'Uraikan hasil bimbingan…',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      contentPadding: const EdgeInsets.all(12),
                    ),
                  ),
                  const SizedBox(height: 10),
                  TextField(
                    controller: followCtrl,
                    maxLines: 3,
                    decoration: InputDecoration(
                      labelText: 'Tindak Lanjut (opsional)',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      contentPadding: const EdgeInsets.all(12),
                    ),
                  ),
                  const SizedBox(height: 8),
                ]),
              )),
              Padding(
                padding: EdgeInsets.fromLTRB(20, 12, 20,
                  MediaQuery.of(ctx).viewInsets.bottom + 20),
                child: Row(children: [
                  Expanded(child: OutlinedButton(
                    onPressed: () => Navigator.pop(ctx),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
                    child: const Text('Batal'),
                  )),
                  const SizedBox(width: 10),
                  Expanded(child: FilledButton(
                    onPressed: () async {
                      if (noteCtrl.text.trim().isEmpty) return;
                      Navigator.pop(ctx);
                      try {
                        await ApiClient.patch('/guru/bk-consultations/$id/complete', data: {
                          'conducted_date': conductedStr,
                          'teacher_note': noteCtrl.text.trim(),
                          if (followCtrl.text.trim().isNotEmpty)
                            'follow_up': followCtrl.text.trim(),
                        });
                        if (mounted) {
                          _showSnack('Jurnal bimbingan BK berhasil disimpan.', success: true);
                          _load();
                        }
                      } catch (e) {
                        if (mounted) _showSnack(ApiClient.extractError(e));
                      }
                    },
                    style: FilledButton.styleFrom(
                      backgroundColor: AppColors.green500,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
                    child: const Text('Simpan'),
                  )),
                ]),
              ),
            ]),
          ),
        ),
      ),
    );
  }

  Future<void> _cancel(int id) async {
    final reasonCtrl = TextEditingController();
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Batalkan Pengajuan'),
        content: TextField(
          controller: reasonCtrl,
          decoration: InputDecoration(
            labelText: 'Alasan (opsional)',
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Tidak')),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            style: FilledButton.styleFrom(backgroundColor: AppColors.red500),
            child: const Text('Batalkan'),
          ),
        ],
      ),
    );
    if (confirm != true || !mounted) return;
    try {
      await ApiClient.patch('/guru/bk-consultations/$id/cancel',
        data: {'cancelled_reason': reasonCtrl.text.trim()});
      _showSnack('Pengajuan dibatalkan.', success: true);
      _load();
    } catch (e) {
      if (mounted) _showSnack(ApiClient.extractError(e));
    }
  }

  void _showSnack(String msg, {bool success = false}) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: success ? AppColors.green500 : AppColors.red500,
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
    ));
  }

  @override
  Widget build(BuildContext context) {
    return Column(children: [
      // ── Filter chips ──
      SizedBox(
        height: 48,
        child: ListView(
          scrollDirection: Axis.horizontal,
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          children: _statuses.map((s) {
            final (val, label) = s;
            final count = val.isEmpty ? _total : (_counts[val] ?? 0);
            final selected = _status == val;
            return Padding(
              padding: const EdgeInsets.only(right: 8),
              child: GestureDetector(
                onTap: () {
                  setState(() => _status = val);
                  _load();
                },
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: selected ? AppColors.violet600 : AppColors.gray100,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Row(children: [
                    Text(label,
                      style: TextStyle(
                        color: selected ? Colors.white : AppColors.gray600,
                        fontSize: 12, fontWeight: FontWeight.w600)),
                    if (count > 0) ...[
                      const SizedBox(width: 4),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                        decoration: BoxDecoration(
                          color: selected ? Colors.white.withValues(alpha: 0.3) : AppColors.gray200,
                          borderRadius: BorderRadius.circular(10)),
                        child: Text('$count',
                          style: TextStyle(
                            color: selected ? Colors.white : AppColors.gray500,
                            fontSize: 10, fontWeight: FontWeight.w700)),
                      ),
                    ],
                  ]),
                ),
              ),
            );
          }).toList(),
        ),
      ),

      // ── List ──
      Expanded(child: _loading
          ? const Center(child: CircularProgressIndicator())
          : _consultations.isEmpty
              ? const Center(child: Text('Belum ada pengajuan bimbingan.',
                  style: TextStyle(color: AppColors.gray400)))
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView.separated(
                    padding: const EdgeInsets.fromLTRB(16, 8, 16, 32),
                    itemCount: _consultations.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 8),
                    itemBuilder: (_, i) => _BkConsultationCard(
                      c: _consultations[i],
                      onSchedule: () => _schedule(_consultations[i]['id'] as int),
                      onComplete: () => _complete(_consultations[i]['id'] as int),
                      onCancel: () => _cancel(_consultations[i]['id'] as int),
                    ),
                  ),
                ),
      ),
    ]);
  }
}

class _BkConsultationCard extends StatelessWidget {
  final Map<String, dynamic> c;
  final VoidCallback onSchedule;
  final VoidCallback onComplete;
  final VoidCallback onCancel;
  const _BkConsultationCard({
    required this.c,
    required this.onSchedule,
    required this.onComplete,
    required this.onCancel,
  });

  @override
  Widget build(BuildContext context) {
    final status = c['status'] as String;
    final (Color border, Color badgeBg, Color badgeFg) = switch (status) {
      'pending'   => (AppColors.amber100,  AppColors.amber100,  AppColors.amber500),
      'scheduled' => (AppColors.blue50,    AppColors.blue50,    AppColors.blue600),
      'completed' => (AppColors.green100,  AppColors.green100,  AppColors.green600),
      _           => (AppColors.gray100,   AppColors.gray100,   AppColors.gray500),
    };

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: border),
        boxShadow: AppShadow.sm,
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(c['student_name'] as String? ?? '—',
              style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: AppColors.gray800)),
            Text([c['student_nis'], c['student_class']].whereType<String>().join(' · '),
              style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
          ])),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(color: badgeBg, borderRadius: BorderRadius.circular(20)),
            child: Text(c['status_label'] as String,
              style: TextStyle(color: badgeFg, fontSize: 10, fontWeight: FontWeight.w700)),
          ),
        ]),
        const SizedBox(height: 8),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
          decoration: BoxDecoration(
            color: AppColors.gray50,
            borderRadius: BorderRadius.circular(8),
          ),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            const Text('Topik:', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.gray500)),
            const SizedBox(height: 2),
            Text(c['topic'] as String,
              style: const TextStyle(fontSize: 12, color: AppColors.gray800)),
            if (c['student_note'] != null) ...[
              const SizedBox(height: 4),
              Text(c['student_note'] as String,
                style: const TextStyle(fontSize: 11, color: AppColors.gray500)),
            ],
          ]),
        ),
        const SizedBox(height: 6),
        Text('Diajukan: ${c['created_at'] ?? '—'}',
          style: const TextStyle(fontSize: 10, color: AppColors.gray400)),

        if (status == 'scheduled' && c['scheduled_date'] != null) ...[
          const SizedBox(height: 6),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
            decoration: BoxDecoration(color: AppColors.blue50, borderRadius: BorderRadius.circular(8)),
            child: Text('Dijadwalkan: ${c['scheduled_date']}',
              style: const TextStyle(color: AppColors.blue600, fontSize: 11)),
          ),
        ],

        if (status == 'completed') ...[
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(color: AppColors.green50, borderRadius: BorderRadius.circular(8)),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              if (c['conducted_date'] != null)
                Text('Dilaksanakan: ${c['conducted_date']}',
                  style: const TextStyle(color: AppColors.gray500, fontSize: 11)),
              if (c['teacher_note'] != null) ...[
                const SizedBox(height: 4),
                Text(c['teacher_note'] as String,
                  style: const TextStyle(color: AppColors.gray700, fontSize: 11)),
              ],
            ]),
          ),
        ],

        if (status == 'cancelled' && c['cancelled_reason'] != null) ...[
          const SizedBox(height: 6),
          Text(c['cancelled_reason'] as String,
            style: const TextStyle(color: AppColors.gray400, fontSize: 11, fontStyle: FontStyle.italic)),
        ],

        if (status == 'pending') ...[
          const SizedBox(height: 10),
          Row(children: [
            Expanded(child: _ActionBtn('Jadwalkan', AppColors.blue600, onSchedule)),
            const SizedBox(width: 6),
            Expanded(child: _ActionBtn('Selesai', AppColors.green600, onComplete)),
            const SizedBox(width: 6),
            _ActionBtn('Tolak', AppColors.gray200, onCancel, textColor: AppColors.gray600),
          ]),
        ],
        if (status == 'scheduled') ...[
          const SizedBox(height: 10),
          Row(children: [
            Expanded(child: _ActionBtn('Isi Jurnal', AppColors.green600, onComplete)),
            const SizedBox(width: 6),
            _ActionBtn('Batalkan', AppColors.gray200, onCancel, textColor: AppColors.gray600),
          ]),
        ],
      ]),
    );
  }
}

class _ActionBtn extends StatelessWidget {
  final String label;
  final Color bgColor;
  final VoidCallback onTap;
  final Color? textColor;
  const _ActionBtn(this.label, this.bgColor, this.onTap, {this.textColor});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8),
        decoration: BoxDecoration(color: bgColor, borderRadius: BorderRadius.circular(10)),
        alignment: Alignment.center,
        child: Text(label,
          style: TextStyle(
            color: textColor ?? Colors.white,
            fontSize: 11, fontWeight: FontWeight.w700)),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────

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
