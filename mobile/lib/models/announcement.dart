class AnnouncementItem {
  final int      id;
  final String   title;
  final String   body;
  final bool     isPinned;
  final DateTime publishedAt;
  final String?  authorName;

  const AnnouncementItem({
    required this.id,
    required this.title,
    required this.body,
    required this.isPinned,
    required this.publishedAt,
    this.authorName,
  });

  factory AnnouncementItem.fromJson(Map<String, dynamic> json) {
    return AnnouncementItem(
      id:          json['id']           as int,
      title:       json['title']        as String,
      body:        json['body']         as String,
      isPinned:    json['is_pinned']    as bool? ?? false,
      publishedAt: DateTime.parse(json['published_at'] as String),
      authorName:  json['author_name']  as String?,
    );
  }
}
