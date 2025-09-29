<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        <?= html_escape($title ?? 'RO Siap Dibuat Laporan Produksi') ?>
                        <span class="tools pull-right">
                            <a href="<?= site_url('production') ?>" class="btn btn-sm btn-default" style="color: black;">List Production</a>
                        </span>
                    </header>

                    <div class="card-body">
                        <?php if ($this->session->flashdata('message')): ?>
                            <div class="alert alert-success"><?= $this->session->flashdata('message') ?></div>
                        <?php endif; ?>
                        <?php if ($this->session->flashdata('flash_error')): ?>
                            <div class="alert alert-danger"><?= $this->session->flashdata('flash_error') ?></div>
                        <?php endif; ?>

                        <form method="get" class="form-inline mb-3">
                            <input type="text" class="form-control" name="q" value="<?= html_escape($q ?? '') ?>" placeholder="Search (RO / WO)">
                            <button class="btn btn-primary ml-2" type="submit">Search</button>
                            <a class="btn btn-default ml-1" href="<?= current_url() ?>">Reset</a>
                        </form>

                        <div class="adv-table">
                            <table class="display table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>RO No</th>
                                        <th>WO No</th>
                                        <th>SFG</th>
                                        <th>Brand</th>
                                        <th>Departement</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($rows)): foreach ($rows as $r): ?>
                                            <tr>
                                                <td><?= html_escape($r['no_ro']) ?></td>
                                                <td><?= html_escape($r['no_wo'] ?? '') ?></td>
                                                <td><?= html_escape(($r['sfg_code'] ? ($r['sfg_code'] . ' - ') : '') . ($r['sfg_name'] ?? '')) ?></td>
                                                <td><?= html_escape($r['brand_name'] ?? '') ?></td>
                                                <td><?= html_escape($r['departement_name'] ?? '') ?></td>
                                                <td class="text-right">
                                                    <?php if (($r['status_ro'] ?? '') === 'submitted' && empty($r['prod_id'])): ?>
                                                        <a href="<?= site_url('production/create/' . (int)$r['id']) ?>" class="btn btn-sm btn-primary">
                                                            Create Report
                                                        </a>
                                                    <?php elseif (($r['status_ro'] ?? '') === 'belum_lengkap' && !empty($r['prod_id'])): ?>
                                                        <a href="<?= site_url('production/edit_by_ro/' . (int)$r['id']) ?>" class="btn btn-sm btn-warning">
                                                            Update Report
                                                        </a>
                                                    <?php elseif (!empty($r['prod_id'])): ?>
                                                        <a href="<?= site_url('production/show/' . (int)$r['prod_id']) ?>" class="btn btn-sm btn-info">Show</a>
                                                        <a target="_blank" href="<?= site_url('production/pdf/' . (int)$r['prod_id']) ?>" class="btn btn-sm btn-secondary">PDF</a>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
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

                        <?php if (!empty($pagination_links)): ?>
                            <nav aria-label="Page nav" class="mt-2"><?= $pagination_links ?></nav>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>