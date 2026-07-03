import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../config/app_config.dart';
import '../providers/auth_provider.dart';
import '../theme/app_colors.dart';
import 'home_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey      = GlobalKey<FormState>();
  final _loginCtrl    = TextEditingController();
  final _passwordCtrl = TextEditingController();
  bool  _obscure      = true;

  @override
  void dispose() {
    _loginCtrl.dispose();
    _passwordCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    FocusScope.of(context).unfocus();

    final auth    = context.read<AuthProvider>();
    final success = await auth.login(_loginCtrl.text, _passwordCtrl.text);

    if (!mounted) return;
    if (success) {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const HomeScreen()),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(auth.error ?? 'Login gagal.'),
          backgroundColor: AppColors.red500,
          behavior:        SnackBarBehavior.floating,
          shape:           RoundedRectangleBorder(borderRadius: AppRadius.button),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final isLoading = context.watch<AuthProvider>().isLoading;

    return Scaffold(
      backgroundColor: AppColors.slate100,
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Container(
              constraints: const BoxConstraints(maxWidth: 480),
              decoration: BoxDecoration(
                borderRadius: AppRadius.card,
                boxShadow: [
                  BoxShadow(
                    color:  Colors.black.withOpacity(0.15),
                    blurRadius: 32,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              clipBehavior: Clip.antiAlias,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  _BluePanelTop(),
                  _WhitePanelBottom(
                    formKey:      _formKey,
                    loginCtrl:    _loginCtrl,
                    passwordCtrl: _passwordCtrl,
                    obscure:      _obscure,
                    isLoading:    isLoading,
                    onToggleObscure: () => setState(() => _obscure = !_obscure),
                    onSubmit:     _submit,
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

// ─── Panel Atas: Biru gradient (logo + visi) ─────────────────────────────────
class _BluePanelTop extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 36, horizontal: 24),
      decoration: const BoxDecoration(gradient: AppColors.loginGradient),
      child: Stack(
        children: [
          // Dekorasi lingkaran blur (sesuai web)
          Positioned(
            top: -20, left: -20,
            child: _BlurCircle(size: 120, opacity: 0.10),
          ),
          Positioned(
            bottom: -16, right: -16,
            child: _BlurCircle(size: 96, opacity: 0.12),
          ),
          // Konten
          Column(
            children: [
              // Logo dalam lingkaran
              Container(
                width: 80, height: 80,
                decoration: BoxDecoration(
                  shape:  BoxShape.circle,
                  color:  Colors.white.withOpacity(0.20),
                  border: Border.all(color: Colors.white.withOpacity(0.30), width: 3),
                ),
                padding: const EdgeInsets.all(10),
                child: Image.asset('assets/images/logo_sekolah.png', fit: BoxFit.contain),
              ),
              const SizedBox(height: 14),
              const Text(
                'VISI SMAN 1 GIANYAR',
                style: TextStyle(
                  color:         Colors.white,
                  fontSize:      11,
                  fontWeight:    FontWeight.w800,
                  letterSpacing: 1.5,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 8),
              Container(width: 32, height: 1, color: Colors.white.withOpacity(0.40)),
              const SizedBox(height: 10),
              const Text(
                'Insan Cerdas, Sarat Prestasi,\nBerkarakter, Berbudaya,\nPeduli Lingkungan,\ndan Berwawasan Global',
                style: TextStyle(
                  color:    AppColors.blue200,
                  fontSize: 11,
                  height:   1.5,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 10),
              const Text(
                '"Learn, Inovate, and Build The Future"',
                style: TextStyle(
                  color:      Colors.white70,
                  fontSize:   10,
                  fontStyle:  FontStyle.italic,
                ),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _BlurCircle extends StatelessWidget {
  final double size;
  final double opacity;
  const _BlurCircle({required this.size, required this.opacity});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size, height: size,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: Colors.white.withOpacity(opacity),
      ),
    );
  }
}

// ─── Panel Bawah: Putih (form login) ─────────────────────────────────────────
class _WhitePanelBottom extends StatelessWidget {
  final GlobalKey<FormState> formKey;
  final TextEditingController loginCtrl;
  final TextEditingController passwordCtrl;
  final bool              obscure;
  final bool              isLoading;
  final VoidCallback      onToggleObscure;
  final VoidCallback      onSubmit;

  const _WhitePanelBottom({
    required this.formKey,
    required this.loginCtrl,
    required this.passwordCtrl,
    required this.obscure,
    required this.isLoading,
    required this.onToggleObscure,
    required this.onSubmit,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      color: AppColors.white,
      padding: const EdgeInsets.all(28),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Logo horizontal kecil
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Image.asset('assets/images/logo_sekolah.png', width: 32, height: 32, fit: BoxFit.contain),
              const SizedBox(width: 8),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: const [
                  Text('SMAN 1 GIANYAR',
                    style: TextStyle(
                      fontSize:    10,
                      fontWeight:  FontWeight.w800,
                      letterSpacing: 1,
                      color:       AppColors.gray700,
                    ),
                  ),
                  Text('SMA Negeri 1 Gianyar',
                    style: TextStyle(fontSize: 9, color: AppColors.gray400),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 20),

          const Text('Login SIMS',
            style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppColors.gray800),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 2),
          const Text('Sistem Informasi Manajemen Siswa',
            style: TextStyle(fontSize: 10, color: AppColors.blue600, fontWeight: FontWeight.w600, letterSpacing: 0.3),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 4),
          const Text('Silakan masukkan kredensial Anda',
            style: TextStyle(fontSize: 11, color: AppColors.gray400),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 24),

          Form(
            key: formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Input NIS / Email
                _SIMSInput(
                  controller:  loginCtrl,
                  hint:        'Email / NIS / NIP',
                  prefixIcon:  Icons.person_outline_rounded,
                  keyboardType: TextInputType.text,
                  textInputAction: TextInputAction.next,
                  validator: (v) => (v == null || v.trim().isEmpty) ? 'Wajib diisi' : null,
                ),
                const SizedBox(height: 12),

                // Input Password
                _SIMSInput(
                  controller:     passwordCtrl,
                  hint:           'Password',
                  prefixIcon:     Icons.lock_outline_rounded,
                  obscureText:    obscure,
                  textInputAction: TextInputAction.done,
                  onFieldSubmitted: (_) => onSubmit(),
                  validator: (v) => (v == null || v.isEmpty) ? 'Wajib diisi' : null,
                  suffixIcon: IconButton(
                    icon: Icon(
                      obscure ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                      size:  18,
                      color: AppColors.gray400,
                    ),
                    onPressed: onToggleObscure,
                  ),
                ),
                const SizedBox(height: 20),

                // Tombol Login (gradient biru seperti web)
                _GradientButton(
                  label:     isLoading ? 'Memverifikasi...' : 'Login',
                  isLoading: isLoading,
                  onPressed: isLoading ? null : onSubmit,
                ),
              ],
            ),
          ),

          const SizedBox(height: 20),
          const Text(
            '© 2025 SMA Negeri 1 Gianyar · SIMS',
            style: TextStyle(fontSize: 9, color: AppColors.gray400),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}

// ─── Shared widgets ───────────────────────────────────────────────────────────

class _SIMSInput extends StatelessWidget {
  final TextEditingController controller;
  final String           hint;
  final IconData         prefixIcon;
  final bool             obscureText;
  final TextInputType    keyboardType;
  final TextInputAction  textInputAction;
  final String? Function(String?)? validator;
  final void Function(String)? onFieldSubmitted;
  final Widget?          suffixIcon;

  const _SIMSInput({
    required this.controller,
    required this.hint,
    required this.prefixIcon,
    this.obscureText       = false,
    this.keyboardType      = TextInputType.text,
    this.textInputAction   = TextInputAction.next,
    this.validator,
    this.onFieldSubmitted,
    this.suffixIcon,
  });

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller:      controller,
      obscureText:     obscureText,
      keyboardType:    keyboardType,
      textInputAction: textInputAction,
      onFieldSubmitted: onFieldSubmitted,
      validator:       validator,
      style: const TextStyle(fontSize: 13, color: AppColors.gray700),
      decoration: InputDecoration(
        hintText:          hint,
        hintStyle:         const TextStyle(color: AppColors.gray400, fontSize: 13),
        prefixIcon:        Icon(prefixIcon, size: 18, color: AppColors.gray400),
        suffixIcon:        suffixIcon,
        filled:            true,
        fillColor:         AppColors.gray50,
        contentPadding:    const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
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
        errorBorder: OutlineInputBorder(
          borderRadius: AppRadius.input,
          borderSide:   const BorderSide(color: AppColors.red500),
        ),
      ),
    );
  }
}

class _GradientButton extends StatelessWidget {
  final String       label;
  final bool         isLoading;
  final VoidCallback? onPressed;

  const _GradientButton({required this.label, required this.isLoading, this.onPressed});

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 44,
      decoration: BoxDecoration(
        gradient: onPressed != null ? AppColors.primaryGradient : null,
        color:    onPressed == null ? AppColors.gray200 : null,
        borderRadius: AppRadius.button,
        boxShadow: onPressed != null ? [
          BoxShadow(
            color:  AppColors.blue600.withOpacity(0.35),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ] : null,
      ),
      child: Material(
        color:       Colors.transparent,
        borderRadius: AppRadius.button,
        child: InkWell(
          onTap:       onPressed,
          borderRadius: AppRadius.button,
          child: Center(
            child: isLoading
                ? const SizedBox(
                    width: 18, height: 18,
                    child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                  )
                : Text(
                    label,
                    style: const TextStyle(
                      color:       Colors.white,
                      fontWeight:  FontWeight.w600,
                      fontSize:    14,
                    ),
                  ),
          ),
        ),
      ),
    );
  }
}
