import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../models/guru_extracurricular.dart';
import '../../services/api_client.dart';
import '../../services/guru_service.dart';
import '../../theme/app_colors.dart';
import '../../widgets/image_viewer_dialog.dart';
import 'guru_extracurricular_journal_detail_screen.dart';

class GuruExtracurricularScreen extends StatefulWidget {
  final int? initialExtracurricularId;
  final int initialTabIndex;

  const GuruExtracurricularScreen({
    super.key,
    this.initialExtracurricularId,
    this.initialTabIndex = 0,
  });

  @override
  State<GuruExtracurricularScreen> createState() =>
      _GuruExtracurricularScreenState();
}

class _GuruExtracurricularScreenState extends State<GuruExtracurricularScreen>
    with SingleTickerProviderStateMixin {
  late final TabController _tabCtrl;

  List<GuruExtraSummary> _extras = [];
  GuruExtraSummary? _selectedExtra;
  bool _loadingExtras = true;
  String? _extrasError;

  // Anggota & form absen
  List<GuruExtraMember> _members = [];
  List<GuruExtraAttendanceRow> _attendanceRows = [];
  bool _loadingMembers = false;
  String? _membersError;

  // Filter pencarian absen & tab anggota
  String _attSearch = '';
  final _attSearchCtrl = TextEditingController();
  String _memberSearch = '';
  final _memberSearchCtrl = TextEditingController();

  // Form Jurnal Ekstra
  final _titleCtrl = TextEditingController();
  final _locationCtrl = TextEditingController();
  final _materialCtrl = TextEditingController();
  final _activityCtrl = TextEditingController();
  final _obstaclesCtrl = TextEditingController();
  final _notesCtrl = TextEditingController();
  DateTime _selectedDate = DateTime.now();
  TimeOfDay _startTime = const TimeOfDay(hour: 15, minute: 0);
  TimeOfDay _endTime = const TimeOfDay(hour: 17, minute: 0);
  File? _photo;
  bool _submitting = false;

  // Riwayat Jurnal
  final List<GuruExtraJournal> _journals = [];
  bool _loadingJournals = false;
  String? _journalsError;
  int _journalPage = 1;
  bool _journalHasMore = true;
  int? _filterMonth;
  int _filterYear = DateTime.now().year;
  bool _downloadingPdf = false;

  Future<void> _downloadMembersPdf() async {
    final extra = _selectedExtra;
    if (extra == null || _downloadingPdf) return;
    setState(() => _downloadingPdf = true);
    try {
      final url = await GuruService.getExtracurricularMembersPdfUrl(extra.id);
      final uri = Uri.parse(url);
      final launched = await launchUrl(uri, mode: LaunchMode.externalApplication);
      if (!launched && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Tidak dapat membuka tautan unduhan PDF.'),
            backgroundColor: AppColors.red500,
          ),
        );
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
      if (mounted) setState(() => _downloadingPdf = false);
    }
  }

  @override
  void initState() {
    super.initState();
    final idx = widget.initialTabIndex.clamp(0, 2);
    _tabCtrl = TabController(length: 3, vsync: this, initialIndex: idx);
    _loadExtras();
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    _attSearchCtrl.dispose();
    _memberSearchCtrl.dispose();
    _titleCtrl.dispose();
    _locationCtrl.dispose();
    _materialCtrl.dispose();
    _activityCtrl.dispose();
    _obstaclesCtrl.dispose();
    _notesCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadExtras() async {
    setState(() {
      _loadingExtras = true;
      _extrasError = null;
    });
    try {
      final list = await GuruService.getMyExtracurriculars();
      if (!mounted) return;
      GuruExtraSummary? selected = _selectedExtra;
      if (selected != null) {
        selected = list.cast<GuruExtraSummary?>().firstWhere(
              (e) => e?.id == selected!.id,
              orElse: () => null,
            );
      }
      selected ??= widget.initialExtracurricularId != null
          ? list.cast<GuruExtraSummary?>().firstWhere(
                (e) => e?.id == widget.initialExtracurricularId,
                orElse: () => list.isNotEmpty ? list.first : null,
              )
          : (list.isNotEmpty ? list.first : null);

      setState(() {
        _extras = list;
        _selectedExtra = selected;
        _loadingExtras = false;
      });

      if (selected != null) {
        await Future.wait([
          _loadMembers(selected.id),
          _loadJournals(selected.id, reset: true),
        ]);
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _extrasError = ApiClient.extractError(e);
        _loadingExtras = false;
      });
    }
  }

  Future<void> _selectExtra(GuruExtraSummary extra) async {
    if (_selectedExtra?.id == extra.id) return;
    setState(() {
      _selectedExtra = extra;
      _members = [];
      _attendanceRows = [];
      _journals.clear();
      _attSearch = '';
      _attSearchCtrl.clear();
      _memberSearch = '';
      _memberSearchCtrl.clear();
    });
    await Future.wait([
      _loadMembers(extra.id),
      _loadJournals(extra.id, reset: true),
    ]);
  }

  Future<void> _loadMembers(int extraId) async {
    setState(() {
      _loadingMembers = true;
      _membersError = null;
    });
    try {
      final oldStatuses = {
        for (final r in _attendanceRows) r.userId: r.status,
      };
      final members = await GuruService.getExtracurricularMembers(extraId);
      if (!mounted) return;
      setState(() {
        _members = members;
        _attendanceRows = members.map((m) {
          final row = GuruExtraAttendanceRow.fromMember(m);
          if (oldStatuses.containsKey(m.userId)) {
            row.status = oldStatuses[m.userId]!;
          }
          return row;
        }).toList();
        _loadingMembers = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _membersError = ApiClient.extractError(e);
        _loadingMembers = false;
      });
    }
  }

  Future<void> _loadJournals(int extraId, {bool reset = false}) async {
    if (_loadingJournals) return;
    if (reset) {
      _journalPage = 1;
      _journalHasMore = true;
    }
    setState(() {
      _loadingJournals = true;
      _journalsError = null;
    });
    try {
      final res = await GuruService.getExtracurricularJournals(
        extraId,
        month: _filterMonth,
        year: _filterMonth != null ? _filterYear : null,
        page: _journalPage,
      );
      if (!mounted) return;
      setState(() {
        if (reset) _journals.clear();
        _journals.addAll(res.data);
        _journalHasMore = res.currentPage < res.lastPage;
        _journalPage++;
        _loadingJournals = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _journalsError = ApiClient.extractError(e);
        _loadingJournals = false;
      });
    }
  }

  String _formatTime(TimeOfDay t) =>
      '${t.hour.toString().padLeft(2, '0')}:${t.minute.toString().padLeft(2, '0')}';

  Future<void> _pickPhoto(ImageSource source) async {
    final picked = await ImagePicker().pickImage(
      source: source,
      imageQuality: 80,
      maxWidth: 1600,
    );
    if (picked != null && mounted) {
      setState(() => _photo = File(picked.path));
    }
  }

  Future<void> _submitJournal() async {
    final extra = _selectedExtra;
    if (extra == null || _submitting) return;

    if (_titleCtrl.text.trim().isEmpty ||
        _materialCtrl.text.trim().isEmpty ||
        _activityCtrl.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Judul kegiatan, materi pembinaan, dan uraian kegiatan wajib diisi.'),
          backgroundColor: AppColors.red500,
        ),
      );
      return;
    }

    final startMin = _startTime.hour * 60 + _startTime.minute;
    final endMin = _endTime.hour * 60 + _endTime.minute;
    if (endMin <= startMin) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Jam selesai harus lebih akhir dari jam mulai.'),
          backgroundColor: AppColors.red500,
        ),
      );
      return;
    }

    setState(() => _submitting = true);
    try {
      final res = await GuruService.createExtracurricularJournal(
        extra.id,
        title: _titleCtrl.text.trim(),
        sessionDate: DateFormat('yyyy-MM-dd').format(_selectedDate),
        startTime: _formatTime(_startTime),
        endTime: _formatTime(_endTime),
        location: _locationCtrl.text.trim(),
        material: _materialCtrl.text.trim(),
        activity: _activityCtrl.text.trim(),
        obstacles: _obstaclesCtrl.text.trim(),
        notes: _notesCtrl.text.trim(),
        photo: _photo,
        attendances: _attendanceRows.map((r) => r.toPayload()).toList(),
      );

      if (!mounted) return;
      _titleCtrl.clear();
      _locationCtrl.clear();
      _materialCtrl.clear();
      _activityCtrl.clear();
      _obstaclesCtrl.clear();
      _notesCtrl.clear();
      for (final r in _attendanceRows) {
        r.status = 'hadir';
      }
      setState(() {
        _photo = null;
        _submitting = false;
      });

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res.message),
          backgroundColor: AppColors.green600,
        ),
      );

      _tabCtrl.animateTo(1);
      await Future.wait([
        _loadMembers(extra.id),
        _loadJournals(extra.id, reset: true),
      ]);
    } catch (e) {
      if (!mounted) return;
      setState(() => _submitting = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(ApiClient.extractError(e)),
          backgroundColor: AppColors.red500,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Ekstrakurikuler Pembina'),
        actions: [
          if (_selectedExtra != null)
            IconButton(
              onPressed: _downloadingPdf ? null : _downloadMembersPdf,
              icon: _downloadingPdf
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Icon(Icons.download_rounded),
              tooltip: 'Download PDF Peserta',
            ),
          IconButton(
            onPressed: _loadExtras,
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Muat Ulang',
          ),
        ],
      ),
      body: _loadingExtras
          ? const Center(child: CircularProgressIndicator())
          : _extrasError != null
              ? _buildError(_extrasError!, _loadExtras)
              : _extras.isEmpty
                  ? _buildEmptyPembina()
                  : Column(
                      children: [
                        _buildExtraSelectorHeader(),
                        Container(
                          color: Colors.white,
                          child: TabBar(
                            controller: _tabCtrl,
                            labelColor: AppColors.blue600,
                            unselectedLabelColor: AppColors.gray500,
                            indicatorColor: AppColors.blue600,
                            labelStyle: const TextStyle(
                              fontSize: 12.5,
                              fontWeight: FontWeight.w700,
                            ),
                            tabs: [
                              const Tab(text: 'Jurnal & Absen'),
                              const Tab(text: 'Riwayat Jurnal'),
                              Tab(text: 'Anggota (${_members.length})'),
                            ],
                          ),
                        ),
                        Expanded(
                          child: TabBarView(
                            controller: _tabCtrl,
                            children: [
                              _buildCreateJournalTab(),
                              _buildHistoryTab(),
                              _buildMembersTab(),
                            ],
                          ),
                        ),
                      ],
                    ),
    );
  }

  Widget _buildEmptyPembina() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(28),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 68,
              height: 68,
              decoration: BoxDecoration(
                color: AppColors.emerald50,
                borderRadius: BorderRadius.circular(18),
              ),
              child: const Icon(
                Icons.sports_soccer_rounded,
                size: 36,
                color: AppColors.emerald600,
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              'Belum Ada Ekstrakurikuler Binaan',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w700,
                color: AppColors.gray800,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 6),
            const Text(
              'Anda belum ditugaskan sebagai pembina ekstrakurikuler aktif. Hubungi admin apabila penugasan pembina belum diperbarui.',
              style: TextStyle(fontSize: 12.5, color: AppColors.gray500, height: 1.4),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildExtraSelectorHeader() {
    final selected = _selectedExtra!;
    return Container(
      width: double.infinity,
      color: Colors.white,
      padding: const EdgeInsets.fromLTRB(16, 10, 16, 10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (_extras.length > 1) ...[
            SizedBox(
              height: 36,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: _extras.length,
                separatorBuilder: (_, __) => const SizedBox(width: 8),
                itemBuilder: (_, i) {
                  final e = _extras[i];
                  final isSel = e.id == selected.id;
                  return ChoiceChip(
                    label: Text('${e.name} (${e.activeMembersCount})'),
                    selected: isSel,
                    onSelected: (_) => _selectExtra(e),
                    selectedColor: AppColors.blue600,
                    labelStyle: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: isSel ? Colors.white : AppColors.gray700,
                    ),
                    backgroundColor: AppColors.slate100,
                    side: BorderSide(color: isSel ? AppColors.blue600 : AppColors.gray200),
                  );
                },
              ),
            ),
          ] else ...[
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: AppColors.emerald50,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.groups_2_rounded, size: 18, color: AppColors.emerald600),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        selected.name,
                        style: const TextStyle(
                          fontSize: 14.5,
                          fontWeight: FontWeight.w800,
                          color: AppColors.gray900,
                        ),
                      ),
                      Text(
                        '${selected.activeMembersCount} siswa aktif • ${selected.journalsCount} jurnal tercatat',
                        style: const TextStyle(fontSize: 11.5, color: AppColors.gray500),
                      ),
                    ],
                  ),
                ),
                if (selected.pendingMembersCount > 0)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: AppColors.amber100,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      '${selected.pendingMembersCount} pending',
                      style: const TextStyle(
                        fontSize: 10.5,
                        fontWeight: FontWeight.w700,
                        color: AppColors.amber800,
                      ),
                    ),
                  ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  // ─── Tab 1: Jurnal & Absen Terpadu ─────────────────────────────────────────

  Widget _buildCreateJournalTab() {
    final filteredRows = _attendanceRows.where((r) {
      final q = _attSearch.trim().toLowerCase();
      if (q.isEmpty) return true;
      return r.name.toLowerCase().contains(q) ||
          (r.nis ?? '').toLowerCase().contains(q) ||
          r.className.toLowerCase().contains(q);
    }).toList();

    final hadir = _attendanceRows.where((r) => r.status == 'hadir').length;
    final izin = _attendanceRows.where((r) => r.status == 'izin').length;
    final sakit = _attendanceRows.where((r) => r.status == 'sakit').length;
    final alpa = _attendanceRows.where((r) => r.status == 'alpa').length;

    return RefreshIndicator(
      onRefresh: () async {
        if (_selectedExtra != null) await _loadMembers(_selectedExtra!.id);
      },
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Kartu Form Jurnal Pembina
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppColors.gray200),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Form Jurnal Ekstrakurikuler',
                  style: TextStyle(
                    fontSize: 14.5,
                    fontWeight: FontWeight.w800,
                    color: AppColors.gray900,
                  ),
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () async {
                          final picked = await showDatePicker(
                            context: context,
                            initialDate: _selectedDate,
                            firstDate: DateTime.now().subtract(const Duration(days: 180)),
                            lastDate: DateTime.now().add(const Duration(days: 30)),
                          );
                          if (picked != null) setState(() => _selectedDate = picked);
                        },
                        icon: const Icon(Icons.calendar_today_rounded, size: 15),
                        label: Text(DateFormat('dd MMM yyyy').format(_selectedDate)),
                      ),
                    ),
                    const SizedBox(width: 8),
                    OutlinedButton(
                      onPressed: () async {
                        final t = await showTimePicker(context: context, initialTime: _startTime);
                        if (t != null) setState(() => _startTime = t);
                      },
                      child: Text(_formatTime(_startTime)),
                    ),
                    const Padding(
                      padding: EdgeInsets.symmetric(horizontal: 4),
                      child: Text('–'),
                    ),
                    OutlinedButton(
                      onPressed: () async {
                        final t = await showTimePicker(context: context, initialTime: _endTime);
                        if (t != null) setState(() => _endTime = t);
                      },
                      child: Text(_formatTime(_endTime)),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                _formLabel('Judul Kegiatan / Pertemuan *'),
                const SizedBox(height: 5),
                _formInput(_titleCtrl, 'Contoh: Latihan Teknik Dasar & Pemantapan Lomba'),
                const SizedBox(height: 10),
                _formLabel('Lokasi Kegiatan'),
                const SizedBox(height: 5),
                _formInput(_locationCtrl, 'Contoh: Lapangan Sekolah / Aula SMAN 1 Gianyar'),
                const SizedBox(height: 10),
                _formLabel('Materi Pembinaan *'),
                const SizedBox(height: 5),
                _formInput(_materialCtrl, 'Tuliskan pokok materi yang diberikan kepada siswa...', maxLines: 2),
                const SizedBox(height: 10),
                _formLabel('Uraian Kegiatan *'),
                const SizedBox(height: 5),
                _formInput(_activityCtrl, 'Tuliskan rangkaian aktivitas latihan/pembinaan...', maxLines: 3),
                const SizedBox(height: 10),
                _formLabel('Kendala / Evaluasi (Opsional)'),
                const SizedBox(height: 5),
                _formInput(_obstaclesCtrl, 'Catatan kendala alat, lapangan, atau evaluasi latihan...', maxLines: 2),
                const SizedBox(height: 10),
                _formLabel('Catatan / Tindak Lanjut (Opsional)'),
                const SizedBox(height: 5),
                _formInput(_notesCtrl, 'Rencana agenda pertemuan berikutnya...', maxLines: 2),
                const SizedBox(height: 12),
                _formLabel('Foto Dokumentasi (Opsional)'),
                const SizedBox(height: 6),
                Row(
                  children: [
                    OutlinedButton.icon(
                      onPressed: () => _pickPhoto(ImageSource.camera),
                      icon: const Icon(Icons.camera_alt_outlined, size: 16),
                      label: const Text('Kamera'),
                    ),
                    const SizedBox(width: 8),
                    OutlinedButton.icon(
                      onPressed: () => _pickPhoto(ImageSource.gallery),
                      icon: const Icon(Icons.photo_library_outlined, size: 16),
                      label: const Text('Galeri'),
                    ),
                    if (_photo != null) ...[
                      const Spacer(),
                      TextButton.icon(
                        onPressed: () => setState(() => _photo = null),
                        icon: const Icon(Icons.close_rounded, size: 16, color: AppColors.red500),
                        label: const Text('Hapus', style: TextStyle(color: AppColors.red500)),
                      ),
                    ],
                  ],
                ),
                if (_photo != null) ...[
                  const SizedBox(height: 8),
                  ClipRRect(
                    borderRadius: BorderRadius.circular(10),
                    child: Image.file(
                      _photo!,
                      height: 140,
                      width: double.infinity,
                      fit: BoxFit.cover,
                    ),
                  ),
                ],
              ],
            ),
          ),

          const SizedBox(height: 14),

          // Kartu Absensi Siswa Ekstra
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppColors.gray200),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        'Absensi Siswa Ekstra (${_attendanceRows.length})',
                        style: const TextStyle(
                          fontSize: 14.5,
                          fontWeight: FontWeight.w800,
                          color: AppColors.gray900,
                        ),
                      ),
                    ),
                    TextButton.icon(
                      onPressed: _attendanceRows.isEmpty
                          ? null
                          : () {
                              setState(() {
                                for (final r in _attendanceRows) {
                                  r.status = 'hadir';
                                }
                              });
                            },
                      icon: const Icon(Icons.done_all_rounded, size: 16),
                      label: const Text('Hadir Semua', style: TextStyle(fontSize: 12)),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _sumChip('Hadir', hadir, AppColors.green600),
                      const SizedBox(width: 6),
                      _sumChip('Izin', izin, AppColors.sky600),
                      const SizedBox(width: 6),
                      _sumChip('Sakit', sakit, AppColors.purple500),
                      const SizedBox(width: 6),
                      _sumChip('Alpa', alpa, AppColors.red500),
                    ],
                  ),
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: _attSearchCtrl,
                  onChanged: (v) => setState(() => _attSearch = v),
                  decoration: InputDecoration(
                    hintText: 'Cari nama siswa, NIS, atau kelas...',
                    hintStyle: const TextStyle(fontSize: 12.5, color: AppColors.gray400),
                    prefixIcon: const Icon(Icons.search_rounded, size: 18),
                    suffixIcon: _attSearch.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear_rounded, size: 16),
                            onPressed: () {
                              _attSearchCtrl.clear();
                              setState(() => _attSearch = '');
                            },
                          )
                        : null,
                    filled: true,
                    fillColor: AppColors.gray50,
                    isDense: true,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
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
                const SizedBox(height: 10),
                if (_loadingMembers)
                  const Padding(
                    padding: EdgeInsets.symmetric(vertical: 20),
                    child: Center(child: CircularProgressIndicator()),
                  )
                else if (_membersError != null)
                  _buildError(_membersError!, () => _loadMembers(_selectedExtra!.id))
                else if (_attendanceRows.isEmpty)
                  const Padding(
                    padding: EdgeInsets.symmetric(vertical: 16),
                    child: Center(
                      child: Text(
                        'Belum ada siswa terdaftar sebagai anggota aktif di ekstra ini.',
                        style: TextStyle(fontSize: 12.5, color: AppColors.gray500),
                        textAlign: TextAlign.center,
                      ),
                    ),
                  )
                else if (filteredRows.isEmpty)
                  const Padding(
                    padding: EdgeInsets.symmetric(vertical: 16),
                    child: Center(
                      child: Text(
                        'Tidak ada siswa yang sesuai pencarian.',
                        style: TextStyle(fontSize: 12.5, color: AppColors.gray500),
                      ),
                    ),
                  )
                else
                  ...filteredRows.map((row) => _buildAttendanceRowCard(row)),
              ],
            ),
          ),

          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            height: 48,
            child: FilledButton.icon(
              onPressed: _submitting ? null : _submitJournal,
              icon: _submitting
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                    )
                  : const Icon(Icons.save_rounded, size: 19),
              label: const Text(
                'Simpan Jurnal & Absensi',
                style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.w700),
              ),
              style: FilledButton.styleFrom(
                backgroundColor: AppColors.blue600,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ),
          const SizedBox(height: 24),
        ],
      ),
    );
  }

  Widget _buildAttendanceRowCard(GuruExtraAttendanceRow row) {
    final activeColor = switch (row.status) {
      'hadir' => AppColors.green600,
      'izin' => AppColors.sky600,
      'sakit' => AppColors.purple500,
      _ => AppColors.red500,
    };

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 9),
      decoration: BoxDecoration(
        color: AppColors.gray50,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: activeColor.withValues(alpha: 0.25)),
      ),
      child: Row(
        children: [
          CircleAvatar(
            radius: 17,
            backgroundColor: activeColor.withValues(alpha: 0.14),
            child: Text(
              row.name.isNotEmpty ? row.name[0].toUpperCase() : '?',
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.bold,
                color: activeColor,
              ),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Wrap(
                  spacing: 6,
                  runSpacing: 2,
                  crossAxisAlignment: WrapCrossAlignment.center,
                  children: [
                    Text(
                      row.name,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        color: AppColors.gray800,
                      ),
                    ),
                    if (row.isLeader)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                        decoration: BoxDecoration(
                          color: AppColors.amber100,
                          borderRadius: BorderRadius.circular(5),
                        ),
                        child: Text(
                          row.roleLabel,
                          style: const TextStyle(
                            fontSize: 9.5,
                            fontWeight: FontWeight.w700,
                            color: AppColors.amber800,
                          ),
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: 2),
                Text(
                  '${row.className}${row.nis != null && row.nis!.isNotEmpty ? ' • ${row.nis}' : ''}',
                  style: const TextStyle(fontSize: 11, color: AppColors.gray500),
                ),
              ],
            ),
          ),
          const SizedBox(width: 6),
          Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              _statusPill(row, 'H', 'hadir', AppColors.green600),
              const SizedBox(width: 4),
              _statusPill(row, 'I', 'izin', AppColors.sky600),
              const SizedBox(width: 4),
              _statusPill(row, 'S', 'sakit', AppColors.purple500),
              const SizedBox(width: 4),
              _statusPill(row, 'A', 'alpa', AppColors.red500),
            ],
          ),
        ],
      ),
    );
  }

  Widget _statusPill(
    GuruExtraAttendanceRow row,
    String label,
    String value,
    Color color,
  ) {
    final selected = row.status == value;
    return GestureDetector(
      onTap: () => setState(() => row.status = value),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 140),
        width: 28,
        height: 28,
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: selected ? color : Colors.white,
          borderRadius: BorderRadius.circular(7),
          border: Border.all(color: selected ? color : AppColors.gray300),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11.5,
            fontWeight: FontWeight.w700,
            color: selected ? Colors.white : AppColors.gray600,
          ),
        ),
      ),
    );
  }

  // ─── Tab 2: Riwayat Jurnal ─────────────────────────────────────────────────

  Widget _buildHistoryTab() {
    const months = [
      'Semua Bulan',
      'Januari',
      'Februari',
      'Maret',
      'April',
      'Mei',
      'Juni',
      'Juli',
      'Agustus',
      'September',
      'Oktober',
      'November',
      'Desember',
    ];
    final currentYear = DateTime.now().year;
    final years = [currentYear - 1, currentYear, currentYear + 1];

    return Column(
      children: [
        Container(
          color: Colors.white,
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 10),
          child: Row(
            children: [
              Expanded(
                child: DropdownButtonFormField<int?>(
                  initialValue: _filterMonth,
                  isDense: true,
                  decoration: InputDecoration(
                    contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                  items: List.generate(months.length, (i) {
                    return DropdownMenuItem<int?>(
                      value: i == 0 ? null : i,
                      child: Text(months[i], style: const TextStyle(fontSize: 12.5)),
                    );
                  }),
                  onChanged: (val) {
                    setState(() => _filterMonth = val);
                    if (_selectedExtra != null) {
                      _loadJournals(_selectedExtra!.id, reset: true);
                    }
                  },
                ),
              ),
              if (_filterMonth != null) ...[
                const SizedBox(width: 8),
                SizedBox(
                  width: 96,
                  child: DropdownButtonFormField<int>(
                    initialValue: _filterYear,
                    isDense: true,
                    decoration: InputDecoration(
                      contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                    items: years
                        .map((y) => DropdownMenuItem(
                              value: y,
                              child: Text('$y', style: const TextStyle(fontSize: 12.5)),
                            ))
                        .toList(),
                    onChanged: (val) {
                      if (val == null) return;
                      setState(() => _filterYear = val);
                      if (_selectedExtra != null) {
                        _loadJournals(_selectedExtra!.id, reset: true);
                      }
                    },
                  ),
                ),
              ],
            ],
          ),
        ),
        Expanded(
          child: _loadingJournals && _journals.isEmpty
              ? const Center(child: CircularProgressIndicator())
              : _journalsError != null && _journals.isEmpty
                  ? _buildError(
                      _journalsError!,
                      () => _loadJournals(_selectedExtra!.id, reset: true),
                    )
                  : _journals.isEmpty
                      ? Center(
                          child: Padding(
                            padding: const EdgeInsets.all(24),
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(
                                  Icons.menu_book_outlined,
                                  size: 48,
                                  color: AppColors.gray300,
                                ),
                                const SizedBox(height: 10),
                                const Text(
                                  'Belum Ada Jurnal Ekstrakurikuler',
                                  style: TextStyle(
                                    fontSize: 15,
                                    fontWeight: FontWeight.w700,
                                    color: AppColors.gray700,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                const Text(
                                  'Buat jurnal & absensi kegiatan pertama pada tab Jurnal & Absen.',
                                  style: TextStyle(fontSize: 12.5, color: AppColors.gray500),
                                  textAlign: TextAlign.center,
                                ),
                                const SizedBox(height: 14),
                                FilledButton.icon(
                                  onPressed: () => _tabCtrl.animateTo(0),
                                  icon: const Icon(Icons.add_rounded, size: 18),
                                  label: const Text('Buat Jurnal Sekarang'),
                                ),
                              ],
                            ),
                          ),
                        )
                      : RefreshIndicator(
                          onRefresh: () => _loadJournals(_selectedExtra!.id, reset: true),
                          child: ListView.builder(
                            padding: const EdgeInsets.all(16),
                            itemCount: _journals.length + (_journalHasMore ? 1 : 0),
                            itemBuilder: (_, i) {
                              if (i == _journals.length) {
                                return Padding(
                                  padding: const EdgeInsets.symmetric(vertical: 8),
                                  child: Center(
                                    child: _loadingJournals
                                        ? const CircularProgressIndicator()
                                        : OutlinedButton(
                                            onPressed: () => _loadJournals(_selectedExtra!.id),
                                            child: const Text('Muat Lebih Banyak'),
                                          ),
                                  ),
                                );
                              }
                              return _buildJournalHistoryCard(_journals[i]);
                            },
                          ),
                        ),
        ),
      ],
    );
  }

  Widget _buildJournalHistoryCard(GuruExtraJournal j) {
    return GestureDetector(
      onTap: () async {
        final changed = await Navigator.push<bool>(
          context,
          MaterialPageRoute(
            builder: (_) => GuruExtracurricularJournalDetailScreen(journalId: j.id),
          ),
        );
        if (changed == true && _selectedExtra != null) {
          await Future.wait([
            _loadMembers(_selectedExtra!.id),
            _loadJournals(_selectedExtra!.id, reset: true),
          ]);
        }
      },
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppColors.gray200),
          boxShadow: AppShadow.sm,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    '${j.dateFormatted} • ${j.startTime}–${j.endTime}',
                    style: const TextStyle(
                      fontSize: 11.5,
                      fontWeight: FontWeight.w600,
                      color: AppColors.blue600,
                    ),
                  ),
                ),
                const Icon(Icons.chevron_right_rounded, color: AppColors.gray400, size: 20),
              ],
            ),
            const SizedBox(height: 4),
            Text(
              j.title,
              style: const TextStyle(
                fontSize: 14.5,
                fontWeight: FontWeight.w800,
                color: AppColors.gray900,
              ),
            ),
            if (j.location != null && j.location!.isNotEmpty) ...[
              const SizedBox(height: 3),
              Row(
                children: [
                  const Icon(Icons.location_on_outlined, size: 13, color: AppColors.gray500),
                  const SizedBox(width: 3),
                  Expanded(
                    child: Text(
                      j.location!,
                      style: const TextStyle(fontSize: 11.5, color: AppColors.gray500),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ],
            if (j.material != null && j.material!.isNotEmpty) ...[
              const SizedBox(height: 8),
              Text(
                'Materi: ${j.material!}',
                style: const TextStyle(fontSize: 12.5, color: AppColors.gray700),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ],
            if (j.activity != null && j.activity!.isNotEmpty) ...[
              const SizedBox(height: 3),
              Text(
                j.activity!,
                style: const TextStyle(fontSize: 12, color: AppColors.gray500),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ],
            if (j.photoUrl != null && j.photoUrl!.isNotEmpty) ...[
              const SizedBox(height: 8),
              GestureDetector(
                onTap: () => ImageViewerDialog.show(
                  context,
                  imageUrl: j.photoUrl!,
                  title: j.title,
                ),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: Image.network(
                    j.photoUrl!,
                    height: 110,
                    width: double.infinity,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => const SizedBox.shrink(),
                  ),
                ),
              ),
            ],
            const SizedBox(height: 10),
            Wrap(
              spacing: 6,
              runSpacing: 6,
              children: [
                _sumChip('H', j.hadirCount, AppColors.green600),
                _sumChip('I', j.izinCount, AppColors.sky600),
                _sumChip('S', j.sakitCount, AppColors.purple500),
                _sumChip('A', j.alpaCount, AppColors.red500),
              ],
            ),
          ],
        ),
      ),
    );
  }

  // ─── Tab 3: Daftar Anggota Ekstra ──────────────────────────────────────────

  Widget _buildMembersTab() {
    final extra = _selectedExtra!;
    final q = _memberSearch.trim().toLowerCase();
    final filtered = q.isEmpty
        ? _members
        : _members.where((m) {
            return m.name.toLowerCase().contains(q) ||
                (m.nis ?? '').toLowerCase().contains(q) ||
                m.className.toLowerCase().contains(q);
          }).toList();

    return RefreshIndicator(
      onRefresh: () => _loadMembers(extra.id),
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          SizedBox(
            width: double.infinity,
            child: FilledButton.icon(
              onPressed: _downloadingPdf ? null : _downloadMembersPdf,
              icon: _downloadingPdf
                  ? const SizedBox(
                      width: 16,
                      height: 16,
                      child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                    )
                  : const Icon(Icons.picture_as_pdf_rounded, size: 18),
              label: Text(
                _downloadingPdf ? 'Menyiapkan PDF...' : 'Download Daftar Peserta (PDF)',
                style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w700),
              ),
              style: FilledButton.styleFrom(
                backgroundColor: AppColors.emerald600,
                padding: const EdgeInsets.symmetric(vertical: 11),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
            ),
          ),
          const SizedBox(height: 10),
          if (extra.pendingMembersCount > 0)
            Container(
              margin: const EdgeInsets.only(bottom: 10),
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.amber50,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: AppColors.amber200),
              ),
              child: Row(
                children: [
                  const Icon(Icons.pending_actions_rounded, size: 18, color: AppColors.amber700),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Ada ${extra.pendingMembersCount} pengajuan masuk/keluar anggota yang menunggu persetujuan pembina di Beranda.',
                      style: const TextStyle(fontSize: 12, color: AppColors.amber900),
                    ),
                  ),
                ],
              ),
            ),
          TextField(
            controller: _memberSearchCtrl,
            onChanged: (v) => setState(() => _memberSearch = v),
            decoration: InputDecoration(
              hintText: 'Cari nama anggota, NIS, atau kelas...',
              hintStyle: const TextStyle(fontSize: 12.5, color: AppColors.gray400),
              prefixIcon: const Icon(Icons.search_rounded, size: 18),
              suffixIcon: _memberSearch.isNotEmpty
                  ? IconButton(
                      icon: const Icon(Icons.clear_rounded, size: 16),
                      onPressed: () {
                        _memberSearchCtrl.clear();
                        setState(() => _memberSearch = '');
                      },
                    )
                  : null,
              filled: true,
              fillColor: Colors.white,
              isDense: true,
              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
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
          const SizedBox(height: 12),
          if (_loadingMembers)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 30),
              child: Center(child: CircularProgressIndicator()),
            )
          else if (_membersError != null)
            _buildError(_membersError!, () => _loadMembers(extra.id))
          else if (filtered.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 24),
              child: Center(
                child: Text(
                  'Tidak ada siswa yang terdaftar pada ekstra ini.',
                  style: TextStyle(fontSize: 13, color: AppColors.gray500),
                ),
              ),
            )
          else
            ...filtered.map((m) => Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppColors.gray200),
                  ),
                  child: Row(
                    children: [
                      CircleAvatar(
                        radius: 19,
                        backgroundColor: AppColors.blue50,
                        child: Text(
                          m.name.isNotEmpty ? m.name[0].toUpperCase() : '?',
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            color: AppColors.blue700,
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Wrap(
                              spacing: 6,
                              runSpacing: 2,
                              crossAxisAlignment: WrapCrossAlignment.center,
                              children: [
                                Text(
                                  m.name,
                                  style: const TextStyle(
                                    fontSize: 13.5,
                                    fontWeight: FontWeight.w700,
                                    color: AppColors.gray900,
                                  ),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                                  decoration: BoxDecoration(
                                    color: m.isLeader ? AppColors.amber100 : AppColors.slate100,
                                    borderRadius: BorderRadius.circular(5),
                                  ),
                                  child: Text(
                                    m.roleLabel,
                                    style: TextStyle(
                                      fontSize: 10,
                                      fontWeight: FontWeight.w700,
                                      color: m.isLeader ? AppColors.amber800 : AppColors.gray600,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 2),
                            Text(
                              'Kelas ${m.className}${m.nis != null && m.nis!.isNotEmpty ? ' • NIS ${m.nis}' : ''}',
                              style: const TextStyle(fontSize: 11.5, color: AppColors.gray500),
                            ),
                            if (m.totalSessions > 0) ...[
                              const SizedBox(height: 6),
                              Wrap(
                                spacing: 5,
                                children: [
                                  _miniBadge('H:${m.hadirCount}', AppColors.green600),
                                  _miniBadge('I:${m.izinCount}', AppColors.sky600),
                                  _miniBadge('S:${m.sakitCount}', AppColors.purple500),
                                  _miniBadge('A:${m.alpaCount}', AppColors.red500),
                                  Text(
                                    'dari ${m.totalSessions} sesi',
                                    style: const TextStyle(fontSize: 10.5, color: AppColors.gray400),
                                  ),
                                ],
                              ),
                            ],
                          ],
                        ),
                      ),
                    ],
                  ),
                )),
        ],
      ),
    );
  }

  Widget _miniBadge(String text, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(5),
      ),
      child: Text(
        text,
        style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: color),
      ),
    );
  }

  Widget _sumChip(String label, int count, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        '$label: $count',
        style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: color),
      ),
    );
  }

  Widget _formLabel(String text) => Text(
        text,
        style: const TextStyle(
          fontSize: 12.5,
          fontWeight: FontWeight.w700,
          color: AppColors.gray700,
        ),
      );

  Widget _formInput(TextEditingController ctrl, String hint, {int maxLines = 1}) {
    return TextField(
      controller: ctrl,
      maxLines: maxLines,
      style: const TextStyle(fontSize: 13, color: AppColors.gray800),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: const TextStyle(fontSize: 12.5, color: AppColors.gray400),
        filled: true,
        fillColor: AppColors.gray50,
        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: AppColors.gray200),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: AppColors.gray200),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: AppColors.blue600, width: 1.5),
        ),
      ),
    );
  }

  Widget _buildError(String msg, VoidCallback onRetry) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.error_outline, size: 42, color: AppColors.gray400),
            const SizedBox(height: 10),
            Text(msg, textAlign: TextAlign.center, style: const TextStyle(fontSize: 13)),
            const SizedBox(height: 14),
            FilledButton.icon(
              onPressed: onRetry,
              icon: const Icon(Icons.refresh_rounded, size: 17),
              label: const Text('Coba Lagi'),
            ),
          ],
        ),
      ),
    );
  }
}
