<?php defined('BASEPATH') or exit('No direct script access allowed');

class Register extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Tidak perlu load model lagi karena sudah di-autoload
        // $this->load->model('Generic_model'); // Sudah tidak perlu karena auto-load
    }

    // Fungsi untuk menampilkan form registrasi
    public function index()
    {
        $data['title'] = 'Register - AI ERP';
        $this->load->view('templates/auth_header', $data);
        $this->load->view('register/register', $data);
        $this->load->view('templates/auth_footer');
    }

    // Fungsi untuk menangani registrasi pengguna
    public function register_user()
    {
        // Validasi form input
        $this->form_validation->set_rules('username', 'Username', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('password', 'Password', 'required|trim|min_length[6]');
        $this->form_validation->set_rules('password_confirm', 'Confirm Password', 'required|trim|matches[password]');

        // Jika validasi gagal
        if ($this->form_validation->run() == false) {
            $this->index(); // Kembali ke halaman register jika validasi gagal
        } else {
            // Ambil input dari form
            $username = $this->input->post('username');
            $email = $this->input->post('email');
            $password = password_hash($this->input->post('password'), PASSWORD_DEFAULT); // Hash password

            // Menangani upload image (profile picture)
            $image = $this->_uploadImage();

            // Siapkan data untuk disimpan
            $data = [
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'image' => $image,
                'role_id' => 2, // Default role_id untuk user biasa
                'is_active' => 1, // User aktif
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Simpan data ke tabel 'users' menggunakan Generic_model
            $this->Generic_model->insert_data('users', $data);

            $image = $this->_uploadImage();  // Mengambil nama file yang diupload

            if ($image === 'default.jpg') {
                // Jika upload gagal, gunakan gambar default
                $this->session->set_flashdata('message', 'Image upload failed, using default image.');
            }


            // Set flash message dan redirect ke halaman login
            $this->session->set_flashdata('message', 'Registration successful! Please login.');
            redirect('login'); // Redirect ke halaman login
        }
    }

    // Fungsi untuk menangani upload gambar profil
    private function _uploadImage()
    {
        // Pastikan ada file yang dikirim
        if (!isset($_FILES['image']) || $_FILES['image']['error'] == UPLOAD_ERR_NO_FILE) {
            return 'default.jpg';  // Jika tidak ada file, kembalikan gambar default
        }

        // Folder tempat upload
        $uploadPath = FCPATH . 'assets/images/profiles/';

        // Jika folder tidak ada, coba buat folder
        if (!is_dir($uploadPath)) {
            if (!@mkdir($uploadPath, 0775, true)) {
                log_message('error', 'Gagal membuat folder upload: ' . $uploadPath);
                return 'default.jpg';
            }
        }

        // Validasi ekstensi file
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

        if (!in_array(strtolower($file_extension), $allowed_types)) {
            log_message('error', 'Jenis file tidak diizinkan: ' . $_FILES['image']['name']);
            return 'default.jpg';
        }

        // Validasi ukuran file (max 4MB)
        if ($_FILES['image']['size'] > 4096 * 1024) {  // 4MB
            log_message('error', 'Ukuran file terlalu besar: ' . $_FILES['image']['name']);
            return 'default.jpg';
        }

        // Ambil nama asli file dan pastikan aman
        $original_filename = $_FILES['image']['name'];
        $safe_filename = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $original_filename);  // Ganti karakter tidak valid
        $target_file = $uploadPath . $safe_filename;

        // Cek jika file dengan nama yang sama sudah ada
        if (file_exists($target_file)) {
            // Jika ada, tambahkan timestamp untuk membedakan
            $new_filename = time() . '_' . $safe_filename;
            $target_file = $uploadPath . $new_filename;
        }

        // Pindahkan file ke folder tujuan
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            // Jika berhasil, kembalikan nama file
            return $new_filename ?? $safe_filename;
        } else {
            log_message('error', 'Gagal meng-upload gambar: ' . $_FILES['image']['name']);
            return 'default.jpg';  // Gambar default jika gagal
        }
    }
}
