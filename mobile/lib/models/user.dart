class User {
  final int id;
  final String name;
  final String email;
  final String role;
  final String? nis;
  final String? nisn;
  final String? nip;
  final String? photoUrl;
  final int? classId;
  final String? className;
  final bool deviceBound;
  final String? phone;
  final String? address;
  final String? birthDate;
  final String? gender;
  final String? parentName;
  final String? parentPhone;

  const User({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.nis,
    this.nisn,
    this.nip,
    this.photoUrl,
    this.classId,
    this.className,
    required this.deviceBound,
    this.phone,
    this.address,
    this.birthDate,
    this.gender,
    this.parentName,
    this.parentPhone,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id:          json['id'] as int,
      name:        json['name'] as String,
      email:       json['email'] as String,
      role:        json['role'] as String,
      nis:         json['nis'] as String?,
      nisn:        json['nisn'] as String?,
      nip:         json['nip'] as String?,
      photoUrl:    json['photo_url'] as String?,
      classId:     json['class_id'] as int?,
      className:   json['class_name'] as String?,
      deviceBound: json['device_bound'] as bool? ?? false,
      phone:       json['phone'] as String?,
      address:     json['address'] as String?,
      birthDate:   json['birth_date'] as String?,
      gender:      json['gender'] as String?,
      parentName:  json['parent_name'] as String?,
      parentPhone: json['parent_phone'] as String?,
    );
  }

  String get displayId => nis ?? nisn ?? nip ?? email;

  String get roleLabel => switch (role) {
    'siswa'           => 'Siswa',
    'siswa_pengelola' => 'Siswa Pengelola',
    'guru'            => 'Guru',
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
