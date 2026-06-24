import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/extracurricular.dart';
import '../../providers/extracurricular_provider.dart';
import '../../theme/app_colors.dart';
import 'session_detail_screen.dart';
import 'create_session_screen.dart';

class ExtracurricularScreen extends StatefulWidget {
  const ExtracurricularScreen({super.key});

  @override
  State<ExtracurricularScreen> createState() => _ExtracurricularScreenState();
}

class _ExtracurricularScreenState extends State<ExtracurricularScreen>
    with SingleTickerProviderStateMixin {
  late final TabController _tab;

  @override
  void initState() {
    super.initState();
    _tab = TabController(length: 3, vsync: this);
    WidgetsBinding.instance.addPostFrameCallback((_) => _bootstrap());
  }

  @override
  void dispose() {
    _tab.dispose();
    super.dispose();
  }

  Future<void> _bootstrap() async {
    final p = context.read<ExtracurricularProvider>();
    await Future.wait([p.fetchMy(), p.fetchAll(), p.fetchSessions()]);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.gray50,
      body: NestedScrollView(
        headerSliverBuilder: (_, __) => [
          SliverAppBar(
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
                    padding: const EdgeInsets.fromLTRB(56, 12, 20, 0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Ekstrakurikuler',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Kegiatan ekstrakurikuler sekolah',
                          style: TextStyle(color: Colors.white.withOpacity(0.8), fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
            expandedHeight: 90,
            bottom: TabBar(
              controller: _tab,
              indicatorColor: Colors.white,
              labelColor: Colors.white,
              unselectedLabelColor: Colors.white60,
              labelStyle: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
              tabs: const [
                Tab(text: 'Ekstra Saya'),
                Tab(text: 'Jelajahi'),
                Tab(text: 'Sesi'),
              ],
            ),
          ),
        ],
        body: TabBarView(
          controller: _tab,
          children: const [
            _MyExtrasTab(),
            _BrowseTab(),
            _SessionsTab(),
          ],
        ),
      ),
    );
  }

}

// ─── Tab 1: My Extras ─────────────────────────────────────────────────────────

class _MyExtrasTab extends StatelessWidget {
  const _MyExtrasTab();

  @override
  Widget build(BuildContext context) {
    return Consumer<ExtracurricularProvider>(
      builder: (_, p, __) {
        if (p.loadingMy) return const _Loader();
        if (p.myError != null) return _ErrorView(p.myError!, onRetry: p.fetchMy);
        if (p.myExtras.isEmpty) {
          return const _EmptyView(
            icon: Icons.school_outlined,
            title: 'Belum Ikut Ekstra',
            subtitle: 'Cari dan daftar ekstra di tab Jelajahi.',
          );
        }
        return RefreshIndicator(
          onRefresh: p.fetchMy,
          child: ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: p.myExtras.length,
            itemBuilder: (_, i) => _MyExtraCard(item: p.myExtras[i]),
          ),
        );
      },
    );
  }
}

class _MyExtraCard extends StatelessWidget {
  final MyExtracurricularItem item;
  const _MyExtraCard({required this.item});

  @override
  Widget build(BuildContext context) {
    final Color statusColor;
    final Color statusBg;
    switch (item.myStatus) {
      case 'active':
        statusColor = AppColors.green900;
        statusBg    = AppColors.green100;
      case 'pending_join':
        statusColor = const Color(0xFF92400E);
        statusBg    = AppColors.amber100;
      case 'pending_leave':
        statusColor = const Color(0xFF991B1B);
        statusBg    = AppColors.red100;
      default:
        statusColor = AppColors.gray700;
        statusBg    = AppColors.gray100;
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: AppRadius.card,
        boxShadow: AppShadow.sm,
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            _ExtraLogo(logoUrl: item.logoUrl, size: 52),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    item.name,
                    style: const TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w700,
                      color: AppColors.gray800,
                    ),
                  ),
                  if (item.pembinaName != null) ...[
                    const SizedBox(height: 2),
                    Text(
                      'Pembina: ${item.pembinaName}',
                      style: const TextStyle(fontSize: 12, color: AppColors.gray500),
                    ),
                  ],
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      _Badge(
                        label: item.statusLabel,
                        color: statusColor,
                        bg: statusBg,
                      ),
                      if (item.isKetua) ...[
                        const SizedBox(width: 6),
                        _Badge(
                          label: item.roleLabel,
                          color: AppColors.blue600,
                          bg: AppColors.blue100,
                          icon: Icons.star_rounded,
                        ),
                      ],
                    ],
                  ),
                ],
              ),
            ),
            if (item.isActive) ...[
              const SizedBox(width: 8),
              _LeaveButton(item: item),
            ],
          ],
        ),
      ),
    );
  }
}

class _LeaveButton extends StatelessWidget {
  final MyExtracurricularItem item;
  const _LeaveButton({required this.item});

  @override
  Widget build(BuildContext context) {
    return Consumer<ExtracurricularProvider>(
      builder: (_, p, __) {
        if (p.actionLoading) {
          return const SizedBox(
            width: 20,
            height: 20,
            child: CircularProgressIndicator(strokeWidth: 2),
          );
        }
        return TextButton(
          onPressed: () => _confirmLeave(context, p),
          style: TextButton.styleFrom(
            foregroundColor: AppColors.red500,
            padding: const EdgeInsets.symmetric(horizontal: 8),
          ),
          child: const Text('Keluar', style: TextStyle(fontSize: 12)),
        );
      },
    );
  }

  Future<void> _confirmLeave(BuildContext context, ExtracurricularProvider p) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Keluar dari Ekstra?'),
        content: Text('Permintaan keluar dari "${item.name}" akan dikirim ke admin.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Batal')),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            style: FilledButton.styleFrom(backgroundColor: AppColors.red500),
            child: const Text('Keluar'),
          ),
        ],
      ),
    );
    if (ok == true && context.mounted) {
      final success = await p.leaveExtra(item.id);
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(success ? (p.actionSuccess ?? 'Berhasil') : (p.actionError ?? 'Gagal')),
          backgroundColor: success ? AppColors.green500 : AppColors.red500,
        ));
      }
    }
  }
}

// ─── Tab 2: Browse ────────────────────────────────────────────────────────────

class _BrowseTab extends StatelessWidget {
  const _BrowseTab();

  @override
  Widget build(BuildContext context) {
    return Consumer<ExtracurricularProvider>(
      builder: (_, p, __) {
        if (p.loadingAll) return const _Loader();
        if (p.allError != null) return _ErrorView(p.allError!, onRetry: p.fetchAll);
        if (p.allExtras.isEmpty) {
          return const _EmptyView(
            icon: Icons.explore_outlined,
            title: 'Belum Ada Ekstra',
            subtitle: 'Belum ada ekstrakurikuler yang tersedia.',
          );
        }
        return RefreshIndicator(
          onRefresh: p.fetchAll,
          child: ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: p.allExtras.length,
            itemBuilder: (_, i) => _BrowseCard(item: p.allExtras[i]),
          ),
        );
      },
    );
  }
}

class _BrowseCard extends StatelessWidget {
  final ExtracurricularItem item;
  const _BrowseCard({required this.item});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: AppRadius.card,
        boxShadow: AppShadow.sm,
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                _ExtraLogo(logoUrl: item.logoUrl, size: 52),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        item.name,
                        style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w700,
                          color: AppColors.gray800,
                        ),
                      ),
                      if (item.pembinaName != null) ...[
                        const SizedBox(height: 2),
                        Text(
                          'Pembina: ${item.pembinaName}',
                          style: const TextStyle(fontSize: 12, color: AppColors.gray500),
                        ),
                      ],
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          const Icon(Icons.people_outline, size: 13, color: AppColors.gray500),
                          const SizedBox(width: 4),
                          Text(
                            item.maxMembers != null
                                ? '${item.activeMembers} / ${item.maxMembers} anggota'
                                : '${item.activeMembers} anggota',
                            style: const TextStyle(fontSize: 12, color: AppColors.gray500),
                          ),
                          if (item.isFull) ...[
                            const SizedBox(width: 6),
                            _Badge(label: 'Penuh', color: AppColors.red500, bg: AppColors.red100),
                          ],
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
            if (item.description != null) ...[
              const SizedBox(height: 10),
              Text(
                item.description!,
                style: const TextStyle(fontSize: 13, color: AppColors.gray700, height: 1.4),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ],
            const SizedBox(height: 12),
            _JoinLeaveButton(item: item),
          ],
        ),
      ),
    );
  }
}

class _JoinLeaveButton extends StatelessWidget {
  final ExtracurricularItem item;
  const _JoinLeaveButton({required this.item});

  @override
  Widget build(BuildContext context) {
    return Consumer<ExtracurricularProvider>(
      builder: (_, p, __) {
        if (p.actionLoading) {
          return const Center(
            child: SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2)),
          );
        }

        if (item.isMember) {
          return Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 10),
            decoration: BoxDecoration(
              color: AppColors.gray100,
              borderRadius: AppRadius.button,
            ),
            child: Text(
              item.statusLabel,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 13, color: AppColors.gray500, fontWeight: FontWeight.w600),
            ),
          );
        }

        if (item.isFull) {
          return Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 10),
            decoration: BoxDecoration(
              color: AppColors.red100,
              borderRadius: AppRadius.button,
            ),
            child: const Text(
              'Kuota Penuh',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 13, color: AppColors.red500, fontWeight: FontWeight.w600),
            ),
          );
        }

        return SizedBox(
          width: double.infinity,
          child: FilledButton.icon(
            onPressed: () => _join(context, p),
            icon: const Icon(Icons.add_rounded, size: 16),
            label: const Text('Daftar'),
            style: FilledButton.styleFrom(
              backgroundColor: AppColors.blue600,
              padding: const EdgeInsets.symmetric(vertical: 10),
              shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
            ),
          ),
        );
      },
    );
  }

  Future<void> _join(BuildContext context, ExtracurricularProvider p) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Daftar Ekstra?'),
        content: Text('Kamu akan mengirim permintaan bergabung ke "${item.name}".'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Batal')),
          FilledButton(onPressed: () => Navigator.pop(context, true), child: const Text('Daftar')),
        ],
      ),
    );
    if (ok == true && context.mounted) {
      final success = await p.joinExtra(item.id);
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(success ? (p.actionSuccess ?? 'Permintaan terkirim') : (p.actionError ?? 'Gagal')),
          backgroundColor: success ? AppColors.green500 : AppColors.red500,
        ));
      }
    }
  }
}

// ─── Tab 3: Sessions ──────────────────────────────────────────────────────────

class _SessionsTab extends StatefulWidget {
  const _SessionsTab();

  @override
  State<_SessionsTab> createState() => _SessionsTabState();
}

class _SessionsTabState extends State<_SessionsTab> {
  bool _showPast = false;

  @override
  Widget build(BuildContext context) {
    return Consumer<ExtracurricularProvider>(
      builder: (_, p, __) {
        final sessions = _showPast ? p.pastSessions : p.upcomingSessions;

        return Column(
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
              child: Row(
                children: [
                  Expanded(
                    child: SegmentedButton<bool>(
                      style: SegmentedButton.styleFrom(
                        selectedBackgroundColor: AppColors.blue600,
                        selectedForegroundColor: Colors.white,
                        foregroundColor: AppColors.gray700,
                        textStyle: const TextStyle(fontSize: 12),
                      ),
                      segments: const [
                        ButtonSegment(value: false, label: Text('Mendatang')),
                        ButtonSegment(value: true,  label: Text('Lampau')),
                      ],
                      selected: {_showPast},
                      onSelectionChanged: (val) {
                        setState(() => _showPast = val.first);
                        if (val.first) {
                          p.fetchSessions(filter: 'past');
                        } else {
                          p.fetchSessions(filter: 'upcoming');
                        }
                      },
                    ),
                  ),
                  const SizedBox(width: 12),
                  _CreateSessionFab(onCreated: () => p.fetchSessions()),
                ],
              ),
            ),
            Expanded(
              child: p.loadingSessions
                  ? const _Loader()
                  : p.sessionsError != null
                      ? _ErrorView(p.sessionsError!,
                          onRetry: () => p.fetchSessions(filter: _showPast ? 'past' : 'upcoming'))
                      : sessions.isEmpty
                          ? _EmptyView(
                              icon: Icons.calendar_today_outlined,
                              title: _showPast ? 'Belum Ada Sesi Lampau' : 'Tidak Ada Sesi Mendatang',
                              subtitle: _showPast ? '' : 'Ketua ekstra dapat membuat sesi baru.',
                            )
                          : RefreshIndicator(
                              onRefresh: () => p.fetchSessions(filter: _showPast ? 'past' : 'upcoming'),
                              child: ListView.builder(
                                padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                                itemCount: sessions.length,
                                itemBuilder: (_, i) => _SessionCard(session: sessions[i]),
                              ),
                            ),
            ),
          ],
        );
      },
    );
  }
}

class _CreateSessionFab extends StatelessWidget {
  final VoidCallback onCreated;
  const _CreateSessionFab({required this.onCreated});

  @override
  Widget build(BuildContext context) {
    return Consumer<ExtracurricularProvider>(
      builder: (_, p, __) {
        final isKetua = p.myExtras.any((e) => e.isKetua && e.isActive);
        if (!isKetua) return const SizedBox.shrink();

        return IconButton.filled(
          onPressed: () async {
            final result = await Navigator.push<bool>(
              context,
              MaterialPageRoute(builder: (_) => const CreateSessionScreen()),
            );
            if (result == true) onCreated();
          },
          icon: const Icon(Icons.add_rounded),
          style: IconButton.styleFrom(
            backgroundColor: AppColors.blue600,
            foregroundColor: Colors.white,
          ),
          tooltip: 'Buat Sesi',
        );
      },
    );
  }
}

class _SessionCard extends StatelessWidget {
  final ExtraSession session;
  const _SessionCard({required this.session});

  @override
  Widget build(BuildContext context) {
    final isPast = session.isPast;
    final isOpen = session.isOpen;

    final Color statusColor;
    final String statusText;
    if (isPast) {
      statusColor = AppColors.gray400;
      statusText  = 'Selesai';
    } else if (isOpen) {
      statusColor = AppColors.green500;
      statusText  = 'Absen Terbuka';
    } else {
      statusColor = AppColors.amber500;
      statusText  = 'Belum Dibuka';
    }

    return GestureDetector(
      onTap: () => Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => SessionDetailScreen(sessionId: session.id)),
      ),
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: AppRadius.card,
          boxShadow: AppShadow.sm,
        ),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(
                      session.extracurricularName,
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: AppColors.blue600,
                      ),
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: statusColor.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          width: 6, height: 6,
                          decoration: BoxDecoration(color: statusColor, shape: BoxShape.circle),
                        ),
                        const SizedBox(width: 4),
                        Text(
                          statusText,
                          style: TextStyle(fontSize: 11, color: statusColor, fontWeight: FontWeight.w600),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              Text(
                session.title,
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                  color: AppColors.gray800,
                ),
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  const Icon(Icons.calendar_today_rounded, size: 13, color: AppColors.gray500),
                  const SizedBox(width: 4),
                  Text(_formatDate(session.sessionDate),
                      style: const TextStyle(fontSize: 12, color: AppColors.gray500)),
                  const SizedBox(width: 12),
                  const Icon(Icons.access_time_rounded, size: 13, color: AppColors.gray500),
                  const SizedBox(width: 4),
                  Text('${session.startTime} – ${session.endTime}',
                      style: const TextStyle(fontSize: 12, color: AppColors.gray500)),
                ],
              ),
              if (session.location != null) ...[
                const SizedBox(height: 4),
                Row(
                  children: [
                    const Icon(Icons.location_on_outlined, size: 13, color: AppColors.gray500),
                    const SizedBox(width: 4),
                    Text(session.location!,
                        style: const TextStyle(fontSize: 12, color: AppColors.gray500)),
                  ],
                ),
              ],
              const SizedBox(height: 10),
              Row(
                children: [
                  _AttendancePill(
                    icon: Icons.check_circle_outline,
                    label: '${session.hadirCount} hadir',
                    color: AppColors.green500,
                  ),
                  const SizedBox(width: 8),
                  _AttendancePill(
                    icon: Icons.cancel_outlined,
                    label: '${session.alpaCount} alpa',
                    color: AppColors.red500,
                  ),
                ],
              ),
            ],
          ),
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

class _AttendancePill extends StatelessWidget {
  final IconData icon;
  final String   label;
  final Color    color;
  const _AttendancePill({required this.icon, required this.label, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 12, color: color),
          const SizedBox(width: 4),
          Text(label, style: TextStyle(fontSize: 11, color: color, fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}

// ─── Shared widgets ───────────────────────────────────────────────────────────

class _ExtraLogo extends StatelessWidget {
  final String? logoUrl;
  final double  size;
  const _ExtraLogo({required this.logoUrl, required this.size});

  @override
  Widget build(BuildContext context) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(AppRadius.xl),
      child: logoUrl != null
          ? Image.network(
              logoUrl!,
              width: size,
              height: size,
              fit: BoxFit.cover,
              errorBuilder: (_, __, ___) => _placeholder(),
            )
          : _placeholder(),
    );
  }

  Widget _placeholder() {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        gradient: AppColors.primaryGradient,
        borderRadius: BorderRadius.circular(AppRadius.xl),
      ),
      child: const Icon(Icons.school_rounded, color: Colors.white, size: 28),
    );
  }
}

class _Badge extends StatelessWidget {
  final String   label;
  final Color    color;
  final Color    bg;
  final IconData? icon;
  const _Badge({required this.label, required this.color, required this.bg, this.icon});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(20)),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null) ...[
            Icon(icon, size: 11, color: color),
            const SizedBox(width: 3),
          ],
          Text(label, style: TextStyle(fontSize: 11, color: color, fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}

class _Loader extends StatelessWidget {
  const _Loader();

  @override
  Widget build(BuildContext context) =>
      const Center(child: CircularProgressIndicator());
}

class _ErrorView extends StatelessWidget {
  final String       message;
  final VoidCallback? onRetry;
  const _ErrorView(this.message, {this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.wifi_off_rounded, size: 48, color: AppColors.gray400),
            const SizedBox(height: 12),
            Text(message, textAlign: TextAlign.center, style: const TextStyle(color: AppColors.gray500)),
            if (onRetry != null) ...[
              const SizedBox(height: 16),
              FilledButton.icon(
                onPressed: onRetry,
                icon: const Icon(Icons.refresh_rounded, size: 16),
                label: const Text('Coba Lagi'),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _EmptyView extends StatelessWidget {
  final IconData icon;
  final String   title;
  final String   subtitle;
  const _EmptyView({required this.icon, required this.title, required this.subtitle});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 72,
              height: 72,
              decoration: BoxDecoration(
                color: AppColors.blue50,
                borderRadius: BorderRadius.circular(36),
              ),
              child: Icon(icon, size: 36, color: AppColors.blue600),
            ),
            const SizedBox(height: 16),
            Text(
              title,
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.gray800),
            ),
            if (subtitle.isNotEmpty) ...[
              const SizedBox(height: 6),
              Text(
                subtitle,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 13, color: AppColors.gray500),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
