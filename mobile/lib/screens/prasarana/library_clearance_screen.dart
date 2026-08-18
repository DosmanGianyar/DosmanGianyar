import 'package:flutter/material.dart';
import 'package:qr_flutter/qr_flutter.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';

class LibraryClearanceScreen extends StatefulWidget {
  const LibraryClearanceScreen({super.key});

  @override
  State<LibraryClearanceScreen> createState() => _LibraryClearanceScreenState();
}

class _LibraryClearanceScreenState extends State<LibraryClearanceScreen> {
  bool _loading = true;
  bool _isClear = false;
  Map<String, dynamic>? _studentData;
  String _verificationUrl = '';
  String _issueDate = '';
  List<Map<String, dynamic>> _activeLoans = [];

  @override
  void initState() {
    super.initState();
    _fetchClearanceData();
  }

  Future<void> _fetchClearanceData() async {
    setState(() => _loading = true);
    try {
      final res = await ApiClient.get('/siswa/library/clearance');
      setState(() {
        _isClear = res['is_clear'] ?? false;
        _studentData = res['student'] as Map<String, dynamic>?;
        _verificationUrl = res['verification_url']?.toString() ?? '';
        _issueDate = res['issue_date']?.toString() ?? '';
        _activeLoans = List<Map<String, dynamic>>.from(res['active_loans'] as List? ?? []);
        _loading = false;
      });
    } catch (_) {
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Kartu Bebas Perpustakaan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        backgroundColor: Colors.white,
        foregroundColor: AppColors.slate900,
        elevation: 0,
      ),
      body: RefreshIndicator(
        onRefresh: _fetchClearanceData,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    // ─── Digital Certificate Card ──────────────────────────
                    Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: AppRadius.card,
                        border: Border.all(
                          color: _isClear ? const Color(0xFF10B981) : const Color(0xFFF43F5E),
                          width: 2,
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.06),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.center,
                        children: [
                          // Header Logo & Title
                          Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Image.asset(
                                'assets/images/dosman_white_icon.png',
                                width: 36,
                                height: 36,
                                errorBuilder: (_, __, ___) => const Icon(Icons.school_rounded, size: 36, color: AppColors.indigo600),
                              ),
                              const SizedBox(width: 10),
                              const Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text('PERPUSTAKAAN DOSMAN', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.slate800)),
                                  Text('SMA Negeri 1 Gianyar', style: TextStyle(fontSize: 10, color: AppColors.slate500)),
                                ],
                              ),
                            ],
                          ),
                          const Padding(
                            padding: EdgeInsets.symmetric(vertical: 12),
                            child: Divider(height: 1),
                          ),

                          const Text(
                            'KARTU BEBAS PERPUSTAKAAN',
                            style: TextStyle(fontWeight: FontWeight.w900, fontSize: 14, letterSpacing: 0.5, color: AppColors.indigo900),
                          ),
                          const SizedBox(height: 8),

                          // Status Badge
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                            decoration: BoxDecoration(
                              color: _isClear ? const Color(0xFFD1FAE5) : const Color(0xFFFFE4E6),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(
                                color: _isClear ? const Color(0xFF34D399) : const Color(0xFFFB7185),
                              ),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(
                                  _isClear ? Icons.check_circle_rounded : Icons.warning_amber_rounded,
                                  size: 16,
                                  color: _isClear ? const Color(0xFF047857) : const Color(0xFFBE123C),
                                ),
                                const SizedBox(width: 6),
                                Text(
                                  _isClear ? 'BEBAS TANGGUNGAN' : 'MASIH ADA TANGGUNGAN',
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 11,
                                    color: _isClear ? const Color(0xFF047857) : const Color(0xFFBE123C),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 16),

                          // Student Info Table
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: AppColors.slate50,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Column(
                              children: [
                                _infoRow('Nama Siswa', _studentData?['name']?.toString() ?? '—'),
                                const SizedBox(height: 6),
                                _infoRow('NIS / NISN', '${_studentData?['nis'] ?? '—'} / ${_studentData?['nisn'] ?? '—'}'),
                                const SizedBox(height: 6),
                                _infoRow('Kelas', _studentData?['class_name']?.toString() ?? '—'),
                                const SizedBox(height: 6),
                                _infoRow('Tanggal Terbit', _issueDate),
                              ],
                            ),
                          ),
                          const SizedBox(height: 16),

                          // QR Code Verification
                          if (_verificationUrl.isNotEmpty) ...[
                            QrImageView(
                              data: _verificationUrl,
                              version: QrVersions.auto,
                              size: 140.0,
                              eyeStyle: const QrEyeStyle(
                                eyeShape: QrEyeShape.square,
                                color: AppColors.indigo900,
                              ),
                              dataModuleStyle: const QrDataModuleStyle(
                                dataModuleShape: QrDataModuleShape.square,
                                color: AppColors.indigo900,
                              ),
                            ),
                            const SizedBox(height: 6),
                            const Text(
                              'Scan QR Code untuk verifikasi keabsahan',
                              style: TextStyle(fontSize: 10, color: AppColors.slate400),
                            ),
                          ],
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Active Loans warning list if any
                    if (!_isClear && _activeLoans.isNotEmpty) ...[
                      Container(
                        width: double.infinity,
                        decoration: BoxDecoration(
                          color: const Color(0xFFFFF1F2),
                          borderRadius: AppRadius.card,
                          border: Border.all(color: const Color(0xFFFECDD3)),
                        ),
                        padding: const EdgeInsets.all(14),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Row(
                              children: [
                                Icon(Icons.error_outline_rounded, color: Color(0xFFE11D48), size: 18),
                                SizedBox(width: 6),
                                Text(
                                  'Buku Belum Dikembalikan:',
                                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFF9F1239)),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            ..._activeLoans.map((loan) => Padding(
                                  padding: const EdgeInsets.only(bottom: 6),
                                  child: Row(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const Text('• ', style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFFBE123C))),
                                      Expanded(
                                        child: Text(
                                          '${loan['book_title']} (Batas: ${loan['due_at'] ?? '—'})',
                                          style: const TextStyle(fontSize: 11, color: Color(0xFF881337)),
                                        ),
                                      ),
                                    ],
                                  ),
                                )),
                          ],
                        ),
                      ),
                    ],
                  ],
                ),
              ),
      ),
    );
  }

  Widget _infoRow(String label, String value) {
    return Row(
      children: [
        SizedBox(
          width: 100,
          child: Text(label, style: const TextStyle(fontSize: 11, color: AppColors.slate500, fontWeight: FontWeight.w500)),
        ),
        const Text(': ', style: TextStyle(fontSize: 11, color: AppColors.slate500)),
        Expanded(
          child: Text(value, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.slate800)),
        ),
      ],
    );
  }
}
