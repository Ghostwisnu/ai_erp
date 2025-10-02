<?php defined('BASEPATH') or exit('No direct script access allowed');

class Listuser extends MX_Controller
{
    private $table = 'users';

    public function __construct()
    {
        parent::__construct();
        $this->_guard();
        $this->load->model('Generic_model', 'gm');
        $this->load->helper(['paging']);
    }

    private function _guard()
    {
        if (!$this->session->userdata('logged_in')) redirect('login');
    }

    // Display the list of users
    public function index()
    {
        $q = trim($this->input->get('q'));
        $sort_by = $this->input->get('sort_by', TRUE) ?? 'id';
        $sort_order = $this->input->get('sort_order', TRUE) ?? 'asc';

        $per = 10;
        $offset = max(0, (int)$this->input->get('page'));

        if ($q !== '') {
            $this->db->like('username', $q);
        }

        // JOIN dengan tabel roles untuk mendapatkan nama role
        $this->db->select('users.*, roles.role_name');
        $this->db->join('roles', 'roles.id = users.role_id', 'left');

        $total = $this->db->count_all_results($this->table, FALSE);

        $this->db->order_by($sort_by, $sort_order)->limit($per, $offset);
        $rows = $this->db->get()->result_array();

        $custom_rows = [];
        $counter = 1;
        foreach ($rows as $row) {
            $row['custom_id'] = $counter++;
            $row['status'] = ($row['is_active'] == 1) ? 'Active' : 'Blocked';
            $row['attendance_status'] = ($row['attendance_status'] == 1) ? 'Present' : 'Absent';
            $custom_rows[] = $row;
        }

        // Ambil data roles untuk dropdown
        $roles = $this->gm->get_all_data('roles'); // Pastikan data roles diambil

        $data = [
            'title' => 'User List',
            'q' => $q,
            'sort_by' => $sort_by,
            'sort_order' => $sort_order,
            'columns' => [
                ['key' => 'custom_id', 'label' => 'Custom ID'],
                ['key' => 'username', 'label' => 'Username'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'role_name', 'label' => 'Role'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'attendance_status', 'label' => 'Attendance Status'],
            ],
            'rows' => $custom_rows,
            'roles' => $roles, // Kirim data roles ke view
            'actions' => [
                ['label' => 'Edit', 'class' => 'warning', 'url' => 'listuser/edit/{id}'],
                ['label' => 'Delete', 'class' => 'danger', 'url' => 'listuser/delete/{id}', 'confirm' => 'Are you sure you want to delete this user?'],
            ],
            'create_url' => site_url('listuser/create'),
            'pagination_links' => build_pagination($this, site_url('listuser'), $total, $per),
        ];

        $this->_render('listuser/listuser', $data);
    }

    public function create()
    {
        // Ambil data roles dari tabel roles
        $roles = $this->db->select('id, role_name')->get('roles')->result_array(); // Ambil semua data roles

        // Debugging: Jika data roles kosong, beri tahu
        if (empty($roles)) {
            echo "No roles available";
            die(); // Jika tidak ada data roles, hentikan eksekusi
        }

        // Kirim data roles ke view
        $data = [
            'title' => 'Create New User',
            'post_url' => site_url('listuser/store'),
            'back_url' => site_url('listuser'),
            'roles' => $roles,  // Kirim data roles ke view
        ];

        // Render view dengan data
        $this->_render('listuser/create', $data);
    }

    // Store the new user
    public function store()
    {
        // Set rules for form validation
        $this->form_validation->set_rules('username', 'Username', 'required|trim|is_unique[users.username]');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('password', 'Password', 'required|trim|min_length[6]');

        // If validation fails, return to the create form
        if ($this->form_validation->run() == FALSE) {
            return $this->create();
        }

        // If validation passes, insert new user
        $data = [
            'username' => $this->input->post('username', TRUE),
            'email' => $this->input->post('email', TRUE),
            'password' => password_hash($this->input->post('password', TRUE), PASSWORD_DEFAULT),
            'role_id' => $this->input->post('role_id', TRUE),
            'is_active' => 0, // Default status is active
        ];

        $this->gm->insert_data($this->table, $data);
        $this->session->set_flashdata('message', 'User has been added.');
        redirect('listuser');
    }

    public function update_status($id, $status)
    {
        // Ubah status aktif menjadi blocked atau sebaliknya
        $data = ['is_active' => $status];
        $this->gm->update_data($this->table, $data, ['id' => $id]);

        // Set flash message dan redirect
        $this->session->set_flashdata('message', 'User status updated.');
        redirect('listuser');
    }

    // Update user role
    public function update_role()
    {
        // Get form data
        $user_id = $this->input->post('user_id');
        $role_id = $this->input->post('role_id');

        // Update role in the database
        $data = ['role_id' => $role_id];
        $this->gm->update_data($this->table, $data, ['id' => $user_id]);

        // Set flash message and redirect
        $this->session->set_flashdata('message', 'Role updated.');
        redirect('listuser');
    }

    // Other CRUD methods for update, delete, etc.

    private function _render($view, $data)
    {
        $this->load->view('templates/header', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view($view, $data);
        $this->load->view('templates/footer');
    }
}
