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
        title: const Text('Pelanggaran & Poin',
          style: TextStyle(fontWeight: FontWeight.w600, fontSize: 16)),
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
                      SliverToBoxAdapter(child: _SummaryCards(summary: _summary!)),
                      if (_logs.isEmpty)
                        const SliverFillRemaining(
                          child: Center(
                            child: Column(mainAxisSize: MainAxisSize.min, children: [
                              Icon(Icons.verified_user_outlined, size: 56, color: AppColors.gray300),
                              SizedBox(height: 12),
                              Text('Tidak ada catatan pelanggaran atau poin',
                                style: TextStyle(fontSize: 13, color: AppColors.gray400)),
                            ]),
                          ),
                        )
                      else
                        SliverPadding(
                          padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
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
}

// ─── Summary Cards ────────────────────────────────────────────────────────────

class _SummaryCards extends StatelessWidget {
  final ConductSummary summary;
  const _SummaryCards({required this.summary});

  @override
  Widget build(BuildContext context) {
    final total = summary.totalPoint;
    return Container(
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF0F2460), Color(0xFF1E3A8A)],
          begin: Alignment.topLeft, end: Alignment.bottomRight,
        ),
        borderRadius: AppRadius.card,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Total Poin Anda',
            style: TextStyle(color: Color(0xFFBFDBFE), fontSize: 12)),
          const SizedBox(height: 4),
          Row(children: [
            Text('$total',
              style: TextStyle(
                color: total >= 0 ? Colors.white : AppColors.red300,
                fontSize: 32, fontWeight: FontWeight.bold, height: 1,
              )),
            const SizedBox(width: 6),
            Text('poin',
              style: const TextStyle(color: Color(0xFFBFDBFE), fontSize: 14)),
          ]),
          const SizedBox(height: 16),
          Row(children: [
            Expanded(child: _MiniStat(
              label: 'Poin Prestasi',
              value: '+${summary.prestasiPoint}',
              color: AppColors.green400,
              icon: Icons.arrow_upward_rounded,
            )),
            const SizedBox(width: 12),
            Expanded(child: _MiniStat(
              label: 'Poin Pelanggaran',
              value: '-${summary.pelanggaranPoint}',
              color: AppColors.red300,
              icon: Icons.arrow_downward_rounded,
            )),
          ]),
        ],
      ),
    );
  }
}

class _MiniStat extends StatelessWidget {
  final String label, value;
  final Color  color;
  final IconData icon;
  const _MiniStat({required this.label, required this.value, required this.color, required this.icon});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(children: [
        Icon(icon, color: color, size: 16),
        const SizedBox(width: 6),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(label, style: const TextStyle(color: Color(0xFFBFDBFE), fontSize: 10)),
          Text(value, style: TextStyle(color: color, fontSize: 15, fontWeight: FontWeight.bold)),
        ])),
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
          child: Center(child: Text(log.pointLabel,
            style: TextStyle(
              color: log.typeColor,
              fontWeight: FontWeight.bold, fontSize: 13))),
        ),
        const SizedBox(width: 12),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Expanded(child: Text(log.categoryName,
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
          if (log.note != null && log.note!.isNotEmpty) ...[
            const SizedBox(height: 6),
            Text(log.note!,
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
