import 'package:flutter/material.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';

class BkConsultationScreen extends StatefulWidget {
  const BkConsultationScreen({super.key});

  @override
  State<BkConsultationScreen> createState() => _BkConsultationScreenState();
}

class _BkConsultationScreenState extends State<BkConsultationScreen> {
  List<Map<String, dynamic>> _consultations  = [];
  List<Map<String, dynamic>> _bkTeachers     = [];
  Map<String, dynamic>?      _active;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _isLoading = true; _error = null; });
    try {
      final body = await ApiClient.get('/bk-consultations');
      setState(() {
        _consultations = List<Map<String, dynamic>>.from(body['consultations'] ?? []);
        _bkTeachers    = List<Map<String, dynamic>>.from(body['bk_teachers']   ?? []);
        _active        = body['active_consultation'] as Map<String, dynamic>?;
      });
    } catch (e) {
      setState(() => _error = ApiClient.extractError(e));
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _submit({required int teacherId, required String topic, String? note}) async {
    try {
      await ApiClient.post('/bk-consultations', data: {
        'teacher_id': teacherId,
        'topic': topic,
        if (note != null && note.isNotEmpty) 'student_note': note,
      });
      if (mounted) {
        _showSnack('Pengajuan bimbingan BK berhasil dikirim.', success: true);
        _load();
      }
    } catch (e) {
      if (mounted) _showSnack(ApiClient.extractError(e));
    }
  }

  Future<void> _cancel(int id) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Batalkan Pengajuan'),
        content: const Text('Yakin ingin membatalkan pengajuan bimbingan BK ini?'),
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
      await ApiClient.patch('/bk-consultations/$id/cancel');
      _showSnack('Pengajuan dibatalkan.', success: true);
      _load();
    } catch (e) {
      if (mounted) _showSnack(ApiClient.extractError(e));
    }
  }

  Future<void> _changeTeacher(int id, int currentTeacherId) async {
    int? selectedId = currentTeacherId;
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx2, setSt) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: const Text('Ganti Guru BK'),
          content: DropdownButtonFormField<int>(
            value: selectedId,
            decoration: InputDecoration(
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            ),
            items: _bkTeachers.map((t) => DropdownMenuItem<int>(
              value: t['id'] as int,
              child: Text(t['name'] as String, overflow: TextOverflow.ellipsis),
            )).toList(),
            onChanged: (v) => setSt(() => selectedId = v),
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
            FilledButton(
              onPressed: () => Navigator.pop(ctx, true),
              style: FilledButton.styleFrom(backgroundColor: AppColors.violet600),
              child: const Text('Simpan'),
            ),
          ],
        ),
      ),
    );
    if (confirm != true || selectedId == null || !mounted) return;
    try {
      await ApiClient.patch('/bk-consultations/$id/change-teacher', data: {'teacher_id': selectedId});
      _showSnack('Guru BK berhasil diganti.', success: true);
      _load();
    } catch (e) {
      if (mounted) _showSnack(ApiClient.extractError(e));
    }
  }

  void _openCreate() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _CreateSheet(
        bkTeachers: _bkTeachers,
        onSubmit: _submit,
      ),
    );
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
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Bimbingan BK'),
        actions: [
          if (!_isLoading && _active == null && _bkTeachers.isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(right: 8),
              child: TextButton.icon(
                onPressed: _openCreate,
                icon: const Icon(Icons.add, size: 18),
                label: const Text('Ajukan'),
                style: TextButton.styleFrom(foregroundColor: AppColors.violet600),
              ),
            ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _ErrorView(error: _error!, onRetry: _load)
              : RefreshIndicator(
                  onRefresh: _load,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        if (_active != null) ...[
                          _ActiveCard(
                            c: _active!,
                            onCancel: () => _cancel(_active!['id'] as int),
                            onChangeTeacher: () => _changeTeacher(
                              _active!['id'] as int,
                              _active!['teacher_id'] as int,
                            ),
                          ),
                          const SizedBox(height: 12),
                        ],

                        if (_active == null) ...[
                          if (_bkTeachers.isEmpty)
                            Container(
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                color: AppColors.yellow50,
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(color: AppColors.amber100),
                              ),
                              child: const Text(
                                'Belum ada Guru BK terdaftar di sekolah ini.',
                                style: TextStyle(color: AppColors.amber500, fontSize: 13),
                                textAlign: TextAlign.center,
                              ),
                            )
                          else
                            FilledButton.icon(
                              onPressed: _openCreate,
                              icon: const Icon(Icons.add_comment_rounded),
                              label: const Text('Ajukan Bimbingan BK'),
                              style: FilledButton.styleFrom(
                                backgroundColor: AppColors.violet600,
                                minimumSize: const Size.fromHeight(48),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(14)),
                              ),
                            ),
                          const SizedBox(height: 16),
                        ],

                        if (_consultations.isNotEmpty) ...[
                          const Text('Riwayat Bimbingan BK',
                            style: TextStyle(
                              fontSize: 13, fontWeight: FontWeight.w700,
                              color: AppColors.gray700)),
                          const SizedBox(height: 8),
                          ..._consultations.map((c) => Padding(
                            padding: const EdgeInsets.only(bottom: 8),
                            child: _ConsultationCard(c: c),
                          )),
                        ] else if (!_isLoading) ...[
                          const SizedBox(height: 40),
                          const Icon(Icons.chat_bubble_outline_rounded,
                            size: 48, color: AppColors.gray200),
                          const SizedBox(height: 12),
                          const Text('Belum ada riwayat bimbingan BK.',
                            style: TextStyle(color: AppColors.gray400, fontSize: 13),
                            textAlign: TextAlign.center),
                        ],
                      ],
                    ),
                  ),
                ),
    );
  }
}

// ─── Active Card ──────────────────────────────────────────────────────────────

class _ActiveCard extends StatelessWidget {
  final Map<String, dynamic> c;
  final VoidCallback onCancel;
  final VoidCallback onChangeTeacher;
  const _ActiveCard({required this.c, required this.onCancel, required this.onChangeTeacher});

  @override
  Widget build(BuildContext context) {
    final status = c['status'] as String;
    final isPending = status == 'pending';
    final grad = isPending
        ? const LinearGradient(colors: [Color(0xFFF59E0B), Color(0xFFEA580C)])
        : const LinearGradient(colors: [Color(0xFF2563EB), Color(0xFF4F46E5)]);

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: grad,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Container(
            width: 36, height: 36,
            decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.2),
              borderRadius: BorderRadius.circular(12)),
            child: const Icon(Icons.chat_bubble_outline_rounded, color: Colors.white, size: 18),
          ),
          const SizedBox(width: 10),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text('Pengajuan Aktif · ${c['status_label']}',
              style: const TextStyle(color: Colors.white70, fontSize: 11)),
            Text(c['topic'] as String,
              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 14)),
            Text('Guru BK: ${c['teacher_name'] ?? '—'}',
              style: const TextStyle(color: Colors.white70, fontSize: 11)),
          ])),
        ]),

        if (status == 'scheduled' && c['scheduled_date'] != null) ...[
          const SizedBox(height: 10),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Text('Dijadwalkan: ${c['scheduled_date']}',
              style: const TextStyle(color: Colors.white, fontSize: 12)),
          ),
        ],

        if (isPending) ...[
          const SizedBox(height: 12),
          Row(children: [
            Expanded(child: OutlinedButton(
              onPressed: onChangeTeacher,
              style: OutlinedButton.styleFrom(
                foregroundColor: Colors.white,
                side: const BorderSide(color: Colors.white30),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                padding: const EdgeInsets.symmetric(vertical: 10),
              ),
              child: const Text('Ganti Guru BK', style: TextStyle(fontSize: 12)),
            )),
            const SizedBox(width: 8),
            OutlinedButton(
              onPressed: onCancel,
              style: OutlinedButton.styleFrom(
                foregroundColor: Colors.white70,
                side: const BorderSide(color: Colors.white24),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 14),
              ),
              child: const Text('Batalkan', style: TextStyle(fontSize: 12)),
            ),
          ]),
        ],
      ]),
    );
  }
}

// ─── Consultation Card ────────────────────────────────────────────────────────

class _ConsultationCard extends StatelessWidget {
  final Map<String, dynamic> c;
  const _ConsultationCard({required this.c});

  @override
  Widget build(BuildContext context) {
    final status = c['status'] as String;
    final (Color badgeBg, Color badgeFg, Color border) = switch (status) {
      'pending'   => (AppColors.amber100,  AppColors.amber500,  AppColors.amber100),
      'scheduled' => (AppColors.blue50,    AppColors.blue600,   AppColors.blue50),
      'completed' => (AppColors.green100,  AppColors.green600,  AppColors.green100),
      _           => (AppColors.gray100,   AppColors.gray500,   AppColors.gray100),
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
          Expanded(child: Text(c['topic'] as String,
            style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: AppColors.gray800))),
          const SizedBox(width: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(
              color: badgeBg,
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text(c['status_label'] as String,
              style: TextStyle(color: badgeFg, fontSize: 10, fontWeight: FontWeight.w700)),
          ),
        ]),
        const SizedBox(height: 4),
        Text('${c['teacher_name'] ?? '—'} · ${_formatDate(c['created_at'] as String)}',
          style: const TextStyle(color: AppColors.gray400, fontSize: 11)),

        if (c['student_note'] != null) ...[
          const SizedBox(height: 6),
          Text(c['student_note'] as String,
            style: const TextStyle(color: AppColors.gray600, fontSize: 12)),
        ],

        if (status == 'scheduled' && c['scheduled_date'] != null) ...[
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
            decoration: BoxDecoration(
              color: AppColors.blue50,
              borderRadius: BorderRadius.circular(8),
            ),
            child: Text('Dijadwalkan: ${c['scheduled_date']}',
              style: const TextStyle(color: AppColors.blue600, fontSize: 11)),
          ),
        ],

        if (status == 'completed') ...[
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: AppColors.green50,
              borderRadius: BorderRadius.circular(8),
            ),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              if (c['conducted_date'] != null)
                Text('Dilaksanakan: ${c['conducted_date']}',
                  style: const TextStyle(color: AppColors.gray500, fontSize: 11)),
              if (c['teacher_note'] != null) ...[
                const SizedBox(height: 4),
                const Text('Catatan Guru BK:',
                  style: TextStyle(fontWeight: FontWeight.w600, fontSize: 11, color: AppColors.gray700)),
                const SizedBox(height: 2),
                Text(c['teacher_note'] as String,
                  style: const TextStyle(color: AppColors.gray600, fontSize: 11)),
              ],
              if (c['follow_up'] != null) ...[
                const SizedBox(height: 4),
                const Text('Tindak Lanjut:',
                  style: TextStyle(fontWeight: FontWeight.w600, fontSize: 11, color: AppColors.gray700)),
                const SizedBox(height: 2),
                Text(c['follow_up'] as String,
                  style: const TextStyle(color: AppColors.gray600, fontSize: 11)),
              ],
            ]),
          ),
        ],

        if (status == 'cancelled' && c['cancelled_reason'] != null) ...[
          const SizedBox(height: 6),
          Text(c['cancelled_reason'] as String,
            style: const TextStyle(color: AppColors.gray400, fontSize: 11,
              fontStyle: FontStyle.italic)),
        ],
      ]),
    );
  }

  String _formatDate(String raw) {
    try {
      final dt = DateTime.parse(raw);
      const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
      return '${dt.day} ${months[dt.month - 1]} ${dt.year}';
    } catch (_) {
      return raw;
    }
  }
}

// ─── Create Sheet ─────────────────────────────────────────────────────────────

class _CreateSheet extends StatefulWidget {
  final List<Map<String, dynamic>> bkTeachers;
  final Future<void> Function({required int teacherId, required String topic, String? note}) onSubmit;
  const _CreateSheet({required this.bkTeachers, required this.onSubmit});

  @override
  State<_CreateSheet> createState() => _CreateSheetState();
}

class _CreateSheetState extends State<_CreateSheet> {
  final _formKey   = GlobalKey<FormState>();
  final _topicCtrl = TextEditingController();
  final _noteCtrl  = TextEditingController();
  int?   _selectedTeacherId;
  bool   _submitting = false;

  @override
  void dispose() {
    _topicCtrl.dispose();
    _noteCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedTeacherId == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Pilih Guru BK terlebih dahulu.'),
        backgroundColor: AppColors.red500,
        behavior: SnackBarBehavior.floating,
      ));
      return;
    }
    setState(() => _submitting = true);
    try {
      await widget.onSubmit(
        teacherId: _selectedTeacherId!,
        topic: _topicCtrl.text.trim(),
        note: _noteCtrl.text.trim().isEmpty ? null : _noteCtrl.text.trim(),
      );
      if (mounted) Navigator.pop(context);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return DraggableScrollableSheet(
      initialChildSize: 0.75,
      minChildSize: 0.5,
      maxChildSize: 0.95,
      expand: false,
      builder: (_, scrollCtrl) => Container(
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
            child: Align(
              alignment: Alignment.centerLeft,
              child: Text('Ajukan Bimbingan BK',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800,
                  color: AppColors.gray800)),
            ),
          ),
          const SizedBox(height: 16),
          Expanded(child: SingleChildScrollView(
            controller: scrollCtrl,
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Form(
              key: _formKey,
              child: Column(children: [
                DropdownButtonFormField<int>(
                  value: _selectedTeacherId,
                  decoration: InputDecoration(
                    labelText: 'Guru BK *',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  ),
                  items: widget.bkTeachers.map((t) => DropdownMenuItem<int>(
                    value: t['id'] as int,
                    child: Text(t['name'] as String, overflow: TextOverflow.ellipsis),
                  )).toList(),
                  onChanged: (v) => setState(() => _selectedTeacherId = v),
                  validator: (v) => v == null ? 'Pilih Guru BK' : null,
                ),
                const SizedBox(height: 14),
                TextFormField(
                  controller: _topicCtrl,
                  decoration: InputDecoration(
                    labelText: 'Topik / Permasalahan *',
                    hintText: 'Contoh: Masalah pertemanan, motivasi belajar',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  ),
                  maxLength: 200,
                  validator: (v) => (v?.trim().isEmpty ?? true) ? 'Topik wajib diisi' : null,
                ),
                const SizedBox(height: 14),
                TextFormField(
                  controller: _noteCtrl,
                  decoration: InputDecoration(
                    labelText: 'Keterangan Tambahan (opsional)',
                    hintText: 'Ceritakan lebih detail jika diperlukan…',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  ),
                  maxLines: 4,
                  maxLength: 1000,
                ),
                const SizedBox(height: 8),
              ]),
            ),
          )),
          Padding(
            padding: EdgeInsets.fromLTRB(20, 12, 20, MediaQuery.of(context).viewInsets.bottom + 20),
            child: FilledButton(
              onPressed: _submitting ? null : _submit,
              style: FilledButton.styleFrom(
                backgroundColor: AppColors.violet600,
                minimumSize: const Size.fromHeight(48),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
              child: _submitting
                  ? const SizedBox(height: 18, width: 18,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : const Text('Kirim Pengajuan',
                      style: TextStyle(fontWeight: FontWeight.w700)),
            ),
          ),
        ]),
      ),
    );
  }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

class _ErrorView extends StatelessWidget {
  final String error;
  final VoidCallback onRetry;
  const _ErrorView({required this.error, required this.onRetry});
  @override
  Widget build(BuildContext context) => Center(child: Padding(
    padding: const EdgeInsets.all(24),
    child: Column(mainAxisSize: MainAxisSize.min, children: [
      const Icon(Icons.error_outline, size: 48, color: AppColors.red500),
      const SizedBox(height: 12),
      Text(error, textAlign: TextAlign.center, style: const TextStyle(color: AppColors.gray600)),
      const SizedBox(height: 16),
      FilledButton(onPressed: onRetry, child: const Text('Coba Lagi')),
    ]),
  ));
}
