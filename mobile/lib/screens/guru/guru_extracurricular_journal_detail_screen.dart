import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import '../../models/guru_extracurricular.dart';
import '../../services/api_client.dart';
import '../../services/guru_service.dart';
import '../../theme/app_colors.dart';
import '../../widgets/image_viewer_dialog.dart';

class GuruExtracurricularJournalDetailScreen extends StatefulWidget {
  final int journalId;

  const GuruExtracurricularJournalDetailScreen({
    super.key,
    required this.journalId,
  });

  @override
  State<GuruExtracurricularJournalDetailScreen> createState() =>
      _GuruExtracurricularJournalDetailScreenState();
}

class _GuruExtracurricularJournalDetailScreenState
    extends State<GuruExtracurricularJournalDetailScreen> {
  GuruExtraJournal? _journal;
  List<GuruExtraAttendanceRow> _members = [];
  bool _loading = true;
  bool _savingAttendance = false;
  bool _deleting = false;
  bool _changed = false;
  String? _error;
  String _searchQuery = '';
  final _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadDetail();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadDetail() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final res = await GuruService.getExtracurricularJournalDetail(widget.journalId);
      if (!mounted) return;
      setState(() {
        _journal = res.journal;
        _members = res.members;
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = ApiClient.extractError(e);
        _loading = false;
      });
    }
  }

  Future<void> _saveAttendance() async {
    if (_members.isEmpty || _savingAttendance) return;
    setState(() => _savingAttendance = true);
    try {
      final payload = _members.map((m) => m.toPayload()).toList();
      final res = await GuruService.saveExtracurricularAttendance(
        widget.journalId,
        payload,
      );
      if (!mounted) return;
      setState(() {
        _journal = res.journal;
        _savingAttendance = false;
        _changed = true;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res.message),
          backgroundColor: AppColors.green600,
        ),
      );
    } catch (e) {
      if (!mounted) return;
      setState(() => _savingAttendance = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(ApiClient.extractError(e)),
          backgroundColor: AppColors.red500,
        ),
      );
    }
  }

  Future<void> _confirmDelete() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Hapus Jurnal Ekstrakurikuler?'),
        content: const Text(
          'Jurnal kegiatan, foto dokumentasi, dan data absensi pada sesi ini akan dihapus permanen.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            style: FilledButton.styleFrom(backgroundColor: AppColors.red500),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );
    if (ok != true || !mounted) return;

    setState(() => _deleting = true);
    try {
      final msg = await GuruService.deleteExtracurricularJournal(widget.journalId);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(msg), backgroundColor: AppColors.green600),
      );
      Navigator.pop(context, true);
    } catch (e) {
      if (!mounted) return;
      setState(() => _deleting = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(ApiClient.extractError(e)),
          backgroundColor: AppColors.red500,
        ),
      );
    }
  }

  Future<void> _openEditSheet() async {
    final journal = _journal;
    if (journal == null) return;
    final updated = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _EditExtraJournalSheet(journal: journal),
    );
    if (updated == true) {
      _changed = true;
      await _loadDetail();
    }
  }

  List<GuruExtraAttendanceRow> get _filteredMembers {
    final q = _searchQuery.trim().toLowerCase();
    if (q.isEmpty) return _members;
    return _members.where((m) {
      return m.name.toLowerCase().contains(q) ||
          (m.nis ?? '').toLowerCase().contains(q) ||
          m.className.toLowerCase().contains(q);
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, _) {
        if (!didPop) {
          Navigator.pop(context, _changed);
        }
      },
      child: Scaffold(
        backgroundColor: AppColors.slate100,
        appBar: AppBar(
          title: const Text('Detail Jurnal Ekstra'),
          leading: IconButton(
            icon: const Icon(Icons.arrow_back_rounded),
            onPressed: () => Navigator.pop(context, _changed),
          ),
          actions: [
            if (_journal != null && !_loading) ...[
              IconButton(
                onPressed: _deleting ? null : _openEditSheet,
                icon: const Icon(Icons.edit_outlined),
                tooltip: 'Edit Jurnal',
              ),
              IconButton(
                onPressed: _deleting ? null : _confirmDelete,
                icon: _deleting
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Icon(Icons.delete_outline_rounded, color: AppColors.red500),
                tooltip: 'Hapus Jurnal',
              ),
            ],
          ],
        ),
        body: _loading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? Center(
                    child: Padding(
                      padding: const EdgeInsets.all(24),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.error_outline, size: 48, color: AppColors.gray400),
                          const SizedBox(height: 12),
                          Text(_error!, textAlign: TextAlign.center),
                          const SizedBox(height: 16),
                          FilledButton.icon(
                            onPressed: _loadDetail,
                            icon: const Icon(Icons.refresh_rounded, size: 18),
                            label: const Text('Coba Lagi'),
                          ),
                        ],
                      ),
                    ),
                  )
                : RefreshIndicator(
                    onRefresh: _loadDetail,
                    child: ListView(
                      padding: const EdgeInsets.all(16),
                      children: [
                        _buildJournalDetailCard(_journal!),
                        const SizedBox(height: 14),
                        _buildAttendanceSection(),
                        const SizedBox(height: 24),
                      ],
                    ),
                  ),
      ),
    );
  }

  Widget _buildJournalDetailCard(GuruExtraJournal j) {
    return Container(
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
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: AppColors.blue50,
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  j.extracurricularName,
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: AppColors.blue700,
                  ),
                ),
              ),
              const Spacer(),
              Text(
                '${j.startTime} – ${j.endTime}',
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: AppColors.gray600,
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            j.title,
            style: const TextStyle(
              fontSize: 17,
              fontWeight: FontWeight.w800,
              color: AppColors.gray900,
            ),
          ),
          const SizedBox(height: 6),
          Row(
            children: [
              const Icon(Icons.calendar_today_rounded, size: 13, color: AppColors.gray500),
              const SizedBox(width: 5),
              Text(
                j.dateFormatted,
                style: const TextStyle(fontSize: 12, color: AppColors.gray600),
              ),
              if (j.location != null && j.location!.isNotEmpty) ...[
                const SizedBox(width: 12),
                const Icon(Icons.location_on_outlined, size: 14, color: AppColors.gray500),
                const SizedBox(width: 3),
                Expanded(
                  child: Text(
                    j.location!,
                    style: const TextStyle(fontSize: 12, color: AppColors.gray600),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ],
            ],
          ),
          if (j.creatorName != null && j.creatorName!.isNotEmpty) ...[
            const SizedBox(height: 4),
            Text(
              'Dibuat oleh: ${j.creatorName}',
              style: const TextStyle(fontSize: 11, color: AppColors.gray400),
            ),
          ],
          const Divider(height: 22),
          if (j.material != null && j.material!.isNotEmpty)
            _detailItem('Materi Pembinaan', j.material!),
          if (j.activity != null && j.activity!.isNotEmpty)
            _detailItem('Uraian Kegiatan', j.activity!),
          if (j.obstacles != null && j.obstacles!.isNotEmpty)
            _detailItem('Kendala / Evaluasi', j.obstacles!),
          if (j.notes != null && j.notes!.isNotEmpty)
            _detailItem('Catatan / Tindak Lanjut', j.notes!),
          if (j.photoUrl != null && j.photoUrl!.isNotEmpty) ...[
            const SizedBox(height: 6),
            const Text(
              'Foto Dokumentasi',
              style: TextStyle(
                fontSize: 11.5,
                fontWeight: FontWeight.w700,
                color: AppColors.gray600,
              ),
            ),
            const SizedBox(height: 6),
            GestureDetector(
              onTap: () => ImageViewerDialog.show(
                context,
                imageUrl: j.photoUrl!,
                title: j.title,
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(10),
                child: Image.network(
                  j.photoUrl!,
                  height: 170,
                  width: double.infinity,
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => Container(
                    height: 80,
                    color: AppColors.gray100,
                    alignment: Alignment.center,
                    child: const Text('Foto tidak dapat dimuat'),
                  ),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _detailItem(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: const TextStyle(
              fontSize: 11.5,
              fontWeight: FontWeight.w700,
              color: AppColors.gray500,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            value,
            style: const TextStyle(
              fontSize: 13.5,
              color: AppColors.gray800,
              height: 1.4,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAttendanceSection() {
    final hadir = _members.where((m) => m.status == 'hadir').length;
    final izin = _members.where((m) => m.status == 'izin').length;
    final sakit = _members.where((m) => m.status == 'sakit').length;
    final alpa = _members.where((m) => m.status == 'alpa').length;
    final filtered = _filteredMembers;

    return Container(
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
                  'Absensi Anggota (${_members.length} siswa)',
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: AppColors.gray900,
                  ),
                ),
              ),
              TextButton.icon(
                onPressed: () {
                  setState(() {
                    for (final m in _members) {
                      m.status = 'hadir';
                    }
                  });
                },
                icon: const Icon(Icons.done_all_rounded, size: 16),
                label: const Text('Hadir Semua', style: TextStyle(fontSize: 12)),
              ),
            ],
          ),
          const SizedBox(height: 8),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _statChip('Hadir', hadir, AppColors.green600),
                const SizedBox(width: 6),
                _statChip('Izin', izin, AppColors.sky600),
                const SizedBox(width: 6),
                _statChip('Sakit', sakit, AppColors.purple500),
                const SizedBox(width: 6),
                _statChip('Alpa', alpa, AppColors.red500),
              ],
            ),
          ),
          const SizedBox(height: 10),
          TextField(
            controller: _searchCtrl,
            onChanged: (v) => setState(() => _searchQuery = v),
            decoration: InputDecoration(
              hintText: 'Cari nama, NIS, atau kelas...',
              hintStyle: const TextStyle(fontSize: 12.5, color: AppColors.gray400),
              prefixIcon: const Icon(Icons.search_rounded, size: 18),
              suffixIcon: _searchQuery.isNotEmpty
                  ? IconButton(
                      icon: const Icon(Icons.clear_rounded, size: 16),
                      onPressed: () {
                        _searchCtrl.clear();
                        setState(() => _searchQuery = '');
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
          const SizedBox(height: 12),
          if (filtered.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 16),
              child: Center(
                child: Text(
                  'Tidak ada anggota sesuai pencarian.',
                  style: TextStyle(fontSize: 12.5, color: AppColors.gray500),
                ),
              ),
            )
          else
            ...filtered.map((m) => _AttendanceStudentTile(
                  row: m,
                  onChanged: (st) => setState(() => m.status = st),
                )),
          if (_members.isNotEmpty) ...[
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              height: 46,
              child: FilledButton.icon(
                onPressed: _savingAttendance ? null : _saveAttendance,
                icon: _savingAttendance
                    ? const SizedBox(
                        width: 16,
                        height: 16,
                        child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                      )
                    : const Icon(Icons.save_rounded, size: 18),
                label: const Text('Simpan Perubahan Absensi'),
                style: FilledButton.styleFrom(
                  backgroundColor: AppColors.blue600,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _statChip(String label, int count, Color color) {
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
}

class _AttendanceStudentTile extends StatelessWidget {
  final GuruExtraAttendanceRow row;
  final ValueChanged<String> onChanged;

  const _AttendanceStudentTile({
    required this.row,
    required this.onChanged,
  });

  Color _statusColor(String status) {
    return switch (status) {
      'hadir' => AppColors.green600,
      'izin' => AppColors.sky600,
      'sakit' => AppColors.purple500,
      _ => AppColors.red500,
    };
  }

  @override
  Widget build(BuildContext context) {
    final activeColor = _statusColor(row.status);
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
              _pill('H', 'hadir', AppColors.green600),
              const SizedBox(width: 4),
              _pill('I', 'izin', AppColors.sky600),
              const SizedBox(width: 4),
              _pill('S', 'sakit', AppColors.purple500),
              const SizedBox(width: 4),
              _pill('A', 'alpa', AppColors.red500),
            ],
          ),
        ],
      ),
    );
  }

  Widget _pill(String label, String value, Color color) {
    final selected = row.status == value;
    return GestureDetector(
      onTap: () => onChanged(value),
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
}

class _EditExtraJournalSheet extends StatefulWidget {
  final GuruExtraJournal journal;

  const _EditExtraJournalSheet({required this.journal});

  @override
  State<_EditExtraJournalSheet> createState() => _EditExtraJournalSheetState();
}

class _EditExtraJournalSheetState extends State<_EditExtraJournalSheet> {
  late final TextEditingController _titleCtrl;
  late final TextEditingController _locationCtrl;
  late final TextEditingController _materialCtrl;
  late final TextEditingController _activityCtrl;
  late final TextEditingController _obstaclesCtrl;
  late final TextEditingController _notesCtrl;

  late DateTime _selectedDate;
  late TimeOfDay _startTime;
  late TimeOfDay _endTime;
  File? _newPhoto;
  bool _removePhoto = false;
  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    final j = widget.journal;
    _titleCtrl = TextEditingController(text: j.title);
    _locationCtrl = TextEditingController(text: j.location ?? '');
    _materialCtrl = TextEditingController(text: j.material ?? '');
    _activityCtrl = TextEditingController(text: j.activity ?? '');
    _obstaclesCtrl = TextEditingController(text: j.obstacles ?? '');
    _notesCtrl = TextEditingController(text: j.notes ?? '');

    _selectedDate = DateTime.tryParse(j.sessionDate) ?? DateTime.now();
    _startTime = _parseTime(j.startTime, const TimeOfDay(hour: 15, minute: 0));
    _endTime = _parseTime(j.endTime, const TimeOfDay(hour: 17, minute: 0));
  }

  TimeOfDay _parseTime(String raw, TimeOfDay fallback) {
    final parts = raw.split(':');
    if (parts.length >= 2) {
      final h = int.tryParse(parts[0]);
      final m = int.tryParse(parts[1]);
      if (h != null && m != null) return TimeOfDay(hour: h, minute: m);
    }
    return fallback;
  }

  String _formatTime(TimeOfDay t) =>
      '${t.hour.toString().padLeft(2, '0')}:${t.minute.toString().padLeft(2, '0')}';

  @override
  void dispose() {
    _titleCtrl.dispose();
    _locationCtrl.dispose();
    _materialCtrl.dispose();
    _activityCtrl.dispose();
    _obstaclesCtrl.dispose();
    _notesCtrl.dispose();
    super.dispose();
  }

  Future<void> _pickPhoto(ImageSource source) async {
    final picked = await ImagePicker().pickImage(
      source: source,
      imageQuality: 80,
      maxWidth: 1600,
    );
    if (picked != null && mounted) {
      setState(() {
        _newPhoto = File(picked.path);
        _removePhoto = false;
      });
    }
  }

  Future<void> _submit() async {
    if (_titleCtrl.text.trim().isEmpty ||
        _materialCtrl.text.trim().isEmpty ||
        _activityCtrl.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Judul, materi, dan uraian kegiatan wajib diisi.'),
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
      await GuruService.updateExtracurricularJournal(
        widget.journal.id,
        title: _titleCtrl.text.trim(),
        sessionDate: DateFormat('yyyy-MM-dd').format(_selectedDate),
        startTime: _formatTime(_startTime),
        endTime: _formatTime(_endTime),
        location: _locationCtrl.text.trim(),
        material: _materialCtrl.text.trim(),
        activity: _activityCtrl.text.trim(),
        obstacles: _obstaclesCtrl.text.trim(),
        notes: _notesCtrl.text.trim(),
        photo: _newPhoto,
        removePhoto: _removePhoto,
      );
      if (!mounted) return;
      Navigator.pop(context, true);
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
    final bottomInset = MediaQuery.of(context).viewInsets.bottom;
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      padding: EdgeInsets.fromLTRB(16, 12, 16, 16 + bottomInset),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: AppColors.gray300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 12),
            const Text(
              'Edit Jurnal Ekstrakurikuler',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () async {
                      final d = await showDatePicker(
                        context: context,
                        initialDate: _selectedDate,
                        firstDate: DateTime(2023),
                        lastDate: DateTime.now().add(const Duration(days: 30)),
                      );
                      if (d != null) setState(() => _selectedDate = d);
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
            const SizedBox(height: 10),
            _field(_titleCtrl, 'Judul Kegiatan *'),
            const SizedBox(height: 10),
            _field(_locationCtrl, 'Lokasi Kegiatan'),
            const SizedBox(height: 10),
            _field(_materialCtrl, 'Materi Pembinaan *', maxLines: 2),
            const SizedBox(height: 10),
            _field(_activityCtrl, 'Uraian Kegiatan *', maxLines: 3),
            const SizedBox(height: 10),
            _field(_obstaclesCtrl, 'Kendala / Evaluasi', maxLines: 2),
            const SizedBox(height: 10),
            _field(_notesCtrl, 'Catatan / Tindak Lanjut', maxLines: 2),
            const SizedBox(height: 10),
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
                if (_newPhoto != null ||
                    (!_removePhoto &&
                        widget.journal.photoUrl != null &&
                        widget.journal.photoUrl!.isNotEmpty)) ...[
                  const Spacer(),
                  TextButton(
                    onPressed: () => setState(() {
                      _newPhoto = null;
                      _removePhoto = true;
                    }),
                    child: const Text('Hapus Foto', style: TextStyle(color: AppColors.red500)),
                  ),
                ],
              ],
            ),
            if (_newPhoto != null) ...[
              const SizedBox(height: 8),
              ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: Image.file(_newPhoto!, height: 110, width: double.infinity, fit: BoxFit.cover),
              ),
            ],
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              height: 46,
              child: FilledButton(
                onPressed: _submitting ? null : _submit,
                style: FilledButton.styleFrom(backgroundColor: AppColors.blue600),
                child: _submitting
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                      )
                    : const Text('Simpan Perubahan'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _field(TextEditingController ctrl, String label, {int maxLines = 1}) {
    return TextField(
      controller: ctrl,
      maxLines: maxLines,
      decoration: InputDecoration(
        labelText: label,
        filled: true,
        fillColor: AppColors.gray50,
        isDense: true,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
      ),
    );
  }
}
