-- =====================================================
-- NORMALISASI 3NF (Third Normal Form)
-- Menghilangkan transitive dependency
-- =====================================================

-- Transitive Dependencies yang ditemukan:
-- ID → Nama_Pelanggan → No_HP, Email, Alamat
-- ID → Nama_Karyawan → Shift_Karyawan
-- Nama_Barang → Kategori_Barang

-- Tabel 1: PELANGGAN_3NF
CREATE TABLE pelanggan_3nf (
    ID_Pelanggan INT AUTO_INCREMENT PRIMARY KEY,
    Nama_Pelanggan VARCHAR(100) UNIQUE,
    No_HP VARCHAR(15),
    Email VARCHAR(100),
    Alamat TEXT
);

-- Tabel 2: KARYAWAN_3NF
CREATE TABLE karyawan_3nf (
    ID_Karyawan INT AUTO_INCREMENT PRIMARY KEY,
    Nama_Karyawan VARCHAR(100) UNIQUE,
    Shift_Karyawan ENUM('Pagi', 'Sore')
);

-- Tabel 3: KATEGORI_3NF
CREATE TABLE kategori_3nf (
    ID_Kategori INT AUTO_INCREMENT PRIMARY KEY,
    Nama_Kategori VARCHAR(50) UNIQUE
);

-- Tabel 4: BARANG_3NF
CREATE TABLE barang_3nf (
    ID_Barang INT AUTO_INCREMENT PRIMARY KEY,
    Nama_Barang VARCHAR(100),
    ID_Kategori INT,
    Harga_Sewa DECIMAL(10, 2),
    FOREIGN KEY (ID_Kategori) REFERENCES kategori_3nf (ID_Kategori)
);

-- Tabel 5: TRANSAKSI_3NF
CREATE TABLE transaksi_3nf (
    ID_Transaksi INT PRIMARY KEY,
    ID_Pelanggan INT,
    ID_Karyawan INT,
    Tanggal_Pinjam DATE,
    Tanggal_Kembali DATE,
    Metode_Bayar ENUM('Transfer', 'Cash'),
    Status_Bayar ENUM('Lunas', 'Belum Lunas'),
    Tanggal_Bayar DATE,
    Denda DECIMAL(10, 2),
    Keterangan_Denda TEXT,
    Ulasan_Pelanggan TEXT,
    Rating INT,
    FOREIGN KEY (ID_Pelanggan) REFERENCES pelanggan_3nf (ID_Pelanggan),
    FOREIGN KEY (ID_Karyawan) REFERENCES karyawan_3nf (ID_Karyawan)
);

-- Tabel 6: DETAIL_TRANSAKSI_3NF
CREATE TABLE detail_transaksi_3nf (
    ID_Detail INT AUTO_INCREMENT PRIMARY KEY,
    ID_Transaksi INT,
    ID_Barang INT,
    Kondisi_Barang ENUM('Baik', 'Rusak'),
    FOREIGN KEY (ID_Transaksi) REFERENCES transaksi_3nf (ID_Transaksi),
    FOREIGN KEY (ID_Barang) REFERENCES barang_3nf (ID_Barang)
);

-- Insert data sample untuk 3NF
INSERT INTO
    pelanggan_3nf (
        Nama_Pelanggan,
        No_HP,
        Email,
        Alamat
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
    );

INSERT INTO
    karyawan_3nf (Nama_Karyawan, Shift_Karyawan)
VALUES ('Dewi Marlina', 'Pagi'),
    ('Joko Supriyadi', 'Sore');

INSERT INTO
    kategori_3nf (Nama_Kategori)
VALUES ('Tenda'),
    ('Matras'),
    ('Carrier'),
    ('Headlamp'),
    ('Trekking Pole');

INSERT INTO
    barang_3nf (
        Nama_Barang,
        ID_Kategori,
        Harga_Sewa
    )
VALUES ('Tenda Dome 4P', 1, 150000),
    ('Matras Eiger', 2, 20000),
    (
        'Carrier Consina 60L',
        3,
        300000
    ),
    ('Headlamp Petzl', 4, 200000),
    ('Trekking Pole', 5, 50000);

INSERT INTO
    transaksi_3nf
VALUES (
        1,
        1,
        1,
        '2025-06-01',
        '2025-06-03',
        'Transfer',
        'Lunas',
        '2025-06-01',
        0,
        NULL,
        'Sangat puas',
        5
    ),
    (
        2,
        2,
        2,
        '2025-06-02',
        '2025-06-05',
        'Cash',
        'Belum Lunas',
        NULL,
        50000,
        'telat 1 hari',
        'Muat banyak tapi berat',
        4
    ),
    (
        3,
        3,
        2,
        '2025-06-03',
        '2025-06-04',
        'Transfer',
        'Lunas',
        '2025-06-03',
        0,
        NULL,
        'Lampu oke, stick longgar',
        2
    );

INSERT INTO
    detail_transaksi_3nf (
        ID_Transaksi,
        ID_Barang,
        Kondisi_Barang
    )
VALUES (1, 1, 'Baik'),
    (1, 2, 'Baik'),
    (2, 3, 'Baik'),
    (3, 4, 'Baik'),
    (3, 5, 'Rusak');

-- ✅ HASIL 3NF:
-- - Memenuhi 1NF dan 2NF
-- - Tidak ada transitive dependency
-- - Data sudah terstruktur dengan baik
-- - Redundansi minimal