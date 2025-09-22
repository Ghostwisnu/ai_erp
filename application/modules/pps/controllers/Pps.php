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
        // Fetch all WO data
        $wo_data = $this->db->select('w.id AS wo_id, w.no_wo, b.name AS brand_name, w.art_color')
            ->from($this->wo_l1 . ' w')
            ->join('brands b', 'b.id = w.brand_id', 'left')
            ->get()->result_array();

        // Fetch checkin details for all WO and calculate the necessary values
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

        $data['wo_summary'] = $wo_summary;
        $this->_render('pps/index', $data);
    }

    /**
     * Calculate Finish Goods quantity based on the smallest quantity from each category.
     */
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
        $finish_goods_qty = min($valid_category_qty);

        return $finish_goods_qty;
    }

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
