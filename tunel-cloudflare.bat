@echo off
setlocal EnableDelayedExpansion
chcp 65001 >nul
title Tunel Cloudflare - escolar.test
color 0A

set "SYS=%SystemRoot%\System32"
set "CF=C:\Program Files (x86)\cloudflared\cloudflared.exe"
set "HTTPD=C:\laragon\bin\apache\httpd-2.4.66-260223-Win64-VS18\bin\httpd.exe"
set "HTTPD_D=C:/laragon/bin/apache/httpd-2.4.66-260223-Win64-VS18"
set "LOG=%TEMP%\cloudflared-escolar.log"
set "TMP=%TEMP%\cf-code.tmp"

echo ==================================================
echo   TUNEL CLOUDFLARE  -  escolar.test
echo   Presiona una tecla al final para detener el tunel
echo ==================================================
echo.

rem -- Limpia un tunel anterior que haya quedado colgado
"%SYS%\taskkill.exe" /F /IM cloudflared.exe >nul 2>&1
if exist "!LOG!" del "!LOG!" >nul 2>&1
if exist "!TMP!" del "!TMP!" >nul 2>&1

rem -- 1) Apache
"%SYS%\tasklist.exe" /FI "IMAGENAME eq httpd.exe" 2>nul | "%SYS%\find.exe" /I "httpd.exe" >nul
if !errorlevel!==0 (
    echo [OK] Apache: ya esta corriendo
) else (
    echo [..] Apache: no esta corriendo, levantandolo...
    start "" "!HTTPD!" -d "!HTTPD_D!"
)

rem -- 2) ESPERAR a que el servidor local responda (hasta ~90s)
echo [..] Esperando a que Apache responda en http://127.0.0.1 ...
for /L %%i in (1,1,60) do (
    "%SYS%\curl.exe" -s -o nul http://127.0.0.1/ >nul 2>&1
    if !errorlevel!==0 goto :originok
    "%SYS%\ping.exe" -n 2 127.0.0.1 >nul
)
echo [!!] Apache NO responde en http://127.0.0.1 despues de 90s.
echo     Sin servidor local, el tunel da error 1033. Revisa Laragon.
goto :wait

:originok
echo [OK] Apache: respondiendo en http://127.0.0.1

rem -- 3) Sitio escolar.test (vhost con ServerAlias *.trycloudflare.com)
"%SYS%\curl.exe" -s -o nul -w "%%{http_code}" http://escolar.test/ 2>nul | "%SYS%\findstr.exe" "200 302" >nul
if !errorlevel!==0 (
    echo [OK] Sitio: escolar.test responde
) else (
    echo [!!] Sitio: escolar.test NO responde. Revisa Laragon y que el vhost
    echo     tenga "ServerAlias *.trycloudflare.com" en auto.escolar.test.conf
    goto :wait
)

rem -- 4) Tunel
start "Tunel Cloudflare (escolar)" /min "%SystemRoot%\System32\cmd.exe" /c ""!CF!" tunnel --url http://127.0.0.1 --no-autoupdate > "!LOG!" 2>&1"

echo.
echo [..] Esperando URL publica...
set "URL="
for /L %%i in (1,1,60) do (
    for /f "tokens=2 delims=|" %%L in ('""%SYS%\findstr.exe" /C:"trycloudflare.com" "!LOG!" 2^>nul"') do set "URL=%%L"
    if defined URL goto :found
    "%SYS%\ping.exe" -n 2 127.0.0.1 >nul
)

echo [!!] No se pudo obtener la URL a tiempo.
echo     Revisa el log: !LOG!
goto :wait

:found
set "URL=!URL: =!"

rem -- 5) ESPERAR a que la URL publica responda (Cloudflare tarda unos seg
rem        en conectar con tu servidor; antes de eso da 1033/530)
echo [..] URL obtenida. Esperando a que Cloudflare conecte con tu servidor...
set "CODE=000"
for /L %%i in (1,1,30) do (
    "%SYS%\curl.exe" -s -o nul -w "%%{http_code}" "!URL!" 2>nul > "!TMP!"
    set /p CODE=<"!TMP!"
    if "!CODE!"=="200" goto :ready
    if "!CODE!"=="302" goto :ready
    "%SYS%\ping.exe" -n 2 127.0.0.1 >nul
)
echo [!!] La URL no respondio 200/302 en 60s. Si ves error 1033,
echo     reintenta la URL en unos segundos (Apache puede tardar mas).
goto :show

:ready
echo [OK] URL lista y respondiendo (HTTP !CODE!).

:show
echo.
echo ==================================================
echo   TU URL PUBLICA:
echo.
echo   !URL!
echo.
echo   Abrila desde CUALQUIER PC conectada a Internet.
echo   Mientras esta ventana este abierta, el tunel corre.
echo ==================================================
start "" "!URL!"

:wait
echo.
echo Presiona una tecla para DETENER el tunel...
pause >nul
"%SYS%\taskkill.exe" /F /IM cloudflared.exe >nul 2>&1
echo [OK] Tunel detenido. Si cerraste la ventana con la X,
echo     el tunel se limpia solo en la proxima ejecucion.
"%SYS%\ping.exe" -n 4 127.0.0.1 >nul
exit /b 0