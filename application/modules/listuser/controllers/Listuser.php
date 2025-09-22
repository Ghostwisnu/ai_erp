<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Listuser extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Ensure the user is logged in
        $this->_check_login();
        // Load model to handle data
        $this->load->model('Generic_model');
    }

    // Check if the user is logged in
    private function _check_login()
    {
        if (!$this->session->userdata('username')) {
            redirect('login');
        }
    }

    // Display the list of users
    public function index()
    {
        // Get the username and role_id from session
        $data['user'] = $this->session->userdata('username');
        $data['role_id'] = $this->session->userdata('role_id');

        // Get all users
        $data['users'] = $this->Generic_model->get_all_data('users');
        // Get all roles for role management
        $data['roles'] = $this->Generic_model->get_all_data('roles');
        $data['title'] = 'User List - AI ERP';

        // Load the view and pass the data
        $this->load->view('templates/header', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('listuser/listuser', $data);
        $this->load->view('templates/footer');
    }

    // Update user role
    public function update_role()
    {
        // Get form data
        $user_id = $this->input->post('user_id');
        $role_id = $this->input->post('role_id');

        // Update role in the database
        $data = ['role_id' => $role_id];
        $this->Generic_model->update_data('users', $data, ['id' => $user_id]);

        // Redirect back to the user list page
        redirect('listuser');
    }

    // Delete user
    public function delete($id)
    {
        $this->Generic_model->delete_data('users', ['id' => $id]);
        redirect('listuser');
    }
}
