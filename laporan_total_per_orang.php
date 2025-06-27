<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Query untuk total transaksi per orang (Harga sewa + Denda)
$query = "SELECT 
    p.nama_pelanggan,
    p.no_hp,
    p.email,
    p.alamat_lengkap,
    COUNT(t.id_transaksi) as jumlah_transaksi,
    SUM(t.total_harga) as total_harga_sewa,
    SUM(t.denda) as total_denda,
    SUM(t.total_harga + t.denda) as total_pembayaran,
    AVG(t.rating) as rata_rata_rating,
    COUNT(CASE WHEN t.status_bayar = 'Lunas' THEN 1 END) as transaksi_lunas,
    COUNT(CASE WHEN t.status_bayar = 'Belum Lunas' THEN 1 END) as transaksi_belum_lunas
FROM pelanggan p
LEFT JOIN transaksi t ON p.id_pelanggan = t.id_pelanggan
GROUP BY p.id_pelanggan, p.nama_pelanggan, p.no_hp, p.email, p.alamat_lengkap
HAVING jumlah_transaksi > 0
ORDER BY total_pembayaran DESC";

$stmt = $db->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Total Transaksi per Orang - Rental Alat Pendakian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .table-responsive {
            border-radius: 10px;
        }

        .badge-custom {
            font-size: 0.8rem;
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
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-calculator"></i> Total Transaksi per Orang
                        </h4>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Laporan ini menampilkan total transaksi per pelanggan dengan perhitungan:
                            <strong>Harga Sewa + Denda</strong> untuk setiap pelanggan.
                        </p>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    Data diurutkan berdasarkan total pembayaran tertinggi
                                </small>
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i>
                                    Generated: <?= date('d/m/Y H:i:s') ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <?php
            $total_customers = count($results);
            $total_pembayaran_all = array_sum(array_column($results, 'total_pembayaran'));
            $total_denda_all = array_sum(array_column($results, 'total_denda'));
            $avg_rating = array_sum(array_column($results, 'rata_rata_rating')) / count(array_filter(array_column($results, 'rata_rata_rating')));
            ?>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-primary text-white">
                    <div class="card-body">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h4><?= number_format($total_customers) ?></h4>
                        <p class="mb-0">Total Pelanggan</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                        <h4>Rp <?= number_format($total_pembayaran_all) ?></h4>
                        <p class="mb-0">Total Pembayaran</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-danger text-white">
                    <div class="card-body">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <h4>Rp <?= number_format($total_denda_all) ?></h4>
                        <p class="mb-0">Total Denda</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-warning text-white">
                    <div class="card-body">
                        <i class="fas fa-star fa-2x mb-2"></i>
                        <h4><?= number_format($avg_rating, 1) ?></h4>
                        <p class="mb-0">Rata-rata Rating</p>
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
                            <i class="fas fa-table"></i> Data Total Transaksi per Pelanggan
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
                                        <th>Alamat</th>
                                        <th>Jumlah Transaksi</th>
                                        <th>Total Harga Sewa</th>
                                        <th>Total Denda</th>
                                        <th>Total Pembayaran</th>
                                        <th>Status Bayar</th>
                                        <th>Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1;
                                    foreach ($results as $row): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
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
                                                <small><?= htmlspecialchars($row['alamat_lengkap']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info badge-custom"><?= $row['jumlah_transaksi'] ?> Transaksi</span>
                                            </td>
                                            <td>
                                                <strong>Rp <?= number_format($row['total_harga_sewa']) ?></strong>
                                            </td>
                                            <td>
                                                <?php if ($row['total_denda'] > 0): ?>
                                                    <span class="text-danger fw-bold">Rp <?= number_format($row['total_denda']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-success">Rp 0</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong class="text-primary">Rp <?= number_format($row['total_pembayaran']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-success badge-custom"><?= $row['transaksi_lunas'] ?> Lunas</span><br>
                                                <span class="badge bg-danger badge-custom"><?= $row['transaksi_belum_lunas'] ?> Belum Lunas</span>
                                            </td>
                                            <td>
                                                <?php if ($row['rata_rata_rating']): ?>
                                                    <div class="text-warning">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star<?= $i <= round($row['rata_rata_rating']) ? '' : '-o' ?>"></i>
                                                        <?php endfor; ?>
                                                        <br><small>(<?= number_format($row['rata_rata_rating'], 1) ?>/5)</small>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">Belum ada rating</span>
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

        <!-- SQL Query Information -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-code"></i> SQL Query
                        </h5>
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-3"><code>SELECT 
    p.nama_pelanggan,
    p.no_hp,
    p.email,
    p.alamat_lengkap,
    COUNT(t.id_transaksi) as jumlah_transaksi,
    SUM(t.total_harga) as total_harga_sewa,
    SUM(t.denda) as total_denda,
    SUM(t.total_harga + t.denda) as total_pembayaran,
    AVG(t.rating) as rata_rata_rating,
    COUNT(CASE WHEN t.status_bayar = 'Lunas' THEN 1 END) as transaksi_lunas,
    COUNT(CASE WHEN t.status_bayar = 'Belum Lunas' THEN 1 END) as transaksi_belum_lunas
FROM pelanggan p
LEFT JOIN transaksi t ON p.id_pelanggan = t.id_pelanggan
GROUP BY p.id_pelanggan, p.nama_pelanggan, p.no_hp, p.email, p.alamat_lengkap
HAVING jumlah_transaksi > 0
ORDER BY total_pembayaran DESC;</code></pre>
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

    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                "pageLength": 15,
                "order": [
                    [7, "desc"]
                ], // Sort by Total Pembayaran
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                }
            });
        });
    </script>
</body>

</html>