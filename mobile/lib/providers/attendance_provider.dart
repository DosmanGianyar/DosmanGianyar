import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:geolocator/geolocator.dart';
import '../models/attendance.dart';
import '../services/attendance_service.dart';
import '../services/api_client.dart';

class AttendanceProvider extends ChangeNotifier {
  AttendanceStatus?      _status;
  AttendanceHistory?     _history;
  List<AttendanceRecord> _currentMonthRecords = [];

  bool    _isLoadingStatus  = false;
  bool    _isLoadingHistory = false;
  bool    _isSubmitting     = false;
  String? _error;
  String? _successMessage;

  AttendanceStatus?      get status               => _status;
  AttendanceHistory?     get history              => _history;
  List<AttendanceRecord> get currentMonthRecords  => _currentMonthRecords;
  bool                   get isLoadingStatus      => _isLoadingStatus;
  bool                   get isLoadingHistory     => _isLoadingHistory;
  bool                   get isSubmitting         => _isSubmitting;
  String?                get error                => _error;
  String?                get successMessage       => _successMessage;

  void clearMessages() {
    _error          = null;
    _successMessage = null;
    notifyListeners();
  }

  // ─── Status ───────────────────────────────────────────────────────────────

  Future<void> fetchStatus() async {
    _isLoadingStatus = true;
    _error           = null;
    notifyListeners();

    try {
      _status = await AttendanceService.getTodayStatus();
    } catch (e) {
      _error = ApiClient.extractError(e);
    } finally {
      _isLoadingStatus = false;
      notifyListeners();
    }
  }

  // ─── Current-month dots (mini-calendar dashboard) ─────────────────────────

  Future<void> fetchCurrentMonthDots() async {
    try {
      final now  = DateTime.now();
      final hist = await AttendanceService.getHistory(month: now.month, year: now.year);
      _currentMonthRecords = hist.records;
      notifyListeners();
    } catch (_) {
      // Non-critical: mini-calendar kosong jika gagal
    }
  }

  // ─── Check In ─────────────────────────────────────────────────────────────

  Future<bool> checkIn(File photo, Position position) async {
    _isSubmitting   = true;
    _error          = null;
    _successMessage = null;
    notifyListeners();

    try {
      _successMessage = await AttendanceService.checkIn(photo, position);
      await fetchStatus();
      await fetchCurrentMonthDots();
      return true;
    } catch (e) {
      _error = ApiClient.extractError(e);
      return false;
    } finally {
      _isSubmitting = false;
      notifyListeners();
    }
  }

  // ─── Check Out ────────────────────────────────────────────────────────────

  Future<bool> checkOut(File photo, Position position) async {
    _isSubmitting   = true;
    _error          = null;
    _successMessage = null;
    notifyListeners();

    try {
      _successMessage = await AttendanceService.checkOut(photo, position);
      await fetchStatus();
      await fetchCurrentMonthDots();
      return true;
    } catch (e) {
      _error = ApiClient.extractError(e);
      return false;
    } finally {
      _isSubmitting = false;
      notifyListeners();
    }
  }

  // ─── History ──────────────────────────────────────────────────────────────

  Future<void> fetchHistory({required int month, required int year}) async {
    _isLoadingHistory = true;
    _error            = null;
    notifyListeners();

    try {
      _history = await AttendanceService.getHistory(month: month, year: year);
    } catch (e) {
      _error = ApiClient.extractError(e);
    } finally {
      _isLoadingHistory = false;
      notifyListeners();
    }
  }
}
