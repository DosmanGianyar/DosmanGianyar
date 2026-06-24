class RegulationItem {
  final int    id;
  final String title;
  final String content;

  const RegulationItem({required this.id, required this.title, required this.content});

  factory RegulationItem.fromJson(Map<String, dynamic> json) {
    return RegulationItem(
      id:      json['id']      as int,
      title:   json['title']   as String,
      content: json['content'] as String,
    );
  }
}

class RegulationGroup {
  final String            category;
  final String            categoryLabel;
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
