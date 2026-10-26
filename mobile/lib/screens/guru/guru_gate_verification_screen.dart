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
  List<dynamic> _rejectedList = [];
  final Map<int, bool> _verifyingMap = {};

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
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
          _rejectedList = res['rejected'] ?? [];
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

  Future<void> _rejectItem(int id, String studentName) async {
    final noteController = TextEditingController();
    final bool? confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text('Kembalikan Ajuan $studentName', style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Tuliskan catatan alasan agar siswa dapat memperbaiki ajuannya:',
                style: TextStyle(fontSize: 12, color: AppColors.gray600)),
            const SizedBox(height: 10),
            TextField(
              controller: noteController,
              maxLines: 3,
              style: const TextStyle(fontSize: 13),
              decoration: InputDecoration(
                hintText: 'Contoh: Alasan tidak sesuai kronologi di gerbang...',
                hintStyle: const TextStyle(fontSize: 12, color: AppColors.gray400),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                contentPadding: const EdgeInsets.all(12),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () {
              if (noteController.text.trim().isEmpty) {
                ScaffoldMessenger.of(ctx).showSnackBar(
                  const SnackBar(content: Text('Catatan alasan harus diisi!'), backgroundColor: AppColors.red500),
                );
                return;
              }
              Navigator.pop(ctx, true);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFE11D48),
              foregroundColor: Colors.white,
            ),
            child: const Text('Kembalikan ke Siswa'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    setState(() => _verifyingMap[id] = true);
    try {
      final res = await ApiClient.post('/guru/conduct-self-reports/$id/reject', data: {
        'rejection_note': noteController.text.trim(),
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Pengajuan $studentName dikembalikan untuk diperbaiki.'),
            backgroundColor: AppColors.amber600,
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

  Future<void> _deleteItem(int id, String studentName) async {
    final bool? confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Hapus Ajuan Pembinaan?', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
        content: Text('Apakah Anda yakin ingin menghapus ajuan dari $studentName? Data tidak dapat dipulihkan.',
            style: const TextStyle(fontSize: 12, color: AppColors.gray600)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.red500,
              foregroundColor: Colors.white,
            ),
            child: const Text('Ya, Hapus'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    setState(() => _verifyingMap[id] = true);
    try {
      final res = await ApiClient.delete('/guru/conduct-self-reports/$id');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Pengajuan $studentName berhasil dihapus.'),
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
                        style: const TextStyle(color: Color(0xFFD97706), fontSize: 11, fontWeight: FontWeight.w900),
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
            Tab(
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Text('Dikembalikan'),
                  if (_rejectedList.isNotEmpty) ...[
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(10)),
                      child: Text(
                        '${_rejectedList.length}',
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
                      _buildRejectedTab(),
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
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: isVerifying ? null : () => _verifyItem(id, studentName),
                      icon: isVerifying
                          ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                          : const Icon(Icons.check_circle_rounded, size: 16),
                      label: Text(isVerifying ? 'Proses...' : 'Verifikasi', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.emerald600,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 10),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  OutlinedButton.icon(
                    onPressed: isVerifying ? null : () => _rejectItem(id, studentName),
                    icon: const Icon(Icons.assignment_return_rounded, size: 16, color: Color(0xFFE11D48)),
                    label: const Text('Kembalikan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFFE11D48))),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: Color(0xFFFDA4AF)),
                      backgroundColor: const Color(0xFFFFF1F2),
                      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 10),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                  const SizedBox(width: 4),
                  IconButton(
                    onPressed: isVerifying ? null : () => _deleteItem(id, studentName),
                    icon: const Icon(Icons.delete_outline_rounded, color: AppColors.gray400, size: 20),
                    tooltip: 'Hapus Ajuan',
                  ),
                ],
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
        final int id = item['id'];
        final String studentName = item['student_name'] ?? '—';
        final String className = item['class_name'] ?? '—';
        final int latCount = item['lateness_count'] ?? 1;
        final String reason = item['category_name'] ?? 'Terlambat';
        final String verifierName = item['verifier_name'] ?? 'Guru';
        final String? time = item['verified_at'];
        final bool isVerifying = _verifyingMap[id] == true;

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
              IconButton(
                onPressed: isVerifying ? null : () => _deleteItem(id, studentName),
                icon: const Icon(Icons.delete_outline_rounded, color: AppColors.gray400, size: 18),
                tooltip: 'Hapus Ajuan',
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildRejectedTab() {
    if (_rejectedList.isEmpty) {
      return ListView(
        children: const [
          SizedBox(height: 80),
          Center(
            child: Text('Tidak ada pengajuan yang dikembalikan.', style: TextStyle(color: AppColors.gray400, fontSize: 13)),
          ),
        ],
      );
    }

    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: _rejectedList.length,
      separatorBuilder: (_, __) => const SizedBox(height: 12),
      itemBuilder: (ctx, i) {
        final item = _rejectedList[i];
        final int id = item['id'];
        final String studentName = item['student_name'] ?? '—';
        final String className = item['class_name'] ?? '—';
        final String reason = item['category_name'] ?? 'Terlambat';
        final String? rejNote = item['rejection_note'];
        final String? time = item['verified_at'];
        final bool isVerifying = _verifyingMap[id] == true;

        return Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFFECDD3)),
            boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 4, offset: Offset(0, 2))],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const CircleAvatar(
                    backgroundColor: Color(0xFFFFE4E6),
                    radius: 20,
                    child: Icon(Icons.assignment_return_rounded, color: Color(0xFFE11D48), size: 20),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(studentName, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.gray900)),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(color: const Color(0xFFFFE4E6), borderRadius: BorderRadius.circular(6)),
                              child: Text('Kelas $className', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFFE11D48))),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text('⚡ $reason', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFFB45309))),
                        if (rejNote != null && rejNote.isNotEmpty) ...[
                          const SizedBox(height: 6),
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: const Color(0xFFFFF1F2),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(color: const Color(0xFFFECDD3)),
                            ),
                            child: Text('Catatan Pengembalian: "$rejNote"',
                                style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF9F1239))),
                          ),
                        ],
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: isVerifying ? null : () => _verifyItem(id, studentName),
                      icon: isVerifying
                          ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: AppColors.emerald600, strokeWidth: 2))
                          : const Icon(Icons.check_circle_outline_rounded, size: 16, color: AppColors.emerald600),
                      label: Text(isVerifying ? 'Proses...' : 'Verifikasi Langsung', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: AppColors.emerald600)),
                      style: OutlinedButton.styleFrom(
                        side: const BorderSide(color: AppColors.emerald600),
                        padding: const EdgeInsets.symmetric(vertical: 9),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  IconButton(
                    onPressed: isVerifying ? null : () => _deleteItem(id, studentName),
                    icon: const Icon(Icons.delete_outline_rounded, color: AppColors.gray400, size: 20),
                    tooltip: 'Hapus Ajuan',
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }
}
