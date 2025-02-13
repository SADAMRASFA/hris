<?php
include '../template/header.php';
include '../template/sidebar.php';
include '../connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $pegawai_id = $_POST['pegawai_id'];
    $nomor_kontrak = $_POST['nomor_kontrak'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_berakhir = $_POST['tanggal_berakhir'];
    $jabatan = $_POST['jabatan'];
    $gaji = $_POST['gaji'];
    $status_kontrak = $_POST['status_kontrak'];

    // Query untuk menyimpan data ke database
    $stmt = $conn->prepare("INSERT INTO kontrak_pegawai (pegawai_id, nomor_kontrak, tanggal_mulai, tanggal_berakhir, jabatan, gaji, status_kontrak) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param("issssds", $pegawai_id, $nomor_kontrak, $tanggal_mulai, $tanggal_berakhir, $jabatan, $gaji, $status_kontrak);
        
        if ($stmt->execute()) {
            echo "<script>alert('Kontrak pegawai berhasil ditambahkan!'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Gagal menambahkan kontrak pegawai: " . addslashes($stmt->error) . "');</script>";
        }
        
        $stmt->close();
    } else {
        echo "<script>alert('Gagal mempersiapkan query: " . addslashes($conn->error) . "');</script>";
    }
}
?>

<main id="main" class="main">
    <div class="container">
        <h1>Tambah Kontrak Pegawai</h1>
        <form method="post" action="">
            <div class="mb-3">
                <label for="pegawai_id" class="form-label">ID Pegawai</label>
                <input type="number" class="form-control" id="pegawai_id" name="pegawai_id" required>
            </div>
            <div class="mb-3">
                <label for="nomor_kontrak" class="form-label">Nomor Kontrak</label>
                <input type="text" class="form-control" id="nomor_kontrak" name="nomor_kontrak" required>
            </div>
            <div class="mb-3">
                <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
            </div>
            <div class="mb-3">
                <label for="tanggal_berakhir" class="form-label">Tanggal Berakhir</label>
                <input type="date" class="form-control" id="tanggal_berakhir" name="tanggal_berakhir" required>
            </div>
            <div class="mb-3">
                <label for="jabatan" class="form-label">Jabatan</label>
                <input type="text" class="form-control" id="jabatan" name="jabatan" required>
            </div>
            <div class="mb-3">
                <label for="gaji" class="form-label">Gaji</label>
                <input type="number" class="form-control" id="gaji" name="gaji" required>
            </div>
            <div class="mb-3">
                <label for="status_kontrak" class="form-label">Status Kontrak</label>
                <select class="form-select" id="status_kontrak" name="status_kontrak" required>
                    <option value="Aktif">Aktif</option>
                    <option value="Nonaktif">Nonaktif</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</main>

<?php include '../template/footer.php'; ?>