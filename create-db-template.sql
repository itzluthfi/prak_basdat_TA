-- =====================================================
-- Database: Sistem Informasi Rental Alat Pendakian
-- File: CREATE TABLE dan INSERT DATA ONLY
-- Created: June 26, 2025
-- =====================================================

-- Set SQL mode untuk kompatibilitas
SET sql_mode = '';

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
        'admin',
        'admin123',
        'Aktif'
    ),
    (
        'Siti Nurhaliza',
        'Admin',
        'Sore',
        'staff',
        'staff123',
        'Aktif'
    ),
    (
        'Budi Santoso',
        'Manager',
        'Pagi',
        'manager',
        'manager123',
        'Aktif'
    ),
    (
        'Dewi Marlina',
        'Staff',
        'Pagi',
        'dewi',
        'password123',
        'Aktif'
    ),
    (
        'Joko Supriyadi',
        'Staff',
        'Sore',
        'joko',
        'password123',
        'Aktif'
    );

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
-- SELESAI - Database siap digunakan
-- =====================================================