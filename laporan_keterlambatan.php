<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Query untuk statistik keterlambatan pelanggan
$query = "SELECT 
    p.nama_pelanggan,
    p.no_hp,
    p.email,
    COUNT(CASE WHEN t.denda > 0 THEN 1 END) as jumlah_keterlambatan,
    COALESCE(SUM(t.denda), 0) as total_denda,
    COUNT(t.id_transaksi) as total_transaksi,
    ROUND((COUNT(CASE WHEN t.denda > 0 THEN 1 END) * 100.0 / COUNT(t.id_transaksi)), 2) as persentase_telat
FROM pelanggan p
LEFT JOIN transaksi t ON p.id_pelanggan = t.id_pelanggan
GROUP BY p.id_pelanggan, p.nama_pelanggan, p.no_hp, p.email
ORDER BY total_denda DESC";

$stmt = $db->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Keterlambatan Pelanggan - Rental Alat Pendakian</title>
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
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">
                            <i class="fas fa-clock"></i> Statistik Keterlambatan Pelanggan
                        </h4>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Laporan ini menampilkan statistik keterlambatan pelanggan dalam mengembalikan barang
                            dan total denda yang harus dibayar.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <?php
            $total_pelanggan_telat = count(array_filter($results, function ($item) {
                return $item['jumlah_keterlambatan'] > 0;
            }));
            $total_denda_keseluruhan = array_sum(array_column($results, 'total_denda'));
            $total_keterlambatan = array_sum(array_column($results, 'jumlah_keterlambatan'));
            $pelanggan_terburuk = $results[0] ?? null;
            ?>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-danger text-white">
                    <div class="card-body">
                        <i class="fas fa-user-clock fa-2x mb-2"></i>
                        <h4><?= number_format($total_pelanggan_telat) ?></h4>
                        <p class="mb-0">Pelanggan Telat</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-warning text-dark">
                    <div class="card-body">
                        <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                        <h4>Rp <?= number_format($total_denda_keseluruhan) ?></h4>
                        <p class="mb-0">Total Denda</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-secondary text-white">
                    <div class="card-body">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <h4><?= number_format($total_keterlambatan) ?></h4>
                        <p class="mb-0">Total Keterlambatan</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-info text-white">
                    <div class="card-body">
                        <i class="fas fa-percentage fa-2x mb-2"></i>
                        <h4><?= $pelanggan_terburuk ? number_format($pelanggan_terburuk['persentase_telat'], 1) : 0 ?>%</h4>
                        <p class="mb-0">Persentase Tertinggi</p>
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
                            <i class="fas fa-table"></i> Data Keterlambatan Pelanggan
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Pelanggan</th>
                                        <th>Kontak</th>
                                        <th>Total Transaksi</th>
                                        <th>Jumlah Keterlambatan</th>
                                        <th>Total Denda</th>
                                        <th>Persentase Telat</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $index => $row): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($row['nama_pelanggan']) ?></strong>
                                            </td>
                                            <td>
                                                <small>
                                                    <i class="fas fa-phone"></i> <?= htmlspecialchars($row['no_hp']) ?><br>
                                                    <i class="fas fa-envelope"></i> <?= htmlspecialchars($row['email']) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?= number_format($row['total_transaksi']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $row['jumlah_keterlambatan'] > 0 ? 'danger' : 'success' ?> fs-6">
                                                    <?= number_format($row['jumlah_keterlambatan']) ?>x
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="<?= $row['total_denda'] > 0 ? 'text-danger' : 'text-success' ?>">
                                                    Rp <?= number_format($row['total_denda']) ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-<?= $row['persentase_telat'] > 50 ? 'danger' : ($row['persentase_telat'] > 25 ? 'warning' : 'success') ?>"
                                                        style="width: <?= $row['persentase_telat'] ?>%">
                                                        <?= number_format($row['persentase_telat'], 1) ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($row['persentase_telat'] == 0): ?>
                                                    <span class="badge bg-success">Excellent</span>
                                                <?php elseif ($row['persentase_telat'] <= 25): ?>
                                                    <span class="badge bg-primary">Good</span>
                                                <?php elseif ($row['persentase_telat'] <= 50): ?>
                                                    <span class="badge bg-warning">Warning</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Critical</span>
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

        <!-- Charts Section -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie"></i> Distribusi Status Pelanggan
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" width="400" height="400"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar"></i> Top 10 Pelanggan Dengan Denda Tertinggi
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="dendaChart" width="400" height="400"></canvas>
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
                    [5, "desc"]
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                }
            });
        });

        // Status Distribution Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        <?php
        $excellent = count(array_filter($results, function ($item) {
            return $item['persentase_telat'] == 0;
        }));
        $good = count(array_filter($results, function ($item) {
            return $item['persentase_telat'] > 0 && $item['persentase_telat'] <= 25;
        }));
        $warning = count(array_filter($results, function ($item) {
            return $item['persentase_telat'] > 25 && $item['persentase_telat'] <= 50;
        }));
        $critical = count(array_filter($results, function ($item) {
            return $item['persentase_telat'] > 50;
        }));
        ?>

        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Excellent', 'Good', 'Warning', 'Critical'],
                datasets: [{
                    data: [<?= $excellent ?>, <?= $good ?>, <?= $warning ?>, <?= $critical ?>],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(0, 123, 255, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Top Denda Chart
        const dendaCtx = document.getElementById('dendaChart').getContext('2d');
        new Chart(dendaCtx, {
            type: 'bar',
            data: {
                labels: [<?php
                            $top10 = array_slice(array_filter($results, function ($item) {
                                return $item['total_denda'] > 0;
                            }), 0, 10);
                            echo implode(',', array_map(function ($item) {
                                return '"' . substr(addslashes($item['nama_pelanggan']), 0, 15) . '"';
                            }, $top10));
                            ?>],
                datasets: [{
                    label: 'Total Denda (Rp)',
                    data: [<?php
                            echo implode(',', array_map(function ($item) {
                                return $item['total_denda'];
                            }, $top10));
                            ?>],
                    backgroundColor: 'rgba(220, 53, 69, 0.8)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>