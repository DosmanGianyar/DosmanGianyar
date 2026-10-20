import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../models/notification_item.dart';
import '../providers/notification_provider.dart';
import '../widgets/image_viewer_dialog.dart';
import '../theme/app_colors.dart';
import '../services/api_client.dart';
import '../services/push_notification_service.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
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
              return TextButton(
                onPressed: () => prov.markAllRead(),
                child: const Text(
                  'Tandai Baca',
                  style: TextStyle(color: AppColors.blue200, fontSize: 11, fontWeight: FontWeight.bold),
                ),
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
                  color: Colors.white.withValues(alpha: 0.90),
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

          final items = prov.notifications;

          return RefreshIndicator(
            onRefresh: () => prov.fetchAll(),
            child: ListView.builder(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
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
                    _buildTelegramPostBubble(item, () => prov.markRead(item.id)),
                  ],
                );
              },
            ),
          );
        },
      ),
    );
  }

  Widget _buildDatePill(String label) {
    return Center(
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 10),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
        decoration: BoxDecoration(
          color: Colors.black.withValues(alpha: 0.22),
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
    final (badgeLabel, badgeColor, badgeBg, badgeIcon) = switch (item.type) {
      'reminder_checkin'  => ('PRESENSI DATANG', const Color(0xFF0284C7), const Color(0xFFE0F2FE), Icons.login_rounded),
      'reminder_checkout' => ('PRESENSI PULANG', const Color(0xFFD97706), const Color(0xFFFEF3C7), Icons.logout_rounded),
      'announcement'      => ('PENGUMUMAN', const Color(0xFF7C3AED), const Color(0xFFEDE9FE), Icons.campaign_rounded),
      'warning'           => ('PERINGATAN', const Color(0xFFDC2626), const Color(0xFFFEE2E2), Icons.warning_rounded),
      'success'           => ('BERHASIL', const Color(0xFF059669), const Color(0xFFD1FAE5), Icons.check_circle_rounded),
      _                   => ('INFORMASI', const Color(0xFF0F2460), const Color(0xFFE2E8F0), Icons.info_rounded),
    };

    final timeStr = DateFormat('HH:mm').format(item.createdAt);

    return Align(
      alignment: Alignment.centerLeft,
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          margin: const EdgeInsets.symmetric(vertical: 4),
          decoration: BoxDecoration(
            color: item.isRead ? Colors.white : const Color(0xFFF8FAFC),
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: item.isRead ? AppColors.gray200 : AppColors.blue200,
              width: item.isRead ? 0.8 : 1.4,
            ),
            boxShadow: const [
              BoxShadow(
                color: Color(0x0A000000),
                blurRadius: 4,
                offset: Offset(0, 2),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // ─── Header Post (Saluran SMA N 1 Gianyar) ───
              Padding(
                padding: const EdgeInsets.fromLTRB(12, 10, 12, 6),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                      decoration: BoxDecoration(
                        color: badgeBg,
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(badgeIcon, size: 12, color: badgeColor),
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
                    if (!item.isRead)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: AppColors.blue600,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Text(
                          'BARU',
                          style: TextStyle(color: Colors.white, fontSize: 8.5, fontWeight: FontWeight.bold),
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
                    margin: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
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
                padding: const EdgeInsets.fromLTRB(12, 4, 12, 2),
                child: Text(
                  item.title,
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: item.isRead ? FontWeight.bold : FontWeight.w900,
                    color: AppColors.gray900,
                    height: 1.3,
                  ),
                ),
              ),

              // ─── Isi Pesan Lengkap ───
              Padding(
                padding: const EdgeInsets.fromLTRB(12, 2, 12, 8),
                child: Text(
                  item.body,
                  style: const TextStyle(
                    fontSize: 13,
                    color: Color(0xFF334155),
                    height: 1.45,
                  ),
                ),
              ),

              // ─── Footer: Jam & Centang Dua (WA/Telegram style) ───
              Padding(
                padding: const EdgeInsets.fromLTRB(12, 0, 12, 8),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    Text(
                      timeStr,
                      style: const TextStyle(fontSize: 10, color: Color(0xFF94A3B8)),
                    ),
                    const SizedBox(width: 4),
                    Icon(
                      Icons.done_all_rounded,
                      size: 15,
                      color: item.isRead ? const Color(0xFF38BDF8) : const Color(0xFF94A3B8), // Biru jika sudah dibaca
                    ),
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
