import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/user.dart';
import '../../providers/auth_provider.dart';
import '../../providers/notification_provider.dart';
import '../../theme/app_colors.dart';
import '../notifications_screen.dart';
import '../profile_screen.dart';
import 'guru_absensi_harian_screen.dart';
import 'guru_bk_screen.dart';
import 'guru_conduct_screen.dart';
import 'guru_early_checkout_screen.dart';
import 'guru_forgot_attendance_screen.dart';
import 'guru_home_screen.dart';
import 'guru_homeroom_consultation_screen.dart';
import 'guru_input_nilai_screen.dart';
import 'guru_permit_screen.dart';
import 'guru_rekap_screen.dart';
import 'guru_sarpras_screen.dart';
import 'guru_teaching_session_screen.dart';
import 'guru_tp_screen.dart';

class GuruShell extends StatefulWidget {
  const GuruShell({super.key});

  @override
  State<GuruShell> createState() => _GuruShellState();
}

class _GuruShellState extends State<GuruShell> {
  int _currentIndex = 0;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<NotificationProvider>().fetchUnreadCount();
    });
  }

  void _go(Widget screen) {
    Navigator.push(context, MaterialPageRoute(builder: (_) => screen));
  }

  @override
  Widget build(BuildContext context) {
    final user   = context.watch<AuthProvider>().user;
    final unread = context.watch<NotificationProvider>().unreadCount;

    final pages = [
      const GuruHomeScreen(),
      _GuruTeachingTab(onNavigate: _go),
      _GuruServicesTab(onNavigate: _go),
      const ProfileScreen(),
    ];

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
            ),
            Expanded(
              child: IndexedStack(
                index: _currentIndex,
                children: pages,
              ),
            ),
          ],
        ),
      ),
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
              blurRadius: 10,
              offset: const Offset(0, -2),
            ),
          ],
        ),
        child: NavigationBar(
          selectedIndex: _currentIndex,
          onDestinationSelected: (i) => setState(() => _currentIndex = i),
          backgroundColor: Colors.white,
          indicatorColor: AppColors.blue100,
          elevation: 0,
          destinations: const [
            NavigationDestination(
              icon: Icon(Icons.home_outlined, color: AppColors.gray500),
              selectedIcon: Icon(Icons.home_rounded, color: AppColors.blue700),
              label: 'Beranda',
            ),
            NavigationDestination(
              icon: Icon(Icons.menu_book_outlined, color: AppColors.gray500),
              selectedIcon: Icon(Icons.menu_book_rounded, color: AppColors.blue700),
              label: 'Mengajar',
            ),
            NavigationDestination(
              icon: Icon(Icons.grid_view_outlined, color: AppColors.gray500),
              selectedIcon: Icon(Icons.grid_view_rounded, color: AppColors.blue700),
              label: 'Layanan',
            ),
            NavigationDestination(
              icon: Icon(Icons.person_outline_rounded, color: AppColors.gray500),
              selectedIcon: Icon(Icons.person_rounded, color: AppColors.blue700),
              label: 'Profil',
            ),
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
  final VoidCallback onNotifTap;

  const _GuruTopBar({
    required this.user,
    required this.unread,
    required this.onNotifTap,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      decoration: const BoxDecoration(gradient: AppColors.topbarGradient),
      child: Row(
        children: [
          // Logo Badge Icon
          Container(
            padding: const EdgeInsets.all(6),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(Icons.school_rounded, color: Colors.white, size: 20),
          ),
          const SizedBox(width: 12),

          // Title & User Info
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'DOSMAN — Portal Guru',
                  style: TextStyle(fontSize: 10, color: Colors.white70, fontWeight: FontWeight.w600, letterSpacing: 0.5),
                ),
                Text(
                  user?.name ?? 'Guru Portal',
                  style: const TextStyle(fontSize: 14, color: Colors.white, fontWeight: FontWeight.bold),
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),

          // Notification Bell
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
                    padding: const EdgeInsets.all(4),
                    decoration: const BoxDecoration(color: AppColors.red500, shape: BoxShape.circle),
                    child: Text(
                      unread > 9 ? '9+' : '$unread',
                      style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold),
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }
}

// ─── Tab 1: Mengajar & Kurikulum Hub ─────────────────────────────────────────

class _GuruTeachingTab extends StatelessWidget {
  final void Function(Widget) onNavigate;

  const _GuruTeachingTab({required this.onNavigate});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(16, 20, 16, 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Mengajar & Kurikulum',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: AppColors.gray800),
          ),
          const SizedBox(height: 4),
          const Text(
            'Kelola sesi pembelajaran, presensi jam, jurnal, TP & penilaian',
            style: TextStyle(fontSize: 12, color: AppColors.gray500),
          ),
          const SizedBox(height: 16),

          _buildHubCard(
            title: 'Sesi Mengajar (Presensi & Jurnal)',
            subtitle: 'Mulai jam pelajaran, catat absensi siswa & isi jurnal kelas',
            icon: Icons.timer_outlined,
            color: AppColors.blue600,
            bg: AppColors.blue50,
            onTap: () => onNavigate(const GuruTeachingSessionScreen()),
          ),
          const SizedBox(height: 12),

          _buildHubCard(
            title: 'Tujuan Pembelajaran (TP)',
            subtitle: 'Input TP baru, pilih Tingkatan Kelas (10, 11, 12), & bagikan ke guru serumpun',
            icon: Icons.checklist_rounded,
            color: AppColors.purple600,
            bg: const Color(0xFFF3E8FF),
            badge: 'Pilihan Kelas 10, 11, 12',
            onTap: () => onNavigate(const GuruTpScreen()),
          ),
          const SizedBox(height: 12),

          _buildHubCard(
            title: 'Input Nilai Siswa',
            subtitle: 'Penilaian harian & asesmen formatif/sumatif siswa',
            icon: Icons.assignment_turned_in_rounded,
            color: AppColors.emerald600,
            bg: const Color(0xFFECFDF5),
            onTap: () => onNavigate(const GuruInputNilaiScreen()),
          ),
          const SizedBox(height: 12),

          _buildHubCard(
            title: 'Rekap Nilai Siswa',
            subtitle: 'Lihat rekapitulasi capaian nilai dan analisis kelas',
            icon: Icons.bar_chart_rounded,
            color: AppColors.teal600,
            bg: const Color(0xFFCCFBF1),
            onTap: () => onNavigate(const GuruRekapScreen()),
          ),
        ],
      ),
    );
  }

  Widget _buildHubCard({
    required String title,
    required String subtitle,
    required IconData icon,
    required Color color,
    required Color bg,
    String? badge,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.gray100),
          boxShadow: AppShadow.sm,
        ),
        child: Row(
          children: [
            Container(
              width: 48,
              height: 48,
              decoration: BoxDecoration(
                color: bg,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, color: color, size: 24),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          title,
                          style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.gray800),
                        ),
                      ),
                      if (badge != null) ...[
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: color.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            badge,
                            style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: color),
                          ),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    subtitle,
                    style: const TextStyle(fontSize: 11, color: AppColors.gray500),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 8),
            const Icon(Icons.chevron_right_rounded, color: AppColors.gray400),
          ],
        ),
      ),
    );
  }
}

// ─── Tab 2: Layanan & Perwalian Hub ──────────────────────────────────────────

class _GuruServicesTab extends StatelessWidget {
  final void Function(Widget) onNavigate;

  const _GuruServicesTab({required this.onNavigate});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(16, 20, 16, 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Layanan & Perwalian',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: AppColors.gray800),
          ),
          const SizedBox(height: 4),
          const Text(
            'Kedisiplinan, persetujuan surat izin, perwalian BK, & sarpras',
            style: TextStyle(fontSize: 12, color: AppColors.gray500),
          ),
          const SizedBox(height: 16),

          _buildHubCard(
            title: 'Catatan Kedisiplinan Siswa',
            subtitle: 'Catat poin pelanggaran & prestasi siswa',
            icon: Icons.warning_amber_rounded,
            color: AppColors.orange600,
            bg: AppColors.orange50,
            onTap: () => onNavigate(const GuruConductScreen()),
          ),
          const SizedBox(height: 12),

          _buildHubCard(
            title: 'Persetujuan Surat Izin Siswa',
            subtitle: 'Verifikasi pengajuan izin sakit / dispensasi siswa kelas perwalian',
            icon: Icons.mark_email_unread_rounded,
            color: AppColors.teal600,
            bg: const Color(0xFFCCFBF1),
            onTap: () => onNavigate(const GuruPermitScreen()),
          ),
          const SizedBox(height: 12),

          _buildHubCard(
            title: 'Persetujuan Pulang Awal',
            subtitle: 'Kelola persetujuan izin pulang awal siswa saat jam sekolah',
            icon: Icons.directions_run_rounded,
            color: AppColors.amber600,
            bg: const Color(0xFFFEF3C7),
            onTap: () => onNavigate(const GuruEarlyCheckoutScreen()),
          ),
          const SizedBox(height: 12),

          _buildHubCard(
            title: 'Persetujuan Lupa Presensi',
            subtitle: 'Verifikasi konfirmasi klaim presensi siswa',
            icon: Icons.more_time_rounded,
            color: AppColors.blue600,
            bg: AppColors.blue50,
            onTap: () => onNavigate(const GuruForgotAttendanceScreen()),
          ),
          const SizedBox(height: 12),

          _buildHubCard(
            title: 'Konsultasi BK & Wali Kelas',
            subtitle: 'Jadwal konsultasi dan bimbingan siswa wali',
            icon: Icons.record_voice_over_rounded,
            color: AppColors.indigo600,
            bg: AppColors.indigo50,
            onTap: () => onNavigate(const GuruHomeroomConsultationScreen()),
          ),
          const SizedBox(height: 12),

          _buildHubCard(
            title: 'Sarana & Prasarana',
            subtitle: 'Pinjam ruangan, alat sekolah, & laporkan kerusakan',
            icon: Icons.inventory_2_rounded,
            color: AppColors.slate700,
            bg: AppColors.slate100,
            onTap: () => onNavigate(const GuruSarprasScreen()),
          ),
          const SizedBox(height: 12),

          _buildHubCard(
            title: 'Presensi Harian Guru',
            subtitle: 'Status absensi dan kehadiran guru mandiri',
            icon: Icons.badge_rounded,
            color: AppColors.emerald600,
            bg: const Color(0xFFECFDF5),
            onTap: () => onNavigate(const GuruAbsensiHarianScreen()),
          ),
        ],
      ),
    );
  }

  Widget _buildHubCard({
    required String title,
    required String subtitle,
    required IconData icon,
    required Color color,
    required Color bg,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.gray100),
          boxShadow: AppShadow.sm,
        ),
        child: Row(
          children: [
            Container(
              width: 48,
              height: 48,
              decoration: BoxDecoration(
                color: bg,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, color: color, size: 24),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.gray800),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    subtitle,
                    style: const TextStyle(fontSize: 11, color: AppColors.gray500),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 8),
            const Icon(Icons.chevron_right_rounded, color: AppColors.gray400),
          ],
        ),
      ),
    );
  }
}
