import 'package:flutter/material.dart';
import '../../models/conduct_log.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';

class ConductScreen extends StatefulWidget {
  const ConductScreen({super.key});

  @override
  State<ConductScreen> createState() => _ConductScreenState();
}

class _ConductScreenState extends State<ConductScreen> {
  ConductSummary? _summary;
  List<ConductLog> _logs = [];
  bool   _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _isLoading = true; _error = null; });
    try {
      final body = await ApiClient.get('/conduct');
      setState(() {
        _summary = ConductSummary.fromJson(body['summary'] as Map<String, dynamic>);
        _logs    = (body['logs'] as List)
            .map((e) => ConductLog.fromJson(e as Map<String, dynamic>))
            .toList();
      });
    } catch (e) {
      setState(() => _error = ApiClient.extractError(e));
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('SIPINTER (Pendidikan Karakter)',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 16)),
        backgroundColor: const Color(0xFF0F2460),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showSelfReportDialog,
        backgroundColor: (_summary?.hasRejectedReport ?? false)
            ? const Color(0xFFE11D48)
            : ((_summary?.hasPendingReport ?? false) ? const Color(0xFFB45309) : const Color(0xFFD97706)),
        icon: Icon(
          (_summary?.hasRejectedReport ?? false)
              ? Icons.edit_note_rounded
              : ((_summary?.hasPendingReport ?? false) ? Icons.hourglass_top_rounded : Icons.flash_on_rounded),
          color: Colors.white,
        ),
        label: Text(
          (_summary?.hasRejectedReport ?? false)
              ? 'Perbaiki Pengajuan'
              : ((_summary?.hasPendingReport ?? false) ? 'Menunggu Verifikasi' : 'Lapor Mandiri'),
          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _ErrorView(message: _error!, onRetry: _load)
              : RefreshIndicator(
                  onRefresh: _load,
                  child: CustomScrollView(
                    slivers: [
                      SliverToBoxAdapter(
                        child: Column(
                          children: [
                            if (_summary?.hasRejectedReport ?? false)
                              Container(
                                margin: const EdgeInsets.fromLTRB(16, 16, 16, 0),
                                padding: const EdgeInsets.all(14),
                                decoration: BoxDecoration(
                                  gradient: const LinearGradient(colors: [Color(0xFFE11D48), Color(0xFFBE123C)]),
                                  borderRadius: AppRadius.card,
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        const Icon(Icons.error_outline_rounded, color: Colors.white, size: 20),
                                        const SizedBox(width: 8),
                                        const Expanded(
                                          child: Text(
                                            '✕ Pengajuan Dikembalikan Guru',
                                            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                                          ),
                                        ),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                          decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(6)),
                                          child: const Text('Perlu Revisi', style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold)),
                                        ),
                                      ],
                                    ),
                                    if (_summary?.rejectedReport?['rejection_note'] != null) ...[
                                      const SizedBox(height: 8),
                                      Container(
                                        padding: const EdgeInsets.all(8),
                                        decoration: BoxDecoration(color: Colors.black12, borderRadius: BorderRadius.circular(8)),
                                        child: Text(
                                          'Catatan: "${_summary!.rejectedReport!['rejection_note']}"',
                                          style: const TextStyle(color: Colors.white, fontSize: 11, fontStyle: FontStyle.italic),
                                        ),
                                      ),
                                    ],
                                    const SizedBox(height: 10),
                                    Row(
                                      children: [
                                        ElevatedButton.icon(
                                          onPressed: _showSelfReportDialog,
                                          icon: const Icon(Icons.edit_rounded, size: 14),
                                          label: const Text('Perbaiki Alasan', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor: Colors.white,
                                            foregroundColor: const Color(0xFFBE123C),
                                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                          ),
                                        ),
                                        const SizedBox(width: 8),
                                        if (_summary?.rejectedReport?['id'] != null)
                                          TextButton(
                                            onPressed: () => _cancelReport(_summary!.rejectedReport!['id'] as int),
                                            child: const Text('Batalkan', style: TextStyle(color: Colors.white70, fontSize: 11)),
                                          ),
                                      ],
                                    ),
                                  ],
                                ),
                              )
                            else
                              Container(
                                margin: const EdgeInsets.fromLTRB(16, 16, 16, 0),
                                padding: const EdgeInsets.all(14),
                                decoration: BoxDecoration(
                                  gradient: (_summary?.hasPendingReport ?? false)
                                      ? const LinearGradient(colors: [Color(0xFFD97706), Color(0xFFB45309)])
                                      : const LinearGradient(colors: [Color(0xFFF59E0B), Color(0xFFD97706)]),
                                  borderRadius: AppRadius.card,
                                ),
                                child: Row(
                                  children: [
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            (_summary?.hasPendingReport ?? false)
                                                ? '⏱️ Pengajuan Menunggu Verifikasi'
                                                : '⚡ Terlambat / Lapor Pembinaan Mandiri?',
                                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                                          ),
                                          const SizedBox(height: 2),
                                          Text(
                                            (_summary?.hasPendingReport ?? false)
                                                ? 'Tunjukkan pengajuan Anda ke Guru Piket di gerbang.'
                                                : 'Ajukan dari HP Anda untuk diverifikasi oleh Guru Piket di gerbang.',
                                            style: const TextStyle(color: Colors.white70, fontSize: 11),
                                          ),
                                        ],
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    if (_summary?.hasPendingReport ?? false)
                                      if (_summary?.pendingReport?['id'] != null)
                                        TextButton(
                                          onPressed: () => _cancelReport(_summary!.pendingReport!['id'] as int),
                                          child: const Text('Batalkan', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                                        )
                                      else
                                        const SizedBox.shrink()
                                    else
                                      ElevatedButton(
                                        onPressed: _showSelfReportDialog,
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: Colors.white,
                                          foregroundColor: const Color(0xFFB45309),
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                          elevation: 0,
                                        ),
                                        child: const Text('Ajukan', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                                      ),
                                  ],
                                ),
                              ),
                            _SummaryCards(summary: _summary!),
                          ],
                        ),
                      ),
                      if (_logs.isEmpty)
                        const SliverFillRemaining(
                          child: Center(
                            child: Column(mainAxisSize: MainAxisSize.min, children: [
                              Icon(Icons.task_alt_rounded, size: 56, color: AppColors.gray300),
                              SizedBox(height: 12),
                              Text('Belum ada catatan perilaku',
                                style: TextStyle(fontSize: 13, color: AppColors.gray400)),
                            ]),
                          ),
                        )
                      else
                        SliverPadding(
                          padding: const EdgeInsets.fromLTRB(16, 0, 16, 80),
                          sliver: SliverList.separated(
                            itemCount: _logs.length,
                            separatorBuilder: (_, __) => const SizedBox(height: 8),
                            itemBuilder: (_, i) => _LogCard(log: _logs[i]),
                          ),
                        ),
                    ],
                  ),
                ),
    );
  }

  Future<void> _cancelReport(int id) async {
    final bool? confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Batalkan Pengajuan?'),
        content: const Text('Apakah Anda yakin ingin membatalkan/menghapus pengajuan pembinaan mandiri ini?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Tidak')),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.red500, foregroundColor: Colors.white),
            child: const Text('Ya, Batalkan'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    try {
      final res = await ApiClient.delete('/conduct/self-report/$id');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message'] ?? 'Pengajuan berhasil dibatalkan.'), backgroundColor: AppColors.emerald600),
        );
        _load();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(ApiClient.extractError(e)), backgroundColor: AppColors.red500),
        );
      }
    }
  }

  void _showSelfReportDialog() {
    final bool isRejected = _summary?.hasRejectedReport ?? false;
    final int? editId = isRejected ? (_summary?.rejectedReport?['id'] as int?) : null;

    if (!isRejected && (_summary?.hasPendingReport ?? false)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Anda masih memiliki 1 pengajuan yang sedang menunggu verifikasi Guru Piket.'),
          backgroundColor: Color(0xFFB45309),
        ),
      );
      return;
    }

    String selectedReason = 'Terlambat Masuk Sekolah';
    final descController = TextEditingController();
    final otherReasonController = TextEditingController();

    if (isRejected && _summary?.rejectedReport != null) {
      final rej = _summary!.rejectedReport!;
      final cat = rej['category_name'] as String? ?? '';
      if (['Terlambat Masuk Sekolah', 'Terlambat Kembali dari Izin Keluar', 'Seragam / Atribut Tidak Lengkap'].contains(cat)) {
        selectedReason = cat;
      } else {
        selectedReason = 'Lainnya';
        otherReasonController.text = cat;
      }
      if (rej['description'] != null) {
        descController.text = rej['description'];
      }
    }

    bool isSubmitting = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (modalCtx, setModalState) => Padding(
          padding: EdgeInsets.only(
            left: 20, right: 20, top: 20,
            bottom: MediaQuery.of(modalCtx).viewInsets.bottom + 20,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Icon(
                        isRejected ? Icons.edit_note_rounded : Icons.flash_on_rounded,
                        color: isRejected ? const Color(0xFFE11D48) : const Color(0xFFD97706),
                        size: 22,
                      ),
                      const SizedBox(width: 8),
                      Text(
                        isRejected ? 'Perbaiki Pengajuan Mandiri' : 'Lapor Pembinaan Mandiri',
                        style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.gray800),
                      ),
                    ],
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, size: 20, color: AppColors.gray400),
                    onPressed: () => Navigator.pop(modalCtx),
                  ),
                ],
              ),
              if (isRejected && _summary?.rejectedReport?['rejection_note'] != null) ...[
                const SizedBox(height: 8),
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFF1F2),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: const Color(0xFFFECDD3)),
                  ),
                  child: Text(
                    'Catatan Guru: "${_summary!.rejectedReport!['rejection_note']}"',
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF9F1239)),
                  ),
                ),
              ],
              const SizedBox(height: 14),
              const Text('Alasan Pembinaan Disiplin',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.gray700)),
              const SizedBox(height: 6),
              DropdownButtonFormField<String>(
                initialValue: selectedReason,
                decoration: InputDecoration(
                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.gray200)),
                ),
                items: const [
                  DropdownMenuItem(value: 'Terlambat Masuk Sekolah', child: Text('Terlambat Masuk Sekolah (Gerbang)', style: TextStyle(fontSize: 12))),
                  DropdownMenuItem(value: 'Terlambat Kembali dari Izin Keluar', child: Text('Terlambat Kembali dari Izin Keluar', style: TextStyle(fontSize: 12))),
                  DropdownMenuItem(value: 'Seragam / Atribut Tidak Lengkap', child: Text('Seragam / Atribut Tidak Lengkap', style: TextStyle(fontSize: 12))),
                  DropdownMenuItem(value: 'Lainnya', child: Text('Alasan Lainnya (Input Sendiri)', style: TextStyle(fontSize: 12))),
                ],
                onChanged: (val) {
                  if (val != null) setModalState(() => selectedReason = val);
                },
              ),
              if (selectedReason == 'Lainnya') ...[
                const SizedBox(height: 12),
                const Text('Sebutkan Alasan / Pelanggaran Lainnya *',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.gray700)),
                const SizedBox(height: 6),
                TextField(
                  controller: otherReasonController,
                  style: const TextStyle(fontSize: 12),
                  decoration: InputDecoration(
                    hintText: 'Contoh: Meninggalkan kelas tanpa izin / Tidak memakai dasi.',
                    contentPadding: const EdgeInsets.all(12),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.gray200)),
                  ),
                ),
              ],
              const SizedBox(height: 12),
              const Text('Keterangan / Alasan Tambahan (Opsional)',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.gray700)),
              const SizedBox(height: 6),
              TextField(
                controller: descController,
                maxLines: 2,
                style: const TextStyle(fontSize: 12),
                decoration: InputDecoration(
                  hintText: 'Contoh: Terjebak kemacetan di jalan utama.',
                  contentPadding: const EdgeInsets.all(12),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.gray200)),
                ),
              ),
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: isSubmitting ? null : () async {
                    if (selectedReason == 'Lainnya' && otherReasonController.text.trim().isEmpty) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Harap sebutkan alasan atau jenis pelanggaran lainnya.'), backgroundColor: AppColors.red500),
                      );
                      return;
                    }

                    setModalState(() => isSubmitting = true);
                    try {
                      final payload = {
                        'reason': selectedReason,
                        'other_reason': otherReasonController.text.trim(),
                        'description': descController.text.trim(),
                      };

                      if (isRejected && editId != null) {
                        await ApiClient.put('/conduct/self-report/$editId', data: payload);
                      } else {
                        await ApiClient.post('/conduct/self-report', data: payload);
                      }

                      if (modalCtx.mounted) {
                        Navigator.pop(modalCtx);
                      }
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(isRejected
                                ? 'Pengajuan berhasil diperbaiki dan dikirim ulang ke Guru Piket.'
                                : 'Pengajuan pembinaan mandiri berhasil dikirim. Tunjukkan ke Guru Piket.'),
                            backgroundColor: AppColors.emerald600,
                          ),
                        );
                        _load();
                      }
                    } catch (e) {
                      setModalState(() => isSubmitting = false);
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text(ApiClient.extractError(e)), backgroundColor: AppColors.red500),
                        );
                      }
                    }
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: isRejected ? const Color(0xFFE11D48) : const Color(0xFFD97706),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: isSubmitting
                      ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                      : Text(isRejected ? 'Simpan & Kirim Ulang' : 'Kirim Pengajuan',
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13)),
                ),
              ),
            ],
          ),
        ),
    );
  }
}

// ─── Summary Cards ────────────────────────────────────────────────────────────

class _SummaryCards extends StatelessWidget {
  final ConductSummary summary;
  const _SummaryCards({required this.summary});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Row(children: [
        Expanded(child: _CountCard(
          label: 'Apresiasi Karakter',
          count: summary.prestasiCount,
          color: AppColors.emerald600,
          bg: AppColors.emerald50,
          icon: Icons.thumb_up_rounded,
        )),
        const SizedBox(width: 12),
        Expanded(child: _CountCard(
          label: 'Kedisiplinan Karakter',
          count: summary.pelanggaranCount,
          color: AppColors.red500,
          bg: AppColors.red50,
          icon: Icons.thumb_down_rounded,
        )),
      ]),
    );
  }
}

class _CountCard extends StatelessWidget {
  final String  label;
  final int     count;
  final Color   color, bg;
  final IconData icon;
  const _CountCard({
    required this.label, required this.count,
    required this.color, required this.bg, required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      decoration: BoxDecoration(color: bg, borderRadius: AppRadius.card),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Icon(icon, color: color, size: 20),
        const SizedBox(height: 8),
        Text('$count',
          style: TextStyle(fontSize: 26, fontWeight: FontWeight.bold, color: color, height: 1)),
        const SizedBox(height: 2),
        Text(label,
          style: const TextStyle(fontSize: 11, color: AppColors.gray500)),
      ]),
    );
  }
}

// ─── Log Card ─────────────────────────────────────────────────────────────────

class _LogCard extends StatelessWidget {
  final ConductLog log;
  const _LogCard({required this.log});

  String _fmtDate(String s) {
    try {
      final d = DateTime.parse(s);
      const m = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
      const wd = ['','Sen','Sel','Rab','Kam','Jum','Sab','Min'];
      return '${wd[d.weekday]}, ${d.day} ${m[d.month]} ${d.year}';
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
      child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Container(
          width: 40, height: 40,
          decoration: BoxDecoration(color: log.typeBg, borderRadius: BorderRadius.circular(10)),
          child: Center(child: Icon(
            log.isPrestasi ? Icons.thumb_up_rounded : Icons.thumb_down_rounded,
            color: log.typeColor, size: 20,
          )),
        ),
        const SizedBox(width: 12),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Expanded(child: Text(log.displayCategoryName,
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray800))),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
              decoration: BoxDecoration(color: log.typeBg, borderRadius: BorderRadius.circular(20)),
              child: Text(log.typeLabel,
                style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: log.typeColor)),
            ),
          ]),
          const SizedBox(height: 2),
          Text(_fmtDate(log.date),
            style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
          if (log.teacherName != null) ...[
            const SizedBox(height: 2),
            Text('Oleh: ${log.teacherName}',
              style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
          ],
          if ((log.displayNoteBody ?? log.note) != null && (log.displayNoteBody ?? log.note)!.isNotEmpty) ...[
            const SizedBox(height: 6),
            Text(log.displayNoteBody ?? log.note!,
              style: const TextStyle(fontSize: 12, color: AppColors.gray600),
              maxLines: 2, overflow: TextOverflow.ellipsis),
          ],
          const SizedBox(height: 4),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
            decoration: BoxDecoration(
              color: AppColors.gray50, borderRadius: BorderRadius.circular(4)),
            child: Text(log.context,
              style: const TextStyle(fontSize: 10, color: AppColors.gray500)),
          ),
        ])),
      ]),
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
