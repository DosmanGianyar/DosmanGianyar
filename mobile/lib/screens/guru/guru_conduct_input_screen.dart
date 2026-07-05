import 'package:flutter/material.dart';
import '../../models/guru_models.dart';
import '../../services/guru_service.dart';
import '../../theme/app_colors.dart';

class GuruConductInputScreen extends StatefulWidget {
  const GuruConductInputScreen({super.key});

  @override
  State<GuruConductInputScreen> createState() => _GuruConductInputScreenState();
}

class _GuruConductInputScreenState extends State<GuruConductInputScreen>
    with SingleTickerProviderStateMixin {
  late final TabController _tabCtrl;

  List<ConductCategory> _prestasiCats   = [];
  List<ConductCategory> _pelanggaranCats = [];
  List<SimpleStudent>   _students        = [];
  List<Map<String, dynamic>> _classes    = [];

  bool _loadingCats     = true;
  bool _loadingStudents = false;
  bool _submitting      = false;

  int? _selectedClassId;
  SimpleStudent? _selectedStudent;
  ConductCategory? _selectedCategory;
  final _noteCtrl = TextEditingController();
  final _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 2, vsync: this);
    _tabCtrl.addListener(() {
      if (!_tabCtrl.indexIsChanging) {
        setState(() { _selectedCategory = null; });
      }
    });
    _loadInitial();
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    _noteCtrl.dispose();
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadInitial() async {
    try {
      final results = await Future.wait([
        GuruService.getConductCategories(),
        GuruService.getConductClasses(),
      ]);
      final cats    = results[0] as Map<String, List<ConductCategory>>;
      final classes = results[1] as List<Map<String, dynamic>>;
      if (mounted) {
        setState(() {
          _prestasiCats    = cats['prestasi']    ?? [];
          _pelanggaranCats = cats['pelanggaran'] ?? [];
          _classes         = classes;
          _loadingCats     = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loadingCats = false);
    }
  }

  Future<void> _loadStudents() async {
    setState(() { _loadingStudents = true; _students = []; _selectedStudent = null; });
    try {
      final list = await GuruService.getConductStudents(
        classId: _selectedClassId,
        q: _searchCtrl.text.trim().isEmpty ? null : _searchCtrl.text.trim(),
      );
      if (mounted) setState(() { _students = list; _loadingStudents = false; });
    } catch (_) {
      if (mounted) setState(() => _loadingStudents = false);
    }
  }

  Future<void> _submit() async {
    if (_selectedStudent == null) {
      _showSnack('Pilih siswa terlebih dahulu', AppColors.orange500);
      return;
    }
    if (_selectedCategory == null) {
      _showSnack('Pilih kategori', AppColors.orange500);
      return;
    }

    setState(() => _submitting = true);
    try {
      final msg = await GuruService.createConductLog(
        studentId:  _selectedStudent!.id,
        categoryId: _selectedCategory!.id,
        note:       _noteCtrl.text.trim().isEmpty ? null : _noteCtrl.text.trim(),
      );
      if (mounted) {
        _showSnack(msg, AppColors.emerald600);
        setState(() {
          _selectedStudent  = null;
          _selectedCategory = null;
          _noteCtrl.clear();
        });
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
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Catat Pelanggaran / Prestasi'),
        bottom: TabBar(
          controller: _tabCtrl,
          tabs: const [
            Tab(text: 'Pelanggaran'),
            Tab(text: 'Prestasi'),
          ],
          labelColor: AppColors.blue600,
          indicatorColor: AppColors.blue600,
          unselectedLabelColor: AppColors.gray400,
        ),
      ),
      body: _loadingCats
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                Expanded(
                  child: TabBarView(
                    controller: _tabCtrl,
                    children: [
                      _buildForm(isPrestasi: false),
                      _buildForm(isPrestasi: true),
                    ],
                  ),
                ),
              ],
            ),
    );
  }

  Widget _buildForm({required bool isPrestasi}) {
    final categories = isPrestasi ? _prestasiCats : _pelanggaranCats;
    final accentColor = isPrestasi ? AppColors.emerald600 : AppColors.red500;
    final bgColor     = isPrestasi ? AppColors.emerald50  : AppColors.red50;

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ── Pilih Kelas ────────────────────────────────────────────
          _sectionLabel('1. Pilih Kelas'),
          const SizedBox(height: 8),
          _ClassDropdown(
            classes:  _classes,
            selected: _selectedClassId,
            onChanged: (id) {
              setState(() { _selectedClassId = id; _selectedStudent = null; });
              _loadStudents();
            },
          ),
          const SizedBox(height: 16),

          // ── Cari Siswa ─────────────────────────────────────────────
          _sectionLabel('2. Pilih Siswa'),
          const SizedBox(height: 8),
          Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _searchCtrl,
                  decoration: InputDecoration(
                    hintText: 'Cari nama / NIS...',
                    prefixIcon: const Icon(Icons.search, size: 18, color: AppColors.gray400),
                    filled: true,
                    fillColor: AppColors.white,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: const BorderSide(color: AppColors.gray200),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: const BorderSide(color: AppColors.gray200),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: const BorderSide(color: AppColors.blue600, width: 1.5),
                    ),
                  ),
                  onSubmitted: (_) => _loadStudents(),
                ),
              ),
              const SizedBox(width: 8),
              ElevatedButton(
                onPressed: _loadStudents,
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
            _SelectedChip(
              label: '${_selectedStudent!.name} (${_selectedStudent!.nis ?? '—'})',
              onRemove: () => setState(() => _selectedStudent = null),
            )
          else if (_loadingStudents)
            const Center(child: Padding(
              padding: EdgeInsets.all(16),
              child: CircularProgressIndicator(strokeWidth: 2),
            ))
          else if (_students.isNotEmpty)
            Container(
              height: 180,
              decoration: BoxDecoration(
                color: AppColors.white,
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
                    subtitle: Text(
                      '${s.nis ?? '—'} · ${s.className ?? ''}',
                      style: const TextStyle(fontSize: 11, color: AppColors.gray500),
                    ),
                    onTap: () => setState(() {
                      _selectedStudent = s;
                      _students        = [];
                    }),
                  );
                },
              ),
            ),
          const SizedBox(height: 16),

          // ── Pilih Kategori ─────────────────────────────────────────
          _sectionLabel('3. Pilih Kategori ${isPrestasi ? "Prestasi" : "Pelanggaran"}'),
          const SizedBox(height: 8),
          if (categories.isEmpty)
            const Text('Tidak ada kategori.', style: TextStyle(color: AppColors.gray400, fontSize: 13))
          else
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: categories.map((cat) {
                final selected = _selectedCategory?.id == cat.id;
                return GestureDetector(
                  onTap: () => setState(() => _selectedCategory = cat),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    decoration: BoxDecoration(
                      color: selected ? accentColor : AppColors.white,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(
                        color: selected ? accentColor : AppColors.gray200,
                      ),
                    ),
                    child: Text(
                      cat.name,
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                        color: selected ? Colors.white : AppColors.gray700,
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
          const SizedBox(height: 16),

          // ── Catatan ────────────────────────────────────────────────
          _sectionLabel('4. Catatan (opsional)'),
          const SizedBox(height: 8),
          TextField(
            controller: _noteCtrl,
            maxLines: 3,
            decoration: InputDecoration(
              hintText: 'Deskripsi tambahan...',
              filled: true,
              fillColor: AppColors.white,
              contentPadding: const EdgeInsets.all(12),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(10),
                borderSide: const BorderSide(color: AppColors.gray200),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(10),
                borderSide: const BorderSide(color: AppColors.gray200),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(10),
                borderSide: const BorderSide(color: AppColors.blue600, width: 1.5),
              ),
            ),
          ),
          const SizedBox(height: 24),

          // ── Preview ────────────────────────────────────────────────
          if (_selectedStudent != null && _selectedCategory != null)
            Container(
              padding: const EdgeInsets.all(14),
              margin: const EdgeInsets.only(bottom: 16),
              decoration: BoxDecoration(
                color: bgColor,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: accentColor.withValues(alpha: 0.3)),
              ),
              child: Row(
                children: [
                  Icon(
                    isPrestasi ? Icons.star_rounded : Icons.warning_rounded,
                    color: accentColor, size: 20,
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          _selectedStudent!.name,
                          style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: accentColor),
                        ),
                        Text(
                          _selectedCategory!.name,
                          style: const TextStyle(fontSize: 12, color: AppColors.gray700),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

          // ── Tombol Simpan ──────────────────────────────────────────
          SizedBox(
            width: double.infinity,
            child: FilledButton.icon(
              onPressed: _submitting ? null : _submit,
              icon: _submitting
                  ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : Icon(isPrestasi ? Icons.star_rounded : Icons.warning_rounded, size: 18),
              label: Text(
                _submitting ? 'Menyimpan...' : 'Simpan ${isPrestasi ? "Prestasi" : "Pelanggaran"}',
              ),
              style: FilledButton.styleFrom(
                backgroundColor: accentColor,
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

  Widget _sectionLabel(String text) => Text(
    text,
    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.gray700),
  );
}

class _ClassDropdown extends StatelessWidget {
  final List<Map<String, dynamic>> classes;
  final int? selected;
  final void Function(int?) onChanged;

  const _ClassDropdown({required this.classes, required this.selected, required this.onChanged});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: AppColors.gray200),
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<int?>(
          value: selected,
          isExpanded: true,
          hint: const Text('Semua kelas', style: TextStyle(fontSize: 13, color: AppColors.gray400)),
          items: [
            const DropdownMenuItem<int?>(value: null, child: Text('Semua kelas', style: TextStyle(fontSize: 13))),
            ...classes.map((c) => DropdownMenuItem<int?>(
              value: c['id'] as int,
              child: Text(c['name'] as String, style: const TextStyle(fontSize: 13)),
            )),
          ],
          onChanged: onChanged,
        ),
      ),
    );
  }
}

class _SelectedChip extends StatelessWidget {
  final String label;
  final VoidCallback onRemove;

  const _SelectedChip({required this.label, required this.onRemove});

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
          Expanded(
            child: Text(label,
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.blue600)),
          ),
          GestureDetector(
            onTap: onRemove,
            child: const Icon(Icons.close_rounded, size: 16, color: AppColors.blue600),
          ),
        ],
      ),
    );
  }
}
