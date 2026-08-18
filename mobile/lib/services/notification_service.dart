import '../models/notification_item.dart';
import '../models/announcement.dart';
import 'api_client.dart';

class NotificationService {
  NotificationService._();

  static Future<({List<NotificationItem> items, int unreadCount})> fetchAll() async {
    final body = await ApiClient.get('/notifications');
    final rawList = body['notifications'] as List? ?? [];
    final items = <NotificationItem>[];
    for (final e in rawList) {
      if (e is Map<String, dynamic>) {
        try {
          items.add(NotificationItem.fromJson(e));
        } catch (_) {}
      }
    }
    final unreadCount = body['unread_count'] != null
        ? (int.tryParse(body['unread_count'].toString()) ?? 0)
        : 0;
    return (items: items, unreadCount: unreadCount);
  }

  static Future<int> fetchUnreadCount() async {
    final body = await ApiClient.get('/notifications/unread-count');
    return body['unread_count'] != null
        ? (int.tryParse(body['unread_count'].toString()) ?? 0)
        : 0;
  }

  static Future<void> markRead(int id) async {
    await ApiClient.post('/notifications/$id/read');
  }

  static Future<void> markAllRead() async {
    await ApiClient.post('/notifications/read-all');
  }

  static Future<List<AnnouncementItem>> fetchAnnouncements() async {
    final body = await ApiClient.get('/announcements');
    return (body['announcements'] as List)
        .map((e) => AnnouncementItem.fromJson(e as Map<String, dynamic>))
        .toList();
  }
}
