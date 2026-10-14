import '../config/app_config.dart';

class AnnouncementItem {
  final int      id;
  final String   title;
  final String   body;
  final String?  imageUrl;
  final bool     isPinned;
  final DateTime publishedAt;
  final String?  authorName;

  final bool     showAsModal;

  const AnnouncementItem({
    required this.id,
    required this.title,
    required this.body,
    this.imageUrl,
    required this.isPinned,
    required this.publishedAt,
    this.authorName,
    this.showAsModal = false,
  });

  static String? resolveImageUrl(dynamic raw) {
    if (raw == null) return null;
    final str = raw.toString().trim();
    if (str.isEmpty) return null;

    final baseWeb = AppConfig.baseUrl.replaceAll('/api/v1', '');

    // Path relatif seperti '/storage/...' atau 'storage/...'
    if (str.startsWith('/')) {
      return '$baseWeb$str';
    }
    if (str.startsWith('storage/')) {
      return '$baseWeb/$str';
    }

    // Jika server backend mengembalikan http://localhost saat testing lokal,
    // gantikan localhost dengan host baseWeb aplikasi mobile agar bisa diakses Android & iOS
    if (str.contains('://localhost') || str.contains('://127.0.0.1')) {
      final uri = Uri.tryParse(str);
      if (uri != null) {
        return '$baseWeb${uri.path}';
      }
    }

    return str;
  }

  factory AnnouncementItem.fromJson(Map<String, dynamic> json) {
    final rawId = json['id'];
    final id = rawId is int ? rawId : (int.tryParse(rawId?.toString() ?? '') ?? 0);
    final rawDate = json['published_at']?.toString() ?? '';
    final publishedAt = DateTime.tryParse(rawDate) ?? DateTime.now();

    return AnnouncementItem(
      id:          id,
      title:       json['title']?.toString() ?? '',
      body:        json['body']?.toString() ?? '',
      imageUrl:    resolveImageUrl(json['image_url']),
      isPinned:    json['is_pinned'] == true || json['is_pinned'] == 1 || json['is_pinned'] == '1',
      publishedAt: publishedAt,
      authorName:  json['author_name'] as String?,
      showAsModal: json['show_as_modal'] == true || json['show_as_modal'] == 1 || json['show_as_modal'] == '1',
    );
  }
}
