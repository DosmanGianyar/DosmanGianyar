import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/extracurricular_provider.dart';
import '../../theme/app_colors.dart';

class CreateSessionScreen extends StatefulWidget {
  const CreateSessionScreen({super.key});

  @override
  State<CreateSessionScreen> createState() => _CreateSessionScreenState();
}

class _CreateSessionScreenState extends State<CreateSessionScreen> {
  final _formKey = GlobalKey<FormState>();

  // Fields
  int?                    _selectedExtraId;
  final _titleCtrl        = TextEditingController();
  DateTime?               _sessionDate;
  final _locationCtrl     = TextEditingController();
  final _notesCtrl        = TextEditingController();
  TimeOfDay?              _startTime;
  TimeOfDay?              _endTime;

  @override
  void dispose() {
    _titleCtrl.dispose();
    _locationCtrl.dispose();
    _notesCtrl.dispose();
    super.dispose();
  }

  String _formatTime(TimeOfDay? t) {
    if (t == null) return '';
    final h = t.hour.toString().padLeft(2, '0');
    final m = t.minute.toString().padLeft(2, '0');
    return '$h:$m';
  }

  String _formatDate(DateTime? d) {
    if (d == null) return '';
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return '${d.day} ${months[d.month - 1]} ${d.year}';
  }

  Future<void> _pickDate() async {
    final now  = DateTime.now();
    final date = await showDatePicker(
      context: context,
      initialDate: _sessionDate ?? now,
      firstDate: now.subtract(const Duration(days: 30)),
      lastDate: now.add(const Duration(days: 365)),
    );
    if (date != null) setState(() => _sessionDate = date);
  }

  Future<void> _pickTime({required bool isStart}) async {
    final initial = isStart
        ? (_startTime ?? const TimeOfDay(hour: 14, minute: 0))
        : (_endTime   ?? const TimeOfDay(hour: 16, minute: 0));
    final t = await showTimePicker(context: context, initialTime: initial);
    if (t != null) {
      setState(() {
        if (isStart) _startTime = t;
        else          _endTime   = t;
      });
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_sessionDate == null) {
      _showError('Pilih tanggal sesi.');
      return;
    }
    if (_startTime == null || _endTime == null) {
      _showError('Pilih waktu mulai dan selesai.');
      return;
    }
    final start = _startTime!;
    final end   = _endTime!;
    if (end.hour < start.hour || (end.hour == start.hour && end.minute <= start.minute)) {
      _showError('Waktu selesai harus lebih dari waktu mulai.');
      return;
    }

    final data = {
      'extracurricular_id': _selectedExtraId,
      'title':              _titleCtrl.text.trim(),
      'session_date':       _sessionDate!.toIso8601String().substring(0, 10),
      'start_time':         _formatTime(_startTime),
      'end_time':           _formatTime(_endTime),
      'location':           _locationCtrl.text.trim().isEmpty ? null : _locationCtrl.text.trim(),
      'notes':              _notesCtrl.text.trim().isEmpty ? null : _notesCtrl.text.trim(),
    };

    final p = context.read<ExtracurricularProvider>();
    final ok = await p.createSession(data);

    if (context.mounted) {
      if (ok) {
        Navigator.pop(context, true);
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Sesi berhasil dibuat.'),
          backgroundColor: AppColors.green500,
        ));
      } else {
        _showError(p.actionError ?? 'Gagal membuat sesi.');
      }
    }
  }

  void _showError(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: AppColors.red500,
    ));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.gray50,
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.close_rounded),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text('Buat Sesi Absen'),
        flexibleSpace: Container(
          decoration: const BoxDecoration(gradient: AppColors.primaryGradient),
        ),
        iconTheme: const IconThemeData(color: Colors.white),
        titleTextStyle: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
      ),
      body: Consumer<ExtracurricularProvider>(
        builder: (_, p, __) {
          // Only show active extras where user is ketua
          final ketuaExtras = p.myExtras.where((e) => e.isKetua && e.isActive).toList();

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _SectionLabel('Ekstrakurikuler'),
                  _Card(
                    child: DropdownButtonFormField<int>(
                      value: _selectedExtraId,
                      decoration: const InputDecoration(
                        border: InputBorder.none,
                        hintText: 'Pilih ekstrakurikuler',
                        hintStyle: TextStyle(color: AppColors.gray400, fontSize: 14),
                        contentPadding: EdgeInsets.zero,
                      ),
                      items: ketuaExtras.map((e) => DropdownMenuItem(
                        value: e.id,
                        child: Text(e.name, style: const TextStyle(fontSize: 14)),
                      )).toList(),
                      onChanged: (v) => setState(() => _selectedExtraId = v),
                      validator: (v) => v == null ? 'Pilih ekstrakurikuler' : null,
                    ),
                  ),
                  const SizedBox(height: 16),

                  _SectionLabel('Judul Sesi'),
                  _Card(
                    child: TextFormField(
                      controller: _titleCtrl,
                      decoration: const InputDecoration(
                        border: InputBorder.none,
                        hintText: 'mis. Latihan Rutin Minggu ke-3',
                        hintStyle: TextStyle(color: AppColors.gray400, fontSize: 14),
                        contentPadding: EdgeInsets.zero,
                      ),
                      style: const TextStyle(fontSize: 14, color: AppColors.gray800),
                      maxLength: 120,
                      validator: (v) => (v == null || v.trim().isEmpty) ? 'Wajib diisi' : null,
                    ),
                  ),
                  const SizedBox(height: 16),

                  _SectionLabel('Tanggal & Waktu'),
                  _Card(
                    child: Column(
                      children: [
                        _PickerTile(
                          icon: Icons.calendar_today_rounded,
                          label: 'Tanggal',
                          value: _sessionDate != null ? _formatDate(_sessionDate) : null,
                          hint: 'Pilih tanggal',
                          onTap: _pickDate,
                        ),
                        const Divider(height: 1),
                        _PickerTile(
                          icon: Icons.access_time_rounded,
                          label: 'Mulai',
                          value: _startTime != null ? _formatTime(_startTime) : null,
                          hint: 'Pilih jam mulai',
                          onTap: () => _pickTime(isStart: true),
                        ),
                        const Divider(height: 1),
                        _PickerTile(
                          icon: Icons.access_time_filled_rounded,
                          label: 'Selesai',
                          value: _endTime != null ? _formatTime(_endTime) : null,
                          hint: 'Pilih jam selesai',
                          onTap: () => _pickTime(isStart: false),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),

                  _SectionLabel('Lokasi (Opsional)'),
                  _Card(
                    child: TextFormField(
                      controller: _locationCtrl,
                      decoration: const InputDecoration(
                        border: InputBorder.none,
                        hintText: 'mis. Aula Sekolah, Lapangan Basket',
                        hintStyle: TextStyle(color: AppColors.gray400, fontSize: 14),
                        contentPadding: EdgeInsets.zero,
                        prefixIcon: Icon(Icons.location_on_outlined, size: 18, color: AppColors.gray500),
                        prefixIconConstraints: BoxConstraints(minWidth: 32),
                      ),
                      style: const TextStyle(fontSize: 14, color: AppColors.gray800),
                      maxLength: 100,
                    ),
                  ),
                  const SizedBox(height: 16),

                  _SectionLabel('Catatan (Opsional)'),
                  _Card(
                    child: TextFormField(
                      controller: _notesCtrl,
                      decoration: const InputDecoration(
                        border: InputBorder.none,
                        hintText: 'Tambahkan catatan untuk anggota...',
                        hintStyle: TextStyle(color: AppColors.gray400, fontSize: 14),
                        contentPadding: EdgeInsets.zero,
                      ),
                      style: const TextStyle(fontSize: 14, color: AppColors.gray800),
                      maxLines: 3,
                      minLines: 2,
                    ),
                  ),
                  const SizedBox(height: 28),

                  SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: FilledButton.icon(
                      onPressed: p.actionLoading ? null : _submit,
                      icon: p.actionLoading
                          ? const SizedBox(
                              width: 16,
                              height: 16,
                              child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                            )
                          : const Icon(Icons.check_rounded, size: 18),
                      label: const Text('Buat Sesi', style: TextStyle(fontSize: 15)),
                      style: FilledButton.styleFrom(
                        backgroundColor: AppColors.blue600,
                        shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
                      ),
                    ),
                  ),
                  const SizedBox(height: 32),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}

// ─── Helper widgets ───────────────────────────────────────────────────────────

class _SectionLabel extends StatelessWidget {
  final String text;
  const _SectionLabel(this.text);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6, left: 2),
      child: Text(
        text,
        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.gray500, letterSpacing: 0.5),
      ),
    );
  }
}

class _Card extends StatelessWidget {
  final Widget child;
  const _Card({required this.child});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: AppRadius.card,
        boxShadow: AppShadow.sm,
      ),
      child: child,
    );
  }
}

class _PickerTile extends StatelessWidget {
  final IconData  icon;
  final String    label;
  final String?   value;
  final String    hint;
  final VoidCallback onTap;
  const _PickerTile({
    required this.icon,
    required this.label,
    required this.value,
    required this.hint,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 12),
        child: Row(
          children: [
            Icon(icon, size: 18, color: AppColors.blue600),
            const SizedBox(width: 12),
            Text(
              label,
              style: const TextStyle(fontSize: 13, color: AppColors.gray500, fontWeight: FontWeight.w500),
            ),
            const Spacer(),
            Text(
              value ?? hint,
              style: TextStyle(
                fontSize: 14,
                color: value != null ? AppColors.gray800 : AppColors.gray400,
                fontWeight: value != null ? FontWeight.w600 : FontWeight.normal,
              ),
            ),
            const SizedBox(width: 4),
            const Icon(Icons.chevron_right_rounded, size: 18, color: AppColors.gray400),
          ],
        ),
      ),
    );
  }
}
