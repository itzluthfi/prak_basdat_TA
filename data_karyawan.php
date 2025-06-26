<?php
require_once 'auth_check.php';
require_once 'config/database.php';

// Check admin access
if (!isAdmin()) {
    header("Location: index.php?error=access_denied");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    if ($action == 'add') {
        $nama_karyawan = trim($_POST['nama_karyawan']);
        $username = trim($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $posisi = trim($_POST['posisi']);
        $shift_karyawan = $_POST['shift_karyawan'];
        $status = $_POST['status'];

        // Check if username already exists
        $check_query = "SELECT COUNT(*) as count FROM karyawan WHERE username = :username";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':username', $username);
        $check_stmt->execute();
        $check_result = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($check_result['count'] > 0) {
            $message = 'Username sudah digunakan!';
            $message_type = 'warning';
        } else {
            $query = "INSERT INTO karyawan (nama_karyawan, username, password, posisi, shift_karyawan, status) 
                      VALUES (:nama_karyawan, :username, :password, :posisi, :shift_karyawan, :status)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nama_karyawan', $nama_karyawan);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':posisi', $posisi);
            $stmt->bindParam(':shift_karyawan', $shift_karyawan);
            $stmt->bindParam(':status', $status);

            if ($stmt->execute()) {
                $message = 'Karyawan berhasil ditambahkan!';
                $message_type = 'success';
            } else {
                $message = 'Gagal menambahkan karyawan!';
                $message_type = 'danger';
            }
        }
    } elseif ($action == 'edit') {
        $id_karyawan = (int)$_POST['id_karyawan'];
        $nama_karyawan = trim($_POST['nama_karyawan']);
        $username = trim($_POST['username']);
        $posisi = trim($_POST['posisi']);
        $shift_karyawan = $_POST['shift_karyawan'];
        $status = $_POST['status'];

        // Check if username is taken by another employee
        $check_query = "SELECT COUNT(*) as count FROM karyawan WHERE username = :username AND id_karyawan != :id_karyawan";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':username', $username);
        $check_stmt->bindParam(':id_karyawan', $id_karyawan);
        $check_stmt->execute();
        $check_result = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($check_result['count'] > 0) {
            $message = 'Username sudah digunakan oleh karyawan lain!';
            $message_type = 'warning';
        } else {
            if (!empty($_POST['password'])) {
                // Update with password
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $query = "UPDATE karyawan SET nama_karyawan = :nama_karyawan, username = :username, 
                          password = :password, posisi = :posisi, shift_karyawan = :shift_karyawan, status = :status WHERE id_karyawan = :id_karyawan";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':password', $password);
            } else {
                // Update without password
                $query = "UPDATE karyawan SET nama_karyawan = :nama_karyawan, username = :username, 
                          posisi = :posisi, shift_karyawan = :shift_karyawan, status = :status WHERE id_karyawan = :id_karyawan";
                $stmt = $db->prepare($query);
            }

            $stmt->bindParam(':id_karyawan', $id_karyawan);
            $stmt->bindParam(':nama_karyawan', $nama_karyawan);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':posisi', $posisi);
            $stmt->bindParam(':shift_karyawan', $shift_karyawan);
            $stmt->bindParam(':status', $status);

            if ($stmt->execute()) {
                $message = 'Data karyawan berhasil diupdate!';
                $message_type = 'success';
            } else {
                $message = 'Gagal mengupdate data karyawan!';
                $message_type = 'danger';
            }
        }
    } elseif ($action == 'delete') {
        $id_karyawan = (int)$_POST['id_karyawan'];

        // Check if employee has active transactions
        $check_query = "SELECT COUNT(*) as count FROM transaksi WHERE id_karyawan = :id_karyawan AND status_bayar = 'Belum Lunas'";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':id_karyawan', $id_karyawan);
        $check_stmt->execute();
        $check_result = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($check_result['count'] > 0) {
            $message = 'Tidak dapat menghapus karyawan yang memiliki transaksi belum lunas!';
            $message_type = 'warning';
        } else {
            $query = "DELETE FROM karyawan WHERE id_karyawan = :id_karyawan";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id_karyawan', $id_karyawan);

            if ($stmt->execute()) {
                $message = 'Karyawan berhasil dihapus!';
                $message_type = 'success';
            } else {
                $message = 'Gagal menghapus karyawan!';
                $message_type = 'danger';
            }
        }
    }
}

// Get all employees with transaction stats
$query = "SELECT 
    k.*,
    COUNT(t.id_transaksi) as total_transaksi,
    SUM(CASE WHEN t.status_bayar = 'Belum Lunas' THEN 1 ELSE 0 END) as transaksi_aktif,
    COALESCE(SUM(dt.jumlah * dt.harga_satuan), 0) as total_nilai_transaksi,
    MAX(t.tanggal_pinjam) as transaksi_terakhir
FROM karyawan k
LEFT JOIN transaksi t ON k.id_karyawan = t.id_karyawan
LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
GROUP BY k.id_karyawan, k.nama_karyawan, k.username, k.posisi, k.shift_karyawan, k.status, k.last_login
ORDER BY k.nama_karyawan";

$stmt = $db->prepare($query);
$stmt->execute();
$karyawan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Karyawan - Rental Alat Pendakian</title>
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
                            <li><a class="dropdown-item" href="data_pelanggan.php">
                                    <i class="fas fa-users"></i> Data Pelanggan
                                </a></li>
                            <li><a class="dropdown-item active" href="data_karyawan.php">
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
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-user-tie"></i> Data Karyawan
                        </h4>
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="fas fa-plus"></i> Tambah Karyawan
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
                        <h4><?= count($karyawan_list) ?></h4>
                        <p class="mb-0">Total Karyawan</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <i class="fas fa-user-check fa-2x mb-2"></i>
                        <h4><?= count(array_filter($karyawan_list, function ($k) {
                                return $k['status'] == 'Aktif';
                            })) ?></h4>
                        <p class="mb-0">Karyawan Aktif</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-warning text-white">
                    <div class="card-body">
                        <i class="fas fa-handshake fa-2x mb-2"></i>
                        <h4><?= count(array_filter($karyawan_list, function ($k) {
                                return $k['transaksi_aktif'] > 0;
                            })) ?></h4>
                        <p class="mb-0">Sedang Handling</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center bg-info text-white">
                    <div class="card-body">
                        <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                        <h4>Rp <?= number_format(array_sum(array_column($karyawan_list, 'total_nilai_transaksi'))) ?></h4>
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
                            <i class="fas fa-table"></i> Daftar Karyawan
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
                                        <th>Nama Karyawan</th>
                                        <th>Username</th>
                                        <th>Posisi</th>
                                        <th>Shift</th>
                                        <th>Status</th>
                                        <th>Total Transaksi</th>
                                        <th>Nilai Transaksi</th>
                                        <th>Last Login</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($karyawan_list as $karyawan): ?>
                                        <tr>
                                            <td><?= $karyawan['id_karyawan'] ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($karyawan['nama_karyawan']) ?></strong>
                                                <?php if ($karyawan['total_transaksi'] > 10): ?>
                                                    <br><span class="badge bg-warning text-dark">Senior</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <code><?= htmlspecialchars($karyawan['username']) ?></code>
                                            </td>
                                            <td>
                                                <?php
                                                $posisi_class = '';
                                                switch ($karyawan['posisi']) {
                                                    case 'Manager':
                                                        $posisi_class = 'bg-danger';
                                                        break;
                                                    case 'Admin':
                                                        $posisi_class = 'bg-warning text-dark';
                                                        break;
                                                    case 'Staff':
                                                        $posisi_class = 'bg-info';
                                                        break;
                                                    default:
                                                        $posisi_class = 'bg-secondary';
                                                }
                                                ?>
                                                <span class="badge <?= $posisi_class ?>">
                                                    <?= htmlspecialchars($karyawan['posisi']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $karyawan['shift_karyawan'] == 'Pagi' ? 'warning' : 'info' ?>">
                                                    <?= htmlspecialchars($karyawan['shift_karyawan']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($karyawan['status'] == 'Aktif'): ?>
                                                    <span class="badge bg-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Nonaktif</span>
                                                <?php endif; ?>

                                                <?php if ($karyawan['transaksi_aktif'] > 0): ?>
                                                    <br><span class="badge bg-primary">
                                                        <?= $karyawan['transaksi_aktif'] ?> Aktif
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary fs-6">
                                                    <?= $karyawan['total_transaksi'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-success">
                                                    Rp <?= number_format($karyawan['total_nilai_transaksi']) ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <?php if ($karyawan['last_login']): ?>
                                                    <?= date('d/m/Y H:i', strtotime($karyawan['last_login'])) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Belum pernah login</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-warning btn-sm" onclick="editItem(<?= htmlspecialchars(json_encode($karyawan)) ?>)" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php if ($karyawan['id_karyawan'] != $_SESSION['user_id']): ?>
                                                        <button class="btn btn-danger btn-sm" onclick="deleteItem(<?= $karyawan['id_karyawan'] ?>, '<?= htmlspecialchars($karyawan['nama_karyawan']) ?>')" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
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
                            <i class="fas fa-plus"></i> Tambah Karyawan Baru
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="add_nama_karyawan" class="form-label">Nama Karyawan *</label>
                                <input type="text" class="form-control" id="add_nama_karyawan" name="nama_karyawan" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="add_username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="add_username" name="username" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="add_password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="add_password" name="password" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_posisi" class="form-label">Posisi *</label>
                                    <select class="form-select" id="add_posisi" name="posisi" required>
                                        <option value="">Pilih Posisi</option>
                                        <option value="Manager">Manager</option>
                                        <option value="Admin">Admin</option>
                                        <option value="Staff">Staff</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="add_shift_karyawan" class="form-label">Shift *</label>
                                    <select class="form-select" id="add_shift_karyawan" name="shift_karyawan" required>
                                        <option value="">Pilih Shift</option>
                                        <option value="Pagi">Pagi</option>
                                        <option value="Sore">Sore</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="add_status" class="form-label">Status *</label>
                                <select class="form-select" id="add_status" name="status" required>
                                    <option value="">Pilih Status</option>
                                    <option value="Aktif">Aktif</option>
                                    <option value="Nonaktif">Nonaktif</option>
                                </select>
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
                            <i class="fas fa-edit"></i> Edit Karyawan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id_karyawan" id="edit_id_karyawan">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_nama_karyawan" class="form-label">Nama Karyawan *</label>
                                <input type="text" class="form-control" id="edit_nama_karyawan" name="nama_karyawan" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="edit_username" name="username" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="edit_password" class="form-label">Password (kosongkan jika tidak diubah)</label>
                                <input type="password" class="form-control" id="edit_password" name="password">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_posisi" class="form-label">Posisi *</label>
                                <select class="form-select" id="edit_posisi" name="posisi" required>
                                    <option value="">Pilih Posisi</option>
                                    <option value="Manager">Manager</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Staff">Staff</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_shift_karyawan" class="form-label">Shift *</label>
                                <select class="form-select" id="edit_shift_karyawan" name="shift_karyawan" required>
                                    <option value="">Pilih Shift</option>
                                    <option value="Pagi">Pagi</option>
                                    <option value="Sore">Sore</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_status" class="form-label">Status *</label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="">Pilih Status</option>
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
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
                            <i class="fas fa-trash"></i> Hapus Karyawan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id_karyawan" id="delete_id_karyawan">

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Peringatan!</strong>
                            Apakah Anda yakin ingin menghapus karyawan "<span id="delete_nama_karyawan"></span>"?
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
                        title: 'Data Karyawan'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        title: 'Data Karyawan',
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
            document.getElementById('edit_id_karyawan').value = item.id_karyawan;
            document.getElementById('edit_nama_karyawan').value = item.nama_karyawan;
            document.getElementById('edit_username').value = item.username;
            document.getElementById('edit_posisi').value = item.posisi;
            document.getElementById('edit_shift_karyawan').value = item.shift_karyawan;
            document.getElementById('edit_status').value = item.status;

            var editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        }

        // Delete function
        function deleteItem(id, nama) {
            document.getElementById('delete_id_karyawan').value = id;
            document.getElementById('delete_nama_karyawan').textContent = nama;

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