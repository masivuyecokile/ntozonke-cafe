@echo off
cd /d "%~dp0"

echo Ntozonke Cafe Client Configuration
echo ----------------------------------
echo.

set /p SERVER_URL=Server URL [example: http://192.168.1.10:8089]: 
if "%SERVER_URL%"=="" set SERVER_URL=http://localhost:8089

set /p CLIENT_KEY=Client API Key [default: ntozonke-local-client-2026]: 
if "%CLIENT_KEY%"=="" set CLIENT_KEY=ntozonke-local-client-2026

set /p ADMIN_PIN=Admin Exit PIN [default: 1234]: 
if "%ADMIN_PIN%"=="" set ADMIN_PIN=1234

set /p BUSINESS_NAME=Business Name [default: Ntozonke Internet Cafe]: 
if "%BUSINESS_NAME%"=="" set BUSINESS_NAME=Ntozonke Internet Cafe

set POLL_SECONDS=5
set LOGO_PATH=logo.png

powershell -NoProfile -ExecutionPolicy Bypass -Command "$config = [ordered]@{ server_url=$env:SERVER_URL; client_key=$env:CLIENT_KEY; poll_seconds=[int]$env:POLL_SECONDS; admin_pin=$env:ADMIN_PIN; logo_path=$env:LOGO_PATH; business_name=$env:BUSINESS_NAME }; $config | ConvertTo-Json | Set-Content -Path 'config.json' -Encoding UTF8"

echo.
echo Configuration saved to:
echo %cd%\config.json
echo.

set /p RESET_IDENTITY=Reset this PC identity and register again? (Y/N): 
if /I "%RESET_IDENTITY%"=="Y" (
    if exist client_identity.json del client_identity.json
    echo Existing client identity removed.
)

echo.
echo Done.
pause