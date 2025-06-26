-- =====================================================
-- NORMALISASI 5NF (Fifth Normal Form / Project-Join Normal Form)
-- Menghilangkan join dependencies
-- =====================================================

-- Analisis Join Dependencies:
-- Dalam sistem rental, ada kemungkinan join dependency seperti:
-- (Pelanggan, Barang, Karyawan) dapat didekomposisi menjadi:
-- (Pelanggan, Barang), (Barang, Karyawan), (Karyawan, Pelanggan)

-- Untuk 5NF, kita perlu memastikan tidak ada lossless decomposition

-- Tabel entitas dasar
CREATE TABLE pelanggan_5nf (
    ID_Pelanggan INT AUTO_INCREMENT PRIMARY KEY,
    Nama_Pelanggan VARCHAR(100) NOT NULL UNIQUE,
    No_HP VARCHAR(15) NOT NULL UNIQUE,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Status_Pelanggan ENUM('Aktif', 'Tidak Aktif') DEFAULT 'Aktif'
);

CREATE TABLE alamat_pelanggan_5nf (
    ID_Alamat INT AUTO_INCREMENT PRIMARY KEY,
    ID_Pelanggan INT NOT NULL,
    Alamat TEXT NOT NULL,
    Jenis_Alamat ENUM('Utama', 'Alternatif') DEFAULT 'Utama',
    FOREIGN KEY (ID_Pelanggan) REFERENCES pelanggan_5nf (ID_Pelanggan)
);

CREATE TABLE karyawan_5nf (
    ID_Karyawan INT AUTO_INCREMENT PRIMARY KEY,
    Nama_Karyawan VARCHAR(100) NOT NULL UNIQUE,
    Status_Karyawan ENUM('Aktif', 'Tidak Aktif') DEFAULT 'Aktif'
);

CREATE TABLE shift_5nf (
    ID_Shift INT AUTO_INCREMENT PRIMARY KEY,
    Nama_Shift ENUM('Pagi', 'Sore', 'Malam') NOT NULL UNIQUE
);

CREATE TABLE jadwal_karyawan_5nf (
    ID_Jadwal INT AUTO_INCREMENT PRIMARY KEY,
    ID_Karyawan INT NOT NULL,
    ID_Shift INT NOT NULL,
    Tanggal DATE NOT NULL,
    FOREIGN KEY (ID_Karyawan) REFERENCES karyawan_5nf (ID_Karyawan),
    FOREIGN KEY (ID_Shift) REFERENCES shift_5nf (ID_Shift),
    UNIQUE (ID_Karyawan, Tanggal)
);

CREATE TABLE kategori_5nf (
    ID_Kategori INT AUTO_INCREMENT PRIMARY KEY,
    Nama_Kategori VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE barang_5nf (
    ID_Barang INT AUTO_INCREMENT PRIMARY KEY,
    Nama_Barang VARCHAR(100) NOT NULL,
    ID_Kategori INT NOT NULL,
    Harga_Sewa DECIMAL(10, 2) NOT NULL CHECK (Harga_Sewa >= 0),
    Status_Barang ENUM(
        'Tersedia',
        'Disewa',
        'Rusak',
        'Maintenance'
    ) DEFAULT 'Tersedia',
    FOREIGN KEY (ID_Kategori) REFERENCES kategori_5nf (ID_Kategori)
);

-- Dekomposisi join dependency untuk relationship yang kompleks

-- Tabel 1: Relasi Pelanggan-Barang (preferensi/history)
CREATE TABLE preferensi_pelanggan_barang_5nf (
    ID_Preferensi INT AUTO_INCREMENT PRIMARY KEY,
    ID_Pelanggan INT NOT NULL,
    ID_Barang INT NOT NULL,
    Tingkat_Preferensi INT CHECK (
        Tingkat_Preferensi >= 1
        AND Tingkat_Preferensi <= 5
    ),
    FOREIGN KEY (ID_Pelanggan) REFERENCES pelanggan_5nf (ID_Pelanggan),
    FOREIGN KEY (ID_Barang) REFERENCES barang_5nf (ID_Barang),
    UNIQUE (ID_Pelanggan, ID_Barang)
);

-- Tabel 2: Relasi Barang-Karyawan (expertise/specialization)
CREATE TABLE keahlian_karyawan_barang_5nf (
    ID_Keahlian INT AUTO_INCREMENT PRIMARY KEY,
    ID_Karyawan INT NOT NULL,
    ID_Barang INT NOT NULL,
    Level_Keahlian INT CHECK (
        Level_Keahlian >= 1
        AND Level_Keahlian <= 5
    ),
    FOREIGN KEY (ID_Karyawan) REFERENCES karyawan_5nf (ID_Karyawan),
    FOREIGN KEY (ID_Barang) REFERENCES barang_5nf (ID_Barang),
    UNIQUE (ID_Karyawan, ID_Barang)
);

-- Tabel 3: Relasi Karyawan-Pelanggan (service history)
CREATE TABLE riwayat_layanan_karyawan_5nf (
    ID_Riwayat INT AUTO_INCREMENT PRIMARY KEY,
    ID_Karyawan INT NOT NULL,
    ID_Pelanggan INT NOT NULL,
    Total_Transaksi INT DEFAULT 0,
    Rata_Rating DECIMAL(3, 2),
    FOREIGN KEY (ID_Karyawan) REFERENCES karyawan_5nf (ID_Karyawan),
    FOREIGN KEY (ID_Pelanggan) REFERENCES pelanggan_5nf (ID_Pelanggan),
    UNIQUE (ID_Karyawan, ID_Pelanggan)
);

-- Tabel transaksi yang menggabungkan semua relasi
CREATE TABLE transaksi_5nf (
    ID_Transaksi INT AUTO_INCREMENT PRIMARY KEY,
    ID_Pelanggan INT NOT NULL,
    ID_Karyawan INT NOT NULL,
    Tanggal_Pinjam DATE NOT NULL,
    Tanggal_Kembali DATE NOT NULL,
    Status_Transaksi ENUM(
        'Aktif',
        'Selesai',
        'Dibatalkan'
    ) DEFAULT 'Aktif',
    FOREIGN KEY (ID_Pelanggan) REFERENCES pelanggan_5nf (ID_Pelanggan),
    FOREIGN KEY (ID_Karyawan) REFERENCES karyawan_5nf (ID_Karyawan),
    CHECK (
        Tanggal_Kembali >= Tanggal_Pinjam
    )
);

-- Tabel detail transaksi
CREATE TABLE detail_transaksi_5nf (
    ID_Detail INT AUTO_INCREMENT PRIMARY KEY,
    ID_Transaksi INT NOT NULL,
    ID_Barang INT NOT NULL,
    Jumlah INT NOT NULL DEFAULT 1 CHECK (Jumlah > 0),
    Harga_Satuan DECIMAL(10, 2) NOT NULL CHECK (Harga_Satuan >= 0),
    FOREIGN KEY (ID_Transaksi) REFERENCES transaksi_5nf (ID_Transaksi),
    FOREIGN KEY (ID_Barang) REFERENCES barang_5nf (ID_Barang),
    UNIQUE (ID_Transaksi, ID_Barang)
);

-- Tabel pembayaran (terpisah untuk menghindari join dependency)
CREATE TABLE pembayaran_5nf (
    ID_Pembayaran INT AUTO_INCREMENT PRIMARY KEY,
    ID_Transaksi INT NOT NULL,
    Metode_Bayar ENUM(
        'Transfer',
        'Cash',
        'E-Wallet'
    ) NOT NULL,
    Status_Bayar ENUM(
        'Lunas',
        'Belum Lunas',
        'Partial'
    ) NOT NULL,
    Tanggal_Bayar DATETIME,
    Jumlah_Bayar DECIMAL(10, 2) NOT NULL CHECK (Jumlah_Bayar >= 0),
    FOREIGN KEY (ID_Transaksi) REFERENCES transaksi_5nf (ID_Transaksi)
);

-- Tabel denda (terpisah untuk menghindari join dependency)
CREATE TABLE denda_5nf (
    ID_Denda INT AUTO_INCREMENT PRIMARY KEY,
    ID_Transaksi INT NOT NULL,
    Jumlah_Denda DECIMAL(10, 2) NOT NULL CHECK (Jumlah_Denda >= 0),
    Keterangan_Denda TEXT,
    Tanggal_Denda DATE NOT NULL,
    Status_Denda ENUM('Belum Bayar', 'Lunas') DEFAULT 'Belum Bayar',
    FOREIGN KEY (ID_Transaksi) REFERENCES transaksi_5nf (ID_Transaksi)
);

-- Tabel kondisi barang tracking
CREATE TABLE kondisi_barang_5nf (
    ID_Kondisi INT AUTO_INCREMENT PRIMARY KEY,
    ID_Barang INT NOT NULL,
    ID_Transaksi INT,
    Kondisi ENUM('Baik', 'Rusak', 'Diperbaiki') NOT NULL,
    Tanggal_Kondisi DATETIME DEFAULT CURRENT_TIMESTAMP,
    Keterangan TEXT,
    FOREIGN KEY (ID_Barang) REFERENCES barang_5nf (ID_Barang),
    FOREIGN KEY (ID_Transaksi) REFERENCES transaksi_5nf (ID_Transaksi)
);

-- Tabel ulasan (terpisah dari transaksi)
CREATE TABLE ulasan_5nf (
    ID_Ulasan INT AUTO_INCREMENT PRIMARY KEY,
    ID_Transaksi INT NOT NULL,
    ID_Pelanggan INT NOT NULL,
    Rating INT CHECK (
        Rating >= 1
        AND Rating <= 5
    ),
    Ulasan_Pelanggan TEXT,
    Tanggal_Ulasan DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ID_Transaksi) REFERENCES transaksi_5nf (ID_Transaksi),
    FOREIGN KEY (ID_Pelanggan) REFERENCES pelanggan_5nf (ID_Pelanggan),
    UNIQUE (ID_Transaksi, ID_Pelanggan)
);

-- ✅ HASIL 5NF:
-- - Memenuhi 1NF, 2NF, 3NF, BCNF, dan 4NF
-- - Tidak ada join dependencies yang dapat didekomposisi lebih lanjut
-- - Setiap tabel mewakili satu konsep yang tidak dapat dipecah lagi
-- - Struktur optimal untuk query performance dan data integrity
-- - Fleksibilitas maksimal untuk perubahan business requirements