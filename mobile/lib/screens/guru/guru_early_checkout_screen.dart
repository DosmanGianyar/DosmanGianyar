import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/guru_models.dart';
import '../../services/guru_service.dart';
import '../../theme/app_colors.dart';
import 'widgets/guru_widgets.dart';

class GuruEarlyCheckoutScreen extends StatefulWidget {
  const GuruEarlyCheckoutScreen({super.key});

  @override
  State<GuruEarlyCheckoutScreen> createState() => _GuruEarlyCheckoutScreenState();
}

class _GuruEarlyCheckoutScreenState extends State<GuruEarlyCheckoutScreen> {
  String _status = 'pending';
  final List<GuruEarlyCheckout> _items = [];
  int _page = 1;
  bool _loading = false;
  bool _hasMore = true;
  String? _error;
  final _scroll = ScrollController();

  @override
  void initState() {
    super.initState();
    _load(reset: true);
    _scroll.addListener(() {
      if (_scroll.position.pixels >= _scroll.position.maxScrollExtent - 200 && !_loading && _hasMore) {
        _load();
      }
    });
  }

  @override
  void dispose() {
    _scroll.dispose();
    super.dispose();
  }

  Future<void> _load({bool reset = false}) async {
    if (_loading) return;
    if (reset) { _page = 1; _hasMore = true; }
    setState(() { _loading = true; _error = null; });
    try {
      final result = await GuruService.getEarlyCheckouts(status: _status, page: _page);
      if (mounted) {
        setState(() {
          if (reset) _items.clear();
          _items.addAll(result.data);
          _hasMore = result.meta.hasMore;
          _page++;
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) setState(() { _error = e.toString(); _loading = false; });
    }
  }

  Future<void> _approve(GuruEarlyCheckout item) async {
    final confirm = await showApproveDialog(context, title: 'Setujui Pulang Lebih Awal?');
    if (confirm != true || !mounted) return;
    try {
      await GuruService.approveEarlyCheckout(item.id);
      _load(reset: true);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Pengajuan disetujui'), backgroundColor: AppColors.green600),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppColors.red500),
        );
      }
    }
  }

  Future<void> _reject(GuruEarlyCheckout item) async {
    final note = await showRejectDialog(context, title: 'Tolak Pulang Lebih Awal');
    if (note == null || !mounted) return;
    try {
      await GuruService.rejectEarlyCheckout(item.id, note);
      _load(reset: true);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Pengajuan ditolak'), backgroundColor: AppColors.red500),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppColors.red500),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(title: const Text('Pulang Lebih Awal')),
      body: Column(
        children: [
          _buildTabBar(),
          Expanded(child: _buildBody()),
        ],
      ),
    );
  }

  Widget _buildTabBar() {
    return Container(
      color: AppColors.white,
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
      child: Row(
        children: [
          for (final s in [('pending', 'Menunggu'), ('approved', 'Disetujui'), ('rejected', 'Ditolak')])
            Expanded(
              child: GestureDetector(
                onTap: () { _status = s.$1; _load(reset: true); },
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 150),
                  margin: const EdgeInsets.symmetric(horizontal: 3),
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  decoration: BoxDecoration(
                    color: _status == s.$1 ? AppColors.orange600 : AppColors.gray100,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    s.$2,
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: _status == s.$1 ? Colors.white : AppColors.gray500,
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildBody() {
    if (_error != null && _items.isEmpty) return ErrorRetry(onRetry: () => _load(reset: true));
    if (!_loading && _items.isEmpty) {
      return const EmptyState(message: 'Tidak ada pengajuan pulang awal', icon: Icons.exit_to_app_outlined);
    }

    return RefreshIndicator(
      onRefresh: () => _load(reset: true),
      child: ListView.separated(
        controller: _scroll,
        padding: const EdgeInsets.all(16),
        itemCount: _items.length + (_loading ? 1 : 0),
        separatorBuilder: (_, __) => const SizedBox(height: 10),
        itemBuilder: (_, i) {
          if (i == _items.length) {
            return const Center(child: Padding(padding: EdgeInsets.all(12), child: CircularProgressIndicator()));
          }
          return _buildCard(_items[i]);
        },
      ),
    );
  }

  Widget _buildCard(GuruEarlyCheckout item) {
    final timeStr = item.requestedTime.length >= 5 ? item.requestedTime.substring(0, 5) : item.requestedTime;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppRadius.xl),
        border: Border.all(color: AppColors.gray100),
        boxShadow: AppShadow.sm,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(children: [
            Expanded(
              child: Text(
                item.studentName,
                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.gray800),
              ),
            ),
            StatusBadge(item.status),
          ]),
          const SizedBox(height: 4),
          Row(children: [
            const Icon(Icons.class_, size: 12, color: AppColors.gray400),
            const SizedBox(width: 4),
            Text(item.className, style: const TextStyle(fontSize: 12, color: AppColors.gray500)),
            const SizedBox(width: 12),
            const Icon(Icons.calendar_today_rounded, size: 12, color: AppColors.gray400),
            const SizedBox(width: 4),
            Text(_fmtDate(item.date), style: const TextStyle(fontSize: 12, color: AppColors.gray500)),
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              decoration: BoxDecoration(color: AppColors.orange50, borderRadius: BorderRadius.circular(6)),
              child: Row(children: [
                const Icon(Icons.schedule_rounded, size: 10, color: AppColors.orange600),
                const SizedBox(width: 3),
                Text(timeStr, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.orange600)),
              ]),
            ),
          ]),
          const SizedBox(height: 8),
          Text(item.reason, style: const TextStyle(fontSize: 12, color: AppColors.gray700), maxLines: 2, overflow: TextOverflow.ellipsis),
          if (item.reviewerNote != null && item.isRejected) ...[
            const SizedBox(height: 6),
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(color: AppColors.red50, borderRadius: BorderRadius.circular(8)),
              child: Text('Catatan: ${item.reviewerNote}', style: const TextStyle(fontSize: 11, color: AppColors.red500)),
            ),
          ],
          if (item.isPending) ...[
            const SizedBox(height: 12),
            Row(children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: () => _reject(item),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppColors.red500,
                    side: const BorderSide(color: AppColors.red500),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    padding: const EdgeInsets.symmetric(vertical: 8),
                  ),
                  child: const Text('Tolak', style: TextStyle(fontSize: 13)),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: FilledButton(
                  onPressed: () => _approve(item),
                  style: FilledButton.styleFrom(
                    backgroundColor: AppColors.orange600,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    padding: const EdgeInsets.symmetric(vertical: 8),
                  ),
                  child: const Text('Setujui', style: TextStyle(fontSize: 13)),
                ),
              ),
            ]),
          ],
        ],
      ),
    );
  }

  String _fmtDate(String d) {
    try {
      return DateFormat('d MMM y', 'id_ID').format(DateTime.parse(d));
    } catch (_) { return d; }
  }
}
