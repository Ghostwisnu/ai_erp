<?php defined('BASEPATH') or exit('No direct script access allowed');

class Login extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library(['form_validation', 'session']);
        $this->load->model('Generic_model');
    }

    public function index()
    {
        $data['title'] = 'Login - AI ERP';
        $this->load->view('templates/auth_header', $data);
        $this->load->view('login/login', $data);
        $this->load->view('templates/auth_footer');
    }

    public function login_user()
    {
        $this->form_validation->set_rules('username', 'Username or Email', 'required|trim');
        $this->form_validation->set_rules('password', 'Password', 'required|trim');

        if ($this->form_validation->run() === FALSE) {
            return $this->index();
        }

        $username_or_email = $this->input->post('username', TRUE); // XSS filter
        $password          = $this->input->post('password', TRUE);

        // Ambil user sebagai ARRAY
        $user = $this->Generic_model->get_user_by_email_or_username($username_or_email);

        if (!$user) {
            $this->session->set_flashdata('message', 'Username atau Email tidak terdaftar.');
            return redirect('login');
        }

        // Opsional: cek status aktif
        if (isset($user['is_active']) && (int)$user['is_active'] !== 1) {
            $this->session->set_flashdata('message', 'Akun belum aktif.');
            return redirect('login');
        }

        // Verifikasi password (pastikan hash di DB benar)
        if (!$this->Generic_model->verify_password($password, $user['password'])) {
            $this->session->set_flashdata('message', 'Password salah.');
            return redirect('login');
        }

        $user_data = [
            'user_id'   => $user['id'],
            'username'  => $user['username'],
            'email'     => $user['email'],
            'role_id'   => $user['role_id'] ?? null,
            'image'     => $user['image']  ?? null,
            'logged_in' => true
        ];
        $this->session->set_userdata($user_data);

        return redirect('dashboard');
    }

    public function logout()
    {
        $this->session->unset_userdata(['user_id', 'username', 'email', 'role_id', 'image', 'logged_in']);
        $this->session->set_flashdata('message', 'Anda berhasil logout.');
        return redirect('login');
    }
}
