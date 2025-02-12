<?php
include "../connection.php";
require_once "../template/header.php";
require_once "../template/sidebar.php";

// Konfigurasi pagination
$items_per_page = 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start_from = ($page-1) * $items_per_page;

// Tambahkan kondisi pencarian
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where = '';
if (!empty($search)) {
    $where = "WHERE p.nama_peg LIKE '%$search%' 
              OR j.nama_jenis_cuti LIKE '%$search%'
              OR tc.status_cuti LIKE '%$search%'";
}

// Query untuk mengambil data cuti dengan join
$query = "SELECT tc.*, p.nama_peg, j.nama_jenis_cuti 
          FROM tabel_cuti tc
          JOIN pegawai p ON tc.id_peg = p.id_peg
          JOIN jenis_cuti j ON tc.id_jenis_cuti = j.id_jenis_cuti
          $where
          ORDER BY tc.tanggal_pengajuan DESC
          LIMIT $start_from, $items_per_page";
$result = mysqli_query($conn, $query);

// Query untuk total records
$query_total = "SELECT COUNT(*) as total FROM tabel_cuti";
$result_total = mysqli_query($conn, $query_total);
$row_total = mysqli_fetch_assoc($result_total);
$total_pages = ceil($row_total['total'] / $items_per_page);
?>

<style>
:root {
    --bg-dark: #f5f5f5;
    --card-1: #fff6e6;  /* Soft Orange */
    --card-2: #e6f3ff;  /* Soft Blue */
    --card-3: #f0ffe6;  /* Soft Green */
    --icon-1: #ff9f43;  /* Deep Orange */
    --icon-2: #54a0ff;  /* Deep Blue */
    --icon-3: #4caf50;  /* Deep Green */
    --text-primary: #2c2c2c;
    --text-secondary: #666666;
}

/* Card Stats Styling */
.stats-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 30px;
    perspective: 1000px;  /* Add perspective for 3D effect */
}

.stats-card {
    border-radius: 16px;
    padding: 24px;
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    border: none;
    box-shadow: 
        0 5px 15px rgba(0, 0, 0, 0.08),
        0 15px 35px rgba(0, 0, 0, 0.05),
        0 50px 100px rgba(0, 0, 0, 0.03);
    position: relative;
    overflow: hidden;
    z-index: 1;
    transform-style: preserve-3d;
    transform: translateZ(0) rotateX(0) rotateY(0);
}

.stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    opacity: 0.1;
    z-index: -1;
    background-image: 
        radial-gradient(circle at 20% 20%, rgba(0,0,0,0.05) 0%, transparent 20%),
        radial-gradient(circle at 80% 80%, rgba(0,0,0,0.05) 0%, transparent 20%),
        linear-gradient(45deg, transparent 48%, rgba(0,0,0,0.02) 49%, rgba(0,0,0,0.02) 51%, transparent 52%),
        linear-gradient(-45deg, transparent 48%, rgba(0,0,0,0.02) 49%, rgba(0,0,0,0.02) 51%, transparent 52%);
    background-size: 30px 30px, 30px 30px, 20px 20px, 20px 20px;
    transform: translateZ(-1px);
}

/* 3D hover effect */
.stats-card:hover {
    transform: translateY(-10px) rotateX(5deg) rotateY(-5deg);
    box-shadow: 
        20px 20px 60px rgba(0, 0, 0, 0.1),
        -20px -20px 60px rgba(255, 255, 255, 0.8),
        0 4px 15px rgba(0, 0, 0, 0.1);
}

/* Lighting effect */
.stats-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.3) 0%,
        rgba(255, 255, 255, 0) 50%,
        rgba(0, 0, 0, 0.05) 100%
    );
    z-index: 1;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.stats-card:hover::after {
    opacity: 1;
}

/* Card specific styling */
.stats-card:nth-child(1) {
    background: linear-gradient(145deg, var(--card-1), #ffffff);
}

.stats-card:nth-child(2) {
    background: linear-gradient(145deg, var(--card-2), #ffffff);
}

.stats-card:nth-child(3) {
    background: linear-gradient(145deg, var(--card-3), #ffffff);
}

/* Content styling with 3D effect */
.stats-card h5 {
    color: var(--text-secondary);
    font-size: 1.1rem;
    margin-bottom: 12px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    transform: translateZ(20px);
    position: relative;
}

.stats-card h2 {
    font-size: 2.4rem;
    font-weight: 600;
    margin: 0;
    transform: translateZ(30px);
    position: relative;
}

/* Enhanced icon styling */
.stats-card:nth-child(1) h5 i {
    color: var(--icon-1);
    filter: drop-shadow(0 4px 6px rgba(255,159,67,0.3));
    transform: translateZ(25px);
}

.stats-card:nth-child(2) h5 i {
    color: var(--icon-2);
    filter: drop-shadow(0 4px 6px rgba(84,160,255,0.3));
    transform: translateZ(25px);
}

.stats-card:nth-child(3) h5 i {
    color: var(--icon-3);
    filter: drop-shadow(0 4px 6px rgba(76,175,80,0.3));
    transform: translateZ(25px);
}

/* Enhanced number styling */
.stats-card:nth-child(1) h2 {
    color: var(--icon-1);
    text-shadow: 
        2px 2px 4px rgba(255,159,67,0.2),
        -2px -2px 4px rgba(255,255,255,0.8);
}

.stats-card:nth-child(2) h2 {
    color: var(--icon-2);
    text-shadow: 
        2px 2px 4px rgba(84,160,255,0.2),
        -2px -2px 4px rgba(255,255,255,0.8);
}

.stats-card:nth-child(3) h2 {
    color: var(--icon-3);
    text-shadow: 
        2px 2px 4px rgba(76,175,80,0.2),
        -2px -2px 4px rgba(255,255,255,0.8);
}

/* Decorative dots with 3D */
.stats-card .dots {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 60px;
    height: 60px;
    opacity: 0.2;
    background-image: 
        radial-gradient(circle, currentColor 2px, transparent 2px);
    background-size: 10px 10px;
    transform: translateZ(15px);
}

/* Table Styling */
.table-container {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 25px;
    margin-top: 20px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.table {
    width: 100%;
    margin-bottom: 0;
}

.table thead th {
    background: rgba(232, 107, 68, 0.1);
    color: #d45e3a;
    font-weight: 600;
    border: none;
    padding: 15px;
}

.table tbody td {
    padding: 15px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(232, 107, 68, 0.1);
}

/* Status Badge */
.status-badge {
    padding: 8px 15px;
    border-radius: 30px;
    font-weight: 500;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.status-diajukan { 
    background: linear-gradient(45deg, #ffd700, #ffc107);
    color: #000; 
}

.status-disetujui { 
    background: linear-gradient(45deg, #4CAF50, #81C784);
    color: #fff; 
}

.status-ditolak { 
    background: linear-gradient(45deg, #f44336, #e57373);
    color: #fff; 
}

/* Action Buttons */
.btn-action {
    padding: 8px 20px;
    border-radius: 50px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
    border: none;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.btn-action i {
    font-size: 1.1rem;
}

.btn-add-type {
    background: linear-gradient(45deg, #28a745, #20c997);
    color: white;
}

.btn-add-type:hover {
    background: linear-gradient(45deg, #218838, #1e7e34);
    color: white;
}

.btn-add-leave {
    background: linear-gradient(45deg, #e86b44, #fca667);
    color: white;
}

.btn-add-leave:hover {
    background: linear-gradient(45deg, #d45e3a, #e86b44);
    color: white;
}

.action-buttons {
    display: flex;
    gap: 15px;
    align-items: center;
}

/* Animasi hover */
@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}

.btn-action:hover {
    animation: pulse 1s infinite;
}

/* Responsive Design */
@media (max-width: 768px) {
    .stats-container {
        grid-template-columns: 1fr;
        perspective: none;
    }
    
    .stats-card {
        transform: none !important;
    }
    
    .table-responsive {
        border-radius: 15px;
    }
}

/* Pagination Container */
.pagination-container {
    margin-top: 2rem;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    position: relative;
    z-index: 1;
}

/* Pagination Style */
.pagination {
    display: flex;
    gap: 8px;
    padding: 10px;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: 50px;
    box-shadow: 
        0 10px 30px rgba(0,0,0,0.05),
        0 1px 8px rgba(0,0,0,0.05);
    border: 1px solid rgba(255, 255, 255, 0.5);
}

.page-item {
    list-style: none;
}

.page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: transparent;
    color: #34495e;  /* Warna text lebih gelap */
    font-weight: 600;  /* Font weight ditambah */
    text-decoration: none;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    font-size: 1rem;  /* Ukuran font diperbesar */
}

.page-item.active .page-link {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
}

.page-item:not(.active) .page-link:hover {
    background: rgba(52, 152, 219, 0.1);
    border-color: #3498db;
    transform: translateY(-2px);
}

.page-item.disabled .page-link {
    color: #95a5a6;  /* Warna disabled lebih gelap */
    cursor: not-allowed;
    background: rgba(0, 0, 0, 0.05);
}

/* Pagination Info */
.pagination-info {
    text-align: center;
    color: #34495e;  /* Warna text info lebih gelap */
    font-size: 0.9rem;
    margin-top: 1rem;
    font-weight: 500;  /* Font weight ditambah */
}

/* Responsive Pagination */
@media (max-width: 768px) {
    .pagination {
        gap: 4px;
        padding: 8px;
    }

    .page-link {
        width: 35px;
        height: 35px;
        font-size: 0.9rem;  /* Ukuran font mobile diperbesar */
    }
}

/* Page Title Styling */
.page-title {
    position: relative;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    color: #34495e;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-title::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100px;
    height: 4px;
    background: linear-gradient(90deg, #3498db, #2ecc71);
    border-radius: 2px;
}

.page-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 100px;
    width: 50px;
    height: 4px;
    background: linear-gradient(90deg, #2ecc71, rgba(46, 204, 113, 0.2));
    border-radius: 2px;
}

.page-title i {
    font-size: 1.8rem;
    background: linear-gradient(135deg, #3498db, #2ecc71);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 2px 4px rgba(52, 152, 219, 0.2));
}

/* Optional: Animasi pada load */
@keyframes titleSlide {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.page-title {
    animation: titleSlide 0.5s ease-out forwards;
}

.search-container {
    max-width: 800px;
    margin: 0 auto 2rem;
}

.search-wrapper {
    position: relative;
    width: 100%;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-input {
    width: 100%;
    padding: 15px 160px 15px 50px;
    font-size: 1rem;
    color: #555;
    background: #fff;
    border: 2px solid #e1e1e1;
    border-radius: 100px !important;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.search-input:focus {
    border-color: #e86b44;
    box-shadow: 0 5px 20px rgba(232, 107, 68, 0.15);
    outline: none;
}

.search-icon {
    position: absolute;
    left: 20px;
    color: #aaa;
    font-size: 1.1rem;
    pointer-events: none;
    transition: all 0.3s ease;
}

.search-input:focus + .search-icon {
    color: #e86b44;
}

.btn-search {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    background: linear-gradient(45deg, #e86b44, #fca667);
    color: white;
    border: none;
    padding: 10px 25px;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-search:hover {
    background: linear-gradient(45deg, #d45e3a, #e86b44);
    transform: translateY(-50%) translateX(-2px);
    box-shadow: 0 5px 15px rgba(232, 107, 68, 0.3);
}

.btn-search i {
    font-size: 0.9rem;
}

.clear-search {
    position: absolute;
    right: 140px;
    top: 50%;
    transform: translateY(-50%);
    color: #aaa;
    font-size: 1.1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .search-container {
        max-width: 100%;
        padding: 0 15px;
    }
    
    .search-input {
        padding: 12px 140px 12px 45px;
        font-size: 0.95rem;
    }

    .btn-search {
        padding: 8px 20px;
        font-size: 0.85rem;
    }

    .clear-search {
        right: 120px;
    }
}

@media (max-width: 480px) {
    .btn-search span {
        display: none;
    }

    .btn-search {
        padding: 8px;
        right: 10px;
    }

    .search-input {
        padding-right: 90px;
    }

    .clear-search {
        right: 60px;
    }
}

/* Animasi untuk hasil pencarian */
.table tbody tr {
    transition: all 0.3s ease;
}

.highlight {
    background-color: rgba(232, 107, 68, 0.1);
}
</style>

<main id="main" class="main">
    <!-- Background Elements dari kode sebelumnya -->
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
            <h1 class="page-title">
                <i class="bi bi-calendar-check"></i>
                Daftar Pengajuan Cuti
            </h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item active">Manajemen Cuti</li>
                </ol>
            </nav>
        </div>

        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stats-card">
                <div class="dots"></div>
                <h5><i class="bi bi-file-text"></i>Total Pengajuan</h5>
                <h2><?php echo mysqli_num_rows($result); ?></h2>
            </div>
            <div class="stats-card">
                <h5><i class="bi bi-clock-history me-2"></i>Menunggu Persetujuan</h5>
                <h2><?php 
                    $pending = mysqli_query($conn, "SELECT COUNT(*) as count FROM tabel_cuti WHERE status_cuti='Diajukan'");
                    echo mysqli_fetch_assoc($pending)['count'];
                ?></h2>
            </div>
            <div class="stats-card">
                <h5><i class="bi bi-check-circle me-2"></i>Disetujui Bulan Ini</h5>
                <h2><?php 
                    $approved = mysqli_query($conn, "SELECT COUNT(*) as count FROM tabel_cuti 
                                                   WHERE status_cuti='Disetujui' 
                                                   AND MONTH(tanggal_pengajuan)=MONTH(CURRENT_DATE())");
                    echo mysqli_fetch_assoc($approved)['count'];
                ?></h2>
            </div>
        </div>

        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0">Data Pengajuan Cuti</h5>
                <div class="action-buttons">
                    <a href="tambah_jeniscuti.php" class="btn-action btn-add-type">
                        <i class="bi bi-plus-circle"></i>
                        <span>Tambahkan Jenis Cuti</span>
                    </a>
                    <a href="tambah_cuti.php" class="btn-action btn-add-leave">
                        <i class="bi bi-calendar-plus"></i>
                        <span>Ajukan Cuti</span>
                    </a>
                </div>
            </div>

            <!-- Update bagian search form -->
            <div class="search-container mb-4">
                <form action="" method="GET" class="search-form">
                    <div class="search-wrapper">
                        <div class="search-input-wrapper">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" 
                                   class="form-control search-input" 
                                   name="search" 
                                   placeholder="Cari berdasarkan nama pegawai atau jenis cuti..." 
                                   value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
                                   autocomplete="off">
                            <button type="submit" class="btn-search">
                                <i class="bi bi-search"></i> Cari
                            </button>
                            <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                                <a href="index.php" class="clear-search">
                                    <i class="bi bi-x-circle-fill"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Pegawai</th>
                            <th>Jenis Cuti</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Status</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result)) {
                            $statusClass = "status-" . strtolower($row['status_cuti']);
                        ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= $row['nama_peg']; ?></td>
                                <td><?= $row['nama_jenis_cuti']; ?></td>
                                <td><?= date('d/m/Y', strtotime($row['tanggal_mulai'])); ?></td>
                                <td><?= date('d/m/Y', strtotime($row['tanggal_selesai'])); ?></td>
                                <td><span class="status-badge <?= $statusClass ?>"><?= $row['status_cuti']; ?></span></td>
                                <td><?= date('d/m/Y', strtotime($row['tanggal_pengajuan'])); ?></td>
                                <td>
                                    <a href="detail_cuti.php?id=<?= $row['id_cuti']; ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if($row['status_cuti'] == 'Diajukan'): ?>
                                        <a href="edit_cuti.php?id=<?= $row['id_cuti']; ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="approve_cuti.php?id=<?= $row['id_cuti']; ?>" class="btn btn-sm btn-success">
                                            <i class="bi bi-check-circle"></i>
                                        </a>
                                        <a href="hapus_cuti.php?id=<?= $row['id_cuti']; ?>" class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Yakin ingin menghapus data ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tambahkan HTML pagination di bawah table -->
        <div class="pagination-container">
            <ul class="pagination">
                <!-- Previous Button -->
                <li class="page-item <?php if($page <= 1){ echo 'disabled'; } ?>">
                    <a class="page-link" href="<?php if($page <= 1){ echo '#'; } else { echo "?page=".($page-1); } ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>

                <!-- Page Numbers -->
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);

                if($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                    if($start_page > 2) {
                        echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                    }
                }

                for($i = $start_page; $i <= $end_page; $i++) {
                    echo '<li class="page-item '.($page == $i ? 'active' : '').'">
                            <a class="page-link" href="?page='.$i.'">'.$i.'</a>
                          </li>';
                }

                if($end_page < $total_pages) {
                    if($end_page < $total_pages - 1) {
                        echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                    }
                    echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.'">'.$total_pages.'</a></li>';
                }
                ?>

                <!-- Next Button -->
                <li class="page-item <?php if($page >= $total_pages){ echo 'disabled'; } ?>">
                    <a class="page-link" href="<?php if($page >= $total_pages){ echo '#'; } else { echo "?page=".($page+1); } ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Optional: Tambahkan info pagination -->
        <div class="pagination-info">
            Menampilkan <?php echo $start_from + 1; ?> - 
            <?php echo min($start_from + $items_per_page, $row_total['total']); ?> 
            dari <?php echo $row_total['total']; ?> data
        </div>
    </div>
</main>

<script>
// Script untuk animasi daun tetap sama
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

<!-- Tambahkan script untuk highlight hasil pencarian -->
<?php if (!empty($search)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchTerm = '<?= $search ?>';
    const tableRows = document.querySelectorAll('tbody tr');
    
    tableRows.forEach(row => {
        if (row.textContent.toLowerCase().includes(searchTerm.toLowerCase())) {
            row.classList.add('highlight');
        }
    });
});
</script>
<?php endif; ?>

<!-- Tambahkan informasi hasil pencarian jika ada -->
<?php if (!empty($search)): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle me-1"></i>
        Hasil pencarian untuk: <strong><?= htmlspecialchars($search) ?></strong>
        (<?= mysqli_num_rows($result) ?> data ditemukan)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php require_once "../template/footer.php"; ?>
