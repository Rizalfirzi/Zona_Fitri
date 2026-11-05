@echo off
title Zona Fitri - Realtime Coffee App
echo ========================================================
echo   ZONA FITRI - REALTIME COFFEE OUTLET ^& STOCK APP
echo ========================================================
echo   Membuka server lokal PHP di http://localhost:8000 ...
echo   Tekan CTRL+C untuk menghentikan server.
echo ========================================================
php -S localhost:8000 router.php
pause
