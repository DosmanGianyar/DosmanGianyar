import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../models/notification_item.dart';
import '../providers/auth_provider.dart';
import '../providers/notification_provider.dart';
import '../widgets/image_viewer_dialog.dart';
import '../theme/app_colors.dart';
import '../services/api_client.dart';
import '../services/push_notification_service.dart';
import 'announcement_list_screen.dart';
import 'chat/chat_list_screen.dart';
import 'chat/student_chat_entry.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  String _selectedFilter = 'all'; // 'all', 'unread', 'read'

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<NotificationProvider>().fetchAll();
    });
  }

  Future<void> _triggerTestNotification() async {
    final notifProv = context.read<NotificationProvider>();
    await PushNotificationService.showTestNotification(
      title: '🔔 Uji Coba Push Notifikasi SIMS',
      body: 'Selamat! Spanduk, nada alert, dan pesan notifikasi di HP Anda berfungsi 100%.',
    );
    try {
      final res = await ApiClient.post('/fcm-token/test');
      final isOk = res['success'] == true;
      final msg = res['message']?.toString() ?? 'Uji coba selesai.';
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(msg),
            backgroundColor: isOk ? AppColors.green500 : const Color(0xFFD97706),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Koneksi ke server: ${ApiClient.extractError(e)}'),
            backgroundColor: AppColors.red500,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
    await notifProv.fetchAll();
  }

  void _handleNotificationTap(NotificationItem item) {
    final type = item.type.toLowerCase();
    final title = item.title.toLowerCase();

    // 1. Pengumuman / Informasi -> Buka Halaman Pengumuman
    if (type == 'announcement' ||
        type == 'info' ||
        type == 'information' ||
        title.contains('pengumuman') ||
        title.contains('informasi')) {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const AnnouncementListScreen()),
      );
      return;
    }

    // 2. Chat / Konsultasi Segitiga -> Buka Ruang Chat Terkait
    if (type.startsWith('chat') || title.contains('pesan baru') || title.contains('chat')) {
      final user = context.read<AuthProvider>().user;
      if (user?.role == 'siswa') {
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const StudentChatEntryScreen()),
        );
      } else {
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const ChatListScreen()),
        );
      }
      return;
    }
  }

  String _formatDateHeader(DateTime dt) {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final notifDate = DateTime(dt.year, dt.month, dt.day);

    final diffDays = today.difference(notifDate).inDays;
    if (diffDays == 0) return 'HARI INI';
    if (diffDays == 1) return 'KEMARIN';
    return DateFormat('d MMMM yyyy', 'id_ID').format(dt).toUpperCase();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFEBE5DF), // WhatsApp / Telegram channel wallpaper tone
      appBar: AppBar(
        backgroundColor: const Color(0xFF0F2460),
        foregroundColor: Colors.white,
        elevation: 1,
        titleSpacing: 0,
        title: Row(
          children: [
            Container(
              width: 36, height: 36,
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.15),
                shape: BoxShape.circle,
              ),
              clipBehavior: Clip.antiAlias,
              padding: const EdgeInsets.all(4),
              child: Image.asset(
                'assets/images/logo_sekolah.png',
                fit: BoxFit.contain,
                errorBuilder: (_, __, ___) => const Icon(Icons.school_rounded, color: Colors.white, size: 20),
              ),
            ),
            const SizedBox(width: 10),
            const Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Text(
                        'Pemberitahuan Sekolah',
                        style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                      ),
                      SizedBox(width: 4),
                      Icon(Icons.verified_rounded, color: Color(0xFF38BDF8), size: 16),
                    ],
                  ),
                  Text(
                    'Saluran Resmi SMA N 1 Gianyar',
                    style: TextStyle(color: Color(0xFFCBD5E1), fontSize: 10),
                  ),
                ],
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Uji Coba Push Notifikasi',
            icon: const Icon(Icons.notifications_active_rounded, size: 20, color: Color(0xFFFACC15)),
            onPressed: _triggerTestNotification,
          ),
          Consumer<NotificationProvider>(
            builder: (_, prov, __) {
              if (prov.unreadCount == 0) return const SizedBox.shrink();
              return TextButton.icon(
                icon: const Icon(Icons.done_all_rounded, size: 16, color: Color(0xFF38BDF8)),
                label: const Text(
                  'Tandai Baca',
                  style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                ),
                onPressed: () => prov.markAllRead(),
              );
            },
          ),
        ],
      ),
      body: Consumer<NotificationProvider>(
        builder: (_, prov, __) {
          if (prov.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          if (prov.notifications.isEmpty) {
            return Center(
              child: Container(
                margin: const EdgeInsets.all(24),
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.95),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: const Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.mark_chat_read_rounded, size: 48, color: AppColors.gray400),
                    SizedBox(height: 12),
                    Text(
                      'Belum Ada Notifikasi',
                      style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.gray800),
                    ),
                    SizedBox(height: 4),
                    Text(
                      'Pemberitahuan resmi presensi dan sekolah akan muncul di saluran ini.',
                      style: TextStyle(fontSize: 12, color: AppColors.gray500),
                      textAlign: TextAlign.center,
                    ),
                  ],
                ),
              ),
            );
          }

          // Filter data
          final allItems = prov.notifications;
          final items = allItems.where((item) {
            if (_selectedFilter == 'unread') return !item.isRead;
            if (_selectedFilter == 'read') return item.isRead;
            return true;
          }).toList();

          return Column(
            children: [
              // ─── Filter Bar: Semua, Belum Dibaca, Sudah Dibaca ───
              _buildFilterBar(prov.unreadCount, allItems.length - prov.unreadCount, allItems.length),

              // ─── List Notifikasi ───
              Expanded(
                child: RefreshIndicator(
                  onRefresh: () => prov.fetchAll(),
                  child: items.isEmpty
                      ? Center(
                          child: Container(
                            margin: const EdgeInsets.all(24),
                            padding: const EdgeInsets.all(20),
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.95),
                              borderRadius: BorderRadius.circular(16),
                            ),
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(
                                  _selectedFilter == 'unread'
                                      ? Icons.check_circle_rounded
                                      : Icons.inbox_rounded,
                                  size: 40,
                                  color: _selectedFilter == 'unread' ? const Color(0xFF059669) : AppColors.gray400,
                                ),
                                const SizedBox(height: 10),
                                Text(
                                  _selectedFilter == 'unread'
                                      ? 'Semua Pesan Sudah Dibaca! 🎉'
                                      : 'Tidak Ada Pesan',
                                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.gray800),
                                ),
                              ],
                            ),
                          ),
                        )
                      : ListView.builder(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                          itemCount: items.length,
                          itemBuilder: (ctx, idx) {
                            final item = items[idx];
                            final currentDateStr = _formatDateHeader(item.createdAt);
                            final prevDateStr = idx > 0 ? _formatDateHeader(items[idx - 1].createdAt) : null;
                            final showDatePill = idx == 0 || currentDateStr != prevDateStr;

                            return Column(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                if (showDatePill) _buildDatePill(currentDateStr),
                                _buildTelegramPostBubble(item, () {
                                  prov.markRead(item.id);
                                  _handleNotificationTap(item);
                                }),
                              ],
                            );
                          },
                        ),
                ),
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildFilterBar(int unreadCount, int readCount, int totalCount) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      color: Colors.white,
      child: Row(
        children: [
          _buildFilterChip('all', 'Semua ($totalCount)'),
          const SizedBox(width: 8),
          _buildFilterChip(
            'unread',
            'Belum Dibaca ($unreadCount)',
            badgeColor: unreadCount > 0 ? const Color(0xFFDC2626) : null,
          ),
          const SizedBox(width: 8),
          _buildFilterChip('read', 'Sudah Dibaca ($readCount)'),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String filterKey, String label, {Color? badgeColor}) {
    final isSelected = _selectedFilter == filterKey;
    return GestureDetector(
      onTap: () => setState(() => _selectedFilter = filterKey),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          color: isSelected
              ? (badgeColor ?? const Color(0xFF0F2460))
              : const Color(0xFFF1F5F9),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected
                ? (badgeColor ?? const Color(0xFF0F2460))
                : const Color(0xFFE2E8F0),
            width: isSelected ? 1.4 : 1.0,
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11,
            fontWeight: isSelected ? FontWeight.w900 : FontWeight.w600,
            color: isSelected ? Colors.white : const Color(0xFF475569),
          ),
        ),
      ),
    );
  }

  Widget _buildDatePill(String label) {
    return Center(
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 10),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
        decoration: BoxDecoration(
          color: Colors.black.withValues(alpha: 0.28),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Text(
          label,
          style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: Colors.white, letterSpacing: 0.5),
        ),
      ),
    );
  }

  Widget _buildTelegramPostBubble(NotificationItem item, VoidCallback onTap) {
    final isUnread = !item.isRead;
    final type = item.type.toLowerCase();
    final titleLower = item.title.toLowerCase();

    // Deteksi tipe chat untuk membedakan Orang Tua, Siswa, dan Wali Kelas
    final isChatOrangTua = type == 'chat_orangtua' || titleLower.contains('(orang tua)');
    final isChatSiswa    = type == 'chat_siswa' || titleLower.contains('(siswa)');
    final isChatGuru     = type == 'chat_guru' || titleLower.contains('(wali kelas)');
    final isGeneralChat  = type == 'chat' || titleLower.contains('pesan baru') || titleLower.contains('chat');

    final isAnnouncement = type == 'announcement' || titleLower.contains('pengumuman');

    final (badgeLabel, badgeColor, badgeBg, badgeIcon, showSchoolLogo, actionHint) = switch (type) {
      'reminder_checkin'  => ('PRESENSI DATANG', const Color(0xFF0284C7), const Color(0xFFE0F2FE), Icons.login_rounded, false, null),
      'reminder_checkout' => ('PRESENSI PULANG', const Color(0xFFD97706), const Color(0xFFFEF3C7), Icons.logout_rounded, false, null),
      'announcement'      => ('PENGUMUMAN SEKOLAH', const Color(0xFF7C3AED), const Color(0xFFEDE9FE), Icons.campaign_rounded, true, 'Buka Pengumuman Sekolah ↗'),
      'warning'           => ('PERINGATAN', const Color(0xFFDC2626), const Color(0xFFFEE2E2), Icons.warning_rounded, false, null),
      'success'           => ('BERHASIL', const Color(0xFF059669), const Color(0xFFD1FAE5), Icons.check_circle_rounded, false, null),
      _ => () {
        if (isChatOrangTua) {
          return ('CHAT ORANG TUA 👨‍👩‍👧', const Color(0xFFB45309), const Color(0xFFFEF3C7), Icons.family_restroom_rounded, false, 'Buka Percakapan Chat ↗');
        } else if (isChatSiswa) {
          return ('CHAT SISWA 🎒', const Color(0xFF047857), const Color(0xFFD1FAE5), Icons.school_rounded, false, 'Buka Percakapan Chat ↗');
        } else if (isChatGuru) {
          return ('CHAT WALI KELAS 👨‍🏫', const Color(0xFF6D28D9), const Color(0xFFEDE9FE), Icons.co_present_rounded, false, 'Buka Percakapan Chat ↗');
        } else if (isGeneralChat) {
          return ('CHAT SEGITIGA 💬', const Color(0xFF1D4ED8), const Color(0xFFEFF6FF), Icons.forum_rounded, false, 'Buka Percakapan Chat ↗');
        } else if (isAnnouncement) {
          return ('PENGUMUMAN SEKOLAH', const Color(0xFF7C3AED), const Color(0xFFEDE9FE), Icons.campaign_rounded, true, 'Buka Pengumuman Sekolah ↗');
        }
        return ('INFORMASI DOSMAN', const Color(0xFF0F2460), const Color(0xFFE2E8F0), Icons.info_rounded, true, 'Buka Pengumuman Sekolah ↗');
      }(),
    };

    final timeStr = DateFormat('HH:mm').format(item.createdAt);

    return Align(
      alignment: Alignment.centerLeft,
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          margin: const EdgeInsets.symmetric(vertical: 5),
          decoration: BoxDecoration(
            color: isUnread ? const Color(0xFFEFF6FF) : Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border(
              left: BorderSide(
                color: isUnread ? const Color(0xFF1D4ED8) : const Color(0xFFCBD5E1),
                width: isUnread ? 5.5 : 3.0,
              ),
              top: BorderSide(
                color: isUnread ? const Color(0xFF93C5FD) : const Color(0xFFE2E8F0),
                width: isUnread ? 1.4 : 0.8,
              ),
              right: BorderSide(
                color: isUnread ? const Color(0xFF93C5FD) : const Color(0xFFE2E8F0),
                width: isUnread ? 1.4 : 0.8,
              ),
              bottom: BorderSide(
                color: isUnread ? const Color(0xFF93C5FD) : const Color(0xFFE2E8F0),
                width: isUnread ? 1.4 : 0.8,
              ),
            ),
            boxShadow: isUnread
                ? [
                    const BoxShadow(
                      color: Color(0x281D4ED8),
                      blurRadius: 10,
                      offset: Offset(0, 3),
                    ),
                  ]
                : const [
                    BoxShadow(
                      color: Color(0x0A000000),
                      blurRadius: 3,
                      offset: Offset(0, 1),
                    ),
                  ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // ─── Header Post (Logo Sekolah / Ikon Peran & Badge) ───
              Padding(
                padding: const EdgeInsets.fromLTRB(14, 12, 14, 6),
                child: Row(
                  children: [
                    // Logo Dosman Kecil untuk Informasi & Pengumuman
                    if (showSchoolLogo) ...[
                      Container(
                        width: 20, height: 20,
                        margin: const EdgeInsets.only(right: 6),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          shape: BoxShape.circle,
                          border: Border.all(color: const Color(0xFFCBD5E1), width: 0.8),
                          boxShadow: const [
                            BoxShadow(color: Color(0x15000000), blurRadius: 2, offset: Offset(0, 1)),
                          ],
                        ),
                        clipBehavior: Clip.antiAlias,
                        padding: const EdgeInsets.all(2),
                        child: Image.asset(
                          'assets/images/logo_sekolah.png',
                          fit: BoxFit.contain,
                          errorBuilder: (_, __, ___) => const Icon(Icons.school_rounded, size: 12, color: Color(0xFF0F2460)),
                        ),
                      ),
                    ],

                    // Badge Kategori dengan Ikon Peran (Ortu, Siswa, Guru, dll.)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3.5),
                      decoration: BoxDecoration(
                        color: badgeBg,
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(badgeIcon, size: 13, color: badgeColor),
                          const SizedBox(width: 4),
                          Text(
                            badgeLabel,
                            style: TextStyle(
                              fontSize: 9.5,
                              fontWeight: FontWeight.w900,
                              color: badgeColor,
                              letterSpacing: 0.4,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const Spacer(),

                    // BADGE STATUS TEGAS DI POJOK KANAN ATAS
                    if (isUnread)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3.5),
                        decoration: BoxDecoration(
                          color: const Color(0xFFDC2626),
                          borderRadius: BorderRadius.circular(12),
                          boxShadow: const [
                            BoxShadow(color: Color(0x33DC2626), blurRadius: 4, offset: Offset(0, 1)),
                          ],
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.mark_email_unread_rounded, size: 11, color: Colors.white),
                            SizedBox(width: 4),
                            Text(
                              'BELUM DIBACA',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 9.5,
                                fontWeight: FontWeight.w900,
                                letterSpacing: 0.4,
                              ),
                            ),
                          ],
                        ),
                      )
                    else
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF1F5F9),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.done_all_rounded, size: 13, color: Color(0xFF0284C7)),
                            SizedBox(width: 3),
                            Text(
                              'Sudah Dibaca',
                              style: TextStyle(
                                color: Color(0xFF64748B),
                                fontSize: 9,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ],
                        ),
                      ),
                  ],
                ),
              ),

              // ─── Foto Lampiran Jika Ada (WA/TG Post Preview) ───
              if (item.imageUrl != null && item.imageUrl!.isNotEmpty)
                GestureDetector(
                  onTap: () => ImageViewerDialog.show(
                    context,
                    imageUrl: item.imageUrl!,
                    title: item.title,
                  ),
                  child: Container(
                    margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    clipBehavior: Clip.antiAlias,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Image.network(
                      item.imageUrl!,
                      width: double.infinity,
                      height: 160,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => const SizedBox.shrink(),
                    ),
                  ),
                ),

              // ─── Judul Pesan ───
              Padding(
                padding: const EdgeInsets.fromLTRB(14, 4, 14, 3),
                child: Text(
                  item.title,
                  style: TextStyle(
                    fontSize: isUnread ? 15 : 14,
                    fontWeight: isUnread ? FontWeight.w900 : FontWeight.w700,
                    color: isUnread ? const Color(0xFF0F172A) : const Color(0xFF475569),
                    height: 1.3,
                  ),
                ),
              ),

              // ─── Isi Pesan Lengkap ───
              Padding(
                padding: const EdgeInsets.fromLTRB(14, 2, 14, 8),
                child: Text(
                  item.body,
                  style: TextStyle(
                    fontSize: 13,
                    color: isUnread ? const Color(0xFF1E293B) : const Color(0xFF64748B),
                    fontWeight: isUnread ? FontWeight.w500 : FontWeight.normal,
                    height: 1.45,
                  ),
                ),
              ),

              // ─── Petunjuk Aksi (Buka Pengumuman / Buka Ruang Chat) ───
              if (actionHint != null)
                Padding(
                  padding: const EdgeInsets.fromLTRB(14, 0, 14, 8),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4.5),
                    decoration: BoxDecoration(
                      color: badgeBg.withValues(alpha: 0.6),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: badgeColor.withValues(alpha: 0.25)),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(badgeIcon, size: 12, color: badgeColor),
                        const SizedBox(width: 5),
                        Text(
                          actionHint,
                          style: TextStyle(
                            fontSize: 10.5,
                            fontWeight: FontWeight.bold,
                            color: badgeColor,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),

              // ─── Footer: Jam & Indikator Status Tegas ───
              Container(
                padding: const EdgeInsets.fromLTRB(14, 6, 14, 8),
                decoration: BoxDecoration(
                  color: isUnread ? const Color(0xFFE0E7FF).withValues(alpha: 0.3) : const Color(0xFFF8FAFC),
                  borderRadius: const BorderRadius.only(
                    bottomLeft: Radius.circular(15),
                    bottomRight: Radius.circular(15),
                  ),
                ),
                child: Row(
                  children: [
                    Text(
                      timeStr,
                      style: TextStyle(
                        fontSize: 10.5,
                        color: isUnread ? const Color(0xFF475569) : const Color(0xFF94A3B8),
                        fontWeight: isUnread ? FontWeight.bold : FontWeight.normal,
                      ),
                    ),
                    const Spacer(),

                    // Indikator Status Bawah
                    if (isUnread) ...[
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFEF2F2),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: const Color(0xFFFCA5A5), width: 1),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.fiber_manual_record, size: 7, color: Color(0xFFDC2626)),
                            SizedBox(width: 4),
                            Text(
                              'Belum Dibaca',
                              style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.w900, color: Color(0xFFDC2626)),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 4),
                      const Icon(Icons.done_rounded, size: 16, color: Color(0xFF94A3B8)),
                    ] else ...[
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                        decoration: BoxDecoration(
                          color: const Color(0xFFE0F2FE),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: const Color(0xFFBAE6FD), width: 1),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(
                              'Dibaca',
                              style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.w800, color: Color(0xFF0284C7)),
                            ),
                            SizedBox(width: 4),
                            Icon(Icons.done_all_rounded, size: 16, color: Color(0xFF0284C7)),
                          ],
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
