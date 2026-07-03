import 'package:flutter/material.dart';
import '../../theme/app_colors.dart';

class PrasaranaScreen extends StatelessWidget {
  const PrasaranaScreen({super.key});

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

    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // ─── Header ──────────────────────────────────────────────────
          Container(
            decoration: BoxDecoration(
              gradient: AppColors.prasaranaGradient,
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
                child: const Icon(Icons.business_rounded, color: Colors.white, size: 24),
              ),
              const SizedBox(width: 12),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(dateStr, style: const TextStyle(color: Color(0xFFDDD6FE), fontSize: 11)),
                const Text('Sarana & Prasarana',
                  style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold, height: 1.3)),
                const Text('SMA Negeri 1 Gianyar',
                  style: TextStyle(color: Color(0xFFDDD6FE), fontSize: 11)),
              ])),
            ]),
          ),
          const SizedBox(height: 12),

          // ─── Stats 4-grid ─────────────────────────────────────────────
          Row(children: [
            Expanded(child: _StatCard(value: '0', label: 'Pinjaman Aktif',    color: AppColors.violet600)),
            const SizedBox(width: 10),
            Expanded(child: _StatCard(value: '0', label: 'Sudah Dikembalikan', color: AppColors.gray600)),
          ]),
          const SizedBox(height: 10),
          Row(children: [
            Expanded(child: _StatCard(value: '0', label: 'Laporan Diproses',  color: AppColors.orange600)),
            const SizedBox(width: 10),
            Expanded(child: _StatCard(value: '0', label: 'Total Laporan',     color: AppColors.blue600)),
          ]),
          const SizedBox(height: 12),

          // ─── Aksi Cepat ───────────────────────────────────────────────
          Row(children: [
            Expanded(
              child: GestureDetector(
                onTap: () => _showComingSoon(context, 'Scan Aset'),
                child: Container(
                  decoration: BoxDecoration(
                    gradient: AppColors.prasaranaGradient,
                    borderRadius: AppRadius.card,
                  ),
                  padding: const EdgeInsets.all(14),
                  child: Row(children: [
                    Container(
                      width: 40, height: 40,
                      decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.20), borderRadius: BorderRadius.circular(12)),
                      child: const Icon(Icons.qr_code_scanner_rounded, color: Colors.white, size: 20),
                    ),
                    const SizedBox(width: 10),
                    const Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text('Scan Aset', style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold)),
                      Text('Scan QR untuk pinjam', style: TextStyle(color: Color(0xFFDDD6FE), fontSize: 10)),
                    ]),
                  ]),
                ),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _AksiCard(
                icon: Icons.list_alt_rounded,
                iconBg: AppColors.violet100,
                iconColor: AppColors.violet600,
                title: 'Semua Pinjaman',
                subtitle: 'Riwayat lengkap',
                onTap: () => _showComingSoon(context, 'Semua Pinjaman'),
              ),
            ),
          ]),
          const SizedBox(height: 10),
          _AksiCard(
            icon: Icons.inventory_2_outlined,
            iconBg: AppColors.violet100,
            iconColor: AppColors.violet600,
            title: 'Katalog Aset',
            subtitle: 'Browse aset tanpa scan QR',
            onTap: () => _showComingSoon(context, 'Katalog Aset'),
          ),
          const SizedBox(height: 16),

          // ─── Pinjaman Aktif ───────────────────────────────────────────
          _SectionHeader(title: 'Pinjaman Aktif'),
          const SizedBox(height: 8),
          _EmptyCard(
            icon: Icons.swap_horiz_rounded,
            iconColor: AppColors.violet500,
            message: 'Tidak ada pinjaman aktif',
            action: 'Scan aset untuk meminjam',
            actionColor: AppColors.violet600,
            onAction: () => _showComingSoon(context, 'Scan Aset'),
          ),
          const SizedBox(height: 16),

          // ─── Laporan Kerusakan ────────────────────────────────────────
          _SectionHeader(title: 'Laporan Kerusakan Saya'),
          const SizedBox(height: 8),
          _EmptyCard(
            icon: Icons.warning_amber_rounded,
            iconColor: AppColors.orange500,
            message: 'Belum ada laporan kerusakan',
          ),
        ],
      ),
    );
  }
}

class _StatCard extends StatelessWidget {
  final String value;
  final String label;
  final Color  color;
  const _StatCard({required this.value, required this.label, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: AppRadius.card,
        border: Border.all(color: AppColors.gray100),
        boxShadow: AppShadow.sm,
      ),
      padding: const EdgeInsets.all(16),
      child: Column(children: [
        Text(value, style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color)),
        const SizedBox(height: 4),
        Text(label, style: const TextStyle(fontSize: 11, color: AppColors.gray500), textAlign: TextAlign.center),
      ]),
    );
  }
}

class _AksiCard extends StatelessWidget {
  final IconData icon;
  final Color    iconBg;
  final Color    iconColor;
  final String   title;
  final String   subtitle;
  final VoidCallback onTap;

  const _AksiCard({required this.icon, required this.iconBg, required this.iconColor,
    required this.title, required this.subtitle, required this.onTap});

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
        padding: const EdgeInsets.all(14),
        child: Row(children: [
          Container(
            width: 40, height: 40,
            decoration: BoxDecoration(color: iconBg, borderRadius: BorderRadius.circular(12)),
            child: Icon(icon, color: iconColor, size: 20),
          ),
          const SizedBox(width: 12),
          Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(title, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.gray700)),
            Text(subtitle, style: const TextStyle(fontSize: 10, color: AppColors.gray400)),
          ]),
        ]),
      ),
    );
  }
}

class _SectionHeader extends StatelessWidget {
  final String title;
  const _SectionHeader({required this.title});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(left: 4),
      child: Text(title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.gray700)),
    );
  }
}

class _EmptyCard extends StatelessWidget {
  final IconData icon;
  final Color    iconColor;
  final String   message;
  final String?  action;
  final Color?   actionColor;
  final VoidCallback? onAction;

  const _EmptyCard({
    required this.icon, required this.iconColor, required this.message,
    this.action, this.actionColor, this.onAction,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: AppRadius.card,
        border: Border.all(color: AppColors.gray100),
        boxShadow: AppShadow.sm,
      ),
      padding: const EdgeInsets.symmetric(vertical: 28),
      child: Column(children: [
        Icon(icon, size: 28, color: iconColor.withValues(alpha: 0.40)),
        const SizedBox(height: 8),
        Text(message, style: const TextStyle(fontSize: 13, color: AppColors.gray400)),
        if (action != null) ...[
          const SizedBox(height: 6),
          GestureDetector(
            onTap: onAction,
            child: Text(action!, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: actionColor ?? AppColors.blue600)),
          ),
        ],
      ]),
    );
  }
}
