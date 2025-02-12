<?php
include "../connection.php";
require_once "../template/header.php";
require_once "../template/sidebar.php";

// Ambil ID cuti dari URL
$id_cuti = $_GET['id'];

// Ambil detail cuti
$query_cuti = "SELECT tc.*, p.nama_peg, j.nama_jenis_cuti 
               FROM tabel_cuti tc
               JOIN pegawai p ON tc.id_peg = p.id_peg
               JOIN jenis_cuti j ON tc.id_jenis_cuti = j.id_jenis_cuti
               WHERE tc.id_cuti = $id_cuti";
$result_cuti = mysqli_query($conn, $query_cuti);
$data_cuti = mysqli_fetch_assoc($result_cuti);

// Proses persetujuan/penolakan
if (isset($_POST['submit'])) {
    $status = $_POST['status'];
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    
    // Update status cuti
    $query = "UPDATE tabel_cuti SET 
              status_cuti = '$status'
              WHERE id_cuti = $id_cuti";
    
    if (mysqli_query($conn, $query)) {
        $notif = "success|Pengajuan cuti berhasil " . strtolower($status) . "!";
    } else {
        $notif = "error|Gagal memperbarui status: " . mysqli_error($conn);
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

.info-group {
    background: rgba(252, 229, 162, 0.2);
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 20px;
    border: 2px solid rgba(232, 107, 68, 0.3);
}

.info-label {
    color: #d45e3a;
    font-weight: 600;
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.info-value {
    color: #333;
    font-size: 1.1rem;
    font-weight: 500;
}

.status-buttons {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.status-btn {
    flex: 1;
    padding: 15px;
    border-radius: 12px;
    border: none;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-approve {
    background: linear-gradient(45deg, #4CAF50, #81C784);
    color: white;
}

.btn-reject {
    background: linear-gradient(45deg, #f44336, #e57373);
    color: white;
}

.status-btn.active {
    transform: scale(0.95);
    box-shadow: inset 0 2px 5px rgba(0,0,0,0.2);
}

.btn-autumn {
    background: linear-gradient(45deg, #e86b44, #fca667) !important;
    color: white !important;
    border: none !important;
    box-shadow: 0 4px 15px rgba(232, 107, 68, 0.3);
    padding: 12px 25px;
    border-radius: 12px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-autumn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(232, 107, 68, 0.4);
}

textarea.form-control {
    background: rgba(252, 229, 162, 0.2) !important;
    border: 2px solid rgba(232, 107, 68, 0.3) !important;
    border-radius: 12px !important;
    min-height: 120px;
    padding: 15px;
}

textarea.form-control:focus {
    background: rgba(252, 229, 162, 0.3) !important;
    border-color: #e86b44 !important;
    box-shadow: 0 0 0 0.25rem rgba(232, 107, 68, 0.25) !important;
}

/* Animasi */
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

.animate-fade {
    animation: fadeInUp 0.5s ease forwards;
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
            <h1>Persetujuan Cuti</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="index.php">Manajemen Cuti</a></li>
                    <li class="breadcrumb-item active">Persetujuan Cuti</li>
                </ol>
            </nav>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="form-card animate-fade">
                    <h5 class="card-title text-center mb-4">Detail Pengajuan Cuti</h5>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label">Nama Pegawai</div>
                                <div class="info-value"><?= $data_cuti['nama_peg'] ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label">Jenis Cuti</div>
                                <div class="info-value"><?= $data_cuti['nama_jenis_cuti'] ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label">Tanggal Mulai</div>
                                <div class="info-value"><?= date('d/m/Y', strtotime($data_cuti['tanggal_mulai'])) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label">Tanggal Selesai</div>
                                <div class="info-value"><?= date('d/m/Y', strtotime($data_cuti['tanggal_selesai'])) ?></div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" class="animate-fade" style="animation-delay: 0.2s;">
                        <div class="status-buttons mb-4">
                            <button type="button" class="status-btn btn-approve" onclick="setStatus('Disetujui')">
                                <i class="bi bi-check-circle"></i> Setujui
                            </button>
                            <button type="button" class="status-btn btn-reject" onclick="setStatus('Ditolak')">
                                <i class="bi bi-x-circle"></i> Tolak
                            </button>
                        </div>

                        <input type="hidden" name="status" id="status_cuti">

                        <div class="mb-4">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" name="keterangan" rows="4" 
                                      placeholder="Tambahkan keterangan atau alasan..."></textarea>
                        </div>

                        <div class="text-center">
                            <button type="submit" name="submit" class="btn btn-autumn me-2">
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

    // Validasi sebelum submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const status = document.getElementById('status_cuti').value;
        
        if (!status) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Silakan pilih status persetujuan terlebih dahulu!',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'btn btn-autumn'
                }
            });
            return false;
        }
        
        return true;
    });

    function setStatus(status) {
        document.getElementById('status_cuti').value = status;
        
        // Reset semua button
        document.querySelectorAll('.status-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Aktifkan button yang dipilih
        if (status === 'Disetujui') {
            document.querySelector('.btn-approve').classList.add('active');
        } else {
            document.querySelector('.btn-reject').classList.add('active');
        }
    }

    // Animasi daun
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
</script>

<?php require_once "../template/footer.php"; ?> 