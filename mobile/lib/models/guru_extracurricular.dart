class GuruExtraSummary {
  final int id;
  final String name;
  final String? description;
  final String? contactPerson;
  final String? logoUrl;
  final String pembinaNames;
  final int activeMembersCount;
  final int pendingMembersCount;
  final int journalsCount;
  final String? lastSessionDate;

  const GuruExtraSummary({
    required this.id,
    required this.name,
    this.description,
    this.contactPerson,
    this.logoUrl,
    required this.pembinaNames,
    required this.activeMembersCount,
    required this.pendingMembersCount,
    required this.journalsCount,
    this.lastSessionDate,
  });

  factory GuruExtraSummary.fromJson(Map<String, dynamic> json) =>
      GuruExtraSummary(
        id: (json['id'] as num).toInt(),
        name: json['name'] as String? ?? '—',
        description: json['description'] as String?,
        contactPerson: json['contact_person'] as String?,
        logoUrl: json['logo_url'] as String?,
        pembinaNames: json['pembina_names'] as String? ?? '—',
        activeMembersCount: (json['active_members_count'] as num?)?.toInt() ?? 0,
        pendingMembersCount:
            (json['pending_members_count'] as num?)?.toInt() ?? 0,
        journalsCount: (json['journals_count'] as num?)?.toInt() ?? 0,
        lastSessionDate: json['last_session_date'] as String?,
      );
}

class GuruExtraMember {
  final int userId;
  final String name;
  final String? nis;
  final String className;
  final String role;
  final String roleLabel;
  final String? joinedAt;
  final int hadirCount;
  final int izinCount;
  final int sakitCount;
  final int alpaCount;
  final int totalSessions;

  const GuruExtraMember({
    required this.userId,
    required this.name,
    this.nis,
    required this.className,
    required this.role,
    required this.roleLabel,
    this.joinedAt,
    required this.hadirCount,
    required this.izinCount,
    required this.sakitCount,
    required this.alpaCount,
    required this.totalSessions,
  });

  bool get isLeader => role == 'ketua' || role == 'wakil_ketua';

  factory GuruExtraMember.fromJson(Map<String, dynamic> json) =>
      GuruExtraMember(
        userId: (json['user_id'] as num).toInt(),
        name: json['name'] as String? ?? 'Siswa',
        nis: json['nis'] as String?,
        className: json['class_name'] as String? ?? '—',
        role: json['role'] as String? ?? 'member',
        roleLabel: json['role_label'] as String? ?? 'Anggota',
        joinedAt: json['joined_at'] as String?,
        hadirCount: (json['hadir_count'] as num?)?.toInt() ?? 0,
        izinCount: (json['izin_count'] as num?)?.toInt() ?? 0,
        sakitCount: (json['sakit_count'] as num?)?.toInt() ?? 0,
        alpaCount: (json['alpa_count'] as num?)?.toInt() ?? 0,
        totalSessions: (json['total_sessions'] as num?)?.toInt() ?? 0,
      );
}

class GuruExtraAttendanceRow {
  final int userId;
  final String name;
  final String? nis;
  final String className;
  final String role;
  final String roleLabel;
  String status; // hadir | izin | sakit | alpa
  String? note;

  GuruExtraAttendanceRow({
    required this.userId,
    required this.name,
    this.nis,
    required this.className,
    required this.role,
    required this.roleLabel,
    this.status = 'hadir',
    this.note,
  });

  bool get isLeader => role == 'ketua' || role == 'wakil_ketua';

  factory GuruExtraAttendanceRow.fromMember(GuruExtraMember m) =>
      GuruExtraAttendanceRow(
        userId: m.userId,
        name: m.name,
        nis: m.nis,
        className: m.className,
        role: m.role,
        roleLabel: m.roleLabel,
        status: 'hadir',
      );

  factory GuruExtraAttendanceRow.fromJson(Map<String, dynamic> json) =>
      GuruExtraAttendanceRow(
        userId: (json['user_id'] as num).toInt(),
        name: json['name'] as String? ?? 'Siswa',
        nis: json['nis'] as String?,
        className: json['class_name'] as String? ?? '—',
        role: json['role'] as String? ?? 'member',
        roleLabel: json['role_label'] as String? ?? 'Anggota',
        status: (json['attendance'] as String?) ?? 'hadir',
        note: json['note'] as String?,
      );

  Map<String, dynamic> toPayload() => {
        'user_id': userId,
        'status': status,
        if (note != null && note!.trim().isNotEmpty) 'note': note!.trim(),
      };
}

class GuruExtraJournal {
  final int id;
  final int extracurricularId;
  final String extracurricularName;
  final String title;
  final String sessionDate;
  final String dateFormatted;
  final String startTime;
  final String endTime;
  final String? location;
  final String? material;
  final String? activity;
  final String? obstacles;
  final String? notes;
  final String? photoUrl;
  final String? creatorName;
  final bool hasJournal;
  final int hadirCount;
  final int izinCount;
  final int sakitCount;
  final int alpaCount;
  final int totalMarked;
  final int totalMembers;

  const GuruExtraJournal({
    required this.id,
    required this.extracurricularId,
    required this.extracurricularName,
    required this.title,
    required this.sessionDate,
    required this.dateFormatted,
    required this.startTime,
    required this.endTime,
    this.location,
    this.material,
    this.activity,
    this.obstacles,
    this.notes,
    this.photoUrl,
    this.creatorName,
    required this.hasJournal,
    required this.hadirCount,
    required this.izinCount,
    required this.sakitCount,
    required this.alpaCount,
    required this.totalMarked,
    required this.totalMembers,
  });

  factory GuruExtraJournal.fromJson(Map<String, dynamic> json) =>
      GuruExtraJournal(
        id: (json['id'] as num).toInt(),
        extracurricularId: (json['extracurricular_id'] as num).toInt(),
        extracurricularName: json['extracurricular_name'] as String? ?? '—',
        title: json['title'] as String? ?? '—',
        sessionDate: json['session_date'] as String? ?? '',
        dateFormatted: json['date_formatted'] as String? ?? '—',
        startTime: json['start_time'] as String? ?? '',
        endTime: json['end_time'] as String? ?? '',
        location: json['location'] as String?,
        material: json['material'] as String?,
        activity: json['activity'] as String?,
        obstacles: json['obstacles'] as String?,
        notes: json['notes'] as String?,
        photoUrl: json['photo_url'] as String?,
        creatorName: json['creator_name'] as String?,
        hasJournal: json['has_journal'] as bool? ?? false,
        hadirCount: (json['hadir_count'] as num?)?.toInt() ?? 0,
        izinCount: (json['izin_count'] as num?)?.toInt() ?? 0,
        sakitCount: (json['sakit_count'] as num?)?.toInt() ?? 0,
        alpaCount: (json['alpa_count'] as num?)?.toInt() ?? 0,
        totalMarked: (json['total_marked'] as num?)?.toInt() ?? 0,
        totalMembers: (json['total_members'] as num?)?.toInt() ?? 0,
      );
}
