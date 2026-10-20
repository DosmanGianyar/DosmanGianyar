import 'package:flutter/material.dart';
import '../../models/chat_models.dart';
import '../../services/chat_api_service.dart';
import '../../theme/app_colors.dart';
import 'chat_room_screen.dart';

class StudentChatEntryScreen extends StatefulWidget {
  const StudentChatEntryScreen({super.key});

  @override
  State<StudentChatEntryScreen> createState() => _StudentChatEntryScreenState();
}

class _StudentChatEntryScreenState extends State<StudentChatEntryScreen> {
  bool _loading = true;
  String? _error;
  ChatRoomModel? _classGroupRoom;
  ChatRoomModel? _trioRoom;

  @override
  void initState() {
    super.initState();
    _loadRooms();
  }

  Future<void> _loadRooms() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final rooms = await ChatApiService.getRooms(type: 'all');
      if (!mounted) return;

      ChatRoomModel? group;
      ChatRoomModel? trio;

      for (final r in rooms) {
        if (r.isClassGroup && group == null) {
          group = r;
        } else if (!r.isPrivate && !r.isClassGroup && trio == null) {
          trio = r;
        }
      }

      setState(() {
        _classGroupRoom = group;
        _trioRoom = trio;
        _loading = false;
        if (group == null && trio == null) {
          _error = 'Belum ada ruang obrolan aktif. Pastikan Anda terdaftar dalam kelas yang memiliki Wali Kelas.';
        }
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _loading = false;
        _error = 'Gagal memuat ruang obrolan: $e';
      });
    }
  }

  void _openRoom(ChatRoomModel room) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => ChatRoomScreen(
          roomId: room.id,
          initialTitle: room.name,
        ),
      ),
    ).then((_) => _loadRooms());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text(
          'Ruang Obrolan Siswa',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16),
        ),
        backgroundColor: const Color(0xFF0F2460),
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Segarkan',
            onPressed: _loadRooms,
          ),
        ],
      ),
      body: _loading
          ? const Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircularProgressIndicator(),
                  SizedBox(height: 16),
                  Text(
                    'Menghubungkan ke ruang obrolan...',
                    style: TextStyle(fontSize: 13, color: AppColors.gray600),
                  ),
                ],
              ),
            )
          : RefreshIndicator(
              onRefresh: _loadRooms,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 18),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // ─── Header Card ───
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Color(0xFF0F2460), Color(0xFF1E3A8A)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(16),
                        boxShadow: [
                          BoxShadow(
                            color: const Color(0xFF0F2460).withValues(alpha: 0.20),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                  decoration: BoxDecoration(
                                    color: Colors.white.withValues(alpha: 0.15),
                                    borderRadius: BorderRadius.circular(12),
                                    border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
                                  ),
                                  child: const Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Text('💬', style: TextStyle(fontSize: 10)),
                                      SizedBox(width: 4),
                                      Text(
                                        'Komunikasi Terbina & Terarah',
                                        style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                                      ),
                                    ],
                                  ),
                                ),
                                const SizedBox(height: 8),
                                const Text(
                                  'Pilih Ruang Obrolan',
                                  style: TextStyle(color: Colors.white, fontSize: 17, fontWeight: FontWeight.bold),
                                ),
                                const SizedBox(height: 4),
                                const Text(
                                  'Diskusikan pelajaran bersama sekelas atau lakukan bimbingan perwalian bersama orang tua & wali kelas.',
                                  style: TextStyle(color: Color(0xFFCBD5E1), fontSize: 11.5, height: 1.35),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(width: 12),
                          Container(
                            width: 52,
                            height: 52,
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(14),
                              border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
                            ),
                            child: const Center(
                              child: Text('👨‍🎓', style: TextStyle(fontSize: 26)),
                            ),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 20),

                    if (_error != null && _classGroupRoom == null && _trioRoom == null) ...[
                      Container(
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: AppColors.gray200),
                        ),
                        child: Column(
                          children: [
                            const Icon(Icons.info_outline_rounded, size: 48, color: Color(0xFFD97706)),
                            const SizedBox(height: 12),
                            Text(
                              _error!,
                              style: const TextStyle(fontSize: 13, color: AppColors.gray700, height: 1.4),
                              textAlign: TextAlign.center,
                            ),
                            const SizedBox(height: 16),
                            FilledButton.icon(
                              onPressed: _loadRooms,
                              icon: const Icon(Icons.refresh_rounded, size: 16),
                              label: const Text('Coba Lagi'),
                              style: FilledButton.styleFrom(backgroundColor: const Color(0xFF0F2460)),
                            ),
                          ],
                        ),
                      ),
                    ] else ...[
                      // ─── Card 1: Grup Kelas ───
                      if (_classGroupRoom != null)
                        _buildRoomOptionCard(
                          room: _classGroupRoom!,
                          isGroup: true,
                          title: _classGroupRoom!.name,
                          badgeText: '👥 Grup Seluruh Kelas',
                          badgeBg: const Color(0xFFD1FAE5),
                          badgeColor: const Color(0xFF047857),
                          subtitle: 'Diskusi & informasi terbuka bersama seluruh teman satu kelas dan dipantau oleh Wali Kelas.',
                          accentColor: const Color(0xFF059669),
                          iconData: Icons.groups_3_rounded,
                        )
                      else
                        _buildUnavailableCard(
                          title: 'Grup Kelas',
                          subtitle: 'Grup kelas belum tersedia. Hubungi admin untuk penetapan kelas.',
                          iconData: Icons.groups_3_rounded,
                          accentColor: AppColors.gray400,
                        ),

                      const SizedBox(height: 14),

                      // ─── Card 2: Konsultasi Segitiga ───
                      if (_trioRoom != null)
                        _buildRoomOptionCard(
                          room: _trioRoom!,
                          isGroup: false,
                          title: 'Konsultasi Segitiga',
                          badgeText: '👨‍👩‍👧 Siswa · Ortu · Wali',
                          badgeBg: const Color(0xFFEFF6FF),
                          badgeColor: const Color(0xFF1D4ED8),
                          subtitle: 'Ruang khusus perwalian pribadi antara Anda, Orang Tua Anda, dan Wali Kelas.',
                          accentColor: const Color(0xFF2563EB),
                          iconData: Icons.family_restroom_rounded,
                        )
                      else
                        _buildUnavailableCard(
                          title: 'Konsultasi Segitiga',
                          subtitle: 'Ruang konsultasi perwalian belum tersedia.',
                          iconData: Icons.family_restroom_rounded,
                          accentColor: AppColors.gray400,
                        ),
                    ],

                    const SizedBox(height: 24),

                    // ─── Security Policy Note ───
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFEF3C7).withValues(alpha: 0.6),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: const Color(0xFFFDE68A)),
                      ),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            padding: const EdgeInsets.all(6),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF59E0B).withValues(alpha: 0.2),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Icon(Icons.shield_rounded, size: 20, color: Color(0xFFB45309)),
                          ),
                          const SizedBox(width: 12),
                          const Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Kebijakan Keamanan Sekolah',
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                    color: Color(0xFF92400E),
                                  ),
                                ),
                                SizedBox(height: 4),
                                Text(
                                  'Demi mencegah perundungan (bullying) dan menjaga etika digital, obrolan privat antar-siswa ditiadakan. Seluruh komunikasi dilakukan di Grup Kelas resmi bersama Wali Kelas.',
                                  style: TextStyle(
                                    fontSize: 11,
                                    color: Color(0xFF78350F),
                                    height: 1.4,
                                  ),
                                ),
                              ],
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

  Widget _buildRoomOptionCard({
    required ChatRoomModel room,
    required bool isGroup,
    required String title,
    required String badgeText,
    required Color badgeBg,
    required Color badgeColor,
    required String subtitle,
    required Color accentColor,
    required IconData iconData,
  }) {
    final hasUnread = room.unreadCount > 0;

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: hasUnread ? accentColor.withValues(alpha: 0.5) : const Color(0xFFE2E8F0),
          width: hasUnread ? 1.5 : 1.0,
        ),
        boxShadow: [
          BoxShadow(
            color: hasUnread ? accentColor.withValues(alpha: 0.08) : Colors.black.withValues(alpha: 0.03),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: () => _openRoom(room),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Avatar Icon
                    Container(
                      width: 48,
                      height: 48,
                      decoration: BoxDecoration(
                        color: badgeBg,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(iconData, color: badgeColor, size: 26),
                    ),
                    const SizedBox(width: 14),

                    // Title & Badges
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2.5),
                                decoration: BoxDecoration(
                                  color: badgeBg,
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  badgeText,
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                    color: badgeColor,
                                  ),
                                ),
                              ),
                              const Spacer(),
                              if (hasUnread)
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                  decoration: BoxDecoration(
                                    color: AppColors.red500,
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Text(
                                    '${room.unreadCount} baru',
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 10,
                                      fontWeight: FontWeight.w900,
                                    ),
                                  ),
                                ),
                            ],
                          ),
                          const SizedBox(height: 6),
                          Text(
                            title,
                            style: const TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w800,
                              color: AppColors.gray900,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 10),
                Text(
                  subtitle,
                  style: const TextStyle(fontSize: 12, color: AppColors.gray600, height: 1.35),
                ),

                // Last Message Preview if exists
                if (room.lastMessage.isNotEmpty) ...[
                  const SizedBox(height: 10),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    decoration: BoxDecoration(
                      color: AppColors.gray50,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: AppColors.gray100),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          Icons.chat_bubble_outline_rounded,
                          size: 13,
                          color: hasUnread ? accentColor : AppColors.gray400,
                        ),
                        const SizedBox(width: 6),
                        Expanded(
                          child: Text(
                            room.lastMessage,
                            style: TextStyle(
                              fontSize: 11,
                              color: hasUnread ? AppColors.gray800 : AppColors.gray500,
                              fontWeight: hasUnread ? FontWeight.w700 : FontWeight.normal,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        if (room.lastTimeHuman.isNotEmpty) ...[
                          const SizedBox(width: 6),
                          Text(
                            room.lastTimeHuman,
                            style: TextStyle(
                              fontSize: 10,
                              color: hasUnread ? accentColor : AppColors.gray400,
                              fontWeight: hasUnread ? FontWeight.w700 : FontWeight.normal,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                ],

                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    Text(
                      'Buka Obrolan',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: accentColor,
                      ),
                    ),
                    const SizedBox(width: 4),
                    Icon(Icons.arrow_forward_rounded, size: 14, color: accentColor),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildUnavailableCard({
    required String title,
    required String subtitle,
    required IconData iconData,
    required Color accentColor,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.gray50,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.gray200),
      ),
      child: Row(
        children: [
          Icon(iconData, size: 32, color: accentColor),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.gray600),
                ),
                const SizedBox(height: 2),
                Text(
                  subtitle,
                  style: const TextStyle(fontSize: 11, color: AppColors.gray500),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
