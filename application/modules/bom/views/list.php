<section id="main-content">
    <section class="wrapper">
        <div class="row state-overview">
            <!-- tempat statistik bila perlu -->
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">List BOM</h3>
                <a href="<?= site_url('bom/create') ?>" class="btn btn-primary">Tambah BOM</a>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th style="width:60px">#</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Art &amp; Color</th>
                            <th style="width:180px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($bom_l1)): ?>
                            <?php $no = 1;
                            foreach ($bom_l1 as $bom): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($bom['item_code'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($bom['item_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($bom['category_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($bom['brand_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($bom['art_color'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <a href="<?= site_url('bom/edit/' . $bom['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="<?= site_url('bom/delete/' . $bom['id']) ?>"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Are you sure you want to delete this item?')">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">Belum ada data BOM.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</section>