import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

class HomeroomConsultation {
  final int    id;
  final String topic;
  final String status;
  final String statusLabel;
  final String createdAt;
  final String? studentNote;
  final String? teacherName;
  final String? scheduledDate;
  final String? conductedDate;
  final String? teacherNote;
  final String? followUp;
  final String? cancelledReason;

  const HomeroomConsultation({
    required this.id,
    required this.topic,
    required this.status,
    required this.statusLabel,
    required this.createdAt,
    this.studentNote,
    this.teacherName,
    this.scheduledDate,
    this.conductedDate,
    this.teacherNote,
    this.followUp,
    this.cancelledReason,
  });

  factory HomeroomConsultation.fromJson(Map<String, dynamic> j) =>
      HomeroomConsultation(
        id:              j['id']               as int,
        topic:           j['topic']            as String,
        status:          j['status']           as String,
        statusLabel:     j['status_label']     as String,
        createdAt:       j['created_at']       as String,
        studentNote:     j['student_note']     as String?,
        teacherName:     j['teacher_name']     as String?,
        scheduledDate:   j['scheduled_date']   as String?,
        conductedDate:   j['conducted_date']   as String?,
        teacherNote:     j['teacher_note']     as String?,
        followUp:        j['follow_up']        as String?,
        cancelledReason: j['cancelled_reason'] as String?,
      );

  bool get isPending    => status == 'pending';
  bool get isScheduled  => status == 'scheduled';
  bool get isCompleted  => status == 'completed';
  bool get isCancelled  => status == 'cancelled';
  bool get canCancel    => isPending;

  Color get statusColor => switch (status) {
    'pending'   => AppColors.amber500,
    'scheduled' => AppColors.blue600,
    'completed' => AppColors.green500,
    'cancelled' => AppColors.red500,
    _           => AppColors.gray500,
  };

  Color get statusBg => switch (status) {
    'pending'   => AppColors.amber100,
    'scheduled' => AppColors.blue50,
    'completed' => AppColors.green100,
    'cancelled' => AppColors.red100,
    _           => AppColors.gray100,
  };

  IconData get statusIcon => switch (status) {
    'pending'   => Icons.hourglass_empty_rounded,
    'scheduled' => Icons.calendar_month_rounded,
    'completed' => Icons.check_circle_rounded,
    'cancelled' => Icons.cancel_rounded,
    _           => Icons.help_outline,
  };
}
