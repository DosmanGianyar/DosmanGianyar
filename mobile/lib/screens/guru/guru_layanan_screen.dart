import 'package:flutter/material.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';

class GuruLayananScreen extends StatefulWidget {
  const GuruLayananScreen({super.key});

  @override
  State<GuruLayananScreen> createState() => _GuruLayananScreenState();
}

class _GuruLayananScreenState extends State<GuruLayananScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isLoading = true;
  String? _error;
  String _searchQuery = '';

  List<dynamic> _waliKelas = [];
  List<dynamic> _extracurriculars = [];
  List<dynamic> _gurus = [];
  List<dynamic> _piketSchedule = [];
  Map<String, dynamic>? _liveMonitoring;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() { _isLoading = true; _error = null; });
    try {
      final res = await ApiClient.get('/guru/layanan');
      if (res['status'] == 'success' && res['data'] != null) {
        final d = res['data'];
        if (mounted) {
          setState(() {
            _waliKelas = d['wali_kelas'] ?? [];
            _extracurriculars = d['extracurriculars'] ?? [];
            _gurus = d['gurus'] ?? [];
            _piketSchedule = d['piket_schedule'] ?? [];
            _liveMonitoring = d['live_monitoring'] as Map<String, dynamic>?;
            _isLoading = false;
          });
        }
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

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.gray50,
      appBar: AppBar(
        title: const Text(
          'Layanan & Direktori Sekolah',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white),
        ),
        backgroundColor: const Color(0xFF0F2460),
        foregroundColor: Colors.white,
        elevation: 0,
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.amber,
          indicatorWeight: 3,
          isScrollable: true,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          labelStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
          tabs: const [
            Tab(text: '🏫 Wali Kelas'),
            Tab(text: '📌 Pembina Ekstra'),
            Tab(text: '🛡️ Piket & Jadwal'),
            Tab(text: '👨‍🏫 Semua Guru'),
          ],
        ),
      ),
      body: Column(
        children: [
          // Banner Read Only Notice
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
            color: AppColors.blue50,
            child: const Row(
              children: [
                Icon(Icons.info_outline_rounded, size: 16, color: AppColors.blue600),
                SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Direktori Informasi Sekolah Senin - Sabtu (Read-Only)',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.blue800),
                  ),
                ),
              ],
            ),
          ),

          // Search Field
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
            child: TextField(
              onChanged: (val) => setState(() => _searchQuery = val.toLowerCase()),
              decoration: InputDecoration(
                hintText: 'Cari nama, kelas, atau mapel...',
                hintStyle: const TextStyle(fontSize: 12, color: AppColors.gray400),
                prefixIcon: const Icon(Icons.search_rounded, size: 20, color: AppColors.gray400),
                contentPadding: const EdgeInsets.symmetric(vertical: 10),
                filled: true,
                fillColor: Colors.white,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: AppColors.gray200),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: AppColors.gray200),
                ),
              ),
            ),
          ),

          // Content
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _error != null
                    ? Center(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(_error!, style: const TextStyle(fontSize: 12, color: AppColors.red500)),
                            const SizedBox(height: 8),
                            ElevatedButton(onPressed: _loadData, child: const Text('Coba Lagi')),
                          ],
                        ),
                      )
                    : TabBarView(
                        controller: _tabController,
                        children: [
                          _buildWaliKelasList(),
                          _buildExtracurricularsList(),
                          _buildPiketScheduleList(),
                          _buildGurusList(),
                        ],
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildWaliKelasList() {
    final filtered = _waliKelas.where((item) {
      final name = (item['homeroom_teacher'] ?? '').toString().toLowerCase();
      final className = (item['class_name'] ?? '').toString().toLowerCase();
      return name.contains(_searchQuery) || className.contains(_searchQuery);
    }).toList();

    if (filtered.isEmpty) {
      return const Center(child: Text('Tidak ada data wali kelas', style: TextStyle(fontSize: 12, color: AppColors.gray400)));
    }

    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: filtered.length,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (_, i) {
        final item = filtered[i];
        return Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppColors.gray200),
          ),
          child: Row(
            children: [
              Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  color: AppColors.blue50,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: AppColors.blue100),
                ),
                child: Center(
                  child: Text(
                    item['class_name'] ?? '—',
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w900, color: AppColors.blue700),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      item['homeroom_teacher'] ?? 'Belum Ada Wali',
                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.gray800),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Kelas ${item['class_name']} · NIP: ${item['nip']}',
                      style: const TextStyle(fontSize: 11, color: AppColors.gray500),
                    ),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildExtracurricularsList() {
    final filtered = _extracurriculars.where((item) {
      final name = (item['name'] ?? '').toString().toLowerCase();
      final pembina = (item['pembina_names'] ?? '').toString().toLowerCase();
      return name.contains(_searchQuery) || pembina.contains(_searchQuery);
    }).toList();

    if (filtered.isEmpty) {
      return const Center(child: Text('Tidak ada data ekstrakurikuler', style: TextStyle(fontSize: 12, color: AppColors.gray400)));
    }

    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: filtered.length,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (_, i) {
        final item = filtered[i];
        return Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppColors.gray200),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                item['name'] ?? '—',
                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.gray900),
              ),
              const SizedBox(height: 6),
              Row(
                children: [
                  const Icon(Icons.person_rounded, size: 14, color: AppColors.amber600),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      'Pembina: ${item['pembina_names'] ?? '—'}',
                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.gray700),
                    ),
                  ),
                ],
              ),
              if (item['contact_person'] != null && (item['contact_person'] as String).isNotEmpty) ...[
                const SizedBox(height: 4),
                Row(
                  children: [
                    const Icon(Icons.phone_rounded, size: 14, color: AppColors.gray400),
                    const SizedBox(width: 6),
                    Text(
                      'Kontak: ${item['contact_person']}',
                      style: const TextStyle(fontSize: 11, color: AppColors.gray500),
                    ),
                  ],
                ),
              ],
            ],
          ),
        );
      },
    );
  }

  Widget _buildPiketScheduleList() {
    final live = _liveMonitoring;
    final activeInfo = live?['active_info'] as Map<String, dynamic>?;
    final monitoringList = (live?['monitoring_list'] as List? ?? []).where((item) {
      if (_searchQuery.isEmpty) return true;
      final cName = (item['class_name'] ?? '').toString().toLowerCase();
      final tName = (item['teacher_name'] ?? '').toString().toLowerCase();
      final sName = (item['subject_name'] ?? '').toString().toLowerCase();
      return cName.contains(_searchQuery) || tName.contains(_searchQuery) || sName.contains(_searchQuery);
    }).toList();

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Live Period Banner
        if (live != null && activeInfo != null) ...[
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
                  color: const Color(0xFF0F2460).withOpacity(0.3),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: Colors.emerald.withOpacity(0.2),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: Colors.emerald.withOpacity(0.4)),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.access_time_rounded, size: 12, color: Colors.emeraldAccent),
                          SizedBox(width: 4),
                          Text(
                            'Jam Berjalan',
                            style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.emeraldAccent),
                          ),
                        ],
                      ),
                    ),
                    const Spacer(),
                    Text(
                      'Hari ${live['day_name']}',
                      style: const TextStyle(fontSize: 11, color: Colors.white70),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  activeInfo['label'] ?? 'Jam Berjalan',
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white),
                ),
                const SizedBox(height: 4),
                Text(
                  'Waktu KBM: ${activeInfo['time_range'] ?? '—'} WITA',
                  style: const TextStyle(fontSize: 11, color: Colors.white70),
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    _buildStatChip('Total Kelas', '${live['total_classes'] ?? 0}', Colors.white24),
                    const SizedBox(width: 8),
                    _buildStatChip('Terjadwal', '${live['filled_schedules'] ?? 0}', Colors.emerald.withOpacity(0.3)),
                    const SizedBox(width: 8),
                    _buildStatChip('Belum Input', '${live['missing_schedules'] ?? 0}', Colors.amber.withOpacity(0.3)),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                '🏫 Pengajar Jam Ke-${live['selected_period'] ?? 1}',
                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.gray900),
              ),
              Text(
                '${monitoringList.length} Kelas',
                style: const TextStyle(fontSize: 11, color: AppColors.gray500),
              ),
            ],
          ),
          const SizedBox(height: 10),
          if (monitoringList.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 12),
              child: Text('Tidak ada kelas sesuai filter', style: TextStyle(fontSize: 12, color: AppColors.gray400)),
            )
          else
            ...monitoringList.map((item) {
              final bool hasSch = item['has_schedule'] as bool? ?? false;
              final bool hasJrnl = item['has_journal'] as bool? ?? false;
              return Container(
                margin: const EdgeInsets.only(bottom: 8),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: hasSch ? AppColors.gray200 : AppColors.amber200,
                  ),
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(
                        color: const Color(0xFF0F2460),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        item['class_name'] ?? '—',
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.white),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: hasSch
                          ? Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  item['teacher_name'] ?? '—',
                                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.gray800),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                Text(
                                  item['subject_name'] ?? '—',
                                  style: const TextStyle(fontSize: 11, color: AppColors.blue600),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ],
                            )
                          : const Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Jadwal belum di-input',
                                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.amber800),
                                ),
                                Text(
                                  'Belum ada guru terjadwal di jam ini',
                                  style: TextStyle(fontSize: 10, color: AppColors.gray400),
                                ),
                              ],
                            ),
                    ),
                    if (hasSch)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: hasJrnl ? AppColors.green50 : AppColors.amber50,
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(
                            color: hasJrnl ? AppColors.green200 : AppColors.amber200,
                          ),
                        ),
                        child: Text(
                          hasJrnl ? '✓ Jurnal' : '⏳ Belum Jurnal',
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: hasJrnl ? AppColors.green700 : AppColors.amber800,
                          ),
                        ),
                      ),
                  ],
                ),
              );
            }),
          const SizedBox(height: 18),
          const Divider(),
          const SizedBox(height: 10),
          const Text(
            '🗓️ Sesi Mengajar Lengkap Per Hari',
            style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.gray900),
          ),
          const SizedBox(height: 10),
        ],

        if (_piketSchedule.isEmpty)
          const Center(child: Text('Belum ada jadwal mengajar/piket tercatat', style: TextStyle(fontSize: 12, color: AppColors.gray400)))
        else
          ..._piketSchedule.map((dayData) {
            final dayName = dayData['day_name'] ?? '—';
            final sessions = (dayData['sessions'] as List? ?? []).where((s) {
              final tName = (s['teacher_name'] ?? '').toString().toLowerCase();
              final sub = (s['subject'] ?? '').toString().toLowerCase();
              final cls = (s['class_name'] ?? '').toString().toLowerCase();
              return tName.contains(_searchQuery) || sub.contains(_searchQuery) || cls.contains(_searchQuery);
            }).toList();

            return Container(
              margin: const EdgeInsets.only(bottom: 12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppColors.gray200),
              ),
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        '🗓️ Hari $dayName',
                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.gray900),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: AppColors.blue50,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          '${sessions.length} Sesi',
                          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.blue700),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  if (sessions.isEmpty)
                    const Text('Tidak ada jadwal sesuai pencarian', style: TextStyle(fontSize: 11, color: AppColors.gray400))
                  else
                    ...sessions.take(6).map((s) => Padding(
                      padding: const EdgeInsets.only(bottom: 6),
                      child: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: AppColors.gray100,
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              'Jam ${s['period']}',
                              style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.gray700),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              '${s['teacher_name']} (${s['subject']} - ${s['class_name']})',
                              style: const TextStyle(fontSize: 11, color: AppColors.gray800),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    )),
                ],
              ),
            );
          }),
      ],
    );
  }

  Widget _buildStatChip(String label, String value, Color bg) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8),
        decoration: BoxDecoration(
          color: bg,
          borderRadius: BorderRadius.circular(10),
        ),
        child: Column(
          children: [
            Text(label, style: const TextStyle(fontSize: 9, color: Colors.white70)),
            const SizedBox(height: 2),
            Text(value, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.white)),
          ],
        ),
      ),
    );
  }

  Widget _buildGurusList() {
    final filtered = _gurus.where((item) {
      final name = (item['name'] ?? '').toString().toLowerCase();
      final subject = (item['subject'] ?? '').toString().toLowerCase();
      return name.contains(_searchQuery) || subject.contains(_searchQuery);
    }).toList();

    if (filtered.isEmpty) {
      return const Center(child: Text('Tidak ada data guru', style: TextStyle(fontSize: 12, color: AppColors.gray400)));
    }

    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: filtered.length,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (_, i) {
        final item = filtered[i];
        return Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppColors.gray200),
          ),
          child: Row(
            children: [
              CircleAvatar(
                radius: 20,
                backgroundColor: AppColors.blue600,
                child: Text(
                  (item['name'] as String? ?? '?').substring(0, 1),
                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      item['name'] ?? '—',
                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.gray800),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      '${item['subject']} · NIP: ${item['nip']}',
                      style: const TextStyle(fontSize: 11, color: AppColors.gray500),
                    ),
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
