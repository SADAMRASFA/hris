<?php
include "../connection.php";
require_once "../template/header.php";
require_once "../template/sidebar.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_jenis_cuti = mysqli_real_escape_string($conn, $_POST['nama_jenis_cuti']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    
    // Dapatkan ID terakhir
    $query_last_id = "SELECT MAX(id_jenis_cuti) as last_id FROM jenis_cuti";
    $result_last_id = mysqli_query($conn, $query_last_id);
    $row_last_id = mysqli_fetch_assoc($result_last_id);
    $new_id = $row_last_id['last_id'] + 1;
    
    $query = "INSERT INTO jenis_cuti (id_jenis_cuti, nama_jenis_cuti, keterangan) 
              VALUES ($new_id, '$nama_jenis_cuti', '$keterangan')";
    
    if (mysqli_query($conn, $query)) {
        $notif = "success|Jenis cuti berhasil ditambahkan!";
    } else {
        $notif = "error|Gagal menambahkan jenis cuti: " . mysqli_error($conn);
    }
}
?>

<style>
.form-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
    margin-bottom: 30px;
}

.form-title {
    color: #2c2c2c;
    font-size: 1.8rem;
    font-weight: 600;
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid rgba(232, 107, 68, 0.2);
}

.form-label {
    color: #555;
    font-weight: 500;
    margin-bottom: 8px;
    font-size: 0.95rem;
}

.form-control {
    border-radius: 12px;
    padding: 12px 15px;
    border: 2px solid #e1e1e1;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
}

.form-control:focus {
    border-color: #e86b44;
    box-shadow: 0 0 0 0.2rem rgba(232, 107, 68, 0.25);
}

textarea.form-control {
    min-height: 120px;
    resize: vertical;
}

.btn-group {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
}

.btn {
    padding: 12px 30px;
    border-radius: 50px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-autumn {
    background: linear-gradient(45deg, #e86b44, #fca667);
    color: white;
    border: none;
}

.btn-autumn:hover {
    background: linear-gradient(45deg, #d45e3a, #e86b44);
    transform: translateY(-2px);
    color: white;
    box-shadow: 0 5px 15px rgba(232, 107, 68, 0.3);
}

.btn-secondary {
    background: #6c757d;
    color: white;
    border: none;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
}

.required-field::after {
    content: '*';
    color: #dc3545;
    margin-left: 4px;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    color: #555;
    font-weight: 500;
    font-size: 0.95rem;
}

.form-control {
    display: block;
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    border-radius: 12px;
    border: 2px solid #e1e1e1;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
}

.form-control:focus {
    border-color: #e86b44;
    box-shadow: 0 0 0 0.2rem rgba(232, 107, 68, 0.25);
    outline: none;
}

/* Animasi untuk form elements */
.form-control {
    transform: translateY(10px);
    opacity: 0;
    animation: slideUp 0.5s ease forwards;
}

@keyframes slideUp {
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Delay animasi untuk setiap elemen */
.form-floating:nth-child(1) .form-control {
    animation-delay: 0.1s;
}

.form-floating:nth-child(2) .form-control {
    animation-delay: 0.2s;
}
</style>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Tambah Jenis Cuti</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php">Manajemen Cuti</a></li>
                <li class="breadcrumb-item active">Tambah Jenis Cuti</li>
            </ol>
        </nav>
    </div>

    <div class="form-card">
        <h5 class="form-title">
            <i class="bi bi-calendar-plus me-2"></i>
            Form Tambah Jenis Cuti
        </h5>
        <form method="POST" class="row g-4">
            <div class="col-12">
                <div class="form-group">
                    <label for="nama_jenis_cuti" class="form-label required-field">Nama Jenis Cuti</label>
                    <input type="text" class="form-control" id="nama_jenis_cuti" name="nama_jenis_cuti" 
                           placeholder="Masukkan nama jenis cuti" required>
                </div>
            </div>

            <div class="col-12">
                <div class="form-group">
                    <label for="keterangan" class="form-label required-field">Keterangan</label>
                    <textarea class="form-control" id="keterangan" name="keterangan" 
                              placeholder="Masukkan keterangan jenis cuti" rows="4" required></textarea>
                </div>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-autumn">
                    <i class="bi bi-save"></i> Simpan
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
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
            showConfirmButton: true,
            confirmButtonText: 'OK',
            customClass: {
                confirmButton: 'btn btn-autumn'
            },
            buttonsStyling: false,
            timer: 2000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        }).then((result) => {
            if (icon === 'success') {
                window.location.href = 'index.php';
            }
        });
    <?php endif; ?>

    // Validasi form sebelum submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const namaJenisCuti = document.getElementById('nama_jenis_cuti').value.trim();
        const keterangan = document.getElementById('keterangan').value.trim();
        
        if (!namaJenisCuti || !keterangan) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Semua field harus diisi!',
                showConfirmButton: true,
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'btn btn-autumn'
                },
                buttonsStyling: false,
                showClass: {
                    popup: 'animate__animated animate__shakeX'
                }
            });
        } else {
            // Tampilkan loading saat submit
            Swal.fire({
                title: 'Menyimpan Data...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
    });

    // Konfirmasi sebelum batal
    document.querySelector('.btn-secondary').addEventListener('click', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang sudah diisi akan hilang!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, batalkan!',
            cancelButtonText: 'Tidak, lanjutkan',
            customClass: {
                confirmButton: 'btn btn-secondary me-2',
                cancelButton: 'btn btn-autumn'
            },
            buttonsStyling: false,
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'index.php';
            }
        });
    });
</script>

<!-- Tambahkan CSS untuk animasi -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

<?php require_once "../template/footer.php"; ?> 