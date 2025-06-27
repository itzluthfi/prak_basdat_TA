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
        $nama_barang = trim($_POST['nama_barang']);
        $id_kategori = (int)$_POST['id_kategori'];
        $stok_tersedia = (int)$_POST['stok_tersedia'];
        $harga_sewa = (float)$_POST['harga_sewa'];

        $query = "INSERT INTO barang (nama_barang, id_kategori, stok_tersedia, harga_sewa) 
                  VALUES (:nama_barang, :id_kategori, :stok_tersedia, :harga_sewa)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nama_barang', $nama_barang);
        $stmt->bindParam(':id_kategori', $id_kategori);
        $stmt->bindParam(':stok_tersedia', $stok_tersedia);
        $stmt->bindParam(':harga_sewa', $harga_sewa);

        if ($stmt->execute()) {
            $message = 'Barang berhasil ditambahkan!';
            $message_type = 'success';
        } else {
            $message = 'Gagal menambahkan barang!';
            $message_type = 'danger';
        }
    } elseif ($action == 'edit') {
        $id_barang = (int)$_POST['id_barang'];
        $nama_barang = trim($_POST['nama_barang']);
        $id_kategori = (int)$_POST['id_kategori'];
        $stok_tersedia = (int)$_POST['stok_tersedia'];
        $harga_sewa = (float)$_POST['harga_sewa'];

        $query = "UPDATE barang SET nama_barang = :nama_barang, id_kategori = :id_kategori, 
                  stok_tersedia = :stok_tersedia, harga_sewa = :harga_sewa 
                  WHERE id_barang = :id_barang";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_barang', $id_barang);
        $stmt->bindParam(':nama_barang', $nama_barang);
        $stmt->bindParam(':id_kategori', $id_kategori);
        $stmt->bindParam(':stok_tersedia', $stok_tersedia);
        $stmt->bindParam(':harga_sewa', $harga_sewa);

        if ($stmt->execute()) {
            $message = 'Barang berhasil diupdate!';
            $message_type = 'success';
        } else {
            $message = 'Gagal mengupdate barang!';
            $message_type = 'danger';
        }
    } elseif ($action == 'delete') {
        $id_barang = (int)$_POST['id_barang'];

        // Check if item is being used in any active transaction
        $check_query = "SELECT COUNT(*) as count FROM detail_transaksi dt 
                       JOIN transaksi t ON dt.id_transaksi = t.id_transaksi 
                       WHERE dt.id_barang = :id_barang AND t.status_bayar = 'Belum Lunas'";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':id_barang', $id_barang);
        $check_stmt->execute();
        $check_result = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($check_result['count'] > 0) {
            $message = 'Tidak dapat menghapus barang yang sedang disewa!';
            $message_type = 'warning';
        } else {
            $query = "DELETE FROM barang WHERE id_barang = :id_barang";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id_barang', $id_barang);

            if ($stmt->execute()) {
                $message = 'Barang berhasil dihapus!';
                $message_type = 'success';
            } else {
                $message = 'Gagal menghapus barang!';
                $message_type = 'danger';
            }
        }
    }
}

// Get all items with category names
$query = "SELECT b.*, kb.nama_kategori 
          FROM barang b 
          JOIN kategori_barang kb ON b.id_kategori = kb.id_kategori 
          ORDER BY b.nama_barang";
$stmt = $db->prepare($query);
$stmt->execute();
$barang_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for dropdown
$cat_query = "SELECT * FROM kategori_barang ORDER BY nama_kategori";
$cat_stmt = $db->prepare($cat_query);
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang - Rental Alat Pendakian</title>
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
                            <li><a class="dropdown-item active" href="data_barang.php">
                                    <i class="fas fa-box"></i> Data Barang
                                </a></li>
                            <li><a class="dropdown-item" href="data_pelanggan.php">
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
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-box"></i> Data Barang
                        </h4>
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="fas fa-plus"></i> Tambah Barang
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

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-table"></i> Daftar Barang
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
                                        <th>Nama Barang</th>
                                        <th>Kategori</th>
                                        <th>Stok</th>
                                        <th>Harga Sewa</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($barang_list as $barang): ?>
                                        <tr>
                                            <td><?= $barang['id_barang'] ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($barang['nama_barang']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?= htmlspecialchars($barang['nama_kategori']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $barang['stok_tersedia'] <= 5 ? 'danger' : ($barang['stok_tersedia'] <= 10 ? 'warning' : 'success') ?> fs-6">
                                                    <?= $barang['stok_tersedia'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-success">
                                                    Rp <?= number_format($barang['harga_sewa']) ?>
                                                </strong>
                                                <small class="text-muted d-block">/hari</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-warning btn-sm" onclick="editItem(<?= htmlspecialchars(json_encode($barang)) ?>)" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" onclick="deleteItem(<?= $barang['id_barang'] ?>, '<?= htmlspecialchars($barang['nama_barang']) ?>')" title="Hapus">
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
                            <i class="fas fa-plus"></i> Tambah Barang Baru
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="add_nama_barang" class="form-label">Nama Barang *</label>
                                <input type="text" class="form-control" id="add_nama_barang" name="nama_barang" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="add_id_kategori" class="form-label">Kategori *</label>
                                <select class="form-select" id="add_id_kategori" name="id_kategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id_kategori'] ?>"><?= htmlspecialchars($cat['nama_kategori']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="add_stok_tersedia" class="form-label">Stok Tersedia *</label>
                                <input type="number" class="form-control" id="add_stok_tersedia" name="stok_tersedia" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="add_harga_sewa" class="form-label">Harga Sewa/Hari *</label>
                                <input type="number" class="form-control" id="add_harga_sewa" name="harga_sewa" min="0" step="0.01" required>
                            </div>
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
                            <i class="fas fa-edit"></i> Edit Barang
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id_barang" id="edit_id_barang">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_nama_barang" class="form-label">Nama Barang *</label>
                                <input type="text" class="form-control" id="edit_nama_barang" name="nama_barang" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_id_kategori" class="form-label">Kategori *</label>
                                <select class="form-select" id="edit_id_kategori" name="id_kategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id_kategori'] ?>"><?= htmlspecialchars($cat['nama_kategori']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_stok_tersedia" class="form-label">Stok Tersedia *</label>
                                <input type="number" class="form-control" id="edit_stok_tersedia" name="stok_tersedia" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_harga_sewa" class="form-label">Harga Sewa/Hari *</label>
                                <input type="number" class="form-control" id="edit_harga_sewa" name="harga_sewa" min="0" step="0.01" required>
                            </div>
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
                            <i class="fas fa-trash"></i> Hapus Barang
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id_barang" id="delete_id_barang">

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Peringatan!</strong>
                            Apakah Anda yakin ingin menghapus barang "<span id="delete_nama_barang"></span>"?
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
                        title: 'Data Barang'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        title: 'Data Barang',
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
            document.getElementById('edit_id_barang').value = item.id_barang;
            document.getElementById('edit_nama_barang').value = item.nama_barang;
            document.getElementById('edit_id_kategori').value = item.id_kategori;
            document.getElementById('edit_stok_tersedia').value = item.stok_tersedia;
            document.getElementById('edit_harga_sewa').value = item.harga_sewa;

            var editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        }

        // Delete function
        function deleteItem(id, nama) {
            document.getElementById('delete_id_barang').value = id;
            document.getElementById('delete_nama_barang').textContent = nama;

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