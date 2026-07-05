import 'package:flutter/material.dart';
import '../../models/guru_models.dart';
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

  Future<void> _showForm({TujuanPembelajaran? existing}) async {
    final result = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _TpFormSheet(existing: existing),
    );
    if (result == true) _load();
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
                          onEdit: () => _showForm(existing: _tpList[i]),
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
  final VoidCallback onEdit;
  final VoidCallback onDelete;
  const _TpCard({required this.tp, required this.onEdit, required this.onDelete});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppRadius.xl),
        border: Border.all(color: AppColors.gray100),
        boxShadow: AppShadow.sm,
      ),
      child: Padding(
        padding: const EdgeInsets.fromLTRB(14, 12, 8, 12),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 36, height: 36,
              decoration: BoxDecoration(
                color: AppColors.blue50,
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Icon(Icons.checklist_rounded, size: 18, color: AppColors.blue600),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (tp.code != null && tp.code!.isNotEmpty)
                    Container(
                      margin: const EdgeInsets.only(bottom: 4),
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.blue100,
                        borderRadius: BorderRadius.circular(5),
                      ),
                      child: Text(tp.code!, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: AppColors.blue700)),
                    ),
                  Text(tp.description, style: const TextStyle(fontSize: 13, color: AppColors.gray700)),
                  if (tp.subjectName != null && tp.subjectName!.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text(tp.subjectName!, style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
                  ],
                ],
              ),
            ),
            Column(
              children: [
                GestureDetector(
                  onTap: onEdit,
                  child: const Padding(
                    padding: EdgeInsets.all(6),
                    child: Icon(Icons.edit_outlined, size: 18, color: AppColors.gray400),
                  ),
                ),
                GestureDetector(
                  onTap: onDelete,
                  child: const Padding(
                    padding: EdgeInsets.all(6),
                    child: Icon(Icons.delete_outline_rounded, size: 18, color: AppColors.red500),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

// ─── TP Form Sheet ────────────────────────────────────────────────────────────

class _TpFormSheet extends StatefulWidget {
  final TujuanPembelajaran? existing;
  const _TpFormSheet({this.existing});

  @override
  State<_TpFormSheet> createState() => _TpFormSheetState();
}

class _TpFormSheetState extends State<_TpFormSheet> {
  late final TextEditingController _codeCtrl;
  late final TextEditingController _descCtrl;
  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    _codeCtrl = TextEditingController(text: widget.existing?.code ?? '');
    _descCtrl = TextEditingController(text: widget.existing?.description ?? '');
  }

  @override
  void dispose() {
    _codeCtrl.dispose();
    _descCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (_descCtrl.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Deskripsi TP wajib diisi'),
        backgroundColor: AppColors.orange500,
        behavior: SnackBarBehavior.floating,
      ));
      return;
    }
    setState(() => _submitting = true);
    try {
      if (widget.existing != null) {
        await GuruService.updateTp(
          id:          widget.existing!.id,
          code:        _codeCtrl.text.trim().isEmpty ? null : _codeCtrl.text.trim(),
          description: _descCtrl.text.trim(),
        );
      } else {
        await GuruService.createTp(
          code:        _codeCtrl.text.trim().isEmpty ? null : _codeCtrl.text.trim(),
          description: _descCtrl.text.trim(),
        );
      }
      if (mounted) Navigator.pop(context, true);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(e.toString()),
          backgroundColor: AppColors.red500,
          behavior: SnackBarBehavior.floating,
        ));
      }
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
            Center(
              child: Container(width: 36, height: 4,
                decoration: BoxDecoration(color: AppColors.gray300, borderRadius: BorderRadius.circular(2))),
            ),
            const SizedBox(height: 16),
            Text(
              widget.existing != null ? 'Edit TP' : 'Tambah TP Baru',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.gray800),
            ),
            const SizedBox(height: 16),
            const Text('Kode TP (opsional)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.gray600)),
            const SizedBox(height: 6),
            TextField(
              controller: _codeCtrl,
              decoration: _inputDeco('Contoh: TP 1.1'),
              style: const TextStyle(fontSize: 13),
            ),
            const SizedBox(height: 12),
            const Text('Deskripsi Tujuan Pembelajaran *', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.gray600)),
            const SizedBox(height: 6),
            TextField(
              controller: _descCtrl,
              maxLines: 4,
              decoration: _inputDeco('Peserta didik mampu...'),
              style: const TextStyle(fontSize: 13),
            ),
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
    filled: true,
    fillColor: AppColors.gray50,
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
      if (mounted) setState(() { _list = list; _loading = false; });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _addNew() async {
    final result = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => const _TpFormSheet(),
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
