import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'api_client.dart';
import '../screens/notifications_screen.dart';

@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  debugPrint('[FCM] Handling background message: ${message.messageId}');
}

class PushNotificationService {
  PushNotificationService._();

  static final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();
  static final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  static final FlutterLocalNotificationsPlugin _localNotifications = FlutterLocalNotificationsPlugin();

  static String? _fcmToken;
  static String? get fcmToken => _fcmToken;

  /// Membuka halaman Notifikasi ketika notifikasi diklik
  static void openNotificationsPage() {
    final state = navigatorKey.currentState;
    if (state != null) {
      state.push(
        MaterialPageRoute(builder: (_) => const NotificationsScreen()),
      );
    }
  }

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

      // 3. Setup Android local notification channel (High Alert & Sound)
      const AndroidNotificationChannel channel = AndroidNotificationChannel(
        'sims_alert_v3_channel',
        'Notifikasi Alert SIMS',
        description: 'Notifikasi penting presensi, pengumuman, dan alert SMAN 1 Gianyar',
        importance: Importance.max,
        playSound: true,
        enableVibration: true,
        enableLights: true,
      );

      const AndroidInitializationSettings initializationSettingsAndroid =
          AndroidInitializationSettings('@mipmap/ic_launcher');

      const InitializationSettings initializationSettings = InitializationSettings(
        android: initializationSettingsAndroid,
      );

      await _localNotifications.initialize(
        initializationSettings,
        onDidReceiveNotificationResponse: (NotificationResponse response) {
          debugPrint('[FCM] Notification tapped on device: ${response.payload}');
          openNotificationsPage();
        },
      );

      final androidPlugin = _localNotifications
          .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>();

      if (androidPlugin != null) {
        await androidPlugin.requestNotificationsPermission();
        await androidPlugin.createNotificationChannel(channel);
      }

      // 4. Foreground Message Listener
      FirebaseMessaging.onMessage.listen((RemoteMessage message) {
        RemoteNotification? notification = message.notification;
        AndroidNotification? android = message.notification?.android;

        if (notification != null && android != null && !kIsWeb) {
          final Int64List vibrationPattern = Int64List.fromList([0, 500, 250, 500]);
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
                priority: Priority.max,
                playSound: true,
                enableVibration: true,
                vibrationPattern: vibrationPattern,
              ),
            ),
          );
        }
      });

      // 5. App Opened from Notification Listener
      FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
        debugPrint('[FCM] Notification opened app from background: ${message.messageId}');
        openNotificationsPage();
      });

      _messaging.getInitialMessage().then((RemoteMessage? message) {
        if (message != null) {
          debugPrint('[FCM] Initial notification opened app: ${message.messageId}');
          openNotificationsPage();
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

  /// Trigger a local push notification test directly on the device with full alert sound & vibration
  static Future<void> showTestNotification({
    String title = '🔔 Uji Coba Push Notifikasi SIMS',
    String body = 'Selamat! Push Notifikasi dan nada alert di HP Anda telah aktif dan terbaca.',
  }) async {
    try {
      const AndroidNotificationChannel channel = AndroidNotificationChannel(
        'sims_alert_v3_channel',
        'Notifikasi Alert SIMS',
        description: 'Notifikasi penting presensi, pengumuman, dan alert SMAN 1 Gianyar',
        importance: Importance.max,
        playSound: true,
        enableVibration: true,
        enableLights: true,
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
        // Hapus channel lama agar Android memperbarui seting nada & alert
        await androidPlugin.deleteNotificationChannel('sims_high_importance_channel');
        await androidPlugin.createNotificationChannel(channel);
      }

      final Int64List vibrationPattern = Int64List.fromList([0, 500, 250, 500]);

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
            priority: Priority.max,
            playSound: true,
            enableVibration: true,
            vibrationPattern: vibrationPattern,
            styleInformation: BigTextStyleInformation(
              body,
              contentTitle: title,
              htmlFormatContentTitle: true,
              htmlFormatBigText: true,
            ),
          ),
        ),
      );
    } catch (e) {
      debugPrint('[FCM] Test notification error: $e');
    }
  }
}
