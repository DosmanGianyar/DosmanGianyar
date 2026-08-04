import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../config/app_config.dart';

/// Wrapper Dio dengan interceptor otomatis:
///   - Inject Bearer token dari secure storage
///   - Inject X-Device-ID header
///   - Tangani error 401/403 secara terpusat
class ApiClient {
  ApiClient._();

  static const _storage = FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
  );

  static final Dio _dio = _buildDio();

  static Dio _buildDio() {
    final dio = Dio(
      BaseOptions(
        baseUrl:        AppConfig.baseUrl,
        connectTimeout: AppConfig.connectTimeout,
        receiveTimeout: AppConfig.receiveTimeout,
        headers: {
          'Accept':       'application/json',
          'Content-Type': 'application/json',
        },
      ),
    );

    dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token    = await _storage.read(key: 'auth_token');
          final deviceId = await _storage.read(key: 'device_id');

          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          if (deviceId != null) {
            options.headers['X-Device-ID'] = deviceId;
          }

          handler.next(options);
        },
        onError: (error, handler) {
          // Biarkan caller yang handle error spesifik
          handler.next(error);
        },
      ),
    );

    return dio;
  }

  // ─── Public methods ──────────────────────────────────────────────────────

  static Future<Map<String, dynamic>> get(
    String path, {
    Map<String, dynamic>? params,
  }) async {
    final resp = await _dio.get(path, queryParameters: params);
    return resp.data as Map<String, dynamic>;
  }

  static Future<List<dynamic>> getList(
    String path, {
    Map<String, dynamic>? params,
  }) async {
    final resp = await _dio.get(path, queryParameters: params);
    return resp.data as List<dynamic>;
  }

  static Future<Map<String, dynamic>> post(
    String path, {
    Map<String, dynamic>? data,
  }) async {
    final resp = await _dio.post(path, data: data);
    return resp.data as Map<String, dynamic>;
  }

  static Future<Map<String, dynamic>> put(
    String path, {
    Map<String, dynamic>? data,
  }) async {
    final resp = await _dio.put(path, data: data);
    return resp.data as Map<String, dynamic>;
  }

  static Future<Map<String, dynamic>> patch(
    String path, {
    Map<String, dynamic>? data,
  }) async {
    final resp = await _dio.patch(path, data: data);
    return resp.data as Map<String, dynamic>;
  }

  static Future<Map<String, dynamic>> delete(
    String path, {
    Map<String, dynamic>? data,
  }) async {
    final resp = await _dio.delete(path, data: data);
    return resp.data as Map<String, dynamic>;
  }

  static Future<Map<String, dynamic>> postForm(
    String path,
    FormData formData,
  ) async {
    final resp = await _dio.post(
      path,
      data: formData,
      options: Options(contentType: 'multipart/form-data'),
    );
    return resp.data as Map<String, dynamic>;
  }

  /// Extrak pesan error dari DioException
  static String extractError(Object error) {
    if (error is DioException) {
      // Prioritas: ambil pesan dari body response server
      final data = error.response?.data;
      if (data is Map) {
        if (data['message'] != null) return data['message'] as String;
        if (data['error']   != null) return data['error']   as String;
        // Laravel validation errors
        final errors = data['errors'];
        if (errors is Map && errors.isNotEmpty) {
          final first = errors.values.first;
          if (first is List && first.isNotEmpty) return first.first as String;
        }
      }

      // Fallback berdasarkan HTTP status
      final status = error.response?.statusCode;
      if (status != null) {
        return switch (status) {
          401 => 'Email/NIS atau password salah.',
          403 => 'Akses ditolak. Hubungi admin.',
          422 => 'Data tidak valid. Periksa kembali.',
          429 => 'Terlalu banyak percobaan. Tunggu sebentar.',
          500 => 'Server error. Hubungi admin.',
          _   => 'Terjadi kesalahan ($status).',
        };
      }

      // Fallback berdasarkan tipe koneksi
      return switch (error.type) {
        DioExceptionType.connectionTimeout => 'Koneksi timeout. Pastikan internet aktif lalu coba lagi.',
        DioExceptionType.receiveTimeout    => 'Server tidak merespons. Coba lagi sebentar.',
        DioExceptionType.connectionError   => 'Tidak dapat terhubung ke server.\nPastikan internet aktif lalu coba lagi.',
        DioExceptionType.sendTimeout       => 'Koneksi timeout saat mengirim data. Coba lagi.',
        DioExceptionType.badCertificate    => 'Sertifikat SSL tidak valid. Hubungi admin.',
        DioExceptionType.cancel            => 'Permintaan dibatalkan.',
        _                                  => 'Koneksi bermasalah. Pastikan internet aktif lalu coba lagi.',
      };
    }
    return 'Error: ${error.toString()}';
  }

  static Future<void> saveToken(String token)        => _storage.write(key: 'auth_token',   value: token);
  static Future<void> saveDeviceId(String id)        => _storage.write(key: 'device_id',    value: id);
  static Future<void> saveUserCache(String userJson) => _storage.write(key: 'cached_user',  value: userJson);
  static Future<String?> getToken()                  => _storage.read(key: 'auth_token');
  static Future<String?> getDeviceId()               => _storage.read(key: 'device_id');
  static Future<String?> getUserCache()              => _storage.read(key: 'cached_user');

  // ─── Multi-Account Saved Credentials ────────────────────────────────────

  static Future<List<Map<String, String>>> getSavedAccounts() async {
    try {
      final jsonStr = await _storage.read(key: 'saved_accounts_list');
      if (jsonStr != null && jsonStr.isNotEmpty) {
        final List<dynamic> list = jsonDecode(jsonStr);
        return list.map((e) => Map<String, String>.from(e as Map)).toList();
      }
    } catch (_) {}

    final single = await getSavedCredentials();
    if (single != null) {
      return [single];
    }
    return [];
  }

  static Future<void> saveSavedAccount(String login, String password) async {
    final list = await getSavedAccounts();
    list.removeWhere((item) => item['login']?.toLowerCase() == login.trim().toLowerCase());
    list.insert(0, {'login': login.trim(), 'password': password});
    if (list.length > 5) list.removeLast();

    await _storage.write(key: 'saved_accounts_list', value: jsonEncode(list));
    await saveSavedCredentials(login.trim(), password);
  }

  static Future<void> removeSavedAccount(String login) async {
    final list = await getSavedAccounts();
    list.removeWhere((item) => item['login']?.toLowerCase() == login.trim().toLowerCase());
    await _storage.write(key: 'saved_accounts_list', value: jsonEncode(list));
    if (list.isEmpty) {
      await clearSavedCredentials();
    }
  }

  static Future<void> saveSavedCredentials(String login, String password) async {
    await _storage.write(key: 'saved_login',    value: login);
    await _storage.write(key: 'saved_password', value: password);
  }

  static Future<Map<String, String>?> getSavedCredentials() async {
    final login = await _storage.read(key: 'saved_login');
    final password = await _storage.read(key: 'saved_password');
    if (login != null && login.isNotEmpty) {
      return {'login': login, 'password': password ?? ''};
    }
    return null;
  }

  static Future<void> clearSavedCredentials() async {
    await _storage.delete(key: 'saved_login');
    await _storage.delete(key: 'saved_password');
  }

  static Future<void> clearAuth() async {
    final savedAccounts = await _storage.read(key: 'saved_accounts_list');
    final savedLogin    = await _storage.read(key: 'saved_login');
    final savedPassword = await _storage.read(key: 'saved_password');
    final deviceId      = await _storage.read(key: 'device_id');
    await _storage.deleteAll();
    if (savedAccounts != null) await _storage.write(key: 'saved_accounts_list', value: savedAccounts);
    if (savedLogin != null)    await _storage.write(key: 'saved_login',    value: savedLogin);
    if (savedPassword != null) await _storage.write(key: 'saved_password', value: savedPassword);
    if (deviceId != null)      await _storage.write(key: 'device_id',      value: deviceId);
  }
}
