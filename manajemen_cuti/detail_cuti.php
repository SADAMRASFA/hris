<?php
include "../connection.php";
require_once "../template/header.php";
require_once "../template/sidebar.php";

// Ambil ID cuti dari URL
$id_cuti = isset($_GET['id']) ? $_GET['id'] : 0;

// Query untuk mengambil detail cuti
$query = "SELECT tc.*, p.nama_peg, p.gender_peg, p.status_peg, p.almt_peg, 
          p.no_telp_peg, p.email_peg, j.nama_jenis_cuti, j.keterangan 
          FROM tabel_cuti tc
          JOIN pegawai p ON tc.id_peg = p.id_peg
          JOIN jenis_cuti j ON tc.id_jenis_cuti = j.id_jenis_cuti
          WHERE tc.id_cuti = $id_cuti";

$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Data Tidak Ditemukan!',
            text: 'Data yang Anda cari tidak ditemukan.',
            confirmButtonText: 'OK'
        }).then((result) => {
            window.location.href = 'index.php';
        });
    </script>
    <?php
    exit;
}

$data_cuti = mysqli_fetch_assoc($result);

// Hitung durasi cuti
$tanggal_mulai = new DateTime($data_cuti['tanggal_mulai']);
$tanggal_selesai = new DateTime($data_cuti['tanggal_selesai']);
$durasi = $tanggal_mulai->diff($tanggal_selesai)->days + 1;
?>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.detail-card {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 30px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.detail-header {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid rgba(232, 107, 68, 0.2);
}

.detail-title {
    color: #d45e3a;
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 10px;
}

.detail-subtitle {
    color: #666;
    font-size: 1.1rem;
}

.info-section {
    background: rgba(252, 229, 162, 0.1);
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
}

.info-section h5 {
    color: #d45e3a;
    font-size: 1.2rem;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-section h5 i {
    font-size: 1.4rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.info-item {
    padding: 15px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 12px;
    border: 1px solid rgba(232, 107, 68, 0.2);
}

.info-label {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.info-value {
    color: #333;
    font-weight: 500;
    font-size: 1.1rem;
}

.status-badge {
    display: inline-block;
    padding: 8px 15px;
    border-radius: 30px;
    font-weight: 500;
    font-size: 0.9rem;
    text-align: center;
}

.status-diajukan {
    background: rgba(255, 193, 7, 0.2);
    color: #ffc107;
}

.status-disetujui {
    background: rgba(40, 167, 69, 0.2);
    color: #28a745;
}

.status-ditolak {
    background: rgba(220, 53, 69, 0.2);
    color: #dc3545;
}

.btn-back {
    background: linear-gradient(45deg, #e86b44, #fca667);
    color: white;
    padding: 10px 25px;
    border-radius: 12px;
    border: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-back:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(232, 107, 68, 0.3);
    color: white;
}

@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Detail Pengajuan Cuti</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php">Manajemen Cuti</a></li>
                <li class="breadcrumb-item active">Detail Cuti</li>
            </ol>
        </nav>
    </div>

    <div class="detail-card">
        <div class="detail-header">
            <div class="detail-title">Detail Pengajuan Cuti</div>
            <div class="detail-subtitle">ID Pengajuan: #<?= $data_cuti['id_cuti'] ?></div>
        </div>

        <div class="info-section">
            <h5><i class="bi bi-person-circle"></i> Informasi Pegawai</h5>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Nama Pegawai</div>
                    <div class="info-value"><?= $data_cuti['nama_peg'] ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Jenis Kelamin</div>
                    <div class="info-value"><?= $data_cuti['gender_peg'] ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status Pegawai</div>
                    <div class="info-value"><?= $data_cuti['status_peg'] ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?= $data_cuti['email_peg'] ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">No. Telepon</div>
                    <div class="info-value"><?= $data_cuti['no_telp_peg'] ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Alamat</div>
                    <div class="info-value"><?= $data_cuti['almt_peg'] ?></div>
                </div>
            </div>
        </div>

        <div class="info-section">
            <h5><i class="bi bi-calendar-check"></i> Detail Cuti</h5>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Jenis Cuti</div>
                    <div class="info-value"><?= $data_cuti['nama_jenis_cuti'] ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Keterangan Cuti</div>
                    <div class="info-value"><?= $data_cuti['keterangan'] ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-badge status-<?= strtolower($data_cuti['status_cuti']) ?>">
                            <?= $data_cuti['status_cuti'] ?>
                        </span>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tanggal Mulai</div>
                    <div class="info-value"><?= date('d/m/Y', strtotime($data_cuti['tanggal_mulai'])) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tanggal Selesai</div>
                    <div class="info-value"><?= date('d/m/Y', strtotime($data_cuti['tanggal_selesai'])) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Durasi</div>
                    <div class="info-value"><?= $durasi ?> Hari</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tanggal Pengajuan</div>
                    <div class="info-value"><?= date('d/m/Y', strtotime($data_cuti['tanggal_pengajuan'])) ?></div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="index.php" class="btn-back">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</main>

<?php require_once "../template/footer.php"; ?> 