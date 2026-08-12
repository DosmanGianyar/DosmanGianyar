@echo off
title SIMS SMAN 1 Gianyar - Local Server
echo ==========================================================
echo  SIMS SMAN 1 Gianyar - Starting Local Development Server
echo ==========================================================
cd /d "%~dp0"

powershell -ExecutionPolicy Bypass -File "%~dp0run-local.ps1"
pause
