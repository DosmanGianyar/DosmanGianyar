import 'package:flutter/foundation.dart';
import '../models/notification_item.dart';
import '../models/announcement.dart';
import '../services/notification_service.dart';

class NotificationProvider extends ChangeNotifier {
  List<NotificationItem>  _notifications  = [];
  List<AnnouncementItem>  _announcements  = [];
  int                     _unreadCount    = 0;
  bool                    _isLoading      = false;

  List<NotificationItem>  get notifications  => _notifications;
  List<AnnouncementItem>  get announcements  => _announcements;
  int                     get unreadCount    => _unreadCount;
  bool                    get isLoading      => _isLoading;

  // ─── Fetch ────────────────────────────────────────────────────────────────

  Future<void> fetchAll() async {
    _isLoading = true;
    notifyListeners();

    try {
      final result = await NotificationService.fetchAll();
      _notifications = result.items;
      _unreadCount   = result.unreadCount;
    } catch (_) {
      // Non-critical — badge stays at 0
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchUnreadCount() async {
    try {
      _unreadCount = await NotificationService.fetchUnreadCount();
      notifyListeners();
    } catch (_) {}
  }

  Future<void> fetchAnnouncements() async {
    try {
      _announcements = await NotificationService.fetchAnnouncements();
      notifyListeners();
    } catch (_) {}
  }

  // ─── Actions ──────────────────────────────────────────────────────────────

  Future<void> markRead(int id) async {
    try {
      await NotificationService.markRead(id);
      _notifications = _notifications.map((n) {
        return n.id == id
            ? NotificationItem(
                id: n.id, title: n.title, body: n.body,
                type: n.type, url: n.url, isRead: true, createdAt: n.createdAt,
              )
            : n;
      }).toList();
      _unreadCount = _notifications.where((n) => !n.isRead).length;
      notifyListeners();
    } catch (_) {}
  }

  Future<void> markAllRead() async {
    try {
      await NotificationService.markAllRead();
      _notifications = _notifications.map((n) => NotificationItem(
        id: n.id, title: n.title, body: n.body,
        type: n.type, url: n.url, isRead: true, createdAt: n.createdAt,
      )).toList();
      _unreadCount = 0;
      notifyListeners();
    } catch (_) {}
  }
}
