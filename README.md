# Sistem Rental Alat Pendakian - PHP Web Interface

Sistem web PHP untuk menampilkan laporan dan analisis data rental alat pendakian yang telah dinormalisasi.

## 🌟 Fitur Utama Sistem

### 🔐 Sistem Authentication & Security
- **Login/Logout Secure**: Session management dengan timeout 4 jam
- **Role-based Access Control**: Kontrol akses berdasarkan posisi karyawan
- **Password Protection**: Hash security untuk data sensitif
- **Session Management**: Auto-logout dan security checks

### 📋 Data Management (CRUD Lengkap)
- **Data Barang**: Create, Read, Update, Delete dengan validasi
- **Data Pelanggan**: Customer management dengan history transaksi
- **Data Karyawan**: Employee management dengan authentication
- **Data Transaksi**: Complete transaction lifecycle management

### 📊 Dashboard & Analytics
- **Interactive Dashboard**: Real-time statistics dengan Chart.js
- **Multi-level Navigation**: Dropdown menu dengan kategori lengkap
- **Responsive Design**: Bootstrap 5 untuk semua device
- **Data Visualization**: Interactive charts dan metrics

### 📈 Laporan Bisnis Komprehensif (8 Laporan)
1. **Laporan Frekuensi Penyewaan**: `laporan_frekuensi.php`
2. **Statistik Keterlambatan**: `laporan_keterlambatan.php`
3. **Riwayat Transaksi**: `laporan_transaksi.php`
4. **Barang Bermasalah**: `laporan_barang_rusak.php`
5. **Durasi Sewa per Kategori**: `laporan_durasi.php`
6. **Kinerja Karyawan**: `laporan_karyawan.php`
7. **Laporan Bulanan**: `laporan_bulanan.php`
8. **Total Transaksi**: `laporan_total_transaksi.php`

### 🔍 Database Views (6 Views)
1. **Detail Transaksi Lengkap**: `view_detail_transaksi.php`
2. **Summary Kategori**: `view_summary_kategori.php`
3. **Analisis Pelanggan**: `view_analisis_pelanggan.php`
4. **Kinerja Karyawan Detail**: `view_kinerja_karyawan.php`
5. **Executive Dashboard**: `view_executive_dashboard.php`
6. **Barang Bermasalah**: `view_barang_bermasalah.php`

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP 7.4+ dengan PDO
- **Database**: MySQL/MariaDB
- **Frontend**: Bootstrap 5.3, Font Awesome 6
- **JavaScript**: jQuery, DataTables, Chart.js
- **Server**: Laragon (Apache + MySQL)

## 📁 Struktur File

```
prak_basdat_TA/
├── config/
│   └── database.php              # Konfigurasi database
├── index.php                     # Dashboard utama
├── laporan_frekuensi.php        # Laporan 1
├── laporan_keterlambatan.php    # Laporan 2
├── view_detail_transaksi.php    # View 1
├── rental_alat_pendakian.sql    # Database SQL lengkap
├── normalisasi_1nf.sql          # Normalisasi 1NF
├── normalisasi_2nf.sql          # Normalisasi 2NF
├── normalisasi_3nf.sql          # Normalisasi 3NF
├── normalisasi_bcnf.sql         # Normalisasi BCNF
├── normalisasi_4nf.sql          # Normalisasi 4NF
├── normalisasi_5nf.sql          # Normalisasi 5NF
└── ringkasan_normalisasi.sql    # Ringkasan lengkap
```

## 🚀 Cara Instalasi

### 1. Setup Environment
```bash
# Pastikan Laragon sudah terinstall
# Start Apache dan MySQL di Laragon
```

### 2. Setup Database
```sql
-- Import file SQL ke MySQL
mysql -u root -p < rental_alat_pendakian.sql
```

### 3. Konfigurasi Database
Edit `config/database.php`:
```php
private $host = "localhost";
private $db_name = "rental_alat_pendakian";
private $username = "root";
private $password = "";
```

### 4. Akses Website
```
http://localhost/prak_basdat_TA/
```

## 📊 Fitur Analisis

### 🎯 Interactive Charts
- **Bar Charts**: Frekuensi penyewaan, pendapatan
- **Pie Charts**: Distribusi status pelanggan
- **Progress Bars**: Persentase keterlambatan

### 📋 Data Tables
- **Search & Filter**: Real-time filtering
- **Sorting**: Multi-column sorting
- **Pagination**: Efficient data display
- **Export**: Data export capabilities

### 🔢 Statistics Cards
- **Real-time Data**: Auto-updated from database
- **Color-coded**: Status indicators
- **Interactive**: Click to drill-down

## 🎨 UI/UX Features

### 🎯 Responsive Design
- **Mobile-first**: Bootstrap responsive grid
- **Cross-browser**: Compatible dengan semua browser modern
- **Fast Loading**: Optimized queries dan caching

### 🎪 Visual Elements
- **Icons**: Font Awesome icons
- **Colors**: Professional color scheme
- **Animations**: Smooth transitions
- **Cards**: Modern card-based layout

## 🔧 Advanced Features

### 🛡️ Security
- **PDO Prepared Statements**: SQL injection protection
- **Input Validation**: Data sanitization
- **Error Handling**: Graceful error management

### ⚡ Performance
- **Efficient Queries**: Optimized SQL views
- **Lazy Loading**: On-demand data loading
- **Caching**: Query result caching

### 📱 Mobile Support
- **Responsive Tables**: Horizontal scrolling
- **Touch-friendly**: Mobile navigation
- **Adaptive Layout**: Screen size optimization

## 📈 Database Views Implemented

1. **v_detail_transaksi_lengkap** (5 tables JOIN)
2. **v_summary_kategori** (4 tables JOIN)
3. **v_analisis_pelanggan** (4 tables JOIN)
4. **v_kinerja_karyawan_detail** (5 tables JOIN)
5. **v_dashboard_executive** (6 tables JOIN)
6. **v_barang_bermasalah** (4 tables JOIN + complex WHERE)

## 🎓 Educational Value

### 📚 Database Concepts
- **Normalization**: 1NF hingga 5NF
- **Complex Joins**: Multi-table relationships
- **Aggregate Functions**: COUNT, SUM, AVG, GROUP BY
- **Views**: Complex view creation

### 💻 Web Development
- **MVC Pattern**: Separation of concerns
- **REST-like URLs**: Clean URL structure
- **AJAX**: Dynamic content loading
- **Progressive Enhancement**: Graceful degradation

## 🔍 Testing & Validation

### ✅ SQL Compatibility
- **ONLY_FULL_GROUP_BY**: Compatible mode
- **Cross-platform**: MySQL/MariaDB support
- **Error Handling**: Comprehensive error catching

### 🧪 Data Validation
- **Input Sanitization**: XSS protection
- **Type Checking**: Data type validation
- **Boundary Testing**: Edge case handling

## 📝 Documentation

Setiap file PHP dilengkapi dengan:
- **Header Comments**: Deskripsi fungsi
- **Inline Comments**: Penjelasan kode
- **SQL Comments**: Query explanation
- **Error Messages**: User-friendly errors

## 🎯 Demo Features

1. **Live Statistics**: Real-time dashboard
2. **Interactive Filters**: Dynamic data filtering
3. **Export Functionality**: Data export options
4. **Print Support**: Print-friendly layouts
5. **Search Integration**: Global search functionality

## 🚀 Deployment Ready

- **Production Ready**: Error handling lengkap
- **Scalable**: Efficient database queries
- **Maintainable**: Clean code structure
- **Documented**: Comprehensive documentation

### 💻 File-file Sistem Lengkap

#### 🔐 Authentication & Security
- `login.php` - Halaman login dengan validasi
- `logout.php` - Logout handler 
- `auth_check.php` - Session validation middleware

#### 📋 Data Management (CRUD)
- `data_barang.php` - Management barang dengan CRUD
- `data_pelanggan.php` - Management pelanggan dengan statistics
- `data_karyawan.php` - Management karyawan dengan roles
- `data_transaksi.php` - Transaction management system

#### 🎯 Core System
- `config/` - Konfigurasi sistem
- `index.php` - Halaman utama
- `404.php` - Halaman tidak ditemukan
- `500.php` - Halaman kesalahan server

#### 📊 Laporan & Analisis
- `laporan_frekuensi.php` - Laporan frekuensi penyewaan
- `laporan_keterlambatan.php` - Laporan statistik keterlambatan
- `laporan_transaksi.php` - Riwayat transaksi
- `laporan_barang_rusak.php` - Laporan barang bermasalah
- `laporan_durasi.php` - Durasi sewa per kategori
- `laporan_karyawan.php` - Kinerja karyawan
- `laporan_bulanan.php` - Laporan bulanan
- `laporan_total_transaksi.php` - Total transaksi

#### 🔍 Views Database
- `view_detail_transaksi.php` - Detail transaksi lengkap
- `view_summary_kategori.php` - Summary kategori
- `view_analisis_pelanggan.php` - Analisis pelanggan
- `view_kinerja_karyawan.php` - Kinerja karyawan detail
- `view_executive_dashboard.php` - Executive dashboard
- `view_barang_bermasalah.php` - Barang bermasalah

#### 📁 File SQL
- `rental_alat_pendakian.sql` - Database SQL lengkap
- `normalisasi_1nf.sql` - Normalisasi 1NF
- `normalisasi_2nf.sql` - Normalisasi 2NF
- `normalisasi_3nf.sql` - Normalisasi 3NF
- `normalisasi_bcnf.sql` - Normalisasi BCNF
- `normalisasi_4nf.sql` - Normalisasi 4NF
- `normalisasi_5nf.sql` - Normalisasi 5NF
- `ringkasan_normalisasi.sql` - Ringkasan lengkap
