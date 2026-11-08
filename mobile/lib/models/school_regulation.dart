class RegulationItem {
  final int          id;
  final String?      category;
  final String?      badgeText;
  final String       title;
  final String       content;
  final String?      imageUrl;
  final List<String> imageUrls;
  final int          sortOrder;

  const RegulationItem({
    required this.id,
    this.category,
    this.badgeText,
    required this.title,
    required this.content,
    this.imageUrl,
    this.imageUrls = const [],
    this.sortOrder = 0,
  });

  factory RegulationItem.fromJson(Map<String, dynamic> json) {
    return RegulationItem(
      id:        json['id']        as int,
      category:  json['category']  as String?,
      badgeText: json['badge_text'] as String?,
      title:     json['title']     as String,
      content:   json['content']   as String,
      imageUrl:  json['image_url'] as String?,
      imageUrls: (json['image_urls'] as List<dynamic>?)
              ?.map((e) => e.toString())
              .toList() ??
          [],
      sortOrder: (json['sort_order'] as num?)?.toInt() ?? 0,
    );
  }
}

class UniformGuide {
  final int          id;
  final String       day;
  final String       title;
  final String       content;
  final String?      imageUrl;
  final List<String> imageUrls;

  const UniformGuide({
    required this.id,
    required this.day,
    required this.title,
    required this.content,
    this.imageUrl,
    this.imageUrls = const [],
  });

  factory UniformGuide.fromJson(Map<String, dynamic> json) {
    return UniformGuide(
      id:        json['id']        as int,
      day:       (json['day'] as String?) ?? '',
      title:     (json['title'] as String?) ?? '',
      content:   (json['content'] as String?) ?? '',
      imageUrl:  json['image_url'] as String?,
      imageUrls: (json['image_urls'] as List<dynamic>?)
              ?.map((e) => e.toString())
              .toList() ??
          [],
    );
  }
}

class RegulationGroup {
  final String               category;
  final String               categoryLabel;
  final List<RegulationItem> items;

  const RegulationGroup({
    required this.category,
    required this.categoryLabel,
    required this.items,
  });

  factory RegulationGroup.fromJson(Map<String, dynamic> json) {
    return RegulationGroup(
      category:      json['category']       as String,
      categoryLabel: json['category_label'] as String,
      items: (json['items'] as List<dynamic>)
          .map((e) => RegulationItem.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}
