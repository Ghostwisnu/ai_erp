<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">

                    <div class="card-body">
                        <?php if ($this->session->flashdata('message')): ?>
                            <div class="alert alert-success"><?= $this->session->flashdata('message') ?></div>
                        <?php endif; ?>
                        <?php if (!empty($flash_error)): ?>
                            <div class="alert alert-danger"><?= $flash_error ?></div>
                        <?php endif; ?>

                        <!-- Search & Sorting -->
                        <form method="get" class="form-inline mb-3">
                            <div class="form-group">
                                <input type="text" class="form-control" name="q" value="<?= html_escape($q ?? '') ?>" placeholder="Search (RO No / WO No)">
                                <button class="btn btn-primary ml-2" type="submit">Search</button>
                                <a class="btn btn-default ml-1" href="<?= current_url() ?>">Reset</a>
                            </div>
                            <div class="form-group ml-2">
                                <label>Sort By:</label>
                                <select name="sort_by" class="form-control ml-2">
                                    <option value="id" <?= ($sort_by ?? '') == 'id' ? 'selected' : '' ?>>ID</option>
                                    <option value="no_ro" <?= ($sort_by ?? '') == 'no_ro' ? 'selected' : '' ?>>RO No</option>
                                    <option value="ro_date" <?= ($sort_by ?? '') == 'ro_date' ? 'selected' : '' ?>>RO Date</option>
                                    <option value="status_ro" <?= ($sort_by ?? '') == 'status_ro' ? 'selected' : '' ?>>Status</option>
                                </select>
                                <select name="sort_order" class="form-control ml-2">
                                    <option value="asc" <?= ($sort_order ?? '') == 'asc' ? 'selected' : '' ?>>Asc</option>
                                    <option value="desc" <?= ($sort_order ?? '') == 'desc' ? 'selected' : '' ?>>Desc</option>
                                </select>
                                <button class="btn btn-primary ml-2" type="submit">Sort</button>
                            </div>
                        </form>

                        <div class="adv-table">
                            <table class="display table table-bordered table-striped" id="dynamic-table">
                                <thead>
                                    <tr>
                                        <th>RO No</th>
                                        <th>WO No</th>
                                        <th>RO Date</th>
                                        <th>Status</th>
                                        <th>Ready?</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($rows)): foreach ($rows as $r): ?>
                                            <tr>
                                                <td><?= html_escape($r['no_ro']) ?></td>
                                                <td><?= html_escape($r['no_wo'] ?? '') ?></td>
                                                <td><?= html_escape($r['ro_date'] ?? '') ?></td>
                                                <td><?= html_escape($r['status_ro'] ?? '') ?></td>
                                                <td>
                                                    <?php if (!empty($r['ready'])): ?>
                                                        <span class="badge badge-success">OK</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning">Short</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-right">
                                                    <a href="<?= site_url('checkout/preview/' . (int)$r['id']) ?>" class="btn btn-sm btn-info">Preview</a>
                                                    <?php if (!empty($r['ready'])): ?>
                                                        <form method="post" action="<?= site_url('checkout/confirm/' . (int)$r['id']) ?>" class="d-inline"
                                                            onsubmit="return confirm('Lanjutkan checkout RO ini?');">
                                                            <input type="hidden"
                                                                name="<?= $this->security->get_csrf_token_name(); ?>"
                                                                value="<?= $this->security->get_csrf_hash(); ?>">
                                                            <button type="submit" class="btn btn-sm btn-success">Confirm & Checkout</button>
                                                        </form>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-secondary" disabled>Confirm & Checkout</button>
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