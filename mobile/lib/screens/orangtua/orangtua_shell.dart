import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/user.dart';
import '../../providers/auth_provider.dart';
import '../../providers/orangtua_provider.dart';
import '../../theme/app_colors.dart';
import '../../providers/notification_provider.dart';
import '../../services/push_notification_service.dart';
import '../../widgets/announcement_popup_dialog.dart';
import '../login_screen.dart';
import '../notifications_screen.dart';
import '../profile_screen.dart';
import 'orangtua_achievement_screen.dart';
import 'orangtua_attendance_screen.dart';
import 'orangtua_conduct_screen.dart';
import '../kesiswaan/school_regulation_screen.dart';
import '../chat/chat_list_screen.dart';

class OrangtuaShell extends StatefulWidget {
  const OrangtuaShell({super.key});

  @override
  State<OrangtuaShell> createState() => _OrangtuaShellState();
}

class _OrangtuaShellState extends State<OrangtuaShell> {
  bool _popupChecked = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = context.read<AuthProvider>().user;
      context.read<OrangtuaProvider>().initFromUser(user?.children ?? []);
      context.read<OrangtuaProvider>().refreshChildren();
      context.read<NotificationProvider>().fetchUnreadCount();
      _checkPopupAnnouncement();
      PushNotificationService.requestPermissionIfNeeded(context);
    });
  }

  Future<void> _checkPopupAnnouncement() async {
    if (!mounted || _popupChecked) return;
    _popupChecked = true;

    try {
      final authProv  = context.read<AuthProvider>();
      final notifProv = context.read<NotificationProvider>();
      final userId    = authProv.user?.id;

      final popup = await notifProv.fetchPopupAnnouncement(userId: userId);
      if (popup != null && mounted) {
        await AnnouncementPopupDialog.show(
          context,
          announcement: popup,
          userId: userId,
          onDismissForever: () {
            notifProv.dismissPopupForever(popup.id, userId: userId);
          },
        );
      }
    } catch (_) {}
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

  void _openChat() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const ChatListScreen(filterType: 'all')),
    );
  }

  void _openNotifications() {
    final notifProv = context.read<NotificationProvider>();
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const NotificationsScreen()),
    ).then((_) {
      if (mounted) notifProv.fetchUnreadCount();
    });
  }

  @override
  Widget build(BuildContext context) {
    final user   = context.watch<AuthProvider>().user;
    final prov   = context.watch<OrangtuaProvider>();
    final unread = context.watch<NotificationProvider>().unreadCount;
    final child  = prov.selectedChild;

    return Scaffold(
      backgroundColor: AppColors.slate100,
      body: SafeArea(
        bottom: false,
        child: RefreshIndicator(
          onRefresh: () async {
            await Future.wait([
              context.read<OrangtuaProvider>().refreshChildren(),
              context.read<NotificationProvider>().fetchUnreadCount(),
            ]);
          },
          child: ListView(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
            children: [
              _TopBar(
                userName: user?.name ?? '',
                unread: unread,
                onNotif: _openNotifications,
                onProfile: _openProfile,
                onChat: _openChat,
                onLogout: _logout,
              ),
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
  final int          unread;
  final VoidCallback onNotif;
  final VoidCallback onProfile;
  final VoidCallback onChat;
  final VoidCallback onLogout;

  const _TopBar({
    required this.userName,
    required this.unread,
    required this.onNotif,
    required this.onProfile,
    required this.onChat,
    required this.onLogout,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.gray200),
      ),
      child: Row(
        children: [
          Container(
            width: 34,
            height: 34,
            padding: const EdgeInsets.all(5),
            decoration: BoxDecoration(
              color: AppColors.slate100,
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: AppColors.gray200),
            ),
            child: Image.asset(
              'assets/images/logo_sekolah.png',
              fit: BoxFit.contain,
              errorBuilder: (_, __, ___) => const Icon(
                Icons.school_outlined,
                color: AppColors.blue600,
                size: 18,
              ),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Portal Orang Tua',
                  style: TextStyle(
                    fontSize: 11,
                    color: AppColors.gray500,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                Text(
                  userName,
                  style: const TextStyle(
                    fontSize: 14,
                    color: AppColors.gray900,
                    fontWeight: FontWeight.w700,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
          IconButton(
            onPressed: onChat,
            icon: const Icon(Icons.chat_bubble_outline_rounded, color: AppColors.gray700, size: 20),
            tooltip: 'Chat Wali Kelas',
            visualDensity: VisualDensity.compact,
          ),
          Stack(
            clipBehavior: Clip.none,
            children: [
              IconButton(
                onPressed: onNotif,
                icon: const Icon(Icons.notifications_outlined, color: AppColors.gray700, size: 22),
                tooltip: 'Notifikasi',
                visualDensity: VisualDensity.compact,
              ),
              if (unread > 0)
                Positioned(
                  top: 4,
                  right: 4,
                  child: Container(
                    padding: const EdgeInsets.all(3.5),
                    decoration: const BoxDecoration(
                      color: AppColors.red500,
                      shape: BoxShape.circle,
                    ),
                    child: Text(
                      unread > 9 ? '9+' : '$unread',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 8.5,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
            ],
          ),
          IconButton(
            onPressed: onProfile,
            icon: const Icon(Icons.person_outline_rounded, color: AppColors.gray700, size: 22),
            tooltip: 'Profil',
            visualDensity: VisualDensity.compact,
          ),
          IconButton(
            onPressed: onLogout,
            icon: const Icon(Icons.logout_rounded, color: AppColors.gray500, size: 20),
            tooltip: 'Keluar',
            visualDensity: VisualDensity.compact,
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
          const SizedBox(height: 18),
          OutlinedButton.icon(
            onPressed: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const SchoolRegulationScreen()),
            ),
            icon: const Icon(Icons.balance_rounded, size: 18),
            label: const Text('Lihat Tata Tertib Sekolah'),
            style: OutlinedButton.styleFrom(
              foregroundColor: const Color(0xFF7C3AED),
              side: const BorderSide(color: Color(0xFFC4B5FD)),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
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
      decoration: BoxDecoration(
        color: Colors.white, borderRadius: AppRadius.card,
        border: Border.all(color: AppColors.gray200),
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        children: [
          Container(height: 3, color: const Color(0xFF206BC4)),
          Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                CircleAvatar(
                  radius: 22,
                  backgroundColor: const Color(0xFFEBF3FE),
                  backgroundImage: child.photoUrl != null ? NetworkImage(child.photoUrl!) : null,
                  onBackgroundImageError: child.photoUrl != null ? (_, __) {} : null,
                  child: child.photoUrl == null
                      ? Text(child.initials, style: const TextStyle(color: Color(0xFF206BC4), fontWeight: FontWeight.w700))
                      : null,
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(child.name, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.gray800)),
                      if (child.className != null)
                        Text(child.className!, style: const TextStyle(fontSize: 12, color: AppColors.gray500)),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Menu grid (Card Kecil Kotak 2 Kolom) ────────────────────────────────────

class _MenuGrid extends StatelessWidget {
  final ChildSummary child;
  const _MenuGrid({required this.child});

  @override
  Widget build(BuildContext context) {
    final attBadge     = child.badges.attendance;
    final conductBadge = child.badges.conduct;
    final achBadge     = child.badges.achievement;
    final chatBadge    = child.badges.chat;

    final items = <_SquareMenuItem>[
      _SquareMenuItem(
        icon: Icons.calendar_today_outlined,
        iconBg: AppColors.blue50,
        iconColor: AppColors.blue600,
        tag: 'Harian',
        title: 'Riwayat Absensi',
        subtitle: 'Kehadiran & foto',
        hasBadge: attBadge.hasUnread,
        badgeText: 'Baru',
        badgeColor: const Color(0xFF206BC4),
        onTap: () {
          context.read<OrangtuaProvider>().markFeatureSeen(child.id, 'attendance');
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => OrangtuaAttendanceScreen(studentId: child.id, studentName: child.name),
            ),
          );
        },
      ),
      _SquareMenuItem(
        icon: Icons.assignment_outlined,
        iconBg: AppColors.amber50,
        iconColor: AppColors.amber700,
        tag: 'SIPINTER',
        title: 'Catatan Karakter',
        subtitle: 'Disiplin & apresiasi',
        hasBadge: conductBadge.hasUnread,
        badgeText: conductBadge.count > 0 ? '${conductBadge.count} Baru' : 'Baru',
        badgeColor: AppColors.red600,
        onTap: () {
          context.read<OrangtuaProvider>().markFeatureSeen(child.id, 'conduct');
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => OrangtuaConductScreen(studentId: child.id, studentName: child.name),
            ),
          );
        },
      ),
      _SquareMenuItem(
        icon: Icons.emoji_events_outlined,
        iconBg: AppColors.emerald50,
        iconColor: AppColors.emerald600,
        tag: 'Rekap',
        title: 'Prestasi Siswa',
        subtitle: 'Daftar penghargaan',
        hasBadge: achBadge.hasUnread,
        badgeText: achBadge.count > 0 ? '${achBadge.count} Baru' : 'Baru',
        badgeColor: AppColors.emerald600,
        onTap: () {
          context.read<OrangtuaProvider>().markFeatureSeen(child.id, 'achievement');
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => OrangtuaAchievementScreen(studentId: child.id, studentName: child.name),
            ),
          );
        },
      ),
      _SquareMenuItem(
        icon: Icons.chat_bubble_outline_rounded,
        iconBg: AppColors.blue50,
        iconColor: AppColors.blue600,
        tag: 'Obrolan',
        title: 'Chat Wali Kelas',
        subtitle: 'Ruang bersama & privat',
        hasBadge: chatBadge.hasUnread,
        badgeText: chatBadge.count > 0 ? '${chatBadge.count}' : 'Baru',
        badgeColor: AppColors.blue600,
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => const ChatListScreen(filterType: 'all'),
          ),
        ),
      ),
      _SquareMenuItem(
        icon: Icons.menu_book_outlined,
        iconBg: const Color(0xFFF1F5F9),
        iconColor: AppColors.gray700,
        tag: 'Pedoman',
        title: 'Tata Tertib',
        subtitle: 'Aturan & kewajiban',
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => const SchoolRegulationScreen(),
          ),
        ),
      ),
      _SquareMenuItem(
        icon: Icons.person_outline_rounded,
        iconBg: const Color(0xFFF1F5F9),
        iconColor: AppColors.gray700,
        tag: 'Akun',
        title: 'Profil Orang Tua',
        subtitle: 'Data diri & kontak',
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => const ProfileScreen(),
          ),
        ),
      ),
    ];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Padding(
          padding: EdgeInsets.only(left: 2, bottom: 10),
          child: Text(
            'MENU PEMANTAUAN ANAK',
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w700,
              color: AppColors.gray500,
              letterSpacing: 0.6,
            ),
          ),
        ),
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: items.length,
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            crossAxisSpacing: 10,
            mainAxisSpacing: 10,
            childAspectRatio: 1.32,
          ),
          itemBuilder: (context, index) => _SquareMenuCard(item: items[index]),
        ),
      ],
    );
  }
}

class _SquareMenuItem {
  final IconData     icon;
  final Color        iconBg;
  final Color        iconColor;
  final String       tag;
  final String       title;
  final String       subtitle;
  final VoidCallback onTap;
  final bool         hasBadge;
  final String?      badgeText;
  final Color?       badgeColor;

  const _SquareMenuItem({
    required this.icon,
    required this.iconBg,
    required this.iconColor,
    required this.tag,
    required this.title,
    required this.subtitle,
    required this.onTap,
    this.hasBadge = false,
    this.badgeText,
    this.badgeColor,
  });
}

class _SquareMenuCard extends StatelessWidget {
  final _SquareMenuItem item;
  const _SquareMenuCard({required this.item});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(12),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: item.onTap,
        child: Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: item.hasBadge
                  ? (item.badgeColor ?? AppColors.red500).withValues(alpha: 0.4)
                  : AppColors.gray200,
              width: item.hasBadge ? 1.5 : 1.0,
            ),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Stack(
                    clipBehavior: Clip.none,
                    children: [
                      Container(
                        width: 38,
                        height: 38,
                        decoration: BoxDecoration(
                          color: item.iconBg,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Icon(item.icon, color: item.iconColor, size: 20),
                      ),
                      if (item.hasBadge)
                        Positioned(
                          top: -2,
                          right: -2,
                          child: Container(
                            width: 10,
                            height: 10,
                            decoration: BoxDecoration(
                              color: item.badgeColor ?? AppColors.red500,
                              shape: BoxShape.circle,
                              border: Border.all(color: Colors.white, width: 2),
                            ),
                          ),
                        ),
                    ],
                  ),
                  if (item.hasBadge)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                      decoration: BoxDecoration(
                        color: (item.badgeColor ?? AppColors.red500),
                        borderRadius: BorderRadius.circular(6),
                        boxShadow: [
                          BoxShadow(
                            color: (item.badgeColor ?? AppColors.red500).withValues(alpha: 0.35),
                            blurRadius: 4,
                            offset: const Offset(0, 1.5),
                          ),
                        ],
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Container(
                            width: 5,
                            height: 5,
                            decoration: const BoxDecoration(
                              color: Colors.white,
                              shape: BoxShape.circle,
                            ),
                          ),
                          const SizedBox(width: 4),
                          Text(
                            item.badgeText ?? 'Baru',
                            style: const TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w800,
                              color: Colors.white,
                              letterSpacing: 0.2,
                            ),
                          ),
                        ],
                      ),
                    )
                  else
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                      decoration: BoxDecoration(
                        color: AppColors.slate100,
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        item.tag,
                        style: const TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w600,
                          color: AppColors.gray500,
                        ),
                      ),
                    ),
                ],
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    item.title,
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: AppColors.gray900,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 2),
                  Text(
                    item.subtitle,
                    style: const TextStyle(
                      fontSize: 11,
                      color: AppColors.gray500,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
