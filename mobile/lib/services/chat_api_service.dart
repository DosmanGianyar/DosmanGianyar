import 'dart:io';
import 'package:dio/dio.dart';
import '../models/chat_models.dart';
import 'api_client.dart';

class ChatApiService {
  ChatApiService._();

  /// GET /api/v1/chat/rooms?type=trio|parent_teacher
  static Future<List<ChatRoomModel>> getRooms({String? type}) async {
    try {
      final params = type != null ? {'type': type} : null;
      final res = await ApiClient.get('/chat/rooms', params: params);
      if (res['success'] == true && res['data'] is List) {
        return (res['data'] as List)
            .map((item) => ChatRoomModel.fromJson(item as Map<String, dynamic>))
            .toList();
      }
      return [];
    } catch (_) {
      return [];
    }
  }

  /// GET /api/v1/chat/rooms/{room}
  static Future<Map<String, dynamic>?> getRoomDetail(int roomId) async {
    try {
      final res = await ApiClient.get('/chat/rooms/$roomId');
      if (res['success'] == true && res['data'] is Map<String, dynamic>) {
        final data = res['data'] as Map<String, dynamic>;
        final roomJson = data['room'] as Map<String, dynamic>? ?? {};
        final participantsJson = data['participants'] as List? ?? [];
        final messagesJson = data['messages'] as List? ?? [];

        return {
          'room': ChatRoomModel.fromJson(roomJson),
          'participants': participantsJson
              .map((p) => ChatParticipantModel.fromJson(p as Map<String, dynamic>))
              .toList(),
          'messages': messagesJson
              .map((m) => ChatMessageModel.fromJson(m as Map<String, dynamic>))
              .toList(),
        };
      }
      return null;
    } catch (_) {
      return null;
    }
  }

  /// GET /api/v1/chat/rooms/{room}/messages?after_id=...
  static Future<Map<String, dynamic>> pollMessages(int roomId, {int afterId = 0}) async {
    try {
      final res = await ApiClient.get(
        '/chat/rooms/$roomId/messages',
        params: afterId > 0 ? {'after_id': afterId} : null,
      );

      final messagesJson = res['messages'] as List? ?? [];
      final messages = messagesJson
          .map((m) => ChatMessageModel.fromJson(m as Map<String, dynamic>))
          .toList();

      return {
        'messages': messages,
        'read_statuses': res['read_statuses'] as Map<String, dynamic>? ?? {},
        'deleted_ids': (res['deleted_ids'] as List?)?.map((id) => int.tryParse(id.toString()) ?? 0).toList() ?? [],
      };
    } catch (_) {
      return {'messages': <ChatMessageModel>[], 'read_statuses': {}, 'deleted_ids': []};
    }
  }

  /// POST /api/v1/chat/rooms/{room}/send
  static Future<ChatMessageModel?> sendMessage(
    int roomId, {
    String? message,
    File? attachment,
  }) async {
    try {
      final formData = FormData();
      if (message != null && message.trim().isNotEmpty) {
        formData.fields.add(MapEntry('message', message.trim()));
      }
      if (attachment != null && await attachment.exists()) {
        final fileName = attachment.path.split(Platform.pathSeparator).last;
        formData.files.add(MapEntry(
          'attachment',
          await MultipartFile.fromFile(attachment.path, filename: fileName),
        ));
      }

      final res = await ApiClient.postForm('/chat/rooms/$roomId/send', formData);
      if (res['success'] == true && res['data'] is Map<String, dynamic>) {
        return ChatMessageModel.fromJson(res['data'] as Map<String, dynamic>);
      }
      return null;
    } catch (_) {
      return null;
    }
  }

  /// DELETE /api/v1/chat/messages/{message}
  static Future<bool> deleteMessage(int messageId) async {
    try {
      final res = await ApiClient.delete('/chat/messages/$messageId');
      return res['success'] == true;
    } catch (_) {
      return false;
    }
  }

  /// GET /api/v1/chat/unread-count
  static Future<int> getUnreadCount() async {
    try {
      final res = await ApiClient.get('/chat/unread-count');
      if (res['success'] == true && res['unread'] != null) {
        return int.tryParse(res['unread'].toString()) ?? 0;
      }
      return 0;
    } catch (_) {
      return 0;
    }
  }
}
