// Models untuk fitur guru — attendance, permits, conduct

// ─── SchoolClass ─────────────────────────────────────────────────────────────

class GuruClass {
  final int id;
  final String name;
  final String? grade;
  final int studentCount;

  const GuruClass({
    required this.id,
    required this.name,
    this.grade,
    required this.studentCount,
  });

  factory GuruClass.fromJson(Map<String, dynamic> json) => GuruClass(
    id:           json['id'] as int,
    name:         json['name'] as String,
    grade:        json['grade'] as String?,
    studentCount: json['student_count'] as int? ?? 0,
  );
}

// ─── Daily Attendance ─────────────────────────────────────────────────────────

class DailyAttendanceSummary {
  final String date;
  final int classId;
  final Map<String, int> summary;
  final List<DailyStudentRow> students;

  const DailyAttendanceSummary({
    required this.date,
    required this.classId,
    required this.summary,
    required this.students,
  });

  factory DailyAttendanceSummary.fromJson(Map<String, dynamic> json) {
    final sumMap = (json['summary'] as Map<String, dynamic>? ?? {});
    return DailyAttendanceSummary(
      date:     json['date'] as String,
      classId:  json['class_id'] as int,
      summary:  sumMap.map((k, v) => MapEntry(k, (v as num).toInt())),
      students: (json['students'] as List<dynamic>? ?? [])
          .map((e) => DailyStudentRow.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class DailyStudentRow {
  final int id;
  final String name;
  final String? nis;
  final String status;
  final String? checkInTime;
  final bool hasEarlyCheckout;

  const DailyStudentRow({
    required this.id,
    required this.name,
    this.nis,
    required this.status,
    this.checkInTime,
    required this.hasEarlyCheckout,
  });

  factory DailyStudentRow.fromJson(Map<String, dynamic> json) => DailyStudentRow(
    id:               json['id'] as int,
    name:             json['name'] as String,
    nis:              json['nis'] as String?,
    status:           json['status'] as String? ?? 'alpa',
    checkInTime:      json['check_in_time'] as String?,
    hasEarlyCheckout: json['has_early_checkout'] as bool? ?? false,
  );
}

// ─── Rekap ───────────────────────────────────────────────────────────────────

class RekapAbsensi {
  final int classId;
  final int month;
  final int year;
  final List<String> allDays;
  final List<String> schoolDays;
  final List<String> offDays;
  final List<RekapStudentRow> students;

  const RekapAbsensi({
    required this.classId,
    required this.month,
    required this.year,
    required this.allDays,
    required this.schoolDays,
    required this.offDays,
    required this.students,
  });

  factory RekapAbsensi.fromJson(Map<String, dynamic> json) => RekapAbsensi(
    classId:    json['class_id'] as int,
    month:      json['month'] as int,
    year:       json['year'] as int,
    allDays:    (json['all_days'] as List<dynamic>? ?? []).cast<String>(),
    schoolDays: (json['school_days'] as List<dynamic>? ?? []).cast<String>(),
    offDays:    (json['off_days'] as List<dynamic>? ?? []).cast<String>(),
    students:   (json['students'] as List<dynamic>? ?? [])
        .map((e) => RekapStudentRow.fromJson(e as Map<String, dynamic>))
        .toList(),
  );
}

class RekapStudentRow {
  final int id;
  final String name;
  final String? nis;
  final Map<String, String> statuses;
  final Map<String, int> counts;

  const RekapStudentRow({
    required this.id,
    required this.name,
    this.nis,
    required this.statuses,
    required this.counts,
  });

  factory RekapStudentRow.fromJson(Map<String, dynamic> json) {
    final statusMap = (json['statuses'] as Map<String, dynamic>? ?? {})
        .map((k, v) => MapEntry(k, v as String));
    final countMap  = (json['counts'] as Map<String, dynamic>? ?? {})
        .map((k, v) => MapEntry(k, (v as num).toInt()));
    return RekapStudentRow(
      id:       json['id'] as int,
      name:     json['name'] as String,
      nis:      json['nis'] as String?,
      statuses: statusMap,
      counts:   countMap,
    );
  }
}

// ─── Permit ──────────────────────────────────────────────────────────────────

class GuruPermit {
  final int id;
  final String studentName;
  final String className;
  final String type;
  final String typeLabel;
  final String startDate;
  final String endDate;
  final String reason;
  final String status;
  final String? rejectionNote;

  const GuruPermit({
    required this.id,
    required this.studentName,
    required this.className,
    required this.type,
    required this.typeLabel,
    required this.startDate,
    required this.endDate,
    required this.reason,
    required this.status,
    this.rejectionNote,
  });

  bool get isPending  => status == 'pending';
  bool get isApproved => status == 'approved';
  bool get isRejected => status == 'rejected';

  factory GuruPermit.fromJson(Map<String, dynamic> json) => GuruPermit(
    id:             json['id'] as int,
    studentName:    json['student_name'] as String,
    className:      json['class_name'] as String,
    type:           json['type'] as String,
    typeLabel:      json['type_label'] as String,
    startDate:      json['start_date'] as String? ?? '—',
    endDate:        json['end_date'] as String? ?? '—',
    reason:         json['reason'] as String? ?? '',
    status:         json['status'] as String,
    rejectionNote:  json['rejection_note'] as String?,
  );
}

// ─── Forgot Attendance ────────────────────────────────────────────────────────

class GuruForgotAttendance {
  final int id;
  final String studentName;
  final String className;
  final String date;
  final String reason;
  final String status;
  final String? teacherNote;

  const GuruForgotAttendance({
    required this.id,
    required this.studentName,
    required this.className,
    required this.date,
    required this.reason,
    required this.status,
    this.teacherNote,
  });

  bool get isPending  => status == 'pending';
  bool get isApproved => status == 'approved';
  bool get isRejected => status == 'rejected';

  factory GuruForgotAttendance.fromJson(Map<String, dynamic> json) => GuruForgotAttendance(
    id:          json['id'] as int,
    studentName: json['student_name'] as String,
    className:   json['class_name'] as String,
    date:        json['date'] as String,
    reason:      json['reason'] as String? ?? '',
    status:      json['status'] as String,
    teacherNote: json['teacher_note'] as String?,
  );
}

// ─── Early Checkout ───────────────────────────────────────────────────────────

class GuruEarlyCheckout {
  final int id;
  final String studentName;
  final String className;
  final String date;
  final String requestedTime;
  final String reason;
  final String status;
  final String? reviewerNote;

  const GuruEarlyCheckout({
    required this.id,
    required this.studentName,
    required this.className,
    required this.date,
    required this.requestedTime,
    required this.reason,
    required this.status,
    this.reviewerNote,
  });

  bool get isPending  => status == 'pending';
  bool get isApproved => status == 'approved';
  bool get isRejected => status == 'rejected';

  factory GuruEarlyCheckout.fromJson(Map<String, dynamic> json) => GuruEarlyCheckout(
    id:            json['id'] as int,
    studentName:   json['student_name'] as String,
    className:     json['class_name'] as String,
    date:          json['date'] as String,
    requestedTime: json['requested_time'] as String? ?? '',
    reason:        json['reason'] as String? ?? '',
    status:        json['status'] as String,
    reviewerNote:  json['reviewer_note'] as String?,
  );
}

// ─── Conduct ─────────────────────────────────────────────────────────────────

class GuruConductStudent {
  final int id;
  final String name;
  final String? nis;
  final int prestasiCount;
  final int pelanggaranCount;

  const GuruConductStudent({
    required this.id,
    required this.name,
    this.nis,
    required this.prestasiCount,
    required this.pelanggaranCount,
  });

  factory GuruConductStudent.fromJson(Map<String, dynamic> json) => GuruConductStudent(
    id:               json['id'] as int,
    name:             json['name'] as String,
    nis:              json['nis'] as String?,
    prestasiCount:    json['prestasi_count'] as int? ?? 0,
    pelanggaranCount: json['pelanggaran_count'] as int? ?? 0,
  );
}

// ─── Paginated wrapper ────────────────────────────────────────────────────────

class PaginatedMeta {
  final int currentPage;
  final int lastPage;
  final int total;

  const PaginatedMeta({
    required this.currentPage,
    required this.lastPage,
    required this.total,
  });

  bool get hasMore => currentPage < lastPage;

  factory PaginatedMeta.fromJson(Map<String, dynamic> json) => PaginatedMeta(
    currentPage: json['current_page'] as int? ?? 1,
    lastPage:    json['last_page'] as int? ?? 1,
    total:       json['total'] as int? ?? 0,
  );
}
