<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        Submenu Management
                        <span class="tools pull-right">
                            <?php if (!empty($menu_id)): ?>
                                <a href="<?= site_url('submenu/create/' . $menu_id); ?>" class="btn btn-sm btn-primary" style="color: black;">+ Tambah Submenu</a>
                            <?php endif; ?>
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                        </span>
                    </header>
                    <div class="card-body">

                        <?php if ($this->session->flashdata('message')): ?>
                            <div class="alert alert-success"><?= $this->session->flashdata('message'); ?></div>
                        <?php endif; ?>

                        <form method="get" action="<?= base_url('submenu/index'); ?>" class="form-inline mb-3">
                            <div class="form-group">
                                <label class="mr-2">Pilih Menu:</label>
                                <select name="menu_id" class="form-control" onchange="this.form.submit()">
                                    <?php foreach ($menus as $m): ?>
                                        <option value="<?= $m['id']; ?>" <?= ($menu_id == $m['id'] ? 'selected' : ''); ?>>
                                            <?= html_escape($m['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>

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
                                    foreach ($subs as $s): ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td><?= (int)$s['sort_order']; ?></td>
                                            <td><?= html_escape($s['name']); ?></td>
                                            <td><?= html_escape($s['slug']); ?></td>
                                            <td><?= html_escape($s['url']); ?></td>
                                            <td><i class="<?= html_escape($s['icon']); ?>"></i> <small><?= html_escape($s['icon']); ?></small></td>
                                            <td>
                                                <span class="badge bg-<?= !empty($s['is_active']) ? 'success' : 'danger'; ?>">
                                                    <?= !empty($s['is_active']) ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <a href="<?= site_url('submenu/edit/' . $s['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                                <a href="<?= site_url('submenu/delete/' . $s['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus submenu ini?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <hr>
                        <h4>Access Control (Submenu × Role)</h4>
                        <?php $ci = &get_instance();
                        $ci->load->model('Generic_model'); ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Submenu</th>
                                        <?php foreach ($roles as $r): ?>
                                            <th class="text-center"><?= html_escape($r['role_name']); ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subs as $s): ?>
                                        <tr>
                                            <td><strong><?= html_escape($s['name']); ?></strong> <small>(<?= html_escape($s['slug']); ?>)</small></td>
                                            <?php foreach ($roles as $r):
                                                $has = $ci->Generic_model->get_row_where('role_submenu', ['role_id' => $r['id'], 'submenu_id' => $s['id'], 'can_view' => 1]);
                                                $toggleTo = $has ? 0 : 1;
                                                $url = site_url('useraccess/submenu/' . $r['id'] . '/' . $s['id'] . '?can=' . $toggleTo);
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