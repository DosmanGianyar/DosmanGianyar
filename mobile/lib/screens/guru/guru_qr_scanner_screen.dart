import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../../theme/app_colors.dart';

class GuruQrScannerScreen extends StatefulWidget {
  const GuruQrScannerScreen({super.key});

  @override
  State<GuruQrScannerScreen> createState() => _GuruQrScannerScreenState();
}

class _GuruQrScannerScreenState extends State<GuruQrScannerScreen> {
  final MobileScannerController _scannerController = MobileScannerController(
    detectionSpeed: DetectionSpeed.normal,
    facing: CameraFacing.back,
    torchEnabled: false,
  );

  bool _isScanned = false;
  bool _isTorchOn = false;

  @override
  void dispose() {
    _scannerController.dispose();
    super.dispose();
  }

  void _handleBarcode(BarcodeCapture capture) {
    if (_isScanned) return;

    final List<Barcode> barcodes = capture.barcodes;
    for (final barcode in barcodes) {
      final String? rawValue = barcode.rawValue;
      if (rawValue != null && rawValue.trim().isNotEmpty) {
        setState(() {
          _isScanned = true;
        });

        HapticFeedback.mediumImpact();

        Navigator.pop(context, rawValue.trim());
        break;
      }
    }
  }

  Future<void> _openManualInputDialog() async {
    final codeCtrl = TextEditingController();
    final manualCode = await showDialog<String>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(Icons.edit_note_rounded, color: AppColors.blue600),
            SizedBox(width: 8),
            Text('Input Manual Barcode / NISN', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Masukkan nomor NISN, NIS, atau kode barcode dari Kartu Siswa:',
              style: TextStyle(fontSize: 12, color: AppColors.gray600),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: codeCtrl,
              autofocus: true,
              keyboardType: TextInputType.text,
              decoration: InputDecoration(
                hintText: 'Contoh: 0081234567 atau Token QR',
                prefixIcon: const Icon(Icons.qr_code_rounded, size: 20),
                filled: true,
                fillColor: AppColors.gray50,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
              ),
              onSubmitted: (val) => Navigator.pop(ctx, val.trim()),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.blue600,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () => Navigator.pop(ctx, codeCtrl.text.trim()),
            child: const Text('Gunakan Kode'),
          ),
        ],
      ),
    );

    if (manualCode != null && manualCode.trim().isNotEmpty && mounted) {
      Navigator.pop(context, manualCode.trim());
    }
  }

  @override
  Widget build(BuildContext context) {
    final scanWindowSize = MediaQuery.of(context).size.width * 0.72;

    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        children: [
          // ─── Kamera Live Scanner ───────────────────────────────────────────
          MobileScanner(
            controller: _scannerController,
            onDetect: _handleBarcode,
          ),

          // ─── Overlay Gelap & Frame Scanner ────────────────────────────────
          ColorFiltered(
            colorFilter: ColorFilter.mode(
              Colors.black.withValues(alpha: 0.6),
              BlendMode.srcOut,
            ),
            child: Stack(
              children: [
                Container(
                  decoration: const BoxDecoration(
                    color: Colors.red,
                    backgroundBlendMode: BlendMode.dstOut,
                  ),
                ),
                Align(
                  alignment: Alignment.center,
                  child: Container(
                    width: scanWindowSize,
                    height: scanWindowSize,
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(20),
                    ),
                  ),
                ),
              ],
            ),
          ),

          // ─── Border Frame Putih Berwarna Biru di Tengah ────────────────────
          Align(
            alignment: Alignment.center,
            child: Container(
              width: scanWindowSize,
              height: scanWindowSize,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppColors.blue500, width: 3),
                boxShadow: [
                  BoxShadow(
                    color: AppColors.blue500.withValues(alpha: 0.4),
                    blurRadius: 15,
                    spreadRadius: 2,
                  ),
                ],
              ),
            ),
          ),

          // ─── Header bar dengan Tombol Tutup & Flashlight ─────────────────
          SafeArea(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  IconButton(
                    style: IconButton.styleFrom(
                      backgroundColor: Colors.black45,
                      foregroundColor: Colors.white,
                    ),
                    icon: const Icon(Icons.arrow_back_rounded),
                    onPressed: () => Navigator.pop(context),
                  ),
                  const Text(
                    'Pindai QR / Barcode Kartu Siswa',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                      shadows: [Shadow(color: Colors.black, blurRadius: 4)],
                    ),
                  ),
                  IconButton(
                    style: IconButton.styleFrom(
                      backgroundColor: _isTorchOn ? Colors.amber : Colors.black45,
                      foregroundColor: _isTorchOn ? Colors.black : Colors.white,
                    ),
                    icon: Icon(_isTorchOn ? Icons.flash_on_rounded : Icons.flash_off_rounded),
                    onPressed: () async {
                      await _scannerController.toggleTorch();
                      setState(() {
                        _isTorchOn = !_isTorchOn;
                      });
                    },
                  ),
                ],
              ),
            ),
          ),

          // ─── Bottom Hint & Manual Input Button ────────────────────────────
          SafeArea(
            child: Align(
              alignment: Alignment.bottomCenter,
              child: Padding(
                padding: const EdgeInsets.all(24.0),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                      decoration: BoxDecoration(
                        color: Colors.black87,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.white24),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.qr_code_scanner_rounded, color: AppColors.blue400, size: 20),
                          SizedBox(width: 8),
                          Text(
                            'Arahkan kamera tepat pada QR Code Kartu Pelajar',
                            style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                    OutlinedButton.icon(
                      style: OutlinedButton.styleFrom(
                        foregroundColor: Colors.white,
                        side: const BorderSide(color: Colors.white70),
                        backgroundColor: Colors.black54,
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      onPressed: _openManualInputDialog,
                      icon: const Icon(Icons.keyboard_rounded, size: 18),
                      label: const Text(
                        'Kartu Rusak? Input Manual NISN / Token',
                        style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
