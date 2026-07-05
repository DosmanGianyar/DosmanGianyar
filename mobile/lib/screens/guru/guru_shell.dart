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
import 'guru_absensi_harian_screen.dart';
import 'guru_bk_screen.dart';
import 'guru_conduct_input_screen.dart';
import 'guru_conduct_screen.dart';
import 'guru_early_checkout_screen.dart';
import 'guru_forgot_attendance_screen.dart';
import 'guru_home_screen.dart';
import 'guru_input_nilai_screen.dart';
import 'guru_homeroom_consultation_screen.dart';
import 'guru_journal_screen.dart';
import 'guru_permit_screen.dart';
import 'guru_tp_screen.dart';
import 'guru_rekap_screen.dart';
import 'guru_sarpras_screen.dart';
import 'guru_teaching_session_screen.dart';

class GuruShell extends StatefulWidget {
  const GuruShell({super.key});

  @override
  State<GuruShell> createState() => _GuruShellState();
}

class _GuruShellState extends State<GuruShell> {
  List<GuruClass> _classes = [];
  bool _classesError = false;
  final _scaffoldKey = GlobalKey<ScaffoldState>();

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

  int? get _homeroomClassId =>
      context.read<AuthProvider>().user?.homeroomClassId;

  Future<void> _logout() async {
    _scaffoldKey.currentState?.closeDrawer();
    await Future.delayed(const Duration(milliseconds: 250));
    if (!mounted) return;
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

  void _go(Widget screen) {
    _scaffoldKey.currentState?.closeDrawer();
    Navigator.push(context, MaterialPageRoute(builder: (_) => screen));
  }

  @override
  Widget build(BuildContext context) {
    final user   = context.watch<AuthProvider>().user;
    final unread = context.watch<NotificationProvider>().unreadCount;

    return Scaffold(
      key: _scaffoldKey,
      backgroundColor: AppColors.slate100,
      drawer: _GuruDrawer(
        user:            user,
        classes:         _classes,
        homeroomClassId: _homeroomClassId,
        classesError:    _classesError,
        onReloadClasses: _loadClasses,
        onNavigate:      _go,
        onLogout:        _logout,
      ),
      body: SafeArea(
        bottom: false,
        child: Column(
          children: [
            _GuruTopBar(
              user:        user,
              unread:      unread,
              onMenuTap:   () => _scaffoldKey.currentState?.openDrawer(),
              onNotifTap:  () {
                final notifProv = context.read<NotificationProvider>();
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                ).then((_) => notifProv.fetchUnreadCount());
              },
            ),
            const Expanded(child: GuruHomeScreen()),
          ],
        ),
      ),
    );
  }
}

// ─── Top Bar ─────────────────────────────────────────────────────────────────

class _GuruTopBar extends StatelessWidget {
  final User? user;
  final int unread;
  final VoidCallback onMenuTap;
  final VoidCallback onNotifTap;

  const _GuruTopBar({
    required this.user,
    required this.unread,
    required this.onMenuTap,
    required this.onNotifTap,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 8),
      decoration: const BoxDecoration(gradient: AppColors.topbarGradient),
      child: Row(
        children: [
          // Hamburger
          IconButton(
            onPressed: onMenuTap,
            icon: const Icon(Icons.menu_rounded, color: Colors.white, size: 22),
          ),

          // Title
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'DOSMAN — Portal Guru',
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

          // Notification
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
        ],
      ),
    );
  }
}

// ─── Sidebar Drawer ───────────────────────────────────────────────────────────

class _GuruDrawer extends StatelessWidget {
  final User? user;
  final List<GuruClass> classes;
  final int? homeroomClassId;
  final bool classesError;
  final VoidCallback onReloadClasses;
  final void Function(Widget) onNavigate;
  final VoidCallback onLogout;

  const _GuruDrawer({
    required this.user,
    required this.classes,
    required this.homeroomClassId,
    required this.classesError,
    required this.onReloadClasses,
    required this.onNavigate,
    required this.onLogout,
  });

  @override
  Widget build(BuildContext context) {
    return Drawer(
      backgroundColor: Colors.white,
      child: Column(
        children: [
          _buildLogo(),
          _buildUserCard(),
          Expanded(child: _buildNav()),
          _buildLogout(),
        ],
      ),
    );
  }

  Widget _buildLogo() {
    return Container(
      height: 70,
      padding: const EdgeInsets.symmetric(horizontal: 16),
      decoration: const BoxDecoration(
        border: Border(bottom: BorderSide(color: AppColors.gray100)),
      ),
      child: Row(
        children: [
          Container(
            width: 36, height: 36,
            decoration: BoxDecoration(
              color: AppColors.blue100,
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(Icons.school_rounded, color: AppColors.blue600, size: 20),
          ),
          const SizedBox(width: 12),
          const Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'SMA Negeri 1 Gianyar',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.gray800),
              ),
              Text(
                'Portal Guru',
                style: TextStyle(fontSize: 11, color: AppColors.gray400),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildUserCard() {
    return Container(
      padding: const EdgeInsets.fromLTRB(12, 10, 12, 10),
      decoration: const BoxDecoration(
        border: Border(bottom: BorderSide(color: AppColors.gray100)),
      ),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: AppColors.gray50,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Row(
          children: [
            CircleAvatar(
              radius: 18,
              backgroundColor: AppColors.blue600,
              child: Text(
                user?.initials ?? 'G',
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Colors.white),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    user?.name ?? '',
                    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.gray800),
                    overflow: TextOverflow.ellipsis,
                  ),
                  Text(
                    user?.subjectDisplay.isNotEmpty == true ? user!.subjectDisplay : 'Guru',
                    style: const TextStyle(fontSize: 11, color: AppColors.gray400),
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildNav() {
    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Dashboard
          _NavTile(
            icon:  Icons.home_rounded,
            label: 'Dashboard',
            onTap: () {/* already on home — drawer will close via back tap */},
          ),
          const SizedBox(height: 6),

          // Kesiswaan
          _NavSection(
            icon:  Icons.groups_rounded,
            label: 'Kesiswaan',
            children: [
              _NavTile(
                icon:  Icons.calendar_today_rounded,
                label: 'Absensi Harian',
                onTap: () => onNavigate(GuruAbsensiHarianScreen(
                  classes: classes,
                  initialClassId: homeroomClassId,
                )),
              ),
              _NavTile(
                icon:  Icons.manage_accounts_rounded,
                label: 'Rekap Siswa',
                onTap: () => onNavigate(GuruConductScreen(
                  classes: classes,
                  initialClassId: homeroomClassId,
                )),
              ),
              _NavTile(
                icon:  Icons.add_circle_outline_rounded,
                label: 'Catat Perilaku Siswa',
                onTap: () => onNavigate(const GuruConductInputScreen()),
              ),
              _NavTile(
                icon:  Icons.check_circle_outline_rounded,
                label: 'Approval Izin/Sakit',
                onTap: () => onNavigate(const GuruPermitScreen()),
              ),
              _NavTile(
                icon:  Icons.history_rounded,
                label: 'Lupa Absen Siswa',
                onTap: () => onNavigate(const GuruForgotAttendanceScreen()),
              ),
              _NavTile(
                icon:  Icons.exit_to_app_rounded,
                label: 'Izin Pulang Awal',
                onTap: () => onNavigate(const GuruEarlyCheckoutScreen()),
              ),
            ],
          ),
          const SizedBox(height: 6),

          // Kurikulum
          _NavSection(
            icon:  Icons.menu_book_rounded,
            label: 'Kurikulum',
            children: [
              _NavTile(
                icon:  Icons.checklist_rounded,
                label: 'Tujuan Pembelajaran (TP)',
                onTap: () => onNavigate(const GuruTpScreen()),
              ),
              _NavTile(
                icon:  Icons.assignment_rounded,
                label: 'Input Nilai',
                onTap: () => onNavigate(const GuruInputNilaiScreen()),
              ),
              _NavTile(
                icon:  Icons.how_to_reg_rounded,
                label: 'Absensi Mengajar',
                onTap: () => onNavigate(const GuruTeachingSessionScreen()),
              ),
              _NavTile(
                icon:  Icons.menu_book_outlined,
                label: 'Jurnal Mengajar',
                onTap: () => onNavigate(const GuruJournalScreen()),
              ),
              _NavTile(
                icon:  Icons.chat_bubble_outline_rounded,
                label: 'Jurnal Bimbingan Guru Wali',
                onTap: () => onNavigate(const GuruHomeroomConsultationScreen()),
              ),
            ],
          ),
          const SizedBox(height: 6),

          // BK
          _NavSection(
            icon:  Icons.psychology_rounded,
            label: 'Bimbingan Konseling',
            children: [
              _NavTile(
                icon:  Icons.psychology_outlined,
                label: 'Catatan BK',
                onTap: () => onNavigate(const GuruBkScreen()),
              ),
            ],
          ),
          const SizedBox(height: 6),

          // Sarpras
          _NavSection(
            icon:  Icons.business_rounded,
            label: 'Sarana & Prasarana',
            children: [
              _NavTile(
                icon:  Icons.inventory_2_rounded,
                label: 'Inventaris & Peminjaman',
                onTap: () => onNavigate(const GuruSarprasScreen()),
              ),
            ],
          ),
          const SizedBox(height: 6),

          // Laporan
          _NavSection(
            icon:  Icons.bar_chart_rounded,
            label: 'Laporan',
            children: [
              _NavTile(
                icon:  Icons.bar_chart_rounded,
                label: 'Rekap Absensi',
                onTap: () => onNavigate(GuruRekapScreen(
                  classes: classes,
                  initialClassId: homeroomClassId,
                )),
              ),
            ],
          ),
          const SizedBox(height: 6),

          // Akun
          _NavSection(
            icon:  Icons.person_rounded,
            label: 'Akun',
            children: [
              _NavTile(
                icon:  Icons.person_outline_rounded,
                label: 'Profil Saya',
                onTap: () => onNavigate(const ProfileScreen()),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildLogout() {
    return Column(
      children: [
        const Divider(height: 1, color: AppColors.gray100),
        InkWell(
          onTap: onLogout,
          child: const Padding(
            padding: EdgeInsets.symmetric(horizontal: 20, vertical: 14),
            child: Row(
              children: [
                Icon(Icons.logout_rounded, size: 18, color: AppColors.gray400),
                SizedBox(width: 12),
                Text('Keluar', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: AppColors.gray500)),
              ],
            ),
          ),
        ),
        const SizedBox(height: 8),
      ],
    );
  }
}

// ─── Nav helpers ─────────────────────────────────────────────────────────────

class _NavTile extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback? onTap;

  const _NavTile({
    required this.icon,
    required this.label,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
        child: Row(
          children: [
            Icon(icon, size: 16, color: AppColors.gray400),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                label,
                style: const TextStyle(fontSize: 13, color: AppColors.gray600, fontWeight: FontWeight.w500),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _NavSection extends StatelessWidget {
  final IconData icon;
  final String label;
  final List<Widget> children;
  final bool initiallyExpanded;

  const _NavSection({
    required this.icon,
    required this.label,
    required this.children,
    this.initiallyExpanded = false,
  });

  @override
  Widget build(BuildContext context) {
    return Theme(
      data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
      child: ExpansionTile(
        leading: Icon(icon, size: 18, color: AppColors.gray500),
        title: Text(
          label,
          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700),
        ),
        initiallyExpanded: initiallyExpanded,
        tilePadding: const EdgeInsets.symmetric(horizontal: 10),
        childrenPadding: const EdgeInsets.only(left: 14),
        iconColor: AppColors.gray400,
        collapsedIconColor: AppColors.gray400,
        children: children,
      ),
    );
  }
}
