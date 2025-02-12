<?php
include "../connection.php";
require_once "../template/header.php";
require_once "../template/sidebar.php";

// Pencarian
$search = $_GET['search'] ?? '';

// Pagination
$limit = 10; // Batas data per halaman
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $limit;

// Query untuk mengambil data penggajian beserta data pegawai dan jabatan dengan pencarian dan paginasi
$query = "SELECT pg.*, j.nama_jabatan, peg.nama_peg, peg.id_peg 
          FROM penggajian pg
          JOIN jabatan j ON j.id_jabatan = pg.id_jabatan 
          JOIN pegawai peg ON peg.id_peg = pg.id_peg 
          WHERE peg.nama_peg LIKE '%$search%' 
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);
if (!$result) {
    die("Error dalam query: " . mysqli_error($conn));
}

// Menghitung total data untuk pagination
$total_data_query = "SELECT COUNT(*) as total 
                    FROM penggajian pg
                    JOIN pegawai peg ON peg.id_peg = pg.id_peg
                    WHERE peg.nama_peg LIKE '%$search%'";
$total_data_result = mysqli_query($conn, $total_data_query);
$total_data = mysqli_fetch_assoc($total_data_result)['total'];
$total_pages = ceil($total_data / $limit);
?>

<style>
/* Stats Card Styling */
.stats-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 30px;
    perspective: 1000px;
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

.stats-card:nth-child(1) {
    background: linear-gradient(145deg, #fff6e6, #ffffff);
}

.stats-card:nth-child(2) {
    background: linear-gradient(145deg, #e6f3ff, #ffffff);
}

.stats-card:nth-child(3) {
    background: linear-gradient(145deg, #f0ffe6, #ffffff);
}

.stats-card:hover {
    transform: translateY(-10px) rotateX(5deg) rotateY(-5deg);
    box-shadow: 
        20px 20px 60px rgba(0, 0, 0, 0.1),
        -20px -20px 60px rgba(255, 255, 255, 0.8);
}

/* Search Styling */
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
    border-color: #4e73df;
    box-shadow: 0 5px 20px rgba(78, 115, 223, 0.15);
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
    color: #4e73df;
}

.btn-search {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    background: linear-gradient(45deg, #4e73df, #224abe);
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
    background: linear-gradient(45deg, #224abe, #4e73df);
    transform: translateY(-50%) translateX(-2px);
    box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
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

.clear-search:hover {
    color: #e74a3b;
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
    background: white;
}

.table thead th {
    background: linear-gradient(120deg, #4e73df, #224abe, #1a237e);
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 1px;
    padding: 15px;
    border: none;
    position: relative;
    overflow: hidden;
}

.table tbody td {
    padding: 15px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(78, 115, 223, 0.1);
}

.table tbody tr {
    transition: all 0.3s ease;
}

.table tbody tr:hover {
    background: rgba(78, 115, 223, 0.08);
    transform: scale(1.01);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* Nominal Column Styling */
.nominal-column {
    font-family: 'Roboto Mono', monospace;
    font-weight: 600;
    color: #2c3e50;
    background: rgba(52, 152, 219, 0.05);
}

.table td.gaji-pokok {
    color: #2ecc71;
    font-weight: 600;
}

.table td.bonus {
    color: #e67e22;
    font-weight: 600;
}

.table td.potongan {
    color: #e74c3c;
    font-weight: 600;
}

.table td.total {
    color: #2980b9;
    font-weight: 700;
    background: rgba(41, 128, 185, 0.05);
}

/* Action Buttons in Table */
.table-action-btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    margin: 0 3px;
    transition: all 0.3s ease;
    border: none;
}

.btn-gradient-info {
    background: linear-gradient(45deg, #36b9cc, #1a8eaf);
    color: white;
}

.btn-gradient-warning {
    background: linear-gradient(45deg, #f6c23e, #dfa408);
    color: white;
}

.btn-gradient-danger {
    background: linear-gradient(45deg, #e74a3b, #be2617);
    color: white;
}

.table-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

/* Status Badge Styling */
.status-badge {
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-active {
    background: linear-gradient(45deg, #2ecc71, #27ae60);
    color: white;
}

.status-pending {
    background: linear-gradient(45deg, #f1c40f, #f39c12);
    color: white;
}

/* Animasi untuk kolom nominal saat hover */
.nominal-column:hover {
    transform: scale(1.05);
    transition: transform 0.3s ease;
    background: rgba(52, 152, 219, 0.1);
}

/* Garis pemisah kolom yang lebih halus */
.table td, .table th {
    border-right: 1px solid rgba(0,0,0,0.05);
}

.table td:last-child, .table th:last-child {
    border-right: none;
}

/* Footer tabel dengan summary */
.table-footer {
    background: linear-gradient(to right, rgba(78, 115, 223, 0.05), rgba(78, 115, 223, 0.02));
    padding: 15px;
    border-top: 1px solid rgba(0,0,0,0.05);
    font-weight: 600;
}

/* Search Container Styles */
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
    border-color: #4e73df;
    box-shadow: 0 5px 20px rgba(78, 115, 223, 0.15);
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
    color: #4e73df;
}

.btn-search {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    background: linear-gradient(45deg, #4e73df, #224abe);
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
    background: linear-gradient(45deg, #224abe, #4e73df);
    transform: translateY(-50%) translateX(-2px);
    box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
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

.clear-search:hover {
    color: #e74a3b;
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

/* Action Buttons Styling */
.action-buttons {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
}

.btn-action {
    position: relative;
    padding: 12px 24px;
    border-radius: 50px;
    border: none;
    font-weight: 500;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    color: white;
    overflow: hidden;
}

.btn-action::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.btn-action:hover::before {
    opacity: 1;
}

.btn-action i {
    font-size: 1.1rem;
    transition: transform 0.3s ease;
}

.btn-action:hover i {
    transform: translateX(3px);
}

.btn-add {
    background: linear-gradient(45deg, #4e73df, #224abe);
    box-shadow: 0 4px 15px rgba(78, 115, 223, 0.2);
}

.btn-cut {
    background: linear-gradient(45deg, #e74a3b, #be2617);
    box-shadow: 0 4px 15px rgba(231, 74, 59, 0.2);
}

.btn-allowance {
    background: linear-gradient(45deg, #36b9cc, #258391);
    box-shadow: 0 4px 15px rgba(54, 185, 204, 0.2);
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    color: white;
}

.btn-action:active {
    transform: translateY(1px);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .action-buttons {
        flex-direction: column;
        gap: 10px;
    }
    
    .btn-action {
        width: 100%;
        justify-content: center;
    }
}

/* Pagination Styling */
.pagination-container {
    margin-top: 2rem;
    display: flex;
    justify-content: center;
}

.pagination {
    display: flex;
    gap: 8px;
    padding: 10px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 50px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.page-item {
    list-style: none;
}

.page-item.active .page-link {
    background: linear-gradient(45deg, #4e73df, #224abe);
    border-color: transparent;
    color: white;
    transform: scale(1.1);
}

.page-link {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    border: 2px solid transparent;
    color: #555;
    font-weight: 500;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    padding: 0;
    margin: 0;
    font-size: 0.9rem;
}

.page-link:hover:not(.active) {
    background: rgba(78, 115, 223, 0.1);
    color: #4e73df;
    transform: translateY(-2px);
}

.page-item.disabled .page-link {
    opacity: 0.5;
    cursor: not-allowed;
    background: #f8f9fc;
}

/* Pagination Info Styling */
.pagination-info {
    text-align: center;
    margin-top: 1rem;
    color: #666;
    font-size: 0.9rem;
    padding: 10px;
    background: rgba(255, 255, 255, 0.7);
    border-radius: 30px;
    backdrop-filter: blur(5px);
    display: inline-block;
    position: relative;
    left: 50%;
    transform: translateX(-50%);
}

/* Dots separator */
.page-dots {
    display: flex;
    align-items: center;
    padding: 0 8px;
    color: #666;
}

/* Navigation arrows */
.page-nav {
    font-size: 1.1rem;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: linear-gradient(45deg, #4e73df, #224abe);
    color: white;
    transition: all 0.3s ease;
}

.page-nav:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(78, 115, 223, 0.2);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .pagination {
        gap: 5px;
        padding: 8px;
    }

    .page-link {
        width: 30px;
        height: 30px;
        font-size: 0.85rem;
    }

    .pagination-info {
        font-size: 0.85rem;
        padding: 8px 15px;
    }
}
</style>

<main id="main" class="main">
    <div class="pagetitle">
        <h1 class="page-title">
            <i class="bi bi-currency-dollar"></i>
            Data Penggajian Karyawan
        </h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item active">Penggajian</li>
            </ol>
        </nav>
    </div>

    <!-- Stats Cards -->
    <div class="stats-container">
        <div class="stats-card">
            <h5><i class="bi bi-people me-2"></i>Total Karyawan</h5>
            <h2><?php 
                $total_karyawan = mysqli_query($conn, "SELECT COUNT(*) as count FROM pegawai");
                echo mysqli_fetch_assoc($total_karyawan)['count'];
            ?></h2>
        </div>
        <div class="stats-card">
            <h5><i class="bi bi-cash-stack me-2"></i>Total Gaji Bulan Ini</h5>
            <h2>Rp <?php 
                $total_gaji = mysqli_query($conn, "SELECT SUM(gaji_pokok) as total FROM penggajian");
                echo number_format(mysqli_fetch_assoc($total_gaji)['total'], 0, ',', '.');
            ?></h2>
        </div>
        <div class="stats-card">
            <h5><i class="bi bi-graph-up me-2"></i>Rata-rata Gaji</h5>
            <h2>Rp <?php 
                $avg_gaji = mysqli_query($conn, "SELECT AVG(gaji_pokok) as avg FROM penggajian");
                echo number_format(mysqli_fetch_assoc($avg_gaji)['avg'], 0, ',', '.');
            ?></h2>
        </div>
    </div>

    <!-- Search dan Table Container -->
    <div class="table-container">
        <div class="card shadow">
            <div class="card-body">
                <div class="card-header-custom">
                    <div class="title-with-search">
                        <h5 class="card-title mb-0 text-primary">
                            <i class="bi bi-currency-dollar me-2"></i>
                            Daftar Penggajian Karyawan
                        </h5>
                        <div class="search-container mb-4">
                            <form action="" method="GET" class="search-form">
                                <div class="search-wrapper">
                                    <div class="search-input-wrapper">
                                        <i class="bi bi-search search-icon"></i>
                                        <input type="text" 
                                               class="form-control search-input" 
                                               name="search" 
                                               placeholder="Cari nama pegawai..." 
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
                    </div>
                </div>

                <div class="row my-4">
                    <div class="col-md-12">
                        <div class="action-buttons">
                            <a href="tambah.php" class="btn-action btn-add">
                                <i class="bi bi-plus-circle"></i>
                                <span>Tambah Data</span>
                            </a>
                            <a href="potong_gaji.php" class="btn-action btn-cut">
                                <i class="bi bi-scissors"></i>
                                <span>Potongan Gaji</span>
                            </a>
                            <a href="tunjangan.php" class="btn-action btn-allowance">
                                <i class="bi bi-cash-stack"></i>
                                <span>Tunjangan</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr class="text-center">
                                <th width="5%">No</th>
                                <th width="20%">Nama Pegawai</th>
                                <th width="15%">Jabatan</th>
                                <th width="15%">Gaji Pokok</th>
                                <th width="10%">Bonus</th>
                                <th width="15%">Total Potongan</th>
                                <th width="15%">Total Gaji Bersih</th>
                                <th width="5%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = $offset + 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                // Menghitung total potongan dan gaji bersih
                                $query_potongan = "SELECT SUM(p.jumlah) as total_potongan 
                                                   FROM potongan_gaji p
                                                   JOIN penggajian_potongan pp ON pp.id_potongan = p.id_potongan
                                                   WHERE pp.id_penggajian = '{$row['id_penggajian']}'";
                                $result_potongan = mysqli_query($conn, $query_potongan);
                                $potongan = mysqli_fetch_assoc($result_potongan);
                                $total_potongan = $potongan['total_potongan'] ?? 0;
                                $total_gaji = $row['gaji_pokok'] + $row['bonus'] - $total_potongan;
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama_peg']) ?></td>
                                <td><?= htmlspecialchars($row['nama_jabatan']) ?></td>
                                <td class="text-end nominal-column">Rp <?= number_format($row['gaji_pokok'], 0, ',', '.') ?></td>
                                <td class="text-end nominal-column">Rp <?= number_format($row['bonus'], 0, ',', '.') ?></td>
                                <td class="text-end nominal-column">Rp <?= number_format($total_potongan, 0, ',', '.') ?></td>
                                <td class="text-end nominal-column">Rp <?= number_format($total_gaji, 0, ',', '.') ?></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="detail_gaji.php?id_jabatan=<?= $row['id_jabatan'] ?>&id_pegawai=<?= $row['id_peg'] ?>" 
                                           class="table-action-btn btn-gradient-info"
                                           data-bs-toggle="tooltip" 
                                           title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="edit.php?id=<?= $row['id_penggajian'] ?>" 
                                           class="table-action-btn btn-gradient-warning"
                                           data-bs-toggle="tooltip" 
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="hapus.php?id=<?= $row['id_penggajian'] ?>" 
                                           class="table-action-btn btn-gradient-danger"
                                           onclick="return confirm('Yakin ingin menghapus data ini?')"
                                           data-bs-toggle="tooltip" 
                                           title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Update bagian pagination -->
                <div class="pagination-container">
                    <ul class="pagination">
                        <!-- Previous Button -->
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link page-nav" href="<?= $page <= 1 ? '#' : "?page=".($page-1)."&search=".urlencode($search) ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>

                        <!-- Page Numbers -->
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);

                        if($start_page > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?page=1&search='.urlencode($search).'">1</a></li>';
                            if($start_page > 2) {
                                echo '<li class="page-dots">...</li>';
                            }
                        }

                        for($i = $start_page; $i <= $end_page; $i++) {
                            echo '<li class="page-item '.($page == $i ? 'active' : '').'">
                                    <a class="page-link" href="?page='.$i.'&search='.urlencode($search).'">'.$i.'</a>
                                  </li>';
                        }

                        if($end_page < $total_pages) {
                            if($end_page < $total_pages - 1) {
                                echo '<li class="page-dots">...</li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.'&search='.urlencode($search).'">'.$total_pages.'</a></li>';
                        }
                        ?>

                        <!-- Next Button -->
                        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link page-nav" href="<?= $page >= $total_pages ? '#' : "?page=".($page+1)."&search=".urlencode($search) ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Pagination Info -->
                <div class="pagination-info">
                    Menampilkan <?= $offset + 1 ?> - 
                    <?= min($offset + $limit, $total_data) ?> 
                    dari <?= $total_data ?> data
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
});
</script>

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

<?php if (!empty($search)): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle me-1"></i>
        Hasil pencarian untuk: <strong><?= htmlspecialchars($search) ?></strong>
        (<?= mysqli_num_rows($result) ?> data ditemukan)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php
require_once "../template/footer.php";
?>
