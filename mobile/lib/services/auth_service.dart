import 'dart:convert';
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

    final user = User.fromJson(body['user'] as Map<String, dynamic>);
    await ApiClient.saveUserCache(json.encode(user.toJson()));
    return user;
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

  /// Ambil data user saat ini dari server, simpan ke cache.
  static Future<User> fetchMe() async {
    final body = await ApiClient.get('/auth/me');
    final user = User.fromJson(body['user'] as Map<String, dynamic>);
    await ApiClient.saveUserCache(json.encode(user.toJson()));
    return user;
  }

  /// Muat data user dari cache lokal (untuk offline/network error).
  static Future<User?> loadCachedUser() async {
    final data = await ApiClient.getUserCache();
    if (data == null) return null;
    try {
      return User.fromJson(json.decode(data) as Map<String, dynamic>);
    } catch (_) {
      return null;
    }
  }
}
