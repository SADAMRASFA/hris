<?php
// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "../connection.php";

// Proses hapus harus di paling atas file
if (isset($_GET['hapus'])) {
    header('Content-Type: application/json');
    
    try {
        $id_potongan = $_GET['hapus'];
        
        // Mulai transaction
        $conn->begin_transaction();
        
        // Hapus dari penggajian_potongan terlebih dahulu
        $query_relasi = "DELETE FROM penggajian_potongan WHERE id_potongan = ?";
        $stmt_relasi = $conn->prepare($query_relasi);
        $stmt_relasi->bind_param("i", $id_potongan);
        $stmt_relasi->execute();
        
        // Kemudian hapus dari tabel potongan_gaji
        $query = "DELETE FROM potongan_gaji WHERE id_potongan = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_potongan);
        
        if ($stmt->execute()) {
            $conn->commit();
            echo json_encode([
                'status' => 'success',
                'message' => 'Data berhasil dihapus'
            ]);
        } else {
            throw new Exception("Gagal menghapus data");
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    
    exit;
}

// Setelah proses hapus, baru include template
require_once "../template/header.php";
require_once "../template/sidebar.php";

$notif = "";
$search = isset($_GET['search']) ? $_GET['search'] : "";

// Proses Tambah/Edit Potongan
if (isset($_POST['submit_potongan'])) {
    $id_penggajian = $_POST['id_penggajian'];
    $nama_potongan = $_POST['nama_potongan'];
    $jumlah = str_replace('.', '', $_POST['jumlah']); // Bersihkan format angka
    $id_potongan = $_POST['id_potongan'] ?? '';

    try {
        if ($id_potongan) {
            // Proses Edit
            $query = "UPDATE potongan_gaji SET nama_potongan = ?, jumlah = ? WHERE id_potongan = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sdi", $nama_potongan, $jumlah, $id_potongan);
            
            if ($stmt->execute()) {
                // Update relasi
                $query_relasi = "UPDATE penggajian_potongan SET id_penggajian = ? WHERE id_potongan = ?";
                $stmt_relasi = $conn->prepare($query_relasi);
                $stmt_relasi->bind_param("ii", $id_penggajian, $id_potongan);
                $stmt_relasi->execute();
                
                $notif = "success|Data potongan berhasil diupdate!";
            } else {
                throw new Exception("Gagal mengupdate data!");
            }
        } else {
            // Proses Tambah
            $query = "INSERT INTO potongan_gaji (nama_potongan, jumlah) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sd", $nama_potongan, $jumlah);
            
            if ($stmt->execute()) {
                $id_potongan = $stmt->insert_id;
                
                // Tambah relasi
                $query_relasi = "INSERT INTO penggajian_potongan (id_penggajian, id_potongan) VALUES (?, ?)";
                $stmt_relasi = $conn->prepare($query_relasi);
                $stmt_relasi->bind_param("ii", $id_penggajian, $id_potongan);
                $stmt_relasi->execute();
                
                $notif = "success|Data potongan berhasil ditambahkan!";
            } else {
                throw new Exception("Gagal menambah data!");
            }
        }
    } catch (Exception $e) {
        $notif = "error|" . $e->getMessage();
    }
}

// Di awal file, setelah session_start()
$notif = "";
if (isset($_SESSION['notif'])) {
    $notif = $_SESSION['notif'];
    unset($_SESSION['notif']); // Hapus notif setelah ditampilkan
}

// Di bagian atas file, setelah koneksi database
$limit = 5; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Query dasar untuk menghitung total records
$count_query = "SELECT COUNT(*) as total FROM potongan_gaji pot
                LEFT JOIN penggajian_potongan pp ON pot.id_potongan = pp.id_potongan
                LEFT JOIN penggajian p ON pp.id_penggajian = p.id_penggajian
                LEFT JOIN pegawai peg ON p.id_peg = peg.id_peg";

// Query dasar untuk mengambil data
$query_potongan = "SELECT 
    pot.id_potongan,
    pot.nama_potongan,
    pot.jumlah,
    p.id_penggajian,
    peg.nama_peg,
    peg.id_peg
FROM 
    potongan_gaji pot
    LEFT JOIN penggajian_potongan pp ON pot.id_potongan = pp.id_potongan
    LEFT JOIN penggajian p ON pp.id_penggajian = p.id_penggajian
    LEFT JOIN pegawai peg ON p.id_peg = peg.id_peg";

// Tambahkan kondisi search jika ada
if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $where_clause = " WHERE pot.id_potongan LIKE '%$search%' 
                      OR pot.nama_potongan LIKE '%$search%'";
    $count_query .= $where_clause;
    $query_potongan .= $where_clause;
}

// Hitung total records untuk pagination
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $limit);

// Tambahkan ordering dan limit
$query_potongan .= " ORDER BY pot.id_potongan DESC LIMIT $offset, $limit";

// Eksekusi query utama
$result_potongan = mysqli_query($conn, $query_potongan);
if (!$result_potongan) {
    die("Query error: " . mysqli_error($conn));
}

// Query untuk dropdown pegawai
$query_penggajian = "SELECT DISTINCT p.id_penggajian, pg.nama_peg 
                     FROM penggajian p 
                     JOIN pegawai pg ON pg.id_peg = p.id_peg 
                     ORDER BY pg.nama_peg ASC";
$result_penggajian = mysqli_query($conn, $query_penggajian);

if (!$result_penggajian) {
    die("Error query penggajian: " . mysqli_error($conn));
}

?>

<style>
:root {
    --primary: #2563eb;
    --primary-dark: #1e40af;
    --secondary: #64748b;
    --success: #059669;
    --info: #0284c7;
    --warning: #d97706;
    --danger: #dc2626;
    --light: #f8fafc;
    --dark: #1e293b;
    --gradient-1: linear-gradient(135deg, #2563eb, #1e40af);
    --gradient-2: linear-gradient(135deg, #64748b, #475569);
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
    padding: 12px 16px;
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
    padding: 12px 16px;
}

.table tbody td {
    padding: 12px 16px;
    vertical-align: middle;
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
}

.btn-primary {
    background: var(--gradient-1);
    border: none;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
}

.btn-secondary {
    background: var(--gradient-2);
    border: none;
    box-shadow: 0 4px 12px rgba(100, 116, 139, 0.2);
}

.btn-danger {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    border: none;
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
    margin-top: 20px;
}

.page-link {
    color: var(--primary);
    padding: 8px 16px;
    border: 1px solid rgba(37, 99, 235, 0.1);
    margin: 0 3px;
    border-radius: 8px;
    transition: all 0.3s ease;
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
}

/* Money Input */
.money {
    font-family: 'Roboto Mono', monospace;
    letter-spacing: 0.5px;
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
}
</style>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Potongan Gaji</h1>
    </div>

    <section class="section">
        <div class="row mb-3">
            <div class="col-lg-12">
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Form Tambah Potongan -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title" id="formTitle">Form Tambah Potongan Gaji</h5>
                        <form method="POST" id="formPotongan">
                            <input type="hidden" name="id_potongan" id="id_potongan">
                            <div class="mb-3">
                                <label for="id_penggajian" class="form-label">Pilih Pegawai</label>
                                <select class="form-control" id="id_penggajian" name="id_penggajian" required>
                                    <option value="">-- Pilih Pegawai --</option>
                                    <?php while ($row = mysqli_fetch_assoc($result_penggajian)) : ?>
                                        <option value="<?= $row['id_penggajian'] ?>">
                                            <?= htmlspecialchars($row['nama_peg']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="nama_potongan" class="form-label">Nama Potongan</label>
                                <input type="text" class="form-control" id="nama_potongan" name="nama_potongan" required>
                            </div>
                            <div class="mb-3">
                                <label for="jumlah" class="form-label">Jumlah Potongan</label>
                                <input type="text" class="form-control" id="jumlah" name="jumlah" required>
                            </div>
                            <button type="submit" name="submit_potongan" class="btn btn-primary">Simpan</button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">Batal</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabel Daftar Potongan -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Daftar Potongan Gaji</h5>

                        <!-- Form Search -->
                        <div class="row mb-3">
                            <div class="col-md-7">
                                <form method="GET" class="mb-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan ID atau Nama potongan..." value="<?= htmlspecialchars($search) ?>">
                                        <button class="btn btn-primary" type="submit">Cari</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>ID Penggajian</th>
                                        <th>Nama Potongan</th>
                                        <th>Jumlah</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = $offset + 1;
                                    while ($row = mysqli_fetch_assoc($result_potongan)) : 
                                    ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= $row['id_penggajian'] ? $row['id_penggajian'] : '-' ?></td>
                                            <td><?= htmlspecialchars($row['nama_potongan']) ?></td>
                                            <td>Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></td>
                                            <td>
                                                <button type="button" 
                                                        class="btn btn-warning btn-sm"
                                                        onclick="editPotongan(
                                                            '<?= $row['id_potongan'] ?>', 
                                                            '<?= $row['id_penggajian'] ?>', 
                                                            '<?= htmlspecialchars($row['nama_potongan'], ENT_QUOTES) ?>', 
                                                            '<?= $row['jumlah'] ?>'
                                                        )">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm"
                                                        onclick="hapusPotongan('<?= $row['id_potongan'] ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>

                            <!-- Pagination -->
                            <?php if ($total_pages > 0): ?>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <nav aria-label="Page navigation">
                                            <ul class="pagination justify-content-center">
                                                <!-- Previous -->
                                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                                    <a class="page-link border-0" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">Previous</a>
                                                </li>
                                                
                                                <!-- Current Page Number -->
                                                <li class="page-item active">
                                                    <a class="page-link" href="#"><?= $page ?></a>
                                                </li>
                                                
                                                <!-- Next -->
                                                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                                    <a class="page-link border-0" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">Next</a>
                                                </li>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>

                                <style>
                                .page-link {
                                    text-decoration: none;
                                    border: none;
                                }
                                .page-link:hover {
                                    text-decoration: none;
                                    border: none;
                                }
                                </style>
                            <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/autonumeric@4.1.0"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function editPotongan(id, penggajian, nama, jumlah) {
        // Update judul form
        document.getElementById('formTitle').textContent = 'Edit Potongan Gaji';
        
        // Isi form dengan data yang ada
        document.getElementById('id_potongan').value = id;
        document.getElementById('id_penggajian').value = penggajian;
        document.getElementById('nama_potongan').value = nama;
        document.getElementById('jumlah').value = jumlah;
        
        // Scroll ke form
        document.getElementById('formPotongan').scrollIntoView({ behavior: 'smooth' });
    }

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    function resetForm() {
        // Reset judul form
        document.getElementById('formTitle').textContent = 'Form Tambah Potongan Gaji';
        
        // Reset form
        document.getElementById('formPotongan').reset();
        document.getElementById('id_potongan').value = '';
        document.getElementById('jumlah').value = '';
    }

    // Format input jumlah saat diketik
    document.getElementById('jumlah').addEventListener('input', function(e) {
        let value = this.value.replace(/\D/g, '');
        this.value = formatRupiah(value);
    });

    // Fungsi untuk menghapus potongan
    function hapusPotongan(id) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: "Apakah Anda yakin ingin menghapus data ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch(`potong_gaji.php?hapus=${id}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Terjadi kesalahan saat menghapus data');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: error.message
                        });
                    });
            }
        });
    }

    // Inisialisasi AutoNumeric saat dokumen dimuat
    document.addEventListener('DOMContentLoaded', function() {
        // Format input jumlah dengan AutoNumeric
        const jumlahInput = document.getElementById('jumlah');
        new AutoNumeric(jumlahInput, {
            digitGroupSeparator: '.',
            decimalPlaces: 0,
            minimumValue: '0',
            allowDecimalPadding: false,
            modifyValueOnWheel: false
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
</script>

<?php require_once "../template/footer.php"; ?>
