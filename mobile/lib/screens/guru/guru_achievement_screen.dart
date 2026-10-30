import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../services/api_client.dart';
import '../../theme/app_colors.dart';

class GuruAchievementScreen extends StatefulWidget {
  const GuruAchievementScreen({super.key});

  @override
  State<GuruAchievementScreen> createState() => _GuruAchievementScreenState();
}

class _GuruAchievementScreenState extends State<GuruAchievementScreen> {
  bool _loading = true;
  String? _error;

  Map<String, dynamic> _stats = {};
  List<Map<String, dynamic>> _myExtracurriculars = [];
  List<Map<String, dynamic>> _achievements = [];

  int? _selectedExtraId;
  String _selectedStatus = '';
  String _search = '';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final params = <String, dynamic>{};
      if (_selectedExtraId != null) params['extracurricular_id'] = _selectedExtraId.toString();
      if (_selectedStatus.isNotEmpty) params['status'] = _selectedStatus;

      final body = await ApiClient.get('/guru/achievements', params: params);
      if (!mounted) return;
      setState(() {
        _stats = Map<String, dynamic>.from(body['stats'] ?? {});
        _myExtracurriculars = List<Map<String, dynamic>>.from(body['my_extracurriculars'] ?? []);
        _achievements = List<Map<String, dynamic>>.from(body['achievements'] ?? []);
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = ApiClient.extractError(e);
        _loading = false;
      });
    }
  }

  List<Map<String, dynamic>> get _filtered {
    if (_search.trim().isEmpty) return _achievements;
    final q = _search.toLowerCase();
    return _achievements.where((a) {
      final title = (a['title'] ?? '').toString().toLowerCase();
      final student = (a['student_name'] ?? '').toString().toLowerCase();
      final rank = (a['rank'] ?? '').toString().toLowerCase();
      final extra = (a['extracurricular_name'] ?? '').toString().toLowerCase();
      return title.contains(q) || student.contains(q) || rank.contains(q) || extra.contains(q);
    }).toList();
  }

  Future<void> _openUrl(String? url) async {
    if (url == null || url.isEmpty) return;
    final uri = Uri.tryParse(url);
    if (uri != null) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Juara & Prestasi Siswa Binaan'),
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        color: AppColors.primary,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? Center(
                    child: Padding(
                      padding: const EdgeInsets.all(24),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(_error!, textAlign: TextAlign.center, style: const TextStyle(color: AppColors.gray600)),
                          const SizedBox(height: 12),
                          ElevatedButton(onPressed: _load, child: const Text('Coba Lagi')),
                        ],
                      ),
                    ),
                  )
                : ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      _buildSummaryBanner(),
                      const SizedBox(height: 14),
                      _buildFilters(),
                      const SizedBox(height: 14),
                      if (_filtered.isEmpty)
                        Container(
                          padding: const EdgeInsets.all(32),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(18),
                            border: Border.all(color: AppColors.gray200),
                          ),
                          child: const Column(
                            children: [
                              Icon(Icons.emoji_events_outlined, size: 48, color: AppColors.amber500),
                              SizedBox(height: 10),
                              Text('Belum Ada Data Juara Siswa Binaan',
                                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.gray800)),
                              SizedBox(height: 4),
                              Text(
                                'Daftar prestasi siswa yang memilih Anda sebagai Guru Pembina atau terkait ekstrakurikuler binaan Anda akan tampil di sini.',
                                textAlign: TextAlign.center,
                                style: TextStyle(fontSize: 12, color: AppColors.gray500),
                              ),
                            ],
                          ),
                        )
                      else
                        ..._filtered.map(_buildAchievementCard),
                    ],
                  ),
      ),
    );
  }

  Widget _buildSummaryBanner() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF064E3B), Color(0xFF047857), Color(0xFF059669)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(18),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.emoji_events_rounded, color: Color(0xFFFDE68A), size: 22),
              SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Rekap Juara Siswa Binaan',
                  style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.w800),
                ),
              ),
            ],
          ),
          if (_myExtracurriculars.isNotEmpty) ...[
            const SizedBox(height: 8),
            Wrap(
              spacing: 6,
              runSpacing: 6,
              children: _myExtracurriculars.map((e) => Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.18),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  '🏅 ${e['name']}',
                  style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                ),
              )).toList(),
            ),
          ],
          const SizedBox(height: 14),
          Row(
            children: [
              _statBox('${_stats['total'] ?? 0}', 'Total Juara'),
              const SizedBox(width: 8),
              _statBox('${_stats['approved'] ?? 0}', 'Disetujui'),
              const SizedBox(width: 8),
              _statBox('${_stats['nasional_inter'] ?? 0}', 'Nas/Internl.'),
              const SizedBox(width: 8),
              _statBox('${_stats['total_students'] ?? 0}', 'Siswa'),
            ],
          ),
        ],
      ),
    );
  }

  Widget _statBox(String value, String label) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 6),
        decoration: BoxDecoration(
          color: Colors.white.withValues(alpha: 0.14),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          children: [
            Text(value, style: const TextStyle(color: Colors.white, fontSize: 17, fontWeight: FontWeight.w900)),
            const SizedBox(height: 2),
            Text(label, style: const TextStyle(color: Color(0xFFD1FAE5), fontSize: 10, fontWeight: FontWeight.w600), maxLines: 1, overflow: TextOverflow.ellipsis),
          ],
        ),
      ),
    );
  }

  Widget _buildFilters() {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.gray200),
      ),
      child: Column(
        children: [
          TextField(
            onChanged: (v) => setState(() => _search = v),
            decoration: InputDecoration(
              hintText: 'Cari nama siswa, judul lomba, atau juara...',
              hintStyle: const TextStyle(fontSize: 12, color: AppColors.gray400),
              prefixIcon: const Icon(Icons.search_rounded, size: 18, color: AppColors.gray400),
              filled: true,
              fillColor: AppColors.gray50,
              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.gray200)),
              enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.gray200)),
            ),
          ),
          if (_myExtracurriculars.length > 1) ...[
            const SizedBox(height: 10),
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  ChoiceChip(
                    label: const Text('Semua Ekstra', style: TextStyle(fontSize: 11)),
                    selected: _selectedExtraId == null,
                    onSelected: (_) {
                      setState(() => _selectedExtraId = null);
                      _load();
                    },
                  ),
                  const SizedBox(width: 6),
                  ..._myExtracurriculars.map((e) => Padding(
                    padding: const EdgeInsets.only(right: 6),
                    child: ChoiceChip(
                      label: Text(e['name'].toString(), style: const TextStyle(fontSize: 11)),
                      selected: _selectedExtraId == e['id'],
                      onSelected: (_) {
                        setState(() => _selectedExtraId = e['id'] as int);
                        _load();
                      },
                    ),
                  )),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildAchievementCard(Map<String, dynamic> item) {
    final isBeregu = item['is_beregu'] == true;
    final teamMembers = List<Map<String, dynamic>>.from(item['team_members'] ?? []);
    final rank = item['rank']?.toString();
    final extraName = item['extracurricular_name']?.toString();
    final pembinaName = item['pembina_name']?.toString();
    final photoUrl = item['photo_url']?.toString();
    final certUrl = item['certificate_url']?.toString();

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.gray200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Wrap(
            spacing: 6,
            runSpacing: 6,
            children: [
              if (rank != null && rank.isNotEmpty)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(color: AppColors.amber100, borderRadius: BorderRadius.circular(8)),
                  child: Text('🥇 $rank', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.amber700)),
                ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(color: AppColors.blue50, borderRadius: BorderRadius.circular(8)),
                child: Text('Tingkat ${item['level_label'] ?? '-'}', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.blue600)),
              ),
              if (extraName != null && extraName.isNotEmpty)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(color: AppColors.purple50, borderRadius: BorderRadius.circular(8)),
                  child: Text('🏅 $extraName', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.purple700)),
                ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            item['title']?.toString() ?? '',
            style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: AppColors.gray900),
          ),
          const SizedBox(height: 4),
          Text(
            '${item['event_name'] ?? '-'} · ${item['achievement_date'] ?? '-'}',
            style: const TextStyle(fontSize: 11.5, color: AppColors.gray500),
          ),
          const Divider(height: 18),
          if (isBeregu && teamMembers.length > 1) ...[
            Text('👥 Beregu (${teamMembers.length} Siswa):',
                style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.purple700)),
            const SizedBox(height: 4),
            Wrap(
              spacing: 6,
              runSpacing: 4,
              children: teamMembers.map((tm) => Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(color: AppColors.gray100, borderRadius: BorderRadius.circular(8)),
                child: Text('${tm['name']} (${tm['class_name']})',
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.gray800)),
              )).toList(),
            ),
          ] else ...[
            Row(
              children: [
                const Icon(Icons.person_rounded, size: 15, color: AppColors.emerald600),
                const SizedBox(width: 5),
                Expanded(
                  child: Text(
                    '${item['student_name'] ?? '-'} (${item['student_class'] ?? '-'})',
                    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.gray800),
                  ),
                ),
              ],
            ),
          ],
          if (pembinaName != null) ...[
            const SizedBox(height: 4),
            Text('👨‍🏫 Pembina: $pembinaName',
                style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.emerald600)),
          ],
          if ((photoUrl != null && photoUrl.isNotEmpty) || (certUrl != null && certUrl.isNotEmpty)) ...[
            const SizedBox(height: 10),
            Row(
              children: [
                if (photoUrl != null && photoUrl.isNotEmpty)
                  OutlinedButton.icon(
                    onPressed: () => _openUrl(photoUrl),
                    icon: const Icon(Icons.photo_camera_outlined, size: 14),
                    label: const Text('Foto Kegiatan', style: TextStyle(fontSize: 11)),
                  ),
                if (photoUrl != null && certUrl != null) const SizedBox(width: 8),
                if (certUrl != null && certUrl.isNotEmpty)
                  OutlinedButton.icon(
                    onPressed: () => _openUrl(certUrl),
                    icon: const Icon(Icons.picture_as_pdf_outlined, size: 14),
                    label: const Text('Piagam / Sertifikat', style: TextStyle(fontSize: 11)),
                  ),
              ],
            ),
          ],
        ],
      ),
    );
  }
}

