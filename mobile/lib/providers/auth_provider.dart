import 'package:flutter/foundation.dart';
import '../models/user.dart';
import '../services/auth_service.dart';
import '../services/api_client.dart';

enum AuthState { unknown, authenticated, unauthenticated }

class AuthProvider extends ChangeNotifier {
  AuthState _state = AuthState.unknown;
  User?     _user;
  String?   _error;
  bool      _isLoading = false;

  AuthState get state     => _state;
  User?     get user      => _user;
  String?   get error     => _error;
  bool      get isLoading => _isLoading;
  bool      get isLoggedIn => _state == AuthState.authenticated;

  // ─── Init ─────────────────────────────────────────────────────────────────

  /// Dipanggil saat app pertama kali dibuka.
  Future<void> checkAuth() async {
    final loggedIn = await AuthService.isLoggedIn();
    if (!loggedIn) {
      _state = AuthState.unauthenticated;
      notifyListeners();
      return;
    }

    try {
      _user  = await AuthService.fetchMe();
      _state = AuthState.authenticated;
    } catch (_) {
      // Token mungkin expired — paksa login ulang
      await ApiClient.clearAuth();
      _state = AuthState.unauthenticated;
    }
    notifyListeners();
  }

  // ─── Login ────────────────────────────────────────────────────────────────

  Future<bool> login(String loginInput, String password) async {
    _isLoading = true;
    _error     = null;
    notifyListeners();

    try {
      _user  = await AuthService.login(loginInput, password);
      _state = AuthState.authenticated;
      return true;
    } catch (e) {
      _error = ApiClient.extractError(e);
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // ─── Logout ───────────────────────────────────────────────────────────────

  Future<void> logout() async {
    await AuthService.logout();
    _user  = null;
    _state = AuthState.unauthenticated;
    _error = null;
    notifyListeners();
  }
}
