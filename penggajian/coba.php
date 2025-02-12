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

<!-- Tambahkan style untuk efek partikel galaksi -->
<style>
@keyframes gradientAnimation {
    0% { background-position: 0% 50% }
    50% { background-position: 100% 50% }
    100% { background-position: 0% 50% }
}

@keyframes textShimmer {
    0% { background-position: 0% 50% }
    100% { background-position: 100% 50% }
}

@keyframes float {
    0% {
        transform: translate(0, 0) scale(0.8);
        opacity: 0.6;
    }
    50% {
        transform: translate(50px, -80px) scale(1.5);
        opacity: 1;
    }
    100% {
        transform: translate(-100px, 100px) scale(0.8);
        opacity: 0.4;
    }
}

.particle {
    position: absolute;
    border-radius: 50%;
    box-shadow: 0 0 8px rgba(255, 255, 255, 0.8);
    animation: float 15s infinite ease-in-out;
    pointer-events: none;
    z-index: 1;
}

.content-wrapper {
    position: relative;
    z-index: 2;
}

#main {
    background: radial-gradient(circle at center, #0f2027, #203a43, #2c5364) !important;
    position: relative;
    overflow: hidden;
    min-height: 100vh;
}
</style>

<!-- Tambahkan script untuk membuat partikel -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainElement = document.getElementById('main');
    const totalParticles = 150;
    const galaxyColors = ['#8A2BE2', '#4B0082', '#FF1493', '#00FFFF', '#FF4500'];
    
    for (let i = 0; i < totalParticles; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        const size = Math.random() * 15 + 5 + 'px';
        particle.style.width = size;
        particle.style.height = size;
        particle.style.background = galaxyColors[Math.floor(Math.random() * galaxyColors.length)];
        particle.style.top = Math.random() * 100 + 'vh';
        particle.style.left = Math.random() * 100 + 'vw';
        particle.style.animationDelay = Math.random() * 10 + 's';
        particle.style.animationDuration = 8 + Math.random() * 12 + 's';
        mainElement.appendChild(particle);
    }
});
</script>

<main id="main" class="main">
    <div class="content-wrapper">
        <div class="pagetitle">
            <h1 style="font-size: 36px;
                       color: #fff;
                       text-shadow: 0 0 10px rgba(255,255,255,0.5);">
                Data Penggajian
            </h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php" style="color: #fff;">Home</a></li>
                    <li class="breadcrumb-item active" style="color: rgba(255,255,255,0.8);">Penggajian</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                        <div class="card-body">
                            <h5 class="card-title">Daftar Penggajian Karyawan</h5>

                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <!-- Tombol-tombol -->
                                    <a href="tambah.php" class="btn btn-primary">
                                        <i class="bi bi-plus"></i> Tambah Data Penggajian
                                    </a>
                                    <a href="potong_gaji.php" class="btn btn-secondary">
                                        <i class="bi bi-file-earmark-plus"></i> Potongan Gaji
                                    </a>
                                    <a href="tunjangan.php" class="btn btn-secondary">
                                        <i class="bi bi-file-earmark-plus"></i> Tunjangan
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <!-- Form Pencarian -->
                                    <form method="GET" class="d-flex">
                                        <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Cari nama pegawai" value="<?= htmlspecialchars($search) ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
                                    </form>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped datatable">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Pegawai</th>
                                            <th>Jabatan</th>
                                            <th>Gaji Pokok</th>
                                            <th>Bonus</th>
                                            <th>Total Potongan</th>
                                            <th>Total Gaji Bersih</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = $offset + 1;
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            // Menghitung total potongan
                                            $query_potongan = "SELECT SUM(p.jumlah) as total_potongan 
                                                               FROM potongan_gaji p
                                                               JOIN penggajian_potongan pp ON pp.id_potongan = p.id_potongan
                                                               WHERE pp.id_penggajian = '{$row['id_penggajian']}'";
                                            $result_potongan = mysqli_query($conn, $query_potongan);
                                            $potongan = mysqli_fetch_assoc($result_potongan);
                                            $total_potongan = $potongan['total_potongan'] ?? 0;

                                            // Menghitung total gaji bersih
                                            $total_gaji = $row['gaji_pokok'] + $row['bonus'] - $total_potongan;
                                        ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['nama_peg']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_jabatan']) ?></td>
                                            <td>Rp <?= number_format($row['gaji_pokok'], 0, ',', '.') ?></td>
                                            <td>Rp <?= number_format($row['bonus'], 0, ',', '.') ?></td>
                                            <td>Rp <?= number_format($total_potongan, 0, ',', '.') ?></td>
                                            <td>Rp <?= number_format($total_gaji, 0, ',', '.') ?></td>
                                            <td>
                                                <a href="detail_gaji.php?id_jabatan=<?= $row['id_jabatan'] ?>&id_pegawai=<?= $row['id_peg'] ?>" 
                                                   class="btn btn-info btn-sm">
                                                    <i class="bi bi-eye"></i> Detail
                                                </a>
                                                <a href="edit.php?id=<?= $row['id_penggajian'] ?>" 
                                                   class="btn btn-warning btn-sm">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <a href="hapus.php?id=<?= $row['id_penggajian'] ?>" 
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </a>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="index.php?page=<?= max(1, $page - 1) ?>&search=<?= urlencode($search) ?>">Previous</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="index.php?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="index.php?page=<?= min($total_pages, $page + 1) ?>&search=<?= urlencode($search) ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php
require_once "../template/footer.php";
?>


<!-- tema kedua -->
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
@keyframes glow {
    0%, 100% {
        box-shadow: 0 0 20px #00ff8c,
                   0 0 40px #00ff8c,
                   0 0 60px #00ff8c;
    }
    50% {
        box-shadow: 0 0 10px #00ff8c,
                   0 0 20px #00ff8c,
                   0 0 30px #00ff8c;
    }
}

@keyframes float {
    0% {
        transform: translateY(0px) rotate(0deg);
    }
    50% {
        transform: translateY(-20px) rotate(180deg);
    }
    100% {
        transform: translateY(0px) rotate(360deg);
    }
}

.luminous-particle {
    position: absolute;
    width: 4px;
    height: 4px;
    background: #00ff8c;
    border-radius: 50%;
    filter: blur(2px);
    animation: float 6s infinite ease-in-out;
    opacity: 0.6;
}

.content-wrapper {
    position: relative;
    z-index: 2;
    padding: 20px;
    background: rgba(0, 0, 0, 0.7);
    border-radius: 15px;
    backdrop-filter: blur(10px);
    box-shadow: 0 0 20px rgba(0, 255, 140, 0.2);
}

#main {
    background: linear-gradient(45deg, #0a0a0a, #1a1a1a, #0a0a0a);
    position: relative;
    overflow: hidden;
    min-height: 100vh;
    padding: 20px;
}

.card {
    background: rgba(22, 22, 22, 0.9) !important;
    border: 1px solid rgba(0, 255, 140, 0.2) !important;
    box-shadow: 0 0 15px rgba(0, 255, 140, 0.1) !important;
}

.table {
    color: #e0e0e0 !important;
}

.table thead th {
    background: rgba(0, 255, 140, 0.1);
    color: #00ff8c;
    border-color: rgba(0, 255, 140, 0.2);
}

.table td {
    border-color: rgba(255, 255, 255, 0.1);
}

.btn-primary {
    background: linear-gradient(45deg, #00ff8c, #00b36b) !important;
    border: none !important;
    box-shadow: 0 0 10px rgba(0, 255, 140, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 20px rgba(0, 255, 140, 0.5);
}

h1 {
    color: #00ff8c !important;
    text-shadow: 0 0 10px rgba(0, 255, 140, 0.5);
    font-weight: 600;
    letter-spacing: 2px;
}

.breadcrumb-item a {
    color: #00ff8c !important;
}

.breadcrumb-item.active {
    color: rgba(0, 255, 140, 0.7) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainElement = document.getElementById('main');
    const particleCount = 50;

    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'luminous-particle';
        
        // Random position
        particle.style.left = Math.random() * 100 + 'vw';
        particle.style.top = Math.random() * 100 + 'vh';
        
        // Random size
        const size = Math.random() * 4 + 2;
        particle.style.width = size + 'px';
        particle.style.height = size + 'px';
        
        // Random animation duration and delay
        particle.style.animationDuration = (Math.random() * 6 + 4) + 's';
        particle.style.animationDelay = Math.random() * 5 + 's';
        
        mainElement.appendChild(particle);
    }
});
</script>

<main id="main" class="main">
    <div class="content-wrapper">
        <div class="pagetitle">
            <h1>Data Penggajian</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item active">Penggajian</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Daftar Penggajian Karyawan</h5>

                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <!-- Tombol-tombol -->
                                    <a href="tambah.php" class="btn btn-primary">
                                        <i class="bi bi-plus"></i> Tambah Data Penggajian
                                    </a>
                                    <a href="potong_gaji.php" class="btn btn-secondary">
                                        <i class="bi bi-file-earmark-plus"></i> Potongan Gaji
                                    </a>
                                    <a href="tunjangan.php" class="btn btn-secondary">
                                        <i class="bi bi-file-earmark-plus"></i> Tunjangan
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <!-- Form Pencarian -->
                                    <form method="GET" class="d-flex">
                                        <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Cari nama pegawai" value="<?= htmlspecialchars($search) ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
                                    </form>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped datatable">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Pegawai</th>
                                            <th>Jabatan</th>
                                            <th>Gaji Pokok</th>
                                            <th>Bonus</th>
                                            <th>Total Potongan</th>
                                            <th>Total Gaji Bersih</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = $offset + 1;
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            // Menghitung total potongan
                                            $query_potongan = "SELECT SUM(p.jumlah) as total_potongan 
                                                               FROM potongan_gaji p
                                                               JOIN penggajian_potongan pp ON pp.id_potongan = p.id_potongan
                                                               WHERE pp.id_penggajian = '{$row['id_penggajian']}'";
                                            $result_potongan = mysqli_query($conn, $query_potongan);
                                            $potongan = mysqli_fetch_assoc($result_potongan);
                                            $total_potongan = $potongan['total_potongan'] ?? 0;

                                            // Menghitung total gaji bersih
                                            $total_gaji = $row['gaji_pokok'] + $row['bonus'] - $total_potongan;
                                        ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['nama_peg']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_jabatan']) ?></td>
                                            <td>Rp <?= number_format($row['gaji_pokok'], 0, ',', '.') ?></td>
                                            <td>Rp <?= number_format($row['bonus'], 0, ',', '.') ?></td>
                                            <td>Rp <?= number_format($total_potongan, 0, ',', '.') ?></td>
                                            <td>Rp <?= number_format($total_gaji, 0, ',', '.') ?></td>
                                            <td>
                                                <a href="detail_gaji.php?id_jabatan=<?= $row['id_jabatan'] ?>&id_pegawai=<?= $row['id_peg'] ?>" 
                                                   class="btn btn-info btn-sm">
                                                    <i class="bi bi-eye"></i> Detail
                                                </a>
                                                <a href="edit.php?id=<?= $row['id_penggajian'] ?>" 
                                                   class="btn btn-warning btn-sm">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <a href="hapus.php?id=<?= $row['id_penggajian'] ?>" 
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </a>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="index.php?page=<?= max(1, $page - 1) ?>&search=<?= urlencode($search) ?>">Previous</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="index.php?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="index.php?page=<?= min($total_pages, $page + 1) ?>&search=<?= urlencode($search) ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php
require_once "../template/footer.php";
?>


<!--mode 3-->

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
@keyframes sway {
    0%, 100% {
        transform: rotate(0deg);
    }
    50% {
        transform: rotate(5deg);
    }
}

@keyframes float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

@keyframes glow {
    0%, 100% {
        filter: brightness(1);
    }
    50% {
        filter: brightness(1.3);
    }
}

@keyframes leafFall {
    0% {
        transform: translateY(-100vh) rotate(0deg);
        opacity: 0;
    }
    50% {
        opacity: 1;
    }
    100% {
        transform: translateY(100vh) rotate(360deg);
        opacity: 0;
    }
}

#main {
    background: linear-gradient(to bottom, #1a472a, #2d5a27);
    position: relative;
    overflow: hidden;
    min-height: 100vh;
    padding: 20px;
}

/* Tree Container */
.tree-container {
    position: fixed;
    right: 0;
    bottom: 0;
    width: 400px;
    height: 600px;
    pointer-events: none;
    z-index: 1;
}

.tree-trunk {
    position: absolute;
    bottom: 0;
    right: 50px;
    width: 60px;
    height: 400px;
    background: linear-gradient(90deg, #4a3728, #6b4c35);
    border-radius: 10px;
    transform-origin: bottom;
    animation: sway 8s ease-in-out infinite;
}

.tree-crown {
    position: absolute;
    bottom: 300px;
    right: 0;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle at center, #4CAF50, #2E7D32);
    border-radius: 50%;
    box-shadow: 
        -50px -20px 0 #388E3C,
        50px -30px 0 #43A047,
        0 -50px 0 #66BB6A;
    animation: sway 8s ease-in-out infinite;
}

.firefly {
    position: absolute;
    width: 4px;
    height: 4px;
    background: #FFEB3B;
    border-radius: 50%;
    box-shadow: 0 0 10px #FFEB3B;
    animation: float 3s ease-in-out infinite, glow 2s ease-in-out infinite;
}

.leaf {
    position: absolute;
    width: 20px;
    height: 20px;
    background: #4CAF50;
    clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
    animation: leafFall 10s linear infinite;
}

.content-wrapper {
    position: relative;
    z-index: 2;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    padding: 20px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
}

.card {
    background: rgba(255, 255, 255, 0.15) !important;
    border: none !important;
    backdrop-filter: blur(5px);
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1) !important;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2) !important;
}

.table {
    color: #fff !important;
}

.table thead th {
    background: rgba(76, 175, 80, 0.3);
    color: #fff;
    border: none;
    font-weight: 600;
}

.table td {
    border-color: rgba(255, 255, 255, 0.1);
}

.btn-primary {
    background: linear-gradient(45deg, #4CAF50, #66BB6A) !important;
    border: none !important;
    box-shadow: 0 0 15px rgba(76, 175, 80, 0.3);
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(76, 175, 80, 0.5);
}

h1 {
    color: #fff !important;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    font-weight: 600;
}

.breadcrumb-item a {
    color: #A5D6A7 !important;
}

.breadcrumb-item.active {
    color: rgba(255, 255, 255, 0.7) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainElement = document.getElementById('main');
    
    // Create tree
    const treeContainer = document.createElement('div');
    treeContainer.className = 'tree-container';
    
    const trunk = document.createElement('div');
    trunk.className = 'tree-trunk';
    
    const crown = document.createElement('div');
    crown.className = 'tree-crown';
    
    treeContainer.appendChild(trunk);
    treeContainer.appendChild(crown);
    mainElement.appendChild(treeContainer);
    
    // Create fireflies
    for(let i = 0; i < 20; i++) {
        const firefly = document.createElement('div');
        firefly.className = 'firefly';
        firefly.style.left = Math.random() * 100 + 'vw';
        firefly.style.top = Math.random() * 100 + 'vh';
        firefly.style.animationDelay = Math.random() * 3 + 's';
        mainElement.appendChild(firefly);
    }
    
    // Create falling leaves
    setInterval(() => {
        const leaf = document.createElement('div');
        leaf.className = 'leaf';
        leaf.style.left = Math.random() * 100 + 'vw';
        leaf.style.transform = `rotate(${Math.random() * 360}deg)`;
        mainElement.appendChild(leaf);
        
        // Remove leaf after animation
        setTimeout(() => {
            leaf.remove();
        }, 10000);
    }, 1000);
});
</script>

<main id="main" class="main">
    <div class="content-wrapper">
        <div class="pagetitle">
            <h1>Data Penggajian</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item active">Penggajian</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Daftar Penggajian Karyawan</h5>

                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <!-- Tombol-tombol -->
                                    <a href="tambah.php" class="btn btn-primary">
                                        <i class="bi bi-plus"></i> Tambah Data Penggajian
                                    </a>
                                    <a href="potong_gaji.php" class="btn btn-secondary">
                                        <i class="bi bi-file-earmark-plus"></i> Potongan Gaji
                                    </a>
                                    <a href="tunjangan.php" class="btn btn-secondary">
                                        <i class="bi bi-file-earmark-plus"></i> Tunjangan
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <!-- Form Pencarian -->
                                    <form method="GET" class="d-flex">
                                        <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Cari nama pegawai" value="<?= htmlspecialchars($search) ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
                                    </form>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped datatable">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Pegawai</th>
                                            <th>Jabatan</th>
                                            <th>Gaji Pokok</th>
                                            <th>Bonus</th>
                                            <th>Total Potongan</th>
                                            <th>Total Gaji Bersih</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = $offset + 1;
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            // Menghitung total potongan
                                            $query_potongan = "SELECT SUM(p.jumlah) as total_potongan 
                                                               FROM potongan_gaji p
                                                               JOIN penggajian_potongan pp ON pp.id_potongan = p.id_potongan
                                                               WHERE pp.id_penggajian = '{$row['id_penggajian']}'";
                                            $result_potongan = mysqli_query($conn, $query_potongan);
                                            $potongan = mysqli_fetch_assoc($result_potongan);
                                            $total_potongan = $potongan['total_potongan'] ?? 0;

                                            // Menghitung total gaji bersih
                                            $total_gaji = $row['gaji_pokok'] + $row['bonus'] - $total_potongan;
                                        ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['nama_peg']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_jabatan']) ?></td>
                                            <td>Rp <?= number_format($row['gaji_pokok'], 0, ',', '.') ?></td>
                                            <td>Rp <?= number_format($row['bonus'], 0, ',', '.') ?></td>
                                            <td>Rp <?= number_format($total_potongan, 0, ',', '.') ?></td>
                                            <td>Rp <?= number_format($total_gaji, 0, ',', '.') ?></td>
                                            <td>
                                                <a href="detail_gaji.php?id_jabatan=<?= $row['id_jabatan'] ?>&id_pegawai=<?= $row['id_peg'] ?>" 
                                                   class="btn btn-info btn-sm">
                                                    <i class="bi bi-eye"></i> Detail
                                                </a>
                                                <a href="edit.php?id=<?= $row['id_penggajian'] ?>" 
                                                   class="btn btn-warning btn-sm">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <a href="hapus.php?id=<?= $row['id_penggajian'] ?>" 
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </a>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="index.php?page=<?= max(1, $page - 1) ?>&search=<?= urlencode($search) ?>">Previous</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="index.php?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="index.php?page=<?= min($total_pages, $page + 1) ?>&search=<?= urlencode($search) ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php
require_once "../template/footer.php";
?>


<!--mode 4-->
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
@keyframes heroFloat {
    0%, 100% {
        transform: translateY(0) scale(1);
    }
    50% {
        transform: translateY(-20px) scale(1.05);
    }
}

@keyframes energyPulse {
    0%, 100% {
        box-shadow: 0 0 20px #4fc3f7,
                   0 0 40px #4fc3f7;
    }
    50% {
        box-shadow: 0 0 30px #ff4081,
                   0 0 60px #ff4081;
    }
}

@keyframes battleAura {
    0% {
        transform: rotate(0deg);
        opacity: 0.3;
    }
    100% {
        transform: rotate(360deg);
        opacity: 0.6;
    }
}

@keyframes lightningStrike {
    0%, 100% {
        opacity: 0;
    }
    5%, 95% {
        opacity: 0.8;
    }
}

#main {
    background: linear-gradient(135deg, #1a237e, #311b92);
    position: relative;
    overflow: hidden;
    min-height: 100vh;
    padding: 20px;
}

/* Hero Background Effect */
.ml-container {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow: hidden;
    pointer-events: none;
}

.battle-aura {
    position: absolute;
    width: 200%;
    height: 200%;
    background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIj48Y2lyY2xlIGN4PSI1MCIgY3k9IjUwIiByPSI0MCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjNGZjM2Y3IiBzdHJva2Utd2lkdGg9IjIiLz48L3N2Zz4=');
    opacity: 0.3;
    animation: battleAura 20s linear infinite;
}

.hero-silhouette {
    position: fixed;
    right: -50px;
    bottom: 0;
    width: 400px;
    height: 600px;
    background: url('path/to/hero.png') no-repeat center/contain;
    animation: heroFloat 6s ease-in-out infinite;
    opacity: 0.2;
    filter: brightness(0) invert(1);
}

.lightning {
    position: absolute;
    width: 3px;
    height: 100px;
    background: linear-gradient(to bottom, transparent, #4fc3f7, transparent);
    animation: lightningStrike 3s infinite;
}

.content-wrapper {
    position: relative;
    z-index: 2;
    background: rgba(13, 13, 23, 0.8);
    border-radius: 15px;
    padding: 20px;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(79, 195, 247, 0.3);
    box-shadow: 0 0 30px rgba(79, 195, 247, 0.2);
    animation: energyPulse 4s infinite;
}

.card {
    background: rgba(25, 25, 35, 0.9) !important;
    border: 1px solid rgba(79, 195, 247, 0.2) !important;
    box-shadow: 0 0 15px rgba(79, 195, 247, 0.1) !important;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 0 30px rgba(255, 64, 129, 0.2) !important;
    border-color: rgba(255, 64, 129, 0.3) !important;
}

.table {
    color: #fff !important;
}

.table thead th {
    background: linear-gradient(90deg, #1a237e, #311b92);
    color: #4fc3f7;
    border: none;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
}

.table td {
    border-color: rgba(79, 195, 247, 0.1);
}

.btn-primary {
    background: linear-gradient(45deg, #4fc3f7, #ff4081) !important;
    border: none !important;
    box-shadow: 0 0 20px rgba(79, 195, 247, 0.4);
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: scale(1.05);
    box-shadow: 0 0 30px rgba(255, 64, 129, 0.6);
}

h1 {
    color: #4fc3f7 !important;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-weight: 800;
    text-shadow: 0 0 10px rgba(79, 195, 247, 0.5),
                 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.breadcrumb-item a {
    color: #4fc3f7 !important;
    text-transform: uppercase;
    font-weight: 600;
}

.breadcrumb-item.active {
    color: rgba(255, 64, 129, 0.8) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainElement = document.getElementById('main');
    
    // Create ML Container
    const mlContainer = document.createElement('div');
    mlContainer.className = 'ml-container';
    
    // Create Battle Aura
    const battleAura = document.createElement('div');
    battleAura.className = 'battle-aura';
    mlContainer.appendChild(battleAura);
    
    // Create Hero Silhouette
    const hero = document.createElement('div');
    hero.className = 'hero-silhouette';
    mlContainer.appendChild(hero);
    
    // Create Lightning Effects
    for(let i = 0; i < 10; i++) {
        const lightning = document.createElement('div');
        lightning.className = 'lightning';
        lightning.style.left = Math.random() * 100 + 'vw';
        lightning.style.animationDelay = Math.random() * 3 + 's';
        lightning.style.height = 100 + Math.random() * 200 + 'px';
        mlContainer.appendChild(lightning);
    }
    
    mainElement.appendChild(mlContainer);
});
</script>

<main id="main" class="main">
    <div class="content-wrapper">
        <div class="pagetitle">
            <h1>Data Penggajian</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item active">Penggajian</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Daftar Penggajian Karyawan</h5>

                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <!-- Tombol-tombol -->
                                    <a href="tambah.php" class="btn btn-primary">
                                        <i class="bi bi-plus"></i> Tambah Data Penggajian
                                    </a>
                                    <a href="potong_gaji.php" class="btn btn-secondary">
                                        <i class="bi bi-file-earmark-plus"></i> Potongan Gaji
                                    </a>
                                    <a href="tunjangan.php" class="btn btn-secondary">
                                        <i class="bi bi-file-earmark-plus"></i> Tunjangan
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <!-- Form Pencarian -->
                                    <form method="GET" class="d-flex">
                                        <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Cari nama pegawai" value="<?= htmlspecialchars($search) ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
                                    </form>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped datatable">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Pegawai</th>
                                            <th>Jabatan</th>
                                            <th>Gaji Pokok</th>
                                            <th>Bonus</th>
                                            <th>Total Potongan</th>
                                            <th>Total Gaji Bersih</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = $offset + 1;
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            // Menghitung total potongan
                                            $query_potongan = "SELECT SUM(p.jumlah) as total_potongan 
                                                               FROM potongan_gaji p
                                                               JOIN penggajian_potongan pp ON pp.id_potongan = p.id_potongan
                                                               WHERE pp.id_penggajian = '{$row['id_penggajian']}'";
                                            $result_potongan = mysqli_query($conn, $query_potongan);
                                            $potongan = mysqli_fetch_assoc($result_potongan);
                                            $total_potongan = $potongan['total_potongan'] ?? 0;

                                            // Menghitung total gaji bersih
                                            $total_gaji = $row['gaji_pokok'] + $row['bonus'] - $total_potongan;
                                        ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['nama_peg']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_jabatan']) ?></td>
                                            <td>Rp <?= number_format($row['gaji_pokok'], 0, ',', '.') ?></td>
                                            <td>Rp <?= number_format($row['bonus'], 0, ',', '.') ?></td>
                                            <td>Rp <?= number_format($total_potongan, 0, ',', '.') ?></td>
                                            <td>Rp <?= number_format($total_gaji, 0, ',', '.') ?></td>
                                            <td>
                                                <a href="detail_gaji.php?id_jabatan=<?= $row['id_jabatan'] ?>&id_pegawai=<?= $row['id_peg'] ?>" 
                                                   class="btn btn-info btn-sm">
                                                    <i class="bi bi-eye"></i> Detail
                                                </a>
                                                <a href="edit.php?id=<?= $row['id_penggajian'] ?>" 
                                                   class="btn btn-warning btn-sm">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <a href="hapus.php?id=<?= $row['id_penggajian'] ?>" 
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </a>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="index.php?page=<?= max(1, $page - 1) ?>&search=<?= urlencode($search) ?>">Previous</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="index.php?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="index.php?page=<?= min($total_pages, $page + 1) ?>&search=<?= urlencode($search) ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php
require_once "../template/footer.php";
?>


<!--mode 5-->

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
@keyframes gradientFlow {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

@keyframes floatingParticle {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
        opacity: 0.3;
    }
    50% {
        transform: translateY(-20px) rotate(180deg);
        opacity: 0.6;
    }
}

@keyframes glowPulse {
    0%, 100% {
        box-shadow: 0 0 20px rgba(99, 102, 241, 0.2);
    }
    50% {
        box-shadow: 0 0 30px rgba(99, 102, 241, 0.4);
    }
}

#main {
    background: linear-gradient(-45deg, #1e1b4b, #312e81, #1e40af, #1e3a8a);
    background-size: 400% 400%;
    animation: gradientFlow 15s ease infinite;
    position: relative;
    overflow: hidden;
    min-height: 100vh;
    padding: 20px;
}

.particle {
    position: absolute;
    width: 10px;
    height: 10px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    pointer-events: none;
}

.content-wrapper {
    position: relative;
    z-index: 2;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    padding: 25px;
    backdrop-filter: blur(10px);
    box-shadow: 
        0 0 30px rgba(0, 0, 0, 0.1),
        inset 0 0 1px rgba(255, 255, 255, 0.5);
    animation: glowPulse 4s infinite;
}

.card {
    background: #ffffff !important;
    border: none !important;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05) !important;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1) !important;
}

.table {
    color: #374151 !important;
}

.table thead th {
    background: #f3f4f6;
    color: #1f2937;
    border-bottom: 2px solid #e5e7eb;
    font-weight: 600;
    padding: 12px;
}

.table td {
    border-color: #e5e7eb;
    padding: 12px;
    vertical-align: middle;
}

.btn-primary {
    background: linear-gradient(45deg, #4f46e5, #6366f1) !important;
    border: none !important;
    box-shadow: 0 4px 6px rgba(99, 102, 241, 0.2);
    transition: all 0.3s ease;
    font-weight: 500;
    padding: 8px 16px;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 8px rgba(99, 102, 241, 0.3);
}

h1 {
    color: #1f2937 !important;
    font-weight: 700;
    font-size: 1.875rem;
    margin-bottom: 1rem;
    position: relative;
    display: inline-block;
}

h1::after {
    content: '';
    position: absolute;
    bottom: -4px;
    left: 0;
    width: 100%;
    height: 2px;
    background: linear-gradient(90deg, #4f46e5, #6366f1);
}

.breadcrumb-item a {
    color: #4f46e5 !important;
    font-weight: 500;
    text-decoration: none;
}

.breadcrumb-item.active {
    color: #6b7280 !important;
}

/* Form styling */
.form-control {
    border: 1px solid #e5e7eb;
    padding: 8px 12px;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.form-control:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.form-label {
    color: #374151;
    font-weight: 500;
    margin-bottom: 0.5rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainElement = document.getElementById('main');
    
    // Create floating particles
    for(let i = 0; i < 50; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        
        // Random position
        particle.style.left = Math.random() * 100 + 'vw';
        particle.style.top = Math.random() * 100 + 'vh';
        
        // Random size
        const size = Math.random() * 15 + 5;
        particle.style.width = size + 'px';
        particle.style.height = size + 'px';
        
        // Random animation
        particle.style.animationDuration = (Math.random() * 3 + 2) + 's';
        particle.style.animationDelay = Math.random() * 2 + 's';
        
        mainElement.appendChild(particle);
    }
});
</script>

<main id="main" class="main">
    <div class="content-wrapper">
        <div class="pagetitle">
            <h1>Data Penggajian</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item active">Penggajian</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Daftar Penggajian Karyawan</h5>

                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <!-- Tombol-tombol -->
                                    <a href="tambah.php" class="btn btn-primary">
                                        <i class="bi bi-plus"></i> Tambah Data Penggajian
                                    </a>
                                    <a href="potong_gaji.php" class="btn btn-secondary">
                                        <i class="bi bi-file-earmark-plus"></i> Potongan Gaji
                                    </a>
                                    <a href="tunjangan.php" class="btn btn-secondary">
                                        <i class="bi bi-file-earmark-plus"></i> Tunjangan
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <!-- Form Pencarian -->
                                    <form method="GET" class="d-flex">
                                        <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Cari nama pegawai" value="<?= htmlspecialchars($search) ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
                                    </form>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped datatable">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Pegawai</th>
                                            <th>Jabatan</th>
                                            <th>Gaji Pokok</th>
                                            <th>Bonus</th>
                                            <th>Total Potongan</th>
                                            <th>Total Gaji Bersih</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = $offset + 1;
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            // Menghitung total potongan
                                            $query_potongan = "SELECT SUM(p.jumlah) as total_potongan 
                                                               FROM potongan_gaji p
                                                               JOIN penggajian_potongan pp ON pp.id_potongan = p.id_potongan
                                                               WHERE pp.id_penggajian = '{$row['id_penggajian']}'";
                                            $result_potongan = mysqli_query($conn, $query_potongan);
                                            $potongan = mysqli_fetch_assoc($result_potongan);
                                            $total_potongan = $potongan['total_potongan'] ?? 0;

                                            // Menghitung total gaji bersih
                                            $total_gaji = $row['gaji_pokok'] + $row['bonus'] - $total_potongan;
                                        ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['nama_peg']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_jabatan']) ?></td>
                                            <td>Rp <?= number_format($row['gaji_pokok'], 0, ',', '.') ?></td>
                                            <td>Rp <?= number_format($row['bonus'], 0, ',', '.') ?></td>
                                            <td>Rp <?= number_format($total_potongan, 0, ',', '.') ?></td>
                                            <td>Rp <?= number_format($total_gaji, 0, ',', '.') ?></td>
                                            <td>
                                                <a href="detail_gaji.php?id_jabatan=<?= $row['id_jabatan'] ?>&id_pegawai=<?= $row['id_peg'] ?>" 
                                                   class="btn btn-info btn-sm">
                                                    <i class="bi bi-eye"></i> Detail
                                                </a>
                                                <a href="edit.php?id=<?= $row['id_penggajian'] ?>" 
                                                   class="btn btn-warning btn-sm">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <a href="hapus.php?id=<?= $row['id_penggajian'] ?>" 
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </a>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="index.php?page=<?= max(1, $page - 1) ?>&search=<?= urlencode($search) ?>">Previous</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="index.php?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="index.php?page=<?= min($total_pages, $page + 1) ?>&search=<?= urlencode($search) ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php
require_once "../template/footer.php";
?>

<!--mode 6-->
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
#main {
    position: relative;
    min-height: 100vh;
    overflow: hidden;
    padding: 20px;
}

.bg {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    pointer-events: none;
    z-index: 0;
}

.trees {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: 1;
    pointer-events: none;
}

.girl {
    position: fixed;
    bottom: 0;
    scale: 0.65;
    pointer-events: none;
    animation: animateGirl 10s linear infinite;
    z-index: 2;
}

@keyframes animateGirl {
    0% {
        transform: translateX(calc(100% + 100vw));
    }
    50% {
        transform: translateX(calc(-100% - 100vw));
    }
    50.01% {
        transform: translateX(calc(-100% - 100vw)) rotateY(180deg);
    }
    100% {
        transform: translateX(calc(100% + 100vw)) rotateY(180deg);
    }
}

.leaves {
    position: fixed;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: 3;
    pointer-events: none;
}

.leaves .set {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    pointer-events: none;
}

.leaves .set div {
    position: absolute;
    display: block;
}

@keyframes animate {
    0% {
        opacity: 0;
        top: -10%;
        transform: translateX(20px) rotate(0deg);
    }
    10% {
        opacity: 1;
    }
    20% {
        transform: translateX(-20px) rotate(45deg);
    }
    40% {
        transform: translateX(-20px) rotate(90deg);
    }
    60% {
        transform: translateX(20px) rotate(180deg);
    }
    80% {
        transform: translateX(-20px) rotate(45deg);
    }
    100% {
        top: 110%;
        transform: translateX(20px) rotate(225deg);
    }
}

.leaves .set div:nth-child(1) { left: 20%; animation: animate 20s linear infinite; }
.leaves .set div:nth-child(2) { left: 50%; animation: animate 14s linear infinite; }
.leaves .set div:nth-child(3) { left: 70%; animation: animate 12s linear infinite; }
.leaves .set div:nth-child(4) { left: 5%; animation: animate 15s linear infinite; }
.leaves .set div:nth-child(5) { left: 85%; animation: animate 18s linear infinite; }
.leaves .set div:nth-child(6) { left: 90%; animation: animate 12s linear infinite; }
.leaves .set div:nth-child(7) { left: 15%; animation: animate 14s linear infinite; }
.leaves .set div:nth-child(8) { left: 60%; animation: animate 15s linear infinite; }

.content-wrapper {
    position: relative;
    z-index: 5;
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 20px;
    padding: 25px;
    margin: 20px auto;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.card {
    background: rgba(255, 255, 255, 0.2) !important;
    backdrop-filter: blur(10px) !important;
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
    box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1) !important;
}

.table {
    color: #333;
}

.table thead th {
    background: rgba(0, 0, 0, 0.4);
    color: white;
    border: none;
}

.table td {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.2);
}

.btn-primary {
    background: rgba(13, 110, 253, 0.8) !important;
    border: none !important;
    backdrop-filter: blur(10px);
}

.btn-primary:hover {
    background: rgba(13, 110, 253, 1) !important;
    transform: translateY(-2px);
}
</style>

<main id="main" class="main">
    <!-- Background Elements -->
    <img src="../bahan/bg.jpg" class="bg">
    <img src="../bahan/trees.png" class="trees">
    <img src="../bahan/girl.png" class="girl">
    
    <!-- Falling Leaves -->
    <div class="leaves">
        <div class="set">
            <div><img src="../bahan/leaf_01.png"></div>
            <div><img src="../bahan/leaf_02.png"></div>
            <div><img src="../bahan/leaf_03.png"></div>
            <div><img src="../bahan/leaf_04.png"></div>
            <div><img src="../bahan/leaf_01.png"></div>
            <div><img src="../bahan/leaf_02.png"></div>
            <div><img src="../bahan/leaf_03.png"></div>
            <div><img src="../bahan/leaf_04.png"></div>
        </div>
    </div>

    <!-- Content -->
    <div class="content-wrapper">
        <div class="pagetitle">
            <h1>Data Penggajian</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item active">Penggajian</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Daftar Penggajian Karyawan</h5>

                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <!-- Tombol-tombol -->
                                    <a href="tambah.php" class="btn btn-primary">
                                        <i class="bi bi-plus"></i> Tambah Data Penggajian
                                    </a>
                                    <a href="potong_gaji.php" class="btn btn-secondary">
                                        <i class="bi bi-file-earmark-plus"></i> Potongan Gaji
                                    </a>
                                    <a href="tunjangan.php" class="btn btn-secondary">
                                        <i class="bi bi-file-earmark-plus"></i> Tunjangan
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <!-- Form Pencarian -->
                                    <form method="GET" class="d-flex">
                                        <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Cari nama pegawai" value="<?= htmlspecialchars($search) ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
                                    </form>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped datatable">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Pegawai</th>
                                            <th>Jabatan</th>
                                            <th>Gaji Pokok</th>
                                            <th>Bonus</th>
                                            <th>Total Potongan</th>
                                            <th>Total Gaji Bersih</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = $offset + 1;
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            // Menghitung total potongan
                                            $query_potongan = "SELECT SUM(p.jumlah) as total_potongan 
                                                               FROM potongan_gaji p
                                                               JOIN penggajian_potongan pp ON pp.id_potongan = p.id_potongan
                                                               WHERE pp.id_penggajian = '{$row['id_penggajian']}'";
                                            $result_potongan = mysqli_query($conn, $query_potongan);
                                            $potongan = mysqli_fetch_assoc($result_potongan);
                                            $total_potongan = $potongan['total_potongan'] ?? 0;

                                            // Menghitung total gaji bersih
                                            $total_gaji = $row['gaji_pokok'] + $row['bonus'] - $total_potongan;
                                        ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['nama_peg']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_jabatan']) ?></td>
                                            <td>Rp <?= number_format($row['gaji_pokok'], 0, ',', '.') ?></td>
                                            <td>Rp <?= number_format($row['bonus'], 0, ',', '.') ?></td>
                                            <td>Rp <?= number_format($total_potongan, 0, ',', '.') ?></td>
                                            <td>Rp <?= number_format($total_gaji, 0, ',', '.') ?></td>
                                            <td>
                                                <a href="detail_gaji.php?id_jabatan=<?= $row['id_jabatan'] ?>&id_pegawai=<?= $row['id_peg'] ?>" 
                                                   class="btn btn-info btn-sm">
                                                    <i class="bi bi-eye"></i> Detail
                                                </a>
                                                <a href="edit.php?id=<?= $row['id_penggajian'] ?>" 
                                                   class="btn btn-warning btn-sm">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <a href="hapus.php?id=<?= $row['id_penggajian'] ?>" 
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </a>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="index.php?page=<?= max(1, $page - 1) ?>&search=<?= urlencode($search) ?>">Previous</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="index.php?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="index.php?page=<?= min($total_pages, $page + 1) ?>&search=<?= urlencode($search) ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php
require_once "../template/footer.php";
?>

