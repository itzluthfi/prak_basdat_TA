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
        $kategori = trim($_POST['kategori']);
        $stok = (int)$_POST['stok'];
        $harga_sewa = (float)$_POST['harga_sewa'];
        $deskripsi = trim($_POST['deskripsi']);
        $kondisi = $_POST['kondisi'];

        $query = "INSERT INTO barang (nama_barang, kategori, stok, harga_sewa_per_hari, deskripsi, kondisi) 
                  VALUES (:nama_barang, :kategori, :stok, :harga_sewa, :deskripsi, :kondisi)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nama_barang', $nama_barang);
        $stmt->bindParam(':kategori', $kategori);
        $stmt->bindParam(':stok', $stok);
        $stmt->bindParam(':harga_sewa', $harga_sewa);
        $stmt->bindParam(':deskripsi', $deskripsi);
        $stmt->bindParam(':kondisi', $kondisi);

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
        $kategori = trim($_POST['kategori']);
        $stok = (int)$_POST['stok'];
        $harga_sewa = (float)$_POST['harga_sewa'];
        $deskripsi = trim($_POST['deskripsi']);
        $kondisi = $_POST['kondisi'];

        $query = "UPDATE barang SET nama_barang = :nama_barang, kategori = :kategori, 
                  stok = :stok, harga_sewa_per_hari = :harga_sewa, deskripsi = :deskripsi, 
                  kondisi = :kondisi WHERE id_barang = :id_barang";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_barang', $id_barang);
        $stmt->bindParam(':nama_barang', $nama_barang);
        $stmt->bindParam(':kategori', $kategori);
        $stmt->bindParam(':stok', $stok);
        $stmt->bindParam(':harga_sewa', $harga_sewa);
        $stmt->bindParam(':deskripsi', $deskripsi);
        $stmt->bindParam(':kondisi', $kondisi);

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
                       WHERE dt.id_barang = :id_barang AND t.status = 'Aktif'";
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

// Get all items
$query = "SELECT * FROM barang ORDER BY nama_barang";
$stmt = $db->prepare($query);
$stmt->execute();
$barang_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for dropdown
$cat_query = "SELECT DISTINCT kategori FROM barang ORDER BY kategori";
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
                                        <th>Kondisi</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($barang_list as $barang): ?>
                                        <tr>
                                            <td><?= $barang['id_barang'] ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($barang['nama_barang']) ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars(substr($barang['deskripsi'] ?? '', 0, 50)) ?>
                                                    <?= strlen($barang['deskripsi'] ?? '') > 50 ? '...' : '' ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?= htmlspecialchars($barang['kategori']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $barang['stok'] <= 5 ? 'danger' : ($barang['stok'] <= 10 ? 'warning' : 'success') ?> fs-6">
                                                    <?= $barang['stok'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-success">
                                                    Rp <?= number_format($barang['harga_sewa_per_hari']) ?>
                                                </strong>
                                                <small class="text-muted d-block">/hari</small>
                                            </td>
                                            <td>
                                                <?php
                                                $kondisi_class = '';
                                                switch ($barang['kondisi']) {
                                                    case 'Baik':
                                                        $kondisi_class = 'bg-success';
                                                        break;
                                                    case 'Cukup':
                                                        $kondisi_class = 'bg-warning';
                                                        break;
                                                    case 'Rusak':
                                                        $kondisi_class = 'bg-danger';
                                                        break;
                                                    default:
                                                        $kondisi_class = 'bg-secondary';
                                                }
                                                ?>
                                                <span class="badge <?= $kondisi_class ?>">
                                                    <?= htmlspecialchars($barang['kondisi']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $status = $barang['stok'] > 0 ? 'Tersedia' : 'Habis';
                                                $status_class = $barang['stok'] > 0 ? 'bg-success' : 'bg-danger';
                                                ?>
                                                <span class="badge <?= $status_class ?>">
                                                    <?= $status ?>
                                                </span>
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
                                <label for="add_kategori" class="form-label">Kategori *</label>
                                <input type="text" class="form-control" id="add_kategori" name="kategori" required list="kategori_list">
                                <datalist id="kategori_list">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat['kategori']) ?>">
                                        <?php endforeach; ?>
                                </datalist>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="add_stok" class="form-label">Stok *</label>
                                <input type="number" class="form-control" id="add_stok" name="stok" min="0" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="add_harga_sewa" class="form-label">Harga Sewa/Hari *</label>
                                <input type="number" class="form-control" id="add_harga_sewa" name="harga_sewa" min="0" step="0.01" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="add_kondisi" class="form-label">Kondisi *</label>
                                <select class="form-select" id="add_kondisi" name="kondisi" required>
                                    <option value="">Pilih Kondisi</option>
                                    <option value="Baik">Baik</option>
                                    <option value="Cukup">Cukup</option>
                                    <option value="Rusak">Rusak</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="add_deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="add_deskripsi" name="deskripsi" rows="3" placeholder="Deskripsi detail barang..."></textarea>
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
                                <label for="edit_kategori" class="form-label">Kategori *</label>
                                <input type="text" class="form-control" id="edit_kategori" name="kategori" required list="kategori_list">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="edit_stok" class="form-label">Stok *</label>
                                <input type="number" class="form-control" id="edit_stok" name="stok" min="0" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_harga_sewa" class="form-label">Harga Sewa/Hari *</label>
                                <input type="number" class="form-control" id="edit_harga_sewa" name="harga_sewa" min="0" step="0.01" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_kondisi" class="form-label">Kondisi *</label>
                                <select class="form-select" id="edit_kondisi" name="kondisi" required>
                                    <option value="">Pilih Kondisi</option>
                                    <option value="Baik">Baik</option>
                                    <option value="Cukup">Cukup</option>
                                    <option value="Rusak">Rusak</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3" placeholder="Deskripsi detail barang..."></textarea>
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
            document.getElementById('edit_kategori').value = item.kategori;
            document.getElementById('edit_stok').value = item.stok;
            document.getElementById('edit_harga_sewa').value = item.harga_sewa_per_hari;
            document.getElementById('edit_kondisi').value = item.kondisi;
            document.getElementById('edit_deskripsi').value = item.deskripsi || '';

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