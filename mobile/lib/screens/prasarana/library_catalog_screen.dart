import 'package:flutter/material.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';
import 'library_screen.dart';

class LibraryCatalogScreen extends StatefulWidget {
  const LibraryCatalogScreen({super.key});

  @override
  State<LibraryCatalogScreen> createState() => _LibraryCatalogScreenState();
}

class _LibraryCatalogScreenState extends State<LibraryCatalogScreen> {
  final TextEditingController _searchCtrl = TextEditingController();
  
  bool _loading = true;
  String _selectedCategory = 'all';
  List<Map<String, dynamic>> _books = [];

  final Map<String, String> _categories = const {
    'all': 'Semua Kategori',
    'Pelajaran': 'Pelajaran',
    'Fiksi': 'Fiksi & Novel',
    'Non-Fiksi': 'Non-Fiksi',
    'Sains': 'Sains & Teknologi',
    'Sejarah': 'Sejarah',
    'Agama': 'Agama',
    'Referensi': 'Referensi',
    'Umum': 'Umum',
  };

  @override
  void initState() {
    super.initState();
    _loadBooks();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadBooks() async {
    setState(() => _loading = true);
    try {
      final queryParams = <String, String>{};
      if (_searchCtrl.text.trim().isNotEmpty) {
        queryParams['search'] = _searchCtrl.text.trim();
      }
      if (_selectedCategory != 'all') {
        queryParams['category'] = _selectedCategory;
      }

      final res = await ApiClient.get('/siswa/library/catalog', params: queryParams);
      setState(() {
        _books   = List<Map<String, dynamic>>.from(res['data'] as List? ?? []);
        _loading = false;
      });
    } catch (_) {
      setState(() => _loading = false);
    }
  }

  void _showBookDetail(Map<String, dynamic> b) {
    final available = (b['available_stock'] as num? ?? 0).toInt();
    final total     = (b['total_stock'] as num? ?? 0).toInt();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => DraggableScrollableSheet(
        initialChildSize: 0.85,
        minChildSize: 0.5,
        maxChildSize: 0.95,
        expand: false,
        builder: (_, scrollCtrl) => Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          padding: const EdgeInsets.all(20),
          child: ListView(
            controller: scrollCtrl,
            children: [
              Center(
                child: Container(
                  width: 40, height: 4,
                  decoration: BoxDecoration(color: AppColors.slate200, borderRadius: BorderRadius.circular(2)),
                ),
              ),
              const SizedBox(height: 16),
              
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(12),
                    child: Container(
                      width: 100,
                      height: 140,
                      color: AppColors.slate100,
                      child: Image.network(
                        b['cover_url'] ?? '',
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => const Icon(Icons.book_rounded, size: 40, color: AppColors.slate400),
                      ),
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: const Color(0xFFEEF2FF),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            b['category'] ?? 'Umum',
                            style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.indigo700),
                          ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          b['title'] ?? 'Judul Tak Diketahui',
                          style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.slate900, height: 1.2),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Penulis: ${b['author'] ?? '—'}',
                          style: const TextStyle(fontSize: 12, color: AppColors.slate600),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Terbit: ${b['publish_year'] ?? '—'} · ${b['publisher'] ?? ''}',
                          style: const TextStyle(fontSize: 11, color: AppColors.slate400),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // Info Stock & Rack Grid
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.slate50,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: AppColors.slate200),
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: Column(children: [
                        const Text('KODE BUKU', style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: AppColors.slate400)),
                        const SizedBox(height: 2),
                        Text(b['book_code'] ?? '—', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.slate800)),
                      ]),
                    ),
                    Container(width: 1, height: 28, color: AppColors.slate200),
                    Expanded(
                      child: Column(children: [
                        const Text('LOKASI RAK', style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: AppColors.slate400)),
                        const SizedBox(height: 2),
                        Text('📍 ${b['shelf_location'] ?? 'Rak Umum'}', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.indigo700)),
                      ]),
                    ),
                    Container(width: 1, height: 28, color: AppColors.slate200),
                    Expanded(
                      child: Column(children: [
                        const Text('STOK TERSEDIA', style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: AppColors.slate400)),
                        const SizedBox(height: 2),
                        Text(
                          available > 0 ? '$available / $total' : 'Dipinjam Semua',
                          style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: available > 0 ? AppColors.emerald500 : AppColors.red500),
                        ),
                      ]),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              const Text('Sinopsis / Deskripsi Buku:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.slate900)),
              const SizedBox(height: 6),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.slate50,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.slate200),
                ),
                child: Text(
                  (b['description'] != null && b['description'].toString().trim().isNotEmpty)
                      ? b['description']
                      : 'Belum ada penjelasan sinopsis untuk buku ini.',
                  style: const TextStyle(fontSize: 12, color: AppColors.slate700, height: 1.4),
                ),
              ),
              const SizedBox(height: 20),

              Row(
                children: [
                  Expanded(
                    flex: 2,
                    child: ElevatedButton.icon(
                      onPressed: available > 0
                          ? () {
                              Navigator.pop(ctx);
                              showModalBottomSheet(
                                context: context,
                                isScrollControlled: true,
                                backgroundColor: Colors.transparent,
                                builder: (_) => BorrowBookModal(initialBook: b),
                              );
                            }
                          : null,
                      icon: Icon(available > 0 ? Icons.auto_stories_rounded : Icons.block_rounded, size: 18),
                      label: Text(
                        available > 0 ? 'Pinjam Buku Ini' : 'Stok Habis (Dipinjam Semua)',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.indigo600,
                        disabledBackgroundColor: AppColors.slate300,
                        foregroundColor: Colors.white,
                        disabledForegroundColor: AppColors.slate500,
                        minimumSize: const Size.fromHeight(46),
                        elevation: available > 0 ? 2 : 0,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  OutlinedButton(
                    onPressed: () => Navigator.pop(ctx),
                    style: OutlinedButton.styleFrom(
                      minimumSize: const Size(80, 46),
                      foregroundColor: AppColors.slate700,
                      side: const BorderSide(color: AppColors.slate300),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: const Text('Tutup', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                  ),
                ],
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
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('E-Katalog Buku Perpustakaan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        backgroundColor: Colors.white,
        foregroundColor: AppColors.slate900,
        elevation: 0,
      ),
      body: Column(
        children: [
          // ─── Header Search & Category Filter ───────────────────────────
          Container(
            color: Colors.white,
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 12),
            child: Column(
              children: [
                TextField(
                  controller: _searchCtrl,
                  onSubmitted: (_) => _loadBooks(),
                  decoration: InputDecoration(
                    hintText: 'Cari Judul, Pengarang, ISBN...',
                    hintStyle: const TextStyle(fontSize: 12, color: AppColors.slate400),
                    prefixIcon: const Icon(Icons.search_rounded, size: 20, color: AppColors.slate400),
                    suffixIcon: _searchCtrl.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear_rounded, size: 18),
                            onPressed: () {
                              _searchCtrl.clear();
                              _loadBooks();
                            },
                          )
                        : null,
                    filled: true,
                    fillColor: AppColors.slate50,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.slate200)),
                    enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.slate200)),
                  ),
                ),
                const SizedBox(height: 10),

                // Category Chips
                SizedBox(
                  height: 34,
                  child: ListView.separated(
                    scrollDirection: Axis.horizontal,
                    itemCount: _categories.length,
                    separatorBuilder: (_, __) => const SizedBox(width: 6),
                    itemBuilder: (context, index) {
                      final key = _categories.keys.elementAt(index);
                      final label = _categories.values.elementAt(index);
                      final isSelected = _selectedCategory == key;

                      return ChoiceChip(
                        label: Text(label, style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: isSelected ? Colors.white : AppColors.slate700)),
                        selected: isSelected,
                        selectedColor: AppColors.indigo700,
                        backgroundColor: AppColors.slate50,
                        onSelected: (bool selected) {
                          if (selected) {
                            setState(() => _selectedCategory = key);
                            _loadBooks();
                          }
                        },
                      );
                    },
                  ),
                ),
              ],
            ),
          ),

          // ─── Grid View Buku ───────────────────────────────────────────
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _books.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: const [
                            Icon(Icons.menu_book_rounded, size: 56, color: AppColors.slate400),
                            SizedBox(height: 12),
                            Text('Tidak ada buku ditemukan', style: TextStyle(fontSize: 13, color: AppColors.slate500, fontWeight: FontWeight.bold)),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: _loadBooks,
                        child: GridView.builder(
                          padding: const EdgeInsets.all(16),
                          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 2,
                            childAspectRatio: 0.58,
                            crossAxisSpacing: 12,
                            mainAxisSpacing: 12,
                          ),
                          itemCount: _books.length,
                          itemBuilder: (context, index) {
                            final b = _books[index];
                            final available = (b['available_stock'] as num? ?? 0).toInt();

                            return GestureDetector(
                              onTap: () => _showBookDetail(b),
                              child: Container(
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(16),
                                  border: Border.all(color: AppColors.slate200),
                                  boxShadow: [
                                    BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 6, offset: const Offset(0, 2)),
                                  ],
                                ),
                                clipBehavior: Clip.antiAlias,
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Expanded(
                                      child: Stack(
                                        children: [
                                          Positioned.fill(
                                            child: Container(
                                              color: AppColors.slate100,
                                              child: Image.network(
                                                b['cover_url'] ?? '',
                                                fit: BoxFit.cover,
                                                errorBuilder: (_, __, ___) => const Center(
                                                  child: Icon(Icons.book_rounded, size: 36, color: AppColors.slate400),
                                                ),
                                              ),
                                            ),
                                          ),
                                          Positioned(
                                            top: 6, left: 6,
                                            child: Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                              decoration: BoxDecoration(
                                                color: Colors.black.withValues(alpha: 0.6),
                                                borderRadius: BorderRadius.circular(4),
                                              ),
                                              child: Text(
                                                b['category'] ?? 'Umum',
                                                style: const TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Colors.white),
                                              ),
                                            ),
                                          ),
                                          Positioned(
                                            bottom: 6, right: 6,
                                            child: Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                              decoration: BoxDecoration(
                                                color: available > 0 ? AppColors.emerald500 : AppColors.red500,
                                                borderRadius: BorderRadius.circular(4),
                                              ),
                                              child: Text(
                                                available > 0 ? 'Stok ($available)' : 'Habis',
                                                style: const TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Colors.white),
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                    Padding(
                                      padding: const EdgeInsets.all(10),
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            b['title'] ?? '',
                                            maxLines: 2,
                                            overflow: TextOverflow.ellipsis,
                                            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.slate900, height: 1.2),
                                          ),
                                          const SizedBox(height: 2),
                                          Text(
                                            b['author'] ?? 'Penulis tak diketahui',
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                            style: const TextStyle(fontSize: 10, color: AppColors.slate500),
                                          ),
                                          const SizedBox(height: 6),
                                          Row(
                                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                            children: [
                                              Text(
                                                '📍 ${b['shelf_location'] ?? 'Rak Umum'}',
                                                style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.bold, color: AppColors.indigo700),
                                              ),
                                              const Icon(Icons.arrow_forward_ios_rounded, size: 10, color: AppColors.slate400),
                                            ],
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }
}
