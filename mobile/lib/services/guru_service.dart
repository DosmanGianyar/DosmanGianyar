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
    final body = await ApiClient.getList(
      '/guru/conduct-students',
      params: {if (classId != null) 'class_id': classId, if (q != null) 'q': q},
    );
    return body.map((e) => SimpleStudent.fromJson(e as Map<String, dynamic>)).toList();
  }

  static Future<List<Map<String, dynamic>>> getConductClasses() async {
    final body = await ApiClient.getList('/guru/conduct-classes');
    return body.cast<Map<String, dynamic>>();
  }

  static Future<String> createConductLog({
    required int studentId,
    required String type,
    // pelanggaran
    String? description,
    String? severity,
    // prestasi perilaku
    int? categoryId,
    // prestasi lomba
    String? prestasiType,
    String? lombaName,
    String? lombaLevel,
    String? lombaRank,
    // both
    String? note,
  }) async {
    final body = await ApiClient.post(
      '/guru/conduct-logs',
      data: {
        'student_id':                      studentId,
        'type':                            type,
        if (description  != null) 'description':   description,
        if (severity     != null) 'severity':       severity,
        if (prestasiType != null) 'prestasi_type':  prestasiType,
        if (categoryId   != null) 'category_id':    categoryId,
        if (lombaName    != null) 'lomba_name':      lombaName,
        if (lombaLevel   != null) 'lomba_level':     lombaLevel,
        if (lombaRank    != null) 'lomba_rank':      lombaRank,
        if (note         != null) 'note':            note,
      },
    );
    return body['message'] as String;
  }

  static Future<Map<String, dynamic>> getConductHistory({
    String? type,
    int page = 1,
  }) async {
    return ApiClient.get('/guru/conduct-history', params: {
      if (type != null) 'type': type,
      'page': page,
    });
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
    final body = await ApiClient.getList('/guru/teaching-sessions/class-students/$classId');
    return body.map((e) => SimpleStudent.fromJson(e as Map<String, dynamic>)).toList();
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

  // ── Tujuan Pembelajaran (TP) ───────────────────────────────────────────────

  static Future<List<TujuanPembelajaran>> getTpList({int? subjectId}) async {
    final body = await ApiClient.getList(
      '/guru/tp',
      params: {if (subjectId != null) 'subject_id': subjectId},
    );
    return body
        .map((e) => TujuanPembelajaran.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  static Future<TujuanPembelajaran> createTp({
    required int subjectId,
    String? code,
    required String description,
  }) async {
    final body = await ApiClient.post('/guru/tp', data: {
      'subject_id':  subjectId,
      if (code != null && code.isNotEmpty) 'code': code,
      'description': description,
    });
    return TujuanPembelajaran.fromJson(body['tp'] as Map<String, dynamic>);
  }

  static Future<TujuanPembelajaran> updateTp({
    required int id,
    required int subjectId,
    String? code,
    required String description,
  }) async {
    final body = await ApiClient.put('/guru/tp/$id', data: {
      'subject_id':  subjectId,
      if (code != null && code.isNotEmpty) 'code': code,
      'description': description,
    });
    return TujuanPembelajaran.fromJson(body['tp'] as Map<String, dynamic>);
  }

  static Future<TujuanPembelajaran> toggleTp(int id) async {
    final body = await ApiClient.patch('/guru/tp/$id/toggle');
    return TujuanPembelajaran.fromJson(body['tp'] as Map<String, dynamic>);
  }

  static Future<void> deleteTp(int id) async {
    await ApiClient.delete('/guru/tp/$id');
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
    final body = await ApiClient.getList('/guru/journals/class-students/$classId');
    return body.map((e) => SimpleStudent.fromJson(e as Map<String, dynamic>)).toList();
  }

  static Future<String> createJournal({
    required int classId,
    int? subjectId,
    required String date,
    int? period,
    int? periodEnd,
    int? tpId,
    String? learningObjectives,
    required String material,
    required String activity,
    String? notes,
    required List<Map<String, dynamic>> absentStudents,
  }) async {
    final body = await ApiClient.post(
      '/guru/journals',
      data: {
        'class_id':                        classId,
        if (subjectId != null)  'subject_id':  subjectId,
        'date':                            date,
        if (period != null)     'period':      period,
        if (periodEnd != null)  'period_end':  periodEnd,
        if (tpId != null)       'tp_id':       tpId,
        if (learningObjectives != null) 'learning_objectives': learningObjectives,
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

  // ── Input Nilai ────────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>> getGradeClasses() async {
    return ApiClient.get('/guru/grades/classes');
  }

  static Future<List<SubjectItem>> getGradeSubjects() async {
    final body = await ApiClient.getList('/guru/grades/subjects');
    return body.map((e) => SubjectItem.fromJson(e as Map<String, dynamic>)).toList();
  }

  static Future<({List<StudentGradeRow> students, List<SubjectItem> subjects})> getGrades({
    required int classId,
    required int semester,
    required String academicYear,
  }) async {
    final body = await ApiClient.get(
      '/guru/grades',
      params: {'class_id': classId, 'semester': semester, 'academic_year': academicYear},
    );
    return (
      students: (body['students'] as List<dynamic>).map((e) => StudentGradeRow.fromJson(e as Map<String, dynamic>)).toList(),
      subjects: (body['subjects'] as List<dynamic>).map((e) => SubjectItem.fromJson(e as Map<String, dynamic>)).toList(),
    );
  }

  static Future<String> storeGrade({
    required int studentId,
    required int subjectId,
    required String type,
    required double score,
    required int semester,
    required String academicYear,
    String? notes,
  }) async {
    final body = await ApiClient.post(
      '/guru/grades',
      data: {
        'student_id':    studentId,
        'subject_id':    subjectId,
        'type':          type,
        'score':         score,
        'semester':      semester,
        'academic_year': academicYear,
        if (notes != null) 'notes': notes,
      },
    );
    return body['message'] as String;
  }

  static Future<Map<String, dynamic>> exportGrades({
    required int classId, required int semester, required String academicYear,
  }) async {
    return ApiClient.get(
      '/guru/grades/export',
      params: {'class_id': classId, 'semester': semester, 'academic_year': academicYear},
    );
  }

  // ── BK ─────────────────────────────────────────────────────────────────────

  static Future<List<Map<String, dynamic>>> getBkClasses() async {
    final body = await ApiClient.getList('/guru/bk/classes');
    return body.cast<Map<String, dynamic>>();
  }

  static Future<List<SimpleStudent>> getBkStudents({int? classId, String? q}) async {
    final body = await ApiClient.getList(
      '/guru/bk/students',
      params: {
        if (classId != null) 'class_id': classId,
        if (q != null && q.isNotEmpty) 'q': q,
      },
    );
    return body.map((e) => SimpleStudent.fromJson(e as Map<String, dynamic>)).toList();
  }

  static Future<({List<BkLogItem> data, int currentPage, int lastPage, int total})> getBkLogs({
    int? classId, int? studentId, int page = 1,
  }) async {
    final body = await ApiClient.get(
      '/guru/bk',
      params: {
        if (classId != null)   'class_id': classId,
        if (studentId != null) 'student_id': studentId,
        'page': page,
      },
    );
    return (
      data:        (body['data'] as List<dynamic>).map((e) => BkLogItem.fromJson(e as Map<String, dynamic>)).toList(),
      currentPage: body['current_page'] as int? ?? 1,
      lastPage:    body['last_page'] as int? ?? 1,
      total:       body['total'] as int? ?? 0,
    );
  }

  static Future<String> storeBkLog({
    required int studentId,
    required String coachingNote,
    required String date,
  }) async {
    final body = await ApiClient.post(
      '/guru/bk',
      data: {'student_id': studentId, 'coaching_note': coachingNote, 'date': date},
    );
    return body['message'] as String;
  }

  // ── Sarpras ────────────────────────────────────────────────────────────────

  static Future<SarprasStats> getSarprasStats() async {
    final body = await ApiClient.get('/guru/sarpras/stats');
    return SarprasStats.fromJson(body);
  }

  static Future<Map<String, dynamic>> getSarprasCategories() async {
    return ApiClient.get('/guru/sarpras/categories');
  }

  static Future<({List<AssetItem> data, int currentPage, int lastPage, int total})> getAssets({
    String? category, String? condition, String? q, int page = 1,
  }) async {
    final body = await ApiClient.get(
      '/guru/sarpras/assets',
      params: {
        if (category  != null) 'category': category,
        if (condition != null) 'condition': condition,
        if (q != null && q.isNotEmpty) 'q': q,
        'page': page,
      },
    );
    return (
      data:        (body['data'] as List<dynamic>).map((e) => AssetItem.fromJson(e as Map<String, dynamic>)).toList(),
      currentPage: body['current_page'] as int? ?? 1,
      lastPage:    body['last_page'] as int? ?? 1,
      total:       body['total'] as int? ?? 0,
    );
  }

  static Future<({List<DamageReportItem> data, int currentPage, int lastPage})> getDamageReports({
    String? status, int page = 1,
  }) async {
    final body = await ApiClient.get(
      '/guru/sarpras/damage',
      params: {
        if (status != null) 'status': status,
        'page': page,
      },
    );
    return (
      data:        (body['data'] as List<dynamic>).map((e) => DamageReportItem.fromJson(e as Map<String, dynamic>)).toList(),
      currentPage: body['current_page'] as int? ?? 1,
      lastPage:    body['last_page'] as int? ?? 1,
    );
  }

  static Future<String> storeDamageReport({
    required int assetId,
    required String description,
  }) async {
    final body = await ApiClient.post(
      '/guru/sarpras/damage',
      data: {'asset_id': assetId, 'description': description},
    );
    return body['message'] as String;
  }

  static Future<({List<LoanItem> data, int currentPage, int lastPage})> getLoans({
    String? status, int page = 1,
  }) async {
    final body = await ApiClient.get(
      '/guru/sarpras/loans',
      params: {
        if (status != null) 'status': status,
        'page': page,
      },
    );
    return (
      data:        (body['data'] as List<dynamic>).map((e) => LoanItem.fromJson(e as Map<String, dynamic>)).toList(),
      currentPage: body['current_page'] as int? ?? 1,
      lastPage:    body['last_page'] as int? ?? 1,
    );
  }

  static Future<String> storeLoan({
    required int assetId,
    required String startDate,
    required String endDate,
    required String purpose,
  }) async {
    final body = await ApiClient.post(
      '/guru/sarpras/loans',
      data: {'asset_id': assetId, 'start_date': startDate, 'end_date': endDate, 'purpose': purpose},
    );
    return body['message'] as String;
  }

  static Future<String> returnLoan(int id) async {
    final body = await ApiClient.patch('/guru/sarpras/loans/$id/return', data: {});
    return body['message'] as String;
  }

  // ─── Jurnal Bimbingan Guru Wali ──────────────────────────────────────────

  static Future<Map<String, dynamic>> getHomeroomConsultations({String? status}) async {
    final body = await ApiClient.get(
      '/guru/homeroom-consultations',
      params: status != null && status.isNotEmpty ? {'status': status} : null,
    );
    return {
      'class':         body['class'] as Map<String, dynamic>,
      'consultations': (body['consultations'] as List<dynamic>)
          .map((e) => GuruHomeroomConsultation.fromJson(e as Map<String, dynamic>))
          .toList(),
      'counts': GuruHomeroomCounts.fromJson(body['counts'] as Map<String, dynamic>),
    };
  }

  static Future<GuruHomeroomConsultation> scheduleConsultation(int id, String scheduledDate) async {
    final body = await ApiClient.patch(
      '/guru/homeroom-consultations/$id/schedule',
      data: {'scheduled_date': scheduledDate},
    );
    return GuruHomeroomConsultation.fromJson(body['consultation'] as Map<String, dynamic>);
  }

  static Future<GuruHomeroomConsultation> completeConsultation({
    required int    id,
    required String conductedDate,
    required String teacherNote,
    String?         followUp,
  }) async {
    final body = await ApiClient.patch(
      '/guru/homeroom-consultations/$id/complete',
      data: {'conducted_date': conductedDate, 'teacher_note': teacherNote, 'follow_up': followUp},
    );
    return GuruHomeroomConsultation.fromJson(body['consultation'] as Map<String, dynamic>);
  }

  static Future<GuruHomeroomConsultation> cancelConsultation(int id, {String? reason}) async {
    final body = await ApiClient.patch(
      '/guru/homeroom-consultations/$id/cancel',
      data: {'cancelled_reason': reason},
    );
    return GuruHomeroomConsultation.fromJson(body['consultation'] as Map<String, dynamic>);
  }
}
