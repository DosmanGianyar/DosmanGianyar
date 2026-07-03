import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

class ConductSummary {
  final int totalPoint, prestasiPoint, pelanggaranPoint;
  const ConductSummary({
    required this.totalPoint,
    required this.prestasiPoint,
    required this.pelanggaranPoint,
  });
  factory ConductSummary.fromJson(Map<String, dynamic> j) => ConductSummary(
    totalPoint:       j['total_point']       as int,
    prestasiPoint:    j['prestasi_point']    as int,
    pelanggaranPoint: j['pelanggaran_point'] as int,
  );
}

class ConductLog {
  final int    id;
  final int    point;
  final String categoryName;
  final String type;
  final String context;
  final String date;
  final String createdAt;
  final String? note;
  final String? photoUrl;
  final String? teacherName;

  const ConductLog({
    required this.id,
    required this.point,
    required this.categoryName,
    required this.type,
    required this.context,
    required this.date,
    required this.createdAt,
    this.note,
    this.photoUrl,
    this.teacherName,
  });

  factory ConductLog.fromJson(Map<String, dynamic> j) => ConductLog(
    id:           j['id']            as int,
    point:        j['point']         as int,
    categoryName: j['category_name'] as String,
    type:         j['type']          as String,
    context:      j['context']       as String,
    date:         j['date']          as String,
    createdAt:    j['created_at']    as String,
    note:         j['note']          as String?,
    photoUrl:     j['photo_url']     as String?,
    teacherName:  j['teacher_name']  as String?,
  );

  bool get isPrestasi    => point > 0;
  bool get isPelanggaran => point < 0;

  Color get typeColor => isPrestasi ? AppColors.green500 : AppColors.red500;
  Color get typeBg    => isPrestasi ? AppColors.green100  : AppColors.red100;
  String get typeLabel => isPrestasi ? 'Prestasi' : 'Pelanggaran';

  String get pointLabel => isPrestasi ? '+$point' : '$point';
}
