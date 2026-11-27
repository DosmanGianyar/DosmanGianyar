import 'package:flutter/material.dart';
import '../../models/gallery.dart';
import '../../services/api_client.dart';
import '../../services/gallery_service.dart';
import '../../theme/app_colors.dart';
import 'gallery_detail_screen.dart';

class GalleryListScreen extends StatefulWidget {
  const GalleryListScreen({super.key});

  @override
  State<GalleryListScreen> createState() => _GalleryListScreenState();
}

class _GalleryListScreenState extends State<GalleryListScreen> {
  List<GalleryItem> _items = [];
  bool _loading = true;
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
      final list = await GalleryService.getGalleries();
      if (!mounted) return;
      setState(() {
        _items = list;
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.gray50,
      appBar: AppBar(
        title: const Text(
          'Galeri & Dokumentasi Kegiatan',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white),
        ),
        backgroundColor: const Color(0xFF0F2460),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? ListView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    children: [
                      const SizedBox(height: 80),
                      Center(
                        child: Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 24),
                          child: Column(
                            children: [
                              const Icon(Icons.cloud_off_rounded, size: 42, color: AppColors.gray400),
                              const SizedBox(height: 10),
                              Text(
                                _error!,
                                textAlign: TextAlign.center,
                                style: const TextStyle(fontSize: 13, color: AppColors.gray600),
                              ),
                              const SizedBox(height: 12),
                              FilledButton.icon(
                                onPressed: _load,
                                icon: const Icon(Icons.refresh_rounded, size: 18),
                                label: const Text('Coba Lagi'),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  )
                : _items.isEmpty
                    ? ListView(
                        physics: const AlwaysScrollableScrollPhysics(),
                        children: const [
                          SizedBox(height: 100),
                          Center(
                            child: Column(
                              children: [
                                Icon(Icons.photo_library_outlined, size: 48, color: AppColors.gray300),
                                SizedBox(height: 12),
                                Text(
                                  'Belum ada kegiatan dokumentasi.',
                                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: AppColors.gray600),
                                ),
                                SizedBox(height: 4),
                                Text(
                                  'Kegiatan baru hanya dibuat oleh Admin sekolah.',
                                  style: TextStyle(fontSize: 12, color: AppColors.gray400),
                                ),
                              ],
                            ),
                          ),
                        ],
                      )
                    : ListView.separated(
                        padding: const EdgeInsets.all(16),
                        itemCount: _items.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 12),
                        itemBuilder: (context, index) {
                          final item = _items[index];
                          return _GalleryCard(
                            item: item,
                            onTap: () async {
                              await Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => GalleryDetailScreen(galleryId: item.id, initialTitle: item.title),
                                ),
                              );
                              _load();
                            },
                          );
                        },
                      ),
      ),
    );
  }
}

class _GalleryCard extends StatelessWidget {
  final GalleryItem item;
  final VoidCallback onTap;

  const _GalleryCard({required this.item, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(14),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Container(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppColors.gray200),
          ),
          child: Row(
            children: [
              SizedBox(
                width: 96,
                height: 96,
                child: item.coverUrl != null && item.coverUrl!.isNotEmpty
                    ? Image.network(
                        item.coverUrl!,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => _placeholder(),
                      )
                    : _placeholder(),
              ),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.all(12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        item.title,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: AppColors.gray900,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          if (item.eventDateLabel != null) ...[
                            const Icon(Icons.calendar_today_rounded, size: 12, color: AppColors.gray400),
                            const SizedBox(width: 4),
                            Text(
                              item.eventDateLabel!,
                              style: const TextStyle(fontSize: 11.5, color: AppColors.gray500),
                            ),
                            const SizedBox(width: 10),
                          ],
                          const Icon(Icons.photo_outlined, size: 13, color: AppColors.gray400),
                          const SizedBox(width: 3),
                          Text(
                            '${item.photosCount} foto',
                            style: const TextStyle(fontSize: 11.5, color: AppColors.gray500),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      if (item.canUpload)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: AppColors.emerald50,
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: AppColors.emerald200),
                          ),
                          child: Text(
                            'Bisa Upload (Sisa ${item.remainingQuota}/${item.maxPhotosPerUser})',
                            style: const TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w700,
                              color: AppColors.emerald700,
                            ),
                          ),
                        )
                      else
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: AppColors.gray100,
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            item.isUploadOpen ? 'Hanya Lihat Galeri' : 'Upload Ditutup',
                            style: const TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w600,
                              color: AppColors.gray600,
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              ),
              const Padding(
                padding: EdgeInsets.only(right: 10),
                child: Icon(Icons.chevron_right_rounded, color: AppColors.gray400),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _placeholder() {
    return Container(
      color: AppColors.slate100,
      child: const Center(
        child: Icon(Icons.photo_library_outlined, color: AppColors.gray400, size: 28),
      ),
    );
  }
}
