-- =====================================================
-- NORMALISASI 1NF (First Normal Form)
-- Menghilangkan multi-valued attributes
-- =====================================================

-- Tabel setelah 1NF - memisahkan multi-valued attributes
CREATE TABLE rental_1nf (
    ID INT,
    Nama_Pelanggan VARCHAR(100),
    No_HP VARCHAR(15),
    Email VARCHAR(100),
    Alamat TEXT,
    Nama_Barang VARCHAR(100),
    Kategori_Barang VARCHAR(50),
    Harga_Sewa DECIMAL(10, 2),
    Kondisi_Barang ENUM('Baik', 'Rusak'),
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

-- Contoh data 1NF (memisahkan record yang memiliki multiple values)
INSERT INTO
    rental_1nf
VALUES
    -- Transaksi 1 - Rudi Hartono (dipecah menjadi 2 record)
    (
        1,
        'Rudi Hartono',
        '85217922655',
        'rudi.hartono@gmail.com',
        'Jl. Mayjend Sungkono No.12, Surabaya',
        'Tenda Dome 4P',
        'Tenda',
        150000,
        'Baik',
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
        1,
        'Rudi Hartono',
        '85217922655',
        'rudi.hartono@gmail.com',
        'Jl. Mayjend Sungkono No.12, Surabaya',
        'Matras Eiger',
        'Matras',
        20000,
        'Baik',
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

-- Transaksi 2 - Sari Lestari (1 barang)
(
    2,
    'Sari Lestari',
    '82234567890',
    'sari.lestari@gmail.com',
    'Jl. Soekarno Hatta No.88, Lowokwaru, Malang',
    'Carrier Consina 60L',
    'Carrier',
    300000,
    'Baik',
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

-- Transaksi 3 - Dini Oktaviani (dipecah menjadi 2 record)
(
    3,
    'Dini Oktaviani',
    '85331234567',
    'dini.oktaviani@gmail.com',
    'Perumahan Puri Indah Blok B2 No.5, Sidoarjo',
    'Headlamp Petzl',
    'Headlamp',
    200000,
    'Baik',
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
),
(
    3,
    'Dini Oktaviani',
    '85331234567',
    'dini.oktaviani@gmail.com',
    'Perumahan Puri Indah Blok B2 No.5, Sidoarjo',
    'Trekking Pole',
    'Trekking Pole',
    50000,
    'Rusak',
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

-- ✅ HASIL 1NF:
-- - Setiap cell berisi nilai tunggal (atomic)
-- - Tidak ada multi-valued attributes
-- - Masih ada redundansi data (akan diatasi di 2NF)