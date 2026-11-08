import 'package:flutter/material.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';

class GuruGateVerificationScreen extends StatefulWidget {
  const GuruGateVerificationScreen({super.key});

  @override
  State<GuruGateVerificationScreen> createState() =>
      _GuruGateVerificationScreenState();
}

class _GuruGateVerificationScreenState extends State<GuruGateVerificationScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isLoading = true;
  String? _error;

  String? _selectedMonth; // YYYY-MM (null = default bulan berjalan dari server)
  String _monthLabel = 'Bulan Ini';
  List<Map<String, String>> _availableMonths = [];

  String _searchQuery = '';
  final TextEditingController _searchController = TextEditingController();

  // Filter sub-tab di Tab 2 (Riwayat Mandiri) dan Tab 3 (Pelanggaran Bulanan)
  String _selfReportSubFilter = 'all'; // 'all' | 'verified' | 'rejected'
  String _violationSubView = 'recap'; // 'recap' | 'logs'

  List<dynamic> _pendingList = [];
  List<dynamic> _verifiedList = [];
  List<dynamic> _rejectedList = [];
  List<dynamic> _monthlyViolations = [];
  List<dynamic> _monthlyStudentRecap = [];

  final Map<int, bool> _verifyingMap = {};

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _load();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final params = <String, dynamic>{};
      if (_selectedMonth != null && _selectedMonth!.isNotEmpty) {
        params['month'] = _selectedMonth;
      }
      final res = await ApiClient.get(
        '/guru/conduct-self-reports',
        params: params,
      );
      if (mounted) {
        final rawMonths = res['available_months'] as List<dynamic>? ?? [];
        setState(() {
          _selectedMonth = res['selected_month']?.toString() ?? _selectedMonth;
          _monthLabel = res['month_label']?.toString() ?? 'Bulan Ini';
          _availableMonths = rawMonths
              .map((m) => {
                    'value': (m['value'] ?? '').toString(),
                    'label': (m['label'] ?? '').toString(),
                  })
              .toList();
          _pendingList = res['pending'] ?? [];
          _verifiedList = res['verified'] ?? [];
          _rejectedList = res['rejected'] ?? [];
          _monthlyViolations = res['monthly_violations'] ?? [];
          _monthlyStudentRecap = res['monthly_student_recap'] ?? [];
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = ApiClient.extractError(e);
          _isLoading = false;
        });
      }
    }
  }

  bool _matchesSearch(Map<String, dynamic> item) {
    if (_searchQuery.trim().isEmpty) return true;
    final q = _searchQuery.trim().toLowerCase();
    final name = (item['student_name'] ?? '').toString().toLowerCase();
    final cls = (item['class_name'] ?? '').toString().toLowerCase();
    final cat = (item['category_name'] ?? item['last_category'] ?? '')
        .toString()
        .toLowerCase();
    return name.contains(q) || cls.contains(q) || cat.contains(q);
  }

  Future<void> _verifyItem(int id, String studentName) async {
    setState(() => _verifyingMap[id] = true);
    try {
      final res =
          await ApiClient.post('/guru/conduct-self-reports/$id/verify');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
                res['message'] ?? 'Pengajuan $studentName berhasil diverifikasi.'),
            backgroundColor: AppColors.emerald600,
          ),
        );
        _load();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(ApiClient.extractError(e)),
            backgroundColor: AppColors.red500,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _verifyingMap.remove(id));
      }
    }
  }

  Future<void> _rejectItem(int id, String studentName) async {
    final noteController = TextEditingController();
    final bool? confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        title: Text(
          'Kembalikan Ajuan $studentName',
          style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700),
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Tuliskan catatan alasan agar siswa dapat memperbaiki ajuannya:',
              style: TextStyle(fontSize: 12, color: AppColors.gray600),
            ),
            const SizedBox(height: 10),
            TextField(
              controller: noteController,
              maxLines: 3,
              style: const TextStyle(fontSize: 13),
              decoration: InputDecoration(
                hintText: 'Contoh: Alasan tidak sesuai kronologi di gerbang...',
                hintStyle:
                    const TextStyle(fontSize: 12, color: AppColors.gray400),
                border:
                    OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                contentPadding: const EdgeInsets.all(12),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () {
              if (noteController.text.trim().isEmpty) {
                ScaffoldMessenger.of(ctx).showSnackBar(
                  const SnackBar(
                    content: Text('Catatan alasan harus diisi!'),
                    backgroundColor: AppColors.red500,
                  ),
                );
                return;
              }
              Navigator.pop(ctx, true);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFE11D48),
              foregroundColor: Colors.white,
            ),
            child: const Text('Kembalikan'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    setState(() => _verifyingMap[id] = true);
    try {
      final res = await ApiClient.post(
        '/guru/conduct-self-reports/$id/reject',
        data: {'rejection_note': noteController.text.trim()},
      );
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ??
                'Pengajuan $studentName dikembalikan untuk diperbaiki.'),
            backgroundColor: AppColors.amber600,
          ),
        );
        _load();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(ApiClient.extractError(e)),
            backgroundColor: AppColors.red500,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _verifyingMap.remove(id));
      }
    }
  }

  Future<void> _deleteItem(int id, String studentName) async {
    final bool? confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        title: const Text(
          'Hapus Ajuan Pembinaan?',
          style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700),
        ),
        content: Text(
          'Apakah Anda yakin ingin menghapus ajuan dari $studentName? Data tidak dapat dipulihkan.',
          style: const TextStyle(fontSize: 12, color: AppColors.gray600),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.red500,
              foregroundColor: Colors.white,
            ),
            child: const Text('Ya, Hapus'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    setState(() => _verifyingMap[id] = true);
    try {
      final res = await ApiClient.delete('/guru/conduct-self-reports/$id');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
                res['message'] ?? 'Pengajuan $studentName berhasil dihapus.'),
            backgroundColor: AppColors.emerald600,
          ),
        );
        _load();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(ApiClient.extractError(e)),
            backgroundColor: AppColors.red500,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _verifyingMap.remove(id));
      }
    }
  }

  void _openMonthPicker() {
    if (_availableMonths.isEmpty) return;
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(18)),
      ),
      builder: (ctx) {
        return SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Padding(
                padding: EdgeInsets.fromLTRB(18, 16, 18, 8),
                child: Text(
                  'Pilih Periode Bulan',
                  style: TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.w700,
                    color: AppColors.gray900,
                  ),
                ),
              ),
              const Divider(height: 1),
              Flexible(
                child: ListView.builder(
                  shrinkWrap: true,
                  itemCount: _availableMonths.length,
                  itemBuilder: (_, i) {
                    final m = _availableMonths[i];
                    final val = m['value'] ?? '';
                    final lbl = m['label'] ?? '';
                    final isSelected = val == _selectedMonth;
                    return ListTile(
                      dense: true,
                      title: Text(
                        i == 0 ? '$lbl (Bulan Berjalan)' : lbl,
                        style: TextStyle(
                          fontSize: 13.5,
                          fontWeight:
                              isSelected ? FontWeight.w700 : FontWeight.w500,
                          color: isSelected
                              ? AppColors.blue600
                              : AppColors.gray800,
                        ),
                      ),
                      trailing: isSelected
                          ? const Icon(Icons.check_circle_rounded,
                              color: AppColors.blue600, size: 18)
                          : null,
                      onTap: () {
                        Navigator.pop(ctx);
                        if (val != _selectedMonth) {
                          setState(() {
                            _selectedMonth = val;
                            _monthLabel = lbl;
                          });
                          _load();
                        }
                      },
                    );
                  },
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  void _showStudentMonthlyDetailSheet(Map<String, dynamic> recap) {
    final String studentName = recap['student_name']?.toString() ?? '—';
    final String className = recap['class_name']?.toString() ?? '—';
    final int monthlyCount = recap['monthly_violation_count'] as int? ?? 1;
    final int selfCount = recap['monthly_self_report_count'] as int? ?? 0;
    final int totalAllTime = recap['total_all_time_count'] as int? ?? monthlyCount;
    final List<dynamic> items = recap['items'] as List<dynamic>? ?? [];

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(18)),
      ),
      builder: (ctx) {
        return DraggableScrollableSheet(
          expand: false,
          initialChildSize: 0.6,
          minChildSize: 0.4,
          maxChildSize: 0.9,
          builder: (_, scrollCtrl) {
            return Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Padding(
                  padding: const EdgeInsets.fromLTRB(18, 16, 18, 12),
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              studentName,
                              style: const TextStyle(
                                fontSize: 15,
                                fontWeight: FontWeight.w700,
                                color: AppColors.gray900,
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              'Kelas $className • Periode $_monthLabel',
                              style: const TextStyle(
                                fontSize: 12,
                                color: AppColors.gray500,
                              ),
                            ),
                          ],
                        ),
                      ),
                      IconButton(
                        onPressed: () => Navigator.pop(ctx),
                        icon: const Icon(Icons.close_rounded,
                            color: AppColors.gray500),
                      ),
                    ],
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 18),
                  child: Row(
                    children: [
                      _buildMiniStatChip(
                          'Bulan Ini', '$monthlyCount x', const Color(0xFFE11D48)),
                      const SizedBox(width: 8),
                      _buildMiniStatChip(
                          'Lapor Mandiri', '$selfCount x', const Color(0xFFD97706)),
                      const SizedBox(width: 8),
                      _buildMiniStatChip(
                          'Total Akumulasi', '$totalAllTime x', AppColors.gray700),
                    ],
                  ),
                ),
                const SizedBox(height: 12),
                const Divider(height: 1),
                Expanded(
                  child: ListView.separated(
                    controller: scrollCtrl,
                    padding: const EdgeInsets.all(16),
                    itemCount: items.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 10),
                    itemBuilder: (_, idx) {
                      final log = items[idx] as Map<String, dynamic>;
                      final bool isSelf = log['is_self_reported'] == true;
                      return Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: AppColors.gray50,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: AppColors.gray200),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  '${log['date_label'] ?? ''} • ${log['time_label'] ?? ''} WITA',
                                  style: const TextStyle(
                                    fontSize: 11.5,
                                    fontWeight: FontWeight.w600,
                                    color: AppColors.gray600,
                                  ),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                      horizontal: 7, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: isSelf
                                        ? const Color(0xFFFEF3C7)
                                        : const Color(0xFFFFE4E6),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: Text(
                                    isSelf ? 'Lapor Mandiri' : 'Catat Guru',
                                    style: TextStyle(
                                      fontSize: 10,
                                      fontWeight: FontWeight.w700,
                                      color: isSelf
                                          ? const Color(0xFFB45309)
                                          : const Color(0xFFBE123C),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            Text(
                              log['category_name']?.toString() ?? 'Pelanggaran',
                              style: const TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w700,
                                color: AppColors.gray900,
                              ),
                            ),
                            if (log['description'] != null &&
                                log['description'].toString().isNotEmpty) ...[
                              const SizedBox(height: 3),
                              Text(
                                '"${log['description']}"',
                                style: const TextStyle(
                                  fontSize: 11.5,
                                  color: AppColors.gray600,
                                ),
                              ),
                            ],
                            const SizedBox(height: 4),
                            Text(
                              'Diverifikasi / Dicatat: ${log['recorder_name'] ?? 'Guru'}',
                              style: const TextStyle(
                                fontSize: 10.5,
                                color: AppColors.gray500,
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
                ),
              ],
            );
          },
        );
      },
    );
  }

  Widget _buildMiniStatChip(String label, String value, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 8),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: color.withValues(alpha: 0.2)),
        ),
        child: Column(
          children: [
            Text(
              value,
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w800,
                color: color,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: const TextStyle(
                fontSize: 10,
                fontWeight: FontWeight.w600,
                color: AppColors.gray600,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final totalMandiriHistory = _verifiedList.length + _rejectedList.length;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text(
          'Piket Gerbang & Disiplin',
          style: TextStyle(
            fontSize: 15.5,
            fontWeight: FontWeight.w700,
            color: AppColors.gray900,
          ),
        ),
        backgroundColor: Colors.white,
        foregroundColor: AppColors.gray900,
        elevation: 0,
        centerTitle: false,
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 12),
            child: OutlinedButton.icon(
              onPressed: _openMonthPicker,
              style: OutlinedButton.styleFrom(
                foregroundColor: AppColors.gray800,
                side: const BorderSide(color: AppColors.gray200),
                backgroundColor: AppColors.gray50,
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
              icon: const Icon(Icons.calendar_month_rounded,
                  size: 15, color: AppColors.blue600),
              label: Text(
                _monthLabel,
                style: const TextStyle(
                  fontSize: 11.5,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ),
        ],
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(98),
          child: Column(
            children: [
              // Search Bar
              Padding(
                padding: const EdgeInsets.fromLTRB(14, 0, 14, 8),
                child: SizedBox(
                  height: 38,
                  child: TextField(
                    controller: _searchController,
                    onChanged: (v) => setState(() => _searchQuery = v),
                    style: const TextStyle(fontSize: 12.5),
                    decoration: InputDecoration(
                      hintText: 'Cari nama siswa, kelas, atau jenis pelanggaran...',
                      hintStyle: const TextStyle(
                          fontSize: 12, color: AppColors.gray400),
                      prefixIcon: const Icon(Icons.search_rounded,
                          size: 18, color: AppColors.gray400),
                      suffixIcon: _searchQuery.isNotEmpty
                          ? IconButton(
                              onPressed: () {
                                _searchController.clear();
                                setState(() => _searchQuery = '');
                              },
                              icon: const Icon(Icons.clear_rounded,
                                  size: 16, color: AppColors.gray400),
                            )
                          : null,
                      filled: true,
                      fillColor: AppColors.gray50,
                      contentPadding:
                          const EdgeInsets.symmetric(horizontal: 12),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(10),
                        borderSide: const BorderSide(color: AppColors.gray200),
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(10),
                        borderSide: const BorderSide(color: AppColors.gray200),
                      ),
                    ),
                  ),
                ),
              ),
              // TabBar
              Container(
                decoration: const BoxDecoration(
                  border: Border(
                    bottom: BorderSide(color: AppColors.gray200, width: 1),
                  ),
                ),
                child: TabBar(
                  controller: _tabController,
                  indicatorColor: AppColors.blue600,
                  indicatorWeight: 2.5,
                  labelColor: AppColors.blue600,
                  unselectedLabelColor: AppColors.gray500,
                  labelPadding: const EdgeInsets.symmetric(horizontal: 6),
                  labelStyle: const TextStyle(
                      fontWeight: FontWeight.w700, fontSize: 12),
                  tabs: [
                    Tab(
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Text('Pending'),
                          if (_pendingList.isNotEmpty) ...[
                            const SizedBox(width: 5),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 5, vertical: 1),
                              decoration: BoxDecoration(
                                color: const Color(0xFFFEF3C7),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                '${_pendingList.length}',
                                style: const TextStyle(
                                  color: Color(0xFFB45309),
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                    Tab(
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Text('Lapor Mandiri'),
                          if (totalMandiriHistory > 0) ...[
                            const SizedBox(width: 5),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 5, vertical: 1),
                              decoration: BoxDecoration(
                                color: AppColors.gray100,
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                '$totalMandiriHistory',
                                style: const TextStyle(
                                  color: AppColors.gray700,
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                    Tab(
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Text('Pelanggaran'),
                          if (_monthlyViolations.isNotEmpty) ...[
                            const SizedBox(width: 5),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 5, vertical: 1),
                              decoration: BoxDecoration(
                                color: const Color(0xFFFFE4E6),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                '${_monthlyViolations.length}',
                                style: const TextStyle(
                                  color: Color(0xFFBE123C),
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                            ),
                          ],
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
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(_error!,
                          style: const TextStyle(color: AppColors.red500)),
                      const SizedBox(height: 12),
                      ElevatedButton(
                          onPressed: _load, child: const Text('Coba Lagi')),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  child: TabBarView(
                    controller: _tabController,
                    children: [
                      _buildPendingTab(),
                      _buildSelfReportHistoryTab(),
                      _buildMonthlyViolationsTab(),
                    ],
                  ),
                ),
    );
  }

  // ── TAB 1: PENDING SELF-REPORTS ──────────────────────────────────────────
  Widget _buildPendingTab() {
    final filtered = _pendingList
        .where((e) => _matchesSearch(e as Map<String, dynamic>))
        .toList();

    if (filtered.isEmpty) {
      return ListView(
        children: [
          const SizedBox(height: 80),
          Center(
            child: Column(
              children: [
                const Icon(Icons.check_circle_outline_rounded,
                    size: 48, color: AppColors.gray300),
                const SizedBox(height: 10),
                Text(
                  'Tidak ada pengajuan pending pada $_monthLabel',
                  style: const TextStyle(
                    fontSize: 13.5,
                    fontWeight: FontWeight.w700,
                    color: AppColors.gray600,
                  ),
                ),
                const SizedBox(height: 4),
                const Text(
                  'Lihat tab "Lapor Mandiri" atau "Pelanggaran" untuk rekap bulanan.',
                  style: TextStyle(fontSize: 11.5, color: AppColors.gray400),
                ),
              ],
            ),
          ),
        ],
      );
    }

    return ListView.separated(
      padding: const EdgeInsets.all(14),
      itemCount: filtered.length,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (ctx, i) {
        final item = filtered[i] as Map<String, dynamic>;
        final int id = item['id'];
        final String studentName = item['student_name'] ?? '—';
        final String className = item['class_name'] ?? '—';
        final int monthCount = item['monthly_violation_count'] ?? 1;
        final int latCount = item['lateness_count'] ?? 1;
        final String reason = item['category_name'] ?? 'Terlambat';
        final String? desc = item['description'];
        final String dateLabel = item['date_label'] ?? '';
        final String time = item['created_at'] ?? '';
        final bool isVerifying = _verifyingMap[id] == true;

        return Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppColors.gray200),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                studentName,
                                style: const TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.w700,
                                  color: AppColors.gray900,
                                ),
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(
                                color: AppColors.gray100,
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                className,
                                style: const TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w700,
                                  color: AppColors.gray700,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '$dateLabel • $time WITA',
                          style: const TextStyle(
                              fontSize: 11.5, color: AppColors.gray500),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  Expanded(
                    child: Text(
                      reason,
                      style: const TextStyle(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFFB45309),
                      ),
                    ),
                  ),
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                    decoration: BoxDecoration(
                      color: monthCount >= 3
                          ? const Color(0xFFFFE4E6)
                          : const Color(0xFFFEF3C7),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      '$monthCount x Bulan Ini (Total $latCount x)',
                      style: TextStyle(
                        fontSize: 10.5,
                        fontWeight: FontWeight.w700,
                        color: monthCount >= 3
                            ? const Color(0xFFBE123C)
                            : const Color(0xFFB45309),
                      ),
                    ),
                  ),
                ],
              ),
              if (desc != null && desc.isNotEmpty) ...[
                const SizedBox(height: 6),
                Text(
                  '"$desc"',
                  style: const TextStyle(
                    fontSize: 11.5,
                    color: AppColors.gray600,
                  ),
                ),
              ],
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: isVerifying
                          ? null
                          : () => _verifyItem(id, studentName),
                      icon: isVerifying
                          ? const SizedBox(
                              width: 15,
                              height: 15,
                              child: CircularProgressIndicator(
                                  color: Colors.white, strokeWidth: 2),
                            )
                          : const Icon(Icons.check_rounded, size: 16),
                      label: Text(
                        isVerifying ? 'Proses...' : 'Verifikasi',
                        style: const TextStyle(
                            fontWeight: FontWeight.w700, fontSize: 12),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.emerald600,
                        foregroundColor: Colors.white,
                        elevation: 0,
                        padding: const EdgeInsets.symmetric(vertical: 9),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  OutlinedButton(
                    onPressed: isVerifying
                        ? null
                        : () => _rejectItem(id, studentName),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: Color(0xFFFDA4AF)),
                      backgroundColor: const Color(0xFFFFF1F2),
                      padding: const EdgeInsets.symmetric(
                          vertical: 9, horizontal: 12),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10)),
                    ),
                    child: const Text(
                      'Kembalikan',
                      style: TextStyle(
                        fontWeight: FontWeight.w700,
                        fontSize: 12,
                        color: Color(0xFFE11D48),
                      ),
                    ),
                  ),
                  const SizedBox(width: 4),
                  IconButton(
                    onPressed: isVerifying
                        ? null
                        : () => _deleteItem(id, studentName),
                    icon: const Icon(Icons.delete_outline_rounded,
                        color: AppColors.gray400, size: 19),
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }

  // ── TAB 2: DAFTAR SIAPA SAJA YANG PERNAH MELAKUKAN PELAPORAN MANDIRI ─────
  Widget _buildSelfReportHistoryTab() {
    final List<Map<String, dynamic>> combined = [];
    if (_selfReportSubFilter == 'all' || _selfReportSubFilter == 'verified') {
      for (final item in _verifiedList) {
        final m = Map<String, dynamic>.from(item as Map);
        m['_status'] = 'verified';
        if (_matchesSearch(m)) combined.add(m);
      }
    }
    if (_selfReportSubFilter == 'all' || _selfReportSubFilter == 'rejected') {
      for (final item in _rejectedList) {
        final m = Map<String, dynamic>.from(item as Map);
        m['_status'] = 'rejected';
        if (_matchesSearch(m)) combined.add(m);
      }
    }

    return Column(
      children: [
        // Filter Chip Bar
        Container(
          width: double.infinity,
          padding: const EdgeInsets.fromLTRB(14, 10, 14, 6),
          color: Colors.white,
          child: Row(
            children: [
              _buildFilterChip(
                label: 'Semua (${_verifiedList.length + _rejectedList.length})',
                selected: _selfReportSubFilter == 'all',
                onTap: () => setState(() => _selfReportSubFilter = 'all'),
              ),
              const SizedBox(width: 8),
              _buildFilterChip(
                label: 'Diverifikasi (${_verifiedList.length})',
                selected: _selfReportSubFilter == 'verified',
                onTap: () => setState(() => _selfReportSubFilter = 'verified'),
              ),
              const SizedBox(width: 8),
              _buildFilterChip(
                label: 'Dikembalikan (${_rejectedList.length})',
                selected: _selfReportSubFilter == 'rejected',
                onTap: () => setState(() => _selfReportSubFilter = 'rejected'),
              ),
            ],
          ),
        ),
        Expanded(
          child: combined.isEmpty
              ? ListView(
                  children: [
                    const SizedBox(height: 70),
                    Center(
                      child: Text(
                        'Belum ada riwayat pelaporan mandiri pada $_monthLabel.',
                        style: const TextStyle(
                            color: AppColors.gray400, fontSize: 12.5),
                      ),
                    ),
                  ],
                )
              : ListView.separated(
                  padding: const EdgeInsets.all(14),
                  itemCount: combined.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 10),
                  itemBuilder: (ctx, i) {
                    final item = combined[i];
                    final int id = item['id'];
                    final bool isRejected = item['_status'] == 'rejected';
                    final String studentName = item['student_name'] ?? '—';
                    final String className = item['class_name'] ?? '—';
                    final int monthCount =
                        item['monthly_violation_count'] ?? 1;
                    final int latCount = item['lateness_count'] ?? 1;
                    final String reason = item['category_name'] ?? 'Terlambat';
                    final String dateLabel = item['date_label'] ?? '';
                    final String verifierName =
                        item['verifier_name'] ?? 'Guru';
                    final String? rejNote = item['rejection_note'];
                    final bool isVerifying = _verifyingMap[id] == true;

                    return Container(
                      padding: const EdgeInsets.all(13),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(13),
                        border: Border.all(
                          color: isRejected
                              ? const Color(0xFFFECDD3)
                              : AppColors.gray200,
                        ),
                      ),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            width: 34,
                            height: 34,
                            decoration: BoxDecoration(
                              color: isRejected
                                  ? const Color(0xFFFFE4E6)
                                  : const Color(0xFFD1FAE5),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Icon(
                              isRejected
                                  ? Icons.assignment_return_rounded
                                  : Icons.check_rounded,
                              color: isRejected
                                  ? const Color(0xFFE11D48)
                                  : AppColors.emerald600,
                              size: 18,
                            ),
                          ),
                          const SizedBox(width: 11),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Expanded(
                                      child: Text(
                                        studentName,
                                        style: const TextStyle(
                                          fontSize: 13.5,
                                          fontWeight: FontWeight.w700,
                                          color: AppColors.gray900,
                                        ),
                                      ),
                                    ),
                                    Text(
                                      className,
                                      style: const TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.w700,
                                        color: AppColors.gray600,
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 3),
                                Text(
                                  '$reason • $monthCount x Bulan Ini (Total $latCount x)',
                                  style: const TextStyle(
                                      fontSize: 11.5, color: AppColors.gray700),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  '$dateLabel • Oleh $verifierName',
                                  style: TextStyle(
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.w600,
                                    color: isRejected
                                        ? const Color(0xFFE11D48)
                                        : AppColors.emerald600,
                                  ),
                                ),
                                if (isRejected &&
                                    rejNote != null &&
                                    rejNote.isNotEmpty) ...[
                                  const SizedBox(height: 4),
                                  Text(
                                    'Catatan: "$rejNote"',
                                    style: const TextStyle(
                                      fontSize: 11,
                                      color: Color(0xFF9F1239),
                                    ),
                                  ),
                                ],
                              ],
                            ),
                          ),
                          IconButton(
                            onPressed: isVerifying
                                ? null
                                : () => _deleteItem(id, studentName),
                            icon: const Icon(Icons.delete_outline_rounded,
                                color: AppColors.gray400, size: 18),
                          ),
                        ],
                      ),
                    );
                  },
                ),
        ),
      ],
    );
  }

  // ── TAB 3: DATA PELANGGARAN SISWA PER BULAN (DEFAULT BULAN BERJALAN) ─────
  Widget _buildMonthlyViolationsTab() {
    final filteredRecap = _monthlyStudentRecap
        .where((e) => _matchesSearch(e as Map<String, dynamic>))
        .toList();
    final filteredLogs = _monthlyViolations
        .where((e) => _matchesSearch(e as Map<String, dynamic>))
        .toList();

    return Column(
      children: [
        Container(
          width: double.infinity,
          padding: const EdgeInsets.fromLTRB(14, 10, 14, 6),
          color: Colors.white,
          child: Row(
            children: [
              _buildFilterChip(
                label: 'Rekap Siswa (${filteredRecap.length})',
                selected: _violationSubView == 'recap',
                onTap: () => setState(() => _violationSubView = 'recap'),
              ),
              const SizedBox(width: 8),
              _buildFilterChip(
                label: 'Semua Catatan (${filteredLogs.length})',
                selected: _violationSubView == 'logs',
                onTap: () => setState(() => _violationSubView = 'logs'),
              ),
            ],
          ),
        ),
        Expanded(
          child: _violationSubView == 'recap'
              ? _buildMonthlyStudentRecapList(filteredRecap)
              : _buildMonthlyViolationLogsList(filteredLogs),
        ),
      ],
    );
  }

  Widget _buildMonthlyStudentRecapList(List<dynamic> recapList) {
    if (recapList.isEmpty) {
      return ListView(
        children: [
          const SizedBox(height: 70),
          Center(
            child: Text(
              'Belum ada siswa tercatat melanggar pada $_monthLabel.',
              style: const TextStyle(color: AppColors.gray400, fontSize: 12.5),
            ),
          ),
        ],
      );
    }

    return ListView.separated(
      padding: const EdgeInsets.all(14),
      itemCount: recapList.length,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (ctx, i) {
        final item = recapList[i] as Map<String, dynamic>;
        final String studentName = item['student_name'] ?? '—';
        final String className = item['class_name'] ?? '—';
        final int monthlyCount = item['monthly_violation_count'] ?? 1;
        final int selfCount = item['monthly_self_report_count'] ?? 0;
        final String lastDate = item['last_date_label'] ?? '';
        final String lastCategory = item['last_category'] ?? 'Pelanggaran';
        final bool isFrequent = monthlyCount >= 3;

        return InkWell(
          onTap: () => _showStudentMonthlyDetailSheet(item),
          borderRadius: BorderRadius.circular(13),
          child: Container(
            padding: const EdgeInsets.all(13),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(13),
              border: Border.all(
                color: isFrequent
                    ? const Color(0xFFFECDD3)
                    : AppColors.gray200,
              ),
            ),
            child: Row(
              children: [
                Container(
                  width: 38,
                  height: 38,
                  alignment: Alignment.center,
                  decoration: BoxDecoration(
                    color: isFrequent
                        ? const Color(0xFFFFE4E6)
                        : const Color(0xFFFEF3C7),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    '${monthlyCount}x',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w800,
                      color: isFrequent
                          ? const Color(0xFFBE123C)
                          : const Color(0xFFB45309),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              studentName,
                              style: const TextStyle(
                                fontSize: 13.5,
                                fontWeight: FontWeight.w700,
                                color: AppColors.gray900,
                              ),
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 7, vertical: 2),
                            decoration: BoxDecoration(
                              color: AppColors.gray100,
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              className,
                              style: const TextStyle(
                                fontSize: 10.5,
                                fontWeight: FontWeight.w700,
                                color: AppColors.gray700,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 3),
                      Text(
                        'Terakhir: $lastCategory ($lastDate)',
                        style: const TextStyle(
                          fontSize: 11.5,
                          color: AppColors.gray600,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Lapor Mandiri: ${selfCount}x • Total Bulan Ini: ${monthlyCount}x',
                        style: const TextStyle(
                          fontSize: 10.5,
                          fontWeight: FontWeight.w600,
                          color: AppColors.gray500,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 6),
                const Icon(Icons.chevron_right_rounded,
                    color: AppColors.gray400, size: 20),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildMonthlyViolationLogsList(List<dynamic> logs) {
    if (logs.isEmpty) {
      return ListView(
        children: [
          const SizedBox(height: 70),
          Center(
            child: Text(
              'Belum ada catatan pelanggaran pada $_monthLabel.',
              style: const TextStyle(color: AppColors.gray400, fontSize: 12.5),
            ),
          ),
        ],
      );
    }

    return ListView.separated(
      padding: const EdgeInsets.all(14),
      itemCount: logs.length,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (ctx, i) {
        final item = logs[i] as Map<String, dynamic>;
        final String studentName = item['student_name'] ?? '—';
        final String className = item['class_name'] ?? '—';
        final String categoryName = item['category_name'] ?? 'Pelanggaran';
        final String? desc = item['description'];
        final bool isSelf = item['is_self_reported'] == true;
        final String dateLabel = item['date_label'] ?? '';
        final String timeLabel = item['created_at'] ?? '';
        final String recorder = item['recorder_name'] ?? 'Guru';

        return Container(
          padding: const EdgeInsets.all(13),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(13),
            border: Border.all(color: AppColors.gray200),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(
                      studentName,
                      style: const TextStyle(
                        fontSize: 13.5,
                        fontWeight: FontWeight.w700,
                        color: AppColors.gray900,
                      ),
                    ),
                  ),
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                    decoration: BoxDecoration(
                      color: AppColors.gray100,
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      className,
                      style: const TextStyle(
                        fontSize: 10.5,
                        fontWeight: FontWeight.w700,
                        color: AppColors.gray700,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 4),
              Row(
                children: [
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: isSelf
                          ? const Color(0xFFFEF3C7)
                          : const Color(0xFFFFE4E6),
                      borderRadius: BorderRadius.circular(5),
                    ),
                    child: Text(
                      isSelf ? 'Lapor Mandiri' : 'Catat Guru',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w700,
                        color: isSelf
                            ? const Color(0xFFB45309)
                            : const Color(0xFFBE123C),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      categoryName,
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: AppColors.gray800,
                      ),
                    ),
                  ),
                ],
              ),
              if (desc != null && desc.isNotEmpty) ...[
                const SizedBox(height: 4),
                Text(
                  '"$desc"',
                  style: const TextStyle(
                    fontSize: 11.5,
                    color: AppColors.gray600,
                  ),
                ),
              ],
              const SizedBox(height: 5),
              Text(
                '$dateLabel, $timeLabel WITA • Oleh $recorder',
                style: const TextStyle(
                  fontSize: 10.5,
                  color: AppColors.gray500,
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildFilterChip({
    required String label,
    required bool selected,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color: selected ? AppColors.gray900 : AppColors.gray100,
          borderRadius: BorderRadius.circular(8),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11.5,
            fontWeight: selected ? FontWeight.w700 : FontWeight.w600,
            color: selected ? Colors.white : AppColors.gray700,
          ),
        ),
      ),
    );
  }
}
