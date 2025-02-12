<?php
include "../connection.php"; // Pastikan koneksi ke database sudah benar

if (isset($_GET['id'])) {
    $id_penggajian = $_GET['id'];

    // Query hapus data
    $query = "DELETE FROM penggajian WHERE id_penggajian = '$id_penggajian'";
    

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data berhasil dihapus!'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data: " . mysqli_error($conn) . "');</script>";
    }
} else {
    echo "<script>alert('ID tidak ditemukan!'); window.location='index.php';</script>";
}
?>
