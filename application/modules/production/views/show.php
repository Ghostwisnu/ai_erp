<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        <?= html_escape($title ?? 'Production Report Detail') ?>
                        <span class="tools pull-right">
                            <a class="btn btn-sm btn-default" href="<?= site_url('production') ?>">Back</a>
                            <?php if (!empty($hdr['id'])): ?>
                                <a class="btn btn-sm btn-primary" target="_blank" href="<?= site_url('production/pdf/' . (int)$hdr['id']) ?>">Export PDF</a>
                            <?php endif; ?>
                        </span>
                    </header>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <th>Kode Production</th>
                                        <td><?= html_escape($hdr['prod_code'] ?? '') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Kode RO</th>
                                        <td><?= html_escape($ro['no_ro'] ?? '') ?></td>
                                    </tr>
                                    <tr>
                                        <th>No WO</th>
                                        <td><?= html_escape($wo_no ?? '') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Item (WO L2)</th>
                                        <td><?= html_escape($sfg ?? '') ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <th>Brand</th>
                                        <td><?= html_escape($brand ?? '') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Departement</th>
                                        <td><?= html_escape($dept ?? '') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Art &amp; Color</th>
                                        <td><?= html_escape($hdr['art_color'] ?? '') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Total Qty</th>
                                        <td><?= (int)($hdr['total_qty'] ?? 0) ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <h5 class="mt-3">Rincian Per Size</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th style="width:60px;">#</th>
                                        <th>Size</th>
                                        <th class="text-right" style="width:120px;">Plan (WO)</th>
                                        <th class="text-right" style="width:120px;">Input Qty</th>
                                        <th class="text-right" style="width:120px;">Qty Kurang</th>
                                        <th style="width:220px;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($details)): $i = 1;
                                        foreach ($details as $d): ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= html_escape($d['size_name'] ?? ('#' . $d['size_id'])) ?></td>
                                                <td class="text-right"><?= (int)($d['plan_qty'] ?? 0) ?></td>
                                                <td class="text-right"><?= (int)($d['input_qty'] ?? 0) ?></td>
                                                <td class="text-right"><?= (int)($d['short_qty'] ?? 0) ?></td>
                                                <td><?= html_escape($d['status_size'] ?? '') ?></td>
                                            </tr>
                                        <?php endforeach;
                                    else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No data</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>