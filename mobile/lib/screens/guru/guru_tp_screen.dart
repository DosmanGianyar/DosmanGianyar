import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/guru_models.dart';
import '../../models/user.dart';
import '../../providers/auth_provider.dart';
import '../../services/guru_service.dart';
import '../../theme/app_colors.dart';
import 'widgets/guru_widgets.dart';

class GuruTpScreen extends StatefulWidget {
  const GuruTpScreen({super.key});

  @override
  State<GuruTpScreen> createState() => _GuruTpScreenState();
}

class _GuruTpScreenState extends State<GuruTpScreen> {
  List<TujuanPembelajaran> _tpList = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final list = await GuruService.getTpList();
      if (mounted) setState(() { _tpList = list; _loading = false; });
    } catch (e) {
      if (mounted) setState(() { _error = e.toString(); _loading = false; });
    }
  }

  List<SubjectRef> get _mySubjects =>
      context.read<AuthProvider>().user?.subjects ?? [];

  Future<void> _showForm({TujuanPembelajaran? existing}) async {
    final result = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _TpFormSheet(existing: existing, subjects: _mySubjects),
    );
    if (result == true) _load();
  }

  Future<void> _toggleActive(TujuanPembelajaran tp) async {
    try {
      final updated = await GuruService.toggleTp(tp.id);
      if (mounted) {
        final idx = _tpList.indexWhere((t) => t.id == tp.id);
        if (idx >= 0) setState(() => _tpList[idx] = updated);
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(updated.isActive ? 'TP diaktifkan' : 'TP dinonaktifkan'),
          backgroundColor: updated.isActive ? AppColors.emerald600 : AppColors.gray500,
          behavior: SnackBarBehavior.floating,
        ));
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(e.toString()), backgroundColor: AppColors.red500,
          behavior: SnackBarBehavior.floating));
      }
    }
  }

  Future<void> _confirmDelete(TujuanPembelajaran tp) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Hapus TP?'),
        content: Text('Hapus "${tp.displayLabel}"?'),
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
    if (ok != true) return;
    try {
      await GuruService.deleteTp(tp.id);
      _load();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(e.toString()), backgroundColor: AppColors.red500,
          behavior: SnackBarBehavior.floating));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Tujuan Pembelajaran (TP)'),
        actions: [
          IconButton(
            icon: const Icon(Icons.add_rounded),
            tooltip: 'Tambah TP',
            onPressed: () => _showForm(),
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? ErrorRetry(onRetry: _load)
              : _tpList.isEmpty
                  ? EmptyState(
                      message: 'Belum ada TP\nKetuk + untuk menambah',
                      icon: Icons.checklist_rounded,
                      action: TextButton.icon(
                        onPressed: () => _showForm(),
                        icon: const Icon(Icons.add),
                        label: const Text('Tambah TP'),
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: _load,
                      child: ListView.separated(
                        padding: const EdgeInsets.all(16),
                        itemCount: _tpList.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 8),
                        itemBuilder: (_, i) => _TpCard(
                          tp: _tpList[i],
                          onToggle: () => _toggleActive(_tpList[i]),
                          onEdit:   () => _showForm(existing: _tpList[i]),
                          onDelete: () => _confirmDelete(_tpList[i]),
                        ),
                      ),
                    ),
    );
  }
}

// ─── TP Card ──────────────────────────────────────────────────────────────────

class _TpCard extends StatelessWidget {
  final TujuanPembelajaran tp;
  final VoidCallback onToggle;
  final VoidCallback onEdit;
  final VoidCallback onDelete;
  const _TpCard({required this.tp, required this.onToggle, required this.onEdit, required this.onDelete});

  @override
  Widget build(BuildContext context) {
    final active      = tp.isActive;
    final accentColor = !active
        ? AppColors.gray400
        : (tp.isMine ? AppColors.blue600 : AppColors.emerald600);
    final bgColor     = !active
        ? AppColors.gray100
        : (tp.isMine ? AppColors.blue50  : const Color(0xFFECFDF5));

    return Opacity(
      opacity: active ? 1.0 : 0.65,
      child: Container(
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(AppRadius.xl),
          border: Border.all(color: active ? AppColors.gray100 : AppColors.gray200),
          boxShadow: active ? AppShadow.sm : [],
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(AppRadius.xl),
          child: IntrinsicHeight(
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Container(width: 4, color: accentColor),
                Expanded(
                  child: Padding(
                    padding: const EdgeInsets.fromLTRB(12, 12, 8, 12),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          width: 34, height: 34,
                          decoration: BoxDecoration(color: bgColor, borderRadius: BorderRadius.circular(8)),
                          child: Icon(Icons.checklist_rounded, size: 17, color: accentColor),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // Badge row
                              Wrap(
                                spacing: 5, runSpacing: 4,
                                children: [
                                  if (tp.code != null && tp.code!.isNotEmpty)
                                    _badge(tp.code!, tp.isMine ? AppColors.blue100 : const Color(0xFFD1FAE5), accentColor),
                                  _badge(
                                    tp.isMine ? 'Milik Saya' : 'Dibagikan',
                                    active
                                        ? (tp.isMine ? AppColors.blue50 : const Color(0xFFECFDF5))
                                        : AppColors.gray100,
                                    accentColor,
                                  ),
                                  _badge(
                                    active ? 'Aktif' : 'Nonaktif',
                                    active ? const Color(0xFFD1FAE5) : AppColors.gray100,
                                    active ? AppColors.emerald600 : AppColors.gray500,
                                  ),
                                ],
                              ),
                              const SizedBox(height: 5),
                              Text(tp.description, style: TextStyle(
                                fontSize: 13,
                                color: active ? AppColors.gray700 : AppColors.gray400,
                                decoration: active ? null : TextDecoration.lineThrough,
                              )),
                              const SizedBox(height: 4),
                              Row(
                                children: [
                                  if (tp.subjectName != null && tp.subjectName!.isNotEmpty)
                                    Text(tp.subjectName!, style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
                                  if (!tp.isMine && tp.teacherName != null) ...[
                                    const Text(' · ', style: TextStyle(color: AppColors.gray300, fontSize: 11)),
                                    Text(tp.teacherName!, style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
                                  ],
                                ],
                              ),
                            ],
                          ),
                        ),
                        if (tp.isMine)
                          Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              // Tombol aktif/nonaktif
                              GestureDetector(
                                onTap: onToggle,
                                child: Padding(
                                  padding: const EdgeInsets.all(6),
                                  child: Icon(
                                    active ? Icons.toggle_on_rounded : Icons.toggle_off_rounded,
                                    size: 22,
                                    color: active ? AppColors.emerald600 : AppColors.gray400,
                                  ),
                                ),
                              ),
                              GestureDetector(
                                onTap: onEdit,
                                child: const Padding(padding: EdgeInsets.all(6),
                                  child: Icon(Icons.edit_outlined, size: 17, color: AppColors.gray400)),
                              ),
                              GestureDetector(
                                onTap: onDelete,
                                child: const Padding(padding: EdgeInsets.all(6),
                                  child: Icon(Icons.delete_outline_rounded, size: 17, color: AppColors.red500)),
                              ),
                            ],
                          ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _badge(String text, Color bg, Color fg) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
    decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(4)),
    child: Text(text, style: TextStyle(fontSize: 9, fontWeight: FontWeight.w700, color: fg)),
  );
}

// ─── TP Form Sheet ────────────────────────────────────────────────────────────

class _TpFormSheet extends StatefulWidget {
  final TujuanPembelajaran? existing;
  final List<SubjectRef> subjects;
  const _TpFormSheet({this.existing, required this.subjects});

  @override
  State<_TpFormSheet> createState() => _TpFormSheetState();
}

class _TpFormSheetState extends State<_TpFormSheet> {
  late final TextEditingController _codeCtrl;
  late final TextEditingController _descCtrl;
  int? _selectedSubjectId;
  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    _codeCtrl = TextEditingController(text: widget.existing?.code ?? '');
    _descCtrl = TextEditingController(text: widget.existing?.description ?? '');
    _selectedSubjectId = widget.existing?.subjectId ??
        (widget.subjects.isNotEmpty ? widget.subjects.first.id : null);
  }

  @override
  void dispose() {
    _codeCtrl.dispose();
    _descCtrl.dispose();
    super.dispose();
  }

  void _snack(String msg, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg), backgroundColor: color, behavior: SnackBarBehavior.floating));
  }

  Future<void> _submit() async {
    if (_selectedSubjectId == null) { _snack('Pilih mata pelajaran', AppColors.orange500); return; }
    if (_descCtrl.text.trim().isEmpty) { _snack('Deskripsi TP wajib diisi', AppColors.orange500); return; }
    setState(() => _submitting = true);
    try {
      if (widget.existing != null) {
        await GuruService.updateTp(
          id:          widget.existing!.id,
          subjectId:   _selectedSubjectId!,
          code:        _codeCtrl.text.trim().isEmpty ? null : _codeCtrl.text.trim(),
          description: _descCtrl.text.trim(),
        );
      } else {
        await GuruService.createTp(
          subjectId:   _selectedSubjectId!,
          code:        _codeCtrl.text.trim().isEmpty ? null : _codeCtrl.text.trim(),
          description: _descCtrl.text.trim(),
        );
      }
      if (mounted) Navigator.pop(context, true);
    } catch (e) {
      if (mounted) _snack(e.toString(), AppColors.red500);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
      child: Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
        ),
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(child: Container(width: 36, height: 4,
              decoration: BoxDecoration(color: AppColors.gray300, borderRadius: BorderRadius.circular(2)))),
            const SizedBox(height: 16),
            Text(
              widget.existing != null ? 'Edit TP' : 'Tambah TP Baru',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.gray800),
            ),
            const SizedBox(height: 16),

            // Mata Pelajaran
            const Text('Mata Pelajaran *', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.gray600)),
            const SizedBox(height: 6),
            if (widget.subjects.isEmpty)
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(color: AppColors.orange50, borderRadius: BorderRadius.circular(8), border: Border.all(color: AppColors.orange100)),
                child: const Text('Belum ada mata pelajaran. Hubungi admin untuk menambahkan mata pelajaran Anda.',
                  style: TextStyle(fontSize: 12, color: AppColors.orange600)),
              )
            else
              Wrap(
                spacing: 6, runSpacing: 6,
                children: widget.subjects.map((s) {
                  final sel = _selectedSubjectId == s.id;
                  return GestureDetector(
                    onTap: () => setState(() => _selectedSubjectId = s.id),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
                      decoration: BoxDecoration(
                        color: sel ? AppColors.blue600 : AppColors.gray50,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: sel ? AppColors.blue600 : AppColors.gray200),
                      ),
                      child: Text(s.name, style: TextStyle(
                        fontSize: 12, fontWeight: FontWeight.w600,
                        color: sel ? Colors.white : AppColors.gray700)),
                    ),
                  );
                }).toList(),
              ),
            const SizedBox(height: 12),

            // Kode TP
            const Text('Kode TP (opsional)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.gray600)),
            const SizedBox(height: 6),
            TextField(controller: _codeCtrl, decoration: _inputDeco('Contoh: TP 1.1'),
              style: const TextStyle(fontSize: 13)),
            const SizedBox(height: 12),

            // Deskripsi
            const Text('Deskripsi Tujuan Pembelajaran *', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.gray600)),
            const SizedBox(height: 6),
            TextField(controller: _descCtrl, maxLines: 4, decoration: _inputDeco('Peserta didik mampu...'),
              style: const TextStyle(fontSize: 13)),
            const SizedBox(height: 20),

            SizedBox(
              width: double.infinity,
              child: FilledButton(
                onPressed: _submitting ? null : _submit,
                style: FilledButton.styleFrom(
                  backgroundColor: AppColors.blue600,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                child: _submitting
                    ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : Text(widget.existing != null ? 'Simpan Perubahan' : 'Tambah TP'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  InputDecoration _inputDeco(String hint) => InputDecoration(
    hintText: hint,
    hintStyle: const TextStyle(color: AppColors.gray400, fontSize: 13),
    filled: true, fillColor: AppColors.gray50,
    contentPadding: const EdgeInsets.all(12),
    border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: AppColors.gray200)),
    enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: AppColors.gray200)),
    focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: AppColors.blue600, width: 1.5)),
  );
}

// ─── Reusable TP Picker (dipakai di form jurnal) ──────────────────────────────

class TpPickerSheet extends StatefulWidget {
  final int? currentTpId;
  final int? subjectId;
  const TpPickerSheet({super.key, this.currentTpId, this.subjectId});

  @override
  State<TpPickerSheet> createState() => _TpPickerSheetState();
}

class _TpPickerSheetState extends State<TpPickerSheet> {
  List<TujuanPembelajaran> _list = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final list = await GuruService.getTpList(subjectId: widget.subjectId);
      // Picker hanya menampilkan TP yang aktif
      if (mounted) setState(() { _list = list.where((t) => t.isActive).toList(); _loading = false; });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _addNew() async {
    final subjects = context.read<AuthProvider>().user?.subjects ?? <SubjectRef>[];
    final result = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _TpFormSheet(subjects: subjects),
    );
    if (result == true) _load();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * .7),
      child: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 14, 12, 8),
            child: Row(
              children: [
                const Text('Pilih Tujuan Pembelajaran',
                    style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.gray800)),
                const Spacer(),
                IconButton(
                  icon: const Icon(Icons.add_rounded, color: AppColors.blue600),
                  tooltip: 'Tambah TP Baru',
                  onPressed: _addNew,
                ),
              ],
            ),
          ),
          const Divider(height: 1, color: AppColors.gray100),
          if (_loading)
            const Padding(padding: EdgeInsets.all(32), child: CircularProgressIndicator())
          else if (_list.isEmpty)
            Padding(
              padding: const EdgeInsets.all(32),
              child: Column(
                children: [
                  const Icon(Icons.checklist_rounded, size: 40, color: AppColors.gray300),
                  const SizedBox(height: 12),
                  const Text('Belum ada TP', style: TextStyle(color: AppColors.gray400, fontSize: 13)),
                  const SizedBox(height: 12),
                  TextButton.icon(
                    onPressed: _addNew,
                    icon: const Icon(Icons.add),
                    label: const Text('Tambah TP Baru'),
                  ),
                ],
              ),
            )
          else
            Expanded(
              child: ListView.separated(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
                itemCount: _list.length,
                separatorBuilder: (_, __) => const SizedBox(height: 6),
                itemBuilder: (_, i) {
                  final tp = _list[i];
                  final selected = tp.id == widget.currentTpId;
                  return GestureDetector(
                    onTap: () => Navigator.pop(context, tp),
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: selected ? AppColors.blue50 : AppColors.gray50,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(
                          color: selected ? AppColors.blue400 : AppColors.gray200,
                          width: selected ? 1.5 : 1,
                        ),
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                if (tp.code != null && tp.code!.isNotEmpty) ...[
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                    decoration: BoxDecoration(
                                      color: selected ? AppColors.blue200 : AppColors.gray200,
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                    child: Text(tp.code!, style: TextStyle(
                                      fontSize: 10, fontWeight: FontWeight.w800,
                                      color: selected ? AppColors.blue700 : AppColors.gray600,
                                    )),
                                  ),
                                  const SizedBox(height: 4),
                                ],
                                Text(tp.description, style: TextStyle(
                                  fontSize: 13,
                                  color: selected ? AppColors.blue700 : AppColors.gray700,
                                )),
                              ],
                            ),
                          ),
                          if (selected)
                            const Icon(Icons.check_circle_rounded, color: AppColors.blue600, size: 20),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),
        ],
      ),
    );
  }
}
