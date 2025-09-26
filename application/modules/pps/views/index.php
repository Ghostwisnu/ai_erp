<section id="main-content">
    <section class="wrapper">
        <div class="card">
            <div class="card-header">
                <h3><?= html_escape($title ?? 'Production Summary') ?></h3>
            </div>

            <div class="card-body">
                <!-- Search Form -->
                <form method="GET" action="<?= site_url('pps/index') ?>" class="form-inline mb-3">
                    <input type="text" name="search" class="form-control mr-2" placeholder="Cari WO, Kategori WO, Brand..." value="<?= $this->input->get('search') ?>">
                    <button type="submit" class="btn btn-primary">Cari</button>
                    <a href="<?= site_url('pps/index') ?>" class="btn btn-secondary ml-2">Reset</a>
                </form>

                <!-- Export to PDF Button -->
                <a href="<?= site_url('pps/export_pdf?search=' . $this->input->get('search')) ?>" class="btn btn-success mb-3">Export to PDF</a>

                <?php if (!empty($wo_summary)): ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No WO</th>
                                <th>Kategori WO</th>
                                <th>Brand Name</th>
                                <th>Art & Color</th>
                                <th>Cutting (Qty)</th>
                                <th>Sewing (Qty)</th>
                                <th>Semi (Qty)</th>
                                <th>Lasting (Qty)</th>
                                <th>Finishing (Qty)</th>
                                <th>Packaging (Qty)</th>
                                <th>Finish Goods (Qty)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php foreach ($wo_summary as $wo): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= html_escape($wo['no_wo']) ?></td>
                                    <td><?= html_escape($wo['kategori_wo']) ?></td>
                                    <td><?= html_escape($wo['brand_name']) ?></td>
                                    <td><?= html_escape($wo['art_color']) ?></td>
                                    <td><?= $wo['cutting'] ?></td>
                                    <td><?= $wo['sewing'] ?></td>
                                    <td><?= $wo['semi'] ?></td>
                                    <td><?= $wo['lasting'] ?></td>
                                    <td><?= $wo['finishing'] ?></td>
                                    <td><?= $wo['packaging'] ?></td>
                                    <td><?= $wo['finish_goods'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Pagination Links -->
                    <div class="d-flex justify-content-center">
                        <?= $pagination ?>
                    </div>
                <?php else: ?>
                    <p>No data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</section>