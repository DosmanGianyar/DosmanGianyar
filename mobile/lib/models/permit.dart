import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

class Permit {
  final int     id;
  final String  type;
  final String  typeLabel;
  final String  startDate;
  final String  endDate;
  final String  reason;
  final String  status;
  final String  statusLabel;
  final String? rejectionNote;
  final String? fileUrl;
  final DateTime createdAt;

  const Permit({
    required this.id,
    required this.type,
    required this.typeLabel,
    required this.startDate,
    required this.endDate,
    required this.reason,
    required this.status,
    required this.statusLabel,
    this.rejectionNote,
    this.fileUrl,
    required this.createdAt,
  });

  factory Permit.fromJson(Map<String, dynamic> json) => Permit(
    id:            json['id']             as int,
    type:          json['type']           as String,
    typeLabel:     json['type_label']     as String,
    startDate:     json['start_date']     as String,
    endDate:       json['end_date']       as String,
    reason:        json['reason']         as String,
    status:        json['status']         as String,
    statusLabel:   json['status_label']   as String,
    rejectionNote: json['rejection_note'] as String?,
    fileUrl:       json['file_url']       as String?,
    createdAt:     DateTime.parse(json['created_at'] as String),
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

  Color get typeColor => switch (type) {
    'sakit'      => AppColors.purple500,
    'dispensasi' => AppColors.orange500,
    _            => AppColors.sky500,
  };

  Color get typeBg => switch (type) {
    'sakit'      => const Color(0xFFF3E8FF),
    'dispensasi' => AppColors.orange100,
    _            => AppColors.sky100,
  };
}
