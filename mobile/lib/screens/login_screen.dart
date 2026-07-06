import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../theme/app_colors.dart';
import 'home_screen.dart';
import 'guru/guru_shell.dart';

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
      final role = context.read<AuthProvider>().user?.role;
      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(
          builder: (_) => role == 'guru' ? const GuruShell() : const HomeScreen(),
        ),
        (_) => false,
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

// ─── Panel Atas: Biru gradient (logo + motto) ────────────────────────────────
class _BluePanelTop extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(24, 40, 24, 36),
      decoration: const BoxDecoration(gradient: AppColors.loginGradient),
      child: Stack(
        alignment: Alignment.topCenter,
        children: [
          // Dekorasi lingkaran blur background
          Positioned(top: -30, left: -30,  child: _BlurCircle(size: 140, opacity: 0.08)),
          Positioned(top:  20, right: -20, child: _BlurCircle(size:  80, opacity: 0.06)),
          Positioned(bottom: -20, left: 40, child: _BlurCircle(size: 100, opacity: 0.07)),
          Positioned(bottom: -10, right: -10, child: _BlurCircle(size: 70, opacity: 0.09)),

          // Konten utama
          Column(
            children: [
              // ── Separator dekoratif ──────────────────────────────────
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Container(width: 40, height: 0.8, color: Colors.white.withValues(alpha: 0.25)),
                  const SizedBox(width: 10),
                  Transform.rotate(
                    angle: 0.785, // 45°
                    child: Container(
                      width: 6, height: 6,
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.60),
                        borderRadius: BorderRadius.circular(1),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Container(width: 40, height: 0.8, color: Colors.white.withValues(alpha: 0.25)),
                ],
              ),
              const SizedBox(height: 16),

              // ── Motto utama ──────────────────────────────────────────
              const Text(
                'Widya Wahana Bhakti',
                style: TextStyle(
                  color:         Colors.white,
                  fontSize:      22,
                  fontWeight:    FontWeight.w800,
                  letterSpacing: 0.8,
                  height:        1.2,
                  shadows: [
                    Shadow(
                      color:  Colors.black26,
                      offset: Offset(0, 2),
                      blurRadius: 8,
                    ),
                  ],
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 14),

              // ── Tagline dalam pill ───────────────────────────────────
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 8),
                decoration: BoxDecoration(
                  color:        Colors.white.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(24),
                  border:       Border.all(color: Colors.white.withValues(alpha: 0.22), width: 1),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.auto_awesome_rounded, size: 12, color: Colors.white.withValues(alpha: 0.70)),
                    const SizedBox(width: 7),
                    const Text(
                      'Learn, Innovate and Build The Future',
                      style: TextStyle(
                        color:      Colors.white,
                        fontSize:   11,
                        fontStyle:  FontStyle.italic,
                        fontWeight: FontWeight.w500,
                        letterSpacing: 0.2,
                      ),
                    ),
                    const SizedBox(width: 7),
                    Icon(Icons.auto_awesome_rounded, size: 12, color: Colors.white.withValues(alpha: 0.70)),
                  ],
                ),
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

// ─── DOSMAN Brand Lockup ─────────────────────────────────────────────────────
class _DosmanBrand extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Logo bulat besar dengan lingkaran glow tipis
        Container(
          width: 72, height: 72,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: AppColors.blue50,
            border: Border.all(color: AppColors.blue200, width: 2),
            boxShadow: [
              BoxShadow(
                color: AppColors.blue600.withValues(alpha: 0.12),
                blurRadius: 16,
                spreadRadius: 2,
              ),
            ],
          ),
          padding: const EdgeInsets.all(10),
          child: Image.asset('assets/images/logo_sekolah.png', fit: BoxFit.contain),
        ),
        const SizedBox(height: 14),

        // Wordmark DOSMAN dengan gradient
        ShaderMask(
          shaderCallback: (bounds) => const LinearGradient(
            begin: Alignment.topLeft,
            end:   Alignment.bottomRight,
            colors: [AppColors.blue600, AppColors.indigo700],
          ).createShader(bounds),
          child: const Text(
            'DOSMAN',
            style: TextStyle(
              color:         Colors.white,
              fontSize:      30,
              fontWeight:    FontWeight.w900,
              letterSpacing: 6,
              height:        1,
            ),
          ),
        ),
        const SizedBox(height: 6),

        // Aksen bawah — garis pendek + titik tengah
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(width: 28, height: 1.5, color: AppColors.blue200),
            const SizedBox(width: 6),
            Container(
              width: 5, height: 5,
              decoration: BoxDecoration(
                color:  AppColors.blue600,
                shape:  BoxShape.circle,
              ),
            ),
            const SizedBox(width: 6),
            Container(width: 28, height: 1.5, color: AppColors.blue200),
          ],
        ),
        const SizedBox(height: 6),

        // Aksara Bali
        const Text(
          '᭞ᬲ᭄ᬫᬦ᭄᭑ᬕᬶᬬᬜᬄ᭞',
          style: TextStyle(
            fontFamily:    'NotoSansBalinese',
            fontSize:      13,
            color:         AppColors.gray500,
          ),
        ),
        const SizedBox(height: 2),

        // Nama sekolah
        const Text(
          'SMA Negeri 1 Gianyar',
          style: TextStyle(
            fontSize:      11,
            fontWeight:    FontWeight.w500,
            color:         AppColors.gray500,
            letterSpacing: 0.3,
          ),
        ),
      ],
    );
  }
}

// ─── Panel Bawah: Putih (logo DOSMAN + form login) ───────────────────────────
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
      padding: const EdgeInsets.fromLTRB(28, 28, 28, 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // ── Logo + Wordmark DOSMAN ──────────────────────────────────
          _DosmanBrand(),
          const SizedBox(height: 24),

          // ── Separator ───────────────────────────────────────────────
          Row(
            children: [
              Expanded(child: Container(height: 1, color: AppColors.gray100)),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 12),
                child: Text(
                  'Masuk ke akun Anda',
                  style: TextStyle(fontSize: 11, color: AppColors.gray400, fontWeight: FontWeight.w500),
                ),
              ),
              Expanded(child: Container(height: 1, color: AppColors.gray100)),
            ],
          ),
          const SizedBox(height: 20),

          // ── Form ─────────────────────────────────────────────────────
          Form(
            key: formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                _SIMSInput(
                  controller:      loginCtrl,
                  hint:            'Email / NIS / NIP',
                  prefixIcon:      Icons.person_outline_rounded,
                  keyboardType:    TextInputType.text,
                  textInputAction: TextInputAction.next,
                  validator: (v) => (v == null || v.trim().isEmpty) ? 'Wajib diisi' : null,
                ),
                const SizedBox(height: 12),
                _SIMSInput(
                  controller:       passwordCtrl,
                  hint:             'Password',
                  prefixIcon:       Icons.lock_outline_rounded,
                  obscureText:      obscure,
                  textInputAction:  TextInputAction.done,
                  onFieldSubmitted: (_) => onSubmit(),
                  validator: (v) => (v == null || v.isEmpty) ? 'Wajib diisi' : null,
                  suffixIcon: IconButton(
                    icon: Icon(
                      obscure ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                      size: 18, color: AppColors.gray400,
                    ),
                    onPressed: onToggleObscure,
                  ),
                ),
                const SizedBox(height: 20),
                _GradientButton(
                  label:     isLoading ? 'Memverifikasi...' : 'Masuk',
                  isLoading: isLoading,
                  onPressed: isLoading ? null : onSubmit,
                ),
              ],
            ),
          ),

          const SizedBox(height: 18),
          const Text(
            '© 2025 SMA Negeri 1 Gianyar',
            style: TextStyle(fontSize: 9, color: AppColors.gray300),
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
