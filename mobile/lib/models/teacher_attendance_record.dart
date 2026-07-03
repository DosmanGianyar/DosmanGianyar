import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

class TeacherAttendanceRecord {
  final int     id;
  final int     period;
  final String  status;
  final String  statusLabel;
  final String? teacherName;
  final String? subjectName;
  final String? subjectCode;
  final String? startTime;
  final String? endTime;
  final String? note;

  const TeacherAttendanceRecord({
    required this.id,
    required this.period,
    required this.status,
    required this.statusLabel,
    this.teacherName,
    this.subjectName,
    this.subjectCode,
    this.startTime,
    this.endTime,
    this.note,
  });

  factory TeacherAttendanceRecord.fromJson(Map<String, dynamic> j) =>
      TeacherAttendanceRecord(
        id:          j['id']           as int,
        period:      j['period']       as int,
        status:      j['status']       as String,
        statusLabel: j['status_label'] as String,
        teacherName: j['teacher_name'] as String?,
        subjectName: j['subject_name'] as String?,
        subjectCode: j['subject_code'] as String?,
        startTime:   j['start_time']   as String?,
        endTime:     j['end_time']     as String?,
        note:        j['note']         as String?,
      );

  bool get isHadir => status == 'hadir';

  Color get statusColor => switch (status) {
    'hadir'       => AppColors.green500,
    'tidak_hadir' => AppColors.red500,
    'izin'        => AppColors.blue600,
    'sakit'       => AppColors.amber500,
    _             => AppColors.gray500,
  };

  Color get statusBg => switch (status) {
    'hadir'       => AppColors.green100,
    'tidak_hadir' => AppColors.red100,
    'izin'        => AppColors.blue50,
    'sakit'       => AppColors.amber100,
    _             => AppColors.gray100,
  };

  IconData get statusIcon => switch (status) {
    'hadir'       => Icons.check_circle_rounded,
    'tidak_hadir' => Icons.cancel_rounded,
    'izin'        => Icons.info_rounded,
    'sakit'       => Icons.local_hospital_rounded,
    _             => Icons.help_outline_rounded,
  };
}
