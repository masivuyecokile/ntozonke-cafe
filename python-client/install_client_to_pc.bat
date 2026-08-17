@echo off
setlocal

set INSTALL_DIR=C:\NtozonkeClient
set STARTUP_DIR=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup

echo Installing Ntozonke Cafe Client...
echo.

if not exist "%INSTALL_DIR%" mkdir "%INSTALL_DIR%"
if not exist "%INSTALL_DIR%\logs" mkdir "%INSTALL_DIR%\logs"

copy /Y "%~dp0client_app.py" "%INSTALL_DIR%\client_app.py"
copy /Y "%~dp0start_client_hidden.vbs" "%INSTALL_DIR%\start_client_hidden.vbs"
copy /Y "%~dp0run_client_debug.bat" "%INSTALL_DIR%\run_client_debug.bat"

if exist "%~dp0logo.png" (
    copy /Y "%~dp0logo.png" "%INSTALL_DIR%\logo.png"
)

if not exist "%INSTALL_DIR%\config.json" (
    copy /Y "%~dp0config.json" "%INSTALL_DIR%\config.json"
) else (
    echo Existing config.json found. Keeping it.
)

powershell -NoProfile -ExecutionPolicy Bypass -Command "$WshShell = New-Object -ComObject WScript.Shell; $Shortcut = $WshShell.CreateShortcut([Environment]::GetFolderPath('Startup') + '\Ntozonke Cafe Client.lnk'); $Shortcut.TargetPath = 'wscript.exe'; $Shortcut.Arguments = '""C:\NtozonkeClient\start_client_hidden.vbs""'; $Shortcut.WorkingDirectory = 'C:\NtozonkeClient'; $Shortcut.Save()"

echo.
echo Installation complete.
echo Installed to: %INSTALL_DIR%
echo Startup shortcut created.
echo.
echo Run this to test now:
echo C:\NtozonkeClient\run_client_debug.bat
echo.

pause