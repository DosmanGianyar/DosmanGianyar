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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Catatan Perilaku Siswa'),
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
                              'Catatan Negatif',
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
                          isNegatifActive ? 'Aktif (Klik utk reset)' : 'Klik filter negatif',
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
                              'Catatan Positif',
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
                          isPositifActive ? 'Aktif (Klik utk reset)' : 'Klik filter positif',
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
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: _filterType == 'pelanggaran' ? AppColors.red50 : AppColors.green50,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: _filterType == 'pelanggaran' ? AppColors.red100 : AppColors.green100,
              ),
            ),
            child: Row(
              children: [
                Icon(
                  _filterType == 'pelanggaran' ? Icons.warning_amber_rounded : Icons.emoji_events_rounded,
                  size: 14,
                  color: _filterType == 'pelanggaran' ? AppColors.red500 : AppColors.green600,
                ),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    _filterType == 'pelanggaran'
                        ? 'Menampilkan hanya Catatan Negatif (${_filtered.length} siswa)'
                        : 'Menampilkan hanya Catatan Positif (${_filtered.length} siswa)',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: _filterType == 'pelanggaran' ? AppColors.red600 : AppColors.green800,
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
                        color: _filterType == 'pelanggaran' ? AppColors.red200 : AppColors.green200,
                      ),
                    ),
                    child: Icon(
                      Icons.close_rounded,
                      size: 12,
                      color: _filterType == 'pelanggaran' ? AppColors.red600 : AppColors.green800,
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
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => GuruConductInputScreen(
              initialTab: 2,
              initialFilter: _filterType != 'all' ? _filterType : null,
            ),
          ),
        ).then((_) => _load());
      },
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
                    Text(s.nis!, style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
                ],
              ),
            ),
            Row(
              children: [
                if (s.pelanggaranCount > 0) ...[
                  GestureDetector(
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => const GuruConductInputScreen(
                            initialTab: 2,
                            initialFilter: 'pelanggaran',
                          ),
                        ),
                      ).then((_) => _load());
                    },
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
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => const GuruConductInputScreen(
                            initialTab: 2,
                            initialFilter: 'prestasi',
                          ),
                        ),
                      ).then((_) => _load());
                    },
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
