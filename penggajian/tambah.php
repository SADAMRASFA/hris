<?php
include "../connection.php";
require_once "../template/header.php";
require_once "../template/sidebar.php";

$notif = "";

// Proses tambah data penggajian
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_peg = $_POST['id_peg'];
    $id_jabatan = $_POST['id_jabatan'];
    $gaji_pokok = $_POST['gaji_pokok'];
    $bonus = $_POST['bonus'];
    $potongan_bpjs = $_POST['potongan_bpjs'];
    $potongan_pajak = $_POST['potongan_pajak'];
    $potongan_lain = $_POST['potongan_lain'];

    $query = "INSERT INTO penggajian (id_peg, id_jabatan, gaji_pokok, bonus, potongan_bpjs, potongan_pajak, potongan_lain) 
              VALUES ('$id_peg', '$id_jabatan', '$gaji_pokok', '$bonus', '$potongan_bpjs', '$potongan_pajak', '$potongan_lain')";

    if (mysqli_query($conn, $query)) {
        $notif = "success|Data berhasil ditambahkan!";
    } else {
        $notif = "error|Gagal menambahkan data: " . mysqli_error($conn);
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
    --gradient-3: linear-gradient(135deg, #059669, #047857);
}

/* Modern Form Container */
.form-container {
    background: linear-gradient(145deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.85));
    border-radius: 16px;
    padding: 40px;
    box-shadow: 
        0 10px 25px rgba(37, 99, 235, 0.1),
        0 4px 12px rgba(37, 99, 235, 0.05);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(37, 99, 235, 0.1);
    position: relative;
}

/* Clean Form Controls */
.form-control {
    border: 2px solid rgba(37, 99, 235, 0.2);
    border-radius: 8px;
    padding: 12px 16px;
    transition: all 0.2s ease;
    background: rgba(255, 255, 255, 0.9);
    color: var(--dark);
}

.form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    transform: translateY(-1px);
}

/* Professional Input Groups */
.input-group-text {
    background: var(--gradient-1);
    color: white;
    border: none;
    padding: 0 20px;
    font-weight: 500;
}

/* Modern Buttons */
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

/* Clean Labels */
.form-label {
    color: var(--dark);
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 8px;
    display: block;
}

/* Enhanced Select */
select.form-control {
    cursor: pointer;
    background-image: linear-gradient(45deg, var(--primary) 50%, transparent 50%),
                      linear-gradient(135deg, transparent 50%, var(--primary) 50%);
    background-position: 
        calc(100% - 20px) calc(1em + 2px),
        calc(100% - 15px) calc(1em + 2px);
    background-size: 
        5px 5px,
        5px 5px;
    background-repeat: no-repeat;
    padding-right: 40px;
}

/* Form Title */
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

/* Form Groups */
.form-group {
    margin-bottom: 24px;
}

/* Loading State */
.form-container.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(4px);
    border-radius: 16px;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .form-container {
        padding: 20px;
    }
    
    .btn-submit {
        width: 100%;
        margin-bottom: 10px;
    }
}

/* Success Animation */
@keyframes successPulse {
    0% { 
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(28, 200, 138, 0.4);
    }
    50% { 
        transform: scale(1.05);
        box-shadow: 0 0 0 10px rgba(28, 200, 138, 0);
    }
    100% { 
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(28, 200, 138, 0);
    }
}

.form-success {
    animation: successPulse 0.5s ease-in-out;
}
</style>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Tambah Data Penggajian</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php">Penggajian</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-container">
                    <h5 class="form-title">Form Tambah Penggajian</h5>

                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Pegawai</label>
                            <select name="id_peg" class="form-control" required>
                                <option value="">Pilih Pegawai</option>
                                <?php
                                $pegawai = mysqli_query($conn, "SELECT * FROM pegawai");
                                while ($peg = mysqli_fetch_assoc($pegawai)) {
                                    echo "<option value='{$peg['id_peg']}'>{$peg['nama_peg']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Jabatan</label>
                            <select name="id_jabatan" class="form-control" required>
                                <option value="">Pilih Jabatan</option>
                                <?php
                                $jabatan = mysqli_query($conn, "SELECT * FROM jabatan");
                                while ($jab = mysqli_fetch_assoc($jabatan)) {
                                    echo "<option value='{$jab['id_jabatan']}'>{$jab['nama_jabatan']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Gaji Pokok</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="gaji_pokok" class="form-control currency-input" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Bonus</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="bonus" class="form-control currency-input">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Potongan BPJS</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="potongan_bpjs" class="form-control currency-input">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Potongan Pajak</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="potongan_pajak" class="form-control currency-input">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Potongan Lain</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="potongan_lain" class="form-control currency-input">
                            </div>
                        </div>

                        <div class="btn-container">
                            <button type="submit" class="btn-submit btn-save">
                                <i class="bi bi-check-circle"></i> Simpan
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

<!-- Tambahkan script untuk animasi -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animasi hover pada form groups
    const formGroups = document.querySelectorAll('.form-group');
    formGroups.forEach(group => {
        group.addEventListener('mouseover', function() {
            this.style.transform = 'translateX(5px)';
        });
        group.addEventListener('mouseout', function() {
            this.style.transform = 'translateX(0)';
        });
    });

    // Animasi submit form
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const container = document.querySelector('.form-container');
        container.classList.add('loading');
    });
});
</script>
