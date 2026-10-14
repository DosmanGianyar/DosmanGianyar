import 'package:flutter/foundation.dart';
import '../models/notification_item.dart';
import '../models/announcement.dart';
import '../services/notification_service.dart';
import '../services/announcement_popup_storage.dart';

class NotificationProvider extends ChangeNotifier {
  List<NotificationItem>  _notifications  = [];
  List<AnnouncementItem>  _announcements  = [];
  AnnouncementItem?       _popupAnnouncement;
  int                     _unreadCount    = 0;
  bool                    _isLoading      = false;

  List<NotificationItem>  get notifications     => _notifications;
  List<AnnouncementItem>  get announcements     => _announcements;
  AnnouncementItem?       get popupAnnouncement => _popupAnnouncement;
  int                     get unreadCount       => _unreadCount;
  bool                    get isLoading         => _isLoading;

  // ─── Fetch ────────────────────────────────────────────────────────────────

  Future<void> fetchAll() async {
    _isLoading = true;
    notifyListeners();

    try {
      final result = await NotificationService.fetchAll();
      _notifications = result.items;
      _unreadCount   = result.unreadCount;
    } catch (e) {
      debugPrint('Error fetching notifications: $e');
      _unreadCount = _notifications.where((n) => !n.isRead).length;
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

  /// Mengambil pengumuman pop-up aktif yang belum ditandai "Jangan Tampilkan Lagi"
  Future<AnnouncementItem?> fetchPopupAnnouncement({int? userId}) async {
    try {
      final item = await NotificationService.fetchPopupAnnouncement();
      if (item != null) {
        final isDismissed = await AnnouncementPopupStorage.isDismissed(item.id, userId: userId);
        if (!isDismissed) {
          _popupAnnouncement = item;
          notifyListeners();
          return item;
        }
      }
      _popupAnnouncement = null;
      notifyListeners();
      return null;
    } catch (e) {
      debugPrint('[NotificationProvider] fetchPopupAnnouncement error: $e');
      return null;
    }
  }

  /// Menandai pengumuman sebagai "Jangan Tampilkan Lagi" selamanya di perangkat ini
  Future<void> dismissPopupForever(int announcementId, {int? userId}) async {
    await AnnouncementPopupStorage.markDismissed(announcementId, userId: userId);
    _popupAnnouncement = null;
    notifyListeners();
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
