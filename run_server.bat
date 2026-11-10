@ECHO OFF
TITLE Huling Tracker - Haus Keliling
ECHO ========================================================
ECHO       HULING TRACKER (HAUS KELILING) - LARAVEL 12
ECHO ========================================================
ECHO.
ECHO Memulai Development Server di http://127.0.0.1:8000
ECHO.
ECHO Akun Demo:
ECHO - Driver POS: nanda@haus.com / HausNanda2026!
ECHO - Admin Web:  admin@haus.com / password
ECHO.
ECHO Tekan CTRL+C untuk menghentikan server.
ECHO ========================================================
ECHO.

C:\xampp\php\php.exe artisan serve --host=0.0.0.0 --port=8000
PAUSE

