<section id="main-content">
    <section class="wrapper">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">List Work Orders</h3>
                <a href="<?= site_url('wo/create') ?>" class="btn btn-primary">Buat WO</a>
            </div>

            <div class="card-body">
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
                            <th>Unit</th>
                            <th>Date Order</th>
                            <th>X-Factory</th>
                            <th class="text-right">Total Size Qty</th>
                            <th>Created</th>
                            <th>WO Number</th> <!-- Added column for WO Number -->
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
                                    <td><?= htmlspecialchars($row['unit_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['date_order'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['x_factory_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-right">
                                        <?= htmlspecialchars(rtrim(rtrim(number_format((float)($row['total_size_qty'] ?? 0), 6, '.', ''), '0'), '.'), ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['no_wo'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td> <!-- Display No WO -->
                                    <td>
                                        <a href="<?= site_url('wo/edit/' . (int)$row['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="<?= site_url('wo/export/' . (int)$row['id']) ?>" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener">PDF</a>
                                        <!-- Optional delete action if method is implemented -->
                                        <!-- <a href="<?= site_url('wo/delete/' . (int)$row['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus WO ini?')">Delete</a> -->
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
            </div>
        </div>
    </section>
</section>