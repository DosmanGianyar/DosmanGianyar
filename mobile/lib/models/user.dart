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
  final String? angkatan;
  final bool canEditProfile;
  final String? hobbies;
  final String? aspirations;
  final String? rtRw;
  final String? kelurahan;
  final String? kecamatan;
  final String? kabupaten;
  final String? residenceStatus;
  final String? transportation;
  final double? distanceKm;
  final int?    travelTimeMinutes;
  final String? fatherName;
  final String? fatherPhone;
  final String? fatherJob;
  final String? motherName;
  final String? motherPhone;
  final String? motherJob;
  final String? guardianName;
  final String? guardianPhone;
  final String? guardianJob;
  final String? emergencyContactName;
  final String? emergencyContactPhone;
  final String? emergencyContactRelation;
  final String? bloodType;
  final String? medicalHistory;
  final int?    heightCm;
  final int?    weightKg;
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
    this.canEditProfile = true,
    this.phone,
    this.address,
    this.birthDate,
    this.gender,
    this.parentName,
    this.parentPhone,
    this.angkatan,
    this.hobbies,
    this.aspirations,
    this.rtRw,
    this.kelurahan,
    this.kecamatan,
    this.kabupaten,
    this.residenceStatus,
    this.transportation,
    this.distanceKm,
    this.travelTimeMinutes,
    this.fatherName,
    this.fatherPhone,
    this.fatherJob,
    this.motherName,
    this.motherPhone,
    this.motherJob,
    this.guardianName,
    this.guardianPhone,
    this.guardianJob,
    this.emergencyContactName,
    this.emergencyContactPhone,
    this.emergencyContactRelation,
    this.bloodType,
    this.medicalHistory,
    this.heightCm,
    this.weightKg,
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
      photoUrl:          _normalizeUrl(json['photo_url'] as String?),
      classId:           json['class_id'] as int?,
      className:         json['class_name'] as String?,
      homeroomClassId:   json['homeroom_class_id'] as int?,
      homeroomClassName: json['homeroom_class_name'] as String?,
      deviceBound:       json['device_bound'] as bool? ?? false,
      mustChangePassword: json['must_change_password'] as bool? ?? false,
      isBk:              json['is_bk'] as bool? ?? false,
      canEditProfile:    json['can_edit_profile'] as bool? ?? true,
      phone:             json['phone'] as String?,
      address:           json['address'] as String?,
      birthDate:         json['birth_date'] as String?,
      gender:            json['gender'] as String?,
      parentName:        json['parent_name'] as String?,
      parentPhone:       json['parent_phone'] as String?,
      angkatan:          json['angkatan'] as String?,
      hobbies:           json['hobbies'] as String?,
      aspirations:       json['aspirations'] as String?,
      rtRw:              json['rt_rw'] as String?,
      kelurahan:         json['kelurahan'] as String?,
      kecamatan:         json['kecamatan'] as String?,
      kabupaten:         json['kabupaten'] as String?,
      residenceStatus:   json['residence_status'] as String?,
      transportation:     json['transportation'] as String?,
      distanceKm:        (json['distance_km'] as num?)?.toDouble(),
      travelTimeMinutes: json['travel_time_minutes'] as int?,
      fatherName:        json['father_name'] as String?,
      fatherPhone:       json['father_phone'] as String?,
      fatherJob:         json['father_job'] as String?,
      motherName:        json['mother_name'] as String?,
      motherPhone:       json['mother_phone'] as String?,
      motherJob:         json['mother_job'] as String?,
      guardianName:      json['guardian_name'] as String?,
      guardianPhone:     json['guardian_phone'] as String?,
      guardianJob:       json['guardian_job'] as String?,
      emergencyContactName:     json['emergency_contact_name'] as String?,
      emergencyContactPhone:    json['emergency_contact_phone'] as String?,
      emergencyContactRelation: json['emergency_contact_relation'] as String?,
      bloodType:         json['blood_type'] as String?,
      medicalHistory:    json['medical_history'] as String?,
      heightCm:          json['height_cm'] as int?,
      weightKg:          json['weight_kg'] as int?,
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
    'angkatan':           angkatan,
    'children':           children.map((c) => {
      'id': c.id, 'name': c.name, 'class_name': c.className, 'photo_url': c.photoUrl,
    }).toList(),
  };

  String get displayId => nis ?? nisn ?? nip ?? email;

  bool get isSiswa => role.startsWith('siswa');

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

  static String? _normalizeUrl(String? url) {
    if (url == null || url.trim().isEmpty) return null;
    var u = url.trim();
    if (u.contains('sims.sman1-gianyar.sch.id')) {
      u = u.replaceAll('https://sims.sman1-gianyar.sch.id', 'https://36.93.15.146')
           .replaceAll('http://sims.sman1-gianyar.sch.id', 'https://36.93.15.146');
    }
    if (u.contains('localhost')) {
      u = u.replaceAll('https://localhost', 'https://36.93.15.146')
           .replaceAll('http://localhost', 'https://36.93.15.146');
    }
    return u;
  }
}
