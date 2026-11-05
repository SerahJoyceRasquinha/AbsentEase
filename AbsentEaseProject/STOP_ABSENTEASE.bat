@echo off
REM =====================================================================
REM  DOUBLE-CLICK ME to stop AbsentEase (MySQL, Apache, XAMPP Control Panel).
REM  The real work is done by launcher\stop_absentease.ps1
REM =====================================================================
title AbsentEase - stopping...
cd /d "%~dp0"

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0launcher\stop_absentease.ps1"

echo.
echo This window closes in 5 seconds...
timeout /t 5 >nul
