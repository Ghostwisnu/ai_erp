<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        <?= html_escape($title ?? 'Request Orders') ?>
                        <span class="tools pull-right">
                            <a href="<?= site_url('ro/create') ?>" class="btn btn-sm btn-primary">+ Add New Request Order</a>
                        </span>
                    </header>

                    <div class="card-body">
                        <?php if ($this->session->flashdata('message')): ?>
                            <div class="alert alert-success"><?= $this->session->flashdata('message') ?></div>
                        <?php endif; ?>
                        <?php if (!empty($flash_error)): ?>
                            <div class="alert alert-danger"><?= $flash_error ?></div>
                        <?php endif; ?>

                        <!-- Search & Sorting Filter -->
                        <form method="get" class="form-inline mb-3">
                            <div class="form-group">
                                <input type="text" class="form-control" name="q" value="<?= html_escape($q ?? '') ?>" placeholder="Search...">
                                <button class="btn btn-primary ml-2" type="submit">Search</button>
                                <a class="btn btn-default ml-1" href="<?= current_url() ?>">Reset</a>
                            </div>
                            <div class="form-group ml-2">
                                <label>Sort By:</label>
                                <select name="sort_by" class="form-control ml-2">
                                    <option value="id" <?= ($sort_by ?? '') == 'id' ? 'selected' : '' ?>>ID</option>
                                    <option value="no_ro" <?= ($sort_by ?? '') == 'no_ro' ? 'selected' : '' ?>>Request Order No</option>
                                    <option value="status_ro" <?= ($sort_by ?? '') == 'status_ro' ? 'selected' : '' ?>>Status</option>
                                    <option value="ro_date" <?= ($sort_by ?? '') == 'ro_date' ? 'selected' : '' ?>>RO Date</option>
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
                                        <th><a href="<?= current_url() . '?sort_by=no_ro&sort_order=' . (($sort_order ?? 'asc') == 'asc' ? 'desc' : 'asc') ?>">Request Order No</a></th>
                                        <th><a href="<?= current_url() . '?sort_by=status_ro&sort_order=' . (($sort_order ?? 'asc') == 'asc' ? 'desc' : 'asc') ?>">Status</a></th>
                                        <th><a href="<?= current_url() . '?sort_by=ro_date&sort_order=' . (($sort_order ?? 'asc') == 'asc' ? 'desc' : 'asc') ?>">RO Date</a></th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($rows)): foreach ($rows as $r): ?>
                                            <tr>
                                                <td><?= html_escape($r['no_ro']) ?></td>
                                                <td><?= html_escape($r['status_ro']) ?></td>
                                                <td><?= html_escape($r['ro_date'] ?? '') ?></td>
                                                <td class="text-right">
                                                    <a href="<?= site_url('ro/edit/' . (int)$r['id']) ?>" class="btn btn-sm btn-info">Edit</a>
                                                    <a href="<?= site_url('ro/pdf/' . (int)$r['id']) ?>" target="_blank" class="btn btn-sm btn-secondary">Export PDF</a>
                                                    <a href="<?= site_url('ro/delete/' . (int)$r['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach;
                                    else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No data</td>
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