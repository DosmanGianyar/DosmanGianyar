import 'package:flutter/material.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';

class GuruGateVerificationScreen extends StatefulWidget {
  const GuruGateVerificationScreen({super.key});

  @override
  State<GuruGateVerificationScreen> createState() => _GuruGateVerificationScreenState();
}

class _GuruGateVerificationScreenState extends State<GuruGateVerificationScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isLoading = true;
  String? _error;
  List<dynamic> _pendingList = [];
  List<dynamic> _verifiedList = [];
  final Map<int, bool> _verifyingMap = {};

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _load();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final res = await ApiClient.get('/guru/conduct-self-reports');
      if (mounted) {
        setState(() {
          _pendingList = res['pending'] ?? [];
          _verifiedList = res['verified'] ?? [];
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = ApiClient.extractError(e);
          _isLoading = false;
        });
      }
    }
  }

  Future<void> _verifyItem(int id, String studentName) async {
    setState(() => _verifyingMap[id] = true);
    try {
      final res = await ApiClient.post('/guru/conduct-self-reports/$id/verify');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Pengajuan $studentName berhasil diverifikasi.'),
            backgroundColor: AppColors.emerald600,
          ),
        );
        _load();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(ApiClient.extractError(e)),
            backgroundColor: AppColors.red500,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _verifyingMap.remove(id));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Piket Gerbang — Self Report'),
        backgroundColor: const Color(0xFFD97706),
        foregroundColor: Colors.white,
        elevation: 0,
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.white,
          indicatorWeight: 3,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
          tabs: [
            Tab(
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Text('Pending'),
                  if (_pendingList.isNotEmpty) ...[
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(10)),
                      child: Text(
                        '${_pendingList.length}',
                        style: const TextStyle(color: Color(0xFFD97706), fontSize: 11, fontWeight: FontWeight.black),
                      ),
                    ),
                  ],
                ],
              ),
            ),
            Tab(
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Text('Diverifikasi'),
                  if (_verifiedList.isNotEmpty) ...[
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(10)),
                      child: Text(
                        '${_verifiedList.length}',
                        style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(_error!, style: const TextStyle(color: AppColors.red500)),
                      const SizedBox(height: 12),
                      ElevatedButton(onPressed: _load, child: const Text('Coba Lagi')),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  child: TabBarView(
                    controller: _tabController,
                    children: [
                      _buildPendingTab(),
                      _buildVerifiedTab(),
                    ],
                  ),
                ),
    );
  }

  Widget _buildPendingTab() {
    if (_pendingList.isEmpty) {
      return ListView(
        children: const [
          SizedBox(height: 80),
          Center(
            child: Column(
              children: [
                Icon(Icons.check_circle_outline_rounded, size: 56, color: AppColors.gray300),
                SizedBox(height: 12),
                Text('Tidak ada pengajuan mandiri yang pending',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.gray600)),
                SizedBox(height: 4),
                Text('Siswa terlambat dapat mengajukan langsung via HP.',
                    style: TextStyle(fontSize: 12, color: AppColors.gray400)),
              ],
            ),
          ),
        ],
      );
    }

    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: _pendingList.length,
      separatorBuilder: (_, __) => const SizedBox(height: 12),
      itemBuilder: (ctx, i) {
        final item = _pendingList[i];
        final int id = item['id'];
        final String studentName = item['student_name'] ?? '—';
        final String className = item['class_name'] ?? '—';
        final int latCount = item['lateness_count'] ?? 1;
        final String reason = item['category_name'] ?? 'Terlambat';
        final String? desc = item['description'];
        final String time = item['created_at'] ?? '';
        final bool isVerifying = _verifyingMap[id] == true;

        return Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFFDE68A)),
            boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 4, offset: Offset(0, 2))],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  CircleAvatar(
                    backgroundColor: const Color(0xFFFEF3C7),
                    radius: 20,
                    child: Text(
                      studentName.isNotEmpty ? studentName.substring(0, 1).toUpperCase() : 'S',
                      style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFFB45309)),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                studentName,
                                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.gray900),
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(color: const Color(0xFFDBEAFE), borderRadius: BorderRadius.circular(6)),
                              child: Text(
                                'Kelas $className',
                                style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF1E40AF)),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: latCount >= 5 ? const Color(0xFFFEE2E2) : (latCount >= 2 ? const Color(0xFFFFEDD5) : const Color(0xFFFEF3C7)),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                latCount >= 5 ? '🚨 $latCount x Pembinaan (Sering)' : (latCount >= 2 ? '⚠️ Total $latCount x Pembinaan' : '⚠️ Catatan Ke-1'),
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                  color: latCount >= 5 ? const Color(0xFF991B1B) : (latCount >= 2 ? const Color(0xFF9A3412) : const Color(0xFF92400E)),
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Text('Pukul $time WITA', style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
                          ],
                        ),
                        const SizedBox(height: 6),
                        Text('⚡ $reason', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFFB45309))),
                        if (desc != null && desc.isNotEmpty) ...[
                          const SizedBox(height: 4),
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(color: AppColors.gray50, borderRadius: BorderRadius.circular(8)),
                            child: Text('"$desc"', style: const TextStyle(fontSize: 11, fontStyle: FontStyle.italic, color: AppColors.gray600)),
                          ),
                        ],
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: isVerifying ? null : () => _verifyItem(id, studentName),
                  icon: isVerifying
                      ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                      : const Icon(Icons.check_circle_rounded, size: 18),
                  label: Text(isVerifying ? 'Memproses...' : 'Verifikasi & Izinkan Masuk', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.emerald600,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 10),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildVerifiedTab() {
    if (_verifiedList.isEmpty) {
      return ListView(
        children: const [
          SizedBox(height: 80),
          Center(
            child: Text('Belum ada pengajuan yang diverifikasi hari ini.', style: TextStyle(color: AppColors.gray400, fontSize: 13)),
          ),
        ],
      );
    }

    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: _verifiedList.length,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (ctx, i) {
        final item = _verifiedList[i];
        final String studentName = item['student_name'] ?? '—';
        final String className = item['class_name'] ?? '—';
        final int latCount = item['lateness_count'] ?? 1;
        final String reason = item['category_name'] ?? 'Terlambat';
        final String verifierName = item['verifier_name'] ?? 'Guru';
        final String? time = item['verified_at'];

        return Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppColors.gray200),
          ),
          child: Row(
            children: [
              const CircleAvatar(
                backgroundColor: Color(0xFFD1FAE5),
                radius: 18,
                child: Icon(Icons.check_rounded, color: AppColors.emerald600, size: 20),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(studentName, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.gray900)),
                        ),
                        Text('Kelas $className', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.gray500)),
                      ],
                    ),
                    const SizedBox(height: 2),
                    Text('$reason • Total $latCount x', style: const TextStyle(fontSize: 11, color: AppColors.gray500)),
                    Text('Diverifikasi $time WITA oleh $verifierName', style: const TextStyle(fontSize: 10, color: AppColors.emerald600, fontWeight: FontWeight.bold)),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
