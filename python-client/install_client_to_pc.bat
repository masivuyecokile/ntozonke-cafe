@echo off
setlocal

set INSTALL_DIR=C:\NtozonkeClient
set STARTUP_DIR=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup

echo Installing Ntozonke Cafe Client...
echo.

if not exist "%INSTALL_DIR%" mkdir "%INSTALL_DIR%"
if not exist "%INSTALL_DIR%\logs" mkdir "%INSTALL_DIR%\logs"

copy /Y "%~dp0client_app.py" "%INSTALL_DIR%\client_app.py"
copy /Y "%~dp0client_watchdog.py" "%INSTALL_DIR%\client_watchdog.py"
copy /Y "%~dp0start_client_hidden.vbs" "%INSTALL_DIR%\start_client_hidden.vbs"
copy /Y "%~dp0start_watchdog_hidden.vbs" "%INSTALL_DIR%\start_watchdog_hidden.vbs"
copy /Y "%~dp0run_client_debug.bat" "%INSTALL_DIR%\run_client_debug.bat"
copy /Y "%~dp0stop_client.bat" "%INSTALL_DIR%\stop_client.bat"
copy /Y "%~dp0configure_client.bat" "%INSTALL_DIR%\configure_client.bat"
copy /Y "%~dp0config.example.json" "%INSTALL_DIR%\config.example.json"

if exist "%~dp0logo.png" (
    copy /Y "%~dp0logo.png" "%INSTALL_DIR%\logo.png"
)

if not exist "%INSTALL_DIR%\config.json" (
    copy /Y "%~dp0config.json" "%INSTALL_DIR%\config.json"
) else (
    echo Existing config.json found. Keeping it.
)

echo.
set /p RUN_CONFIG=Do you want to configure this client now? (Y/N): 
if /I "%RUN_CONFIG%"=="Y" (
    call "%INSTALL_DIR%\configure_client.bat"
)

if exist "%INSTALL_DIR%\disable_watchdog.flag" (
    del "%INSTALL_DIR%\disable_watchdog.flag"
)

powershell -NoProfile -ExecutionPolicy Bypass -Command "$WshShell = New-Object -ComObject WScript.Shell; $Shortcut = $WshShell.CreateShortcut([Environment]::GetFolderPath('Startup') + '\Ntozonke Cafe Client.lnk'); $Shortcut.TargetPath = 'wscript.exe'; $Shortcut.Arguments = '""C:\NtozonkeClient\start_watchdog_hidden.vbs""'; $Shortcut.WorkingDirectory = 'C:\NtozonkeClient'; $Shortcut.Save()"

echo.
echo Installation complete.
echo Installed to: %INSTALL_DIR%
echo Startup watchdog shortcut created.
echo.
echo Test now:
echo C:\NtozonkeClient\start_watchdog_hidden.vbs
echo.

pause