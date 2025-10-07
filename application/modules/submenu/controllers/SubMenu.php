<?php defined('BASEPATH') or exit('No direct script access allowed');

class SubMenu extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->_guard();
        $this->load->model('Menu_model');
        $this->load->model('Generic_model');
        $this->load->library(['form_validation', 'session']);
        $this->load->helper(['url', 'form']);
    }

    private function _guard()
    {
        if (!$this->session->userdata('logged_in')) redirect('login');
        if ((int)$this->session->userdata('role_id') !== 1) show_error('Forbidden', 403);
    }

    public function index($menu_id = null)
    {
        $data['title']   = 'Kelola Submenu';
        $data['menus']   = $this->Menu_model->get_all_menus(true);

        // ✅ BACA menu_id dari query string lebih dulu
        $selected = (int) $this->input->get('menu_id');

        // ✅ Jika tidak ada di query, pakai parameter URI (jika ada)
        if (!$selected && $menu_id !== null) {
            $selected = (int) $menu_id;
        }

        // ✅ Jika tetap kosong, fallback ke item pertama kalau ada
        if (!$selected && !empty($data['menus'])) {
            $selected = (int) $data['menus'][0]['id'];
        }

        // (opsional) validasi: pastikan selected ada di daftar menus aktif
        $validIds = array_column($data['menus'], 'id');
        if ($selected && !in_array($selected, $validIds)) {
            // jika invalid, fallback ke pertama
            $selected = (int) ($data['menus'][0]['id'] ?? 0);
        }

        $data['menu_id'] = $selected ?: null;
        $data['subs']    = $data['menu_id'] ? $this->Menu_model->get_submenus_by_menu($data['menu_id']) : [];
        $data['roles']   = $this->Menu_model->get_all_roles();

        $data['user']    = $this->session->userdata('username');
        $data['role_id'] = $this->session->userdata('role_id');

        $this->load->view('templates/header', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('submenu/submenu', $data);
        $this->load->view('templates/footer');
    }

    public function create($menu_id)
    {
        $data['title']   = 'Tambah Submenu';
        $data['menu_id'] = (int)$menu_id;
        $data['menus']   = $this->Menu_model->get_all_menus(true);
        $data['user']    = $this->session->userdata('username');
        $data['role_id'] = $this->session->userdata('role_id');

        $this->load->view('templates/header', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('submenu/submenu_create', $data);
        $this->load->view('templates/footer');
    }

    public function store()
    {
        $this->form_validation->set_rules('menu_id', 'Menu', 'required|integer');
        $this->form_validation->set_rules('name', 'Nama', 'required|trim');
        $this->form_validation->set_rules('slug', 'Slug', 'required|alpha_dash|is_unique[submenus.slug]');
        $this->form_validation->set_rules('url',  'URL',  'required|trim');
        $this->form_validation->set_rules('sort_order', 'Urutan', 'required|integer');

        if ($this->form_validation->run() === FALSE) return $this->create($this->input->post('menu_id'));

        $payload = [
            'menu_id'    => (int)$this->input->post('menu_id', TRUE),
            'name'       => $this->input->post('name', TRUE),
            'slug'       => $this->input->post('slug', TRUE),
            'url'        => $this->input->post('url', TRUE),
            'icon'       => $this->input->post('icon', TRUE) ?: NULL,
            'is_active'  => (int)$this->input->post('is_active', TRUE),
            'sort_order' => (int)$this->input->post('sort_order', TRUE)
        ];

        $this->Menu_model->create_submenu($payload);
        $this->session->set_flashdata('message', 'Submenu ditambahkan.');

        // ✅ Perbaiki redirect: kembali ke halaman index dengan menu terpilih
        redirect('submenu/index/' . $payload['menu_id']);
        // Atau gunakan query string:
        // redirect('submenu/index?menu_id='.$payload['menu_id']);
    }

    public function edit($id)
    {
        $data['title'] = 'Edit Submenu';
        $data['sub']   = $this->Menu_model->get_submenu($id);
        if (!$data['sub']) show_404();

        $data['menus']  = $this->Menu_model->get_all_menus(true);
        $data['user']   = $this->session->userdata('username');
        $data['role_id'] = $this->session->userdata('role_id');

        $this->load->view('templates/header', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('submenu/submenu_edit', $data);
        $this->load->view('templates/footer');
    }

    public function update($id)
    {
        $sub = $this->Menu_model->get_submenu($id);
        if (!$sub) show_404();

        $is_unique = ($sub['slug'] === $this->input->post('slug')) ? '' : '|is_unique[submenus.slug]';
        $this->form_validation->set_rules('menu_id', 'Menu', 'required|integer');
        $this->form_validation->set_rules('name', 'Nama', 'required|trim');
        $this->form_validation->set_rules('slug', 'Slug', 'required|alpha_dash' . $is_unique);
        $this->form_validation->set_rules('url',  'URL',  'required|trim');
        $this->form_validation->set_rules('sort_order', 'Urutan', 'required|integer');

        if ($this->form_validation->run() === FALSE) return $this->edit($id);

        $payload = [
            'menu_id'    => (int)$this->input->post('menu_id', TRUE),
            'name'       => $this->input->post('name', TRUE),
            'slug'       => $this->input->post('slug', TRUE),
            'url'        => $this->input->post('url', TRUE),
            'icon'       => $this->input->post('icon', TRUE) ?: NULL,
            'is_active'  => (int)$this->input->post('is_active', TRUE),
            'sort_order' => (int)$this->input->post('sort_order', TRUE)
        ];

        $this->Menu_model->update_submenu($id, $payload);
        $this->session->set_flashdata('message', 'Submenu diperbarui.');

        // ✅ Perbaiki redirect
        redirect('submenu/index/' . $payload['menu_id']);
        // atau: redirect('submenu/index?menu_id='.$payload['menu_id']);
    }

    public function delete($id)
    {
        $sub = $this->Menu_model->get_submenu($id);
        if ($sub) {
            $menu_id = (int)$sub['menu_id'];
            $this->Menu_model->delete_submenu($id);
            $this->session->set_flashdata('message', 'Submenu dihapus.');

            // ✅ Perbaiki redirect
            redirect('submenu/index/' . $menu_id);
            // atau: redirect('submenu/index?menu_id='.$menu_id);
        }
        show_404();
    }
}
