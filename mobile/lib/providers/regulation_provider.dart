import 'package:flutter/foundation.dart';
import '../models/school_regulation.dart';
import '../services/regulation_service.dart';
import '../services/api_client.dart';

class RegulationProvider extends ChangeNotifier {
  List<RegulationGroup> _groups    = [];
  bool                  _isLoading = false;
  String?               _error;

  List<RegulationGroup> get groups    => _groups;
  bool                  get isLoading => _isLoading;
  String?               get error     => _error;
  bool                  get isEmpty   => !_isLoading && _groups.isEmpty && _error == null;

  Future<void> fetch() async {
    if (_isLoading) return;
    _isLoading = true;
    _error     = null;
    notifyListeners();

    try {
      _groups = await RegulationService.fetchAll();
    } catch (e) {
      _error = ApiClient.extractError(e);
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
