@echo off
cd /d "%~dp0"

echo Ntozonke Cafe Client Debug Mode
echo --------------------------------
echo Folder: %cd%
echo.
echo Press Ctrl + C to stop from this window.
echo Admin exit inside client: Ctrl + Shift + Q
echo.

python client_app.py

pause