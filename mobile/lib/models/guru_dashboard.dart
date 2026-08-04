class GuruAlert {
  final String name;
  final String schoolClass;
  final int pelanggaranCount;

  const GuruAlert({
    required this.name,
    required this.schoolClass,
    required this.pelanggaranCount,
  });

  factory GuruAlert.fromJson(Map<String, dynamic> json) => GuruAlert(
    name:             json['name'] as String,
    schoolClass:      json['class'] as String? ?? '—',
    pelanggaranCount: json['pelanggaran_count'] as int? ?? 0,
  );
}

class JournalItem {
  final int id;
  final String dateFormatted;
  final String className;
  final String subjectName;
  final String period;
  final String material;
  final String activity;
  final String? notes;
  final String? tpCode;
  final String? tpDescription;
  final List<Map<String, String>> absentStudents;

  const JournalItem({
    required this.id,
    required this.dateFormatted,
    required this.className,
    required this.subjectName,
    required this.period,
    required this.material,
    required this.activity,
    this.notes,
    this.tpCode,
    this.tpDescription,
    required this.absentStudents,
  });

  factory JournalItem.fromJson(Map<String, dynamic> json) => JournalItem(
    id: json['id'] as int,
    dateFormatted: json['date_formatted'] as String? ?? '—',
    className: json['class_name'] as String? ?? '—',
    subjectName: json['subject_name'] as String? ?? '—',
    period: json['period']?.toString() ?? '1',
    material: json['material'] as String? ?? '',
    activity: json['activity'] as String? ?? '',
    notes: json['notes'] as String?,
    tpCode: json['tp_code'] as String?,
    tpDescription: json['tp_description'] as String?,
    absentStudents: (json['absent_students'] as List<dynamic>? ?? [])
        .map((e) => <String, String>{
          'student_name': (e['student_name'] as String? ?? '—'),
          'status': (e['status'] as String? ?? 'alpa'),
        })
        .toList(),
  );
}

class WeeklyJournalGroup {
  final String weekRange;
  final int count;
  final List<JournalItem> journals;

  const WeeklyJournalGroup({
    required this.weekRange,
    required this.count,
    required this.journals,
  });

  factory WeeklyJournalGroup.fromJson(Map<String, dynamic> json) => WeeklyJournalGroup(
    weekRange: json['week_range'] as String? ?? '',
    count: json['count'] as int? ?? 0,
    journals: (json['journals'] as List<dynamic>? ?? [])
        .map((e) => JournalItem.fromJson(e as Map<String, dynamic>))
        .toList(),
  );
}

class GuruMyExtracurricular {
  final int id;
  final String name;
  final int membersCount;
  final String? contactPerson;

  const GuruMyExtracurricular({
    required this.id,
    required this.name,
    required this.membersCount,
    this.contactPerson,
  });

  factory GuruMyExtracurricular.fromJson(Map<String, dynamic> json) => GuruMyExtracurricular(
    id: json['id'] as int,
    name: json['name'] as String,
    membersCount: json['members_count'] as int? ?? 0,
    contactPerson: json['contact_person'] as String?,
  );
}

class GuruDashboard {
  final bool isHomeroom;
  final int? homeroomClassId;
  final String? homeroomClassName;
  final List<String> mySubjects;
  final int totalStudents;
  final int pendingPermits;
  final int pendingEarlyCheckouts;
  final int pendingForgotAttendances;
  final List<GuruAlert> recentAlerts;
  final List<WeeklyJournalGroup> weeklyJournals;
  final List<GuruMyExtracurricular> myExtracurriculars;

  const GuruDashboard({
    this.isHomeroom = false,
    this.homeroomClassId,
    this.homeroomClassName,
    this.mySubjects = const [],
    required this.totalStudents,
    required this.pendingPermits,
    required this.pendingEarlyCheckouts,
    required this.pendingForgotAttendances,
    required this.recentAlerts,
    required this.weeklyJournals,
    required this.myExtracurriculars,
  });

  int get totalPending => pendingPermits + pendingEarlyCheckouts + pendingForgotAttendances;

  factory GuruDashboard.fromJson(Map<String, dynamic> json) => GuruDashboard(
    isHomeroom:                json['is_homeroom'] as bool? ?? ((json['total_students'] as int? ?? 0) > 0),
    homeroomClassId:           json['homeroom_class_id'] as int?,
    homeroomClassName:         json['homeroom_class_name'] as String?,
    mySubjects:                (json['my_subjects'] as List<dynamic>? ?? []).map((e) => e.toString()).toList(),
    totalStudents:             json['total_students'] as int? ?? 0,
    pendingPermits:            json['pending_permits'] as int? ?? 0,
    pendingEarlyCheckouts:     json['pending_early_checkouts'] as int? ?? 0,
    pendingForgotAttendances:  json['pending_forgot_attendances'] as int? ?? 0,
    recentAlerts: (json['recent_alerts'] as List<dynamic>? ?? [])
        .map((e) => GuruAlert.fromJson(e as Map<String, dynamic>))
        .toList(),
    weeklyJournals: (json['weekly_journals'] as List<dynamic>? ?? [])
        .map((e) => WeeklyJournalGroup.fromJson(e as Map<String, dynamic>))
        .toList(),
    myExtracurriculars: (json['my_extracurriculars'] as List<dynamic>? ?? [])
        .map((e) => GuruMyExtracurricular.fromJson(e as Map<String, dynamic>))
        .toList(),
  );
}
