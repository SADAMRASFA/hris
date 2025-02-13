<!-- Modal Tambah Mutasi -->
<div class="modal fade" id="mutasiModal" tabindex="-1" aria-labelledby="mutasiModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mutasiModalLabel">Tambah Data Mutasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="id_pegawai" class="form-label">ID Pegawai</label>
                        <input type="text" class="form-control" id="id_pegawai" name="id_pegawai" required>
                    </div>
                    <div class="mb-3">
                        <label for="jbt_lama" class="form-label">Jabatan Lama</label>
                        <input type="text" class="form-control" id="jbt_lama" name="jbt_lama" required>
                    </div>
                    <div class="mb-3">
                        <label for="jbt_baru" class="form-label">Jabatan Baru</label>
                        <input type="text" class="form-control" id="jbt_baru" name="jbt_baru" required>
                    </div>
                    <div class="mb-3">
                        <label for="dpm_lama" class="form-label">Departemen Lama</label>
                        <input type="text" class="form-control" id="dpm_lama" name="dpm_lama" required>
                    </div>
                    <div class="mb-3">
                        <label for="dpm_baru" class="form-label">Departemen Baru</label>
                        <input type="text" class="form-control" id="dpm_baru" name="dpm_baru" required>
                    </div>
                    <div class="mb-3">
                        <label for="tgl_mutasi" class="form-label">Tanggal Mutasi</label>
                        <input type="date" class="form-control" id="tgl_mutasi" name="tgl_mutasi" required>
                    </div>
                    <div class="mb-3">
                        <label for="alasan_mutasi" class="form-label">Alasan Mutasi</label>
                        <textarea class="form-control" id="alasan_mutasi" name="alasan_mutasi" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="status_mutasi" class="form-label">Status Mutasi</label>
                        <select class="form-control" id="status_mutasi" name="status_mutasi" required>
                            <option value="">Pilih Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add_mutasi" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Mutasi -->
<div class="modal fade" id="editMutasiModal" tabindex="-1" aria-labelledby="editMutasiModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMutasiModalLabel">Edit Data Mutasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" id="edit_id_mutasi" name="id_mutasi">
                    <div class="mb-3">
                        <label for="edit_id_pegawai" class="form-label">ID Pegawai</label>
                        <input type="text" class="form-control" id="edit_id_pegawai" name="id_pegawai" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_jbt_lama" class="form-label">Jabatan Lama</label>
                        <input type="text" class="form-control" id="edit_jbt_lama" name="jbt_lama" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_jbt_baru" class="form-label">Jabatan Baru</label>
                        <input type="text" class="form-control" id="edit_jbt_baru" name="jbt_baru" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_dpm_lama" class="form-label">Departemen Lama</label>
                        <input type="text" class="form-control" id="edit_dpm_lama" name="dpm_lama" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_dpm_baru" class="form-label">Departemen Baru</label>
                        <input type="text" class="form-control" id="edit_dpm_baru" name="dpm_baru" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_tgl_mutasi" class="form-label">Tanggal Mutasi</label>
                        <input type="date" class="form-control" id="edit_tgl_mutasi" name="tgl_mutasi" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_alasan_mutasi" class="form-label">Alasan Mutasi</label>
                        <textarea class="form-control" id="edit_alasan_mutasi" name="alasan_mutasi" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status_mutasi" class="form-label">Status Mutasi</label>
                        <select class="form-control" id="edit_status_mutasi" name="status_mutasi" required>
                            <option value="">Pilih Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_mutasi" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>