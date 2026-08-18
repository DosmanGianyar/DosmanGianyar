import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';

class LibraryVisitScreen extends StatefulWidget {
  const LibraryVisitScreen({super.key});

  @override
  State<LibraryVisitScreen> createState() => _LibraryVisitScreenState();
}

class _LibraryVisitScreenState extends State<LibraryVisitScreen> {
  bool _loading = true;
  List<Map<String, dynamic>> _visits = [];

  @override
  void initState() {
    super.initState();
    _loadVisits();
  }

  Future<void> _loadVisits() async {
    setState(() => _loading = true);
    try {
      final res = await ApiClient.get('/siswa/library/visits');
      if (mounted) {
        setState(() {
          _visits = List<Map<String, dynamic>>.from(res['data'] as List? ?? []);
          _loading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _openQrScanner() {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => _QrScannerPage(
          onQrScanned: (scannedCode) {
            Navigator.pop(context); // Close camera page
            _showVisitFormModal(scannedCode);
          },
        ),
      ),
    );
  }

  void _showVisitFormModal([String? defaultCode]) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _VisitFormModal(
        initialQrCode: defaultCode ?? 'SIMS_PERPUS_VISIT',
        onSuccess: _loadVisits,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Kunjungan Perpustakaan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        backgroundColor: Colors.white,
        foregroundColor: AppColors.slate900,
        elevation: 0,
      ),
      body: RefreshIndicator(
        onRefresh: _loadVisits,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // ─── Top Banner ─────────────────────────────────────────────
              Container(
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF0284C7), Color(0xFF2563EB), Color(0xFF4F46E5)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: AppRadius.card,
                ),
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    Container(
                      width: 48,
                      height: 48,
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.20),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: const Icon(Icons.qr_code_scanner_rounded, color: Color(0xFFFDE047), size: 28),
                    ),
                    const SizedBox(width: 12),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('BACA DI TEMPAT', style: TextStyle(color: Color(0xFFFEF08A), fontSize: 10, fontWeight: FontWeight.bold)),
                          Text('Kunjungan Perpustakaan', style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
                          Text('Perpustakaan Wijaya Kusuma SMAN 1 Gianyar', style: TextStyle(color: Color(0xFFE0F2FE), fontSize: 11)),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              // ─── Scan Button Card ─────────────────────────────────────────
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: AppRadius.card,
                  border: Border.all(color: AppColors.slate200),
                  boxShadow: const [BoxShadow(color: Colors.black45, blurRadius: 4, offset: Offset(0, 2))],
                ),
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    const Text(
                      'Scan Kode QR Kunjungan',
                      style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.slate900),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'Arahkan kamera ke Kode QR di banner/meja perpustakaan untuk mencatat kehadiran membaca Anda.',
                      textAlign: TextAlign.center,
                      style: TextStyle(fontSize: 11, color: AppColors.slate500),
                    ),
                    const SizedBox(height: 14),
                    SizedBox(
                      width: double.infinity,
                      height: 46,
                      child: ElevatedButton.icon(
                        onPressed: _openQrScanner,
                        icon: const Icon(Icons.camera_alt_rounded, size: 20),
                        label: const Text('Scan QR Kunjungan Sekarang', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF2563EB),
                          foregroundColor: Colors.white,
                          elevation: 2,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ),
                    const SizedBox(height: 8),
                    TextButton.icon(
                      onPressed: () => _showVisitFormModal('SIMS_PERPUS_VISIT'),
                      icon: const Icon(Icons.edit_note_rounded, size: 16),
                      label: const Text('Input Manual / Test QR', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                      style: TextButton.styleFrom(foregroundColor: AppColors.indigo700),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              // ─── Riwayat Kunjungan Saya ──────────────────────────────────
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Riwayat Kunjungan Saya', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.slate900)),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(color: const Color(0xFFE0F2FE), borderRadius: BorderRadius.circular(20)),
                    child: Text('${_visits.length} Kunjungan', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF0369A1))),
                  ),
                ],
              ),
              const SizedBox(height: 10),

              if (_loading)
                const Center(child: Padding(padding: EdgeInsets.all(32.0), child: CircularProgressIndicator()))
              else if (_visits.isEmpty)
                Container(
                  padding: const EdgeInsets.all(28),
                  decoration: BoxDecoration(color: Colors.white, borderRadius: AppRadius.card, border: Border.all(color: AppColors.slate200)),
                  child: const Column(
                    children: [
                      Icon(Icons.style_outlined, size: 44, color: AppColors.slate400),
                      SizedBox(height: 8),
                      Text('Belum Ada Riwayat Kunjungan', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.slate700)),
                      SizedBox(height: 4),
                      Text('Silakan scan Kode QR Kunjungan saat Anda berada di perpustakaan sekolah.', textAlign: TextAlign.center, style: TextStyle(fontSize: 11, color: AppColors.slate500)),
                    ],
                  ),
                )
              else
                ListView.separated(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: _visits.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 8),
                  itemBuilder: (context, index) {
                    final v = _visits[index];
                    return Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: AppRadius.card,
                        border: Border.all(color: AppColors.slate200),
                      ),
                      padding: const EdgeInsets.all(12),
                      child: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(color: const Color(0xFFE0F2FE), borderRadius: BorderRadius.circular(12)),
                            child: const Icon(Icons.menu_book_rounded, color: Color(0xFF0284C7), size: 20),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(v['purpose'] ?? 'Membaca Buku', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.slate900)),
                                if (v['notes'] != null && (v['notes'] as String).isNotEmpty) ...[
                                  const SizedBox(height: 2),
                                  Text(v['notes'] as String, style: const TextStyle(fontSize: 11, color: AppColors.slate600)),
                                ],
                                const SizedBox(height: 4),
                                Row(
                                  children: [
                                    const Icon(Icons.access_time_rounded, size: 12, color: AppColors.slate400),
                                    const SizedBox(width: 4),
                                    Text(v['visited_at'] ?? '—', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w500, color: AppColors.slate500)),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    );
                  },
                ),
            ],
          ),
        ),
      ),
    );
  }
}

// ─── Camera QR Scanner Page ──────────────────────────────────────────────────
class _QrScannerPage extends StatefulWidget {
  final ValueChanged<String> onQrScanned;
  const _QrScannerPage({required onQrScanned}) : _onQrScanned = onQrScanned;

  final ValueChanged<String> _onQrScanned;

  @override
  State<_QrScannerPage> createState() => _QrScannerPageState();
}

class _QrScannerPageState extends State<_QrScannerPage> {
  final MobileScannerController _controller = MobileScannerController();
  bool _handled = false;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        title: const Text('Scan QR Kunjungan', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.flash_on_rounded),
            onPressed: () => _controller.toggleTorch(),
          ),
          IconButton(
            icon: const Icon(Icons.cameraswitch_rounded),
            onPressed: () => _controller.switchCamera(),
          ),
        ],
      ),
      body: Stack(
        alignment: Alignment.center,
        children: [
          MobileScanner(
            controller: _controller,
            onDetect: (capture) {
              if (_handled) return;
              final barcodes = capture.barcodes;
              for (final barcode in barcodes) {
                final raw = barcode.rawValue;
                if (raw != null && raw.isNotEmpty) {
                  _handled = true;
                  widget._onQrScanned(raw);
                  break;
                }
              }
            },
          ),

          // Overlay frame
          Container(
            width: 250,
            height: 250,
            decoration: BoxDecoration(
              border: Border.all(color: const Color(0xFF38BDF8), width: 3),
              borderRadius: BorderRadius.circular(20),
            ),
          ),

          const Positioned(
            bottom: 40,
            child: Container(
              padding: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              decoration: BoxDecoration(
                color: Colors.black87,
                borderRadius: BorderRadius.all(Radius.circular(20)),
              ),
              child: Text(
                'Posisikan Kode QR Kunjungan di dalam kotak',
                style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Modal Form Pengisian Kunjungan ─────────────────────────────────────────
class _VisitFormModal extends StatefulWidget {
  final String initialQrCode;
  final VoidCallback onSuccess;

  const _VisitFormModal({required this.initialQrCode, required this.onSuccess});

  @override
  State<_VisitFormModal> createState() => _VisitFormModalState();
}

class _VisitFormModalState extends State<_VisitFormModal> {
  final _formKey = GlobalKey<FormState>();
  late TextEditingController _qrCodeController;
  late TextEditingController _notesController;

  String _selectedPurpose = 'Membaca Buku Paket / Literasi';
  final String _customPurpose = '';
  DateTime _visitedAt = DateTime.now();
  bool _submitting = false;

  final List<String> _purposeOptions = [
    'Membaca Buku Paket / Literasi',
    'Mengerjakan Tugas / Kliping',
    'Kerja Kelompok',
    'Mencari Referensi / Jurnal',
    'Lainnya',
  ];

  @override
  void initState() {
    super.initState();
    _qrCodeController = TextEditingController(text: widget.initialQrCode);
    _notesController  = TextEditingController();
  }

  @override
  void dispose() {
    _qrCodeController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    final code = _qrCodeController.text.trim();
    if (!code.contains('SIMS_PERPUS_VISIT') && !code.contains('SIMS_LIBRARY_VISIT')) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Kode QR tidak valid! Pastikan memindai Kode QR Resmi Perpustakaan.'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    setState(() => _submitting = true);

    try {
      final res = await ApiClient.post('/siswa/library/visits', {
        'qr_code': code,
        'visited_at': _visitedAt.toIso8601String(),
        'purpose_option': _selectedPurpose,
        'purpose_custom': _customPurpose,
        'notes': _notesController.text.trim(),
      });

      if (mounted) {
        Navigator.pop(context); // close modal
        widget.onSuccess();
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Kunjungan perpustakaan berhasil dicatat!'),
            backgroundColor: Colors.emerald,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() => _submitting = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(e.toString().replaceAll('Exception: ', '')),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(context).viewInsets.bottom + 20),
      child: Form(
        key: _formKey,
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Form Kunjungan Perpustakaan', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.slate900)),
                  IconButton(icon: const Icon(Icons.close_rounded), onPressed: () => Navigator.pop(context)),
                ],
              ),
              const Divider(),
              const SizedBox(height: 8),

              // QR Code Input
              TextFormField(
                controller: _qrCodeController,
                decoration: InputDecoration(
                  labelText: 'Kode QR Kunjungan *',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  prefixIcon: const Icon(Icons.qr_code_rounded),
                ),
                validator: (v) => (v == null || v.isEmpty) ? 'Kode QR wajib diisi' : null,
              ),
              const SizedBox(height: 12),

              // Waktu Kunjungan Picker
              ListTile(
                contentPadding: const EdgeInsets.symmetric(horizontal: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12), side: BorderSide(color: AppColors.slate300)),
                leading: const Icon(Icons.calendar_today_rounded, color: AppColors.indigo700),
                title: const Text('Waktu Kunjungan', style: TextStyle(fontSize: 12, color: AppColors.slate600)),
                subtitle: Text(DateFormat('dd MMMM yyyy, HH:mm').format(_visitedAt), style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.slate900)),
                trailing: const Icon(Icons.edit_calendar_rounded, size: 18),
                onTap: () async {
                  final pickedDate = await showDatePicker(
                    context: context,
                    initialDate: _visitedAt,
                    firstDate: DateTime(2025),
                    lastDate: DateTime.now().add(const Duration(days: 1)),
                  );
                  if (pickedDate != null && mounted) {
                    final pickedTime = await showTimePicker(
                      context: context,
                      initialTime: TimeOfDay.fromDateTime(_visitedAt),
                    );
                    if (pickedTime != null && mounted) {
                      setState(() {
                        _visitedAt = DateTime(
                          pickedDate.year,
                          pickedDate.month,
                          pickedDate.day,
                          pickedTime.hour,
                          pickedTime.minute,
                        );
                      });
                    }
                  }
                },
              ),
              const SizedBox(height: 12),

              // Keperluan Dropdown
              DropdownButtonFormField<String>(
                value: _selectedPurpose,
                decoration: InputDecoration(
                  labelText: 'Keperluan Membaca *',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  prefixIcon: const Icon(Icons.bookmark_outline_rounded),
                ),
                items: _purposeOptions
                    .map((opt) => DropdownMenuItem(value: opt, child: Text(opt, style: const TextStyle(fontSize: 12))))
                    .toList(),
                onChanged: (val) {
                  if (val != null) setState(() => _selectedPurpose = val);
                },
              ),
              const SizedBox(height: 12),

              // Catatan
              TextFormField(
                controller: _notesController,
                maxLines: 2,
                decoration: InputDecoration(
                  labelText: 'Catatan / Judul Buku (Opsional)',
                  hintText: 'Contoh: Membaca Buku Fisika XII Bab 3',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  prefixIcon: const Icon(Icons.notes_rounded),
                ),
              ),
              const SizedBox(height: 18),

              SizedBox(
                height: 48,
                child: ElevatedButton.icon(
                  onPressed: _submitting ? null : _submit,
                  icon: _submitting
                      ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                      : const Icon(Icons.check_circle_rounded),
                  label: Text(_submitting ? 'Menyimpan...' : 'Simpan Kunjungan', style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF2563EB),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
