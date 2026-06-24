import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/extracurricular.dart';
import '../../providers/extracurricular_provider.dart';
import '../../theme/app_colors.dart';

class SessionDetailScreen extends StatefulWidget {
  final int sessionId;
  const SessionDetailScreen({super.key, required this.sessionId});

  @override
  State<SessionDetailScreen> createState() => _SessionDetailScreenState();
}

class _SessionDetailScreenState extends State<SessionDetailScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<ExtracurricularProvider>().fetchSessionDetail(widget.sessionId);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.gray50,
      body: Consumer<ExtracurricularProvider>(
        builder: (_, p, __) {
          if (p.loadingDetail) {
            return const Scaffold(
              body: Center(child: CircularProgressIndicator()),
            );
          }
          if (p.detailError != null) {
            return Scaffold(
              appBar: AppBar(title: const Text('Detail Sesi')),
              body: Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.error_outline, size: 48, color: AppColors.gray400),
                    const SizedBox(height: 12),
                    Text(p.detailError!, style: const TextStyle(color: AppColors.gray500)),
                    const SizedBox(height: 16),
                    FilledButton.icon(
                      onPressed: () => p.fetchSessionDetail(widget.sessionId),
                      icon: const Icon(Icons.refresh_rounded, size: 16),
                      label: const Text('Coba Lagi'),
                    ),
                  ],
                ),
              ),
            );
          }
          if (p.sessionDetail == null) return const SizedBox.shrink();

          final session  = p.sessionDetail!;
          final myRole   = p.sessionMyRole ?? 'member';
          final isKetua  = myRole == 'ketua' || myRole == 'pembina' || myRole == 'admin';
          final members  = p.sessionMembers;
          final isPast   = session.isPast;

          return CustomScrollView(
            slivers: [
              _SessionAppBar(session: session, isKetua: isKetua),
              SliverToBoxAdapter(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _SessionInfoCard(session: session),
                    _SummaryRow(session: session),
                    if (isKetua) _KetuaActions(session: session, isPast: isPast),
                    const SizedBox(height: 8),
                    Padding(
                      padding: const EdgeInsets.fromLTRB(16, 4, 16, 8),
                      child: Text(
                        'Daftar Anggota (${members.length})',
                        style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w700,
                          color: AppColors.gray800,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              SliverList(
                delegate: SliverChildBuilderDelegate(
                  (_, i) => _MemberTile(
                    member:   members[i],
                    canEdit:  isKetua && !isPast,
                    onToggle: () => p.toggleMemberAttendance(members[i].userId),
                  ),
                  childCount: members.length,
                ),
              ),
              if (isKetua && !isPast)
                SliverToBoxAdapter(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: _SaveButton(sessionId: session.id),
                  ),
                ),
              const SliverToBoxAdapter(child: SizedBox(height: 32)),
            ],
          );
        },
      ),
    );
  }
}

// ─── App bar ──────────────────────────────────────────────────────────────────

class _SessionAppBar extends StatelessWidget {
  final ExtraSession session;
  final bool         isKetua;
  const _SessionAppBar({required this.session, required this.isKetua});

  @override
  Widget build(BuildContext context) {
    return SliverAppBar(
      expandedHeight: 130,
      pinned: true,
      leading: IconButton(
        icon: const Icon(Icons.arrow_back_rounded, color: Colors.white),
        onPressed: () => Navigator.pop(context),
      ),
      flexibleSpace: FlexibleSpaceBar(
        background: Container(
          decoration: const BoxDecoration(gradient: AppColors.primaryGradient),
          child: SafeArea(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(20, 56, 20, 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  Text(
                    session.extracurricularName,
                    style: TextStyle(color: Colors.white.withOpacity(0.8), fontSize: 12),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    session.title,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

// ─── Info card ────────────────────────────────────────────────────────────────

class _SessionInfoCard extends StatelessWidget {
  final ExtraSession session;
  const _SessionInfoCard({required this.session});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: AppRadius.card,
        boxShadow: AppShadow.sm,
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            _InfoRow(Icons.calendar_today_rounded, 'Tanggal', _formatDate(session.sessionDate)),
            const Divider(height: 16),
            _InfoRow(Icons.access_time_rounded, 'Waktu',
                '${session.startTime} – ${session.endTime}'),
            if (session.location != null) ...[
              const Divider(height: 16),
              _InfoRow(Icons.location_on_outlined, 'Lokasi', session.location!),
            ],
            if (session.notes != null) ...[
              const Divider(height: 16),
              _InfoRow(Icons.notes_rounded, 'Catatan', session.notes!),
            ],
          ],
        ),
      ),
    );
  }

  String _formatDate(String dateStr) {
    try {
      final d = DateTime.parse(dateStr);
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const days   = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
      return '${days[d.weekday % 7]}, ${d.day} ${months[d.month - 1]} ${d.year}';
    } catch (_) {
      return dateStr;
    }
  }
}

class _InfoRow extends StatelessWidget {
  final IconData icon;
  final String   label;
  final String   value;
  const _InfoRow(this.icon, this.label, this.value);

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 16, color: AppColors.blue600),
        const SizedBox(width: 10),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(label, style: const TextStyle(fontSize: 11, color: AppColors.gray500)),
              const SizedBox(height: 2),
              Text(value, style: const TextStyle(fontSize: 14, color: AppColors.gray800, fontWeight: FontWeight.w500)),
            ],
          ),
        ),
      ],
    );
  }
}

// ─── Summary row ──────────────────────────────────────────────────────────────

class _SummaryRow extends StatelessWidget {
  final ExtraSession session;
  const _SummaryRow({required this.session});

  @override
  Widget build(BuildContext context) {
    final total = session.hadirCount + session.alpaCount;
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Row(
        children: [
          Expanded(child: _StatCard(label: 'Hadir',   value: session.hadirCount, color: AppColors.green500, bg: AppColors.green100)),
          const SizedBox(width: 10),
          Expanded(child: _StatCard(label: 'Alpa',    value: session.alpaCount,  color: AppColors.red500,   bg: AppColors.red100)),
          const SizedBox(width: 10),
          Expanded(child: _StatCard(label: 'Total',   value: total,              color: AppColors.blue600,  bg: AppColors.blue100)),
        ],
      ),
    );
  }
}

class _StatCard extends StatelessWidget {
  final String label;
  final int    value;
  final Color  color;
  final Color  bg;
  const _StatCard({required this.label, required this.value, required this.color, required this.bg});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 12),
      decoration: BoxDecoration(color: bg, borderRadius: AppRadius.card),
      child: Column(
        children: [
          Text(
            '$value',
            style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color),
          ),
          const SizedBox(height: 2),
          Text(label, style: TextStyle(fontSize: 11, color: color, fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}

// ─── Ketua actions ────────────────────────────────────────────────────────────

class _KetuaActions extends StatelessWidget {
  final ExtraSession session;
  final bool         isPast;
  const _KetuaActions({required this.session, required this.isPast});

  @override
  Widget build(BuildContext context) {
    if (isPast) return const SizedBox.shrink();

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
      child: Consumer<ExtracurricularProvider>(
        builder: (_, p, __) {
          final isOpen = session.isOpen;
          return SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: p.actionLoading ? null : () => _toggle(context, p),
              icon: Icon(
                isOpen ? Icons.lock_outline_rounded : Icons.lock_open_rounded,
                size: 16,
              ),
              label: Text(isOpen ? 'Tutup Absen' : 'Buka Absen'),
              style: OutlinedButton.styleFrom(
                foregroundColor: isOpen ? AppColors.red500 : AppColors.green500,
                side: BorderSide(color: isOpen ? AppColors.red500 : AppColors.green500),
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
              ),
            ),
          );
        },
      ),
    );
  }

  Future<void> _toggle(BuildContext context, ExtracurricularProvider p) async {
    final success = await p.toggleSessionOpen(session.id);
    if (context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(success
            ? (session.isOpen ? 'Absen ditutup.' : 'Absen dibuka.')
            : (p.actionError ?? 'Gagal')),
        backgroundColor: success ? AppColors.green500 : AppColors.red500,
      ));
    }
  }
}

// ─── Member tile ──────────────────────────────────────────────────────────────

class _MemberTile extends StatelessWidget {
  final ExtraSessionMember member;
  final bool               canEdit;
  final VoidCallback        onToggle;
  const _MemberTile({required this.member, required this.canEdit, required this.onToggle});

  @override
  Widget build(BuildContext context) {
    final attendance = member.attendance;
    final isHadir    = attendance == 'hadir';
    final isAlpa     = attendance == 'alpa';

    Color tileColor = AppColors.white;
    if (isHadir) tileColor = AppColors.green100;
    if (isAlpa)  tileColor = AppColors.red100;

    return Container(
      margin: const EdgeInsets.fromLTRB(16, 0, 16, 8),
      decoration: BoxDecoration(
        color: tileColor,
        borderRadius: AppRadius.card,
        border: Border.all(
          color: isHadir
              ? AppColors.green500.withOpacity(0.3)
              : isAlpa
                  ? AppColors.red500.withOpacity(0.3)
                  : AppColors.gray200,
        ),
      ),
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
        leading: CircleAvatar(
          radius: 20,
          backgroundColor: isHadir
              ? AppColors.green500
              : isAlpa
                  ? AppColors.red500
                  : AppColors.gray200,
          child: Text(
            member.name.isNotEmpty ? member.name[0].toUpperCase() : '?',
            style: TextStyle(
              color: (isHadir || isAlpa) ? Colors.white : AppColors.gray700,
              fontWeight: FontWeight.bold,
              fontSize: 15,
            ),
          ),
        ),
        title: Text(
          member.name,
          style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14, color: AppColors.gray800),
        ),
        subtitle: member.nis != null
            ? Text(member.nis!, style: const TextStyle(fontSize: 12, color: AppColors.gray500))
            : null,
        trailing: canEdit
            ? _AttendanceToggle(attendance: attendance, onToggle: onToggle)
            : _AttendanceBadge(attendance: attendance),
      ),
    );
  }
}

class _AttendanceToggle extends StatelessWidget {
  final String?      attendance;
  final VoidCallback onToggle;
  const _AttendanceToggle({required this.attendance, required this.onToggle});

  @override
  Widget build(BuildContext context) {
    final isHadir = attendance == 'hadir';
    return GestureDetector(
      onTap: onToggle,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: isHadir ? AppColors.green500 : AppColors.red500,
          borderRadius: BorderRadius.circular(20),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              isHadir ? Icons.check_circle_rounded : Icons.cancel_rounded,
              size: 14,
              color: Colors.white,
            ),
            const SizedBox(width: 4),
            Text(
              isHadir ? 'Hadir' : 'Alpa',
              style: const TextStyle(fontSize: 12, color: Colors.white, fontWeight: FontWeight.w600),
            ),
          ],
        ),
      ),
    );
  }
}

class _AttendanceBadge extends StatelessWidget {
  final String? attendance;
  const _AttendanceBadge({required this.attendance});

  @override
  Widget build(BuildContext context) {
    if (attendance == null) return const SizedBox.shrink();
    final isHadir = attendance == 'hadir';
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: isHadir ? AppColors.green500 : AppColors.red500,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        isHadir ? 'Hadir' : 'Alpa',
        style: const TextStyle(fontSize: 11, color: Colors.white, fontWeight: FontWeight.w600),
      ),
    );
  }
}

// ─── Save button ──────────────────────────────────────────────────────────────

class _SaveButton extends StatelessWidget {
  final int sessionId;
  const _SaveButton({required this.sessionId});

  @override
  Widget build(BuildContext context) {
    return Consumer<ExtracurricularProvider>(
      builder: (_, p, __) {
        return SizedBox(
          width: double.infinity,
          height: 48,
          child: FilledButton.icon(
            onPressed: p.actionLoading ? null : () => _save(context, p),
            icon: p.actionLoading
                ? const SizedBox(
                    width: 16,
                    height: 16,
                    child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                  )
                : const Icon(Icons.save_rounded, size: 18),
            label: const Text('Simpan Kehadiran', style: TextStyle(fontSize: 15)),
            style: FilledButton.styleFrom(
              backgroundColor: AppColors.blue600,
              shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
            ),
          ),
        );
      },
    );
  }

  Future<void> _save(BuildContext context, ExtracurricularProvider p) async {
    final success = await p.saveAttendance(sessionId);
    if (context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(success ? (p.actionSuccess ?? 'Kehadiran tersimpan') : (p.actionError ?? 'Gagal')),
        backgroundColor: success ? AppColors.green500 : AppColors.red500,
      ));
    }
  }
}
