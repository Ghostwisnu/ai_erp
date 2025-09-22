<section id="main-content">
    <section class="wrapper">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Surat Tanda Terima</h3>
                <a href="<?= site_url('checkin/create') ?>" class="btn btn-primary">Buat STR</a>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('message')): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($this->session->flashdata('message'), ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th style="width:80px">ID</th>
                            <th>No STR</th>
                            <th>No SJ</th>
                            <th>Tanggal</th>
                            <th>Dibuat</th>
                            <th style="width:140px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rows)): foreach ($rows as $r): ?>
                                <tr>
                                    <td>#<?= (int)$r['id'] ?></td>
                                    <td><?= htmlspecialchars($r['no_str'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($r['no_sj'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($r['arrival_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($r['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <a href="<?= site_url('checkin/pdf/' . (int)$r['id']) ?>" class="btn btn-secondary btn-sm" target="_blank">PDF</a>
                                        <a href="<?= site_url('checkin/delete_checkin/' . (int)$r['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus STR ini?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada STR.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</section>