import 'package:flutter/material.dart';
import '../../models/homeroom_consultation.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';

class HomeroomConsultationScreen extends StatefulWidget {
  const HomeroomConsultationScreen({super.key});

  @override
  State<HomeroomConsultationScreen> createState() =>
      _HomeroomConsultationScreenState();
}

class _HomeroomConsultationScreenState
    extends State<HomeroomConsultationScreen> {
  List<HomeroomConsultation> _items = [];
  String?  _teacherName;
  bool     _isLoading = true;
  String?  _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _isLoading = true; _error = null; });
    try {
      final body = await ApiClient.get('/homeroom-consultations');
      final teacherMap = body['homeroom_teacher'] as Map<String, dynamic>?;
      setState(() {
        _teacherName = teacherMap?['name'] as String?;
        _items = (body['consultations'] as List)
            .map((e) => HomeroomConsultation.fromJson(e as Map<String, dynamic>))
            .toList();
      });
    } catch (e) {
      setState(() => _error = ApiClient.extractError(e));
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _cancel(HomeroomConsultation item) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Batalkan Pengajuan'),
        content: const Text('Yakin ingin membatalkan pengajuan bimbingan ini?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Tidak'),
          ),
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
      await ApiClient.patch('/homeroom-consultations/${item.id}/cancel');
      _showSnack('Pengajuan bimbingan dibatalkan.', success: true);
      _load();
    } catch (e) {
      _showSnack(ApiClient.extractError(e));
    }
  }

  void _openCreate() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _CreateSheet(
        teacherName: _teacherName,
        onCreated: () { Navigator.pop(context); _load(); },
      ),
    );
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Bimbingan Wali Kelas',
          style: TextStyle(fontWeight: FontWeight.w600, fontSize: 16)),
        backgroundColor: const Color(0xFF0F2460),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _openCreate,
        backgroundColor: AppColors.blue600,
        icon: const Icon(Icons.add_rounded),
        label: const Text('Ajukan Bimbingan'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _ErrorView(message: _error!, onRetry: _load)
              : RefreshIndicator(
                  onRefresh: _load,
                  child: CustomScrollView(
                    slivers: [
                      if (_teacherName != null)
                        SliverToBoxAdapter(child: _TeacherBanner(name: _teacherName!)),
                      if (_items.isEmpty)
                        const SliverFillRemaining(
                          child: Center(
                            child: Column(mainAxisSize: MainAxisSize.min, children: [
                              Icon(Icons.support_agent_rounded, size: 56, color: AppColors.gray300),
                              SizedBox(height: 12),
                              Text('Belum ada pengajuan bimbingan',
                                style: TextStyle(fontSize: 13, color: AppColors.gray400)),
                            ]),
                          ),
                        )
                      else
                        SliverPadding(
                          padding: const EdgeInsets.fromLTRB(16, 0, 16, 100),
                          sliver: SliverList.separated(
                            itemCount: _items.length,
                            separatorBuilder: (_, __) => const SizedBox(height: 8),
                            itemBuilder: (_, i) => _ConsultationCard(
                              item: _items[i],
                              onCancel: () => _cancel(_items[i]),
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
    );
  }
}

// ─── Teacher Banner ───────────────────────────────────────────────────────────

class _TeacherBanner extends StatelessWidget {
  final String name;
  const _TeacherBanner({required this.name});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: AppColors.blue50,
        borderRadius: AppRadius.card,
        border: Border.all(color: AppColors.blue200),
      ),
      child: Row(children: [
        Container(
          width: 36, height: 36,
          decoration: const BoxDecoration(
            color: AppColors.blue600, shape: BoxShape.circle),
          child: const Icon(Icons.person_rounded, color: Colors.white, size: 18),
        ),
        const SizedBox(width: 10),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          const Text('Wali Kelas',
            style: TextStyle(fontSize: 11, color: AppColors.blue400)),
          Text(name,
            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.blue800)),
        ])),
      ]),
    );
  }
}

// ─── Consultation Card ────────────────────────────────────────────────────────

class _ConsultationCard extends StatelessWidget {
  final HomeroomConsultation item;
  final VoidCallback         onCancel;
  const _ConsultationCard({required this.item, required this.onCancel});

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
        color: Colors.white, borderRadius: AppRadius.card,
        border: Border.all(color: AppColors.gray100), boxShadow: AppShadow.sm,
      ),
      padding: const EdgeInsets.all(14),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Container(
            width: 32, height: 32,
            decoration: BoxDecoration(color: item.statusBg, borderRadius: BorderRadius.circular(8)),
            child: Icon(item.statusIcon, color: item.statusColor, size: 16),
          ),
          const SizedBox(width: 10),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(item.topic,
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray800),
              maxLines: 2, overflow: TextOverflow.ellipsis),
            Text(_fmtDate(item.createdAt.substring(0, 10)),
              style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
          ])),
          const SizedBox(width: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(color: item.statusBg, borderRadius: BorderRadius.circular(20)),
            child: Text(item.statusLabel,
              style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: item.statusColor)),
          ),
          if (item.canCancel) ...[
            const SizedBox(width: 4),
            IconButton(
              onPressed: onCancel,
              icon: const Icon(Icons.delete_outline_rounded, size: 16, color: AppColors.red500),
              visualDensity: VisualDensity.compact, padding: EdgeInsets.zero,
            ),
          ],
        ]),

        if (item.scheduledDate != null) ...[
          const SizedBox(height: 8),
          _InfoRow(
            icon: Icons.calendar_month_rounded,
            color: AppColors.blue600,
            label: 'Jadwal: ${_fmtDate(item.scheduledDate!)}',
          ),
        ],
        if (item.conductedDate != null) ...[
          const SizedBox(height: 4),
          _InfoRow(
            icon: Icons.check_circle_rounded,
            color: AppColors.green500,
            label: 'Dilaksanakan: ${_fmtDate(item.conductedDate!)}',
          ),
        ],

        if (item.studentNote != null && item.studentNote!.isNotEmpty) ...[
          const SizedBox(height: 8),
          const Text('Catatan:', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w500, color: AppColors.gray500)),
          const SizedBox(height: 2),
          Text(item.studentNote!,
            style: const TextStyle(fontSize: 12, color: AppColors.gray600),
            maxLines: 3, overflow: TextOverflow.ellipsis),
        ],

        if (item.teacherNote != null && item.teacherNote!.isNotEmpty) ...[
          const SizedBox(height: 8),
          _NoteBox(
            icon: Icons.comment_outlined,
            color: AppColors.blue600,
            bg: AppColors.blue50,
            label: 'Catatan Wali Kelas',
            text: item.teacherNote!,
          ),
        ],
        if (item.followUp != null && item.followUp!.isNotEmpty) ...[
          const SizedBox(height: 6),
          _NoteBox(
            icon: Icons.arrow_right_alt_rounded,
            color: AppColors.green600,
            bg: AppColors.green50,
            label: 'Tindak Lanjut',
            text: item.followUp!,
          ),
        ],
        if (item.cancelledReason != null && item.cancelledReason!.isNotEmpty) ...[
          const SizedBox(height: 6),
          _NoteBox(
            icon: Icons.info_outline_rounded,
            color: AppColors.red500,
            bg: AppColors.red50,
            label: 'Alasan Pembatalan',
            text: item.cancelledReason!,
          ),
        ],
      ]),
    );
  }
}

class _InfoRow extends StatelessWidget {
  final IconData icon; final Color color; final String label;
  const _InfoRow({required this.icon, required this.color, required this.label});
  @override
  Widget build(BuildContext context) => Row(children: [
    Icon(icon, size: 13, color: color),
    const SizedBox(width: 6),
    Text(label, style: TextStyle(fontSize: 11, color: color)),
  ]);
}

class _NoteBox extends StatelessWidget {
  final IconData icon; final Color color, bg;
  final String label, text;
  const _NoteBox({required this.icon, required this.color, required this.bg,
    required this.label, required this.text});
  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.all(8),
    decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(8)),
    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Row(children: [
        Icon(icon, size: 12, color: color),
        const SizedBox(width: 4),
        Text(label, style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: color)),
      ]),
      const SizedBox(height: 4),
      Text(text, style: TextStyle(fontSize: 11, color: color)),
    ]),
  );
}

// ─── Create Sheet ─────────────────────────────────────────────────────────────

class _CreateSheet extends StatefulWidget {
  final String?      teacherName;
  final VoidCallback onCreated;
  const _CreateSheet({required this.onCreated, this.teacherName});

  @override
  State<_CreateSheet> createState() => _CreateSheetState();
}

class _CreateSheetState extends State<_CreateSheet> {
  final _topicCtrl = TextEditingController();
  final _noteCtrl  = TextEditingController();
  bool  _isSaving  = false;

  @override
  void dispose() { _topicCtrl.dispose(); _noteCtrl.dispose(); super.dispose(); }

  Future<void> _submit() async {
    if (_topicCtrl.text.trim().isEmpty) {
      _showSnack('Topik bimbingan tidak boleh kosong.');
      return;
    }
    setState(() => _isSaving = true);
    try {
      await ApiClient.post('/homeroom-consultations', data: {
        'topic':        _topicCtrl.text.trim(),
        'student_note': _noteCtrl.text.trim().isEmpty ? null : _noteCtrl.text.trim(),
      });
      widget.onCreated();
    } catch (e) {
      _showSnack(ApiClient.extractError(e));
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  void _showSnack(String msg) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg), backgroundColor: AppColors.red500,
      behavior: SnackBarBehavior.floating));
  }

  @override
  Widget build(BuildContext context) {
    final bottom = MediaQuery.of(context).viewInsets.bottom;
    return Container(
      margin: const EdgeInsets.fromLTRB(12, 0, 12, 12),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.all(Radius.circular(20)),
      ),
      padding: EdgeInsets.fromLTRB(20, 20, 20, 20 + bottom),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Center(child: Container(
            width: 40, height: 4,
            decoration: BoxDecoration(
              color: AppColors.gray200, borderRadius: BorderRadius.circular(2)),
          )),
          const SizedBox(height: 16),
          const Text('Ajukan Bimbingan',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.gray800)),
          const SizedBox(height: 4),
          Text(
            widget.teacherName != null
                ? 'Kepada: ${widget.teacherName}'
                : 'Perlu persetujuan wali kelas.',
            style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
          const SizedBox(height: 16),

          // Topik
          const Text('Topik Bimbingan *',
            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.gray600)),
          const SizedBox(height: 6),
          TextField(
            controller: _topicCtrl,
            maxLength: 200,
            style: const TextStyle(fontSize: 13, color: AppColors.gray700),
            decoration: InputDecoration(
              hintText: 'Contoh: Masalah akademik semester ini...',
              hintStyle: const TextStyle(color: AppColors.gray400, fontSize: 12),
              filled: true, fillColor: AppColors.gray50,
              counterStyle: const TextStyle(fontSize: 10),
              contentPadding: const EdgeInsets.all(12),
              border: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: const BorderSide(color: AppColors.gray200)),
              enabledBorder: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: const BorderSide(color: AppColors.gray200)),
              focusedBorder: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: const BorderSide(color: AppColors.blue600, width: 2)),
            ),
          ),
          const SizedBox(height: 14),

          // Catatan
          const Text('Catatan (opsional)',
            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.gray600)),
          const SizedBox(height: 6),
          TextField(
            controller: _noteCtrl,
            maxLines: 3, maxLength: 1000,
            style: const TextStyle(fontSize: 13, color: AppColors.gray700),
            decoration: InputDecoration(
              hintText: 'Ceritakan lebih lanjut...',
              hintStyle: const TextStyle(color: AppColors.gray400, fontSize: 12),
              filled: true, fillColor: AppColors.gray50,
              counterStyle: const TextStyle(fontSize: 10),
              contentPadding: const EdgeInsets.all(12),
              border: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: const BorderSide(color: AppColors.gray200)),
              enabledBorder: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: const BorderSide(color: AppColors.gray200)),
              focusedBorder: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: const BorderSide(color: AppColors.blue600, width: 2)),
            ),
          ),
          const SizedBox(height: 16),

          FilledButton(
            onPressed: _isSaving ? null : _submit,
            style: FilledButton.styleFrom(
              backgroundColor: AppColors.blue600,
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
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

// ─── Error View ───────────────────────────────────────────────────────────────

class _ErrorView extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;
  const _ErrorView({required this.message, required this.onRetry});
  @override
  Widget build(BuildContext context) => Center(
    child: Column(mainAxisSize: MainAxisSize.min, children: [
      const Icon(Icons.error_outline_rounded, size: 48, color: AppColors.red500),
      const SizedBox(height: 8),
      Text(message,
        style: const TextStyle(fontSize: 13, color: AppColors.gray500),
        textAlign: TextAlign.center),
      const SizedBox(height: 12),
      TextButton(onPressed: onRetry, child: const Text('Coba Lagi')),
    ]),
  );
}
