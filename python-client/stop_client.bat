@echo off
cd /d "%~dp0"

echo Stopping Ntozonke Cafe Client safely...
echo.

echo stopped > disable_watchdog.flag

if exist client.pid (
    set /p CLIENT_PID=<client.pid
    echo Stopping client PID %CLIENT_PID%...
    taskkill /F /PID %CLIENT_PID% >nul 2>&1
)

timeout /t 2 /nobreak >nul

if exist watchdog.pid (
    set /p WATCHDOG_PID=<watchdog.pid
    echo Stopping watchdog PID %WATCHDOG_PID%...
    taskkill /F /PID %WATCHDOG_PID% >nul 2>&1
)

if exist client.pid del client.pid
if exist watchdog.pid del watchdog.pid

echo.
echo Client and watchdog stopped.
echo To start again, double-click:
echo C:\NtozonkeClient\start_watchdog_hidden.vbs
echo.

pause