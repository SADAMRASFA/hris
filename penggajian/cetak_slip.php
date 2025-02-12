<?php
include "../connection.php";

// Ambil data dari parameter URL
$id_jabatan = intval($_GET['id_jabatan']);
$id_pegawai = intval($_GET['id_pegawai']);

// Query data pegawai dan jabatan
$query_pegawai = "SELECT p.*, j.nama_jabatan 
                  FROM pegawai p 
                  JOIN jabatan j ON j.id_jabatan = p.id_jabatan 
                  WHERE p.id_peg = ?";
$stmt = $conn->prepare($query_pegawai);
$stmt->bind_param("i", $id_pegawai);
$stmt->execute();
$pegawai = $stmt->get_result()->fetch_object();
$stmt->close();

// Query data gaji
$query_gaji = "SELECT * FROM penggajian WHERE id_jabatan = ? AND id_peg = ?";
$stmt = $conn->prepare($query_gaji);
$stmt->bind_param("ii", $id_jabatan, $id_pegawai);
$stmt->execute();
$gaji = $stmt->get_result()->fetch_object();
$stmt->close();

// Query untuk mengambil detail potongan
$query_potongan = "SELECT pg.*, pp.id_penggajian 
                   FROM potongan_gaji pg
                   JOIN penggajian_potongan pp ON pg.id_potongan = pp.id_potongan
                   WHERE pp.id_penggajian = ?";
$stmt = $conn->prepare($query_potongan);
$stmt->bind_param("i", $gaji->id_penggajian);
$stmt->execute();
$result_potongan = $stmt->get_result();

$potongan_detail = [];
while ($row = $result_potongan->fetch_assoc()) {
    $potongan_detail[] = $row;
}
$stmt->close();

// Hitung total potongan
$total_potongan_tambahan = 0;
foreach ($potongan_detail as $potongan) {
    $total_potongan_tambahan += $potongan['jumlah'];
}

$total_potongan = $total_potongan_tambahan + 
                  $gaji->potongan_bpjs + 
                  $gaji->potongan_pajak + 
                  $gaji->potongan_lain;

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Slip Gaji</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            color: #000;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 8px;
            border: 1px solid #000;
            text-align: left;
        }
        .table th {
            background-color: #f0f0f0;
        }
        .table td {
            text-align: right;
        }
        .table .info {
            background-color: #f9f9f9;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <h2 style="text-align:center;">Slip Gaji <?= htmlspecialchars($pegawai->nama_peg) ?></h2>

    <table class="table">
        <tr>
            <th>Nama Pegawai</th>
            <td><?= htmlspecialchars($pegawai->nama_peg) ?></td>
        </tr>
        <tr>
            <th>Jabatan</th>
            <td><?= htmlspecialchars($pegawai->nama_jabatan) ?></td>
        </tr>
        <tr>
            <th>Gaji Pokok</th>
            <td>Rp <?= number_format($gaji->gaji_pokok, 0, ',', '.') ?></td>
        </tr>
        <tr>
            <th>Bonus</th>
            <td>Rp <?= number_format($gaji->bonus, 0, ',', '.') ?></td>
        </tr>

        <tr>
            <th colspan="2" class="bg-light">Detail Potongan</th>
        </tr>

        <?php foreach ($potongan_detail as $potongan): ?>
        <tr>
            <th>Potongan <?= htmlspecialchars(ucfirst($potongan['nama_potongan'])) ?></th>
            <td>Rp <?= number_format($potongan['jumlah'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>

        <?php if ($gaji->potongan_bpjs > 0): ?>
        <tr>
            <th>Potongan BPJS</th>
            <td>Rp <?= number_format($gaji->potongan_bpjs, 0, ',', '.') ?></td>
        </tr>
        <?php endif; ?>

        <?php if ($gaji->potongan_pajak > 0): ?>
        <tr>
            <th>Potongan Pajak</th>
            <td>Rp <?= number_format($gaji->potongan_pajak, 0, ',', '.') ?></td>
        </tr>
        <?php endif; ?>

        <?php if ($gaji->potongan_lain > 0): ?>
        <tr>
            <th>Potongan Lain</th>
            <td>Rp <?= number_format($gaji->potongan_lain, 0, ',', '.') ?></td>
        </tr>
        <?php endif; ?>

        <tr class="info">
            <th>Total Potongan</th>
            <td>Rp <?= number_format($total_potongan, 0, ',', '.') ?></td>
        </tr>

        <tr>
            <th>Total Gaji Bersih</th>
            <td><strong>Rp <?= number_format($gaji->gaji_pokok + $gaji->bonus - $total_potongan, 0, ',', '.') ?></strong></td>
        </tr>
    </table>

    <p style="text-align:right;">Tertanda, <br><br> <strong>Manager Keuangan</strong></p>

    <button class="no-print" onclick="window.print()">Cetak Slip Gaji</button>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>

</body>
</html>
