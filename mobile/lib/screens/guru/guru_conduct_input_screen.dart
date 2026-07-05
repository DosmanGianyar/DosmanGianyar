import 'package:flutter/material.dart';
import '../../models/guru_models.dart';
import '../../services/guru_service.dart';
import '../../theme/app_colors.dart';

class GuruConductInputScreen extends StatefulWidget {
  const GuruConductInputScreen({super.key});

  @override
  State<GuruConductInputScreen> createState() => _GuruConductInputScreenState();
}

class _GuruConductInputScreenState extends State<GuruConductInputScreen>
    with SingleTickerProviderStateMixin {
  late final TabController _tabCtrl;

  List<ConductCategory>      _prestasiCats = [];
  List<SimpleStudent>        _students     = [];
  List<Map<String, dynamic>> _classes      = [];

  bool _loadingCats     = true;
  bool _loadingStudents = false;
  bool _submitting      = false;

  int?           _selectedClassId;
  SimpleStudent? _selectedStudent;

  // Pelanggaran
  final _descriptionCtrl = TextEditingController();
  String? _selectedSeverity;

  // Prestasi
  String           _prestasiType     = 'perilaku'; // 'perilaku' | 'lomba'
  ConductCategory? _selectedCategory; // untuk perilaku
  final _lombaNameCtrl  = TextEditingController();
  String? _selectedLombaLevel;
  String? _selectedLombaRank;

  // Shared
  final _noteCtrl   = TextEditingController();
  final _searchCtrl = TextEditingController();

  // Riwayat
  List<ConductHistoryItem> _history     = [];
  bool    _historyLoading  = false;
  bool    _historyLoadMore = false;
  int     _historyPage     = 1;
  int     _historyLastPage = 1;
  String? _historyFilter;
  late final ScrollController _historyScrollCtrl;

  // Data statis
  static const _lombaLevels = [
    ('sekolah',       'Tingkat Sekolah'),
    ('kabupaten',     'Kabupaten/Kota'),
    ('provinsi',      'Provinsi'),
    ('nasional',      'Nasional'),
    ('internasional', 'Internasional'),
  ];
  static const _lombaRanks = [
    ('juara_1', 'Juara 1'),
    ('juara_2', 'Juara 2'),
    ('juara_3', 'Juara 3'),
    ('harapan', 'Juara Harapan'),
    ('peserta', 'Peserta'),
  ];

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 3, vsync: this);
    _tabCtrl.addListener(() {
      if (_tabCtrl.indexIsChanging) return;
      if (_tabCtrl.index == 2 && _history.isEmpty && !_historyLoading) {
        _loadHistory(reset: true);
      } else if (_tabCtrl.index != 2) {
        _resetForm();
      }
    });
    _historyScrollCtrl = ScrollController()
      ..addListener(() {
        if (_historyScrollCtrl.position.pixels >=
            _historyScrollCtrl.position.maxScrollExtent - 100) {
          _loadMoreHistory();
        }
      });
    _loadInitial();
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    _noteCtrl.dispose();
    _searchCtrl.dispose();
    _descriptionCtrl.dispose();
    _lombaNameCtrl.dispose();
    _historyScrollCtrl.dispose();
    super.dispose();
  }

  void _resetForm() {
    setState(() {
      _selectedStudent    = null;
      _selectedCategory   = null;
      _selectedSeverity   = null;
      _selectedLombaLevel = null;
      _selectedLombaRank  = null;
      _descriptionCtrl.clear();
      _lombaNameCtrl.clear();
      _noteCtrl.clear();
      _students = [];
    });
  }

  Future<void> _loadInitial() async {
    try {
      final results = await Future.wait([
        GuruService.getConductCategories(),
        GuruService.getConductClasses(),
      ]);
      final cats    = results[0] as Map<String, List<ConductCategory>>;
      final classes = results[1] as List<Map<String, dynamic>>;
      if (mounted) {
        setState(() {
          _prestasiCats = cats['prestasi'] ?? [];
          _classes      = classes;
          _loadingCats  = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loadingCats = false);
    }
  }

  Future<void> _loadStudents() async {
    setState(() { _loadingStudents = true; _students = []; _selectedStudent = null; });
    try {
      final list = await GuruService.getConductStudents(
        classId: _selectedClassId,
        q: _searchCtrl.text.trim().isEmpty ? null : _searchCtrl.text.trim(),
      );
      if (mounted) setState(() { _students = list; _loadingStudents = false; });
    } catch (_) {
      if (mounted) setState(() => _loadingStudents = false);
    }
  }

  Future<void> _loadHistory({bool reset = false}) async {
    if (reset) {
      setState(() { _historyLoading = true; _history = []; _historyPage = 1; });
    }
    try {
      final body = await GuruService.getConductHistory(
        type: _historyFilter,
        page: reset ? 1 : _historyPage,
      );
      final items = (body['data'] as List)
          .map((e) => ConductHistoryItem.fromJson(e as Map<String, dynamic>))
          .toList();
      final meta = body['meta'] as Map<String, dynamic>;
      if (mounted) {
        setState(() {
          if (reset) {
            _history = items;
          } else {
            _history.addAll(items);
          }
          _historyLastPage = meta['last_page'] as int;
          _historyLoading  = false;
          _historyLoadMore = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() { _historyLoading = false; _historyLoadMore = false; });
    }
  }

  void _loadMoreHistory() {
    if (_historyLoadMore || _historyLoading) return;
    if (_historyPage >= _historyLastPage) return;
    setState(() { _historyLoadMore = true; _historyPage++; });
    _loadHistory();
  }

  Future<void> _submit() async {
    final isPrestasi = _tabCtrl.index == 1;

    if (_selectedStudent == null) {
      _showSnack('Pilih siswa terlebih dahulu', AppColors.orange500);
      return;
    }

    if (!isPrestasi) {
      // Pelanggaran
      if (_descriptionCtrl.text.trim().isEmpty) {
        _showSnack('Isi deskripsi pelanggaran', AppColors.orange500);
        return;
      }
      if (_selectedSeverity == null) {
        _showSnack('Pilih tingkat pelanggaran', AppColors.orange500);
        return;
      }
    } else if (_prestasiType == 'perilaku') {
      if (_selectedCategory == null) {
        _showSnack('Pilih kategori prestasi perilaku', AppColors.orange500);
        return;
      }
    } else {
      // Lomba
      if (_lombaNameCtrl.text.trim().isEmpty) {
        _showSnack('Isi nama lomba', AppColors.orange500);
        return;
      }
      if (_selectedLombaLevel == null) {
        _showSnack('Pilih tingkat lomba', AppColors.orange500);
        return;
      }
      if (_selectedLombaRank == null) {
        _showSnack('Pilih peringkat lomba', AppColors.orange500);
        return;
      }
    }

    setState(() => _submitting = true);
    try {
      final msg = await GuruService.createConductLog(
        studentId:    _selectedStudent!.id,
        type:         isPrestasi ? 'prestasi' : 'pelanggaran',
        description:  isPrestasi ? null : _descriptionCtrl.text.trim(),
        severity:     isPrestasi ? null : _selectedSeverity,
        prestasiType: isPrestasi ? _prestasiType : null,
        categoryId:   (isPrestasi && _prestasiType == 'perilaku') ? _selectedCategory!.id : null,
        lombaName:    (isPrestasi && _prestasiType == 'lomba') ? _lombaNameCtrl.text.trim() : null,
        lombaLevel:   (isPrestasi && _prestasiType == 'lomba') ? _selectedLombaLevel : null,
        lombaRank:    (isPrestasi && _prestasiType == 'lomba') ? _selectedLombaRank : null,
        note:         _noteCtrl.text.trim().isEmpty ? null : _noteCtrl.text.trim(),
      );
      if (mounted) {
        _showSnack(msg, AppColors.emerald600);
        _resetForm();
        if (_history.isNotEmpty) _loadHistory(reset: true);
      }
    } catch (e) {
      if (mounted) _showSnack(e.toString(), AppColors.red500);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  void _showSnack(String msg, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: color,
      behavior: SnackBarBehavior.floating,
    ));
  }

  // ── Build ─────────────────────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate100,
      appBar: AppBar(
        title: const Text('Catat Pelanggaran / Prestasi'),
        bottom: TabBar(
          controller: _tabCtrl,
          tabs: const [
            Tab(text: 'Pelanggaran'),
            Tab(text: 'Prestasi'),
            Tab(text: 'Riwayat'),
          ],
          labelColor: AppColors.blue600,
          indicatorColor: AppColors.blue600,
          unselectedLabelColor: AppColors.gray400,
        ),
      ),
      body: _loadingCats
          ? const Center(child: CircularProgressIndicator())
          : TabBarView(
              controller: _tabCtrl,
              children: [
                _buildPelanggaranForm(),
                _buildPrestasiForm(),
                _buildHistoryTab(),
              ],
            ),
    );
  }

  // ── Pelanggaran Tab ───────────────────────────────────────────────────────

  Widget _buildPelanggaranForm() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _sectionLabel('1. Pilih Kelas'),
          const SizedBox(height: 8),
          _ClassDropdown(classes: _classes, selected: _selectedClassId, onChanged: (id) {
            setState(() { _selectedClassId = id; _selectedStudent = null; });
            _loadStudents();
          }),
          const SizedBox(height: 16),

          _sectionLabel('2. Pilih Siswa'),
          const SizedBox(height: 8),
          _studentSearchBar(),
          const SizedBox(height: 8),
          _studentPickerArea(),
          const SizedBox(height: 16),

          _sectionLabel('3. Deskripsi Pelanggaran'),
          const SizedBox(height: 8),
          _textField(controller: _descriptionCtrl, hint: 'Ceritakan pelanggaran yang dilakukan...', maxLines: 4),
          const SizedBox(height: 16),

          _sectionLabel('4. Tingkat Pelanggaran'),
          const SizedBox(height: 8),
          Row(
            children: ['ringan', 'sedang', 'berat'].map((s) {
              final isSelected = _selectedSeverity == s;
              final color = switch (s) {
                'ringan' => AppColors.amber500,
                'sedang' => AppColors.orange500,
                _        => AppColors.red500,
              };
              return Expanded(
                child: Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: GestureDetector(
                    onTap: () => setState(() => _selectedSeverity = s),
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 150),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      decoration: BoxDecoration(
                        color: isSelected ? color : AppColors.white,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: isSelected ? color : AppColors.gray200, width: isSelected ? 2 : 1),
                      ),
                      alignment: Alignment.center,
                      child: Text(
                        s[0].toUpperCase() + s.substring(1),
                        style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: isSelected ? Colors.white : AppColors.gray600),
                      ),
                    ),
                  ),
                ),
              );
            }).toList(),
          ),
          const SizedBox(height: 16),

          _sectionLabel('5. Catatan (opsional)'),
          const SizedBox(height: 8),
          _textField(controller: _noteCtrl, hint: 'Catatan tambahan...', maxLines: 2),
          const SizedBox(height: 24),

          if (_selectedStudent != null && _selectedSeverity != null) _pelanggaranPreview(),
          _submitButton(isPrestasi: false),
          const SizedBox(height: 32),
        ],
      ),
    );
  }

  Widget _pelanggaranPreview() {
    final color = switch (_selectedSeverity) {
      'ringan' => AppColors.amber500, 'sedang' => AppColors.orange500, _ => AppColors.red500,
    };
    final label = _selectedSeverity![0].toUpperCase() + _selectedSeverity!.substring(1);
    return Container(
      padding: const EdgeInsets.all(14),
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: AppColors.red50,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color.withValues(alpha: 0.4)),
      ),
      child: Row(children: [
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
          decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(6)),
          child: Text(label, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Colors.white)),
        ),
        const SizedBox(width: 10),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(_selectedStudent!.name,
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.gray800)),
          if (_descriptionCtrl.text.isNotEmpty)
            Text(_descriptionCtrl.text,
                style: const TextStyle(fontSize: 11, color: AppColors.gray500),
                maxLines: 2, overflow: TextOverflow.ellipsis),
        ])),
      ]),
    );
  }

  // ── Prestasi Tab ──────────────────────────────────────────────────────────

  Widget _buildPrestasiForm() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _sectionLabel('1. Pilih Kelas'),
          const SizedBox(height: 8),
          _ClassDropdown(classes: _classes, selected: _selectedClassId, onChanged: (id) {
            setState(() { _selectedClassId = id; _selectedStudent = null; });
            _loadStudents();
          }),
          const SizedBox(height: 16),

          _sectionLabel('2. Pilih Siswa'),
          const SizedBox(height: 8),
          _studentSearchBar(),
          const SizedBox(height: 8),
          _studentPickerArea(),
          const SizedBox(height: 16),

          // ── Toggle Jenis Prestasi ─────────────────────────────────────
          _sectionLabel('3. Jenis Prestasi'),
          const SizedBox(height: 8),
          Container(
            decoration: BoxDecoration(
              color: AppColors.gray100,
              borderRadius: BorderRadius.circular(10),
            ),
            padding: const EdgeInsets.all(4),
            child: Row(
              children: [
                _prestasiToggle('perilaku', 'Perilaku / Harian', Icons.thumb_up_rounded),
                _prestasiToggle('lomba',    'Prestasi Lomba',    Icons.emoji_events_rounded),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // ── Konten berdasarkan jenis ──────────────────────────────────
          if (_prestasiType == 'perilaku') ...[
            _sectionLabel('4. Pilih Kategori Perilaku'),
            const SizedBox(height: 8),
            if (_prestasiCats.isEmpty)
              const Text('Tidak ada kategori prestasi.',
                  style: TextStyle(color: AppColors.gray400, fontSize: 13))
            else
              Wrap(
                spacing: 8, runSpacing: 8,
                children: _prestasiCats.map((cat) {
                  final selected = _selectedCategory?.id == cat.id;
                  return GestureDetector(
                    onTap: () => setState(() => _selectedCategory = cat),
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 150),
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      decoration: BoxDecoration(
                        color: selected ? AppColors.emerald600 : AppColors.white,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(
                          color: selected ? AppColors.emerald600 : AppColors.gray200,
                          width: selected ? 2 : 1,
                        ),
                      ),
                      child: Text(cat.name,
                          style: TextStyle(fontSize: 13, fontWeight: FontWeight.w500,
                              color: selected ? Colors.white : AppColors.gray700)),
                    ),
                  );
                }).toList(),
              ),
          ] else ...[
            // ── Form Lomba ─────────────────────────────────────────────
            _sectionLabel('4. Nama Lomba'),
            const SizedBox(height: 8),
            _textField(controller: _lombaNameCtrl, hint: 'Contoh: Olimpiade Matematika Nasional...', maxLines: 1),
            const SizedBox(height: 16),

            _sectionLabel('5. Tingkat Lomba'),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8, runSpacing: 8,
              children: _lombaLevels.map(((String val, String label) pair) {
                final isSelected = _selectedLombaLevel == pair.$1;
                return GestureDetector(
                  onTap: () => setState(() => _selectedLombaLevel = pair.$1),
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 150),
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    decoration: BoxDecoration(
                      color: isSelected ? AppColors.blue600 : AppColors.white,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(
                        color: isSelected ? AppColors.blue600 : AppColors.gray200,
                        width: isSelected ? 2 : 1,
                      ),
                    ),
                    child: Text(pair.$2,
                        style: TextStyle(fontSize: 13, fontWeight: FontWeight.w500,
                            color: isSelected ? Colors.white : AppColors.gray700)),
                  ),
                );
              }).toList(),
            ),
            const SizedBox(height: 16),

            _sectionLabel('6. Peringkat'),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8, runSpacing: 8,
              children: _lombaRanks.map(((String val, String label) pair) {
                final isSelected = _selectedLombaRank == pair.$1;
                final color = switch (pair.$1) {
                  'juara_1' => const Color(0xFFD4AF37), // gold
                  'juara_2' => const Color(0xFF9EA5A8), // silver
                  'juara_3' => const Color(0xFFCD7F32), // bronze
                  _         => AppColors.blue600,
                };
                return GestureDetector(
                  onTap: () => setState(() => _selectedLombaRank = pair.$1),
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 150),
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 9),
                    decoration: BoxDecoration(
                      color: isSelected ? color : AppColors.white,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(
                        color: isSelected ? color : AppColors.gray200,
                        width: isSelected ? 2 : 1,
                      ),
                    ),
                    child: Text(pair.$2,
                        style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600,
                            color: isSelected ? Colors.white : AppColors.gray700)),
                  ),
                );
              }).toList(),
            ),
          ],
          const SizedBox(height: 16),

          _sectionLabel(_prestasiType == 'perilaku' ? '5. Catatan (opsional)' : '7. Catatan (opsional)'),
          const SizedBox(height: 8),
          _textField(controller: _noteCtrl, hint: 'Catatan tambahan...', maxLines: 3),
          const SizedBox(height: 24),

          if (_selectedStudent != null) _prestasiPreview(),
          _submitButton(isPrestasi: true),
          const SizedBox(height: 32),
        ],
      ),
    );
  }

  Widget _prestasiToggle(String value, String label, IconData icon) {
    final isSelected = _prestasiType == value;
    return Expanded(
      child: GestureDetector(
        onTap: () {
          if (_prestasiType != value) {
            setState(() {
              _prestasiType     = value;
              _selectedCategory = null;
              _selectedLombaLevel = null;
              _selectedLombaRank  = null;
              _lombaNameCtrl.clear();
            });
          }
        },
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 150),
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: BoxDecoration(
            color: isSelected ? Colors.white : Colors.transparent,
            borderRadius: BorderRadius.circular(8),
            boxShadow: isSelected ? [BoxShadow(color: Colors.black.withValues(alpha: 0.08), blurRadius: 4, offset: const Offset(0, 2))] : null,
          ),
          child: Row(mainAxisAlignment: MainAxisAlignment.center, children: [
            Icon(icon, size: 16, color: isSelected ? AppColors.emerald600 : AppColors.gray500),
            const SizedBox(width: 6),
            Text(label, style: TextStyle(
              fontSize: 13, fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
              color: isSelected ? AppColors.emerald600 : AppColors.gray500,
            )),
          ]),
        ),
      ),
    );
  }

  Widget _prestasiPreview() {
    if (_prestasiType == 'perilaku' && _selectedCategory == null) return const SizedBox.shrink();
    if (_prestasiType == 'lomba' && (_lombaNameCtrl.text.isEmpty || _selectedLombaRank == null)) return const SizedBox.shrink();

    final rankLabel = _lombaRanks.where((r) => r.$1 == _selectedLombaRank).firstOrNull?.$2;
    final levelLabel = _lombaLevels.where((l) => l.$1 == _selectedLombaLevel).firstOrNull?.$2;

    return Container(
      padding: const EdgeInsets.all(14),
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: AppColors.emerald50,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: AppColors.emerald600.withValues(alpha: 0.3)),
      ),
      child: Row(children: [
        Icon(
          _prestasiType == 'lomba' ? Icons.emoji_events_rounded : Icons.star_rounded,
          color: AppColors.emerald600, size: 20,
        ),
        const SizedBox(width: 10),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(_selectedStudent!.name,
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.emerald600)),
          if (_prestasiType == 'perilaku')
            Text(_selectedCategory!.name, style: const TextStyle(fontSize: 12, color: AppColors.gray700))
          else ...[
            Text(_lombaNameCtrl.text, style: const TextStyle(fontSize: 12, color: AppColors.gray700),
                maxLines: 1, overflow: TextOverflow.ellipsis),
            if (rankLabel != null || levelLabel != null)
              Text('${rankLabel ?? ''} ${levelLabel != null ? '· $levelLabel' : ''}'.trim(),
                  style: const TextStyle(fontSize: 11, color: AppColors.gray500)),
          ],
        ])),
      ]),
    );
  }

  // ── Riwayat Tab ───────────────────────────────────────────────────────────

  Widget _buildHistoryTab() {
    return Column(children: [
      Container(
        color: AppColors.white,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        child: Row(children: [
          _filterChip('Semua',       null),
          const SizedBox(width: 8),
          _filterChip('Pelanggaran', 'pelanggaran'),
          const SizedBox(width: 8),
          _filterChip('Prestasi',    'prestasi'),
        ]),
      ),
      const Divider(height: 1),
      Expanded(
        child: _historyLoading
            ? const Center(child: CircularProgressIndicator())
            : _history.isEmpty
                ? const Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
                    Icon(Icons.history_rounded, size: 48, color: AppColors.gray300),
                    SizedBox(height: 12),
                    Text('Belum ada catatan',
                        style: TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: AppColors.gray500)),
                    SizedBox(height: 4),
                    Text('Catatan yang Anda buat\nakan muncul di sini',
                        textAlign: TextAlign.center,
                        style: TextStyle(fontSize: 13, color: AppColors.gray400)),
                  ]))
                : RefreshIndicator(
                    onRefresh: () => _loadHistory(reset: true),
                    child: ListView.builder(
                      controller: _historyScrollCtrl,
                      padding: const EdgeInsets.all(16),
                      itemCount: _history.length + (_historyLoadMore ? 1 : 0),
                      itemBuilder: (_, i) {
                        if (i == _history.length) {
                          return const Center(child: Padding(
                            padding: EdgeInsets.all(16),
                            child: CircularProgressIndicator(strokeWidth: 2),
                          ));
                        }
                        return _HistoryCard(item: _history[i]);
                      },
                    ),
                  ),
      ),
    ]);
  }

  Widget _filterChip(String label, String? value) {
    final isActive = _historyFilter == value;
    final color = value == null
        ? AppColors.blue600
        : value == 'pelanggaran' ? AppColors.red500 : AppColors.emerald600;
    return GestureDetector(
      onTap: () {
        if (_historyFilter == value) return;
        setState(() => _historyFilter = value);
        _loadHistory(reset: true);
      },
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
        decoration: BoxDecoration(
          color: isActive ? color : AppColors.gray100,
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(label, style: TextStyle(
          fontSize: 13, fontWeight: FontWeight.w600,
          color: isActive ? Colors.white : AppColors.gray600,
        )),
      ),
    );
  }

  // ── Shared Widgets ────────────────────────────────────────────────────────

  Widget _studentSearchBar() {
    return Row(children: [
      Expanded(
        child: TextField(
          controller: _searchCtrl,
          decoration: InputDecoration(
            hintText: 'Cari nama / NIS...',
            prefixIcon: const Icon(Icons.search, size: 18, color: AppColors.gray400),
            filled: true, fillColor: AppColors.white,
            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.gray200)),
            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.gray200)),
            focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.blue600, width: 1.5)),
          ),
          onSubmitted: (_) => _loadStudents(),
        ),
      ),
      const SizedBox(width: 8),
      ElevatedButton(
        onPressed: _loadStudents,
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.blue600, foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        ),
        child: const Icon(Icons.search, size: 18),
      ),
    ]);
  }

  Widget _studentPickerArea() {
    if (_selectedStudent != null) {
      return _SelectedChip(
        label: '${_selectedStudent!.name} (${_selectedStudent!.nis ?? '—'})',
        onRemove: () => setState(() => _selectedStudent = null),
      );
    }
    if (_loadingStudents) {
      return const Center(child: Padding(padding: EdgeInsets.all(16), child: CircularProgressIndicator(strokeWidth: 2)));
    }
    if (_students.isNotEmpty) {
      return Container(
        height: 180,
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: AppColors.gray200),
        ),
        child: ListView.separated(
          padding: const EdgeInsets.all(4),
          itemCount: _students.length,
          separatorBuilder: (_, __) => const Divider(height: 1),
          itemBuilder: (_, i) {
            final s = _students[i];
            return ListTile(
              dense: true,
              title: Text(s.name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
              subtitle: Text('${s.nis ?? '—'} · ${s.className ?? ''}',
                  style: const TextStyle(fontSize: 11, color: AppColors.gray500)),
              onTap: () => setState(() { _selectedStudent = s; _students = []; }),
            );
          },
        ),
      );
    }
    return const SizedBox.shrink();
  }

  Widget _textField({required TextEditingController controller, required String hint, int maxLines = 3}) {
    return TextField(
      controller: controller, maxLines: maxLines,
      decoration: InputDecoration(
        hintText: hint, filled: true, fillColor: AppColors.white,
        contentPadding: const EdgeInsets.all(12),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.gray200)),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.gray200)),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.blue600, width: 1.5)),
      ),
    );
  }

  Widget _submitButton({required bool isPrestasi}) {
    final color = isPrestasi ? AppColors.emerald600 : AppColors.red500;
    return SizedBox(
      width: double.infinity,
      child: FilledButton.icon(
        onPressed: _submitting ? null : _submit,
        icon: _submitting
            ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
            : Icon(isPrestasi ? Icons.star_rounded : Icons.warning_rounded, size: 18),
        label: Text(_submitting ? 'Menyimpan...' : 'Simpan ${isPrestasi ? "Prestasi" : "Pelanggaran"}'),
        style: FilledButton.styleFrom(
          backgroundColor: color,
          padding: const EdgeInsets.symmetric(vertical: 14),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
      ),
    );
  }

  Widget _sectionLabel(String text) => Text(text,
      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.gray700));
}

// ── History Card ──────────────────────────────────────────────────────────────

class _HistoryCard extends StatelessWidget {
  final ConductHistoryItem item;
  const _HistoryCard({required this.item});

  @override
  Widget build(BuildContext context) {
    final isPelanggaran = item.type == 'pelanggaran';
    final isLomba       = item.prestasiType == 'lomba';

    final severityColor = switch (item.severity) {
      'ringan' => AppColors.amber500,
      'sedang' => AppColors.orange500,
      'berat'  => AppColors.red500,
      _        => AppColors.gray400,
    };
    final rankColor = switch (item.lombaRank) {
      'juara_1' => const Color(0xFFD4AF37),
      'juara_2' => const Color(0xFF9EA5A8),
      'juara_3' => const Color(0xFFCD7F32),
      _         => AppColors.blue600,
    };

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.gray100),
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 4, offset: const Offset(0, 2))],
      ),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          // Header: nama + tanggal
          Row(children: [
            Expanded(child: Text(item.studentName,
                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.gray800))),
            Text(item.dateLabel, style: const TextStyle(fontSize: 11, color: AppColors.gray400)),
          ]),
          const SizedBox(height: 2),
          Text('${item.studentNis ?? '—'} · ${item.className}',
              style: const TextStyle(fontSize: 12, color: AppColors.gray500)),
          const SizedBox(height: 10),

          // Badge row
          Wrap(spacing: 6, runSpacing: 6, children: [
            // Tipe utama
            _badge(
              isPelanggaran ? 'Pelanggaran' : isLomba ? 'Prestasi Lomba' : 'Prestasi Perilaku',
              isPelanggaran ? AppColors.red500 : AppColors.emerald600,
              isPelanggaran ? AppColors.red50 : AppColors.emerald50,
            ),
            // Sub badge
            if (isPelanggaran && item.severity != null)
              _solidBadge(item.severity![0].toUpperCase() + item.severity!.substring(1), severityColor),
            if (isLomba && item.lombaRankLabel != null)
              _solidBadge(item.lombaRankLabel!, rankColor),
            if (isLomba && item.lombaLevelLabel != null)
              _badge(item.lombaLevelLabel!, AppColors.blue600, AppColors.blue50),
            if (!isPelanggaran && !isLomba && item.categoryName != null)
              _badge(item.categoryName!, AppColors.blue600, AppColors.blue50),
          ]),

          // Konten utama
          if (isPelanggaran && item.description != null && item.description!.isNotEmpty) ...[
            const SizedBox(height: 8),
            Text(item.description!, style: const TextStyle(fontSize: 13, color: AppColors.gray700),
                maxLines: 3, overflow: TextOverflow.ellipsis),
          ],
          if (isLomba && item.lombaName != null) ...[
            const SizedBox(height: 8),
            Text(item.lombaName!, style: const TextStyle(fontSize: 13, color: AppColors.gray700),
                maxLines: 2, overflow: TextOverflow.ellipsis),
          ],

          // Catatan
          if (item.note != null && item.note!.isNotEmpty) ...[
            const SizedBox(height: 6),
            Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
              const Icon(Icons.notes_rounded, size: 13, color: AppColors.gray400),
              const SizedBox(width: 4),
              Expanded(child: Text(item.note!,
                  style: const TextStyle(fontSize: 12, color: AppColors.gray500),
                  maxLines: 2, overflow: TextOverflow.ellipsis)),
            ]),
          ],
        ]),
      ),
    );
  }

  Widget _badge(String label, Color textColor, Color bgColor) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
    decoration: BoxDecoration(
      color: bgColor,
      borderRadius: BorderRadius.circular(6),
      border: Border.all(color: textColor.withValues(alpha: 0.3)),
    ),
    child: Text(label, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: textColor)),
  );

  Widget _solidBadge(String label, Color color) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
    decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(6)),
    child: Text(label, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Colors.white)),
  );
}

// ── Helper Widgets ────────────────────────────────────────────────────────────

class _ClassDropdown extends StatelessWidget {
  final List<Map<String, dynamic>> classes;
  final int? selected;
  final void Function(int?) onChanged;

  const _ClassDropdown({required this.classes, required this.selected, required this.onChanged});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12),
      decoration: BoxDecoration(
        color: AppColors.white, borderRadius: BorderRadius.circular(10),
        border: Border.all(color: AppColors.gray200),
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<int?>(
          value: selected, isExpanded: true,
          hint: const Text('Semua kelas', style: TextStyle(fontSize: 13, color: AppColors.gray400)),
          items: [
            const DropdownMenuItem<int?>(value: null, child: Text('Semua kelas', style: TextStyle(fontSize: 13))),
            ...classes.map((c) => DropdownMenuItem<int?>(
              value: c['id'] as int,
              child: Text(c['name'] as String, style: const TextStyle(fontSize: 13)),
            )),
          ],
          onChanged: onChanged,
        ),
      ),
    );
  }
}

class _SelectedChip extends StatelessWidget {
  final String label;
  final VoidCallback onRemove;

  const _SelectedChip({required this.label, required this.onRemove});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: AppColors.blue50, borderRadius: BorderRadius.circular(8),
        border: Border.all(color: AppColors.blue200),
      ),
      child: Row(children: [
        const Icon(Icons.person_rounded, size: 16, color: AppColors.blue600),
        const SizedBox(width: 8),
        Expanded(child: Text(label,
            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.blue600))),
        GestureDetector(
          onTap: onRemove,
          child: const Icon(Icons.close_rounded, size: 16, color: AppColors.blue600),
        ),
      ]),
    );
  }
}
