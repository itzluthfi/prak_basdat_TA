-- =====================================================
-- UPDATE SCRIPT untuk Database yang Sudah Ada
-- Jalankan ini jika database sudah dibuat sebelumnya
-- =====================================================

USE rental_alat_pendakian;

-- Tambah kolom posisi jika belum ada
ALTER TABLE karyawan
ADD COLUMN posisi ENUM('Manager', 'Admin', 'Staff') NOT NULL DEFAULT 'Staff' AFTER nama_karyawan;

-- Update data karyawan yang sudah ada dengan posisi yang sesuai
UPDATE karyawan
SET
    posisi = 'Manager'
WHERE
    nama_karyawan = 'Dewi Marlina';

UPDATE karyawan
SET
    posisi = 'Staff'
WHERE
    nama_karyawan = 'Joko Supriyadi';

-- Tambah username dan password jika belum ada
ALTER TABLE karyawan
ADD COLUMN username VARCHAR(50) UNIQUE AFTER posisi,
ADD COLUMN password VARCHAR(255) AFTER username,
ADD COLUMN status ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif' AFTER password,
ADD COLUMN last_login DATETIME NULL AFTER status;

-- Insert sample users untuk testing
UPDATE karyawan
SET
    username = 'admin',
    password = 'admin123',
    posisi = 'Manager',
    status = 'Aktif'
WHERE
    nama_karyawan = 'Dewi Marlina';

UPDATE karyawan
SET
    username = 'staff',
    password = 'staff123',
    posisi = 'Staff',
    status = 'Aktif'
WHERE
    nama_karyawan = 'Joko Supriyadi';

-- Tambah karyawan Manager dan Admin untuk demo
INSERT INTO
    karyawan (
        nama_karyawan,
        posisi,
        shift_karyawan,
        username,
        password,
        status
    )
VALUES (
        'Ahmad Ridwan',
        'Manager',
        'Pagi',
        'manager',
        'manager123',
        'Aktif'
    ),
    (
        'Siti Nurhaliza',
        'Admin',
        'Sore',
        'admin2',
        'admin123',
        'Aktif'
    )
ON DUPLICATE KEY UPDATE
    username = VALUES(username),
    password = VALUES(password),
    posisi = VALUES(posisi),
    status = VALUES(status);

-- Update struktur tabel untuk kompatibilitas
-- Pastikan kolom-kolom yang dibutuhkan ada
DESCRIBE karyawan;

SELECT 'Database update completed successfully!' as status;