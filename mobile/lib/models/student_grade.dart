import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

class GradeSummarySubject {
  final int        subjectId;
  final String     subjectName;
  final String     subjectCode;
  final List<double> uhScores;
  final double?    uhAvg;
  final double?    uts;
  final double?    uas;
  final double?    finalScore;

  const GradeSummarySubject({
    required this.subjectId,
    required this.subjectName,
    required this.subjectCode,
    required this.uhScores,
    this.uhAvg,
    this.uts,
    this.uas,
    this.finalScore,
  });

  factory GradeSummarySubject.fromJson(Map<String, dynamic> j) {
    return GradeSummarySubject(
      subjectId:   j['subject_id']   as int,
      subjectName: j['subject_name'] as String,
      subjectCode: j['subject_code'] as String,
      uhScores:    (j['uh_scores'] as List)
          .map((e) => (e as num).toDouble())
          .toList(),
      uhAvg:       (j['uh_avg']      as num?)?.toDouble(),
      uts:         (j['uts']         as num?)?.toDouble(),
      uas:         (j['uas']         as num?)?.toDouble(),
      finalScore:  (j['final_score'] as num?)?.toDouble(),
    );
  }

  Color scoreColor(double? score) {
    if (score == null) return AppColors.gray400;
    if (score >= 80)  return AppColors.green600;
    if (score >= 65)  return AppColors.amber500;
    return AppColors.red500;
  }

  Color scoreBg(double? score) {
    if (score == null) return AppColors.gray100;
    if (score >= 80)  return AppColors.green100;
    if (score >= 65)  return AppColors.amber100;
    return AppColors.red100;
  }

  String fmtScore(double? score) =>
      score == null ? '—' : score.toStringAsFixed(score.truncateToDouble() == score ? 0 : 1);
}

class StudentGrade {
  final int    id;
  final String subjectName;
  final String subjectCode;
  final String type;
  final String typeLabel;
  final double score;
  final String? notes;
  final String? recorder;
  final String  createdAt;

  const StudentGrade({
    required this.id,
    required this.subjectName,
    required this.subjectCode,
    required this.type,
    required this.typeLabel,
    required this.score,
    required this.createdAt,
    this.notes,
    this.recorder,
  });

  factory StudentGrade.fromJson(Map<String, dynamic> j) => StudentGrade(
    id:          j['id']           as int,
    subjectName: j['subject_name'] as String,
    subjectCode: j['subject_code'] as String,
    type:        j['type']         as String,
    typeLabel:   j['type_label']   as String,
    score:       (j['score']       as num).toDouble(),
    createdAt:   j['created_at']   as String,
    notes:       j['notes']        as String?,
    recorder:    j['recorder']     as String?,
  );

  Color get scoreColor {
    if (score >= 80) return AppColors.green600;
    if (score >= 65) return AppColors.amber500;
    return AppColors.red500;
  }

  Color get scoreBg {
    if (score >= 80) return AppColors.green100;
    if (score >= 65) return AppColors.amber100;
    return AppColors.red100;
  }

  Color get typeColor => switch (type) {
    'UTS' => AppColors.blue600,
    'UAS' => AppColors.violet600,
    _     => AppColors.gray600,
  };

  Color get typeBg => switch (type) {
    'UTS' => AppColors.blue50,
    'UAS' => AppColors.violet50,
    _     => AppColors.gray100,
  };
}
