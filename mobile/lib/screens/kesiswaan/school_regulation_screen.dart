import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../models/school_regulation.dart';
import '../../providers/regulation_provider.dart';
import '../../theme/app_colors.dart';

/// Halaman Tata Tertib Siswa SMAN 1 Gianyar
/// Tampilan Modern, Responsif, Interaktif dengan Filter Kategori,
/// Live Search, Visual Badges, dan Fitur Salin / Akses Scan PDF.
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: Consumer<RegulationProvider>(
        builder: (_, p, __) {
          if (p.isLoading) {
            return const Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircularProgressIndicator(color: AppColors.blue600),
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

          // Kumpulkan semua item dan hitung total
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

          final totalFilteredItems = filteredGroups.fold<int>(
            0,
            (acc, g) => acc + g.items.length,
          );

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
                backgroundColor: const Color(0xFF0F2460),
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
                    tooltip: _expandAll ? 'Tutup Semua Detail' : 'Buka Semua Detail',
                    onPressed: () => _toggleExpandAll(allItems),
                  ),
                ],
                flexibleSpace: FlexibleSpaceBar(
                  background: Container(
                    decoration: const BoxDecoration(
                      gradient: LinearGradient(
                        colors: [Color(0xFF0F2460), Color(0xFF1E3A8A), Color(0xFF312E81)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                    ),
                    child: SafeArea(
                      child: Padding(
                        padding: const EdgeInsets.fromLTRB(20, 52, 20, 16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          mainAxisAlignment: MainAxisAlignment.end,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: const Color.fromRGBO(255, 255, 255, 0.15),
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(
                                  color: const Color.fromRGBO(255, 255, 255, 0.25),
                                ),
                              ),
                              child: const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Text('📜', style: TextStyle(fontSize: 12)),
                                  SizedBox(width: 6),
                                  Text(
                                    'DOKUMEN RESMI SEKOLAH',
                                    style: TextStyle(
                                      color: Color(0xFFBFDBFE),
                                      fontSize: 10,
                                      fontWeight: FontWeight.w800,
                                      letterSpacing: 0.8,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 8),
                            const Text(
                              'Tata Tertib Peserta Didik',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 21,
                                fontWeight: FontWeight.w800,
                                letterSpacing: -0.3,
                              ),
                            ),
                            const SizedBox(height: 2),
                            const Text(
                              'SMA Negeri 1 Gianyar · Wajib ditaati seluruh siswa',
                              style: TextStyle(
                                color: Color(0xFFE2E8F0),
                                fontSize: 12.5,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
              ),

              // ─── Search Bar & Stat Bar ────────────────────────────────────
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 10),
                  child: Column(
                    children: [
                      // Search Input Box
                      Container(
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          boxShadow: const [
                            BoxShadow(
                              color: Color.fromRGBO(0, 0, 0, 0.04),
                              blurRadius: 10,
                              offset: Offset(0, 3),
                            ),
                          ],
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                        ),
                        child: TextField(
                          controller: _searchCtrl,
                          onChanged: (val) {
                            setState(() {
                              _searchQuery = val.trim();
                            });
                          },
                          decoration: InputDecoration(
                            hintText: 'Cari tata tertib (cth: seragam, rambut, telat)...',
                            hintStyle: const TextStyle(fontSize: 12.5, color: Color(0xFF94A3B8)),
                            prefixIcon: const Icon(Icons.search_rounded, color: AppColors.blue600, size: 22),
                            suffixIcon: _searchQuery.isNotEmpty
                                ? IconButton(
                                    icon: const Icon(Icons.clear_rounded, size: 18, color: Color(0xFF94A3B8)),
                                    onPressed: () {
                                      _searchCtrl.clear();
                                      setState(() => _searchQuery = '');
                                    },
                                  )
                                : null,
                            border: InputBorder.none,
                            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 13),
                          ),
                        ),
                      ),

                      const SizedBox(height: 12),

                      // Stat Pill Banner
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [Color(0xFFEFF6FF), Color(0xFFEEF2FF)],
                          ),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: const Color(0xFFDBEAFE)),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.verified_rounded, color: Color(0xFF2563EB), size: 18),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                _searchQuery.isEmpty
                                    ? 'Menampilkan ${allItems.length} peraturan dalam ${p.groups.length} kategori'
                                    : 'Ditemukan $totalFilteredItems peraturan yang cocok',
                                style: const TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w600,
                                  color: Color(0xFF1E3A8A),
                                ),
                              ),
                            ),
                            if (p.pdfUrl != null && p.pdfUrl!.isNotEmpty)
                              GestureDetector(
                                onTap: () => _openPdf(p.pdfUrl),
                                child: const Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Text(
                                      'PDF Resmi',
                                      style: TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.bold,
                                        color: Color(0xFF2563EB),
                                      ),
                                    ),
                                    SizedBox(width: 2),
                                    Icon(Icons.open_in_new_rounded, size: 13, color: Color(0xFF2563EB)),
                                  ],
                                ),
                              ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              // ─── Filter Kategori Horizontal Chips ─────────────────────────
              SliverToBoxAdapter(
                child: SizedBox(
                  height: 44,
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
                          activeColor: AppColors.blue600,
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

              const SliverToBoxAdapter(child: SizedBox(height: 12)),

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
                            width: 68,
                            height: 68,
                            decoration: BoxDecoration(
                              color: const Color(0xFFF1F5F9),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: const Icon(Icons.search_off_rounded, size: 36, color: Color(0xFF94A3B8)),
                          ),
                          const SizedBox(height: 14),
                          const Text(
                            'Tidak ada peraturan cocok',
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF334155),
                            ),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            'Tidak ditemukan kata kunci "$_searchQuery" pada tata tertib.',
                            textAlign: TextAlign.center,
                            style: const TextStyle(fontSize: 12.5, color: Color(0xFF64748B)),
                          ),
                          const SizedBox(height: 16),
                          FilledButton.icon(
                            onPressed: () {
                              _searchCtrl.clear();
                              setState(() {
                                _searchQuery = '';
                                _selectedCategory = 'all';
                              });
                            },
                            icon: const Icon(Icons.restart_alt_rounded, size: 16),
                            label: const Text('Reset Pencarian', style: TextStyle(fontSize: 12.5)),
                            style: FilledButton.styleFrom(
                              backgroundColor: AppColors.blue600,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
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
                        padding: const EdgeInsets.fromLTRB(16, 6, 16, 16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Header Kategori
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                              decoration: BoxDecoration(
                                color: meta.backgroundColor,
                                borderRadius: BorderRadius.circular(14),
                                border: Border.all(color: meta.borderColor),
                              ),
                              child: Row(
                                children: [
                                  Container(
                                    width: 32,
                                    height: 32,
                                    decoration: BoxDecoration(
                                      color: meta.primaryColor,
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                    child: Icon(meta.icon, size: 17, color: Colors.white),
                                  ),
                                  const SizedBox(width: 10),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          group.categoryLabel,
                                          style: TextStyle(
                                            fontSize: 14,
                                            fontWeight: FontWeight.bold,
                                            color: meta.primaryColor,
                                          ),
                                        ),
                                        Text(
                                          '${group.items.length} Ketentuan Khusus',
                                          style: TextStyle(
                                            fontSize: 11,
                                            color: meta.primaryColor.withValues(alpha: 0.8),
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
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? activeColor : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected ? activeColor : const Color(0xFFCBD5E1),
            width: 1.2,
          ),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: activeColor.withValues(alpha: 0.3),
                    blurRadius: 8,
                    offset: const Offset(0, 3),
                  ),
                ]
              : const [
                  BoxShadow(
                    color: Color.fromRGBO(0, 0, 0, 0.02),
                    blurRadius: 4,
                    offset: Offset(0, 2),
                  ),
                ],
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              size: 15,
              color: isSelected ? Colors.white : const Color(0xFF64748B),
            ),
            const SizedBox(width: 6),
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
                color: isSelected ? Colors.white : const Color(0xFF334155),
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
  final VoidCallback onToggle;

  const _RegulationCard({
    required this.item,
    required this.itemNumber,
    required this.theme,
    required this.isExpanded,
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
        backgroundColor: const Color(0xFF1E293B),
        behavior: SnackBarBehavior.floating,
        duration: const Duration(seconds: 2),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 250),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isExpanded ? theme.primaryColor.withValues(alpha: 0.35) : const Color(0xFFE2E8F0),
          width: isExpanded ? 1.5 : 1,
        ),
        boxShadow: [
          BoxShadow(
            color: isExpanded ? theme.primaryColor.withValues(alpha: 0.06) : const Color.fromRGBO(0, 0, 0, 0.03),
            blurRadius: isExpanded ? 12 : 6,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: Material(
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
                      width: 28,
                      height: 28,
                      decoration: BoxDecoration(
                        color: theme.backgroundColor,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: theme.borderColor),
                      ),
                      child: Center(
                        child: Text(
                          itemNumber < 10 ? '0$itemNumber' : '$itemNumber',
                          style: TextStyle(
                            fontSize: 11.5,
                            fontWeight: FontWeight.bold,
                            color: theme.primaryColor,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),

                    // Judul Aturan
                    Expanded(
                      child: Padding(
                        padding: const EdgeInsets.only(top: 3),
                        child: Text(
                          item.title,
                          style: const TextStyle(
                            fontSize: 14.5,
                            fontWeight: FontWeight.w700,
                            color: Color(0xFF1E293B),
                            height: 1.35,
                          ),
                        ),
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
                          color: const Color(0xFFF1F5F9),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Icon(
                          Icons.keyboard_arrow_down_rounded,
                          size: 18,
                          color: Color(0xFF64748B),
                        ),
                      ),
                    ),
                  ],
                ),

                // Cuplikan Isi (Preview singkat saat collapsed agar tidak terlalu sepi)
                if (!isExpanded) ...[
                  const SizedBox(height: 8),
                  Padding(
                    padding: const EdgeInsets.only(left: 38),
                    child: Text(
                      item.content,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 12.5,
                        color: Color(0xFF64748B),
                        height: 1.45,
                      ),
                    ),
                  ),
                ],

                // Detail Isi Lengkap saat Expanded
                if (isExpanded) ...[
                  const SizedBox(height: 12),
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(13),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
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
                          foregroundColor: const Color(0xFF64748B),
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
    );
  }
}

// ─── Formatter Isi Teks Agar Tidak Menumpuk Kering ───────────────────────────

class _FormattedContent extends StatelessWidget {
  final String content;
  const _FormattedContent({required this.content});

  @override
  Widget build(BuildContext context) {
    final lines = content.split('\n').where((l) => l.trim().isNotEmpty).toList();

    // Jika terdiri dari beberapa poin bernomor atau bullet
    if (lines.length > 1) {
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
                    child: Icon(Icons.circle, size: 5, color: Color(0xFF3B82F6)),
                  ),
                Expanded(
                  child: Text(
                    trimmed,
                    style: const TextStyle(
                      fontSize: 13,
                      color: Color(0xFF334155),
                      height: 1.55,
                    ),
                  ),
                ),
              ],
            ),
          );
        }).toList(),
      );
    }

    // Satu paragraf deskripsi
    return Text(
      content,
      style: const TextStyle(
        fontSize: 13,
        color: Color(0xFF334155),
        height: 1.6,
      ),
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
          Icon(Icons.menu_book_rounded, size: 52, color: Color(0xFF94A3B8)),
          SizedBox(height: 14),
          Text(
            'Belum ada tata tertib aktif',
            style: TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.bold,
              color: Color(0xFF475569),
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
            const Icon(Icons.wifi_off_rounded, size: 48, color: Color(0xFFEF4444)),
            const SizedBox(height: 14),
            Text(
              message,
              textAlign: TextAlign.center,
              style: const TextStyle(color: Color(0xFF64748B), fontSize: 13),
            ),
            const SizedBox(height: 16),
            FilledButton.icon(
              onPressed: onRetry,
              icon: const Icon(Icons.refresh_rounded, size: 16),
              label: const Text('Coba Lagi'),
              style: FilledButton.styleFrom(
                backgroundColor: AppColors.blue600,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
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
          primaryColor: Color(0xFF2563EB), // Blue
          backgroundColor: Color(0xFFEFF6FF),
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
