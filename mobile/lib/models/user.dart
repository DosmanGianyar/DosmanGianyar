class SubjectRef {
  final int    id;
  final String name;
  const SubjectRef({required this.id, required this.name});
  factory SubjectRef.fromJson(Map<String, dynamic> json) => SubjectRef(
    id:   json['id'] as int,
    name: json['name'] as String,
  );
}

/// Ringkasan data anak, dikirim di payload login/me untuk akun orangtua.
class ChildSummary {
  final int     id;
  final String  name;
  final String? className;
  final String? photoUrl;

  const ChildSummary({
    required this.id,
    required this.name,
    this.className,
    this.photoUrl,
  });

  factory ChildSummary.fromJson(Map<String, dynamic> json) => ChildSummary(
    id:        json['id'] as int,
    name:      json['name'] as String,
    className: json['class_name'] as String?,
    photoUrl:  json['photo_url'] as String?,
  );

  String get initials {
    final parts = name.trim().split(' ');
    if (parts.length >= 2) return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
    return name.isNotEmpty ? name[0].toUpperCase() : '?';
  }
}

class User {
  final int id;
  final String name;
  final String email;
  final String role;
  final String? nis;
  final String? nisn;
  final String? nip;
  final String? subject;
  final List<SubjectRef> subjects;
  final String? photoUrl;
  final int?    homeroomClassId;
  final String? homeroomClassName;
  final int? classId;
  final String? className;
  final bool deviceBound;
  final bool mustChangePassword;
  final bool isBk;
  final String? phone;
  final String? address;
  final String? birthDate;
  final String? gender;
  final String? parentName;
  final String? parentPhone;
  final List<ChildSummary> children;

  const User({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.nis,
    this.nisn,
    this.nip,
    this.subject,
    this.subjects = const [],
    this.photoUrl,
    this.classId,
    this.className,
    this.homeroomClassId,
    this.homeroomClassName,
    required this.deviceBound,
    this.mustChangePassword = false,
    this.isBk = false,
    this.phone,
    this.address,
    this.birthDate,
    this.gender,
    this.parentName,
    this.parentPhone,
    this.children = const [],
  });

  String get subjectDisplay {
    if (subjects.isNotEmpty) return subjects.map((s) => s.name).join(', ');
    return subject ?? '';
  }

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id:                json['id'] as int,
      name:              json['name'] as String,
      email:             json['email'] as String,
      role:              json['role'] as String,
      nis:               json['nis'] as String?,
      nisn:              json['nisn'] as String?,
      nip:               json['nip'] as String?,
      subject:           json['subject'] as String?,
      subjects:          (json['subjects'] as List<dynamic>? ?? [])
                             .map((e) => SubjectRef.fromJson(e as Map<String, dynamic>))
                             .toList(),
      photoUrl:          json['photo_url'] as String?,
      classId:           json['class_id'] as int?,
      className:         json['class_name'] as String?,
      homeroomClassId:   json['homeroom_class_id'] as int?,
      homeroomClassName: json['homeroom_class_name'] as String?,
      deviceBound:       json['device_bound'] as bool? ?? false,
      mustChangePassword: json['must_change_password'] as bool? ?? false,
      isBk:              json['is_bk'] as bool? ?? false,
      phone:             json['phone'] as String?,
      address:           json['address'] as String?,
      birthDate:         json['birth_date'] as String?,
      gender:            json['gender'] as String?,
      parentName:        json['parent_name'] as String?,
      parentPhone:       json['parent_phone'] as String?,
      children:          (json['children'] as List<dynamic>? ?? [])
                             .map((e) => ChildSummary.fromJson(e as Map<String, dynamic>))
                             .toList(),
    );
  }

  Map<String, dynamic> toJson() => {
    'id':                 id,
    'name':               name,
    'email':              email,
    'role':               role,
    'nis':                nis,
    'nisn':               nisn,
    'nip':                nip,
    'subject':            subject,
    'photo_url':          photoUrl,
    'class_id':           classId,
    'class_name':         className,
    'homeroom_class_id':  homeroomClassId,
    'homeroom_class_name': homeroomClassName,
    'device_bound':       deviceBound,
    'must_change_password': mustChangePassword,
    'phone':              phone,
    'address':            address,
    'birth_date':         birthDate,
    'gender':             gender,
    'parent_name':        parentName,
    'parent_phone':       parentPhone,
    'children':           children.map((c) => {
      'id': c.id, 'name': c.name, 'class_name': c.className, 'photo_url': c.photoUrl,
    }).toList(),
  };

  String get displayId => nis ?? nisn ?? nip ?? email;

  String get roleLabel => switch (role) {
    'siswa'           => 'Siswa',
    'siswa_pengelola' => 'Siswa Pengelola',
    'guru'            => 'Guru',
    'orangtua'        => 'Orangtua',
    _                 => role,
  };

  String get genderLabel => switch (gender) {
    'L' => 'Laki-laki',
    'P' => 'Perempuan',
    _   => '—',
  };

  String get initials {
    final parts = name.trim().split(' ');
    if (parts.length >= 2) return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
    return name.isNotEmpty ? name[0].toUpperCase() : '?';
  }
}
