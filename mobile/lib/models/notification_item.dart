class NotificationItem {
  final int     id;
  final String  title;
  final String  body;
  final String  type;
  final String? url;
  final String? imageUrl;
  final bool    isRead;
  final DateTime createdAt;

  const NotificationItem({
    required this.id,
    required this.title,
    required this.body,
    required this.type,
    this.url,
    this.imageUrl,
    required this.isRead,
    required this.createdAt,
  });

  factory NotificationItem.fromJson(Map<String, dynamic> json) {
    return NotificationItem(
      id:        json['id'] != null ? (int.tryParse(json['id'].toString()) ?? 0) : 0,
      title:     json['title']?.toString() ?? '',
      body:      json['body']?.toString() ?? '',
      type:      json['type']?.toString() ?? 'info',
      url:       json['url']?.toString(),
      imageUrl:  json['image_url']?.toString(),
      isRead:    json['is_read'] == true || json['is_read'] == 1 || json['is_read'] == 'true',
      createdAt: DateTime.tryParse(json['created_at']?.toString() ?? '') ?? DateTime.now(),
    );
  }
}
