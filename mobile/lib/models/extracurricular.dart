class ExtracurricularItem {
  final int     id;
  final String  name;
  final String? description;
  final String? logoUrl;
  final String? pembinaName;
  final int     activeMembers;
  final int?    maxMembers;
  final bool    isFull;
  final String? myStatus; // pending_join | active | pending_leave | null
  final String? myRole;   // member | ketua | null

  const ExtracurricularItem({
    required this.id,
    required this.name,
    this.description,
    this.logoUrl,
    this.pembinaName,
    required this.activeMembers,
    this.maxMembers,
    required this.isFull,
    this.myStatus,
    this.myRole,
  });

  factory ExtracurricularItem.fromJson(Map<String, dynamic> json) {
    return ExtracurricularItem(
      id:            json['id']             as int,
      name:          json['name']           as String,
      description:   json['description']    as String?,
      logoUrl:       json['logo_url']       as String?,
      pembinaName:   json['pembina_name']   as String?,
      activeMembers: json['active_members'] as int? ?? 0,
      maxMembers:    json['max_members']    as int?,
      isFull:        json['is_full']        as bool? ?? false,
      myStatus:      json['my_status']      as String?,
      myRole:        json['my_role']        as String?,
    );
  }

  bool get isMyActive      => myStatus == 'active';
  bool get isPendingJoin   => myStatus == 'pending_join';
  bool get isPendingLeave  => myStatus == 'pending_leave';
  bool get isKetua         => myRole   == 'ketua';
  bool get isMember        => myStatus != null;

  String get statusLabel => switch (myStatus) {
    'pending_join'  => 'Menunggu Persetujuan',
    'active'        => isKetua ? 'Ketua' : 'Anggota',
    'pending_leave' => 'Mengajukan Keluar',
    _               => '',
  };
}

class MyExtracurricularItem {
  final int     id;
  final String  name;
  final String? description;
  final String? logoUrl;
  final String? pembinaName;
  final String  myRole;
  final String  myStatus;
  final String  roleLabel;
  final String  statusLabel;
  final DateTime joinedAt;

  const MyExtracurricularItem({
    required this.id,
    required this.name,
    this.description,
    this.logoUrl,
    this.pembinaName,
    required this.myRole,
    required this.myStatus,
    required this.roleLabel,
    required this.statusLabel,
    required this.joinedAt,
  });

  bool get isKetua        => myRole   == 'ketua';
  bool get isActive       => myStatus == 'active';
  bool get isPendingJoin  => myStatus == 'pending_join';
  bool get isPendingLeave => myStatus == 'pending_leave';

  factory MyExtracurricularItem.fromJson(Map<String, dynamic> json) {
    return MyExtracurricularItem(
      id:          json['id']           as int,
      name:        json['name']         as String,
      description: json['description']  as String?,
      logoUrl:     json['logo_url']     as String?,
      pembinaName: json['pembina_name'] as String?,
      myRole:      json['my_role']      as String,
      myStatus:    json['my_status']    as String,
      roleLabel:   json['role_label']   as String,
      statusLabel: json['status_label'] as String,
      joinedAt:    DateTime.parse(json['joined_at'] as String),
    );
  }
}

class ExtraSession {
  final int     id;
  final int     extracurricularId;
  final String  extracurricularName;
  final String  title;
  final String  sessionDate;
  final String  startTime;
  final String  endTime;
  final String? location;
  final String? notes;
  final bool    isOpen;
  final int     hadirCount;
  final int     alpaCount;

  const ExtraSession({
    required this.id,
    required this.extracurricularId,
    required this.extracurricularName,
    required this.title,
    required this.sessionDate,
    required this.startTime,
    required this.endTime,
    this.location,
    this.notes,
    required this.isOpen,
    required this.hadirCount,
    required this.alpaCount,
  });

  factory ExtraSession.fromJson(Map<String, dynamic> json) {
    return ExtraSession(
      id:                   json['id']                    as int,
      extracurricularId:    json['extracurricular_id']    as int,
      extracurricularName:  json['extracurricular_name']  as String? ?? '',
      title:                json['title']                 as String,
      sessionDate:          json['session_date']          as String,
      startTime:            json['start_time']            as String,
      endTime:              json['end_time']              as String,
      location:             json['location']              as String?,
      notes:                json['notes']                 as String?,
      isOpen:               json['is_open']               as bool? ?? false,
      hadirCount:           json['hadir_count']           as int? ?? 0,
      alpaCount:            json['alpa_count']            as int? ?? 0,
    );
  }

  DateTime get dateTime => DateTime.parse(sessionDate);
  bool get isPast => dateTime.isBefore(DateTime.now().subtract(const Duration(hours: 1)));
}

class ExtraSessionMember {
  final int     userId;
  final String  name;
  final String? nis;
  final String  role;
  String?       attendance; // hadir | alpa | null

  ExtraSessionMember({
    required this.userId,
    required this.name,
    this.nis,
    required this.role,
    this.attendance,
  });

  factory ExtraSessionMember.fromJson(Map<String, dynamic> json) {
    return ExtraSessionMember(
      userId:     json['user_id']    as int,
      name:       json['name']       as String,
      nis:        json['nis']        as String?,
      role:       json['role']       as String,
      attendance: json['attendance'] as String?,
    );
  }

  bool get isKetua  => role == 'ketua';
  bool get isHadir  => attendance == 'hadir';
}
