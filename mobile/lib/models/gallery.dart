class GalleryItem {
  final int id;
  final String title;
  final String? description;
  final String? eventDate;
  final String? eventDateLabel;
  final String? coverUrl;
  final bool isPublished;
  final bool isUploadOpen;
  final bool canUpload;
  final int myUploadedCount;
  final int maxPhotosPerUser;
  final int remainingQuota;
  final int photosCount;
  final int reportsCount;

  const GalleryItem({
    required this.id,
    required this.title,
    this.description,
    this.eventDate,
    this.eventDateLabel,
    this.coverUrl,
    required this.isPublished,
    required this.isUploadOpen,
    required this.canUpload,
    required this.myUploadedCount,
    required this.maxPhotosPerUser,
    required this.remainingQuota,
    required this.photosCount,
    required this.reportsCount,
  });

  factory GalleryItem.fromJson(Map<String, dynamic> json) {
    return GalleryItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: json['title'] as String? ?? '',
      description: json['description'] as String?,
      eventDate: json['event_date'] as String?,
      eventDateLabel: json['event_date_label'] as String?,
      coverUrl: json['cover_url'] as String?,
      isPublished: json['is_published'] == true,
      isUploadOpen: json['is_upload_open'] == true,
      canUpload: json['can_upload'] == true,
      myUploadedCount: (json['my_uploaded_count'] as num?)?.toInt() ?? 0,
      maxPhotosPerUser: (json['max_photos_per_user'] as num?)?.toInt() ?? 10,
      remainingQuota: (json['remaining_quota'] as num?)?.toInt() ?? 0,
      photosCount: (json['photos_count'] as num?)?.toInt() ?? 0,
      reportsCount: (json['reports_count'] as num?)?.toInt() ?? 0,
    );
  }
}

class GalleryPhotoItem {
  final int id;
  final String url;
  final String thumbnailUrl;
  final String? caption;
  final String uploaderName;
  final String uploaderRole;
  final bool isMine;
  final bool canDelete;
  final int? fileSizeKb;
  final String? createdAt;

  const GalleryPhotoItem({
    required this.id,
    required this.url,
    required this.thumbnailUrl,
    this.caption,
    required this.uploaderName,
    required this.uploaderRole,
    required this.isMine,
    required this.canDelete,
    this.fileSizeKb,
    this.createdAt,
  });

  factory GalleryPhotoItem.fromJson(Map<String, dynamic> json) {
    final url = json['url'] as String? ?? '';
    return GalleryPhotoItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      url: url,
      thumbnailUrl: (json['thumbnail_url'] as String?) ?? url,
      caption: json['caption'] as String?,
      uploaderName: json['uploader_name'] as String? ?? 'Admin',
      uploaderRole: json['uploader_role'] as String? ?? 'admin',
      isMine: json['is_mine'] == true,
      canDelete: json['can_delete'] == true,
      fileSizeKb: (json['file_size_kb'] as num?)?.toInt(),
      createdAt: json['created_at'] as String?,
    );
  }
}

class GalleryReportItem {
  final int id;
  final String title;
  final String originalName;
  final int? fileSize;
  final String uploaderName;
  final String downloadUrl;

  const GalleryReportItem({
    required this.id,
    required this.title,
    required this.originalName,
    this.fileSize,
    required this.uploaderName,
    required this.downloadUrl,
  });

  factory GalleryReportItem.fromJson(Map<String, dynamic> json) {
    return GalleryReportItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: json['title'] as String? ?? '',
      originalName: json['original_name'] as String? ?? '',
      fileSize: (json['file_size'] as num?)?.toInt(),
      uploaderName: json['uploader_name'] as String? ?? 'Admin',
      downloadUrl: json['download_url'] as String? ?? '',
    );
  }
}

class GalleryDetailResult {
  final GalleryItem gallery;
  final List<GalleryPhotoItem> photos;
  final List<GalleryReportItem> reports;

  const GalleryDetailResult({
    required this.gallery,
    required this.photos,
    required this.reports,
  });
}
