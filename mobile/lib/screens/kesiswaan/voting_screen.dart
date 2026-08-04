import 'package:flutter/material.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';
import 'voting_detail_screen.dart';

class VotingScreen extends StatefulWidget {
  const VotingScreen({super.key});

  @override
  State<VotingScreen> createState() => _VotingScreenState();
}

class _VotingScreenState extends State<VotingScreen> {
  bool _loading = true;
  bool _isEvotingActive = true;
  String? _errorMessage;
  List<Map<String, dynamic>> _sessions = [];

  @override
  void initState() {
    super.initState();
    _loadSessions();
  }

  Future<void> _loadSessions() async {
    setState(() {
      _loading = true;
      _errorMessage = null;
    });

    try {
      final res = await ApiClient.get('/siswa/voting');
      final active = (res['is_evoting_active'] ?? true) as bool;
      final rawList = (res['sessions'] as List<dynamic>? ?? []);

      setState(() {
        _isEvotingActive = active;
        if (!active) {
          _errorMessage = res['message'] ?? 'Fitur E-Voting saat ini sedang dinonaktifkan oleh Administrator.';
        }
        _sessions = List<Map<String, dynamic>>.from(rawList);
        _loading = false;
      });
    } catch (e) {
      setState(() {
        _errorMessage = 'Gagal memuat sesi E-Voting: $e';
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('E-Voting Siswa', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
        backgroundColor: Colors.white,
        elevation: 0.5,
        foregroundColor: AppColors.gray800,
      ),
      body: RefreshIndicator(
        onRefresh: _loadSessions,
        child: _buildBody(),
      ),
    );
  }

  Widget _buildBody() {
    if (_loading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (!_isEvotingActive || _errorMessage != null) {
      return ListView(
        padding: const EdgeInsets.all(24),
        children: [
          const SizedBox(height: 40),
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: AppColors.amber50,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppColors.amber200),
            ),
            child: Column(
              children: [
                const Icon(Icons.power_settings_new_rounded, size: 48, color: AppColors.amber600),
                const SizedBox(height: 12),
                const Text('E-Voting Nonaktif', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.amber900)),
                const SizedBox(height: 8),
                Text(
                  _errorMessage ?? 'Fitur E-Voting saat ini tidak aktif.',
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 12, color: AppColors.amber800, height: 1.4),
                ),
              ],
            ),
          ),
        ],
      );
    }

    if (_sessions.isEmpty) {
      return ListView(
        padding: const EdgeInsets.all(24),
        children: [
          const SizedBox(height: 40),
          Center(
            child: Column(
              children: [
                Icon(Icons.how_to_vote_outlined, size: 64, color: AppColors.gray300),
                const SizedBox(height: 12),
                const Text('Belum Ada Sesi Voting', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.gray700)),
                const SizedBox(height: 4),
                const Text('Sesi E-Voting aktif akan muncul di halaman ini.', style: TextStyle(fontSize: 12, color: AppColors.gray400)),
              ],
            ),
          ),
        ],
      );
    }

    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: _sessions.length,
      separatorBuilder: (_, __) => const SizedBox(height: 12),
      itemBuilder: (ctx, i) {
        final item = _sessions[i];
        final bool isActive   = item['status'] == 'active';
        final bool hasVoted  = item['user_has_voted'] == true;
        final int  candidatesCount = item['candidates_count'] ?? 0;
        final int  totalVotes      = item['total_votes'] ?? 0;

        return GestureDetector(
          onTap: () async {
            await Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => VotingDetailScreen(sessionId: item['id'] as int)),
            );
            _loadSessions();
          },
          child: Container(
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: isActive ? AppColors.violet200 : AppColors.gray200),
              boxShadow: [
                BoxShadow(
                  color: (isActive ? AppColors.violet600 : Colors.black).withValues(alpha: 0.05),
                  blurRadius: 10,
                  offset: const Offset(0, 3),
                ),
              ],
            ),
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: isActive ? AppColors.emerald50 : AppColors.gray100,
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: isActive ? AppColors.emerald200 : AppColors.gray300),
                      ),
                      child: Text(
                        isActive ? '● Berlangsung' : 'Selesai',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                          color: isActive ? AppColors.emerald700 : AppColors.gray600,
                        ),
                      ),
                    ),
                    const Spacer(),
                    if (hasVoted)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppColors.violet50,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: AppColors.violet200),
                        ),
                        child: const Row(
                          children: [
                            Icon(Icons.check_circle_rounded, size: 12, color: AppColors.violet600),
                            SizedBox(width: 4),
                            Text('Sudah Memilih', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.violet700)),
                          ],
                        ),
                      )
                    else if (isActive)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppColors.amber50,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: AppColors.amber200),
                        ),
                        child: const Text('Menunggu Suara', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.amber700)),
                      ),
                  ],
                ),
                const SizedBox(height: 12),
                Text(
                  item['title'] ?? '—',
                  style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.gray900),
                ),
                if (item['description'] != null && (item['description'] as String).isNotEmpty) ...[
                  const SizedBox(height: 4),
                  Text(
                    item['description'],
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 12, color: AppColors.gray500, height: 1.3),
                  ),
                ],
                const SizedBox(height: 14),
                Row(
                  children: [
                    Icon(Icons.people_outline_rounded, size: 14, color: AppColors.gray400),
                    const SizedBox(width: 4),
                    Text('$candidatesCount Kandidat', style: const TextStyle(fontSize: 11, color: AppColors.gray600)),
                    const SizedBox(width: 16),
                    Icon(Icons.how_to_vote_outlined, size: 14, color: AppColors.gray400),
                    const SizedBox(width: 4),
                    Text('$totalVotes Suara Masuk', style: const TextStyle(fontSize: 11, color: AppColors.gray600)),
                    const Spacer(),
                    const Icon(Icons.arrow_forward_ios_rounded, size: 12, color: AppColors.gray400),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
