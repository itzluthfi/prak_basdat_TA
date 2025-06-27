<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Query untuk view detail transaksi lengkap
$query = "SELECT * FROM v_detail_transaksi_lengkap ORDER BY ID DESC";

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
                            View ini menggabungkan 6 tabel: TRANSAKSI, PELANGGAN, KARYAWAN, DETAIL_TRANSAKSI, BARANG, dan KATEGORI_BARANG.
                            Menampilkan satu baris per transaksi dengan data multi-barang yang digabung menggunakan GROUP_CONCAT.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <?php
            $total_records = count($results);
            $unique_customers = count(array_unique(array_column($results, 'Nama_Pelanggan')));
            $unique_transactions = $total_records; // Karena sudah satu baris per transaksi

            // Hitung total barang dari kolom Nama_Barang yang di-GROUP_CONCAT
            $total_items = 0;
            foreach ($results as $row) {
                if (!empty($row['Nama_Barang'])) {
                    $items = explode(', ', $row['Nama_Barang']);
                    $total_items += count($items);
                }
            }
            ?>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-primary text-white">
                    <div class="card-body">
                        <i class="fas fa-receipt fa-2x mb-2"></i>
                        <h4><?= number_format($total_records) ?></h4>
                        <p class="mb-0">Total Transaksi</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <i class="fas fa-box fa-2x mb-2"></i>
                        <h4><?= number_format($total_items) ?></h4>
                        <p class="mb-0">Total Item Disewa</p>
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
                        <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                        <h4><?= number_format($unique_transactions) ?></h4>
                        <p class="mb-0">Unique Transactions</p>
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
                    $customers = array_unique(array_column($results, 'Nama_Pelanggan'));
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
                    $employees = array_unique(array_column($results, 'Nama_Karyawan'));
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
                                        <th>Alamat</th>
                                        <th>Barang</th>
                                        <th>Kategori</th>
                                        <th>Harga Sewa</th>
                                        <th>Kondisi</th>
                                        <th>Tanggal Pinjam</th>
                                        <th>Tanggal Kembali</th>
                                        <th>Karyawan</th>
                                        <th>Shift</th>
                                        <th>Metode Bayar</th>
                                        <th>Status Bayar</th>
                                        <th>Denda</th>
                                        <th>Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $row): ?>
                                        <tr>
                                            <td><strong><?= $row['ID'] ?></strong></td>
                                            <td><?= htmlspecialchars($row['Nama_Pelanggan']) ?></td>
                                            <td>
                                                <small>
                                                    <?= htmlspecialchars($row['No_HP']) ?><br>
                                                    <?= htmlspecialchars($row['Email']) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars($row['Alamat_Lengkap']) ?></small>
                                            </td>
                                            <td>
                                                <small><strong><?= htmlspecialchars($row['Nama_Barang']) ?></strong></small>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars($row['Kategori_Barang']) ?></small>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars($row['Harga_Sewa']) ?></small>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars($row['Kondisi_Barang']) ?></small>
                                            </td>
                                            <td>
                                                <small><?= $row['Tanggal_Pinjam'] ? date('d/m/Y', strtotime($row['Tanggal_Pinjam'])) : '-' ?></small>
                                            </td>
                                            <td>
                                                <small><?= $row['Tanggal_Kembali'] ? date('d/m/Y', strtotime($row['Tanggal_Kembali'])) : '-' ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($row['Nama_Karyawan']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $row['Shift_Karyawan'] == 'Pagi' ? 'warning' : 'info' ?>">
                                                    <?= $row['Shift_Karyawan'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $row['Metode_Bayar'] == 'Transfer' ? 'primary' : 'success' ?>">
                                                    <?= $row['Metode_Bayar'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $row['Status_Bayar'] == 'Lunas' ? 'success' : 'danger' ?>">
                                                    <?= $row['Status_Bayar'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($row['Denda'] && $row['Denda'] > 0): ?>
                                                    <span class="text-danger">Rp <?= number_format($row['Denda']) ?></span>
                                                    <?php if ($row['Keterangan_Denda']): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars($row['Keterangan_Denda']) ?></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-success">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($row['Rating']): ?>
                                                    <div class="text-warning">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star<?= $i <= $row['Rating'] ? '' : '-o' ?>"></i>
                                                        <?php endfor; ?>
                                                        <br><small>(<?= $row['Rating'] ?>/5)</small>
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
    t.id_transaksi AS ID,
    p.nama_pelanggan AS Nama_Pelanggan,
    p.no_hp AS No_HP,
    p.email AS Email,
    p.alamat_lengkap AS Alamat_Lengkap,
    GROUP_CONCAT(DISTINCT b.nama_barang ORDER BY b.nama_barang SEPARATOR ', ') AS Nama_Barang,
    GROUP_CONCAT(DISTINCT kb.nama_kategori ORDER BY kb.nama_kategori SEPARATOR ', ') AS Kategori_Barang,
    GROUP_CONCAT(DISTINCT dt.harga_satuan ORDER BY b.nama_barang SEPARATOR ', ') AS Harga_Sewa,
    GROUP_CONCAT(DISTINCT 
        CASE 
            WHEN dt.kondisi_kembali IS NOT NULL THEN dt.kondisi_kembali 
            ELSE dt.kondisi_awal 
        END 
        ORDER BY b.nama_barang SEPARATOR ', '
    ) AS Kondisi_Barang,
    t.tanggal_pinjam AS Tanggal_Pinjam,
    t.tanggal_kembali AS Tanggal_Kembali,
    k.nama_karyawan AS Nama_Karyawan,
    k.shift_karyawan AS Shift_Karyawan,
    t.metode_bayar AS Metode_Bayar,
    t.status_bayar AS Status_Bayar,
    t.tanggal_bayar AS Tanggal_Bayar,
    CASE WHEN t.denda > 0 THEN t.denda ELSE NULL END AS Denda,
    CASE WHEN t.denda > 0 THEN t.keterangan_denda ELSE NULL END AS Keterangan_Denda,
    t.ulasan_pelanggan AS Ulasan_Pelanggan,
    t.rating AS Rating
FROM
    transaksi t
    JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
    JOIN karyawan k ON t.id_karyawan = k.id_karyawan
    JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
    JOIN barang b ON dt.id_barang = b.id_barang
    JOIN kategori_barang kb ON b.id_kategori = kb.id_kategori
GROUP BY 
    t.id_transaksi, p.nama_pelanggan, p.no_hp, p.email, p.alamat_lengkap,
    k.nama_karyawan, k.shift_karyawan, t.tanggal_pinjam, t.tanggal_kembali,
    t.metode_bayar, t.status_bayar, t.tanggal_bayar, t.denda, t.keterangan_denda,
    t.ulasan_pelanggan, t.rating
ORDER BY t.id_transaksi;</code></pre>
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
                table.column(1).search(this.value).draw(); // Kolom Nama_Pelanggan
            });

            $('#filterKaryawan').on('change', function() {
                table.column(10).search(this.value).draw(); // Kolom Nama_Karyawan
            });

            $('#filterStatus').on('change', function() {
                table.column(13).search(this.value).draw(); // Kolom Status_Bayar
            });
        });
    </script>
</body>

</html>