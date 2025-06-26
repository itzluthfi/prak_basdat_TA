-- =====================================================
-- NORMALISASI BCNF (Boyce-Codd Normal Form)
-- Mengatasi dependency anomalies
-- =====================================================

-- Analisis BCNF:
-- Struktur 3NF sudah memenuhi BCNF karena:
-- 1. Setiap determinant adalah candidate key atau super key
-- 2. Tidak ada dependency yang melanggar aturan BCNF

-- Namun, untuk memperkuat struktur, kita tambahkan beberapa improvement:

-- Tabel yang sama dengan 3NF dengan perbaikan constraint
CREATE TABLE pelanggan_bcnf (
    ID_Pelanggan INT AUTO_INCREMENT PRIMARY KEY,
    Nama_Pelanggan VARCHAR(100) NOT NULL UNIQUE,
    No_HP VARCHAR(15) NOT NULL UNIQUE,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Alamat TEXT NOT NULL
);

CREATE TABLE karyawan_bcnf (
    ID_Karyawan INT AUTO_INCREMENT PRIMARY KEY,
    Nama_Karyawan VARCHAR(100) NOT NULL UNIQUE,
    Shift_Karyawan ENUM('Pagi', 'Sore') NOT NULL
);

CREATE TABLE kategori_bcnf (
    ID_Kategori INT AUTO_INCREMENT PRIMARY KEY,
    Nama_Kategori VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE barang_bcnf (
    ID_Barang INT AUTO_INCREMENT PRIMARY KEY,
    Nama_Barang VARCHAR(100) NOT NULL,
    ID_Kategori INT NOT NULL,
    Harga_Sewa DECIMAL(10, 2) NOT NULL CHECK (Harga_Sewa >= 0),
    FOREIGN KEY (ID_Kategori) REFERENCES kategori_bcnf (ID_Kategori),
    UNIQUE (Nama_Barang, ID_Kategori)
);

CREATE TABLE transaksi_bcnf (
    ID_Transaksi INT AUTO_INCREMENT PRIMARY KEY,
    ID_Pelanggan INT NOT NULL,
    ID_Karyawan INT NOT NULL,
    Tanggal_Pinjam DATE NOT NULL,
    Tanggal_Kembali DATE NOT NULL,
    Metode_Bayar ENUM('Transfer', 'Cash') NOT NULL,
    Status_Bayar ENUM('Lunas', 'Belum Lunas') NOT NULL,
    Tanggal_Bayar DATE,
    Total_Harga DECIMAL(10, 2) NOT NULL CHECK (Total_Harga >= 0),
    Denda DECIMAL(10, 2) DEFAULT 0 CHECK (Denda >= 0),
    Keterangan_Denda TEXT,
    Ulasan_Pelanggan TEXT,
    Rating INT CHECK (
        Rating >= 1
        AND Rating <= 5
    ),
    FOREIGN KEY (ID_Pelanggan) REFERENCES pelanggan_bcnf (ID_Pelanggan),
    FOREIGN KEY (ID_Karyawan) REFERENCES karyawan_bcnf (ID_Karyawan),
    CHECK (
        Tanggal_Kembali >= Tanggal_Pinjam
    )
);

CREATE TABLE detail_transaksi_bcnf (
    ID_Detail INT AUTO_INCREMENT PRIMARY KEY,
    ID_Transaksi INT NOT NULL,
    ID_Barang INT NOT NULL,
    Jumlah INT NOT NULL DEFAULT 1 CHECK (Jumlah > 0),
    Harga_Satuan DECIMAL(10, 2) NOT NULL CHECK (Harga_Satuan >= 0),
    Kondisi_Awal ENUM('Baik', 'Rusak') NOT NULL,
    Kondisi_Kembali ENUM('Baik', 'Rusak'),
    FOREIGN KEY (ID_Transaksi) REFERENCES transaksi_bcnf (ID_Transaksi),
    FOREIGN KEY (ID_Barang) REFERENCES barang_bcnf (ID_Barang),
    UNIQUE (ID_Transaksi, ID_Barang)
);

-- ✅ HASIL BCNF:
-- - Memenuhi 1NF, 2NF, dan 3NF
-- - Setiap determinant adalah super key
-- - Tidak ada dependency anomalies
-- - Constraint tambahan untuk data integrity