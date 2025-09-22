<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">

                <section class="card">
                    <header class="card-header">
                        User Access — Menu
                        <span class="tools pull-right">
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                        </span>
                    </header>
                    <div class="card-body">
                        <?php if ($this->session->flashdata('message')): ?>
                            <div class="alert alert-success"><?= $this->session->flashdata('message'); ?></div>
                        <?php endif; ?>

                        <?php
                        $ci = &get_instance();
                        $ci->load->model(['Menu_model', 'Generic_model']);
                        $all_roles = isset($roles) ? $roles : $ci->Menu_model->get_all_roles();
                        $all_menus = isset($menus) ? $menus : $ci->Menu_model->get_all_menus(false);
                        ?>

                        <div class="adv-table">
                            <table class="display table table-bordered table-striped" id="dynamic-table">
                                <thead>
                                    <tr>
                                        <th>Menu</th>
                                        <?php foreach ($all_roles as $r): ?>
                                            <th class="text-center"><?= html_escape($r['role_name']); ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_menus as $m): ?>
                                        <tr>
                                            <td><strong><?= html_escape($m['name']); ?></strong> <small>(<?= html_escape($m['slug']); ?>)</small></td>
                                            <?php foreach ($all_roles as $r):
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

                <section class="card">
                    <header class="card-header">
                        User Access — Submenu
                        <span class="tools pull-right">
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                        </span>
                    </header>
                    <div class="card-body">

                        <?php foreach ($all_menus as $m):
                            $subs = $ci->Menu_model->get_submenus_by_menu($m['id'], false);
                            if (empty($subs)) continue;
                        ?>
                            <h4 style="margin-top:10px;"><?= html_escape($m['name']); ?></h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Submenu</th>
                                            <?php foreach ($all_roles as $r): ?>
                                                <th class="text-center"><?= html_escape($r['role_name']); ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($subs as $s): ?>
                                            <tr>
                                                <td><?= html_escape($s['name']); ?> <small>(<?= html_escape($s['slug']); ?>)</small></td>
                                                <?php foreach ($all_roles as $r):
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
                        <?php endforeach; ?>

                    </div>
                </section>

            </div>
        </div>
    </section>
</section>