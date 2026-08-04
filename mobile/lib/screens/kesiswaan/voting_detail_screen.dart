import 'package:flutter/material.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';

class VotingDetailScreen extends StatefulWidget {
  final int sessionId;
  const VotingDetailScreen({super.key, required this.sessionId});

  @override
  State<VotingDetailScreen> createState() => _VotingDetailScreenState();
}

class _VotingDetailScreenState extends State<VotingDetailScreen> {
  bool _loading = true;
  bool _submitting = false;
  String? _errorMessage;
  Map<String, dynamic>? _sessionData;

  @override
  void initState() {
    super.initState();
    _loadDetail();
  }

  Future<void> _loadDetail() async {
    setState(() {
      _loading = true;
      _errorMessage = null;
    });

    try {
      final res = await ApiClient.get('/siswa/voting/${widget.sessionId}');
      setState(() {
        _sessionData = res;
        _loading = false;
      });
    } catch (e) {
      setState(() {
        _errorMessage = 'Gagal memuat detail voting: $e';
        _loading = false;
      });
    }
  }

  Future<void> _submitVote(int candidateId, String candidateName, int number) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Konfirmasi Pilihan', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
        content: Text(
          'Apakah Anda yakin ingin memberikan suara untuk Nomor $number: $candidateName?\n\nPilihan Anda tidak dapat diubah setelah dikirim.',
          style: const TextStyle(fontSize: 13, height: 1.4),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.violet600,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Kirim Suara'),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    setState(() => _submitting = true);

    try {
      final res = await ApiClient.post('/siswa/voting/${widget.sessionId}/vote', {
        'candidate_id': candidateId,
      });

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(res['message'] ?? 'Suara berhasil dikirim!'),
          backgroundColor: AppColors.emerald600,
          behavior: SnackBarBehavior.floating,
        ));
      }
      await _loadDetail();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text('Gagal mengirim suara: $e'),
          backgroundColor: AppColors.red600,
          behavior: SnackBarBehavior.floating,
        ));
      }
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Text(_sessionData?['title'] ?? 'Detail E-Voting', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
        backgroundColor: Colors.white,
        elevation: 0.5,
        foregroundColor: AppColors.gray800,
      ),
      body: _buildContent(),
    );
  }

  Widget _buildContent() {
    if (_loading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_errorMessage != null || _sessionData == null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.error_outline_rounded, size: 48, color: AppColors.red500),
              const SizedBox(height: 12),
              Text(_errorMessage ?? 'Data tidak ditemukan.', textAlign: TextAlign.center, style: const TextStyle(fontSize: 13, color: AppColors.gray700)),
              const SizedBox(height: 16),
              ElevatedButton(onPressed: _loadDetail, child: const Text('Coba Lagi')),
            ],
          ),
        ),
      );
    }

    final data = _sessionData!;
    final bool isActive  = data['status'] == 'active';
    final bool hasVoted = data['user_has_voted'] == true;
    final int? votedId  = data['user_voted_id'] as int?;
    final candidates    = (data['candidates'] as List<dynamic>? ?? []);

    return Stack(
      children: [
        ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Banner Info
            Container(
              decoration: BoxDecoration(
                gradient: AppColors.blueGradient,
                borderRadius: BorderRadius.circular(16),
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
                          color: Colors.white.withValues(alpha: 0.20),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          isActive ? '● SEDANG BERLANGSUNG' : 'SELESAI',
                          style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.white),
                        ),
                      ),
                      const Spacer(),
                      Text(
                        'Total Suara: ${data['total_votes'] ?? 0}',
                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.white),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Text(
                    data['title'] ?? '',
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white),
                  ),
                  if (data['description'] != null && (data['description'] as String).isNotEmpty) ...[
                    const SizedBox(height: 6),
                    Text(
                      data['description'],
                      style: TextStyle(fontSize: 12, color: Colors.white.withValues(alpha: 0.90), height: 1.4),
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 16),

            if (hasVoted)
              Container(
                padding: const EdgeInsets.all(12),
                margin: const EdgeInsets.only(bottom: 16),
                decoration: BoxDecoration(
                  color: AppColors.emerald50,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.emerald200),
                ),
                child: const Row(
                  children: [
                    Icon(Icons.check_circle_rounded, color: AppColors.emerald600, size: 20),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Anda telah memberikan suara pada sesi ini. Hasil suara ditampilkan di bawah.',
                        style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.emerald900),
                      ),
                    ),
                  ],
                ),
              ),

            const Text('Kandidat Calon', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.gray800)),
            const SizedBox(height: 10),

            ...candidates.map((c) {
              final int candidateId   = c['id'] as int;
              final int number        = c['number'] ?? 0;
              final String name       = c['name'] ?? '';
              final String? photoUrl  = c['photo_url'] as String?;
              final String? vision    = c['vision'] as String?;
              final String? mission   = c['mission'] as String?;
              final num percentage    = c['percentage'] ?? 0;
              final int votesCount    = c['votes_count'] ?? 0;
              final bool isMyChoice   = votedId == candidateId;

              return Container(
                margin: const EdgeInsets.only(bottom: 14),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(
                    color: isMyChoice ? AppColors.emerald500 : AppColors.gray200,
                    width: isMyChoice ? 2 : 1,
                  ),
                  boxShadow: AppShadow.sm,
                ),
                padding: const EdgeInsets.all(14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Nomor Urut Badge
                        Container(
                          width: 36, height: 36,
                          decoration: BoxDecoration(
                            color: isMyChoice ? AppColors.emerald600 : AppColors.violet600,
                            shape: BoxShape.circle,
                          ),
                          alignment: Alignment.center,
                          child: Text(
                            '$number',
                            style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                          ),
                        ),
                        const SizedBox(width: 12),
                        // Foto & Nama
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      name,
                                      style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.gray900),
                                    ),
                                  ),
                                  if (isMyChoice)
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: AppColors.emerald100,
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: const Text('Pilihan Anda', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.emerald700)),
                                    ),
                                ],
                              ),
                              if (photoUrl != null && photoUrl.isNotEmpty) ...[
                                const SizedBox(height: 8),
                                ClipRRect(
                                  borderRadius: BorderRadius.circular(8),
                                  child: Image.network(photoUrl, height: 140, width: double.infinity, fit: BoxFit.cover),
                                ),
                              ],
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    // Visi & Misi Accordion / Display
                    if (vision != null && vision.isNotEmpty) ...[
                      const Text('Visi:', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.gray700)),
                      Text(vision, style: const TextStyle(fontSize: 11, color: AppColors.gray600, height: 1.3)),
                      const SizedBox(height: 6),
                    ],
                    if (mission != null && mission.isNotEmpty) ...[
                      const Text('Misi:', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.gray700)),
                      Text(mission, style: const TextStyle(fontSize: 11, color: AppColors.gray600, height: 1.3)),
                      const SizedBox(height: 10),
                    ],

                    // Hasil Suara (Jika sudah memilih atau voting selesai)
                    if (hasVoted || !isActive) ...[
                      const Divider(height: 16),
                      Row(
                        children: [
                          Text('$percentage%', style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.violet700)),
                          const SizedBox(width: 8),
                          Expanded(
                            child: ClipRRect(
                              borderRadius: BorderRadius.circular(6),
                              child: LinearProgressIndicator(
                                value: (percentage / 100).toDouble(),
                                minHeight: 8,
                                backgroundColor: AppColors.gray100,
                                color: isMyChoice ? AppColors.emerald500 : AppColors.violet600,
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Text('$votesCount suara', style: const TextStyle(fontSize: 11, color: AppColors.gray500)),
                        ],
                      ),
                    ],

                    // Tombol Vote (Jika aktif & belum memilih)
                    if (isActive && !hasVoted) ...[
                      const SizedBox(height: 12),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton.icon(
                          onPressed: () => _submitVote(candidateId, name, number),
                          icon: const Icon(Icons.how_to_vote_rounded, size: 18),
                          label: Text('PILIH KANDIDAT NO. $number'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.violet600,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
              );
            }),
          ],
        ),

        if (_submitting)
          Container(
            color: Colors.black38,
            child: const Center(child: CircularProgressIndicator()),
          ),
      ],
    );
  }
}
