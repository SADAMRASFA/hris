<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../vendor/autoload.php';
include "../connection.php";

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['id_jabatan']) || !isset($_GET['id_pegawai'])) {
    die("ID tidak valid");
}

$id_jabatan = intval($_GET['id_jabatan']);
$id_pegawai = intval($_GET['id_pegawai']);

// Query data pegawai
$query = "SELECT p.*, j.nama_jabatan, g.* 
          FROM pegawai p 
          JOIN jabatan j ON j.id_jabatan = p.id_jabatan 
          LEFT JOIN penggajian g ON g.id_peg = p.id_peg 
          WHERE p.id_peg = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_pegawai);
$stmt->execute();
$pegawai = $stmt->get_result()->fetch_object();

// Query tunjangan
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

// Query potongan
$query_potongan = "SELECT pg.*, pp.id_penggajian 
                   FROM potongan_gaji pg
                   JOIN penggajian_potongan pp ON pg.id_potongan = pp.id_potongan
                   WHERE pp.id_penggajian = ?";
$stmt = $conn->prepare($query_potongan);
$stmt->bind_param("i", $pegawai->id_penggajian);
$stmt->execute();
$result_potongan = $stmt->get_result();

$potongan_detail = [];
$total_potongan_tambahan = 0;
while ($row = $result_potongan->fetch_assoc()) {
    $potongan_detail[] = $row;
    $total_potongan_tambahan += $row['jumlah'];
}

$total_potongan = $total_potongan_tambahan + $pegawai->potongan_bpjs + $pegawai->potongan_pajak + $pegawai->potongan_lain;
$total_gaji_bersih = $pegawai->gaji_pokok + $pegawai->bonus + $total_tunjangan - $total_potongan;

// Generate HTML
$html = '
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .company-name { font-size: 20px; font-weight: bold; }
        .slip-title { font-size: 16px; font-weight: bold; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; border: 1px solid #ddd; }
        th { background-color: #f5f5f5; }
        .total { font-weight: bold; }
        .signature { text-align: right; margin-top: 50px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">PT. NAMA PERUSAHAAN</div>
        <div>Jl. Alamat Perusahaan No. 123, Kota</div>
        <div>Telp: (021) 1234567 | Email: info@perusahaan.com</div>
    </div>

    <div class="slip-title">SLIP GAJI KARYAWAN</div>
    <div>Periode: ' . date('F Y') . '</div>

    <table>
        <tr>
            <td width="150">Nama Karyawan</td>
            <td>: ' . htmlspecialchars($pegawai->nama_peg) . '</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>: ' . htmlspecialchars($pegawai->nama_jabatan) . '</td>
        </tr>
    </table>

    <div class="slip-title">RINCIAN GAJI</div>
    <table>
        <tr>
            <th>KETERANGAN</th>
            <th>JUMLAH</th>
        </tr>
        <tr>
            <td>Gaji Pokok</td>
            <td align="right">Rp ' . number_format($pegawai->gaji_pokok, 0, ',', '.') . '</td>
        </tr>';

if ($pegawai->bonus > 0) {
    $html .= '
        <tr>
            <td>Bonus</td>
            <td align="right">Rp ' . number_format($pegawai->bonus, 0, ',', '.') . '</td>
        </tr>';
}

foreach ($tunjangan_detail as $tunjangan) {
    $html .= '
        <tr>
            <td>Tunjangan ' . htmlspecialchars($tunjangan['nama_tunjangan']) . '</td>
            <td align="right">Rp ' . number_format($tunjangan['jumlah'], 0, ',', '.') . '</td>
        </tr>';
}

$html .= '
        <tr class="total">
            <td>Total Tunjangan</td>
            <td align="right">Rp ' . number_format($total_tunjangan, 0, ',', '.') . '</td>
        </tr>
    </table>

    <div class="slip-title">RINCIAN POTONGAN</div>
    <table>
        <tr>
            <th>KETERANGAN</th>
            <th>JUMLAH</th>
        </tr>';

foreach ($potongan_detail as $potongan) {
    $html .= '
        <tr>
            <td>Potongan ' . htmlspecialchars($potongan['nama_potongan']) . '</td>
            <td align="right">Rp ' . number_format($potongan['jumlah'], 0, ',', '.') . '</td>
        </tr>';
}

if ($pegawai->potongan_bpjs > 0) {
    $html .= '
        <tr>
            <td>Potongan BPJS</td>
            <td align="right">Rp ' . number_format($pegawai->potongan_bpjs, 0, ',', '.') . '</td>
        </tr>';
}

if ($pegawai->potongan_pajak > 0) {
    $html .= '
        <tr>
            <td>Potongan Pajak</td>
            <td align="right">Rp ' . number_format($pegawai->potongan_pajak, 0, ',', '.') . '</td>
        </tr>';
}

if ($pegawai->potongan_lain > 0) {
    $html .= '
        <tr>
            <td>Potongan Lain</td>
            <td align="right">Rp ' . number_format($pegawai->potongan_lain, 0, ',', '.') . '</td>
        </tr>';
}

$html .= '
        <tr class="total">
            <td>Total Potongan</td>
            <td align="right">Rp ' . number_format($total_potongan, 0, ',', '.') . '</td>
        </tr>
    </table>

    <table>
        <tr class="total">
            <td>TOTAL GAJI BERSIH</td>
            <td align="right">Rp ' . number_format($total_gaji_bersih, 0, ',', '.') . '</td>
        </tr>
    </table>

    <div class="signature">
        <div>' . date('d F Y') . '</div>
        <div>Manajer HRD,</div>
        <br><br><br>
        <div><u>Nama Manajer HRD</u></div>
        <div>NIP. 123456789</div>
    </div>
</body>
</html>';

// Initialize Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output PDF
$dompdf->stream('Slip_Gaji_' . $pegawai->nama_peg . '_' . date('F_Y') . '.pdf', array('Attachment' => true));
?> 