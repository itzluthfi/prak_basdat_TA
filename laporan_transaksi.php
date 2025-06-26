<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Query untuk riwayat transaksi pelanggan
$query = "SELECT 
    p.nama_pelanggan,
    p.no_hp,
    p.email,
    t.id_transaksi,
    t.tanggal_pinjam,
    t.tanggal_kembali,
    t.total_harga,
    t.denda,
    t.status_bayar,
    t.metode_bayar,
    t.rating,
    GROUP_CONCAT(b.nama_barang SEPARATOR ', ') as barang_disewa,
    COUNT(dt.id_detail) as jumlah_item
FROM pelanggan p
JOIN transaksi t ON p.id_pelanggan = t.id_pelanggan
JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
JOIN barang b ON dt.id_barang = b.id_barang
GROUP BY t.id_transaksi, p.id_pelanggan, p.nama_pelanggan, p.no_hp, p.email,
         t.tanggal_pinjam, t.tanggal_kembali, t.total_harga, t.denda, 
         t.status_bayar, t.metode_bayar, t.rating
ORDER BY p.nama_pelanggan, t.tanggal_pinjam DESC";

$stmt = $db->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi Pelanggan - Rental Alat Pendakian</title>
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
                            <i class="fas fa-history"></i> Riwayat Transaksi Pelanggan
                        </h4>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Laporan ini menampilkan semua transaksi penyewaan berdasarkan seluruh nama pelanggan
                            dengan detail lengkap termasuk barang yang disewa dan status pembayaran.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <?php
            $total_transaksi = count($results);
            $total_pendapatan = array_sum(array_column($results, 'total_harga'));
            $total_denda = array_sum(array_column($results, 'denda'));
            $transaksi_lunas = count(array_filter($results, function ($item) {
                return $item['status_bayar'] == 'Lunas';
            }));
            ?>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-primary text-white">
                    <div class="card-body">
                        <i class="fas fa-receipt fa-2x mb-2"></i>
                        <h4><?= number_format($total_transaksi) ?></h4>
                        <p class="mb-0">Total Transaksi</p>
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
                <div class="card text-center bg-warning text-dark">
                    <div class="card-body">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <h4>Rp <?= number_format($total_denda) ?></h4>
                        <p class="mb-0">Total Denda</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-info text-white">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <h4><?= number_format($transaksi_lunas) ?></h4>
                        <p class="mb-0">Transaksi Lunas</p>
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
                            <i class="fas fa-table"></i> Data Riwayat Transaksi
                        </h5>
                        <div class="btn-group">
                            <button class="btn btn-success btn-sm" onclick="exportToExcel()">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="exportToPDF()">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>ID Transaksi</th>
                                        <th>Pelanggan</th>
                                        <th>Kontak</th>
                                        <th>Barang Disewa</th>
                                        <th>Jumlah Item</th>
                                        <th>Total Harga</th>
                                        <th>Denda</th>
                                        <th>Total Bayar</th>
                                        <th>Tanggal</th>
                                        <th>Metode</th>
                                        <th>Status</th>
                                        <th>Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $index => $row): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><strong><?= $row['id_transaksi'] ?></strong></td>
                                            <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                                            <td>
                                                <small>
                                                    <i class="fas fa-phone"></i> <?= htmlspecialchars($row['no_hp']) ?><br>
                                                    <i class="fas fa-envelope"></i> <?= htmlspecialchars($row['email']) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars($row['barang_disewa']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= $row['jumlah_item'] ?> item</span>
                                            </td>
                                            <td>Rp <?= number_format($row['total_harga']) ?></td>
                                            <td>
                                                <?php if ($row['denda'] > 0): ?>
                                                    <span class="text-danger">Rp <?= number_format($row['denda']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-success">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong>Rp <?= number_format($row['total_harga'] + $row['denda']) ?></strong>
                                            </td>
                                            <td>
                                                <small>
                                                    <?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?><br>
                                                    <?= date('d/m/Y', strtotime($row['tanggal_kembali'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $row['metode_bayar'] == 'Transfer' ? 'primary' : 'success' ?>">
                                                    <?= $row['metode_bayar'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $row['status_bayar'] == 'Lunas' ? 'success' : 'danger' ?>">
                                                    <?= $row['status_bayar'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($row['rating']): ?>
                                                    <div class="text-warning">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star<?= $i <= $row['rating'] ? '' : '-o' ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
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
                            <i class="fas fa-chart-pie"></i> Distribusi Status Pembayaran
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
                            <i class="fas fa-chart-bar"></i> Metode Pembayaran
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="paymentChart" width="400" height="400"></canvas>
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
        // Initialize DataTable with export buttons
        $(document).ready(function() {
            $('#dataTable').DataTable({
                "pageLength": 10,
                "order": [
                    [1, "desc"]
                ],
                "dom": 'Bfrtip',
                "buttons": [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm'
                    }
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                }
            });
        });

        // Export functions
        function exportToExcel() {
            $('#dataTable').DataTable().button('.buttons-excel').trigger();
        }

        function exportToPDF() {
            $('#dataTable').DataTable().button('.buttons-pdf').trigger();
        }

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        <?php
        $lunas = count(array_filter($results, function ($item) {
            return $item['status_bayar'] == 'Lunas';
        }));
        $belum_lunas = count(array_filter($results, function ($item) {
            return $item['status_bayar'] == 'Belum Lunas';
        }));
        ?>

        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Lunas', 'Belum Lunas'],
                datasets: [{
                    data: [<?= $lunas ?>, <?= $belum_lunas ?>],
                    backgroundColor: ['rgba(40, 167, 69, 0.8)', 'rgba(220, 53, 69, 0.8)']
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

        // Payment Method Chart
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        <?php
        $transfer = count(array_filter($results, function ($item) {
            return $item['metode_bayar'] == 'Transfer';
        }));
        $cash = count(array_filter($results, function ($item) {
            return $item['metode_bayar'] == 'Cash';
        }));
        ?>

        new Chart(paymentCtx, {
            type: 'bar',
            data: {
                labels: ['Transfer', 'Cash'],
                datasets: [{
                    label: 'Jumlah Transaksi',
                    data: [<?= $transfer ?>, <?= $cash ?>],
                    backgroundColor: ['rgba(0, 123, 255, 0.8)', 'rgba(40, 167, 69, 0.8)']
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