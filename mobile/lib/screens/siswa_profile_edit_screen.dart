import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/user.dart';
import '../providers/auth_provider.dart';
import '../services/api_client.dart';
import '../theme/app_colors.dart';

class SiswaProfileEditScreen extends StatefulWidget {
  const SiswaProfileEditScreen({super.key});

  @override
  State<SiswaProfileEditScreen> createState() => _SiswaProfileEditScreenState();
}

class _SiswaProfileEditScreenState extends State<SiswaProfileEditScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isLoading = false;

  // 1. Kontak & Domisili
  final _phoneCtrl        = TextEditingController();
  final _hobbiesCtrl      = TextEditingController();
  final _aspirationsCtrl  = TextEditingController();
  final _addressCtrl      = TextEditingController();
  final _rtRwCtrl         = TextEditingController();
  final _kelurahanCtrl    = TextEditingController();
  final _kecamatanCtrl    = TextEditingController();
  final _kabupatenCtrl    = TextEditingController();
  String? _residenceStatus;
  String? _transportation;
  final _distanceCtrl     = TextEditingController();
  final _travelTimeCtrl   = TextEditingController();

  // 2. Ortu & Darurat
  final _fatherNameCtrl   = TextEditingController();
  final _fatherPhoneCtrl  = TextEditingController();
  final _fatherJobCtrl    = TextEditingController();
  final _motherNameCtrl   = TextEditingController();
  final _motherPhoneCtrl  = TextEditingController();
  final _motherJobCtrl    = TextEditingController();
  final _guardianNameCtrl = TextEditingController();
  final _guardianPhoneCtrl= TextEditingController();
  final _guardianJobCtrl  = TextEditingController();
  final _emergNameCtrl    = TextEditingController();
  final _emergPhoneCtrl   = TextEditingController();
  final _emergRelCtrl     = TextEditingController();

  // 3. Kesehatan
  String? _bloodType;
  final _medicalCtrl      = TextEditingController();
  final _heightCtrl       = TextEditingController();
  final _weightCtrl       = TextEditingController();

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _initData();
  }

  void _initData() {
    final user = context.read<AuthProvider>().user;
    if (user == null) return;

    _phoneCtrl.text       = user.phone ?? '';
    _hobbiesCtrl.text     = user.hobbies ?? '';
    _aspirationsCtrl.text = user.aspirations ?? '';
    _addressCtrl.text     = user.address ?? '';
    _rtRwCtrl.text        = user.rtRw ?? '';
    _kelurahanCtrl.text   = user.kelurahan ?? '';
    _kecamatanCtrl.text   = user.kecamatan ?? '';
    _kabupatenCtrl.text   = user.kabupaten ?? '';
    _residenceStatus      = user.residenceStatus;
    _transportation       = user.transportation;
    _distanceCtrl.text    = user.distanceKm?.toString() ?? '';
    _travelTimeCtrl.text  = user.travelTimeMinutes?.toString() ?? '';

    _fatherNameCtrl.text  = user.fatherName ?? '';
    _fatherPhoneCtrl.text = user.fatherPhone ?? '';
    _fatherJobCtrl.text   = user.fatherJob ?? '';
    _motherNameCtrl.text  = user.motherName ?? '';
    _motherPhoneCtrl.text = user.motherPhone ?? '';
    _motherJobCtrl.text   = user.motherJob ?? '';
    _guardianNameCtrl.text= user.guardianName ?? '';
    _guardianPhoneCtrl.text = user.guardianPhone ?? '';
    _guardianJobCtrl.text = user.guardianJob ?? '';
    _emergNameCtrl.text   = user.emergencyContactName ?? '';
    _emergPhoneCtrl.text  = user.emergencyContactPhone ?? '';
    _emergRelCtrl.text    = user.emergencyContactRelation ?? '';

    _bloodType            = user.bloodType;
    _medicalCtrl.text     = user.medicalHistory ?? '';
    _heightCtrl.text      = user.heightCm?.toString() ?? '';
    _weightCtrl.text      = user.weightKg?.toString() ?? '';
  }

  @override
  void dispose() {
    _tabController.dispose();
    _phoneCtrl.dispose(); _hobbiesCtrl.dispose(); _aspirationsCtrl.dispose();
    _addressCtrl.dispose(); _rtRwCtrl.dispose(); _kelurahanCtrl.dispose();
    _kecamatanCtrl.dispose(); _kabupatenCtrl.dispose(); _distanceCtrl.dispose();
    _travelTimeCtrl.dispose(); _fatherNameCtrl.dispose(); _fatherPhoneCtrl.dispose();
    _fatherJobCtrl.dispose(); _motherNameCtrl.dispose(); _motherPhoneCtrl.dispose();
    _motherJobCtrl.dispose(); _guardianNameCtrl.dispose(); _guardianPhoneCtrl.dispose();
    _guardianJobCtrl.dispose(); _emergNameCtrl.dispose(); _emergPhoneCtrl.dispose();
    _emergRelCtrl.dispose(); _medicalCtrl.dispose(); _heightCtrl.dispose();
    _weightCtrl.dispose();
    super.dispose();
  }

  Future<void> _saveProfile() async {
    final authProv = context.read<AuthProvider>();
    final user = authProv.user;
    if (user != null && !user.canEditProfile) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Pengisian & pembaruan data profil sedang dikunci oleh pihak sekolah.'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    setState(() => _isLoading = true);
    try {
      final resp = await ApiClient.put('/auth/profile', data: {
        'phone': _phoneCtrl.text.trim(),
        'hobbies': _hobbiesCtrl.text.trim(),
        'aspirations': _aspirationsCtrl.text.trim(),
        'address': _addressCtrl.text.trim(),
        'rt_rw': _rtRwCtrl.text.trim(),
        'kelurahan': _kelurahanCtrl.text.trim(),
        'kecamatan': _kecamatanCtrl.text.trim(),
        'kabupaten': _kabupatenCtrl.text.trim(),
        'residence_status': _residenceStatus,
        'transportation': _transportation,
        'distance_km': double.tryParse(_distanceCtrl.text.trim()),
        'travel_time_minutes': int.tryParse(_travelTimeCtrl.text.trim()),
        'father_name': _fatherNameCtrl.text.trim(),
        'father_phone': _fatherPhoneCtrl.text.trim(),
        'father_job': _fatherJobCtrl.text.trim(),
        'mother_name': _motherNameCtrl.text.trim(),
        'mother_phone': _motherPhoneCtrl.text.trim(),
        'mother_job': _motherJobCtrl.text.trim(),
        'guardian_name': _guardianNameCtrl.text.trim(),
        'guardian_phone': _guardianPhoneCtrl.text.trim(),
        'guardian_job': _guardianJobCtrl.text.trim(),
        'emergency_contact_name': _emergNameCtrl.text.trim(),
        'emergency_contact_phone': _emergPhoneCtrl.text.trim(),
        'emergency_contact_relation': _emergRelCtrl.text.trim(),
        'blood_type': _bloodType,
        'medical_history': _medicalCtrl.text.trim(),
        'height_cm': int.tryParse(_heightCtrl.text.trim()),
        'weight_kg': int.tryParse(_weightCtrl.text.trim()),
      });

      if (mounted) {
        if (resp['user'] != null) {
          authProv.updateUser(User.fromJson(resp['user'] as Map<String, dynamic>));
        }
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Data profil berhasil disimpan!'),
            backgroundColor: AppColors.emerald600,
          ),
        );
        Navigator.pop(context);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Gagal menyimpan: ${e.toString()}'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    final canEdit = user?.canEditProfile ?? true;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Kelengkapan Profil Siswa'),
        bottom: TabBar(
          controller: _tabController,
          labelColor: AppColors.blue600,
          unselectedLabelColor: AppColors.gray500,
          indicatorColor: AppColors.blue600,
          tabs: const [
            Tab(icon: Icon(Icons.home_outlined), text: 'Domisili'),
            Tab(icon: Icon(Icons.family_restroom_outlined), text: 'Orang Tua'),
            Tab(icon: Icon(Icons.health_and_safety_outlined), text: 'Kesehatan'),
          ],
        ),
      ),
      body: Column(
        children: [
          if (!canEdit)
            Container(
              width: double.infinity,
              color: Colors.amber.shade100,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              child: const Row(
                children: [
                  Icon(Icons.lock_rounded, size: 18, color: Colors.brown),
                  SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      '🔒 Form profil sedang dikunci oleh pihak sekolah (Read-Only). Hubungi Wali Kelas jika ada data yang ingin diperbarui.',
                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.brown),
                    ),
                  ),
                ],
              ),
            ),
          Expanded(
            child: TabBarView(
              controller: _tabController,
              children: [
                _buildDomisiliTab(canEdit),
                _buildOrtuTab(canEdit),
                _buildKesehatanTab(canEdit),
              ],
            ),
          ),
          if (canEdit)
            Padding(
              padding: const EdgeInsets.all(16),
              child: SizedBox(
                width: double.infinity,
                height: 46,
                child: ElevatedButton.icon(
                  onPressed: _isLoading ? null : _saveProfile,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.blue600,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  icon: _isLoading
                      ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                      : const Icon(Icons.save_rounded, size: 18),
                  label: Text(_isLoading ? 'Menyimpan...' : 'Simpan Data Profil', style: const TextStyle(fontWeight: FontWeight.bold)),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildDomisiliTab(bool enabled) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        _field('No. WhatsApp Siswa', _phoneCtrl, enabled, keyboard: TextInputType.phone, hint: 'Misal: 081234567890'),
        _field('Hobi / Minat', _hobbiesCtrl, enabled, hint: 'Misal: Membaca, Badminton, Coding'),
        _field('Cita-Cita', _aspirationsCtrl, enabled, hint: 'Misal: Dokter, Komputer Scientist'),
        const Divider(height: 24),
        _field('Alamat Jalan', _addressCtrl, enabled, maxLines: 2, hint: 'Jl. Ratna No. 10'),
        Row(children: [
          Expanded(child: _field('RT / RW', _rtRwCtrl, enabled, hint: '002/001')),
          const SizedBox(width: 12),
          Expanded(child: _field('Kelurahan / Desa', _kelurahanCtrl, enabled, hint: 'Gianyar')),
        ]),
        Row(children: [
          Expanded(child: _field('Kecamatan', _kecamatanCtrl, enabled, hint: 'Gianyar')),
          const SizedBox(width: 12),
          Expanded(child: _field('Kabupaten / Kota', _kabupatenCtrl, enabled, hint: 'Gianyar')),
        ]),
        const SizedBox(height: 12),
        DropdownButtonFormField<String>(
          value: _residenceStatus,
          decoration: const InputDecoration(labelText: 'Status Tempat Tinggal', border: OutlineInputBorder()),
          items: const [
            DropdownMenuItem(value: 'bersama_orangtua', child: Text('Tinggal Bersama Orang Tua')),
            DropdownMenuItem(value: 'wali', child: Text('Tinggal Bersama Wali')),
            DropdownMenuItem(value: 'kost', child: Text('Kost / Kontrak')),
            DropdownMenuItem(value: 'asrama', child: Text('Asrama')),
          ],
          onChanged: enabled ? (v) => setState(() => _residenceStatus = v) : null,
        ),
        const SizedBox(height: 16),
        DropdownButtonFormField<String>(
          value: _transportation,
          decoration: const InputDecoration(labelText: 'Moda Transportasi Ke Sekolah', border: OutlineInputBorder()),
          items: const [
            DropdownMenuItem(value: 'sepeda_motor', child: Text('Sepeda Motor')),
            DropdownMenuItem(value: 'diantar', child: Text('Diantar Orang Tua / Wali')),
            DropdownMenuItem(value: 'sepeda', child: Text('Sepeda')),
            DropdownMenuItem(value: 'jalan_kaki', child: Text('Jalan Kaki')),
            DropdownMenuItem(value: 'umum', child: Text('Angkutan Umum')),
          ],
          onChanged: enabled ? (v) => setState(() => _transportation = v) : null,
        ),
        const SizedBox(height: 16),
        Row(children: [
          Expanded(child: _field('Jarak ke Sekolah (km)', _distanceCtrl, enabled, keyboard: TextInputType.number, hint: '5.2')),
          const SizedBox(width: 12),
          Expanded(child: _field('Waktu Tempuh (menit)', _travelTimeCtrl, enabled, keyboard: TextInputType.number, hint: '15')),
        ]),
      ],
    );
  }

  Widget _buildOrtuTab(bool enabled) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        const Text('Data Ayah Kandung', style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.blue600)),
        const SizedBox(height: 8),
        _field('Nama Ayah', _fatherNameCtrl, enabled),
        _field('No. WA Ayah', _fatherPhoneCtrl, enabled, keyboard: TextInputType.phone),
        _field('Pekerjaan Ayah', _fatherJobCtrl, enabled),
        const Divider(height: 24),
        const Text('Data Ibu Kandung', style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.blue600)),
        const SizedBox(height: 8),
        _field('Nama Ibu', _motherNameCtrl, enabled),
        _field('No. WA Ibu', _motherPhoneCtrl, enabled, keyboard: TextInputType.phone),
        _field('Pekerjaan Ibu', _motherJobCtrl, enabled),
        const Divider(height: 24),
        const Text('Data Wali (Opsional jika tidak tinggal bersama ortu)', style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.gray700)),
        const SizedBox(height: 8),
        _field('Nama Wali', _guardianNameCtrl, enabled),
        _field('No. WA Wali', _guardianPhoneCtrl, enabled, keyboard: TextInputType.phone),
        _field('Pekerjaan Wali', _guardianJobCtrl, enabled),
        const Divider(height: 24),
        const Text('Kontak Darurat (Emergency Contact)', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.red)),
        const SizedBox(height: 8),
        _field('Nama Kontak Darurat', _emergNameCtrl, enabled, hint: 'Misal: Paman Budi'),
        _field('No. WA Darurat', _emergPhoneCtrl, enabled, keyboard: TextInputType.phone),
        _field('Hubungan', _emergRelCtrl, enabled, hint: 'Misal: Paman / Kakek / Kakak'),
      ],
    );
  }

  Widget _buildKesehatanTab(bool enabled) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        DropdownButtonFormField<String>(
          value: _bloodType,
          decoration: const InputDecoration(labelText: 'Golongan Darah', border: OutlineInputBorder()),
          items: const [
            DropdownMenuItem(value: 'A', child: Text('Golongan Darah A')),
            DropdownMenuItem(value: 'B', child: Text('Golongan Darah B')),
            DropdownMenuItem(value: 'AB', child: Text('Golongan Darah AB')),
            DropdownMenuItem(value: 'O', child: Text('Golongan Darah O')),
          ],
          onChanged: enabled ? (v) => setState(() => _bloodType = v) : null,
        ),
        const SizedBox(height: 16),
        _field('Riwayat Penyakit Khusus / Alergi', _medicalCtrl, enabled, maxLines: 3, hint: 'Misal: Alergi udang, Asma saat cuaca dingin'),
        Row(children: [
          Expanded(child: _field('Tinggi Badan (cm)', _heightCtrl, enabled, keyboard: TextInputType.number, hint: '165')),
          const SizedBox(width: 12),
          Expanded(child: _field('Berat Badan (kg)', _weightCtrl, enabled, keyboard: TextInputType.number, hint: '55')),
        ]),
      ],
    );
  }

  Widget _field(String label, TextEditingController ctrl, bool enabled, {TextInputType? keyboard, int maxLines = 1, String? hint}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: TextFormField(
        controller: ctrl,
        enabled: enabled,
        keyboardType: keyboard,
        maxLines: maxLines,
        decoration: InputDecoration(
          labelText: label,
          hintText: hint,
          border: const OutlineInputBorder(),
        ),
      ),
    );
  }
}
