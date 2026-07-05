import 'package:flutter/material.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';
import 'grade_screen.dart';
import 'teacher_attendance_screen.dart';
import '../kesiswaan/homeroom_consultation_screen.dart';

class KurikulumScreen extends StatefulWidget {
  const KurikulumScreen({super.key});

  @override
  State<KurikulumScreen> createState() => _KurikulumScreenState();
}

class _KurikulumScreenState extends State<KurikulumScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabCtrl;
  final _tabs = const ['Jadwal', 'Mingguan', 'Kalender', 'Nilai'];

  String? _guruWaliName;
  bool    _loadingWali = true;

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: _tabs.length, vsync: this);
    _fetchGuruWali();
  }

  Future<void> _fetchGuruWali() async {
    try {
      final body = await ApiClient.get('/siswa/guru-wali');
      final gw = body['guru_wali'] as Map<String, dynamic>?;
      if (mounted) {
        setState(() {
          _guruWaliName = gw?['name'] as String?;
          _loadingWali  = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loadingWali = false);
    }
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    super.dispose();
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
              gradient: AppColors.kurikulumGradient,
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
                child: const Icon(Icons.menu_book_rounded, color: Colors.white, size: 24),
              ),
              const SizedBox(width: 12),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(dateStr, style: const TextStyle(color: Color(0xFFA7F3D0), fontSize: 11)),
                const Text('Kurikulum', style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold, height: 1.3)),
                const Text('SMA Negeri 1 Gianyar', style: TextStyle(color: Color(0xFFA7F3D0), fontSize: 11)),
              ])),
            ]),
          ),
          const SizedBox(height: 12),

          // ─── Guru Wali Card ───────────────────────────────────────────
          _GuruWaliCard(name: _guruWaliName, loading: _loadingWali),
          const SizedBox(height: 8),

          // ─── Quick Links ─────────────────────────────────────────────
          _QuickLink(
            icon: Icons.assignment_turned_in_outlined,
            iconBg: AppColors.blue50,
            iconColor: AppColors.blue600,
            title: 'Absensi Guru Mengajar',
            subtitle: 'Lihat kehadiran guru di kelas kamu',
            onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const TeacherAttendanceScreen())),
          ),
          const SizedBox(height: 8),
          _QuickLink(
            icon: Icons.chat_bubble_outline_rounded,
            iconBg: AppColors.violet50,
            iconColor: AppColors.violet600,
            title: 'Bimbingan Guru Wali',
            subtitle: 'Ajukan dan lihat riwayat bimbingan',
            onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const HomeroomConsultationScreen())),
          ),
          const SizedBox(height: 12),

          // ─── Tab ─────────────────────────────────────────────────────
          Container(
            decoration: BoxDecoration(
              color: AppColors.white,
              borderRadius: AppRadius.card,
              border: Border.all(color: AppColors.gray100),
              boxShadow: AppShadow.sm,
            ),
            clipBehavior: Clip.antiAlias,
            child: Column(children: [
              // Tab buttons (scrollable horizontal)
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.all(12),
                child: Row(children: List.generate(_tabs.length, (i) {
                  return Padding(
                    padding: EdgeInsets.only(right: i < _tabs.length - 1 ? 8 : 0),
                    child: _TabChip(
                      label: _tabs[i],
                      selected: _tabCtrl.index == i,
                      selectedColor: AppColors.emerald600,
                      onTap: i == 3
                          ? () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GradeScreen()))
                          : () => setState(() => _tabCtrl.index = i),
                    ),
                  );
                })),
              ),
              const Divider(height: 1, color: AppColors.gray100),

              // Tab content header
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
                child: Row(children: [
                  Text(_tabHeader(_tabCtrl.index, now, days, months),
                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700)),
                ]),
              ),

              // Empty state
              _EmptyTabContent(
                icon: Icons.calendar_today_outlined,
                iconColor: AppColors.emerald500,
                label: _tabEmptyLabel(_tabCtrl.index),
              ),
            ]),
          ),
        ],
      ),
    );
  }

  String _tabHeader(int i, DateTime now, List<String> days, List<String> months) {
    return switch (i) {
      0 => '${days[now.weekday]}, ${now.day} ${months[now.month]} ${now.year}',
      1 => 'Jadwal Mingguan',
      2 => 'Kalender Akademik',
      _ => 'Nilai Semester',
    };
  }

  String _tabEmptyLabel(int i) {
    return switch (i) {
      0 => 'Belum ada jadwal untuk hari ini',
      1 => 'Belum ada jadwal pelajaran',
      2 => 'Belum ada jadwal akademik',
      _ => 'Belum ada nilai untuk semester ini',
    };
  }
}

// ─── Guru Wali Card ───────────────────────────────────────────────────────────

class _GuruWaliCard extends StatelessWidget {
  final String? name;
  final bool    loading;

  const _GuruWaliCard({this.name, required this.loading});

  @override
  Widget build(BuildContext context) {
    if (loading) {
      return Container(
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: AppRadius.card,
          border: Border.all(color: AppColors.gray100),
          boxShadow: AppShadow.sm,
        ),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        child: Row(children: [
          Container(
            width: 40, height: 40,
            decoration: BoxDecoration(
              color: AppColors.gray100,
              borderRadius: BorderRadius.circular(12),
            ),
          ),
          const SizedBox(width: 12),
          Container(height: 14, width: 120, color: AppColors.gray100),
        ]),
      );
    }

    if (name != null) {
      return Container(
        decoration: BoxDecoration(
          color: const Color(0xFFEEF2FF),
          borderRadius: AppRadius.card,
          border: Border.all(color: const Color(0xFFC7D2FE)),
        ),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        child: Row(children: [
          Container(
            width: 40, height: 40,
            decoration: BoxDecoration(
              color: const Color(0xFFC7D2FE),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(Icons.person_rounded, color: Color(0xFF4338CA), size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            const Text('Guru Wali Kamu',
              style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF6366F1))),
            Text(name!,
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF3730A3))),
          ])),
          GestureDetector(
            onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const HomeroomConsultationScreen())),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: const Color(0xFF4F46E5),
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Text('Bimbingan',
                style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Colors.white)),
            ),
          ),
        ]),
      );
    }

    return Container(
      decoration: BoxDecoration(
        color: AppColors.gray50,
        borderRadius: AppRadius.card,
        border: Border.all(color: AppColors.gray100),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      child: Row(children: [
        Container(
          width: 40, height: 40,
          decoration: BoxDecoration(
            color: AppColors.gray100,
            borderRadius: BorderRadius.circular(12),
          ),
          child: const Icon(Icons.person_outline_rounded, color: AppColors.gray400, size: 20),
        ),
        const SizedBox(width: 12),
        Column(crossAxisAlignment: CrossAxisAlignment.start, children: const [
          Text('Guru Wali',
            style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.gray400)),
          Text('Belum memiliki Guru Wali',
            style: TextStyle(fontSize: 13, color: AppColors.gray400)),
        ]),
      ]),
    );
  }
}

// ─── Shared widgets ───────────────────────────────────────────────────────────

class _QuickLink extends StatelessWidget {
  final IconData icon;
  final Color    iconBg;
  final Color    iconColor;
  final String   title;
  final String   subtitle;
  final VoidCallback onTap;

  const _QuickLink({
    required this.icon, required this.iconBg, required this.iconColor,
    required this.title, required this.subtitle, required this.onTap,
  });

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
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray800)),
            Text(subtitle, style: const TextStyle(fontSize: 11, color: AppColors.gray500)),
          ])),
          const Icon(Icons.chevron_right, color: AppColors.gray400, size: 18),
        ]),
      ),
    );
  }
}

class _TabChip extends StatelessWidget {
  final String label;
  final bool   selected;
  final Color  selectedColor;
  final VoidCallback onTap;

  const _TabChip({required this.label, required this.selected, required this.selectedColor, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: selected ? selectedColor : AppColors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: selected ? selectedColor : AppColors.gray200),
          boxShadow: selected ? [BoxShadow(color: selectedColor.withValues(alpha: 0.25), blurRadius: 6, offset: const Offset(0, 2))] : null,
        ),
        child: Text(label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w600,
            color: selected ? Colors.white : AppColors.gray600,
          )),
      ),
    );
  }
}

class _EmptyTabContent extends StatelessWidget {
  final IconData icon;
  final Color    iconColor;
  final String   label;

  const _EmptyTabContent({required this.icon, required this.iconColor, required this.label});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 40),
      child: Column(children: [
        Container(
          width: 56, height: 56,
          decoration: BoxDecoration(
            color: iconColor.withValues(alpha: 0.10),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Icon(icon, color: iconColor, size: 28),
        ),
        const SizedBox(height: 12),
        Text(label, style: const TextStyle(fontSize: 13, color: AppColors.gray400)),
      ]),
    );
  }
}
