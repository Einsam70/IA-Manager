@echo off
setlocal
cd /d "%~dp0"

where php >nul 2>nul
if errorlevel 1 (
    echo No se encontro PHP en el PATH.
    echo Instala PHP 8.0 o superior y vuelve a intentarlo.
    pause
    exit /b 1
)

set "PYTHON_COMMAND="
where py >nul 2>nul
if not errorlevel 1 set "PYTHON_COMMAND=py -3"

if not defined PYTHON_COMMAND (
    where python >nul 2>nul
    if not errorlevel 1 set "PYTHON_COMMAND=python"
)

if not defined PYTHON_COMMAND (
    echo No se encontro Python 3 en el PATH.
    pause
    exit /b 1
)

php -m | findstr /I /C:"pdo_sqlite" >nul
if errorlevel 1 (
    echo Falta la extension pdo_sqlite de PHP.
    pause
    exit /b 1
)

php -m | findstr /I /C:"mbstring" >nul
if errorlevel 1 (
    echo Falta la extension mbstring de PHP.
    pause
    exit /b 1
)

php -m | findstr /I /C:"zip" >nul
if errorlevel 1 (
    echo Falta la extension zip de PHP.
    pause
    exit /b 1
)

php -r "exit(function_exists('shell_exec') ? 0 : 1);"
if errorlevel 1 (
    echo PHP tiene shell_exec desactivado.
    pause
    exit /b 1
)

%PYTHON_COMMAND% -c "from PIL import Image" >nul 2>nul
if errorlevel 1 (
    echo Falta la dependencia Pillow de Python.
    echo Ejecuta: %PYTHON_COMMAND% -m pip install -r requirements.txt
    pause
    exit /b 1
)

php app\services\init_db.php
if errorlevel 1 (
    echo No se pudo inicializar la base de datos.
    pause
    exit /b 1
)

echo.
echo IA Manager disponible en http://127.0.0.1:8000/
start "" "http://127.0.0.1:8000/"
php -S 127.0.0.1:8000 -t app\public app\router.php
