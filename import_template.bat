@echo off
echo ================================================
echo   IMPORT DATABASE SIMPLE TEMPLATE
echo   RENTAL ALAT PENDAKIAN (CREATE + INSERT ONLY)
echo ================================================
echo.

echo 1. Pastikan Laragon MySQL sudah running...
echo 2. Akan mengimport ke database: rental_alat_pendakian
echo 3. File ini hanya berisi CREATE TABLE dan INSERT DATA
echo.

echo Mencoba koneksi tanpa password...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS rental_alat_pendakian;" 2>nul

if %errorlevel% equ 0 (
    echo Berhasil konek tanpa password, mengimport database...
    mysql -u root rental_alat_pendakian < "C:\laragon\www\prak_basdat_TA\create-db-template.sql"
    if %errorlevel% equ 0 (
        echo.
        echo ================================================
        echo   DATABASE TEMPLATE BERHASIL DIIMPORT!
        echo ================================================
        echo.
        echo Database berisi:
        echo - 6 Tabel: pelanggan, karyawan, kategori_barang, barang, transaksi, detail_transaksi
        echo - Data sample untuk semua tabel
        echo - Tanpa query, view, atau stored procedure
        echo.
        echo Sekarang Anda bisa akses: http://localhost/prak_basdat_TA
        echo.
        echo Login credentials:
        echo - Username: admin, Password: admin123 (Manager)
        echo - Username: staff, Password: staff123 (Admin)  
        echo - Username: manager, Password: manager123 (Manager)
        echo - Username: dewi, Password: password123 (Staff)
        echo - Username: joko, Password: password123 (Staff)
        echo.
    ) else (
        echo ERROR: Gagal mengimport database template!
    )
) else (
    echo Perlu password untuk MySQL...
    echo Masukkan password MySQL (atau tekan Enter jika kosong):
    set /p password="Password: "
    if "%password%"=="" (
        mysql -u root -e "CREATE DATABASE IF NOT EXISTS rental_alat_pendakian;"
        mysql -u root rental_alat_pendakian < "C:\laragon\www\prak_basdat_TA\create-db-template.sql"
    ) else (
        mysql -u root -p%password% -e "CREATE DATABASE IF NOT EXISTS rental_alat_pendakian;"
        mysql -u root -p%password% rental_alat_pendakian < "C:\laragon\www\prak_basdat_TA\create-db-template.sql"
    )
    echo Database template import completed!
)

echo.
echo ================================================
echo Catatan:
echo - File ini mengimport create-db-template.sql
echo - Jika ingin file lengkap (dengan query/view), jalankan import_database.bat
echo - Untuk import manual: import create-db-template.sql via phpMyAdmin
echo ================================================
echo.
pause
