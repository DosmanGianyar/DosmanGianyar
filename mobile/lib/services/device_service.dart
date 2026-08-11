import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:device_info_plus/device_info_plus.dart';
import 'package:geolocator/geolocator.dart';

class DeviceService {
  // ─── Device ID & Info ───────────────────────────────────────────────────────

  /// Mengambil Device ID unik perangkat (Android ID / iOS Identifier).
  static Future<String> getDeviceId() async {
    final deviceInfo = DeviceInfoPlugin();
    try {
      if (Platform.isAndroid) {
        final androidInfo = await deviceInfo.androidInfo;
        return androidInfo.id; // Unique Android Hardware/OS ID
      } else if (Platform.isIOS) {
        final iosInfo = await deviceInfo.iosInfo;
        return iosInfo.identifierForVendor ?? 'ios-unknown';
      }
    } catch (_) {
      return 'unknown-device';
    }
    return 'unknown-${defaultTargetPlatform.name}';
  }

  // ─── GPS Verification ────────────────────────────────────────────────────

  /// Meminta izin lokasi lalu mengembalikan posisi yang sudah diverifikasi.
  /// Memiliki fallback otomatis 3 Tahap (High Accuracy Fused -> Last Known -> Medium Accuracy)
  /// agar tidak stuck / error "GPS tidak ditemukan" di Android.
  static Future<Position> getVerifiedPosition() async {
    // Pastikan location service aktif
    final serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      throw const LocationServiceException('Layanan lokasi (GPS) belum aktif. Silakan aktifkan GPS di HP Anda.');
    }

    // Minta izin
    var permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
    }
    if (permission == LocationPermission.deniedForever) {
      throw const LocationPermissionDeniedForeverException(
        'Izin lokasi diblokir permanen. Buka Pengaturan HP -> Izin Aplikasi -> SIMS -> Lokasi untuk mengaktifkan.',
      );
    }
    if (permission == LocationPermission.denied) {
      throw const LocationPermissionException('Izin lokasi ditolak oleh pengguna.');
    }

    Position? position;

    // Tahap 1: Coba ambil posisi presisi tinggi dengan Fused Location (Timeout 6 detik)
    try {
      position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
        timeLimit: const Duration(seconds: 6),
      );
    } catch (_) {
      // Jika getCurrentPosition timeout / error, lanjut ke fallback Tahap 2
    }

    // Tahap 2: Jika belum dapat, ambil Last Known Position (Posisi terakhir yang tersimpan di sistem HP)
    if (position == null) {
      try {
        position = await Geolocator.getLastKnownPosition();
      } catch (_) {}
    }

    // Tahap 3: Jika masih null, coba sekali lagi dengan mode medium accuracy (Timeout 5 detik)
    if (position == null) {
      try {
        position = await Geolocator.getCurrentPosition(
          desiredAccuracy: LocationAccuracy.medium,
          timeLimit: const Duration(seconds: 5),
        );
      } catch (_) {}
    }

    if (position == null) {
      throw const LocationServiceException(
        'Lokasi GPS tidak ditemukan. Pastikan Anda berada di luar ruangan / dekat jendela dan GPS HP aktif.',
      );
    }

    // ── Deteksi #1: Platform melaporkan mock location (Android Native API) ─────────
    if (position.isMocked) {
      throw const MockLocationException(
        'Fake GPS terdeteksi (isMocked=true). '
        'Matikan aplikasi pemalsuan lokasi lalu coba lagi.',
      );
    }

    // ── Deteksi #2: Sinyal GPS terlalu lemah / tidak akurat (> 200m) ─────────
    if (position.accuracy > 200.0) {
      throw LocationServiceException(
        'Sinyal GPS kurang akurat (${position.accuracy.toStringAsFixed(0)}m). '
        'Bawa HP ke area yang tidak terhalang atap tebal.',
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
