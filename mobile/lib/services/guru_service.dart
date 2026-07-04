import '../models/guru_dashboard.dart';
import '../models/guru_models.dart';
import 'api_client.dart';

class GuruService {
  GuruService._();

  static Future<GuruDashboard> getDashboard() async {
    final body = await ApiClient.get('/guru/dashboard');
    return GuruDashboard.fromJson(body);
  }

  static Future<List<GuruClass>> getClasses() async {
    final body = await ApiClient.get('/guru/classes');
    return (body['classes'] as List<dynamic>)
        .map((e) => GuruClass.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  static Future<DailyAttendanceSummary> getAttendanceDaily({
    required int classId,
    required String date,
  }) async {
    final body = await ApiClient.get(
      '/guru/attendance/daily',
      params: {'class_id': classId, 'date': date},
    );
    return DailyAttendanceSummary.fromJson(body);
  }

  static Future<RekapAbsensi> getAttendanceRekap({
    required int classId,
    required int month,
    required int year,
  }) async {
    final body = await ApiClient.get(
      '/guru/attendance/rekap',
      params: {'class_id': classId, 'month': month, 'year': year},
    );
    return RekapAbsensi.fromJson(body);
  }

  // ── Permits ────────────────────────────────────────────────────────────────

  static Future<({List<GuruPermit> data, PaginatedMeta meta})> getPermits({
    String status = 'pending',
    int page = 1,
  }) async {
    final body = await ApiClient.get(
      '/guru/permits',
      params: {'status': status, 'page': page},
    );
    return (
      data: (body['data'] as List<dynamic>)
          .map((e) => GuruPermit.fromJson(e as Map<String, dynamic>))
          .toList(),
      meta: PaginatedMeta.fromJson(body['meta'] as Map<String, dynamic>),
    );
  }

  static Future<String> approvePermit(int id) async {
    final body = await ApiClient.post('/guru/permits/$id/approve');
    return body['message'] as String;
  }

  static Future<String> rejectPermit(int id, String note) async {
    final body = await ApiClient.post(
      '/guru/permits/$id/reject',
      data: {'rejection_note': note},
    );
    return body['message'] as String;
  }

  // ── Forgot Attendance ──────────────────────────────────────────────────────

  static Future<({List<GuruForgotAttendance> data, PaginatedMeta meta})> getForgotAttendance({
    String status = 'pending',
    int page = 1,
  }) async {
    final body = await ApiClient.get(
      '/guru/forgot-attendance',
      params: {'status': status, 'page': page},
    );
    return (
      data: (body['data'] as List<dynamic>)
          .map((e) => GuruForgotAttendance.fromJson(e as Map<String, dynamic>))
          .toList(),
      meta: PaginatedMeta.fromJson(body['meta'] as Map<String, dynamic>),
    );
  }

  static Future<String> approveForgotAttendance(int id) async {
    final body = await ApiClient.post('/guru/forgot-attendance/$id/approve');
    return body['message'] as String;
  }

  static Future<String> rejectForgotAttendance(int id, String note) async {
    final body = await ApiClient.post(
      '/guru/forgot-attendance/$id/reject',
      data: {'teacher_note': note},
    );
    return body['message'] as String;
  }

  // ── Early Checkouts ────────────────────────────────────────────────────────

  static Future<({List<GuruEarlyCheckout> data, PaginatedMeta meta})> getEarlyCheckouts({
    String status = 'pending',
    int page = 1,
  }) async {
    final body = await ApiClient.get(
      '/guru/early-checkouts',
      params: {'status': status, 'page': page},
    );
    return (
      data: (body['data'] as List<dynamic>)
          .map((e) => GuruEarlyCheckout.fromJson(e as Map<String, dynamic>))
          .toList(),
      meta: PaginatedMeta.fromJson(body['meta'] as Map<String, dynamic>),
    );
  }

  static Future<String> approveEarlyCheckout(int id, {String? note}) async {
    final body = await ApiClient.post(
      '/guru/early-checkouts/$id/approve',
      data: {'reviewer_note': note},
    );
    return body['message'] as String;
  }

  static Future<String> rejectEarlyCheckout(int id, String note) async {
    final body = await ApiClient.post(
      '/guru/early-checkouts/$id/reject',
      data: {'reviewer_note': note},
    );
    return body['message'] as String;
  }

  // ── Conduct ────────────────────────────────────────────────────────────────

  static Future<List<GuruConductStudent>> getConduct(int classId) async {
    final body = await ApiClient.get(
      '/guru/conduct',
      params: {'class_id': classId},
    );
    return (body['students'] as List<dynamic>)
        .map((e) => GuruConductStudent.fromJson(e as Map<String, dynamic>))
        .toList();
  }
}
