<?php
include "../connection.php";
require_once "../template/header.php";
require_once "../template/sidebar.php";

// Ambil data pegawai untuk dropdown
$query_pegawai = "SELECT id_peg, nama_peg FROM pegawai ORDER BY nama_peg";
$result_pegawai = mysqli_query($conn, $query_pegawai);

// Ambil data jenis cuti untuk dropdown
$query_jenis = "SELECT id_jenis_cuti, nama_jenis_cuti FROM jenis_cuti ORDER BY nama_jenis_cuti";
$result_jenis = mysqli_query($conn, $query_jenis);

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_peg = $_POST['id_peg'];
    $id_jenis_cuti = $_POST['id_jenis_cuti'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $tanggal_pengajuan = date('Y-m-d');
    
    $query_last_id = "SELECT MAX(id_cuti) as last_id FROM tabel_cuti";
    $result_last_id = mysqli_query($conn, $query_last_id);
    $row_last_id = mysqli_fetch_assoc($result_last_id);
    $last_id = $row_last_id['last_id'];
    $new_id = $last_id + 1;

    $query = "INSERT INTO tabel_cuti (id_cuti, id_peg, id_jenis_cuti, tanggal_mulai, tanggal_selesai, status_cuti, tanggal_pengajuan) 
              VALUES ($new_id, $id_peg, $id_jenis_cuti, '$tanggal_mulai', '$tanggal_selesai', 'Diajukan', '$tanggal_pengajuan')";
    
    if (mysqli_query($conn, $query)) {
        $notif = "success|Pengajuan cuti berhasil ditambahkan!";
    } else {
        $notif = "error|Gagal menambahkan pengajuan cuti: " . mysqli_error($conn);
    }
}
?>

<style>
:root {
    --primary: #34495e;
    --secondary: #2c3e50;
    --accent: #3498db;
    --bg-pattern: #f8f9fa;
    --card-bg: rgba(255, 255, 255, 0.95);
}

/* Background Pattern */
.main {
    background-color: var(--bg-pattern);
    background-image: 
        radial-gradient(#3498db 1px, transparent 1px),
        radial-gradient(#3498db 1px, transparent 1px);
    background-size: 40px 40px;
    background-position: 0 0, 20px 20px;
    background-attachment: fixed;
    position: relative;
    overflow: hidden;
}

/* Floating Elements */
.main::before,
.main::after {
    content: '';
    position: fixed;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: linear-gradient(45deg, #3498db22, #2ecc7122);
    filter: blur(40px);
    z-index: 0;
}

.main::before {
    top: -100px;
    right: -100px;
}

.main::after {
    bottom: -100px;
    left: -100px;
}

.form-card {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 
        0 10px 30px rgba(0,0,0,0.1),
        0 1px 8px rgba(0,0,0,0.08);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

/* Decorative Elements */
.form-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, #3498db, #2ecc71, #f1c40f);
}

.form-card::after {
    content: '';
    position: absolute;
    top: 6px;
    right: 0;
    width: 100px;
    height: 100px;
    background: linear-gradient(45deg, #3498db22, #2ecc7122);
    border-radius: 0 0 0 100%;
    z-index: 0;
}

/* Form Elements */
.form-label {
    color: var(--primary);
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 0.7rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-label i {
    color: var(--accent);
    font-size: 1.1rem;
}

.form-control, .form-select {
    border: 2px solid rgba(52, 152, 219, 0.2);
    border-radius: 12px;
    padding: 0.9rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

.form-control:focus, .form-select:focus {
    border-color: var(--accent);
    box-shadow: 
        inset 0 2px 4px rgba(0,0,0,0.05),
        0 0 0 4px rgba(52, 152, 219, 0.15);
    background: white;
}

/* Card Title with Icon */
.card-title {
    color: var(--primary);
    font-size: 1.6rem;
    font-weight: 700;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid rgba(52, 152, 219, 0.2);
    position: relative;
    display: flex;
    align-items: center;
    gap: 12px;
}

.card-title i {
    color: var(--accent);
    font-size: 1.8rem;
}

/* Enhanced Buttons */
.btn-autumn {
    background: linear-gradient(135deg, #3498db, #2ecc71);
    color: white;
    padding: 1rem 2rem;
    border-radius: 12px;
    border: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    position: relative;
    overflow: hidden;
}

.btn-autumn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.2),
        transparent
    );
    transition: 0.5s;
}

.btn-autumn:hover::before {
    left: 100%;
}

.btn-autumn:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
}

/* Form Row Enhancement */
.row > div {
    position: relative;
    z-index: 1;
}

/* Animated Background */
@keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .form-card {
        padding: 1.5rem;
        margin: 1rem;
    }
    
    .card-title {
        font-size: 1.3rem;
    }
    
    .btn-autumn {
        width: 100%;
        justify-content: center;
    }
}

/* Custom Select Styling */
.form-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%233498db' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 1em;
    padding-right: 2.5rem;
}

/* Date Input Enhancement */
input[type="date"] {
    position: relative;
    padding-right: 40px;
}

input[type="date"]::-webkit-calendar-picker-indicator {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%233498db' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'%3E%3C/rect%3E%3Cline x1='16' y1='2' x2='16' y2='6'%3E%3C/line%3E%3Cline x1='8' y1='2' x2='8' y2='6'%3E%3C/line%3E%3Cline x1='3' y1='10' x2='21' y2='10'%3E%3C/line%3E%3C/svg%3E");
    cursor: pointer;
}

/* Table Container Enhancement */
.table-container {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 
        0 10px 30px rgba(0,0,0,0.1),
        0 1px 8px rgba(0,0,0,0.08);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

/* Decorative Elements for Table */
.table-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #3498db, #2ecc71, #f1c40f);
}

.table-container::after {
    content: '';
    position: absolute;
    top: 5px;
    right: 0;
    width: 150px;
    height: 150px;
    background: linear-gradient(45deg, #3498db15, #2ecc7115);
    border-radius: 0 0 0 100%;
    z-index: 0;
}

/* Table Styling */
.table {
    position: relative;
    z-index: 1;
}

.table thead th {
    background: rgba(52, 152, 219, 0.1);
    color: #2c3e50;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    padding: 1rem;
    border: none;
    position: relative;
}

.table thead th:first-child {
    border-radius: 10px 0 0 10px;
}

.table thead th:last-child {
    border-radius: 0 10px 10px 0;
}

.table tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid rgba(52, 152, 219, 0.1);
}

.table tbody tr:hover {
    background: rgba(52, 152, 219, 0.05);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.table tbody td {
    padding: 1rem;
    color: #34495e;
    vertical-align: middle;
}

/* Status Badge Styling */
.badge {
    padding: 0.5rem 1rem;
    border-radius: 30px;
    font-weight: 500;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.badge-pending {
    background: linear-gradient(135deg, #f1c40f20, #f39c1220);
    color: #f39c12;
    border: 1px solid #f39c1240;
}

.badge-approved {
    background: linear-gradient(135deg, #2ecc7120, #27ae6020);
    color: #27ae60;
    border: 1px solid #27ae6040;
}

.badge-rejected {
    background: linear-gradient(135deg, #e74c3c20, #c0392b20);
    color: #c0392b;
    border: 1px solid #c0392b40;
}

/* Action Buttons */
.btn-action {
    width: 35px;
    height: 35px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    margin: 0 3px;
    border: none;
    background: rgba(52, 152, 219, 0.1);
    color: #3498db;
}

.btn-action:hover {
    background: #3498db;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}

/* Table Header Title */
.table-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    position: relative;
    z-index: 1;
}

.table-title h5 {
    color: #2c3e50;
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.table-title h5 i {
    color: #3498db;
    font-size: 1.4rem;
}

/* Empty State */
.table-empty {
    text-align: center;
    padding: 3rem;
    color: #7f8c8d;
}

.table-empty i {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
    opacity: 0.5;
}

/* Responsive Table */
@media (max-width: 768px) {
    .table-container {
        padding: 1rem;
        border-radius: 15px;
    }

    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
    }

    .table thead th {
        white-space: nowrap;
    }

    .btn-action {
        width: 30px;
        height: 30px;
    }
}

/* Loading State */
.table-loading {
    position: relative;
    min-height: 200px;
}

.table-loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: #3498db;
}

/* Hover Effects */
.table tbody tr:hover td {
    color: #2c3e50;
}

.table tbody tr:hover .badge {
    transform: scale(1.05);
}
</style>

<main id="main" class="main">
    <!-- Background Elements -->
    <div class="background">
        <div class="tree"></div>
        <div class="tree"></div>
        <div class="tree"></div>
        <div class="bush"></div>
        <div class="bush"></div>
        <div class="bush"></div>
        <div class="cloud"></div>
        <div class="cloud"></div>
    </div>

    <div class="content-wrapper">
        <div class="pagetitle">
            <h1>Pengajuan Cuti</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="index.php">Manajemen Cuti</a></li>
                    <li class="breadcrumb-item active">Tambah Cuti</li>
                </ol>
            </nav>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="form-card">
                    <h5 class="card-title">
                        <i class="bi bi-calendar-plus"></i>
                        Form Pengajuan Cuti
                    </h5>
                    
                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label for="id_peg" class="form-label">
                                <i class="bi bi-person"></i>
                                Nama Pegawai
                            </label>
                            <select class="form-select" name="id_peg" required>
                                <option value="">Pilih Pegawai</option>
                                <?php while ($row = mysqli_fetch_assoc($result_pegawai)) { ?>
                                    <option value="<?= $row['id_peg'] ?>"><?= $row['nama_peg'] ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="id_jenis_cuti" class="form-label">Jenis Cuti</label>
                            <select class="form-select" name="id_jenis_cuti" required>
                                <option value="">Pilih jenis cuti</option>
                                <?php while ($row = mysqli_fetch_assoc($result_jenis)) { ?>
                                    <option value="<?= $row['id_jenis_cuti'] ?>"><?= $row['nama_jenis_cuti'] ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" name="tanggal_mulai" required>
                        </div>

                        <div class="col-md-6">
                            <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" name="tanggal_selesai" required>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-autumn">
                                <i class="bi bi-save"></i> Simpan
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    <?php if (isset($notif)): ?>
        const [icon, message] = "<?= $notif ?>".split('|');
        Swal.fire({
            icon: icon,
            title: icon === 'success' ? 'Berhasil!' : 'Gagal!',
            text: message,
            confirmButtonText: 'OK',
            customClass: {
                confirmButton: 'btn btn-autumn',
                popup: 'animated bounceInDown'
            },
            backdrop: `
                rgba(0,0,123,0.4)
                url("/images/nyan-cat.gif")
                left top
                no-repeat
            `
        }).then((result) => {
            if (result.isConfirmed && icon === 'success') {
                window.location.href = 'index.php';
            }
        });
    <?php endif; ?>
</script>

<script>
// Script untuk animasi daun
function createLeaf() {
    const leaf = document.createElement('div');
    leaf.classList.add('leaf');
    leaf.style.left = Math.random() * 100 + 'vw';
    leaf.style.animationDuration = Math.random() * 5 + 5 + 's';
    leaf.style.animationDelay = Math.random() * 5 + 's';
    document.getElementById('main').appendChild(leaf);

    setTimeout(() => {
        leaf.remove();
    }, 10000);
}

setInterval(createLeaf, 500);

// Validasi tanggal
document.querySelector('form').addEventListener('submit', function(e) {
    const tanggalMulai = new Date(document.querySelector('[name="tanggal_mulai"]').value);
    const tanggalSelesai = new Date(document.querySelector('[name="tanggal_selesai"]').value);
    
    if (tanggalSelesai < tanggalMulai) {
        e.preventDefault();
        alert('Tanggal selesai tidak boleh lebih awal dari tanggal mulai!');
    }
});
</script>

<?php require_once "../template/footer.php"; ?> 