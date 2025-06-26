<?php
require_once 'auth_check.php';
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    if ($action == 'add') {
        $nama_pelanggan = trim($_POST['nama_pelanggan']);
        $no_telepon = trim($_POST['no_telepon']);
        $alamat = trim($_POST['alamat']);
        $email = trim($_POST['email']);

        $query = "INSERT INTO pelanggan (nama_pelanggan, no_telepon, alamat, email) 
                  VALUES (:nama_pelanggan, :no_telepon, :alamat, :email)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nama_pelanggan', $nama_pelanggan);
        $stmt->bindParam(':no_telepon', $no_telepon);
        $stmt->bindParam(':alamat', $alamat);
        $stmt->bindParam(':email', $email);

        if ($stmt->execute()) {
            $message = 'Pelanggan berhasil ditambahkan!';
            $message_type = 'success';
        } else {
            $message = 'Gagal menambahkan pelanggan!';
            $message_type = 'danger';
        }
    } elseif ($action == 'edit') {
        $id_pelanggan = (int)$_POST['id_pelanggan'];
        $nama_pelanggan = trim($_POST['nama_pelanggan']);
        $no_telepon = trim($_POST['no_telepon']);
        $alamat = trim($_POST['alamat']);
        $email = trim($_POST['email']);

        $query = "UPDATE pelanggan SET nama_pelanggan = :nama_pelanggan, no_telepon = :no_telepon, 
                  alamat = :alamat, email = :email WHERE id_pelanggan = :id_pelanggan";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_pelanggan', $id_pelanggan);
        $stmt->bindParam(':nama_pelanggan', $nama_pelanggan);
        $stmt->bindParam(':no_telepon', $no_telepon);
        $stmt->bindParam(':alamat', $alamat);
        $stmt->bindParam(':email', $email);

        if ($stmt->execute()) {
            $message = 'Data pelanggan berhasil diupdate!';
            $message_type = 'success';
        } else {
            $message = 'Gagal mengupdate data pelanggan!';
            $message_type = 'danger';
        }
    } elseif ($action == 'delete') {
        $id_pelanggan = (int)$_POST['id_pelanggan'];

        // Check if customer has active transactions
        $check_query = "SELECT COUNT(*) as count FROM transaksi WHERE id_pelanggan = :id_pelanggan AND status = 'Aktif'";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':id_pelanggan', $id_pelanggan);
        $check_stmt->execute();
        $check_result = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($check_result['count'] > 0) {
            $message = 'Tidak dapat menghapus pelanggan yang memiliki transaksi aktif!';
            $message_type = 'warning';
        } else {
            $query = "DELETE FROM pelanggan WHERE id_pelanggan = :id_pelanggan";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id_pelanggan', $id_pelanggan);

            if ($stmt->execute()) {
                $message = 'Pelanggan berhasil dihapus!';
                $message_type = 'success';
            } else {
                $message = 'Gagal menghapus pelanggan!';
                $message_type = 'danger';
            }
        }
    }
}

// Get all customers with transaction stats
$query = "SELECT 
    p.*,
    COUNT(t.id_transaksi) as total_transaksi,
    SUM(CASE WHEN t.status = 'Aktif' THEN 1 ELSE 0 END) as transaksi_aktif,
    COALESCE(SUM(dt.jumlah * dt.harga_satuan), 0) as total_nilai_transaksi,
    MAX(t.tanggal_sewa) as transaksi_terakhir
FROM pelanggan p
LEFT JOIN transaksi t ON p.id_pelanggan = t.id_pelanggan
LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
GROUP BY p.id_pelanggan, p.nama_pelanggan, p.no_telepon, p.alamat, p.email
ORDER BY p.nama_pelanggan";

$stmt = $db->prepare($query);
$stmt->execute();
$pelanggan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pelanggan - Rental Alat Pendakian</title>
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-database"></i> Data Master
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="data_barang.php">
                                    <i class="fas fa-box"></i> Data Barang
                                </a></li>
                            <li><a class="dropdown-item active" href="data_pelanggan.php">
                                    <i class="fas fa-users"></i> Data Pelanggan
                                </a></li>
                            <li><a class="dropdown-item" href="data_karyawan.php">
                                    <i class="fas fa-user-tie"></i> Data Karyawan
                                </a></li>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['nama_karyawan']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-users"></i> Data Pelanggan
                        </h4>
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="fas fa-plus"></i> Tambah Pelanggan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?= $message_type == 'success' ? 'check-circle' : ($message_type == 'warning' ? 'exclamation-triangle' : 'times-circle') ?> me-2"></i>
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card text-center bg-primary text-white">
                    <div class="card-body">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h4><?= count($pelanggan_list) ?></h4>
                        <p class="mb-0">Total Pelanggan</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <i class="fas fa-user-check fa-2x mb-2"></i>
                        <h4><?= count(array_filter($pelanggan_list, function ($p) {
                                return $p['transaksi_aktif'] > 0;
                            })) ?></h4>
                        <p class="mb-0">Pelanggan Aktif</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-warning text-white">
                    <div class="card-body">
                        <i class="fas fa-handshake fa-2x mb-2"></i>
                        <h4><?= count(array_filter($pelanggan_list, function ($p) {
                                return $p['total_transaksi'] > 0;
                            })) ?></h4>
                        <p class="mb-0">Pernah Transaksi</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-info text-white">
                    <div class="card-body">
                        <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                        <h4>Rp <?= number_format(array_sum(array_column($pelanggan_list, 'total_nilai_transaksi'))) ?></h4>
                        <p class="mb-0">Total Nilai</p>
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
                            <i class="fas fa-table"></i> Daftar Pelanggan
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
                                        <th>Nama Pelanggan</th>
                                        <th>Kontak</th>
                                        <th>Alamat</th>
                                        <th>Total Transaksi</th>
                                        <th>Status</th>
                                        <th>Nilai Transaksi</th>
                                        <th>Transaksi Terakhir</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pelanggan_list as $pelanggan): ?>
                                        <tr>
                                            <td><?= $pelanggan['id_pelanggan'] ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($pelanggan['nama_pelanggan']) ?></strong>
                                                <?php if ($pelanggan['total_transaksi'] > 5): ?>
                                                    <br><span class="badge bg-warning text-dark">VIP</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <i class="fas fa-phone text-primary"></i>
                                                <?= htmlspecialchars($pelanggan['no_telepon']) ?>
                                                <br>
                                                <i class="fas fa-envelope text-secondary"></i>
                                                <small><?= htmlspecialchars($pelanggan['email']) ?></small>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars($pelanggan['alamat']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary fs-6">
                                                    <?= $pelanggan['total_transaksi'] ?>
                                                </span>
                                                <?php if ($pelanggan['transaksi_aktif'] > 0): ?>
                                                    <br><span class="badge bg-warning">
                                                        <?= $pelanggan['transaksi_aktif'] ?> Aktif
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($pelanggan['transaksi_aktif'] > 0): ?>
                                                    <span class="badge bg-success">Sedang Menyewa</span>
                                                <?php elseif ($pelanggan['total_transaksi'] > 0): ?>
                                                    <span class="badge bg-info">Pelanggan Lama</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Belum Transaksi</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong class="text-success">
                                                    Rp <?= number_format($pelanggan['total_nilai_transaksi']) ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <?php if ($pelanggan['transaksi_terakhir']): ?>
                                                    <?= date('d/m/Y', strtotime($pelanggan['transaksi_terakhir'])) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-warning btn-sm" onclick="editItem(<?= htmlspecialchars(json_encode($pelanggan)) ?>)" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" onclick="deleteItem(<?= $pelanggan['id_pelanggan'] ?>, '<?= htmlspecialchars($pelanggan['nama_pelanggan']) ?>')" title="Hapus">
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
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-plus"></i> Tambah Pelanggan Baru
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="add_nama_pelanggan" class="form-label">Nama Pelanggan *</label>
                                <input type="text" class="form-control" id="add_nama_pelanggan" name="nama_pelanggan" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="add_no_telepon" class="form-label">No. Telepon *</label>
                                <input type="tel" class="form-control" id="add_no_telepon" name="no_telepon" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="add_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="add_email" name="email" placeholder="contoh@email.com">
                        </div>

                        <div class="mb-3">
                            <label for="add_alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="add_alamat" name="alamat" rows="3" placeholder="Alamat lengkap pelanggan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-edit"></i> Edit Pelanggan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id_pelanggan" id="edit_id_pelanggan">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_nama_pelanggan" class="form-label">Nama Pelanggan *</label>
                                <input type="text" class="form-control" id="edit_nama_pelanggan" name="nama_pelanggan" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_no_telepon" class="form-label">No. Telepon *</label>
                                <input type="tel" class="form-control" id="edit_no_telepon" name="no_telepon" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" placeholder="contoh@email.com">
                        </div>

                        <div class="mb-3">
                            <label for="edit_alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="edit_alamat" name="alamat" rows="3" placeholder="Alamat lengkap pelanggan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-trash"></i> Hapus Pelanggan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id_pelanggan" id="delete_id_pelanggan">

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Peringatan!</strong>
                            Apakah Anda yakin ingin menghapus pelanggan "<span id="delete_nama_pelanggan"></span>"?
                            <br><br>
                            Tindakan ini tidak dapat dibatalkan!
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                </form>
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

    <script>
        // Initialize DataTable
        $(document).ready(function() {
            var table = $('#dataTable').DataTable({
                "pageLength": 10,
                "order": [
                    [1, "asc"]
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                },
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Data Pelanggan'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        title: 'Data Pelanggan',
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

        // Edit function
        function editItem(item) {
            document.getElementById('edit_id_pelanggan').value = item.id_pelanggan;
            document.getElementById('edit_nama_pelanggan').value = item.nama_pelanggan;
            document.getElementById('edit_no_telepon').value = item.no_telepon;
            document.getElementById('edit_email').value = item.email || '';
            document.getElementById('edit_alamat').value = item.alamat || '';

            var editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        }

        // Delete function
        function deleteItem(id, nama) {
            document.getElementById('delete_id_pelanggan').value = id;
            document.getElementById('delete_nama_pelanggan').textContent = nama;

            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }

        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>

</html>