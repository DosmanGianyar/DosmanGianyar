import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

class ConductSummary {
  final int prestasiCount, pelanggaranCount;
  const ConductSummary({required this.prestasiCount, required this.pelanggaranCount});
  factory ConductSummary.fromJson(Map<String, dynamic> j) => ConductSummary(
    prestasiCount:    j['prestasi_count']    as int,
    pelanggaranCount: j['pelanggaran_count'] as int,
  );
}

class ConductLog {
  final int    id;
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
    categoryName: j['category_name'] as String,
    type:         j['type']          as String,
    context:      j['context']       as String,
    date:         j['date']          as String,
    createdAt:    j['created_at']    as String,
    note:         j['note']          as String?,
    photoUrl:     j['photo_url']     as String?,
    teacherName:  j['teacher_name']  as String?,
  );

  bool get isPrestasi    => type == 'prestasi';
  bool get isPelanggaran => type == 'pelanggaran';

  Color get typeColor => isPrestasi ? AppColors.green500 : AppColors.red500;
  Color get typeBg    => isPrestasi ? AppColors.green100  : AppColors.red100;
  String get typeLabel => isPrestasi ? 'Prestasi' : 'Pelanggaran';
}
