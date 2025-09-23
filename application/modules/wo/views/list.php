<section id="main-content">
    <section class="wrapper">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">List Work Orders</h3>
                <a href="<?= site_url('wo/create') ?>" class="btn btn-primary">Buat WO</a>
            </div>

            <div class="card-body">
                <!-- Search Form -->
                <form method="GET" action="<?= site_url('wo/index') ?>" class="form-inline mb-3">
                    <input type="text" name="search" class="form-control mr-2" placeholder="Cari WO..." value="<?= $this->input->get('search') ?>">

                    <!-- Filter berdasarkan kategori WO -->
                    <select name="kategori_wo" class="form-control mr-2">
                        <option value="">-- Pilih Kategori --</option>
                        <option value="Injection" <?= $this->input->get('kategori_wo') == 'Injection' ? 'selected' : '' ?>>Injection</option>
                        <option value="Cementing" <?= $this->input->get('kategori_wo') == 'Cementing' ? 'selected' : '' ?>>Cementing</option>
                        <option value="Stitchdown" <?= $this->input->get('kategori_wo') == 'Stitchdown' ? 'selected' : '' ?>>Stitchdown</option>
                    </select>

                    <button type="submit" class="btn btn-primary">Cari</button>
                    <a href="<?= site_url('wo/index') ?>" class="btn btn-secondary ml-2">Reset</a> <!-- Reset Button -->
                </form>

                <?php if ($this->session->flashdata('message')): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($this->session->flashdata('message'), ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th style="width:80px">WO ID</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Brand</th>
                            <th>Kategori</th> <!-- Ganti Unit dengan Kategori -->
                            <th>Date Order</th>
                            <th>X-Factory</th>
                            <th class="text-right">Total Size Qty</th>
                            <th>Created</th>
                            <th>WO Number</th>
                            <th style="width:160px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($wo_list)): ?>
                            <?php foreach ($wo_list as $row): ?>
                                <tr>
                                    <td>#<?= (int)$row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['item_code'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['item_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['brand_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['kategori_wo'] ?? '', ENT_QUOTES, 'UTF-8') ?></td> <!-- Tampilkan kategori_wo -->
                                    <td><?= htmlspecialchars($row['date_order'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['x_factory_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-right">
                                        <?= htmlspecialchars(rtrim(rtrim(number_format((float)($row['total_size_qty'] ?? 0), 6, '.', ''), '0'), '.'), ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['no_wo'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <a href="<?= site_url('wo/edit/' . (int)$row['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="<?= site_url('wo/export/' . (int)$row['id']) ?>" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener">PDF</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="text-center text-muted">Belum ada data WO.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    <?= $this->pagination->create_links(); ?>
                </div>
            </div>
        </div>
    </section>
</section>