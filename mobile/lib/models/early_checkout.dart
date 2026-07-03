import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

class EarlyCheckout {
  final int     id;
  final String  date;
  final String  requestedTime;
  final String  reason;
  final String  status;
  final String  statusLabel;
  final String? reviewerNote;
  final DateTime createdAt;

  const EarlyCheckout({
    required this.id,
    required this.date,
    required this.requestedTime,
    required this.reason,
    required this.status,
    required this.statusLabel,
    this.reviewerNote,
    required this.createdAt,
  });

  factory EarlyCheckout.fromJson(Map<String, dynamic> json) => EarlyCheckout(
    id:            json['id']             as int,
    date:          json['date']           as String,
    requestedTime: json['requested_time'] as String,
    reason:        json['reason']         as String,
    status:        json['status']         as String,
    statusLabel:   json['status_label']   as String,
    reviewerNote:  json['reviewer_note']  as String?,
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
}
