<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Query untuk laporan kinerja karyawan
$query = "SELECT 
    k.id_karyawan,
    k.nama_karyawan,
    k.posisi,
    k.shift_karyawan,
    COUNT(t.id_transaksi) as total_transaksi,
    SUM(CASE WHEN t.status_bayar = 'Lunas' THEN 1 ELSE 0 END) as transaksi_lunas,
    SUM(CASE WHEN t.status_bayar = 'Belum Lunas' THEN 1 ELSE 0 END) as transaksi_belum_lunas,
    SUM(dt.jumlah * dt.harga_satuan) as total_pendapatan,
    AVG(DATEDIFF(t.tanggal_kembali, t.tanggal_pinjam)) as rata_rata_durasi,
    (SUM(CASE WHEN t.status_bayar = 'Lunas' THEN 1 ELSE 0 END) / NULLIF(COUNT(t.id_transaksi), 0) * 100) as persentase_lunas,
    AVG(t.rating) as rata_rata_rating
FROM karyawan k
LEFT JOIN transaksi t ON k.id_karyawan = t.id_karyawan
LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
WHERE k.status = 'Aktif'
GROUP BY k.id_karyawan, k.nama_karyawan, k.posisi, k.shift_karyawan
ORDER BY total_pendapatan DESC";

$stmt = $db->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk statistik umum
$stats_query = "SELECT 
    COUNT(DISTINCT k.id_karyawan) as total_karyawan,
    SUM(dt.jumlah * dt.harga_satuan) as total_pendapatan_semua,
    AVG(monthly_stats.transaksi_per_bulan) as rata_transaksi_per_bulan
FROM karyawan k
LEFT JOIN transaksi t ON k.id_karyawan = t.id_karyawan
LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
LEFT JOIN (
    SELECT 
        id_karyawan,
        COUNT(*) as transaksi_per_bulan
    FROM transaksi 
    WHERE MONTH(tanggal_pinjam) = MONTH(CURDATE())
    AND YEAR(tanggal_pinjam) = YEAR(CURDATE())
    GROUP BY id_karyawan
) monthly_stats ON k.id_karyawan = monthly_stats.id_karyawan";

$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Query untuk top performer
$top_query = "SELECT 
    k.nama_karyawan,
    SUM(dt.jumlah * dt.harga_satuan) as pendapatan
FROM karyawan k
JOIN transaksi t ON k.id_karyawan = t.id_karyawan
JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
GROUP BY k.id_karyawan, k.nama_karyawan
ORDER BY pendapatan DESC
LIMIT 1";

$top_stmt = $db->prepare($top_query);
$top_stmt->execute();
$top_performer = $top_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kinerja Karyawan - Rental Alat Pendakian</title>
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
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-users"></i> Laporan Kinerja Karyawan
                        </h4>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Laporan evaluasi kinerja karyawan berdasarkan jumlah transaksi, pendapatan, dan tingkat penyelesaian tugas.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card text-center bg-primary text-white">
                    <div class="card-body">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h4><?= $stats['total_karyawan'] ?></h4>
                        <p class="mb-0">Total Karyawan</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                        <h4>Rp <?= number_format($stats['total_pendapatan_semua']) ?></h4>
                        <p class="mb-0">Total Pendapatan</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-warning text-white">
                    <div class="card-body">
                        <i class="fas fa-chart-line fa-2x mb-2"></i>
                        <h4><?= number_format($stats['rata_transaksi_per_bulan'] ?? 0, 1) ?></h4>
                        <p class="mb-0">Avg Transaksi/Bulan</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-info text-white">
                    <div class="card-body">
                        <i class="fas fa-trophy fa-2x mb-2"></i>
                        <h5 class="text-truncate"><?= $top_performer['nama_karyawan'] ?? 'N/A' ?></h5>
                        <p class="mb-0">Top Performer</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-table"></i> Data Kinerja Karyawan
                        </h5>
                        <div>
                            <button id="exportPDF" class="btn btn-danger btn-sm me-2">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                            <button id="exportExcel" class="btn btn-success btn-sm">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Karyawan</th>
                                        <th>Posisi</th>
                                        <th>Total Transaksi</th>
                                        <th>Selesai</th>
                                        <th>Aktif</th>
                                        <th>Total Pendapatan</th>
                                        <th>Rata-rata Durasi</th>
                                        <th>% Selesai</th>
                                        <th>Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['id_karyawan']) ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($row['nama_karyawan']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?= htmlspecialchars($row['posisi']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary fs-6">
                                                    <?= $row['total_transaksi'] ?? 0 ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success fs-6">
                                                    <?= $row['transaksi_selesai'] ?? 0 ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning fs-6">
                                                    <?= $row['transaksi_aktif'] ?? 0 ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-success">
                                                    Rp <?= number_format($row['total_pendapatan'] ?? 0) ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <?= number_format($row['rata_rata_durasi'] ?? 0, 1) ?> hari
                                            </td>
                                            <td>
                                                <?php
                                                $persentase = $row['persentase_selesai'] ?? 0;
                                                $badge_class = 'bg-danger';
                                                if ($persentase >= 90) $badge_class = 'bg-success';
                                                elseif ($persentase >= 75) $badge_class = 'bg-warning';
                                                elseif ($persentase >= 50) $badge_class = 'bg-info';
                                                ?>
                                                <span class="badge <?= $badge_class ?>">
                                                    <?= number_format($persentase, 1) ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $rating = 1;
                                                if ($persentase >= 90 && ($row['total_pendapatan'] ?? 0) > 1000000) $rating = 5;
                                                elseif ($persentase >= 80 && ($row['total_pendapatan'] ?? 0) > 500000) $rating = 4;
                                                elseif ($persentase >= 70 && ($row['total_pendapatan'] ?? 0) > 250000) $rating = 3;
                                                elseif ($persentase >= 50) $rating = 2;

                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $rating) {
                                                        echo '<i class="fas fa-star text-warning"></i>';
                                                    } else {
                                                        echo '<i class="far fa-star text-muted"></i>';
                                                    }
                                                }
                                                ?>
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

        <!-- Charts Section -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar"></i> Pendapatan per Karyawan
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
                            <i class="fas fa-pie-chart"></i> Distribusi Posisi
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="posisiChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Analysis -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line"></i> Analisis Kinerja
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="kinerjaTrendChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

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
        // Initialize DataTable
        $(document).ready(function() {
            var table = $('#dataTable').DataTable({
                "pageLength": 10,
                "order": [
                    [6, "desc"]
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                },
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Laporan Kinerja Karyawan'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        title: 'Laporan Kinerja Karyawan',
                        orientation: 'landscape'
                    }
                ]
            });

            // Custom export buttons
            $('#exportExcel').click(function() {
                table.button('.buttons-excel').trigger();
            });

            $('#exportPDF').click(function() {
                table.button('.buttons-pdf').trigger();
            });
        });

        // Bar Chart - Pendapatan per Karyawan
        const barCtx = document.getElementById('pendapatanChart').getContext('2d');
        const karyawanNames = [
            <?php
            echo implode(',', array_map(function ($item) {
                return '"' . htmlspecialchars($item['nama_karyawan']) . '"';
            }, array_slice($results, 0, 8)));
            ?>
        ];
        const pendapatanData = [
            <?php
            echo implode(',', array_map(function ($item) {
                return $item['total_pendapatan'] ?? 0;
            }, array_slice($results, 0, 8)));
            ?>
        ];

        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: karyawanNames,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: pendapatanData,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
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
                        text: 'Pendapatan per Karyawan'
                    }
                }
            }
        });

        // Pie Chart - Distribusi Posisi
        const pieCtx = document.getElementById('posisiChart').getContext('2d');
        const posisiData = {};
        <?php foreach ($results as $row): ?>
            if (!posisiData['<?= $row['posisi'] ?>']) {
                posisiData['<?= $row['posisi'] ?>'] = 0;
            }
            posisiData['<?= $row['posisi'] ?>']++;
        <?php endforeach; ?>

        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: Object.keys(posisiData),
                datasets: [{
                    data: Object.values(posisiData),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 205, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribusi Posisi Karyawan'
                    }
                }
            }
        });

        // Line Chart - Trend Kinerja
        const lineCtx = document.getElementById('kinerjaTrendChart').getContext('2d');
        const persentaseData = [
            <?php
            echo implode(',', array_map(function ($item) {
                return $item['persentase_selesai'] ?? 0;
            }, $results));
            ?>
        ];

        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: karyawanNames.concat([
                    <?php
                    if (count($results) > 8) {
                        echo implode(',', array_map(function ($item) {
                            return '"' . htmlspecialchars($item['nama_karyawan']) . '"';
                        }, array_slice($results, 8)));
                    }
                    ?>
                ]),
                datasets: [{
                    label: 'Persentase Selesai (%)',
                    data: persentaseData,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Trend Persentase Penyelesaian Tugas'
                    }
                }
            }
        });
    </script>
</body>

</html>