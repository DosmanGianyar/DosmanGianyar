import 'dart:io';
import 'package:dio/dio.dart';
import 'package:geolocator/geolocator.dart';
import '../models/attendance.dart';
import 'api_client.dart';

class AttendanceService {
  AttendanceService._();

  /// Status presensi hari ini + info shift.
  static Future<AttendanceStatus> getTodayStatus() async {
    final body = await ApiClient.get('/attendance/status');
    return AttendanceStatus.fromJson(body);
  }

  /// Absen masuk — GPS sudah diverifikasi di layer UI sebelum memanggil ini.
  static Future<String> checkIn(File photoFile, Position position) async {
    final formData = FormData.fromMap({
      'photo':     await MultipartFile.fromFile(
        photoFile.path,
        filename: 'selfie_checkin.jpg',
      ),
      'latitude':  position.latitude.toString(),
      'longitude': position.longitude.toString(),
      'accuracy':  position.accuracy.toString(),
    });

    final body = await ApiClient.postForm('/attendance/checkin', formData);
    return body['message'] as String;
  }

  /// Absen pulang — GPS sudah diverifikasi di layer UI sebelum memanggil ini.
  static Future<String> checkOut(File photoFile, Position position) async {
    final formData = FormData.fromMap({
      'photo':     await MultipartFile.fromFile(
        photoFile.path,
        filename: 'selfie_checkout.jpg',
      ),
      'latitude':  position.latitude.toString(),
      'longitude': position.longitude.toString(),
      'accuracy':  position.accuracy.toString(),
    });

    final body = await ApiClient.postForm('/attendance/checkout', formData);
    return body['message'] as String;
  }

  /// Riwayat presensi per bulan.
  static Future<AttendanceHistory> getHistory({
    required int month,
    required int year,
  }) async {
    final body = await ApiClient.get(
      '/attendance/history',
      params: {'month': month, 'year': year},
    );
    return AttendanceHistory.fromJson(body);
  }

  /// Info shift aktif + koordinat geofence.
  static Future<ActiveShift> getActiveShift() async {
    final body = await ApiClient.get('/shifts/active');
    return ActiveShift.fromJson(body);
  }

  /// Kalkulasi jarak ke titik sekolah (meter) menggunakan Geolocator.
  static double distanceTo(double schoolLat, double schoolLng, Position pos) {
    return Geolocator.distanceBetween(
      pos.latitude, pos.longitude,
      schoolLat, schoolLng,
    );
  }
}
