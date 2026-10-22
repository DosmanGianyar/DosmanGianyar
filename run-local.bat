@echo off
title SIMAK DOSMAN - Local Server
echo ==========================================================
echo  SIMAK DOSMAN - Starting Local Development Server
echo ==========================================================
cd /d "%~dp0"

powershell -ExecutionPolicy Bypass -File "%~dp0run-local.ps1"
pause
