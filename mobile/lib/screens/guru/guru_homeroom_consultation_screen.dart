import 'package:flutter/material.dart';
import '../../models/guru_models.dart';
import '../../services/guru_service.dart';
import '../../theme/app_colors.dart';

class GuruHomeroomConsultationScreen extends StatefulWidget {
  const GuruHomeroomConsultationScreen({super.key});

  @override
  State<GuruHomeroomConsultationScreen> createState() =>
      _GuruHomeroomConsultationScreenState();
}

class _GuruHomeroomConsultationScreenState
    extends State<GuruHomeroomConsultationScreen> {
  List<GuruHomeroomConsultation> _items        = [];
  GuruHomeroomCounts?            _counts;
  int                            _studentCount = 0;
  String  _statusFilter = '';
  bool    _loading      = true;
  String? _error;

  static const _statusOpts = [
    ('', 'Semua'),
    ('pending',   'Menunggu'),
    ('scheduled', 'Dijadwalkan'),
    ('completed', 'Selesai'),
    ('cancelled', 'Dibatalkan'),
  ];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final result = await GuruService.getHomeroomConsultations(
        status: _statusFilter.isEmpty ? null : _statusFilter,
      );
      if (mounted) {
        setState(() {
          _studentCount = result['student_count'] as int? ?? 0;
          _items        = result['consultations'] as List<GuruHomeroomConsultation>;
          _counts       = result['counts'] as GuruHomeroomCounts;
          _loading      = false;
        });
      }
    } catch (e) {
      if (mounted) setState(() { _error = e.toString(); _loading = false; });
    }
  }

  void _updateItem(GuruHomeroomConsultation updated) {
    setState(() {
      final idx = _items.indexWhere((c) => c.id == updated.id);
      if (idx >= 0) _items[idx] = updated;
    });
    _load();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Jurnal Bimbingan Guru Wali'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: _load,
            tooltip: 'Muat ulang',
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _ErrorState(message: _error!, onRetry: _load)
              : _buildBody(),
    );
  }

  Widget _buildBody() {
    return RefreshIndicator(
      onRefresh: _load,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(14),
        child: Column(
          children: [
            // ─── Header guru wali
            _ClassHeader(studentCount: _studentCount, counts: _counts),
            const SizedBox(height: 10),

            // ─── Stats
            if (_counts != null) _StatsRow(counts: _counts!),
            const SizedBox(height: 10),

            // ─── Filter chips
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: _statusOpts.map((opt) {
                  final (val, label) = opt;
                  final selected = _statusFilter == val;
                  return Padding(
                    padding: const EdgeInsets.only(right: 6),
                    child: FilterChip(
                      label: Text(label, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: selected ? Colors.white : AppColors.gray600)),
                      selected: selected,
                      selectedColor: AppColors.blue600,
                      backgroundColor: Colors.white,
                      checkmarkColor: Colors.white,
                      side: BorderSide(color: selected ? AppColors.blue600 : AppColors.gray200),
                      padding: const EdgeInsets.symmetric(horizontal: 4),
                      onSelected: (_) {
                        setState(() => _statusFilter = val);
                        _load();
                      },
                    ),
                  );
                }).toList(),
              ),
            ),
            const SizedBox(height: 8),

            // ─── List
            if (_items.isEmpty)
              _EmptyState(filter: _statusFilter)
            else
              ...List.generate(_items.length, (i) => _ConsultationCard(
                consultation: _items[i],
                onUpdate: _updateItem,
              )),
          ],
        ),
      ),
    );
  }
}

// ─── Header ───────────────────────────────────────────────────────────────────

class _ClassHeader extends StatelessWidget {
  final int studentCount;
  final GuruHomeroomCounts? counts;
  const _ClassHeader({required this.studentCount, this.counts});

  @override
  Widget build(BuildContext context) {
    final total = (counts?.pending ?? 0) + (counts?.scheduled ?? 0) +
        (counts?.completed ?? 0) + (counts?.cancelled ?? 0);
    return Container(
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF4F46E5), Color(0xFF1D4ED8)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      padding: const EdgeInsets.all(16),
      child: Row(
        children: [
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            const Text('Guru Wali', style: TextStyle(fontSize: 11, color: Color(0xFFC7D2FE))),
            Text('$studentCount Siswa Terdaftar',
                style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Colors.white)),
          ])),
          Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
            Text('$total', style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Colors.white)),
            const Text('Total Pengajuan', style: TextStyle(fontSize: 10, color: Color(0xFFC7D2FE))),
          ]),
        ],
      ),
    );
  }
}

// ─── Stats Row ────────────────────────────────────────────────────────────────

class _StatsRow extends StatelessWidget {
  final GuruHomeroomCounts counts;
  const _StatsRow({required this.counts});

  @override
  Widget build(BuildContext context) {
    return Row(children: [
      _StatBox(value: counts.pending,   label: 'Menunggu',     color: const Color(0xFFF59E0B)),
      const SizedBox(width: 6),
      _StatBox(value: counts.scheduled, label: 'Dijadwalkan',  color: AppColors.blue600),
      const SizedBox(width: 6),
      _StatBox(value: counts.completed, label: 'Selesai',      color: const Color(0xFF10B981)),
      const SizedBox(width: 6),
      _StatBox(value: counts.cancelled, label: 'Dibatalkan',   color: AppColors.gray400),
    ]);
  }
}

class _StatBox extends StatelessWidget {
  final int value;
  final String label;
  final Color color;
  const _StatBox({required this.value, required this.label, required this.color});

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.gray100),
        ),
        child: Column(children: [
          Text('$value', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: color)),
          const SizedBox(height: 2),
          Text(label, style: const TextStyle(fontSize: 9, color: AppColors.gray500), textAlign: TextAlign.center),
        ]),
      ),
    );
  }
}

// ─── Consultation Card ────────────────────────────────────────────────────────

class _ConsultationCard extends StatelessWidget {
  final GuruHomeroomConsultation consultation;
  final void Function(GuruHomeroomConsultation) onUpdate;
  const _ConsultationCard({required this.consultation, required this.onUpdate});

  static const _statusColor = {
    'pending':   Color(0xFFF59E0B),
    'scheduled': Color(0xFF3B82F6),
    'completed': Color(0xFF10B981),
    'cancelled': Color(0xFF9CA3AF),
  };
  static const _statusBg = {
    'pending':   Color(0xFFFEF3C7),
    'scheduled': Color(0xFFDBEAFE),
    'completed': Color(0xFFD1FAE5),
    'cancelled': Color(0xFFF3F4F6),
  };

  @override
  Widget build(BuildContext context) {
    final c = consultation;
    final statusC  = _statusColor[c.status] ?? AppColors.gray400;
    final statusBg = _statusBg[c.status]    ?? AppColors.gray100;

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.gray100),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          Padding(
            padding: const EdgeInsets.fromLTRB(14, 12, 14, 8),
            child: Row(children: [
              CircleAvatar(
                radius: 18,
                backgroundColor: AppColors.blue100,
                child: Text(
                  c.studentName.isNotEmpty ? c.studentName[0].toUpperCase() : '?',
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.blue600),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(c.studentName, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.gray800)),
                Text('${c.studentNis ?? '—'} · ${c.createdAt}',
                  style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
              ])),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(color: statusBg, borderRadius: BorderRadius.circular(20)),
                child: Text(c.statusLabel, style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: statusC)),
              ),
            ]),
          ),

          // Topik
          if (c.topic != null || c.studentNote != null)
            Padding(
              padding: const EdgeInsets.fromLTRB(14, 0, 14, 8),
              child: Container(
                width: double.infinity,
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(color: AppColors.gray50, borderRadius: BorderRadius.circular(8)),
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  if (c.topic != null) ...[
                    const Text('Topik', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.gray400)),
                    const SizedBox(height: 2),
                    Text(c.topic!, style: const TextStyle(fontSize: 12, color: AppColors.gray700)),
                    const SizedBox(height: 6),
                  ],
                  if (c.studentNote != null) ...[
                    const Text('Catatan Siswa', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.gray400)),
                    const SizedBox(height: 2),
                    Text(c.studentNote!, style: const TextStyle(fontSize: 12, color: AppColors.gray700)),
                  ],
                ]),
              ),
            ),

          // Info tambahan berdasarkan status
          if (c.status == 'scheduled' && c.scheduledDate != null)
            Padding(
              padding: const EdgeInsets.fromLTRB(14, 0, 14, 8),
              child: Row(children: [
                const Icon(Icons.calendar_today_rounded, size: 13, color: AppColors.blue600),
                const SizedBox(width: 4),
                Text('Dijadwalkan: ${c.scheduledDate}', style: const TextStyle(fontSize: 11, color: AppColors.blue600, fontWeight: FontWeight.w600)),
              ]),
            ),

          if (c.status == 'completed') ...[
            if (c.conductedDate != null)
              Padding(
                padding: const EdgeInsets.fromLTRB(14, 0, 14, 4),
                child: Row(children: [
                  const Icon(Icons.check_circle_rounded, size: 13, color: Color(0xFF10B981)),
                  const SizedBox(width: 4),
                  Text('Dilaksanakan: ${c.conductedDate}', style: const TextStyle(fontSize: 11, color: Color(0xFF10B981), fontWeight: FontWeight.w600)),
                ]),
              ),
            if (c.teacherNote != null)
              Padding(
                padding: const EdgeInsets.fromLTRB(14, 0, 14, 8),
                child: Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(color: const Color(0xFFD1FAE5), borderRadius: BorderRadius.circular(8)),
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    const Text('Catatan Guru', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF065F46))),
                    const SizedBox(height: 2),
                    Text(c.teacherNote!, style: const TextStyle(fontSize: 12, color: Color(0xFF064E3B))),
                    if (c.followUp != null) ...[
                      const SizedBox(height: 6),
                      const Text('Tindak Lanjut', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF065F46))),
                      const SizedBox(height: 2),
                      Text(c.followUp!, style: const TextStyle(fontSize: 12, color: Color(0xFF064E3B))),
                    ],
                  ]),
                ),
              ),
          ],

          if (c.status == 'cancelled' && c.cancelledReason != null)
            Padding(
              padding: const EdgeInsets.fromLTRB(14, 0, 14, 8),
              child: Text('Alasan: ${c.cancelledReason}',
                style: const TextStyle(fontSize: 11, color: AppColors.gray500, fontStyle: FontStyle.italic)),
            ),

          // Tombol aksi
          if (c.status == 'pending' || c.status == 'scheduled')
            Padding(
              padding: const EdgeInsets.fromLTRB(14, 0, 14, 12),
              child: Row(children: [
                if (c.status == 'pending')
                  Expanded(child: _ActionBtn(
                    label: 'Jadwalkan',
                    color: AppColors.blue600,
                    onTap: () => _showScheduleDialog(context),
                  )),
                if (c.status == 'scheduled') ...[
                  Expanded(child: _ActionBtn(
                    label: 'Selesaikan',
                    color: const Color(0xFF10B981),
                    onTap: () => _showCompleteDialog(context),
                  )),
                ],
                const SizedBox(width: 8),
                Expanded(child: _ActionBtn(
                  label: 'Batalkan',
                  color: AppColors.gray400,
                  onTap: () => _confirmCancel(context),
                )),
              ]),
            ),
        ],
      ),
    );
  }

  Future<void> _showScheduleDialog(BuildContext context) async {
    final now  = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: now,
      firstDate: now,
      lastDate: now.add(const Duration(days: 365)),
    );
    if (picked == null || !context.mounted) return;

    final dateStr = '${picked.year}-${picked.month.toString().padLeft(2,'0')}-${picked.day.toString().padLeft(2,'0')}';
    try {
      final updated = await GuruService.scheduleConsultation(consultation.id, dateStr);
      onUpdate(updated);
      if (context.mounted) _snack(context, 'Bimbingan berhasil dijadwalkan.', Colors.green);
    } catch (e) {
      if (context.mounted) _snack(context, e.toString(), Colors.red);
    }
  }

  Future<void> _showCompleteDialog(BuildContext context) async {
    final noteCtrl    = TextEditingController();
    final followCtrl  = TextEditingController();
    String conductedDate = DateTime.now().toIso8601String().substring(0, 10);

    await showDialog<void>(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setS) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: const Text('Selesaikan Bimbingan', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
          content: SingleChildScrollView(
            child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.start, children: [
              const Text('Tanggal Pelaksanaan', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.gray600)),
              const SizedBox(height: 4),
              GestureDetector(
                onTap: () async {
                  final now = DateTime.now();
                  final p = await showDatePicker(
                    context: ctx,
                    initialDate: now,
                    firstDate: DateTime(now.year - 1),
                    lastDate: now,
                  );
                  if (p != null) setS(() => conductedDate = '${p.year}-${p.month.toString().padLeft(2,'0')}-${p.day.toString().padLeft(2,'0')}');
                },
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  decoration: BoxDecoration(border: Border.all(color: AppColors.gray200), borderRadius: BorderRadius.circular(8)),
                  child: Row(children: [
                    const Icon(Icons.calendar_today_rounded, size: 14, color: AppColors.gray400),
                    const SizedBox(width: 8),
                    Text(conductedDate, style: const TextStyle(fontSize: 13)),
                  ]),
                ),
              ),
              const SizedBox(height: 12),
              const Text('Catatan Guru *', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.gray600)),
              const SizedBox(height: 4),
              TextField(
                controller: noteCtrl,
                maxLines: 4,
                decoration: InputDecoration(
                  hintText: 'Isi catatan hasil bimbingan...',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: AppColors.gray200)),
                  contentPadding: const EdgeInsets.all(10),
                ),
              ),
              const SizedBox(height: 12),
              const Text('Tindak Lanjut', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.gray600)),
              const SizedBox(height: 4),
              TextField(
                controller: followCtrl,
                maxLines: 2,
                decoration: InputDecoration(
                  hintText: 'Rencana tindak lanjut (opsional)...',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: AppColors.gray200)),
                  contentPadding: const EdgeInsets.all(10),
                ),
              ),
            ]),
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
            FilledButton(
              style: FilledButton.styleFrom(backgroundColor: const Color(0xFF10B981)),
              onPressed: () async {
                if (noteCtrl.text.trim().isEmpty) {
                  _snack(ctx, 'Catatan guru wajib diisi', Colors.orange);
                  return;
                }
                Navigator.pop(ctx);
                try {
                  final updated = await GuruService.completeConsultation(
                    id: consultation.id,
                    conductedDate: conductedDate,
                    teacherNote: noteCtrl.text.trim(),
                    followUp: followCtrl.text.trim().isEmpty ? null : followCtrl.text.trim(),
                  );
                  onUpdate(updated);
                  if (context.mounted) _snack(context, 'Jurnal bimbingan berhasil disimpan.', Colors.green);
                } catch (e) {
                  if (context.mounted) _snack(context, e.toString(), Colors.red);
                }
              },
              child: const Text('Simpan'),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _confirmCancel(BuildContext context) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Batalkan Bimbingan?', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
        content: const Text('Pengajuan bimbingan ini akan dibatalkan.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Tidak')),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: Colors.red),
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Ya, Batalkan'),
          ),
        ],
      ),
    );
    if (confirmed != true || !context.mounted) return;
    try {
      final updated = await GuruService.cancelConsultation(consultation.id);
      onUpdate(updated);
      if (context.mounted) _snack(context, 'Pengajuan dibatalkan.', Colors.grey);
    } catch (e) {
      if (context.mounted) _snack(context, e.toString(), Colors.red);
    }
  }

  void _snack(BuildContext context, String msg, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: color,
      behavior: SnackBarBehavior.floating,
    ));
  }
}

// ─── Action Button ────────────────────────────────────────────────────────────

class _ActionBtn extends StatelessWidget {
  final String label;
  final Color  color;
  final VoidCallback onTap;
  const _ActionBtn({required this.label, required this.color, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.12),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: color.withValues(alpha: 0.3)),
        ),
        child: Text(label,
          textAlign: TextAlign.center,
          style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: color)),
      ),
    );
  }
}

// ─── Empty State ──────────────────────────────────────────────────────────────

class _EmptyState extends StatelessWidget {
  final String filter;
  const _EmptyState({required this.filter});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(top: 40),
      child: Column(children: [
        Icon(Icons.chat_bubble_outline_rounded, size: 48, color: AppColors.gray200),
        const SizedBox(height: 12),
        Text(
          filter.isEmpty ? 'Belum ada pengajuan bimbingan' : 'Tidak ada pengajuan dengan status ini',
          style: const TextStyle(fontSize: 14, color: AppColors.gray400),
          textAlign: TextAlign.center,
        ),
      ]),
    );
  }
}

// ─── Error State ──────────────────────────────────────────────────────────────

class _ErrorState extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;
  const _ErrorState({required this.message, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          const Icon(Icons.error_outline_rounded, size: 40, color: AppColors.gray300),
          const SizedBox(height: 12),
          Text(message, textAlign: TextAlign.center, style: const TextStyle(fontSize: 13, color: AppColors.gray500)),
          const SizedBox(height: 16),
          FilledButton(onPressed: onRetry, child: const Text('Coba Lagi')),
        ]),
      ),
    );
  }
}
