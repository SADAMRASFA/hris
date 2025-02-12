<?php
include "../connection.php";
require_once "../template/header.php";
require_once "../template/sidebar.php";

$notif = "";
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Proses Tambah/Edit Tunjangan
if (isset($_POST['submit_tunjangan'])) {
    $id_jabatan = $_POST['id_jabatan'];
    $nama_tunjangan = $_POST['nama_tunjangan'];
    $jumlah_tunjangan = str_replace('.', '', $_POST['jumlah_tunjangan']);
    $id_tunjangan = $_POST['id_tunjangan'] ?? '';

    try {
        if ($id_tunjangan) {
            // Proses Edit
            $query = "UPDATE tunjangan SET id_jabatan = ?, nama_tunjangan = ?, jumlah = ? WHERE id_tunjangan = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isdi", $id_jabatan, $nama_tunjangan, $jumlah_tunjangan, $id_tunjangan);
        } else {
            // Proses Tambah
            $query = "INSERT INTO tunjangan (id_jabatan, nama_tunjangan, jumlah) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isd", $id_jabatan, $nama_tunjangan, $jumlah_tunjangan);
        }

        if ($stmt->execute()) {
            $notif = "success|Data tunjangan berhasil " . ($id_tunjangan ? 'diupdate!' : 'disimpan!');
        } else {
            throw new Exception("Gagal " . ($id_tunjangan ? 'mengupdate' : 'menyimpan') . " data!");
        }
        $stmt->close();
    } catch (Exception $e) {
        $notif = "error|" . $e->getMessage();
    }
}

// Proses Hapus Data
if (isset($_GET['hapus'])) {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => ''];
    
    try {
        $id_tunjangan = $_GET['hapus'];
        
        // Mulai transaction
        $conn->begin_transaction();
        
        // Hapus dari penggajian_tunjangan terlebih dahulu
        $query_relasi = "DELETE FROM penggajian_tunjangan WHERE id_tunjangan = ?";
        $stmt_relasi = $conn->prepare($query_relasi);
        $stmt_relasi->bind_param("i", $id_tunjangan);
        $stmt_relasi->execute();
        
        // Kemudian hapus dari tabel tunjangan
        $query = "DELETE FROM tunjangan WHERE id_tunjangan = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_tunjangan);
        
        if ($stmt->execute()) {
            $conn->commit();
            $response = [
                'status' => 'success',
                'message' => 'Data berhasil dihapus'
            ];
        } else {
            throw new Exception($stmt->error);
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

// Query untuk data jabatan
$query_jabatan = "SELECT * FROM jabatan ORDER BY nama_jabatan";
$jabatan_result = mysqli_query($conn, $query_jabatan);

// Inisialisasi search
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query dasar untuk menghitung total records
$count_query = "SELECT COUNT(*) as total FROM tunjangan t
                LEFT JOIN jabatan j ON t.id_jabatan = j.id_jabatan";

// Query dasar untuk mengambil data
$query = "SELECT t.*, j.nama_jabatan 
          FROM tunjangan t
          LEFT JOIN jabatan j ON t.id_jabatan = j.id_jabatan";

// Tambahkan kondisi search jika ada
if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $where_clause = " WHERE j.nama_jabatan LIKE '%$search%' 
                      OR t.nama_tunjangan LIKE '%$search%'";
    $count_query .= $where_clause;
    $query .= $where_clause;
}

// Hitung total records untuk pagination
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $limit);

// Tambahkan ordering dan limit
$query .= " ORDER BY t.id_tunjangan DESC LIMIT $offset, $limit";

// Eksekusi query utama
$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query error: " . mysqli_error($conn));
}

// Data untuk Edit Tunjangan
$edit_tunjangan = null;
if (isset($_GET['edit'])) {
    $id_tunjangan = $_GET['edit'];
    $query = "SELECT * FROM tunjangan WHERE id_tunjangan = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_tunjangan);
    $stmt->execute();
    $edit_tunjangan = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<style>
:root {
    --primary: #2563eb;
    --primary-dark: #1e40af;
    --secondary: #64748b;
    --success: #059669;
    --info: #0284c7;
    --warning: #f59e0b;
    --danger: #dc2626;
    --light: #f8fafc;
    --dark: #1e293b;
    --gradient-1: linear-gradient(135deg, #2563eb, #1e40af);
    --gradient-2: linear-gradient(135deg, #64748b, #475569);
    --gradient-warning: linear-gradient(135deg, #f59e0b, #d97706);
    --gradient-danger: linear-gradient(135deg, #dc2626, #b91c1c);
}

/* Card Styling */
.card {
    border: none;
    border-radius: 16px;
    background: linear-gradient(145deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.85));
    box-shadow: 
        0 10px 25px rgba(37, 99, 235, 0.1),
        0 4px 12px rgba(37, 99, 235, 0.05);
    backdrop-filter: blur(10px);
    margin-bottom: 25px;
}

.card-title {
    color: var(--dark);
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid rgba(37, 99, 235, 0.1);
    position: relative;
}

.card-title::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 80px;
    height: 2px;
    background: var(--gradient-1);
}

/* Form Controls */
.form-control {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px 18px;
    transition: all 0.3s ease;
    background: #f8fafc;
    color: #334155;
    font-size: 14px;
}

.form-control:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.form-control:focus {
    border-color: #3b82f6;
    background: #ffffff;
    box-shadow: 
        0 0 0 4px rgba(59, 130, 246, 0.1),
        0 1px 2px rgba(0, 0, 0, 0.05);
    transform: translateY(-1px);
}

/* Select Styling */
select.form-control {
    background-image: linear-gradient(45deg, transparent 50%, #3b82f6 50%),
                      linear-gradient(135deg, #3b82f6 50%, transparent 50%);
    background-position: 
        calc(100% - 20px) calc(1em + 2px),
        calc(100% - 15px) calc(1em + 2px);
    background-size: 
        5px 5px,
        5px 5px;
    background-repeat: no-repeat;
    padding-right: 40px;
}

/* Table Styling */
.table {
    margin-bottom: 0;
}

.table thead th {
    background: var(--gradient-1);
    color: white;
    font-weight: 600;
    border: none;
    padding: 14px 16px;
    white-space: nowrap;
}

.table tbody td {
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(37, 99, 235, 0.1);
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(37, 99, 235, 0.02);
}

/* Button Styling */
.btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
}

.btn-primary {
    background: var(--gradient-1);
    color: white;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
}

.btn-secondary {
    background: var(--gradient-2);
    color: white;
    box-shadow: 0 4px 12px rgba(100, 116, 139, 0.2);
}

.btn-warning {
    background: var(--gradient-warning);
    color: white;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
}

.btn-danger {
    background: var(--gradient-danger);
    color: white;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
}

/* Search Input Group */
.input-group {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border-radius: 10px;
    overflow: hidden;
}

.input-group .form-control {
    border-radius: 10px 0 0 10px;
    border-right: none;
}

.input-group .btn {
    border-radius: 0 10px 10px 0;
    padding: 10px 25px;
}

/* Pagination Styling */
.pagination {
    margin-top: 25px;
    gap: 5px;
}

.page-link {
    color: var(--primary);
    padding: 8px 16px;
    border: 1px solid rgba(37, 99, 235, 0.1);
    border-radius: 8px;
    transition: all 0.3s ease;
    margin: 0 2px;
}

.page-item.active .page-link {
    background: var(--gradient-1);
    border-color: transparent;
}

.page-link:hover {
    background: rgba(37, 99, 235, 0.1);
    transform: translateY(-1px);
}

/* Form Label */
.form-label {
    color: #475569;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 8px;
    display: block;
}

/* Money Input */
.money {
    font-family: 'Roboto Mono', monospace;
    letter-spacing: 0.5px;
}

/* Action Buttons */
.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
}

.btn-action-group {
    display: flex;
    gap: 8px;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .card {
        margin-bottom: 20px;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .btn-action-group {
        flex-direction: column;
    }
}

.table-action-btn {
    width: 35px;
    height: 35px;
    margin: 0 3px;
    border-radius: 12px;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
}

.table-action-btn:hover {
    transform: translateY(-2px);
}

.table-action-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, rgba(255,255,255,0.2), rgba(255,255,255,0));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.table-action-btn:hover::before {
    opacity: 1;
}

.btn-gradient-info {
    background: linear-gradient(45deg, #3498db, #2980b9);
    color: white;
}

.btn-gradient-warning {
    background: linear-gradient(45deg, #f1c40f, #f39c12);
    color: white;
}

.btn-gradient-danger {
    background: linear-gradient(45deg, #e74c3c, #c0392b);
    color: white;
}

/* Animasi untuk table rows */
.table tbody tr {
    animation: fadeIn 0.5s ease-out forwards;
    opacity: 0;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.table tbody tr:nth-child(1) { animation-delay: 0.1s; }
.table tbody tr:nth-child(2) { animation-delay: 0.2s; }
.table tbody tr:nth-child(3) { animation-delay: 0.3s; }
/* ... dan seterusnya */
</style>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Data Tunjangan</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php">Penggajian</a></li>
                <li class="breadcrumb-item active">Tunjangan</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row mb-3">
            <div class="col-lg-12">
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

    <section class="section">
        <div class="row">
            <!-- Form Tambah/Edit -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title" id="formTitle">Tambah Tunjangan</h5>
                        <form method="POST" id="formTunjangan">
                            <input type="hidden" name="id_tunjangan" id="id_tunjangan">
                            <div class="mb-3">
                                <label for="id_jabatan" class="form-label">Pilih Jabatan</label>
                                <select class="form-control" id="id_jabatan" name="id_jabatan" required>
                                    <option value="">-- Pilih Jabatan --</option>
                                    <?php 
                                    $jabatan = mysqli_query($conn, "SELECT * FROM jabatan");
                                    while ($jab = mysqli_fetch_assoc($jabatan)) : 
                                    ?>
                                        <option value="<?= $jab['id_jabatan'] ?>">
                                            <?= $jab['nama_jabatan'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="nama_tunjangan" class="form-label">Nama Tunjangan</label>
                                <input type="text" class="form-control" id="nama_tunjangan" name="nama_tunjangan" required>
                            </div>
                            <div class="mb-3">
                                <label for="jumlah_tunjangan" class="form-label">Jumlah Tunjangan</label>
                                <input type="text" class="form-control money" id="jumlah_tunjangan" name="jumlah_tunjangan" required>
                            </div>
                            <button type="submit" name="submit_tunjangan" class="btn btn-primary">Simpan</button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">Batal</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabel Daftar Tunjangan -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Daftar Tunjangan</h5>

                        <!-- Form Pencarian di dalam Daftar Tunjangan -->
                        <!-- Form Pencarian -->
                        <div class="row mb-3">
                            <div class="col-md-7">
                                <form method="GET" class="mb-3">
                                    <div class="input-group">
                                        <input type="text" 
                                               class="form-control" 
                                               name="search" 
                                               placeholder="Cari berdasarkan jabatan atau nama tunjangan..." 
                                               value="<?= htmlspecialchars($search) ?>">
                                        <button class="btn btn-primary" type="submit">Cari</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Jabatan</th>
                                        <th>Nama Tunjangan</th>
                                        <th>Jumlah</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = $offset + 1;
                                    while ($row = mysqli_fetch_assoc($result)) : 
                                    ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama_jabatan']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_tunjangan']) ?></td>
                                        <td>Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" 
                                                        class="btn btn-warning btn-sm"
                                                        onclick="editTunjangan(
                                                            <?= $row['id_tunjangan'] ?>, 
                                                            '<?= $row['id_jabatan'] ?>', 
                                                            '<?= htmlspecialchars($row['nama_tunjangan']) ?>', 
                                                            '<?= $row['jumlah'] ?>'
                                                        )">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm delete-btn"
                                                        data-id="<?= $row['id_tunjangan'] ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="tunjangan.php?page=<?= max(1, $page - 1) ?>&search=<?= urlencode($search) ?>">Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="tunjangan.php?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="tunjangan.php?page=<?= min($total_pages, $page + 1) ?>&search=<?= urlencode($search) ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/autonumeric@4.1.0/dist/autoNumeric.min.js"></script>

<!-- Script untuk notifikasi dan konfirmasi -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format input uang
    const moneyInputs = document.querySelectorAll('.money');
    moneyInputs.forEach(input => {
        new AutoNumeric(input, {
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            decimalPlaces: 0,
            minimumValue: '0'
        });
    });

    // Handler untuk tombol edit
    document.querySelectorAll('.edit-button').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const jabatan = this.dataset.jabatan;
            const nama = this.dataset.nama;
            const jumlah = this.dataset.jumlah;
            
            // Isi form dengan data
            document.querySelector('input[name="id_tunjangan"]').value = id;
            document.querySelector('select[name="id_jabatan"]').value = jabatan;
            document.querySelector('input[name="nama_tunjangan"]').value = nama;
            
            // Set nilai jumlah dengan format uang
            const jumlahInput = document.querySelector('input[name="jumlah_tunjangan"]');
            if (AutoNumeric.getAutoNumericElement(jumlahInput)) {
                AutoNumeric.getAutoNumericElement(jumlahInput).set(jumlah);
            } else {
                new AutoNumeric(jumlahInput, {
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    decimalPlaces: 0,
                    minimumValue: '0'
                }).set(jumlah);
            }

            // Update judul form
            document.querySelector('.card-title').textContent = 'Edit Tunjangan';
            
            // Scroll ke form
            document.querySelector('.card').scrollIntoView({ behavior: 'smooth' });
        });
    });

    // Handler untuk tombol hapus
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: "Apakah Anda yakin ingin menghapus data ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tampilkan loading
                    Swal.fire({
                        title: 'Memproses...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Kirim request dengan XMLHttpRequest
                    const xhr = new XMLHttpRequest();
                    xhr.open('GET', `hapus_tunjangan.php?id=${id}`, true);
                    
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: response.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    throw new Error(response.message);
                                }
                            } catch (e) {
                                console.error('Response:', xhr.responseText);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Terjadi kesalahan saat memproses response'
                                });
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Terjadi kesalahan saat menghubungi server'
                            });
                        }
                    };
                    
                    xhr.onerror = function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Terjadi kesalahan koneksi'
                        });
                    };
                    
                    xhr.send();
                }
            });
        });
    });

    // Reset form ketika tombol batal diklik
    document.querySelector('button[type="reset"]').addEventListener('click', function() {
        document.querySelector('form').reset();
        document.querySelector('.card-title').textContent = 'Tambah Tunjangan';
        const jumlahInput = document.querySelector('input[name="jumlah_tunjangan"]');
        if (AutoNumeric.getAutoNumericElement(jumlahInput)) {
            AutoNumeric.getAutoNumericElement(jumlahInput).set(0);
        }
    });

    // Tampilkan notifikasi jika ada
    <?php if ($notif) : 
        list($type, $message) = explode('|', $notif);
    ?>
        Swal.fire({
            icon: '<?= $type ?>',
            title: '<?= $message ?>',
            showConfirmButton: false,
            timer: 2000
        });
    <?php endif; ?>
});

function editTunjangan(id, jabatan, nama, jumlah) {
    // Update judul form
    document.getElementById('formTitle').textContent = 'Edit Tunjangan';
    
    // Isi form dengan data yang ada
    document.getElementById('id_tunjangan').value = id;
    document.getElementById('id_jabatan').value = jabatan;
    document.getElementById('nama_tunjangan').value = nama;
    document.getElementById('jumlah_tunjangan').value = formatRupiah(jumlah);
    
    // Scroll ke form
    document.getElementById('formTunjangan').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    // Reset judul form
    document.getElementById('formTitle').textContent = 'Tambah Tunjangan';
    
    // Reset form
    document.getElementById('formTunjangan').reset();
    document.getElementById('id_tunjangan').value = '';
    
    // Reset format rupiah
    const jumlahInput = document.getElementById('jumlah_tunjangan');
    if (jumlahInput._autoNumeric) {
        jumlahInput._autoNumeric.set(0);
    }
}

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID').format(angka);
}
</script>

<?php require_once "../template/footer.php"; ?>
