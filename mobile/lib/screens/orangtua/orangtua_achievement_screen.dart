import 'package:flutter/material.dart';
import '../../models/achievement.dart';
import '../../services/orangtua_service.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';

/// Daftar prestasi anak — versi baca-saja untuk akun orangtua (tanpa form lapor prestasi).
class OrangtuaAchievementScreen extends StatefulWidget {
  final int    studentId;
  final String studentName;
  const OrangtuaAchievementScreen({super.key, required this.studentId, required this.studentName});

  @override
  State<OrangtuaAchievementScreen> createState() => _OrangtuaAchievementScreenState();
}

class _OrangtuaAchievementScreenState extends State<OrangtuaAchievementScreen> {
  AchievementStats? _stats;
  List<Achievement> _items = [];
  bool    _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _isLoading = true; _error = null; });
    try {
      final (stats, items) = await OrangtuaService.getAchievements(widget.studentId);
      if (mounted) setState(() { _stats = stats; _items = items; });
    } catch (e) {
      if (mounted) setState(() => _error = ApiClient.extractError(e));
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: Text('Prestasi — ${widget.studentName}',
          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 15)),
        backgroundColor: const Color(0xFF0F2460),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _ErrorView(message: _error!, onRetry: _load)
              : RefreshIndicator(
                  onRefresh: _load,
                  child: CustomScrollView(
                    slivers: [
                      if (_stats != null)
                        SliverToBoxAdapter(child: _StatsBar(stats: _stats!)),
                      if (_items.isEmpty)
                        const SliverFillRemaining(
                          child: Center(
                            child: Column(mainAxisSize: MainAxisSize.min, children: [
                              Icon(Icons.workspace_premium_rounded, size: 56, color: AppColors.gray300),
                              SizedBox(height: 12),
                              Text('Belum ada prestasi yang tercatat',
                                style: TextStyle(fontSize: 13, color: AppColors.gray400)),
                            ]),
                          ),
                        )
                      else
                        SliverPadding(
                          padding: EdgeInsets.fromLTRB(16, 0, 16, 24 + MediaQuery.of(context).padding.bottom),
                          sliver: SliverList.separated(
                            itemCount: _items.length,
                            separatorBuilder: (_, __) => const SizedBox(height: 8),
                            itemBuilder: (_, i) => _AchievementCard(item: _items[i]),
                          ),
                        ),
                    ],
                  ),
                ),
    );
  }
}

class _StatsBar extends StatelessWidget {
  final AchievementStats stats;
  const _StatsBar({required this.stats});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white, borderRadius: AppRadius.card,
        border: Border.all(color: AppColors.gray100), boxShadow: AppShadow.sm,
      ),
      child: Row(children: [
        _StatCell(count: stats.pending,  label: 'Menunggu', color: AppColors.amber500),
        _Divider(),
        _StatCell(count: stats.approved, label: 'Disetujui', color: AppColors.green500),
        _Divider(),
        _StatCell(count: stats.rejected, label: 'Ditolak',  color: AppColors.red500),
      ]),
    );
  }
}

class _StatCell extends StatelessWidget {
  final int count; final String label; final Color color;
  const _StatCell({required this.count, required this.label, required this.color});
  @override
  Widget build(BuildContext context) => Expanded(child: Column(children: [
    Text('$count', style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color)),
    Text(label, style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
  ]));
}

class _Divider extends StatelessWidget {
  @override
  Widget build(BuildContext context) => Container(width: 1, height: 32, color: AppColors.gray100);
}

class _AchievementCard extends StatelessWidget {
  final Achievement item;
  const _AchievementCard({required this.item});

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
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(color: item.levelBg, borderRadius: BorderRadius.circular(20)),
            child: Text(item.levelLabel, style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: item.levelColor)),
          ),
          const SizedBox(width: 6),
          if (item.rank != null) ...[
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(color: AppColors.yellow50, borderRadius: BorderRadius.circular(20)),
              child: Text(item.rank!, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.yellow600)),
            ),
            const SizedBox(width: 6),
          ],
          const Spacer(),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(color: item.statusBg, borderRadius: BorderRadius.circular(20)),
            child: Text(item.statusLabel, style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: item.statusColor)),
          ),
        ]),
        const SizedBox(height: 8),
        Text(item.title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray800)),
        if (item.categoryName != null) ...[
          const SizedBox(height: 2),
          Text(item.categoryName!, style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
        ],
        const SizedBox(height: 4),
        Row(children: [
          const Icon(Icons.calendar_today_rounded, size: 11, color: AppColors.gray400),
          const SizedBox(width: 4),
          Text(_fmtDate(item.achievementDate), style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
        ]),
        if (item.description != null && item.description!.isNotEmpty) ...[
          const SizedBox(height: 6),
          Text(item.description!, style: const TextStyle(fontSize: 12, color: AppColors.gray500),
            maxLines: 2, overflow: TextOverflow.ellipsis),
        ],
        if (item.rejectionReason != null && item.rejectionReason!.isNotEmpty) ...[
          const SizedBox(height: 6),
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(color: AppColors.red50, borderRadius: BorderRadius.circular(8)),
            child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
              const Icon(Icons.info_outline_rounded, size: 13, color: AppColors.red500),
              const SizedBox(width: 6),
              Expanded(child: Text(item.rejectionReason!, style: const TextStyle(fontSize: 11, color: AppColors.red500))),
            ]),
          ),
        ],
      ]),
    );
  }
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
