import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../../models/gallery.dart';
import '../../services/api_client.dart';
import '../../services/gallery_service.dart';
import '../../theme/app_colors.dart';

class GalleryDetailScreen extends StatefulWidget {
  final int galleryId;
  final String initialTitle;

  const GalleryDetailScreen({
    super.key,
    required this.galleryId,
    required this.initialTitle,
  });

  @override
  State<GalleryDetailScreen> createState() => _GalleryDetailScreenState();
}

class _GalleryDetailScreenState extends State<GalleryDetailScreen> {
  GalleryDetailResult? _detail;
  bool _loading = true;
  bool _uploading = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final res = await GalleryService.getGalleryDetail(widget.galleryId);
      if (!mounted) return;
      setState(() {
        _detail = res;
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = ApiClient.extractError(e);
        _loading = false;
      });
    }
  }

  Future<void> _pickAndUpload() async {
    final g = _detail?.gallery;
    if (g == null || !g.canUpload) return;

    if (g.remainingQuota <= 0) {
      _showSnack('Kuota ${g.maxPhotosPerUser} foto Anda untuk kegiatan ini sudah penuh.', isError: true);
      return;
    }

    final picker = ImagePicker();
    final picked = await picker.pickMultiImage();
    if (picked.isEmpty || !mounted) return;

    if (picked.length > g.remainingQuota) {
      _showSnack(
        'Sisa kuota upload Anda hanya ${g.remainingQuota} foto lagi (maks ${g.maxPhotosPerUser} foto). Pilih ulang maksimal ${g.remainingQuota} foto.',
        isError: true,
      );
      return;
    }

    final captionCtrl = TextEditingController();
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text('Upload ${picked.length} Foto?', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Foto akan otomatis dikompres ke ukuran standar lihat dan disimpan ke arsip Synology.',
              style: TextStyle(fontSize: 12.5, color: Colors.grey[700]),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: captionCtrl,
              maxLength: 200,
              decoration: const InputDecoration(
                labelText: 'Keterangan Singkat (Opsional)',
                hintText: 'Contoh: Dokumentasi sesi pembukaan',
                border: OutlineInputBorder(),
                isDense: true,
              ),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          FilledButton.icon(
            onPressed: () => Navigator.pop(ctx, true),
            icon: const Icon(Icons.cloud_upload_rounded, size: 18),
            label: const Text('Upload Sekarang'),
          ),
        ],
      ),
    );
    final caption = captionCtrl.text.trim();
    captionCtrl.dispose();

    if (confirm != true || !mounted) return;

    setState(() => _uploading = true);
    try {
      final result = await GalleryService.uploadPhotos(
        galleryId: g.id,
        filePaths: picked.map((x) => x.path).toList(),
        caption: caption.isEmpty ? null : caption,
      );
      if (!mounted) return;
      _showSnack(result.message);
      await _load();
    } catch (e) {
      if (!mounted) return;
      _showSnack(ApiClient.extractError(e), isError: true);
    } finally {
      if (mounted) setState(() => _uploading = false);
    }
  }

  Future<void> _deletePhoto(GalleryPhotoItem photo) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Hapus Foto?', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
        content: const Text('Foto akan dihapus permanen dari galeri dan penyimpanan Synology.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: AppColors.red600),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );
    if (ok != true || !mounted) return;

    try {
      await GalleryService.deletePhoto(galleryId: widget.galleryId, photoId: photo.id);
      if (!mounted) return;
      _showSnack('Foto berhasil dihapus.');
      await _load();
    } catch (e) {
      if (!mounted) return;
      _showSnack(ApiClient.extractError(e), isError: true);
    }
  }

  void _openPreview(GalleryPhotoItem photo) {
    showDialog<void>(
      context: context,
      builder: (ctx) => Dialog(
        backgroundColor: Colors.black,
        insetPadding: const EdgeInsets.all(12),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Align(
              alignment: Alignment.topRight,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Padding(
                    padding: const EdgeInsets.only(left: 14),
                    child: Text(
                      'Oleh ${photo.uploaderName}',
                      style: const TextStyle(color: Colors.white70, fontSize: 12),
                    ),
                  ),
                  Row(
                    children: [
                      if (photo.canDelete)
                        IconButton(
                          icon: const Icon(Icons.delete_outline_rounded, color: Colors.redAccent),
                          onPressed: () {
                            Navigator.pop(ctx);
                            _deletePhoto(photo);
                          },
                        ),
                      IconButton(
                        icon: const Icon(Icons.close_rounded, color: Colors.white),
                        onPressed: () => Navigator.pop(ctx),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            Flexible(
              child: InteractiveViewer(
                child: Image.network(
                  photo.url,
                  fit: BoxFit.contain,
                  errorBuilder: (_, __, ___) => const Padding(
                    padding: EdgeInsets.all(40),
                    child: Icon(Icons.broken_image_outlined, color: Colors.white54, size: 48),
                  ),
                ),
              ),
            ),
            if (photo.caption != null && photo.caption!.isNotEmpty)
              Padding(
                padding: const EdgeInsets.all(12),
                child: Text(
                  photo.caption!,
                  style: const TextStyle(color: Colors.white, fontSize: 13),
                  textAlign: TextAlign.center,
                ),
              ),
          ],
        ),
      ),
    );
  }

  void _showSnack(String msg, {bool isError = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg),
        backgroundColor: isError ? AppColors.red600 : AppColors.emerald600,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final g = _detail?.gallery;
    final photos = _detail?.photos ?? const <GalleryPhotoItem>[];

    return Scaffold(
      backgroundColor: AppColors.gray50,
      appBar: AppBar(
        title: Text(
          g?.title ?? widget.initialTitle,
          style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Colors.white),
        ),
        backgroundColor: const Color(0xFF0F2460),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      floatingActionButton: (g != null && g.canUpload && g.remainingQuota > 0 && !_uploading)
          ? FloatingActionButton.extended(
              onPressed: _pickAndUpload,
              backgroundColor: const Color(0xFF0F2460),
              foregroundColor: Colors.white,
              icon: const Icon(Icons.add_a_photo_rounded),
              label: Text('Upload (${g.remainingQuota})'),
            )
          : null,
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(_error!, style: const TextStyle(color: AppColors.red600)),
                      const SizedBox(height: 10),
                      FilledButton(onPressed: _load, child: const Text('Muat Ulang')),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      if (_uploading) ...[
                        const LinearProgressIndicator(),
                        const SizedBox(height: 10),
                        const Text(
                          'Mengunggah & mengompres foto ke server Synology...',
                          style: TextStyle(fontSize: 12, color: AppColors.blue600, fontWeight: FontWeight.w600),
                        ),
                        const SizedBox(height: 12),
                      ],
                      if (g != null)
                        Container(
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: AppColors.gray200),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              if (g.description != null && g.description!.isNotEmpty) ...[
                                Text(g.description!, style: const TextStyle(fontSize: 13, color: AppColors.gray700)),
                                const SizedBox(height: 10),
                              ],
                              Row(
                                children: [
                                  Icon(
                                    g.canUpload ? Icons.cloud_upload_outlined : Icons.lock_outline_rounded,
                                    size: 16,
                                    color: g.canUpload ? AppColors.emerald600 : AppColors.gray500,
                                  ),
                                  const SizedBox(width: 6),
                                  Expanded(
                                    child: Text(
                                      g.canUpload
                                          ? 'Kuota upload Anda: ${g.myUploadedCount} / ${g.maxPhotosPerUser} foto (sisa ${g.remainingQuota})'
                                          : (g.isUploadOpen
                                              ? 'Mode lihat saja (untuk siswa, hanya akun terpilih yang dapat upload).'
                                              : 'Upload foto untuk kegiatan ini sudah ditutup Admin.'),
                                      style: TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.w600,
                                        color: g.canUpload ? AppColors.emerald700 : AppColors.gray600,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      const SizedBox(height: 14),
                      if (photos.isEmpty)
                        const Padding(
                          padding: EdgeInsets.symmetric(vertical: 48),
                          child: Center(
                            child: Text(
                              'Belum ada foto pada kegiatan ini.',
                              style: TextStyle(fontSize: 13, color: AppColors.gray500),
                            ),
                          ),
                        )
                      else
                        GridView.builder(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 3,
                            crossAxisSpacing: 8,
                            mainAxisSpacing: 8,
                          ),
                          itemCount: photos.length,
                          itemBuilder: (_, idx) {
                            final photo = photos[idx];
                            return GestureDetector(
                              onTap: () => _openPreview(photo),
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(10),
                                child: Stack(
                                  fit: StackFit.expand,
                                  children: [
                                    Image.network(
                                      photo.thumbnailUrl,
                                      fit: BoxFit.cover,
                                      errorBuilder: (_, __, ___) => Container(
                                        color: AppColors.gray200,
                                        child: const Icon(Icons.broken_image_outlined, color: AppColors.gray400),
                                      ),
                                    ),
                                    Positioned(
                                      left: 0,
                                      right: 0,
                                      bottom: 0,
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 3),
                                        color: Colors.black54,
                                        child: Text(
                                          photo.uploaderName,
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                          style: const TextStyle(color: Colors.white, fontSize: 9.5),
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
                      const SizedBox(height: 70),
                    ],
                  ),
                ),
    );
  }
}
