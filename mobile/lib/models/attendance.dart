class ShiftInfo {
  final String checkInOpen;
  final String checkInLate;
  final String checkInClose;
  final String checkOutOpen;

  const ShiftInfo({
    required this.checkInOpen,
    required this.checkInLate,
    required this.checkInClose,
    required this.checkOutOpen,
  });

  factory ShiftInfo.fromJson(Map<String, dynamic> json) {
    return ShiftInfo(
      checkInOpen:  json['check_in_open']  as String,
      checkInLate:  json['check_in_late']  as String,
      checkInClose: json['check_in_close'] as String,
      checkOutOpen: json['check_out_open'] as String,
    );
  }
}

class AttendanceLocation {
  final String name;
  final double lat;
  final double lng;
  final int    radiusMeters;

  const AttendanceLocation({
    required this.name,
    required this.lat,
    required this.lng,
    required this.radiusMeters,
  });

  factory AttendanceLocation.fromJson(Map<String, dynamic> json) {
    return AttendanceLocation(
      name:         json['name']                                    as String,
      lat:          ((json['latitude']  ?? json['lat'])             as num).toDouble(),
      lng:          ((json['longitude'] ?? json['lng'])             as num).toDouble(),
      radiusMeters: (json['radius_meters']                         as num).toInt(),
    );
  }
}

class ActiveShift {
  final ShiftInfo          shift;
  final AttendanceLocation location;

  const ActiveShift({required this.shift, required this.location});

  factory ActiveShift.fromJson(Map<String, dynamic> json) {
    return ActiveShift(
      shift:    ShiftInfo.fromJson(json['shift'] as Map<String, dynamic>),
      location: AttendanceLocation.fromJson(json['location'] as Map<String, dynamic>),
    );
  }
}

class TodayAttendance {
  final String  status;
  final String? checkInTime;
  final String? checkOutTime;
  final bool    isFakeGps;
  final String? checkInPhotoUrl;
  final String? checkOutPhotoUrl;

  const TodayAttendance({
    required this.status,
    this.checkInTime,
    this.checkOutTime,
    required this.isFakeGps,
    this.checkInPhotoUrl,
    this.checkOutPhotoUrl,
  });

  factory TodayAttendance.fromJson(Map<String, dynamic> json) {
    return TodayAttendance(
      status:            json['status']              as String,
      checkInTime:       json['check_in_time']       as String?,
      checkOutTime:      json['check_out_time']      as String?,
      isFakeGps:         json['is_fake_gps']         as bool? ?? false,
      checkInPhotoUrl:   json['check_in_photo_url']  as String?,
      checkOutPhotoUrl:  json['check_out_photo_url'] as String?,
    );
  }

  String get statusLabel => switch (status) {
    'hadir'      => 'Hadir',
    'terlambat'  => 'Terlambat',
    'izin'       => 'Izin',
    'sakit'      => 'Sakit',
    'alpa'       => 'Tidak Hadir',
    'dispensasi' => 'Dispensasi',
    _            => status,
  };
}

class AttendanceStatus {
  final String serverTime;
  final bool isHoliday;
  final ShiftInfo shift;
  final TodayAttendance? attendance;
  final bool canCheckin;
  final bool canCheckout;

  const AttendanceStatus({
    required this.serverTime,
    required this.isHoliday,
    required this.shift,
    this.attendance,
    required this.canCheckin,
    required this.canCheckout,
  });

  factory AttendanceStatus.fromJson(Map<String, dynamic> json) {
    return AttendanceStatus(
      serverTime:  json['server_time'] as String,
      isHoliday:   json['is_holiday'] as bool,
      shift:       ShiftInfo.fromJson(json['shift'] as Map<String, dynamic>),
      attendance:  json['attendance'] != null
          ? TodayAttendance.fromJson(json['attendance'] as Map<String, dynamic>)
          : null,
      canCheckin:  json['can_checkin'] as bool,
      canCheckout: json['can_checkout'] as bool,
    );
  }
}

class AttendanceRecord {
  final String  date;
  final String? checkInTime;
  final String? checkOutTime;
  final String  status;
  final bool    isFakeGps;
  final String? checkInPhotoUrl;
  final String? checkOutPhotoUrl;

  const AttendanceRecord({
    required this.date,
    this.checkInTime,
    this.checkOutTime,
    required this.status,
    required this.isFakeGps,
    this.checkInPhotoUrl,
    this.checkOutPhotoUrl,
  });

  factory AttendanceRecord.fromJson(Map<String, dynamic> json) {
    return AttendanceRecord(
      date:             json['date']              as String,
      checkInTime:      json['check_in_time']     as String?,
      checkOutTime:     json['check_out_time']    as String?,
      status:           json['status']            as String,
      isFakeGps:        json['is_fake_gps']       as bool? ?? false,
      checkInPhotoUrl:  json['check_in_photo_url']  as String?,
      checkOutPhotoUrl: json['check_out_photo_url'] as String?,
    );
  }

  String get statusLabel => switch (status) {
    'hadir'      => 'Hadir',
    'terlambat'  => 'Terlambat',
    'izin'       => 'Izin',
    'sakit'      => 'Sakit',
    'alpa'       => 'Alpa',
    'dispensasi' => 'Dispensasi',
    _            => status,
  };
}

class AttendanceHistory {
  final int month;
  final int year;
  final Map<String, int> summary;
  final List<AttendanceRecord> records;

  const AttendanceHistory({
    required this.month,
    required this.year,
    required this.summary,
    required this.records,
  });

  factory AttendanceHistory.fromJson(Map<String, dynamic> json) {
    return AttendanceHistory(
      month:   json['month'] as int,
      year:    json['year'] as int,
      summary: Map<String, int>.from(json['summary'] as Map),
      records: (json['records'] as List)
          .map((e) => AttendanceRecord.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}
