import '../models/attendance.dart';
import '../models/achievement.dart';
import '../models/conduct_log.dart';
import '../models/user.dart';
import 'api_client.dart';

/// Endpoint khusus akun orangtua — semua bersifat baca-saja (read-only)
/// atas data anak yang terhubung ke akun tersebut.
class OrangtuaService {
  OrangtuaService._();

  static Future<List<ChildSummary>> getChildren() async {
    final body = await ApiClient.get('/orangtua/children');
    return (body['children'] as List)
        .map((e) => ChildSummary.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  static Future<AttendanceHistory> getAttendanceHistory({
    required int studentId,
    required int month,
    required int year,
  }) async {
    final body = await ApiClient.get('/orangtua/attendance', params: {
      'student_id': studentId,
      'month':      month,
      'year':       year,
    });
    return AttendanceHistory.fromJson(body);
  }

  static Future<(ConductSummary, List<ConductLog>)> getConduct(int studentId) async {
    final body = await ApiClient.get('/orangtua/conduct', params: {'student_id': studentId});
    final summary = ConductSummary.fromJson(body['summary'] as Map<String, dynamic>);
    final logs = (body['logs'] as List)
        .map((e) => ConductLog.fromJson(e as Map<String, dynamic>))
        .toList();
    return (summary, logs);
  }

  static Future<(AchievementStats, List<Achievement>)> getAchievements(int studentId) async {
    final body = await ApiClient.get('/orangtua/achievements', params: {'student_id': studentId});
    final stats = AchievementStats.fromJson(body['stats'] as Map<String, dynamic>);
    final items = (body['achievements'] as List)
        .map((e) => Achievement.fromJson(e as Map<String, dynamic>))
        .toList();
    return (stats, items);
  }
}
