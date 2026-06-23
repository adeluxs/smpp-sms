@echo off
REM Local development setup without Docker

echo Installing PHP dependencies...
cd laravel
composer install
if errorlevel 1 (
    echo Install composer from https://getcomposer.org
    exit /b 1
)

echo Installing Python dependencies...
cd ..\smpp-engine
python -m venv venv
call venv\Scripts\activate
pip install -e .

echo Setup complete.
echo.
echo To start services:
echo   Terminal 1: cd laravel && php artisan serve
echo   Terminal 2: cd laravel && php artisan queue:work
echo   Terminal 3: cd smpp-engine && python -m smpp_engine
echo   Terminal 4: cd smpp-mock && python -m smpp_mock --port 2776