class GuruAlert {
  final String name;
  final String schoolClass;
  final int pelanggaranCount;

  const GuruAlert({
    required this.name,
    required this.schoolClass,
    required this.pelanggaranCount,
  });

  factory GuruAlert.fromJson(Map<String, dynamic> json) => GuruAlert(
    name:             json['name'] as String,
    schoolClass:      json['class'] as String? ?? '—',
    pelanggaranCount: json['pelanggaran_count'] as int? ?? 0,
  );
}

class GuruDashboard {
  final int totalStudents;
  final int pendingPermits;
  final int pendingEarlyCheckouts;
  final int pendingForgotAttendances;
  final List<GuruAlert> recentAlerts;

  const GuruDashboard({
    required this.totalStudents,
    required this.pendingPermits,
    required this.pendingEarlyCheckouts,
    required this.pendingForgotAttendances,
    required this.recentAlerts,
  });

  int get totalPending => pendingPermits + pendingEarlyCheckouts + pendingForgotAttendances;

  factory GuruDashboard.fromJson(Map<String, dynamic> json) => GuruDashboard(
    totalStudents:             json['total_students'] as int? ?? 0,
    pendingPermits:            json['pending_permits'] as int? ?? 0,
    pendingEarlyCheckouts:     json['pending_early_checkouts'] as int? ?? 0,
    pendingForgotAttendances:  json['pending_forgot_attendances'] as int? ?? 0,
    recentAlerts: (json['recent_alerts'] as List<dynamic>? ?? [])
        .map((e) => GuruAlert.fromJson(e as Map<String, dynamic>))
        .toList(),
  );
}
