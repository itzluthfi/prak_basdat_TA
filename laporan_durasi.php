<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Query untuk durasi sewa rata-rata per kategori barang
$query = "SELECT 
    kb.nama_kategori,
    COUNT(DISTINCT t.id_transaksi) as jumlah_transaksi,
    AVG(DATEDIFF(t.tanggal_kembali, t.tanggal_pinjam)) as rata_rata_durasi_hari,
    MIN(DATEDIFF(t.tanggal_kembali, t.tanggal_pinjam)) as durasi_minimum,
    MAX(DATEDIFF(t.tanggal_kembali, t.tanggal_pinjam)) as durasi_maksimum,
    SUM(dt.jumlah * dt.harga_satuan) as total_pendapatan,
    COUNT(dt.id_detail) as total_item_disewa
FROM kategori_barang kb
JOIN barang b ON kb.id_kategori = b.id_kategori
JOIN detail_transaksi dt ON b.id_barang = dt.id_barang
JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
WHERE t.tanggal_kembali IS NOT NULL
GROUP BY kb.id_kategori, kb.nama_kategori
ORDER BY rata_rata_durasi_hari DESC";

$stmt = $db->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk statistik umum durasi
$stats_query = "SELECT 
    AVG(DATEDIFF(t.tanggal_kembali, t.tanggal_pinjam)) as rata_rata_durasi_keseluruhan,
    MAX(DATEDIFF(t.tanggal_kembali, t.tanggal_pinjam)) as durasi_terpanjang,
    MIN(DATEDIFF(t.tanggal_kembali, t.tanggal_pinjam)) as durasi_terpendek,
    COUNT(DISTINCT t.id_transaksi) as total_transaksi_selesai,
    COUNT(DISTINCT kb.id_kategori) as total_kategori
FROM transaksi t
JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
JOIN barang b ON dt.id_barang = b.id_barang
JOIN kategori_barang kb ON b.id_kategori = kb.id_kategori
WHERE t.tanggal_kembali IS NOT NULL";

$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Durasi Sewa Rata-rata per Kategori - Rental Alat Pendakian</title>
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
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-calendar-alt"></i> Durasi Sewa Rata-rata per Kategori
                        </h4>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Laporan ini menampilkan rata-rata lama penyewaan untuk setiap kategori barang yang tersedia.
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
                        <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                        <h4><?= number_format($stats['rata_rata_durasi_keseluruhan'], 1) ?></h4>
                        <p class="mb-0">Rata-rata Hari</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <i class="fas fa-arrow-up fa-2x mb-2"></i>
                        <h4><?= $stats['durasi_terpanjang'] ?></h4>
                        <p class="mb-0">Durasi Terpanjang</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-warning text-white">
                    <div class="card-body">
                        <i class="fas fa-arrow-down fa-2x mb-2"></i>
                        <h4><?= $stats['durasi_terpendek'] ?></h4>
                        <p class="mb-0">Durasi Terpendek</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-danger text-white">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <h4><?= $stats['total_transaksi_selesai'] ?></h4>
                        <p class="mb-0">Transaksi Selesai</p>
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
                            <i class="fas fa-table"></i> Data Durasi Sewa per Kategori
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
                                        <th>No</th>
                                        <th>Kategori Barang</th>
                                        <th>Jumlah Transaksi</th>
                                        <th>Rata-rata Durasi (Hari)</th>
                                        <th>Durasi Min</th>
                                        <th>Durasi Max</th>
                                        <th>Total Pendapatan</th>
                                        <th>Total Item Disewa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1;
                                    foreach ($results as $row): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td>
                                                <strong class="text-primary"><?= htmlspecialchars($row['nama_kategori']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= $row['jumlah_transaksi'] ?> Transaksi</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary fs-6">
                                                    <?= number_format($row['rata_rata_durasi_hari'], 1) ?> hari
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success"><?= $row['durasi_minimum'] ?> hari</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning"><?= $row['durasi_maksimum'] ?> hari</span>
                                            </td>
                                            <td>
                                                <strong class="text-success">
                                                    Rp <?= number_format($row['total_pendapatan']) ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?= $row['total_item_disewa'] ?> Items</span>
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
                            <i class="fas fa-pie-chart"></i> Distribusi Kategori Durasi
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="durasiChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line"></i> Trend Durasi Penyewaan
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="trendChart" width="400" height="300"></canvas>
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
                    [4, "desc"]
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                },
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Laporan Durasi Penyewaan'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        title: 'Laporan Durasi Penyewaan'
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

        // Pie Chart - Kategori Durasi
        const pieCtx = document.getElementById('durasiChart').getContext('2d');
        const kategoriData = {};
        <?php foreach ($results as $row): ?>
            if (!kategoriData['<?= $row['kategori_durasi'] ?>']) {
                kategoriData['<?= $row['kategori_durasi'] ?>'] = 0;
            }
            kategoriData['<?= $row['kategori_durasi'] ?>']++;
        <?php endforeach; ?>

        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: Object.keys(kategoriData),
                datasets: [{
                    data: Object.values(kategoriData),
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribusi Kategori Durasi'
                    }
                }
            }
        });

        // Line Chart - Trend Durasi
        const lineCtx = document.getElementById('trendChart').getContext('2d');
        const trendData = [
            <?php
            echo implode(',', array_map(function ($item) {
                return $item['durasi_hari'];
            }, array_slice($results, 0, 15))); // Last 15 transactions
            ?>
        ];

        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: [<?php
                            echo implode(',', array_map(function ($item, $index) {
                                return '"Transaksi ' . ($index + 1) . '"';
                            }, array_slice($results, 0, 15), array_keys(array_slice($results, 0, 15))));
                            ?>],
                datasets: [{
                    label: 'Durasi (Hari)',
                    data: trendData,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
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
                        text: 'Trend Durasi Penyewaan (15 Transaksi Terakhir)'
                    }
                }
            }
        });
    </script>
</body>

</html>