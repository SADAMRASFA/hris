<?php
include "../connection.php";
require_once "../template/header.php";
require_once "../template/sidebar.php";

$notif = "";

// Cek apakah ID ada di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID tidak ditemukan!'); window.location='index.php';</script>";
    exit();
}

$id = $_GET['id'];

// Ambil data penggajian berdasarkan ID
$query = "SELECT * FROM penggajian WHERE id_penggajian = '$id'";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='index.php';</script>";
    exit();
}

$data = mysqli_fetch_assoc($result);

// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_peg = $_POST['id_peg'];
    $id_jabatan = $_POST['id_jabatan'];
    $gaji_pokok = $_POST['gaji_pokok'];
    $bonus = $_POST['bonus'];
    $potongan_bpjs = $_POST['potongan_bpjs'];
    $potongan_pajak = $_POST['potongan_pajak'];
    $potongan_lain = $_POST['potongan_lain'];

    $update_query = "UPDATE penggajian SET 
                        id_peg = '$id_peg', 
                        id_jabatan = '$id_jabatan', 
                        gaji_pokok = '$gaji_pokok', 
                        bonus = '$bonus', 
                        potongan_bpjs = '$potongan_bpjs', 
                        potongan_pajak = '$potongan_pajak', 
                        potongan_lain = '$potongan_lain' 
                    WHERE id_penggajian = '$id'";

    if (mysqli_query($conn, $update_query)) {
        $notif = "success|Data berhasil diperbarui!";
    } else {
        $notif = "error|Gagal memperbarui data: " . mysqli_error($conn);
    }
}
?>

<style>
:root {
    --primary: #2563eb;
    --primary-dark: #1e40af;
    --secondary: #64748b;
    --success: #059669;
    --info: #0284c7;
    --warning: #d97706;
    --danger: #dc2626;
    --light: #f8fafc;
    --dark: #1e293b;
    --gradient-1: linear-gradient(135deg, #2563eb, #1e40af);
    --gradient-2: linear-gradient(135deg, #64748b, #475569);
}

.form-container {
    background: linear-gradient(145deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.85));
    border-radius: 16px;
    padding: 40px;
    box-shadow: 
        0 10px 25px rgba(37, 99, 235, 0.1),
        0 4px 12px rgba(37, 99, 235, 0.05);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(37, 99, 235, 0.1);
}

.form-control {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px 18px;
    transition: all 0.3s ease;
    background: #f8fafc;
    color: #334155;
    font-size: 15px;
}

.form-control:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.form-control:focus {
    border-color: #3b82f6;
    background: #ffffff;
    box-shadow: 
        0 0 0 4px rgba(59, 130, 246, 0.1),
        0 1px 2px rgba(0, 0, 0, 0.05);
    transform: translateY(-1px);
}

.form-label {
    color: #475569;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 8px;
    transition: color 0.2s ease;
}

.form-group {
    margin-bottom: 24px;
    position: relative;
}

.form-group:hover .form-label {
    color: #3b82f6;
}

.form-title {
    color: var(--dark);
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid rgba(37, 99, 235, 0.1);
    position: relative;
}

.form-title::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 100px;
    height: 2px;
    background: var(--gradient-1);
}

.btn-submit {
    padding: 12px 30px;
    border-radius: 8px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 14px;
}

.btn-save {
    background: var(--gradient-1);
    color: white;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
}

.btn-cancel {
    background: var(--gradient-2);
    color: white;
    box-shadow: 0 4px 12px rgba(100, 116, 139, 0.2);
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
}

select.form-control {
    background-image: linear-gradient(45deg, transparent 50%, #3b82f6 50%),
                      linear-gradient(135deg, #3b82f6 50%, transparent 50%);
    background-position: 
        calc(100% - 20px) calc(1em + 2px),
        calc(100% - 15px) calc(1em + 2px);
    background-size: 
        5px 5px,
        5px 5px;
    background-repeat: no-repeat;
    padding-right: 40px;
}

.input-group {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border-radius: 10px;
}

.input-group-text {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 0 20px;
    font-weight: 500;
    min-width: 60px;
    border-top-left-radius: 10px;
    border-bottom-left-radius: 10px;
}
</style>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Edit Data Penggajian</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php">Penggajian</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-container">
                    <h5 class="form-title">Form Edit Penggajian</h5>

                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Pegawai</label>
                            <select name="id_peg" class="form-control" required>
                                <?php
                                $pegawai = mysqli_query($conn, "SELECT * FROM pegawai");
                                while ($peg = mysqli_fetch_assoc($pegawai)) {
                                    $selected = ($peg['id_peg'] == $data['id_peg']) ? "selected" : "";
                                    echo "<option value='{$peg['id_peg']}' $selected>{$peg['nama_peg']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Jabatan</label>
                            <select name="id_jabatan" class="form-control" required>
                                <?php
                                $jabatan = mysqli_query($conn, "SELECT * FROM jabatan");
                                while ($jab = mysqli_fetch_assoc($jabatan)) {
                                    $selected = ($jab['id_jabatan'] == $data['id_jabatan']) ? "selected" : "";
                                    echo "<option value='{$jab['id_jabatan']}' $selected>{$jab['nama_jabatan']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Gaji Pokok</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="gaji_pokok" class="form-control" value="<?= $data['gaji_pokok'] ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Bonus</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="bonus" class="form-control" value="<?= $data['bonus'] ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Potongan BPJS</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="potongan_bpjs" class="form-control" value="<?= $data['potongan_bpjs'] ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Potongan Pajak</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="potongan_pajak" class="form-control" value="<?= $data['potongan_pajak'] ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Potongan Lain</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="potongan_lain" class="form-control" value="<?= $data['potongan_lain'] ?>">
                            </div>
                        </div>

                        <div class="btn-container">
                            <button type="submit" class="btn-submit btn-save">
                                <i class="bi bi-check-circle"></i> Simpan Perubahan
                            </button>
                            <a href="index.php" class="btn-submit btn-cancel">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
require_once "../template/footer.php";
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    <?php if ($notif): ?>
        const [icon, message] = "<?= $notif ?>".split('|');
        Swal.fire({
            icon: icon,
            title: icon === 'success' ? 'Berhasil!' : 'Gagal!',
            text: message,
            confirmButtonText: 'OK'
        });
    <?php endif; ?>
</script>
