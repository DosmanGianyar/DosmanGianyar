class NotificationItem {
  final int     id;
  final String  title;
  final String  body;
  final String  type;
  final String? url;
  final bool    isRead;
  final DateTime createdAt;

  const NotificationItem({
    required this.id,
    required this.title,
    required this.body,
    required this.type,
    this.url,
    required this.isRead,
    required this.createdAt,
  });

  factory NotificationItem.fromJson(Map<String, dynamic> json) {
    return NotificationItem(
      id:        json['id']         as int,
      title:     json['title']      as String,
      body:      json['body']       as String,
      type:      json['type']       as String? ?? 'info',
      url:       json['url']        as String?,
      isRead:    json['is_read']    as bool? ?? false,
      createdAt: DateTime.parse(json['created_at'] as String),
    );
  }
}
