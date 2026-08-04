import 'package:flutter/foundation.dart';
import 'api_client.dart';

class PushNotificationService {
  PushNotificationService._();

  static String? _fcmToken;
  static String? get fcmToken => _fcmToken;

  /// Inisialisasi awal notifikasi push & channel lokal.
  static Future<void> initialize() async {
    try {
      debugPrint('[PushNotificationService] Initializing push notification channels...');
    } catch (e) {
      debugPrint('[PushNotificationService] Initialization note: $e');
    }
  }

  /// Register FCM Token ke server Laravel saat user login.
  static Future<void> registerToken(String token) async {
    _fcmToken = token;
    try {
      await ApiClient.post('/fcm-token', data: {
        'fcm_token': token,
        'device_type': defaultTargetPlatform == TargetPlatform.iOS ? 'ios' : 'android',
        'device_name': defaultTargetPlatform.name,
      });
      debugPrint('[PushNotificationService] FCM Token registered to backend successfully.');
    } catch (e) {
      debugPrint('[PushNotificationService] Failed to register FCM Token: $e');
    }
  }

  /// Remove FCM Token dari server Laravel saat user logout.
  static Future<void> unregisterToken() async {
    if (_fcmToken == null) return;
    try {
      await ApiClient.delete('/fcm-token', data: {
        'fcm_token': _fcmToken,
      });
      _fcmToken = null;
      debugPrint('[PushNotificationService] FCM Token unregistered successfully.');
    } catch (e) {
      debugPrint('[PushNotificationService] Failed to unregister FCM Token: $e');
    }
  }
}
