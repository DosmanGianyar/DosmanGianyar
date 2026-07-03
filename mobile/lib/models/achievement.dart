import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

class AchievementCategory {
  final int    id;
  final String name;
  const AchievementCategory({required this.id, required this.name});
  factory AchievementCategory.fromJson(Map<String, dynamic> j) =>
      AchievementCategory(id: j['id'] as int, name: j['name'] as String);
}

class AchievementStats {
  final int pending, approved, rejected;
  const AchievementStats({required this.pending, required this.approved, required this.rejected});
  factory AchievementStats.fromJson(Map<String, dynamic> j) => AchievementStats(
    pending:  j['pending']  as int,
    approved: j['approved'] as int,
    rejected: j['rejected'] as int,
  );
}

class Achievement {
  final int    id;
  final String title;
  final String level;
  final String levelLabel;
  final String status;
  final String statusLabel;
  final String achievementDate;
  final String createdAt;
  final String? categoryName;
  final String? rank;
  final String? description;
  final String? rejectionReason;
  final String? photoUrl;
  final String? certificateUrl;

  const Achievement({
    required this.id,
    required this.title,
    required this.level,
    required this.levelLabel,
    required this.status,
    required this.statusLabel,
    required this.achievementDate,
    required this.createdAt,
    this.categoryName,
    this.rank,
    this.description,
    this.rejectionReason,
    this.photoUrl,
    this.certificateUrl,
  });

  factory Achievement.fromJson(Map<String, dynamic> j) => Achievement(
    id:              j['id']               as int,
    title:           j['title']            as String,
    level:           j['level']            as String,
    levelLabel:      j['level_label']      as String,
    status:          j['status']           as String,
    statusLabel:     j['status_label']     as String,
    achievementDate: j['achievement_date'] as String,
    createdAt:       j['created_at']       as String,
    categoryName:    j['category_name']    as String?,
    rank:            j['rank']             as String?,
    description:     j['description']      as String?,
    rejectionReason: j['rejection_reason'] as String?,
    photoUrl:        j['photo_url']        as String?,
    certificateUrl:  j['certificate_url']  as String?,
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

  Color get levelColor => switch (level) {
    'sekolah'       => AppColors.gray500,
    'kabupaten'     => AppColors.blue600,
    'provinsi'      => AppColors.amber500,
    'nasional'      => AppColors.green500,
    'internasional' => AppColors.red500,
    _               => AppColors.gray500,
  };

  Color get levelBg => switch (level) {
    'sekolah'       => AppColors.gray100,
    'kabupaten'     => AppColors.blue50,
    'provinsi'      => AppColors.amber100,
    'nasional'      => AppColors.green100,
    'internasional' => AppColors.red100,
    _               => AppColors.gray100,
  };
}
