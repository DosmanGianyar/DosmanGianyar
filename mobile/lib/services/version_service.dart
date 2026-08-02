import 'package:package_info_plus/package_info_plus.dart';
import 'package:url_launcher/url_launcher.dart';
import 'api_client.dart';

class VersionCheckResult {
  final bool needsForceUpdate;
  final String title;
  final String message;
  final String updateUrl;
  final String currentVersion;
  final String minRequiredVersion;

  VersionCheckResult({
    required this.needsForceUpdate,
    required this.title,
    required this.message,
    required this.updateUrl,
    required this.currentVersion,
    required this.minRequiredVersion,
  });
}

class VersionService {
  VersionService._();

  /// Periksa apakah versi aplikasi saat ini memenuhi versi minimal server
  static Future<VersionCheckResult?> checkVersion() async {
    try {
      final info = await PackageInfo.fromPlatform();
      final currentVersion = info.version; // e.g. "1.3.2"

      final data = await ApiClient.get('/app/version');
      final minRequired = data['min_required_version'] as String? ?? '1.0.0';
      final forceUpdate  = data['force_update'] as bool? ?? false;
      final updateUrl    = data['update_url'] as String? ?? '';
      final title        = data['title'] as String? ?? 'Pembaruan Aplikasi Diperlukan';
      final message      = data['message'] as String? ?? 'Silakan perbarui aplikasi Anda di Play Store untuk melanjutkan.';

      final isOutdated = _isVersionLower(currentVersion, minRequired);

      return VersionCheckResult(
        needsForceUpdate: forceUpdate && isOutdated,
        title: title,
        message: message,
        updateUrl: updateUrl,
        currentVersion: currentVersion,
        minRequiredVersion: minRequired,
      );
    } catch (_) {
      // Jika offline / error network, abaikan dulu agar aplikasi tetap berfungsi saat offline
      return null;
    }
  }

  /// Membandingkan 2 string versi (e.g. "1.3.2" < "1.4.0")
  static bool _isVersionLower(String current, String target) {
    try {
      final currentParts = current.split('+').first.split('.').map((e) => int.tryParse(e) ?? 0).toList();
      final targetParts  = target.split('+').first.split('.').map((e) => int.tryParse(e) ?? 0).toList();

      for (int i = 0; i < 3; i++) {
        final c = i < currentParts.length ? currentParts[i] : 0;
        final t = i < targetParts.length ? targetParts[i] : 0;
        if (c < t) return true;
        if (c > t) return false;
      }
      return false;
    } catch (_) {
      return false;
    }
  }

  /// Buka link Play Store
  static Future<void> launchPlayStore(String url) async {
    if (url.isEmpty) return;
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }
}
