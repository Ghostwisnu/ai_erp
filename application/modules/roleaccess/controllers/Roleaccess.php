<?php defined('BASEPATH') or exit('No direct script access allowed');

class Roleaccess extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Pastikan pengguna sudah login
        $this->_check_login();
        // Load model untuk mengambil data dari database jika diperlukan
        $this->load->model('Generic_model');
    }

    // Fungsi untuk mengecek apakah pengguna sudah login
    private function _check_login()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
    }

    // Fungsi utama untuk menampilkan daftar role
    public function index()
    {
        // Ambil data pengguna yang sedang login
        $data['user'] = $this->session->userdata('username');
        $data['role_id'] = $this->session->userdata('role_id');

        // Ambil data role dari database
        $data['roles'] = $this->Generic_model->get_all_data('roles');
        $data['title'] = 'Role Management - AI ERP';

        // Tampilkan halaman role access
        $this->load->view('templates/header', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('roleaccess/roleaccess', $data);
        $this->load->view('templates/footer');
    }

    // Fungsi untuk menambah role baru
    public function add_role()
    {
        // Ambil data dari form
        $role_name = $this->input->post('role_name');
        $role_description = $this->input->post('role_description');

        $data = [
            'role_name' => $role_name,
            'role_description' => $role_description
        ];

        // Insert data role ke database
        $this->Generic_model->insert_data('roles', $data);

        // Redirect ke halaman role management
        redirect('roleaccess');
    }

    // Fungsi untuk menghapus role
    public function delete_role($id)
    {
        // Hapus role berdasarkan ID
        $this->Generic_model->delete_data('roles', ['id' => $id]);

        // Redirect ke halaman role management
        redirect('roleaccess');
    }
}
