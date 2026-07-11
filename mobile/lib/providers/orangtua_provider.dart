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
