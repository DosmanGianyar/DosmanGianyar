import 'dart:io';
import 'package:camera/camera.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image/image.dart' as img;
import 'package:latlong2/latlong.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:provider/provider.dart';
import '../../models/attendance.dart';
import '../../providers/attendance_provider.dart';
import '../../services/attendance_service.dart';
import '../../services/device_service.dart';
import '../../theme/app_colors.dart';

// ─── Phase enum ───────────────────────────────────────────────────────────────

enum _Phase { loading, locationError, locationOk, camera }

// Kamera depan menyimpan file JPEG dalam kondisi mirror (terbalik secara
// horizontal) meski preview yang dilihat user tampak normal. Dibalik ulang
// di isolate terpisah supaya tidak nge-block UI thread.
Uint8List _unmirrorJpeg(Uint8List bytes) {
  final decoded = img.decodeJpg(bytes);
  if (decoded == null) return bytes;
  final flipped = img.flipHorizontal(decoded);
  return Uint8List.fromList(img.encodeJpg(flipped, quality: 92));
}

// ─── Screen ───────────────────────────────────────────────────────────────────

class AttendanceScreen extends StatefulWidget {
  final bool isCheckOut;
  const AttendanceScreen({super.key, required this.isCheckOut});

  @override
  State<AttendanceScreen> createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen>
    with WidgetsBindingObserver {
  // ── Location phase state ────────────────────────────────────────────────
  _Phase          _phase          = _Phase.loading;
  ActiveShift?    _activeShift;
  Position?       _position;
  double?         _distanceMeters;
  String?         _locationErrorTitle;
  String?         _locationErrorBody;
  bool            _isOutsideArea  = false;
  bool            _isPermDeniedForever = false;

  // ── Camera phase state ──────────────────────────────────────────────────
  CameraController? _camera;
  bool              _initializingCamera = false;
  bool              _isCapturing        = false;
  File?             _capturedPhoto;

  // ── Getters ─────────────────────────────────────────────────────────────
  bool get _isCheckOut => widget.isCheckOut;
  Color get _accent    => _isCheckOut ? AppColors.emerald500 : AppColors.blue600;
  String get _label    => _isCheckOut ? 'Absen Pulang' : 'Absen Masuk';

  // ─── Lifecycle ────────────────────────────────────────────────────────────

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _verifyLocation();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _camera?.dispose();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (_phase != _Phase.camera || _camera == null) return;
    if (state == AppLifecycleState.inactive) {
      _camera!.dispose();
      _camera = null;
    } else if (state == AppLifecycleState.resumed) {
      _initCamera();
    }
  }

  // ─── Phase 1: Location Verification ──────────────────────────────────────

  Future<void> _verifyLocation() async {
    setState(() {
      _phase                 = _Phase.loading;
      _locationErrorTitle    = null;
      _locationErrorBody     = null;
      _isOutsideArea         = false;
      _isPermDeniedForever   = false;
      _position              = null;
      _distanceMeters        = null;
    });

    try {
      // Fetch shift data and GPS in parallel
      final (activeShift, position) = await (
        AttendanceService.getActiveShift(),
        DeviceService.getVerifiedPosition(),
      ).wait;

      final distance = AttendanceService.distanceTo(
        activeShift.location.lat,
        activeShift.location.lng,
        position,
      );

      if (!mounted) return;

      setState(() {
        _activeShift    = activeShift;
        _position       = position;
        _distanceMeters = distance;
      });

      if (distance > activeShift.location.radiusMeters) {
        setState(() {
          _isOutsideArea      = true;
          _locationErrorTitle = 'Di Luar Area Absensi';
          _locationErrorBody  =
              'Anda berada ${distance.toStringAsFixed(0)} m dari titik absensi.\n'
              'Radius yang diizinkan: ${activeShift.location.radiusMeters} m.\n\n'
              'Pastikan Anda berada di lingkungan ${activeShift.location.name}.';
          _phase = _Phase.locationError;
        });
      } else {
        setState(() => _phase = _Phase.locationOk);
      }
    } on MockLocationException {
      if (!mounted) return;
      setState(() {
        _locationErrorTitle = 'GPS Palsu Terdeteksi';
        _locationErrorBody  =
            'Aplikasi mendeteksi penggunaan mock location atau VPN.\n\n'
            'Matikan aplikasi pemalsuan lokasi, lalu coba kembali.';
        _phase = _Phase.locationError;
      });
    } on LocationServiceException {
      if (!mounted) return;
      setState(() {
        _locationErrorTitle = 'Layanan GPS Tidak Aktif';
        _locationErrorBody  =
            'Aktifkan layanan lokasi (GPS) di pengaturan perangkat, '
            'lalu coba kembali.';
        _phase = _Phase.locationError;
      });
    } on LocationPermissionDeniedForeverException {
      if (!mounted) return;
      setState(() {
        _locationErrorTitle    = 'Izin Lokasi Diblokir';
        _locationErrorBody     =
            'Izin lokasi diblokir secara permanen.\n\n'
            'Buka Pengaturan → Aplikasi → SIMS_DOSMAN → Izin → '
            'Lokasi → Izinkan saat menggunakan aplikasi.';
        _isPermDeniedForever   = true;
        _phase                 = _Phase.locationError;
      });
    } on LocationPermissionException {
      if (!mounted) return;
      setState(() {
        _locationErrorTitle = 'Izin Lokasi Diperlukan';
        _locationErrorBody  =
            'Aplikasi membutuhkan izin akses lokasi untuk memverifikasi '
            'kehadiran Anda.';
        _phase = _Phase.locationError;
      });
    } catch (e) {
      if (!mounted) return;
      // Might be a network error loading the shift data
      final msg = e.toString();
      setState(() {
        _locationErrorTitle = 'Gagal Memverifikasi Lokasi';
        _locationErrorBody  = msg.length > 120 ? '${msg.substring(0, 120)}…' : msg;
        _phase = _Phase.locationError;
      });
    }
  }

  // ─── Phase 2: Camera ──────────────────────────────────────────────────────

  Future<void> _proceedToCamera() async {
    // Cek camera permission sebelum membuka kamera
    var camStatus = await Permission.camera.status;
    if (camStatus.isDenied) {
      camStatus = await Permission.camera.request();
    }
    if (camStatus.isPermanentlyDenied) {
      if (!mounted) return;
      _showSettingsDialog(
        title:   'Izin Kamera Diblokir',
        message: 'Izin kamera diblokir secara permanen.\n\n'
                 'Buka Pengaturan → Aplikasi → SIMS_DOSMAN → Izin → Kamera → Izinkan.',
      );
      return;
    }
    if (!camStatus.isGranted) return;

    setState(() {
      _phase              = _Phase.camera;
      _initializingCamera = true;
      _capturedPhoto      = null;
    });
    await _initCamera();
  }

  void _showSettingsDialog({required String title, required String message}) {
    showDialog<void>(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: AppRadius.card),
        title: Text(title, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
        content: Text(message, style: const TextStyle(fontSize: 13, color: AppColors.gray500, height: 1.5)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal', style: TextStyle(color: AppColors.gray500)),
          ),
          FilledButton(
            onPressed: () { Navigator.pop(context); openAppSettings(); },
            style: FilledButton.styleFrom(backgroundColor: AppColors.blue600),
            child: const Text('Buka Pengaturan'),
          ),
        ],
      ),
    );
  }

  Future<void> _initCamera() async {
    setState(() => _initializingCamera = true);
    try {
      final cameras = await availableCameras();
      final front   = cameras.firstWhere(
        (c) => c.lensDirection == CameraLensDirection.front,
        orElse: () => cameras.first,
      );
      _camera = CameraController(
        front,
        ResolutionPreset.medium,
        enableAudio: false,
        imageFormatGroup: ImageFormatGroup.jpeg,
      );
      await _camera!.initialize();
    } catch (e) {
      _showSnack('Gagal membuka kamera: $e');
    } finally {
      if (mounted) setState(() => _initializingCamera = false);
    }
  }

  // Ambil foto lalu langsung kirim absen — tidak ada opsi ulang/konfirmasi,
  // cukup 1x pengambilan foto (konsisten dengan alur di web).
  Future<void> _capture() async {
    if (_isCapturing) return;
    if (_camera == null || !_camera!.value.isInitialized) return;
    setState(() => _isCapturing = true);
    try {
      final xFile      = await _camera!.takePicture();
      final rawBytes    = await xFile.readAsBytes();
      final fixedBytes  = await compute(_unmirrorJpeg, rawBytes);
      final fixedFile   = await File(xFile.path).writeAsBytes(fixedBytes);
      setState(() => _capturedPhoto = fixedFile);
      await _submit();
    } catch (e) {
      _showSnack('Gagal mengambil foto: $e');
    } finally {
      if (mounted) setState(() => _isCapturing = false);
    }
  }

  Future<void> _submit() async {
    if (_capturedPhoto == null || _position == null) return;
    final provider = context.read<AttendanceProvider>();
    final success  = _isCheckOut
        ? await provider.checkOut(_capturedPhoto!, _position!)
        : await provider.checkIn(_capturedPhoto!, _position!);

    if (!mounted) return;
    _showResult(
      success
          ? (provider.successMessage ?? 'Presensi berhasil!')
          : (provider.error ?? 'Presensi gagal.'),
      isSuccess: success,
    );
  }

  // ─── UI helpers ───────────────────────────────────────────────────────────

  void _showSnack(String msg) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content:         Text(msg),
        backgroundColor: AppColors.red500,
        behavior:        SnackBarBehavior.floating,
        shape:           RoundedRectangleBorder(borderRadius: AppRadius.button),
      ),
    );
  }

  void _showResult(String message, {required bool isSuccess}) {
    var handled = false;
    void finish() {
      if (handled || !mounted) return;
      handled = true;
      Navigator.of(context).pop(); // tutup dialog
      if (isSuccess) Navigator.of(context).pop(); // kembali ke dashboard
    }

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: AppRadius.card),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 64, height: 64,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: isSuccess ? AppColors.green100 : AppColors.red100,
              ),
              child: Icon(
                isSuccess ? Icons.check_rounded : Icons.close_rounded,
                size:  32,
                color: isSuccess ? AppColors.green500 : AppColors.red500,
              ),
            ),
            const SizedBox(height: 16),
            Text(
              isSuccess ? 'Presensi Berhasil!' : 'Presensi Gagal',
              style: TextStyle(
                fontSize:   17,
                fontWeight: FontWeight.bold,
                color: isSuccess ? AppColors.green900 : AppColors.gray800,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              message,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 13, color: AppColors.gray500),
            ),
          ],
        ),
        actionsAlignment: MainAxisAlignment.center,
        actions: [
          FilledButton(
            onPressed: finish,
            style: FilledButton.styleFrom(
              backgroundColor: isSuccess ? AppColors.blue600 : AppColors.gray500,
              shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
              padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 12),
            ),
            child: const Text('OK'),
          ),
        ],
      ),
    );

    // Absen berhasil — otomatis kembali ke dashboard tanpa perlu tap OK,
    // konsisten dengan alur di web.
    if (isSuccess) {
      Future.delayed(const Duration(milliseconds: 1500), finish);
    }
  }

  // ─── Build ────────────────────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    return switch (_phase) {
      _Phase.loading     => _buildLocationLoading(),
      _Phase.locationError => _buildLocationError(),
      _Phase.locationOk  => _buildLocationOk(),
      _Phase.camera      => _buildCamera(),
    };
  }

  // ── Phase 1a: Loading ──────────────────────────────────────────────────────

  Widget _buildLocationLoading() {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      body: SafeArea(
        child: Column(
          children: [
            _ScreenHeader(title: _label, onBack: () => Navigator.of(context).pop()),
            const Expanded(
              child: Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    _PulsingIcon(
                      icon:  Icons.location_searching_rounded,
                      color: AppColors.blue600,
                    ),
                    SizedBox(height: 24),
                    Text(
                      'Memeriksa Lokasi Anda...',
                      style: TextStyle(
                        fontSize:   16,
                        fontWeight: FontWeight.w600,
                        color:      AppColors.gray700,
                      ),
                    ),
                    SizedBox(height: 6),
                    Text(
                      'Harap tunggu, sedang memverifikasi\nGPS dan area absensi',
                      style: TextStyle(fontSize: 12, color: AppColors.gray400, height: 1.5),
                      textAlign: TextAlign.center,
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── Phase 1b: Location Error ───────────────────────────────────────────────

  Widget _buildLocationError() {
    final isOutside  = _isOutsideArea;
    final iconBg     = isOutside ? AppColors.amber100 : AppColors.red100;
    final iconColor  = isOutside ? AppColors.amber500  : AppColors.red500;
    final iconData   = isOutside
        ? Icons.location_off_rounded
        : Icons.gps_off_rounded;

    return Scaffold(
      backgroundColor: AppColors.slate100,
      body: SafeArea(
        child: Column(
          children: [
            _ScreenHeader(title: _label, onBack: () => Navigator.of(context).pop()),
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(24),
                child: Column(
                  children: [
                    const SizedBox(height: 16),

                    // Icon
                    Container(
                      width: 80, height: 80,
                      decoration: BoxDecoration(shape: BoxShape.circle, color: iconBg),
                      child: Icon(iconData, size: 40, color: iconColor),
                    ),
                    const SizedBox(height: 20),

                    // Error title
                    Text(
                      _locationErrorTitle ?? 'Verifikasi Gagal',
                      style: const TextStyle(
                        fontSize:   18,
                        fontWeight: FontWeight.bold,
                        color:      AppColors.gray800,
                      ),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 12),

                    // Error body
                    Text(
                      _locationErrorBody ?? '',
                      style: const TextStyle(
                        fontSize: 13, color: AppColors.gray500, height: 1.6),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 24),

                    // Peta lokasi (radius + titik GPS)
                    if (_activeShift != null && _position != null) ...[
                      _LocationMap(
                        location: _activeShift!.location,
                        position: _position!,
                      ),
                      const SizedBox(height: 16),
                    ],

                    // Location info card (if we have it)
                    if (_activeShift != null)
                      _LocationInfoCard(
                        location:       _activeShift!.location,
                        distanceMeters: _distanceMeters,
                      ),

                    if (_activeShift != null) const SizedBox(height: 24),

                    // Buka Pengaturan (hanya saat deniedForever)
                    if (_isPermDeniedForever) ...[
                      SizedBox(
                        width: double.infinity,
                        child: FilledButton.icon(
                          onPressed: () => openAppSettings(),
                          icon:  const Icon(Icons.settings_rounded),
                          label: const Text('Buka Pengaturan'),
                          style: FilledButton.styleFrom(
                            backgroundColor: AppColors.blue600,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),
                    ],

                    // Retry button
                    SizedBox(
                      width: double.infinity,
                      child: FilledButton.icon(
                        onPressed: _verifyLocation,
                        icon:  const Icon(Icons.refresh_rounded),
                        label: const Text('Coba Lagi'),
                        style: FilledButton.styleFrom(
                          backgroundColor: _isPermDeniedForever
                              ? AppColors.gray500
                              : AppColors.blue600,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
                        ),
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Back
                    SizedBox(
                      width: double.infinity,
                      child: OutlinedButton(
                        onPressed: () => Navigator.of(context).pop(),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppColors.gray500,
                          side:    const BorderSide(color: AppColors.gray200),
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape:   RoundedRectangleBorder(borderRadius: AppRadius.button),
                        ),
                        child: const Text('Kembali'),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── Phase 1c: Location OK ──────────────────────────────────────────────────

  Widget _buildLocationOk() {
    final location = _activeShift!.location;
    final distance = _distanceMeters!;
    final accuracy = _position!.accuracy;

    return Scaffold(
      backgroundColor: AppColors.slate100,
      body: SafeArea(
        child: Column(
          children: [
            _ScreenHeader(title: _label, onBack: () => Navigator.of(context).pop()),
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(24),
                child: Column(
                  children: [
                    const SizedBox(height: 8),

                    // ✅ Verified icon
                    Container(
                      width: 80, height: 80,
                      decoration: const BoxDecoration(
                        shape: BoxShape.circle,
                        color: AppColors.green100,
                      ),
                      child: const Icon(
                        Icons.verified_rounded,
                        size:  40,
                        color: AppColors.green500,
                      ),
                    ),
                    const SizedBox(height: 16),

                    const Text(
                      'Lokasi Terverifikasi',
                      style: TextStyle(
                        fontSize:   20,
                        fontWeight: FontWeight.bold,
                        color:      AppColors.green900,
                      ),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'Anda berada dalam area absensi',
                      style: TextStyle(fontSize: 13, color: AppColors.gray400),
                    ),
                    const SizedBox(height: 24),

                    // Peta lokasi (radius + titik GPS)
                    _LocationMap(location: location, position: _position!),
                    const SizedBox(height: 16),

                    // Location detail card
                    _LocationInfoCard(
                      location:       location,
                      distanceMeters: distance,
                      accuracy:       accuracy,
                      isVerified:     true,
                    ),
                    const SizedBox(height: 32),

                    // Info text
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color:        AppColors.blue50,
                        borderRadius: AppRadius.card,
                        border:       Border.all(color: AppColors.blue100),
                      ),
                      child: Row(
                        children: const [
                          Icon(Icons.info_outline_rounded,
                            size: 16, color: AppColors.blue600),
                          SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              'Pastikan wajah Anda terlihat jelas saat mengambil foto selfie.',
                              style: TextStyle(fontSize: 12, color: AppColors.blue700),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Lanjut button
                    SizedBox(
                      width: double.infinity,
                      height: 52,
                      child: DecoratedBox(
                        decoration: BoxDecoration(
                          gradient:     AppColors.primaryGradient,
                          borderRadius: AppRadius.button,
                          boxShadow: [
                            BoxShadow(
                              color:      AppColors.blue600.withOpacity(0.35),
                              blurRadius: 12,
                              offset:     const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: Material(
                          color:        Colors.transparent,
                          borderRadius: AppRadius.button,
                          child: InkWell(
                            onTap:        _proceedToCamera,
                            borderRadius: AppRadius.button,
                            child: const Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.camera_alt_rounded,
                                  color: Colors.white, size: 20),
                                SizedBox(width: 10),
                                Text(
                                  'Lanjut Ambil Foto',
                                  style: TextStyle(
                                    color:      Colors.white,
                                    fontSize:   15,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                                SizedBox(width: 6),
                                Icon(Icons.arrow_forward_rounded,
                                  color: Colors.white70, size: 18),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── Phase 2: Camera ────────────────────────────────────────────────────────

  Widget _buildCamera() {
    final isSubmitting = context.watch<AttendanceProvider>().isSubmitting;

    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        title: Text(_label,
          style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w600)),
        elevation:   0,
        centerTitle: true,
      ),
      body: Stack(
        children: [
          // Camera or photo preview
          if (_capturedPhoto != null)
            _PhotoPreview(file: _capturedPhoto!)
          else if (_initializingCamera || _camera == null)
            const Center(child: CircularProgressIndicator(color: AppColors.blue600))
          else
            _CameraPreview(controller: _camera!),

          // Oval face guide
          if (_capturedPhoto == null && !_initializingCamera)
            const _FaceOvalOverlay(),

          // GPS badge (always show — location is already verified)
          if (_capturedPhoto == null)
            Positioned(
              top: 12, left: 12,
              child: _GpsBadge(position: _position!),
            ),

          // Bottom action
          Positioned(
            bottom: 40, left: 24, right: 24,
            child: (isSubmitting || _isCapturing)
                ? const Center(
                    child: CircularProgressIndicator(color: Colors.white))
                : _CaptureButton(
                    accent:  _accent,
                    onTap:   _capture,
                    label:   _isCheckOut
                        ? 'Ambil Foto & Absen Pulang'
                        : 'Ambil Foto & Presensi',
                  ),
          ),
        ],
      ),
    );
  }
}

// ─── Shared screen header ─────────────────────────────────────────────────────

class _ScreenHeader extends StatelessWidget {
  final String     title;
  final VoidCallback onBack;

  const _ScreenHeader({required this.title, required this.onBack});

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 56,
      decoration: const BoxDecoration(
        color:  AppColors.white,
        border: Border(bottom: BorderSide(color: AppColors.gray200)),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 8),
      child: Row(
        children: [
          IconButton(
            icon:  const Icon(Icons.arrow_back_rounded, color: AppColors.gray700),
            onPressed: onBack,
          ),
          Expanded(
            child: Text(
              title,
              style: const TextStyle(
                fontSize:   16,
                fontWeight: FontWeight.w600,
                color:      AppColors.gray800,
              ),
              textAlign: TextAlign.center,
            ),
          ),
          // Spacer to balance back button
          const SizedBox(width: 48),
        ],
      ),
    );
  }
}

// ─── Pulsing icon (loading state) ────────────────────────────────────────────

class _PulsingIcon extends StatefulWidget {
  final IconData icon;
  final Color    color;

  const _PulsingIcon({required this.icon, required this.color});

  @override
  State<_PulsingIcon> createState() => _PulsingIconState();
}

class _PulsingIconState extends State<_PulsingIcon>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  late final Animation<double>   _scale;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync:    this,
      duration: const Duration(milliseconds: 900),
    )..repeat(reverse: true);
    _scale = Tween(begin: 0.88, end: 1.0).animate(
      CurvedAnimation(parent: _ctrl, curve: Curves.easeInOut));
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return ScaleTransition(
      scale: _scale,
      child: Container(
        width: 88, height: 88,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          color: widget.color.withOpacity(0.12),
        ),
        child: Icon(widget.icon, size: 44, color: widget.color),
      ),
    );
  }
}

// ─── Location info card ───────────────────────────────────────────────────────

class _LocationInfoCard extends StatelessWidget {
  final AttendanceLocation location;
  final double?            distanceMeters;
  final double?            accuracy;
  final bool               isVerified;

  const _LocationInfoCard({
    required this.location,
    this.distanceMeters,
    this.accuracy,
    this.isVerified = false,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color:        isVerified ? AppColors.green100 : AppColors.white,
        borderRadius: AppRadius.card,
        border: Border.all(
          color: isVerified ? const Color(0xFF86EFAC) : AppColors.gray200),
        boxShadow: AppShadow.sm,
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                Icons.location_on_rounded,
                size:  18,
                color: isVerified ? AppColors.green500 : AppColors.blue600,
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  location.name,
                  style: TextStyle(
                    fontSize:   14,
                    fontWeight: FontWeight.w600,
                    color: isVerified ? AppColors.green900 : AppColors.gray800,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          const Divider(height: 1, color: AppColors.gray100),
          const SizedBox(height: 10),

          _infoRow('Radius izin', '${location.radiusMeters} m',
            isVerified ? AppColors.green500 : AppColors.blue600),

          if (distanceMeters != null) ...[
            const SizedBox(height: 6),
            _infoRow(
              'Jarak Anda',
              '${distanceMeters!.toStringAsFixed(0)} m',
              distanceMeters! <= location.radiusMeters
                  ? AppColors.green500 : AppColors.red500,
            ),
          ],

          if (accuracy != null) ...[
            const SizedBox(height: 6),
            _infoRow('Akurasi GPS', '±${accuracy!.toStringAsFixed(0)} m',
              AppColors.gray500),
          ],
        ],
      ),
    );
  }

  Widget _infoRow(String label, String value, Color valueColor) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: const TextStyle(fontSize: 12, color: AppColors.gray500)),
        Text(value,
          style: TextStyle(
            fontSize:   13,
            fontWeight: FontWeight.w600,
            color:      valueColor,
          )),
      ],
    );
  }
}

// ─── Peta lokasi (radius + titik GPS) — setara peta Leaflet di web ────────────

class _LocationMap extends StatelessWidget {
  final AttendanceLocation location;
  final Position           position;

  const _LocationMap({required this.location, required this.position});

  @override
  Widget build(BuildContext context) {
    final school = LatLng(location.lat, location.lng);
    final user    = LatLng(position.latitude, position.longitude);
    final bounds  = LatLngBounds.fromPoints([school, user]);

    return ClipRRect(
      borderRadius: AppRadius.card,
      child: SizedBox(
        height: 220,
        child: FlutterMap(
          options: MapOptions(
            initialCenter: school,
            initialZoom:   17,
            initialCameraFit: CameraFit.bounds(
              bounds:  bounds,
              padding: const EdgeInsets.all(48),
              maxZoom: 18,
            ),
            interactionOptions: const InteractionOptions(
              flags: InteractiveFlag.pinchZoom | InteractiveFlag.drag,
            ),
          ),
          children: [
            TileLayer(
              urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
              userAgentPackageName: 'com.sman1gianyar.sims_mobile',
            ),
            CircleLayer(circles: [
              CircleMarker(
                point:             school,
                radius:            location.radiusMeters.toDouble(),
                useRadiusInMeter:  true,
                color:             AppColors.red500.withOpacity(0.15),
                borderColor:       AppColors.red500,
                borderStrokeWidth: 2,
              ),
            ]),
            MarkerLayer(markers: [
              Marker(
                point:  school,
                width:  32,
                height: 32,
                child: Container(
                  decoration: BoxDecoration(
                    shape:     BoxShape.circle,
                    color:     AppColors.blue600,
                    border:    Border.all(color: Colors.white, width: 3),
                    boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.35), blurRadius: 6)],
                  ),
                  child: const Icon(Icons.school_rounded, color: Colors.white, size: 16),
                ),
              ),
              Marker(
                point:  user,
                width:  20,
                height: 20,
                child: Container(
                  decoration: BoxDecoration(
                    shape:     BoxShape.circle,
                    color:     AppColors.red500,
                    border:    Border.all(color: Colors.white, width: 3),
                    boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.35), blurRadius: 6)],
                  ),
                ),
              ),
            ]),
          ],
        ),
      ),
    );
  }
}

// ─── Camera widgets ───────────────────────────────────────────────────────────

class _CameraPreview extends StatelessWidget {
  final CameraController controller;
  const _CameraPreview({required this.controller});

  @override
  Widget build(BuildContext context) {
    return SizedBox.expand(
      child: FittedBox(
        fit: BoxFit.cover,
        child: SizedBox(
          width:  controller.value.previewSize!.height,
          height: controller.value.previewSize!.width,
          child:  CameraPreview(controller),
        ),
      ),
    );
  }
}

class _PhotoPreview extends StatelessWidget {
  final File file;
  const _PhotoPreview({required this.file});

  @override
  Widget build(BuildContext context) {
    return SizedBox.expand(
      child: FittedBox(fit: BoxFit.cover, child: Image.file(file)),
    );
  }
}

class _FaceOvalOverlay extends StatelessWidget {
  const _FaceOvalOverlay();

  @override
  Widget build(BuildContext context) {
    return IgnorePointer(
      child: Center(
        child: Container(
          width: 180, height: 232,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(120),
            border: Border.all(
              color: Colors.white.withOpacity(0.60),
              width: 2,
            ),
          ),
        ),
      ),
    );
  }
}

class _GpsBadge extends StatelessWidget {
  final Position position;
  const _GpsBadge({required this.position});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
      decoration: BoxDecoration(
        color:        AppColors.green500.withOpacity(0.85),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.gps_fixed, color: Colors.white, size: 14),
          const SizedBox(width: 6),
          Text(
            'GPS aktif  ·  Akurasi ±${position.accuracy.toStringAsFixed(0)} m',
            style: const TextStyle(color: Colors.white, fontSize: 12),
          ),
        ],
      ),
    );
  }
}

class _CaptureButton extends StatelessWidget {
  final Color         accent;
  final String        label;
  final VoidCallback  onTap;

  const _CaptureButton({
    required this.accent,
    required this.label,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        height: 54,
        decoration: BoxDecoration(
          color:        accent,
          borderRadius: AppRadius.button,
          boxShadow: [
            BoxShadow(
              color:      accent.withOpacity(0.45),
              blurRadius: 12,
              offset:     const Offset(0, 4),
            ),
          ],
        ),
        alignment: Alignment.center,
        child: Text(
          label,
          style: const TextStyle(
            color:      Colors.white,
            fontSize:   15,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
    );
  }
}
