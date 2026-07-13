import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:dio/dio.dart';
import '../../models/permit.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';

class PermitScreen extends StatefulWidget {
  const PermitScreen({super.key});

  @override
  State<PermitScreen> createState() => _PermitScreenState();
}

class _PermitScreenState extends State<PermitScreen> {
  List<Permit> _permits    = [];
  bool         _isLoading  = true;
  String?      _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _isLoading = true; _error = null; });
    try {
      final body = await ApiClient.get('/permits');
      setState(() {
        _permits = (body['permits'] as List)
            .map((e) => Permit.fromJson(e as Map<String, dynamic>))
            .toList();
      });
    } catch (e) {
      setState(() => _error = ApiClient.extractError(e));
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _delete(Permit p) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Hapus Pengajuan'),
        content: const Text('Yakin ingin menghapus pengajuan ini?'),
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
    if (confirm != true || !mounted) return;
    try {
      await ApiClient.delete('/permits/${p.id}');
      _showSnack('Pengajuan berhasil dihapus.', success: true);
      _load();
    } catch (e) {
      _showSnack(ApiClient.extractError(e));
    }
  }

  void _showSnack(String msg, {bool success = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: success ? AppColors.green500 : AppColors.red500,
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
    ));
  }

  void _openCreate() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _CreatePermitSheet(onCreated: () { Navigator.pop(context); _load(); }),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Izin, Sakit & Dispensasi',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 16)),
        backgroundColor: const Color(0xFF0F2460),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _openCreate,
        backgroundColor: AppColors.blue600,
        icon: const Icon(Icons.add_rounded),
        label: const Text('Buat Pengajuan'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _ErrorView(message: _error!, onRetry: _load)
              : RefreshIndicator(
                  onRefresh: _load,
                  child: _permits.isEmpty
                      ? const _EmptyView()
                      : ListView.separated(
                          padding: EdgeInsets.fromLTRB(16, 16, 16, 100 + MediaQuery.of(context).padding.bottom),
                          itemCount: _permits.length,
                          separatorBuilder: (_, __) => const SizedBox(height: 10),
                          itemBuilder: (_, i) => _PermitCard(
                            permit: _permits[i],
                            onDelete: () => _delete(_permits[i]),
                          ),
                        ),
                ),
    );
  }
}

// ─── Create Sheet ─────────────────────────────────────────────────────────────

class _CreatePermitSheet extends StatefulWidget {
  final VoidCallback onCreated;
  const _CreatePermitSheet({required this.onCreated});

  @override
  State<_CreatePermitSheet> createState() => _CreatePermitSheetState();
}

class _CreatePermitSheetState extends State<_CreatePermitSheet> {
  String        _type       = 'izin';
  DateTime?     _startDate;
  DateTime?     _endDate;
  XFile?        _file;
  final _reasonCtrl = TextEditingController();
  bool _isSaving = false;

  final _types = [
    ('izin',       'Izin',        AppColors.sky500),
    ('sakit',      'Sakit',       AppColors.purple500),
    ('dispensasi', 'Dispensasi',  AppColors.orange500),
  ];

  @override
  void dispose() {
    _reasonCtrl.dispose();
    super.dispose();
  }

  Future<void> _pickDate({required bool isStart}) async {
    final now   = DateTime.now();
    final first = isStart ? now : (_startDate ?? now);
    final picked = await showDatePicker(
      context:      context,
      initialDate:  first,
      firstDate:    now,
      lastDate:     now.add(const Duration(days: 60)),
    );
    if (picked == null || !mounted) return;
    setState(() {
      if (isStart) {
        _startDate = picked;
        if (_endDate != null && _endDate!.isBefore(picked)) _endDate = picked;
      } else {
        _endDate = picked;
      }
    });
  }

  Future<void> _pickFile() async {
    // image_picker menggunakan Android Photo Picker bawaan sistem (Android 13+)
    // atau document picker (≤12) — keduanya tidak memerlukan runtime permission.
    final picker = ImagePicker();
    final file   = await picker.pickImage(
      source:       ImageSource.gallery,
      imageQuality: 85,
    );
    if (file != null && mounted) setState(() => _file = file);
  }

  Future<void> _submit() async {
    if (_startDate == null || _endDate == null) {
      _showSnack('Pilih tanggal mulai dan selesai.');
      return;
    }
    if (_reasonCtrl.text.trim().isEmpty) {
      _showSnack('Alasan tidak boleh kosong.');
      return;
    }
    setState(() => _isSaving = true);
    try {
      final formData = FormData.fromMap({
        'type':       _type,
        'start_date': _fmt(_startDate!),
        'end_date':   _fmt(_endDate!),
        'reason':     _reasonCtrl.text.trim(),
        if (_file != null)
          'file': await MultipartFile.fromFile(_file!.path, filename: 'lampiran.jpg'),
      });
      await ApiClient.postForm('/permits', formData);
      widget.onCreated();
    } catch (e) {
      _showSnack(ApiClient.extractError(e));
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  String _fmt(DateTime d) =>
      '${d.year}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}';

  void _showSnack(String msg) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: AppColors.red500,
      behavior: SnackBarBehavior.floating,
    ));
  }

  @override
  Widget build(BuildContext context) {
    final bottom = MediaQuery.of(context).viewInsets.bottom;
    final safeBot = MediaQuery.of(context).padding.bottom;
    return Container(
      margin: const EdgeInsets.fromLTRB(12, 0, 12, 12),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.all(Radius.circular(20)),
      ),
      padding: EdgeInsets.fromLTRB(20, 20, 20, 20 + bottom + safeBot),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Handle
          Center(child: Container(
            width: 40, height: 4,
            decoration: BoxDecoration(color: AppColors.gray200, borderRadius: BorderRadius.circular(2)),
          )),
          const SizedBox(height: 16),
          const Text('Buat Pengajuan',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.gray800)),
          const SizedBox(height: 16),

          // Type chips
          const Text('Jenis', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.gray600)),
          const SizedBox(height: 8),
          Row(
            children: _types.map((t) {
              final selected = _type == t.$1;
              return Expanded(
                child: Padding(
                  padding: const EdgeInsets.only(right: 6),
                  child: GestureDetector(
                    onTap: () => setState(() => _type = t.$1),
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 10),
                      decoration: BoxDecoration(
                        color:        selected ? t.$3.withValues(alpha: 0.12) : AppColors.gray50,
                        borderRadius: BorderRadius.circular(10),
                        border:       Border.all(
                          color: selected ? t.$3 : AppColors.gray200,
                          width: selected ? 1.5 : 1,
                        ),
                      ),
                      alignment: Alignment.center,
                      child: Text(t.$2,
                        style: TextStyle(
                          fontSize:   12,
                          fontWeight: FontWeight.w600,
                          color:      selected ? t.$3 : AppColors.gray500,
                        ),
                      ),
                    ),
                  ),
                ),
              );
            }).toList(),
          ),
          const SizedBox(height: 14),

          // Date range
          Row(
            children: [
              Expanded(child: _DateField(
                label:     'Tanggal Mulai',
                value:     _startDate,
                onPick:    () => _pickDate(isStart: true),
              )),
              const SizedBox(width: 10),
              Expanded(child: _DateField(
                label:     'Tanggal Selesai',
                value:     _endDate,
                onPick:    () => _pickDate(isStart: false),
              )),
            ],
          ),
          const SizedBox(height: 14),

          // Reason
          const Text('Alasan', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.gray600)),
          const SizedBox(height: 6),
          TextField(
            controller:  _reasonCtrl,
            maxLines:    3,
            maxLength:   500,
            style: const TextStyle(fontSize: 13, color: AppColors.gray700),
            decoration: InputDecoration(
              hintText:       'Tuliskan alasan...',
              hintStyle:      const TextStyle(color: AppColors.gray400, fontSize: 13),
              filled:         true,
              fillColor:      AppColors.gray50,
              counterStyle:   const TextStyle(fontSize: 10, color: AppColors.gray400),
              contentPadding: const EdgeInsets.all(12),
              border: OutlineInputBorder(
                borderRadius: AppRadius.input,
                borderSide: const BorderSide(color: AppColors.gray200),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: AppRadius.input,
                borderSide: const BorderSide(color: AppColors.gray200),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: AppRadius.input,
                borderSide: const BorderSide(color: AppColors.blue600, width: 2),
              ),
            ),
          ),
          const SizedBox(height: 14),

          // Lampiran
          const Text('Lampiran (opsional)',
            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.gray600)),
          const SizedBox(height: 2),
          const Text('Surat dokter / SK kegiatan / dokumen pendukung lainnya',
            style: TextStyle(fontSize: 10, color: AppColors.gray400)),
          const SizedBox(height: 6),
          _AttachmentPickerTile(file: _file, onTap: _pickFile),
          const SizedBox(height: 16),

          FilledButton(
            onPressed: _isSaving ? null : _submit,
            style: FilledButton.styleFrom(
              backgroundColor: AppColors.blue600,
              padding:         const EdgeInsets.symmetric(vertical: 14),
              shape:           RoundedRectangleBorder(borderRadius: AppRadius.button),
            ),
            child: _isSaving
                ? const SizedBox(width: 18, height: 18,
                    child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                : const Text('Kirim Pengajuan',
                    style: TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
          ),
        ],
      ),
    );
  }
}

// ─── Permit Card ──────────────────────────────────────────────────────────────

class _PermitCard extends StatelessWidget {
  final Permit       permit;
  final VoidCallback onDelete;
  const _PermitCard({required this.permit, required this.onDelete});

  String _fmtDate(String s) {
    try {
      final d = DateTime.parse(s);
      const m = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
      return '${d.day} ${m[d.month]} ${d.year}';
    } catch (_) { return s; }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color:        Colors.white,
        borderRadius: AppRadius.card,
        border:       Border.all(color: AppColors.gray100),
        boxShadow:    AppShadow.sm,
      ),
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color:        permit.typeBg,
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(permit.typeLabel,
                style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: permit.typeColor)),
            ),
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color:        permit.statusBg,
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(permit.statusLabel,
                style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: permit.statusColor)),
            ),
            const Spacer(),
            if (permit.isPending)
              IconButton(
                onPressed:  onDelete,
                icon: const Icon(Icons.delete_outline_rounded, size: 18, color: AppColors.red500),
                visualDensity: VisualDensity.compact,
                padding: EdgeInsets.zero,
              ),
          ]),
          const SizedBox(height: 8),
          Row(children: [
            const Icon(Icons.calendar_today_rounded, size: 13, color: AppColors.gray400),
            const SizedBox(width: 4),
            Text('${_fmtDate(permit.startDate)} — ${_fmtDate(permit.endDate)}',
              style: const TextStyle(fontSize: 12, color: AppColors.gray600)),
          ]),
          const SizedBox(height: 4),
          Text(permit.reason,
            style: const TextStyle(fontSize: 12, color: AppColors.gray500),
            maxLines: 2, overflow: TextOverflow.ellipsis),
          if (permit.fileUrl != null) ...[
            const SizedBox(height: 8),
            Row(children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(6),
                child: Image.network(
                  permit.fileUrl!,
                  width: 40, height: 40,
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => Container(
                    width: 40, height: 40,
                    decoration: BoxDecoration(
                      color: AppColors.gray100,
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: const Icon(Icons.insert_drive_file_outlined, size: 18, color: AppColors.gray400),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              const Text('Lampiran terlampir',
                style: TextStyle(fontSize: 11, color: AppColors.gray500)),
            ]),
          ],
          if (permit.rejectionNote != null && permit.rejectionNote!.isNotEmpty) ...[
            const SizedBox(height: 6),
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: AppColors.red100, borderRadius: BorderRadius.circular(8)),
              child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                const Icon(Icons.info_outline_rounded, size: 14, color: AppColors.red500),
                const SizedBox(width: 6),
                Expanded(child: Text(permit.rejectionNote!,
                  style: const TextStyle(fontSize: 11, color: AppColors.red500))),
              ]),
            ),
          ],
        ],
      ),
    );
  }
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

class _DateField extends StatelessWidget {
  final String    label;
  final DateTime? value;
  final VoidCallback onPick;
  const _DateField({required this.label, this.value, required this.onPick});

  String _fmt(DateTime d) {
    const m = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return '${d.day} ${m[d.month]} ${d.year}';
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onPick,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.gray600)),
          const SizedBox(height: 6),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 11),
            decoration: BoxDecoration(
              color: AppColors.gray50,
              borderRadius: AppRadius.input,
              border: Border.all(color: AppColors.gray200),
            ),
            child: Row(children: [
              const Icon(Icons.calendar_today_rounded, size: 14, color: AppColors.gray400),
              const SizedBox(width: 6),
              Text(value != null ? _fmt(value!) : 'Pilih tanggal',
                style: TextStyle(
                  fontSize: 12,
                  color: value != null ? AppColors.gray700 : AppColors.gray400,
                )),
            ]),
          ),
        ],
      ),
    );
  }
}

class _AttachmentPickerTile extends StatelessWidget {
  final XFile?       file;
  final VoidCallback onTap;
  const _AttachmentPickerTile({required this.file, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: AppColors.gray50, borderRadius: AppRadius.input,
          border: Border.all(
            color: file != null ? AppColors.blue600 : AppColors.gray200,
            width: file != null ? 1.5 : 1,
          ),
        ),
        child: file != null
            ? Row(children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(6),
                  child: Image.file(File(file!.path), width: 40, height: 40, fit: BoxFit.cover),
                ),
                const SizedBox(width: 10),
                Expanded(child: Text(file!.name,
                  style: const TextStyle(fontSize: 12, color: AppColors.gray700),
                  overflow: TextOverflow.ellipsis)),
                const Icon(Icons.edit_rounded, size: 14, color: AppColors.gray400),
              ])
            : const Row(children: [
                Icon(Icons.attach_file_rounded, size: 18, color: AppColors.gray400),
                SizedBox(width: 8),
                Text('Pilih foto lampiran', style: TextStyle(fontSize: 12, color: AppColors.gray400)),
              ]),
      ),
    );
  }
}

class _EmptyView extends StatelessWidget {
  const _EmptyView();
  @override
  Widget build(BuildContext context) => const Center(
    child: Column(mainAxisSize: MainAxisSize.min, children: [
      Icon(Icons.description_outlined, size: 56, color: AppColors.gray300),
      SizedBox(height: 12),
      Text('Belum ada pengajuan', style: TextStyle(fontSize: 14, color: AppColors.gray400)),
      SizedBox(height: 4),
      Text('Ketuk + untuk membuat pengajuan baru',
        style: TextStyle(fontSize: 12, color: AppColors.gray300)),
    ]),
  );
}

class _ErrorView extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;
  const _ErrorView({required this.message, required this.onRetry});
  @override
  Widget build(BuildContext context) => Center(
    child: Column(mainAxisSize: MainAxisSize.min, children: [
      const Icon(Icons.error_outline_rounded, size: 48, color: AppColors.red500),
      const SizedBox(height: 8),
      Text(message, style: const TextStyle(fontSize: 13, color: AppColors.gray500), textAlign: TextAlign.center),
      const SizedBox(height: 12),
      TextButton(onPressed: onRetry, child: const Text('Coba Lagi')),
    ]),
  );
}
