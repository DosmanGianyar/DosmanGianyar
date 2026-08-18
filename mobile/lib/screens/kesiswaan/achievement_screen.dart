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
        label: const Text('Laporkan / Kurasi Prestasi'),
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
      color: const Color(0xFF0F2460),
      padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
      child: Row(children: [
        Expanded(child: _StatChip(label: 'Pending', count: stats.pending, color: AppColors.yellow600)),
        const SizedBox(width: 8),
        Expanded(child: _StatChip(label: 'Disetujui', count: stats.approved, color: AppColors.emerald500)),
        const SizedBox(width: 8),
        Expanded(child: _StatChip(label: 'Ditolak', count: stats.rejected, color: AppColors.red500)),
      ]),
    );
  }
}

class _StatChip extends StatelessWidget {
  final String label;
  final int count;
  final Color color;
  const _StatChip({required this.label, required this.count, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 10),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: Colors.white.withValues(alpha: 0.15)),
      ),
      child: Row(mainAxisAlignment: MainAxisAlignment.center, children: [
        Container(width: 8, height: 8, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
        const SizedBox(width: 6),
        Text('$label: $count',
          style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w500)),
      ]),
    );
  }
}

// ─── Achievement Card ─────────────────────────────────────────────────────────

class _AchievementCard extends StatelessWidget {
  final Achievement item;
  const _AchievementCard({required this.item});

  Color _statusColor() {
    switch (item.status) {
      case 'approved': return AppColors.emerald500;
      case 'rejected': return AppColors.red500;
      default:         return AppColors.yellow600;
    }
  }

  String _statusLabel() {
    switch (item.status) {
      case 'approved': return 'Disetujui';
      case 'rejected': return 'Ditolak';
      default:         return 'Menunggu Verifikasi';
    }
  }

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 0,
      color: Colors.white,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(14),
        side: const BorderSide(color: AppColors.gray200),
      ),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Expanded(child: Text(item.title,
              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.gray800))),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(
                color: _statusColor().withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(_statusLabel(),
                style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: _statusColor())),
            ),
          ]),
          const SizedBox(height: 6),
          Row(children: [
            if (item.categoryName != null) ...[
              Text(item.categoryName!,
                style: const TextStyle(fontSize: 11, color: AppColors.gray500, fontWeight: FontWeight.w500)),
              const Text(' · ', style: TextStyle(color: AppColors.gray400)),
            ],
            Text(item.levelLabel,
              style: const TextStyle(fontSize: 11, color: AppColors.purple700, fontWeight: FontWeight.w600)),
            if (item.rank != null) ...[
              const Text(' · ', style: TextStyle(color: AppColors.gray400)),
              Text(item.rank!,
                style: const TextStyle(fontSize: 11, color: AppColors.yellow600, fontWeight: FontWeight.w600)),
            ],
          ]),
          if (item.rejectionReason != null && item.rejectionReason!.isNotEmpty) ...[
            const SizedBox(height: 8),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: AppColors.red50,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text('Alasan ditolak: ${item.rejectionReason}',
                style: const TextStyle(fontSize: 11, color: AppColors.red500)),
            ),
          ],
        ]),
      ),
    );
  }
}

// ─── Create Sheet Form ────────────────────────────────────────────────────────

class _CreateSheet extends StatefulWidget {
  final VoidCallback onCreated;
  const _CreateSheet({required this.onCreated});

  @override
  State<_CreateSheet> createState() => _CreateSheetState();
}

class _CreateSheetState extends State<_CreateSheet> {
  bool _isCuration = false;

  final _titleCtrl            = TextEditingController();
  final _eventNameCtrl        = TextEditingController();
  final _organizerCtrl        = TextEditingController();
  final _rankCtrl             = TextEditingController();
  final _descCtrl             = TextEditingController();
  final _eventUrlCtrl         = TextEditingController();

  // Berkas 5 Poin Kurasi
  final _docStandardUrlCtrl           = TextEditingController();
  final _selectionLevelUrlCtrl        = TextEditingController();
  final _frequencyConsistencyUrlCtrl = TextEditingController();

  int?                     _categoryId;
  String                   _fieldCategory     = 'akademik';
  String                   _participationType = 'individu';
  String?                  _level;
  DateTime?                _date;
  XFile?                   _photo;
  XFile?                   _certificate;
  XFile?                   _assignmentLetter;

  // 5 Poin Kurasi Files
  XFile? _docStandardFile;
  XFile? _selectionLevelFile;
  XFile? _frequencyConsistencyFile;
  XFile? _infrastructureFile;
  XFile? _rewardCertFile;
  XFile? _rewardPhotoFile;
  XFile? _rewardRecapFile;

  String _selectionLevel       = '3_tingkat';
  String _frequencyConsistency = 'berturut_3';
  String _infrastructureType   = 'utama_pendukung';

  final List<String> _docStandardChecklist = [];
  final List<String> _rewardTypes          = [];

  List<AchievementCategory> _categories      = [];
  List<Map<String, dynamic>> _selectedTeamMembers = [];
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
    _docStandardUrlCtrl.dispose();
    _selectionLevelUrlCtrl.dispose();
    _frequencyConsistencyUrlCtrl.dispose();
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
    final picker = ImagePicker();
    final file   = await picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
    if (file != null && mounted) {
      setState(() {
        if (type == 0) _photo = file;
        else if (type == 1) _certificate = file;
        else if (type == 2) _assignmentLetter = file;
        else if (type == 10) _docStandardFile = file;
        else if (type == 11) _selectionLevelFile = file;
        else if (type == 12) _frequencyConsistencyFile = file;
        else if (type == 13) _infrastructureFile = file;
        else if (type == 14) _rewardCertFile = file;
        else if (type == 15) _rewardPhotoFile = file;
        else if (type == 16) _rewardRecapFile = file;
      });
    }
  }

  Future<void> _openTeamMemberPicker() async {
    final searchCtrl = TextEditingController();
    List<Map<String, dynamic>> searchResults = [];
    bool isLoading = false;

    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          Future<void> performSearch(String q) async {
            setModalState(() => isLoading = true);
            try {
              final body = await ApiClient.get('/students/search', params: {'q': q});
              final list = List<Map<String, dynamic>>.from(body['students'] ?? []);
              setModalState(() {
                searchResults = list;
                isLoading = false;
              });
            } catch (_) {
              setModalState(() => isLoading = false);
            }
          }

          if (searchResults.isEmpty && !isLoading && searchCtrl.text.isEmpty) {
            performSearch('');
          }

          return Container(
            height: MediaQuery.of(context).size.height * 0.7,
            decoration: const BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
            ),
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 40, height: 4,
                    decoration: BoxDecoration(color: AppColors.gray200, borderRadius: BorderRadius.circular(2)),
                  ),
                ),
                const SizedBox(height: 12),
                const Text('Pilih Anggota Tim (Siswa Lain)',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.gray800)),
                const SizedBox(height: 4),
                const Text('Cari nama atau kelas teman se-tim kamu SMAN 1 Gianyar',
                    style: TextStyle(fontSize: 11, color: AppColors.gray400)),
                const SizedBox(height: 12),
                TextField(
                  controller: searchCtrl,
                  onChanged: (val) => performSearch(val),
                  decoration: InputDecoration(
                    hintText: 'Cari nama teman / kelas...',
                    hintStyle: const TextStyle(fontSize: 12, color: AppColors.gray400),
                    prefixIcon: const Icon(Icons.search_rounded, size: 18, color: AppColors.gray400),
                    filled: true,
                    fillColor: AppColors.gray50,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.gray200)),
                    enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.gray200)),
                  ),
                ),
                const SizedBox(height: 12),
                Expanded(
                  child: isLoading
                      ? const Center(child: CircularProgressIndicator())
                      : searchResults.isEmpty
                          ? const Center(child: Text('Tidak ada siswa ditemukan', style: TextStyle(fontSize: 12, color: AppColors.gray400)))
                          : ListView.builder(
                              itemCount: searchResults.length,
                              itemBuilder: (context, index) {
                                final s = searchResults[index];
                                final isSelected = _selectedTeamMembers.any((m) => m['id'] == s['id']);
                                return CheckboxListTile(
                                  value: isSelected,
                                  title: Text(s['name'] ?? '', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                                  subtitle: Text('${s['class_name'] ?? ''} · NISN: ${s['nisn'] ?? '-'}',
                                      style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
                                  activeColor: AppColors.purple700,
                                  onChanged: (bool? checked) {
                                    setState(() {
                                      if (checked == true) {
                                        if (!_selectedTeamMembers.any((m) => m['id'] == s['id'])) {
                                          _selectedTeamMembers.add(s);
                                        }
                                      } else {
                                        _selectedTeamMembers.removeWhere((m) => m['id'] == s['id']);
                                      }
                                    });
                                    setModalState(() {});
                                  },
                                );
                              },
                            ),
                ),
                ElevatedButton(
                  onPressed: () => Navigator.pop(context),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.purple700,
                    minimumSize: const Size.fromHeight(44),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: Text('Selesai (${_selectedTeamMembers.length} Terpilih)', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.white)),
                ),
              ],
            ),
          );
        },
      ),
    );
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
      final Map<String, dynamic> formMap = {
        'is_curation':        _isCuration ? '1' : '0',
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
      };

      if (_isCuration) {
        formMap['selection_level']       = _selectionLevel;
        formMap['frequency_consistency'] = _frequencyConsistency;
        formMap['infrastructure_type']   = _infrastructureType;

        if (_docStandardUrlCtrl.text.trim().isNotEmpty)
          formMap['doc_standard_url'] = _docStandardUrlCtrl.text.trim();
        if (_selectionLevelUrlCtrl.text.trim().isNotEmpty)
          formMap['selection_level_url'] = _selectionLevelUrlCtrl.text.trim();
        if (_frequencyConsistencyUrlCtrl.text.trim().isNotEmpty)
          formMap['frequency_consistency_url'] = _frequencyConsistencyUrlCtrl.text.trim();

        if (_docStandardFile != null)
          formMap['doc_standard_file'] = await MultipartFile.fromFile(_docStandardFile!.path, filename: 'doc_standard.jpg');
        if (_selectionLevelFile != null)
          formMap['selection_level_file'] = await MultipartFile.fromFile(_selectionLevelFile!.path, filename: 'selection_level.jpg');
        if (_frequencyConsistencyFile != null)
          formMap['frequency_consistency_file'] = await MultipartFile.fromFile(_frequencyConsistencyFile!.path, filename: 'frequency.jpg');
        if (_infrastructureFile != null)
          formMap['infrastructure_file'] = await MultipartFile.fromFile(_infrastructureFile!.path, filename: 'infrastructure.jpg');
        if (_rewardCertFile != null)
          formMap['reward_certificate_file'] = await MultipartFile.fromFile(_rewardCertFile!.path, filename: 'reward_cert.jpg');
        if (_rewardPhotoFile != null)
          formMap['reward_photo_file'] = await MultipartFile.fromFile(_rewardPhotoFile!.path, filename: 'reward_photo.jpg');
        if (_rewardRecapFile != null)
          formMap['reward_recap_file'] = await MultipartFile.fromFile(_rewardRecapFile!.path, filename: 'reward_recap.jpg');
      }

      final formData = FormData.fromMap(formMap);
      if (_participationType == 'beregu' && _selectedTeamMembers.isNotEmpty) {
        for (var i = 0; i < _selectedTeamMembers.length; i++) {
          formData.fields.add(MapEntry('team_member_ids[$i]', _selectedTeamMembers[i]['id'].toString()));
        }
      }

      if (_isCuration) {
        for (var i = 0; i < _docStandardChecklist.length; i++) {
          formData.fields.add(MapEntry('doc_standard_checklist[$i]', _docStandardChecklist[i]));
        }
        for (var i = 0; i < _rewardTypes.length; i++) {
          formData.fields.add(MapEntry('reward_types[$i]', _rewardTypes[i]));
        }
      }

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
            const Text('Laporkan & Kurasi Prestasi',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.gray800)),
            const SizedBox(height: 4),
            const Text('Pilih tujuan pengajuan dan lengkapi metadata prestasi.',
              style: TextStyle(fontSize: 11, color: AppColors.gray400)),
            const SizedBox(height: 16),

            // ─── CARDS PILIHAN UTAMA MODE PENGAJUAN ───────────────────────
            Row(children: [
              Expanded(
                child: GestureDetector(
                  onTap: () => setState(() => _isCuration = false),
                  child: Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: !_isCuration ? AppColors.blue50 : Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: !_isCuration ? AppColors.blue600 : AppColors.gray200, width: !_isCuration ? 2 : 1),
                    ),
                    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Row(children: [
                        const Icon(Icons.emoji_events_rounded, color: AppColors.blue600, size: 20),
                        const Spacer(),
                        Radio<bool>(value: false, groupValue: _isCuration, onChanged: (v) => setState(() => _isCuration = false)),
                      ]),
                      const Text('Hanya Laporkan', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.gray900)),
                      const SizedBox(height: 2),
                      const Text('Portofolio & Poin Sekolah', style: TextStyle(fontSize: 10, color: AppColors.gray500)),
                    ]),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: GestureDetector(
                  onTap: () => setState(() => _isCuration = true),
                  child: Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: _isCuration ? AppColors.purple50 : Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: _isCuration ? AppColors.purple700 : AppColors.gray200, width: _isCuration ? 2 : 1),
                    ),
                    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Row(children: [
                        const Icon(Icons.verified_rounded, color: AppColors.purple700, size: 20),
                        const Spacer(),
                        Radio<bool>(value: true, groupValue: _isCuration, onChanged: (v) => setState(() => _isCuration = true)),
                      ]),
                      const Text('Laporkan & Kurasi', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.gray900)),
                      const SizedBox(height: 2),
                      const Text('Persyaratan Kemendikdasmen', style: TextStyle(fontSize: 10, color: AppColors.gray500)),
                    ]),
                  ),
                ),
              ),
            ]),
            const SizedBox(height: 20),

            // Judul Prestasi
            const _Label('Judul Capaian / Prestasi *',
              subText: 'Contoh: Juara 1 OSN Matematika Tingkat Provinsi Bali 2026'),
            const SizedBox(height: 6),
            _InputField(controller: _titleCtrl, hint: 'Tulis nama prestasi lengkap...'),
            const SizedBox(height: 14),

            // Penyelenggara Lomba
            const _Label('Penyelenggara Lomba / Ajang',
              subText: 'Contoh: Kemendikbudristek, Universitas Udayana, KONI Bali, BRIN'),
            const SizedBox(height: 6),
            _InputField(controller: _organizerCtrl, hint: 'Ketik lembaga penyelenggara...'),
            const SizedBox(height: 14),

            // Rumpun Bidang & Keikutsertaan
            Row(children: [
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                const _Label('Rumpun Bidang *', subText: 'Pilih rumpun lomba'),
                const SizedBox(height: 6),
                DropdownButtonFormField<String>(
                  value: _fieldCategory,
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
                const _Label('Keikutsertaan *', subText: 'Individu / Tim'),
                const SizedBox(height: 6),
                DropdownButtonFormField<String>(
                  value: _participationType,
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
            if (_participationType == 'beregu') ...[
              const SizedBox(height: 10),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.purple50,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: AppColors.purple200),
                ),
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  const Row(children: [
                    Icon(Icons.group_outlined, size: 16, color: AppColors.purple700),
                    SizedBox(width: 6),
                    Text('Anggota Tim (Siswa Lain SMAN 1 Gianyar)',
                        style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.purple900)),
                  ]),
                  const SizedBox(height: 4),
                  const Text('Pilih teman se-tim kamu agar data prestasi otomatis terdaftar di akun mereka.',
                      style: TextStyle(fontSize: 10.5, color: AppColors.purple700)),
                  const SizedBox(height: 10),
                  if (_selectedTeamMembers.isNotEmpty) ...[
                    Wrap(
                      spacing: 6, runSpacing: 6,
                      children: _selectedTeamMembers.map((m) => Chip(
                        label: Text(m['name'] ?? '', style: const TextStyle(fontSize: 11, color: Colors.white, fontWeight: FontWeight.w600)),
                        backgroundColor: AppColors.purple700,
                        deleteIcon: const Icon(Icons.close_rounded, size: 14, color: Colors.white),
                        onDeleted: () => setState(() => _selectedTeamMembers.removeWhere((item) => item['id'] == m['id'])),
                        padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 0),
                        materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                      )).toList(),
                    ),
                    const SizedBox(height: 10),
                  ],
                  SizedBox(
                    width: double.infinity,
                    child: OutlinedButton.icon(
                      onPressed: _openTeamMemberPicker,
                      icon: const Icon(Icons.person_add_alt_1_rounded, size: 16, color: AppColors.purple700),
                      label: Text(_selectedTeamMembers.isEmpty ? 'Cari & Tambah Anggota Tim' : 'Tambah Anggota Lain (${_selectedTeamMembers.length} Terpilih)',
                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.purple700)),
                      style: OutlinedButton.styleFrom(
                        side: const BorderSide(color: AppColors.purple300),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        backgroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 10),
                      ),
                    ),
                  ),
                ]),
              ),
            ],
            const SizedBox(height: 14),

            // Kategori
            const _Label('Kategori Lomba *', subText: 'Pilih jenis kompetisi'),
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
                decoration: const InputDecoration(
                  filled: true, fillColor: AppColors.gray50,
                  contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                  border: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: BorderSide(color: AppColors.gray200)),
                  enabledBorder: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: BorderSide(color: AppColors.gray200)),
                ),
              ),
            const SizedBox(height: 14),

            // Tingkat
            const _Label('Tingkat Prestasi *', subText: 'Pilih cakupan wilayah perlombaan'),
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
                const _Label('Peringkat / Juara', subText: 'Juara 1 / Emas'),
                const SizedBox(height: 6),
                _InputField(controller: _rankCtrl, hint: 'Contoh: Juara 1'),
              ])),
              const SizedBox(width: 10),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                const _Label('Tanggal Lomba *', subText: 'Tanggal penyerahan'),
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

            // Foto kegiatan / Piala
            const _Label('Foto Kegiatan / Penyerahan Piala *',
              subText: 'Wajib! Foto fisik penyerahan piala / di panggung juara'),
            const SizedBox(height: 6),
            _ImagePickerTile(
              file: _photo,
              label: 'Pilih foto kegiatan (Wajib)',
              onTap: () => _pickFile(type: 0),
            ),
            const SizedBox(height: 14),

            // Sertifikat
            const _Label('Sertifikat / Piagam Juara (opsional)',
              subText: 'Scan/foto piagam resmi yang mencantumkan nama & NISN'),
            const SizedBox(height: 6),
            _ImagePickerTile(
              file: _certificate,
              label: 'Pilih file scan sertifikat',
              onTap: () => _pickFile(type: 1),
            ),
            const SizedBox(height: 14),

            // ─── 5 POIN BERKAS KURASI KEMENDIKDASMEN ───────────────────────
            if (_isCuration) ...[
              const SizedBox(height: 10),
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: AppColors.purple50.withValues(alpha: 0.6),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppColors.purple200),
                ),
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  const Row(children: [
                    Icon(Icons.verified_rounded, size: 18, color: AppColors.purple700),
                    SizedBox(width: 6),
                    Text('5 Poin Berkas Persyaratan Kurasi',
                        style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.purple900)),
                  ]),
                  const SizedBox(height: 4),
                  const Text('Upload berkas kurasi resmi agar ajang terdaftar di Puspresnas & BTIKP.',
                      style: TextStyle(fontSize: 11, color: AppColors.purple700)),
                  const SizedBox(height: 14),

                  // P1: Dokumen Standar Juknis
                  const _Label('P1: Dokumen Juknis/Pedoman Ajang', subText: 'File Juknis PDF/DOCX atau Tautan URL'),
                  const SizedBox(height: 6),
                  _ImagePickerTile(file: _docStandardFile, label: 'Upload Berkas Juknis P1', onTap: () => _pickFile(type: 10)),
                  const SizedBox(height: 6),
                  _InputField(controller: _docStandardUrlCtrl, hint: 'Atau paste URL Website Juknis Resmi...'),
                  const SizedBox(height: 14),

                  // P2: Tingkat Seleksi
                  const _Label('P2: Tingkatan Seleksi Lomba', subText: 'Tahapan seleksi yang dilalui'),
                  const SizedBox(height: 6),
                  DropdownButtonFormField<String>(
                    value: _selectionLevel,
                    items: const [
                      DropdownMenuItem(value: '3_tingkat', child: Text('≥3 Tingkat (Kab -> Prov -> Nas)', style: TextStyle(fontSize: 12))),
                      DropdownMenuItem(value: '2_tingkat', child: Text('2 Tingkat (Prov -> Nas)', style: TextStyle(fontSize: 12))),
                      DropdownMenuItem(value: '1_tingkat', child: Text('1 Tingkat (Langsung Final)', style: TextStyle(fontSize: 12))),
                    ],
                    onChanged: (v) => setState(() => _selectionLevel = v ?? '3_tingkat'),
                    decoration: const InputDecoration(
                      filled: true, fillColor: Colors.white,
                      contentPadding: EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                      border: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: BorderSide(color: AppColors.gray200)),
                    ),
                  ),
                  const SizedBox(height: 6),
                  _ImagePickerTile(file: _selectionLevelFile, label: 'Upload Bukti Tahapan Seleksi P2', onTap: () => _pickFile(type: 11)),
                  const SizedBox(height: 14),

                  // P3: Konsistensi Frekuensi
                  const _Label('P3: Konsistensi Frekuensi Lomba', subText: 'Rutinitas ajang lintas tahun'),
                  const SizedBox(height: 6),
                  DropdownButtonFormField<String>(
                    value: _frequencyConsistency,
                    items: const [
                      DropdownMenuItem(value: 'berturut_gt3', child: Text('Berturut-turut >3 Kali', style: TextStyle(fontSize: 12))),
                      DropdownMenuItem(value: 'berturut_3', child: Text('Berturut 3 Kali', style: TextStyle(fontSize: 12))),
                      DropdownMenuItem(value: 'berturut_2', child: Text('Berturut 2 Kali', style: TextStyle(fontSize: 12))),
                      DropdownMenuItem(value: 'tidak_berturut', child: Text('Tidak Berturut-turut', style: TextStyle(fontSize: 12))),
                    ],
                    onChanged: (v) => setState(() => _frequencyConsistency = v ?? 'berturut_3'),
                    decoration: const InputDecoration(
                      filled: true, fillColor: Colors.white,
                      contentPadding: EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                      border: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: BorderSide(color: AppColors.gray200)),
                    ),
                  ),
                  const SizedBox(height: 6),
                  _ImagePickerTile(file: _frequencyConsistencyFile, label: 'Upload Berkas Juknis Lintas Tahun P3', onTap: () => _pickFile(type: 12)),
                  const SizedBox(height: 14),

                  // P4: Sarana Prasarana
                  const _Label('P4: Sarana & Prasarana Lomba', subText: 'Fasilitas tempat & alat lomba'),
                  const SizedBox(height: 6),
                  DropdownButtonFormField<String>(
                    value: _infrastructureType,
                    items: const [
                      DropdownMenuItem(value: 'utama_pendukung', child: Text('Sarana Utama & Pendukung Lengkap', style: TextStyle(fontSize: 12))),
                      DropdownMenuItem(value: 'utama', child: Text('Sarana Utama Saja', style: TextStyle(fontSize: 12))),
                      DropdownMenuItem(value: 'pendukung', child: Text('Sarana Pendukung Saja', style: TextStyle(fontSize: 12))),
                    ],
                    onChanged: (v) => setState(() => _infrastructureType = v ?? 'utama_pendukung'),
                    decoration: const InputDecoration(
                      filled: true, fillColor: Colors.white,
                      contentPadding: EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                      border: OutlineInputBorder(borderRadius: AppRadius.input, borderSide: BorderSide(color: AppColors.gray200)),
                    ),
                  ),
                  const SizedBox(height: 6),
                  _ImagePickerTile(file: _infrastructureFile, label: 'Upload Foto Sarpras Lomba P4', onTap: () => _pickFile(type: 13)),
                  const SizedBox(height: 14),

                  // P5: Penghargaan & SK Rekap
                  const _Label('P5: Penghargaan & SK Rekap Pemenang', subText: 'Scan Piagam, Foto Panggung, & SK Rekap'),
                  const SizedBox(height: 6),
                  _ImagePickerTile(file: _rewardCertFile, label: 'Upload Scan Piagam P5', onTap: () => _pickFile(type: 14)),
                  const SizedBox(height: 6),
                  _ImagePickerTile(file: _rewardPhotoFile, label: 'Upload Foto Panggung Juara P5', onTap: () => _pickFile(type: 15)),
                  const SizedBox(height: 6),
                  _ImagePickerTile(file: _rewardRecapFile, label: 'Upload Dokumen SK Rekap Pemenang P5', onTap: () => _pickFile(type: 16)),
                ]),
              ),
            ],

            const SizedBox(height: 20),

            FilledButton(
              onPressed: _isSaving ? null : _submit,
              style: FilledButton.styleFrom(
                backgroundColor: _isCuration ? AppColors.purple700 : AppColors.blue600,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
              ),
              child: _isSaving
                  ? const SizedBox(width: 18, height: 18,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : Text(_isCuration ? 'Kirim Laporkan & Kurasi Kemendikdasmen' : 'Kirim Laporkan Prestasi Internal',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

class _Label extends StatelessWidget {
  final String  text;
  final String? subText;
  const _Label(this.text, {this.subText});

  @override
  Widget build(BuildContext context) {
    if (subText == null) {
      return Text(text,
        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.gray700));
    }
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(text,
          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.gray700)),
        const SizedBox(height: 2),
        Text(subText!,
          style: const TextStyle(fontSize: 11, color: AppColors.gray400, fontStyle: FontStyle.italic)),
      ],
    );
  }
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
            color: file != null ? AppColors.purple700 : AppColors.gray200,
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
                  style: const TextStyle(fontSize: 12, color: AppColors.gray700, fontWeight: FontWeight.bold),
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
