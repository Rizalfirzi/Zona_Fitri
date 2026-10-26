@echo off
title RID Store - Realtime Coffee App
echo ========================================================
echo   RID STORE - REALTIME COFFEE OUTLET ^& STOCK APP
echo ========================================================
echo   Membuka server lokal PHP di http://localhost:8000 ...
echo   Tekan CTRL+C untuk menghentikan server.
echo ========================================================
php -S localhost:8000 router.php
pause
