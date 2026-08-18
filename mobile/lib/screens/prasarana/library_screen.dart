import 'package:flutter/material.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';
import 'library_clearance_screen.dart';
import 'library_visit_screen.dart';
import 'library_catalog_screen.dart';

class LibraryScreen extends StatefulWidget {
  const LibraryScreen({super.key});

  @override
  State<LibraryScreen> createState() => _LibraryScreenState();
}

class _LibraryScreenState extends State<LibraryScreen> {
  final ScrollController _scrollController = ScrollController();

  bool _loading = true;
  bool _isClear = true;
  int _activeLoansCount = 0;
  int _returnedLoansCount = 0;
  List<Map<String, dynamic>> _loans = [];

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    try {
      final summaryRes = await ApiClient.get('/siswa/library/summary');
      final loansRes   = await ApiClient.get('/siswa/library/loans');

      setState(() {
        _isClear            = summaryRes['is_clear'] ?? true;
        _activeLoansCount   = summaryRes['active_loans_count'] ?? 0;
        _returnedLoansCount = summaryRes['returned_loans_count'] ?? 0;
        _loans              = List<Map<String, dynamic>>.from(loansRes['loans'] as List? ?? []);
        _loading            = false;
      });
    } catch (_) {
      setState(() => _loading = false);
    }
  }

  void _openBorrowModal() async {
    final result = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => const _BorrowBookModal(),
    );

    if (result == true) {
      await _loadData();

      // Scroll otomatis ke bagian Riwayat Peminjaman Buku
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (_scrollController.hasClients) {
          _scrollController.animateTo(
            _scrollController.position.maxScrollExtent,
            duration: const Duration(milliseconds: 600),
            curve: Curves.easeOutCubic,
          );
        }
      });
    }
  }

  void _openCatalogScreen() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const LibraryCatalogScreen()),
    );
  }

  void _openClearanceCard() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const LibraryClearanceScreen()),
    );
  }

  void _openVisitScreen() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const LibraryVisitScreen()),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Perpustakaan & Buku', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        backgroundColor: Colors.white,
        foregroundColor: AppColors.slate900,
        elevation: 0,
      ),
      body: RefreshIndicator(
        onRefresh: _loadData,
        child: SingleChildScrollView(
          controller: _scrollController,
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // ─── Header Banner ─────────────────────────────────────────
              Container(
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF1D4ED8), Color(0xFF4338CA), Color(0xFF6D28D9)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: AppRadius.card,
                ),
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    Container(
                      width: 48,
                      height: 48,
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.20),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: const Icon(Icons.menu_book_rounded, color: Color(0xFFFDE047), size: 26),
                    ),
                    const SizedBox(width: 12),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('PRASARANA SEKOLAH', style: TextStyle(color: Color(0xFFFEF08A), fontSize: 10, fontWeight: FontWeight.bold)),
                          Text('Perpustakaan & Peminjaman Buku',
                              style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold, height: 1.2)),
                          Text('SMA Negeri 1 Gianyar', style: TextStyle(color: Color(0xFFDBEAFE), fontSize: 11)),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),

              // ─── Card Status Bebas Perpustakaan ─────────────────────────
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: AppRadius.card,
                  border: Border.all(color: AppColors.slate200),
                ),
                padding: const EdgeInsets.all(14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Status Bebas Perpustakaan', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.slate700)),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: _isClear ? const Color(0xFFD1FAE5) : const Color(0xFFFFE4E6),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(
                            _isClear ? 'BEBAS PERPUSTAKAAN' : 'ADA PINJAMAN',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                              color: _isClear ? const Color(0xFF047857) : const Color(0xFFBE123C),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Text(
                      _isClear
                          ? '✓ Tidak ada penunggakan buku. Anda berhak mencetak Kartu Bebas Perpustakaan.'
                          : '⚠️ Terdapat $_activeLoansCount buku sedang dipinjam/belum dikembalikan.',
                      style: TextStyle(
                        fontSize: 11,
                        color: _isClear ? const Color(0xFF047857) : const Color(0xFFBE123C),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: ElevatedButton.icon(
                            onPressed: _openCatalogScreen,
                            icon: const Icon(Icons.grid_view_rounded, size: 15),
                            label: const Text('E-Katalog', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold)),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.indigo700,
                              foregroundColor: Colors.white,
                              elevation: 1,
                              padding: const EdgeInsets.symmetric(vertical: 11),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                          ),
                        ),
                        const SizedBox(width: 6),
                        Expanded(
                          child: ElevatedButton.icon(
                            onPressed: _openVisitScreen,
                            icon: const Icon(Icons.qr_code_scanner_rounded, size: 15),
                            label: const Text('Kunjungan', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold)),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF2563EB),
                              foregroundColor: Colors.white,
                              elevation: 1,
                              padding: const EdgeInsets.symmetric(vertical: 11),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                          ),
                        ),
                        const SizedBox(width: 6),
                        Expanded(
                          child: ElevatedButton.icon(
                            onPressed: _openClearanceCard,
                            icon: const Icon(Icons.qr_code_rounded, size: 15),
                            label: const Text('Kartu Bebas', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold)),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFFEEF2FF),
                              foregroundColor: AppColors.indigo700,
                              elevation: 0,
                              padding: const EdgeInsets.symmetric(vertical: 11),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),

              // ─── Stats Grid ─────────────────────────────────────────────
              Row(
                children: [
                  Expanded(
                    child: Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: AppRadius.card,
                        border: Border.all(color: AppColors.slate200),
                      ),
                      padding: const EdgeInsets.all(12),
                      child: Column(
                        children: [
                          Text('$_activeLoansCount', style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppColors.indigo600)),
                          const Text('Buku Dipinjam', style: TextStyle(fontSize: 11, color: AppColors.slate500)),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: AppRadius.card,
                        border: Border.all(color: AppColors.slate200),
                      ),
                      padding: const EdgeInsets.all(12),
                      child: Column(
                        children: [
                          Text('$_returnedLoansCount', style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Color(0xFF10B981))),
                          const Text('Sudah Dikembalikan', style: TextStyle(fontSize: 11, color: AppColors.slate500)),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // ─── Tombol Catat Peminjaman ─────────────────────────────────
              ElevatedButton.icon(
                onPressed: _openBorrowModal,
                icon: const Icon(Icons.add_rounded, size: 18),
                label: const Text('Catat Peminjaman Buku Baru', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.indigo600,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  elevation: 2,
                ),
              ),
              const SizedBox(height: 20),

              // ─── List Riwayat Buku ───────────────────────────────────────
              const Row(
                children: [
                  Icon(Icons.history_rounded, size: 18, color: AppColors.slate700),
                  SizedBox(width: 6),
                  Text('Riwayat Peminjaman Buku Saya', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.slate800)),
                ],
              ),
              const SizedBox(height: 10),

              if (_loading)
                const Center(child: Padding(padding: EdgeInsets.all(24), child: CircularProgressIndicator()))
              else if (_loans.isEmpty)
                Container(
                  padding: const EdgeInsets.all(24),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: AppRadius.card,
                    border: Border.all(color: AppColors.slate200),
                  ),
                  child: const Column(
                    children: [
                      Icon(Icons.library_books_outlined, size: 40, color: AppColors.slate400),
                      SizedBox(height: 8),
                      Text('Belum Ada Peminjaman Buku', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.slate700)),
                      Text('Gunakan tombol di atas untuk mencatat pinjaman buku perpustakaan.',
                          textAlign: TextAlign.center, style: TextStyle(fontSize: 11, color: AppColors.slate400)),
                    ],
                  ),
                )
              else
                ListView.separated(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: _loans.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 10),
                  itemBuilder: (context, index) {
                    final loan = _loans[index];
                    final status = loan['status']?.toString() ?? 'borrowed';
                    final isOverdue = loan['is_overdue'] == true;

                    Color statusBg = const Color(0xFFFEF3C7);
                    Color statusFg = const Color(0xFF92400E);

                    if (status == 'returned') {
                      statusBg = const Color(0xFFD1FAE5);
                      statusFg = const Color(0xFF065F46);
                    } else if (status == 'overdue' || isOverdue) {
                      statusBg = const Color(0xFFFFE4E6);
                      statusFg = const Color(0xFF991B1B);
                    }

                    return Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: AppRadius.card,
                        border: Border.all(color: AppColors.slate200),
                      ),
                      padding: const EdgeInsets.all(14),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Expanded(
                                child: Text(
                                  loan['book_title']?.toString() ?? 'Buku',
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.slate800),
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: statusBg,
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  loan['status_label']?.toString() ?? 'Dipinjam',
                                  style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: statusFg),
                                ),
                              ),
                            ],
                          ),
                          if ((loan['book_code'] != null && loan['book_code'].toString().isNotEmpty) ||
                              (loan['book_nisb'] != null && loan['book_nisb'].toString().isNotEmpty) ||
                              (loan['book_author'] != null && loan['book_author'].toString().isNotEmpty)) ...[
                            const SizedBox(height: 4),
                            Wrap(
                              spacing: 8,
                              runSpacing: 2,
                              children: [
                                if (loan['book_code'] != null && loan['book_code'].toString().isNotEmpty)
                                  Text('Kode: ${loan['book_code']}', style: const TextStyle(fontSize: 11, color: AppColors.slate500, fontFamily: 'monospace')),
                                if (loan['book_nisb'] != null && loan['book_nisb'].toString().isNotEmpty)
                                  Text('ISBN: ${loan['book_nisb']}', style: const TextStyle(fontSize: 11, color: AppColors.slate500, fontFamily: 'monospace')),
                                if (loan['book_author'] != null && loan['book_author'].toString().isNotEmpty)
                                  Text('Pengarang: ${loan['book_author']}', style: const TextStyle(fontSize: 11, color: AppColors.slate600, fontWeight: FontWeight.w500)),
                              ],
                            ),
                          ],
                          const Divider(height: 16),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text('Pinjam: ${loan['borrowed_at'] ?? '—'}', style: const TextStyle(fontSize: 11, color: AppColors.slate500)),
                              Text(
                                'Batas: ${loan['due_at'] ?? '—'}',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: isOverdue ? FontWeight.bold : FontWeight.normal,
                                  color: isOverdue ? const Color(0xFFDC2626) : AppColors.slate700,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    );
                  },
                ),
            ],
          ),
        ),
      ),
    );
  }
}

// ─── Bottom Sheet Modal Form Pinjam Buku ─────────────────────────────────────
class _BorrowBookModal extends StatefulWidget {
  const _BorrowBookModal();

  @override
  State<_BorrowBookModal> createState() => _BorrowBookModalState();
}

class _BorrowBookModalState extends State<_BorrowBookModal> {
  final _formKey = GlobalKey<FormState>();
  final _titleController  = TextEditingController();
  final _codeController   = TextEditingController();
  final _nisbController   = TextEditingController();
  final _authorController = TextEditingController();
  final _notesController  = TextEditingController();

  DateTime _borrowedAt = DateTime.now();
  DateTime _dueAt      = DateTime.now().add(const Duration(days: 7));
  bool _submitting = false;

  @override
  void dispose() {
    _titleController.dispose();
    _codeController.dispose();
    _nisbController.dispose();
    _authorController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _submitting = true);
    try {
      final res = await ApiClient.post(
        '/siswa/library/loans',
        data: {
          'book_title': _titleController.text.trim(),
          'book_code': _codeController.text.trim(),
          'book_nisb': _nisbController.text.trim(),
          'book_author': _authorController.text.trim(),
          'borrowed_at': _borrowedAt.toIso8601String().substring(0, 10),
          'due_at': _dueAt.toIso8601String().substring(0, 10),
          'notes': _notesController.text.trim(),
        },
      );

      if (mounted) {
        final message = res['message']?.toString() ?? 'Peminjaman buku berhasil dicatat.';
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(message),
            backgroundColor: const Color(0xFF059669),
            duration: const Duration(seconds: 4),
          ),
        );
        Navigator.pop(context, true);
      }
    } catch (e) {
      if (mounted) {
        String errorMsg = ApiClient.extractErrorMessage(e);
        if (errorMsg.contains('404')) {
          errorMsg = 'Endpoint Perpustakaan belum di-deploy pada server live (sims.sman1-gianyar.sch.id). Silakan lakukan update/deploy backend server terlebih dahulu.';
        }
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(errorMsg),
            backgroundColor: const Color(0xFFDC2626),
            duration: const Duration(seconds: 6),
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(context).viewInsets.bottom + 20),
      child: SingleChildScrollView(
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Catat Peminjaman Buku', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                  IconButton(
                    onPressed: () => Navigator.pop(context),
                    icon: const Icon(Icons.close_rounded),
                  ),
                ],
              ),
              const SizedBox(height: 12),

              // Judul Buku
              TextFormField(
                controller: _titleController,
                decoration: const InputDecoration(
                  labelText: 'Judul Buku *',
                  hintText: 'Contoh: Matematika Peminatan Kelas XII',
                  border: OutlineInputBorder(),
                ),
                validator: (val) => val == null || val.trim().isEmpty ? 'Judul buku wajib diisi' : null,
              ),
              const SizedBox(height: 12),

              // Pengarang Buku
              TextFormField(
                controller: _authorController,
                decoration: const InputDecoration(
                  labelText: 'Pengarang / Penulis Buku (Opsional)',
                  hintText: 'Contoh: Prof. Dr. Sukartha',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 12),

              // ISBN / Kode Buku Row
              Row(
                children: [
                  Expanded(
                    child: TextFormField(
                      controller: _nisbController,
                      decoration: const InputDecoration(
                        labelText: 'ISBN Buku (Opsional)',
                        hintText: 'Contoh: 978-602-123-4567',
                        border: OutlineInputBorder(),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: TextFormField(
                      controller: _codeController,
                      decoration: const InputDecoration(
                        labelText: 'No. Inventaris (Opsional)',
                        hintText: 'Contoh: BIB-2026-088',
                        border: OutlineInputBorder(),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),

              // Tanggal Pinjam & Batas
              Row(
                children: [
                  Expanded(
                    child: InkWell(
                      onTap: () async {
                        final picked = await showDatePicker(
                          context: context,
                          initialDate: _borrowedAt,
                          firstDate: DateTime(2025),
                          lastDate: DateTime(2030),
                        );
                        if (picked != null) setState(() => _borrowedAt = picked);
                      },
                      child: InputDecorator(
                        decoration: const InputDecoration(labelText: 'Tgl Pinjam', border: OutlineInputBorder()),
                        child: Text('${_borrowedAt.day}/${_borrowedAt.month}/${_borrowedAt.year}', style: const TextStyle(fontSize: 12)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: InkWell(
                      onTap: () async {
                        final picked = await showDatePicker(
                          context: context,
                          initialDate: _dueAt,
                          firstDate: _borrowedAt,
                          lastDate: DateTime(2030),
                        );
                        if (picked != null) setState(() => _dueAt = picked);
                      },
                      child: InputDecorator(
                        decoration: const InputDecoration(labelText: 'Batas Kembali', border: OutlineInputBorder()),
                        child: Text('${_dueAt.day}/${_dueAt.month}/${_dueAt.year}', style: const TextStyle(fontSize: 12)),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),



              // Catatan
              TextFormField(
                controller: _notesController,
                maxLines: 2,
                decoration: const InputDecoration(
                  labelText: 'Catatan (Opsional)',
                  hintText: 'Catatan kondisi buku...',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 16),

              ElevatedButton(
                onPressed: _submitting ? null : _submit,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.indigo600,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: _submitting
                    ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Text('Simpan Peminjaman', style: TextStyle(fontWeight: FontWeight.bold)),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
