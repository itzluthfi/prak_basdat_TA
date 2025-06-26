<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Query untuk deteksi barang bermasalah
$query = "SELECT 
    b.nama_barang,
    kb.nama_kategori,
    b.harga_sewa,
    COUNT(dt.id_detail) as total_disewa,
    COUNT(CASE WHEN dt.kondisi_awal = 'Rusak' THEN 1 END) as kondisi_awal_rusak,
    COUNT(CASE WHEN dt.kondisi_kembali = 'Rusak' THEN 1 END) as kondisi_kembali_rusak,
    COUNT(CASE WHEN dt.kondisi_awal = 'Rusak' OR dt.kondisi_kembali = 'Rusak' THEN 1 END) as total_bermasalah,
    ROUND((COUNT(CASE WHEN dt.kondisi_awal = 'Rusak' OR dt.kondisi_kembali = 'Rusak' THEN 1 END) * 100.0 / COUNT(dt.id_detail)), 2) as persentase_bermasalah,
    SUM(CASE WHEN dt.kondisi_kembali = 'Rusak' THEN dt.harga_satuan ELSE 0 END) as estimasi_kerugian
FROM barang b
JOIN kategori_barang kb ON b.id_kategori = kb.id_kategori
LEFT JOIN detail_transaksi dt ON b.id_barang = dt.id_barang
WHERE dt.id_detail IS NOT NULL
GROUP BY b.id_barang, b.nama_barang, kb.nama_kategori, b.harga_sewa
HAVING total_bermasalah > 0
ORDER BY persentase_bermasalah DESC, estimasi_kerugian DESC";

$stmt = $db->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deteksi Barang Bermasalah - Rental Alat Pendakian</title>
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
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-exclamation-triangle"></i> Deteksi Barang Bermasalah
                        </h4>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Laporan ini menampilkan barang yang sering dikembalikan dalam kondisi rusak
                            atau sudah rusak sejak awal penyewaan. Berguna untuk quality control dan maintenance.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert if no problems -->
        <?php if (empty($results)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <strong>Excellent!</strong> Tidak ada barang yang bermasalah ditemukan.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <?php
            $total_barang_bermasalah = count($results);
            $total_kerugian = array_sum(array_column($results, 'estimasi_kerugian'));
            $rata_persentase = $results ? array_sum(array_column($results, 'persentase_bermasalah')) / count($results) : 0;
            $barang_kritis = count(array_filter($results, function ($item) {
                return $item['persentase_bermasalah'] > 50;
            }));
            ?>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-danger text-white">
                    <div class="card-body">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <h4><?= number_format($total_barang_bermasalah) ?></h4>
                        <p class="mb-0">Barang Bermasalah</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-warning text-dark">
                    <div class="card-body">
                        <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                        <h4>Rp <?= number_format($total_kerugian) ?></h4>
                        <p class="mb-0">Estimasi Kerugian</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-info text-white">
                    <div class="card-body">
                        <i class="fas fa-percentage fa-2x mb-2"></i>
                        <h4><?= number_format($rata_persentase, 1) ?>%</h4>
                        <p class="mb-0">Rata-rata Kerusakan</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-dark text-white">
                    <div class="card-body">
                        <i class="fas fa-skull-crossbones fa-2x mb-2"></i>
                        <h4><?= number_format($barang_kritis) ?></h4>
                        <p class="mb-0">Status Kritis (>50%)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <?php if (!empty($results)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-table"></i> Data Barang Bermasalah
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
                                            <th>Harga Sewa</th>
                                            <th>Total Disewa</th>
                                            <th>Kondisi Awal Rusak</th>
                                            <th>Rusak Saat Kembali</th>
                                            <th>Total Bermasalah</th>
                                            <th>Persentase</th>
                                            <th>Estimasi Kerugian</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results as $index => $row): ?>
                                            <tr class="<?= $row['persentase_bermasalah'] > 75 ? 'table-danger' : ($row['persentase_bermasalah'] > 50 ? 'table-warning' : '') ?>">
                                                <td><?= $index + 1 ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($row['nama_barang']) ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?= htmlspecialchars($row['nama_kategori']) ?>
                                                    </span>
                                                </td>
                                                <td>Rp <?= number_format($row['harga_sewa']) ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?= $row['total_disewa'] ?>x</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-warning"><?= $row['kondisi_awal_rusak'] ?>x</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-danger"><?= $row['kondisi_kembali_rusak'] ?>x</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-dark"><?= $row['total_bermasalah'] ?>x</span>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 25px;">
                                                        <div class="progress-bar bg-<?= $row['persentase_bermasalah'] > 75 ? 'danger' : ($row['persentase_bermasalah'] > 50 ? 'warning' : ($row['persentase_bermasalah'] > 25 ? 'info' : 'success')) ?>"
                                                            style="width: <?= $row['persentase_bermasalah'] ?>%">
                                                            <?= number_format($row['persentase_bermasalah'], 1) ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong class="text-danger">
                                                        Rp <?= number_format($row['estimasi_kerugian']) ?>
                                                    </strong>
                                                </td>
                                                <td>
                                                    <?php if ($row['persentase_bermasalah'] > 75): ?>
                                                        <span class="badge bg-danger">CRITICAL</span>
                                                    <?php elseif ($row['persentase_bermasalah'] > 50): ?>
                                                        <span class="badge bg-warning">HIGH RISK</span>
                                                    <?php elseif ($row['persentase_bermasalah'] > 25): ?>
                                                        <span class="badge bg-info">MODERATE</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">LOW RISK</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-warning" title="Maintenance">
                                                            <i class="fas fa-tools"></i>
                                                        </button>
                                                        <button class="btn btn-danger" title="Retire">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
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
                                <i class="fas fa-chart-pie"></i> Distribusi Status Risiko
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="riskChart" width="400" height="400"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar"></i> Top 10 Barang Paling Bermasalah
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="problemChart" width="400" height="400"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Recommendations -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-lightbulb"></i> Rekomendasi Tindakan
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card border-danger">
                                    <div class="card-header bg-danger text-white">
                                        <h6 class="mb-0">CRITICAL (>75%)</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled mb-0">
                                            <li><i class="fas fa-times text-danger"></i> Hentikan penyewaan</li>
                                            <li><i class="fas fa-trash text-danger"></i> Pertimbangkan penghapusan</li>
                                            <li><i class="fas fa-exchange-alt text-info"></i> Ganti dengan yang baru</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0">HIGH RISK (51-75%)</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled mb-0">
                                            <li><i class="fas fa-tools text-warning"></i> Maintenance intensif</li>
                                            <li><i class="fas fa-eye text-info"></i> Monitor ketat</li>
                                            <li><i class="fas fa-dollar-sign text-success"></i> Kurangi harga sewa</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">MODERATE (26-50%)</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled mb-0">
                                            <li><i class="fas fa-wrench text-info"></i> Maintenance rutin</li>
                                            <li><i class="fas fa-clipboard-check text-primary"></i> Cek berkala</li>
                                            <li><i class="fas fa-user-graduate text-success"></i> Training user</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">LOW RISK (≤25%)</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled mb-0">
                                            <li><i class="fas fa-check text-success"></i> Kondisi baik</li>
                                            <li><i class="fas fa-calendar text-info"></i> Maintenance terjadwal</li>
                                            <li><i class="fas fa-thumbs-up text-success"></i> Lanjutkan penyewaan</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                    [8, "desc"]
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                }
            });
        });

        <?php if (!empty($results)): ?>
            // Risk Distribution Chart
            const riskCtx = document.getElementById('riskChart').getContext('2d');
            <?php
            $critical = count(array_filter($results, function ($item) {
                return $item['persentase_bermasalah'] > 75;
            }));
            $high = count(array_filter($results, function ($item) {
                return $item['persentase_bermasalah'] > 50 && $item['persentase_bermasalah'] <= 75;
            }));
            $moderate = count(array_filter($results, function ($item) {
                return $item['persentase_bermasalah'] > 25 && $item['persentase_bermasalah'] <= 50;
            }));
            $low = count(array_filter($results, function ($item) {
                return $item['persentase_bermasalah'] <= 25;
            }));
            ?>

            new Chart(riskCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Critical', 'High Risk', 'Moderate', 'Low Risk'],
                    datasets: [{
                        data: [<?= $critical ?>, <?= $high ?>, <?= $moderate ?>, <?= $low ?>],
                        backgroundColor: [
                            'rgba(220, 53, 69, 0.8)',
                            'rgba(255, 193, 7, 0.8)',
                            'rgba(0, 123, 255, 0.8)',
                            'rgba(40, 167, 69, 0.8)'
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

            // Problem Items Chart
            const problemCtx = document.getElementById('problemChart').getContext('2d');
            new Chart(problemCtx, {
                type: 'bar',
                data: {
                    labels: [<?php
                                echo implode(',', array_map(function ($item) {
                                    return '"' . substr(addslashes($item['nama_barang']), 0, 15) . '"';
                                }, array_slice($results, 0, 10)));
                                ?>],
                    datasets: [{
                        label: 'Persentase Bermasalah (%)',
                        data: [<?php
                                echo implode(',', array_map(function ($item) {
                                    return $item['persentase_bermasalah'];
                                }, array_slice($results, 0, 10)));
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
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        <?php endif; ?>
    </script>
</body>

</html>