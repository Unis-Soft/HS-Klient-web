@echo off
setlocal
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0sign_release.ps1" -ExePath "%~dp0HSBridge.exe"
set ERR=%ERRORLEVEL%
echo.
if not "%ERR%"=="0" (
  echo PODPIS SELHAL. Chybovy kod: %ERR%
  pause
  exit /b %ERR%
)
echo Podpis dokoncen.
pause
