<?php
require_once 'auth_check.php';
require_once 'config/database.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Rental Alat Pendakian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-brand {
            font-weight: bold;
            color: #28a745 !important;
        }

        .card {
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            font-size: 3rem;
            color: #28a745;
            margin-bottom: 1rem;
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .hero-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 4rem 0;
        }

        .footer {
            background-color: #343a40;
            color: white;
            padding: 2rem 0;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-mountain"></i> Rental Alat Pendakian
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-database"></i> Data Master
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="data_barang.php">
                                    <i class="fas fa-box"></i> Data Barang
                                </a></li>
                            <li><a class="dropdown-item" href="data_pelanggan.php">
                                    <i class="fas fa-users"></i> Data Pelanggan
                                </a></li>
                            <li><a class="dropdown-item" href="data_karyawan.php">
                                    <i class="fas fa-user-tie"></i> Data Karyawan
                                </a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="data_transaksi.php">
                                    <i class="fas fa-handshake"></i> Transaksi
                                </a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-chart-bar"></i> Laporan
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="laporan_frekuensi.php">
                                    <i class="fas fa-chart-pie"></i> Laporan Frekuensi
                                </a></li>
                            <li><a class="dropdown-item" href="laporan_keterlambatan.php">
                                    <i class="fas fa-clock"></i> Laporan Keterlambatan
                                </a></li>
                            <li><a class="dropdown-item" href="laporan_durasi.php">
                                    <i class="fas fa-calendar-alt"></i> Laporan Durasi
                                </a></li>
                            <li><a class="dropdown-item" href="laporan_karyawan.php">
                                    <i class="fas fa-users"></i> Laporan Karyawan
                                </a></li>
                            <li><a class="dropdown-item" href="laporan_bulanan.php">
                                    <i class="fas fa-calendar"></i> Laporan Bulanan
                                </a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="laporan_transaksi.php">
                                    <i class="fas fa-file-invoice"></i> Riwayat Transaksi
                                </a></li>
                            <li><a class="dropdown-item" href="laporan_total_per_orang.php">
                                    <i class="fas fa-calculator"></i> Total Transaksi per Orang
                                </a></li>
                            <li><a class="dropdown-item" href="laporan_barang_rusak.php">
                                    <i class="fas fa-exclamation-triangle"></i> Barang Bermasalah
                                </a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-eye"></i> Views
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="view_detail_transaksi.php">
                                    <i class="fas fa-table"></i> Detail Transaksi
                                </a></li>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['nama_karyawan']) ?>
                            <span class="badge bg-secondary ms-1"><?= htmlspecialchars($_SESSION['posisi']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <h6 class="dropdown-header">
                                    <i class="fas fa-user-circle"></i> Profil Pengguna
                                </h6>
                            </li>
                            <li><span class="dropdown-item-text">
                                    <strong><?= htmlspecialchars($_SESSION['nama_karyawan']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($_SESSION['posisi']) ?></small>
                                </span></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
            </ul>
        </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">
                <i class="fas fa-mountain"></i> Sistem Rental Alat Pendakian
            </h1>
            <p class="lead mb-4">Dashboard Analisis dan Pelaporan Lengkap</p>
            <p class="mb-0">Praktikum Basis Data - Normalisasi Database & Advanced Queries</p>
        </div>
    </section>

    <!-- Statistics Cards -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Statistik Utama</h2>
            <div class="row">
                <?php
                // Total Transaksi
                $query = "SELECT COUNT(*) as total FROM transaksi";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $total_transaksi = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

                // Total Pendapatan
                $query = "SELECT SUM(total_harga + denda) as total FROM transaksi";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $total_pendapatan = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

                // Total Pelanggan
                $query = "SELECT COUNT(*) as total FROM pelanggan";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $total_pelanggan = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

                // Total Barang
                $query = "SELECT COUNT(*) as total FROM barang";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $total_barang = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                ?>

                <div class="col-md-3 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-receipt feature-icon"></i>
                            <h3 class="text-success"><?= number_format($total_transaksi) ?></h3>
                            <p class="card-text">Total Transaksi</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-money-bill-wave feature-icon"></i>
                            <h3 class="text-success">Rp <?= number_format($total_pendapatan ?: 0) ?></h3>
                            <p class="card-text">Total Pendapatan</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-users feature-icon"></i>
                            <h3 class="text-success"><?= number_format($total_pelanggan) ?></h3>
                            <p class="card-text">Total Pelanggan</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-box feature-icon"></i>
                            <h3 class="text-success"><?= number_format($total_barang) ?></h3>
                            <p class="card-text">Total Barang</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Feature Cards -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Fitur Laporan</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line feature-icon"></i>
                            <h5 class="card-title">Laporan Frekuensi</h5>
                            <p class="card-text">Analisis barang yang paling sering disewa</p>
                            <a href="laporan_frekuensi.php" class="btn btn-success">
                                <i class="fas fa-eye"></i> Lihat Laporan
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-clock feature-icon"></i>
                            <h5 class="card-title">Keterlambatan</h5>
                            <p class="card-text">Statistik keterlambatan dan denda pelanggan</p>
                            <a href="laporan_keterlambatan.php" class="btn btn-success">
                                <i class="fas fa-eye"></i> Lihat Laporan
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-exclamation-triangle feature-icon"></i>
                            <h5 class="card-title">Barang Bermasalah</h5>
                            <p class="card-text">Deteksi barang yang sering rusak</p>
                            <a href="laporan_barang_rusak.php" class="btn btn-success">
                                <i class="fas fa-eye"></i> Lihat Laporan
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-user-tie feature-icon"></i>
                            <h5 class="card-title">Kinerja Karyawan</h5>
                            <p class="card-text">Analisis performa dan rating karyawan</p>
                            <a href="laporan_karyawan.php" class="btn btn-success">
                                <i class="fas fa-eye"></i> Lihat Laporan
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-calendar-alt feature-icon"></i>
                            <h5 class="card-title">Laporan Bulanan</h5>
                            <p class="card-text">Identifikasi pola penyewaan bulanan</p>
                            <a href="laporan_bulanan.php" class="btn btn-success">
                                <i class="fas fa-eye"></i> Lihat Laporan
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-calculator feature-icon"></i>
                            <h5 class="card-title">Total per Orang</h5>
                            <p class="card-text">Total pembayaran (sewa + denda) per pelanggan</p>
                            <a href="laporan_total_per_orang.php" class="btn btn-success">
                                <i class="fas fa-eye"></i> Lihat Laporan
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-history feature-icon"></i>
                            <h5 class="card-title">Riwayat Transaksi</h5>
                            <p class="card-text">Riwayat lengkap transaksi semua pelanggan</p>
                            <a href="laporan_transaksi.php" class="btn btn-success">
                                <i class="fas fa-eye"></i> Lihat Laporan
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 3: Additional Features -->
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-bar feature-icon"></i>
                            <h5 class="card-title">Durasi Sewa</h5>
                            <p class="card-text">Rata-rata durasi sewa per kategori barang</p>
                            <a href="laporan_durasi.php" class="btn btn-success">
                                <i class="fas fa-eye"></i> Lihat Laporan
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-database feature-icon"></i>
                            <h5 class="card-title">Database Views</h5>
                            <p class="card-text">Akses views untuk analisis mendalam</p>
                            <a href="view_detail_transaksi.php" class="btn btn-success">
                                <i class="fas fa-eye"></i> Lihat Views
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-cogs feature-icon"></i>
                            <h5 class="card-title">Data Master</h5>
                            <p class="card-text">Kelola data barang, pelanggan, karyawan</p>
                            <a href="data_barang.php" class="btn btn-success">
                                <i class="fas fa-eye"></i> Kelola Data
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container text-center">
            <div class="row">
                <div class="col-md-12">
                    <h5>Sistem Informasi Rental Alat Pendakian</h5>
                    <p class="mb-0">Praktikum Basis Data - Normalisasi & Advanced Queries</p>
                    <p class="mb-0">
                        <i class="fas fa-code"></i> Built with PHP, MySQL & Bootstrap
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>