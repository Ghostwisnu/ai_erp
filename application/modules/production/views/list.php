<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        <?= html_escape($title ?? 'Laporan Produksi') ?>
                        <span class="tools pull-right">
                            <a href="<?= site_url('production/ro_list') ?>" class="btn btn-sm btn-default">RO Submitted</a>
                        </span>
                    </header>
                    <div class="card-body">
                        <?php if ($this->session->flashdata('message')): ?>
                            <div class="alert alert-success"><?= $this->session->flashdata('message') ?></div>
                        <?php endif; ?>
                        <?php if ($this->session->flashdata('flash_error')): ?>
                            <div class="alert alert-danger"><?= $this->session->flashdata('flash_error') ?></div>
                        <?php endif; ?>

                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Prod Code</th>
                                    <th>RO</th>
                                    <th>WO</th>
                                    <th>SFG</th>
                                    <th class="text-right">Total</th>
                                    <th>Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($rows)): foreach ($rows as $r): ?>
                                        <tr>
                                            <td><?= html_escape($r['prod_code']) ?></td>
                                            <td><?= html_escape($r['no_ro'] ?? '') ?></td>
                                            <td><?= html_escape($r['no_wo'] ?? '') ?></td>
                                            <td><?= html_escape($r['sfg_name'] ?? '') ?></td>
                                            <td class="text-right"><?= number_format((float)($r['total_qty'] ?? 0), 6) ?></td>
                                            <td><?= html_escape($r['status_prod'] ?? '') ?></td>
                                            <td class="text-right">
                                                <a class="btn btn-sm btn-info" href="<?= site_url('production/show/' . (int)$r['id']) ?>">Show</a>
                                                <a class="btn btn-sm btn-primary" target="_blank" href="<?= site_url('production/pdf/' . (int)$r['id']) ?>">PDF</a>
                                            </td>
                                        </tr>
                                    <?php endforeach;
                                else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No data</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                    </div>
                </section>
            </div>
        </div>
    </section>
</section>