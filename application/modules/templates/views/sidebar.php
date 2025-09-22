<?php
$role_id = (int)$this->session->userdata('role_id');
$ci = &get_instance();
$ci->load->model('Menu_model');
$menu_tree = $ci->Menu_model->get_menu_tree_by_role($role_id);

function is_active_uri($target)
{
    $ci = &get_instance();
    $current = trim($ci->uri->uri_string(), '/');
    $t = trim((string)$target, '/');
    if ($t === '') return false;
    return strpos($current, $t) === 0;
}
?>
<aside>
    <div id="sidebar" class="nav-collapse ">
        <ul class="sidebar-menu" id="nav-accordion">
            <?php foreach ($menu_tree as $m):
                $has_children = !empty($m['submenus']);
                $href         = $m['url'] ? site_url($m['url']) : 'javascript:;';
                $is_active    = is_active_uri($m['url'] ?? '') || array_reduce($m['submenus'], function ($c, $sm) {
                    return $c || is_active_uri($sm['url']);
                }, false);
            ?>
                <li class="<?= $has_children ? 'sub-menu' : '' ?>">
                    <a class="<?= $is_active ? 'active' : '' ?>" href="<?= $href ?>">
                        <i class="<?= !empty($m['icon']) ? $m['icon'] : 'fa fa-circle-o' ?>"></i>
                        <span><?= html_escape($m['name']) ?></span>
                    </a>

                    <?php if ($has_children): ?>
                        <ul class="sub" style="<?= $is_active ? 'display:block;' : '' ?>">
                            <?php foreach ($m['submenus'] as $sm): $sm_active = is_active_uri($sm['url']); ?>
                                <li class="<?= $sm_active ? 'active' : '' ?>">
                                    <a href="<?= site_url($sm['url']) ?>">
                                        <i class="<?= !empty($sm['icon']) ? $sm['icon'] : 'fa fa-angle-right' ?>"></i>
                                        <?= html_escape($sm['name']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</aside>