import 'package:device_info_plus/device_info_plus.dart';
import 'package:flutter/foundation.dart';
import 'package:geolocator/geolocator.dart';

/// Layanan untuk mendapatkan device ID yang stabil dan memverifikasi GPS asli.
class DeviceService {
  DeviceService._();

  static final _deviceInfo = DeviceInfoPlugin();

  // ─── Device ID ───────────────────────────────────────────────────────────

  /// Mengembalikan ID unik perangkat:
  ///   Android → ANDROID_ID (stabil per app signature)
  ///   iOS     → identifierForVendor (stabil per vendor)
  static Future<String> getDeviceId() async {
    try {
      if (defaultTargetPlatform == TargetPlatform.android) {
        final info = await _deviceInfo.androidInfo;
        return info.id; // ANDROID_ID — 64-bit hex string
      }

      if (defaultTargetPlatform == TargetPlatform.iOS) {
        final info = await _deviceInfo.iosInfo;
        return info.identifierForVendor ?? 'ios-unknown';
      }
    } catch (_) {
      // Fallback jika plugin gagal
    }
    return 'unknown-${defaultTargetPlatform.name}';
  }

  // ─── GPS Verification ────────────────────────────────────────────────────

  /// Meminta izin lokasi lalu mengembalikan posisi yang sudah diverifikasi.
  /// Melempar [MockLocationException] jika terdeteksi Fake GPS.
  static Future<Position> getVerifiedPosition() async {
    // Pastikan location service aktif
    final serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      throw const LocationServiceException('GPS tidak aktif. Aktifkan lokasi di pengaturan.');
    }

    // Minta izin
    var permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
    }
    if (permission == LocationPermission.deniedForever) {
      throw const LocationPermissionDeniedForeverException(
        'Izin lokasi diblokir permanen. Buka pengaturan untuk mengaktifkan.',
      );
    }
    if (permission == LocationPermission.denied) {
      throw const LocationPermissionException('Izin lokasi ditolak.');
    }

    // Ambil posisi dengan akurasi terbaik
    final position = await Geolocator.getCurrentPosition(
      desiredAccuracy: LocationAccuracy.best,
    );

    // ── Deteksi #1: Platform melaporkan mock location (Android Native API) ─────────
    if (position.isMocked) {
      throw const MockLocationException(
        'Fake GPS terdeteksi (isMocked=true). '
        'Matikan aplikasi pemalsuan lokasi lalu coba lagi.',
      );
    }

    // ── Deteksi #2: Sinyal GPS terlalu lemah / tidak akurat (> 100m) ─────────
    if (position.accuracy > 100.0) {
      throw LocationServiceException(
        'Sinyal GPS lemah (akurasi ${position.accuracy.toStringAsFixed(0)}m). '
        'Bawa HP ke area terbuka.',
      );
    }

    return position;
  }
}

// ─── Custom Exceptions ────────────────────────────────────────────────────────

class MockLocationException implements Exception {
  final String message;
  const MockLocationException(this.message);
  @override
  String toString() => message;
}

class LocationServiceException implements Exception {
  final String message;
  const LocationServiceException(this.message);
  @override
  String toString() => message;
}

class LocationPermissionException implements Exception {
  final String message;
  const LocationPermissionException(this.message);
  @override
  String toString() => message;
}

class LocationPermissionDeniedForeverException implements Exception {
  final String message;
  const LocationPermissionDeniedForeverException(this.message);
  @override
  String toString() => message;
}
