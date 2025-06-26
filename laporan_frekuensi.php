<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Query untuk laporan frekuensi penyewaan
$query = "SELECT 
    b.nama_barang,
    kb.nama_kategori,
    COUNT(dt.id_detail) as frekuensi_sewa,
    SUM(dt.jumlah * dt.harga_satuan) as total_pendapatan
FROM barang b
LEFT JOIN detail_transaksi dt ON b.id_barang = dt.id_barang
LEFT JOIN kategori_barang kb ON b.id_kategori = kb.id_kategori
GROUP BY b.id_barang, b.nama_barang, kb.nama_kategori
ORDER BY frekuensi_sewa DESC";

$stmt = $db->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Frekuensi Penyewaan - Rental Alat Pendakian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
                            <i class="fas fa-chart-line"></i> Laporan Frekuensi Penyewaan Alat
                        </h4>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Laporan ini menampilkan daftar barang paling sering disewa dan berapa kali disewa,
                            beserta total pendapatan dari setiap barang.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <?php
            $total_transaksi = array_sum(array_column($results, 'frekuensi_sewa'));
            $total_pendapatan = array_sum(array_column($results, 'total_pendapatan'));
            $barang_terlaris = $results[0] ?? null;
            $barang_tidak_disewa = count(array_filter($results, function ($item) {
                return $item['frekuensi_sewa'] == 0;
            }));
            ?>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-primary text-white">
                    <div class="card-body">
                        <i class="fas fa-receipt fa-2x mb-2"></i>
                        <h4><?= number_format($total_transaksi) ?></h4>
                        <p class="mb-0">Total Penyewaan</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                        <h4>Rp <?= number_format($total_pendapatan) ?></h4>
                        <p class="mb-0">Total Pendapatan</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-warning text-white">
                    <div class="card-body">
                        <i class="fas fa-star fa-2x mb-2"></i>
                        <h4><?= $barang_terlaris ? $barang_terlaris['frekuensi_sewa'] : 0 ?></h4>
                        <p class="mb-0">Frekuensi Tertinggi</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-danger text-white">
                    <div class="card-body">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <h4><?= $barang_tidak_disewa ?></h4>
                        <p class="mb-0">Barang Tidak Disewa</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-table"></i> Data Frekuensi Penyewaan
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Barang</th>
                                        <th>Kategori</th>
                                        <th>Frekuensi Sewa</th>
                                        <th>Total Pendapatan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $index => $row): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($row['nama_barang']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?= htmlspecialchars($row['nama_kategori']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $row['frekuensi_sewa'] > 0 ? 'success' : 'danger' ?> fs-6">
                                                    <?= number_format($row['frekuensi_sewa']) ?>x
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-success">
                                                    Rp <?= number_format($row['total_pendapatan'] ?: 0) ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <?php if ($row['frekuensi_sewa'] > 2): ?>
                                                    <span class="badge bg-success">Populer</span>
                                                <?php elseif ($row['frekuensi_sewa'] > 0): ?>
                                                    <span class="badge bg-warning">Normal</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Tidak Diminati</span>
                                                <?php endif; ?>
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

        <!-- Chart Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar"></i> Grafik Frekuensi Penyewaan
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="frequencyChart" width="400" height="200"></canvas>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#dataTable').DataTable({
                "pageLength": 10,
                "order": [
                    [3, "desc"]
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                }
            });
        });

        // Chart
        const ctx = document.getElementById('frequencyChart').getContext('2d');
        const chartData = {
            labels: [<?php
                        echo implode(',', array_map(function ($item) {
                            return '"' . addslashes($item['nama_barang']) . '"';
                        }, array_slice($results, 0, 10))); // Top 10
                        ?>],
            datasets: [{
                label: 'Frekuensi Sewa',
                data: [<?php
                        echo implode(',', array_map(function ($item) {
                            return $item['frekuensi_sewa'];
                        }, array_slice($results, 0, 10)));
                        ?>],
                backgroundColor: 'rgba(40, 167, 69, 0.8)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1
            }]
        };

        new Chart(ctx, {
            type: 'bar',
            data: chartData,
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
                        text: 'Top 10 Barang Paling Sering Disewa'
                    }
                }
            }
        });
    </script>
</body>

</html>