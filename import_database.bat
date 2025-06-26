@echo off
echo ================================================
echo   IMPORT DATABASE RENTAL ALAT PENDAKIAN
echo ================================================
echo.

echo 1. Pastikan Laragon MySQL sudah running...
echo 2. Akan mengimport ke database: rental_alat_pendakian
echo.

echo Mencoba koneksi tanpa password...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS rental_alat_pendakian;" 2>nul

if %errorlevel% equ 0 (
    echo Berhasil konek tanpa password, mengimport database...
    mysql -u root rental_alat_pendakian < "C:\laragon\www\prak_basdat_TA\rental_alat_pendakian.sql"
    if %errorlevel% equ 0 (
        echo.
        echo ================================================
        echo   DATABASE BERHASIL DIIMPORT!
        echo ================================================
        echo.
        echo Sekarang Anda bisa akses: http://localhost/prak_basdat_TA
        echo.
        echo Login credentials:
        echo - Username: admin, Password: admin123 (Manager)
        echo - Username: staff, Password: staff123 (Admin)  
        echo - Username: manager, Password: manager123 (Manager)
        echo.
    ) else (
        echo ERROR: Gagal mengimport database!
    )
) else (
    echo Perlu password untuk MySQL...
    echo Masukkan password MySQL (atau tekan Enter jika kosong):
    mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS rental_alat_pendakian;"
    mysql -u root -p rental_alat_pendakian < "C:\laragon\www\prak_basdat_TA\rental_alat_pendakian.sql"
    echo Database import completed!
)

echo.
pause

