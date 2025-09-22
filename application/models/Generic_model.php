<?php defined('BASEPATH') or exit('No direct script access allowed');

class Generic_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // Get the last item counter from the table for generating new code
    public function get_last_item_counter($table)
    {
        // Fetch the last item by code, sorting in descending order
        $this->db->select('code');
        $this->db->from($table);
        $this->db->order_by('id', 'DESC');  // Sort by the ID in descending order to get the latest inserted item
        $this->db->limit(1); // Get only the latest one

        $last_item = $this->db->get()->row_array();

        // If we have a code, get the last number in the code
        if ($last_item && isset($last_item['code'])) {
            // Extract the number from the last code, assuming format is always [Category]-[Brand]-[Dept]-[Number]
            preg_match('/\d+$/', $last_item['code'], $matches);  // Match the number at the end of the code

            return (int)($matches[0] ?? 0);  // Return the counter, or 0 if not found
        }

        return 0;  // Return 0 if there's no item yet
    }

    // Generic_model.php (contoh)
    public function get_bom_l1_list()
    {
        $this->db->select('
        l1.id, i.item_code AS item_code, i.item_name AS item_name,
        c.name AS category_name, b.name AS brand_name, u.name AS unit_name
    ');
        $this->db->from('bom_l1 l1');
        $this->db->join('items i', 'i.id = l1.item_id', 'left');
        $this->db->join('categories c', 'c.id = i.category_id', 'left');
        $this->db->join('brands b', 'b.id = l1.brand_id', 'left'); // atau i.brand_id
        $this->db->join('units u', 'u.id = l1.unit_id', 'left');
        $this->db->order_by('l1.id', 'DESC');
        return $this->db->get()->result_array();
    }

    public function insert_and_get_id($table, $data)
    {
        $ok = $this->db->insert($table, $data);
        if (!$ok) return false;
        return $this->db->insert_id();
    }


    // Fungsi untuk insert data
    public function insert_data($table, $data)
    {
        return $this->db->insert($table, $data);
    }

    // Fungsi untuk insert data multiple (array of data)
    public function insert_multiple_data($table, $data)
    {
        return $this->db->insert_batch($table, $data);
    }

    // Fungsi untuk update data
    public function update_data($table, $data, $where)
    {
        return $this->db->update($table, $data, $where);
    }

    // Fungsi untuk delete data
    public function delete_data($table, $where)
    {
        return $this->db->delete($table, $where);
    }

    // Fungsi untuk mengambil semua data
    public function get_all_data($table)
    {
        return $this->db->get($table)->result_array();
    }

    // Fungsi untuk mengambil data berdasarkan kondisi tertentu
    public function get_row_where($table, $where)
    {
        return $this->db->get_where($table, $where)->row_array();
    }

    public function get_where($table, $where)
    {
        return $this->db->get_where($table, $where)->result_array();
    }


    // Fungsi untuk mengambil data berdasarkan kondisi tertentu (menggunakan LIKE)
    public function get_where_like($table, $where)
    {
        $this->db->like($where);
        return $this->db->get($table)->result_array();
    }

    // Fungsi untuk mengambil data berdasarkan limit
    public function get_data_limit($table, $limit, $offset = 0)
    {
        return $this->db->get($table, $limit, $offset)->result_array();
    }

    // Fungsi untuk mengambil total count data
    public function get_count($table)
    {
        return $this->db->count_all($table);
    }

    // Fungsi untuk mencari data berdasarkan field tertentu
    public function get_field_data($table, $field)
    {
        return $this->db->select($field)->get($table)->result_array();
    }

    // Fungsi untuk menjalankan query custom
    public function custom_query($query)
    {
        return $this->db->query($query)->result_array();
    }

    // Fungsi untuk menggunakan transaksi dalam operasi CRUD
    public function start_transaction()
    {
        $this->db->trans_start();
    }

    public function complete_transaction()
    {
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    // Fungsi untuk mendapatkan data berdasarkan join
    public function get_joined_data($table1, $table2, $on_condition, $where = [], $select_fields = '*', $join_type = 'INNER')
    {
        $this->db->select($select_fields);
        $this->db->from($table1);
        $this->db->join($table2, $on_condition, $join_type);
        if (!empty($where)) {
            $this->db->where($where);
        }
        return $this->db->get()->result_array();
    }

    // Fungsi untuk mendapatkan user berdasarkan username atau email
    public function get_user_by_email_or_username($username_or_email)
    {
        $this->db->where('email', $username_or_email);
        $this->db->or_where('username', $username_or_email);
        return $this->db->get('users')->row_array();
    }

    // Fungsi untuk memverifikasi password
    public function verify_password($password_input, $password_db)
    {
        return password_verify($password_input, $password_db); // Memverifikasi password hash
    }

    // Fungsi untuk memeriksa apakah pengguna aktif atau tidak
    public function is_user_active($user_id)
    {
        $this->db->where('id', $user_id);
        $this->db->where('is_active', 1);
        return $this->db->get('users')->row_array();
    }
}
