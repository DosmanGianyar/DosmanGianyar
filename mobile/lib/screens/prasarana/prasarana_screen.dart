import 'package:flutter/material.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';
import 'library_screen.dart';
import 'library_visit_screen.dart';

class PrasaranaScreen extends StatefulWidget {
  const PrasaranaScreen({super.key});

  @override
  State<PrasaranaScreen> createState() => _PrasaranaScreenState();
}

class _PrasaranaScreenState extends State<PrasaranaScreen> {
  bool _loading = true;
  int _activeLoansCount = 0;
  int _returnedLoansCount = 0;
  int _damagePendingCount = 0;
  int _damageTotalCount = 0;

  List<Map<String, dynamic>> _loans = [];
  List<Map<String, dynamic>> _damageReports = [];

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    try {
      final summary = await ApiClient.get('/siswa/sarpras/summary');
      final loans   = await ApiClient.get('/siswa/sarpras/loans');
      final damage  = await ApiClient.get('/siswa/sarpras/damage-reports');

      setState(() {
        _activeLoansCount   = summary['active_loans'] ?? 0;
        _returnedLoansCount = summary['returned_loans'] ?? 0;
        _damagePendingCount = summary['damage_pending'] ?? 0;
        _damageTotalCount   = summary['damage_total'] ?? 0;

        _loans         = List<Map<String, dynamic>>.from(loans as List? ?? []);
        _damageReports = List<Map<String, dynamic>>.from(damage as List? ?? []);
        _loading       = false;
      });
    } catch (_) {
      setState(() => _loading = false);
    }
  }

  void _openCatalogAndBorrowModal() async {
    final result = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => const _BorrowAssetModal(),
    );

    if (result == true) {
      _loadData();
    }
  }

  void _openDamageReportModal() async {
    final result = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => const _DamageReportModal(),
    );

    if (result == true) {
      _loadData();
    }
  }

  @override
  Widget build(BuildContext context) {
    final now = DateTime.now();
    const months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const days = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    final dateStr = '${days[now.weekday]}, ${now.day} ${months[now.month]} ${now.year}';

    return RefreshIndicator(
      onRefresh: _loadData,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // ─── Header ──────────────────────────────────────────────────
            Container(
              decoration: BoxDecoration(
                gradient: AppColors.prasaranaGradient,
                borderRadius: AppRadius.card,
              ),
              padding: const EdgeInsets.all(16),
              child: Row(children: [
                Container(
                  width: 48, height: 48,
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.20),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: const Icon(Icons.business_rounded, color: Colors.white, size: 24),
                ),
                const SizedBox(width: 12),
                Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text(dateStr, style: const TextStyle(color: Color(0xFFDDD6FE), fontSize: 11)),
                  const Text('Sarana & Prasarana',
                    style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold, height: 1.3)),
                  const Text('SMA Negeri 1 Gianyar',
                    style: TextStyle(color: Color(0xFFDDD6FE), fontSize: 11)),
                ])),
              ]),
            ),
            const SizedBox(height: 12),

            // ─── Stats 4-grid ─────────────────────────────────────────────
            Row(children: [
              Expanded(child: _StatCard(value: '$_activeLoansCount',   label: 'Pinjaman Aktif',    color: AppColors.violet600)),
              const SizedBox(width: 10),
              Expanded(child: _StatCard(value: '$_returnedLoansCount', label: 'Sudah Dikembalikan', color: AppColors.gray600)),
            ]),
            const SizedBox(height: 10),
            Row(children: [
              Expanded(child: _StatCard(value: '$_damagePendingCount', label: 'Laporan Diproses',  color: AppColors.orange600)),
              const SizedBox(width: 10),
              Expanded(child: _StatCard(value: '$_damageTotalCount',   label: 'Total Laporan',     color: AppColors.blue600)),
            ]),
            const SizedBox(height: 14),

            // ─── Tombol Aksi ──────────────────────────────────────────────
            Row(children: [
              Expanded(
                child: GestureDetector(
                  onTap: _openCatalogAndBorrowModal,
                  child: Container(
                    decoration: BoxDecoration(
                      gradient: AppColors.prasaranaGradient,
                      borderRadius: AppRadius.card,
                    ),
                    padding: const EdgeInsets.all(14),
                    child: Row(children: [
                      Container(
                        width: 38, height: 38,
                        decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.20), borderRadius: BorderRadius.circular(12)),
                        child: const Icon(Icons.add_shopping_cart_rounded, color: Colors.white, size: 20),
                      ),
                      const SizedBox(width: 10),
                      const Expanded(
                        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                          Text('Pinjam Barang', style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold)),
                          Text('Browse katalog aset', style: TextStyle(color: Color(0xFFDDD6FE), fontSize: 9.5)),
                        ]),
                      ),
                    ]),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: GestureDetector(
                  onTap: _openDamageReportModal,
                  child: Container(
                    decoration: BoxDecoration(
                      color: AppColors.orange50,
                      borderRadius: AppRadius.card,
                      border: Border.all(color: AppColors.orange200),
                    ),
                    padding: const EdgeInsets.all(14),
                    child: Row(children: [
                      Container(
                        width: 38, height: 38,
                        decoration: BoxDecoration(color: AppColors.orange100, borderRadius: BorderRadius.circular(12)),
                        child: const Icon(Icons.report_problem_rounded, color: AppColors.orange600, size: 20),
                      ),
                      const SizedBox(width: 10),
                      const Expanded(
                        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                          Text('Lapor Kerusakan', style: TextStyle(color: AppColors.orange900, fontSize: 12, fontWeight: FontWeight.bold)),
                          Text('Fasilitas / Kelas', style: TextStyle(color: AppColors.orange700, fontSize: 9.5)),
                        ]),
                      ),
                    ]),
                  ),
                ),
              ),
            ]),
            const SizedBox(height: 10),

            // Tombol Kunjungan Perpustakaan (Membaca di Tempat)
            GestureDetector(
              onTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const LibraryVisitScreen()),
                );
              },
              child: Container(
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF1D4ED8), Color(0xFF4338CA)],
                    begin: Alignment.centerLeft,
                    end: Alignment.centerRight,
                  ),
                  borderRadius: AppRadius.card,
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF1D4ED8).withValues(alpha: 0.25),
                      blurRadius: 8,
                      offset: const Offset(0, 3),
                    ),
                  ],
                ),
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                child: Row(
                  children: [
                    Container(
                      width: 42,
                      height: 42,
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.20),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.qr_code_scanner_rounded, color: Color(0xFFFDE047), size: 22),
                    ),
                    const SizedBox(width: 12),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Text('KUNJUNGAN PERPUSTAKAAN', style: TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold)),
                              SizedBox(width: 6),
                              Icon(Icons.verified_rounded, color: Color(0xFFFDE047), size: 14),
                            ],
                          ),
                          SizedBox(height: 2),
                          Text('Presensi membaca di tempat (Scan QR SIMAK DOSMAN)', style: TextStyle(color: Color(0xFFDBEAFE), fontSize: 10)),
                        ],
                      ),
                    ),
                    const Icon(Icons.chevron_right_rounded, color: Colors.white, size: 22),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 18),

            // ─── List Pinjaman Saya ─────────────────────────────────────────
            const _SectionHeader(title: 'Riwayat Pinjaman Saya'),
            const SizedBox(height: 8),
            if (_loading)
              const Center(child: Padding(padding: EdgeInsets.all(24), child: CircularProgressIndicator()))
            else if (_loans.isEmpty)
              _EmptyCard(
                icon: Icons.swap_horiz_rounded,
                iconColor: AppColors.violet500,
                message: 'Belum ada pinjaman barang',
                action: 'Pinjam Aset Sekolah',
                actionColor: AppColors.violet600,
                onAction: _openCatalogAndBorrowModal,
              )
            else
              ..._loans.map((loan) {
                final String status = loan['status'] ?? 'pending';
                final Color badgeBg = matchStatusBg(status);
                final Color badgeFg = matchStatusFg(status);

                return Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppColors.gray200),
                    boxShadow: AppShadow.sm,
                  ),
                  padding: const EdgeInsets.all(12),
                  child: Row(
                    children: [
                      Container(
                        width: 36, height: 36,
                        decoration: BoxDecoration(color: AppColors.violet50, borderRadius: BorderRadius.circular(10)),
                        child: const Icon(Icons.inventory_2_outlined, color: AppColors.violet600, size: 18),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(loan['asset_name'] ?? '—', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.gray900)),
                            Text('Tgl Pinjam: ${loan['loan_date']}  ·  Kembali: ${loan['return_date']}', style: const TextStyle(fontSize: 11, color: AppColors.gray500)),
                          ],
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(color: badgeBg, borderRadius: BorderRadius.circular(12)),
                        child: Text(loan['status_label'] ?? status, style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: badgeFg)),
                      ),
                    ],
                  ),
                );
              }),

            const SizedBox(height: 18),

            // ─── List Laporan Kerusakan ────────────────────────────────────
            const _SectionHeader(title: 'Laporan Kerusakan Saya'),
            const SizedBox(height: 8),
            if (_loading)
              const Center(child: Padding(padding: EdgeInsets.all(24), child: CircularProgressIndicator()))
            else if (_damageReports.isEmpty)
              _EmptyCard(
                icon: Icons.warning_amber_rounded,
                iconColor: AppColors.orange500,
                message: 'Belum ada laporan kerusakan',
                action: 'Buat Laporan Kerusakan',
                actionColor: AppColors.orange600,
                onAction: _openDamageReportModal,
              )
            else
              ..._damageReports.map((rep) {
                final String status = rep['status'] ?? 'pending';
                return Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppColors.gray200),
                    boxShadow: AppShadow.sm,
                  ),
                  padding: const EdgeInsets.all(12),
                  child: Row(
                    children: [
                      Container(
                        width: 36, height: 36,
                        decoration: BoxDecoration(color: AppColors.orange50, borderRadius: BorderRadius.circular(10)),
                        child: const Icon(Icons.report_problem_outlined, color: AppColors.orange600, size: 18),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(rep['title'] ?? '—', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.gray900)),
                            Text('Lokasi: ${rep['location']}  ·  ${rep['date']}', style: const TextStyle(fontSize: 11, color: AppColors.gray500)),
                          ],
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: status == 'resolved' ? AppColors.emerald50 : AppColors.orange50,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          rep['status_label'] ?? status,
                          style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: status == 'resolved' ? AppColors.emerald700 : AppColors.orange700),
                        ),
                      ),
                    ],
                  ),
                );
              }),
          ],
        ),
      ),
    );
  }

  Color matchStatusBg(String s) {
    switch (s) {
      case 'approved': case 'active': return AppColors.blue50;
      case 'returned': return AppColors.emerald50;
      case 'rejected': return AppColors.red50;
      default: return AppColors.amber50;
    }
  }

  Color matchStatusFg(String s) {
    switch (s) {
      case 'approved': case 'active': return AppColors.blue700;
      case 'returned': return AppColors.emerald700;
      case 'rejected': return AppColors.red700;
      default: return AppColors.amber700;
    }
  }
}

// ─── Modal Pinjam Barang ──────────────────────────────────────────────────────
class _BorrowAssetModal extends StatefulWidget {
  const _BorrowAssetModal();

  @override
  State<_BorrowAssetModal> createState() => _BorrowAssetModalState();
}

class _BorrowAssetModalState extends State<_BorrowAssetModal> {
  bool _loading = true;
  bool _submitting = false;
  List<Map<String, dynamic>> _assets = [];
  int? _selectedAssetId;
  DateTime _loanDate   = DateTime.now();
  DateTime _returnDate = DateTime.now().add(const Duration(days: 1));
  final _notesCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _fetchCatalog();
  }

  Future<void> _fetchCatalog() async {
    try {
      final res = await ApiClient.get('/siswa/sarpras/catalog');
      final list = (res['assets'] as List? ?? []);
      setState(() {
        _assets  = List<Map<String, dynamic>>.from(list);
        _loading = false;
      });
    } catch (_) {
      setState(() => _loading = false);
    }
  }

  Future<void> _submit() async {
    if (_selectedAssetId == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Pilih barang terlebih dahulu')));
      return;
    }

    setState(() => _submitting = true);
    try {
      await ApiClient.post('/siswa/sarpras/loans', data: {
        'asset_id': _selectedAssetId,
        'loan_date': _loanDate.toIso8601String().substring(0, 10),
        'return_date': _returnDate.toIso8601String().substring(0, 10),
        'notes': _notesCtrl.text,
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Pengajuan peminjaman berhasil dikirim!'), backgroundColor: AppColors.emerald600));
        Navigator.pop(context, true);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e'), backgroundColor: AppColors.red600));
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
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(context).viewInsets.bottom + 20),
      child: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            Row(
              children: [
                const Text('Form Pinjam Barang Sekolah', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                const Spacer(),
                IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(context)),
              ],
            ),
            const SizedBox(height: 12),
            if (_loading)
              const Center(child: CircularProgressIndicator())
            else ...[
              const Text('Pilih Barang', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.gray700)),
              const SizedBox(height: 6),
              DropdownButtonFormField<int>(
                value: _selectedAssetId,
                decoration: InputDecoration(
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                ),
                hint: const Text('— Pilih Barang / Aset —'),
                items: _assets.map((a) {
                  return DropdownMenuItem<int>(
                    value: a['id'] as int,
                    child: Text('${a['name']} (${a['room_name']})', style: const TextStyle(fontSize: 12)),
                  );
                }).toList(),
                onChanged: (val) => setState(() => _selectedAssetId = val),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _notesCtrl,
                decoration: InputDecoration(
                  labelText: 'Keperluan / Catatan Pinjam',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.violet600,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  onPressed: _submitting ? null : _submit,
                  child: _submitting ? const CircularProgressIndicator(color: Colors.white) : const Text('KIRIM PENGAJUAN PINJAMAN'),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

// ─── Modal Lapor Kerusakan ───────────────────────────────────────────────────
class _DamageReportModal extends StatefulWidget {
  const _DamageReportModal();

  @override
  State<_DamageReportModal> createState() => _DamageReportModalState();
}

class _DamageReportModalState extends State<_DamageReportModal> {
  bool _submitting = false;
  final _titleCtrl = TextEditingController();
  final _descCtrl  = TextEditingController();

  Future<void> _submit() async {
    if (_titleCtrl.text.trim().isEmpty || _descCtrl.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Lengkapi judul dan deskripsi kerusakan')));
      return;
    }

    setState(() => _submitting = true);
    try {
      await ApiClient.post('/siswa/sarpras/damage-reports', data: {
        'title': _titleCtrl.text.trim(),
        'description': _descCtrl.text.trim(),
      });

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Laporan kerusakan berhasil dikirim!'), backgroundColor: AppColors.emerald600));
        Navigator.pop(context, true);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e'), backgroundColor: AppColors.red600));
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
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(context).viewInsets.bottom + 20),
      child: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            Row(
              children: [
                const Text('Lapor Kerusakan Fasilitas', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                const Spacer(),
                IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(context)),
              ],
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _titleCtrl,
              decoration: InputDecoration(
                labelText: 'Judul Laporan / Barang Rusak',
                hintText: 'Misal: Proyektor Kelas X MIPA 1 Mati',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _descCtrl,
              maxLines: 3,
              decoration: InputDecoration(
                labelText: 'Deskripsi Kerusakan / Lokasi',
                hintText: 'Jelaskan kronologi atau kondisi barang yang rusak...',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
              ),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.orange600,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                onPressed: _submitting ? null : _submit,
                child: _submitting ? const CircularProgressIndicator(color: Colors.white) : const Text('KIRIM LAPORAN KERUSAKAN'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _StatCard extends StatelessWidget {
  final String value;
  final String label;
  final Color  color;
  const _StatCard({required this.value, required this.label, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: AppRadius.card,
        border: Border.all(color: AppColors.gray100),
        boxShadow: AppShadow.sm,
      ),
      padding: const EdgeInsets.all(14),
      child: Column(children: [
        Text(value, style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color)),
        const SizedBox(height: 4),
        Text(label, style: const TextStyle(fontSize: 10.5, color: AppColors.gray500), textAlign: TextAlign.center),
      ]),
    );
  }
}

class _SectionHeader extends StatelessWidget {
  final String title;
  const _SectionHeader({required this.title});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(left: 4),
      child: Text(title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.gray700)),
    );
  }
}

class _EmptyCard extends StatelessWidget {
  final IconData icon;
  final Color    iconColor;
  final String   message;
  final String?  action;
  final Color?   actionColor;
  final VoidCallback? onAction;

  const _EmptyCard({
    required this.icon, required this.iconColor, required this.message,
    this.action, this.actionColor, this.onAction,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: AppRadius.card,
        border: Border.all(color: AppColors.gray100),
        boxShadow: AppShadow.sm,
      ),
      padding: const EdgeInsets.symmetric(vertical: 24),
      child: Column(children: [
        Icon(icon, size: 28, color: iconColor.withValues(alpha: 0.40)),
        const SizedBox(height: 8),
        Text(message, style: const TextStyle(fontSize: 13, color: AppColors.gray400)),
        if (action != null) ...[
          const SizedBox(height: 6),
          GestureDetector(
            onTap: onAction,
            child: Text(action!, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: actionColor ?? AppColors.blue600)),
          ),
        ],
      ]),
    );
  }
}
