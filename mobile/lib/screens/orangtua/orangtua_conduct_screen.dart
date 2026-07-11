import 'package:flutter/material.dart';
import '../../models/conduct_log.dart';
import '../../services/orangtua_service.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';

/// Catatan perilaku (positif & negatif) anak — versi baca-saja untuk akun orangtua.
class OrangtuaConductScreen extends StatefulWidget {
  final int    studentId;
  final String studentName;
  const OrangtuaConductScreen({super.key, required this.studentId, required this.studentName});

  @override
  State<OrangtuaConductScreen> createState() => _OrangtuaConductScreenState();
}

class _OrangtuaConductScreenState extends State<OrangtuaConductScreen> {
  ConductSummary? _summary;
  List<ConductLog> _logs = [];
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
      final (summary, logs) = await OrangtuaService.getConduct(widget.studentId);
      if (mounted) setState(() { _summary = summary; _logs = logs; });
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
        title: Text('Catatan Perilaku — ${widget.studentName}',
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
                      SliverToBoxAdapter(child: _SummaryCards(summary: _summary!)),
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

class _SummaryCards extends StatelessWidget {
  final ConductSummary summary;
  const _SummaryCards({required this.summary});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Row(children: [
        Expanded(child: _CountCard(
          label: 'Catatan Positif', count: summary.prestasiCount,
          color: AppColors.emerald600, bg: AppColors.emerald50, icon: Icons.thumb_up_rounded,
        )),
        const SizedBox(width: 12),
        Expanded(child: _CountCard(
          label: 'Catatan Negatif', count: summary.pelanggaranCount,
          color: AppColors.red500, bg: AppColors.red50, icon: Icons.thumb_down_rounded,
        )),
      ]),
    );
  }
}

class _CountCard extends StatelessWidget {
  final String   label;
  final int      count;
  final Color    color, bg;
  final IconData icon;
  const _CountCard({required this.label, required this.count, required this.color, required this.bg, required this.icon});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      decoration: BoxDecoration(color: bg, borderRadius: AppRadius.card),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Icon(icon, color: color, size: 20),
        const SizedBox(height: 8),
        Text('$count', style: TextStyle(fontSize: 26, fontWeight: FontWeight.bold, color: color, height: 1)),
        const SizedBox(height: 2),
        Text(label, style: const TextStyle(fontSize: 11, color: AppColors.gray500)),
      ]),
    );
  }
}

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
          Text(_fmtDate(log.date), style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
          if (log.teacherName != null) ...[
            const SizedBox(height: 2),
            Text('Oleh: ${log.teacherName}', style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
          ],
          if (log.note != null && log.note!.isNotEmpty) ...[
            const SizedBox(height: 6),
            Text(log.note!, style: const TextStyle(fontSize: 12, color: AppColors.gray600),
              maxLines: 2, overflow: TextOverflow.ellipsis),
          ],
          const SizedBox(height: 4),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
            decoration: BoxDecoration(color: AppColors.gray50, borderRadius: BorderRadius.circular(4)),
            child: Text(log.context, style: const TextStyle(fontSize: 10, color: AppColors.gray500)),
          ),
        ])),
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
