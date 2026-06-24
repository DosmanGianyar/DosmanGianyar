import '../models/user.dart';
import 'api_client.dart';
import 'device_service.dart';

class AuthService {
  AuthService._();

  /// Login dari Flutter.
  /// Secara otomatis ambil device_id dan kirim ke server.
  /// Return [User] jika berhasil, lempar Exception jika gagal.
  static Future<User> login(String loginInput, String password) async {
    final deviceId = await DeviceService.getDeviceId();

    final body = await ApiClient.post('/auth/login', data: {
      'login':     loginInput.trim(),
      'password':  password,
      'device_id': deviceId,
    });

    await ApiClient.saveToken(body['token'] as String);
    await ApiClient.saveDeviceId(deviceId);

    return User.fromJson(body['user'] as Map<String, dynamic>);
  }

  /// Logout — hapus token dari server dan local storage.
  static Future<void> logout() async {
    try {
      await ApiClient.post('/auth/logout');
    } catch (_) {
      // Tetap hapus local meski server gagal
    } finally {
      await ApiClient.clearAuth();
    }
  }

  /// Cek apakah user sudah login (ada token tersimpan).
  static Future<bool> isLoggedIn() async {
    final token = await ApiClient.getToken();
    return token != null && token.isNotEmpty;
  }

  /// Ambil data user saat ini dari server.
  static Future<User> fetchMe() async {
    final body = await ApiClient.get('/auth/me');
    return User.fromJson(body['user'] as Map<String, dynamic>);
  }
}
