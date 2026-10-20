import 'package:flutter/material.dart';
import '../../models/chat_models.dart';
import '../../services/chat_api_service.dart';
import '../../theme/app_colors.dart';
import 'chat_room_screen.dart';

class ChatListScreen extends StatefulWidget {
  final String? filterType; // 'trio' | 'parent_teacher' | null

  const ChatListScreen({super.key, this.filterType});

  @override
  State<ChatListScreen> createState() => _ChatListScreenState();
}

class _ChatListScreenState extends State<ChatListScreen> {
  List<ChatRoomModel> _rooms = [];
  bool _loading = true;
  String _activeTab = 'all'; // 'all' | 'trio' | 'parent_teacher'
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _activeTab = widget.filterType ?? 'all';
    _loadRooms();
  }

  Future<void> _loadRooms() async {
    setState(() => _loading = true);
    final rooms = await ChatApiService.getRooms(
      type: _activeTab == 'all' ? null : _activeTab,
    );
    if (mounted) {
      setState(() {
        _rooms = rooms;
        _loading = false;
      });
    }
  }

  List<ChatRoomModel> get _filteredRooms {
    if (_searchQuery.trim().isEmpty) return _rooms;
    final q = _searchQuery.toLowerCase();
    return _rooms.where((r) {
      return r.name.toLowerCase().contains(q) ||
          r.studentName.toLowerCase().contains(q) ||
          r.homeroomTeacherName.toLowerCase().contains(q) ||
          r.className.toLowerCase().contains(q);
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text(
          'Konsultasi Perwalian',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.white),
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
      body: Column(
        children: [
          // ─── Search Bar ───
          Container(
            color: const Color(0xFF0F2460),
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(12),
              ),
              child: TextField(
                onChanged: (val) => setState(() => _searchQuery = val),
                style: const TextStyle(color: Colors.white, fontSize: 13),
                decoration: const InputDecoration(
                  icon: Icon(Icons.search_rounded, color: Colors.white70, size: 20),
                  hintText: 'Cari nama siswa atau kelas...',
                  hintStyle: TextStyle(color: Colors.white54, fontSize: 12),
                  border: InputBorder.none,
                  enabledBorder: InputBorder.none,
                  focusedBorder: InputBorder.none,
                  isDense: true,
                  contentPadding: EdgeInsets.symmetric(vertical: 10),
                ),
              ),
            ),
          ),

          // ─── Filter Tabs (Semua, Trio, Privat) ───
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            color: Colors.white,
            child: Row(
              children: [
                _buildTab('all', 'Semua'),
                const SizedBox(width: 8),
                _buildTab('trio', 'Trio (Siswa & Ortu)'),
                const SizedBox(width: 8),
                _buildTab('parent_teacher', 'Privat Ortu'),
              ],
            ),
          ),
          const Divider(height: 1, color: AppColors.gray200),

          // ─── Rooms List ───
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _filteredRooms.isEmpty
                    ? _buildEmptyState()
                    : RefreshIndicator(
                        onRefresh: _loadRooms,
                        child: ListView.separated(
                          padding: const EdgeInsets.symmetric(vertical: 8),
                          itemCount: _filteredRooms.length,
                          separatorBuilder: (_, __) => const Divider(height: 1, indent: 74, color: AppColors.gray100),
                          itemBuilder: (ctx, idx) => _buildRoomTile(_filteredRooms[idx]),
                        ),
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildTab(String key, String label) {
    final active = _activeTab == key;
    return GestureDetector(
      onTap: () {
        if (_activeTab != key) {
          setState(() => _activeTab = key);
          _loadRooms();
        }
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: active ? const Color(0xFF0F2460) : AppColors.gray100,
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.bold,
            color: active ? Colors.white : AppColors.gray600,
          ),
        ),
      ),
    );
  }

  Widget _buildRoomTile(ChatRoomModel room) {
    final hasUnread = room.unreadCount > 0;

    return InkWell(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => ChatRoomScreen(roomId: room.id, initialTitle: room.name),
          ),
        ).then((_) => _loadRooms());
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        decoration: BoxDecoration(
          color: hasUnread ? const Color(0xFFEFF6FF) : Colors.white,
          border: Border(
            left: BorderSide(
              color: hasUnread ? const Color(0xFF2563EB) : const Color(0xFFE2E8F0),
              width: hasUnread ? 5.0 : 2.5,
            ),
            bottom: const BorderSide(color: Color(0xFFF1F5F9), width: 1.0),
          ),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            // Avatar
            CircleAvatar(
              radius: 24,
              backgroundColor: room.isPrivate ? const Color(0xFFFEF3C7) : AppColors.blue100,
              backgroundImage: room.studentPhoto != null ? NetworkImage(room.studentPhoto!) : null,
              child: room.studentPhoto == null
                  ? Icon(
                      room.isPrivate ? Icons.lock_person_rounded : Icons.groups_rounded,
                      color: room.isPrivate ? const Color(0xFFB45309) : AppColors.blue700,
                      size: 24,
                    )
                  : null,
            ),
            const SizedBox(width: 12),

            // Content
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          room.studentName,
                          style: TextStyle(
                            fontSize: 14.5,
                            fontWeight: hasUnread ? FontWeight.w900 : FontWeight.w700,
                            color: hasUnread ? const Color(0xFF0F172A) : AppColors.gray800,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      if (room.lastTimeHuman.isNotEmpty)
                        Text(
                          room.lastTimeHuman,
                          style: TextStyle(
                            fontSize: 10.5,
                            color: hasUnread ? const Color(0xFF2563EB) : AppColors.gray400,
                            fontWeight: hasUnread ? FontWeight.w900 : FontWeight.normal,
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 2),

                  // Detail Wali Kelas & Kelas
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                        decoration: BoxDecoration(
                          color: AppColors.gray100,
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          room.className.isNotEmpty ? room.className : 'Kelas',
                          style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.bold, color: AppColors.gray700),
                        ),
                      ),
                      const SizedBox(width: 6),
                      if (room.isPrivate) ...[
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFEF3C7),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: const Text(
                            'Privat Ortu-Wali',
                            style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.bold, color: Color(0xFFB45309)),
                          ),
                        ),
                        const SizedBox(width: 6),
                      ],
                      Expanded(
                        child: Text(
                          'Wali: ${room.homeroomTeacherName}',
                          style: const TextStyle(fontSize: 10.5, color: Color(0xFF6D28D9), fontWeight: FontWeight.w600),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 5),

                  // Last Message Snippet
                  Row(
                    children: [
                      if (!hasUnread && room.lastMessage.isNotEmpty) ...[
                        const Icon(Icons.done_all_rounded, size: 14, color: Color(0xFF0284C7)),
                        const SizedBox(width: 4),
                      ],
                      Expanded(
                        child: Text(
                          room.lastMessage.isNotEmpty ? room.lastMessage : 'Belum ada percakapan',
                          style: TextStyle(
                            fontSize: 12,
                            color: hasUnread ? const Color(0xFF0F172A) : AppColors.gray500,
                            fontWeight: hasUnread ? FontWeight.w800 : FontWeight.normal,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      if (hasUnread) ...[
                        const SizedBox(width: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2.5),
                          decoration: BoxDecoration(
                            color: const Color(0xFF25D366), // WhatsApp green unread badge
                            borderRadius: BorderRadius.circular(10),
                            boxShadow: const [
                              BoxShadow(color: Color(0x3325D366), blurRadius: 4, offset: Offset(0, 1)),
                            ],
                          ),
                          child: Text(
                            '${room.unreadCount} BARU',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 9.5,
                              fontWeight: FontWeight.w900,
                              letterSpacing: 0.3,
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return const Center(
      child: Padding(
        padding: EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.chat_bubble_outline_rounded, size: 54, color: AppColors.gray300),
            SizedBox(height: 12),
            Text(
              'Tidak Ada Ruang Konsultasi',
              style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.gray700),
            ),
            SizedBox(height: 4),
            Text(
              'Ruang obrolan otomatis dibuat untuk setiap siswa perwalian dan orang tua terdaftar.',
              style: TextStyle(fontSize: 12, color: AppColors.gray400),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}
