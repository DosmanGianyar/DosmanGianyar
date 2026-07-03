import 'package:flutter/material.dart';
import '../../models/early_checkout.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';

class EarlyCheckoutScreen extends StatefulWidget {
  const EarlyCheckoutScreen({super.key});

  @override
  State<EarlyCheckoutScreen> createState() => _EarlyCheckoutScreenState();
}

class _EarlyCheckoutScreenState extends State<EarlyCheckoutScreen> {
  List<EarlyCheckout> _items     = [];
  bool                _isLoading = true;
  String?             _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _isLoading = true; _error = null; });
    try {
      final body = await ApiClient.get('/early-checkout');
      setState(() {
        _items = (body['requests'] as List)
            .map((e) => EarlyCheckout.fromJson(e as Map<String, dynamic>))
            .toList();
      });
    } catch (e) {
      setState(() => _error = ApiClient.extractError(e));
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _delete(EarlyCheckout item) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Batalkan Pengajuan'),
        content: const Text('Yakin ingin membatalkan pengajuan ini?'),
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
      await ApiClient.delete('/early-checkout/${item.id}');
      _showSnack('Pengajuan berhasil dibatalkan.', success: true);
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
      builder: (_) => _CreateSheet(onCreated: () { Navigator.pop(context); _load(); }),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Izin Pulang Lebih Awal',
          style: TextStyle(fontWeight: FontWeight.w600, fontSize: 16)),
        backgroundColor: const Color(0xFF0F2460),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _openCreate,
        backgroundColor: AppColors.emerald600,
        icon: const Icon(Icons.add_rounded),
        label: const Text('Buat Pengajuan'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _ErrorView(message: _error!, onRetry: _load)
              : RefreshIndicator(
                  onRefresh: _load,
                  child: _items.isEmpty
                      ? const _EmptyView()
                      : ListView.separated(
                          padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
                          itemCount: _items.length,
                          separatorBuilder: (_, __) => const SizedBox(height: 10),
                          itemBuilder: (_, i) => _ItemCard(
                            item: _items[i],
                            onDelete: () => _delete(_items[i]),
                          ),
                        ),
                ),
    );
  }
}

// ─── Create Sheet ─────────────────────────────────────────────────────────────

class _CreateSheet extends StatefulWidget {
  final VoidCallback onCreated;
  const _CreateSheet({required this.onCreated});

  @override
  State<_CreateSheet> createState() => _CreateSheetState();
}

class _CreateSheetState extends State<_CreateSheet> {
  DateTime? _date;
  TimeOfDay _time = const TimeOfDay(hour: 12, minute: 0);
  bool _timePicked = false;
  final _reasonCtrl = TextEditingController();
  bool _isSaving    = false;

  @override
  void dispose() { _reasonCtrl.dispose(); super.dispose(); }

  Future<void> _pickDate() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context:     context,
      initialDate: now,
      firstDate:   now,
      lastDate:    now.add(const Duration(days: 7)),
      helpText:    'Pilih tanggal izin pulang',
    );
    if (picked != null && mounted) setState(() => _date = picked);
  }

  Future<void> _pickTime() async {
    final picked = await showTimePicker(
      context:     context,
      initialTime: _time,
      helpText:    'Pilih jam pulang',
    );
    if (picked != null && mounted) setState(() { _time = picked; _timePicked = true; });
  }

  Future<void> _submit() async {
    if (_date == null)    { _showSnack('Pilih tanggal terlebih dahulu.'); return; }
    if (!_timePicked)     { _showSnack('Pilih jam pulang terlebih dahulu.'); return; }
    if (_reasonCtrl.text.trim().isEmpty) { _showSnack('Alasan tidak boleh kosong.'); return; }
    setState(() => _isSaving = true);
    final dateStr = '${_date!.year}-${_date!.month.toString().padLeft(2,'0')}-${_date!.day.toString().padLeft(2,'0')}';
    final timeStr = '${_time.hour.toString().padLeft(2,'0')}:${_time.minute.toString().padLeft(2,'0')}';
    try {
      await ApiClient.post('/early-checkout', data: {
        'date':           dateStr,
        'requested_time': timeStr,
        'reason':         _reasonCtrl.text.trim(),
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
      content: Text(msg), backgroundColor: AppColors.red500, behavior: SnackBarBehavior.floating));
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
            decoration: BoxDecoration(color: AppColors.gray200, borderRadius: BorderRadius.circular(2)),
          )),
          const SizedBox(height: 16),
          const Text('Ajukan Izin Pulang Lebih Awal',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.gray800)),
          const SizedBox(height: 4),
          const Text('Perlu persetujuan guru atau admin.',
            style: TextStyle(fontSize: 11, color: AppColors.gray400)),
          const SizedBox(height: 16),

          // Date + Time row
          Row(children: [
            Expanded(
              child: GestureDetector(
                onTap: _pickDate,
                child: _PickerField(
                  icon: Icons.calendar_today_rounded,
                  iconColor: AppColors.emerald600,
                  label: 'Tanggal',
                  value: _date != null ? _fmtDate(_date!) : 'Pilih tanggal',
                  hasValue: _date != null,
                ),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: GestureDetector(
                onTap: _pickTime,
                child: _PickerField(
                  icon: Icons.access_time_rounded,
                  iconColor: AppColors.emerald600,
                  label: 'Jam Pulang',
                  value: _timePicked
                      ? '${_time.hour.toString().padLeft(2,'0')}:${_time.minute.toString().padLeft(2,'0')}'
                      : 'Pilih jam',
                  hasValue: _timePicked,
                ),
              ),
            ),
          ]),
          const SizedBox(height: 14),

          // Reason
          const Text('Alasan', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.gray600)),
          const SizedBox(height: 6),
          TextField(
            controller: _reasonCtrl,
            maxLines: 3,
            maxLength: 500,
            style: const TextStyle(fontSize: 13, color: AppColors.gray700),
            decoration: InputDecoration(
              hintText: 'Tuliskan alasan pulang lebih awal...',
              hintStyle: const TextStyle(color: AppColors.gray400, fontSize: 12),
              filled: true, fillColor: AppColors.gray50,
              counterStyle: const TextStyle(fontSize: 10),
              contentPadding: const EdgeInsets.all(12),
              border: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: const BorderSide(color: AppColors.gray200)),
              enabledBorder: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: const BorderSide(color: AppColors.gray200)),
              focusedBorder: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: const BorderSide(color: AppColors.emerald600, width: 2)),
            ),
          ),
          const SizedBox(height: 16),

          FilledButton(
            onPressed: _isSaving ? null : _submit,
            style: FilledButton.styleFrom(
              backgroundColor: AppColors.emerald600,
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
            ),
            child: _isSaving
                ? const SizedBox(width: 18, height: 18,
                    child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                : const Text('Kirim Pengajuan', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
          ),
        ],
      ),
    );
  }

  String _fmtDate(DateTime d) {
    const m = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return '${d.day} ${m[d.month]} ${d.year}';
  }
}

class _PickerField extends StatelessWidget {
  final IconData icon;
  final Color    iconColor;
  final String   label;
  final String   value;
  final bool     hasValue;
  const _PickerField({required this.icon, required this.iconColor, required this.label, required this.value, required this.hasValue});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.gray600)),
        const SizedBox(height: 6),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 13),
          decoration: BoxDecoration(
            color: AppColors.gray50, borderRadius: AppRadius.input,
            border: Border.all(color: AppColors.gray200),
          ),
          child: Row(children: [
            Icon(icon, size: 15, color: hasValue ? iconColor : AppColors.gray400),
            const SizedBox(width: 6),
            Expanded(child: Text(value,
              style: TextStyle(fontSize: 12, color: hasValue ? AppColors.gray700 : AppColors.gray400),
              overflow: TextOverflow.ellipsis)),
          ]),
        ),
      ],
    );
  }
}

// ─── Item Card ────────────────────────────────────────────────────────────────

class _ItemCard extends StatelessWidget {
  final EarlyCheckout item;
  final VoidCallback  onDelete;
  const _ItemCard({required this.item, required this.onDelete});

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
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          const Icon(Icons.logout_rounded, size: 16, color: AppColors.emerald600),
          const SizedBox(width: 6),
          Expanded(child: Text('${_fmtDate(item.date)} — pukul ${item.requestedTime}',
            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray800))),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
            decoration: BoxDecoration(color: item.statusBg, borderRadius: BorderRadius.circular(20)),
            child: Text(item.statusLabel,
              style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: item.statusColor)),
          ),
          if (item.isPending) ...[
            const SizedBox(width: 4),
            IconButton(
              onPressed: onDelete,
              icon: const Icon(Icons.delete_outline_rounded, size: 16, color: AppColors.red500),
              visualDensity: VisualDensity.compact, padding: EdgeInsets.zero,
            ),
          ],
        ]),
        const SizedBox(height: 6),
        Text(item.reason,
          style: const TextStyle(fontSize: 12, color: AppColors.gray500),
          maxLines: 2, overflow: TextOverflow.ellipsis),
        if (item.reviewerNote != null && item.reviewerNote!.isNotEmpty) ...[
          const SizedBox(height: 6),
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(color: AppColors.blue50, borderRadius: BorderRadius.circular(8)),
            child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
              const Icon(Icons.comment_outlined, size: 13, color: AppColors.blue600),
              const SizedBox(width: 6),
              Expanded(child: Text(item.reviewerNote!,
                style: const TextStyle(fontSize: 11, color: AppColors.blue600))),
            ]),
          ),
        ],
      ]),
    );
  }
}

class _EmptyView extends StatelessWidget {
  const _EmptyView();
  @override
  Widget build(BuildContext context) => const Center(
    child: Column(mainAxisSize: MainAxisSize.min, children: [
      Icon(Icons.logout_rounded, size: 56, color: AppColors.gray300),
      SizedBox(height: 12),
      Text('Belum ada pengajuan izin pulang', style: TextStyle(fontSize: 14, color: AppColors.gray400)),
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
