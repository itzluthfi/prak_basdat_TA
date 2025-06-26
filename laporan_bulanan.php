<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get current year and month for filter
$current_year = date('Y');
$current_month = date('m');

// Handle filter parameters
$filter_year = isset($_GET['year']) ? $_GET['year'] : $current_year;
$filter_month = isset($_GET['month']) ? $_GET['month'] : $current_month;

// Query untuk laporan bulanan
$query = "SELECT 
    YEAR(t.tanggal_pinjam) as tahun,
    MONTH(t.tanggal_pinjam) as bulan,
    MONTHNAME(t.tanggal_pinjam) as nama_bulan,
    COUNT(t.id_transaksi) as total_transaksi,
    SUM(CASE WHEN t.status_bayar = 'Lunas' THEN 1 ELSE 0 END) as transaksi_lunas,
    SUM(CASE WHEN t.status_bayar = 'Belum Lunas' THEN 1 ELSE 0 END) as transaksi_belum_lunas,
    COUNT(DISTINCT t.id_pelanggan) as pelanggan_unik,
    SUM(dt.jumlah) as total_barang_disewa,
    SUM(dt.jumlah * dt.harga_satuan) as total_pendapatan,
    SUM(t.denda) as total_denda,
    AVG(dt.jumlah * dt.harga_satuan) as rata_pendapatan_per_transaksi,
    (SUM(CASE WHEN t.status_bayar = 'Lunas' THEN 1 ELSE 0 END) / NULLIF(COUNT(t.id_transaksi), 0) * 100) as persentase_lunas,
    AVG(t.rating) as rata_rata_rating
FROM transaksi t
JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
WHERE YEAR(t.tanggal_pinjam) = :filter_year
GROUP BY YEAR(t.tanggal_pinjam), MONTH(t.tanggal_pinjam)
ORDER BY tahun DESC, bulan DESC";

$stmt = $db->prepare($query);
$stmt->bindParam(':filter_year', $filter_year, PDO::PARAM_INT);
$stmt->execute();
$monthly_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk data bulan tertentu (detail)
$detail_query = "SELECT 
    DATE(t.tanggal_pinjam) as tanggal,
    COUNT(t.id_transaksi) as transaksi_harian,
    SUM(dt.jumlah * dt.harga_satuan) as pendapatan_harian,
    SUM(t.denda) as denda_harian
FROM transaksi t
JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
WHERE YEAR(t.tanggal_pinjam) = :filter_year 
AND MONTH(t.tanggal_pinjam) = :filter_month
GROUP BY DATE(t.tanggal_pinjam)
ORDER BY tanggal";

$detail_stmt = $db->prepare($detail_query);
$detail_stmt->bindParam(':filter_year', $filter_year, PDO::PARAM_INT);
$detail_stmt->bindParam(':filter_month', $filter_month, PDO::PARAM_INT);
$detail_stmt->execute();
$daily_results = $detail_stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk statistik tahun
$year_stats_query = "SELECT 
    COUNT(t.id_transaksi) as total_transaksi_tahun,
    SUM(dt.jumlah * dt.harga_satuan) as total_pendapatan_tahun,
    AVG(monthly_stats.transaksi_per_bulan) as rata_transaksi_per_bulan,
    AVG(monthly_stats.pendapatan_per_bulan) as rata_pendapatan_per_bulan
FROM transaksi t
JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
JOIN (
    SELECT 
        MONTH(tanggal_pinjam) as bulan,
        COUNT(*) as transaksi_per_bulan,
        SUM(dt2.jumlah * dt2.harga_satuan) as pendapatan_per_bulan
    FROM transaksi t2
    JOIN detail_transaksi dt2 ON t2.id_transaksi = dt2.id_transaksi
    WHERE YEAR(t2.tanggal_pinjam) = :filter_year
    GROUP BY MONTH(t2.tanggal_pinjam)
) monthly_stats
WHERE YEAR(t.tanggal_pinjam) = :filter_year";

$year_stats_stmt = $db->prepare($year_stats_query);
$year_stats_stmt->bindParam(':filter_year', $filter_year, PDO::PARAM_INT);
$year_stats_stmt->execute();
$year_stats = $year_stats_stmt->fetch(PDO::FETCH_ASSOC);

// Get available years for dropdown
$years_query = "SELECT DISTINCT YEAR(tanggal_pinjam) as tahun FROM transaksi ORDER BY tahun DESC";
$years_stmt = $db->prepare($years_query);
$years_stmt->execute();
$available_years = $years_stmt->fetchAll(PDO::FETCH_ASSOC);

$months = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Bulanan - Rental Alat Pendakian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-mountain"></i> Rental Alat Pendakian
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">
                            <i class="fas fa-calendar-alt"></i> Laporan Bulanan
                        </h4>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Laporan komprehensif kinerja bisnis per bulan dan analisis trend tahunan.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-filter"></i> Filter Laporan
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="year" class="form-label">Tahun</label>
                                <select name="year" id="year" class="form-select">
                                    <?php foreach ($available_years as $year): ?>
                                        <option value="<?= $year['tahun'] ?>" <?= $year['tahun'] == $filter_year ? 'selected' : '' ?>>
                                            <?= $year['tahun'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="month" class="form-label">Bulan (untuk detail harian)</label>
                                <select name="month" id="month" class="form-select">
                                    <?php foreach ($months as $num => $name): ?>
                                        <option value="<?= $num ?>" <?= $num == $filter_month ? 'selected' : '' ?>>
                                            <?= $name ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card text-center bg-primary text-white">
                    <div class="card-body">
                        <i class="fas fa-chart-line fa-2x mb-2"></i>
                        <h4><?= number_format($year_stats['total_transaksi_tahun'] ?? 0) ?></h4>
                        <p class="mb-0">Transaksi Tahun <?= $filter_year ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                        <h4>Rp <?= number_format($year_stats['total_pendapatan_tahun'] ?? 0) ?></h4>
                        <p class="mb-0">Pendapatan Tahun</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-warning text-white">
                    <div class="card-body">
                        <i class="fas fa-calendar-check fa-2x mb-2"></i>
                        <h4><?= number_format($year_stats['rata_transaksi_per_bulan'] ?? 0, 1) ?></h4>
                        <p class="mb-0">Avg Transaksi/Bulan</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-info text-white">
                    <div class="card-body">
                        <i class="fas fa-coins fa-2x mb-2"></i>
                        <h4>Rp <?= number_format($year_stats['rata_pendapatan_per_bulan'] ?? 0) ?></h4>
                        <p class="mb-0">Avg Pendapatan/Bulan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Data Table -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-table"></i> Data Bulanan Tahun <?= $filter_year ?>
                        </h5>
                        <div>
                            <button id="exportMonthlyPDF" class="btn btn-danger btn-sm me-2">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                            <button id="exportMonthlyExcel" class="btn btn-success btn-sm">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="monthlyTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Bulan</th>
                                        <th>Total Transaksi</th>
                                        <th>Selesai</th>
                                        <th>Aktif</th>
                                        <th>Pelanggan Unik</th>
                                        <th>Total Barang</th>
                                        <th>Total Pendapatan</th>
                                        <th>Avg/Transaksi</th>
                                        <th>% Selesai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($monthly_results as $row): ?>
                                        <tr>
                                            <td>
                                                <strong><?= $row['nama_bulan'] ?> <?= $row['tahun'] ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary fs-6">
                                                    <?= $row['total_transaksi'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success fs-6">
                                                    <?= $row['transaksi_selesai'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning fs-6">
                                                    <?= $row['transaksi_aktif'] ?>
                                                </span>
                                            </td>
                                            <td><?= $row['pelanggan_unik'] ?></td>
                                            <td><?= number_format($row['total_barang_disewa']) ?></td>
                                            <td>
                                                <strong class="text-success">
                                                    Rp <?= number_format($row['total_pendapatan']) ?>
                                                </strong>
                                            </td>
                                            <td>
                                                Rp <?= number_format($row['rata_pendapatan_per_transaksi']) ?>
                                            </td>
                                            <td>
                                                <?php
                                                $persentase = $row['persentase_selesai'];
                                                $badge_class = 'bg-danger';
                                                if ($persentase >= 90) $badge_class = 'bg-success';
                                                elseif ($persentase >= 75) $badge_class = 'bg-warning';
                                                elseif ($persentase >= 50) $badge_class = 'bg-info';
                                                ?>
                                                <span class="badge <?= $badge_class ?>">
                                                    <?= number_format($persentase, 1) ?>%
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Detail Table -->
        <?php if (!empty($daily_results)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-day"></i> Detail Harian - <?= $months[$filter_month] ?> <?= $filter_year ?>
                            </h5>
                            <div>
                                <button id="exportDailyPDF" class="btn btn-danger btn-sm me-2">
                                    <i class="fas fa-file-pdf"></i> Export PDF
                                </button>
                                <button id="exportDailyExcel" class="btn btn-success btn-sm">
                                    <i class="fas fa-file-excel"></i> Export Excel
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="dailyTable" class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Hari</th>
                                            <th>Transaksi Harian</th>
                                            <th>Pendapatan Harian</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($daily_results as $row): ?>
                                            <tr>
                                                <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                                <td><?= date('l', strtotime($row['tanggal'])) ?></td>
                                                <td>
                                                    <span class="badge bg-primary fs-6">
                                                        <?= $row['transaksi_harian'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong class="text-success">
                                                        Rp <?= number_format($row['pendapatan_harian']) ?>
                                                    </strong>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status = 'Normal';
                                                    $badge_class = 'bg-success';
                                                    if ($row['transaksi_harian'] >= 5) {
                                                        $status = 'Sibuk';
                                                        $badge_class = 'bg-warning';
                                                    }
                                                    if ($row['transaksi_harian'] >= 10) {
                                                        $status = 'Sangat Sibuk';
                                                        $badge_class = 'bg-danger';
                                                    }
                                                    if ($row['transaksi_harian'] == 0) {
                                                        $status = 'Libur';
                                                        $badge_class = 'bg-secondary';
                                                    }
                                                    ?>
                                                    <span class="badge <?= $badge_class ?>">
                                                        <?= $status ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Charts Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line"></i> Trend Pendapatan Bulanan
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="pendapatanChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar"></i> Transaksi vs Pelanggan
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="transaksiChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Chart -->
        <?php if (!empty($daily_results)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-area"></i> Aktivitas Harian - <?= $months[$filter_month] ?> <?= $filter_year ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="dailyChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Back Button -->
        <div class="row mt-4 mb-4">
            <div class="col-12 text-center">
                <a href="index.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Initialize DataTables
        $(document).ready(function() {
            var monthlyTable = $('#monthlyTable').DataTable({
                "pageLength": 12,
                "order": [
                    [0, "desc"]
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                },
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Laporan Bulanan <?= $filter_year ?>'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        title: 'Laporan Bulanan <?= $filter_year ?>'
                    }
                ]
            });

            // Custom export buttons for monthly
            $('#exportMonthlyExcel').click(function() {
                monthlyTable.button('.buttons-excel').trigger();
            });

            $('#exportMonthlyPDF').click(function() {
                monthlyTable.button('.buttons-pdf').trigger();
            });

            <?php if (!empty($daily_results)): ?>
                var dailyTable = $('#dailyTable').DataTable({
                    "pageLength": 31,
                    "order": [
                        [0, "asc"]
                    ],
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                    },
                    dom: 'Bfrtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '<i class="fas fa-file-excel"></i> Excel',
                            className: 'btn btn-success btn-sm',
                            title: 'Detail Harian <?= $months[$filter_month] ?> <?= $filter_year ?>'
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fas fa-file-pdf"></i> PDF',
                            className: 'btn btn-danger btn-sm',
                            title: 'Detail Harian <?= $months[$filter_month] ?> <?= $filter_year ?>'
                        }
                    ]
                });

                // Custom export buttons for daily
                $('#exportDailyExcel').click(function() {
                    dailyTable.button('.buttons-excel').trigger();
                });

                $('#exportDailyPDF').click(function() {
                    dailyTable.button('.buttons-pdf').trigger();
                });
            <?php endif; ?>
        });

        // Line Chart - Trend Pendapatan Bulanan
        const lineCtx = document.getElementById('pendapatanChart').getContext('2d');
        const monthLabels = [
            <?php
            echo implode(',', array_map(function ($item) {
                return '"' . $item['nama_bulan'] . '"';
            }, array_reverse($monthly_results)));
            ?>
        ];
        const pendapatanData = [
            <?php
            echo implode(',', array_map(function ($item) {
                return $item['total_pendapatan'];
            }, array_reverse($monthly_results)));
            ?>
        ];

        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: pendapatanData,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Trend Pendapatan Bulanan <?= $filter_year ?>'
                    }
                }
            }
        });

        // Bar Chart - Transaksi vs Pelanggan
        const barCtx = document.getElementById('transaksiChart').getContext('2d');
        const transaksiData = [
            <?php
            echo implode(',', array_map(function ($item) {
                return $item['total_transaksi'];
            }, array_reverse($monthly_results)));
            ?>
        ];
        const pelangganData = [
            <?php
            echo implode(',', array_map(function ($item) {
                return $item['pelanggan_unik'];
            }, array_reverse($monthly_results)));
            ?>
        ];

        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Total Transaksi',
                    data: transaksiData,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: 'Pelanggan Unik',
                    data: pelangganData,
                    backgroundColor: 'rgba(255, 99, 132, 0.8)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Perbandingan Transaksi dan Pelanggan <?= $filter_year ?>'
                    }
                }
            }
        });

        <?php if (!empty($daily_results)): ?>
            // Area Chart - Daily Activity
            const areaCtx = document.getElementById('dailyChart').getContext('2d');
            const dailyLabels = [
                <?php
                echo implode(',', array_map(function ($item) {
                    return '"' . date('d/m', strtotime($item['tanggal'])) . '"';
                }, $daily_results));
                ?>
            ];
            const dailyTransaksi = [
                <?php
                echo implode(',', array_map(function ($item) {
                    return $item['transaksi_harian'];
                }, $daily_results));
                ?>
            ];
            const dailyPendapatan = [
                <?php
                echo implode(',', array_map(function ($item) {
                    return $item['pendapatan_harian'];
                }, $daily_results));
                ?>
            ];

            new Chart(areaCtx, {
                type: 'line',
                data: {
                    labels: dailyLabels,
                    datasets: [{
                        label: 'Transaksi Harian',
                        data: dailyTransaksi,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        fill: true,
                        yAxisID: 'y'
                    }, {
                        label: 'Pendapatan Harian (Rp)',
                        data: dailyPendapatan,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        fill: true,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Tanggal'
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Jumlah Transaksi'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Pendapatan (Rp)'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Aktivitas Harian <?= $months[$filter_month] ?> <?= $filter_year ?>'
                        }
                    }
                }
            });
        <?php endif; ?>
    </script>
</body>

</html>