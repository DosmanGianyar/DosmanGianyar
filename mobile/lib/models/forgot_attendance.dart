import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

class ForgotAttendance {
  final int     id;
  final String  date;
  final String  reason;
  final String  status;
  final String  statusLabel;
  final String? teacherNote;
  final DateTime createdAt;

  const ForgotAttendance({
    required this.id,
    required this.date,
    required this.reason,
    required this.status,
    required this.statusLabel,
    this.teacherNote,
    required this.createdAt,
  });

  factory ForgotAttendance.fromJson(Map<String, dynamic> json) => ForgotAttendance(
    id:          json['id']           as int,
    date:        json['date']         as String,
    reason:      json['reason']       as String,
    status:      json['status']       as String,
    statusLabel: json['status_label'] as String,
    teacherNote: json['teacher_note'] as String?,
    createdAt:   DateTime.parse(json['created_at'] as String),
  );

  bool get isPending => status == 'pending';

  Color get statusColor => switch (status) {
    'approved' => AppColors.green500,
    'rejected' => AppColors.red500,
    _          => AppColors.amber500,
  };

  Color get statusBg => switch (status) {
    'approved' => AppColors.green100,
    'rejected' => AppColors.red100,
    _          => AppColors.amber100,
  };
}
