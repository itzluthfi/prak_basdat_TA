<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Query untuk view detail transaksi lengkap
$query = "SELECT * FROM v_detail_transaksi_lengkap ORDER BY id_transaksi DESC";

$stmt = $db->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi Lengkap - Rental Alat Pendakian</title>
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

    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-database"></i> VIEW: Detail Transaksi Lengkap
                        </h4>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            <strong>Database View:</strong> <code>v_detail_transaksi_lengkap</code><br>
                            View ini menggabungkan 5 tabel: TRANSAKSI, PELANGGAN, KARYAWAN, DETAIL_TRANSAKSI, dan BARANG.
                            Menampilkan informasi lengkap setiap transaksi dengan detail pelanggan, karyawan, dan barang.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <?php
            $total_records = count($results);
            $total_subtotal = array_sum(array_column($results, 'subtotal'));
            $unique_customers = count(array_unique(array_column($results, 'nama_pelanggan')));
            $unique_items = count(array_unique(array_column($results, 'nama_barang')));
            ?>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-primary text-white">
                    <div class="card-body">
                        <i class="fas fa-receipt fa-2x mb-2"></i>
                        <h4><?= number_format($total_records) ?></h4>
                        <p class="mb-0">Total Records</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                        <h4>Rp <?= number_format($total_subtotal) ?></h4>
                        <p class="mb-0">Total Subtotal</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-warning text-white">
                    <div class="card-body">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h4><?= number_format($unique_customers) ?></h4>
                        <p class="mb-0">Unique Customers</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-info text-white">
                    <div class="card-body">
                        <i class="fas fa-box fa-2x mb-2"></i>
                        <h4><?= number_format($unique_items) ?></h4>
                        <p class="mb-0">Unique Items</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Filter Pelanggan:</label>
                <select class="form-select" id="filterPelanggan">
                    <option value="">Semua Pelanggan</option>
                    <?php
                    $customers = array_unique(array_column($results, 'nama_pelanggan'));
                    sort($customers);
                    foreach ($customers as $customer): ?>
                        <option value="<?= htmlspecialchars($customer) ?>"><?= htmlspecialchars($customer) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Filter Karyawan:</label>
                <select class="form-select" id="filterKaryawan">
                    <option value="">Semua Karyawan</option>
                    <?php
                    $employees = array_unique(array_column($results, 'nama_karyawan'));
                    sort($employees);
                    foreach ($employees as $employee): ?>
                        <option value="<?= htmlspecialchars($employee) ?>"><?= htmlspecialchars($employee) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Filter Status:</label>
                <select class="form-select" id="filterStatus">
                    <option value="">Semua Status</option>
                    <option value="Lunas">Lunas</option>
                    <option value="Belum Lunas">Belum Lunas</option>
                </select>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-table"></i> Data Detail Transaksi
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-striped table-hover table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Pelanggan</th>
                                        <th>Kontak</th>
                                        <th>Karyawan</th>
                                        <th>Shift</th>
                                        <th>Barang</th>
                                        <th>Qty</th>
                                        <th>Harga</th>
                                        <th>Subtotal</th>
                                        <th>Kondisi</th>
                                        <th>Tanggal</th>
                                        <th>Metode</th>
                                        <th>Status</th>
                                        <th>Denda</th>
                                        <th>Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $row): ?>
                                        <tr>
                                            <td><strong><?= $row['id_transaksi'] ?></strong></td>
                                            <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                                            <td>
                                                <small>
                                                    <?= htmlspecialchars($row['no_hp']) ?><br>
                                                    <?= htmlspecialchars($row['email']) ?>
                                                </small>
                                            </td>
                                            <td><?= htmlspecialchars($row['nama_karyawan']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $row['shift_karyawan'] == 'Pagi' ? 'warning' : 'info' ?>">
                                                    <?= $row['shift_karyawan'] ?>
                                                </span>
                                            </td>
                                            <td><strong><?= htmlspecialchars($row['nama_barang']) ?></strong></td>
                                            <td><?= $row['jumlah'] ?></td>
                                            <td>Rp <?= number_format($row['harga_satuan']) ?></td>
                                            <td><strong>Rp <?= number_format($row['subtotal']) ?></strong></td>
                                            <td>
                                                <small>
                                                    <span class="badge bg-<?= $row['kondisi_awal'] == 'Baik' ? 'success' : 'danger' ?>">
                                                        <?= $row['kondisi_awal'] ?>
                                                    </span>
                                                    →
                                                    <span class="badge bg-<?= $row['kondisi_kembali'] == 'Baik' ? 'success' : 'danger' ?>">
                                                        <?= $row['kondisi_kembali'] ?: 'N/A' ?>
                                                    </span>
                                                </small>
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
                                                <?php if ($row['denda'] > 0): ?>
                                                    <span class="text-danger">Rp <?= number_format($row['denda']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-success">-</span>
                                                <?php endif; ?>
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

        <!-- SQL Query Info -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-code"></i> SQL Query View
                        </h5>
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-3"><code>CREATE VIEW v_detail_transaksi_lengkap AS
SELECT
    t.id_transaksi,
    p.nama_pelanggan,
    p.no_hp,
    p.email,
    k.nama_karyawan,
    k.shift_karyawan,
    b.nama_barang,
    dt.jumlah,
    dt.harga_satuan,
    dt.kondisi_awal,
    dt.kondisi_kembali,
    t.tanggal_pinjam,
    t.tanggal_kembali,
    t.metode_bayar,
    t.status_bayar,
    t.denda,
    t.ulasan_pelanggan,
    t.rating,
    (dt.jumlah * dt.harga_satuan) AS subtotal
FROM
    transaksi t
    JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
    JOIN karyawan k ON t.id_karyawan = k.id_karyawan
    JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
    JOIN barang b ON dt.id_barang = b.id_barang
ORDER BY t.id_transaksi, b.nama_barang;</code></pre>
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
            // Initialize DataTable
            var table = $('#dataTable').DataTable({
                "pageLength": 15,
                "order": [
                    [0, "desc"]
                ],
                "scrollX": true,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                }
            });

            // Custom filters
            $('#filterPelanggan').on('change', function() {
                table.column(1).search(this.value).draw();
            });

            $('#filterKaryawan').on('change', function() {
                table.column(3).search(this.value).draw();
            });

            $('#filterStatus').on('change', function() {
                table.column(12).search(this.value).draw();
            });
        });
    </script>
</body>

</html>