class ChatRoomModel {
  final int id;
  final String name;
  final String type; // 'trio' | 'parent_teacher'
  final bool isPrivate;
  final int? studentId;
  final String studentName;
  final String studentNis;
  final String? studentPhoto;
  final String className;
  final String homeroomTeacherName;
  final String parentNames;
  final int unreadCount;
  final String lastMessage;
  final String? lastMessageAt;
  final String lastTimeHuman;

  const ChatRoomModel({
    required this.id,
    required this.name,
    required this.type,
    required this.isPrivate,
    this.studentId,
    required this.studentName,
    required this.studentNis,
    this.studentPhoto,
    required this.className,
    required this.homeroomTeacherName,
    required this.parentNames,
    required this.unreadCount,
    required this.lastMessage,
    this.lastMessageAt,
    required this.lastTimeHuman,
  });

  factory ChatRoomModel.fromJson(Map<String, dynamic> json) {
    return ChatRoomModel(
      id: json['id'] != null ? (int.tryParse(json['id'].toString()) ?? 0) : 0,
      name: json['name']?.toString() ?? '',
      type: json['type']?.toString() ?? 'trio',
      isPrivate: json['is_private'] == true,
      studentId: json['student_id'] != null ? int.tryParse(json['student_id'].toString()) : null,
      studentName: json['student_name']?.toString() ?? json['name']?.toString() ?? 'Siswa',
      studentNis: json['student_nis']?.toString() ?? '',
      studentPhoto: json['student_photo']?.toString(),
      className: json['class_name']?.toString() ?? '',
      homeroomTeacherName: json['homeroom_teacher_name']?.toString() ?? 'Belum ditentukan',
      parentNames: json['parent_names']?.toString() ?? 'Belum terhubung',
      unreadCount: json['unread_count'] != null ? (int.tryParse(json['unread_count'].toString()) ?? 0) : 0,
      lastMessage: json['last_message']?.toString() ?? '',
      lastMessageAt: json['last_message_at']?.toString(),
      lastTimeHuman: json['last_time_human']?.toString() ?? '',
    );
  }
}

class ChatMessageModel {
  final int id;
  final int senderId;
  final String senderName;
  final String senderRole; // 'Wali Kelas' | 'Orang Tua' | 'Siswa' | 'Pengguna'
  final String? senderPhoto;
  final bool isMe;
  final bool isDeleted;
  final bool canDelete;
  final String message;
  final String? attachmentUrl;
  final String? attachmentName;
  final String? attachmentType;
  final String createdAt;
  final String time;
  final String date;
  final String readStatus; // 'sent' | 'read' | 'read_all'
  final int readsCount;
  final dynamic readDetails;

  const ChatMessageModel({
    required this.id,
    required this.senderId,
    required this.senderName,
    required this.senderRole,
    this.senderPhoto,
    required this.isMe,
    required this.isDeleted,
    required this.canDelete,
    required this.message,
    this.attachmentUrl,
    this.attachmentName,
    this.attachmentType,
    required this.createdAt,
    required this.time,
    required this.date,
    required this.readStatus,
    required this.readsCount,
    this.readDetails,
  });

  factory ChatMessageModel.fromJson(Map<String, dynamic> json) {
    return ChatMessageModel(
      id: json['id'] != null ? (int.tryParse(json['id'].toString()) ?? 0) : 0,
      senderId: json['sender_id'] != null ? (int.tryParse(json['sender_id'].toString()) ?? 0) : 0,
      senderName: json['sender_name']?.toString() ?? 'Pengguna',
      senderRole: json['sender_role']?.toString() ?? 'Pengguna',
      senderPhoto: json['sender_photo']?.toString(),
      isMe: json['is_me'] == true,
      isDeleted: json['is_deleted'] == true,
      canDelete: json['can_delete'] == true,
      message: json['message']?.toString() ?? '',
      attachmentUrl: json['attachment_url']?.toString(),
      attachmentName: json['attachment_name']?.toString(),
      attachmentType: json['attachment_type']?.toString(),
      createdAt: json['created_at']?.toString() ?? '',
      time: json['time']?.toString() ?? '',
      date: json['date']?.toString() ?? '',
      readStatus: json['read_status']?.toString() ?? 'sent',
      readsCount: json['reads_count'] != null ? (int.tryParse(json['reads_count'].toString()) ?? 0) : 0,
      readDetails: json['read_details'],
    );
  }
}

class ChatParticipantModel {
  final int userId;
  final String name;
  final String role; // 'Wali Kelas' | 'Orang Tua' | 'Siswa'
  final String? lastRead;
  final String statusText;

  const ChatParticipantModel({
    required this.userId,
    required this.name,
    required this.role,
    this.lastRead,
    required this.statusText,
  });

  factory ChatParticipantModel.fromJson(Map<String, dynamic> json) {
    return ChatParticipantModel(
      userId: json['user_id'] != null ? (int.tryParse(json['user_id'].toString()) ?? 0) : 0,
      name: json['name']?.toString() ?? 'Pengguna',
      role: json['role']?.toString() ?? 'Partisipan',
      lastRead: json['last_read']?.toString(),
      statusText: json['status_text']?.toString() ?? '',
    );
  }
}
