import '../models/user.dart';
import 'api_client.dart';
import 'device_service.dart';

class AuthService {
  AuthService._();

  /// Login dari Flutter.
  /// Device ID dibaca dari storage jika sudah ada (stabil lintas build/reinstall).
  /// Jika belum ada, baca dari sistem dan simpan untuk seterusnya.
  static Future<User> login(String loginInput, String password) async {
    // Gunakan device_id yang tersimpan agar tetap sama meski APK di-reinstall
    String deviceId = await ApiClient.getDeviceId() ?? '';
    if (deviceId.isEmpty) {
      deviceId = await DeviceService.getDeviceId();
    }

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
