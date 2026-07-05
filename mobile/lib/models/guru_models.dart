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

// ─── Conduct API ─────────────────────────────────────────────────────────────

class ConductCategory {
  final int    id;
  final String name;
  final String type;
  final String context;

  const ConductCategory({
    required this.id,
    required this.name,
    required this.type,
    required this.context,
  });

  factory ConductCategory.fromJson(Map<String, dynamic> json) => ConductCategory(
    id:      json['id'] as int,
    name:    json['name'] as String,
    type:    json['type'] as String,
    context: json['context'] as String? ?? '',
  );
}

class SimpleStudent {
  final int    id;
  final String name;
  final String? nis;
  final String? className;

  const SimpleStudent({required this.id, required this.name, this.nis, this.className});

  factory SimpleStudent.fromJson(Map<String, dynamic> json) => SimpleStudent(
    id:        json['id'] as int,
    name:      json['name'] as String,
    nis:       json['nis'] as String?,
    className: json['class_name'] as String?,
  );
}

class ConductHistoryItem {
  final int     id;
  final String  type;            // 'pelanggaran' | 'prestasi'
  final int     studentId;
  final String  studentName;
  final String? studentNis;
  final String  className;
  // Pelanggaran
  final String? description;
  final String? severity;
  // Prestasi perilaku
  final String? categoryName;
  // Prestasi lomba
  final String? prestasiType;    // 'perilaku' | 'lomba'
  final String? lombaName;
  final String? lombaLevel;
  final String? lombaLevelLabel;
  final String? lombaRank;
  final String? lombaRankLabel;
  // Shared
  final String? note;
  final String  date;
  final String  dateLabel;

  const ConductHistoryItem({
    required this.id,
    required this.type,
    required this.studentId,
    required this.studentName,
    this.studentNis,
    required this.className,
    this.description,
    this.severity,
    this.categoryName,
    this.prestasiType,
    this.lombaName,
    this.lombaLevel,
    this.lombaLevelLabel,
    this.lombaRank,
    this.lombaRankLabel,
    this.note,
    required this.date,
    required this.dateLabel,
  });

  factory ConductHistoryItem.fromJson(Map<String, dynamic> json) => ConductHistoryItem(
    id:              json['id']                as int,
    type:            json['type']              as String,
    studentId:       json['student_id']        as int,
    studentName:     json['student_name']      as String,
    studentNis:      json['student_nis']       as String?,
    className:       json['class_name']        as String,
    description:     json['description']       as String?,
    severity:        json['severity']          as String?,
    categoryName:    json['category_name']     as String?,
    prestasiType:    json['prestasi_type']     as String?,
    lombaName:       json['lomba_name']        as String?,
    lombaLevel:      json['lomba_level']       as String?,
    lombaLevelLabel: json['lomba_level_label'] as String?,
    lombaRank:       json['lomba_rank']        as String?,
    lombaRankLabel:  json['lomba_rank_label']  as String?,
    note:            json['note']              as String?,
    date:            json['date']              as String,
    dateLabel:       json['date_label']        as String,
  );
}

// ─── Teaching Session ─────────────────────────────────────────────────────────

class TeachingSession {
  final int     id;
  final int     classId;
  final String  className;
  final int?    subjectId;
  final String  subjectName;
  final String  date;
  final int?    period;
  final String? startTime;
  final String? endTime;
  final int     total;
  final int     hadir;
  final int     alpha;
  final List<SessionStudentRow> students;

  const TeachingSession({
    required this.id,
    required this.classId,
    required this.className,
    this.subjectId,
    required this.subjectName,
    required this.date,
    this.period,
    this.startTime,
    this.endTime,
    required this.total,
    required this.hadir,
    required this.alpha,
    this.students = const [],
  });

  factory TeachingSession.fromJson(Map<String, dynamic> json) => TeachingSession(
    id:          json['id'] as int,
    classId:     json['class_id'] as int,
    className:   json['class_name'] as String? ?? '—',
    subjectId:   json['subject_id'] as int?,
    subjectName: json['subject_name'] as String? ?? '—',
    date:        json['date'] as String,
    period:      json['period'] as int?,
    startTime:   json['start_time'] as String?,
    endTime:     json['end_time'] as String?,
    total:       json['total'] as int? ?? 0,
    hadir:       json['hadir'] as int? ?? 0,
    alpha:       json['alpha'] as int? ?? 0,
    students:    (json['students'] as List<dynamic>? ?? [])
        .map((e) => SessionStudentRow.fromJson(e as Map<String, dynamic>))
        .toList(),
  );
}

class SessionStudentRow {
  final int    studentId;
  final String name;
  final String? nis;
  String status;
  String? note;

  SessionStudentRow({
    required this.studentId,
    required this.name,
    this.nis,
    required this.status,
    this.note,
  });

  factory SessionStudentRow.fromJson(Map<String, dynamic> json) => SessionStudentRow(
    studentId: json['student_id'] as int,
    name:      json['name'] as String,
    nis:       json['nis'] as String?,
    status:    json['status'] as String? ?? 'hadir',
    note:      json['note'] as String?,
  );
}

// ─── Tujuan Pembelajaran (TP) ─────────────────────────────────────────────────

class TujuanPembelajaran {
  final int     id;
  final int?    subjectId;
  final String? subjectName;
  final String? code;
  final String  description;

  const TujuanPembelajaran({
    required this.id,
    this.subjectId,
    this.subjectName,
    this.code,
    required this.description,
  });

  factory TujuanPembelajaran.fromJson(Map<String, dynamic> json) => TujuanPembelajaran(
    id:          json['id'] as int,
    subjectId:   json['subject_id'] as int?,
    subjectName: json['subject_name'] as String?,
    code:        json['code'] as String?,
    description: json['description'] as String,
  );

  String get displayLabel => code != null && code!.isNotEmpty
      ? '[$code] $description'
      : description;
}

// ─── Teacher Journal ──────────────────────────────────────────────────────────

class TeacherJournal {
  final int    id;
  final int    classId;
  final String className;
  final int?   subjectId;
  final String subjectName;
  final String date;
  final int?   period;
  final int?   periodEnd;
  final int?   tpId;
  final String? tpCode;
  final String? tpDescription;
  final String? learningObjectives;
  final String material;
  final String activity;
  final String? notes;
  final int    absencesCount;
  final List<JournalAbsentStudent> absentStudents;

  const TeacherJournal({
    required this.id,
    required this.classId,
    required this.className,
    this.subjectId,
    required this.subjectName,
    required this.date,
    this.period,
    this.periodEnd,
    this.tpId,
    this.tpCode,
    this.tpDescription,
    this.learningObjectives,
    required this.material,
    required this.activity,
    this.notes,
    required this.absencesCount,
    this.absentStudents = const [],
  });

  String get tpDisplay {
    if (tpCode != null && tpCode!.isNotEmpty) return '[$tpCode] ${tpDescription ?? ''}';
    if (tpDescription != null) return tpDescription!;
    return learningObjectives ?? '—';
  }

  String get periodLabel {
    if (period == null) return '';
    if (periodEnd != null && periodEnd! > period!) return 'Jam $period–$periodEnd';
    return 'Jam $period';
  }

  factory TeacherJournal.fromJson(Map<String, dynamic> json) => TeacherJournal(
    id:                  json['id'] as int,
    classId:             json['class_id'] as int,
    className:           json['class_name'] as String? ?? '—',
    subjectId:           json['subject_id'] as int?,
    subjectName:         json['subject_name'] as String? ?? '—',
    date:                json['date'] as String,
    period:              json['period'] as int?,
    periodEnd:           json['period_end'] as int?,
    tpId:                json['tp_id'] as int?,
    tpCode:              json['tp_code'] as String?,
    tpDescription:       json['tp_description'] as String?,
    learningObjectives:  json['learning_objectives'] as String?,
    material:            json['material'] as String,
    activity:            json['activity'] as String,
    notes:               json['notes'] as String?,
    absencesCount:       json['absences_count'] as int? ?? 0,
    absentStudents:      (json['absent_students'] as List<dynamic>? ?? [])
        .map((e) => JournalAbsentStudent.fromJson(e as Map<String, dynamic>))
        .toList(),
  );
}

class JournalAbsentStudent {
  final int    studentId;
  final String name;
  final String? nis;
  String status;

  JournalAbsentStudent({
    required this.studentId,
    required this.name,
    this.nis,
    required this.status,
  });

  factory JournalAbsentStudent.fromJson(Map<String, dynamic> json) => JournalAbsentStudent(
    studentId: json['student_id'] as int,
    name:      json['name'] as String,
    nis:       json['nis'] as String?,
    status:    json['status'] as String? ?? 'tidak_hadir',
  );
}

// ─── Input Nilai ─────────────────────────────────────────────────────────────

class SubjectItem {
  final int    id;
  final String name;

  const SubjectItem({required this.id, required this.name});

  factory SubjectItem.fromJson(Map<String, dynamic> json) => SubjectItem(
    id:   json['id'] as int,
    name: json['name'] as String,
  );
}

class StudentGradeRow {
  final int     studentId;
  final String  studentName;
  final String? nis;
  final List<GradeCell> grades;

  const StudentGradeRow({
    required this.studentId,
    required this.studentName,
    this.nis,
    required this.grades,
  });

  factory StudentGradeRow.fromJson(Map<String, dynamic> json) => StudentGradeRow(
    studentId:   json['id'] as int,
    studentName: json['name'] as String,
    nis:         json['nis'] as String?,
    grades:      (json['grades'] as List<dynamic>? ?? [])
        .map((e) => GradeCell.fromJson(e as Map<String, dynamic>))
        .toList(),
  );

  GradeCell? gradeFor(int subjectId, String type) => grades.cast<GradeCell?>().firstWhere(
    (g) => g?.subjectId == subjectId && g?.type == type,
    orElse: () => null,
  );
}

class GradeCell {
  final int?    gradeId;
  final int     subjectId;
  final String  subjectName;
  final String  type;
  final double? score;
  final String? notes;

  const GradeCell({
    this.gradeId,
    required this.subjectId,
    required this.subjectName,
    required this.type,
    this.score,
    this.notes,
  });

  factory GradeCell.fromJson(Map<String, dynamic> json) => GradeCell(
    gradeId:     json['grade_id'] as int?,
    subjectId:   json['subject_id'] as int,
    subjectName: json['subject_name'] as String,
    type:        json['type'] as String,
    score:       (json['score'] as num?)?.toDouble(),
    notes:       json['notes'] as String?,
  );
}

// ─── BK (Bimbingan Konseling) ─────────────────────────────────────────────────

class BkLogItem {
  final int    id;
  final int    studentId;
  final String studentName;
  final String? studentNis;
  final String? className;
  final String coachingNote;
  final String date;
  final String? counselorName;
  final bool   isAuto;

  const BkLogItem({
    required this.id,
    required this.studentId,
    required this.studentName,
    this.studentNis,
    this.className,
    required this.coachingNote,
    required this.date,
    this.counselorName,
    required this.isAuto,
  });

  factory BkLogItem.fromJson(Map<String, dynamic> json) => BkLogItem(
    id:            json['id'] as int,
    studentId:     json['student_id'] as int,
    studentName:   json['student_name'] as String,
    studentNis:    json['student_nis'] as String?,
    className:     json['class_name'] as String?,
    coachingNote:  json['coaching_note'] as String,
    date:          json['date'] as String,
    counselorName: json['counselor_name'] as String?,
    isAuto:        json['is_auto'] as bool? ?? false,
  );
}

// ─── Sarpras ──────────────────────────────────────────────────────────────────

class SarprasStats {
  final int totalAssets;
  final int baik;
  final int rusakRingan;
  final int rusakBerat;
  final int pendingDamage;
  final int pendingLoans;
  final int activeLoans;
  final int myLoans;

  const SarprasStats({
    required this.totalAssets,
    required this.baik,
    required this.rusakRingan,
    required this.rusakBerat,
    required this.pendingDamage,
    required this.pendingLoans,
    required this.activeLoans,
    required this.myLoans,
  });

  factory SarprasStats.fromJson(Map<String, dynamic> json) => SarprasStats(
    totalAssets:  json['total_assets'] as int? ?? 0,
    baik:         json['baik'] as int? ?? 0,
    rusakRingan:  json['rusak_ringan'] as int? ?? 0,
    rusakBerat:   json['rusak_berat'] as int? ?? 0,
    pendingDamage:json['pending_damage'] as int? ?? 0,
    pendingLoans: json['pending_loans'] as int? ?? 0,
    activeLoans:  json['active_loans'] as int? ?? 0,
    myLoans:      json['my_loans'] as int? ?? 0,
  );
}

class AssetItem {
  final int    id;
  final String name;
  final String category;
  final String categoryLabel;
  final String condition;
  final String conditionLabel;
  final String? roomName;
  final int    quantity;
  final int?   purchaseYear;
  final String? description;

  const AssetItem({
    required this.id,
    required this.name,
    required this.category,
    required this.categoryLabel,
    required this.condition,
    required this.conditionLabel,
    this.roomName,
    required this.quantity,
    this.purchaseYear,
    this.description,
  });

  factory AssetItem.fromJson(Map<String, dynamic> json) => AssetItem(
    id:             json['id'] as int,
    name:           json['name'] as String,
    category:       json['category'] as String,
    categoryLabel:  json['category_label'] as String? ?? json['category'] as String,
    condition:      json['condition'] as String,
    conditionLabel: json['condition_label'] as String? ?? json['condition'] as String,
    roomName:       json['room_name'] as String?,
    quantity:       json['quantity'] as int? ?? 1,
    purchaseYear:   json['purchase_year'] as int?,
    description:    json['description'] as String?,
  );
}

class DamageReportItem {
  final int    id;
  final int    assetId;
  final String assetName;
  final String? assetCategory;
  final String? reporterName;
  final String? handlerName;
  final String description;
  final String status;
  final String statusLabel;
  final String? resolutionNote;
  final int    daysOpen;
  final String createdAt;

  const DamageReportItem({
    required this.id,
    required this.assetId,
    required this.assetName,
    this.assetCategory,
    this.reporterName,
    this.handlerName,
    required this.description,
    required this.status,
    required this.statusLabel,
    this.resolutionNote,
    required this.daysOpen,
    required this.createdAt,
  });

  factory DamageReportItem.fromJson(Map<String, dynamic> json) => DamageReportItem(
    id:              json['id'] as int,
    assetId:         json['asset_id'] as int,
    assetName:       json['asset_name'] as String? ?? '—',
    assetCategory:   json['asset_category'] as String?,
    reporterName:    json['reporter_name'] as String?,
    handlerName:     json['handler_name'] as String?,
    description:     json['description'] as String,
    status:          json['status'] as String,
    statusLabel:     json['status_label'] as String? ?? json['status'] as String,
    resolutionNote:  json['resolution_note'] as String?,
    daysOpen:        json['days_open'] as int? ?? 0,
    createdAt:       json['created_at'] as String? ?? '',
  );
}

class LoanItem {
  final int    id;
  final int    assetId;
  final String assetName;
  final String? assetCategory;
  final String? borrowerName;
  final String? approverName;
  final String purpose;
  final String startDate;
  final String endDate;
  final String status;
  final String statusLabel;
  final String? rejectionNote;
  final String createdAt;

  const LoanItem({
    required this.id,
    required this.assetId,
    required this.assetName,
    this.assetCategory,
    this.borrowerName,
    this.approverName,
    required this.purpose,
    required this.startDate,
    required this.endDate,
    required this.status,
    required this.statusLabel,
    this.rejectionNote,
    required this.createdAt,
  });

  factory LoanItem.fromJson(Map<String, dynamic> json) => LoanItem(
    id:            json['id'] as int,
    assetId:       json['asset_id'] as int,
    assetName:     json['asset_name'] as String? ?? '—',
    assetCategory: json['asset_category'] as String?,
    borrowerName:  json['borrower_name'] as String?,
    approverName:  json['approver_name'] as String?,
    purpose:       json['purpose'] as String,
    startDate:     json['start_date'] as String? ?? '',
    endDate:       json['end_date'] as String? ?? '',
    status:        json['status'] as String,
    statusLabel:   json['status_label'] as String? ?? json['status'] as String,
    rejectionNote: json['rejection_note'] as String?,
    createdAt:     json['created_at'] as String? ?? '',
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
