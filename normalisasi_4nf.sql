-- =====================================================
-- NORMALISASI 4NF (Fourth Normal Form)
-- Menghilangkan multi-valued dependencies
-- =====================================================

-- Analisis Multi-Valued Dependencies:
-- Dalam sistem rental alat pendakian, kita perlu mempertimbangkan:
-- 1. Satu barang dapat memiliki multiple kondisi dalam waktu berbeda
-- 2. Satu karyawan dapat menangani multiple shift (jika ada perubahan kebijakan)
-- 3. Satu pelanggan dapat memiliki multiple alamat

-- Untuk mencapai 4NF, kita perlu memisahkan multi-valued dependencies

-- Tabel dasar tetap sama dengan BCNF
CREATE TABLE pelanggan_4nf (
    ID_Pelanggan INT AUTO_INCREMENT PRIMARY KEY,
    Nama_Pelanggan VARCHAR(100) NOT NULL UNIQUE,
    No_HP VARCHAR(15) NOT NULL UNIQUE,
    Email VARCHAR(100) NOT NULL UNIQUE
);

-- Tabel terpisah untuk alamat pelanggan (jika seorang pelanggan punya multiple alamat)
CREATE TABLE alamat_pelanggan_4nf (
    ID_Alamat INT AUTO_INCREMENT PRIMARY KEY,
    ID_Pelanggan INT NOT NULL,
    Alamat TEXT NOT NULL,
    Jenis_Alamat ENUM('Utama', 'Alternatif') DEFAULT 'Utama',
    FOREIGN KEY (ID_Pelanggan) REFERENCES pelanggan_4nf (ID_Pelanggan)
);

CREATE TABLE karyawan_4nf (
    ID_Karyawan INT AUTO_INCREMENT PRIMARY KEY,
    Nama_Karyawan VARCHAR(100) NOT NULL UNIQUE
);

-- Tabel terpisah untuk shift karyawan (jika karyawan bisa multiple shift)
CREATE TABLE shift_karyawan_4nf (
    ID_Shift INT AUTO_INCREMENT PRIMARY KEY,
    ID_Karyawan INT NOT NULL,
    Shift_Karyawan ENUM('Pagi', 'Sore', 'Malam') NOT NULL,
    Tanggal_Berlaku DATE NOT NULL,
    FOREIGN KEY (ID_Karyawan) REFERENCES karyawan_4nf (ID_Karyawan)
);

CREATE TABLE kategori_4nf (
    ID_Kategori INT AUTO_INCREMENT PRIMARY KEY,
    Nama_Kategori VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE barang_4nf (
    ID_Barang INT AUTO_INCREMENT PRIMARY KEY,
    Nama_Barang VARCHAR(100) NOT NULL,
    ID_Kategori INT NOT NULL,
    Harga_Sewa DECIMAL(10, 2) NOT NULL CHECK (Harga_Sewa >= 0),
    FOREIGN KEY (ID_Kategori) REFERENCES kategori_4nf (ID_Kategori)
);

-- Tabel terpisah untuk tracking kondisi barang dari waktu ke waktu
CREATE TABLE kondisi_barang_4nf (
    ID_Kondisi INT AUTO_INCREMENT PRIMARY KEY,
    ID_Barang INT NOT NULL,
    Kondisi ENUM(
        'Baik',
        'Rusak',
        'Diperbaiki',
        'Dihapus'
    ) NOT NULL,
    Tanggal_Kondisi DATETIME NOT NULL,
    Keterangan TEXT,
    FOREIGN KEY (ID_Barang) REFERENCES barang_4nf (ID_Barang)
);

CREATE TABLE transaksi_4nf (
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
    FOREIGN KEY (ID_Pelanggan) REFERENCES pelanggan_4nf (ID_Pelanggan),
    FOREIGN KEY (ID_Karyawan) REFERENCES karyawan_4nf (ID_Karyawan),
    CHECK (
        Tanggal_Kembali >= Tanggal_Pinjam
    )
);

CREATE TABLE detail_transaksi_4nf (
    ID_Detail INT AUTO_INCREMENT PRIMARY KEY,
    ID_Transaksi INT NOT NULL,
    ID_Barang INT NOT NULL,
    Jumlah INT NOT NULL DEFAULT 1 CHECK (Jumlah > 0),
    Harga_Satuan DECIMAL(10, 2) NOT NULL CHECK (Harga_Satuan >= 0),
    ID_Kondisi_Awal INT NOT NULL,
    ID_Kondisi_Kembali INT,
    FOREIGN KEY (ID_Transaksi) REFERENCES transaksi_4nf (ID_Transaksi),
    FOREIGN KEY (ID_Barang) REFERENCES barang_4nf (ID_Barang),
    FOREIGN KEY (ID_Kondisi_Awal) REFERENCES kondisi_barang_4nf (ID_Kondisi),
    FOREIGN KEY (ID_Kondisi_Kembali) REFERENCES kondisi_barang_4nf (ID_Kondisi),
    UNIQUE (ID_Transaksi, ID_Barang)
);

-- Tabel terpisah untuk ulasan (memisahkan multi-valued dependency untuk rating/ulasan)
CREATE TABLE ulasan_transaksi_4nf (
    ID_Ulasan INT AUTO_INCREMENT PRIMARY KEY,
    ID_Transaksi INT NOT NULL,
    Rating INT CHECK (
        Rating >= 1
        AND Rating <= 5
    ),
    Ulasan_Pelanggan TEXT,
    Tanggal_Ulasan DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ID_Transaksi) REFERENCES transaksi_4nf (ID_Transaksi)
);

-- ✅ HASIL 4NF:
-- - Memenuhi 1NF, 2NF, 3NF, dan BCNF
-- - Tidak ada multi-valued dependencies
-- - Setiap multi-valued attribute dipisah ke tabel terpisah
-- - Struktur lebih fleksibel untuk perubahan business rule