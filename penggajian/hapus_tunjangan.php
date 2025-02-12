<?php
// Matikan semua error reporting
error_reporting(0);
ini_set('display_errors', 0);

// Load koneksi database
require_once "../connection.php";

// Set header JSON
header('Content-Type: application/json');

// Inisialisasi response
$response = ['status' => 'error', 'message' => ''];

// Cek apakah ada ID
if (!isset($_GET['id'])) {
    $response['message'] = 'ID tidak ditemukan';
    echo json_encode($response);
    exit;
}

// Ambil ID
$id_tunjangan = (int)$_GET['id'];

// Hapus data
$query = "DELETE FROM tunjangan WHERE id_tunjangan = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_tunjangan);

if ($stmt->execute()) {
    $response['status'] = 'success';
    $response['message'] = 'Data berhasil dihapus';
} else {
    $response['message'] = 'Gagal menghapus data';
}

// Output JSON
echo json_encode($response);
exit; 