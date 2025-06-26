-- =====================================================
-- RINGKASAN PROSES NORMALISASI
-- Sistem Informasi Rental Alat Pendakian
-- =====================================================

/*
PERJALANAN NORMALISASI:

📊 DATA AWAL (0NF - Flat File):
- Satu tabel besar dengan multi-valued attributes
- Redundansi data sangat tinggi
- Anomali insert, update, delete

🔧 1NF (First Normal Form):
- Menghilangkan multi-valued attributes
- Setiap cell berisi nilai tunggal
- Data dipecah menjadi multiple rows

🔧 2NF (Second Normal Form):
- Menghilangkan partial dependencies
- Memisahkan data yang hanya bergantung pada sebagian primary key
- Tabel: TRANSAKSI, BARANG, DETAIL_TRANSAKSI

🔧 3NF (Third Normal Form):
- Menghilangkan transitive dependencies
- Memisahkan: PELANGGAN, KARYAWAN, KATEGORI
- 6 tabel: PELANGGAN, KARYAWAN, KATEGORI, BARANG, TRANSAKSI, DETAIL_TRANSAKSI

🔧 BCNF (Boyce-Codd Normal Form):
- Memastikan setiap determinant adalah super key
- Menambahkan constraint untuk data integrity
- Struktur tetap sama dengan 3NF

🔧 4NF (Fourth Normal Form):
- Menghilangkan multi-valued dependencies
- Memisahkan: ALAMAT_PELANGGAN, SHIFT_KARYAWAN, KONDISI_BARANG, ULASAN
- 9 tabel dengan struktur lebih fleksibel

🔧 5NF (Fifth Normal Form):
- Menghilangkan join dependencies
- Dekomposisi kompleks: PREFERENSI, KEAHLIAN, RIWAYAT_LAYANAN
- 14 tabel dengan optimal design

KEUNTUNGAN SETIAP TAHAP:

1NF ✅ Atomic values
2NF ✅ Reduced redundancy
3NF ✅ Minimal transitive dependency
BCNF ✅ Strong key constraints
4NF ✅ Independent multi-values
5NF ✅ No decomposable joins

TRADE-OFFS:
- Normalisasi tinggi = Query complexity ↑
- Normalisasi tinggi = Data integrity ↑
- Normalisasi tinggi = Storage efficiency ↑
- Normalisasi tinggi = Join operations ↑

REKOMENDASI:
Untuk sistem rental alat pendakian, 3NF sudah cukup optimal
karena memberikan balance antara data integrity dan performance.
*/

-- =====================================================
-- PERBANDINGAN STRUKTUR
-- =====================================================

-- 0NF: 1 tabel dengan 20+ kolom, banyak redundansi
-- 1NF: 1 tabel, data dipecah per item
-- 2NF: 3 tabel (TRANSAKSI, BARANG, DETAIL)
-- 3NF: 6 tabel (+ PELANGGAN, KARYAWAN, KATEGORI)
-- BCNF: 6 tabel + constraints
-- 4NF: 9 tabel (+ ALAMAT, SHIFT, KONDISI, ULASAN terpisah)
-- 5NF: 14 tabel (+ PREFERENSI, KEAHLIAN, RIWAYAT, dll)

-- =====================================================
-- QUERIES UNTUK TESTING NORMALISASI
-- =====================================================

-- Test query untuk memastikan data masih dapat direkonstruksi
-- (Menggunakan struktur 3NF sebagai contoh)

-- 1. Rekonstruksi data asli dari tabel ternormalisasi
SELECT
    t.ID_Transaksi,
    p.Nama_Pelanggan,
    p.No_HP,
    p.Email,
    p.Alamat,
    b.Nama_Barang,
    kb.Nama_Kategori,
    b.Harga_Sewa,
    dt.Kondisi_Barang,
    t.Tanggal_Pinjam,
    t.Tanggal_Kembali,
    k.Nama_Karyawan,
    k.Shift_Karyawan,
    t.Metode_Bayar,
    t.Status_Bayar,
    t.Tanggal_Bayar,
    t.Denda,
    t.Keterangan_Denda,
    t.Ulasan_Pelanggan,
    t.Rating
FROM
    transaksi_3nf t
    JOIN pelanggan_3nf p ON t.ID_Pelanggan = p.ID_Pelanggan
    JOIN karyawan_3nf k ON t.ID_Karyawan = k.ID_Karyawan
    JOIN detail_transaksi_3nf dt ON t.ID_Transaksi = dt.ID_Transaksi
    JOIN barang_3nf b ON dt.ID_Barang = b.ID_Barang
    JOIN kategori_3nf kb ON b.ID_Kategori = kb.ID_Kategori
ORDER BY t.ID_Transaksi;

-- 2. Test integrity constraints
-- Cek foreign key violations
-- Cek data consistency
-- Cek business rules

-- =====================================================
-- KESIMPULAN
-- =====================================================

/*
PILIHAN STRUKTUR FINAL:
Untuk sistem rental alat pendakian, struktur 3NF (seperti yang sudah 
diimplementasi di rental_alat_pendakian.sql) adalah pilihan terbaik 
karena:

1. ✅ Menghilangkan redundansi utama
2. ✅ Mencegah anomali data
3. ✅ Query performance masih optimal
4. ✅ Mudah dipahami dan dikelola
5. ✅ Mendukung semua fitur yang diminta

Struktur ini terdiri dari:
- PELANGGAN (master data pelanggan)
- KARYAWAN (master data karyawan) 
- KATEGORI_BARANG (master kategori)
- BARANG (master barang/alat)
- TRANSAKSI (header transaksi)
- DETAIL_TRANSAKSI (detail barang per transaksi)

Total: 6 tabel dengan relasi 1:N yang jelas dan efisien.
*/