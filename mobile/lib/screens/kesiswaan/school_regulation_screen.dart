import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../models/school_regulation.dart';
import '../../providers/regulation_provider.dart';
import '../../theme/app_colors.dart';

/// Halaman Tata Tertib Siswa SMAN 1 Gianyar
/// Tampilan Modern & Interaktif dengan:
/// - Showcase Contoh Seragam Sekolah Harian (Senin - Sabtu, PJOK & Luar KBM)
/// - Preview Foto Pakaian Sekolah Fullscreen / Zoom
/// - HTML Rich Text Renderer dari Text Editor Admin
/// - Filter Kategori, Live Search, dan Salin Teks
class SchoolRegulationScreen extends StatefulWidget {
  const SchoolRegulationScreen({super.key});

  @override
  State<SchoolRegulationScreen> createState() => _SchoolRegulationScreenState();
}

class _SchoolRegulationScreenState extends State<SchoolRegulationScreen> {
  final TextEditingController _searchCtrl = TextEditingController();
  String _searchQuery = '';
  String _selectedCategory = 'all'; // 'all' atau nama category
  final Set<int> _expandedIds = {};
  bool _expandAll = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final p = context.read<RegulationProvider>();
      if (p.groups.isEmpty && !p.isLoading) {
        p.fetch();
      }
    });
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  void _toggleExpandAll(List<RegulationItem> allItems) {
    setState(() {
      _expandAll = !_expandAll;
      if (_expandAll) {
        _expandedIds.addAll(allItems.map((e) => e.id));
      } else {
        _expandedIds.clear();
      }
    });
  }

  Future<void> _openPdf(String? url) async {
    if (url == null || url.isEmpty) return;
    try {
      final uri = Uri.parse(url);
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      }
    } catch (_) {}
  }

  void _showImagePreview(BuildContext context, String imageUrl, String title) {
    showDialog(
      context: context,
      builder: (_) => Dialog(
        backgroundColor: Colors.transparent,
        insetPadding: const EdgeInsets.all(16),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Align(
              alignment: Alignment.topRight,
              child: IconButton(
                onPressed: () => Navigator.pop(context),
                icon: const Icon(Icons.close_rounded, color: Colors.white, size: 28),
                tooltip: 'Tutup',
              ),
            ),
            ClipRRect(
              borderRadius: BorderRadius.circular(16),
              child: Container(
                color: Colors.white,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    InteractiveViewer(
                      clipBehavior: Clip.none,
                      minScale: 1.0,
                      maxScale: 4.0,
                      child: Image.network(
                        imageUrl,
                        fit: BoxFit.contain,
                        errorBuilder: (_, __, ___) => const Padding(
                          padding: EdgeInsets.all(32),
                          child: Icon(Icons.broken_image_rounded, size: 48, color: AppColors.gray400),
                        ),
                      ),
                    ),
                    Padding(
                      padding: const EdgeInsets.all(12),
                      child: Text(
                        title,
                        textAlign: TextAlign.center,
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                          color: AppColors.gray900,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showUniformDetail(BuildContext context, UniformGuide guide) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => DraggableScrollableSheet(
        initialChildSize: 0.85,
        minChildSize: 0.5,
        maxChildSize: 0.95,
        builder: (_, scrollController) => Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          child: Column(
            children: [
              // Handle Drag
              Center(
                child: Container(
                  margin: const EdgeInsets.only(top: 12, bottom: 8),
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: AppColors.gray300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              Expanded(
                child: ListView(
                  controller: scrollController,
                  padding: const EdgeInsets.fromLTRB(20, 8, 20, 24),
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: const Color(0xFF206BC4),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            guide.day.toUpperCase(),
                            style: const TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w800,
                              color: Colors.white,
                              letterSpacing: 0.3,
                            ),
                          ),
                        ),
                        const Spacer(),
                        IconButton(
                          onPressed: () => Navigator.pop(context),
                          icon: const Icon(Icons.close_rounded, color: AppColors.gray500),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Text(
                      guide.title,
                      style: const TextStyle(
                        fontSize: 17,
                        fontWeight: FontWeight.w800,
                        color: AppColors.gray900,
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Foto Contoh Seragam
                    if (guide.imageUrl != null && guide.imageUrl!.isNotEmpty)
                      GestureDetector(
                        onTap: () => _showImagePreview(context, guide.imageUrl!, guide.title),
                        child: Container(
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: AppColors.gray200),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.04),
                                blurRadius: 10,
                                offset: const Offset(0, 3),
                              ),
                            ],
                          ),
                          clipBehavior: Clip.antiAlias,
                          child: Stack(
                            alignment: Alignment.bottomRight,
                            children: [
                              Image.network(
                                guide.imageUrl!,
                                width: double.infinity,
                                height: 260,
                                fit: BoxFit.cover,
                                errorBuilder: (_, __, ___) => Container(
                                  height: 200,
                                  color: AppColors.slate100,
                                  child: const Center(
                                    child: Icon(Icons.checkroom_rounded, size: 48, color: AppColors.gray400),
                                  ),
                                ),
                              ),
                              Container(
                                margin: const EdgeInsets.all(10),
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                decoration: BoxDecoration(
                                  color: Colors.black.withValues(alpha: 0.7),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: const Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(Icons.zoom_in_rounded, size: 14, color: Colors.white),
                                    SizedBox(width: 4),
                                    Text(
                                      'Ketuk untuk Perbesar',
                                      style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),

                    const SizedBox(height: 18),
                    const Text(
                      'KETENTUAN & RINCIAN ATRIBUT:',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF206BC4),
                        letterSpacing: 0.5,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: AppColors.slate50,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: AppColors.gray200),
                      ),
                      child: _FormattedContent(content: guide.content),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF6F8FB),
      body: Consumer<RegulationProvider>(
        builder: (_, p, __) {
          if (p.isLoading) {
            return const Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircularProgressIndicator(color: Color(0xFF206BC4)),
                  SizedBox(height: 16),
                  Text(
                    'Memuat Tata Tertib Sekolah...',
                    style: TextStyle(color: AppColors.gray600, fontSize: 13),
                  ),
                ],
              ),
            );
          }

          if (p.error != null) {
            return Center(child: _ErrorView(p.error!, onRetry: p.fetch));
          }

          if (p.groups.isEmpty) {
            return const Center(child: _EmptyView());
          }

          final allItems = <RegulationItem>[];
          for (final g in p.groups) {
            allItems.addAll(g.items);
          }

          // Filter berdasarkan pencarian dan kategori yang dipilih
          final filteredGroups = <RegulationGroup>[];
          for (final g in p.groups) {
            if (_selectedCategory != 'all' && g.category != _selectedCategory) {
              continue;
            }

            final matchingItems = g.items.where((item) {
              if (_searchQuery.isEmpty) return true;
              final q = _searchQuery.toLowerCase();
              return item.title.toLowerCase().contains(q) ||
                  item.content.toLowerCase().contains(q) ||
                  (item.badgeText ?? '').toLowerCase().contains(q) ||
                  g.categoryLabel.toLowerCase().contains(q);
            }).toList();

            if (matchingItems.isNotEmpty) {
              filteredGroups.add(RegulationGroup(
                category: g.category,
                categoryLabel: g.categoryLabel,
                items: matchingItems,
              ));
            }
          }

          return CustomScrollView(
            physics: const AlwaysScrollableScrollPhysics(
              parent: BouncingScrollPhysics(),
            ),
            slivers: [
              // ─── Header Modern SMAN 1 Gianyar ─────────────────────────────
              SliverAppBar(
                expandedHeight: 180,
                pinned: true,
                elevation: 0,
                backgroundColor: const Color(0xFF182433),
                leading: IconButton(
                  icon: const Icon(Icons.arrow_back_rounded, color: Colors.white),
                  onPressed: () => Navigator.pop(context),
                  tooltip: 'Kembali',
                ),
                actions: [
                  if (p.pdfUrl != null && p.pdfUrl!.isNotEmpty)
                    IconButton(
                      icon: const Icon(Icons.picture_as_pdf_rounded, color: Colors.white),
                      tooltip: 'Lihat Scan Dokumen PDF Resmi',
                      onPressed: () => _openPdf(p.pdfUrl),
                    ),
                  IconButton(
                    icon: Icon(
                      _expandAll ? Icons.unfold_less_rounded : Icons.unfold_more_rounded,
                      color: Colors.white,
                    ),
                    tooltip: _expandAll ? 'Tutup Semua' : 'Buka Semua',
                    onPressed: () => _toggleExpandAll(allItems),
                  ),
                ],
                flexibleSpace: FlexibleSpaceBar(
                  background: Container(
                    decoration: const BoxDecoration(
                      gradient: LinearGradient(
                        colors: [Color(0xFF182433), Color(0xFF206BC4)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                    ),
                    padding: const EdgeInsets.fromLTRB(20, 60, 20, 20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.end,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(7),
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.15),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: const Icon(Icons.gavel_rounded, color: Colors.white, size: 20),
                            ),
                            const SizedBox(width: 10),
                            const Text(
                              'SMAN 1 GIANYAR',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: Color(0xFF93C5FD),
                                letterSpacing: 1.2,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'Tata Tertib & Seragam Sekolah',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w800,
                            color: Colors.white,
                            letterSpacing: -0.2,
                          ),
                        ),
                        const SizedBox(height: 4),
                        const Text(
                          'Pedoman disiplin, etika, dan ketentuan pakaian resmi',
                          style: TextStyle(fontSize: 12, color: Color(0xFFE2E8F0)),
                        ),
                      ],
                    ),
                  ),
                ),
              ),

              // ─── Search Bar ───────────────────────────────────────────────
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(16, 14, 16, 10),
                  child: Container(
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: AppColors.gray200),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.02),
                          blurRadius: 8,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: TextField(
                      controller: _searchCtrl,
                      onChanged: (v) => setState(() => _searchQuery = v.trim()),
                      decoration: InputDecoration(
                        hintText: 'Cari tata tertib, seragam, atau kata kunci...',
                        hintStyle: const TextStyle(fontSize: 13, color: AppColors.gray400),
                        prefixIcon: const Icon(Icons.search_rounded, color: AppColors.gray400, size: 20),
                        suffixIcon: _searchQuery.isNotEmpty
                            ? IconButton(
                                icon: const Icon(Icons.clear_rounded, size: 18, color: AppColors.gray400),
                                onPressed: () {
                                  _searchCtrl.clear();
                                  setState(() => _searchQuery = '');
                                },
                              )
                            : null,
                        border: InputBorder.none,
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                    ),
                  ),
                ),
              ),

              // ─── Showcase Contoh Seragam Sekolah (Jika Ada) ────────────────
              if (p.uniformGuides.isNotEmpty && _selectedCategory != 'larangan' && _selectedCategory != 'kehadiran')
                SliverToBoxAdapter(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Padding(
                        padding: const EdgeInsets.fromLTRB(18, 8, 18, 10),
                        child: Row(
                          children: [
                            Container(
                              width: 26,
                              height: 26,
                              decoration: BoxDecoration(
                                color: const Color(0xFFEBF3FE),
                                borderRadius: BorderRadius.circular(7),
                              ),
                              child: const Icon(Icons.checkroom_rounded, color: Color(0xFF206BC4), size: 16),
                            ),
                            const SizedBox(width: 8),
                            const Text(
                              'PANDUAN PAKAIAN & SERAGAM SISWA',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w800,
                                color: Color(0xFF182433),
                                letterSpacing: 0.5,
                              ),
                            ),
                            const Spacer(),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                              decoration: BoxDecoration(
                                color: const Color(0xFFEBF3FE),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                '${p.uniformGuides.length} Hari',
                                style: const TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFF206BC4),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                      SizedBox(
                        height: 215,
                        child: ListView.separated(
                          padding: const EdgeInsets.symmetric(horizontal: 16),
                          scrollDirection: Axis.horizontal,
                          itemCount: p.uniformGuides.length,
                          separatorBuilder: (_, __) => const SizedBox(width: 12),
                          itemBuilder: (ctx, idx) {
                            final guide = p.uniformGuides[idx];
                            return _UniformCard(
                              guide: guide,
                              onTap: () => _showUniformDetail(context, guide),
                            );
                          },
                        ),
                      ),
                      const SizedBox(height: 14),
                    ],
                  ),
                ),

              // ─── Filter Kategori Horizontal Chips ─────────────────────────
              SliverToBoxAdapter(
                child: SizedBox(
                  height: 40,
                  child: ListView.separated(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    scrollDirection: Axis.horizontal,
                    itemCount: p.groups.length + 1,
                    separatorBuilder: (_, __) => const SizedBox(width: 8),
                    itemBuilder: (_, index) {
                      if (index == 0) {
                        final isSelected = _selectedCategory == 'all';
                        return _FilterChip(
                          label: 'Semua (${allItems.length})',
                          icon: Icons.apps_rounded,
                          isSelected: isSelected,
                          activeColor: const Color(0xFF206BC4),
                          onTap: () => setState(() => _selectedCategory = 'all'),
                        );
                      }

                      final g = p.groups[index - 1];
                      final isSelected = _selectedCategory == g.category;
                      final meta = _CategoryTheme.get(g.category, g.categoryLabel);

                      return _FilterChip(
                        label: '${meta.shortLabel} (${g.items.length})',
                        icon: meta.icon,
                        isSelected: isSelected,
                        activeColor: meta.primaryColor,
                        onTap: () => setState(() => _selectedCategory = g.category),
                      );
                    },
                  ),
                ),
              ),

              const SliverToBoxAdapter(child: SizedBox(height: 14)),

              // ─── List Kategori & Kartu Peraturan ──────────────────────────
              if (filteredGroups.isEmpty)
                SliverFillRemaining(
                  hasScrollBody: false,
                  child: Center(
                    child: Padding(
                      padding: const EdgeInsets.all(32),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Container(
                            width: 60,
                            height: 60,
                            decoration: BoxDecoration(
                              color: AppColors.gray100,
                              borderRadius: BorderRadius.circular(16),
                            ),
                            child: const Icon(Icons.search_off_rounded, size: 30, color: AppColors.gray400),
                          ),
                          const SizedBox(height: 12),
                          const Text(
                            'Tidak ada peraturan cocok',
                            style: TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                              color: AppColors.gray800,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Tidak ditemukan kata kunci "$_searchQuery" pada tata tertib.',
                            textAlign: TextAlign.center,
                            style: const TextStyle(fontSize: 12, color: AppColors.gray500),
                          ),
                        ],
                      ),
                    ),
                  ),
                )
              else
                SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (_, groupIdx) {
                      final group = filteredGroups[groupIdx];
                      final meta = _CategoryTheme.get(group.category, group.categoryLabel);

                      return Padding(
                        padding: const EdgeInsets.fromLTRB(16, 4, 16, 16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Header Kategori
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(color: AppColors.gray200),
                              ),
                              child: Row(
                                children: [
                                  Container(
                                    width: 32,
                                    height: 32,
                                    decoration: BoxDecoration(
                                      color: meta.backgroundColor,
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Icon(meta.icon, size: 17, color: meta.primaryColor),
                                  ),
                                  const SizedBox(width: 10),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          group.categoryLabel,
                                          style: const TextStyle(
                                            fontSize: 13,
                                            fontWeight: FontWeight.bold,
                                            color: AppColors.gray900,
                                          ),
                                        ),
                                        Text(
                                          '${group.items.length} Ketentuan Tertulis',
                                          style: const TextStyle(
                                            fontSize: 11,
                                            color: AppColors.gray500,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            ),

                            const SizedBox(height: 10),

                            // Kartu-kartu Tata Tertib
                            ListView.separated(
                              shrinkWrap: true,
                              physics: const NeverScrollableScrollPhysics(),
                              itemCount: group.items.length,
                              separatorBuilder: (_, __) => const SizedBox(height: 10),
                              itemBuilder: (_, itemIdx) {
                                final item = group.items[itemIdx];
                                final isExpanded = _expandedIds.contains(item.id);

                                return _RegulationCard(
                                  item: item,
                                  itemNumber: itemIdx + 1,
                                  theme: meta,
                                  isExpanded: isExpanded,
                                  onImageTap: (url) => _showImagePreview(context, url, item.title),
                                  onToggle: () {
                                    setState(() {
                                      if (isExpanded) {
                                        _expandedIds.remove(item.id);
                                      } else {
                                        _expandedIds.add(item.id);
                                      }
                                    });
                                  },
                                );
                              },
                            ),
                          ],
                        ),
                      );
                    },
                    childCount: filteredGroups.length,
                  ),
                ),

              const SliverToBoxAdapter(child: SizedBox(height: 40)),
            ],
          );
        },
      ),
    );
  }
}

// ─── Uniform Showcase Card ──────────────────────────────────────────────────

class _UniformCard extends StatelessWidget {
  final UniformGuide guide;
  final VoidCallback onTap;

  const _UniformCard({
    required this.guide,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 180,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppColors.gray200),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.03),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        clipBehavior: Clip.antiAlias,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Preview Image
            Stack(
              children: [
                if (guide.imageUrl != null && guide.imageUrl!.isNotEmpty)
                  Image.network(
                    guide.imageUrl!,
                    height: 110,
                    width: double.infinity,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => Container(
                      height: 110,
                      color: const Color(0xFFF1F5F9),
                      child: const Center(
                        child: Icon(Icons.checkroom_rounded, color: AppColors.gray400, size: 36),
                      ),
                    ),
                  )
                else
                  Container(
                    height: 110,
                    color: const Color(0xFFF1F5F9),
                    child: const Center(
                      child: Icon(Icons.checkroom_rounded, color: AppColors.gray400, size: 36),
                    ),
                  ),

                // Badge Hari
                Positioned(
                  top: 8,
                  left: 8,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                    decoration: BoxDecoration(
                      color: const Color(0xFF182433).withValues(alpha: 0.85),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      guide.day,
                      style: const TextStyle(
                        fontSize: 9.5,
                        fontWeight: FontWeight.w800,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
              ],
            ),

            // Deskripsi Singkat
            Padding(
              padding: const EdgeInsets.all(10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    guide.title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: AppColors.gray900,
                      height: 1.25,
                    ),
                  ),
                  const SizedBox(height: 6),
                  const Row(
                    children: [
                      Text(
                        'Ketuk detail',
                        style: TextStyle(
                          fontSize: 10.5,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF206BC4),
                        ),
                      ),
                      SizedBox(width: 2),
                      Icon(Icons.arrow_forward_ios_rounded, size: 9, color: Color(0xFF206BC4)),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Filter Chip Component ──────────────────────────────────────────────────

class _FilterChip extends StatelessWidget {
  final String label;
  final IconData icon;
  final bool isSelected;
  final Color activeColor;
  final VoidCallback onTap;

  const _FilterChip({
    required this.label,
    required this.icon,
    required this.isSelected,
    required this.activeColor,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
        decoration: BoxDecoration(
          color: isSelected ? activeColor : Colors.white,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(
            color: isSelected ? activeColor : AppColors.gray200,
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              size: 14,
              color: isSelected ? Colors.white : AppColors.gray600,
            ),
            const SizedBox(width: 6),
            Text(
              label,
              style: TextStyle(
                fontSize: 11.5,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
                color: isSelected ? Colors.white : AppColors.gray800,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Regulation Card Component ──────────────────────────────────────────────

class _RegulationCard extends StatelessWidget {
  final RegulationItem item;
  final int itemNumber;
  final _CategoryTheme theme;
  final bool isExpanded;
  final void Function(String url) onImageTap;
  final VoidCallback onToggle;

  const _RegulationCard({
    required this.item,
    required this.itemNumber,
    required this.theme,
    required this.isExpanded,
    required this.onImageTap,
    required this.onToggle,
  });

  void _copyToClipboard(BuildContext context) {
    Clipboard.setData(ClipboardData(text: '${item.title}\n\n${item.content}'));
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.check_circle_rounded, color: Colors.white, size: 18),
            const SizedBox(width: 8),
            Expanded(child: Text('Teks peraturan "${item.title}" disalin.')),
          ],
        ),
        backgroundColor: const Color(0xFF182433),
        behavior: SnackBarBehavior.floating,
        duration: const Duration(seconds: 2),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: isExpanded ? const Color(0xFF206BC4).withValues(alpha: 0.4) : AppColors.gray200,
          width: isExpanded ? 1.5 : 1,
        ),
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // 3px top accent bar
          if (isExpanded) Container(height: 3, color: const Color(0xFF206BC4)),

          Material(
            color: Colors.transparent,
            child: InkWell(
              onTap: onToggle,
              child: Padding(
                padding: const EdgeInsets.all(14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Badge Nomor
                        Container(
                          width: 26,
                          height: 26,
                          decoration: BoxDecoration(
                            color: theme.backgroundColor,
                            borderRadius: BorderRadius.circular(7),
                          ),
                          child: Center(
                            child: Text(
                              itemNumber < 10 ? '0$itemNumber' : '$itemNumber',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: theme.primaryColor,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),

                        // Judul Aturan & Badge Hari
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              if (item.badgeText != null && item.badgeText!.isNotEmpty)
                                Padding(
                                  padding: const EdgeInsets.only(bottom: 3),
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFEBF3FE),
                                      borderRadius: BorderRadius.circular(5),
                                    ),
                                    child: Text(
                                      item.badgeText!,
                                      style: const TextStyle(
                                        fontSize: 9.5,
                                        fontWeight: FontWeight.bold,
                                        color: Color(0xFF206BC4),
                                      ),
                                    ),
                                  ),
                                ),
                              Text(
                                item.title,
                                style: const TextStyle(
                                  fontSize: 13.5,
                                  fontWeight: FontWeight.w700,
                                  color: AppColors.gray900,
                                  height: 1.35,
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 6),

                        // Indikator Panah Expand/Collapse
                        AnimatedRotation(
                          turns: isExpanded ? 0.5 : 0,
                          duration: const Duration(milliseconds: 200),
                          child: Container(
                            width: 24,
                            height: 24,
                            decoration: BoxDecoration(
                              color: AppColors.slate100,
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: const Icon(
                              Icons.keyboard_arrow_down_rounded,
                              size: 18,
                              color: AppColors.gray600,
                            ),
                          ),
                        ),
                      ],
                    ),

                    // Cuplikan Isi saat collapsed
                    if (!isExpanded) ...[
                      const SizedBox(height: 6),
                      Padding(
                        padding: const EdgeInsets.only(left: 36),
                        child: Text(
                          _cleanPlainText(item.content),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontSize: 12,
                            color: AppColors.gray500,
                            height: 1.4,
                          ),
                        ),
                      ),
                    ],

                    // Detail Isi Lengkap saat Expanded
                    if (isExpanded) ...[
                      const SizedBox(height: 12),

                      // Foto Lampiran Peraturan (Jika ada)
                      if (item.imageUrl != null && item.imageUrl!.isNotEmpty) ...[
                        GestureDetector(
                          onTap: () => onImageTap(item.imageUrl!),
                          child: Container(
                            margin: const EdgeInsets.only(bottom: 12),
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: AppColors.gray200),
                            ),
                            clipBehavior: Clip.antiAlias,
                            child: Stack(
                              alignment: Alignment.bottomRight,
                              children: [
                                Image.network(
                                  item.imageUrl!,
                                  width: double.infinity,
                                  height: 180,
                                  fit: BoxFit.cover,
                                  errorBuilder: (_, __, ___) => const SizedBox.shrink(),
                                ),
                                Container(
                                  margin: const EdgeInsets.all(8),
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: Colors.black.withValues(alpha: 0.65),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: const Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Icon(Icons.zoom_in_rounded, size: 12, color: Colors.white),
                                      SizedBox(width: 4),
                                      Text(
                                        'Perbesar Foto',
                                        style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],

                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: AppColors.gray200),
                        ),
                        child: _FormattedContent(content: item.content),
                      ),

                      const SizedBox(height: 10),

                      // Action Bar Mini: Tag Kategori & Tombol Salin
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: theme.backgroundColor,
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              theme.shortLabel,
                              style: TextStyle(
                                fontSize: 10.5,
                                fontWeight: FontWeight.w600,
                                color: theme.primaryColor,
                              ),
                            ),
                          ),
                          TextButton.icon(
                            onPressed: () => _copyToClipboard(context),
                            style: TextButton.styleFrom(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              minimumSize: Size.zero,
                              tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                              foregroundColor: AppColors.gray600,
                            ),
                            icon: const Icon(Icons.copy_rounded, size: 13),
                            label: const Text('Salin', style: TextStyle(fontSize: 11)),
                          ),
                        ],
                      ),
                    ],
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  String _cleanPlainText(String html) {
    return html
        .replaceAll(RegExp(r'<[^>]*>'), ' ')
        .replaceAll('&nbsp;', ' ')
        .replaceAll('&amp;', '&')
        .replaceAll(RegExp(r'\s+'), ' ')
        .trim();
  }
}

// ─── Formatter Isi Teks Rich Text HTML ──────────────────────────────────────

class _FormattedContent extends StatelessWidget {
  final String content;
  const _FormattedContent({required this.content});

  @override
  Widget build(BuildContext context) {
    // Jika tidak mengandung tag HTML, gunakan fallback parser baris
    if (!content.contains('<')) {
      final lines = content.split('\n').where((l) => l.trim().isNotEmpty).toList();
      return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: lines.map((line) {
          final trimmed = line.trim();
          final isNumbered = RegExp(r'^(\d+[\.\)]|[a-zA-Z][\.\)]|[-•])\s+').hasMatch(trimmed);

          return Padding(
            padding: const EdgeInsets.symmetric(vertical: 3),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (!isNumbered)
                  const Padding(
                    padding: EdgeInsets.only(top: 6, right: 8),
                    child: Icon(Icons.circle, size: 5, color: Color(0xFF206BC4)),
                  ),
                Expanded(
                  child: Text(
                    trimmed,
                    style: const TextStyle(
                      fontSize: 12.5,
                      color: AppColors.gray800,
                      height: 1.5,
                    ),
                  ),
                ),
              ],
            ),
          );
        }).toList(),
      );
    }

    // Parser HTML sederhana untuk tag <h2>, <h3>, <p>, <ul>, <ol>, <li>, <blockquote>
    final blocks = _parseHtmlBlocks(content);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: blocks.map((b) => b.toWidget(context)).toList(),
    );
  }

  List<_HtmlBlock> _parseHtmlBlocks(String html) {
    final blocks = <_HtmlBlock>[];
    final clean = html.trim();

    // Regex untuk memisahkan tag-tag blok utama
    final reg = RegExp(r'<(h2|h3|p|ul|ol|blockquote)[\s\S]*?<\/\1>', caseSensitive: false);
    final matches = reg.allMatches(clean);

    if (matches.isEmpty) {
      // Jika tidak cocok blok terstruktur, bersihkan dan jadikan paragraf
      blocks.add(_HtmlBlock(type: _HtmlType.paragraph, html: clean));
      return blocks;
    }

    int lastIndex = 0;
    for (final m in matches) {
      if (m.start > lastIndex) {
        final between = clean.substring(lastIndex, m.start).trim();
        if (between.isNotEmpty) {
          blocks.add(_HtmlBlock(type: _HtmlType.paragraph, html: between));
        }
      }

      final tag = m.group(1)!.toLowerCase();
      final fullMatch = m.group(0)!;

      if (tag == 'h2') {
        blocks.add(_HtmlBlock(type: _HtmlType.h2, html: fullMatch));
      } else if (tag == 'h3') {
        blocks.add(_HtmlBlock(type: _HtmlType.h3, html: fullMatch));
      } else if (tag == 'blockquote') {
        blocks.add(_HtmlBlock(type: _HtmlType.blockquote, html: fullMatch));
      } else if (tag == 'ul' || tag == 'ol') {
        blocks.add(_HtmlBlock(type: tag == 'ol' ? _HtmlType.orderedList : _HtmlType.bulletList, html: fullMatch));
      } else {
        blocks.add(_HtmlBlock(type: _HtmlType.paragraph, html: fullMatch));
      }

      lastIndex = m.end;
    }

    if (lastIndex < clean.length) {
      final rem = clean.substring(lastIndex).trim();
      if (rem.isNotEmpty) {
        blocks.add(_HtmlBlock(type: _HtmlType.paragraph, html: rem));
      }
    }

    return blocks;
  }
}

enum _HtmlType { h2, h3, paragraph, bulletList, orderedList, blockquote }

class _HtmlBlock {
  final _HtmlType type;
  final String html;

  _HtmlBlock({required this.type, required this.html});

  Widget toWidget(BuildContext context) {
    switch (type) {
      case _HtmlType.h2:
        return Padding(
          padding: const EdgeInsets.only(top: 8, bottom: 6),
          child: Text(
            _stripTags(html),
            style: const TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w800,
              color: Color(0xFF182433),
            ),
          ),
        );

      case _HtmlType.h3:
        return Padding(
          padding: const EdgeInsets.only(top: 6, bottom: 4),
          child: Text(
            _stripTags(html),
            style: const TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w700,
              color: Color(0xFF206BC4),
            ),
          ),
        );

      case _HtmlType.blockquote:
        return Container(
          margin: const EdgeInsets.symmetric(vertical: 6),
          padding: const EdgeInsets.fromLTRB(12, 8, 12, 8),
          decoration: const BoxDecoration(
            color: Color(0xFFEFF6FF),
            borderRadius: BorderRadius.all(Radius.circular(8)),
            border: Border(left: BorderSide(color: Color(0xFF206BC4), width: 3.5)),
          ),
          child: _renderInlineSpans(_stripOuterTag(html, 'blockquote')),
        );

      case _HtmlType.bulletList:
      case _HtmlType.orderedList:
        final liRegex = RegExp(r'<li[\s\S]*?<\/li>', caseSensitive: false);
        final lis = liRegex.allMatches(html).map((m) => m.group(0)!).toList();

        return Padding(
          padding: const EdgeInsets.symmetric(vertical: 4),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: List.generate(lis.length, (i) {
              final liContent = _stripOuterTag(lis[i], 'li');
              final isOrdered = type == _HtmlType.orderedList;

              return Padding(
                padding: const EdgeInsets.symmetric(vertical: 3),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (isOrdered)
                      Padding(
                        padding: const EdgeInsets.only(right: 6, top: 1),
                        child: Text(
                          '${i + 1}.',
                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF206BC4)),
                        ),
                      )
                    else
                      const Padding(
                        padding: EdgeInsets.only(top: 6, right: 8),
                        child: Icon(Icons.circle, size: 5, color: Color(0xFF206BC4)),
                      ),
                    Expanded(
                      child: _renderInlineSpans(liContent),
                    ),
                  ],
                ),
              );
            }),
          ),
        );

      case _HtmlType.paragraph:
        final content = _stripOuterTag(html, 'p');
        return Padding(
          padding: const EdgeInsets.symmetric(vertical: 3),
          child: _renderInlineSpans(content),
        );
    }
  }

  static String _stripTags(String s) {
    return s.replaceAll(RegExp(r'<[^>]*>'), '').trim();
  }

  static String _stripOuterTag(String s, String tag) {
    var out = s.trim();
    final openRegex = RegExp('^<$tag[^>]*>', caseSensitive: false);
    final closeRegex = RegExp('</$tag>\$', caseSensitive: false);
    out = out.replaceFirst(openRegex, '').replaceFirst(closeRegex, '').trim();
    return out;
  }

  static Widget _renderInlineSpans(String html) {
    final spans = <TextSpan>[];
    final tokenRegex = RegExp(r'(<(strong|b|em|i|u)>[\s\S]*?<\/\2>)|([^<]+)', caseSensitive: false);
    final matches = tokenRegex.allMatches(html);

    for (final m in matches) {
      final token = m.group(0)!;
      if (token.startsWith('<strong') || token.startsWith('<b')) {
        spans.add(TextSpan(
          text: _stripTags(token),
          style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.gray900),
        ));
      } else if (token.startsWith('<em') || token.startsWith('<i')) {
        spans.add(TextSpan(
          text: _stripTags(token),
          style: const TextStyle(fontStyle: FontStyle.italic, color: AppColors.gray800),
        ));
      } else if (token.startsWith('<u')) {
        spans.add(TextSpan(
          text: _stripTags(token),
          style: const TextStyle(decoration: TextDecoration.underline, color: AppColors.gray800),
        ));
      } else {
        spans.add(TextSpan(
          text: _stripTags(token),
          style: const TextStyle(color: AppColors.gray800),
        ));
      }
    }

    return Text.rich(
      TextSpan(children: spans),
      style: const TextStyle(fontSize: 12.5, height: 1.5),
    );
  }
}

// ─── Empty & Error Views ────────────────────────────────────────────────────

class _EmptyView extends StatelessWidget {
  const _EmptyView();

  @override
  Widget build(BuildContext context) {
    return const Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.menu_book_rounded, size: 52, color: AppColors.gray400),
          SizedBox(height: 14),
          Text(
            'Belum ada tata tertib aktif',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.bold,
              color: AppColors.gray700,
            ),
          ),
        ],
      ),
    );
  }
}

class _ErrorView extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;
  const _ErrorView(this.message, {required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.wifi_off_rounded, size: 48, color: AppColors.red500),
            const SizedBox(height: 14),
            Text(
              message,
              textAlign: TextAlign.center,
              style: const TextStyle(color: AppColors.gray600, fontSize: 13),
            ),
            const SizedBox(height: 16),
            FilledButton.icon(
              onPressed: onRetry,
              icon: const Icon(Icons.refresh_rounded, size: 16),
              label: const Text('Coba Lagi'),
              style: FilledButton.styleFrom(
                backgroundColor: const Color(0xFF206BC4),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Palette Tema Visual Kategori ───────────────────────────────────────────

class _CategoryTheme {
  final String shortLabel;
  final IconData icon;
  final Color primaryColor;
  final Color backgroundColor;
  final Color borderColor;

  const _CategoryTheme({
    required this.shortLabel,
    required this.icon,
    required this.primaryColor,
    required this.backgroundColor,
    required this.borderColor,
  });

  static _CategoryTheme get(String category, [String? fullLabel]) {
    switch (category.toLowerCase()) {
      case 'kehadiran':
        return const _CategoryTheme(
          shortLabel: 'Kehadiran',
          icon: Icons.access_time_filled_rounded,
          primaryColor: Color(0xFF206BC4), // Blue
          backgroundColor: Color(0xFFEBF3FE),
          borderColor: Color(0xFFBFDBFE),
        );
      case 'berpakaian':
        return const _CategoryTheme(
          shortLabel: 'Pakaian & Seragam',
          icon: Icons.checkroom_rounded,
          primaryColor: Color(0xFFD97706), // Amber
          backgroundColor: Color(0xFFFFFBEB),
          borderColor: Color(0xFFFDE68A),
        );
      case 'perilaku':
        return const _CategoryTheme(
          shortLabel: 'Tata Perilaku',
          icon: Icons.verified_user_rounded,
          primaryColor: Color(0xFF059669), // Emerald
          backgroundColor: Color(0xFFECFDF5),
          borderColor: Color(0xFFA7F3D0),
        );
      case 'larangan':
        return const _CategoryTheme(
          shortLabel: 'Larangan',
          icon: Icons.gpp_bad_rounded,
          primaryColor: Color(0xFFDC2626), // Red
          backgroundColor: Color(0xFFFEF2F2),
          borderColor: Color(0xFFFECACA),
        );
      case 'kerapian':
        return const _CategoryTheme(
          shortLabel: 'Kerapian',
          icon: Icons.content_cut_rounded,
          primaryColor: Color(0xFF0891B2), // Cyan
          backgroundColor: Color(0xFFECFEFF),
          borderColor: Color(0xFFA5F3FC),
        );
      case 'kbm':
        return const _CategoryTheme(
          shortLabel: 'Kegiatan Belajar',
          icon: Icons.school_rounded,
          primaryColor: Color(0xFF4F46E5), // Indigo
          backgroundColor: Color(0xFFEEF2FF),
          borderColor: Color(0xFFC7D2FE),
        );
      case 'ekstrakurikuler':
        return const _CategoryTheme(
          shortLabel: 'Ekstrakurikuler',
          icon: Icons.sports_soccer_rounded,
          primaryColor: Color(0xFFEA580C), // Orange
          backgroundColor: Color(0xFFFFF7ED),
          borderColor: Color(0xFFFED7AA),
        );
      case 'hak_kewajiban':
        return const _CategoryTheme(
          shortLabel: 'Hak & Kewajiban',
          icon: Icons.balance_rounded,
          primaryColor: Color(0xFF7C3AED), // Violet
          backgroundColor: Color(0xFFF5F3FF),
          borderColor: Color(0xFFDDD6FE),
        );
      default:
        return _CategoryTheme(
          shortLabel: fullLabel ?? 'Aturan',
          icon: Icons.article_rounded,
          primaryColor: const Color(0xFF475569),
          backgroundColor: const Color(0xFFF1F5F9),
          borderColor: const Color(0xFFCBD5E1),
        );
    }
  }
}
