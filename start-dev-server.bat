@echo off
setlocal EnableExtensions

set "HOST=127.0.0.1"
set "REQUESTED_PORT=%RZX_DEV_PORT%"
if "%REQUESTED_PORT%"=="" set "REQUESTED_PORT=8000"
if not "%~1"=="" set "REQUESTED_PORT=%~1"

set "PORT="
for /f "usebackq delims=" %%P in (`php -r "$host='127.0.0.1'; $port=(int)getenv('REQUESTED_PORT'); if ($port < 1) { $port=8000; } for ($i=0; $i<20; $i++) { $candidate=$port+$i; $sock=@stream_socket_server('tcp://' . $host . ':' . $candidate, $errno, $errstr); if ($sock !== false) { fclose($sock); echo $candidate; exit(0); } } fwrite(STDERR, 'No free port found'); exit(1);"`) do set "PORT=%%P"

if not defined PORT (
    echo Failed to find an available port.
    exit /b 1
)

echo Starting PHP development server on %HOST%:%PORT%
php -S %HOST%:%PORT% -t public public/dev-server.php

endlocal
