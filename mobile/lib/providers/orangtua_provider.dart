import 'package:flutter/foundation.dart';
import '../models/user.dart';
import '../services/orangtua_service.dart';
import '../services/api_client.dart';

class OrangtuaProvider extends ChangeNotifier {
  List<ChildSummary> _children = [];
  int?    _selectedChildId;
  bool    _isLoading = false;
  String? _error;

  List<ChildSummary> get children        => _children;
  int?                get selectedChildId => _selectedChildId;
  bool                get isLoading       => _isLoading;
  String?             get error           => _error;

  ChildSummary? get selectedChild => _children
      .cast<ChildSummary?>()
      .firstWhere((c) => c?.id == _selectedChildId, orElse: () => null);

  /// Isi daftar anak dari payload login/me, lalu pilih anak pertama secara default.
  void initFromUser(List<ChildSummary> children) {
    _children = children;
    _selectedChildId ??= children.isNotEmpty ? children.first.id : null;
    notifyListeners();
  }

  void selectChild(int studentId) {
    if (_selectedChildId == studentId) return;
    _selectedChildId = studentId;
    notifyListeners();
  }

  Future<void> markFeatureSeen(int studentId, String feature) async {
    final index = _children.indexWhere((c) => c.id == studentId);
    if (index != -1) {
      final child = _children[index];
      final currentBadges = child.badges;
      ChildBadges updatedBadges;

      if (feature == 'attendance') {
        updatedBadges = currentBadges.copyWith(attendance: const ChildBadgeInfo(hasUnread: false, count: 0));
      } else if (feature == 'conduct') {
        updatedBadges = currentBadges.copyWith(conduct: const ChildBadgeInfo(hasUnread: false, count: 0));
      } else if (feature == 'achievement') {
        updatedBadges = currentBadges.copyWith(achievement: const ChildBadgeInfo(hasUnread: false, count: 0));
      } else if (feature == 'chat') {
        updatedBadges = currentBadges.copyWith(chat: const ChildBadgeInfo(hasUnread: false, count: 0));
      } else {
        updatedBadges = currentBadges;
      }

      _children[index] = child.copyWith(badges: updatedBadges);
      notifyListeners();
    }

    // Panggil API mark-seen di background
    try {
      await OrangtuaService.markSeen(studentId: studentId, feature: feature);
    } catch (_) {}
  }

  Future<void> refreshChildren() async {
    _isLoading = true;
    _error     = null;
    notifyListeners();

    try {
      _children = await OrangtuaService.getChildren();
      if (_children.isNotEmpty &&
          !_children.any((c) => c.id == _selectedChildId)) {
        _selectedChildId = _children.first.id;
      }
    } catch (e) {
      _error = ApiClient.extractError(e);
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
