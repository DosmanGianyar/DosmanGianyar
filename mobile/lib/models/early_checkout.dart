import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

class EarlyCheckout {
  final int     id;
  final String  date;
  final String  requestedTime;
  final String  type;            // 'dengan_absen' or 'tanpa_absen'
  final String? absenceCategory; // 'izin' or 'sakit'
  final String  typeLabel;
  final String  reason;
  final String  status;
  final String  statusLabel;
  final String? reviewerNote;
  final DateTime createdAt;

  const EarlyCheckout({
    required this.id,
    required this.date,
    required this.requestedTime,
    this.type = 'dengan_absen',
    this.absenceCategory,
    this.typeLabel = 'Dengan Absen Pulang',
    required this.reason,
    required this.status,
    required this.statusLabel,
    this.reviewerNote,
    required this.createdAt,
  });

  factory EarlyCheckout.fromJson(Map<String, dynamic> json) => EarlyCheckout(
    id:              json['id']               as int,
    date:            json['date']             as String,
    requestedTime:   json['requested_time']   as String? ?? '',
    type:            json['type']             as String? ?? 'dengan_absen',
    absenceCategory: json['absence_category'] as String?,
    typeLabel:       json['type_label']       as String? ?? 'Dengan Absen Pulang',
    reason:          json['reason']           as String,
    status:          json['status']           as String,
    statusLabel:     json['status_label']     as String,
    reviewerNote:    json['reviewer_note']    as String?,
    createdAt:       DateTime.parse(json['created_at'] as String),
  );

  bool get isPending => status == 'pending';
  bool get isDenganAbsen => type == 'dengan_absen';
  bool get isTanpaAbsen => type == 'tanpa_absen';

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
