import '../models/extracurricular.dart';
import 'api_client.dart';

class ExtracurricularService {
  ExtracurricularService._();

  /// Semua ekstra aktif + status keanggotaan user.
  static Future<List<ExtracurricularItem>> fetchAll() async {
    final body = await ApiClient.get('/extracurriculars');
    final list = body['extracurriculars'] as List<dynamic>;
    return list.map((e) => ExtracurricularItem.fromJson(e as Map<String, dynamic>)).toList();
  }

  /// Ekstra yang diikuti user (semua status).
  static Future<List<MyExtracurricularItem>> fetchMy() async {
    final body = await ApiClient.get('/extracurriculars/my');
    final list = body['my_extracurriculars'] as List<dynamic>;
    return list.map((e) => MyExtracurricularItem.fromJson(e as Map<String, dynamic>)).toList();
  }

  /// Ajukan bergabung ke ekstra.
  static Future<String> join(int extraId) async {
    final body = await ApiClient.post('/extracurriculars/$extraId/join');
    return body['message'] as String;
  }

  /// Ajukan keluar dari ekstra.
  static Future<String> leave(int extraId) async {
    final body = await ApiClient.post('/extracurriculars/$extraId/leave');
    return body['message'] as String;
  }

  /// List sesi dari ekstra yang diikuti. filter: 'upcoming' | 'past'
  static Future<List<ExtraSession>> fetchSessions({String filter = 'upcoming'}) async {
    final body = await ApiClient.get('/extracurricular-sessions', params: {'filter': filter});
    final list = body['sessions'] as List<dynamic>;
    return list.map((e) => ExtraSession.fromJson(e as Map<String, dynamic>)).toList();
  }

  /// Detail sesi + daftar anggota + status absen.
  static Future<({ExtraSession session, String myRole, List<ExtraSessionMember> members})>
      fetchSessionDetail(int sessionId) async {
    final body = await ApiClient.get('/extracurricular-sessions/$sessionId');
    final session = ExtraSession.fromJson(body['session'] as Map<String, dynamic>);
    final myRole  = body['my_role'] as String;
    final members = (body['members'] as List<dynamic>)
        .map((m) => ExtraSessionMember.fromJson(m as Map<String, dynamic>))
        .toList();
    return (session: session, myRole: myRole, members: members);
  }

  /// Buat sesi baru (ketua/pembina/admin).
  static Future<ExtraSession> createSession(Map<String, dynamic> data) async {
    final body = await ApiClient.post('/extracurricular-sessions', data: data);
    return ExtraSession.fromJson(body['session'] as Map<String, dynamic>);
  }

  /// Buka/tutup absen sesi.
  static Future<bool> toggleOpen(int sessionId) async {
    final body = await ApiClient.post('/extracurricular-sessions/$sessionId/toggle-open');
    return body['is_open'] as bool;
  }

  /// Simpan kehadiran anggota: list of {user_id, status:'hadir'|'alpa'}.
  static Future<String> markAttendance(
    int sessionId,
    List<Map<String, dynamic>> attendances,
  ) async {
    final body = await ApiClient.post(
      '/extracurricular-sessions/$sessionId/mark',
      data: {'attendances': attendances},
    );
    return body['message'] as String;
  }
}
