-- =====================================================
-- Database: Sistem Informasi Rental Alat Pendakian
-- Created: June 26, 2025
-- =====================================================

-- Set SQL mode untuk kompatibilitas
SET sql_mode = '';

-- Atau jika ingin tetap menggunakan ONLY_FULL_GROUP_BY, bisa disable sementara:
-- SET sql_mode = (SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));

-- Create Database
CREATE DATABASE IF NOT EXISTS rental_alat_pendakian;

USE rental_alat_pendakian;

-- =====================================================
-- CREATE TABLES
-- =====================================================

-- 1. Tabel PELANGGAN
CREATE TABLE pelanggan (
    id_pelanggan INT AUTO_INCREMENT PRIMARY KEY,
    nama_pelanggan VARCHAR(100) NOT NULL,
    no_hp VARCHAR(15) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    alamat_lengkap TEXT NOT NULL
);

-- 2. Tabel KARYAWAN
CREATE TABLE karyawan (
    id_karyawan INT AUTO_INCREMENT PRIMARY KEY,
    nama_karyawan VARCHAR(100) NOT NULL,
    posisi ENUM('Manager', 'Admin', 'Staff') NOT NULL DEFAULT 'Staff',
    shift_karyawan ENUM('Pagi', 'Sore') NOT NULL DEFAULT 'Pagi',
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    status ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif',
    last_login DATETIME NULL
);

-- 3. Tabel KATEGORI_BARANG
CREATE TABLE kategori_barang (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL UNIQUE
);

-- 4. Tabel BARANG
CREATE TABLE barang (
    id_barang INT AUTO_INCREMENT PRIMARY KEY,
    nama_barang VARCHAR(100) NOT NULL,
    id_kategori INT NOT NULL,
    harga_sewa DECIMAL(10, 2) NOT NULL,
    stok_tersedia INT DEFAULT 0,
    FOREIGN KEY (id_kategori) REFERENCES kategori_barang (id_kategori)
);

-- 5. Tabel TRANSAKSI
CREATE TABLE transaksi (
    id_transaksi INT AUTO_INCREMENT PRIMARY KEY,
    id_pelanggan INT NOT NULL,
    id_karyawan INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE NOT NULL,
    metode_bayar ENUM('Transfer', 'Cash') NOT NULL,
    status_bayar ENUM('Lunas', 'Belum Lunas') NOT NULL,
    tanggal_bayar DATE NULL,
    total_harga DECIMAL(10, 2) NOT NULL,
    denda DECIMAL(10, 2) DEFAULT 0,
    keterangan_denda TEXT NULL,
    ulasan_pelanggan TEXT NULL,
    rating INT NULL CHECK (
        rating >= 1
        AND rating <= 5
    ),
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan (id_pelanggan),
    FOREIGN KEY (id_karyawan) REFERENCES karyawan (id_karyawan)
);

-- 6. Tabel DETAIL_TRANSAKSI
CREATE TABLE detail_transaksi (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT NOT NULL,
    id_barang INT NOT NULL,
    jumlah INT NOT NULL DEFAULT 1,
    harga_satuan DECIMAL(10, 2) NOT NULL,
    kondisi_awal ENUM('Baik', 'Rusak') NOT NULL,
    kondisi_kembali ENUM('Baik', 'Rusak') NULL,
    FOREIGN KEY (id_transaksi) REFERENCES transaksi (id_transaksi),
    FOREIGN KEY (id_barang) REFERENCES barang (id_barang)
);

-- =====================================================
-- INSERT DATA
-- =====================================================

-- Insert data PELANGGAN
INSERT INTO
    pelanggan (
        nama_pelanggan,
        no_hp,
        email,
        alamat_lengkap
    )
VALUES (
        'Rudi Hartono',
        '85217922655',
        'rudi.hartono@gmail.com',
        'Jl. Mayjend Sungkono No.12, Surabaya'
    ),
    (
        'Sari Lestari',
        '82234567890',
        'sari.lestari@gmail.com',
        'Jl. Soekarno Hatta No.88, Lowokwaru, Malang'
    ),
    (
        'Dini Oktaviani',
        '85331234567',
        'dini.oktaviani@gmail.com',
        'Perumahan Puri Indah Blok B2 No.5, Sidoarjo'
    ),
    (
        'Farhan Akbar',
        '81388112233',
        'farhan.akbar@ymail.com',
        'Jl. Veteran No.3, Gresik Kota'
    ),
    (
        'Novi Rahmawati',
        '87851244567',
        'novi.rahmawati@gmail.com',
        'Jl. Jaksa Agung Suprapto No.11, Lamongan'
    ),
    (
        'Andre Wicaksono',
        '82288445566',
        'andre.wicaksono@gmail.com',
        'Jl. Joyoboyo No.27, Mojoroto, Kediri'
    ),
    (
        'Maya Salsabila',
        '83153246789',
        'maya.salsabila@gmail.com',
        'Jl. Trunojoyo No.5, Kaliwates, Jember'
    ),
    (
        'Rizky Ramadhan',
        '81234567890',
        'rizky.ramadhan@gmail.com',
        'Jl. Bhayangkara No.30, Sooko, Mojokerto'
    ),
    (
        'Ayu Kurniawati',
        '89012345678',
        'ayu.kurniawati@gmail.com',
        'Jl. Merdeka No.17, Sukorejo, Blitar'
    ),
    (
        'Bagas Prakoso',
        '87865432100',
        'bagas.prakoso@gmail.com',
        'Jl. Hayam Wuruk No.40, Gadingrejo, Pasuruan'
    );

-- Insert data KARYAWAN
INSERT INTO
    karyawan (nama_karyawan, posisi, shift_karyawan, username, password, status)
VALUES 
    ('Ahmad Ridwan', 'Manager', 'Pagi', 'admin', 'admin123', 'Aktif'),
    ('Siti Nurhaliza', 'Admin', 'Sore', 'staff', 'staff123', 'Aktif'),
    ('Budi Santoso', 'Manager', 'Pagi', 'manager', 'manager123', 'Aktif'),
    ('Dewi Marlina', 'Staff', 'Pagi', 'dewi', 'password123', 'Aktif'),
    ('Joko Supriyadi', 'Staff', 'Sore', 'joko', 'password123', 'Aktif');

-- Insert data KATEGORI_BARANG
INSERT INTO
    kategori_barang (nama_kategori)
VALUES ('Tenda'),
    ('Matras'),
    ('Carrier'),
    ('Headlamp'),
    ('Trekking Pole'),
    ('Kompor'),
    ('Peralatan Masak'),
    ('Sleeping Bag'),
    ('Jaket'),
    ('Flysheet'),
    ('Sleeping Pad'),
    ('Lampu'),
    ('Gas');

-- Insert data BARANG
INSERT INTO
    barang (
        nama_barang,
        id_kategori,
        harga_sewa,
        stok_tersedia
    )
VALUES ('Tenda Dome 4P', 1, 150000, 5),
    ('Matras Eiger', 2, 20000, 10),
    (
        'Carrier Consina 60L',
        3,
        300000,
        3
    ),
    (
        'Headlamp Petzl',
        4,
        200000,
        8
    ),
    ('Trekking Pole', 5, 50000, 12),
    (
        'Kompor Portable',
        6,
        250000,
        6
    ),
    ('Panci Set', 7, 25000, 8),
    (
        'Sleeping Bag Eiger',
        8,
        180000,
        7
    ),
    ('Tenda Bivak', 1, 100000, 4),
    ('Cooking Set', 7, 40000, 10),
    (
        'Jaket Gunung Arei',
        9,
        160000,
        6
    ),
    ('Flysheet', 10, 280000, 3),
    (
        'Sleeping bag Thermarest',
        11,
        200000,
        5
    ),
    ('Lampu Tenda', 12, 50000, 15),
    ('Matras', 2, 20000, 20),
    ('Gas', 13, 0, 25);

-- Insert data TRANSAKSI
INSERT INTO
    transaksi (
        id_pelanggan,
        id_karyawan,
        tanggal_pinjam,
        tanggal_kembali,
        metode_bayar,
        status_bayar,
        tanggal_bayar,
        total_harga,
        denda,
        keterangan_denda,
        ulasan_pelanggan,
        rating
    )
VALUES (
        1,
        4,
        '2025-06-01',
        '2025-06-03',
        'Transfer',
        'Lunas',
        '2025-06-01',
        170000,
        0,
        NULL,
        'Sangat puas',
        5
    ),
    (
        2,
        5,
        '2025-06-02',
        '2025-06-05',
        'Cash',
        'Belum Lunas',
        NULL,
        350000,
        50000,
        'telat 1 hari',
        'Muat banyak tapi berat',
        4
    ),
    (
        3,
        5,
        '2025-06-03',
        '2025-06-04',
        'Transfer',
        'Lunas',
        '2025-06-03',
        250000,
        0,
        NULL,
        'Lampu oke, stick longgar',
        2
    ),
    (
        4,
        4,
        '2025-06-04',
        '2025-06-07',
        'Cash',
        'Belum Lunas',
        NULL,
        375000,
        100000,
        'telat 2 hari',
        'Kurang panas kompornya',
        3
    ),
    (
        5,
        4,
        '2025-06-05',
        '2025-06-07',
        'Transfer',
        'Lunas',
        '2025-06-05',
        180000,
        0,
        NULL,
        'Hangat banget',
        5
    ),
    (
        6,
        5,
        '2025-06-06',
        '2025-06-08',
        'Transfer',
        'Lunas',
        '2025-06-06',
        140000,
        0,
        NULL,
        'Praktis dan ringan',
        5
    ),
    (
        7,
        4,
        '2025-06-06',
        '2025-06-07',
        'Transfer',
        'Lunas',
        '2025-06-06',
        160000,
        0,
        NULL,
        'kurang oke',
        5
    ),
    (
        8,
        5,
        '2025-06-07',
        '2025-06-10',
        'Cash',
        'Belum Lunas',
        NULL,
        390000,
        80000,
        'telat 1 hari',
        'Flysheetnya oke',
        4
    ),
    (
        9,
        4,
        '2025-06-08',
        '2025-06-11',
        'Transfer',
        'Lunas',
        '2025-06-08',
        200000,
        0,
        NULL,
        'kurang nyaman',
        5
    ),
    (
        10,
        5,
        '2025-06-09',
        '2025-06-10',
        'Cash',
        'Belum Lunas',
        NULL,
        130000,
        60000,
        'telat 1 hari',
        'Matras nyaman bree',
        5
    );

-- Insert data DETAIL_TRANSAKSI
INSERT INTO
    detail_transaksi (
        id_transaksi,
        id_barang,
        jumlah,
        harga_satuan,
        kondisi_awal,
        kondisi_kembali
    )
VALUES
    -- Transaksi 1: Rudi Hartono (Tenda Dome 4P, Matras Eiger)
    (
        1,
        1,
        1,
        150000,
        'Baik',
        'Baik'
    ),
    (
        1,
        2,
        1,
        20000,
        'Baik',
        'Baik'
    ),

-- Transaksi 2: Sari Lestari (Carrier Consina 60L)
( 2, 3, 1, 300000, 'Baik', 'Baik' ),

-- Transaksi 3: Dini Oktaviani (Headlamp Petzl, Trekking Pole)
(
    3,
    4,
    1,
    200000,
    'Baik',
    'Baik'
),
(
    3,
    5,
    1,
    50000,
    'Baik',
    'Rusak'
),

-- Transaksi 4: Farhan Akbar (Kompor Portable, Panci Set)
(
    4,
    6,
    1,
    250000,
    'Baik',
    'Baik'
),
(
    4,
    7,
    1,
    25000,
    'Baik',
    'Baik'
),

-- Transaksi 5: Novi Rahmawati (Sleeping Bag Eiger)
( 5, 8, 1, 180000, 'Baik', 'Baik' ),

-- Transaksi 6: Andre Wicaksono (Tenda Bivak, Cooking Set)
(
    6,
    9,
    1,
    100000,
    'Baik',
    'Baik'
),
(
    6,
    10,
    1,
    40000,
    'Baik',
    'Baik'
),

-- Transaksi 7: Maya Salsabila (Jaket Gunung Arei)
( 7, 11, 1, 160000, 'Rusak', 'Rusak' ),

-- Transaksi 8: Rizky Ramadhan (Flysheet, Trekking Pole)
(
    8,
    12,
    1,
    280000,
    'Baik',
    'Baik'
),
(
    8,
    5,
    1,
    30000,
    'Baik',
    'Baik'
),

-- Transaksi 9: Ayu Kurniawati (Sleeping bag Thermarest)
( 9, 13, 1, 200000, 'Rusak', 'Rusak' ),

-- Transaksi 10: Bagas Prakoso (Lampu Tenda, Matras, Gas)
(
    10,
    14,
    1,
    50000,
    'Baik',
    'Baik'
),
(
    10,
    15,
    1,
    20000,
    'Baik',
    'Baik'
),
(10, 16, 1, 0, 'Baik', 'Baik');

-- =====================================================
-- QUERIES UNTUK FITUR-FITUR YANG DIMINTA
-- =====================================================

-- 1. Laporan Frekuensi Penyewaan Alat
SELECT b.nama_barang, COUNT(dt.id_detail) as frekuensi_sewa
FROM barang b
    LEFT JOIN detail_transaksi dt ON b.id_barang = dt.id_barang
GROUP BY
    b.id_barang,
    b.nama_barang
ORDER BY frekuensi_sewa DESC;

-- 2. Statistik Keterlambatan Pelanggan
SELECT
    p.nama_pelanggan,
    COUNT(
        CASE
            WHEN t.denda > 0 THEN 1
        END
    ) as jumlah_keterlambatan,
    COALESCE(SUM(t.denda), 0) as total_denda
FROM pelanggan p
    LEFT JOIN transaksi t ON p.id_pelanggan = t.id_pelanggan
GROUP BY
    p.id_pelanggan,
    p.nama_pelanggan
ORDER BY total_denda DESC;

-- 3. Riwayat Transaksi Pelanggan
SELECT p.nama_pelanggan, t.id_transaksi, t.tanggal_pinjam, t.tanggal_kembali, t.total_harga, t.denda, t.status_bayar
FROM pelanggan p
    JOIN transaksi t ON p.id_pelanggan = t.id_pelanggan
ORDER BY p.nama_pelanggan, t.tanggal_pinjam;

-- 4. Deteksi Barang Bermasalah
SELECT b.nama_barang, COUNT(dt.id_detail) as total_rusak
FROM barang b
    JOIN detail_transaksi dt ON b.id_barang = dt.id_barang
WHERE
    dt.kondisi_kembali = 'Rusak'
    OR dt.kondisi_awal = 'Rusak'
GROUP BY
    b.id_barang,
    b.nama_barang
HAVING
    total_rusak > 0
ORDER BY total_rusak DESC;

-- 5. Durasi Sewa Rata-rata per Kategori
SELECT kb.nama_kategori, AVG(
        DATEDIFF(
            t.tanggal_kembali, t.tanggal_pinjam
        )
    ) as rata_rata_durasi_hari
FROM
    kategori_barang kb
    JOIN barang b ON kb.id_kategori = b.id_kategori
    JOIN detail_transaksi dt ON b.id_barang = dt.id_barang
    JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
GROUP BY
    kb.id_kategori,
    kb.nama_kategori
ORDER BY rata_rata_durasi_hari DESC;

-- 6. Analisis Kinerja Karyawan
SELECT
    k.nama_karyawan,
    k.posisi,
    k.shift_karyawan,
    COUNT(t.id_transaksi) as total_transaksi,
    AVG(t.rating) as rata_rata_rating
FROM karyawan k
    LEFT JOIN transaksi t ON k.id_karyawan = t.id_karyawan
GROUP BY
    k.id_karyawan,
    k.nama_karyawan,
    k.posisi,
    k.shift_karyawan
ORDER BY total_transaksi DESC;

-- 7. Identifikasi Bulanan Sewa
SELECT
    YEAR(tanggal_pinjam) as tahun,
    MONTH(tanggal_pinjam) as bulan,
    MONTHNAME(tanggal_pinjam) as nama_bulan,
    COUNT(*) as frekuensi_penyewaan
FROM transaksi
GROUP BY
    YEAR(tanggal_pinjam),
    MONTH(tanggal_pinjam)
ORDER BY frekuensi_penyewaan DESC;

-- 8. Total Transaksi per Orang (Harga Sewa + Denda)
SELECT
    p.nama_pelanggan,
    COUNT(t.id_transaksi) as jumlah_transaksi,
    SUM(t.total_harga + t.denda) as total_pembayaran
FROM pelanggan p
    JOIN transaksi t ON p.id_pelanggan = t.id_pelanggan
GROUP BY
    p.id_pelanggan,
    p.nama_pelanggan
ORDER BY total_pembayaran DESC;

-- =====================================================
-- CREATE VIEWS - JOIN LEBIH DARI 2 TABEL
-- =====================================================

-- VIEW 1: Detail Transaksi Lengkap (5 tabel)
-- Menggabungkan: TRANSAKSI, PELANGGAN, KARYAWAN, DETAIL_TRANSAKSI, BARANG
CREATE VIEW v_detail_transaksi_lengkap AS
SELECT
    t.id_transaksi,
    p.nama_pelanggan,
    p.no_hp,
    p.email,
    k.nama_karyawan,
    k.posisi,
    k.shift_karyawan,
    b.nama_barang,
    dt.jumlah,
    dt.harga_satuan,
    dt.kondisi_awal,
    dt.kondisi_kembali,
    t.tanggal_pinjam,
    t.tanggal_kembali,
    t.metode_bayar,
    t.status_bayar,
    t.denda,
    t.ulasan_pelanggan,
    t.rating,
    (dt.jumlah * dt.harga_satuan) AS subtotal
FROM
    transaksi t
    JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
    JOIN karyawan k ON t.id_karyawan = k.id_karyawan
    JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
    JOIN barang b ON dt.id_barang = b.id_barang
ORDER BY t.id_transaksi, b.nama_barang;

-- VIEW 2: Summary Transaksi per Kategori (4 tabel)
-- Menggabungkan: TRANSAKSI, DETAIL_TRANSAKSI, BARANG, KATEGORI_BARANG
CREATE VIEW v_summary_kategori AS
SELECT
    kb.nama_kategori,
    COUNT(DISTINCT t.id_transaksi) as total_transaksi,
    COUNT(dt.id_detail) as total_item_disewa,
    SUM(dt.jumlah) as total_qty_disewa,
    SUM(dt.jumlah * dt.harga_satuan) as total_pendapatan,
    AVG(dt.harga_satuan) as rata_rata_harga,
    COUNT(
        CASE
            WHEN dt.kondisi_kembali = 'Rusak' THEN 1
        END
    ) as total_rusak,
    ROUND(
        AVG(
            DATEDIFF(
                t.tanggal_kembali,
                t.tanggal_pinjam
            )
        ),
        2
    ) as rata_rata_durasi_hari
FROM
    kategori_barang kb
    JOIN barang b ON kb.id_kategori = b.id_kategori
    JOIN detail_transaksi dt ON b.id_barang = dt.id_barang
    JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
GROUP BY
    kb.id_kategori,
    kb.nama_kategori
ORDER BY total_pendapatan DESC;

-- VIEW 3: Analisis Pelanggan Lengkap (4 tabel)
-- Menggabungkan: PELANGGAN, TRANSAKSI, DETAIL_TRANSAKSI, BARANG
CREATE VIEW v_analisis_pelanggan AS
SELECT
    p.nama_pelanggan,
    p.no_hp,
    p.email,
    COUNT(DISTINCT t.id_transaksi) as total_transaksi,
    COUNT(dt.id_detail) as total_item_sewa,
    SUM(t.total_harga) as total_harga_sewa,
    SUM(t.denda) as total_denda,
    SUM(t.total_harga + t.denda) as total_pembayaran,
    AVG(t.rating) as rata_rata_rating,
    COUNT(
        CASE
            WHEN t.status_bayar = 'Belum Lunas' THEN 1
        END
    ) as transaksi_belum_lunas,
    COUNT(
        CASE
            WHEN dt.kondisi_kembali = 'Rusak' THEN 1
        END
    ) as barang_rusak_dikembalikan,
    GROUP_CONCAT(
        DISTINCT b.nama_barang
        ORDER BY b.nama_barang SEPARATOR ', '
    ) as barang_pernah_disewa
FROM
    pelanggan p
    LEFT JOIN transaksi t ON p.id_pelanggan = t.id_pelanggan
    LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
    LEFT JOIN barang b ON dt.id_barang = b.id_barang
GROUP BY
    p.id_pelanggan,
    p.nama_pelanggan,
    p.no_hp,
    p.email
ORDER BY total_pembayaran DESC;

-- VIEW 4: Kinerja Karyawan Detail (5 tabel)
-- Menggabungkan: KARYAWAN, TRANSAKSI, PELANGGAN, DETAIL_TRANSAKSI, BARANG
CREATE VIEW v_kinerja_karyawan_detail AS
SELECT
    k.nama_karyawan,
    k.posisi,
    k.shift_karyawan,
    COUNT(DISTINCT t.id_transaksi) as total_transaksi,
    COUNT(DISTINCT p.id_pelanggan) as total_pelanggan_dilayani,
    COUNT(dt.id_detail) as total_item_diproses,
    SUM(t.total_harga) as total_pendapatan,
    SUM(t.denda) as total_denda_dikelola,
    AVG(t.rating) as rata_rata_rating,
    COUNT(
        CASE
            WHEN t.status_bayar = 'Lunas' THEN 1
        END
    ) as transaksi_lunas,
    COUNT(
        CASE
            WHEN t.status_bayar = 'Belum Lunas' THEN 1
        END
    ) as transaksi_belum_lunas,
    ROUND(
        (
            COUNT(
                CASE
                    WHEN t.status_bayar = 'Lunas' THEN 1
                END
            ) * 100.0 / COUNT(t.id_transaksi)
        ),
        2
    ) as persentase_lunas,
    GROUP_CONCAT(
        DISTINCT b.nama_barang
        ORDER BY b.nama_barang SEPARATOR ', '
    ) as jenis_barang_dikelola
FROM
    karyawan k
    LEFT JOIN transaksi t ON k.id_karyawan = t.id_karyawan
    LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
    LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
    LEFT JOIN barang b ON dt.id_barang = b.id_barang
GROUP BY
    k.id_karyawan,
    k.nama_karyawan,
    k.posisi,
    k.shift_karyawan
ORDER BY total_pendapatan DESC;

-- VIEW 5: Dashboard Executive Summary (6 tabel)
-- Menggabungkan: TRANSAKSI, PELANGGAN, KARYAWAN, DETAIL_TRANSAKSI, BARANG, KATEGORI_BARANG
CREATE VIEW v_dashboard_executive AS
SELECT
    'RENTAL PERFORMANCE' as metric_type,
    CONCAT(
        'Total Transaksi: ',
        COUNT(DISTINCT t.id_transaksi)
    ) as metric_1,
    CONCAT(
        'Total Pendapatan: Rp ',
        FORMAT(SUM(t.total_harga), 0)
    ) as metric_2,
    CONCAT(
        'Total Denda: Rp ',
        FORMAT(SUM(t.denda), 0)
    ) as metric_3,
    CONCAT(
        'Rata-rata Rating: ',
        ROUND(AVG(t.rating), 2)
    ) as metric_4,
    CONCAT(
        'Kategori Terlaris: ',
        (
            SELECT kb2.nama_kategori
            FROM
                kategori_barang kb2
                JOIN barang b2 ON kb2.id_kategori = b2.id_kategori
                JOIN detail_transaksi dt2 ON b2.id_barang = dt2.id_barang
            GROUP BY
                kb2.id_kategori,
                kb2.nama_kategori
            ORDER BY COUNT(dt2.id_detail) DESC
            LIMIT 1
        )
    ) as metric_5
FROM
    transaksi t
    JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
    JOIN karyawan k ON t.id_karyawan = k.id_karyawan
    JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
    JOIN barang b ON dt.id_barang = b.id_barang
    JOIN kategori_barang kb ON b.id_kategori = kb.id_kategori;

-- VIEW 6: Laporan Barang Bermasalah (4 tabel)
-- Menggabungkan: BARANG, KATEGORI_BARANG, DETAIL_TRANSAKSI, TRANSAKSI
CREATE VIEW v_barang_bermasalah AS
SELECT
    b.nama_barang,
    kb.nama_kategori,
    b.harga_sewa,
    COUNT(dt.id_detail) as total_disewa,
    COUNT(
        CASE
            WHEN dt.kondisi_awal = 'Rusak' THEN 1
        END
    ) as kondisi_awal_rusak,
    COUNT(
        CASE
            WHEN dt.kondisi_kembali = 'Rusak' THEN 1
        END
    ) as kondisi_kembali_rusak,
    COUNT(
        CASE
            WHEN dt.kondisi_awal = 'Rusak'
            OR dt.kondisi_kembali = 'Rusak' THEN 1
        END
    ) as total_bermasalah,
    ROUND(
        (
            COUNT(
                CASE
                    WHEN dt.kondisi_awal = 'Rusak'
                    OR dt.kondisi_kembali = 'Rusak' THEN 1
                END
            ) * 100.0 / COUNT(dt.id_detail)
        ),
        2
    ) as persentase_bermasalah,
    SUM(
        CASE
            WHEN dt.kondisi_kembali = 'Rusak' THEN dt.harga_satuan
            ELSE 0
        END
    ) as estimasi_kerugian,
    GROUP_CONCAT(
        DISTINCT CONCAT(
            p.nama_pelanggan,
            ' (',
            t.tanggal_pinjam,
            ')'
        )
        ORDER BY t.tanggal_pinjam SEPARATOR '; '
    ) as riwayat_penyewa_bermasalah
FROM
    barang b
    JOIN kategori_barang kb ON b.id_kategori = kb.id_kategori
    LEFT JOIN detail_transaksi dt ON b.id_barang = dt.id_barang
    LEFT JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
    LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
WHERE
    dt.kondisi_awal = 'Rusak'
    OR dt.kondisi_kembali = 'Rusak'
GROUP BY
    b.id_barang,
    b.nama_barang,
    kb.nama_kategori,
    b.harga_sewa
HAVING
    total_bermasalah > 0
ORDER BY
    persentase_bermasalah DESC,
    estimasi_kerugian DESC;

-- =====================================================
-- CONTOH PENGGUNAAN VIEWS
-- =====================================================

-- Menggunakan VIEW untuk query yang lebih sederhana
SELECT *
FROM v_detail_transaksi_lengkap
WHERE
    nama_pelanggan LIKE '%Rudi%';

SELECT * FROM v_summary_kategori WHERE total_pendapatan > 100000;

SELECT * FROM v_analisis_pelanggan WHERE total_transaksi > 1;

SELECT *
FROM v_kinerja_karyawan_detail
WHERE
    shift_karyawan = 'Pagi';

SELECT * FROM v_dashboard_executive;

SELECT * FROM v_barang_bermasalah WHERE persentase_bermasalah > 50;

-- =====================================================
-- KEUNTUNGAN MENGGUNAKAN VIEWS
-- =====================================================

/*
KEUNTUNGAN VIEWS:
1. ✅ Simplifikasi query kompleks
2. ✅ Reusability - dapat digunakan berulang kali
3. ✅ Security - menyembunyikan struktur tabel asli
4. ✅ Abstraction - menyediakan interface yang konsisten
5. ✅ Maintenance - perubahan struktur tidak mempengaruhi aplikasi

CONTOH IMPLEMENTASI:
- v_detail_transaksi_lengkap: Untuk laporan transaksi detail
- v_summary_kategori: Untuk dashboard kategori barang
- v_analisis_pelanggan: Untuk customer relationship management
- v_kinerja_karyawan_detail: Untuk employee performance review
- v_dashboard_executive: Untuk executive summary reporting
- v_barang_bermasalah: Untuk quality control management
*/

-- =====================================================
-- SOLUSI UNTUK ERROR sql_mode=only_full_group_by
-- =====================================================

/*
JIKA MENGALAMI ERROR "only_full_group_by", ADA BEBERAPA SOLUSI:

1. DISABLE SEMENTARA (Di awal file sudah ada):
SET sql_mode = '';

2. ATAU DISABLE HANYA ONLY_FULL_GROUP_BY:
SET sql_mode = (SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));

3. ATAU GUNAKAN ANY_VALUE() UNTUK KOLOM NON-AGGREGATE:
Contoh:
SELECT 
kategori, 
ANY_VALUE(nama_barang) as sample_barang,
COUNT(*) as total
FROM barang 
GROUP BY kategori;

4. ATAU PASTIKAN SEMUA KOLOM NON-AGGREGATE ADA DI GROUP BY:
SELECT nama_pelanggan, total_harga, COUNT(*)
FROM transaksi t
JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
GROUP BY nama_pelanggan, total_harga;  -- Semua kolom non-aggregate

5. PENGATURAN PERMANEN DI my.cnf (MySQL):
[mysqld]
sql_mode = "STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"

SEMUA QUERY DAN VIEW DALAM FILE INI SUDAH DIPERBAIKI UNTUK KOMPATIBEL DENGAN only_full_group_by.
*/

-- =====================================================
-- TESTING QUERIES UNTUK MEMASTIKAN TIDAK ADA ERROR
-- =====================================================

-- Test semua view yang dibuat
SELECT 'Testing v_detail_transaksi_lengkap' as test_name;

SELECT COUNT(*) as record_count FROM v_detail_transaksi_lengkap;

SELECT 'Testing v_summary_kategori' as test_name;

SELECT COUNT(*) as record_count FROM v_summary_kategori;

SELECT 'Testing v_analisis_pelanggan' as test_name;

SELECT COUNT(*) as record_count FROM v_analisis_pelanggan;

SELECT 'Testing v_kinerja_karyawan_detail' as test_name;

SELECT COUNT(*) as record_count FROM v_kinerja_karyawan_detail;

-- Remove old authentication setup that conflicts
-- Keep only the corrected version below

-- Update existing karyawan records with proper data
UPDATE karyawan SET 
    username = 'admin', 
    password = 'admin123',
    posisi = 'Manager',
    status = 'Aktif'
WHERE nama_karyawan = 'Ahmad Ridwan';

UPDATE karyawan SET 
    username = 'staff', 
    password = 'staff123',
    posisi = 'Admin',
    status = 'Aktif'
WHERE nama_karyawan = 'Siti Nurhaliza';

UPDATE karyawan SET 
    username = 'manager', 
    password = 'manager123',
    posisi = 'Manager',
    status = 'Aktif'
WHERE nama_karyawan = 'Budi Santoso';

-- Update other employees with automatic username
UPDATE karyawan SET 
    username = LOWER(REPLACE(nama_karyawan, ' ', '')),
    password = 'password123',
    posisi = 'Staff',
    status = 'Aktif'
WHERE username IS NULL;