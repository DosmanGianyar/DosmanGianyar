import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/school_regulation.dart';
import '../../providers/regulation_provider.dart';
import '../../theme/app_colors.dart';

class SchoolRegulationScreen extends StatefulWidget {
  const SchoolRegulationScreen({super.key});

  @override
  State<SchoolRegulationScreen> createState() => _SchoolRegulationScreenState();
}

class _SchoolRegulationScreenState extends State<SchoolRegulationScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final p = context.read<RegulationProvider>();
      if (p.groups.isEmpty && !p.isLoading) p.fetch();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.gray50,
      body: Consumer<RegulationProvider>(
        builder: (_, p, __) => CustomScrollView(
          slivers: [
            _AppBar(),
            if (p.isLoading)
              const SliverFillRemaining(child: Center(child: CircularProgressIndicator()))
            else if (p.error != null)
              SliverFillRemaining(child: _ErrorView(p.error!, onRetry: p.fetch))
            else if (p.groups.isEmpty)
              const SliverFillRemaining(child: _EmptyView())
            else ...[
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 4),
                  child: Text(
                    'Berlaku untuk seluruh siswa SMA Negeri 1 Gianyar',
                    style: const TextStyle(fontSize: 12, color: AppColors.gray500),
                  ),
                ),
              ),
              SliverList(
                delegate: SliverChildBuilderDelegate(
                  (_, i) => _CategorySection(group: p.groups[i]),
                  childCount: p.groups.length,
                ),
              ),
              const SliverToBoxAdapter(child: SizedBox(height: 32)),
            ],
          ],
        ),
      ),
    );
  }
}

// ─── App Bar ──────────────────────────────────────────────────────────────────

class _AppBar extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return SliverAppBar(
      expandedHeight: 120,
      pinned: true,
      leading: IconButton(
        icon: const Icon(Icons.arrow_back_rounded, color: Colors.white),
        onPressed: () => Navigator.pop(context),
      ),
      flexibleSpace: FlexibleSpaceBar(
        background: Container(
          decoration: const BoxDecoration(gradient: AppColors.primaryGradient),
          child: SafeArea(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(20, 56, 20, 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  const Text(
                    'Tata Tertib Sekolah',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'SMA Negeri 1 Gianyar',
                    style: TextStyle(color: Colors.white.withOpacity(0.8), fontSize: 13),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

// ─── Category section ─────────────────────────────────────────────────────────

class _CategorySection extends StatefulWidget {
  final RegulationGroup group;
  const _CategorySection({required this.group});

  @override
  State<_CategorySection> createState() => _CategorySectionState();
}

class _CategorySectionState extends State<_CategorySection> {
  bool _expanded = true;

  (Color, Color, IconData) get _style => switch (widget.group.category) {
    'kehadiran'  => (AppColors.blue600,   AppColors.blue50,    Icons.calendar_today_rounded),
    'berpakaian' => (AppColors.amber500,  AppColors.amber100,  Icons.checkroom_rounded),
    'perilaku'   => (AppColors.green500,  AppColors.green100,  Icons.thumb_up_alt_rounded),
    'larangan'   => (AppColors.red500,    AppColors.red100,    Icons.block_rounded),
    _            => (AppColors.gray500,   AppColors.gray100,   Icons.info_outline_rounded),
  };

  @override
  Widget build(BuildContext context) {
    final (color, bg, icon) = _style;

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Section header — tappable to collapse/expand
          GestureDetector(
            onTap: () => setState(() => _expanded = !_expanded),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              decoration: BoxDecoration(
                color: bg,
                borderRadius: _expanded
                    ? const BorderRadius.vertical(top: Radius.circular(AppRadius.xl2))
                    : AppRadius.card,
                border: Border.all(color: color.withOpacity(0.25)),
              ),
              child: Row(
                children: [
                  Container(
                    width: 36,
                    height: 36,
                    decoration: BoxDecoration(
                      color: color.withOpacity(0.15),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Icon(icon, size: 20, color: color),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          widget.group.categoryLabel,
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w700,
                            color: color,
                          ),
                        ),
                        Text(
                          '${widget.group.items.length} peraturan',
                          style: TextStyle(fontSize: 11, color: color.withOpacity(0.7)),
                        ),
                      ],
                    ),
                  ),
                  Icon(
                    _expanded ? Icons.keyboard_arrow_up_rounded : Icons.keyboard_arrow_down_rounded,
                    color: color,
                  ),
                ],
              ),
            ),
          ),

          // Items
          if (_expanded)
            Container(
              decoration: BoxDecoration(
                color: AppColors.white,
                borderRadius: const BorderRadius.vertical(bottom: Radius.circular(AppRadius.xl2)),
                border: Border.all(color: color.withOpacity(0.2)),
                boxShadow: AppShadow.sm,
              ),
              child: ListView.separated(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: widget.group.items.length,
                separatorBuilder: (_, __) => Divider(
                  height: 1,
                  color: color.withOpacity(0.12),
                  indent: 16,
                  endIndent: 16,
                ),
                itemBuilder: (_, i) => _RegulationTile(
                  item:   widget.group.items[i],
                  number: i + 1,
                  color:  color,
                ),
              ),
            ),
        ],
      ),
    );
  }
}

// ─── Regulation tile ──────────────────────────────────────────────────────────

class _RegulationTile extends StatefulWidget {
  final RegulationItem item;
  final int            number;
  final Color          color;
  const _RegulationTile({required this.item, required this.number, required this.color});

  @override
  State<_RegulationTile> createState() => _RegulationTileState();
}

class _RegulationTileState extends State<_RegulationTile> {
  bool _showContent = false;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: () => setState(() => _showContent = !_showContent),
      borderRadius: BorderRadius.circular(AppRadius.xl),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Nomor urut
            Container(
              width: 26,
              height: 26,
              decoration: BoxDecoration(
                color: widget.color.withOpacity(0.12),
                borderRadius: BorderRadius.circular(13),
              ),
              child: Center(
                child: Text(
                  '${widget.number}',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: widget.color,
                  ),
                ),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          widget.item.title,
                          style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                            color: AppColors.gray800,
                          ),
                        ),
                      ),
                      Icon(
                        _showContent
                            ? Icons.keyboard_arrow_up_rounded
                            : Icons.keyboard_arrow_down_rounded,
                        size: 18,
                        color: AppColors.gray400,
                      ),
                    ],
                  ),
                  if (_showContent) ...[
                    const SizedBox(height: 8),
                    Text(
                      widget.item.content,
                      style: const TextStyle(
                        fontSize: 13,
                        color: AppColors.gray700,
                        height: 1.55,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Empty / Error ────────────────────────────────────────────────────────────

class _EmptyView extends StatelessWidget {
  const _EmptyView();

  @override
  Widget build(BuildContext context) {
    return const Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.scale_outlined, size: 52, color: AppColors.gray400),
          SizedBox(height: 12),
          Text('Belum ada tata tertib', style: TextStyle(color: AppColors.gray500)),
        ],
      ),
    );
  }
}

class _ErrorView extends StatelessWidget {
  final String       message;
  final VoidCallback onRetry;
  const _ErrorView(this.message, {required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.wifi_off_rounded, size: 48, color: AppColors.gray400),
            const SizedBox(height: 12),
            Text(message, textAlign: TextAlign.center, style: const TextStyle(color: AppColors.gray500)),
            const SizedBox(height: 16),
            FilledButton.icon(
              onPressed: onRetry,
              icon: const Icon(Icons.refresh_rounded, size: 16),
              label: const Text('Coba Lagi'),
            ),
          ],
        ),
      ),
    );
  }
}
