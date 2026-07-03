import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/notification_provider.dart';
import '../../theme/app_colors.dart';

class HumasScreen extends StatelessWidget {
  const HumasScreen({super.key});

  void _showComingSoon(BuildContext context, String title) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text('$title — fitur segera hadir.'),
      backgroundColor: AppColors.gray700,
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: AppRadius.button),
    ));
  }

  @override
  Widget build(BuildContext context) {
    final now = DateTime.now();
    const months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const days = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    final dateStr = '${days[now.weekday]}, ${now.day} ${months[now.month]} ${now.year}';

    final announcements = context.watch<NotificationProvider>().announcements;

    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // ─── Header ──────────────────────────────────────────────────
          Container(
            decoration: BoxDecoration(
              gradient: AppColors.humasGradient,
              borderRadius: AppRadius.card,
            ),
            padding: const EdgeInsets.all(16),
            child: Row(children: [
              Container(
                width: 48, height: 48,
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.20),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: const Icon(Icons.campaign_rounded, color: Colors.white, size: 24),
              ),
              const SizedBox(width: 12),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(dateStr, style: const TextStyle(color: Color(0xFFFFCDD2), fontSize: 11)),
                const Text('Humas',
                  style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold, height: 1.3)),
                const Text('Hubungan Masyarakat & Informasi Sekolah',
                  style: TextStyle(color: Color(0xFFFFCDD2), fontSize: 11)),
              ])),
            ]),
          ),
          const SizedBox(height: 12),

          // ─── Aksi Cepat (3 kotak) ────────────────────────────────────
          Row(children: [
            Expanded(child: _QuickBox(
              icon: Icons.campaign_rounded,
              iconBg: AppColors.orange100,
              iconColor: AppColors.orange500,
              label: 'Pengumuman',
              onTap: () {
                // Buka sheet pengumuman (gunakan NotificationProvider)
                _openAnnouncements(context);
              },
            )),
            const SizedBox(width: 10),
            Expanded(child: _QuickBox(
              icon: Icons.event_rounded,
              iconBg: AppColors.rose100,
              iconColor: AppColors.rose500,
              label: 'Agenda',
              onTap: () => _showComingSoon(context, 'Agenda'),
            )),
            const SizedBox(width: 10),
            Expanded(child: _QuickBox(
              icon: Icons.photo_library_outlined,
              iconBg: AppColors.pink100,
              iconColor: AppColors.pink500,
              label: 'Galeri',
              onTap: () => _showComingSoon(context, 'Galeri'),
            )),
          ]),
          const SizedBox(height: 16),

          // ─── Pengumuman Terbaru ───────────────────────────────────────
          Row(children: [
            const Expanded(
              child: Text('Pengumuman Terbaru',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.gray700)),
            ),
            GestureDetector(
              onTap: () => _openAnnouncements(context),
              child: const Text('Lihat Semua',
                style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.orange500)),
            ),
          ]),
          const SizedBox(height: 8),

          if (announcements.isEmpty)
            _EmptyBox(
              icon: Icons.campaign_outlined,
              iconColor: AppColors.orange500,
              message: 'Belum ada pengumuman',
            )
          else
            Container(
              decoration: BoxDecoration(
                color: AppColors.white,
                borderRadius: AppRadius.card,
                border: Border.all(color: AppColors.gray100),
                boxShadow: AppShadow.sm,
              ),
              clipBehavior: Clip.antiAlias,
              child: Column(
                children: List.generate(
                  announcements.length > 5 ? 5 : announcements.length,
                  (i) {
                    final a = announcements[i];
                    return Column(children: [
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        child: Row(children: [
                          Container(
                            width: 36, height: 36,
                            decoration: BoxDecoration(
                              color: AppColors.orange100,
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: const Icon(Icons.campaign_rounded, color: AppColors.orange500, size: 18),
                          ),
                          const SizedBox(width: 12),
                          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                            Text(a.title,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray800)),
                            Text(_formatDate(a.publishedAt),
                              style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
                          ])),
                        ]),
                      ),
                      if (i < (announcements.length > 5 ? 4 : announcements.length - 1))
                        const Divider(height: 1, color: AppColors.gray100),
                    ]);
                  },
                ),
              ),
            ),
          const SizedBox(height: 16),

          // ─── Agenda Sekolah ───────────────────────────────────────────
          const Text('Agenda Sekolah',
            style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.gray700)),
          const SizedBox(height: 8),
          _EmptyBox(
            icon: Icons.event_outlined,
            iconColor: AppColors.rose500,
            message: 'Belum ada agenda sekolah',
          ),
          const SizedBox(height: 16),

          // ─── Galeri Terbaru ───────────────────────────────────────────
          Row(children: [
            const Expanded(
              child: Text('Galeri Terbaru',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.gray700)),
            ),
            GestureDetector(
              onTap: () => _showComingSoon(context, 'Galeri'),
              child: const Text('Lihat Semua',
                style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.orange500)),
            ),
          ]),
          const SizedBox(height: 8),
          _EmptyBox(
            icon: Icons.photo_library_outlined,
            iconColor: AppColors.pink500,
            message: 'Belum ada galeri',
          ),
        ],
      ),
    );
  }

  String _formatDate(DateTime? dt) {
    if (dt == null) return '';
    const months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return '${dt.day} ${months[dt.month]} ${dt.year}';
  }

  void _openAnnouncements(BuildContext context) {
    final prov = context.read<NotificationProvider>();
    prov.fetchAnnouncements();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => ChangeNotifierProvider.value(
        value: prov,
        child: const _AnnouncementSheet(),
      ),
    );
  }
}

// ─── Quick Box ────────────────────────────────────────────────────────────────

class _QuickBox extends StatelessWidget {
  final IconData icon;
  final Color    iconBg;
  final Color    iconColor;
  final String   label;
  final VoidCallback onTap;

  const _QuickBox({required this.icon, required this.iconBg, required this.iconColor,
    required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: AppRadius.card,
          border: Border.all(color: AppColors.gray100),
          boxShadow: AppShadow.sm,
        ),
        padding: const EdgeInsets.symmetric(vertical: 14),
        child: Column(children: [
          Container(
            width: 36, height: 36,
            decoration: BoxDecoration(color: iconBg, borderRadius: BorderRadius.circular(10)),
            child: Icon(icon, color: iconColor, size: 18),
          ),
          const SizedBox(height: 6),
          Text(label,
            style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.gray700),
            textAlign: TextAlign.center),
        ]),
      ),
    );
  }
}

// ─── Empty Box ────────────────────────────────────────────────────────────────

class _EmptyBox extends StatelessWidget {
  final IconData icon;
  final Color    iconColor;
  final String   message;

  const _EmptyBox({required this.icon, required this.iconColor, required this.message});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: AppRadius.card,
        border: Border.all(color: AppColors.gray100),
        boxShadow: AppShadow.sm,
      ),
      padding: const EdgeInsets.symmetric(vertical: 32),
      child: Column(children: [
        Icon(icon, size: 28, color: iconColor.withValues(alpha: 0.35)),
        const SizedBox(height: 8),
        Text(message, style: const TextStyle(fontSize: 13, color: AppColors.gray400)),
      ]),
    );
  }
}

// ─── Announcement Sheet ───────────────────────────────────────────────────────

class _AnnouncementSheet extends StatelessWidget {
  const _AnnouncementSheet();

  @override
  Widget build(BuildContext context) {
    final list = context.watch<NotificationProvider>().announcements;

    return DraggableScrollableSheet(
      initialChildSize: 0.75,
      maxChildSize: 0.95,
      minChildSize: 0.4,
      builder: (_, ctrl) => Container(
        decoration: const BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: Column(children: [
          const SizedBox(height: 12),
          Container(width: 40, height: 4, decoration: BoxDecoration(color: AppColors.gray200, borderRadius: BorderRadius.circular(2))),
          const SizedBox(height: 16),
          const Padding(
            padding: EdgeInsets.symmetric(horizontal: 20),
            child: Align(
              alignment: Alignment.centerLeft,
              child: Text('Pengumuman', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.gray800)),
            ),
          ),
          const SizedBox(height: 12),
          const Divider(height: 1, color: AppColors.gray100),
          Expanded(
            child: list.isEmpty
                ? const Center(child: Text('Belum ada pengumuman.', style: TextStyle(color: AppColors.gray400)))
                : ListView.separated(
                    controller: ctrl,
                    itemCount: list.length,
                    separatorBuilder: (_, __) => const Divider(height: 1, color: AppColors.gray100),
                    itemBuilder: (_, i) {
                      final a = list[i];
                      return Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                          Text(a.title,
                            style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: AppColors.gray800)),
                          const SizedBox(height: 4),
                          if (a.body.isNotEmpty)
                            Text(a.body,
                              style: const TextStyle(fontSize: 12, color: AppColors.gray600, height: 1.5)),
                          const SizedBox(height: 4),
                          Text(_formatDate(a.publishedAt),
                            style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
                        ]),
                      );
                    },
                  ),
          ),
        ]),
      ),
    );
  }

  String _formatDate(DateTime? dt) {
    if (dt == null) return '';
    const months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return '${dt.day} ${months[dt.month]} ${dt.year}';
  }
}
