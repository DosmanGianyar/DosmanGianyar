import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/user.dart';
import '../providers/auth_provider.dart';
import '../services/api_client.dart';
import '../theme/app_colors.dart';
import 'login_screen.dart';

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

  bool _savingProfile  = false;
  bool _savingPassword = false;
  bool _obscureCur     = true;
  bool _obscureNew     = true;
  bool _obscureConf    = true;

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
          style: TextStyle(fontWeight: FontWeight.w600, fontSize: 16)),
        backgroundColor: const Color(0xFF0F2460),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            _IdentityCard(user: user),
            const SizedBox(height: 12),
            _StudentIdCard(user: user),
            const SizedBox(height: 12),
            if (user?.parentName != null || user?.parentPhone != null)
              _ParentCard(user: user!),
            if (user?.parentName != null || user?.parentPhone != null)
              const SizedBox(height: 12),
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
  final User? user;
  const _IdentityCard({this.user});

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
                Container(
                  width: 72, height: 72,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: Colors.white, width: 3),
                    boxShadow: AppShadow.sm,
                  ),
                  clipBehavior: Clip.antiAlias,
                  child: user?.photoUrl != null
                      ? Image.network(user!.photoUrl!, fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => _avatarPlaceholder())
                      : _avatarPlaceholder(),
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
                Text(user?.className ?? '—',
                  style: const TextStyle(fontSize: 12, color: AppColors.gray500)),
              ],
            ),
          ),

          // Grid info (NIS, Kelas, Tgl Lahir, No HP)
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
            child: Column(
              children: [
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
            label:      'Password Baru',
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
            child: FilledButton(
              onPressed: isSaving ? null : onSave,
              style: FilledButton.styleFrom(
                backgroundColor: AppColors.gray700,
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
              ),
              child: isSaving
                  ? const SizedBox(width: 18, height: 18,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : const Text('Perbarui Password',
                      style: TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
            ),
          ),
        ],
      ),
    );
  }
}

// ─── E-Kartu Pelajar ─────────────────────────────────────────────────────────

class _StudentIdCard extends StatefulWidget {
  final User? user;
  const _StudentIdCard({this.user});
  @override
  State<_StudentIdCard> createState() => _StudentIdCardState();
}

class _StudentIdCardState extends State<_StudentIdCard> {
  bool _showFront = true;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Row(
          children: [
            const Text('E-Kartu Pelajar',
              style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700)),
            const Spacer(),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(
                color: AppColors.blue100,
                borderRadius: BorderRadius.circular(20),
              ),
              child: const Text('Ketuk untuk membalik',
                style: TextStyle(fontSize: 9, color: AppColors.blue600)),
            ),
          ],
        ),
        const SizedBox(height: 8),
        GestureDetector(
          onTap: () => setState(() => _showFront = !_showFront),
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
    return Container(
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: AppRadius.card,
        border: Border.all(color: AppColors.gray200),
        boxShadow: AppShadow.sm,
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Header navy
          Container(
            decoration: const BoxDecoration(gradient: AppColors.topbarGradient),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            child: Row(
              children: [
                Image.asset('assets/images/logo_sekolah.png',
                  width: 38, height: 38,
                  errorBuilder: (_, __, ___) => const SizedBox(width: 38)),
                const SizedBox(width: 8),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('SMA NEGERI 1 GIANYAR',
                        style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold, letterSpacing: 0.3)),
                      Text('Jl. Ngurah Rai No.1, Gianyar, Bali',
                        style: TextStyle(color: Color(0xFFBFDBFE), fontSize: 7.5, height: 1.5)),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.2),
                    borderRadius: BorderRadius.circular(4),
                    border: Border.all(color: Colors.white30),
                  ),
                  child: const Text('KARTU PELAJAR',
                    style: TextStyle(color: Colors.white, fontSize: 7.5, fontWeight: FontWeight.bold, letterSpacing: 0.5)),
                ),
              ],
            ),
          ),

          // Body: photo + data
          Padding(
            padding: const EdgeInsets.all(12),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Photo 3:4
                Container(
                  width: 72, height: 96,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(color: AppColors.red500, width: 2),
                  ),
                  clipBehavior: Clip.antiAlias,
                  child: user?.photoUrl != null
                      ? Image.network(user!.photoUrl!, fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => _photoPlaceholder())
                      : _photoPlaceholder(),
                ),
                const SizedBox(width: 12),
                // Data rows
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _KidRow('NISN',         user?.nisn ?? '—'),
                      _KidRow('Nama',         user?.name ?? '—'),
                      _KidRow('NIS',          user?.nis  ?? '—'),
                      _KidRow('Kelas',        user?.className ?? '—'),
                      _KidRow('Tgl. Lahir',   _fmtDate(user?.birthDate)),
                      _KidRow('Jenis Kelamin',user?.genderLabel ?? '—'),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Signature footer
          Padding(
            padding: const EdgeInsets.fromLTRB(12, 0, 16, 12),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    const Text('Kepala Sekolah,',
                      style: TextStyle(fontSize: 8.5, color: AppColors.gray500)),
                    const SizedBox(height: 30),
                    Container(width: 80, height: 1, color: AppColors.gray300),
                    const SizedBox(height: 2),
                    const Text('NIP. ——————',
                      style: TextStyle(fontSize: 7.5, color: AppColors.gray400)),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _photoPlaceholder() => Container(
    color: AppColors.gray100,
    child: const Column(
      mainAxisAlignment: MainAxisAlignment.end,
      children: [
        Icon(Icons.person, size: 44, color: AppColors.gray300),
        SizedBox(height: 4),
      ],
    ),
  );
}

class _IdBack extends StatelessWidget {
  final User? user;
  const _IdBack({super.key, this.user});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end:   Alignment.bottomRight,
          colors: [Color(0xFF0F2460), Color(0xFF1E3FAD)],
        ),
        borderRadius: AppRadius.card,
        boxShadow: AppShadow.sm,
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        children: [
          const SizedBox(height: 20),
          // QR placeholder
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(Icons.qr_code_2_rounded, size: 110, color: Color(0xFF0F2460)),
          ),
          const SizedBox(height: 8),
          const Text('Scan untuk verifikasi identitas',
            style: TextStyle(color: Colors.white54, fontSize: 9.5)),
          const SizedBox(height: 16),
          // SISWA badge
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 4),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.15),
              borderRadius: BorderRadius.circular(4),
              border: Border.all(color: Colors.white30),
            ),
            child: const Text('SISWA',
              style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold, letterSpacing: 2.5)),
          ),
          const SizedBox(height: 14),
          // Name + NIS
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Column(
              children: [
                Text(user?.name ?? '—',
                  style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                  textAlign: TextAlign.center),
                const SizedBox(height: 4),
                Text('NIS: ${user?.nis ?? '—'}   NISN: ${user?.nisn ?? '—'}',
                  style: const TextStyle(color: Colors.white70, fontSize: 9.5),
                  textAlign: TextAlign.center),
              ],
            ),
          ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }
}

class _KidRow extends StatelessWidget {
  final String label;
  final String value;
  const _KidRow(this.label, this.value);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 1.5),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 70,
            child: Text(label,
              style: const TextStyle(fontSize: 9, color: AppColors.gray500, fontWeight: FontWeight.w500)),
          ),
          const Text(': ',
            style: TextStyle(fontSize: 9, color: AppColors.gray400)),
          Expanded(
            child: Text(value,
              style: const TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: AppColors.gray800),
              overflow: TextOverflow.ellipsis, maxLines: 2),
          ),
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
