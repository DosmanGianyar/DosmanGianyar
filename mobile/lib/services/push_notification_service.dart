import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:permission_handler/permission_handler.dart';
import 'api_client.dart';
import '../screens/notifications_screen.dart';
import '../theme/app_colors.dart';

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

      // Configure iOS foreground notification presentation options
      await FirebaseMessaging.instance.setForegroundNotificationPresentationOptions(
        alert: true,
        badge: true,
        sound: true,
      );

      // 3. Setup Android & iOS local notification settings
      const AndroidNotificationChannel channel = AndroidNotificationChannel(
        'sims_urgent_alert_v4',
        'Pemberitahuan Utama SIMS',
        description: 'Notifikasi penting presensi, chat, dan pengumuman SMA Negeri 1 Gianyar',
        importance: Importance.max,
        playSound: true,
        enableVibration: true,
        enableLights: true,
        showBadge: true,
        audioAttributesUsage: AudioAttributesUsage.notificationRingtone,
      );

      const AndroidInitializationSettings initializationSettingsAndroid =
          AndroidInitializationSettings('@mipmap/ic_launcher');

      const DarwinInitializationSettings initializationSettingsDarwin =
          DarwinInitializationSettings(
        requestAlertPermission: true,
        requestBadgePermission: true,
        requestSoundPermission: true,
      );

      const InitializationSettings initializationSettings = InitializationSettings(
        android: initializationSettingsAndroid,
        iOS: initializationSettingsDarwin,
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
        try {
          await androidPlugin.deleteNotificationChannel('sims_alert_v3_channel');
          await androidPlugin.deleteNotificationChannel('sims_high_importance_channel');
        } catch (_) {}
        await androidPlugin.createNotificationChannel(channel);
      }

      // 4. Foreground Message Listener for both Android and iOS
      FirebaseMessaging.onMessage.listen((RemoteMessage message) {
        RemoteNotification? notification = message.notification;

        if (notification != null && !kIsWeb) {
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
                category: AndroidNotificationCategory.message,
                visibility: NotificationVisibility.public,
                audioAttributesUsage: AudioAttributesUsage.notificationRingtone,
                channelShowBadge: true,
                ticker: 'Pemberitahuan SIMS SMAN 1 Gianyar',
              ),
              iOS: const DarwinNotificationDetails(
                presentAlert: true,
                presentBadge: true,
                presentSound: true,
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

  /// Meminta izin notifikasi langsung ke sistem (seperti WhatsApp) saat aplikasi dibuka.
  /// Jika pada Android 13+ belum diizinkan, ini akan memicu dialog pop-up resmi Android:
  /// "Izinkan SIMS SMAN 1 Gianyar mengirimkan notifikasi?".
  static Future<bool> requestPermissionIfNeeded([BuildContext? context]) async {
    try {
      // 1. Cek status izin saat ini via permission_handler
      final status = await Permission.notification.status;
      if (status.isGranted) {
        await _ensureNotificationChannel();
        await registerToken();
        return true;
      }

      // 2. Minta izin secara eksplisit ke OS (seperti WhatsApp)
      final requestResult = await Permission.notification.request();

      if (requestResult.isGranted) {
        debugPrint('[FCM] Notification permission GRANTED by user.');
        await _ensureNotificationChannel();
        await registerToken();
        return true;
      } else if (requestResult.isPermanentlyDenied && context != null && context.mounted) {
        debugPrint('[FCM] Notification permission permanently denied.');
        _showSettingsGuideDialog(context);
      }
      return false;
    } catch (e) {
      debugPrint('[FCM] requestPermissionIfNeeded error: $e');
      return false;
    }
  }

  /// Memastikan channel notifikasi berprioritas tinggi (heads-up banner + sound) aktif di OS
  static Future<void> _ensureNotificationChannel() async {
    try {
      const AndroidNotificationChannel channel = AndroidNotificationChannel(
        'sims_urgent_alert_v4',
        'Pemberitahuan Utama SIMS',
        description: 'Notifikasi penting presensi, chat, dan pengumuman SMA Negeri 1 Gianyar',
        importance: Importance.max,
        playSound: true,
        enableVibration: true,
        enableLights: true,
        showBadge: true,
        audioAttributesUsage: AudioAttributesUsage.notificationRingtone,
      );

      final androidPlugin = _localNotifications
          .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>();

      if (androidPlugin != null) {
        try {
          await androidPlugin.deleteNotificationChannel('sims_alert_v3_channel');
          await androidPlugin.deleteNotificationChannel('sims_high_importance_channel');
        } catch (_) {}
        await androidPlugin.createNotificationChannel(channel);
      }
    } catch (e) {
      debugPrint('[FCM] _ensureNotificationChannel error: $e');
    }
  }

  /// Panduan lembut jika pengguna sebelumnya pernah memilih "Jangan tanya lagi"
  static void _showSettingsGuideDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.notifications_active_rounded, color: AppColors.blue600, size: 26),
            SizedBox(width: 10),
            Text('Aktifkan Notifikasi', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          ],
        ),
        content: const Text(
          'Izin notifikasi SIMS belum aktif di HP Anda.\n\n'
          'Agar Anda mendapatkan info pengumuman sekolah dan pengingat presensi langsung melayang di atas layar dengan suara dering seperti WhatsApp, silakan aktifkan notifikasi.',
          style: TextStyle(fontSize: 13.5, color: AppColors.gray700, height: 1.45),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Nanti Saja', style: TextStyle(color: AppColors.gray600)),
          ),
          FilledButton.icon(
            onPressed: () {
              Navigator.of(ctx).pop();
              openAppSettings();
            },
            icon: const Icon(Icons.settings, size: 16),
            label: const Text('Buka Pengaturan', style: TextStyle(fontWeight: FontWeight.bold)),
            style: FilledButton.styleFrom(
              backgroundColor: AppColors.blue600,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
          ),
        ],
      ),
    );
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
        'sims_urgent_alert_v4',
        'Pemberitahuan Utama SIMS',
        description: 'Notifikasi penting presensi, chat, dan pengumuman SMA Negeri 1 Gianyar',
        importance: Importance.max,
        playSound: true,
        enableVibration: true,
        enableLights: true,
        showBadge: true,
        audioAttributesUsage: AudioAttributesUsage.notificationRingtone,
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
        try {
          await androidPlugin.deleteNotificationChannel('sims_alert_v3_channel');
          await androidPlugin.deleteNotificationChannel('sims_high_importance_channel');
        } catch (_) {}
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
            category: AndroidNotificationCategory.message,
            visibility: NotificationVisibility.public,
            audioAttributesUsage: AudioAttributesUsage.notificationRingtone,
            channelShowBadge: true,
            ticker: 'Pemberitahuan SIMS SMAN 1 Gianyar',
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
