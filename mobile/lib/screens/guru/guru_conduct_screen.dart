import 'package:flutter/material.dart';
import '../../models/guru_models.dart';
import '../../services/guru_service.dart';
import '../../theme/app_colors.dart';
import 'guru_conduct_input_screen.dart';
import 'widgets/guru_widgets.dart';

class GuruConductScreen extends StatefulWidget {
  final List<GuruClass> classes;
  final int? initialClassId;

  const GuruConductScreen({
    super.key,
    required this.classes,
    this.initialClassId,
  });

  @override
  State<GuruConductScreen> createState() => _GuruConductScreenState();
}

class _GuruConductScreenState extends State<GuruConductScreen> {
  late int _classId;
  late List<GuruClass> _classes;
  List<GuruConductStudent>? _students;
  bool _loading = true;
  String? _error;
  String _search = '';
  String _filterType = 'all'; // 'all', 'pelanggaran', 'prestasi'

  @override
  void initState() {
    super.initState();
    _classes = List.from(widget.classes);
    _classId = widget.initialClassId ?? (_classes.isNotEmpty ? _classes.first.id : 0);
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      if (_classes.isEmpty) {
        final freshClasses = await GuruService.getClasses();
        if (freshClasses.isNotEmpty) {
          _classes = freshClasses;
          if (!_classes.any((c) => c.id == _classId)) {
            _classId = _classes.first.id;
          }
        }
      }
    } catch (_) {}

    if (_classes.isEmpty) {
      if (mounted) setState(() { _loading = false; });
      return;
    }

    try {
      final data = await GuruService.getConduct(_classId);
      if (mounted) setState(() { _students = data; _loading = false; });
    } catch (e) {
      if (mounted) setState(() { _error = e.toString(); _loading = false; });
    }
  }

  List<GuruConductStudent> get _filtered {
    var list = _students ?? [];

    if (_filterType == 'pelanggaran') {
      list = list.where((s) => s.pelanggaranCount > 0).toList();
    } else if (_filterType == 'prestasi') {
      list = list.where((s) => s.prestasiCount > 0).toList();
    }

    if (_search.isNotEmpty) {
      final q = _search.toLowerCase();
      list = list.where((s) => s.name.toLowerCase().contains(q)).toList();
    }
    return list;
  }

  void _showStudentDetail(GuruConductStudent student, String? initialType) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _StudentConductDetailSheet(
        student: student,
        initialFilter: initialType ?? (_filterType != 'all' ? _filterType : 'all'),
      ),
    ).then((_) => _load());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('SIPINTER (Pendidikan Karakter)'),
        actions: [
          IconButton(
            icon: const Icon(Icons.add_rounded),
            tooltip: 'Tambah Catatan',
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const GuruConductInputScreen()),
              ).then((_) => _load());
            },
          ),
        ],
      ),
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
            classes: _classes.map((c) => (id: c.id, name: c.name)).toList(),
            selectedId: _classId,
            onChanged: (id) { _classId = id; _load(); },
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: TextField(
              onChanged: (v) => setState(() => _search = v),
              decoration: InputDecoration(
                hintText: 'Cari nama siswa...',
                hintStyle: const TextStyle(fontSize: 13, color: AppColors.gray400),
                prefixIcon: const Icon(Icons.search, size: 18, color: AppColors.gray400),
                contentPadding: const EdgeInsets.symmetric(vertical: 10),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
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

    final filtered = _filtered;

    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _buildSummary(),
          const SizedBox(height: 12),
          if (filtered.isEmpty)
            Container(
              padding: const EdgeInsets.all(32),
              decoration: BoxDecoration(
                color: AppColors.white,
                borderRadius: BorderRadius.circular(AppRadius.xl2),
              ),
              child: Center(
                child: Text(
                  _filterType == 'pelanggaran'
                      ? 'Tidak ada siswa dengan catatan negatif'
                      : _filterType == 'prestasi'
                          ? 'Tidak ada siswa dengan catatan positif'
                          : 'Tidak ada siswa ditemukan',
                  style: const TextStyle(fontSize: 13, color: AppColors.gray400),
                ),
              ),
            )
          else
            Container(
              decoration: BoxDecoration(
                color: AppColors.white,
                borderRadius: BorderRadius.circular(AppRadius.xl2),
                border: Border.all(color: AppColors.gray100),
                boxShadow: AppShadow.sm,
              ),
              child: Column(
                children: filtered.asMap().entries.map((entry) {
                  final i = entry.key;
                  final s = entry.value;
                  return _buildRow(s, i, filtered.length);
                }).toList(),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildSummary() {
    final students = _students ?? [];
    final totalPelanggaran = students.fold(0, (sum, s) => sum + s.pelanggaranCount);
    final totalPrestasi    = students.fold(0, (sum, s) => sum + s.prestasiCount);

    final isNegatifActive = _filterType == 'pelanggaran';
    final isPositifActive = _filterType == 'prestasi';

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Expanded(
              child: Material(
                color: Colors.transparent,
                child: InkWell(
                  onTap: () {
                    setState(() {
                      _filterType = isNegatifActive ? 'all' : 'pelanggaran';
                    });
                  },
                  borderRadius: BorderRadius.circular(AppRadius.xl),
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: isNegatifActive ? AppColors.red50 : AppColors.white,
                      borderRadius: BorderRadius.circular(AppRadius.xl),
                      border: Border.all(
                        color: isNegatifActive ? AppColors.red500 : AppColors.gray100,
                        width: isNegatifActive ? 2 : 1,
                      ),
                      boxShadow: AppShadow.sm,
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            const Text(
                              'Kedisiplinan Karakter',
                              style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.gray600),
                            ),
                            if (isNegatifActive) ...[
                              const Spacer(),
                              const Icon(Icons.check_circle_rounded, size: 14, color: AppColors.red500),
                            ],
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '$totalPelanggaran',
                          style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: AppColors.red500),
                        ),
                        Text(
                          isNegatifActive ? 'Aktif (Klik utk reset)' : 'Klik filter kedisiplinan',
                          style: TextStyle(fontSize: 10, color: isNegatifActive ? AppColors.red500 : AppColors.gray400),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Material(
                color: Colors.transparent,
                child: InkWell(
                  onTap: () {
                    setState(() {
                      _filterType = isPositifActive ? 'all' : 'prestasi';
                    });
                  },
                  borderRadius: BorderRadius.circular(AppRadius.xl),
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: isPositifActive ? AppColors.green50 : AppColors.white,
                      borderRadius: BorderRadius.circular(AppRadius.xl),
                      border: Border.all(
                        color: isPositifActive ? AppColors.green600 : AppColors.gray100,
                        width: isPositifActive ? 2 : 1,
                      ),
                      boxShadow: AppShadow.sm,
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            const Text(
                              'Apresiasi Karakter',
                              style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.gray600),
                            ),
                            if (isPositifActive) ...[
                              const Spacer(),
                              const Icon(Icons.check_circle_rounded, size: 14, color: AppColors.green600),
                            ],
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '$totalPrestasi',
                          style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: AppColors.green600),
                        ),
                        Text(
                          isPositifActive ? 'Aktif (Klik utk reset)' : 'Klik filter apresiasi',
                          style: TextStyle(fontSize: 10, color: isPositifActive ? AppColors.green600 : AppColors.gray400),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
        if (_filterType != 'all') ...[
          const SizedBox(height: 10),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
            decoration: BoxDecoration(
              color: _filterType == 'pelanggaran' ? AppColors.red50 : AppColors.green50,
              borderRadius: BorderRadius.circular(8),
              border: Border.all(
                color: _filterType == 'pelanggaran' ? AppColors.red100 : AppColors.green100,
              ),
            ),
            child: Row(
              children: [
                Icon(
                  _filterType == 'pelanggaran' ? Icons.filter_alt_rounded : Icons.filter_alt_rounded,
                  size: 14,
                  color: _filterType == 'pelanggaran' ? AppColors.red500 : AppColors.green600,
                ),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    _filterType == 'pelanggaran'
                        ? 'Menampilkan hanya Kedisiplinan Karakter (${_filtered.length} siswa)'
                        : 'Menampilkan hanya Apresiasi Karakter (${_filtered.length} siswa)',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: _filterType == 'pelanggaran' ? AppColors.red500 : AppColors.green600,
                    ),
                  ),
                ),
                InkWell(
                  onTap: () => setState(() => _filterType = 'all'),
                  child: Container(
                    padding: const EdgeInsets.all(2),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      shape: BoxShape.circle,
                      border: Border.all(
                        color: _filterType == 'pelanggaran' ? AppColors.red300 : AppColors.green100,
                      ),
                    ),
                    child: Icon(
                      Icons.close_rounded,
                      size: 12,
                      color: _filterType == 'pelanggaran' ? AppColors.red500 : AppColors.green600,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ],
    );
  }

  Widget _buildRow(GuruConductStudent s, int i, int total) {
    return InkWell(
      onTap: () => _showStudentDetail(s, null),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        decoration: BoxDecoration(
          border: i < total - 1
              ? const Border(bottom: BorderSide(color: AppColors.gray100, width: 0.5))
              : null,
        ),
        child: Row(
          children: [
            CircleAvatar(
              radius: 18,
              backgroundColor: AppColors.blue100,
              child: Text(
                s.name.isNotEmpty ? s.name[0].toUpperCase() : '?',
                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.blue600),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(s.name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray800)),
                  if (s.nis != null)
                    Text('NIS: ${s.nis}', style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
                ],
              ),
            ),
            Row(
              children: [
                if (s.pelanggaranCount > 0) ...[
                  GestureDetector(
                    onTap: () => _showStudentDetail(s, 'pelanggaran'),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(color: AppColors.red100, borderRadius: BorderRadius.circular(8)),
                      child: Row(children: [
                        const Icon(Icons.warning_amber_rounded, size: 10, color: AppColors.red500),
                        const SizedBox(width: 3),
                        Text(
                          '${s.pelanggaranCount}',
                          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.red500),
                        ),
                      ]),
                    ),
                  ),
                  const SizedBox(width: 6),
                ],
                if (s.prestasiCount > 0)
                  GestureDetector(
                    onTap: () => _showStudentDetail(s, 'prestasi'),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(color: AppColors.green100, borderRadius: BorderRadius.circular(8)),
                      child: Row(children: [
                        const Icon(Icons.emoji_events_rounded, size: 10, color: AppColors.green600),
                        const SizedBox(width: 3),
                        Text(
                          '${s.prestasiCount}',
                          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.green600),
                        ),
                      ]),
                    ),
                  ),
                if (s.pelanggaranCount == 0 && s.prestasiCount == 0)
                  const Text('—', style: TextStyle(fontSize: 13, color: AppColors.gray300)),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Bottom Sheet: Detail & Riwayat Perilaku Per Siswa ────────────────────────

class _StudentConductDetailSheet extends StatefulWidget {
  final GuruConductStudent student;
  final String? initialFilter;

  const _StudentConductDetailSheet({
    required this.student,
    this.initialFilter,
  });

  @override
  State<_StudentConductDetailSheet> createState() => _StudentConductDetailSheetState();
}

class _StudentConductDetailSheetState extends State<_StudentConductDetailSheet> {
  List<ConductHistoryItem> _history = [];
  bool _loading = true;
  String _filter = 'all'; // 'all', 'pelanggaran', 'prestasi'

  @override
  void initState() {
    super.initState();
    _filter = widget.initialFilter ?? 'all';
    _loadHistory();
  }

  Future<void> _loadHistory() async {
    setState(() => _loading = true);
    try {
      final res = await GuruService.getConductHistory(
        studentId: widget.student.id,
        type: _filter != 'all' ? _filter : null,
      );
      final rawList = res['data'] as List<dynamic>? ?? [];
      final items = rawList.map((e) => ConductHistoryItem.fromJson(e as Map<String, dynamic>)).toList();
      if (mounted) {
        setState(() {
          _history = items;
          _loading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final s = widget.student;

    return Container(
      constraints: BoxConstraints(
        maxHeight: MediaQuery.of(context).size.height * 0.88,
      ),
      decoration: const BoxDecoration(
        color: AppColors.slate100,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        children: [
          // Handle drag
          Center(
            child: Container(
              margin: const EdgeInsets.only(top: 10, bottom: 6),
              width: 36,
              height: 4,
              decoration: BoxDecoration(
                color: AppColors.gray300,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),

          // Header Banner dengan Nama Siswa yang Jelas
          Container(
            width: double.infinity,
            margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF1E293B), Color(0xFF334155)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(18),
              boxShadow: [
                BoxShadow(color: Colors.black.withOpacity(0.15), blurRadius: 10, offset: const Offset(0, 4))
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Icon(Icons.remove_red_eye_rounded, size: 14, color: Color(0xFF94A3B8)),
                    const SizedBox(width: 6),
                    const Text(
                      'AMATI CATATAN PERILAKU SISWA',
                      style: TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: Color(0xFF94A3B8), letterSpacing: 0.8),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    CircleAvatar(
                      radius: 20,
                      backgroundColor: AppColors.blue600,
                      child: Text(
                        s.name.isNotEmpty ? s.name[0].toUpperCase() : '?',
                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            s.name,
                            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white),
                          ),
                          if (s.nis != null)
                            Text(
                              'NIS: ${s.nis}',
                              style: const TextStyle(fontSize: 12, color: Color(0xFFCBD5E1)),
                            ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.red.withOpacity(0.2),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.red.withOpacity(0.4)),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.warning_amber_rounded, size: 12, color: Colors.redAccent),
                          const SizedBox(width: 4),
                          Text(
                            'Negatif: ${s.pelanggaranCount}',
                            style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.white),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.green.withOpacity(0.2),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.green.withOpacity(0.4)),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.emoji_events_rounded, size: 12, color: Colors.greenAccent),
                          const SizedBox(width: 4),
                          Text(
                            'Positif: ${s.prestasiCount}',
                            style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.white),
                          ),
                        ],
                      ),
                    ),
                    const Spacer(),
                    ElevatedButton.icon(
                      onPressed: () {
                        Navigator.pop(context);
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => const GuruConductInputScreen(),
                          ),
                        );
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.blue600,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                        minimumSize: Size.zero,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                      icon: const Icon(Icons.add, size: 14),
                      label: const Text('Catat baru', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Filter bar di dalam modal
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Row(
              children: [
                _buildFilterChip('Semua Catatan', 'all'),
                const SizedBox(width: 8),
                _buildFilterChip('Hanya Negatif', 'pelanggaran'),
                const SizedBox(width: 8),
                _buildFilterChip('Hanya Positif', 'prestasi'),
              ],
            ),
          ),

          // Content list histori per siswa
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _history.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.folder_open_rounded, size: 40, color: AppColors.gray300),
                            const SizedBox(height: 8),
                            Text(
                              'Belum ada catatan ${_filter == 'pelanggaran' ? 'negatif' : _filter == 'prestasi' ? 'positif' : ''} untuk ${s.name}',
                              style: const TextStyle(fontSize: 12, color: AppColors.gray400),
                            ),
                          ],
                        ),
                      )
                    : ListView.separated(
                        padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
                        itemCount: _history.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 8),
                        itemBuilder: (ctx, i) {
                          final item = _history[i];
                          return _buildHistoryCard(item);
                        },
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String label, String value) {
    final active = _filter == value;
    return GestureDetector(
      onTap: () {
        if (_filter != value) {
          setState(() {
            _filter = value;
          });
          _loadHistory();
        }
      },
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: active ? AppColors.blue600 : AppColors.white,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: active ? AppColors.blue600 : AppColors.gray200),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: active ? FontWeight.bold : FontWeight.w500,
            color: active ? Colors.white : AppColors.gray600,
          ),
        ),
      ),
    );
  }

  Widget _buildHistoryCard(ConductHistoryItem item) {
    final isNegatif = item.type == 'pelanggaran';
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: isNegatif ? AppColors.red100 : AppColors.green100),
        boxShadow: AppShadow.sm,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: isNegatif ? AppColors.red100 : AppColors.green100,
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Row(
                  children: [
                    Icon(
                      isNegatif ? Icons.warning_amber_rounded : Icons.emoji_events_rounded,
                      size: 12,
                      color: isNegatif ? AppColors.red500 : AppColors.green600,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      isNegatif ? 'Pelanggaran' : 'Prestasi',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: isNegatif ? AppColors.red500 : AppColors.green600,
                      ),
                    ),
                  ],
                ),
              ),
              const Spacer(),
              Text(
                item.dateLabel ?? item.date,
                style: const TextStyle(fontSize: 11, color: AppColors.gray400),
              ),
            ],
          ),
          const SizedBox(height: 8),
          if (isNegatif) ...[
            if (item.severity != null)
              Text(
                'Tingkat: ${item.severity!.toUpperCase()}',
                style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.gray700),
              ),
            if (item.description != null && item.description!.isNotEmpty)
              Text(
                item.description!,
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.gray800),
              ),
          ] else ...[
            if (item.categoryName != null)
              Text(
                item.categoryName!,
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.green600),
              ),
            if (item.lombaName != null)
              Text(
                '${item.lombaRankLabel ?? ''} - ${item.lombaName}',
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.gray800),
              ),
          ],
          if (item.note != null && item.note!.isNotEmpty) ...[
            const SizedBox(height: 4),
            Text(
              'Catatan: ${item.note}',
              style: const TextStyle(fontSize: 11, fontStyle: FontStyle.italic, color: AppColors.gray500),
            ),
          ],
          if (item.teacherName != null) ...[
            const SizedBox(height: 4),
            Text(
              'Dicatat oleh: ${item.teacherName}',
              style: const TextStyle(fontSize: 10, color: AppColors.gray400),
            ),
          ],
        ],
      ),
    );
  }
}
