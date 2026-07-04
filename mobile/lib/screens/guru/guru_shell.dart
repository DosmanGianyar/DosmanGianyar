import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/guru_models.dart';
import '../../models/user.dart';
import '../../providers/auth_provider.dart';
import '../../providers/notification_provider.dart';
import '../../services/guru_service.dart';
import '../../theme/app_colors.dart';
import '../login_screen.dart';
import '../notifications_screen.dart';
import '../profile_screen.dart';
import 'guru_home_screen.dart';
import 'guru_presensi_screen.dart';
import 'guru_kesiswaan_screen.dart';

class GuruShell extends StatefulWidget {
  const GuruShell({super.key});

  @override
  State<GuruShell> createState() => _GuruShellState();
}

class _GuruShellState extends State<GuruShell> {
  int _tab = 0;
  List<GuruClass> _classes = [];
  bool _classesError = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<NotificationProvider>().fetchUnreadCount();
      _loadClasses();
    });
  }

  Future<void> _loadClasses() async {
    try {
      final classes = await GuruService.getClasses();
      if (mounted) setState(() { _classes = classes; _classesError = false; });
    } catch (_) {
      if (mounted) setState(() => _classesError = true);
    }
  }

  int? get _homeroomClassId {
    final user = context.read<AuthProvider>().user;
    return user?.homeroomClassId;
  }

  Future<void> _logout() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Konfirmasi Logout'),
        content: const Text('Yakin ingin keluar dari aplikasi?'),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal'),
          ),
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

  Widget _buildTab() {
    // Tabs 1 & 2 need classes — show loader/error while fetching
    if (_tab != 0 && _classes.isEmpty) {
      if (_classesError) {
        return Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.cloud_off_rounded, size: 48, color: AppColors.gray400),
              const SizedBox(height: 12),
              const Text('Gagal memuat data kelas', style: TextStyle(color: AppColors.gray600)),
              const SizedBox(height: 8),
              TextButton(onPressed: _loadClasses, child: const Text('Coba Lagi')),
            ],
          ),
        );
      }
      return const Center(child: CircularProgressIndicator());
    }
    return switch (_tab) {
      0 => const GuruHomeScreen(),
      1 => GuruPresensiScreen(classes: _classes, homeroomClassId: _homeroomClassId),
      2 => GuruKesiswaanScreen(classes: _classes, homeroomClassId: _homeroomClassId),
      _ => const GuruHomeScreen(),
    };
  }

  @override
  Widget build(BuildContext context) {
    final user  = context.watch<AuthProvider>().user;
    final unread = context.watch<NotificationProvider>().unreadCount;

    return Scaffold(
      backgroundColor: AppColors.slate100,
      body: SafeArea(
        bottom: false,
        child: Column(
          children: [
            _GuruTopBar(
              user: user,
              unread: unread,
              onNotifTap: () {
                final notifProv = context.read<NotificationProvider>();
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                ).then((_) => notifProv.fetchUnreadCount());
              },
              onProfileTap: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const ProfileScreen()),
              ),
              onLogout: _logout,
            ),
            Expanded(child: _buildTab()),
          ],
        ),
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _tab,
        onDestinationSelected: (i) => setState(() => _tab = i),
        backgroundColor: AppColors.white,
        indicatorColor: AppColors.blue100,
        height: 68,
        labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.home_outlined),
            selectedIcon: Icon(Icons.home_rounded),
            label: 'Beranda',
          ),
          NavigationDestination(
            icon: Icon(Icons.calendar_today_outlined),
            selectedIcon: Icon(Icons.calendar_today_rounded),
            label: 'Presensi',
          ),
          NavigationDestination(
            icon: Icon(Icons.groups_outlined),
            selectedIcon: Icon(Icons.groups_rounded),
            label: 'Kesiswaan',
          ),
        ],
      ),
    );
  }
}

class _GuruTopBar extends StatelessWidget {
  final User? user;
  final int unread;
  final VoidCallback onNotifTap;
  final VoidCallback onProfileTap;
  final VoidCallback onLogout;

  const _GuruTopBar({
    required this.user,
    required this.unread,
    required this.onNotifTap,
    required this.onProfileTap,
    required this.onLogout,
  });

  @override
  Widget build(BuildContext context) {
    final initials = user?.initials ?? 'G';

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: const BoxDecoration(gradient: AppColors.topbarGradient),
      child: Row(
        children: [
          GestureDetector(
            onTap: onProfileTap,
            child: CircleAvatar(
              radius: 18,
              backgroundColor: Colors.white.withValues(alpha: 0.2),
              child: Text(
                initials,
                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: Colors.white),
              ),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'SIMS — Portal Guru',
                  style: TextStyle(fontSize: 11, color: Colors.white54, fontWeight: FontWeight.w500, letterSpacing: 0.5),
                ),
                if (user != null)
                  Text(
                    user!.name,
                    style: const TextStyle(fontSize: 13, color: Colors.white, fontWeight: FontWeight.w600),
                    overflow: TextOverflow.ellipsis,
                  ),
              ],
            ),
          ),
          Stack(
            children: [
              IconButton(
                onPressed: onNotifTap,
                icon: const Icon(Icons.notifications_outlined, color: Colors.white, size: 22),
              ),
              if (unread > 0)
                Positioned(
                  top: 8, right: 8,
                  child: Container(
                    width: 8, height: 8,
                    decoration: const BoxDecoration(color: AppColors.red500, shape: BoxShape.circle),
                  ),
                ),
            ],
          ),
          IconButton(
            onPressed: onLogout,
            icon: const Icon(Icons.logout_rounded, color: Colors.white54, size: 20),
          ),
        ],
      ),
    );
  }
}
