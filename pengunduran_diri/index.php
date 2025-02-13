<?php
include '../template/header.php';
include '../template/sidebar.php';
include '../connection.php';

// Function to retrieve data from pengunduran_diri table
function getPengunduranDiri() {
    global $conn;
    $result = $conn->query("SELECT * FROM pengunduran_diri");
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

// Fetch data from pengunduran_diri table
$pengunduran_diri = getPengunduranDiri();
?>
<main id="main" class="main">
    <div class="container mt-5">
        <!-- Hero Section -->
        <div class="hero-section p-5 text-center text-white mb-5" style="background: linear-gradient(45deg, #4CAF50, #2E7D32); border-radius: 10px;">
            <h1 class="display-4">Dashboard Pengunduran Diri</h1>
            <p class="lead">Kelola pengajuan pengunduran diri karyawan dengan mudah dan cepat.</p>
        </div>

        <!-- Dashboard Summary Cards -->
        <div class="row text-center mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-success" style="border-radius: 10px; background: #e8f5e9;">
                    <div class="card-body">
                        <h4 class="card-title">Pengajuan Disetujui</h4>
                        <p class="display-4"><?= count(array_filter($pengunduran_diri, fn($row) => $row['status_pengajuan'] == 'Disetujui')) ?></p>
                        <p class="card-text">Jumlah pengajuan yang telah disetujui.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-warning" style="border-radius: 10px; background: #fff8e1;">
                    <div class="card-body">
                        <h4 class="card-title">Menunggu Konfirmasi</h4>
                        <p class="display-4"><?= count(array_filter($pengunduran_diri, fn($row) => $row['status_pengajuan'] == 'Menunggu')) ?></p>
                        <p class="card-text">Jumlah pengajuan yang masih menunggu konfirmasi.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-danger" style="border-radius: 10px; background: #ffebee;">
                    <div class="card-body">
                        <h4 class="card-title">Ditolak</h4>
                        <p class="display-4"><?= count(array_filter($pengunduran_diri, fn($row) => $row['status_pengajuan'] == 'Ditolak')) ?></p>
                        <p class="card-text">Jumlah pengajuan yang ditolak.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content (Navigation and Table) -->
        <h2 class="text-center mb-4">Manajemen Pengunduran Diri Karyawan</h2>
        
        <!-- Navigation tabs -->
        <ul class="nav nav-pills justify-content-center" id="pengunduranDiriTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="pengunduran-tab" data-bs-toggle="tab" href="#pengunduran" role="tab" aria-controls="pengunduran" aria-selected="true">
                    <i class="bi bi-file-earmark-plus"></i> Pengajuan Pengunduran Diri
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="status-tab" data-bs-toggle="tab" href="#status" role="tab" aria-controls="status" aria-selected="false">
                    <i class="bi bi-check-circle"></i> Status Pengajuan
                </a>
            </li>
        </ul>

        <div class="tab-content mt-3" id="pengunduranDiriTabContent">
            <!-- Tab 1: Pengajuan Pengunduran Diri -->
            <div class="tab-pane fade show active" id="pengunduran" role="tabpanel" aria-labelledby="pengunduran-tab">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Tambah Pengajuan Pengunduran Diri</h3>
                        <form method="post" action="submit_pengunduran.php">
                            <div class="mb-3">
                                <label for="id_karyawan" class="form-label">ID Karyawan</label>
                                <input type="number" class="form-control" id="id_karyawan" name="id_karyawan" required>
                            </div>
                            <div class="mb-3">
                                <label for="tanggal_pengajuan" class="form-label">Tanggal Pengajuan</label>
                                <input type="date" class="form-control" id="tanggal_pengajuan" name="tanggal_pengajuan" required>
                            </div>
                            <div class="mb-3">
                                <label for="tanggal_efektif" class="form-label">Tanggal Efektif Pengunduran Diri</label>
                                <input type="date" class="form-control" id="tanggal_efektif" name="tanggal_efektif" required>
                            </div>
                            <div class="mb-3">
                                <label for="alasan" class="form-label">Alasan Pengunduran Diri</label>
                                <textarea class="form-control" id="alasan" name="alasan" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="status_pengajuan" class="form-label">Status Pengajuan</label>
                                <select class="form-select" id="status_pengajuan" name="status_pengajuan" required>
                                    <option value="Menunggu">Menunggu</option>
                                    <option value="Disetujui">Disetujui</option>
                                    <option value="Ditolak">Ditolak</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Submit Pengajuan</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Status Pengajuan -->
            <div class="tab-pane fade" id="status" role="tabpanel" aria-labelledby="status-tab">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Status Pengajuan Pengunduran Diri</h3>

                        <!-- Search Input -->
                        <div class="mb-4">
                            <input class="form-control" id="searchInput" type="text" placeholder="Cari berdasarkan ID, Tanggal, atau Alasan..." onkeyup="searchTable()">
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="pengunduranTable">
                                <thead>
                                    <tr>
                                        <th>ID Karyawan</th>
                                        <th>Tanggal Pengajuan</th>
                                        <th>Tanggal Efektif</th>
                                        <th>Alasan</th>
                                        <th>Status Pengajuan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pengunduran_diri as $row): ?>
                                        <tr>
                                            <td><?= $row['id_karyawan'] ?></td>
                                            <td><?= $row['tanggal_pengajuan'] ?></td>
                                            <td><?= $row['tanggal_efektif'] ?></td>
                                            <td><?= $row['alasan'] ?></td>
                                            <td>
                                                <?php
                                                switch ($row['status_pengajuan']) {
                                                    case 'Menunggu':
                                                        echo '<span class="badge bg-warning"><i class="bi bi-hourglass-split"></i> Menunggu</span>';
                                                        break;
                                                    case 'Disetujui':
                                                        echo '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Disetujui</span>';
                                                        break;
                                                    case 'Ditolak':
                                                        echo '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Ditolak</span>';
                                                        break;
                                                    default:
                                                        echo '<span class="badge bg-secondary"><i class="bi bi-question-circle"></i> Tidak Diketahui</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <a href="edit_pengunduran.php?id_pengunduran=<?= $row['id_pengunduran'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                                <a href="delete_pengunduran.php?id_pengunduran=<?= $row['id_pengunduran'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus pengunduran diri ini?')">Delete</a>
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

        <!-- Footer -->
        <div class="footer mt-5 text-center">
            <p class="text-muted">© <?= date('Y') ?> Sistem Pengunduran Diri Karyawan | Dibuat dengan ❤️</p>
        </div>
    </div>
</main>

<!-- Add CSS for better styling -->
<style>
    .card {
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    .hero-section h1 {
        font-size: 2.8rem;
        font-weight: bold;
    }
    .hero-section p {
        font-size: 1.3rem;
    }
    .btn-primary {
        background: linear-gradient(45deg, #4CAF50, #2E7D32);
        transition: 0.3s;
    }
    .btn-primary:hover {
        background: linear-gradient(45deg, #2E7D32, #4CAF50);
    }
    .badge {
        padding: 5px 10px;
        border-radius: 8px;
    }
    .table-striped tbody tr:nth-child(odd) {
        background-color: #f1f1f1;
    }
    .footer {
        font-size: 0.9rem;
        color: #6c757d;
    }
</style>

<!-- JavaScript for Table Search -->
<script>
function searchTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('pengunduranTable');
    const tr = table.getElementsByTagName('tr');

    for (let i = 0; i < tr.length; i++) {
        const td = tr[i].getElementsByTagName('td');
        let match = false;

        for (let j = 0; j < td.length; j++) {
            if (td[j]) {
                if (td[j].textContent.toLowerCase().includes(filter)) {
                    match = true;
                    break;
                }
            }
        }

        if (match) {
            tr[i].style.display = '';
        } else {
            tr[i].style.display = 'none';
        }
    }
}
</script>

<!-- Include JS files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<?php
include '../template/footer.php';
?>
