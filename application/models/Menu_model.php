<?php defined('BASEPATH') or exit('No direct script access allowed');

class Menu_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        // Reuse utilitas CRUD umum dari Generic_model
        $this->load->model('Generic_model');
    }

    /* ===== ROLES ===== */
    public function get_all_roles()
    {
        // id, role_name, role_description, created_at
        return $this->Generic_model->get_all_data('roles');
    }

    /* ===== MENUS ===== */
    public function get_all_menus($only_active = false)
    {
        if ($only_active) $this->db->where('is_active', 1);
        return $this->db->order_by('sort_order', 'ASC')->get('menus')->result_array();
    }

    public function get_menu($id)
    {
        return $this->Generic_model->get_row_where('menus', ['id' => (int)$id]);
    }

    public function create_menu($data)
    {
        return $this->Generic_model->insert_data('menus', $data);
    }

    public function update_menu($id, $data)
    {
        return $this->Generic_model->update_data('menus', $data, ['id' => (int)$id]);
    }

    public function delete_menu($id)
    {
        // ON DELETE CASCADE pada submenus jika FK di DB sudah diset
        return $this->Generic_model->delete_data('menus', ['id' => (int)$id]);
    }

    /* ===== SUBMENUS ===== */
    public function get_submenus_by_menu($menu_id, $only_active = false)
    {
        $this->db->where('menu_id', (int)$menu_id);
        if ($only_active) $this->db->where('is_active', 1);
        return $this->db->order_by('sort_order', 'ASC')->get('submenus')->result_array();
    }

    public function get_submenu($id)
    {
        return $this->Generic_model->get_row_where('submenus', ['id' => (int)$id]);
    }

    public function create_submenu($data)
    {
        return $this->Generic_model->insert_data('submenus', $data);
    }

    public function update_submenu($id, $data)
    {
        return $this->Generic_model->update_data('submenus', $data, ['id' => (int)$id]);
    }

    public function delete_submenu($id)
    {
        return $this->Generic_model->delete_data('submenus', ['id' => (int)$id]);
    }

    /* ===== ACCESS CONTROL ===== */
    public function set_role_menu_access($role_id, $menu_id, $can_view = 1)
    {
        $role_id = (int)$role_id;
        $menu_id = (int)$menu_id;
        $can_view = (int)$can_view;

        $exists = $this->Generic_model->get_row_where('role_menu', [
            'role_id' => $role_id,
            'menu_id' => $menu_id
        ]);

        if ($exists) {
            return $this->Generic_model->update_data('role_menu', ['can_view' => $can_view], [
                'role_id' => $role_id,
                'menu_id' => $menu_id
            ]);
        }
        return $this->Generic_model->insert_data('role_menu', [
            'role_id' => $role_id,
            'menu_id' => $menu_id,
            'can_view' => $can_view
        ]);
    }

    public function set_role_submenu_access($role_id, $submenu_id, $can_view = 1)
    {
        $role_id = (int)$role_id;
        $submenu_id = (int)$submenu_id;
        $can_view = (int)$can_view;

        $exists = $this->Generic_model->get_row_where('role_submenu', [
            'role_id' => $role_id,
            'submenu_id' => $submenu_id
        ]);

        if ($exists) {
            return $this->Generic_model->update_data('role_submenu', ['can_view' => $can_view], [
                'role_id' => $role_id,
                'submenu_id' => $submenu_id
            ]);
        }
        return $this->Generic_model->insert_data('role_submenu', [
            'role_id' => $role_id,
            'submenu_id' => $submenu_id,
            'can_view' => $can_view
        ]);
    }

    /* ===== TREE UNTUK SIDEBAR BERDASAR ROLE ===== */
    public function get_menu_tree_by_role($role_id)
    {
        // Menus yang boleh dilihat
        $this->db->select('m.*')
            ->from('menus m')
            ->join('role_menu rm', 'rm.menu_id = m.id AND rm.can_view = 1')
            ->where('rm.role_id', (int)$role_id)
            ->where('m.is_active', 1)
            ->order_by('m.sort_order', 'ASC');
        $menus = $this->db->get()->result_array();

        // Submenus yang boleh dilihat untuk semua menu sekaligus
        $menu_ids = array_column($menus, 'id');
        $sub_by_menu = [];
        if (!empty($menu_ids)) {
            $this->db->select('sm.*')
                ->from('submenus sm')
                ->join('role_submenu rsm', 'rsm.submenu_id = sm.id AND rsm.can_view = 1')
                ->where_in('sm.menu_id', $menu_ids)
                ->where('rsm.role_id', (int)$role_id)
                ->where('sm.is_active', 1)
                ->order_by('sm.sort_order', 'ASC');
            $subs = $this->db->get()->result_array();

            foreach ($subs as $s) {
                $sub_by_menu[$s['menu_id']][] = $s;
            }
        }

        foreach ($menus as &$m) {
            $m['submenus'] = $sub_by_menu[$m['id']] ?? [];
        }
        return $menus;
    }
}
