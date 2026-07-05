import 'package:flutter/material.dart';
import '../../models/guru_models.dart';
import '../../services/guru_service.dart';
import '../../theme/app_colors.dart';

class GuruSarprasScreen extends StatefulWidget {
  const GuruSarprasScreen({super.key});

  @override
  State<GuruSarprasScreen> createState() => _GuruSarprasScreenState();
}

class _GuruSarprasScreenState extends State<GuruSarprasScreen>
    with SingleTickerProviderStateMixin {
  late final TabController _tabCtrl;

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 4, vsync: this);
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Sarana & Prasarana'),
        bottom: TabBar(
          controller: _tabCtrl,
          isScrollable: true,
          tabAlignment: TabAlignment.start,
          tabs: const [
            Tab(text: 'Dashboard'),
            Tab(text: 'Inventaris'),
            Tab(text: 'Kerusakan'),
            Tab(text: 'Peminjaman'),
          ],
          labelColor: AppColors.blue600,
          indicatorColor: AppColors.blue600,
          unselectedLabelColor: AppColors.gray400,
        ),
      ),
      body: TabBarView(
        controller: _tabCtrl,
        children: const [
          _DashboardTab(),
          _AssetsTab(),
          _DamageTab(),
          _LoansTab(),
        ],
      ),
    );
  }
}

// ─── Dashboard Tab ────────────────────────────────────────────────────────────

class _DashboardTab extends StatefulWidget {
  const _DashboardTab();

  @override
  State<_DashboardTab> createState() => _DashboardTabState();
}

class _DashboardTabState extends State<_DashboardTab> {
  SarprasStats? _stats;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final stats = await GuruService.getSarprasStats();
      if (mounted) setState(() { _stats = stats; _loading = false; });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_stats == null) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text('Gagal memuat data', style: TextStyle(color: AppColors.gray400)),
            const SizedBox(height: 12),
            FilledButton.icon(
              onPressed: () { setState(() => _loading = true); _load(); },
              icon: const Icon(Icons.refresh_rounded, size: 16),
              label: const Text('Coba Lagi'),
            ),
          ],
        ),
      );
    }
    final s = _stats!;
    return RefreshIndicator(
      onRefresh: () async { setState(() => _loading = true); await _load(); },
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Kondisi Aset',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.gray700)),
            const SizedBox(height: 10),
            Row(
              children: [
                Expanded(child: _StatCard(label: 'Total Aset',    value: '${s.totalAssets}', color: AppColors.blue600,    icon: Icons.inventory_2_rounded)),
                const SizedBox(width: 8),
                Expanded(child: _StatCard(label: 'Baik',          value: '${s.baik}',        color: AppColors.emerald600, icon: Icons.check_circle_rounded)),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(child: _StatCard(label: 'Rusak Ringan', value: '${s.rusakRingan}', color: AppColors.orange500, icon: Icons.warning_rounded)),
                const SizedBox(width: 8),
                Expanded(child: _StatCard(label: 'Rusak Berat',  value: '${s.rusakBerat}',  color: AppColors.red500,    icon: Icons.error_rounded)),
              ],
            ),
            const SizedBox(height: 20),
            const Text('Aktivitas',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.gray700)),
            const SizedBox(height: 10),
            Row(
              children: [
                Expanded(child: _StatCard(label: 'Laporan Menunggu', value: '${s.pendingDamage}', color: AppColors.amber500,   icon: Icons.report_problem_rounded)),
                const SizedBox(width: 8),
                Expanded(child: _StatCard(label: 'Pinjam Aktif',     value: '${s.activeLoans}',   color: AppColors.indigo700, icon: Icons.swap_horiz_rounded)),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(child: _StatCard(label: 'Pinjam Menunggu', value: '${s.pendingLoans}', color: AppColors.gray500,    icon: Icons.pending_rounded)),
                const SizedBox(width: 8),
                Expanded(child: _StatCard(label: 'Pinjaman Saya',   value: '${s.myLoans}',      color: AppColors.blue600,   icon: Icons.person_rounded)),
              ],
            ),
            const SizedBox(height: 24),
            const Text(
              'Gunakan tab di atas untuk melihat detail inventaris, melaporkan kerusakan, atau mengajukan peminjaman.',
              style: TextStyle(fontSize: 12, color: AppColors.gray400),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}

class _StatCard extends StatelessWidget {
  final String label;
  final String value;
  final Color  color;
  final IconData icon;

  const _StatCard({required this.label, required this.value, required this.color, required this.icon});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.gray100),
      ),
      child: Row(
        children: [
          Container(
            width: 36, height: 36,
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(icon, color: color, size: 18),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(value, style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: color)),
                Text(label, style: const TextStyle(fontSize: 11, color: AppColors.gray500)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Assets Tab ───────────────────────────────────────────────────────────────

class _AssetsTab extends StatefulWidget {
  const _AssetsTab();

  @override
  State<_AssetsTab> createState() => _AssetsTabState();
}

class _AssetsTabState extends State<_AssetsTab> {
  List<AssetItem> _assets     = [];
  String?  _filterCondition;
  String?  _filterCategory;
  int      _page        = 1;
  int      _lastPage    = 1;
  bool     _loading     = true;
  bool     _loadingMore = false;
  final _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _load({bool refresh = true}) async {
    if (refresh) {
      setState(() { _loading = true; _page = 1; });
    } else {
      setState(() => _loadingMore = true);
    }
    try {
      final result = await GuruService.getAssets(
        condition: _filterCondition,
        category:  _filterCategory,
        q:         _searchCtrl.text.trim().isEmpty ? null : _searchCtrl.text.trim(),
        page:      _page,
      );
      if (mounted) {
        setState(() {
          if (refresh) { _assets = result.data; } else { _assets.addAll(result.data); }
          _lastPage    = result.lastPage;
          _loading     = false;
          _loadingMore = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() { _loading = false; _loadingMore = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Container(
          color: Colors.white,
          padding: const EdgeInsets.fromLTRB(12, 8, 12, 8),
          child: Column(
            children: [
              Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _searchCtrl,
                      decoration: InputDecoration(
                        hintText: 'Cari nama aset...',
                        prefixIcon: const Icon(Icons.search, size: 16, color: AppColors.gray400),
                        filled: true, fillColor: AppColors.gray50,
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: AppColors.gray200)),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: AppColors.gray200)),
                        isDense: true,
                      ),
                      onSubmitted: (_) => _load(),
                    ),
                  ),
                  const SizedBox(width: 8),
                  ElevatedButton(
                    onPressed: () => _load(),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.blue600,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                    child: const Icon(Icons.search, size: 16),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: [
                    _FilterChip(label: 'Semua', selected: _filterCondition == null, onTap: () { setState(() => _filterCondition = null); _load(); }),
                    const SizedBox(width: 6),
                    _FilterChip(label: 'Baik', selected: _filterCondition == 'baik', color: AppColors.emerald600,
                        onTap: () { setState(() => _filterCondition = _filterCondition == 'baik' ? null : 'baik'); _load(); }),
                    const SizedBox(width: 6),
                    _FilterChip(label: 'Rusak Ringan', selected: _filterCondition == 'rusak_ringan', color: AppColors.orange500,
                        onTap: () { setState(() => _filterCondition = _filterCondition == 'rusak_ringan' ? null : 'rusak_ringan'); _load(); }),
                    const SizedBox(width: 6),
                    _FilterChip(label: 'Rusak Berat', selected: _filterCondition == 'rusak_berat', color: AppColors.red500,
                        onTap: () { setState(() => _filterCondition = _filterCondition == 'rusak_berat' ? null : 'rusak_berat'); _load(); }),
                  ],
                ),
              ),
            ],
          ),
        ),
        Expanded(child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _assets.isEmpty
                ? const Center(child: Text('Tidak ada aset', style: TextStyle(color: AppColors.gray400)))
                : NotificationListener<ScrollNotification>(
                    onNotification: (n) {
                      if (n.metrics.pixels >= n.metrics.maxScrollExtent - 100 && _page < _lastPage && !_loadingMore) {
                        _page++; _load(refresh: false);
                      }
                      return false;
                    },
                    child: ListView.builder(
                      padding: const EdgeInsets.all(12),
                      itemCount: _assets.length + (_loadingMore ? 1 : 0),
                      itemBuilder: (_, i) {
                        if (i == _assets.length) {
                          return const Center(child: Padding(
                            padding: EdgeInsets.all(16),
                            child: CircularProgressIndicator(strokeWidth: 2),
                          ));
                        }
                        return _AssetCard(asset: _assets[i]);
                      },
                    ),
                  ),
        ),
      ],
    );
  }
}

class _AssetCard extends StatelessWidget {
  final AssetItem asset;
  const _AssetCard({required this.asset});

  Color get _condColor {
    return switch (asset.condition) {
      'baik'         => AppColors.emerald600,
      'rusak_ringan' => AppColors.orange500,
      'rusak_berat'  => AppColors.red500,
      _              => AppColors.gray400,
    };
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: AppColors.gray100),
      ),
      child: Row(
        children: [
          Container(
            width: 40, height: 40,
            decoration: BoxDecoration(
              color: AppColors.blue50,
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Icon(Icons.inventory_2_rounded, color: AppColors.blue600, size: 20),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(asset.name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                Text(
                  '${asset.categoryLabel} · ${asset.roomName ?? 'Tanpa Ruang'} · ${asset.quantity} unit',
                  style: const TextStyle(fontSize: 11, color: AppColors.gray400),
                ),
                if (asset.purchaseYear != null)
                  Text('Dibeli: ${asset.purchaseYear}', style: const TextStyle(fontSize: 10, color: AppColors.gray300)),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              color: _condColor.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(6),
              border: Border.all(color: _condColor.withValues(alpha: 0.3)),
            ),
            child: Text(
              asset.conditionLabel,
              style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: _condColor),
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Damage Tab ───────────────────────────────────────────────────────────────

class _DamageTab extends StatefulWidget {
  const _DamageTab();

  @override
  State<_DamageTab> createState() => _DamageTabState();
}

class _DamageTabState extends State<_DamageTab> {
  List<DamageReportItem> _reports    = [];
  int     _page        = 1;
  int     _lastPage    = 1;
  bool    _loading     = true;
  bool    _loadingMore = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load({bool refresh = true}) async {
    if (refresh) {
      setState(() { _loading = true; _page = 1; });
    } else {
      setState(() => _loadingMore = true);
    }
    try {
      final result = await GuruService.getDamageReports(page: _page);
      if (mounted) {
        setState(() {
          if (refresh) { _reports = result.data; } else { _reports.addAll(result.data); }
          _lastPage    = result.lastPage;
          _loading     = false;
          _loadingMore = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() { _loading = false; _loadingMore = false; });
    }
  }

  void _openReportDialog(BuildContext context) async {
    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _ReportDamageSheet(onDone: () { _load(); }),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _openReportDialog(context),
        backgroundColor: AppColors.red500,
        icon: const Icon(Icons.add_rounded, color: Colors.white),
        label: const Text('Laporkan Kerusakan', style: TextStyle(color: Colors.white)),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _reports.isEmpty
              ? const Center(child: Text('Belum ada laporan kerusakan', style: TextStyle(color: AppColors.gray400)))
              : NotificationListener<ScrollNotification>(
                  onNotification: (n) {
                    if (n.metrics.pixels >= n.metrics.maxScrollExtent - 100 && _page < _lastPage && !_loadingMore) {
                      _page++; _load(refresh: false);
                    }
                    return false;
                  },
                  child: ListView.builder(
                    padding: const EdgeInsets.fromLTRB(12, 12, 12, 80),
                    itemCount: _reports.length + (_loadingMore ? 1 : 0),
                    itemBuilder: (_, i) {
                      if (i == _reports.length) {
                        return const Center(child: Padding(
                          padding: EdgeInsets.all(16),
                          child: CircularProgressIndicator(strokeWidth: 2),
                        ));
                      }
                      return _DamageCard(report: _reports[i]);
                    },
                  ),
                ),
    );
  }
}

class _DamageCard extends StatelessWidget {
  final DamageReportItem report;
  const _DamageCard({required this.report});

  Color get _statusColor {
    return switch (report.status) {
      'pending'     => AppColors.amber500,
      'in_progress' => AppColors.blue600,
      'resolved'    => AppColors.emerald600,
      _             => AppColors.gray400,
    };
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: AppColors.gray100),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(report.assetName, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: _statusColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(report.statusLabel, style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: _statusColor)),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(report.description, style: const TextStyle(fontSize: 12, color: AppColors.gray600)),
          const SizedBox(height: 6),
          Row(
            children: [
              Text(report.createdAt, style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
              const Spacer(),
              Text('${report.daysOpen} hari', style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
            ],
          ),
          if (report.resolutionNote != null && report.resolutionNote!.isNotEmpty) ...[
            const SizedBox(height: 6),
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: AppColors.emerald50,
                borderRadius: BorderRadius.circular(6),
              ),
              child: Text(
                'Resolusi: ${report.resolutionNote}',
                style: const TextStyle(fontSize: 11, color: AppColors.emerald600),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _ReportDamageSheet extends StatefulWidget {
  final VoidCallback onDone;
  const _ReportDamageSheet({required this.onDone});

  @override
  State<_ReportDamageSheet> createState() => _ReportDamageSheetState();
}

class _ReportDamageSheetState extends State<_ReportDamageSheet> {
  List<AssetItem> _assets       = [];
  AssetItem?      _selectedAsset;
  bool  _loadingAssets = true;
  bool  _submitting    = false;
  final _descCtrl  = TextEditingController();
  final _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadAssets();
  }

  @override
  void dispose() {
    _descCtrl.dispose();
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadAssets() async {
    try {
      final result = await GuruService.getAssets();
      if (mounted) setState(() { _assets = result.data; _loadingAssets = false; });
    } catch (_) {
      if (mounted) setState(() => _loadingAssets = false);
    }
  }

  Future<void> _searchAssets() async {
    final q = _searchCtrl.text.trim();
    setState(() => _loadingAssets = true);
    try {
      final result = await GuruService.getAssets(q: q.isEmpty ? null : q);
      if (mounted) setState(() { _assets = result.data; _loadingAssets = false; });
    } catch (_) {
      if (mounted) setState(() => _loadingAssets = false);
    }
  }

  Future<void> _submit() async {
    if (_selectedAsset == null) {
      _snack('Pilih aset', AppColors.orange500); return;
    }
    if (_descCtrl.text.trim().isEmpty) {
      _snack('Isi deskripsi kerusakan', AppColors.orange500); return;
    }
    setState(() => _submitting = true);
    try {
      await GuruService.storeDamageReport(
        assetId: _selectedAsset!.id,
        description: _descCtrl.text.trim(),
      );
      if (mounted) {
        Navigator.pop(context);
        widget.onDone();
        _snack('Laporan terkirim', AppColors.emerald600);
      }
    } catch (e) {
      if (mounted) { setState(() => _submitting = false); _snack(e.toString(), AppColors.red500); }
    }
  }

  void _snack(String msg, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg), backgroundColor: color, behavior: SnackBarBehavior.floating,
    ));
  }

  @override
  Widget build(BuildContext context) {
    return DraggableScrollableSheet(
      initialChildSize: 0.85,
      minChildSize:     0.5,
      maxChildSize:     0.95,
      builder: (_, scrollCtrl) => Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
        ),
        child: Column(
          children: [
            Container(
              width: 40, height: 4,
              margin: const EdgeInsets.symmetric(vertical: 10),
              decoration: BoxDecoration(color: AppColors.gray200, borderRadius: BorderRadius.circular(2)),
            ),
            const Padding(
              padding: EdgeInsets.symmetric(horizontal: 16),
              child: Text('Laporkan Kerusakan', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
            ),
            const SizedBox(height: 12),
            Expanded(
              child: SingleChildScrollView(
                controller: scrollCtrl,
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 32),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Pilih Aset', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700)),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: _searchCtrl,
                            decoration: InputDecoration(
                              hintText: 'Cari aset...',
                              prefixIcon: const Icon(Icons.search, size: 16, color: AppColors.gray400),
                              filled: true, fillColor: AppColors.gray50, isDense: true,
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: AppColors.gray200)),
                              enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: AppColors.gray200)),
                            ),
                            onSubmitted: (_) => _searchAssets(),
                          ),
                        ),
                        const SizedBox(width: 8),
                        ElevatedButton(
                          onPressed: _searchAssets,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.blue600, foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          ),
                          child: const Icon(Icons.search, size: 16),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    if (_selectedAsset != null)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        decoration: BoxDecoration(
                          color: AppColors.blue50,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: AppColors.blue200),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.inventory_2_rounded, size: 16, color: AppColors.blue600),
                            const SizedBox(width: 8),
                            Expanded(child: Text(_selectedAsset!.name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.blue600))),
                            GestureDetector(
                              onTap: () => setState(() => _selectedAsset = null),
                              child: const Icon(Icons.close_rounded, size: 16, color: AppColors.blue600),
                            ),
                          ],
                        ),
                      )
                    else if (_loadingAssets)
                      const Center(child: Padding(padding: EdgeInsets.all(12), child: CircularProgressIndicator(strokeWidth: 2)))
                    else
                      Container(
                        height: 160,
                        decoration: BoxDecoration(
                          color: AppColors.gray50,
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: AppColors.gray200),
                        ),
                        child: _assets.isEmpty
                            ? const Center(child: Text('Tidak ada aset', style: TextStyle(color: AppColors.gray400, fontSize: 13)))
                            : ListView.separated(
                                padding: const EdgeInsets.all(4),
                                itemCount: _assets.length,
                                separatorBuilder: (_, __) => const Divider(height: 1),
                                itemBuilder: (_, i) {
                                  final a = _assets[i];
                                  return ListTile(
                                    dense: true,
                                    title: Text(a.name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                                    subtitle: Text('${a.categoryLabel} · ${a.conditionLabel}', style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
                                    onTap: () => setState(() => _selectedAsset = a),
                                  );
                                },
                              ),
                      ),
                    const SizedBox(height: 16),
                    const Text('Deskripsi Kerusakan', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700)),
                    const SizedBox(height: 8),
                    TextField(
                      controller: _descCtrl,
                      maxLines: 4,
                      decoration: InputDecoration(
                        hintText: 'Jelaskan kerusakan yang terjadi...',
                        filled: true, fillColor: AppColors.gray50,
                        contentPadding: const EdgeInsets.all(12),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.gray200)),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.gray200)),
                        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.blue600, width: 1.5)),
                      ),
                    ),
                    const SizedBox(height: 24),
                    SizedBox(
                      width: double.infinity,
                      child: FilledButton.icon(
                        onPressed: _submitting ? null : _submit,
                        icon: _submitting
                            ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                            : const Icon(Icons.send_rounded, size: 18),
                        label: Text(_submitting ? 'Mengirim...' : 'Kirim Laporan'),
                        style: FilledButton.styleFrom(
                          backgroundColor: AppColors.red500,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
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
}

// ─── Loans Tab ────────────────────────────────────────────────────────────────

class _LoansTab extends StatefulWidget {
  const _LoansTab();

  @override
  State<_LoansTab> createState() => _LoansTabState();
}

class _LoansTabState extends State<_LoansTab> {
  List<LoanItem> _loans       = [];
  int    _page        = 1;
  int    _lastPage    = 1;
  bool   _loading     = true;
  bool   _loadingMore = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load({bool refresh = true}) async {
    if (refresh) {
      setState(() { _loading = true; _page = 1; });
    } else {
      setState(() => _loadingMore = true);
    }
    try {
      final result = await GuruService.getLoans(page: _page);
      if (mounted) {
        setState(() {
          if (refresh) { _loans = result.data; } else { _loans.addAll(result.data); }
          _lastPage    = result.lastPage;
          _loading     = false;
          _loadingMore = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() { _loading = false; _loadingMore = false; });
    }
  }

  Future<void> _returnLoan(int id) async {
    try {
      await GuruService.returnLoan(id);
      _snack('Peminjaman berhasil dikembalikan', AppColors.emerald600);
      _load();
    } catch (e) {
      _snack(e.toString(), AppColors.red500);
    }
  }

  void _snack(String msg, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg), backgroundColor: color, behavior: SnackBarBehavior.floating,
    ));
  }

  void _openLoanDialog(BuildContext context) async {
    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _RequestLoanSheet(onDone: () { _load(); }),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _openLoanDialog(context),
        backgroundColor: AppColors.blue600,
        icon: const Icon(Icons.add_rounded, color: Colors.white),
        label: const Text('Ajukan Peminjaman', style: TextStyle(color: Colors.white)),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _loans.isEmpty
              ? const Center(child: Text('Belum ada peminjaman', style: TextStyle(color: AppColors.gray400)))
              : NotificationListener<ScrollNotification>(
                  onNotification: (n) {
                    if (n.metrics.pixels >= n.metrics.maxScrollExtent - 100 && _page < _lastPage && !_loadingMore) {
                      _page++; _load(refresh: false);
                    }
                    return false;
                  },
                  child: ListView.builder(
                    padding: const EdgeInsets.fromLTRB(12, 12, 12, 80),
                    itemCount: _loans.length + (_loadingMore ? 1 : 0),
                    itemBuilder: (_, i) {
                      if (i == _loans.length) {
                        return const Center(child: Padding(
                          padding: EdgeInsets.all(16),
                          child: CircularProgressIndicator(strokeWidth: 2),
                        ));
                      }
                      return _LoanCard(loan: _loans[i], onReturn: () => _returnLoan(_loans[i].id));
                    },
                  ),
                ),
    );
  }
}

class _LoanCard extends StatelessWidget {
  final LoanItem loan;
  final VoidCallback onReturn;
  const _LoanCard({required this.loan, required this.onReturn});

  Color get _statusColor {
    return switch (loan.status) {
      'pending'  => AppColors.amber500,
      'approved' => AppColors.blue600,
      'active'   => AppColors.emerald600,
      'returned' => AppColors.gray400,
      'rejected' => AppColors.red500,
      _          => AppColors.gray400,
    };
  }

  @override
  Widget build(BuildContext context) {
    final canReturn = loan.status == 'active' || loan.status == 'approved';
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: AppColors.gray100),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(loan.assetName, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: _statusColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(loan.statusLabel, style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: _statusColor)),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(loan.purpose, style: const TextStyle(fontSize: 12, color: AppColors.gray600)),
          const SizedBox(height: 4),
          Text(
            '${loan.startDate} s/d ${loan.endDate}',
            style: const TextStyle(fontSize: 11, color: AppColors.gray400),
          ),
          if (loan.rejectionNote != null && loan.rejectionNote!.isNotEmpty) ...[
            const SizedBox(height: 6),
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(color: AppColors.red50, borderRadius: BorderRadius.circular(6)),
              child: Text('Alasan ditolak: ${loan.rejectionNote}',
                  style: const TextStyle(fontSize: 11, color: AppColors.red500)),
            ),
          ],
          if (canReturn) ...[
            const SizedBox(height: 10),
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: () async {
                  final confirm = await showDialog<bool>(
                    context: context,
                    builder: (_) => AlertDialog(
                      title: const Text('Konfirmasi Pengembalian'),
                      content: const Text('Tandai peminjaman ini sebagai sudah dikembalikan?'),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      actions: [
                        TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Batal')),
                        FilledButton(onPressed: () => Navigator.pop(context, true), child: const Text('Kembalikan')),
                      ],
                    ),
                  );
                  if (confirm == true) onReturn();
                },
                icon: const Icon(Icons.assignment_return_rounded, size: 16),
                label: const Text('Tandai Dikembalikan'),
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppColors.emerald600,
                  side: const BorderSide(color: AppColors.emerald600),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  padding: const EdgeInsets.symmetric(vertical: 8),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _RequestLoanSheet extends StatefulWidget {
  final VoidCallback onDone;
  const _RequestLoanSheet({required this.onDone});

  @override
  State<_RequestLoanSheet> createState() => _RequestLoanSheetState();
}

class _RequestLoanSheetState extends State<_RequestLoanSheet> {
  List<AssetItem> _assets       = [];
  AssetItem?      _selectedAsset;
  bool  _loadingAssets = true;
  bool  _submitting    = false;
  DateTime? _startDate;
  DateTime? _endDate;
  final _purposeCtrl = TextEditingController();
  final _searchCtrl  = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadAssets();
  }

  @override
  void dispose() {
    _purposeCtrl.dispose();
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadAssets() async {
    try {
      final result = await GuruService.getAssets();
      if (mounted) setState(() { _assets = result.data; _loadingAssets = false; });
    } catch (_) {
      if (mounted) setState(() => _loadingAssets = false);
    }
  }

  Future<void> _searchAssets() async {
    final q = _searchCtrl.text.trim();
    setState(() => _loadingAssets = true);
    try {
      final result = await GuruService.getAssets(q: q.isEmpty ? null : q);
      if (mounted) setState(() { _assets = result.data; _loadingAssets = false; });
    } catch (_) {
      if (mounted) setState(() => _loadingAssets = false);
    }
  }

  String _fmt(DateTime? d) {
    if (d == null) return '—';
    return '${d.year}-${d.month.toString().padLeft(2,'0')}-${d.day.toString().padLeft(2,'0')}';
  }

  Future<void> _pickDate(bool isStart) async {
    final now  = DateTime.now();
    final init = isStart ? (_startDate ?? now) : (_endDate ?? (_startDate ?? now));
    final first = isStart ? now : (_startDate ?? now);
    final picked = await showDatePicker(
      context: context,
      initialDate: init.isBefore(first) ? first : init,
      firstDate:   first,
      lastDate:    DateTime(now.year + 2),
    );
    if (picked != null && mounted) {
      setState(() {
        if (isStart) {
          _startDate = picked;
          if (_endDate != null && _endDate!.isBefore(picked)) _endDate = null;
        } else {
          _endDate = picked;
        }
      });
    }
  }

  Future<void> _submit() async {
    if (_selectedAsset == null) { _snack('Pilih aset', AppColors.orange500); return; }
    if (_startDate == null) { _snack('Pilih tanggal mulai', AppColors.orange500); return; }
    if (_endDate == null)   { _snack('Pilih tanggal selesai', AppColors.orange500); return; }
    if (_purposeCtrl.text.trim().isEmpty) { _snack('Isi tujuan peminjaman', AppColors.orange500); return; }

    setState(() => _submitting = true);
    try {
      await GuruService.storeLoan(
        assetId:   _selectedAsset!.id,
        startDate: _fmt(_startDate),
        endDate:   _fmt(_endDate),
        purpose:   _purposeCtrl.text.trim(),
      );
      if (mounted) {
        Navigator.pop(context);
        widget.onDone();
        _snack('Permintaan peminjaman terkirim', AppColors.emerald600);
      }
    } catch (e) {
      if (mounted) { setState(() => _submitting = false); _snack(e.toString(), AppColors.red500); }
    }
  }

  void _snack(String msg, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg), backgroundColor: color, behavior: SnackBarBehavior.floating,
    ));
  }

  @override
  Widget build(BuildContext context) {
    return DraggableScrollableSheet(
      initialChildSize: 0.9,
      minChildSize:     0.5,
      maxChildSize:     0.95,
      builder: (_, scrollCtrl) => Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
        ),
        child: Column(
          children: [
            Container(
              width: 40, height: 4,
              margin: const EdgeInsets.symmetric(vertical: 10),
              decoration: BoxDecoration(color: AppColors.gray200, borderRadius: BorderRadius.circular(2)),
            ),
            const Padding(
              padding: EdgeInsets.symmetric(horizontal: 16),
              child: Text('Ajukan Peminjaman', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
            ),
            const SizedBox(height: 12),
            Expanded(
              child: SingleChildScrollView(
                controller: scrollCtrl,
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 32),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Pilih Aset
                    const Text('Pilih Aset', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700)),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: _searchCtrl,
                            decoration: InputDecoration(
                              hintText: 'Cari aset...',
                              prefixIcon: const Icon(Icons.search, size: 16, color: AppColors.gray400),
                              filled: true, fillColor: AppColors.gray50, isDense: true,
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: AppColors.gray200)),
                              enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: AppColors.gray200)),
                            ),
                            onSubmitted: (_) => _searchAssets(),
                          ),
                        ),
                        const SizedBox(width: 8),
                        ElevatedButton(
                          onPressed: _searchAssets,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.blue600, foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          ),
                          child: const Icon(Icons.search, size: 16),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    if (_selectedAsset != null)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        decoration: BoxDecoration(
                          color: AppColors.blue50,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: AppColors.blue200),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.inventory_2_rounded, size: 16, color: AppColors.blue600),
                            const SizedBox(width: 8),
                            Expanded(child: Text(_selectedAsset!.name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.blue600))),
                            GestureDetector(onTap: () => setState(() => _selectedAsset = null),
                                child: const Icon(Icons.close_rounded, size: 16, color: AppColors.blue600)),
                          ],
                        ),
                      )
                    else if (_loadingAssets)
                      const Center(child: Padding(padding: EdgeInsets.all(12), child: CircularProgressIndicator(strokeWidth: 2)))
                    else
                      Container(
                        height: 150,
                        decoration: BoxDecoration(color: AppColors.gray50, borderRadius: BorderRadius.circular(10), border: Border.all(color: AppColors.gray200)),
                        child: _assets.isEmpty
                            ? const Center(child: Text('Tidak ada aset', style: TextStyle(color: AppColors.gray400)))
                            : ListView.separated(
                                padding: const EdgeInsets.all(4),
                                itemCount: _assets.length,
                                separatorBuilder: (_, __) => const Divider(height: 1),
                                itemBuilder: (_, i) => ListTile(
                                  dense: true,
                                  title: Text(_assets[i].name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                                  subtitle: Text(_assets[i].categoryLabel, style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
                                  onTap: () => setState(() => _selectedAsset = _assets[i]),
                                ),
                              ),
                      ),
                    const SizedBox(height: 16),

                    // Tanggal
                    Row(
                      children: [
                        Expanded(child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Tanggal Mulai', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700)),
                            const SizedBox(height: 6),
                            GestureDetector(
                              onTap: () => _pickDate(true),
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                                decoration: BoxDecoration(color: AppColors.gray50, borderRadius: BorderRadius.circular(8), border: Border.all(color: AppColors.gray200)),
                                child: Row(children: [
                                  const Icon(Icons.calendar_today_rounded, size: 14, color: AppColors.gray400),
                                  const SizedBox(width: 6),
                                  Text(_fmt(_startDate), style: const TextStyle(fontSize: 13)),
                                ]),
                              ),
                            ),
                          ],
                        )),
                        const SizedBox(width: 12),
                        Expanded(child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Tanggal Selesai', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700)),
                            const SizedBox(height: 6),
                            GestureDetector(
                              onTap: () => _pickDate(false),
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                                decoration: BoxDecoration(color: AppColors.gray50, borderRadius: BorderRadius.circular(8), border: Border.all(color: AppColors.gray200)),
                                child: Row(children: [
                                  const Icon(Icons.calendar_today_rounded, size: 14, color: AppColors.gray400),
                                  const SizedBox(width: 6),
                                  Text(_fmt(_endDate), style: const TextStyle(fontSize: 13)),
                                ]),
                              ),
                            ),
                          ],
                        )),
                      ],
                    ),
                    const SizedBox(height: 16),

                    // Tujuan
                    const Text('Tujuan Peminjaman', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700)),
                    const SizedBox(height: 8),
                    TextField(
                      controller: _purposeCtrl,
                      maxLines: 3,
                      decoration: InputDecoration(
                        hintText: 'Jelaskan tujuan peminjaman...',
                        filled: true, fillColor: AppColors.gray50,
                        contentPadding: const EdgeInsets.all(12),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.gray200)),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.gray200)),
                        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.blue600, width: 1.5)),
                      ),
                    ),
                    const SizedBox(height: 24),
                    SizedBox(
                      width: double.infinity,
                      child: FilledButton.icon(
                        onPressed: _submitting ? null : _submit,
                        icon: _submitting
                            ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                            : const Icon(Icons.send_rounded, size: 18),
                        label: Text(_submitting ? 'Mengirim...' : 'Kirim Permintaan'),
                        style: FilledButton.styleFrom(
                          backgroundColor: AppColors.blue600,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
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
}

// ─── Shared widgets ───────────────────────────────────────────────────────────

class _FilterChip extends StatelessWidget {
  final String label;
  final bool   selected;
  final Color  color;
  final VoidCallback onTap;

  const _FilterChip({
    required this.label,
    required this.selected,
    this.color = AppColors.blue600,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: selected ? color.withValues(alpha: 0.12) : AppColors.gray100,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: selected ? color : AppColors.gray200),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w600,
            color: selected ? color : AppColors.gray500,
          ),
        ),
      ),
    );
  }
}
