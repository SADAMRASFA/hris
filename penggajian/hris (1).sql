-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 08 Feb 2025 pada 05.24
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hris`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi`
--

CREATE TABLE `absensi` (
  `ID_Absensi` int(11) NOT NULL,
  `ID_Pegawai` int(11) NOT NULL,
  `Tanggal_Waktu` datetime NOT NULL,
  `Status_Kehadiran` varchar(10) NOT NULL,
  `Metode_Verifikasi` varchar(20) NOT NULL,
  `Lokasi_IP` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `analisis_sdm`
--

CREATE TABLE `analisis_sdm` (
  `id_analisis` int(11) NOT NULL,
  `judul_analisis` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `tanggal_analisis` date NOT NULL,
  `jenis_analisis` enum('Kehadiran','Kinerja','Pelatihan','Penghargaan') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `departemen`
--

CREATE TABLE `departemen` (
  `id_dep` int(11) NOT NULL,
  `nama_departemen` varchar(100) NOT NULL,
  `kepala_departemen` varchar(100) NOT NULL,
  `lokasi_departemen` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokumen pendukung`
--

CREATE TABLE `dokumen pendukung` (
  `dokumen_peg` int(11) NOT NULL,
  `kontrak_peg` int(11) NOT NULL,
  `jenis_dokumen` int(11) NOT NULL,
  `tanggal_unggah` date NOT NULL,
  `nama_file` text NOT NULL,
  `lokasi_file` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jabatan`
--

CREATE TABLE `jabatan` (
  `id_jabatan` int(11) NOT NULL,
  `nama_jabatan` varchar(100) NOT NULL,
  `desk_jabatan` text NOT NULL,
  `level_jabatan` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jabatan`
--

INSERT INTO `jabatan` (`id_jabatan`, `nama_jabatan`, `desk_jabatan`, `level_jabatan`) VALUES
(1, 'HRD', 'penyeleksi calon pegawai', 3),
(2, 'CEO', 'pengelolaan operasional perusahaan secara keseluruhan', 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal_kehadiran`
--

CREATE TABLE `jadwal_kehadiran` (
  `ID_Jadwal` int(11) NOT NULL,
  `ID_Pegawai` int(11) NOT NULL,
  `Hari` varchar(10) NOT NULL,
  `Jam_Masuk` time NOT NULL,
  `Jam_Keluar` time NOT NULL,
  `Keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jenis_cuti`
--

CREATE TABLE `jenis_cuti` (
  `id_jenis_cuti` int(11) NOT NULL,
  `nama_jenis_cuti` varchar(100) NOT NULL,
  `keterangan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jenis_cuti`
--

INSERT INTO `jenis_cuti` (`id_jenis_cuti`, `nama_jenis_cuti`, `keterangan`) VALUES
(1, 'imlek', 'tahun baru china');

-- --------------------------------------------------------

--
-- Struktur dari tabel `keterlambatan dan ketidakhadiran`
--

CREATE TABLE `keterlambatan dan ketidakhadiran` (
  `ID_ketidakhadiran` int(11) NOT NULL,
  `Tanggal` date NOT NULL,
  `Jam_Masuk_Terlambat` time NOT NULL,
  `Alasan_Keterlambatan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kontrak pegawai`
--

CREATE TABLE `kontrak pegawai` (
  `id_pegawai` int(11) NOT NULL,
  `tanggal_mulai_kontrak` date NOT NULL,
  `tanggal_berakhir_kontrak` date NOT NULL,
  `status_kontrak` varchar(11) NOT NULL,
  `gaji_bulanan` varchar(11) NOT NULL,
  `tipe_kontrak` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan_sdm`
--

CREATE TABLE `laporan_sdm` (
  `id_laporan` int(11) NOT NULL,
  `judul_laporan` varchar(255) NOT NULL,
  `periode_awal` date NOT NULL,
  `periode_akhir` date NOT NULL,
  `isi_laporan` text NOT NULL,
  `tanggal_dibuat` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `lowongan`
--

CREATE TABLE `lowongan` (
  `id_lowongan` int(11) NOT NULL,
  `nama_lowongan` varchar(128) NOT NULL,
  `deskripsi_lowongan` text NOT NULL,
  `jabatan` varchar(128) NOT NULL,
  `tgl_posting` date NOT NULL,
  `tgl_tutup` date NOT NULL,
  `status` enum('Tersedia','Tidak Tersedia') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `mutasi`
--

CREATE TABLE `mutasi` (
  `id_mutasi` int(11) NOT NULL,
  `id_peg` int(11) NOT NULL,
  `id_dep` int(11) NOT NULL,
  `id_jabatan` int(11) NOT NULL,
  `tgl_mutasi` date NOT NULL,
  `alasan` text NOT NULL,
  `status_mutasi` enum('Di setujui','Di tolak','Menunggu') NOT NULL DEFAULT 'Menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pegawai`
--

CREATE TABLE `pegawai` (
  `id_peg` int(11) NOT NULL,
  `id_jabatan` int(11) DEFAULT NULL,
  `nama_peg` varchar(128) NOT NULL,
  `gender_peg` enum('Laki-laki','Perempuan') NOT NULL,
  `status_peg` varchar(128) NOT NULL,
  `almt_peg` text NOT NULL,
  `no_telp_peg` varchar(128) NOT NULL,
  `email_peg` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `pegawai`
--

INSERT INTO `pegawai` (`id_peg`, `id_jabatan`, `nama_peg`, `gender_peg`, `status_peg`, `almt_peg`, `no_telp_peg`, `email_peg`) VALUES
(1, 1, 'MUFLIH HILMY ALY', 'Laki-laki', 'Single', 'BEKASI', '082137678500', 'kenz66912@gmail.com'),
(2, 2, 'KORNELIA KIDI', 'Perempuan', 'Single', 'NTT', '089629897830', 'lia@gmail.com');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelamar`
--

CREATE TABLE `pelamar` (
  `id_pelamar` int(11) NOT NULL,
  `nama_pel` varchar(128) NOT NULL,
  `email_pel` varchar(128) NOT NULL,
  `id_lowongan` int(11) NOT NULL,
  `status_pel` enum('Diterima','Ditolak') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelatihan`
--

CREATE TABLE `pelatihan` (
  `id_pelatihan` int(11) NOT NULL,
  `nama_pelatihan` varchar(128) NOT NULL,
  `deskripsi_pelatihan` varchar(128) NOT NULL,
  `tgl_pelaksanaan` date NOT NULL,
  `jam_pelaksanaan` time NOT NULL,
  `durasi_pelatihan` varchar(128) NOT NULL,
  `lokasi_pelatihan` varchar(128) NOT NULL,
  `pemateri_pelatihan` varchar(128) NOT NULL,
  `status_pelatihan` enum('Terlaksana','Tidak Terlaksana') NOT NULL,
  `id_pegawai` int(11) NOT NULL,
  `kapasitas` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penggajian`
--

CREATE TABLE `penggajian` (
  `id_jabatan` int(11) NOT NULL,
  `id_penggajian` int(11) NOT NULL,
  `gaji_pokok` decimal(50,0) NOT NULL,
  `bonus` decimal(50,0) NOT NULL,
  `potongan_bpjs` decimal(50,0) DEFAULT 0,
  `potongan_pajak` decimal(50,0) DEFAULT 0,
  `potongan_lain` decimal(50,0) DEFAULT 0,
  `id_peg` int(11) NOT NULL,
  `total_potongan` decimal(50,0) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `penggajian`
--

INSERT INTO `penggajian` (`id_jabatan`, `id_penggajian`, `gaji_pokok`, `bonus`, `potongan_bpjs`, `potongan_pajak`, `potongan_lain`, `id_peg`, `total_potongan`) VALUES
(1, 13, 250000000, 150000, 200000, 1000000, 100000, 1, 700000),
(2, 15, 250000000, 3000000, 200000, 300000, 0, 2, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `penggajian_potongan`
--

CREATE TABLE `penggajian_potongan` (
  `id_penggajian` int(11) NOT NULL,
  `id_potongan` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `penggajian_potongan`
--

INSERT INTO `penggajian_potongan` (`id_penggajian`, `id_potongan`) VALUES
(13, 26),
(15, 27),
(15, 28);

-- --------------------------------------------------------

--
-- Struktur dari tabel `penghargaan`
--

CREATE TABLE `penghargaan` (
  `id_peng` int(11) NOT NULL,
  `nama_peng` varchar(128) NOT NULL,
  `jenis_peng` varchar(128) NOT NULL,
  `desk_peng` text NOT NULL,
  `id_peg` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengunduran_diri`
--

CREATE TABLE `pengunduran_diri` (
  `id_pengunduran` int(11) NOT NULL,
  `id_karyawan` int(11) NOT NULL,
  `tanggal_pengajuan` date NOT NULL,
  `tanggal_efektif` date NOT NULL,
  `alasan` text NOT NULL,
  `status_pengajuan` enum('Menunggu','Disetujui','Ditolak','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penilaian`
--

CREATE TABLE `penilaian` (
  `id_penilaian` int(11) NOT NULL,
  `id_pegawai` int(11) NOT NULL,
  `id_KPI` int(11) NOT NULL,
  `nilai` int(11) NOT NULL,
  `periode penilaian` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `phk`
--

CREATE TABLE `phk` (
  `id_phk` int(11) NOT NULL,
  `id_karyawan` int(11) NOT NULL,
  `tanggal_phk` date NOT NULL,
  `alasan_phk` text NOT NULL,
  `status_kompensasi` enum('Diberikan','Tidak Diberikan','','') NOT NULL,
  `jumlah_kompensasi` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `potongan_gaji`
--

CREATE TABLE `potongan_gaji` (
  `id_potongan` int(11) NOT NULL,
  `nama_potongan` varchar(50) NOT NULL,
  `jumlah` decimal(50,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `potongan_gaji`
--

INSERT INTO `potongan_gaji` (`id_potongan`, `nama_potongan`, `jumlah`) VALUES
(26, 'service mobil', 50000000),
(27, 'Tidak masuk kerja', 250000),
(28, 'Tidak masuk kerja', 250000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `promosi`
--

CREATE TABLE `promosi` (
  `id_promosi` int(11) NOT NULL,
  `id_peg` int(11) NOT NULL,
  `id_jabatan` int(11) NOT NULL,
  `tgl_promosi` date NOT NULL,
  `alasan` text NOT NULL,
  `status_promosi` enum('Di setujui','Di tolak','Menunggu') NOT NULL DEFAULT 'Menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat perubahan kontrak`
--

CREATE TABLE `riwayat perubahan kontrak` (
  `id_perubahan` int(11) NOT NULL,
  `id_kontrak` int(11) NOT NULL,
  `tanggal_perubahan` date NOT NULL,
  `gaji_sebelum_perubahan` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tabel cuti`
--

CREATE TABLE `tabel cuti` (
  `id_cuti` int(11) NOT NULL,
  `id_peg` int(11) NOT NULL,
  `id_jenis_cuti` int(11) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status_cuti` varchar(100) NOT NULL,
  `tanggal_pengajuan` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tabel cuti`
--

INSERT INTO `tabel cuti` (`id_cuti`, `id_peg`, `id_jenis_cuti`, `tanggal_mulai`, `tanggal_selesai`, `status_cuti`, `tanggal_pengajuan`) VALUES
(0, 2314, 1, '2025-02-03', '2025-02-08', 'Pending', '2025-02-04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tunjangan`
--

CREATE TABLE `tunjangan` (
  `id_tunjangan` int(11) NOT NULL,
  `id_jabatan` int(11) NOT NULL,
  `nama_tunjangan` varchar(50) NOT NULL,
  `jumlah` decimal(50,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tunjangan`
--

INSERT INTO `tunjangan` (`id_tunjangan`, `id_jabatan`, `nama_tunjangan`, `jumlah`) VALUES
(4, 2, 'asuransi kecelakaan', 10000000),
(5, 1, 'asuransi kecelakaan', 10000000),
(8, 1, 'Tunjangan hari raya', 1000000),
(9, 2, 'Tunjangan hari raya', 1000000),
(10, 1, 'Akhir Tahun', 2000000),
(11, 2, 'Akhir Tahun', 2000000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `umpan_balik`
--

CREATE TABLE `umpan_balik` (
  `id_umpanbalik` enum('junior','senior','manajer') NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `tanggal` date NOT NULL,
  `rating` enum('5','4','3','2','1') NOT NULL,
  `komentar` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`ID_Absensi`);

--
-- Indeks untuk tabel `analisis_sdm`
--
ALTER TABLE `analisis_sdm`
  ADD PRIMARY KEY (`id_analisis`);

--
-- Indeks untuk tabel `departemen`
--
ALTER TABLE `departemen`
  ADD PRIMARY KEY (`id_dep`);

--
-- Indeks untuk tabel `jabatan`
--
ALTER TABLE `jabatan`
  ADD PRIMARY KEY (`id_jabatan`);

--
-- Indeks untuk tabel `jadwal_kehadiran`
--
ALTER TABLE `jadwal_kehadiran`
  ADD PRIMARY KEY (`ID_Jadwal`);

--
-- Indeks untuk tabel `jenis_cuti`
--
ALTER TABLE `jenis_cuti`
  ADD PRIMARY KEY (`id_jenis_cuti`);

--
-- Indeks untuk tabel `laporan_sdm`
--
ALTER TABLE `laporan_sdm`
  ADD PRIMARY KEY (`id_laporan`);

--
-- Indeks untuk tabel `lowongan`
--
ALTER TABLE `lowongan`
  ADD PRIMARY KEY (`id_lowongan`);

--
-- Indeks untuk tabel `mutasi`
--
ALTER TABLE `mutasi`
  ADD PRIMARY KEY (`id_mutasi`),
  ADD KEY `id_peg` (`id_peg`),
  ADD KEY `id_dep` (`id_dep`),
  ADD KEY `id_jabatan` (`id_jabatan`);

--
-- Indeks untuk tabel `pegawai`
--
ALTER TABLE `pegawai`
  ADD PRIMARY KEY (`id_peg`),
  ADD KEY `fk_pegawai_jabatan` (`id_jabatan`);

--
-- Indeks untuk tabel `pelamar`
--
ALTER TABLE `pelamar`
  ADD PRIMARY KEY (`id_pelamar`),
  ADD UNIQUE KEY `id_lowongan` (`id_lowongan`);

--
-- Indeks untuk tabel `pelatihan`
--
ALTER TABLE `pelatihan`
  ADD PRIMARY KEY (`id_pelatihan`),
  ADD UNIQUE KEY `id_pegawai` (`id_pegawai`);

--
-- Indeks untuk tabel `penggajian`
--
ALTER TABLE `penggajian`
  ADD PRIMARY KEY (`id_penggajian`),
  ADD KEY `fk_id_jabatan` (`id_jabatan`),
  ADD KEY `id_peg` (`id_peg`);

--
-- Indeks untuk tabel `penggajian_potongan`
--
ALTER TABLE `penggajian_potongan`
  ADD PRIMARY KEY (`id_penggajian`,`id_potongan`),
  ADD KEY `id_potongan` (`id_potongan`);

--
-- Indeks untuk tabel `penghargaan`
--
ALTER TABLE `penghargaan`
  ADD PRIMARY KEY (`id_peng`),
  ADD UNIQUE KEY `id_peg` (`id_peg`);

--
-- Indeks untuk tabel `pengunduran_diri`
--
ALTER TABLE `pengunduran_diri`
  ADD PRIMARY KEY (`id_pengunduran`);

--
-- Indeks untuk tabel `penilaian`
--
ALTER TABLE `penilaian`
  ADD PRIMARY KEY (`id_penilaian`),
  ADD UNIQUE KEY `id_pegawai` (`id_pegawai`),
  ADD UNIQUE KEY `id_KPI` (`id_KPI`);

--
-- Indeks untuk tabel `phk`
--
ALTER TABLE `phk`
  ADD PRIMARY KEY (`id_phk`);

--
-- Indeks untuk tabel `potongan_gaji`
--
ALTER TABLE `potongan_gaji`
  ADD PRIMARY KEY (`id_potongan`);

--
-- Indeks untuk tabel `promosi`
--
ALTER TABLE `promosi`
  ADD PRIMARY KEY (`id_promosi`),
  ADD KEY `id_peg` (`id_peg`),
  ADD KEY `id_jabatan` (`id_jabatan`);

--
-- Indeks untuk tabel `tabel cuti`
--
ALTER TABLE `tabel cuti`
  ADD PRIMARY KEY (`id_cuti`);

--
-- Indeks untuk tabel `tunjangan`
--
ALTER TABLE `tunjangan`
  ADD PRIMARY KEY (`id_tunjangan`),
  ADD KEY `id_jabatan` (`id_jabatan`);

--
-- Indeks untuk tabel `umpan_balik`
--
ALTER TABLE `umpan_balik`
  ADD UNIQUE KEY `id_umpanbalik` (`id_umpanbalik`),
  ADD UNIQUE KEY `rating` (`rating`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `absensi`
--
ALTER TABLE `absensi`
  MODIFY `ID_Absensi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `analisis_sdm`
--
ALTER TABLE `analisis_sdm`
  MODIFY `id_analisis` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `departemen`
--
ALTER TABLE `departemen`
  MODIFY `id_dep` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `lowongan`
--
ALTER TABLE `lowongan`
  MODIFY `id_lowongan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `mutasi`
--
ALTER TABLE `mutasi`
  MODIFY `id_mutasi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pegawai`
--
ALTER TABLE `pegawai`
  MODIFY `id_peg` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2315;

--
-- AUTO_INCREMENT untuk tabel `pelamar`
--
ALTER TABLE `pelamar`
  MODIFY `id_pelamar` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pelatihan`
--
ALTER TABLE `pelatihan`
  MODIFY `id_pelatihan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `penggajian`
--
ALTER TABLE `penggajian`
  MODIFY `id_penggajian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `penghargaan`
--
ALTER TABLE `penghargaan`
  MODIFY `id_peng` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `penilaian`
--
ALTER TABLE `penilaian`
  MODIFY `id_penilaian` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `potongan_gaji`
--
ALTER TABLE `potongan_gaji`
  MODIFY `id_potongan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT untuk tabel `promosi`
--
ALTER TABLE `promosi`
  MODIFY `id_promosi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tunjangan`
--
ALTER TABLE `tunjangan`
  MODIFY `id_tunjangan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `mutasi`
--
ALTER TABLE `mutasi`
  ADD CONSTRAINT `mutasi_ibfk_1` FOREIGN KEY (`id_peg`) REFERENCES `pegawai` (`id_peg`),
  ADD CONSTRAINT `mutasi_ibfk_2` FOREIGN KEY (`id_dep`) REFERENCES `departemen` (`id_dep`),
  ADD CONSTRAINT `mutasi_ibfk_3` FOREIGN KEY (`id_jabatan`) REFERENCES `jabatan` (`id_jabatan`);

--
-- Ketidakleluasaan untuk tabel `pegawai`
--
ALTER TABLE `pegawai`
  ADD CONSTRAINT `fk_pegawai_jabatan` FOREIGN KEY (`id_jabatan`) REFERENCES `jabatan` (`id_jabatan`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pelamar`
--
ALTER TABLE `pelamar`
  ADD CONSTRAINT `pelamar_ibfk_1` FOREIGN KEY (`id_lowongan`) REFERENCES `lowongan` (`id_lowongan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pelatihan`
--
ALTER TABLE `pelatihan`
  ADD CONSTRAINT `pelatihan_ibfk_1` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id_peg`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `penggajian`
--
ALTER TABLE `penggajian`
  ADD CONSTRAINT `fk_id_jabatan` FOREIGN KEY (`id_jabatan`) REFERENCES `jabatan` (`id_jabatan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `penggajian_ibfk_1` FOREIGN KEY (`id_peg`) REFERENCES `pegawai` (`id_peg`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `penggajian_potongan`
--
ALTER TABLE `penggajian_potongan`
  ADD CONSTRAINT `penggajian_potongan_ibfk_1` FOREIGN KEY (`id_penggajian`) REFERENCES `penggajian` (`id_penggajian`) ON DELETE CASCADE,
  ADD CONSTRAINT `penggajian_potongan_ibfk_2` FOREIGN KEY (`id_potongan`) REFERENCES `potongan_gaji` (`id_potongan`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `penghargaan`
--
ALTER TABLE `penghargaan`
  ADD CONSTRAINT `penghargaan_ibfk_1` FOREIGN KEY (`id_peg`) REFERENCES `pegawai` (`id_peg`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `promosi`
--
ALTER TABLE `promosi`
  ADD CONSTRAINT `promosi_ibfk_1` FOREIGN KEY (`id_peg`) REFERENCES `pegawai` (`id_peg`),
  ADD CONSTRAINT `promosi_ibfk_2` FOREIGN KEY (`id_jabatan`) REFERENCES `jabatan` (`id_jabatan`);

--
-- Ketidakleluasaan untuk tabel `tunjangan`
--
ALTER TABLE `tunjangan`
  ADD CONSTRAINT `tunjangan_ibfk_1` FOREIGN KEY (`id_jabatan`) REFERENCES `jabatan` (`id_jabatan`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
