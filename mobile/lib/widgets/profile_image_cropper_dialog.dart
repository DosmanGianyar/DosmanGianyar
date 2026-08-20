import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image/image.dart' as img;
import 'package:path_provider/path_provider.dart';
import '../theme/app_colors.dart';

class ProfileImageCropperDialog extends StatefulWidget {
  final String imagePath;
  const ProfileImageCropperDialog({super.key, required this.imagePath});

  @override
  State<ProfileImageCropperDialog> createState() => _ProfileImageCropperDialogState();
}

class _ProfileImageCropperDialogState extends State<ProfileImageCropperDialog> {
  final TransformationController _transformController = TransformationController();
  final GlobalKey _cropAreaKey = GlobalKey();
  final GlobalKey _imageKey = GlobalKey();

  bool _isProcessing = false;

  @override
  void dispose() {
    _transformController.dispose();
    super.dispose();
  }

  Future<void> _cropAndSave() async {
    setState(() => _isProcessing = true);
    try {
      final file = File(widget.imagePath);
      final bytes = await file.readAsBytes();
      final decoded = img.decodeImage(bytes);

      if (decoded == null) {
        if (mounted) Navigator.pop(context, widget.imagePath);
        return;
      }

      // Fix orientation automatically
      final oriented = img.bakeOrientation(decoded);

      final cropAreaRenderBox = _cropAreaKey.currentContext?.findRenderObject() as RenderBox?;
      final imageRenderBox = _imageKey.currentContext?.findRenderObject() as RenderBox?;

      if (cropAreaRenderBox == null || imageRenderBox == null) {
        if (mounted) Navigator.pop(context, widget.imagePath);
        return;
      }

      final cropSize = cropAreaRenderBox.size;
      final imageSize = imageRenderBox.size;

      final matrix = _transformController.value;
      final scaleX = matrix.getMaxScaleOnAxis();

      // Find top-left position of image relative to crop window
      final imageOffset = imageRenderBox.localToGlobal(Offset.zero, ancestor: cropAreaRenderBox);

      final factor = oriented.width / imageSize.width;

      final cropXInImage = (-imageOffset.dx / scaleX) * factor;
      final cropYInImage = (-imageOffset.dy / scaleX) * factor;
      final cropWInImage = (cropSize.width / scaleX) * factor;
      final cropHInImage = (cropSize.height / scaleX) * factor;

      final x = cropXInImage.clamp(0.0, (oriented.width - 1).toDouble()).toInt();
      final y = cropYInImage.clamp(0.0, (oriented.height - 1).toDouble()).toInt();
      final w = cropWInImage.clamp(1.0, (oriented.width - x).toDouble()).toInt();
      final h = cropHInImage.clamp(1.0, (oriented.height - y).toDouble()).toInt();

      final cropped = img.copyCrop(oriented, x: x, y: y, width: w, height: h);
      final resized = img.copyResize(cropped, width: 600, height: 600);
      final jpgBytes = img.encodeJpg(resized, quality: 85);

      final tempDir = await getTemporaryDirectory();
      final targetFile = File('${tempDir.path}/cropped_avatar_${DateTime.now().millisecondsSinceEpoch}.jpg');
      await targetFile.writeAsBytes(jpgBytes);

      if (mounted) Navigator.pop(context, targetFile.path);
    } catch (e) {
      if (mounted) Navigator.pop(context, widget.imagePath);
    } finally {
      if (mounted) setState(() => _isProcessing = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      backgroundColor: Colors.black,
      insetPadding: const EdgeInsets.all(16),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(20),
        child: Container(
          width: double.infinity,
          constraints: const BoxConstraints(maxWidth: 450),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Header
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                color: const Color(0xFF1E293B),
                child: Row(
                  children: [
                    const Icon(Icons.crop_rotate_rounded, color: Colors.white, size: 18),
                    const SizedBox(width: 8),
                    const Text(
                      'Sesuaikan & Geser Foto',
                      style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                    ),
                    const Spacer(),
                    IconButton(
                      icon: const Icon(Icons.close_rounded, color: Colors.gray300, size: 20),
                      onPressed: () => Navigator.pop(context, null),
                      padding: EdgeInsets.zero,
                      constraints: const BoxConstraints(),
                    ),
                  ],
                ),
              ),

              // Interactive Crop Area
              Container(
                color: Colors.black,
                padding: const EdgeInsets.symmetric(vertical: 20),
                child: Column(
                  children: [
                    const Text(
                      'Geser & jepit layar untuk memposisikan kepala/wajah',
                      style: TextStyle(color: Colors.white70, fontSize: 11),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 16),
                    Center(
                      child: Container(
                        key: _cropAreaKey,
                        width: 260,
                        height: 260,
                        decoration: BoxDecoration(
                          shape: BoxShape.rectangle,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: AppColors.blue500, width: 2.5),
                          boxShadow: const [
                            BoxShadow(color: Colors.black50, blurRadius: 20, spreadRadius: 10),
                          ],
                        ),
                        clipBehavior: Clip.antiAlias,
                        child: Stack(
                          children: [
                            InteractiveViewer(
                              transformationController: _transformController,
                              minScale: 0.8,
                              maxScale: 4.0,
                              boundaryMargin: const EdgeInsets.all(300),
                              child: Container(
                                alignment: Alignment.center,
                                child: Image.file(
                                  File(widget.imagePath),
                                  key: _imageKey,
                                  fit: BoxFit.contain,
                                ),
                              ),
                            ),
                            // Circular guide overlay
                            IgnorePointer(
                              child: Container(
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  border: Border.all(color: Colors.white.withOpacity(0.6), width: 1.5),
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

              // Actions
              Container(
                padding: const EdgeInsets.all(16),
                color: const Color(0xFF0F172A),
                child: Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: _isProcessing ? null : () => Navigator.pop(context, null),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: Colors.white70,
                          side: const BorderSide(color: Colors.white30),
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        child: const Text('Batal'),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      flex: 2,
                      child: FilledButton.icon(
                        onPressed: _isProcessing ? null : _cropAndSave,
                        style: FilledButton.styleFrom(
                          backgroundColor: AppColors.blue600,
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        icon: _isProcessing
                            ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                            : const Icon(Icons.check_rounded, size: 18),
                        label: const Text('Simpan Foto Profil', style: TextStyle(fontWeight: FontWeight.bold)),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
