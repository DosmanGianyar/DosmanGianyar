import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../models/guru_dashboard.dart';
import '../../models/user.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../services/guru_service.dart';
import '../../theme/app_colors.dart';
import 'guru_absensi_harian_screen.dart';
import 'guru_conduct_screen.dart';
import 'guru_conduct_input_screen.dart';
import 'guru_qr_scanner_screen.dart';
import 'guru_input_nilai_screen.dart';
import 'guru_permit_screen.dart';
import 'guru_sarpras_screen.dart';
import 'guru_teaching_session_screen.dart';
import 'guru_tp_screen.dart';
import 'guru_layanan_screen.dart';

class GuruHomeScreen extends StatefulWidget {
  const GuruHomeScreen({super.key});

  @override
  State<GuruHomeScreen> createState() => _GuruHomeScreenState();
}

class _GuruHomeScreenState extends State<GuruHomeScreen> {
  GuruDashboard? _dashboard;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final data = await GuruService.getDashboard();
      if (mounted) setState(() { _dashboard = data; _loading = false; });
    } catch (e) {
      if (mounted) setState(() { _error = e.toString(); _loading = false; });
    }
  }

  String get _greeting {
    final h = DateTime.now().hour;
    if (h < 11) return 'Pagi';
    if (h < 15) return 'Siang';
    return 'Sore';
  }

  @override
  Widget build(BuildContext context) {
    final user      = context.watch<AuthProvider>().user;
    final fullName  = user?.name ?? 'Guru';

    return RefreshIndicator(
      onRefresh: _load,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(16, 20, 16, 24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ─── Header: Branding SIMAK_DOSMAN, Greeting & Foto Profile ───────
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // ─── Branding Header SIMAK_DOSMAN ──────────────
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(4),
                            decoration: BoxDecoration(
                              color: AppColors.blue50,
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(color: AppColors.blue200),
                            ),
                            child: Image.asset(
                              'assets/images/logo_sekolah.png',
                              width: 20,
                              height: 20,
                              fit: BoxFit.contain,
                              errorBuilder: (_, __, ___) => const Icon(Icons.school_rounded, size: 20, color: AppColors.blue600),
                            ),
                          ),
                          const SizedBox(width: 8),
                          const Text(
                            'SIMAK_DOSMAN',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w900,
                              letterSpacing: 1.1,
                              color: AppColors.blue700,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      Text(
                        'Selamat $_greeting,',
                        style: const TextStyle(fontSize: 13, color: AppColors.gray500, fontWeight: FontWeight.w500),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '$fullName 👋',
                        style: const TextStyle(
                          fontSize: 19,
                          fontWeight: FontWeight.w800,
                          color: AppColors.gray800,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        DateFormat('EEEE, d MMMM y', 'id_ID').format(DateTime.now()),
                        style: const TextStyle(fontSize: 12, color: AppColors.gray500),
                      ),
                      // ─── Badges Mata Pelajaran yang Diampu ──────────────
                      _buildSubjectBadges(user),
                    ],
                  ),
                ),
                const SizedBox(width: 14),

                // ─── Foto Profile Guru Estetik (Header Kanan) ─────────────
                Builder(
                  builder: (context) {
                    final photoUrl = user?.photoUrl;
                    final hasPhoto = photoUrl != null && photoUrl.trim().isNotEmpty;
                    return GestureDetector(
                      onTap: () {
                        // Toast info profile
                        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                          content: Text('Profil Guru: ${user?.name ?? ''}'),
                          behavior: SnackBarBehavior.floating,
                          duration: const Duration(seconds: 1),
                        ));
                      },
                      child: Stack(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(2.5),
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              gradient: const LinearGradient(
                                colors: [AppColors.blue600, Color(0xFF6366F1)],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                              boxShadow: [
                                BoxShadow(
                                  color: AppColors.blue600.withValues(alpha: 0.25),
                                  blurRadius: 10,
                                  offset: const Offset(0, 4),
                                ),
                              ],
                            ),
                            child: CircleAvatar(
                              radius: 26,
                              backgroundColor: Colors.white,
                              child: CircleAvatar(
                                radius: 24,
                                backgroundColor: AppColors.blue100,
                                backgroundImage: hasPhoto ? NetworkImage(photoUrl) : null,
                                child: !hasPhoto
                                    ? Text(
                                        user?.initials ?? 'G',
                                        style: const TextStyle(
                                          fontSize: 17,
                                          fontWeight: FontWeight.w900,
                                          color: AppColors.blue700,
                                        ),
                                      )
                                    : null,
                              ),
                            ),
                          ),
                          Positioned(
                            bottom: 2,
                            right: 2,
                            child: Container(
                              width: 14,
                              height: 14,
                              decoration: BoxDecoration(
                                color: AppColors.emerald500,
                                shape: BoxShape.circle,
                                border: Border.all(color: Colors.white, width: 2.5),
                              ),
                            ),
                          ),
                        ],
                      ),
                    );
                  },
                ),
              ],
            ),
            const SizedBox(height: 20),

            // ─── Stat Cards / Loading / Error ────────────────────────────
            if (_loading)
              const Center(
                child: Padding(
                  padding: EdgeInsets.all(48),
                  child: CircularProgressIndicator(),
                ),
              )
            else if (_error != null)
              _buildError()
            else if (_dashboard != null) ...[
              _buildScanKartuShortcutCard(),
              _buildPembinaBanner(_dashboard!),
              _buildTodayScheduleCard(context, _dashboard!),
              _buildStatGrid(_dashboard!),
              const SizedBox(height: 16),
              _buildQuickActions(),
              const SizedBox(height: 20),
              _buildWeeklyJournals(_dashboard!),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildSubjectBadges(User? user) {
    List<String> subjects = [];
    if (_dashboard != null && _dashboard!.mySubjects.isNotEmpty) {
      subjects = _dashboard!.mySubjects;
    } else if (user != null) {
      if (user.subjects.isNotEmpty) {
        subjects = user.subjects.map((s) => s.name).toList();
      } else if (user.subject != null && user.subject!.isNotEmpty) {
        subjects = user.subject!.split(',').map((s) => s.trim()).where((s) => s.isNotEmpty).toList();
      }
    }

    if (subjects.isEmpty) return const SizedBox.shrink();

    return Padding(
      padding: const EdgeInsets.only(top: 6),
      child: Wrap(
        spacing: 6,
        runSpacing: 4,
        children: subjects.map((sub) => Container(
          padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3.5),
          decoration: BoxDecoration(
            color: AppColors.blue50,
            borderRadius: BorderRadius.circular(8),
            border: Border.all(color: AppColors.blue200, width: 0.8),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.menu_book_rounded, size: 11, color: AppColors.blue600),
              const SizedBox(width: 4),
              Text(
                sub,
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  color: AppColors.blue700,
                ),
              ),
            ],
          ),
        )).toList(),
      ),
    );
  }

  Widget _buildScanKartuShortcutCard() {
    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF4F46E5), Color(0xFF6366F1)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF4F46E5).withValues(alpha: 0.25),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: _openDashboardScanDialog,
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            child: Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.qr_code_scanner_rounded, color: Colors.white, size: 24),
                ),
                const SizedBox(width: 14),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        '📷 Scan Kartu Siswa',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w800,
                          color: Colors.white,
                        ),
                      ),
                      SizedBox(height: 2),
                      Text(
                        'Scan barcode Kartu Pelajar untuk catat pelanggaran / prestasi',
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.white70,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ),
                const Icon(Icons.chevron_right_rounded, color: Colors.white, size: 22),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Future<void> _openDashboardScanDialog() async {
    final code = await Navigator.push<String>(
      context,
      MaterialPageRoute(builder: (_) => const GuruQrScannerScreen()),
    );

    if (code == null || code.trim().isEmpty) return;

    if (mounted) {
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => GuruConductInputScreen(initialCode: code.trim()),
        ),
      );
    }
  }

  Widget _buildPembinaBanner(GuruDashboard data) {
    if (data.myExtracurriculars.isEmpty) return const SizedBox.shrink();

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF312E81), Color(0xFF1E40AF)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(color: Colors.indigo.withValues(alpha: 0.2), blurRadius: 8, offset: const Offset(0, 4))
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Text('🎗️', style: TextStyle(fontSize: 18)),
              const SizedBox(width: 8),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'PEMBINA EKSTRAKURIKULER',
                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Color(0xFFC7D2FE), letterSpacing: 0.5),
                    ),
                    Text(
                      'Anda bertugas membina ${data.myExtracurriculars.length} Ekstrakurikuler',
                      style: const TextStyle(fontSize: 11, color: Colors.white70),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Wrap(
            spacing: 6, runSpacing: 6,
            children: data.myExtracurriculars.map((e) => Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: Colors.white.withValues(alpha: 0.25)),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Text('🏆 ', style: TextStyle(fontSize: 12)),
                  Text(e.name, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.white)),
                  const SizedBox(width: 4),
                  Text('(${e.membersCount} siswa)', style: const TextStyle(fontSize: 10, color: Colors.white70)),
                ],
              ),
            )).toList(),
          ),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: () => _showPendingExtraMembersModal(context),
              icon: const Icon(Icons.check_circle_outline_rounded, size: 16, color: Color(0xFF312E81)),
              label: const Text(
                '📋 Persetujuan Pendaftaran Anggota Ekstra',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Color(0xFF312E81)),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 10),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                elevation: 0,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ─── Histori Jurnal Mengajar (Per Minggu) ───────────────────────────────────
  Widget _buildWeeklyJournals(GuruDashboard data) {
    if (data.weeklyJournals.isEmpty) {
      return Container(
        width: double.infinity,
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(AppRadius.xl),
          border: Border.all(color: AppColors.gray100),
          boxShadow: AppShadow.sm,
        ),
        child: const Center(
          child: Text(
            'Belum ada histori jurnal mengajar yang dibuat.',
            style: TextStyle(fontSize: 12, color: AppColors.gray400),
          ),
        ),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Row(
          children: [
            Icon(Icons.history_edu_rounded, size: 18, color: AppColors.blue600),
            SizedBox(width: 8),
            Text(
              'Histori Jurnal Mengajar (Per Minggu)',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w700,
                color: AppColors.gray800,
              ),
            ),
          ],
        ),
        const SizedBox(height: 10),
        ListView.separated(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: data.weeklyJournals.length,
          separatorBuilder: (_, __) => const SizedBox(height: 12),
          itemBuilder: (_, i) {
            final group = data.weeklyJournals[i];
            return Container(
              decoration: BoxDecoration(
                color: AppColors.white,
                borderRadius: BorderRadius.circular(AppRadius.xl),
                border: Border.all(color: AppColors.gray100),
                boxShadow: AppShadow.sm,
              ),
              clipBehavior: Clip.antiAlias,
              child: ExpansionTile(
                initiallyExpanded: i == 0,
                tilePadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                childrenPadding: const EdgeInsets.all(12),
                backgroundColor: AppColors.white,
                collapsedBackgroundColor: AppColors.white,
                shape: const Border(),
                collapsedShape: const Border(),
                title: Row(
                  children: [
                    Container(
                      width: 8, height: 8,
                      decoration: const BoxDecoration(
                        color: AppColors.blue600,
                        shape: BoxShape.circle,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      'Minggu (${group.weekRange})',
                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.gray800),
                    ),
                  ],
                ),
                trailing: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: AppColors.blue50,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    '${group.count} jurnal',
                    style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.blue600),
                  ),
                ),
                children: group.journals.map((j) {
                  return Container(
                    margin: const EdgeInsets.only(bottom: 8),
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppColors.gray50,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: AppColors.gray200),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Text(
                              j.dateFormatted,
                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.gray800),
                            ),
                            const Spacer(),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: AppColors.blue100,
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                '${j.className} • Jam ${j.period}',
                                style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.blue800),
                              ),
                            ),
                          ],
                        ),
                        if (j.tpCode != null || j.tpDescription != null) ...[
                          const SizedBox(height: 6),
                          Container(
                            width: double.infinity,
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: AppColors.indigo700.withOpacity(0.08),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              'TP: ${j.tpCode != null ? "[${j.tpCode}] " : ""}${j.tpDescription ?? ""}',
                              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.indigo700),
                            ),
                          ),
                        ],
                        const SizedBox(height: 6),
                        Text('Materi: ${j.material}', style: const TextStyle(fontSize: 11, color: AppColors.gray800)),
                        Text('Aktivitas: ${j.activity}', style: const TextStyle(fontSize: 11, color: AppColors.gray600)),
                        if (j.notes != null && j.notes!.isNotEmpty)
                          Text('Catatan: ${j.notes}', style: const TextStyle(fontSize: 11, fontStyle: FontStyle.italic, color: AppColors.gray500)),
                        if (j.absentStudents.isNotEmpty) ...[
                          const SizedBox(height: 6),
                          Wrap(
                            spacing: 4,
                            children: j.absentStudents.map((abs) => Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: AppColors.red50,
                                borderRadius: BorderRadius.circular(4),
                                border: Border.all(color: AppColors.red100),
                              ),
                              child: Text(
                                '${abs['student_name']} (${abs['status']?.toUpperCase()})',
                                style: const TextStyle(fontSize: 10, color: AppColors.red500, fontWeight: FontWeight.w600),
                              ),
                            )).toList(),
                          ),
                        ],
                      ],
                    ),
                  );
                }).toList(),
              ),
            );
          },
        ),
      ],
    );
  }

  // ─── Stat Cards ─────────────────────────────────────────────────────────────
  Widget _buildStatGrid(GuruDashboard data) {
    final journalCount = data.totalJournals;
    final isHomeroom = data.isHomeroom;

    return Row(
      children: [
        if (isHomeroom) ...[
          Expanded(
            child: _StatCard(
              label:     'Total Siswa',
              value:     data.totalStudents.toString(),
              subtitle:  'Siswa Perwalian >',
              icon:      Icons.groups_rounded,
              iconColor: AppColors.blue600,
              iconBg:    AppColors.blue100,
              onTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => GuruAbsensiHarianScreen(
                      classes: const [],
                      initialClassId: data.homeroomClassId,
                    ),
                  ),
                );
              },
            ),
          ),
          const SizedBox(width: 12),
        ],
        Expanded(
          child: _StatCard(
            label:     'Jurnal Saya',
            value:     journalCount.toString(),
            subtitle:  'Histori Mengajar >',
            icon:      Icons.menu_book_rounded,
            iconColor: AppColors.emerald600,
            iconBg:    const Color(0xFFECFDF5),
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => const GuruTeachingSessionScreen(),
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  // ─── Quick Action Grid ────────────────────────────────────────────────────────

  // ─── Quick Action Grid ────────────────────────────────────────────────────────
  Widget _buildQuickActions() {
    final menuItems = [
      {
        'title': 'Sesi Mengajar',
        'subtitle': 'Absensi & Jurnal',
        'icon': Icons.timer_outlined,
        'color': AppColors.blue600,
        'bg': AppColors.blue50,
        'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruTeachingSessionScreen())),
      },
      {
        'title': 'Tujuan Pembelajaran',
        'subtitle': 'Kelola & Share TP',
        'icon': Icons.checklist_rounded,
        'color': const Color(0xFF9333EA),
        'bg': const Color(0xFFF3E8FF),
        'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruTpScreen())),
      },
      {
        'title': 'Input Nilai',
        'subtitle': 'Penilaian Siswa',
        'icon': Icons.assignment_turned_in_rounded,
        'color': AppColors.emerald600,
        'bg': const Color(0xFFECFDF5),
        'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruInputNilaiScreen())),
      },
      {
        'title': 'Kedisiplinan',
        'subtitle': 'Poin Pelanggaran',
        'icon': Icons.warning_amber_rounded,
        'color': AppColors.orange600,
        'bg': AppColors.orange50,
        'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruConductScreen(classes: []))),
      },
      {
        'title': 'Persetujuan Izin',
        'subtitle': 'Izin / Dispensasi',
        'icon': Icons.mark_email_unread_rounded,
        'color': AppColors.teal600,
        'bg': const Color(0xFFCCFBF1),
        'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruPermitScreen())),
      },
      {
        'title': 'Sarana Prasarana',
        'subtitle': 'Peminjaman Asset',
        'icon': Icons.inventory_2_rounded,
        'color': const Color(0xFF4F46E5),
        'bg': const Color(0xFFEEF2FF),
        'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruSarprasScreen())),
      },
      {
        'title': 'Layanan & Direktori',
        'subtitle': 'Wali Kelas, Ekstra, Piket',
        'icon': Icons.business_center_rounded,
        'color': AppColors.indigo600,
        'bg': const Color(0xFFE0E7FF),
        'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const GuruLayananScreen())),
      },
    ];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Menu Utama Guru',
          style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.gray800),
        ),
        const SizedBox(height: 10),
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            childAspectRatio: 2.3,
            crossAxisSpacing: 10,
            mainAxisSpacing: 10,
          ),
          itemCount: menuItems.length,
          itemBuilder: (context, i) {
            final item = menuItems[i];
            final color = item['color'] as Color;
            final bg = item['bg'] as Color;
            return GestureDetector(
              onTap: item['onTap'] as VoidCallback,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                decoration: BoxDecoration(
                  color: AppColors.white,
                  borderRadius: BorderRadius.circular(AppRadius.xl),
                  border: Border.all(color: AppColors.gray100),
                  boxShadow: AppShadow.sm,
                ),
                child: Row(
                  children: [
                    Container(
                      width: 36,
                      height: 38,
                      decoration: BoxDecoration(
                        color: bg,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Icon(item['icon'] as IconData, color: color, size: 20),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            item['title'] as String,
                            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.gray800),
                            maxLines: 1, overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 2),
                          Text(
                            item['subtitle'] as String,
                            style: const TextStyle(fontSize: 10, color: AppColors.gray400),
                            maxLines: 1, overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        ),
      ],
    );
  }

  // ─── Alert List ──────────────────────────────────────────────────────────────
  Widget _buildAlertList(GuruDashboard data) {
    final hasAlerts = data.recentAlerts.isNotEmpty;
    return Container(
      clipBehavior: Clip.antiAlias,
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppRadius.xl2),
        border: Border.all(color: AppColors.gray100),
        boxShadow: AppShadow.sm,
      ),
      child: Column(
        children: [
          // Header
          Container(
            padding: const EdgeInsets.fromLTRB(16, 14, 16, 12),
            decoration: BoxDecoration(
              color: hasAlerts ? AppColors.orange50 : AppColors.gray50,
            ),
            child: Row(
              children: [
                Icon(
                  Icons.warning_amber_rounded,
                  size: 16,
                  color: hasAlerts ? AppColors.orange600 : AppColors.gray400,
                ),
                const SizedBox(width: 8),
                Text(
                  'Siswa Catatan Negatif Terbanyak',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: hasAlerts ? const Color(0xFF9A3412) : AppColors.gray500,
                  ),
                ),
              ],
            ),
          ),
          const Divider(height: 1, color: AppColors.gray100),

          // Content
          if (!hasAlerts)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 24),
              child: Center(
                child: Text(
                  'Tidak ada alert poin kritis',
                  style: TextStyle(fontSize: 13, color: AppColors.gray400),
                ),
              ),
            )
          else
            ...data.recentAlerts.asMap().entries.map((e) {
              final isLast  = e.key == data.recentAlerts.length - 1;
              final alert   = e.value;
              return Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                decoration: BoxDecoration(
                  border: isLast
                      ? null
                      : const Border(bottom: BorderSide(color: AppColors.gray100, width: 0.5)),
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            alert.name,
                            style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                              color: AppColors.gray800,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            alert.schoolClass,
                            style: const TextStyle(fontSize: 11, color: AppColors.gray500),
                          ),
                        ],
                      ),
                    ),
                    Row(
                      children: [
                        Text(
                          '${alert.pelanggaranCount}',
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w800,
                            color: AppColors.red500,
                          ),
                        ),
                        const SizedBox(width: 4),
                        const Text(
                          'catatan negatif',
                          style: TextStyle(fontSize: 11, color: AppColors.gray400),
                        ),
                      ],
                    ),
                  ],
                ),
              );
            }),
        ],
      ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Column(
        children: [
          const SizedBox(height: 48),
          const Icon(Icons.cloud_off_rounded, size: 48, color: AppColors.gray400),
          const SizedBox(height: 12),
          const Text('Gagal memuat data', style: TextStyle(fontSize: 14, color: AppColors.gray600)),
          const SizedBox(height: 8),
          TextButton(onPressed: _load, child: const Text('Coba Lagi')),
        ],
      ),
    );
  }

  // ─── Jadwal Mengajar Guru (Hari Ini & Perminggu) ─────────────────────────
  Widget _buildTodayScheduleCard(BuildContext context, GuruDashboard data) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppRadius.xl2),
        border: Border.all(color: AppColors.blue100),
        boxShadow: AppShadow.sm,
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Card
          Material(
            color: Colors.transparent,
            child: InkWell(
              onTap: () => _showWeeklyScheduleModal(context, data),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    colors: [Color(0xFF2563EB), Color(0xFF4F46E5)],
                    begin: Alignment.centerLeft,
                    end: Alignment.centerRight,
                  ),
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(7),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.2),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Icon(Icons.calendar_today_rounded, color: Colors.white, size: 18),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Jadwal Mengajar Hari Ini (${data.todayDayName})',
                            style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                          const Text(
                            'Tap untuk melihat jadwal perminggu',
                            style: TextStyle(fontSize: 10, color: Color(0xFFDBEAFE)),
                          ),
                        ],
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.2),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.white.withValues(alpha: 0.3)),
                      ),
                      child: const Row(
                        children: [
                          Text('🗓️ Perminggu', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.white)),
                          SizedBox(width: 2),
                          Icon(Icons.chevron_right_rounded, size: 14, color: Colors.white),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),

          // Body Content
          Padding(
            padding: const EdgeInsets.all(12),
            child: data.todaySchedules.isNotEmpty
                ? Column(
                    children: data.todaySchedules.map((sch) {
                      return InkWell(
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => GuruTeachingSessionScreen(
                                initialClassId: sch.classId,
                                initialSubjectId: sch.subjectId,
                                initialPeriod: sch.period,
                                initialPeriods: sch.periods.isNotEmpty ? sch.periods : [sch.period],
                              ),
                            ),
                          );
                        },
                        borderRadius: BorderRadius.circular(14),
                        child: Container(
                          margin: const EdgeInsets.only(bottom: 10),
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: AppColors.blue50.withValues(alpha: 0.7),
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: AppColors.blue200, width: 1.2),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Wrap(
                                    spacing: 6,
                                    runSpacing: 4,
                                    children: (sch.periods.isNotEmpty ? sch.periods : [sch.period]).map((p) => Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4.5),
                                      decoration: BoxDecoration(
                                        color: AppColors.blue600,
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: Text(
                                        'Jam ke-$p',
                                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Colors.white),
                                      ),
                                    )).toList(),
                                  ),
                                  Text(
                                    '${sch.startTime} - ${sch.endTime}',
                                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w900, color: AppColors.blue800),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 8),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    '🏫 Kelas ${sch.className}',
                                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: Color(0xFF1E1B4B)),
                                  ),
                                  if (sch.room != null && sch.room!.isNotEmpty)
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                                      decoration: BoxDecoration(
                                        color: Colors.white,
                                        borderRadius: BorderRadius.circular(6),
                                        border: Border.all(color: AppColors.gray200),
                                      ),
                                      child: Text(
                                        '📍 ${sch.room}',
                                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.gray600),
                                      ),
                                    ),
                                ],
                              ),
                              const SizedBox(height: 4),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    '📚 ${sch.subjectName}',
                                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.gray800),
                                  ),
                                  const Row(
                                    children: [
                                      Text(
                                        'Isi Jurnal & Absen',
                                        style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.blue600),
                                      ),
                                      SizedBox(width: 2),
                                      Icon(Icons.arrow_forward_ios_rounded, size: 10, color: AppColors.blue600),
                                    ],
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      );
                    }).toList(),
                  )
                : Padding(
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    child: Center(
                      child: Column(
                        children: [
                          Text(
                            'Tidak ada jadwal mengajar pada hari ${data.todayDayName}.',
                            style: const TextStyle(fontSize: 12, color: AppColors.gray500),
                          ),
                          const SizedBox(height: 4),
                          GestureDetector(
                            onTap: () => _showWeeklyScheduleModal(context, data),
                            child: const Text(
                              'Lihat Jadwal Mengajar Hari Lain (Perminggu) →',
                              style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.blue600),
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

  void _showWeeklyScheduleModal(BuildContext context, GuruDashboard data) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        final days = [
          {'day': 1, 'name': 'Senin'},
          {'day': 2, 'name': 'Selasa'},
          {'day': 3, 'name': 'Rabu'},
          {'day': 4, 'name': 'Kamis'},
          {'day': 5, 'name': 'Jumat'},
          {'day': 6, 'name': 'Sabtu'},
        ];

        final todayWeekday = DateTime.now().weekday;
        final initialIndex = (todayWeekday >= 1 && todayWeekday <= 6) ? todayWeekday - 1 : 0;

        return DefaultTabController(
          length: 6,
          initialIndex: initialIndex,
          child: SizedBox(
            height: MediaQuery.of(ctx).size.height * 0.82,
            child: Column(
              children: [
                // Modal Header
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                  decoration: const BoxDecoration(
                    color: Color(0xFF0F172A),
                    borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
                  ),
                  child: Row(
                    children: [
                      const Text('📅 ', style: TextStyle(fontSize: 18)),
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Jadwal Mengajar Perminggu',
                              style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Colors.white),
                            ),
                            Text(
                              'Senin - Sabtu',
                              style: TextStyle(fontSize: 11, color: Color(0xFF94A3B8)),
                            ),
                          ],
                        ),
                      ),
                      IconButton(
                        onPressed: () => Navigator.pop(ctx),
                        icon: const Icon(Icons.close_rounded, color: Colors.white, size: 20),
                        padding: EdgeInsets.zero,
                        constraints: const BoxConstraints(),
                      ),
                    ],
                  ),
                ),

                // Tab Bar
                Container(
                  color: AppColors.gray50,
                  child: TabBar(
                    isScrollable: true,
                    labelColor: AppColors.blue600,
                    unselectedLabelColor: AppColors.gray500,
                    indicatorColor: AppColors.blue600,
                    indicatorWeight: 3,
                    labelStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                    tabs: days.map((d) {
                      final dayNum = d['day'] as int;
                      final dName  = d['name'] as String;
                      final grp = data.weeklySchedules.firstWhere(
                        (g) => g.day == dayNum,
                        orElse: () => WeeklyScheduleGroup(day: dayNum, dayName: dName, count: 0, schedules: const []),
                      );
                      return Tab(
                        child: Row(
                          children: [
                            Text(dName),
                            if (grp.count > 0) ...[
                              const SizedBox(width: 4),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                                decoration: BoxDecoration(
                                  color: AppColors.blue100,
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  '${grp.count}',
                                  style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.blue700),
                                ),
                              ),
                            ],
                          ],
                        ),
                      );
                    }).toList(),
                  ),
                ),

                // Tab Content
                Expanded(
                  child: TabBarView(
                    children: days.map((d) {
                      final dayNum = d['day'] as int;
                      final dName  = d['name'] as String;
                      final grp = data.weeklySchedules.firstWhere(
                        (g) => g.day == dayNum,
                        orElse: () => WeeklyScheduleGroup(day: dayNum, dayName: dName, count: 0, schedules: const []),
                      );

                      if (grp.schedules.isEmpty) {
                        return Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              const Icon(Icons.free_breakfast_outlined, size: 40, color: AppColors.gray400),
                              const SizedBox(height: 8),
                              Text(
                                'Tidak ada jadwal mengajar pada hari $dName.',
                                style: const TextStyle(fontSize: 13, color: AppColors.gray500),
                              ),
                            ],
                          ),
                        );
                      }

                      return ListView.separated(
                        padding: const EdgeInsets.all(16),
                        itemCount: grp.schedules.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 10),
                        itemBuilder: (_, idx) {
                          final sch = grp.schedules[idx];
                          return InkWell(
                            onTap: () {
                              Navigator.pop(ctx);
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => GuruTeachingSessionScreen(
                                    initialClassId: sch.classId,
                                    initialSubjectId: sch.subjectId,
                                    initialPeriod: sch.period,
                                    initialPeriods: sch.periods.isNotEmpty ? sch.periods : [sch.period],
                                  ),
                                ),
                              );
                            },
                            borderRadius: BorderRadius.circular(14),
                            child: Container(
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: AppColors.white,
                                borderRadius: BorderRadius.circular(14),
                                border: Border.all(color: AppColors.gray200),
                                boxShadow: AppShadow.sm,
                              ),
                              child: Row(
                                children: [
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                    decoration: BoxDecoration(
                                      color: AppColors.blue100,
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                    child: Column(
                                      children: [
                                        const Text('Jam', style: TextStyle(fontSize: 9, color: AppColors.blue700, fontWeight: FontWeight.w600)),
                                        Text(
                                          '${sch.period}',
                                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.blue800),
                                        ),
                                      ],
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          sch.subjectName,
                                          style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.gray800),
                                        ),
                                        const SizedBox(height: 2),
                                        Text(
                                          'Kelas ${sch.className}${sch.room != null ? ' • Ruang ${sch.room}' : ''}',
                                          style: const TextStyle(fontSize: 12, color: AppColors.gray600),
                                        ),
                                        const SizedBox(height: 2),
                                        Text(
                                          '⏱️ ${sch.startTime} - ${sch.endTime}',
                                          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.blue600),
                                        ),
                                      ],
                                    ),
                                  ),
                                  const Icon(Icons.arrow_forward_ios_rounded, size: 14, color: AppColors.gray400),
                                ],
                              ),
                            ),
                          );
                        },
                      );
                    }).toList(),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}

// ─── Stat Card ───────────────────────────────────────────────────────────────

class _StatCard extends StatelessWidget {
  final String       label;
  final String       value;
  final String       subtitle;
  final IconData     icon;
  final Color        iconColor;
  final Color        iconBg;
  final bool         highlight;
  final VoidCallback? onTap;

  const _StatCard({
    required this.label,
    required this.value,
    required this.subtitle,
    required this.icon,
    required this.iconColor,
    required this.iconBg,
    this.highlight = false,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(AppRadius.xl),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: highlight ? AppColors.orange50 : AppColors.white,
            borderRadius: BorderRadius.circular(AppRadius.xl),
            border: Border.all(
              color: highlight
                  ? AppColors.orange500.withValues(alpha: 0.4)
                  : AppColors.gray100,
            ),
            boxShadow: AppShadow.sm,
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Flexible(
                    child: Text(
                      label,
                      style: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w500,
                        color: AppColors.gray500,
                      ),
                    ),
                  ),
                  Container(
                    width: 32,
                    height: 32,
                    decoration: BoxDecoration(
                      color: iconBg,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(icon, color: iconColor, size: 16),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              Text(
                value,
                style: const TextStyle(
                  fontSize: 26,
                  fontWeight: FontWeight.w800,
                  color: AppColors.gray800,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                subtitle,
                style: TextStyle(fontSize: 11, color: iconColor),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ─── Quick Action Chip ────────────────────────────────────────────────────────

class _QuickActionChip extends StatelessWidget {
  final String   label;
  final IconData icon;
  final Color    color;
  final Color    bg;
  final bool     comingSoon;

  const _QuickActionChip({
    required this.label,
    required this.icon,
    required this.color,
    required this.bg,
    this.comingSoon = false,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: null,
      child: Opacity(
        opacity: comingSoon ? 0.55 : 1.0,
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          decoration: BoxDecoration(
            color: bg,
            borderRadius: BorderRadius.circular(AppRadius.xl),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, size: 14, color: color),
              const SizedBox(width: 6),
              Text(
                label,
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: color,
                ),
              ),
              if (comingSoon) ...[
                const SizedBox(width: 6),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    'Segera',
                    style: TextStyle(fontSize: 9, fontWeight: FontWeight.w700, color: color),
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

void _showPendingExtraMembersModal(BuildContext context) {
  showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    backgroundColor: Colors.transparent,
    builder: (ctx) => const _PendingExtraMembersSheet(),
  );
}

class _PendingExtraMembersSheet extends StatefulWidget {
  const _PendingExtraMembersSheet();

  @override
  State<_PendingExtraMembersSheet> createState() => _PendingExtraMembersSheetState();
}

class _PendingExtraMembersSheetState extends State<_PendingExtraMembersSheet> {
  bool _loading = true;
  String? _error;
  List<dynamic> _items = [];

  @override
  void initState() {
    super.initState();
    _fetch();
  }

  Future<void> _fetch() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final res = await ApiClient.get('/guru/extracurriculars/pending-members');
      if (mounted) {
        setState(() {
          _items = res['pending_members'] ?? [];
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = 'Gagal memuat daftar pengajuan.';
          _loading = false;
        });
      }
    }
  }

  Future<void> _process(int id, bool approve) async {
    final endpoint = approve ? '/approve' : '/reject';
    try {
      final res = await ApiClient.post('/guru/extracurriculars/members/$id$endpoint');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Berhasil diproses.'),
            backgroundColor: approve ? AppColors.emerald600 : AppColors.rose600,
          ),
        );
        _fetch();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Gagal memproses pengajuan.'), backgroundColor: AppColors.rose600),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      constraints: BoxConstraints(
        maxHeight: MediaQuery.of(context).size.height * 0.8,
      ),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      padding: EdgeInsets.fromLTRB(20, 16, 20, MediaQuery.of(context).viewInsets.bottom + 20),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Center(
            child: Container(
              width: 36, height: 4,
              decoration: BoxDecoration(color: AppColors.gray300, borderRadius: BorderRadius.circular(2)),
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: const [
              Icon(Icons.how_to_reg_rounded, color: AppColors.blue800, size: 22),
              SizedBox(width: 8),
              Text(
                'Persetujuan Anggota Ekstra',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.gray900),
              ),
            ],
          ),
          const SizedBox(height: 16),
          if (_loading)
            const Padding(
              padding: EdgeInsets.all(32),
              child: Center(child: CircularProgressIndicator()),
            )
          else if (_error != null)
            Center(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Text(_error!, style: const TextStyle(color: AppColors.rose600, fontSize: 13)),
              ),
            )
          else if (_items.isEmpty)
            const Padding(
              padding: EdgeInsets.all(32),
              child: Center(
                child: Text(
                  'Tidak ada pengajuan pendaftaran/keluar ekstra yang menanti.',
                  style: TextStyle(color: AppColors.gray500, fontSize: 13),
                  textAlign: TextAlign.center,
                ),
              ),
            )
          else
            Flexible(
              child: ListView.separated(
                shrinkWrap: true,
                itemCount: _items.length,
                separatorBuilder: (_, __) => const SizedBox(height: 12),
                itemBuilder: (ctx, i) {
                  final item = _items[i];
                  final isJoin = item['status'] == 'pending_join';

                  return Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: AppColors.gray50,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: AppColors.gray200),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Expanded(
                              child: Text(
                                item['student_name'] ?? '—',
                                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.gray900),
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(
                                color: isJoin ? AppColors.amber50 : AppColors.rose50,
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(color: isJoin ? AppColors.amber200 : AppColors.rose200),
                              ),
                              child: Text(
                                item['status_label'] ?? 'Pending',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                  color: isJoin ? AppColors.amber800 : AppColors.rose800,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Kelas: ${item['class_name']} • Ekstra: ${item['extracurricular_name']}',
                          style: const TextStyle(fontSize: 12, color: AppColors.gray600),
                        ),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Expanded(
                              child: OutlinedButton(
                                onPressed: () => _process(item['id'], false),
                                style: OutlinedButton.styleFrom(
                                  foregroundColor: AppColors.rose600,
                                  side: const BorderSide(color: AppColors.rose300),
                                  padding: const EdgeInsets.symmetric(vertical: 8),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                ),
                                child: const Text('Tolak', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: ElevatedButton(
                                onPressed: () => _process(item['id'], true),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: AppColors.emerald600,
                                  foregroundColor: Colors.white,
                                  padding: const EdgeInsets.symmetric(vertical: 8),
                                  elevation: 0,
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                ),
                                child: const Text('Setujui', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  );
                },
              ),
            ),
        ],
      ),
    );
  }
}
