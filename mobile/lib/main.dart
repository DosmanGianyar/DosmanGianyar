import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'package:provider/provider.dart';
import 'config/app_config.dart';
import 'providers/auth_provider.dart';
import 'providers/attendance_provider.dart';
import 'providers/notification_provider.dart';
import 'providers/extracurricular_provider.dart';
import 'providers/regulation_provider.dart';
import 'providers/orangtua_provider.dart';
import 'screens/login_screen.dart';
import 'screens/home_screen.dart';
import 'screens/change_password_required_screen.dart';
import 'screens/guru/guru_shell.dart';
import 'screens/orangtua/orangtua_shell.dart';
import 'theme/app_colors.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Paksa orientasi portrait (selfie harus berdiri)
  await SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
  ]);

  // Inisialisasi locale Indonesia untuk intl
  await initializeDateFormatting('id_ID', null);

  runApp(const SimsApp());
}

class SimsApp extends StatelessWidget {
  const SimsApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => AttendanceProvider()),
        ChangeNotifierProvider(create: (_) => NotificationProvider()),
        ChangeNotifierProvider(create: (_) => ExtracurricularProvider()),
        ChangeNotifierProvider(create: (_) => RegulationProvider()),
        ChangeNotifierProvider(create: (_) => OrangtuaProvider()),
      ],
      child: MaterialApp(
        title:        AppConfig.appName,
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
          colorScheme: ColorScheme.fromSeed(
            seedColor:   AppColors.blue600,
            primary:     AppColors.blue600,
            secondary:   AppColors.indigo700,
            surface:     AppColors.white,
            background:  AppColors.slate100,
            error:       AppColors.red500,
            onPrimary:   Colors.white,
            onSurface:   AppColors.gray800,
          ),
          scaffoldBackgroundColor: AppColors.slate100,
          useMaterial3:            true,
          fontFamily:              'Roboto',
          appBarTheme: const AppBarTheme(
            backgroundColor: AppColors.white,
            foregroundColor: AppColors.gray800,
            elevation:       0,
            centerTitle:     true,
            titleTextStyle:  TextStyle(
              fontSize:   16,
              fontWeight: FontWeight.w600,
              color:      AppColors.gray800,
            ),
          ),
          inputDecorationTheme: InputDecorationTheme(
            filled:    true,
            fillColor: AppColors.gray50,
            border:    OutlineInputBorder(
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
          filledButtonTheme: FilledButtonThemeData(
            style: FilledButton.styleFrom(
              backgroundColor: AppColors.blue600,
              shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
            ),
          ),
          cardTheme: const CardThemeData(
            color:     AppColors.white,
            elevation: 0,
            shape:     RoundedRectangleBorder(
              borderRadius: AppRadius.card,
              side:         BorderSide(color: AppColors.gray100),
            ),
          ),
        ),
        home: const _AppGate(),
      ),
    );
  }
}

/// Menentukan halaman awal berdasarkan status autentikasi.
class _AppGate extends StatefulWidget {
  const _AppGate();

  @override
  State<_AppGate> createState() => _AppGateState();
}

class _AppGateState extends State<_AppGate> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AuthProvider>().checkAuth();
    });
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    final user = auth.user;
    if (auth.state == AuthState.authenticated &&
        user != null &&
        user.mustChangePassword &&
        (user.role == 'siswa' || user.role == 'pengelola')) {
      return const ChangePasswordRequiredScreen();
    }

    return switch (auth.state) {
      AuthState.unknown         => const Scaffold(
          body: Center(child: CircularProgressIndicator()),
        ),
      AuthState.authenticated   => switch (auth.user?.role) {
          'guru'     => const GuruShell(),
          'orangtua' => const OrangtuaShell(),
          _          => const HomeScreen(),
        },
      AuthState.unauthenticated => const LoginScreen(),
    };
  }
}
