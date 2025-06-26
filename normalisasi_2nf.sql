-- =====================================================
-- NORMALISASI 2NF (Second Normal Form)
-- Menghilangkan partial dependency
-- =====================================================

-- Identifikasi Composite Primary Key: (ID, Nama_Barang)
-- Partial Dependencies yang ditemukan:
-- ID → Nama_Pelanggan, No_HP, Email, Alamat, Tanggal_Pinjam, Tanggal_Kembali, Nama_Karyawan, Shift_Karyawan, Metode_Bayar, Status_Bayar, Tanggal_Bayar, Denda, Keterangan_Denda, Ulasan_Pelanggan, Rating
-- Nama_Barang → Kategori_Barang, Harga_Sewa

-- Tabel 1: TRANSAKSI_2NF (data transaksi utama)
CREATE TABLE transaksi_2nf (
    ID INT PRIMARY KEY,
    Nama_Pelanggan VARCHAR(100),
    No_HP VARCHAR(15),
    Email VARCHAR(100),
    Alamat TEXT,
    Tanggal_Pinjam DATE,
    Tanggal_Kembali DATE,
    Nama_Karyawan VARCHAR(100),
    Shift_Karyawan ENUM('Pagi', 'Sore'),
    Metode_Bayar ENUM('Transfer', 'Cash'),
    Status_Bayar ENUM('Lunas', 'Belum Lunas'),
    Tanggal_Bayar DATE,
    Denda DECIMAL(10, 2),
    Keterangan_Denda TEXT,
    Ulasan_Pelanggan TEXT,
    Rating INT
);

-- Tabel 2: BARANG_2NF (master data barang)
CREATE TABLE barang_2nf (
    Nama_Barang VARCHAR(100) PRIMARY KEY,
    Kategori_Barang VARCHAR(50),
    Harga_Sewa DECIMAL(10, 2)
);

-- Tabel 3: DETAIL_TRANSAKSI_2NF (junction table)
CREATE TABLE detail_transaksi_2nf (
    ID INT,
    Nama_Barang VARCHAR(100),
    Kondisi_Barang ENUM('Baik', 'Rusak'),
    PRIMARY KEY (ID, Nama_Barang),
    FOREIGN KEY (ID) REFERENCES transaksi_2nf (ID),
    FOREIGN KEY (Nama_Barang) REFERENCES barang_2nf (Nama_Barang)
);

-- Insert data TRANSAKSI_2NF
INSERT INTO
    transaksi_2nf
VALUES (
        1,
        'Rudi Hartono',
        '85217922655',
        'rudi.hartono@gmail.com',
        'Jl. Mayjend Sungkono No.12, Surabaya',
        '2025-06-01',
        '2025-06-03',
        'Dewi Marlina',
        'Pagi',
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
        'Sari Lestari',
        '82234567890',
        'sari.lestari@gmail.com',
        'Jl. Soekarno Hatta No.88, Lowokwaru, Malang',
        '2025-06-02',
        '2025-06-05',
        'Joko Supriyadi',
        'Sore',
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
        'Dini Oktaviani',
        '85331234567',
        'dini.oktaviani@gmail.com',
        'Perumahan Puri Indah Blok B2 No.5, Sidoarjo',
        '2025-06-03',
        '2025-06-04',
        'Joko Supriyadi',
        'Sore',
        'Transfer',
        'Lunas',
        '2025-06-03',
        0,
        NULL,
        'Lampu oke, stick longgar',
        2
    );

-- Insert data BARANG_2NF
INSERT INTO
    barang_2nf
VALUES (
        'Tenda Dome 4P',
        'Tenda',
        150000
    ),
    (
        'Matras Eiger',
        'Matras',
        20000
    ),
    (
        'Carrier Consina 60L',
        'Carrier',
        300000
    ),
    (
        'Headlamp Petzl',
        'Headlamp',
        200000
    ),
    (
        'Trekking Pole',
        'Trekking Pole',
        50000
    );

-- Insert data DETAIL_TRANSAKSI_2NF
INSERT INTO
    detail_transaksi_2nf
VALUES (1, 'Tenda Dome 4P', 'Baik'),
    (1, 'Matras Eiger', 'Baik'),
    (
        2,
        'Carrier Consina 60L',
        'Baik'
    ),
    (3, 'Headlamp Petzl', 'Baik'),
    (3, 'Trekking Pole', 'Rusak');

-- ✅ HASIL 2NF:
-- - Memenuhi 1NF
-- - Tidak ada partial dependency
-- - Masih ada transitive dependency (akan diatasi di 3NF)