<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        <?= html_escape($title ?? 'List') ?>
                        <span class="tools pull-right">
                            <?php if (!empty($create_url)): ?>
                                <a href="<?= $create_url ?>" class="btn btn-sm btn-primary">+ Tambah</a>
                            <?php endif; ?>

                            <?php if (!empty($import_url)): ?>
                                <form action="<?= $import_url ?>" method="post" enctype="multipart/form-data" style="display:inline-block;margin-left:8px">
                                    <input type="file" name="excel_file" accept=".xls,.xlsx" required>
                                    <button class="btn btn-sm btn-secondary" type="submit">Upload Excel</button>
                                </form>
                            <?php endif; ?>

                            <?php if (!empty($export_xlsx_url)): ?>
                                <a href="<?= $export_xlsx_url ?>" class="btn btn-sm btn-outline-primary">Export XLSX</a>
                            <?php endif; ?>
                            <?php if (!empty($export_pdf_url)): ?>
                                <a href="<?= $export_pdf_url ?>" class="btn btn-sm btn-outline-danger">Export PDF</a>
                            <?php endif; ?>

                            <!-- Tombol untuk download template Excel, hanya tampil pada halaman yang memerlukan fitur ini -->
                            <?php if (!isset($no_template) || $no_template !== true): ?>
                                <a href="<?= site_url(uri_string() . '/download_template') ?>" class="btn btn-sm btn-info">Download Template</a>
                            <?php endif; ?>

                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                        </span>
                    </header>

                    <div class="card-body">
                        <?php if ($this->session->flashdata('message')): ?>
                            <div class="alert alert-success"><?= $this->session->flashdata('message') ?></div>
                        <?php endif; ?>
                        <?php if (!empty($flash_error)): ?>
                            <div class="alert alert-danger"><?= $flash_error ?></div>
                        <?php endif; ?>

                        <!-- Filter Pencarian dan Sorting di Dalam Tabel -->
                        <form method="get" class="form-inline mb-3">
                            <div class="form-group">
                                <!-- Filter Pencarian -->
                                <input type="text" class="form-control" name="q" value="<?= html_escape($q ?? '') ?>" placeholder="Cari...">

                                <button class="btn btn-primary ml-2" type="submit">Search</button>
                                <a class="btn btn-default ml-1" href="<?= current_url() ?>">Reset</a>
                            </div>

                            <!-- Dropdown Sorting di Dalam Tabel -->
                            <div class="form-group ml-2">
                                <label>Sort By:</label>
                                <select name="sort_by" class="form-control ml-2">
                                    <option value="id" <?= $sort_by == 'id' ? 'selected' : '' ?>>ID</option>
                                    <option value="name" <?= $sort_by == 'name' ? 'selected' : '' ?>>Name</option>
                                    <option value="description" <?= $sort_by == 'description' ? 'selected' : '' ?>>Description</option>
                                </select>

                                <select name="sort_order" class="form-control ml-2">
                                    <option value="asc" <?= $sort_order == 'asc' ? 'selected' : '' ?>>Asc</option>
                                    <option value="desc" <?= $sort_order == 'desc' ? 'selected' : '' ?>>Desc</option>
                                </select>

                                <!-- Submit Button -->
                                <button class="btn btn-primary ml-2" type="submit">Sort</button>
                            </div>
                        </form>
                        <!-- Preview Table untuk Import -->
                        <?php if (!empty($import_preview)): ?>
                            <h4>Preview Data Import</h4>
                            <div class="adv-table">
                                <table class="display table table-bordered table-striped" id="preview-table">
                                    <thead>
                                        <tr>
                                            <?php foreach ($import_preview_columns as $col): ?>
                                                <th><?= html_escape($col) ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($import_preview as $row): ?>
                                            <tr>
                                                <?php foreach ($row as $cell): ?>
                                                    <td><?= html_escape($cell) ?></td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <form method="post" action="<?= $import_url ?>">
                                    <button class="btn btn-sm btn-success mt-3" type="submit">Confirm Import</button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <div class="adv-table">
                            <table class="display table table-bordered table-striped" id="dynamic-table">
                                <thead>
                                    <tr>
                                        <?php foreach ($columns as $col): ?>
                                            <th>
                                                <a href="<?= current_url() . '?sort_by=' . urlencode($col['key']) . '&sort_order=' . ($sort_order == 'asc' ? 'desc' : 'asc') ?>">
                                                    <?= html_escape($col['label']) ?>
                                                </a>
                                            </th>
                                        <?php endforeach; ?>
                                        <?php if (!empty($actions)): ?>
                                            <th class="text-right">Actions</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($rows)): foreach ($rows as $r): ?>
                                            <tr>
                                                <?php foreach ($columns as $col): ?>
                                                    <td>
                                                        <?php
                                                        $key = $col['key'];
                                                        $val = isset($r[$key]) ? $r[$key] : null;
                                                        echo isset($col['format']) ? call_user_func($col['format'], $val, $r)
                                                            : html_escape($val);
                                                        ?>
                                                    </td>
                                                <?php endforeach; ?>

                                                <?php if (!empty($actions)): ?>
                                                    <td class="text-right">
                                                        <?php foreach ($actions as $act):
                                                            $map = [];
                                                            foreach ($r as $k => $v) {
                                                                $map['{' . $k . '}'] = $v;
                                                            }
                                                            $url = site_url(strtr($act['url'], $map));
                                                        ?>
                                                            <a href="<?= $url ?>"
                                                                class="btn btn-sm btn-<?= $act['class'] ?? 'default' ?>"
                                                                <?= !empty($act['confirm']) ? 'onclick="return confirm(\'' . $act['confirm'] . '\')"' : '' ?>>
                                                                <?= $act['label'] ?>
                                                            </a>
                                                        <?php endforeach; ?>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach;
                                    else: ?>
                                        <tr>
                                            <td colspan="<?= count($columns) + (!empty($actions) ? 1 : 0) ?>" class="text-center">No data</td>
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