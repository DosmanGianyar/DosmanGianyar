import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../models/announcement.dart';
import '../services/api_client.dart';
import '../theme/app_colors.dart';

class AnnouncementListScreen extends StatefulWidget {
  const AnnouncementListScreen({super.key});

  @override
  State<AnnouncementListScreen> createState() => _AnnouncementListScreenState();
}

class _AnnouncementListScreenState extends State<AnnouncementListScreen> {
  final List<AnnouncementItem> _items = [];
  final _scrollCtrl = ScrollController();

  bool   _isLoading   = true;
  bool   _isLoadingMore = false;
  bool   _hasMore     = true;
  int    _nextPage    = 1;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
    _scrollCtrl.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollCtrl.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollCtrl.position.pixels >= _scrollCtrl.position.maxScrollExtent - 200 &&
        !_isLoadingMore && _hasMore) {
      _loadMore();
    }
  }

  Future<void> _load() async {
    setState(() { _isLoading = true; _error = null; _items.clear(); _nextPage = 1; _hasMore = true; });
    try {
      final body = await ApiClient.get('/announcements/all', params: {'page': 1});
      if (!mounted) return;
      setState(() {
        _items.addAll((body['announcements'] as List)
            .map((e) => AnnouncementItem.fromJson(e as Map<String, dynamic>)));
        _hasMore  = body['has_more'] as bool? ?? false;
        _nextPage = (body['next_page'] as int?) ?? 2;
      });
    } catch (e) {
      if (mounted) setState(() => _error = ApiClient.extractError(e));
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _loadMore() async {
    if (_isLoadingMore || !_hasMore) return;
    setState(() => _isLoadingMore = true);
    try {
      final body = await ApiClient.get('/announcements/all', params: {'page': _nextPage});
      if (!mounted) return;
      setState(() {
        _items.addAll((body['announcements'] as List)
            .map((e) => AnnouncementItem.fromJson(e as Map<String, dynamic>)));
        _hasMore  = body['has_more'] as bool? ?? false;
        _nextPage = (body['next_page'] as int?) ?? _nextPage + 1;
      });
    } catch (_) {}
    finally { if (mounted) setState(() => _isLoadingMore = false); }
  }

  void _openDetail(AnnouncementItem item) {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => _AnnouncementDetailScreen(item: item)),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Semua Pengumuman',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 16)),
        backgroundColor: const Color(0xFF0F2460),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _ErrorView(message: _error!, onRetry: _load)
              : RefreshIndicator(
                  onRefresh: _load,
                  child: _items.isEmpty
                      ? const Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
                          Icon(Icons.campaign_outlined, size: 56, color: AppColors.gray300),
                          SizedBox(height: 12),
                          Text('Belum ada pengumuman',
                            style: TextStyle(fontSize: 13, color: AppColors.gray400)),
                        ]))
                      : ListView.builder(
                          controller: _scrollCtrl,
                          padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
                          itemCount: _items.length + (_hasMore ? 1 : 0),
                          itemBuilder: (_, i) {
                            if (i == _items.length) {
                              return const Padding(
                                padding: EdgeInsets.symmetric(vertical: 16),
                                child: Center(child: CircularProgressIndicator()),
                              );
                            }
                            return Padding(
                              padding: const EdgeInsets.only(bottom: 8),
                              child: _AnnouncementTile(
                                item: _items[i],
                                onTap: () => _openDetail(_items[i]),
                              ),
                            );
                          },
                        ),
                ),
    );
  }
}

// ─── Tile ─────────────────────────────────────────────────────────────────────

class _AnnouncementTile extends StatelessWidget {
  final AnnouncementItem item;
  final VoidCallback     onTap;
  const _AnnouncementTile({required this.item, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white, borderRadius: AppRadius.card,
          border: Border.all(
            color: item.isPinned ? const Color(0xFF0F2460).withValues(alpha: 0.25) : AppColors.gray100,
            width: item.isPinned ? 1.5 : 1,
          ),
          boxShadow: AppShadow.sm,
        ),
        padding: const EdgeInsets.all(14),
        child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Container(
            width: 36, height: 36,
            decoration: BoxDecoration(
              color: item.isPinned ? const Color(0xFF0F2460) : AppColors.gray100,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(
              item.isPinned ? Icons.push_pin_rounded : Icons.campaign_rounded,
              color: item.isPinned ? Colors.white : AppColors.gray500,
              size: 18,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            if (item.isPinned)
              const Padding(
                padding: EdgeInsets.only(bottom: 4),
                child: Text('DIPIN', style: TextStyle(
                  fontSize: 9, fontWeight: FontWeight.bold,
                  color: Color(0xFF0F2460), letterSpacing: 1)),
              ),
            Text(item.title,
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray800),
              maxLines: 2, overflow: TextOverflow.ellipsis),
            const SizedBox(height: 4),
            Text(item.body,
              style: const TextStyle(fontSize: 12, color: AppColors.gray500),
              maxLines: 2, overflow: TextOverflow.ellipsis),
            const SizedBox(height: 6),
            Row(children: [
              const Icon(Icons.person_outline_rounded, size: 11, color: AppColors.gray400),
              const SizedBox(width: 3),
              Text(item.authorName ?? 'Admin',
                style: const TextStyle(fontSize: 10, color: AppColors.gray400)),
              const Spacer(),
              Text(
                DateFormat('d MMM y', 'id_ID').format(item.publishedAt.toLocal()),
                style: const TextStyle(fontSize: 10, color: AppColors.gray400)),
            ]),
          ])),
          const SizedBox(width: 8),
          const Icon(Icons.chevron_right, color: AppColors.gray300, size: 16),
        ]),
      ),
    );
  }
}

// ─── Detail Screen ────────────────────────────────────────────────────────────

class _AnnouncementDetailScreen extends StatelessWidget {
  final AnnouncementItem item;
  const _AnnouncementDetailScreen({required this.item});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Pengumuman',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 16)),
        backgroundColor: const Color(0xFF0F2460),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Container(
          decoration: BoxDecoration(
            color: Colors.white, borderRadius: AppRadius.card,
            boxShadow: AppShadow.sm,
          ),
          padding: const EdgeInsets.all(20),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            if (item.isPinned) ...[
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFF0F2460),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: const Row(mainAxisSize: MainAxisSize.min, children: [
                  Icon(Icons.push_pin_rounded, size: 11, color: Colors.white),
                  SizedBox(width: 4),
                  Text('DIPIN', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.white)),
                ]),
              ),
              const SizedBox(height: 10),
            ],
            Text(item.title,
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.gray800, height: 1.4)),
            const SizedBox(height: 10),
            Row(children: [
              const Icon(Icons.person_outline_rounded, size: 13, color: AppColors.gray400),
              const SizedBox(width: 4),
              Text(item.authorName ?? 'Admin',
                style: const TextStyle(fontSize: 12, color: AppColors.gray500)),
              const SizedBox(width: 12),
              const Icon(Icons.access_time_rounded, size: 13, color: AppColors.gray400),
              const SizedBox(width: 4),
              Text(
                DateFormat('d MMMM y, HH:mm', 'id_ID').format(item.publishedAt.toLocal()),
                style: const TextStyle(fontSize: 12, color: AppColors.gray500)),
            ]),
            const SizedBox(height: 16),
            const Divider(color: AppColors.gray100),
            const SizedBox(height: 16),
            SelectableText(item.body,
              style: const TextStyle(fontSize: 14, color: AppColors.gray700, height: 1.6)),
          ]),
        ),
      ),
    );
  }
}

// ─── Error View ───────────────────────────────────────────────────────────────

class _ErrorView extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;
  const _ErrorView({required this.message, required this.onRetry});
  @override
  Widget build(BuildContext context) => Center(
    child: Column(mainAxisSize: MainAxisSize.min, children: [
      const Icon(Icons.error_outline_rounded, size: 48, color: AppColors.red500),
      const SizedBox(height: 8),
      Text(message, style: const TextStyle(fontSize: 13, color: AppColors.gray500), textAlign: TextAlign.center),
      const SizedBox(height: 12),
      TextButton(onPressed: onRetry, child: const Text('Coba Lagi')),
    ]),
  );
}
