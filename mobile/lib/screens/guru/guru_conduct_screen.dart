import 'package:flutter/material.dart';
import '../../models/guru_models.dart';
import '../../services/guru_service.dart';
import '../../theme/app_colors.dart';
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
  List<GuruConductStudent>? _students;
  bool _loading = true;
  String? _error;
  String _search = '';

  @override
  void initState() {
    super.initState();
    _classId = widget.initialClassId ?? (widget.classes.isNotEmpty ? widget.classes.first.id : 0);
    _load();
  }

  Future<void> _load() async {
    if (widget.classes.isEmpty) return;
    setState(() { _loading = true; _error = null; });
    try {
      final data = await GuruService.getConduct(_classId);
      if (mounted) setState(() { _students = data; _loading = false; });
    } catch (e) {
      if (mounted) setState(() { _error = e.toString(); _loading = false; });
    }
  }

  List<GuruConductStudent> get _filtered {
    final list = _students ?? [];
    if (_search.isEmpty) return list;
    final q = _search.toLowerCase();
    return list.where((s) => s.name.toLowerCase().contains(q)).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(title: const Text('Catatan Perilaku Siswa')),
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
            classes: widget.classes.map((c) => (id: c.id, name: c.name)).toList(),
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
    if (filtered.isEmpty) return const EmptyState(message: 'Tidak ada siswa ditemukan');

    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _buildSummary(),
          const SizedBox(height: 12),
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

    return Row(
      children: [
        Expanded(
          child: Container(
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
                const Text('Catatan Negatif', style: TextStyle(fontSize: 11, color: AppColors.gray500)),
                const SizedBox(height: 4),
                Text(
                  '$totalPelanggaran',
                  style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: AppColors.red500),
                ),
                const Text('total catatan', style: TextStyle(fontSize: 10, color: AppColors.gray400)),
              ],
            ),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Container(
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
                const Text('Catatan Positif', style: TextStyle(fontSize: 11, color: AppColors.gray500)),
                const SizedBox(height: 4),
                Text(
                  '$totalPrestasi',
                  style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: AppColors.green600),
                ),
                const Text('total catatan', style: TextStyle(fontSize: 10, color: AppColors.gray400)),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildRow(GuruConductStudent s, int i, int total) {
    return Container(
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
                Container(
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
                const SizedBox(width: 6),
              ],
              if (s.prestasiCount > 0)
                Container(
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
              if (s.pelanggaranCount == 0 && s.prestasiCount == 0)
                const Text('—', style: TextStyle(fontSize: 13, color: AppColors.gray300)),
            ],
          ),
        ],
      ),
    );
  }
}
