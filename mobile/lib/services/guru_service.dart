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

  // ── Conduct API (catat pelanggaran & prestasi) ─────────────────────────────

  static Future<Map<String, List<ConductCategory>>> getConductCategories() async {
    final body = await ApiClient.get('/guru/conduct-categories');
    return {
      'prestasi':    (body['prestasi']    as List<dynamic>).map((e) => ConductCategory.fromJson(e)).toList(),
      'pelanggaran': (body['pelanggaran'] as List<dynamic>).map((e) => ConductCategory.fromJson(e)).toList(),
    };
  }

  static Future<List<SimpleStudent>> getConductStudents({int? classId, String? q}) async {
    final body = await ApiClient.get(
      '/guru/conduct-students',
      params: {if (classId != null) 'class_id': classId, if (q != null) 'q': q},
    );
    return (body as List<dynamic>).map((e) => SimpleStudent.fromJson(e)).toList();
  }

  static Future<List<Map<String, dynamic>>> getConductClasses() async {
    final body = await ApiClient.get('/guru/conduct-classes');
    return (body as List<dynamic>).cast<Map<String, dynamic>>();
  }

  static Future<String> createConductLog({
    required int studentId,
    required int categoryId,
    String? note,
  }) async {
    final body = await ApiClient.post(
      '/guru/conduct-logs',
      data: {
        'student_id':  studentId,
        'category_id': categoryId,
        if (note != null) 'note': note,
      },
    );
    return body['message'] as String;
  }

  // ── Teaching Sessions ──────────────────────────────────────────────────────

  static Future<Map<String, dynamic>> getTeachingClasses() async {
    return ApiClient.get('/guru/teaching-classes');
  }

  static Future<({List<TeachingSession> data, PaginatedMeta meta})> getTeachingSessions({
    int? classId, int? month, int? year, int page = 1,
  }) async {
    final body = await ApiClient.get(
      '/guru/teaching-sessions',
      params: {
        if (classId != null) 'class_id': classId,
        if (month != null)   'month': month,
        if (year != null)    'year': year,
        'page': page,
      },
    );
    return (
      data: (body['data'] as List<dynamic>).map((e) => TeachingSession.fromJson(e)).toList(),
      meta: PaginatedMeta.fromJson(body['meta'] as Map<String, dynamic>),
    );
  }

  static Future<TeachingSession> getTeachingSession(int id) async {
    final body = await ApiClient.get('/guru/teaching-sessions/$id');
    return TeachingSession.fromJson(body);
  }

  static Future<List<SimpleStudent>> getSessionClassStudents(int classId) async {
    final body = await ApiClient.get('/guru/teaching-sessions/class-students/$classId');
    return (body as List<dynamic>).map((e) => SimpleStudent.fromJson(e)).toList();
  }

  static Future<String> createTeachingSession({
    required int classId,
    int? subjectId,
    required String date,
    required int period,
    String? startTime,
    String? endTime,
    String? note,
    required List<Map<String, dynamic>> attendances,
  }) async {
    final body = await ApiClient.post(
      '/guru/teaching-sessions',
      data: {
        'class_id':    classId,
        if (subjectId != null) 'subject_id': subjectId,
        'date':        date,
        'period':      period,
        if (startTime != null) 'start_time': startTime,
        if (endTime   != null) 'end_time':   endTime,
        if (note      != null) 'note': note,
        'attendances': attendances,
      },
    );
    return body['message'] as String;
  }

  static Future<Map<String, dynamic>> exportTeachingSessions({
    required int classId, required int month, required int year,
  }) async {
    return ApiClient.get(
      '/guru/teaching-sessions/export',
      params: {'class_id': classId, 'month': month, 'year': year},
    );
  }

  // ── Jurnal Guru ────────────────────────────────────────────────────────────

  static Future<({List<TeacherJournal> data, PaginatedMeta meta})> getJournals({
    int? classId, int? month, int? year, int page = 1,
  }) async {
    final body = await ApiClient.get(
      '/guru/journals',
      params: {
        if (classId != null) 'class_id': classId,
        if (month   != null) 'month': month,
        if (year    != null) 'year': year,
        'page': page,
      },
    );
    return (
      data: (body['data'] as List<dynamic>).map((e) => TeacherJournal.fromJson(e)).toList(),
      meta: PaginatedMeta.fromJson(body['meta'] as Map<String, dynamic>),
    );
  }

  static Future<TeacherJournal> getJournal(int id) async {
    final body = await ApiClient.get('/guru/journals/$id');
    return TeacherJournal.fromJson(body);
  }

  static Future<List<SimpleStudent>> getJournalClassStudents(int classId) async {
    final body = await ApiClient.get('/guru/journals/class-students/$classId');
    return (body as List<dynamic>).map((e) => SimpleStudent.fromJson(e)).toList();
  }

  static Future<String> createJournal({
    required int classId,
    int? subjectId,
    required String date,
    int? period,
    required String learningObjectives,
    required String material,
    required String activity,
    String? notes,
    required List<Map<String, dynamic>> absentStudents,
  }) async {
    final body = await ApiClient.post(
      '/guru/journals',
      data: {
        'class_id':            classId,
        if (subjectId != null) 'subject_id': subjectId,
        'date':                date,
        if (period != null)    'period': period,
        'learning_objectives': learningObjectives,
        'material':            material,
        'activity':            activity,
        if (notes != null)     'notes': notes,
        'absent_students':     absentStudents,
      },
    );
    return body['message'] as String;
  }

  static Future<String> deleteJournal(int id) async {
    final body = await ApiClient.delete('/guru/journals/$id');
    return body['message'] as String;
  }
}
