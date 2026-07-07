import 'package:flutter/material.dart';
import '../services/api_client.dart';
import '../theme/app_colors.dart';

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _formKey         = GlobalKey<FormState>();
  final _identifierCtrl  = TextEditingController();
  bool  _isLoading       = false;

  @override
  void dispose() {
    _identifierCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    FocusScope.of(context).unfocus();
    setState(() => _isLoading = true);

    try {
      final body = await ApiClient.post('/auth/forgot-password', data: {
        'identifier': _identifierCtrl.text.trim(),
      });
      if (!mounted) return;
      _showSnack(body['message'] as String? ?? 'Permintaan berhasil dikirim.', success: true);
      Navigator.of(context).pop();
    } catch (e) {
      if (mounted) _showSnack(ApiClient.extractError(e));
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _showSnack(String msg, {bool success = false}) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: success ? AppColors.green500 : AppColors.red500,
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
    ));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        backgroundColor: AppColors.slate100,
        elevation: 0,
        title: const Text('Lupa Password', style: TextStyle(color: AppColors.gray700, fontSize: 16, fontWeight: FontWeight.bold)),
        iconTheme: const IconThemeData(color: AppColors.gray700),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const SizedBox(height: 12),
                const Text(
                  'Masukkan NISN (siswa) atau NIP (guru) Anda. '
                  'Permintaan reset password akan dikirim ke admin untuk diproses.',
                  style: TextStyle(fontSize: 13, color: AppColors.gray500, height: 1.5),
                ),
                const SizedBox(height: 20),
                TextFormField(
                  controller: _identifierCtrl,
                  keyboardType: TextInputType.text,
                  textInputAction: TextInputAction.done,
                  onFieldSubmitted: (_) => _submit(),
                  validator: (v) => (v == null || v.trim().isEmpty) ? 'Wajib diisi' : null,
                  decoration: InputDecoration(
                    hintText: 'NISN atau NIP',
                    prefixIcon: const Icon(Icons.badge_outlined, size: 18, color: AppColors.gray400),
                    filled: true,
                    fillColor: Colors.white,
                    border: OutlineInputBorder(borderRadius: AppRadius.button, borderSide: BorderSide(color: AppColors.gray100)),
                    enabledBorder: OutlineInputBorder(borderRadius: AppRadius.button, borderSide: BorderSide(color: AppColors.gray100)),
                  ),
                ),
                const SizedBox(height: 20),
                SizedBox(
                  height: 46,
                  child: ElevatedButton(
                    onPressed: _isLoading ? null : _submit,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.blue600,
                      shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
                    ),
                    child: _isLoading
                        ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : const Text('Kirim Permintaan', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
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
