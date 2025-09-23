<?php defined('BASEPATH') or exit('No direct script access allowed');

class Pps extends MX_Controller
{
    private $wo_l1 = 'wo_l1'; // Table for WO level 1
    private $items = 'items'; // Table for items
    private $checkin_det = 'checkin_det'; // Table for checkin details

    public function __construct()
    {
        parent::__construct();
        $this->_check_login();
        $this->load->model('Generic_model', 'gm');
    }

    private function _check_login()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
    }

    public function index()
    {
        $data['title'] = 'Production Summary';

        // Fetch search keyword from GET request
        $search = $this->input->get('search', TRUE);

        // Pagination Configuration
        $config['base_url'] = site_url('pps/index');
        $config['total_rows'] = $this->_get_search_count($search);
        $config['per_page'] = 10;  // Set number of records per page
        $config['uri_segment'] = 3;
        $this->pagination->initialize($config);

        // Get data for current page
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $data['wo_summary'] = $this->_get_search_results($search, $config['per_page'], $page);

        // Pass pagination links to the view
        $data['pagination'] = $this->pagination->create_links();

        $this->_render('pps/index', $data);
    }

    // Method to get data based on search keyword and pagination
    private function _get_search_results($search = '', $limit = 10, $start = 0)
    {
        $this->db->select('w.id AS wo_id, w.no_wo, b.name AS brand_name, w.art_color');
        $this->db->from($this->wo_l1 . ' w');
        $this->db->join('brands b', 'b.id = w.brand_id', 'left');

        if ($search) {
            $this->db->like('w.no_wo', $search);
            $this->db->or_like('b.name', $search);
            $this->db->or_like('w.art_color', $search);
        }

        $this->db->limit($limit, $start);
        $wo_data = $this->db->get()->result_array();

        // Fetch check-in details for all WO and calculate the necessary values
        $wo_summary = [];
        foreach ($wo_data as $wo) {
            $wo_id = $wo['wo_id'];

            // Get check-in details for the WO and calculate qty_in only
            $checkin_data = $this->db->select('cd.wo_l1_id, cd.item_id, SUM(cd.qty_in) AS qty_in')
                ->from($this->checkin_det . ' cd')
                ->where('cd.wo_l1_id', $wo_id)
                ->group_by('cd.wo_l1_id, cd.item_id')
                ->get()->result_array();

            // Initialize quantities for different categories based on item names (Cutting, Sewing, etc.)
            $category_qty = [
                'cutting' => 0,
                'sewing' => 0,
                'semi' => 0,
                'lasting' => 0,
                'finishing' => 0,
                'packaging' => 0,
            ];

            foreach ($checkin_data as $item) {
                $item_name = $this->gm->get_row_where($this->items, ['id' => $item['item_id']])['item_name'];

                // Calculate qty for each category based on item name
                if (strpos(strtolower($item_name), 'cutting') !== false) {
                    $category_qty['cutting'] += $item['qty_in'];  // Use qty_in only
                } elseif (strpos(strtolower($item_name), 'sewing') !== false) {
                    $category_qty['sewing'] += $item['qty_in'];  // Use qty_in only
                } elseif (strpos(strtolower($item_name), 'semi') !== false) {
                    $category_qty['semi'] += $item['qty_in'];  // Use qty_in only
                } elseif (strpos(strtolower($item_name), 'lasting') !== false) {
                    $category_qty['lasting'] += $item['qty_in'];  // Use qty_in only
                } elseif (strpos(strtolower($item_name), 'finishing') !== false) {
                    $category_qty['finishing'] += $item['qty_in'];  // Use qty_in only
                } elseif (strpos(strtolower($item_name), 'packaging') !== false) {
                    $category_qty['packaging'] += $item['qty_in'];  // Use qty_in only
                }
            }

            // Calculate Finish Goods based on the smallest quantity from each category
            $finish_goods_qty = $this->calculate_finish_goods_qty($category_qty);

            $wo_summary[] = [
                'wo_id' => $wo_id,
                'no_wo' => $wo['no_wo'],
                'brand_name' => $wo['brand_name'],
                'art_color' => $wo['art_color'],
                'cutting' => $category_qty['cutting'],
                'sewing' => $category_qty['sewing'],
                'semi' => $category_qty['semi'],
                'lasting' => $category_qty['lasting'],
                'finishing' => $category_qty['finishing'],
                'packaging' => $category_qty['packaging'],
                'finish_goods' => $finish_goods_qty
            ];
        }

        return $wo_summary;
    }

    // Method to get the count of records based on search
    private function _get_search_count($search = '')
    {
        $this->db->select('w.id');
        $this->db->from($this->wo_l1 . ' w');
        $this->db->join('brands b', 'b.id = w.brand_id', 'left');

        if ($search) {
            $this->db->like('w.no_wo', $search);
            $this->db->or_like('b.name', $search);
            $this->db->or_like('w.art_color', $search);
        }

        return $this->db->count_all_results();
    }

    // Method to calculate Finish Goods quantity based on the smallest quantity from each category
    private function calculate_finish_goods_qty($category_qty)
    {
        // Get the total qty for each category, but only if it's greater than 0
        $valid_category_qty = array_filter($category_qty, function ($qty) {
            return $qty > 0;
        });

        // If any category is missing (zero or not set), we can't make Finish Goods
        if (count($valid_category_qty) < count($category_qty)) {
            return 0;
        }

        // The finish goods qty is the minimum qty from all categories (Cutting to Packaging)
        return min($valid_category_qty);
    }

    // Helper render method
    private function _render($view, $data)
    {
        $data['user'] = $this->session->userdata('username');
        $data['role_id'] = $this->session->userdata('role_id');
        $this->load->view('templates/header', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view($view, $data);
        $this->load->view('templates/footer');
    }
}
