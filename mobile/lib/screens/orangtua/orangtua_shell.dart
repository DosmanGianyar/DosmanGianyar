import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/user.dart';
import '../../providers/auth_provider.dart';
import '../../providers/orangtua_provider.dart';
import '../../theme/app_colors.dart';
import '../login_screen.dart';
import '../profile_screen.dart';
import 'orangtua_achievement_screen.dart';
import 'orangtua_attendance_screen.dart';
import 'orangtua_conduct_screen.dart';

class OrangtuaShell extends StatefulWidget {
  const OrangtuaShell({super.key});

  @override
  State<OrangtuaShell> createState() => _OrangtuaShellState();
}

class _OrangtuaShellState extends State<OrangtuaShell> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = context.read<AuthProvider>().user;
      context.read<OrangtuaProvider>().initFromUser(user?.children ?? []);
      context.read<OrangtuaProvider>().refreshChildren();
    });
  }

  Future<void> _logout() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Konfirmasi Logout'),
        content: const Text('Yakin ingin keluar dari aplikasi?'),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Batal')),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            style: FilledButton.styleFrom(backgroundColor: AppColors.red500),
            child: const Text('Logout'),
          ),
        ],
      ),
    );
    if (confirm == true && mounted) {
      await context.read<AuthProvider>().logout();
      if (mounted) {
        Navigator.of(context).pushAndRemoveUntil(
          MaterialPageRoute(builder: (_) => const LoginScreen()),
          (_) => false,
        );
      }
    }
  }

  void _openProfile() {
    Navigator.push(context, MaterialPageRoute(builder: (_) => const ProfileScreen()));
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    final prov = context.watch<OrangtuaProvider>();
    final child = prov.selectedChild;

    return Scaffold(
      backgroundColor: AppColors.slate100,
      body: SafeArea(
        bottom: false,
        child: RefreshIndicator(
          onRefresh: () => context.read<OrangtuaProvider>().refreshChildren(),
          child: ListView(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
            children: [
              _TopBar(userName: user?.name ?? '', onProfile: _openProfile, onLogout: _logout),
              const SizedBox(height: 16),
              if (prov.isLoading && prov.children.isEmpty)
                const Padding(
                  padding: EdgeInsets.only(top: 60),
                  child: Center(child: CircularProgressIndicator()),
                )
              else if (prov.children.isEmpty)
                const _EmptyState()
              else ...[
                if (prov.children.length > 1)
                  _ChildSwitcher(
                    children:   prov.children,
                    selectedId: prov.selectedChildId,
                    onChanged:  (id) => context.read<OrangtuaProvider>().selectChild(id),
                  )
                else
                  _ChildCard(child: prov.children.first),
                const SizedBox(height: 20),
                if (child != null) _MenuGrid(child: child),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

// ─── Top Bar ─────────────────────────────────────────────────────────────────

class _TopBar extends StatelessWidget {
  final String       userName;
  final VoidCallback onProfile;
  final VoidCallback onLogout;
  const _TopBar({required this.userName, required this.onProfile, required this.onLogout});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      decoration: BoxDecoration(gradient: AppColors.topbarGradient, borderRadius: BorderRadius.circular(16)),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('DOSMAN — Portal Orangtua',
                  style: TextStyle(fontSize: 11, color: Colors.white54, fontWeight: FontWeight.w500, letterSpacing: 0.5)),
                Text(userName,
                  style: const TextStyle(fontSize: 14, color: Colors.white, fontWeight: FontWeight.w600),
                  overflow: TextOverflow.ellipsis),
              ],
            ),
          ),
          IconButton(
            onPressed: onProfile,
            icon: const Icon(Icons.person_outline_rounded, color: Colors.white, size: 22),
          ),
          IconButton(
            onPressed: onLogout,
            icon: const Icon(Icons.logout_rounded, color: Colors.white, size: 20),
          ),
        ],
      ),
    );
  }
}

// ─── Empty state (belum ada anak terhubung) ──────────────────────────────────

class _EmptyState extends StatelessWidget {
  const _EmptyState();

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(top: 60),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.family_restroom_rounded, size: 56, color: AppColors.gray300),
          const SizedBox(height: 16),
          const Text('Belum Ada Anak Terhubung',
            style: TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: AppColors.gray700)),
          const SizedBox(height: 6),
          const Padding(
            padding: EdgeInsets.symmetric(horizontal: 24),
            child: Text(
              'Akun ini belum terhubung ke data siswa. Hubungi admin sekolah untuk memastikan no. HP Anda sudah tercatat sebagai data orangtua/wali siswa.',
              style: TextStyle(fontSize: 12, color: AppColors.gray400),
              textAlign: TextAlign.center,
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Child switcher (>1 anak) ────────────────────────────────────────────────

class _ChildSwitcher extends StatelessWidget {
  final List<ChildSummary>    children;
  final int?                  selectedId;
  final ValueChanged<int>     onChanged;
  const _ChildSwitcher({required this.children, required this.selectedId, required this.onChanged});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white, borderRadius: AppRadius.card,
        border: Border.all(color: AppColors.gray100), boxShadow: AppShadow.sm,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Pilih Anak', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.gray500)),
          const SizedBox(height: 8),
          DropdownButtonHideUnderline(
            child: DropdownButton<int>(
              value: selectedId,
              isExpanded: true,
              borderRadius: BorderRadius.circular(12),
              items: children.map((c) => DropdownMenuItem(
                value: c.id,
                child: Text('${c.name}${c.className != null ? ' — ${c.className}' : ''}',
                  style: const TextStyle(fontSize: 13, color: AppColors.gray800)),
              )).toList(),
              onChanged: (v) { if (v != null) onChanged(v); },
            ),
          ),
        ],
      ),
    );
  }
}

class _ChildCard extends StatelessWidget {
  final ChildSummary child;
  const _ChildCard({required this.child});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white, borderRadius: AppRadius.card,
        border: Border.all(color: AppColors.gray100), boxShadow: AppShadow.sm,
      ),
      child: Row(
        children: [
          CircleAvatar(
            radius: 22,
            backgroundColor: AppColors.blue600,
            backgroundImage: child.photoUrl != null ? NetworkImage(child.photoUrl!) : null,
            onBackgroundImageError: child.photoUrl != null ? (_, __) {} : null,
            child: child.photoUrl == null
                ? Text(child.initials, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700))
                : null,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(child.name, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.gray800)),
                if (child.className != null)
                  Text(child.className!, style: const TextStyle(fontSize: 12, color: AppColors.gray400)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Menu grid (Absensi / Catatan / Prestasi) ────────────────────────────────

class _MenuGrid extends StatelessWidget {
  final ChildSummary child;
  const _MenuGrid({required this.child});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Perkembangan Anak',
          style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700)),
        const SizedBox(height: 10),
        _MenuTile(
          icon: Icons.calendar_today_rounded,
          iconBg: AppColors.blue50,
          iconColor: AppColors.blue600,
          title: 'Absensi',
          subtitle: 'Riwayat kehadiran harian',
          onTap: () => Navigator.push(context, MaterialPageRoute(
            builder: (_) => OrangtuaAttendanceScreen(studentId: child.id, studentName: child.name),
          )),
        ),
        const SizedBox(height: 10),
        _MenuTile(
          icon: Icons.assignment_rounded,
          iconBg: AppColors.emerald50,
          iconColor: AppColors.emerald600,
          title: 'Catatan Perilaku',
          subtitle: 'Catatan positif & negatif dari guru',
          onTap: () => Navigator.push(context, MaterialPageRoute(
            builder: (_) => OrangtuaConductScreen(studentId: child.id, studentName: child.name),
          )),
        ),
        const SizedBox(height: 10),
        _MenuTile(
          icon: Icons.workspace_premium_rounded,
          iconBg: AppColors.yellow50,
          iconColor: AppColors.yellow600,
          title: 'Prestasi',
          subtitle: 'Daftar prestasi yang tercatat',
          onTap: () => Navigator.push(context, MaterialPageRoute(
            builder: (_) => OrangtuaAchievementScreen(studentId: child.id, studentName: child.name),
          )),
        ),
      ],
    );
  }
}

class _MenuTile extends StatelessWidget {
  final IconData     icon;
  final Color        iconBg;
  final Color        iconColor;
  final String       title;
  final String       subtitle;
  final VoidCallback onTap;

  const _MenuTile({
    required this.icon, required this.iconBg, required this.iconColor,
    required this.title, required this.subtitle, required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: AppRadius.card,
      child: InkWell(
        borderRadius: AppRadius.card,
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            borderRadius: AppRadius.card,
            border: Border.all(color: AppColors.gray100),
            boxShadow: AppShadow.sm,
          ),
          child: Row(
            children: [
              Container(
                width: 44, height: 44,
                decoration: BoxDecoration(color: iconBg, borderRadius: BorderRadius.circular(12)),
                child: Icon(icon, color: iconColor, size: 22),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(title, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: AppColors.gray800)),
                    const SizedBox(height: 2),
                    Text(subtitle, style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
                  ],
                ),
              ),
              const Icon(Icons.chevron_right_rounded, color: AppColors.gray300),
            ],
          ),
        ),
      ),
    );
  }
}
