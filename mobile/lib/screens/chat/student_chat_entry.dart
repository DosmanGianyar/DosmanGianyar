import 'package:flutter/material.dart';
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

  @override
  void initState() {
    super.initState();
    _loadStudentRoom();
  }

  Future<void> _loadStudentRoom() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    final rooms = await ChatApiService.getRooms(type: 'trio');
    if (!mounted) return;

    if (rooms.isNotEmpty) {
      final room = rooms.first;
      // Langsung buka ruang obrolan siswa
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(
          builder: (_) => ChatRoomScreen(
            roomId: room.id,
            initialTitle: room.name,
          ),
        ),
      );
    } else {
      setState(() {
        _loading = false;
        _error = 'Ruang konsultasi segitiga belum siap. Pastikan Anda telah terdaftar dalam kelas yang memiliki Wali Kelas.';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Konsultasi Wali & Ortu', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16)),
        backgroundColor: const Color(0xFF0F2460),
        foregroundColor: Colors.white,
      ),
      body: Center(
        child: _loading
            ? const Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircularProgressIndicator(),
                  SizedBox(height: 16),
                  Text('Menghubungkan ke ruang konsultasi...', style: TextStyle(fontSize: 13, color: AppColors.gray600)),
                ],
              )
            : Padding(
                padding: const EdgeInsets.all(32),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.info_outline_rounded, size: 56, color: Color(0xFFD97706)),
                    const SizedBox(height: 16),
                    Text(
                      _error ?? 'Ruang obrolan tidak ditemukan.',
                      style: const TextStyle(fontSize: 14, color: AppColors.gray700, height: 1.4),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 20),
                    FilledButton.icon(
                      onPressed: _loadStudentRoom,
                      icon: const Icon(Icons.refresh_rounded, size: 18),
                      label: const Text('Coba Lagi'),
                      style: FilledButton.styleFrom(backgroundColor: const Color(0xFF0F2460)),
                    ),
                  ],
                ),
              ),
      ),
    );
  }
}
