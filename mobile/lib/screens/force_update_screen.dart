import 'package:flutter/material.dart';
import '../services/version_service.dart';
import '../theme/app_colors.dart';

/// Layar penahan wajib (non-dismissible) yang memaksa pengguna memperbarui aplikasi di Play Store
class ForceUpdateScreen extends StatelessWidget {
  final VersionCheckResult result;

  const ForceUpdateScreen({
    super.key,
    required this.result,
  });

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false, // Menolak tombol back / gesture back
      child: Scaffold(
        backgroundColor: AppColors.slate100,
        body: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Container(
                constraints: const BoxConstraints(maxWidth: 440),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: AppRadius.card,
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.12),
                      blurRadius: 24,
                      offset: const Offset(0, 8),
                    ),
                  ],
                ),
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 36),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    // Icon Ilustrasi Pembaruan dengan Gradien Biru
                    Container(
                      width: 90,
                      height: 90,
                      decoration: BoxDecoration(
                        gradient: AppColors.loginGradient,
                        shape: BoxShape.circle,
                        boxShadow: [
                          BoxShadow(
                            color: AppColors.blue600.withOpacity(0.35),
                            blurRadius: 20,
                            offset: const Offset(0, 8),
                          ),
                        ],
                      ),
                      child: const Icon(
                        Icons.system_update_rounded,
                        size: 46,
                        color: Colors.white,
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Title
                    Text(
                      result.title.isNotEmpty
                          ? result.title
                          : 'Pembaruan Aplikasi Diperlukan',
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: AppColors.gray900,
                        letterSpacing: -0.3,
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Badge Versi (Versi Anda vs Versi Terbaru)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                      decoration: BoxDecoration(
                        color: AppColors.blue50,
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: AppColors.blue200),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            'Versi Anda: v${result.currentVersion}',
                            style: const TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w600,
                              color: AppColors.blue600,
                            ),
                          ),
                          const Padding(
                            padding: EdgeInsets.symmetric(horizontal: 6),
                            child: Icon(Icons.arrow_forward_rounded, size: 12, color: AppColors.blue600),
                          ),
                          Text(
                            'Minimal: v${result.minRequiredVersion}',
                            style: const TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              color: AppColors.indigo700,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Message Deskripsi
                    Text(
                      result.message,
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontSize: 13,
                        color: AppColors.gray600,
                        height: 1.5,
                      ),
                    ),
                    const SizedBox(height: 32),

                    // Tombol Gradient: Perbarui Sekarang
                    SizedBox(
                      width: double.infinity,
                      height: 50,
                      child: ElevatedButton(
                        onPressed: () => VersionService.launchPlayStore(result.updateUrl),
                        style: ElevatedButton.styleFrom(
                          padding: EdgeInsets.zero,
                          shape: RoundedRectangleBorder(
                            borderRadius: AppRadius.button,
                          ),
                          elevation: 4,
                          shadowColor: AppColors.blue600.withOpacity(0.4),
                        ),
                        child: Ink(
                          decoration: BoxDecoration(
                            gradient: AppColors.loginGradient,
                            borderRadius: AppRadius.button,
                          ),
                          child: Container(
                            alignment: Alignment.center,
                            child: const Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.shop_two_rounded, color: Colors.white, size: 20),
                                SizedBox(width: 10),
                                Text(
                                  'Perbarui Aplikasi di Play Store',
                                  style: TextStyle(
                                    fontSize: 14,
                                    fontWeight: FontWeight.bold,
                                    color: Colors.white,
                                  ),
                                ),
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
          ),
        ),
      ),
    );
  }
}
