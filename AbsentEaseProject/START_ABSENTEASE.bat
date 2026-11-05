@echo off
REM =====================================================================
REM  DOUBLE-CLICK ME to run AbsentEase.
REM  Starts Apache + MySQL, opens the XAMPP Control Panel, prepares the
REM  database and opens the site in Chrome. The real work is done by
REM  launcher\start_absentease.ps1 (PowerShell, built into Windows).
REM =====================================================================
title AbsentEase - starting...
cd /d "%~dp0"

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0launcher\start_absentease.ps1"

if errorlevel 1 (
    echo.
    echo Something went wrong - read the message above.
    pause
    exit /b 1
)

echo.
echo This window closes in 8 seconds...
timeout /t 8 >nul
