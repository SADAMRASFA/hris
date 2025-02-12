<?php
include "../connection.php";
require_once "../template/header.php";
require_once "../template/sidebar.php";

// Ambil ID cuti dari URL
$id_cuti = $_GET['id'];

// Ambil data cuti yang akan diedit
$query_cuti = "SELECT * FROM tabel_cuti WHERE id_cuti = $id_cuti";
$result_cuti = mysqli_query($conn, $query_cuti);
$data_cuti = mysqli_fetch_assoc($result_cuti);

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
    
    $query = "UPDATE tabel_cuti SET 
              id_peg = $id_peg,
              id_jenis_cuti = $id_jenis_cuti,
              tanggal_mulai = '$tanggal_mulai',
              tanggal_selesai = '$tanggal_selesai'
              WHERE id_cuti = $id_cuti";
    
    if (mysqli_query($conn, $query)) {
        $notif = "success|Data cuti berhasil diupdate!";
    } else {
        $notif = "error|Gagal mengupdate data: " . mysqli_error($conn);
    }
}
?>

<style>
.form-card {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 30px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

/* Card title styling */
.card-title {
    color: #d45e3a;
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    text-align: center;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
}

/* Form group styling */
.form-label {
    color: #d45e3a;
    font-weight: 500;
    font-size: 1rem;
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    background: rgba(252, 229, 162, 0.2) !important;
    border: 2px solid rgba(232, 107, 68, 0.3) !important;
    border-radius: 12px !important;
    padding: 12px 15px;
    color: #333 !important;
    backdrop-filter: blur(5px);
    transition: all 0.3s ease;
    height: auto;
}

.form-control:focus, .form-select:focus {
    background: rgba(252, 229, 162, 0.3) !important;
    border-color: #e86b44 !important;
    box-shadow: 0 0 0 0.25rem rgba(232, 107, 68, 0.25) !important;
    transform: translateY(-2px);
}

/* Button styling */
.btn {
    padding: 12px 25px;
    border-radius: 12px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-autumn {
    background: linear-gradient(45deg, #e86b44, #fca667) !important;
    color: white !important;
    border: none !important;
    box-shadow: 0 4px 15px rgba(232, 107, 68, 0.3);
}

.btn-autumn:hover {
    background: linear-gradient(45deg, #d45e3a, #e86b44) !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(232, 107, 68, 0.4);
}

.btn-secondary {
    background: rgba(108, 117, 125, 0.8) !important;
    border: none !important;
    backdrop-filter: blur(5px);
}

.btn-secondary:hover {
    background: rgba(108, 117, 125, 1) !important;
    transform: translateY(-2px);
}

/* Icon styling */
.bi {
    margin-right: 8px;
}

/* Form row spacing */
.row.g-3 {
    row-gap: 1.5rem !important;
}

/* Breadcrumb styling */
.breadcrumb {
    background: rgba(255, 255, 255, 0.1);
    padding: 10px 20px;
    border-radius: 10px;
    backdrop-filter: blur(5px);
}

.breadcrumb-item a {
    color: #e86b44;
    text-decoration: none;
    transition: all 0.3s ease;
}

.breadcrumb-item a:hover {
    color: #d45e3a;
    text-decoration: underline;
}

.breadcrumb-item.active {
    color: #d45e3a;
    font-weight: 500;
}

/* Animation for form elements */
.form-control, .form-select, .btn {
    animation: fadeInUp 0.5s ease forwards;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .form-card {
        padding: 20px;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 10px;
    }
}

/* Custom select arrow */
.form-select {
    background-image: linear-gradient(45deg, transparent 50%, #e86b44 50%), 
                      linear-gradient(135deg, #e86b44 50%, transparent 50%) !important;
    background-position: calc(100% - 20px) calc(1em + 2px),
                         calc(100% - 15px) calc(1em + 2px) !important;
    background-size: 5px 5px, 5px 5px !important;
    background-repeat: no-repeat !important;
}

/* Date input styling */
input[type="date"] {
    position: relative;
}

input[type="date"]::-webkit-calendar-picker-indicator {
    background: transparent;
    bottom: 0;
    color: transparent;
    cursor: pointer;
    height: auto;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
    width: auto;
}

input[type="date"]::after {
    content: "📅";
    position: absolute;
    right: 10px;
    color: #e86b44;
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
            <h1>Edit Pengajuan Cuti</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="index.php">Manajemen Cuti</a></li>
                    <li class="breadcrumb-item active">Edit Cuti</li>
                </ol>
            </nav>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="form-card">
                    <h5 class="card-title">Form Edit Cuti</h5>
                    
                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label for="id_peg" class="form-label">Nama Pegawai</label>
                            <select class="form-select" name="id_peg" required>
                                <?php while ($row = mysqli_fetch_assoc($result_pegawai)) { ?>
                                    <option value="<?= $row['id_peg'] ?>" 
                                            <?= ($row['id_peg'] == $data_cuti['id_peg']) ? 'selected' : '' ?>>
                                        <?= $row['nama_peg'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="id_jenis_cuti" class="form-label">Jenis Cuti</label>
                            <select class="form-select" name="id_jenis_cuti" required>
                                <?php while ($row = mysqli_fetch_assoc($result_jenis)) { ?>
                                    <option value="<?= $row['id_jenis_cuti'] ?>"
                                            <?= ($row['id_jenis_cuti'] == $data_cuti['id_jenis_cuti']) ? 'selected' : '' ?>>
                                        <?= $row['nama_jenis_cuti'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" name="tanggal_mulai" 
                                   value="<?= $data_cuti['tanggal_mulai'] ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" name="tanggal_selesai" 
                                   value="<?= $data_cuti['tanggal_selesai'] ?>" required>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-autumn">
                                <i class="bi bi-save"></i> Update
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

    // Validasi tanggal
    document.querySelector('form').addEventListener('submit', function(e) {
        const tanggalMulai = new Date(document.querySelector('[name="tanggal_mulai"]').value);
        const tanggalSelesai = new Date(document.querySelector('[name="tanggal_selesai"]').value);
        
        if (tanggalSelesai < tanggalMulai) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai!',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'btn btn-autumn'
                }
            });
        }
    });
</script>

<?php require_once "../template/footer.php"; ?> 