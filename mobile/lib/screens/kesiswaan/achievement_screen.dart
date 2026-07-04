import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:dio/dio.dart';
import '../../models/achievement.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';

class AchievementScreen extends StatefulWidget {
  const AchievementScreen({super.key});

  @override
  State<AchievementScreen> createState() => _AchievementScreenState();
}

class _AchievementScreenState extends State<AchievementScreen> {
  AchievementStats?    _stats;
  List<Achievement>    _items     = [];
  bool                 _isLoading = true;
  String?              _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _isLoading = true; _error = null; });
    try {
      final body = await ApiClient.get('/achievements');
      setState(() {
        _stats = AchievementStats.fromJson(body['stats'] as Map<String, dynamic>);
        _items = (body['achievements'] as List)
            .map((e) => Achievement.fromJson(e as Map<String, dynamic>))
            .toList();
      });
    } catch (e) {
      setState(() => _error = ApiClient.extractError(e));
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _openCreate() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _CreateSheet(onCreated: () { Navigator.pop(context); _load(); }),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Prestasi',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 16)),
        backgroundColor: const Color(0xFF0F2460),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _openCreate,
        backgroundColor: AppColors.yellow600,
        icon: const Icon(Icons.add_rounded),
        label: const Text('Laporkan Prestasi'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _ErrorView(message: _error!, onRetry: _load)
              : RefreshIndicator(
                  onRefresh: _load,
                  child: CustomScrollView(
                    slivers: [
                      if (_stats != null)
                        SliverToBoxAdapter(child: _StatsBar(stats: _stats!)),
                      if (_items.isEmpty)
                        const SliverFillRemaining(
                          child: Center(
                            child: Column(mainAxisSize: MainAxisSize.min, children: [
                              Icon(Icons.workspace_premium_rounded, size: 56, color: AppColors.gray300),
                              SizedBox(height: 12),
                              Text('Belum ada prestasi yang dilaporkan',
                                style: TextStyle(fontSize: 13, color: AppColors.gray400)),
                            ]),
                          ),
                        )
                      else
                        SliverPadding(
                          padding: EdgeInsets.fromLTRB(16, 0, 16, 100 + MediaQuery.of(context).padding.bottom),
                          sliver: SliverList.separated(
                            itemCount: _items.length,
                            separatorBuilder: (_, __) => const SizedBox(height: 8),
                            itemBuilder: (_, i) => _AchievementCard(item: _items[i]),
                          ),
                        ),
                    ],
                  ),
                ),
    );
  }
}

// ─── Stats Bar ────────────────────────────────────────────────────────────────

class _StatsBar extends StatelessWidget {
  final AchievementStats stats;
  const _StatsBar({required this.stats});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white, borderRadius: AppRadius.card,
        border: Border.all(color: AppColors.gray100), boxShadow: AppShadow.sm,
      ),
      child: Row(children: [
        _StatCell(count: stats.pending,  label: 'Menunggu', color: AppColors.amber500),
        _Divider(),
        _StatCell(count: stats.approved, label: 'Disetujui', color: AppColors.green500),
        _Divider(),
        _StatCell(count: stats.rejected, label: 'Ditolak',  color: AppColors.red500),
      ]),
    );
  }
}

class _StatCell extends StatelessWidget {
  final int count; final String label; final Color color;
  const _StatCell({required this.count, required this.label, required this.color});
  @override
  Widget build(BuildContext context) => Expanded(child: Column(children: [
    Text('$count', style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color)),
    Text(label, style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
  ]));
}

class _Divider extends StatelessWidget {
  @override
  Widget build(BuildContext context) =>
    Container(width: 1, height: 32, color: AppColors.gray100);
}

// ─── Achievement Card ─────────────────────────────────────────────────────────

class _AchievementCard extends StatelessWidget {
  final Achievement item;
  const _AchievementCard({required this.item});

  String _fmtDate(String s) {
    try {
      final d = DateTime.parse(s);
      const m = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
      return '${d.day} ${m[d.month]} ${d.year}';
    } catch (_) { return s; }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white, borderRadius: AppRadius.card,
        border: Border.all(color: AppColors.gray100), boxShadow: AppShadow.sm,
      ),
      padding: const EdgeInsets.all(14),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(color: item.levelBg, borderRadius: BorderRadius.circular(20)),
            child: Text(item.levelLabel,
              style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: item.levelColor)),
          ),
          const SizedBox(width: 6),
          if (item.rank != null) ...[
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(color: AppColors.yellow50, borderRadius: BorderRadius.circular(20)),
              child: Text(item.rank!,
                style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.yellow600)),
            ),
            const SizedBox(width: 6),
          ],
          const Spacer(),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(color: item.statusBg, borderRadius: BorderRadius.circular(20)),
            child: Text(item.statusLabel,
              style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: item.statusColor)),
          ),
        ]),
        const SizedBox(height: 8),
        Text(item.title,
          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray800)),
        if (item.categoryName != null) ...[
          const SizedBox(height: 2),
          Text(item.categoryName!,
            style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
        ],
        const SizedBox(height: 4),
        Row(children: [
          const Icon(Icons.calendar_today_rounded, size: 11, color: AppColors.gray400),
          const SizedBox(width: 4),
          Text(_fmtDate(item.achievementDate),
            style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
        ]),
        if (item.description != null && item.description!.isNotEmpty) ...[
          const SizedBox(height: 6),
          Text(item.description!,
            style: const TextStyle(fontSize: 12, color: AppColors.gray500),
            maxLines: 2, overflow: TextOverflow.ellipsis),
        ],
        if (item.rejectionReason != null && item.rejectionReason!.isNotEmpty) ...[
          const SizedBox(height: 6),
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: AppColors.red50, borderRadius: BorderRadius.circular(8)),
            child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
              const Icon(Icons.info_outline_rounded, size: 13, color: AppColors.red500),
              const SizedBox(width: 6),
              Expanded(child: Text(item.rejectionReason!,
                style: const TextStyle(fontSize: 11, color: AppColors.red500))),
            ]),
          ),
        ],
      ]),
    );
  }
}

// ─── Create Sheet ─────────────────────────────────────────────────────────────

class _CreateSheet extends StatefulWidget {
  final VoidCallback onCreated;
  const _CreateSheet({required this.onCreated});

  @override
  State<_CreateSheet> createState() => _CreateSheetState();
}

class _CreateSheetState extends State<_CreateSheet> {
  final _titleCtrl = TextEditingController();
  final _rankCtrl  = TextEditingController();
  final _descCtrl  = TextEditingController();

  List<AchievementCategory> _categories = [];
  int?      _categoryId;
  String?   _level;
  DateTime? _date;
  XFile?    _photo;
  XFile?    _certificate;
  bool      _isSaving      = false;
  bool      _loadingCats   = true;

  static const _levels = [
    ('sekolah', 'Sekolah'),
    ('kabupaten', 'Kabupaten/Kota'),
    ('provinsi', 'Provinsi'),
    ('nasional', 'Nasional'),
    ('internasional', 'Internasional'),
  ];

  @override
  void initState() {
    super.initState();
    _loadCategories();
  }

  @override
  void dispose() {
    _titleCtrl.dispose();
    _rankCtrl.dispose();
    _descCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadCategories() async {
    try {
      final body = await ApiClient.get('/achievement-categories');
      if (!mounted) return;
      setState(() {
        _categories = (body['categories'] as List)
            .map((e) => AchievementCategory.fromJson(e as Map<String, dynamic>))
            .toList();
        _loadingCats = false;
      });
    } catch (_) {
      if (mounted) setState(() => _loadingCats = false);
    }
  }

  Future<void> _pickDate() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: now,
      firstDate: DateTime(now.year - 3),
      lastDate: now,
      helpText: 'Tanggal prestasi',
    );
    if (picked != null && mounted) setState(() => _date = picked);
  }

  Future<void> _pickPhoto({bool isCertificate = false}) async {
    // Cek permission galeri: READ_MEDIA_IMAGES (Android 13+) atau storage (≤12)
    final photoStatus   = await Permission.photos.status;
    final storageStatus = await Permission.storage.status;

    if (photoStatus.isPermanentlyDenied || storageStatus.isPermanentlyDenied) {
      if (mounted) _showSettingsDialog();
      return;
    }

    final picker = ImagePicker();
    final file   = await picker.pickImage(
      source:       ImageSource.gallery,
      imageQuality: 85,
    );

    if (file != null && mounted) {
      setState(() {
        if (isCertificate) _certificate = file; else _photo = file;
      });
      return;
    }

    // Jika null dan permission kini blocked → tampilkan dialog
    if (mounted) {
      final blocked = (await Permission.photos.status).isPermanentlyDenied ||
                      (await Permission.storage.status).isPermanentlyDenied;
      if (blocked) _showSettingsDialog();
    }
  }

  void _showSettingsDialog() {
    showDialog<void>(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: AppRadius.card),
        title: const Text('Izin Galeri Diblokir',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
        content: const Text(
          'Izin akses galeri diblokir secara permanen.\n\n'
          'Buka Pengaturan → Aplikasi → SIMS_DOSMAN → Izin → '
          'File dan media → Izinkan.',
          style: TextStyle(fontSize: 13, color: AppColors.gray500, height: 1.5)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal', style: TextStyle(color: AppColors.gray500)),
          ),
          FilledButton(
            onPressed: () { Navigator.pop(context); openAppSettings(); },
            style: FilledButton.styleFrom(backgroundColor: AppColors.blue600),
            child: const Text('Buka Pengaturan'),
          ),
        ],
      ),
    );
  }

  Future<void> _submit() async {
    if (_titleCtrl.text.trim().isEmpty)   { _showSnack('Judul prestasi tidak boleh kosong.'); return; }
    if (_categoryId == null)              { _showSnack('Pilih kategori prestasi.'); return; }
    if (_level == null)                   { _showSnack('Pilih tingkat prestasi.'); return; }
    if (_date == null)                    { _showSnack('Pilih tanggal prestasi.'); return; }
    if (_photo == null)                   { _showSnack('Foto kegiatan wajib dipilih.'); return; }

    setState(() => _isSaving = true);
    try {
      final dateStr = '${_date!.year}-${_date!.month.toString().padLeft(2,'0')}-${_date!.day.toString().padLeft(2,'0')}';
      final formData = FormData.fromMap({
        'title':            _titleCtrl.text.trim(),
        'category_id':      _categoryId.toString(),
        'level':            _level!,
        'achievement_date': dateStr,
        if (_rankCtrl.text.trim().isNotEmpty) 'rank': _rankCtrl.text.trim(),
        if (_descCtrl.text.trim().isNotEmpty) 'description': _descCtrl.text.trim(),
        'photo': await MultipartFile.fromFile(_photo!.path, filename: 'photo.jpg'),
        if (_certificate != null)
          'certificate': await MultipartFile.fromFile(_certificate!.path, filename: 'certificate.jpg'),
      });

      await ApiClient.postForm('/achievements', formData);
      widget.onCreated();
    } catch (e) {
      _showSnack(ApiClient.extractError(e));
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  void _showSnack(String msg) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg), backgroundColor: AppColors.red500,
      behavior: SnackBarBehavior.floating));
  }

  @override
  Widget build(BuildContext context) {
    final bottom = MediaQuery.of(context).viewInsets.bottom;
    final safeBot = MediaQuery.of(context).padding.bottom;
    return Container(
      margin: const EdgeInsets.fromLTRB(12, 0, 12, 12),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.all(Radius.circular(20)),
      ),
      child: DraggableScrollableSheet(
        initialChildSize: 0.9,
        minChildSize: 0.5,
        maxChildSize: 0.95,
        expand: false,
        builder: (_, scrollCtrl) => ListView(
          controller: scrollCtrl,
          padding: EdgeInsets.fromLTRB(20, 20, 20, 20 + bottom + safeBot),
          children: [
            Center(child: Container(
              width: 40, height: 4,
              decoration: BoxDecoration(
                color: AppColors.gray200, borderRadius: BorderRadius.circular(2)),
            )),
            const SizedBox(height: 16),
            const Text('Laporkan Prestasi',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.gray800)),
            const SizedBox(height: 4),
            const Text('Prestasi perlu diverifikasi oleh admin.',
              style: TextStyle(fontSize: 11, color: AppColors.gray400)),
            const SizedBox(height: 20),

            // Judul
            _Label('Judul Prestasi *'),
            const SizedBox(height: 6),
            _InputField(controller: _titleCtrl, hint: 'Contoh: Juara 1 Lomba Matematika...'),
            const SizedBox(height: 14),

            // Kategori
            _Label('Kategori *'),
            const SizedBox(height: 6),
            if (_loadingCats)
              const Center(child: SizedBox(height: 36, width: 36, child: CircularProgressIndicator(strokeWidth: 2)))
            else
              DropdownButtonFormField<int>(
                value: _categoryId,
                hint: const Text('Pilih kategori', style: TextStyle(fontSize: 13, color: AppColors.gray400)),
                items: _categories.map((c) => DropdownMenuItem(
                  value: c.id,
                  child: Text(c.name, style: const TextStyle(fontSize: 13)),
                )).toList(),
                onChanged: (v) => setState(() => _categoryId = v),
                decoration: InputDecoration(
                  filled: true, fillColor: AppColors.gray50,
                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                  border: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: const BorderSide(color: AppColors.gray200)),
                  enabledBorder: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: const BorderSide(color: AppColors.gray200)),
                ),
              ),
            const SizedBox(height: 14),

            // Tingkat
            _Label('Tingkat Prestasi *'),
            const SizedBox(height: 8),
            Wrap(spacing: 8, runSpacing: 8, children: _levels.map(((String val, String lbl) pair) {
              final selected = _level == pair.$1;
              return GestureDetector(
                onTap: () => setState(() => _level = pair.$1),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 150),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  decoration: BoxDecoration(
                    color: selected ? const Color(0xFF0F2460) : AppColors.gray50,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: selected ? const Color(0xFF0F2460) : AppColors.gray200),
                  ),
                  child: Text(pair.$2,
                    style: TextStyle(
                      fontSize: 12, fontWeight: FontWeight.w500,
                      color: selected ? Colors.white : AppColors.gray600,
                    )),
                ),
              );
            }).toList()),
            const SizedBox(height: 14),

            // Peringkat + Tanggal
            Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                _Label('Peringkat'),
                const SizedBox(height: 6),
                _InputField(controller: _rankCtrl, hint: 'Juara 1, Medali Emas...'),
              ])),
              const SizedBox(width: 10),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                _Label('Tanggal *'),
                const SizedBox(height: 6),
                GestureDetector(
                  onTap: _pickDate,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 13),
                    decoration: BoxDecoration(
                      color: AppColors.gray50, borderRadius: AppRadius.input,
                      border: Border.all(color: AppColors.gray200),
                    ),
                    child: Row(children: [
                      const Icon(Icons.calendar_today_rounded, size: 14, color: AppColors.gray400),
                      const SizedBox(width: 6),
                      Expanded(child: Text(
                        _date != null
                          ? '${_date!.day}/${_date!.month}/${_date!.year}'
                          : 'Pilih tanggal',
                        style: TextStyle(
                          fontSize: 12,
                          color: _date != null ? AppColors.gray700 : AppColors.gray400,
                        ),
                        overflow: TextOverflow.ellipsis,
                      )),
                    ]),
                  ),
                ),
              ])),
            ]),
            const SizedBox(height: 14),

            // Deskripsi
            _Label('Deskripsi'),
            const SizedBox(height: 6),
            TextField(
              controller: _descCtrl, maxLines: 3, maxLength: 1000,
              style: const TextStyle(fontSize: 13, color: AppColors.gray700),
              decoration: InputDecoration(
                hintText: 'Ceritakan lebih lanjut tentang prestasi ini...',
                hintStyle: const TextStyle(color: AppColors.gray400, fontSize: 12),
                filled: true, fillColor: AppColors.gray50,
                counterStyle: const TextStyle(fontSize: 10),
                contentPadding: const EdgeInsets.all(12),
                border: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: const BorderSide(color: AppColors.gray200)),
                enabledBorder: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: const BorderSide(color: AppColors.gray200)),
              ),
            ),
            const SizedBox(height: 14),

            // Foto kegiatan
            _Label('Foto Kegiatan *'),
            const SizedBox(height: 6),
            _ImagePickerTile(
              file: _photo,
              label: 'Pilih foto kegiatan',
              onTap: () => _pickPhoto(),
            ),
            const SizedBox(height: 14),

            // Sertifikat
            _Label('Sertifikat (opsional)'),
            const SizedBox(height: 6),
            _ImagePickerTile(
              file: _certificate,
              label: 'Pilih scan sertifikat',
              onTap: () => _pickPhoto(isCertificate: true),
            ),
            const SizedBox(height: 20),

            FilledButton(
              onPressed: _isSaving ? null : _submit,
              style: FilledButton.styleFrom(
                backgroundColor: AppColors.yellow600,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
              ),
              child: _isSaving
                  ? const SizedBox(width: 18, height: 18,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : const Text('Kirim Laporan Prestasi',
                      style: TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

class _Label extends StatelessWidget {
  final String text;
  const _Label(this.text);
  @override
  Widget build(BuildContext context) => Text(text,
    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.gray600));
}

class _InputField extends StatelessWidget {
  final TextEditingController controller;
  final String hint;
  const _InputField({required this.controller, required this.hint});
  @override
  Widget build(BuildContext context) => TextField(
    controller: controller,
    style: const TextStyle(fontSize: 13, color: AppColors.gray700),
    decoration: InputDecoration(
      hintText: hint,
      hintStyle: const TextStyle(color: AppColors.gray400, fontSize: 12),
      filled: true, fillColor: AppColors.gray50,
      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
      border: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: const BorderSide(color: AppColors.gray200)),
      enabledBorder: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: const BorderSide(color: AppColors.gray200)),
      focusedBorder: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: const BorderSide(color: AppColors.yellow600, width: 2)),
    ),
  );
}

class _ImagePickerTile extends StatelessWidget {
  final XFile?       file;
  final String       label;
  final VoidCallback onTap;
  const _ImagePickerTile({required this.file, required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: AppColors.gray50, borderRadius: AppRadius.input,
          border: Border.all(
            color: file != null ? AppColors.yellow600 : AppColors.gray200,
            width: file != null ? 1.5 : 1,
          ),
        ),
        child: file != null
            ? Row(children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(6),
                  child: Image.file(File(file!.path), width: 48, height: 48, fit: BoxFit.cover),
                ),
                const SizedBox(width: 10),
                Expanded(child: Text(file!.name,
                  style: const TextStyle(fontSize: 12, color: AppColors.gray700),
                  overflow: TextOverflow.ellipsis)),
                const Icon(Icons.edit_rounded, size: 14, color: AppColors.gray400),
              ])
            : Row(children: [
                const Icon(Icons.image_outlined, size: 18, color: AppColors.gray400),
                const SizedBox(width: 8),
                Text(label, style: const TextStyle(fontSize: 12, color: AppColors.gray400)),
              ]),
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
      Text(message,
        style: const TextStyle(fontSize: 13, color: AppColors.gray500),
        textAlign: TextAlign.center),
      const SizedBox(height: 12),
      TextButton(onPressed: onRetry, child: const Text('Coba Lagi')),
    ]),
  );
}
