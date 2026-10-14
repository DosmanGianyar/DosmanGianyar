import 'package:flutter/foundation.dart';
import '../models/school_regulation.dart';
import '../services/regulation_service.dart';
import '../services/api_client.dart';

class RegulationProvider extends ChangeNotifier {
  List<RegulationGroup> _groups    = [];
  String?               _pdfUrl;
  bool                  _isLoading = false;
  String?               _error;

  List<RegulationGroup> get groups    => _groups;
  String?               get pdfUrl    => _pdfUrl;
  bool                  get isLoading => _isLoading;
  String?               get error     => _error;
  bool                  get isEmpty   => !_isLoading && _groups.isEmpty && _error == null;

  Future<void> fetch() async {
    if (_isLoading) return;
    _isLoading = true;
    _error     = null;
    notifyListeners();

    try {
      final res = await RegulationService.fetchAll();
      _groups   = res.groups;
      _pdfUrl   = res.pdfUrl;
    } catch (e) {
      _error = ApiClient.extractErrorMessage(e);
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
