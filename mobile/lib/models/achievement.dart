import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

class AchievementCategory {
  final int    id;
  final String name;
  const AchievementCategory({required this.id, required this.name});
  factory AchievementCategory.fromJson(Map<String, dynamic> j) =>
      AchievementCategory(id: j['id'] as int, name: j['name'] as String);
}

class AchievementPembina {
  final int     id;
  final String  name;
  final String? nip;
  final String? subject;
  final String? extracurricularName;
  final String  label;

  const AchievementPembina({
    required this.id,
    required this.name,
    this.nip,
    this.subject,
    this.extracurricularName,
    required this.label,
  });

  factory AchievementPembina.fromJson(Map<String, dynamic> j) => AchievementPembina(
    id:                  j['id']   as int,
    name:                j['name'] as String,
    nip:                 j['nip']  as String?,
    subject:             j['subject'] as String?,
    extracurricularName: j['extracurricular_name'] as String?,
    label:               (j['label'] ?? j['name']) as String,
  );
}

class AchievementExtracurricular {
  final int     id;
  final String  name;
  final int?    pembinaId;
  final String? pembinaNames;

  const AchievementExtracurricular({
    required this.id,
    required this.name,
    this.pembinaId,
    this.pembinaNames,
  });

  factory AchievementExtracurricular.fromJson(Map<String, dynamic> j) => AchievementExtracurricular(
    id:           j['id']   as int,
    name:         j['name'] as String,
    pembinaId:    j['pembina_id'] as int?,
    pembinaNames: j['pembina_names'] as String?,
  );
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
  final int     id;
  final String  title;
  final String? eventName;
  final String? organizer;
  final String? fieldCategory;
  final String? fieldCategoryLabel;
  final String? participationType;
  final String? participationTypeLabel;
  final String  level;
  final String  levelLabel;
  final String  status;
  final String  statusLabel;
  final String  curationStatus;
  final String  curationStatusLabel;
  final String? curationNote;
  final String  achievementDate;
  final String  createdAt;
  final String? categoryName;
  final int?    pembinaId;
  final String? pembinaName;
  final int?    extracurricularId;
  final String? extracurricularName;
  final String? rank;
  final String? description;
  final String? eventUrl;
  final String? rejectionReason;
  final String? photoUrl;
  final String? certificateUrl;
  final String? assignmentLetterUrl;

  const Achievement({
    required this.id,
    required this.title,
    this.eventName,
    this.organizer,
    this.fieldCategory,
    this.fieldCategoryLabel,
    this.participationType,
    this.participationTypeLabel,
    required this.level,
    required this.levelLabel,
    required this.status,
    required this.statusLabel,
    required this.curationStatus,
    required this.curationStatusLabel,
    this.curationNote,
    required this.achievementDate,
    required this.createdAt,
    this.categoryName,
    this.pembinaId,
    this.pembinaName,
    this.extracurricularId,
    this.extracurricularName,
    this.rank,
    this.description,
    this.eventUrl,
    this.rejectionReason,
    this.photoUrl,
    this.certificateUrl,
    this.assignmentLetterUrl,
  });

  factory Achievement.fromJson(Map<String, dynamic> j) => Achievement(
    id:                     j['id']                     as int,
    title:                  j['title']                  as String,
    eventName:              j['event_name']             as String?,
    organizer:              j['organizer']              as String?,
    fieldCategory:          j['field_category']         as String?,
    fieldCategoryLabel:     j['field_category_label']   as String?,
    participationType:      j['participation_type']     as String?,
    participationTypeLabel: j['participation_type_label'] as String?,
    level:                  j['level']                  as String,
    levelLabel:             j['level_label']            as String,
    status:                 j['status']                 as String,
    statusLabel:            j['status_label']           as String,
    curationStatus:         (j['curation_status']       ?? 'pending') as String,
    curationStatusLabel:    (j['curation_status_label'] ?? 'Menunggu Kurasi') as String,
    curationNote:           j['curation_note']          as String?,
    achievementDate:        j['achievement_date']       as String,
    createdAt:              j['created_at']             as String,
    categoryName:           j['category_name']          as String?,
    pembinaId:              j['pembina_id']             as int?,
    pembinaName:            j['pembina_name']           as String?,
    extracurricularId:      j['extracurricular_id']     as int?,
    extracurricularName:    j['extracurricular_name']   as String?,
    rank:                   j['rank']                   as String?,
    description:            j['description']            as String?,
    eventUrl:               j['event_url']              as String?,
    rejectionReason:        j['rejection_reason']       as String?,
    photoUrl:               j['photo_url']              as String?,
    certificateUrl:         j['certificate_url']        as String?,
    assignmentLetterUrl:    j['assignment_letter_url']  as String?,
  );

  bool get isPending  => curationStatus == 'pending' || status == 'pending';
  bool get isCurated  => curationStatus == 'curated';
  bool get isRevision => curationStatus == 'revision';
  bool get isRejected => curationStatus == 'rejected' || status == 'rejected';

  Color get statusColor => switch (curationStatus) {
    'curated'  => AppColors.emerald600,
    'revision' => AppColors.amber500,
    'rejected' => AppColors.red500,
    _          => AppColors.blue600,
  };

  Color get statusBg => switch (curationStatus) {
    'curated'  => AppColors.emerald50,
    'revision' => AppColors.amber50,
    'rejected' => AppColors.red50,
    _          => AppColors.blue50,
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
