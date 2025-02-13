<?php
// Start session di awal file
session_start();

// Perbaiki path koneksi - karena file koneksi.php ada di folder config yang sejajar dengan promosi_mutasi
require_once '../config/koneksi.php';

// Cek koneksi
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Simpan pesan dalam session
function setMessage($message, $type) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    
    // Tambahkan debug info jika type adalah danger
    if ($type === 'danger') {
        error_log("Error in promosi_mutasi.php: " . $message);
    }
}

// Ambil pesan dari session
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : '';

// Hapus pesan dari session setelah diambil
unset($_SESSION['message']);
unset($_SESSION['message_type']);

// Proses hapus data
if (isset($_GET['delete']) && isset($_GET['tab'])) {
    $id = $mysqli->real_escape_string($_GET['delete']);
    $tab = $_GET['tab'];
    
    if ($tab == 'mutasi') {
        // Cek apakah data ada
        $check_query = "SELECT id_mutasi FROM mutasi WHERE id_mutasi = ?";
        $check_stmt = $mysqli->prepare($check_query);
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Data ditemukan, lakukan penghapusan
            $delete_query = "DELETE FROM mutasi WHERE id_mutasi = ?";
            if ($delete_stmt = $mysqli->prepare($delete_query)) {
                $delete_stmt->bind_param("i", $id);
                
                if ($delete_stmt->execute()) {
                    setMessage("Data mutasi berhasil dihapus!", "success");
                } else {
                    setMessage("Error: Gagal menghapus data mutasi.", "danger");
                }
                $delete_stmt->close();
            } else {
                setMessage("Error: " . $mysqli->error, "danger");
            }
        } else {
            setMessage("Error: Data mutasi tidak ditemukan.", "warning");
        }
        $check_stmt->close();
    } elseif ($tab == 'promosi') {
        // Cek apakah data ada
        $check_query = "SELECT id_peg FROM promosi WHERE id_peg = ?";
        $check_stmt = $mysqli->prepare($check_query);
        $check_stmt->bind_param("s", $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Data ditemukan, lakukan penghapusan
            $delete_query = "DELETE FROM promosi WHERE id_peg = ?";
            if ($delete_stmt = $mysqli->prepare($delete_query)) {
                $delete_stmt->bind_param("s", $id);
                
                if ($delete_stmt->execute()) {
                    setMessage("Data promosi berhasil dihapus!", "success");
                } else {
                    setMessage("Error: Gagal menghapus data promosi.", "danger");
                }
                $delete_stmt->close();
            } else {
                setMessage("Error: " . $mysqli->error, "danger");
            }
        } else {
            setMessage("Error: Data promosi tidak ditemukan.", "warning");
        }
        $check_stmt->close();
    }
    
    // Redirect kembali ke halaman dengan tab yang sama
    header("Location: promosi_mutasi.php?tab=" . $tab);
    exit();
}

// Inisialisasi variabel
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'promosi';
$search = isset($_GET['search']) ? $mysqli->real_escape_string($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 3;
$start = ($page - 1) * $per_page;

// Query sesuai tab aktif dengan pencarian dan JOIN ke tabel pegawai
if ($active_tab == 'promosi') {
    $count_query = "SELECT COUNT(*) as total FROM promosi p 
                    JOIN pegawai pg ON p.id_peg = pg.id_peg";
    $base_query = "SELECT p.*, pg.nama_peg as nama_pegawai 
                   FROM promosi p 
                   JOIN pegawai pg ON p.id_peg = pg.id_peg";
    if (!empty($search)) {
        $where_clause = " WHERE p.id_peg LIKE '%$search%' 
                         OR pg.nama_peg LIKE '%$search%'
                         OR p.jbt_lama LIKE '%$search%' 
                         OR p.jbt_baru LIKE '%$search%' 
                         OR p.alasan_promosi LIKE '%$search%'";
        $count_query .= $where_clause;
        $base_query .= $where_clause;
    }
} else {
    $count_query = "SELECT COUNT(*) as total FROM mutasi m 
                    JOIN pegawai pg ON m.id_peg = pg.id_peg";
    $base_query = "SELECT m.*, pg.nama_peg as nama_pegawai 
                   FROM mutasi m 
                   JOIN pegawai pg ON m.id_peg = pg.id_peg";
    if (!empty($search)) {
        $where_clause = " WHERE m.id_peg LIKE '%$search%' 
                         OR pg.nama_peg LIKE '%$search%'
                         OR m.jbt_lama LIKE '%$search%' 
                         OR m.jbt_baru LIKE '%$search%' 
                         OR m.dpm_lama LIKE '%$search%' 
                         OR m.dpm_baru LIKE '%$search%' 
                         OR m.alasan_mutasi LIKE '%$search%' 
                         OR m.status_mutasi LIKE '%$search%'";
        $count_query .= $where_clause;
        $base_query .= $where_clause;
    }
}

// Hitung total records dan halaman
$total_result = $mysqli->query($count_query);
$total_records = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $per_page);

// Query final dengan LIMIT untuk pagination
$query = $base_query . " LIMIT $start, $per_page";
$result = $mysqli->query($query);

// Proses tambah data mutasi
if (isset($_POST['add_mutasi'])) {
    try {
        $id_peg = isset($_POST['id_pegawai']) ? $mysqli->real_escape_string($_POST['id_pegawai']) : '';
        
        // Cek apakah ID Pegawai ada di tabel pegawai
        $check_pegawai = "SELECT id_peg FROM pegawai WHERE id_peg = ?";
        $check_stmt = $mysqli->prepare($check_pegawai);
        $check_stmt->bind_param("s", $id_peg);
        $check_stmt->execute();
        $pegawai_exists = $check_stmt->get_result()->num_rows > 0;
        $check_stmt->close();

        if (!$pegawai_exists) {
            throw new Exception("ID Pegawai tidak ditemukan dalam database.");
        }

        // Cek apakah pegawai sudah memiliki mutasi aktif
        $check_mutasi = "SELECT id_mutasi FROM mutasi WHERE id_peg = ? AND status_mutasi = 'Pending'";
        $check_mutasi_stmt = $mysqli->prepare($check_mutasi);
        $check_mutasi_stmt->bind_param("s", $id_peg);
        $check_mutasi_stmt->execute();
        $mutasi_exists = $check_mutasi_stmt->get_result()->num_rows > 0;
        $check_mutasi_stmt->close();

        if ($mutasi_exists) {
            throw new Exception("Pegawai ini masih memiliki mutasi yang pending.");
        }

        $jbt_lama = isset($_POST['jbt_lama']) ? $mysqli->real_escape_string($_POST['jbt_lama']) : '';
        $jbt_baru = isset($_POST['jbt_baru']) ? $mysqli->real_escape_string($_POST['jbt_baru']) : '';
        $dpm_lama = isset($_POST['dpm_lama']) ? $mysqli->real_escape_string($_POST['dpm_lama']) : '';
        $dpm_baru = isset($_POST['dpm_baru']) ? $mysqli->real_escape_string($_POST['dpm_baru']) : '';
        $tgl_mutasi = isset($_POST['tgl_mutasi']) ? $mysqli->real_escape_string($_POST['tgl_mutasi']) : '';
        $alasan_mutasi = isset($_POST['alasan_mutasi']) ? $mysqli->real_escape_string($_POST['alasan_mutasi']) : '';
        $status_mutasi = 'Pending'; // Default status

        // Query untuk menambah data mutasi
        $query = "INSERT INTO mutasi (id_peg, jbt_lama, jbt_baru, dpm_lama, dpm_baru, 
                                    tgl_mutasi, alasan_mutasi, status_mutasi) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $mysqli->prepare($query)) {
            $stmt->bind_param("ssssssss", 
                $id_peg,
                $jbt_lama,
                $jbt_baru,
                $dpm_lama,
                $dpm_baru,
                $tgl_mutasi,
                $alasan_mutasi,
                $status_mutasi
            );
            
            if ($stmt->execute()) {
                // Jika mutasi disetujui, update data pegawai
                if ($status_mutasi == 'Approved') {
                    $update_pegawai = "UPDATE pegawai SET 
                                     jbt_peg = ?,
                                     dpm_peg = ?
                                     WHERE id_peg = ?";
                    $update_stmt = $mysqli->prepare($update_pegawai);
                    $update_stmt->bind_param("sss", 
                        $jbt_baru,
                        $dpm_baru,
                        $id_peg
                    );
                    $update_stmt->execute();
                }
                
                setMessage("Data mutasi berhasil ditambahkan!", "success");
                header("Location: promosi_mutasi.php?tab=mutasi");
                exit();
            } else {
                throw new Exception("Error executing query: " . $stmt->error);
            }
            $stmt->close();
        } else {
            throw new Exception("Error preparing query: " . $mysqli->error);
        }
    } catch (Exception $e) {
        setMessage("Error: " . $e->getMessage(), "danger");
        header("Location: promosi_mutasi.php?tab=mutasi");
        exit();
    }
}

// Proses tambah data promosi
if (isset($_POST['add_promosi'])) {
    try {
        $id_peg = isset($_POST['id_pegawai']) ? $mysqli->real_escape_string($_POST['id_pegawai']) : '';
        
        // Cek apakah ID Pegawai ada di tabel pegawai
        $check_pegawai = "SELECT id_peg FROM pegawai WHERE id_peg = ?";
        $check_stmt = $mysqli->prepare($check_pegawai);
        $check_stmt->bind_param("s", $id_peg);
        $check_stmt->execute();
        $result_pegawai = $check_stmt->get_result();
        $pegawai_data = $result_pegawai->fetch_assoc();
        $check_stmt->close();

        if (!$pegawai_data) {
            throw new Exception("ID Pegawai tidak ditemukan dalam database.");
        }

        // Cek apakah ID Pegawai sudah ada di tabel promosi
        $check_promosi = "SELECT COUNT(*) as count FROM promosi WHERE id_peg = ?";
        $check_stmt = $mysqli->prepare($check_promosi);
        $check_stmt->bind_param("s", $id_peg);
        $check_stmt->execute();
        $promosi_exists = $check_stmt->get_result()->fetch_assoc()['count'] > 0;
        $check_stmt->close();

        if ($promosi_exists) {
            throw new Exception("Pegawai ini sudah memiliki data promosi.");
        }

        $jbt_lama = isset($_POST['jbt_lama']) ? $mysqli->real_escape_string($_POST['jbt_lama']) : '';
        $jbt_baru = isset($_POST['jbt_baru']) ? $mysqli->real_escape_string($_POST['jbt_baru']) : '';
        $tanggal_promosi = isset($_POST['tanggal_promosi']) ? $mysqli->real_escape_string($_POST['tanggal_promosi']) : '';
        $alasan_promosi = isset($_POST['alasan_promosi']) ? $mysqli->real_escape_string($_POST['alasan_promosi']) : '';

        // Query untuk menambah data promosi
        $insert_query = "INSERT INTO promosi (id_peg, jbt_lama, jbt_baru, tanggal_promosi, alasan_promosi) 
                        VALUES (?, ?, ?, ?, ?)";
        
        if ($insert_stmt = $mysqli->prepare($insert_query)) {
            $insert_stmt->bind_param("sssss", 
                $id_peg, 
                $jbt_lama, 
                $jbt_baru, 
                $tanggal_promosi, 
                $alasan_promosi
            );
            
            if ($insert_stmt->execute()) {
                setMessage("Data promosi berhasil ditambahkan!", "success");
            } else {
                throw new Exception("Gagal menambahkan data promosi: " . $insert_stmt->error);
            }
            $insert_stmt->close();
        } else {
            throw new Exception("Error preparing query: " . $mysqli->error);
        }
    } catch (Exception $e) {
        setMessage("Error: " . $e->getMessage(), "danger");
    }
    
    header("Location: promosi_mutasi.php?tab=promosi");
    exit();
}

// Proses edit data mutasi
if (isset($_POST['edit_mutasi'])) {
    $id_mutasi = $mysqli->real_escape_string($_POST['id_mutasi']);
    $id_peg = $mysqli->real_escape_string($_POST['id_peg']);
    $jbt_lama = $mysqli->real_escape_string($_POST['jbt_lama']);
    $jbt_baru = $mysqli->real_escape_string($_POST['jbt_baru']);
    $dpm_lama = $mysqli->real_escape_string($_POST['dpm_lama']);
    $dpm_baru = $mysqli->real_escape_string($_POST['dpm_baru']);
    $tgl_mutasi = $mysqli->real_escape_string($_POST['tgl_mutasi']);
    $alasan_mutasi = $mysqli->real_escape_string($_POST['alasan_mutasi']);
    $status_mutasi = $mysqli->real_escape_string($_POST['status_mutasi']);

    $query = "UPDATE mutasi SET 
              id_peg = ?,
              jbt_lama = ?,
              jbt_baru = ?,
              dpm_lama = ?,
              dpm_baru = ?,
              tgl_mutasi = ?,
              alasan_mutasi = ?,
              status_mutasi = ?
              WHERE id_mutasi = ?";
    
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("ssssssssi", 
            $id_peg,
            $jbt_lama,
            $jbt_baru,
            $dpm_lama,
            $dpm_baru,
            $tgl_mutasi,
            $alasan_mutasi,
            $status_mutasi,
            $id_mutasi
        );
        
        if ($stmt->execute()) {
            // Jika status disetujui, update data pegawai
            if ($status_mutasi == 'Approved') {
                $update_pegawai = "UPDATE pegawai SET 
                                 jbt_peg = ?,
                                 dpm_peg = ?
                                 WHERE id_peg = ?";
                $update_stmt = $mysqli->prepare($update_pegawai);
                $update_stmt->bind_param("sss", 
                    $jbt_baru,
                    $dpm_baru,
                    $id_peg
                );
                $update_stmt->execute();
                $update_stmt->close();
            }
            
            setMessage("Data mutasi berhasil diperbarui!", "success");
        } else {
            setMessage("Error: " . $stmt->error, "danger");
        }
        $stmt->close();
    } else {
        setMessage("Error: " . $mysqli->error, "danger");
    }
    
    header("Location: promosi_mutasi.php?tab=mutasi");
    exit();
}

// Proses edit data promosi
if (isset($_POST['edit_promosi'])) {
    $id_peg = $mysqli->real_escape_string($_POST['id_peg']);
    $jbt_lama = $mysqli->real_escape_string($_POST['jbt_lama']);
    $jbt_baru = $mysqli->real_escape_string($_POST['jbt_baru']);
    $tanggal_promosi = $mysqli->real_escape_string($_POST['tanggal_promosi']);
    $alasan_promosi = $mysqli->real_escape_string($_POST['alasan_promosi']);

    $query = "UPDATE promosi SET 
              jbt_lama = ?,
              jbt_baru = ?,
              tanggal_promosi = ?,
              alasan_promosi = ?
              WHERE id_peg = ?";
    
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("sssss", 
            $jbt_lama,
            $jbt_baru,
            $tanggal_promosi,
            $alasan_promosi,
            $id_peg
        );
        
        if ($stmt->execute()) {
            setMessage("Data promosi berhasil diperbarui!", "success");
        } else {
            setMessage("Error: " . $stmt->error, "danger");
        }
        $stmt->close();
    } else {
        setMessage("Error: " . $mysqli->error, "danger");
    }
    
    header("Location: promosi_mutasi.php?tab=promosi");
    exit();
}

// Proses update status mutasi
if (isset($_POST['update_status_mutasi'])) {
    try {
        $id_mutasi = $mysqli->real_escape_string($_POST['id_mutasi']);
        $status_baru = $mysqli->real_escape_string($_POST['status_mutasi']);
        
        // Ambil data mutasi
        $get_mutasi = "SELECT * FROM mutasi WHERE id_mutasi = ?";
        $get_stmt = $mysqli->prepare($get_mutasi);
        $get_stmt->bind_param("i", $id_mutasi);
        $get_stmt->execute();
        $mutasi_data = $get_stmt->get_result()->fetch_assoc();
        $get_stmt->close();
        
        // Update status mutasi
        $update_query = "UPDATE mutasi SET status_mutasi = ? WHERE id_mutasi = ?";
        $update_stmt = $mysqli->prepare($update_query);
        $update_stmt->bind_param("si", $status_baru, $id_mutasi);
        
        if ($update_stmt->execute()) {
            // Jika status disetujui, update data pegawai
            if ($status_baru == 'Approved') {
                $update_pegawai = "UPDATE pegawai SET 
                                 jbt_peg = ?,
                                 dpm_peg = ?
                                 WHERE id_peg = ?";
                $update_stmt = $mysqli->prepare($update_pegawai);
                $update_stmt->bind_param("sss", 
                    $mutasi_data['jbt_baru'],
                    $mutasi_data['dpm_baru'],
                    $mutasi_data['id_peg']
                );
                $update_stmt->execute();
                $update_stmt->close();
            }
            
            setMessage("Status mutasi berhasil diperbarui!", "success");
        } else {
            throw new Exception("Gagal memperbarui status mutasi.");
        }
        
        header("Location: promosi_mutasi.php?tab=mutasi");
        exit();
    } catch (Exception $e) {
        setMessage("Error: " . $e->getMessage(), "danger");
        header("Location: promosi_mutasi.php?tab=mutasi");
        exit();
    }
}

// Perbaiki query untuk mengambil daftar pegawai untuk dropdown
$query_pegawai = "SELECT id_peg, nama_peg FROM pegawai ORDER BY nama_peg";
$result_pegawai = $mysqli->query($query_pegawai);

// Sekarang baru include header dan sidebar
include '../template/header.php';
include '../template/sidebar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Promosi Dan Mutasi</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* Ultra Modern Color Palette */
        :root {
            --ultra-primary: #6366f1;
            --ultra-secondary: #4f46e5;
            --ultra-success: #10b981;
            --ultra-info: #3b82f6;
            --ultra-warning: #f59e0b;
            --ultra-danger: #ef4444;
            --ultra-light: #f3f4f6;
            --ultra-dark: #111827;
            --luxury-gradient-1: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            --luxury-gradient-2: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            --luxury-gradient-3: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --glass-effect: rgba(255, 255, 255, 0.95);
        }

        /* Luxury Background Effect */
        .main-content {
            background: 
                radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(59, 130, 246, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 50% 50%, rgba(16, 185, 129, 0.1) 0%, transparent 50%),
                linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            padding: 2.5rem;
            position: relative;
        }

        /* Premium Page Title */
        .page-title {
            font-size: 3rem;
            font-weight: 900;
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
            color: var(--ultra-dark);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .page-title::before {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 150px;
            height: 5px;
            background: var(--luxury-gradient-1);
            border-radius: 5px;
        }

        /* Ultra Modern Card */
        .card {
            background: var(--glass-effect);
            border: none;
            border-radius: 40px;
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.5) inset;
            backdrop-filter: blur(20px);
            transform-style: preserve-3d;
            transition: all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-20px) rotateX(5deg);
            box-shadow: 
                0 35px 70px -12px rgba(0, 0, 0, 0.2),
                0 0 0 1px rgba(255, 255, 255, 0.6) inset;
        }

        .card-header {
            background: var(--luxury-gradient-1);
            padding: 2.5rem;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at top right, rgba(255,255,255,0.2) 0%, transparent 60%),
                radial-gradient(circle at bottom left, rgba(255,255,255,0.1) 0%, transparent 40%);
            z-index: 1;
        }

        /* Luxury Table Design */
        .table {
            margin: 0;
            border-spacing: 0 15px;
            border-collapse: separate;
        }

        .table thead th {
            background: transparent;
            color: var(--ultra-dark);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.85rem;
            padding: 2rem 1.5rem;
            border: none;
        }

        .table tbody tr {
            background: white;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            transform: translateZ(0);
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            border-radius: 20px;
        }

        .table tbody tr:hover {
            transform: scale(1.02) translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .table td {
            padding: 1.8rem;
            vertical-align: middle;
            border: none;
            color: var(--ultra-dark);
            font-weight: 500;
        }

        .table td:first-child {
            border-top-left-radius: 20px;
            border-bottom-left-radius: 20px;
        }

        .table td:last-child {
            border-top-right-radius: 20px;
            border-bottom-right-radius: 20px;
        }

        /* Premium Search Container */
        .search-container {
            background: var(--glass-effect);
            border-radius: 30px;
            padding: 2.5rem;
            margin-bottom: 3rem;
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.5) inset;
            backdrop-filter: blur(20px);
            transform: translateZ(0);
            transition: all 0.5s ease;
        }

        .search-container:hover {
            transform: translateY(-5px);
            box-shadow: 
                0 30px 60px rgba(0, 0, 0, 0.12),
                0 0 0 1px rgba(255, 255, 255, 0.6) inset;
        }

        .search-form .form-control {
            border: 2px solid rgba(99, 102, 241, 0.1);
            border-radius: 20px;
            padding: 1.5rem 2rem;
            font-size: 1.1rem;
            transition: all 0.4s ease;
            background: rgba(255, 255, 255, 0.8);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .search-form .form-control:focus {
            border-color: var(--ultra-primary);
            background: white;
            box-shadow: 
                0 10px 25px rgba(99, 102, 241, 0.15),
                0 0 0 5px rgba(99, 102, 241, 0.1);
        }

        /* Ultra Modern Status Badges */
        .status-badge {
            padding: 1rem 2rem;
            border-radius: 30px;
            font-weight: 700;
            font-size: 0.9rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .status-badge::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0.2));
            transform: translateY(100%);
            transition: transform 0.4s ease;
        }

        .status-badge:hover::before {
            transform: translateY(0);
        }

        .status-pending {
            background: var(--luxury-gradient-2);
            color: white;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        .status-approved {
            background: var(--luxury-gradient-3);
            color: white;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
        }

        .status-rejected {
            background: linear-gradient(135deg, var(--ultra-danger) 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.3);
        }

        /* Premium Action Buttons */
        .action-btn {
            width: 50px;
            height: 50px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 0.5rem;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
            border: none;
        }

        .action-btn:hover {
            transform: translateY(-5px);
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(255,255,255,0.2), rgba(255,255,255,0.3));
            transform: translateY(100%);
            transition: transform 0.4s ease;
        }

        .action-btn:hover::before {
            transform: translateY(0);
        }

        .btn-edit {
            background: var(--luxury-gradient-2);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
        }

        .btn-delete {
            background: linear-gradient(135deg, var(--ultra-danger) 0%, #dc2626 100%);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
        }

        /* Ultra Modern Pagination */
        .pagination {
            gap: 1rem;
        }

        .pagination .page-link {
            width: 50px;
            height: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--ultra-dark);
            background: white;
            border: none;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .pagination .page-link:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.15);
        }

        .pagination .page-item.active .page-link {
            background: var(--luxury-gradient-1);
            color: white;
            box-shadow: 0 12px 25px rgba(99, 102, 241, 0.4);
        }

        /* Loading Animation */
        .loading-overlay {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 5px solid rgba(99, 102, 241, 0.1);
            border-top: 5px solid var(--ultra-primary);
            border-radius: 50%;
            animation: spin 1s cubic-bezier(0.34, 1.56, 0.64, 1) infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg) scale(1); }
            50% { transform: rotate(180deg) scale(1.2); }
            100% { transform: rotate(360deg) scale(1); }
        }

        /* Luxury Modal Design */
        .modal-content {
            border: none;
            border-radius: 40px;
            overflow: hidden;
            box-shadow: 0 35px 70px rgba(0, 0, 0, 0.2);
            background: var(--glass-effect);
            backdrop-filter: blur(20px);
        }

        .modal-header {
            background: var(--luxury-gradient-1);
            padding: 2.5rem;
            border: none;
        }

        .modal-body {
            padding: 2.5rem;
        }

        .modal-footer {
            padding: 2rem 2.5rem;
            background: rgba(99, 102, 241, 0.03);
            border-top: 1px solid rgba(99, 102, 241, 0.1);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .main-content {
                padding: 1.5rem;
            }

            .page-title {
                font-size: 2.5rem;
            }

            .search-container {
                padding: 1.5rem;
            }

            .table td, .table th {
                padding: 1.2rem;
            }

            .action-btn {
                width: 45px;
                height: 45px;
            }

            .pagination .page-link {
                width: 45px;
                height: 45px;
            }
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        /* Mengatur layout utama */
        .main-content {
            margin-left: 260px; /* Sesuaikan dengan lebar sidebar */
            padding: 2.5rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* Responsive layout */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1.5rem;
            }
        }

        /* Table container */
        .table-responsive {
            overflow-x: auto;
            margin-right: 1rem;
            border-radius: 20px;
        }

        /* Table width control */
        .table {
            width: 100%;
            min-width: 800px; /* Minimum width untuk tabel */
            margin-bottom: 0;
        }

        /* Container untuk card */
        .card {
            margin-right: 1rem;
            margin-left: 1rem;
        }

        /* Search container */
        .search-container {
            margin-right: 1rem;
            margin-left: 1rem;
        }

        /* Pagination container */
        .pagination-container {
            margin-right: 1rem;
            margin-left: 1rem;
            margin-top: 1.5rem;
        }

        /* Mengatur scroll horizontal yang smooth */
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: var(--ultra-primary);
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: var(--ultra-secondary);
        }

        /* Memastikan konten tidak terpotong di mobile */
        @media (max-width: 992px) {
            .card {
                margin-right: 0.5rem;
                margin-left: 0.5rem;
            }

            .search-container {
                margin-right: 0.5rem;
                margin-left: 0.5rem;
            }

            .pagination-container {
                margin-right: 0.5rem;
                margin-left: 0.5rem;
            }
        }

        /* Mengatur jarak dari header */
        .content-wrapper {
            padding-top: 80px; /* Sesuaikan dengan tinggi header */
        }

        /* Container untuk mengatur max-width konten */
        .content-container {
            max-width: 1400px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="sidebar">
            <?php include '../template/sidebar.php'; ?>
        </div>
        
        <div class="main-content">
            <div class="content-wrapper">
                <div class="content-container">
                    <h2 class="mb-4">Manajemen Promosi Dan Mutasi</h2>

                    <?php if ($message): ?>
                    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs mb-4">
                        <li class="nav-item">
                            <a class="nav-link <?= $active_tab == 'promosi' ? 'active' : '' ?>" 
                               href="?tab=promosi">Data Promosi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $active_tab == 'mutasi' ? 'active' : '' ?>" 
                               href="?tab=mutasi">Data Mutasi</a>
                        </li>
                    </ul>

                    <!-- Search and Add Button Row -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form action="" method="GET" class="d-flex">
                                <input type="hidden" name="tab" value="<?= $active_tab ?>">
                                <input type="text" name="search" class="form-control me-2" 
                                       placeholder="Cari data..." value="<?= htmlspecialchars($search) ?>">
                                <button type="submit" class="btn btn-primary">Cari</button>
                                <?php if (!empty($search)): ?>
                                    <a href="?tab=<?= $active_tab ?>" class="btn btn-secondary ms-2">Reset</a>
                                <?php endif; ?>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" 
                                    data-bs-target="#<?= $active_tab ?>Modal">
                                Tambah <?= ucfirst($active_tab) ?>
                            </button>
                        </div>
                    </div>

                    <!-- Notifikasi Hasil Pencarian -->
                    <?php if (!empty($search)): ?>
                        <?php if ($total_records > 0): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                Ditemukan <?= $total_records ?> data untuk pencarian "<?= htmlspecialchars($search) ?>"
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                Tidak ditemukan data untuk pencarian "<?= htmlspecialchars($search) ?>"
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Tabel Data -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <?php if ($total_records > 0): ?>
                                        <?php if ($active_tab == 'promosi'): ?>
                                            <thead>
                                                <tr>
                                                    <th>ID Pegawai</th>
                                                    <th>Nama Pegawai</th>
                                                    <th>Jabatan Lama</th>
                                                    <th>Jabatan Baru</th>
                                                    <th>Tanggal Promosi</th>
                                                    <th>Alasan Promosi</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($row = $result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($row['id_peg']) ?></td>
                                                        <td><?= htmlspecialchars($row['nama_pegawai']) ?></td>
                                                        <td><?= htmlspecialchars($row['jbt_lama']) ?></td>
                                                        <td><?= htmlspecialchars($row['jbt_baru']) ?></td>
                                                        <td><?= htmlspecialchars($row['tanggal_promosi']) ?></td>
                                                        <td><?= htmlspecialchars($row['alasan_promosi']) ?></td>
                                                        <td>
                                                            <button type="button" class="btn btn-edit action-btn" 
                                                                    onclick='editPromosi(<?= json_encode($row) ?>)'>
                                                                <i class="bi bi-pencil-fill text-white"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-delete action-btn"
                                                                    onclick="confirmDelete('<?= $row['id_peg'] ?>', 'promosi')">
                                                                <i class="bi bi-trash-fill text-white"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        <?php else: ?>
                                            <thead>
                                                <tr>
                                                    <th>ID Pegawai</th>
                                                    <th>Nama Pegawai</th>
                                                    <th>Jabatan Lama</th>
                                                    <th>Jabatan Baru</th>
                                                    <th>Departemen Lama</th>
                                                    <th>Departemen Baru</th>
                                                    <th>Tanggal Mutasi</th>
                                                    <th>Alasan Mutasi</th>
                                                    <th>Status</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($row = $result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($row['id_peg']) ?></td>
                                                        <td><?= htmlspecialchars($row['nama_pegawai']) ?></td>
                                                        <td><?= htmlspecialchars($row['jbt_lama']) ?></td>
                                                        <td><?= htmlspecialchars($row['jbt_baru']) ?></td>
                                                        <td><?= htmlspecialchars($row['dpm_lama']) ?></td>
                                                        <td><?= htmlspecialchars($row['dpm_baru']) ?></td>
                                                        <td><?= htmlspecialchars($row['tgl_mutasi']) ?></td>
                                                        <td><?= htmlspecialchars($row['alasan_mutasi']) ?></td>
                                                        <td>
                                                            <span class="status-badge status-<?= strtolower($row['status_mutasi']) ?>">
                                                                <?= htmlspecialchars($row['status_mutasi']) ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-edit action-btn" 
                                                                    onclick='editMutasi(<?= json_encode($row) ?>)'>
                                                                <i class="bi bi-pencil-fill text-white"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-delete action-btn"
                                                                    onclick="confirmDelete('<?= $row['id_mutasi'] ?>', 'mutasi')">
                                                                <i class="bi bi-trash-fill text-white"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        <?php if ($total_pages > 1): ?>
                        <div class="text-center mt-4">
                            <ul class="pagination justify-content-center">
                                <!-- First Page -->
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?tab=<?= $active_tab ?>&page=1&search=<?= urlencode($search) ?>">
                                        <i class="bi bi-chevron-double-left"></i>
                                    </a>
                                </li>
                                
                                <!-- Previous Page -->
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?tab=<?= $active_tab ?>&page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>

                                <!-- Page Numbers -->
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);

                                for ($i = $start_page; $i <= $end_page; $i++): 
                                ?>
                                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                        <a class="page-link" href="?tab=<?= $active_tab ?>&page=<?= $i ?>&search=<?= urlencode($search) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Next Page -->
                                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?tab=<?= $active_tab ?>&page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>

                                <!-- Last Page -->
                                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?tab=<?= $active_tab ?>&page=<?= $total_pages ?>&search=<?= urlencode($search) ?>">
                                        <i class="bi bi-chevron-double-right"></i>
                                    </a>
                                </li>
                            </ul>

                            <!-- Informasi halaman -->
                            <div class="text-muted mt-2">
                                Halaman <?= $page ?> dari <?= $total_pages ?> 
                                (Total <?= $total_records ?> data)
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Modal Forms -->
    <?php 
    include 'modal_promosi.php';
    include 'modal_mutasi.php';
    ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function() {
        // Auto hide alert
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 3000);
    });

    // Fungsi untuk edit mutasi
    function editMutasi(data) {
        // Set nilai ke form edit
        document.getElementById('edit_id_mutasi').value = data.id_mutasi;
        document.getElementById('edit_id_pegawai').value = data.id_pegawai;
        document.getElementById('edit_jbt_lama').value = data.jbt_lama;
        document.getElementById('edit_jbt_baru').value = data.jbt_baru;
        document.getElementById('edit_dpm_lama').value = data.dpm_lama;
        document.getElementById('edit_dpm_baru').value = data.dpm_baru;
        document.getElementById('edit_tgl_mutasi').value = data.tgl_mutasi;
        document.getElementById('edit_alasan_mutasi').value = data.alasan_mutasi;
        document.getElementById('edit_status_mutasi').value = data.status_mutasi;

        // Tampilkan modal edit
        var editModal = new bootstrap.Modal(document.getElementById('editMutasiModal'));
        editModal.show();
    }

    // Fungsi untuk edit promosi
    function editPromosi(data) {
        // Set nilai ke form edit
        document.getElementById('edit_id_peg').value = data.id_peg;
        document.getElementById('edit_jbt_lama').value = data.jbt_lama;
        document.getElementById('edit_jbt_baru').value = data.jbt_baru;
        document.getElementById('edit_tanggal_promosi').value = data.tanggal_promosi;
        document.getElementById('edit_alasan_promosi').value = data.alasan_promosi;

        // Tampilkan modal edit
        var editModal = new bootstrap.Modal(document.getElementById('editPromosiModal'));
        editModal.show();
    }

    // Fungsi konfirmasi hapus dengan SweetAlert2
    function confirmDelete(id, type) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading
                Swal.fire({
                    title: 'Menghapus Data...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Redirect ke URL penghapusan
                window.location.href = `promosi_mutasi.php?tab=${type}&delete=${id}`;
            }
        });
    }

    // Tampilkan notifikasi setelah penghapusan berhasil
    <?php if (isset($_SESSION['message']) && isset($_SESSION['message_type'])): ?>
        Swal.fire({
            title: '<?= ($_SESSION['message_type'] == 'success') ? 'Berhasil!' : 'Gagal!' ?>',
            text: '<?= $_SESSION['message'] ?>',
            icon: '<?= $_SESSION['message_type'] ?>',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false
        });
        <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
    <?php endif; ?>

    // Fungsi untuk menampilkan notifikasi umum
    function showNotification(message, type = 'success') {
        Swal.fire({
            title: type.charAt(0).toUpperCase() + type.slice(1) + '!',
            text: message,
            icon: type,
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false
        });
    }
    </script>

    <!-- Pastikan SweetAlert2 dimuat -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>