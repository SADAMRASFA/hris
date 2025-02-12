<?php
include "../connection.php"; 
require_once "../template/header.php";
require_once "../template/sidebar.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Cek apakah parameter ada di URL
if (!isset($_GET['id_jabatan']) || !isset($_GET['id_pegawai'])) {
    die("<p style='color:red;'>Error: ID jabatan atau ID pegawai tidak ditemukan.</p>");
}

$id_jabatan = intval($_GET['id_jabatan']);
$id_pegawai = intval($_GET['id_pegawai']);

// Query data pegawai, jabatan, dan gaji
$query = "SELECT p.*, j.nama_jabatan, g.* 
          FROM pegawai p 
          JOIN jabatan j ON j.id_jabatan = p.id_jabatan 
          LEFT JOIN penggajian g ON g.id_peg = p.id_peg 
          WHERE p.id_peg = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_pegawai);
$stmt->execute();
$pegawai = $stmt->get_result()->fetch_object();
$stmt->close();

if (!$pegawai) {
    die("<p style='color:red;'>Error: Data pegawai tidak ditemukan.</p>");
}

// Query untuk mengambil detail potongan
$query_potongan = "SELECT pg.*, pp.id_penggajian 
                   FROM potongan_gaji pg
                   JOIN penggajian_potongan pp ON pg.id_potongan = pp.id_potongan
                   WHERE pp.id_penggajian = ?";
$stmt = $conn->prepare($query_potongan);
$stmt->bind_param("i", $pegawai->id_penggajian);
$stmt->execute();
$result_potongan = $stmt->get_result();

$potongan_detail = [];
while ($row = $result_potongan->fetch_assoc()) {
    $potongan_detail[] = $row;
}
$stmt->close();

// Query untuk mengambil data tunjangan berdasarkan id_jabatan
$query_tunjangan = "SELECT * FROM tunjangan WHERE id_jabatan = ?";
$stmt = $conn->prepare($query_tunjangan);
$stmt->bind_param("i", $id_jabatan);
$stmt->execute();
$result_tunjangan = $stmt->get_result();

$tunjangan_detail = [];
$total_tunjangan = 0;
while ($row = $result_tunjangan->fetch_assoc()) {
    $tunjangan_detail[] = $row;
    $total_tunjangan += $row['jumlah'];
}
$stmt->close();

// Hitung total semua potongan
$total_potongan_tambahan = array_sum(array_column($potongan_detail, 'jumlah'));
$total_potongan = $total_potongan_tambahan + $pegawai->potongan_bpjs + $pegawai->potongan_pajak + $pegawai->potongan_lain;

// Hitung total gaji bersih (gaji pokok + bonus + total tunjangan - total potongan)
$total_gaji_bersih = $pegawai->gaji_pokok + $pegawai->bonus + $total_tunjangan - $total_potongan;
?>

<style>
/* Styles untuk tampilan web */
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
}

.card {
    border: none;
    border-radius: 16px;
    background: linear-gradient(145deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.85));
    box-shadow: 0 10px 25px rgba(37, 99, 235, 0.1);
    backdrop-filter: blur(10px);
}

.card-title {
    color: var(--dark);
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid rgba(37, 99, 235, 0.1);
}

.table {
    margin-bottom: 0;
}

.table th {
    background-color: #f8fafc;
    font-weight: 600;
    padding: 12px 16px;
}

.table td {
    padding: 12px 16px;
    vertical-align: middle;
}

.bg-light {
    background-color: #f1f5f9 !important;
    color: #1e293b;
    font-weight: 600;
}

.table-warning {
    background-color: rgba(245, 158, 11, 0.1) !important;
}

.table-info {
    background-color: rgba(2, 132, 199, 0.1) !important;
}

.table-success {
    background-color: rgba(5, 150, 105, 0.1) !important;
}

.btn {
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #2563eb, #1e40af);
    border: none;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
}

.btn-secondary {
    background: linear-gradient(135deg, #64748b, #475569);
    border: none;
    box-shadow: 0 4px 12px rgba(100, 116, 139, 0.2);
}

/* Styles khusus untuk cetakan */
@media print {
    @page {
        size: A4;
        margin: 2cm;
    }

    body {
        font-family: 'Times New Roman', Times, serif;
        font-size: 12pt;
        line-height: 1.3;
        background: #fff;
        color: #000;
    }

    .card {
        box-shadow: none;
        border: none;
        padding: 0;
        background: none;
    }

    .main, .card-body {
        padding: 0 !important;
    }

    .pagetitle, .breadcrumb, .btn, footer, header, .sidebar, .header {
        display: none !important;
    }

    /* Header Surat */
    .print-header {
        text-align: center;
        border-bottom: 3px double #000;
        padding-bottom: 10px;
        margin-bottom: 20px;
        display: block !important;
    }

    .print-header h2 {
        margin: 0;
        font-size: 16pt;
        font-weight: bold;
    }

    .print-header p {
        margin: 5px 0;
        font-size: 11pt;
    }

    /* Informasi Slip Gaji */
    .slip-info {
        margin: 20px 0;
        display: block !important;
    }

    .slip-info table {
        width: 100%;
        border-collapse: collapse;
    }

    .slip-info td, .slip-info th {
        padding: 5px 10px;
        border: none;
        text-align: left;
    }

    /* Tanda Tangan */
    .signature {
        margin-top: 50px;
        text-align: right;
        display: block !important;
    }

    .signature p {
        margin: 5px 0;
    }

    /* Table styling untuk cetakan */
    .table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }

    .table th, .table td {
        border: 1px solid #000;
        padding: 8px;
        text-align: left;
    }

    .bg-light, .table-warning, .table-info, .table-success {
        background-color: transparent !important;
    }

    .table-warning th, .table-info th, .table-success th {
        font-weight: bold;
    }
}

/* Sembunyikan elemen print saat mode web */
.print-header, .slip-info, .signature {
    display: none;
}
</style>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Detail Gaji Karyawan</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php">Penggajian</a></li>
                <li class="breadcrumb-item active">Detail Gaji</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Header Surat untuk Cetakan -->
                        <div class="print-header">
                            <h2>PT. NAMA PERUSAHAAN</h2>
                            <p>Jl. Alamat Perusahaan No. 123, Kota</p>
                            <p>Telp: (021) 1234567 | Email: info@perusahaan.com</p>
                            <h3>SLIP GAJI KARYAWAN</h3>
                            <p>Periode: <?= date('F Y') ?></p>
                        </div>

                        <!-- Informasi Karyawan untuk Cetakan -->
                        <div class="slip-info">
                            <table>
                                <tr>
                                    <td width="150">Nama Karyawan</td>
                                    <td width="10">:</td>
                                    <td><?= htmlspecialchars($pegawai->nama_peg) ?></td>
                                </tr>
                                <tr>
                                    <td>Jabatan</td>
                                    <td>:</td>
                                    <td><?= htmlspecialchars($pegawai->nama_jabatan) ?></td>
                                </tr>
                                <tr>
                                    <td>Periode Gaji</td>
                                    <td>:</td>
                                    <td><?= date('F Y') ?></td>
                                </tr>
                            </table>
                        </div>

                        <h5 class="card-title mb-2">Detail Gaji <?= htmlspecialchars($pegawai->nama_peg) ?></h5>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <tr><th>Nama Pegawai</th><td><?= htmlspecialchars($pegawai->nama_peg) ?></td></tr>
                                <tr><th>Jabatan</th><td><?= htmlspecialchars($pegawai->nama_jabatan) ?></td></tr>
                                <tr><th>Gaji Pokok</th><td>Rp <?= number_format($pegawai->gaji_pokok, 0, ',', '.') ?></td></tr>
                                <tr><th>Bonus</th><td>Rp <?= number_format($pegawai->bonus, 0, ',', '.') ?></td></tr>
                                
                                <tr><th colspan="2" class="bg-light">Detail Tunjangan</th></tr>
                                <?php foreach ($tunjangan_detail as $tunjangan): ?>
                                <tr>
                                    <th>Tunjangan <?= htmlspecialchars($tunjangan['nama_tunjangan']) ?></th>
                                    <td>Rp <?= number_format($tunjangan['jumlah'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="table-warning">
                                    <th>Total Tunjangan</th>
                                    <td>Rp <?= number_format($total_tunjangan, 0, ',', '.') ?></td>
                                </tr>

                                <tr><th colspan="2" class="bg-light">Detail Potongan</th></tr>
                                <?php foreach ($potongan_detail as $potongan): ?>
                                <tr><th>Potongan <?= htmlspecialchars($potongan['nama_potongan']) ?></th><td>Rp <?= number_format($potongan['jumlah'], 0, ',', '.') ?></td></tr>
                                <?php endforeach; ?>
                                
                                <?php if ($pegawai->potongan_bpjs > 0): ?>
                                <tr><th>Potongan BPJS</th><td>Rp <?= number_format($pegawai->potongan_bpjs, 0, ',', '.') ?></td></tr>
                                <?php endif; ?>
                                
                                <?php if ($pegawai->potongan_pajak > 0): ?>
                                <tr><th>Potongan Pajak</th><td>Rp <?= number_format($pegawai->potongan_pajak, 0, ',', '.') ?></td></tr>
                                <?php endif; ?>
                                
                                <?php if ($pegawai->potongan_lain > 0): ?>
                                <tr><th>Potongan Lain</th><td>Rp <?= number_format($pegawai->potongan_lain, 0, ',', '.') ?></td></tr>
                                <?php endif; ?>
                                
                                <tr class="table-info">
                                    <th>Total Potongan</th>
                                    <td>Rp <?= number_format($total_potongan, 0, ',', '.') ?></td>
                                </tr>
                                
                                <tr class="table-success">
                                    <th>Total Gaji Bersih</th>
                                    <td><strong>Rp <?= number_format($total_gaji_bersih, 0, ',', '.') ?></strong></td>
                                </tr>
                            </table>
                        </div>

                        <!-- Tanda Tangan untuk Cetakan -->
                        <div class="signature">
                            <p><?= date('d F Y') ?></p>
                            <p>Manajer HRD,</p>
                            <br><br><br>
                            <p><u>Nama Manajer HRD</u></p>
                            <p>NIP. 123456789</p>
                        </div>

                        <div class="text-end mt-3 no-print">
                            <button onclick="printSlip()" class="btn btn-primary">
                                <i class="bi bi-printer"></i> Cetak Slip Gaji
                            </button>
                            <a href="download_slip.php?id_jabatan=<?= $id_jabatan ?>&id_pegawai=<?= $id_pegawai ?>" class="btn btn-success">
                                <i class="bi bi-download"></i> Unduh PDF
                            </a>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
function printSlip() {
    window.print();
}
</script>

<?php require_once "../template/footer.php"; ?>
