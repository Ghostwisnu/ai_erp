<?php defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Pastikan pengguna sudah login
        $this->_check_login();

        // Load model untuk mengambil data dari database jika diperlukan

    }

    // Fungsi untuk mengecek apakah pengguna sudah login
    private function _check_login()
    {
        if (!$this->session->userdata('logged_in')) {
            // Jika pengguna belum login, redirect ke halaman login
            redirect('login');
        }
    }

    // Fungsi utama untuk menampilkan dashboard
    public function index()
    {
        // Ambil data untuk ditampilkan di dashboard (misalnya data pengguna atau statistik)
        $data['title'] = 'Dashboard - AI ERP';

        // Ambil data pengguna yang sedang login
        $data['user'] = $this->session->userdata('username');
        $data['role_id'] = $this->session->userdata('role_id');

        // Ambil statistik atau data terkait (contoh mengambil jumlah pengguna)
        $data['total_users'] = $this->Generic_model->get_count('users');

        // // Ambil data lain sesuai kebutuhan (misalnya jumlah pesanan atau produk)
        // $data['total_orders'] = $this->Generic_model->get_count('orders'); // Contoh untuk data pesanan (ganti dengan nama tabel yang sesuai)

        // Tampilkan halaman dashboard dengan data
        $this->load->view('templates/header', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('dashboard/dashboard', $data); // Halaman dashboard utama
        $this->load->view('templates/footer');
    }

    // Fungsi untuk logout dari dashboard
    public function logout()
    {
        // Hapus session saat logout
        $this->session->unset_userdata(['user_id', 'username', 'email', 'role_id', 'is_active', 'logged_in']);
        $this->session->set_flashdata('message', 'You have successfully logged out');
        redirect('login');
    }
}
