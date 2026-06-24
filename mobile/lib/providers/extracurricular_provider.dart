import 'package:flutter/foundation.dart';
import '../models/extracurricular.dart';
import '../services/api_client.dart';
import '../services/extracurricular_service.dart';

class ExtracurricularProvider extends ChangeNotifier {
  // ─── Browse/All state ────────────────────────────────────────────────────
  List<ExtracurricularItem> _allExtras  = [];
  bool                      _loadingAll = false;
  String?                   _allError;

  List<ExtracurricularItem> get allExtras  => _allExtras;
  bool                      get loadingAll => _loadingAll;
  String?                   get allError   => _allError;

  // ─── My extras state ─────────────────────────────────────────────────────
  List<MyExtracurricularItem> _myExtras   = [];
  bool                        _loadingMy  = false;
  String?                     _myError;

  List<MyExtracurricularItem> get myExtras   => _myExtras;
  bool                        get loadingMy  => _loadingMy;
  String?                     get myError    => _myError;

  // ─── Sessions state ───────────────────────────────────────────────────────
  List<ExtraSession> _upcomingSessions = [];
  List<ExtraSession> _pastSessions     = [];
  bool               _loadingSessions  = false;
  String?            _sessionsError;

  List<ExtraSession> get upcomingSessions => _upcomingSessions;
  List<ExtraSession> get pastSessions     => _pastSessions;
  bool               get loadingSessions  => _loadingSessions;
  String?            get sessionsError    => _sessionsError;

  // ─── Session detail state ─────────────────────────────────────────────────
  ExtraSession?             _sessionDetail;
  String?                   _sessionMyRole;
  List<ExtraSessionMember>  _sessionMembers = [];
  bool                      _loadingDetail  = false;
  String?                   _detailError;

  ExtraSession?            get sessionDetail  => _sessionDetail;
  String?                  get sessionMyRole  => _sessionMyRole;
  List<ExtraSessionMember> get sessionMembers => _sessionMembers;
  bool                     get loadingDetail  => _loadingDetail;
  String?                  get detailError    => _detailError;

  // ─── Action loading ───────────────────────────────────────────────────────
  bool    _actionLoading = false;
  String? _actionError;
  String? _actionSuccess;

  bool    get actionLoading => _actionLoading;
  String? get actionError   => _actionError;
  String? get actionSuccess => _actionSuccess;

  void clearActionState() {
    _actionError   = null;
    _actionSuccess = null;
    notifyListeners();
  }

  // ─── Fetch methods ────────────────────────────────────────────────────────

  Future<void> fetchAll() async {
    _loadingAll = true;
    _allError   = null;
    notifyListeners();
    try {
      _allExtras = await ExtracurricularService.fetchAll();
    } catch (e) {
      _allError = _extractError(e);
    } finally {
      _loadingAll = false;
      notifyListeners();
    }
  }

  Future<void> fetchMy() async {
    _loadingMy = true;
    _myError   = null;
    notifyListeners();
    try {
      _myExtras = await ExtracurricularService.fetchMy();
    } catch (e) {
      _myError = _extractError(e);
    } finally {
      _loadingMy = false;
      notifyListeners();
    }
  }

  Future<void> fetchSessions({String filter = 'upcoming'}) async {
    _loadingSessions = true;
    _sessionsError   = null;
    notifyListeners();
    try {
      final sessions = await ExtracurricularService.fetchSessions(filter: filter);
      if (filter == 'past') {
        _pastSessions = sessions;
      } else {
        _upcomingSessions = sessions;
      }
    } catch (e) {
      _sessionsError = _extractError(e);
    } finally {
      _loadingSessions = false;
      notifyListeners();
    }
  }

  Future<void> fetchSessionDetail(int sessionId) async {
    _loadingDetail = true;
    _detailError   = null;
    notifyListeners();
    try {
      final result       = await ExtracurricularService.fetchSessionDetail(sessionId);
      _sessionDetail  = result.session;
      _sessionMyRole  = result.myRole;
      _sessionMembers = result.members;
    } catch (e) {
      _detailError = _extractError(e);
    } finally {
      _loadingDetail = false;
      notifyListeners();
    }
  }

  // ─── Actions ──────────────────────────────────────────────────────────────

  Future<bool> joinExtra(int extraId) async {
    _actionLoading = true;
    _actionError   = null;
    _actionSuccess = null;
    notifyListeners();
    try {
      _actionSuccess = await ExtracurricularService.join(extraId);
      await fetchAll();
      await fetchMy();
      return true;
    } catch (e) {
      _actionError = _extractError(e);
      return false;
    } finally {
      _actionLoading = false;
      notifyListeners();
    }
  }

  Future<bool> leaveExtra(int extraId) async {
    _actionLoading = true;
    _actionError   = null;
    _actionSuccess = null;
    notifyListeners();
    try {
      _actionSuccess = await ExtracurricularService.leave(extraId);
      await fetchAll();
      await fetchMy();
      return true;
    } catch (e) {
      _actionError = _extractError(e);
      return false;
    } finally {
      _actionLoading = false;
      notifyListeners();
    }
  }

  Future<bool> toggleSessionOpen(int sessionId) async {
    _actionLoading = true;
    notifyListeners();
    try {
      final isOpen = await ExtracurricularService.toggleOpen(sessionId);
      if (_sessionDetail != null) {
        _sessionDetail = ExtraSession(
          id:                  _sessionDetail!.id,
          extracurricularId:   _sessionDetail!.extracurricularId,
          extracurricularName: _sessionDetail!.extracurricularName,
          title:               _sessionDetail!.title,
          sessionDate:         _sessionDetail!.sessionDate,
          startTime:           _sessionDetail!.startTime,
          endTime:             _sessionDetail!.endTime,
          location:            _sessionDetail!.location,
          notes:               _sessionDetail!.notes,
          isOpen:              isOpen,
          hadirCount:          _sessionDetail!.hadirCount,
          alpaCount:           _sessionDetail!.alpaCount,
        );
      }
      return true;
    } catch (e) {
      _actionError = _extractError(e);
      return false;
    } finally {
      _actionLoading = false;
      notifyListeners();
    }
  }

  Future<bool> saveAttendance(int sessionId) async {
    _actionLoading = true;
    _actionError   = null;
    _actionSuccess = null;
    notifyListeners();
    try {
      final payload = _sessionMembers
          .where((m) => m.attendance != null)
          .map((m) => {'user_id': m.userId, 'status': m.attendance!})
          .toList();
      if (payload.isEmpty) {
        _actionError = 'Belum ada kehadiran yang dicatat.';
        return false;
      }
      _actionSuccess = await ExtracurricularService.markAttendance(sessionId, payload);
      // Refresh detail to get updated counts
      await fetchSessionDetail(sessionId);
      return true;
    } catch (e) {
      _actionError = _extractError(e);
      return false;
    } finally {
      _actionLoading = false;
      notifyListeners();
    }
  }

  Future<bool> createSession(Map<String, dynamic> data) async {
    _actionLoading = true;
    _actionError   = null;
    _actionSuccess = null;
    notifyListeners();
    try {
      await ExtracurricularService.createSession(data);
      _actionSuccess = 'Sesi berhasil dibuat.';
      await fetchSessions();
      return true;
    } catch (e) {
      _actionError = _extractError(e);
      return false;
    } finally {
      _actionLoading = false;
      notifyListeners();
    }
  }

  /// Update attendance status locally (optimistic) — mirrored ke server via saveAttendance.
  void toggleMemberAttendance(int userId) {
    final idx = _sessionMembers.indexWhere((m) => m.userId == userId);
    if (idx == -1) return;
    final current = _sessionMembers[idx].attendance;
    _sessionMembers[idx].attendance = current == 'hadir' ? 'alpa' : 'hadir';
    notifyListeners();
  }

  // ─── Helpers ──────────────────────────────────────────────────────────────

  String _extractError(Object e) => ApiClient.extractError(e);
}
