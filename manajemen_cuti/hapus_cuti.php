<?php
include "../connection.php";
session_start();

// Ambil ID cuti dari URL
$id_cuti = $_GET['id'];

// Cek status cuti sebelum menghapus
$query_check = "SELECT status_cuti FROM tabel_cuti WHERE id_cuti = $id_cuti";
$result_check = mysqli_query($conn, $query_check);
$data_cuti = mysqli_fetch_assoc($result_check);

if ($data_cuti['status_cuti'] == 'Diajukan') {
    // Hapus data cuti
    $query = "DELETE FROM tabel_cuti WHERE id_cuti = $id_cuti";
    
    if (mysqli_query($conn, $query)) {
        $notif = "success|Data cuti berhasil dihapus!";
    } else {
        $notif = "error|Gagal menghapus data: " . mysqli_error($conn);
    }
} else {
    $notif = "warning|Data cuti yang sudah diproses tidak dapat dihapus!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        const [icon, message] = "<?= $notif ?>".split('|');
        Swal.fire({
            icon: icon,
            title: icon === 'success' ? 'Berhasil!' : 'Gagal!',
            text: message,
            confirmButtonText: 'OK',
            customClass: {
                confirmButton: 'btn btn-autumn',
                popup: 'animated bounceInDown'
            },
            backdrop: `
                rgba(0,0,123,0.4)
                url("/images/nyan-cat.gif")
                left top
                no-repeat
            `
        }).then((result) => {
            window.location.href = 'index.php';
        });
    </script>
</body>
</html> 