import 'package:dio/dio.dart';
import '../models/gallery.dart';
import 'api_client.dart';

class GalleryService {
  GalleryService._();

  static Future<List<GalleryItem>> getGalleries() async {
    final res = await ApiClient.get('/galleries');
    final list = (res['data'] as List<dynamic>?) ?? const [];
    return list
        .whereType<Map<String, dynamic>>()
        .map(GalleryItem.fromJson)
        .toList();
  }

  static Future<GalleryDetailResult> getGalleryDetail(int galleryId) async {
    final res = await ApiClient.get('/galleries/$galleryId');
    final data = (res['data'] as Map<String, dynamic>?) ?? const {};
    final gallery = GalleryItem.fromJson(
      (data['gallery'] as Map<String, dynamic>?) ?? const {},
    );
    final photos = ((data['photos'] as List<dynamic>?) ?? const [])
        .whereType<Map<String, dynamic>>()
        .map(GalleryPhotoItem.fromJson)
        .toList();
    final reports = ((data['reports'] as List<dynamic>?) ?? const [])
        .whereType<Map<String, dynamic>>()
        .map(GalleryReportItem.fromJson)
        .toList();

    return GalleryDetailResult(
      gallery: gallery,
      photos: photos,
      reports: reports,
    );
  }

  static Future<({List<GalleryPhotoItem> photos, int uploadedCount, int maxPerUser, int remainingQuota, String message})>
      uploadPhotos({
    required int galleryId,
    required List<String> filePaths,
    String? caption,
  }) async {
    final multipartFiles = <MultipartFile>[];
    for (final path in filePaths) {
      final fileName = path.split(RegExp(r'[/\\]')).last;
      multipartFiles.add(
        await MultipartFile.fromFile(path, filename: fileName),
      );
    }

    final map = <String, dynamic>{
      'photos[]': multipartFiles,
    };
    if (caption != null && caption.trim().isNotEmpty) {
      map['caption'] = caption.trim();
    }

    final formData = FormData.fromMap(map);
    final res = await ApiClient.postForm('/galleries/$galleryId/photos', formData);
    final data = (res['data'] as Map<String, dynamic>?) ?? const {};
    final photos = ((data['photos'] as List<dynamic>?) ?? const [])
        .whereType<Map<String, dynamic>>()
        .map(GalleryPhotoItem.fromJson)
        .toList();

    return (
      photos: photos,
      uploadedCount: (data['my_uploaded_count'] as num?)?.toInt() ?? 0,
      maxPerUser: (data['max_photos_per_user'] as num?)?.toInt() ?? 10,
      remainingQuota: (data['remaining_quota'] as num?)?.toInt() ?? 0,
      message: res['message'] as String? ?? 'Foto berhasil diunggah.',
    );
  }

  static Future<void> deletePhoto({
    required int galleryId,
    required int photoId,
  }) async {
    await ApiClient.delete('/galleries/$galleryId/photos/$photoId');
  }
}
