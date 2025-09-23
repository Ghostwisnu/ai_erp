<section id="main-content">
    <section class="wrapper">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Detail Work Order #<?= $wo['id'] ?></h3>
                <a href="<?= site_url('wo/index') ?>" class="btn btn-secondary">Kembali</a>
            </div>

            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th>Item Code</th>
                        <td><?= htmlspecialchars($wo_header['item_code'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th>Item Name</th>
                        <td><?= htmlspecialchars($wo_header['item_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th>Brand</th>
                        <td><?= htmlspecialchars($wo_header['brand_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th>Kategori</th>
                        <td><?= htmlspecialchars($wo['kategori_wo'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th>Date Order</th>
                        <td><?= htmlspecialchars($wo['date_order'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th>X-Factory Date</th>
                        <td><?= htmlspecialchars($wo['x_factory_date'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th>Total Size Qty</th>
                        <td><?= htmlspecialchars($wo['total_size_qty'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th>Catatan</th>
                        <td><?= nl2br(htmlspecialchars($wo['notes'], ENT_QUOTES, 'UTF-8')) ?></td>
                    </tr>
                    <tr>
                        <th>No WO</th>
                        <td><?= htmlspecialchars($wo_header['no_wo'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                </table>

                <h3 class="mb-2">Size Run</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="text-align: center; border: 1px solid #000;">Size</th>
                            <!-- Loop through the sizes data -->
                            <?php if (!empty($sizes)): ?>
                                <?php foreach ($sizes as $size): ?>
                                    <th style="text-align: center; border: 1px solid #000;">
                                        <?= htmlspecialchars($size['size_name'] ?? 'Unknown Size', ENT_QUOTES, 'UTF-8') ?>
                                    </th>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <th colspan="13" style="text-align: center; border: 1px solid #000;">No sizes available</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align: center; border: 1px solid #000;">Qty</td>
                            <!-- Loop through the quantities for each size -->
                            <?php if (!empty($sizes)): ?>
                                <?php foreach ($sizes as $size): ?>
                                    <td style="text-align: center; border: 1px solid #000;">
                                        <?= htmlspecialchars($size['qty'] ?? '0', ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <td colspan="13" style="text-align: center; border: 1px solid #000;">No quantity data available</td>
                            <?php endif; ?>
                        </tr>
                    </tbody>
                </table>

                <h4>SFG & Materials</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>SFG</th>
                            <th>Materials</th>
                            <th>Required Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($l2_list as $sfg): ?>
                            <tr>
                                <td><?= htmlspecialchars($sfg['item_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php foreach ($sfg['materials'] as $mat): ?>
                                        <div><?= htmlspecialchars($mat['item_name'], ENT_QUOTES, 'UTF-8') ?> (cons: <?= htmlspecialchars($mat['consumption'], ENT_QUOTES, 'UTF-8') ?>)</div>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <?php foreach ($sfg['materials'] as $mat): ?>
                                        <div><?= htmlspecialchars($mat['required_qty'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</section>