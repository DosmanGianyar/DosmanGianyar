import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'api_client.dart';

@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  debugPrint('[FCM] Handling background message: ${message.messageId}');
}

class PushNotificationService {
  PushNotificationService._();

  static final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  static final FlutterLocalNotificationsPlugin _localNotifications = FlutterLocalNotificationsPlugin();

  static String? _fcmToken;
  static String? get fcmToken => _fcmToken;

  /// Inisialisasi Firebase & Channel Notifikasi Lokal.
  static Future<void> initialize() async {
    try {
      // 1. Set background message handler
      FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

      // 2. Request permission (Android 13+ & iOS)
      NotificationSettings settings = await _messaging.requestPermission(
        alert: true,
        badge: true,
        sound: true,
        provisional: false,
      );

      debugPrint('[FCM] User permission status: ${settings.authorizationStatus}');

      // 3. Setup Android local notification channel
      const AndroidNotificationChannel channel = AndroidNotificationChannel(
        'sims_high_importance_channel',
        'Notifikasi SIMS',
        description: 'Notifikasi penting presensi, pengumuman, dan catatan siswa SMAN 1 Gianyar',
        importance: Importance.high,
        playSound: true,
      );

      const AndroidInitializationSettings initializationSettingsAndroid =
          AndroidInitializationSettings('@mipmap/ic_launcher');

      const InitializationSettings initializationSettings = InitializationSettings(
        android: initializationSettingsAndroid,
      );

      await _localNotifications.initialize(initializationSettings);

      await _localNotifications
          .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()
          ?.createNotificationChannel(channel);

      // 4. Foreground Message Listener
      FirebaseMessaging.onMessage.listen((RemoteMessage message) {
        RemoteNotification? notification = message.notification;
        AndroidNotification? android = message.notification?.android;

        if (notification != null && android != null && !kIsWeb) {
          _localNotifications.show(
            notification.hashCode,
            notification.title,
            notification.body,
            NotificationDetails(
              android: AndroidNotificationDetails(
                channel.id,
                channel.name,
                channelDescription: channel.description,
                icon: '@mipmap/ic_launcher',
                importance: Importance.max,
                priority: Priority.high,
                playSound: true,
              ),
            ),
          );
        }
      });

      // 5. Fetch initial FCM Token
      String? token = await _messaging.getToken();
      if (token != null) {
        _fcmToken = token;
        debugPrint('[FCM] Token retrieved: $token');
      }

      // 6. Token refresh listener
      _messaging.onTokenRefresh.listen((newToken) {
        _fcmToken = newToken;
        registerToken(newToken);
      });
    } catch (e) {
      debugPrint('[FCM] Initialization note: $e');
    }
  }

  /// Register FCM Token ke server Laravel saat user login.
  static Future<void> registerToken([String? token]) async {
    final tokenToRegister = token ?? _fcmToken ?? await _messaging.getToken();
    if (tokenToRegister == null) return;

    _fcmToken = tokenToRegister;
    try {
      await ApiClient.post('/fcm-token', data: {
        'fcm_token': tokenToRegister,
        'device_type': defaultTargetPlatform == TargetPlatform.iOS ? 'ios' : 'android',
        'device_name': defaultTargetPlatform.name,
      });
      debugPrint('[FCM] Token registered to backend successfully.');
    } catch (e) {
      debugPrint('[FCM] Failed to register FCM Token: $e');
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
      debugPrint('[FCM] Token unregistered successfully.');
    } catch (e) {
      debugPrint('[FCM] Failed to unregister FCM Token: $e');
    }
  }

  /// Trigger a local push notification test directly on the device
  static Future<void> showTestNotification({
    String title = '🔔 Uji Coba Push Notifikasi SIMS',
    String body = 'Selamat! Push Notifikasi di HP Android Anda berfungsi dengan lancar.',
  }) async {
    try {
      const AndroidNotificationChannel channel = AndroidNotificationChannel(
        'sims_high_importance_channel',
        'Notifikasi SIMS',
        description: 'Notifikasi penting presensi, pengumuman, dan catatan siswa SMAN 1 Gianyar',
        importance: Importance.max,
        playSound: true,
      );

      const AndroidInitializationSettings initializationSettingsAndroid =
          AndroidInitializationSettings('@mipmap/ic_launcher');

      const InitializationSettings initializationSettings = InitializationSettings(
        android: initializationSettingsAndroid,
      );

      await _localNotifications.initialize(initializationSettings);

      final androidPlugin = _localNotifications
          .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>();

      if (androidPlugin != null) {
        await androidPlugin.requestNotificationsPermission();
        await androidPlugin.createNotificationChannel(channel);
      }

      await _localNotifications.show(
        DateTime.now().millisecondsSinceEpoch ~/ 1000,
        title,
        body,
        NotificationDetails(
          android: AndroidNotificationDetails(
            channel.id,
            channel.name,
            channelDescription: channel.description,
            icon: '@mipmap/ic_launcher',
            importance: Importance.max,
            priority: Priority.high,
            playSound: true,
          ),
        ),
      );
    } catch (e) {
      debugPrint('[FCM] Test notification error: $e');
    }
  }
}
