import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../services/api_client.dart';
import '../theme/app_colors.dart';
import 'home_screen.dart';
import 'guru/guru_shell.dart';
import 'orangtua/orangtua_shell.dart';
import 'login_screen.dart';

/// Layar wajib — muncul saat akun baru (password masih default) belum pernah
/// mengganti password sendiri. Tidak bisa dilewati (back ditahan, tidak ada
/// navigasi lain selain ganti password atau logout).
class ChangePasswordRequiredScreen extends StatefulWidget {
  const ChangePasswordRequiredScreen({super.key});

  @override
  State<ChangePasswordRequiredScreen> createState() => _ChangePasswordRequiredScreenState();
}

class _ChangePasswordRequiredScreenState extends State<ChangePasswordRequiredScreen> {
  final _formKey     = GlobalKey<FormState>();
  final _curPassCtrl = TextEditingController();
  final _newPassCtrl = TextEditingController();
  final _confPassCtrl = TextEditingController();

  bool _obscureCur = true;
  bool _obscureNew = true;
  bool _obscureConf = true;
  bool _saving      = false;

  @override
  void dispose() {
    _curPassCtrl.dispose();
    _newPassCtrl.dispose();
    _confPassCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_newPassCtrl.text != _confPassCtrl.text) {
      _showSnack('Konfirmasi password tidak cocok.');
      return;
    }

    setState(() => _saving = true);
    try {
      await ApiClient.put('/auth/change-password', data: {
        'current_password':      _curPassCtrl.text,
        'password':              _newPassCtrl.text,
        'password_confirmation': _confPassCtrl.text,
      });

      if (!mounted) return;
      final auth = context.read<AuthProvider>();
      await auth.checkAuth(); // refresh user dari server -> must_change_password jadi false

      if (!mounted) return;
      final role = auth.user?.role;
      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(
          builder: (_) => switch (role) {
            'guru'     => const GuruShell(),
            'orangtua' => const OrangtuaShell(),
            _          => const HomeScreen(),
          },
        ),
        (_) => false,
      );
    } catch (e) {
      if (mounted) _showSnack(ApiClient.extractError(e));
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  Future<void> _logout() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Konfirmasi Logout'),
        content: const Text('Yakin ingin keluar? Anda perlu login ulang nanti.'),
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

  void _showSnack(String msg) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: AppColors.red500,
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
    ));
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      child: Scaffold(
        backgroundColor: AppColors.slate100,
        appBar: AppBar(
          title: const Text('Ganti Password',
            style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 16)),
          backgroundColor: const Color(0xFF0F2460),
          foregroundColor: Colors.white,
          elevation: 0,
          automaticallyImplyLeading: false,
          actions: [
            TextButton(
              onPressed: _logout,
              child: const Text('Keluar', style: TextStyle(color: Colors.white)),
            ),
          ],
        ),
        body: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Container(
                  decoration: BoxDecoration(
                    color:        AppColors.yellow50,
                    borderRadius: AppRadius.card,
                    border:       Border.all(color: AppColors.amber100),
                  ),
                  padding: const EdgeInsets.all(14),
                  child: const Text(
                    'Demi keamanan akun, Anda wajib mengganti password default sebelum melanjutkan.',
                    style: TextStyle(fontSize: 12.5, color: AppColors.yellow600, height: 1.4),
                  ),
                ),
                const SizedBox(height: 16),
                Container(
                  decoration: BoxDecoration(
                    color:        AppColors.white,
                    borderRadius: AppRadius.card,
                    border:       Border.all(color: AppColors.gray100),
                  ),
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _PasswordField(
                        controller: _curPassCtrl,
                        label:      'Password Saat Ini',
                        obscure:    _obscureCur,
                        onToggle:   () => setState(() => _obscureCur = !_obscureCur),
                        validator:  (v) => (v == null || v.isEmpty) ? 'Wajib diisi' : null,
                      ),
                      const SizedBox(height: 10),
                      _PasswordField(
                        controller: _newPassCtrl,
                        label:      'Password Baru',
                        obscure:    _obscureNew,
                        onToggle:   () => setState(() => _obscureNew = !_obscureNew),
                        validator:  (v) => (v == null || v.length < 8) ? 'Minimal 8 karakter' : null,
                      ),
                      const SizedBox(height: 10),
                      _PasswordField(
                        controller: _confPassCtrl,
                        label:      'Konfirmasi Password Baru',
                        obscure:    _obscureConf,
                        onToggle:   () => setState(() => _obscureConf = !_obscureConf),
                        validator:  (v) => (v == null || v.isEmpty) ? 'Wajib diisi' : null,
                      ),
                      const SizedBox(height: 16),
                      SizedBox(
                        width: double.infinity,
                        child: FilledButton(
                          onPressed: _saving ? null : _submit,
                          style: FilledButton.styleFrom(
                            backgroundColor: AppColors.blue600,
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
                          ),
                          child: _saving
                              ? const SizedBox(width: 18, height: 18,
                                  child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                              : const Text('Perbarui Password',
                                  style: TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _PasswordField extends StatelessWidget {
  final TextEditingController controller;
  final String label;
  final bool obscure;
  final VoidCallback onToggle;
  final String? Function(String?)? validator;

  const _PasswordField({
    required this.controller,
    required this.label,
    required this.obscure,
    required this.onToggle,
    this.validator,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label,
          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w500, color: AppColors.gray600)),
        const SizedBox(height: 4),
        TextFormField(
          controller:  controller,
          obscureText: obscure,
          validator:   validator,
          style: const TextStyle(fontSize: 13, color: AppColors.gray700),
          decoration: InputDecoration(
            suffixIcon: IconButton(
              icon: Icon(obscure ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                size: 18, color: AppColors.gray400),
              onPressed: onToggle,
            ),
            filled:         true,
            fillColor:      AppColors.gray50,
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            border: OutlineInputBorder(
              borderRadius: AppRadius.input,
              borderSide:   const BorderSide(color: AppColors.gray200),
            ),
          ),
        ),
      ],
    );
  }
}
