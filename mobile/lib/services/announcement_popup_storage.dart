import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// Mengelola penyimpanan status "Jangan Tampilkan Lagi" untuk pengumuman pop-up
/// di memori perangkat secara permanen.
class AnnouncementPopupStorage {
  AnnouncementPopupStorage._();

  static const _storage = FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
  );

  static String _key(int announcementId, int? userId) {
    return 'announcement_popup_dismissed_${userId ?? "any"}_$announcementId';
  }

  /// Cek apakah pengumuman tertentu sudah ditandai "Jangan Tampilkan Lagi"
  static Future<bool> isDismissed(int announcementId, {int? userId}) async {
    try {
      final val = await _storage.read(key: _key(announcementId, userId));
      return val == '1' || val == 'true';
    } catch (_) {
      return false;
    }
  }

  /// Tandai pengumuman sebagai "Jangan Tampilkan Lagi" secara permanen
  static Future<void> markDismissed(int announcementId, {int? userId}) async {
    try {
      await _storage.write(key: _key(announcementId, userId), value: 'true');
    } catch (_) {}
  }
}
