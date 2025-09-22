<?php defined('BASEPATH') or exit('No direct script access allowed');

class UserAccess extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->_guard();
        $this->load->model('Menu_model');
        $this->load->library('session');
        $this->load->helper('url');
    }

    private function _guard()
    {
        if (!$this->session->userdata('logged_in')) redirect('login');
        if ((int)$this->session->userdata('role_id') !== 1) show_error('Forbidden', 403);
    }

    public function menu($role_id, $menu_id)
    {
        $can = (int)$this->input->get('can', TRUE);
        $this->Menu_model->set_role_menu_access($role_id, $menu_id, $can ? 1 : 0);
        $this->session->set_flashdata('message', 'Akses menu diperbarui.');
        redirect($_SERVER['HTTP_REFERER'] ?? 'menu');
    }

    public function submenu($role_id, $submenu_id)
    {
        $can = (int)$this->input->get('can', TRUE);
        $this->Menu_model->set_role_submenu_access($role_id, $submenu_id, $can ? 1 : 0);
        $this->session->set_flashdata('message', 'Akses submenu diperbarui.');
        redirect($_SERVER['HTTP_REFERER'] ?? 'submenu');
    }
}
