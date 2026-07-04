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

  static Future<Map<String, dynamic>> delete(String path) async {
    final resp = await _dio.delete(path);
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

  static Future<void> saveToken(String token)    => _storage.write(key: 'auth_token', value: token);
  static Future<void> saveDeviceId(String id)    => _storage.write(key: 'device_id',  value: id);
  static Future<String?> getToken()              => _storage.read(key: 'auth_token');
  static Future<void> clearAuth()                => _storage.deleteAll();
}
