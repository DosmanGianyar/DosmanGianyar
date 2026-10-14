import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../models/announcement.dart';
import '../services/announcement_popup_storage.dart';
import '../theme/app_colors.dart';
import 'image_viewer_dialog.dart';

/// Modal Dialog Pengumuman Sekolah Otomatis untuk Siswa.
/// Perilaku:
/// - Tombol 'X' atau 'Tutup': Menutup pop-up hanya untuk sesi saat ini (saat buka aplikasi lagi tetap muncul).
/// - Tombol 'Jangan Tampilkan Lagi': Memunculkan konfirmasi tambahan. Jika disetujui, pengumuman tidak akan muncul lagi.
class AnnouncementPopupDialog extends StatelessWidget {
  final AnnouncementItem announcement;
  final int? userId;
  final VoidCallback? onDismissForever;

  const AnnouncementPopupDialog({
    super.key,
    required this.announcement,
    this.userId,
    this.onDismissForever,
  });

  /// Helper statis untuk menampilkan dialog pop-up pengumuman
  static Future<void> show(
    BuildContext context, {
    required AnnouncementItem announcement,
    int? userId,
    VoidCallback? onDismissForever,
  }) {
    return showDialog(
      context: context,
      barrierDismissible: true,
      builder: (_) => AnnouncementPopupDialog(
        announcement: announcement,
        userId: userId,
        onDismissForever: onDismissForever,
      ),
    );
  }

  Future<void> _handleDontShowAgain(BuildContext context) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.help_outline_rounded, color: AppColors.amber500, size: 26),
            SizedBox(width: 10),
            Text(
              'Konfirmasi',
              style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
            ),
          ],
        ),
        content: const Text(
          'Apakah Anda yakin tidak ingin melihat pesan ini lagi?\n\n'
          'Pesan ini tidak akan muncul lagi sebagai pop-up saat Anda membuka aplikasi. Anda tetap dapat membacanya di daftar Pengumuman.',
          style: TextStyle(fontSize: 14, color: AppColors.gray700, height: 1.4),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(false),
            child: const Text(
              'Batal',
              style: TextStyle(color: AppColors.gray600, fontWeight: FontWeight.w600),
            ),
          ),
          FilledButton(
            onPressed: () => Navigator.of(ctx).pop(true),
            style: FilledButton.styleFrom(
              backgroundColor: AppColors.blue600,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            child: const Text(
              'Ya, Jangan Tampilkan',
              style: TextStyle(fontWeight: FontWeight.bold),
            ),
          ),
        ],
      ),
    );

    if (confirmed == true && context.mounted) {
      await AnnouncementPopupStorage.markDismissed(announcement.id, userId: userId);
      onDismissForever?.call();
      if (context.mounted) {
        Navigator.of(context).pop();
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final media = MediaQuery.of(context);
    final maxDialogHeight = media.size.height * 0.82;
    final maxDialogWidth = media.size.width > 500 ? 460.0 : media.size.width * 0.90;

    return Dialog(
      backgroundColor: Colors.transparent,
      insetPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
      child: ConstrainedBox(
        constraints: BoxConstraints(
          maxWidth: maxDialogWidth,
          maxHeight: maxDialogHeight,
        ),
        child: Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(26),
            boxShadow: const [
              BoxShadow(
                color: Color.fromRGBO(0, 0, 0, 0.25),
                blurRadius: 24,
                offset: Offset(0, 10),
              ),
            ],
          ),
          clipBehavior: Clip.antiAlias,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // ─── Header Gradient ───────────────────────────────────────────
              Container(
                padding: const EdgeInsets.fromLTRB(18, 14, 12, 14),
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    colors: [Color(0xFF0F2460), Color(0xFF1E3A8A), Color(0xFF312E81)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                ),
                child: Row(
                  children: [
                    Container(
                      width: 38,
                      height: 38,
                      decoration: BoxDecoration(
                        color: const Color.fromRGBO(255, 255, 255, 0.15),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color.fromRGBO(255, 255, 255, 0.20)),
                      ),
                      child: const Center(
                        child: Text('📢', style: TextStyle(fontSize: 18)),
                      ),
                    ),
                    const SizedBox(width: 12),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'PENGUMUMAN SEKOLAH',
                            style: TextStyle(
                              color: Color(0xFFBFDBFE),
                              fontSize: 11,
                              fontWeight: FontWeight.w800,
                              letterSpacing: 0.8,
                            ),
                          ),
                          Text(
                            'SMAN 1 Gianyar',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 15,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ),
                    // Tombol 'X' Biasa (Hanya menutup sesi saat ini)
                    Material(
                      color: const Color.fromRGBO(255, 255, 255, 0.12),
                      shape: const CircleBorder(),
                      clipBehavior: Clip.antiAlias,
                      child: IconButton(
                        icon: const Icon(Icons.close_rounded, color: Colors.white, size: 20),
                        tooltip: 'Tutup (muncul lagi nanti)',
                        onPressed: () => Navigator.of(context).pop(),
                      ),
                    ),
                  ],
                ),
              ),

              // ─── Body Scrollable Content ───────────────────────────────────
              Flexible(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(18),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Banner Gambar Lampiran (bila ada)
                      if (announcement.imageUrl != null && announcement.imageUrl!.isNotEmpty) ...[
                        GestureDetector(
                          onTap: () {
                            ImageViewerDialog.show(
                              context,
                              imageUrl: announcement.imageUrl!,
                              title: announcement.title,
                            );
                          },
                          child: Container(
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(16),
                              color: AppColors.gray100,
                              border: Border.all(color: AppColors.gray200),
                            ),
                            clipBehavior: Clip.antiAlias,
                            child: Stack(
                              children: [
                                Image.network(
                                  announcement.imageUrl!,
                                  width: double.infinity,
                                  fit: BoxFit.cover,
                                  loadingBuilder: (_, child, progress) {
                                    if (progress == null) return child;
                                    return const SizedBox(
                                      height: 180,
                                      child: Center(
                                        child: CircularProgressIndicator(strokeWidth: 2.5),
                                      ),
                                    );
                                  },
                                  errorBuilder: (_, __, ___) => const SizedBox(
                                    height: 120,
                                    child: Center(
                                      child: Column(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          Icon(Icons.broken_image_rounded, color: AppColors.gray400, size: 36),
                                          SizedBox(height: 6),
                                          Text('Gambar lampiran tidak tersedia', style: TextStyle(color: AppColors.gray500, fontSize: 11)),
                                        ],
                                      ),
                                    ),
                                  ),
                                ),
                                Positioned(
                                  bottom: 8,
                                  right: 8,
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                    decoration: BoxDecoration(
                                      color: const Color.fromRGBO(0, 0, 0, 0.65),
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: const Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Icon(Icons.zoom_in, color: Colors.white, size: 14),
                                        SizedBox(width: 4),
                                        Text('Ketuk untuk perbesar', style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w600)),
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 14),
                      ],

                      // Badges: Pinned & Tanggal
                      Wrap(
                        spacing: 8,
                        runSpacing: 6,
                        children: [
                          if (announcement.isPinned)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(
                                color: const Color(0xFFFEF3C7),
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(color: const Color(0xFFFDE68A)),
                              ),
                              child: const Text(
                                '📌 Pinned',
                                style: TextStyle(
                                  color: Color(0xFF92400E),
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: const Color(0xFFEFF6FF),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(color: const Color(0xFFDBEAFE)),
                            ),
                            child: Text(
                              '🗓️ ${DateFormat('d MMMM yyyy, HH:mm', 'id_ID').format(announcement.publishedAt)} WITA',
                              style: const TextStyle(
                                color: Color(0xFF1E40AF),
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),

                      // Judul Pengumuman
                      Text(
                        announcement.title,
                        style: const TextStyle(
                          fontSize: 17,
                          fontWeight: FontWeight.bold,
                          color: AppColors.gray900,
                          height: 1.35,
                        ),
                      ),
                      const SizedBox(height: 10),

                      // Isi Pesan Pengumuman
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: AppColors.gray50,
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: AppColors.gray200),
                        ),
                        child: Text(
                          announcement.body,
                          style: const TextStyle(
                            fontSize: 13.5,
                            color: AppColors.gray800,
                            height: 1.55,
                          ),
                        ),
                      ),

                      if (announcement.authorName != null && announcement.authorName!.isNotEmpty) ...[
                        const SizedBox(height: 12),
                        Text(
                          'Diumumkan oleh: ${announcement.authorName}',
                          style: const TextStyle(
                            fontSize: 11.5,
                            color: AppColors.gray500,
                            fontStyle: FontStyle.italic,
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              ),

              // ─── Footer Action Buttons ─────────────────────────────────────
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                decoration: const BoxDecoration(
                  color: Colors.white,
                  border: Border(
                    top: BorderSide(color: AppColors.gray200),
                  ),
                ),
                child: Row(
                  children: [
                    // Tombol "Jangan tampilkan lagi" (Dengan konfirmasi tambahan)
                    Expanded(
                      flex: 5,
                      child: TextButton.icon(
                        onPressed: () => _handleDontShowAgain(context),
                        style: TextButton.styleFrom(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 10),
                          foregroundColor: AppColors.gray600,
                        ),
                        icon: const Icon(Icons.visibility_off_outlined, size: 16),
                        label: const Text(
                          'Jangan tampilkan lagi',
                          style: TextStyle(
                            fontSize: 11.5,
                            fontWeight: FontWeight.w600,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),

                    // Tombol Tutup Biasa (Mengerti & Tutup - hanya tutup sesi saat ini)
                    Expanded(
                      flex: 4,
                      child: FilledButton(
                        onPressed: () => Navigator.of(context).pop(),
                        style: FilledButton.styleFrom(
                          backgroundColor: AppColors.blue600,
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        child: const Text(
                          'Mengerti & Tutup',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
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
