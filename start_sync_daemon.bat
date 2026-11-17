@echo off
title Zona Fitri - Multi-Brand Realtime MongoDB Sync (Every 5 Mins)
echo ========================================================
echo   Zona Fitri - Realtime Sync Service to MongoDB Atlas
echo ========================================================
echo.
echo Brands: Kopi Kenangan, Tomoro Coffee, Fore, Janji Jiwa
echo Database: MongoDB Atlas (zona_fitri)
echo Interval: 300 seconds (5 minutes)
echo.
echo Tekan Ctrl+C untuk menghentikan sinkronisasi.
echo.

python scripts\sync_mongodb.py --daemon --interval 300

pause
