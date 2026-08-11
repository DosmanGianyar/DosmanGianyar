import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/guru_models.dart';
import '../../providers/auth_provider.dart';
import '../../services/guru_service.dart';
import '../../theme/app_colors.dart';
import '../../widgets/image_viewer_dialog.dart';
import 'widgets/guru_widgets.dart';

class GuruPermitScreen extends StatefulWidget {
  const GuruPermitScreen({super.key});

  @override
  State<GuruPermitScreen> createState() => _GuruPermitScreenState();
}

class _GuruPermitScreenState extends State<GuruPermitScreen> {
  String _status           = 'pending';
  int?   _selectedClassId; // null = Semua Kelas
  List<GuruClass> _classes = [];
  bool   _loadingClasses   = true;

  final List<GuruPermit> _items = [];
  int  _page    = 1;
  bool _loading = false;
  bool _hasMore = true;
  String? _error;

  final _scroll = ScrollController();

  @override
  void initState() {
    super.initState();
    _initClassAndLoad();
    _scroll.addListener(() {
      if (_scroll.position.pixels >= _scroll.position.maxScrollExtent - 200 && !_loading && _hasMore) {
        _load();
      }
    });
  }

  @override
  void dispose() {
    _scroll.dispose();
    super.dispose();
  }

  Future<void> _initClassAndLoad() async {
    final user = context.read<AuthProvider>().user;
    if (user?.homeroomClassId != null) {
      _selectedClassId = user!.homeroomClassId;
    }
    _load(reset: true);
    try {
      final classes = await GuruService.getClasses();
      if (mounted) {
        setState(() {
          _classes = classes;
          _loadingClasses = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loadingClasses = false);
    }
  }

  Future<void> _load({bool reset = false}) async {
    if (_loading) return;
    if (reset) { _page = 1; _hasMore = true; }
    setState(() { _loading = true; _error = null; });
    try {
      final result = await GuruService.getPermits(status: _status, classId: _selectedClassId, page: _page);
      if (mounted) {
        setState(() {
          if (reset) _items.clear();
          _items.addAll(result.data);
          _hasMore = result.meta.hasMore;
          _page++;
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) setState(() { _error = e.toString(); _loading = false; });
    }
  }

  Future<void> _approve(GuruPermit permit) async {
    final confirm = await showApproveDialog(context, title: 'Setujui ${permit.typeLabel}?');
    if (confirm != true || !mounted) return;
    try {
      await GuruService.approvePermit(permit.id);
      _load(reset: true);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('${permit.typeLabel} disetujui'), backgroundColor: AppColors.green600),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppColors.red500),
        );
      }
    }
  }

  Future<void> _reject(GuruPermit permit) async {
    final note = await showRejectDialog(context, title: 'Tolak ${permit.typeLabel}');
    if (note == null || !mounted) return;
    try {
      await GuruService.rejectPermit(permit.id, note);
      _load(reset: true);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Pengajuan ditolak'), backgroundColor: AppColors.red500),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppColors.red500),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(title: const Text('Persetujuan Izin / Sakit / Dispen')),
      body: Column(
        children: [
          _buildClassSelector(),
          _buildTabBar(),
          Expanded(child: _buildBody()),
        ],
      ),
    );
  }

  Widget _buildClassSelector() {
    final user = context.watch<AuthProvider>().user;
    final homeroomId = user?.homeroomClassId;

    return Container(
      color: Colors.white,
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 6),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.class_outlined, size: 16, color: AppColors.blue600),
              const SizedBox(width: 6),
              const Text('Filter Kelas:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.gray700)),
              const Spacer(),
              if (homeroomId != null && _selectedClassId == homeroomId)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(
                    color: AppColors.emerald50,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppColors.emerald600.withOpacity(0.3)),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.star_rounded, size: 12, color: AppColors.emerald600),
                      SizedBox(width: 4),
                      Text('Perwalian Anda', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.emerald700)),
                    ],
                  ),
                ),
            ],
          ),
          const SizedBox(height: 6),
          if (_loadingClasses)
            const SizedBox(height: 36, child: Center(child: SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2))))
          else
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12),
              decoration: BoxDecoration(
                color: AppColors.gray50,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: AppColors.gray200),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<int?>(
                  value: _selectedClassId,
                  isExpanded: true,
                  icon: const Icon(Icons.keyboard_arrow_down_rounded, color: AppColors.gray500),
                  items: [
                    DropdownMenuItem<int?>(
                      value: null,
                      child: Text(
                        homeroomId == null ? 'Semua Kelas (Pilih Kelas)' : 'Semua Kelas',
                        style: const TextStyle(fontSize: 13, color: AppColors.gray700),
                      ),
                    ),
                    ..._classes.map((c) {
                      final isHome = c.id == homeroomId;
                      return DropdownMenuItem<int?>(
                        value: c.id,
                        child: Text(
                          isHome ? '${c.name} (Perwalian Anda ⭐)' : c.name,
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: isHome ? FontWeight.bold : FontWeight.normal,
                            color: isHome ? AppColors.blue600 : AppColors.gray800,
                          ),
                        ),
                      );
                    }),
                  ],
                  onChanged: (val) {
                    if (_selectedClassId != val) {
                      setState(() => _selectedClassId = val);
                      _load(reset: true);
                    }
                  },
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildTabBar() {
    return Container(
      color: AppColors.white,
      padding: const EdgeInsets.fromLTRB(16, 4, 16, 10),
      child: Row(
        children: [
          for (final s in [('pending', 'Menunggu'), ('approved', 'Disetujui'), ('rejected', 'Ditolak')])
            Expanded(
              child: GestureDetector(
                onTap: () { _status = s.$1; _load(reset: true); },
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 150),
                  margin: const EdgeInsets.symmetric(horizontal: 3),
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  decoration: BoxDecoration(
                    color: _status == s.$1 ? AppColors.blue600 : AppColors.gray100,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    s.$2,
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: _status == s.$1 ? Colors.white : AppColors.gray500,
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildBody() {
    if (_loading && _items.isEmpty) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_error != null && _items.isEmpty) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(_error!, style: const TextStyle(color: AppColors.gray500)),
            const SizedBox(height: 8),
            ElevatedButton(onPressed: () => _load(reset: true), child: const Text('Coba Lagi')),
          ],
        ),
      );
    }
    if (_items.isEmpty) {
      return const Center(
        child: Text('Tidak ada data pengajuan', style: TextStyle(color: AppColors.gray400)),
      );
    }
    return RefreshIndicator(
      onRefresh: () => _load(reset: true),
      child: ListView.separated(
        controller: _scroll,
        padding: const EdgeInsets.all(16),
        itemCount: _items.length + (_hasMore ? 1 : 0),
        separatorBuilder: (_, __) => const SizedBox(height: 10),
        itemBuilder: (context, i) {
          if (i == _items.length) {
            return const Center(
              child: Padding(
                padding: EdgeInsets.symmetric(vertical: 16),
                child: CircularProgressIndicator(),
              ),
            );
          }
          final item = _items[i];
          return _buildCard(item);
        },
      ),
    );
  }

  Widget _buildCard(GuruPermit item) {
    final dateStr = item.startDate == item.endDate ? item.startDate : '${item.startDate} s/d ${item.endDate}';
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.gray200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      item.studentName,
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: AppColors.gray800),
                    ),
                    Text(
                      item.className,
                      style: const TextStyle(fontSize: 12, color: AppColors.gray500),
                    ),
                  ],
                ),
              ),
              _buildTypeChip(item),
            ],
          ),
          const Divider(height: 16),
          Row(
            children: [
              const Icon(Icons.calendar_today_outlined, size: 14, color: AppColors.gray500),
              const SizedBox(width: 6),
              Text(
                dateStr,
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.gray700),
              ),
            ],
          ),
          if (item.reason.isNotEmpty) ...[
            const SizedBox(height: 6),
            Text(
              item.reason,
              style: const TextStyle(fontSize: 12, color: AppColors.gray600),
            ),
          ],
          if (item.fileUrl != null) ...[
            const SizedBox(height: 8),
            GestureDetector(
              onTap: () => ImageViewerDialog.show(context, imageUrl: item.fileUrl!),
              child: const Row(
                children: [
                  Icon(Icons.attach_file_rounded, size: 14, color: AppColors.blue600),
                  SizedBox(width: 4),
                  Text('Lihat Berkas Lampiran', style: TextStyle(fontSize: 12, color: AppColors.blue600, fontWeight: FontWeight.w600)),
                ],
              ),
            ),
          ],
          if (item.isPending) ...[
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => _reject(item),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.red600,
                      side: const BorderSide(color: AppColors.red500),
                      padding: const EdgeInsets.symmetric(vertical: 10),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                    child: const Text('Tolak'),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: ElevatedButton(
                    onPressed: () => _approve(item),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.green600,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 10),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                    child: const Text('Setujui'),
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildTypeChip(GuruPermit item) {
    Color bg;
    Color fg;
    switch (item.type) {
      case 'sakit':
        bg = AppColors.amber50;
        fg = AppColors.amber700;
        break;
      case 'dispensasi':
        bg = const Color(0xFFFAF5FF);
        fg = const Color(0xFF7E22CE);
        break;
      default:
        bg = AppColors.blue50;
        fg = AppColors.blue700;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(6)),
      child: Text(
        item.typeLabel,
        style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: fg),
      ),
    );
  }
}
