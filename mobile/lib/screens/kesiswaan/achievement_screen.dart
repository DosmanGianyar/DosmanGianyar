import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
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
        title: const Text('Kurasi Prestasi Siswa',
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
        _StatCell(count: stats.pending,  label: 'Menunggu Kurasi', color: AppColors.amber500),
        _Divider(),
        _StatCell(count: stats.approved, label: 'Lolos Kurasi', color: AppColors.green500),
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
    Text(label, style: const TextStyle(fontSize: 10, color: AppColors.gray400), textAlign: TextAlign.center),
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
            child: Text(item.curationStatusLabel,
              style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: item.statusColor)),
          ),
        ]),
        const SizedBox(height: 8),
        Text(item.title,
          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray800)),
        if (item.organizer != null && item.organizer!.isNotEmpty) ...[
          const SizedBox(height: 2),
          Text('Penyelenggara: ${item.organizer}',
            style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w500, color: AppColors.blue600)),
        ],
        if (item.fieldCategoryLabel != null) ...[
          const SizedBox(height: 2),
          Text('Rumpun: ${item.fieldCategoryLabel}',
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
        if (item.curationNote != null && item.curationNote!.isNotEmpty) ...[
          const SizedBox(height: 6),
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: item.isRevision ? AppColors.amber50 : AppColors.red50,
              borderRadius: BorderRadius.circular(8)),
            child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Icon(Icons.info_outline_rounded, size: 13, color: item.isRevision ? AppColors.amber600 : AppColors.red500),
              const SizedBox(width: 6),
              Expanded(child: Text('Catatan Kurator: ${item.curationNote}',
                style: TextStyle(fontSize: 11, color: item.isRevision ? AppColors.amber700 : AppColors.red500))),
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
  final _titleCtrl            = TextEditingController();
  final _eventNameCtrl        = TextEditingController();
  final _organizerCtrl        = TextEditingController();
  final _rankCtrl             = TextEditingController();
  final _descCtrl             = TextEditingController();
  final _eventUrlCtrl         = TextEditingController();

  int?                     _categoryId;
  String                   _fieldCategory     = 'akademik';
  String                   _participationType = 'individu';
  String?                  _level;
  DateTime?                _date;
  XFile?                   _photo;
  XFile?                   _certificate;
  XFile?                   _assignmentLetter;
  List<AchievementCategory> _categories      = [];
  bool                     _loadingCats       = true;
  bool                     _isSaving          = false;

  final List<(String, String)> _levels = const [
    ('sekolah',       'Sekolah'),
    ('kabupaten',     'Kabupaten/Kota'),
    ('provinsi',      'Provinsi'),
    ('nasional',      'Nasional'),
    ('internasional', 'Internasional'),
  ];

  final List<(String, String)> _fieldCategories = const [
    ('sains_riset',  'Sains & Riset'),
    ('olahraga',     'Olahraga'),
    ('seni_budaya',  'Seni & Budaya'),
    ('bahasa_debat', 'Bahasa & Debat'),
    ('keagamaan',    'Keagamaan'),
    ('akademik',     'Akademik'),
    ('lainnya',      'Lainnya'),
  ];

  @override
  void initState() {
    super.initState();
    _loadCategories();
  }

  @override
  void dispose() {
    _titleCtrl.dispose();
    _eventNameCtrl.dispose();
    _organizerCtrl.dispose();
    _rankCtrl.dispose();
    _descCtrl.dispose();
    _eventUrlCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadCategories() async {
    try {
      final body = await ApiClient.get('/achievements/categories');
      final list = (body['categories'] as List)
          .map((e) => AchievementCategory.fromJson(e as Map<String, dynamic>))
          .toList();
      setState(() { _categories = list; _loadingCats = false; });
    } catch (_) {
      setState(() => _loadingCats = false);
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

  Future<void> _pickFile({required int type}) async {
    // type: 0 = photo, 1 = certificate, 2 = assignmentLetter
    final picker = ImagePicker();
    final file   = await picker.pickImage(source: ImageSource.gallery, imageQuality: 85);

    if (file != null && mounted) {
      setState(() {
        if (type == 0) _photo = file;
        else if (type == 1) _certificate = file;
        else if (type == 2) _assignmentLetter = file;
      });
    }
  }

  Future<void> _submit() async {
    if (_titleCtrl.text.trim().isEmpty)   { _showSnack('Judul prestasi tidak boleh kosong.'); return; }
    if (_categoryId == null)              { _showSnack('Pilih kategori prestasi.'); return; }
    if (_level == null)                   { _showSnack('Pilih tingkat prestasi.'); return; }
    if (_date == null)                    { _showSnack('Pilih tanggal prestasi.'); return; }
    if (_photo == null)                   { _showSnack('Foto kegiatan/penyerahan piala wajib diunggah.'); return; }

    setState(() => _isSaving = true);
    try {
      final dateStr = '${_date!.year}-${_date!.month.toString().padLeft(2,'0')}-${_date!.day.toString().padLeft(2,'0')}';
      final formData = FormData.fromMap({
        'title':              _titleCtrl.text.trim(),
        'event_name':         _eventNameCtrl.text.trim().isNotEmpty ? _eventNameCtrl.text.trim() : _titleCtrl.text.trim(),
        'organizer':          _organizerCtrl.text.trim(),
        'category_id':        _categoryId.toString(),
        'field_category':     _fieldCategory,
        'participation_type': _participationType,
        'level':              _level!,
        'achievement_date':   dateStr,
        if (_rankCtrl.text.trim().isNotEmpty) 'rank': _rankCtrl.text.trim(),
        if (_descCtrl.text.trim().isNotEmpty) 'description': _descCtrl.text.trim(),
        if (_eventUrlCtrl.text.trim().isNotEmpty) 'event_url': _eventUrlCtrl.text.trim(),
        'photo': await MultipartFile.fromFile(_photo!.path, filename: 'photo.jpg'),
        if (_certificate != null)
          'certificate': await MultipartFile.fromFile(_certificate!.path, filename: 'certificate.jpg'),
        if (_assignmentLetter != null)
          'assignment_letter': await MultipartFile.fromFile(_assignmentLetter!.path, filename: 'assignment_letter.jpg'),
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
    final bottom  = MediaQuery.of(context).viewInsets.bottom;
    final safeBot = MediaQuery.of(context).padding.bottom;
    return Container(
      margin: const EdgeInsets.fromLTRB(12, 0, 12, 12),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.all(Radius.circular(20)),
      ),
      child: DraggableScrollableSheet(
        initialChildSize: 0.92,
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
            const Text('Laporkan Berkas Prestasi (Kurasi)',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.gray800)),
            const SizedBox(height: 4),
            const Text('Lengkapi berkas dan metadata agar lolos Kurasi Puspresnas/Sekolah.',
              style: TextStyle(fontSize: 11, color: AppColors.gray400)),
            const SizedBox(height: 20),

            // Judul Prestasi
            _Label('Judul Capaian / Prestasi *'),
            const SizedBox(height: 6),
            _InputField(controller: _titleCtrl, hint: 'Contoh: Juara 1 OSN Matematika Tingkat Provinsi...'),
            const SizedBox(height: 14),

            // Penyelenggara Lomba
            _Label('Penyelenggara Lomba / Ajang'),
            const SizedBox(height: 6),
            _InputField(controller: _organizerCtrl, hint: 'Contoh: Kemendikbudristek, UNUD, KONI Bali...'),
            const SizedBox(height: 14),

            // Rumpun Bidang & Keikutsertaan
            Row(children: [
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                _Label('Rumpun Bidang *'),
                const SizedBox(height: 6),
                DropdownButtonFormField<String>(
                  initialValue: _fieldCategory,
                  items: _fieldCategories.map((fc) => DropdownMenuItem(
                    value: fc.$1, child: Text(fc.$2, style: const TextStyle(fontSize: 12)),
                  )).toList(),
                  onChanged: (v) => setState(() => _fieldCategory = v ?? 'akademik'),
                  decoration: const InputDecoration(
                    filled: true, fillColor: AppColors.gray50,
                    contentPadding: EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                    border: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: BorderSide(color: AppColors.gray200)),
                    enabledBorder: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: BorderSide(color: AppColors.gray200)),
                  ),
                ),
              ])),
              const SizedBox(width: 10),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                const _Label('Keikutsertaan *'),
                const SizedBox(height: 6),
                DropdownButtonFormField<String>(
                  initialValue: _participationType,
                  items: const [
                    DropdownMenuItem(value: 'individu', child: Text('Individu', style: TextStyle(fontSize: 12))),
                    DropdownMenuItem(value: 'beregu', child: Text('Beregu (Tim)', style: TextStyle(fontSize: 12))),
                  ],
                  onChanged: (v) => setState(() => _participationType = v ?? 'individu'),
                  decoration: const InputDecoration(
                    filled: true, fillColor: AppColors.gray50,
                    contentPadding: EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                    border: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: BorderSide(color: AppColors.gray200)),
                    enabledBorder: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: BorderSide(color: AppColors.gray200)),
                  ),
                ),
              ])),
            ]),
            const SizedBox(height: 14),

            // Kategori
            const _Label('Kategori Lomba *'),
            const SizedBox(height: 6),
            if (_loadingCats)
              const Center(child: SizedBox(height: 36, width: 36, child: CircularProgressIndicator(strokeWidth: 2)))
            else
              DropdownButtonFormField<int>(
                initialValue: _categoryId,
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
                _Label('Peringkat / Juara'),
                const SizedBox(height: 6),
                _InputField(controller: _rankCtrl, hint: 'Juara 1, Medali Emas...'),
              ])),
              const SizedBox(width: 10),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                _Label('Tanggal Lomba *'),
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

            // Link Website Pengumuman Resmi
            _Label('Link Web Pengumuman Resmi (opsional)'),
            const SizedBox(height: 6),
            _InputField(controller: _eventUrlCtrl, hint: 'https://puspresnas.kemdikbud.go.id/...'),
            const SizedBox(height: 14),

            // Foto kegiatan / Piala
            _Label('Foto Kegiatan / Penyerahan Piala *'),
            const SizedBox(height: 6),
            _ImagePickerTile(
              file: _photo,
              label: 'Upload foto kegiatan (Wajib)',
              onTap: () => _pickFile(type: 0),
            ),
            const SizedBox(height: 14),

            // Sertifikat
            _Label('Sertifikat / Piagam Juara (opsional)'),
            const SizedBox(height: 6),
            _ImagePickerTile(
              file: _certificate,
              label: 'Upload scan sertifikat',
              onTap: () => _pickFile(type: 1),
            ),
            const SizedBox(height: 14),

            // Surat Tugas / Rekomendasi
            _Label('Surat Tugas / Rekomendasi Sekolah (opsional)'),
            const SizedBox(height: 6),
            _ImagePickerTile(
              file: _assignmentLetter,
              label: 'Upload Surat Tugas Sekolah',
              onTap: () => _pickFile(type: 2),
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
                  : const Text('Kirim Berkas Kurasi',
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
