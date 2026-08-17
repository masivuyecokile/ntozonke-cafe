@echo off
cd /d "%~dp0"

echo Stopping Ntozonke Cafe Client...
echo.

echo stopped > disable_watchdog.flag

taskkill /F /IM python.exe >nul 2>&1
taskkill /F /IM pythonw.exe >nul 2>&1
taskkill /F /IM wscript.exe >nul 2>&1

echo Client and watchdog stopped.
echo To start again, run:
echo start_watchdog_hidden.vbs
echo.

pause