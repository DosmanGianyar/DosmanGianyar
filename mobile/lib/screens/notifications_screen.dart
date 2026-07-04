import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/notification_item.dart';
import '../providers/notification_provider.dart';
import '../theme/app_colors.dart';

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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Notifikasi',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 16)),
        backgroundColor: const Color(0xFF0F2460),
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          Consumer<NotificationProvider>(
            builder: (_, prov, __) {
              if (prov.unreadCount == 0) return const SizedBox.shrink();
              return TextButton(
                onPressed: () => prov.markAllRead(),
                child: const Text('Tandai Semua Dibaca',
                  style: TextStyle(color: AppColors.blue200, fontSize: 12)),
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
            return const Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.notifications_none_rounded, size: 56, color: AppColors.gray300),
                  SizedBox(height: 12),
                  Text('Tidak ada notifikasi',
                    style: TextStyle(fontSize: 14, color: AppColors.gray400)),
                ],
              ),
            );
          }

          return RefreshIndicator(
            onRefresh: () => prov.fetchAll(),
            child: ListView.separated(
              padding: const EdgeInsets.symmetric(vertical: 8),
              itemCount: prov.notifications.length,
              separatorBuilder: (_, __) => const Divider(height: 1, indent: 72, color: AppColors.gray100),
              itemBuilder: (_, i) => _NotifTile(
                item: prov.notifications[i],
                onTap: () => prov.markRead(prov.notifications[i].id),
              ),
            ),
          );
        },
      ),
    );
  }
}

class _NotifTile extends StatelessWidget {
  final NotificationItem item;
  final VoidCallback     onTap;
  const _NotifTile({required this.item, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final (iconData, iconColor, iconBg) = switch (item.type) {
      'success' => (Icons.check_circle_rounded,  AppColors.green500,  AppColors.green100),
      'warning' => (Icons.warning_rounded,        AppColors.amber500,  AppColors.amber100),
      'error'   => (Icons.error_rounded,          AppColors.red500,    AppColors.red100),
      _         => (Icons.notifications_rounded,  AppColors.blue500,   AppColors.blue100),
    };

    return InkWell(
      onTap: onTap,
      child: Container(
        color: item.isRead ? Colors.transparent : AppColors.blue50,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 40, height: 40,
              decoration: BoxDecoration(color: iconBg, shape: BoxShape.circle),
              child: Icon(iconData, color: iconColor, size: 20),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(item.title,
                          style: TextStyle(
                            fontSize:   13,
                            fontWeight: item.isRead ? FontWeight.w500 : FontWeight.bold,
                            color:      AppColors.gray800,
                          ),
                        ),
                      ),
                      if (!item.isRead)
                        Container(
                          width: 8, height: 8,
                          decoration: const BoxDecoration(
                            color: AppColors.blue600, shape: BoxShape.circle),
                        ),
                    ],
                  ),
                  const SizedBox(height: 2),
                  Text(item.body,
                    style: const TextStyle(fontSize: 12, color: AppColors.gray500, height: 1.4),
                    maxLines: 2, overflow: TextOverflow.ellipsis),
                  const SizedBox(height: 4),
                  Text(_timeAgo(item.createdAt),
                    style: const TextStyle(fontSize: 10, color: AppColors.gray400)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _timeAgo(DateTime dt) {
    final diff = DateTime.now().difference(dt);
    if (diff.inMinutes < 1)  return 'Baru saja';
    if (diff.inMinutes < 60) return '${diff.inMinutes} menit lalu';
    if (diff.inHours   < 24) return '${diff.inHours} jam lalu';
    if (diff.inDays    < 7)  return '${diff.inDays} hari lalu';
    final months = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return '${dt.day} ${months[dt.month]} ${dt.year}';
  }
}
