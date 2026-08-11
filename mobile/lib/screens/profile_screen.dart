import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import 'package:qr_flutter/qr_flutter.dart';
import '../config/app_config.dart';
import '../models/user.dart';
import '../providers/auth_provider.dart';
import '../services/api_client.dart';
import '../theme/app_colors.dart';
import '../widgets/image_viewer_dialog.dart';
import 'login_screen.dart';
import 'siswa_profile_edit_screen.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final _phoneCtrl   = TextEditingController();
  final _addressCtrl = TextEditingController();
  final _curPassCtrl = TextEditingController();
  final _newPassCtrl = TextEditingController();
  final _confPassCtrl = TextEditingController();

  bool _savingProfile   = false;
  bool _savingPassword  = false;
  bool _uploadingPhoto  = false;
  bool _obscureCur      = true;
  bool _obscureNew      = true;
  bool _obscureConf     = true;

  @override
  void initState() {
    super.initState();
    final user = context.read<AuthProvider>().user;
    _phoneCtrl.text   = user?.phone   ?? '';
    _addressCtrl.text = user?.address ?? '';
  }

  @override
  void dispose() {
    _phoneCtrl.dispose();
    _addressCtrl.dispose();
    _curPassCtrl.dispose();
    _newPassCtrl.dispose();
    _confPassCtrl.dispose();
    super.dispose();
  }

  Future<void> _saveProfile() async {
    setState(() => _savingProfile = true);
    try {
      final body = await ApiClient.put('/auth/profile', data: {
        'phone':   _phoneCtrl.text.trim(),
        'address': _addressCtrl.text.trim(),
      });
      if (!mounted) return;
      final updatedUser = User.fromJson(body['user'] as Map<String, dynamic>);
      context.read<AuthProvider>().updateUser(updatedUser);
      _showSnack('Profil berhasil diperbarui.', success: true);
    } catch (e) {
      _showSnack(ApiClient.extractError(e));
    } finally {
      if (mounted) setState(() => _savingProfile = false);
    }
  }

  Future<void> _changePassword() async {
    if (_newPassCtrl.text != _confPassCtrl.text) {
      _showSnack('Konfirmasi password tidak cocok.');
      return;
    }
    setState(() => _savingPassword = true);
    try {
      await ApiClient.put('/auth/change-password', data: {
        'current_password':      _curPassCtrl.text,
        'password':              _newPassCtrl.text,
        'password_confirmation': _confPassCtrl.text,
      });
      if (!mounted) return;
      _curPassCtrl.clear();
      _newPassCtrl.clear();
      _confPassCtrl.clear();
      _showSnack('Password berhasil diperbarui.', success: true);
    } catch (e) {
      _showSnack(ApiClient.extractError(e));
    } finally {
      if (mounted) setState(() => _savingPassword = false);
    }
  }

  Future<void> _logout() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Konfirmasi Logout'),
        content: const Text('Yakin ingin keluar dari aplikasi?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            style: FilledButton.styleFrom(backgroundColor: AppColors.red500),
            child: const Text('Logout'),
          ),
        ],
      ),
    );
    if (confirm == true && mounted) {
      await context.read<AuthProvider>().logout();
      if (mounted) {
        Navigator.of(context).pushAndRemoveUntil(
          MaterialPageRoute(builder: (_) => const LoginScreen()),
          (_) => false,
        );
      }
    }
  }

  Future<void> _changePhoto() async {
    // image_picker menggunakan Android Photo Picker bawaan sistem (Android 13+)
    // atau document picker (≤12) — keduanya tidak memerlukan runtime permission.
    final picked = await ImagePicker().pickImage(
      source:       ImageSource.gallery,
      imageQuality: 85,
      maxWidth:     1024,
    );
    if (picked == null) return;

    setState(() => _uploadingPhoto = true);
    try {
      final formData = FormData.fromMap({
        'photo': await MultipartFile.fromFile(picked.path, filename: 'photo.jpg'),
      });
      final body = await ApiClient.postForm('/auth/profile/photo', formData);
      if (!mounted) return;
      final updatedUser = User.fromJson(body['user'] as Map<String, dynamic>);
      context.read<AuthProvider>().updateUser(updatedUser);
      _showSnack('Foto profil berhasil diperbarui.', success: true);
    } catch (e) {
      if (mounted) _showSnack(ApiClient.extractError(e));
    } finally {
      if (mounted) setState(() => _uploadingPhoto = false);
    }
  }

  void _showSnack(String msg, {bool success = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: success ? AppColors.green500 : AppColors.red500,
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
    ));
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;

    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Profil',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 16)),
        backgroundColor: const Color(0xFF0F2460),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: EdgeInsets.fromLTRB(16, 16, 16, 100 + MediaQuery.of(context).padding.bottom),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            _IdentityCard(
              user:          user,
              uploadingPhoto: _uploadingPhoto,
              onChangePhoto: _changePhoto,
            ),
            const SizedBox(height: 12),
            if (user != null && user.isSiswa) ...[
              _ExtendedProfileSummaryCard(user: user),
              const SizedBox(height: 12),
            ],
            if (user?.parentName != null || user?.parentPhone != null) ...[
              _ParentCard(user: user!),
              const SizedBox(height: 12),
            ],
            _EditDataCard(
              phoneCtrl:   _phoneCtrl,
              addressCtrl: _addressCtrl,
              isSaving:    _savingProfile,
              onSave:      _saveProfile,
            ),
            const SizedBox(height: 12),
            _ChangePasswordCard(
              curCtrl:      _curPassCtrl,
              newCtrl:      _newPassCtrl,
              confCtrl:     _confPassCtrl,
              obscureCur:   _obscureCur,
              obscureNew:   _obscureNew,
              obscureConf:  _obscureConf,
              isSaving:     _savingPassword,
              onToggleCur:  () => setState(() => _obscureCur  = !_obscureCur),
              onToggleNew:  () => setState(() => _obscureNew  = !_obscureNew),
              onToggleConf: () => setState(() => _obscureConf = !_obscureConf),
              onSave:       _changePassword,
            ),
            const SizedBox(height: 12),
            OutlinedButton(
              onPressed: _logout,
              style: OutlinedButton.styleFrom(
                foregroundColor: AppColors.red500,
                side: const BorderSide(color: AppColors.red500, width: 1.5),
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: AppRadius.card),
              ),
              child: const Text('Keluar dari Akun',
                style: TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Identity Card ────────────────────────────────────────────────────────────

class _IdentityCard extends StatelessWidget {
  final User?        user;
  final bool         uploadingPhoto;
  final VoidCallback onChangePhoto;
  const _IdentityCard({
    this.user,
    required this.uploadingPhoto,
    required this.onChangePhoto,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color:        AppColors.white,
        borderRadius: AppRadius.card,
        border:       Border.all(color: AppColors.gray100),
        boxShadow:    AppShadow.sm,
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        children: [
          // Banner biru
          Container(
            height: 64,
            decoration: const BoxDecoration(
              gradient: AppColors.primaryGradient,
            ),
          ),

          // Avatar overlapping banner
          Transform.translate(
            offset: const Offset(0, -32),
            child: Column(
              children: [
                Stack(
                  clipBehavior: Clip.none,
                  children: [
                    Container(
                      width: 72, height: 72,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: Colors.white, width: 3),
                        boxShadow: AppShadow.sm,
                      ),
                      clipBehavior: Clip.antiAlias,
                      child: uploadingPhoto
                          ? Container(
                              color: AppColors.gray100,
                              child: const Center(
                                child: SizedBox(width: 28, height: 28,
                                  child: CircularProgressIndicator(strokeWidth: 2.5))))
                          : user?.photoUrl != null
                              ? GestureDetector(
                                  onTap: () => ImageViewerDialog.show(
                                    context,
                                    imageUrl: user!.photoUrl!,
                                    title: 'Foto Profil ${user?.name ?? ""}',
                                  ),
                                  child: Image.network(user!.photoUrl!, fit: BoxFit.cover,
                                      errorBuilder: (_, __, ___) => _avatarPlaceholder()),
                                )
                              : _avatarPlaceholder(),
                    ),
                    // Tombol kamera
                    if (!uploadingPhoto)
                      Positioned(
                        bottom: -4, right: -4,
                        child: GestureDetector(
                          onTap: onChangePhoto,
                          child: Container(
                            width: 26, height: 26,
                            decoration: BoxDecoration(
                              color:  AppColors.blue600,
                              shape:  BoxShape.circle,
                              border: Border.all(color: Colors.white, width: 2),
                              boxShadow: AppShadow.sm,
                            ),
                            child: const Icon(Icons.camera_alt_rounded,
                              size: 13, color: Colors.white),
                          ),
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(user?.name ?? '—',
                  style: const TextStyle(
                    fontSize:   15,
                    fontWeight: FontWeight.bold,
                    color:      AppColors.gray800,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 2),
                Text(
                  switch (user?.role) {
                    'guru'     => user?.subject ?? user?.roleLabel ?? 'Guru',
                    'orangtua' => user?.roleLabel ?? 'Orangtua',
                    _          => user?.className ?? '—',
                  },
                  style: const TextStyle(fontSize: 12, color: AppColors.gray500),
                ),
              ],
            ),
          ),

          // Grid info — role-aware
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
            child: Column(
              children: [
                if (user?.role == 'guru') ...[
                  Row(
                    children: [
                      Expanded(child: _InfoBox(label: 'NIP', value: user?.nip ?? '—')),
                      const SizedBox(width: 8),
                      Expanded(child: _InfoBox(label: 'Mata Pelajaran', value: user?.subject ?? '—')),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Expanded(child: _InfoBox(label: 'Wali Kelas', value: user?.homeroomClassName ?? '—')),
                      const SizedBox(width: 8),
                      Expanded(child: _InfoBox(label: 'No. HP', value: user?.phone ?? '—')),
                    ],
                  ),
                ] else if (user?.role == 'orangtua') ...[
                  Row(
                    children: [
                      Expanded(child: _InfoBox(label: 'No. HP', value: user?.phone ?? '—')),
                      const SizedBox(width: 8),
                      Expanded(child: _InfoBox(label: 'Jumlah Anak', value: '${user?.children.length ?? 0}')),
                    ],
                  ),
                ] else ...[
                  Row(
                    children: [
                      Expanded(child: _InfoBox(label: 'NIS',   value: user?.nis   ?? '—')),
                      const SizedBox(width: 8),
                      Expanded(child: _InfoBox(label: 'Kelas', value: user?.className ?? '—')),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Expanded(child: _InfoBox(label: 'Tanggal Lahir', value: _formatDate(user?.birthDate))),
                      const SizedBox(width: 8),
                      Expanded(child: _InfoBox(label: 'No. HP', value: user?.phone ?? '—')),
                    ],
                  ),
                  if (user?.isSiswa == true) ...[
                    const SizedBox(height: 12),
                    SizedBox(
                      width: double.infinity,
                      child: OutlinedButton.icon(
                        onPressed: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(builder: (_) => const SiswaProfileEditScreen()),
                          );
                        },
                        icon: Icon(
                          user?.canEditProfile == true ? Icons.edit_note_rounded : Icons.lock_outline_rounded,
                          size: 18,
                        ),
                        label: Text(
                          user?.canEditProfile == true
                              ? 'Lengkapi / Edit Data Profil'
                              : 'Lihat Detail Profil (Read-Only)',
                          style: const TextStyle(fontWeight: FontWeight.bold),
                        ),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppColors.blue600,
                          side: const BorderSide(color: AppColors.blue600),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                      ),
                    ),
                  ],
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _avatarPlaceholder() {
    final initials = user?.initials ?? '?';
    return Container(
      color: AppColors.blue600,
      alignment: Alignment.center,
      child: Text(initials,
        style: const TextStyle(
          color: Colors.white, fontSize: 26, fontWeight: FontWeight.bold)),
    );
  }

  String _formatDate(String? dateStr) {
    if (dateStr == null) return '—';
    try {
      final d = DateTime.parse(dateStr);
      const months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                          'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      return '${d.day} ${months[d.month]} ${d.year}';
    } catch (_) {
      return dateStr;
    }
  }
}

class _InfoBox extends StatelessWidget {
  final String label;
  final String value;
  const _InfoBox({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color:        AppColors.gray50,
        borderRadius: BorderRadius.circular(10),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label.toUpperCase(),
            style: const TextStyle(
              fontSize: 9, color: AppColors.gray400,
              fontWeight: FontWeight.w600, letterSpacing: 0.5)),
          const SizedBox(height: 3),
          Text(value,
            style: const TextStyle(
              fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.gray800)),
        ],
      ),
    );
  }
}

// ─── Parent Card ──────────────────────────────────────────────────────────────

class _ParentCard extends StatelessWidget {
  final User user;
  const _ParentCard({required this.user});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color:        AppColors.white,
        borderRadius: AppRadius.card,
        border:       Border.all(color: AppColors.gray100),
        boxShadow:    AppShadow.sm,
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Informasi Orang Tua / Wali',
            style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700)),
          const SizedBox(height: 12),
          _ParentRow(
            icon:  Icons.person_rounded,
            color: AppColors.blue600,
            label: 'Nama Orang Tua',
            value: user.parentName ?? '—',
          ),
          const Divider(height: 20, color: AppColors.gray100),
          _ParentRow(
            icon:  Icons.phone_rounded,
            color: AppColors.green500,
            label: 'No. HP Orang Tua',
            value: user.parentPhone ?? '—',
          ),
        ],
      ),
    );
  }
}

class _ParentRow extends StatelessWidget {
  final IconData icon;
  final Color    color;
  final String   label;
  final String   value;
  const _ParentRow({required this.icon, required this.color,
    required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
          width: 32, height: 32,
          decoration: BoxDecoration(
            color:        color.withOpacity(0.10),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, size: 16, color: color),
        ),
        const SizedBox(width: 12),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(label, style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
            Text(value, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: AppColors.gray800)),
          ],
        ),
      ],
    );
  }
}

// ─── Edit Data Card ───────────────────────────────────────────────────────────

class _EditDataCard extends StatelessWidget {
  final TextEditingController phoneCtrl;
  final TextEditingController addressCtrl;
  final bool         isSaving;
  final VoidCallback onSave;

  const _EditDataCard({
    required this.phoneCtrl,
    required this.addressCtrl,
    required this.isSaving,
    required this.onSave,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color:        AppColors.white,
        borderRadius: AppRadius.card,
        border:       Border.all(color: AppColors.gray100),
        boxShadow:    AppShadow.sm,
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Edit Data Diri',
            style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700)),
          const SizedBox(height: 12),
          _ProfileInput(
            controller: phoneCtrl,
            label:      'No. HP Siswa',
            hint:       '08xxxxxxxxxx',
            keyboard:   TextInputType.phone,
          ),
          const SizedBox(height: 10),
          _ProfileInput(
            controller: addressCtrl,
            label:      'Alamat',
            hint:       'Jl. ...',
          ),
          const SizedBox(height: 14),
          SizedBox(
            width: double.infinity,
            child: FilledButton(
              onPressed: isSaving ? null : onSave,
              style: FilledButton.styleFrom(
                backgroundColor: AppColors.blue600,
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
              ),
              child: isSaving
                  ? const SizedBox(width: 18, height: 18,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : const Text('Simpan',
                      style: TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Change Password Card ─────────────────────────────────────────────────────

class _ChangePasswordCard extends StatelessWidget {
  final TextEditingController curCtrl;
  final TextEditingController newCtrl;
  final TextEditingController confCtrl;
  final bool obscureCur;
  final bool obscureNew;
  final bool obscureConf;
  final bool isSaving;
  final VoidCallback onToggleCur;
  final VoidCallback onToggleNew;
  final VoidCallback onToggleConf;
  final VoidCallback onSave;

  const _ChangePasswordCard({
    required this.curCtrl,
    required this.newCtrl,
    required this.confCtrl,
    required this.obscureCur,
    required this.obscureNew,
    required this.obscureConf,
    required this.isSaving,
    required this.onToggleCur,
    required this.onToggleNew,
    required this.onToggleConf,
    required this.onSave,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color:        AppColors.white,
        borderRadius: AppRadius.card,
        border:       Border.all(color: AppColors.gray100),
        boxShadow:    AppShadow.sm,
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Ganti Password',
            style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700)),
          const SizedBox(height: 10),
          // Alert Box Syarat Password (Sama persis dengan Web)
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFFFFFBEB),
              border: Border.all(color: const Color(0xFFFCD34D)),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: const [
                Row(
                  children: [
                    Icon(Icons.info_outline_rounded, color: Color(0xFFD97706), size: 16),
                    SizedBox(width: 6),
                    Text(
                      'Syarat Ketentuan Password Baru:',
                      style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: Color(0xFF78350F)),
                    ),
                  ],
                ),
                SizedBox(height: 6),
                Padding(
                  padding: EdgeInsets.only(left: 22),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('• Panjang password minimal 8 karakter.',
                        style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w600, color: Color(0xFF92400E))),
                      SizedBox(height: 2),
                      Text('• Password Baru & Konfirmasi harus sama persis.',
                        style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w600, color: Color(0xFF92400E))),
                      SizedBox(height: 2),
                      Text('• Gunakan kombinasi angka atau huruf yang mudah Anda ingat.',
                        style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w600, color: Color(0xFF92400E))),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
          _ProfileInput(
            controller: curCtrl,
            label:      'Password Saat Ini',
            obscure:    obscureCur,
            suffix:     IconButton(
              icon: Icon(obscureCur ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                size: 18, color: AppColors.gray400),
              onPressed: onToggleCur,
            ),
          ),
          const SizedBox(height: 10),
          _ProfileInput(
            controller: newCtrl,
            label:      'Password Baru (Min. 8 Karakter)',
            obscure:    obscureNew,
            suffix:     IconButton(
              icon: Icon(obscureNew ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                size: 18, color: AppColors.gray400),
              onPressed: onToggleNew,
            ),
          ),
          const SizedBox(height: 10),
          _ProfileInput(
            controller: confCtrl,
            label:      'Konfirmasi Password Baru',
            obscure:    obscureConf,
            suffix:     IconButton(
              icon: Icon(obscureConf ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                size: 18, color: AppColors.gray400),
              onPressed: onToggleConf,
            ),
          ),
          const SizedBox(height: 14),
          SizedBox(
            width: double.infinity,
            child: Container(
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF2563EB), Color(0xFF4338CA)],
                ),
                borderRadius: AppRadius.button,
                boxShadow: const [
                  BoxShadow(color: Color(0x332563EB), blurRadius: 8, offset: Offset(0, 3)),
                ],
              ),
              child: ElevatedButton.icon(
                onPressed: isSaving ? null : onSave,
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.transparent,
                  shadowColor: Colors.transparent,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
                ),
                icon: isSaving
                    ? const SizedBox(width: 18, height: 18,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Icon(Icons.lock_reset_rounded, size: 18, color: Colors.white),
                label: const Text('Simpan Password Baru',
                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.white)),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ─── E-Kartu Pelajar ─────────────────────────────────────────────────────────

class StudentIdCard extends StatefulWidget {
  final User? user;
  const StudentIdCard({super.key, this.user});
  @override
  State<StudentIdCard> createState() => _StudentIdCardState();
}

class _StudentIdCardState extends State<StudentIdCard> {
  bool _showFront = true;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Row(
          children: [
            const Icon(Icons.badge_outlined, size: 16, color: AppColors.blue600),
            const SizedBox(width: 6),
            const Text('KARTU PELAJAR DIGITAL',
              style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: AppColors.gray800, letterSpacing: 0.3)),
            const Spacer(),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(
                color: AppColors.blue100,
                borderRadius: BorderRadius.circular(20),
              ),
              child: const Text('Resmi & Read-Only',
                style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: AppColors.blue600)),
            ),
          ],
        ),
        const SizedBox(height: 8),
        LayoutBuilder(
          builder: (context, constraints) {
            final cardWidth  = constraints.maxWidth;
            final cardHeight = cardWidth / 1.48;

            return SizedBox(
              height: cardHeight,
              child: GestureDetector(
                onTap: () => setState(() => _showFront = !_showFront),
                child: FittedBox(
                  fit: BoxFit.contain,
                  alignment: Alignment.center,
                  child: SizedBox(
                    width: 350,
                    height: 236,
                    child: AnimatedSwitcher(
                      duration: const Duration(milliseconds: 350),
                      switchInCurve:  Curves.easeOut,
                      switchOutCurve: Curves.easeIn,
                      transitionBuilder: (child, anim) => FadeTransition(
                        opacity: anim,
                        child: ScaleTransition(scale: Tween(begin: 0.93, end: 1.0).animate(anim), child: child),
                      ),
                      child: _showFront
                          ? _IdFront(key: const ValueKey('f'), user: widget.user)
                          : _IdBack (key: const ValueKey('b'), user: widget.user),
                    ),
                  ),
                ),
              ),
            );
          },
        ),
        const SizedBox(height: 4),
        const Center(
          child: Text('Ketuk kartu untuk melihat QR Code  →',
            style: TextStyle(fontSize: 10, color: AppColors.gray400, fontWeight: FontWeight.w500)),
        ),
      ],
    );
  }
}

class _IdFront extends StatelessWidget {
  final User? user;
  const _IdFront({super.key, this.user});

  String _fmtDate(String? s) {
    if (s == null) return '—';
    try {
      final d = DateTime.parse(s);
      const m = ['','Januari','Februari','Maret','April','Mei','Juni',
                    'Juli','Agustus','September','Oktober','November','Desember'];
      return '${d.day} ${m[d.month]} ${d.year}';
    } catch (_) { return s; }
  }

  @override
  Widget build(BuildContext context) {
    final baseWeb = AppConfig.baseUrl.replaceAll('/api/v1', '');
    final verifyUrl = '$baseWeb/verifikasi/kartu-pelajar/${user?.nis ?? user?.id ?? ''}';

    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFFF8F7F4),
        borderRadius: AppRadius.card,
        boxShadow: AppShadow.sm,
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // ── Header biru ──────────────────────────────────────────
          Container(
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [Color(0xFF0A3880), Color(0xFF1565C0), Color(0xFF1976D2)],
              ),
            ),
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
            child: Row(children: [
              // Logo
              Container(
                width: 38, height: 38,
                decoration: const BoxDecoration(
                  color: Colors.white,
                  shape: BoxShape.circle,
                  boxShadow: [BoxShadow(color: Color(0x55000000), blurRadius: 8, offset: Offset(0, 2))],
                ),
                child: Padding(
                  padding: const EdgeInsets.all(2),
                  child: Image.asset('assets/images/logo_sekolah.png',
                    fit: BoxFit.contain,
                    errorBuilder: (_, __, ___) => const Icon(Icons.school, color: Color(0xFF0A3880), size: 24)),
                ),
              ),
              const SizedBox(width: 8),
              // Nama sekolah
              Expanded(child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('SMA NEGERI 1 GIANYAR',
                    style: TextStyle(
                      color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.w800,
                      letterSpacing: 0.3, height: 1.1)),
                  SizedBox(height: 2),
                  Text('Jl. Ratna No.1, Gianyar, Bali 80511 · Telp. (0361) 943443',
                    style: TextStyle(color: Color(0xFFBFDBFE), fontSize: 7.5, height: 1.3)),
                ],
              )),
              // Badge Kartu Pelajar
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 4),
                decoration: BoxDecoration(
                  border: Border.all(color: Colors.white38, width: 1),
                  borderRadius: BorderRadius.circular(4),
                  color: Colors.white12,
                ),
                child: const Text('KARTU\nPELAJAR',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    color: Colors.white, fontSize: 7, fontWeight: FontWeight.w800,
                    letterSpacing: 0.8, height: 1.25)),
              ),
            ]),
          ),

          // ── Strip emas ────────────────────────────────────────────
          Container(
            height: 3,
            decoration: const BoxDecoration(
              gradient: LinearGradient(colors: [Color(0xFFB45309), Color(0xFFF59E0B), Color(0xFFFBBF24), Color(0xFFF59E0B), Color(0xFFB45309)]),
            ),
          ),

          // ── Body & Footer (Expanded to anchor bottom blue strip) ──
          Expanded(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(10, 6, 10, 4),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  // Row Foto & Data
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Foto
                      Container(
                        width: 60, height: 78,
                        decoration: BoxDecoration(
                          border: Border.all(color: const Color(0xFF1565C0), width: 2),
                          boxShadow: const [BoxShadow(
                            color: Color(0x331565C0), blurRadius: 8, offset: Offset(0, 3))],
                        ),
                        clipBehavior: Clip.antiAlias,
                        child: user?.photoUrl != null
                            ? Image.network(user!.photoUrl!, fit: BoxFit.cover, alignment: Alignment.topCenter,
                                errorBuilder: (_, __, ___) => _photoPlaceholder())
                            : _photoPlaceholder(),
                      ),
                      const SizedBox(width: 9),
                      // Data
                      Expanded(child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Sub-judul
                          const Center(
                            child: Text('KARTU PELAJAR',
                              style: TextStyle(
                                fontSize: 8.5, fontWeight: FontWeight.w900, color: Color(0xFF0A3880),
                                letterSpacing: 1.2, decoration: TextDecoration.underline,
                                decorationColor: Color(0xFF0A3880))),
                          ),
                          const SizedBox(height: 4),
                          // Baris data
                          _KidRow(icon: Icons.person_rounded,           label: 'Nama',          value: user?.name.toUpperCase() ?? '—', bold: true),
                          _KidRow(icon: Icons.badge_outlined,            label: 'NIS/NISN',      value: '${user?.nis ?? '—'} / ${user?.nisn ?? '—'}'),
                          _KidRow(icon: Icons.school_rounded,            label: 'Kelas',         value: user?.className ?? '—'),
                          _KidRow(icon: Icons.groups_rounded,            label: 'Angkatan',      value: user?.angkatan ?? '—'),
                          _KidRow(icon: Icons.calendar_today_rounded,    label: 'Tgl. Lahir',    value: _fmtDate(user?.birthDate)),
                          _KidRow(icon: Icons.wc_rounded,                label: 'Jenis Kelamin', value: user?.genderLabel ?? '—'),
                        ],
                      )),
                    ],
                  ),

                  // Footer: berlaku + ttd kepsek + QR Verifikasi
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      const Text('Berlaku selama\nmenjadi siswa SMAN 1 Gianyar',
                        style: TextStyle(fontSize: 7, color: AppColors.gray400, fontStyle: FontStyle.italic, height: 1.4)),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.center,
                        children: [
                          const Text('Gianyar, 13 Juli 2026',
                            style: TextStyle(fontSize: 7, color: AppColors.gray600, fontWeight: FontWeight.w500)),
                          const SizedBox(height: 1),
                          const Text('Kepala Sekolah,',
                            style: TextStyle(fontSize: 7.5, color: AppColors.gray600, fontWeight: FontWeight.w500)),
                          const SizedBox(height: 1),
                          // QR Verifikasi Kepsek CENTERED ABOVE NAME
                          Container(
                            width: 22, height: 22,
                            margin: const EdgeInsets.symmetric(vertical: 1),
                            padding: const EdgeInsets.all(1),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              border: Border.all(color: AppColors.gray300, width: 0.5),
                              borderRadius: BorderRadius.circular(3),
                              boxShadow: const [BoxShadow(color: Color(0x11000000), blurRadius: 2, offset: Offset(0, 1))],
                            ),
                            child: QrImageView(
                              data: verifyUrl,
                              version: QrVersions.auto,
                              size: 20,
                              padding: EdgeInsets.zero,
                              eyeStyle: const QrEyeStyle(eyeShape: QrEyeShape.square, color: Color(0xFF0A3880)),
                              dataModuleStyle: const QrDataModuleStyle(dataModuleShape: QrDataModuleShape.square, color: Color(0xFF0A3880)),
                            ),
                          ),
                          const SizedBox(height: 1),
                          const Text('I Wayan Sudra Astra, S.Pd., M.Pd.',
                            style: TextStyle(fontSize: 7.5, color: AppColors.gray800, fontWeight: FontWeight.bold, decoration: TextDecoration.underline)),
                          const SizedBox(height: 1),
                          const Text('NIP. 19710415 199703 1 007',
                            style: TextStyle(fontSize: 6.5, color: AppColors.gray600, fontWeight: FontWeight.w600)),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),

          // ── Strip bawah biru (Footer Biru Kartu) ─────────────────
          Container(
            height: 12,
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.centerLeft,
                end: Alignment.centerRight,
                colors: [Color(0xFF0A3880), Color(0xFF1565C0), Color(0xFF1976D2)],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _photoPlaceholder() => Container(
    color: const Color(0xFFDCE8F8),
    child: Column(
      mainAxisAlignment: MainAxisAlignment.end,
      children: const [
        Icon(Icons.person, size: 36, color: Color(0xFF6FA3D8)),
        SizedBox(height: 2),
      ],
    ),
  );
}

class _IdBack extends StatelessWidget {
  final User? user;
  const _IdBack({super.key, this.user});

  @override
  Widget build(BuildContext context) {
    // URL publik /verifikasi/kartu-pelajar/{nis}
    final baseWeb = AppConfig.baseUrl.replaceAll('/api/v1', '');
    final qrData  = '$baseWeb/verifikasi/kartu-pelajar/${user?.nis ?? user?.id ?? ''}';

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: AppRadius.card,
        boxShadow: AppShadow.sm,
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // ── Strip atas biru ───────────────────────────────────────
          Container(
            height: 28,
            decoration: const BoxDecoration(
              gradient: LinearGradient(colors: [Color(0xFF0A3880), Color(0xFF1565C0), Color(0xFF1976D2)]),
            ),
            padding: const EdgeInsets.symmetric(horizontal: 10),
            child: Row(children: [
              Container(
                width: 18, height: 18,
                decoration: const BoxDecoration(color: Colors.white, shape: BoxShape.circle),
                child: Padding(
                  padding: const EdgeInsets.all(1.5),
                  child: Image.asset('assets/images/logo_sekolah.png',
                    fit: BoxFit.contain,
                    errorBuilder: (_, __, ___) => const Icon(Icons.school, color: Color(0xFF0A3880), size: 12)),
                ),
              ),
              const SizedBox(width: 6),
              const Text('SMA NEGERI 1 GIANYAR',
                style: TextStyle(
                  color: Colors.white, fontSize: 9.5, fontWeight: FontWeight.w700, letterSpacing: 0.4)),
              const Spacer(),
              const Text('NPSN 50102079',
                style: TextStyle(color: Colors.white60, fontSize: 7.5)),
            ]),
          ),

          // ── Body tengah ───────────────────────────────────────────
          Expanded(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                // QR Code
                Container(
                  padding: const EdgeInsets.all(6),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    border: Border.all(color: AppColors.gray200),
                    borderRadius: BorderRadius.circular(8),
                    boxShadow: const [BoxShadow(
                      color: Color(0x1A000000), blurRadius: 6, offset: Offset(0, 2))],
                  ),
                  child: QrImageView(
                    data: qrData,
                    version: QrVersions.auto,
                    size: 72,
                    eyeStyle: const QrEyeStyle(
                      eyeShape: QrEyeShape.square,
                      color: Color(0xFF0A3880),
                    ),
                    dataModuleStyle: const QrDataModuleStyle(
                      dataModuleShape: QrDataModuleShape.square,
                      color: Color(0xFF0A3880),
                    ),
                  ),
                ),
                const SizedBox(height: 5),
                const Text('Scan untuk verifikasi keabsahan kartu pelajar',
                  style: TextStyle(fontSize: 8, color: AppColors.gray500, fontWeight: FontWeight.w500, letterSpacing: 0.2)),

                // Divider
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 40, vertical: 4),
                  child: Container(height: 1, color: AppColors.gray200),
                ),

                // Nama + NIS
                Text(user?.name.toUpperCase() ?? '—',
                  style: const TextStyle(
                    fontSize: 10.5, fontWeight: FontWeight.w900, color: Color(0xFF0A3880)),
                  textAlign: TextAlign.center,
                  maxLines: 1, overflow: TextOverflow.ellipsis),
                const SizedBox(height: 2),
                Text(
                  'NIS: ${user?.nis ?? '—'}  ·  NISN: ${user?.nisn ?? '—'}',
                  style: const TextStyle(fontSize: 8, color: AppColors.gray600, fontWeight: FontWeight.w600),
                  textAlign: TextAlign.center),
                const SizedBox(height: 1),
                Text(
                  '${user?.className ?? ''}${user?.angkatan != null ? '  ·  ' + user!.angkatan! : ''}${(user?.genderLabel.isNotEmpty ?? false) ? '  ·  ' + user!.genderLabel : ''}',
                  style: const TextStyle(fontSize: 7.5, color: AppColors.gray500, fontWeight: FontWeight.w500),
                  textAlign: TextAlign.center),
              ],
            ),
          ),

          // ── Strip bawah emas ──────────────────────────────────────
          Container(
            height: 18,
            decoration: const BoxDecoration(
              gradient: LinearGradient(colors: [Color(0xFFB45309), Color(0xFFF59E0B), Color(0xFFFBBF24), Color(0xFFF59E0B), Color(0xFFB45309)]),
            ),
            child: const Center(
              child: Text('SISWA',
                style: TextStyle(
                  color: Colors.white, fontSize: 8, fontWeight: FontWeight.w700,
                  letterSpacing: 3, shadows: [Shadow(color: Color(0x55000000), blurRadius: 4)])),
            ),
          ),
        ],
      ),
    );
  }
}

class _KidRow extends StatelessWidget {
  final IconData icon;
  final String   label;
  final String   value;
  final bool     bold;
  const _KidRow({required this.icon, required this.label, required this.value, this.bold = false});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 1.5),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 8, color: const Color(0xFF1565C0)),
          const SizedBox(width: 3),
          SizedBox(
            width: 56,
            child: Text(label,
              style: const TextStyle(fontSize: 8, color: AppColors.gray500, fontWeight: FontWeight.w500))),
          const Text(': ',
            style: TextStyle(fontSize: 8, color: AppColors.gray400)),
          Expanded(child: Text(value,
            style: TextStyle(
              fontSize: 8, fontWeight: bold ? FontWeight.w700 : FontWeight.w600,
              color: bold ? AppColors.gray800 : AppColors.gray700),
            overflow: TextOverflow.ellipsis, maxLines: 1)),
        ],
      ),
    );
  }
}

// ─── Profile Input ────────────────────────────────────────────────────────────

class _ProfileInput extends StatelessWidget {
  final TextEditingController controller;
  final String           label;
  final String?          hint;
  final bool             obscure;
  final TextInputType    keyboard;
  final Widget?          suffix;

  const _ProfileInput({
    required this.controller,
    required this.label,
    this.hint,
    this.obscure    = false,
    this.keyboard   = TextInputType.text,
    this.suffix,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label,
          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w500, color: AppColors.gray600)),
        const SizedBox(height: 4),
        TextField(
          controller:  controller,
          obscureText: obscure,
          keyboardType: keyboard,
          style: const TextStyle(fontSize: 13, color: AppColors.gray700),
          decoration: InputDecoration(
            hintText:       hint,
            hintStyle:      const TextStyle(fontSize: 13, color: AppColors.gray400),
            suffixIcon:     suffix,
            filled:         true,
            fillColor:      AppColors.gray50,
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            border: OutlineInputBorder(
              borderRadius: AppRadius.input,
              borderSide:   const BorderSide(color: AppColors.gray200),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: AppRadius.input,
              borderSide:   const BorderSide(color: AppColors.gray200),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: AppRadius.input,
              borderSide:   const BorderSide(color: AppColors.blue600, width: 2),
            ),
          ),
        ),
      ],
    );
  }
}

class _ExtendedProfileSummaryCard extends StatelessWidget {
  final User user;
  const _ExtendedProfileSummaryCard({required this.user});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // 1. Domisili & Transportasi
        _SectionSummaryCard(
          title: 'Domisili & Transportasi',
          icon: Icons.home_outlined,
          color: AppColors.blue600,
          children: [
            Row(children: [
              Expanded(child: _InfoBox(label: 'Alamat Jalan', value: user.address ?? '—')),
              const SizedBox(width: 8),
              Expanded(child: _InfoBox(label: 'RT / RW', value: user.rtRw ?? '—')),
            ]),
            const SizedBox(height: 8),
            Row(children: [
              Expanded(child: _InfoBox(label: 'Kelurahan / Desa', value: user.kelurahan ?? '—')),
              const SizedBox(width: 8),
              Expanded(child: _InfoBox(label: 'Kecamatan', value: user.kecamatan ?? '—')),
            ]),
            const SizedBox(height: 8),
            Row(children: [
              Expanded(child: _InfoBox(label: 'Kabupaten / Kota', value: user.kabupaten ?? '—')),
              const SizedBox(width: 8),
              Expanded(child: _InfoBox(label: 'Status Tinggal', value: _residenceLabel(user.residenceStatus))),
            ]),
            const SizedBox(height: 8),
            Row(children: [
              Expanded(child: _InfoBox(label: 'Transportasi', value: _transLabel(user.transportation))),
              const SizedBox(width: 8),
              Expanded(child: _InfoBox(label: 'Jarak & Tempuh', value: user.distanceKm != null ? '${user.distanceKm} km (${user.travelTimeMinutes ?? '—'} mnt)' : '—')),
            ]),
          ],
        ),
        const SizedBox(height: 12),

        // 2. Orang Tua & Darurat
        _SectionSummaryCard(
          title: 'Orang Tua & Kontak Darurat',
          icon: Icons.family_restroom_outlined,
          color: AppColors.emerald600,
          children: [
            Row(children: [
              Expanded(child: _InfoBox(label: 'Ayah Kandung', value: user.fatherName != null ? '${user.fatherName} (${user.fatherPhone ?? '—'})' : '—')),
              const SizedBox(width: 8),
              Expanded(child: _InfoBox(label: 'Pekerjaan Ayah', value: user.fatherJob ?? '—')),
            ]),
            const SizedBox(height: 8),
            Row(children: [
              Expanded(child: _InfoBox(label: 'Ibu Kandung', value: user.motherName != null ? '${user.motherName} (${user.motherPhone ?? '—'})' : '—')),
              const SizedBox(width: 8),
              Expanded(child: _InfoBox(label: 'Pekerjaan Ibu', value: user.motherJob ?? '—')),
            ]),
            if (user.guardianName != null && user.guardianName!.isNotEmpty) ...[
              const SizedBox(height: 8),
              Row(children: [
                Expanded(child: _InfoBox(label: 'Wali', value: '${user.guardianName} (${user.guardianPhone ?? '—'})')),
                const SizedBox(width: 8),
                Expanded(child: _InfoBox(label: 'Pekerjaan Wali', value: user.guardianJob ?? '—')),
              ]),
            ],
            const SizedBox(height: 8),
            Row(children: [
              Expanded(child: _InfoBox(label: 'Kontak Darurat', value: user.emergencyContactName != null ? '${user.emergencyContactName} (${user.emergencyContactPhone ?? '—'})' : '—')),
              const SizedBox(width: 8),
              Expanded(child: _InfoBox(label: 'Hubungan', value: user.emergencyContactRelation ?? '—')),
            ]),
          ],
        ),
        const SizedBox(height: 12),

        // 3. Kesehatan & Fisik (UKS)
        _SectionSummaryCard(
          title: 'Kesehatan & Fisik (UKS)',
          icon: Icons.health_and_safety_outlined,
          color: Colors.red.shade700,
          children: [
            Row(children: [
              Expanded(child: _InfoBox(label: 'Golongan Darah', value: user.bloodType != null ? 'Gol. Darah ${user.bloodType}' : '—')),
              const SizedBox(width: 8),
              Expanded(child: _InfoBox(label: 'Tinggi & Berat', value: user.heightCm != null ? '${user.heightCm} cm / ${user.weightKg ?? '—'} kg' : '—')),
            ]),
            const SizedBox(height: 8),
            _InfoBox(label: 'Riwayat Penyakit / Alergi', value: user.medicalHistory ?? 'Tidak Ada'),
          ],
        ),
        const SizedBox(height: 12),

        // 4. Minat & Cita-Cita
        _SectionSummaryCard(
          title: 'Minat & Cita-Cita',
          icon: Icons.star_outline_rounded,
          color: Colors.amber.shade800,
          children: [
            Row(children: [
              Expanded(child: _InfoBox(label: 'Hobi / Minat', value: user.hobbies ?? '—')),
              const SizedBox(width: 8),
              Expanded(child: _InfoBox(label: 'Cita-Cita', value: user.aspirations ?? '—')),
            ]),
          ],
        ),
      ],
    );
  }

  static String _residenceLabel(String? val) => switch (val) {
    'bersama_orangtua' => 'Bersama Orang Tua',
    'wali'             => 'Bersama Wali',
    'kost'             => 'Kost / Kontrak',
    'asrama'           => 'Asrama',
    _                  => '—',
  };

  static String _transLabel(String? val) => switch (val) {
    'sepeda_motor' => 'Sepeda Motor',
    'diantar'      => 'Diantar Ortu/Wali',
    'sepeda'       => 'Sepeda',
    'jalan_kaki'   => 'Jalan Kaki',
    'umum'         => 'Angkutan Umum',
    _              => '—',
  };
}

class _SectionSummaryCard extends StatelessWidget {
  final String title;
  final IconData icon;
  final Color color;
  final List<Widget> children;

  const _SectionSummaryCard({
    required this.title,
    required this.icon,
    required this.color,
    required this.children,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: AppShadow.sm,
        border: Border.all(color: AppColors.gray100),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 18, color: color),
              const SizedBox(width: 8),
              Text(title, style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: color)),
            ],
          ),
          const SizedBox(height: 10),
          ...children,
        ],
      ),
    );
  }
}
