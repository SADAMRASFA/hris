<?php
include '../connection.php';

// Cek apakah data dikirim melalui POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $id_karyawan = $_POST['id_karyawan'];
    $tanggal_pengajuan = $_POST['tanggal_pengajuan'];
    $tanggal_efektif = $_POST['tanggal_efektif'];
    $alasan = $_POST['alasan'];
    $status_pengajuan = $_POST['status_pengajuan'];

    // Cek apakah semua data sudah ada
    if (empty($id_karyawan) || empty($tanggal_pengajuan) || empty($tanggal_efektif) || empty($alasan) || empty($status_pengajuan)) {
        echo "Semua data harus diisi!";
        exit();
    }

    // Query untuk insert data ke database
    $query = "INSERT INTO pengunduran_diri (id_karyawan, tanggal_pengajuan, tanggal_efektif, alasan, status_pengajuan)
              VALUES (?, ?, ?, ?, ?)";

    // Persiapkan statement
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("issss", $id_karyawan, $tanggal_pengajuan, $tanggal_efektif, $alasan, $status_pengajuan);

        // Eksekusi query
        if ($stmt->execute()) {
            // Redirect ke halaman utama (dashboard) setelah berhasil insert
            header("Location: index.php?status=success");
            exit();
        } else {
            echo "Gagal mengeksekusi query: " . $stmt->error;
        }
    } else {
        echo "Gagal mempersiapkan query: " . $conn->error;
    }
}
?>
