import 'package:flutter/material.dart';
import '../../../theme/app_colors.dart';

// ─── Status badge ─────────────────────────────────────────────────────────────

class StatusBadge extends StatelessWidget {
  final String status;
  const StatusBadge(this.status, {super.key});

  @override
  Widget build(BuildContext context) {
    final (bg, fg, label) = switch (status) {
      'pending'    => (AppColors.amber100,   AppColors.amber500,  'Menunggu'),
      'approved'   => (AppColors.green100,   AppColors.green600,  'Disetujui'),
      'rejected'   => (AppColors.red100,     AppColors.red500,    'Ditolak'),
      'hadir'      => (AppColors.green100,   AppColors.green600,  'Hadir'),
      'terlambat'  => (AppColors.yellow100,  AppColors.yellow600, 'Terlambat'),
      'izin'       => (AppColors.blue100,    AppColors.blue600,   'Izin'),
      'sakit'      => (const Color(0xFFF3E8FF), AppColors.purple500, 'Sakit'),
      'dispensasi' => (AppColors.teal500.withValues(alpha: 0.15), AppColors.teal600, 'Dispen'),
      'alpa'       => (AppColors.red100,     AppColors.red500,    'Alpa'),
      _            => (AppColors.gray100,    AppColors.gray500,   status),
    };
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(8)),
      child: Text(label, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: fg)),
    );
  }
}

// ─── Permit type badge ────────────────────────────────────────────────────────

class TypeBadge extends StatelessWidget {
  final String type;
  const TypeBadge(this.type, {super.key});

  @override
  Widget build(BuildContext context) {
    final (bg, fg, label) = switch (type) {
      'izin'       => (AppColors.sky100,     AppColors.sky700,    'Izin'),
      'sakit'      => (const Color(0xFFF3E8FF), AppColors.purple500, 'Sakit'),
      'dispensasi' => (AppColors.orange100,  AppColors.orange600, 'Dispen'),
      _            => (AppColors.gray100,    AppColors.gray500,   type),
    };
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(8)),
      child: Text(label, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: fg)),
    );
  }
}

// ─── Class + filter selector bar ─────────────────────────────────────────────

typedef ClassPickerCallback = void Function(int classId);

class ClassFilterBar extends StatelessWidget {
  final List<({int id, String name})> classes;
  final int? selectedId;
  final ClassPickerCallback onChanged;

  const ClassFilterBar({
    super.key,
    required this.classes,
    required this.selectedId,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    if (classes.isEmpty) return const SizedBox.shrink();

    return Container(
      height: 40,
      margin: const EdgeInsets.only(bottom: 12),
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        itemCount: classes.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (_, i) {
          final cls = classes[i];
          final selected = cls.id == selectedId;
          return GestureDetector(
            onTap: () => onChanged(cls.id),
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 150),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 0),
              decoration: BoxDecoration(
                color: selected ? AppColors.blue600 : AppColors.white,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(
                  color: selected ? AppColors.blue600 : AppColors.gray200,
                ),
              ),
              alignment: Alignment.center,
              child: Text(
                cls.name,
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: selected ? Colors.white : AppColors.gray600,
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}

// ─── Empty state ──────────────────────────────────────────────────────────────

class EmptyState extends StatelessWidget {
  final String message;
  final IconData icon;

  const EmptyState({
    super.key,
    required this.message,
    this.icon = Icons.inbox_outlined,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, size: 52, color: AppColors.gray300),
          const SizedBox(height: 12),
          Text(
            message,
            style: const TextStyle(fontSize: 14, color: AppColors.gray400),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}

// ─── Error / Retry ────────────────────────────────────────────────────────────

class ErrorRetry extends StatelessWidget {
  final VoidCallback onRetry;

  const ErrorRetry({super.key, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.cloud_off_rounded, size: 48, color: AppColors.gray400),
          const SizedBox(height: 12),
          const Text(
            'Gagal memuat data',
            style: TextStyle(fontSize: 14, color: AppColors.gray600),
          ),
          const SizedBox(height: 8),
          TextButton(onPressed: onRetry, child: const Text('Coba Lagi')),
        ],
      ),
    );
  }
}

// ─── Confirm / reject dialog ──────────────────────────────────────────────────

Future<String?> showRejectDialog(BuildContext context, {required String title}) {
  final ctrl = TextEditingController();
  return showDialog<String>(
    context: context,
    builder: (_) => AlertDialog(
      title: Text(title, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      content: TextField(
        controller: ctrl,
        autofocus: true,
        maxLines: 3,
        decoration: const InputDecoration(
          hintText: 'Alasan penolakan...',
          border: OutlineInputBorder(),
        ),
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context),
          child: const Text('Batal'),
        ),
        FilledButton(
          onPressed: () {
            if (ctrl.text.trim().isEmpty) return;
            Navigator.pop(context, ctrl.text.trim());
          },
          style: FilledButton.styleFrom(backgroundColor: AppColors.red500),
          child: const Text('Tolak'),
        ),
      ],
    ),
  );
}

Future<bool?> showApproveDialog(BuildContext context, {required String title}) {
  return showDialog<bool>(
    context: context,
    builder: (_) => AlertDialog(
      title: Text(title, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      content: const Text('Yakin menyetujui pengajuan ini?'),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context, false),
          child: const Text('Batal'),
        ),
        FilledButton(
          onPressed: () => Navigator.pop(context, true),
          style: FilledButton.styleFrom(backgroundColor: AppColors.green600),
          child: const Text('Setujui'),
        ),
      ],
    ),
  );
}
