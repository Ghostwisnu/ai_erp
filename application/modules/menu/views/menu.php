<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        Menu Management
                        <span class="tools pull-right">
                            <a href="<?= site_url('menu/create'); ?>" class="btn btn-sm btn-primary">+ Tambah Menu</a>
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                        </span>
                    </header>
                    <div class="card-body">
                        <?php if ($this->session->flashdata('message')): ?>
                            <div class="alert alert-success"><?= $this->session->flashdata('message'); ?></div>
                        <?php endif; ?>

                        <div class="adv-table">
                            <table class="display table table-bordered table-striped" id="dynamic-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Urut</th>
                                        <th>Nama</th>
                                        <th>Slug</th>
                                        <th>URL</th>
                                        <th>Icon</th>
                                        <th>Status</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1;
                                    foreach ($menus as $m): ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td><?= (int)$m['sort_order']; ?></td>
                                            <td><?= html_escape($m['name']); ?></td>
                                            <td><?= html_escape($m['slug']); ?></td>
                                            <td><?= html_escape($m['url']); ?></td>
                                            <td><i class="<?= html_escape($m['icon']); ?>"></i> <small><?= html_escape($m['icon']); ?></small></td>
                                            <td>
                                                <span class="badge bg-<?= !empty($m['is_active']) ? 'success' : 'danger'; ?>">
                                                    <?= !empty($m['is_active']) ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <a href="<?= site_url('menu/edit/' . $m['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                                <a href="<?= site_url('menu/delete/' . $m['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus menu ini?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <hr>
                        <h4>Access Control (Menu × Role)</h4>
                        <?php $ci = &get_instance();
                        $ci->load->model('Generic_model'); ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Menu</th>
                                        <?php foreach ($roles as $r): ?>
                                            <th class="text-center"><?= html_escape($r['role_name']); ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($menus as $m): ?>
                                        <tr>
                                            <td><strong><?= html_escape($m['name']); ?></strong> <small>(<?= html_escape($m['slug']); ?>)</small></td>
                                            <?php foreach ($roles as $r):
                                                $has = $ci->Generic_model->get_row_where('role_menu', ['role_id' => $r['id'], 'menu_id' => $m['id'], 'can_view' => 1]);
                                                $toggleTo = $has ? 0 : 1;
                                                $url = site_url('useraccess/menu/' . $r['id'] . '/' . $m['id'] . '?can=' . $toggleTo);
                                            ?>
                                                <td class="text-center">
                                                    <a href="<?= $url; ?>" class="btn btn-<?= $has ? 'success' : 'default'; ?> btn-sm">
                                                        <?= $has ? 'ON' : 'off'; ?>
                                                    </a>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </section>
            </div>
        </div>
    </section>
</section>