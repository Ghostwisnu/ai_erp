<?php defined('BASEPATH') or exit('No direct script access allowed');

class Menu extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->_guard();
        $this->load->model('Menu_model');
        $this->load->library(['form_validation', 'session']);
        $this->load->helper(['url', 'form']);
    }

    private function _guard()
    {
        if (!$this->session->userdata('logged_in')) redirect('login');
        if ((int)$this->session->userdata('role_id') !== 1) show_error('Forbidden', 403);
    }

    public function index()
    {
        $data['title'] = 'Kelola Menu';
        $data['user'] = $this->session->userdata('username');
        $data['role_id'] = $this->session->userdata('role_id');
        $data['menus'] = $this->Menu_model->get_all_menus(false);
        $data['roles'] = $this->Menu_model->get_all_roles();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('menu/menu', $data);
        $this->load->view('templates/footer');
    }

    public function create()
    {
        $data['title'] = 'Tambah Menu';
        $data['user'] = $this->session->userdata('username');
        $data['role_id'] = $this->session->userdata('role_id');

        $this->load->view('templates/header', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('menu/menu_create', $data);
        $this->load->view('templates/footer');
    }

    public function store()
    {
        $this->form_validation->set_rules('name', 'Nama', 'required|trim');
        $this->form_validation->set_rules('slug', 'Slug', 'required|alpha_dash|is_unique[menus.slug]');
        $this->form_validation->set_rules('sort_order', 'Urutan', 'required|integer');

        if ($this->form_validation->run() === FALSE) return $this->create();

        $payload = [
            'name'       => $this->input->post('name', TRUE),
            'slug'       => $this->input->post('slug', TRUE),
            'url'        => $this->input->post('url', TRUE) ?: NULL,
            'icon'       => $this->input->post('icon', TRUE) ?: NULL,
            'is_active'  => (int)$this->input->post('is_active', TRUE),
            'sort_order' => (int)$this->input->post('sort_order', TRUE),
        ];
        $this->Menu_model->create_menu($payload);
        $this->session->set_flashdata('message', 'Menu ditambahkan.');
        redirect('menu/menu');
    }

    public function edit($id)
    {
        $data['title'] = 'Edit Menu';
        $data['menu']  = $this->Menu_model->get_menu($id);
        if (!$data['menu']) show_404();
        $data['user'] = $this->session->userdata('username');
        $data['role_id'] = $this->session->userdata('role_id');
        $this->load->view('templates/header', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('menu/menu_edit', $data);
        $this->load->view('templates/footer');
    }

    public function update($id)
    {
        $menu = $this->Menu_model->get_menu($id);
        if (!$menu) show_404();

        $is_unique = ($menu['slug'] === $this->input->post('slug')) ? '' : '|is_unique[menus.slug]';
        $this->form_validation->set_rules('name', 'Nama', 'required|trim');
        $this->form_validation->set_rules('slug', 'Slug', 'required|alpha_dash' . $is_unique);
        $this->form_validation->set_rules('sort_order', 'Urutan', 'required|integer');

        if ($this->form_validation->run() === FALSE) return $this->edit($id);

        $payload = [
            'name'       => $this->input->post('name', TRUE),
            'slug'       => $this->input->post('slug', TRUE),
            'url'        => $this->input->post('url', TRUE) ?: NULL,
            'icon'       => $this->input->post('icon', TRUE) ?: NULL,
            'is_active'  => (int)$this->input->post('is_active', TRUE),
            'sort_order' => (int)$this->input->post('sort_order', TRUE),
        ];
        $this->Menu_model->update_menu($id, $payload);
        $this->session->set_flashdata('message', 'Menu diperbarui.');
        redirect('menu/menu');
    }

    public function delete($id)
    {
        $this->Menu_model->delete_menu($id);
        $this->session->set_flashdata('message', 'Menu dihapus.');
        redirect('menu/menu');
    }
}
