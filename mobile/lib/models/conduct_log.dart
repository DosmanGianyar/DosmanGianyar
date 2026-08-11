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

  bool get isCatatanPositif  => type == 'prestasi';
  bool get isCatatanNegatif  => type == 'pelanggaran';
  // backward compat
  bool get isPrestasi    => isCatatanPositif;
  bool get isPelanggaran => isCatatanNegatif;

  Color get typeColor => isCatatanPositif ? AppColors.emerald600 : AppColors.red500;
  Color get typeBg    => isCatatanPositif ? AppColors.emerald50  : AppColors.red50;
  Color get typeIconColor => isCatatanPositif ? AppColors.emerald600 : AppColors.red500;
  String get typeLabel => isCatatanPositif ? 'Apresiasi Karakter' : 'Kedisiplinan Karakter';

  String get displayCategoryName {
    if (categoryName.startsWith('__sistem__')) {
      if (note != null && note!.isNotEmpty) {
        final reg = RegExp(r'^\[(.*?)\]\s*(.*)$');
        final match = reg.firstMatch(note!);
        if (match != null && match.group(1) != null && match.group(1)!.trim().isNotEmpty) {
          final t = match.group(1)!.trim();
          return t.substring(0, 1).toUpperCase() + t.substring(1);
        }
      }
      return isCatatanPositif ? 'Catatan Apresiasi' : 'Catatan Kedisiplinan';
    }
    return categoryName;
  }

  String? get displayNoteBody {
    if (note != null && note!.isNotEmpty) {
      final reg = RegExp(r'^\[(.*?)\]\s*(.*)$');
      final match = reg.firstMatch(note!);
      if (match != null && match.group(2) != null && match.group(2)!.trim().isNotEmpty) {
        return match.group(2)!.trim();
      }
      return note;
    }
    return null;
  }
}
