import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../providers/attendance_provider.dart';
import '../providers/notification_provider.dart';
import '../models/attendance.dart';
import '../models/announcement.dart';
import '../models/user.dart';
import '../theme/app_colors.dart';
import 'login_screen.dart';
import 'notifications_screen.dart';
import 'attendance/attendance_screen.dart';
import 'attendance/history_screen.dart';
import 'kesiswaan/kesiswaan_screen.dart';
import 'kurikulum/kurikulum_screen.dart';
import 'prasarana/prasarana_screen.dart';
import 'humas/humas_screen.dart';
import 'profile_screen.dart';
import 'announcement_list_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  // -1 = beranda (dashboard); 0-3 = Kesiswaan/Kurikulum/Prasarana/Humas
  int _selectedTab = -1;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final attProv  = context.read<AttendanceProvider>();
      final notifProv = context.read<NotificationProvider>();
      attProv.fetchStatus();
      attProv.fetchCurrentMonthDots();
      notifProv.fetchUnreadCount();
      notifProv.fetchAnnouncements();
    });
  }

  void _onTabTap(int i) {
    setState(() => _selectedTab = (_selectedTab == i) ? -1 : i);
  }

  void _onPresensiTap() {
    final status     = context.read<AttendanceProvider>().status;
    final canCheckin  = status?.canCheckin  ?? false;
    final canCheckout = status?.canCheckout ?? false;

    if (!canCheckin && !canCheckout) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Saat ini bukan waktu absensi.'),
          backgroundColor: AppColors.amber500,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
        ),
      );
      return;
    }
    _goToAttendance(isCheckOut: canCheckout && !canCheckin);
  }

  void _goToAttendance({required bool isCheckOut}) {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => AttendanceScreen(isCheckOut: isCheckOut)),
    ).then((_) => context.read<AttendanceProvider>().fetchStatus());
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

  void _goHome() {
    setState(() => _selectedTab = -1);
  }

  void _openProfile() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const ProfileScreen()),
    );
  }

  void _openNotifications() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const NotificationsScreen()),
    ).then((_) => context.read<NotificationProvider>().fetchUnreadCount());
  }

  Widget _buildBody() {
    if (_selectedTab < 0) {
      return RefreshIndicator(
        onRefresh: () async {
          final attProv   = context.read<AttendanceProvider>();
          final notifProv = context.read<NotificationProvider>();
          await Future.wait([
            attProv.fetchStatus(),
            attProv.fetchCurrentMonthDots(),
            notifProv.fetchUnreadCount(),
            notifProv.fetchAnnouncements(),
          ]);
        },
        child: _DashboardBody(
          onCheckin:  () => _goToAttendance(isCheckOut: false),
          onCheckout: () => _goToAttendance(isCheckOut: true),
          onHistory:  () => Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const HistoryScreen()),
          ),
          onNotifTap: _openNotifications,
        ),
      );
    }

    return switch (_selectedTab) {
      0 => const KesiswaanScreen(),
      1 => const KurikulumScreen(),
      2 => const PrasaranaScreen(),
      _ => const HumasScreen(),
    };
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;

    return Scaffold(
      backgroundColor: AppColors.slate100,
      body: SafeArea(
        bottom: false,
        child: Column(
          children: [
            _TopHeader(
              user:          user,
              onLogout:      _logout,
              onNotifTap:    _openNotifications,
              onProfileTap:  _openProfile,
              onHomeTap:     _goHome,
            ),
            Expanded(child: _buildBody()),
          ],
        ),
      ),
      bottomNavigationBar: _BottomNav(
        selectedTab:  _selectedTab,
        onTabTap:     _onTabTap,
        onPresensi:   _onPresensiTap,
      ),
    );
  }
}

// ─── Top Header ───────────────────────────────────────────────────────────────

class _TopHeader extends StatelessWidget {
  final User?        user;
  final VoidCallback onLogout;
  final VoidCallback onNotifTap;
  final VoidCallback onProfileTap;
  final VoidCallback onHomeTap;

  const _TopHeader({
    this.user,
    required this.onLogout,
    required this.onNotifTap,
    required this.onProfileTap,
    required this.onHomeTap,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 56,
      decoration: const BoxDecoration(
        gradient: AppColors.topbarGradient,
        boxShadow: [
          BoxShadow(
            color:      Color(0x740F2460),
            blurRadius: 16,
            offset:     Offset(0, 2),
          ),
        ],
      ),
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Row(
        children: [
          // ── Kiri: Logo + nama sekolah ──────────────────────────────
          GestureDetector(
            onTap: onHomeTap,
            child: Row(
            children: [
              Container(
                width: 38, height: 38,
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(8),
                  color:  Colors.white.withOpacity(0.15),
                  border: Border.all(color: Colors.white.withOpacity(0.25)),
                ),
                clipBehavior: Clip.antiAlias,
                child: Padding(
                  padding: const EdgeInsets.all(4),
                  child: Image.asset(
                    'assets/images/logo_sekolah.png',
                    fit: BoxFit.contain,
                    errorBuilder: (_, __, ___) => const Icon(
                      Icons.school_rounded,
                      color: Colors.white,
                      size: 20,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: const [
                  Text('SMA N 1 Gianyar',
                    style: TextStyle(
                      color:       Colors.white,
                      fontSize:    13,
                      fontWeight:  FontWeight.w800,
                      height:      1.2,
                      letterSpacing: 0.5,
                    ),
                  ),
                  Text('SIMS',
                    style: TextStyle(
                      color:         AppColors.blue200,
                      fontSize:      11,
                      height:        1.2,
                      letterSpacing: 1.5,
                    ),
                  ),
                ],
              ),
            ],
          ),
          ), // end GestureDetector onHomeTap

          // ── Tengah: Judul halaman ────────────────────────────────
          const Expanded(
            child: Text(
              'Beranda',
              style: TextStyle(
                fontSize:   14,
                fontWeight: FontWeight.w600,
                color:      Colors.white,
              ),
              textAlign: TextAlign.center,
            ),
          ),

          // ── Kanan: Bell + Avatar ─────────────────────────────────
          Row(
            children: [
              GestureDetector(
                onTap: onNotifTap,
                child: SizedBox(
                  width: 36, height: 36,
                  child: Stack(
                    children: [
                      Center(
                        child: Container(
                          width: 32, height: 32,
                          decoration: BoxDecoration(
                            color:        Colors.white.withOpacity(0.10),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.notifications_none_rounded,
                            size: 20, color: Colors.white),
                        ),
                      ),
                      Consumer<NotificationProvider>(
                        builder: (_, prov, __) {
                          if (prov.unreadCount == 0) return const SizedBox.shrink();
                          return Positioned(
                            top: 2, right: 2,
                            child: Container(
                              width: 16, height: 16,
                              decoration: const BoxDecoration(
                                color: AppColors.red500,
                                shape: BoxShape.circle,
                              ),
                              alignment: Alignment.center,
                              child: Text(
                                prov.unreadCount > 9 ? '9+' : '${prov.unreadCount}',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 9,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          );
                        },
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 6),
              GestureDetector(
                onTap:      onProfileTap,
                onLongPress: onLogout,
                child: Container(
                  width: 32, height: 32,
                  decoration: BoxDecoration(
                    shape:  BoxShape.circle,
                    border: Border.all(color: Colors.white.withOpacity(0.40), width: 2),
                  ),
                  clipBehavior: Clip.antiAlias,
                  child: _UserAvatar(user: user, size: 32),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _UserAvatar extends StatelessWidget {
  final User?  user;
  final double size;

  const _UserAvatar({this.user, required this.size});

  String get _initials {
    final name  = user?.name.trim() ?? '';
    final parts = name.split(' ');
    if (parts.length >= 2) return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
    return name.isNotEmpty ? name[0].toUpperCase() : '?';
  }

  @override
  Widget build(BuildContext context) {
    final photoUrl = user?.photoUrl;
    if (photoUrl != null) {
      return ClipOval(
        child: Image.network(
          photoUrl,
          width: size, height: size,
          fit: BoxFit.cover,
          errorBuilder: (_, __, ___) => _circle(),
        ),
      );
    }
    return _circle();
  }

  Widget _circle() {
    return Container(
      width: size, height: size,
      decoration: const BoxDecoration(
        shape: BoxShape.circle,
        color: AppColors.blue600,
      ),
      alignment: Alignment.center,
      child: Text(
        _initials,
        style: TextStyle(
          color:      Colors.white,
          fontSize:   size * 0.38,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }
}

// ─── Dashboard Body ───────────────────────────────────────────────────────────

class _DashboardBody extends StatelessWidget {
  final VoidCallback onCheckin;
  final VoidCallback onCheckout;
  final VoidCallback onHistory;
  final VoidCallback onNotifTap;

  const _DashboardBody({
    required this.onCheckin,
    required this.onCheckout,
    required this.onHistory,
    required this.onNotifTap,
  });

  @override
  Widget build(BuildContext context) {
    final user        = context.watch<AuthProvider>().user;
    final attendProv  = context.watch<AttendanceProvider>();
    final notifProv   = context.watch<NotificationProvider>();
    final status      = attendProv.status;
    final records     = attendProv.currentMonthRecords;

    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // ── Greeting card (sama persis dengan web) ──────────────
          _GreetingCard(user: user, records: records),
          const SizedBox(height: 8),

          // ── Kalender kehadiran bulanan ───────────────────────────
          _MiniCalendar(records: records),
          const SizedBox(height: 8),

          // ── Card absen masuk ─────────────────────────────────────
          if (attendProv.isLoadingStatus)
            const Center(
              child: Padding(
                padding: EdgeInsets.all(32),
                child: CircularProgressIndicator(),
              ),
            )
          else if (attendProv.error != null)
            _ErrorBanner(message: attendProv.error!)
          else if (status != null) ...[
            _CheckInCard(status: status, onPresensi: onCheckin),
            const SizedBox(height: 8),

            // ── Card absen pulang (hanya jika sudah check-in) ──────
            if (status.attendance?.checkInTime != null) ...[
              _CheckOutCard(status: status, onCheckout: onCheckout),
              const SizedBox(height: 8),
            ],

            _HistoryButton(onTap: onHistory),
          ],

          // ── Banner notifikasi (jika ada yang belum dibaca) ───────
          if (notifProv.unreadCount > 0) ...[
            const SizedBox(height: 8),
            _NotifBanner(count: notifProv.unreadCount, onTap: onNotifTap),
          ],

          // ── Pengumuman ───────────────────────────────────────────
          const SizedBox(height: 8),
          _AnnouncementSection(announcements: notifProv.announcements),
        ],
      ),
    );
  }
}

// ─── Greeting Card ────────────────────────────────────────────────────────────

class _GreetingCard extends StatelessWidget {
  final User?                  user;
  final List<AttendanceRecord> records;

  const _GreetingCard({this.user, required this.records});

  Map<String, int> get _summary {
    final m = {'terlambat': 0, 'alpa': 0, 'izin': 0, 'sakit': 0, 'dispensasi': 0};
    for (final r in records) {
      if (m.containsKey(r.status)) m[r.status] = m[r.status]! + 1;
    }
    return m;
  }

  @override
  Widget build(BuildContext context) {
    final now      = DateTime.now();
    final dateStr  = DateFormat('EEEE, d MMMM y', 'id_ID').format(now);
    final name     = user?.name.trim() ?? '';
    final nl       = name.length;
    final nameFontSize = nl <= 15 ? 18.0 : nl <= 22 ? 16.0 : nl <= 30 ? 14.0 : 12.0;
    final summary  = _summary;

    return Container(
      decoration: const BoxDecoration(
        gradient:     AppColors.primaryGradient,
        borderRadius: AppRadius.card,
      ),
      padding: const EdgeInsets.all(16),
      child: Row(
        children: [
          // Foto / avatar
          Container(
            width: 56, height: 56,
            decoration: BoxDecoration(
              borderRadius: AppRadius.avatar,
              border:       Border.all(color: Colors.white.withOpacity(0.40), width: 2),
            ),
            clipBehavior: Clip.antiAlias,
            child: user?.photoUrl != null
                ? Image.network(
                    user!.photoUrl!,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => _avatarFallback(),
                  )
                : _avatarFallback(),
          ),
          const SizedBox(width: 12),

          // Info
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(dateStr,
                  style: const TextStyle(color: AppColors.blue200, fontSize: 11)),
                const SizedBox(height: 2),
                Text(
                  name.isEmpty ? 'SMA Negeri 1 Gianyar' : name,
                  style: TextStyle(
                    color:      Colors.white,
                    fontSize:   nameFontSize,
                    fontWeight: FontWeight.bold,
                    height:     1.2,
                  ),
                  maxLines:  1,
                  overflow:  TextOverflow.clip,
                ),
                const SizedBox(height: 2),
                Text(
                  [
                    if (user?.className != null) user!.className!,
                    if (user?.nis        != null) 'NIS ${user!.nis}',
                  ].join('  ·  ').ifEmpty('SMA Negeri 1 Gianyar'),
                  style: const TextStyle(color: AppColors.blue200, fontSize: 11),
                  overflow: TextOverflow.ellipsis,
                ),
                // Dot ringkasan kehadiran bulan ini (sama seperti web)
                if (records.isNotEmpty) ...[
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      _SummaryDot(color: const Color(0xFFFACC15), textColor: const Color(0xFFFEF08A), count: summary['terlambat']!),
                      const SizedBox(width: 10),
                      _SummaryDot(color: const Color(0xFFF87171), textColor: const Color(0xFFFCA5A5), count: summary['alpa']!),
                      const SizedBox(width: 10),
                      _SummaryDot(color: const Color(0xFF38BDF8), textColor: const Color(0xFF7DD3FC), count: summary['izin']!),
                      const SizedBox(width: 10),
                      _SummaryDot(color: const Color(0xFFC084FC), textColor: const Color(0xFFD8B4FE), count: summary['sakit']!),
                      const SizedBox(width: 10),
                      _SummaryDot(color: const Color(0xFFFB923C), textColor: const Color(0xFFFDBA74), count: summary['dispensasi']!),
                    ],
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _avatarFallback() {
    return Container(
      color: Colors.white.withOpacity(0.20),
      alignment: Alignment.bottomCenter,
      child: Padding(
        padding: const EdgeInsets.only(bottom: 2),
        child: Icon(Icons.person_rounded,
          color: Colors.white.withOpacity(0.90), size: 44),
      ),
    );
  }
}

class _SummaryDot extends StatelessWidget {
  final Color dotColor;
  final Color textColor;
  final int   count;

  const _SummaryDot({
    required Color color,
    required Color textColor,
    required this.count,
  })  : dotColor  = color,
        textColor = textColor;

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 8, height: 8,
          decoration: BoxDecoration(shape: BoxShape.circle, color: dotColor),
        ),
        const SizedBox(width: 3),
        Text(
          '$count',
          style: TextStyle(
            fontSize:   11,
            fontWeight: FontWeight.bold,
            color:      textColor,
            height:     1,
          ),
        ),
      ],
    );
  }
}

extension _StringX on String {
  String ifEmpty(String fallback) => isEmpty ? fallback : this;
}

// ─── Mini Calendar ────────────────────────────────────────────────────────────

class _MiniCalendar extends StatelessWidget {
  final List<AttendanceRecord> records;
  const _MiniCalendar({required this.records});

  static const _dayLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

  // Kelompokkan status:
  //   hijau  = hadir / terlambat / izin / sakit / dispensasi (ada keterangan)
  //   merah  = alpa ATAU hari kerja lampau tanpa keterangan
  //   biru   = hari ini (override semua)
  //   kosong = hari mendatang atau weekend tanpa record
  _DayState _dayState(int day, int weekdayOfDay, bool isToday, bool isPast, String? status) {
    if (isToday) return _DayState.today;
    if (!isPast) return _DayState.future;         // hari ini/mendatang
    final isWeekend = weekdayOfDay >= 6;          // Sat=6, Sun=7
    if (status == null) {
      return isWeekend ? _DayState.weekend : _DayState.absent;
    }
    return switch (status) {
      'hadir' || 'terlambat' || 'izin' || 'sakit' || 'dispensasi' => _DayState.present,
      'alpa' => _DayState.absent,
      _ => _DayState.future,
    };
  }

  @override
  Widget build(BuildContext context) {
    final now          = DateTime.now();
    final today        = DateTime(now.year, now.month, now.day);
    final firstOfMonth = DateTime(now.year, now.month, 1);
    final daysInMonth  = DateTime(now.year, now.month + 1, 0).day;
    final startOffset  = (firstOfMonth.weekday - 1) % 7; // Mon=0

    final statusMap = <int, String>{
      for (final r in records)
        DateTime.parse(r.date).day: r.status,
    };

    final rows = ((startOffset + daysInMonth) / 7).ceil();

    return Container(
      decoration: BoxDecoration(
        color:        AppColors.white,
        borderRadius: AppRadius.card,
        border:       Border.all(color: AppColors.gray100),
        boxShadow:    AppShadow.sm,
      ),
      padding: const EdgeInsets.fromLTRB(12, 12, 12, 10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          Row(children: [
            const Icon(Icons.calendar_month_rounded, size: 14, color: AppColors.blue600),
            const SizedBox(width: 6),
            Text(
              'Kehadiran ${_monthName(now.month)} ${now.year}',
              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.gray700),
            ),
          ]),
          const SizedBox(height: 10),

          // Day-of-week headers
          Row(
            children: _dayLabels.map((d) => Expanded(
              child: Text(d,
                style: const TextStyle(fontSize: 9, color: AppColors.gray400, fontWeight: FontWeight.w600),
                textAlign: TextAlign.center),
            )).toList(),
          ),
          const SizedBox(height: 4),

          // Calendar grid
          for (int row = 0; row < rows; row++)
            Row(
              children: List.generate(7, (col) {
                final day = row * 7 + col - startOffset + 1;

                if (day < 1 || day > daysInMonth) {
                  return const Expanded(child: SizedBox(height: 30));
                }

                final dayDate   = DateTime(now.year, now.month, day);
                final isToday   = dayDate == today;
                final isPast    = dayDate.isBefore(today);
                final weekdayN  = dayDate.weekday; // Mon=1..Sun=7
                final status    = statusMap[day];
                final state     = _dayState(day, weekdayN, isToday, isPast, status);

                return Expanded(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 2),
                    child: Center(
                      child: Container(
                        width: 26, height: 26,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: switch (state) {
                            _DayState.today   => AppColors.blue600,
                            _DayState.present => AppColors.green500,
                            _DayState.absent  => AppColors.red500,
                            _                 => Colors.transparent,
                          },
                        ),
                        alignment: Alignment.center,
                        child: Text(
                          '$day',
                          style: TextStyle(
                            fontSize:   10,
                            fontWeight: isToday ? FontWeight.bold : FontWeight.w500,
                            color: switch (state) {
                              _DayState.today || _DayState.present || _DayState.absent => Colors.white,
                              _DayState.weekend => AppColors.gray300,
                              _                 => AppColors.gray500,
                            },
                          ),
                        ),
                      ),
                    ),
                  ),
                );
              }),
            ),

          // Legend
          const SizedBox(height: 8),
          const Divider(height: 1, color: AppColors.gray100),
          const SizedBox(height: 6),
          const Wrap(
            spacing: 12,
            children: [
              _LegendDot(color: AppColors.blue600,  label: 'Hari ini'),
              _LegendDot(color: AppColors.green500, label: 'Hadir'),
              _LegendDot(color: AppColors.red500,   label: 'Tidak hadir'),
            ],
          ),
        ],
      ),
    );
  }

  String _monthName(int m) => const [
    '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
  ][m];
}

enum _DayState { today, present, absent, weekend, future }

class _LegendDot extends StatelessWidget {
  final Color  color;
  final String label;
  const _LegendDot({required this.color, required this.label});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 7, height: 7,
          decoration: BoxDecoration(shape: BoxShape.circle, color: color),
        ),
        const SizedBox(width: 3),
        Text(label, style: const TextStyle(fontSize: 9, color: AppColors.gray500)),
      ],
    );
  }
}

// ─── Check-in Card ────────────────────────────────────────────────────────────

class _CheckInCard extends StatelessWidget {
  final AttendanceStatus status;
  final VoidCallback     onPresensi;

  const _CheckInCard({required this.status, required this.onPresensi});

  @override
  Widget build(BuildContext context) {
    final att  = status.attendance;
    final done = att?.checkInTime != null;

    final (bg, border, textMain, textSub) = done
        ? (AppColors.green100,              const Color(0xFF86EFAC),
           AppColors.green900,              const Color(0xFF15803D))
        : (const Color(0xFFF3F4F6),        AppColors.gray200,
           AppColors.gray500,              AppColors.gray400);

    return _AttCard(
      bg: bg, border: border,
      iconBg: done ? _circleColor(att!.status) : AppColors.gray400,
      icon:   _icon(att?.status),
      title:  done ? att!.statusLabel : 'Belum Presensi',
      sub:    done
          ? 'Tercatat jam ${att!.checkInTime!.substring(0, 5)}'
          : 'Segera lakukan presensi masuk',
      textMain: textMain,
      textSub:  textSub,
      trailing: !done && status.canCheckin
          ? _Pill(label: 'Presensi', color: AppColors.blue600, onTap: onPresensi)
          : done
              ? _PhotoBox(borderColor: border, photoUrl: att!.checkInPhotoUrl)
              : null,
    );
  }

  Color _circleColor(String s) => switch (s) {
    'hadir'      => AppColors.green500,
    'terlambat'  => AppColors.yellow500,
    'izin'       => AppColors.blue600,
    'sakit'      => AppColors.blue600,
    'alpa'       => AppColors.red500,
    'dispensasi' => AppColors.teal500,
    _            => AppColors.gray400,
  };

  IconData _icon(String? s) => switch (s) {
    'hadir'      => Icons.check_rounded,
    'terlambat'  => Icons.access_time_rounded,
    'izin'       => Icons.description_outlined,
    'sakit'      => Icons.healing_outlined,
    'alpa'       => Icons.close_rounded,
    'dispensasi' => Icons.event_available_outlined,
    _            => Icons.access_time_rounded,
  };
}

// ─── Check-out Card ───────────────────────────────────────────────────────────

class _CheckOutCard extends StatelessWidget {
  final AttendanceStatus status;
  final VoidCallback     onCheckout;

  const _CheckOutCard({required this.status, required this.onCheckout});

  @override
  Widget build(BuildContext context) {
    final att  = status.attendance;
    final done = att?.checkOutTime != null;

    final (bg, border, textMain, textSub) = done
        ? (AppColors.emerald100,            const Color(0xFF6EE7B7),
           AppColors.emerald900,            AppColors.emerald600)
        : (const Color(0xFFF3F4F6),        AppColors.gray200,
           AppColors.gray500,              AppColors.gray400);

    return _AttCard(
      bg: bg, border: border,
      iconBg: done ? AppColors.emerald500 : AppColors.gray400,
      icon:   done ? Icons.logout_rounded : Icons.access_time_rounded,
      title:  'Absen Pulang',
      sub:    done
          ? 'Tercatat jam ${att!.checkOutTime!.substring(0, 5)}'
          : 'Belum melakukan absen pulang',
      textMain: textMain,
      textSub:  textSub,
      trailing: !done && status.canCheckout
          ? _Pill(
              label: 'Absen\nPulang',
              color: AppColors.emerald600,
              onTap: onCheckout,
            )
          : done
              ? _PhotoBox(
                  borderColor: border,
                  photoUrl:    att!.checkOutPhotoUrl,
                  icon:        Icons.check_circle_outline,
                  iconColor:   AppColors.emerald500,
                )
              : null,
    );
  }
}

// ─── Shared attendance card scaffold ──────────────────────────────────────────

class _AttCard extends StatelessWidget {
  final Color    bg;
  final Color    border;
  final Color    iconBg;
  final IconData icon;
  final String   title;
  final String   sub;
  final Color    textMain;
  final Color    textSub;
  final Widget?  trailing;

  const _AttCard({
    required this.bg,
    required this.border,
    required this.iconBg,
    required this.icon,
    required this.title,
    required this.sub,
    required this.textMain,
    required this.textSub,
    this.trailing,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color:        bg,
        borderRadius: AppRadius.card,
        border:       Border.all(color: border),
        boxShadow:    AppShadow.sm,
      ),
      padding: const EdgeInsets.all(12),
      child: Row(
        children: [
          Container(
            width: 44, height: 44,
            decoration: BoxDecoration(shape: BoxShape.circle, color: iconBg),
            child: Icon(icon, color: Colors.white, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title,
                  style: TextStyle(
                    fontSize: 14, fontWeight: FontWeight.w600, color: textMain)),
                const SizedBox(height: 2),
                Text(sub,
                  style: TextStyle(fontSize: 12, color: textSub)),
              ],
            ),
          ),
          if (trailing != null) ...[
            const SizedBox(width: 8),
            trailing!,
          ],
        ],
      ),
    );
  }
}

class _Pill extends StatelessWidget {
  final String       label;
  final Color        color;
  final VoidCallback onTap;

  const _Pill({required this.label, required this.color, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color:        color,
          borderRadius: BorderRadius.circular(8),
        ),
        child: Text(
          label,
          style: const TextStyle(
            color: Colors.white, fontSize: 12,
            fontWeight: FontWeight.bold, height: 1.3,
          ),
          textAlign: TextAlign.center,
        ),
      ),
    );
  }
}

class _PhotoBox extends StatelessWidget {
  final Color    borderColor;
  final String?  photoUrl;
  final IconData icon;
  final Color    iconColor;

  const _PhotoBox({
    required this.borderColor,
    this.photoUrl,
    this.icon       = Icons.person_rounded,
    this.iconColor  = AppColors.gray400,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 56, height: 56,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(10),
        color:        Colors.white.withOpacity(0.50),
        border:       Border.all(color: borderColor),
      ),
      clipBehavior: Clip.antiAlias,
      child: photoUrl != null
          ? Image.network(
              photoUrl!,
              fit: BoxFit.cover,
              errorBuilder: (_, __, ___) => Icon(icon, color: iconColor, size: 28),
            )
          : Icon(icon, color: iconColor, size: 28),
    );
  }
}

// ─── History Button ───────────────────────────────────────────────────────────

class _HistoryButton extends StatelessWidget {
  final VoidCallback onTap;
  const _HistoryButton({required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color:        AppColors.white,
          borderRadius: AppRadius.card,
          border:       Border.all(color: AppColors.gray100),
          boxShadow:    AppShadow.sm,
        ),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        child: const Row(
          children: [
            Icon(Icons.history_rounded, size: 18, color: AppColors.blue600),
            SizedBox(width: 10),
            Expanded(
              child: Text('Riwayat Absensi',
                style: TextStyle(
                  fontSize:   13,
                  fontWeight: FontWeight.w600,
                  color:      AppColors.gray700,
                )),
            ),
            Icon(Icons.chevron_right, color: AppColors.gray400, size: 20),
          ],
        ),
      ),
    );
  }
}

// ─── Error Banner ─────────────────────────────────────────────────────────────

class _ErrorBanner extends StatelessWidget {
  final String message;
  const _ErrorBanner({required this.message});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color:        AppColors.red100,
        borderRadius: AppRadius.card,
        border:       Border.all(color: AppColors.red500.withOpacity(0.40)),
      ),
      padding: const EdgeInsets.all(14),
      child: Row(
        children: [
          const Icon(Icons.error_outline, color: AppColors.red500),
          const SizedBox(width: 8),
          Expanded(
            child: Text(message,
              style: const TextStyle(color: AppColors.red500, fontSize: 13)),
          ),
        ],
      ),
    );
  }
}

// ─── Notification Banner ─────────────────────────────────────────────────────

class _NotifBanner extends StatelessWidget {
  final int          count;
  final VoidCallback onTap;

  const _NotifBanner({required this.count, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color:        AppColors.blue50,
          borderRadius: AppRadius.card,
          border:       Border.all(color: AppColors.blue100),
          boxShadow:    AppShadow.sm,
        ),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        child: Row(
          children: [
            Container(
              width: 36, height: 36,
              decoration: BoxDecoration(
                color:        AppColors.blue600,
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.notifications_rounded,
                color: Colors.white, size: 18),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    '$count notifikasi belum dibaca',
                    style: const TextStyle(
                      fontSize:   13,
                      fontWeight: FontWeight.w600,
                      color:      AppColors.blue800,
                    ),
                  ),
                  const Text(
                    'Tap untuk melihat',
                    style: TextStyle(fontSize: 11, color: AppColors.blue500),
                  ),
                ],
              ),
            ),
            const Icon(Icons.chevron_right, color: AppColors.blue400, size: 20),
          ],
        ),
      ),
    );
  }
}

// ─── Announcement Section ─────────────────────────────────────────────────────

class _AnnouncementSection extends StatelessWidget {
  final List<AnnouncementItem> announcements;
  const _AnnouncementSection({required this.announcements});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color:        AppColors.white,
        borderRadius: AppRadius.card,
        border:       Border.all(color: AppColors.gray100),
        boxShadow:    AppShadow.sm,
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        children: [
          // Header seperti web
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            child: Row(
              children: [
                const Expanded(
                  child: Text('Pengumuman',
                    style: TextStyle(
                      fontSize:   13,
                      fontWeight: FontWeight.w600,
                      color:      AppColors.gray700,
                    )),
                ),
                GestureDetector(
                  onTap: () => Navigator.push(context,
                    MaterialPageRoute(builder: (_) => const AnnouncementListScreen())),
                  child: const Text('Lihat Semua',
                    style: TextStyle(fontSize: 11, color: AppColors.blue600)),
                ),
              ],
            ),
          ),
          const Divider(height: 1, color: AppColors.gray100),

          // List item
          if (announcements.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 24),
              child: Center(
                child: Text('Tidak ada pengumuman',
                  style: TextStyle(fontSize: 13, color: AppColors.gray400)),
              ),
            )
          else
            ...announcements.asMap().entries.map((entry) {
              final i    = entry.key;
              final item = entry.value;
              return Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (i > 0) const Divider(height: 1, color: AppColors.gray100),
                  _AnnouncementTile(item: item),
                ],
              );
            }),
        ],
      ),
    );
  }
}

class _AnnouncementTile extends StatelessWidget {
  final AnnouncementItem item;
  const _AnnouncementTile({required this.item});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Row(
        children: [
          Container(
            width: 36, height: 36,
            decoration: const BoxDecoration(
              color:  AppColors.blue50,
              shape:  BoxShape.circle,
            ),
            child: const Icon(Icons.campaign_rounded,
              size: 18, color: AppColors.blue600),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    if (item.isPinned) ...[
                      const Icon(Icons.push_pin_rounded,
                        size: 11, color: AppColors.blue600),
                      const SizedBox(width: 3),
                    ],
                    Expanded(
                      child: Text(item.title,
                        style: const TextStyle(
                          fontSize:   13,
                          fontWeight: FontWeight.w500,
                          color:      AppColors.gray800,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 2),
                Text(
                  DateFormat('d MMM y', 'id_ID').format(item.publishedAt.toLocal()),
                  style: const TextStyle(fontSize: 11, color: AppColors.gray400),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Bottom Navigation ────────────────────────────────────────────────────────

class _BottomNav extends StatelessWidget {
  final int              selectedTab;  // -1 = beranda, 0-3 = sections
  final void Function(int) onTabTap;
  final VoidCallback     onPresensi;

  const _BottomNav({
    required this.selectedTab,
    required this.onTabTap,
    required this.onPresensi,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color:  AppColors.white,
        border: Border(top: BorderSide(color: AppColors.gray200)),
      ),
      child: SafeArea(
        top: false,
        child: SizedBox(
          height: 64,
          child: Row(
            children: [
              _NavItem(
                icon:       Icons.group_outlined,
                iconFilled: Icons.group,
                label:      'Kesiswaan',
                selected:   selectedTab == 0,
                onTap:      () => onTabTap(0),
              ),
              _NavItem(
                icon:       Icons.menu_book_outlined,
                iconFilled: Icons.menu_book,
                label:      'Kurikulum',
                selected:   selectedTab == 1,
                onTap:      () => onTabTap(1),
              ),

              // ── Presensi — tombol tengah yang naik ──────────────
              Expanded(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    GestureDetector(
                      onTap: onPresensi,
                      child: Transform.translate(
                        offset: const Offset(0, -12),
                        child: Container(
                          width: 52, height: 52,
                          decoration: BoxDecoration(
                            color:     AppColors.blue600,
                            shape:     BoxShape.circle,
                            border:    Border.all(color: AppColors.white, width: 4),
                            boxShadow: [
                              BoxShadow(
                                color:      AppColors.blue600.withOpacity(0.40),
                                blurRadius: 12,
                                offset:     const Offset(0, 4),
                              ),
                            ],
                          ),
                          child: const Icon(
                            Icons.camera_alt_rounded,
                            color: Colors.white,
                            size:  24,
                          ),
                        ),
                      ),
                    ),
                    const Text(
                      'Presensi',
                      style: TextStyle(
                        fontSize:   10,
                        fontWeight: FontWeight.w500,
                        color:      AppColors.blue600,
                      ),
                    ),
                  ],
                ),
              ),

              _NavItem(
                icon:       Icons.business_outlined,
                iconFilled: Icons.business,
                label:      'Prasarana',
                selected:   selectedTab == 2,
                onTap:      () => onTabTap(2),
              ),
              _NavItem(
                icon:       Icons.campaign_outlined,
                iconFilled: Icons.campaign,
                label:      'Humas',
                selected:   selectedTab == 3,
                onTap:      () => onTabTap(3),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _NavItem extends StatelessWidget {
  final IconData     icon;
  final IconData     iconFilled;
  final String       label;
  final bool         selected;
  final VoidCallback onTap;

  const _NavItem({
    required this.icon,
    required this.iconFilled,
    required this.label,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final color = selected ? AppColors.blue600 : AppColors.gray400;
    return Expanded(
      child: GestureDetector(
        onTap:     onTap,
        behavior:  HitTestBehavior.opaque,
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(selected ? iconFilled : icon, color: color, size: 22),
            const SizedBox(height: 2),
            Text(label,
              style: TextStyle(
                fontSize:   10,
                fontWeight: FontWeight.w500,
                color:      color,
              )),
          ],
        ),
      ),
    );
  }
}
