<?php defined('BASEPATH') or exit('No direct script access allowed');

class Stock extends MX_Controller
{
    private $items_table  = 'items';         // id, item_code, item_name, item_type=FG|SFG|RAW, unit_id, brand_id
    private $units_table  = 'units';         // id, name
    private $brands_table = 'brands';        // id, name
    private $checkin_hdr  = 'checkin_hdr';   // checkin header table (STR)
    private $checkin_det  = 'checkin_det';   // checkin details per WO and material
    private $checkin_det_sizes = 'checkin_det_sizes'; // breakdown by size

    public function __construct()
    {
        parent::__construct();
        $this->_check_login();
        $this->load->model('Generic_model', 'gm');
    }

    private function _check_login()
    {
        if (!$this->session->userdata('logged_in')) redirect('login');
    }

    // application/modules/stock/controllers/Stock.php

    public function index()
    {
        $data['title'] = 'Stok Items (FG / SFG / RAW)';

        // GET query & active tab (optional)
        $q = trim((string)$this->input->get('q', true));
        $active_tab = strtoupper((string)$this->input->get('tab', true));
        if (!in_array($active_tab, ['FG', 'SFG', 'RAW'], true)) {
            $active_tab = 'FG';
        }
        $data['q'] = $q;
        $data['active_tab'] = $active_tab;

        // Base query for items
        $this->db->select('i.id, i.item_code, i.item_name, i.item_type, b.name AS brand_name, u.name AS unit_name');
        $this->db->from($this->items_table . ' i');
        $this->db->join($this->brands_table . ' b', 'b.id = i.brand_id', 'left');
        $this->db->join($this->units_table  . ' u', 'u.id = i.unit_id', 'left');

        // Search (LIKE) if query is set
        if ($q !== '') {
            $this->db->group_start();
            $this->db->like('i.item_code', $q);
            $this->db->or_like('i.item_name', $q);
            $this->db->or_like('b.name', $q);
            $this->db->or_like('u.name', $q);
            $this->db->group_end();
        }

        $this->db->order_by('i.item_name', 'ASC');
        $rows = $this->db->get()->result_array();

        // Split by tab: FG, SFG, RAW
        $data['items_fg'] = $data['items_sfg'] = $data['items_raw'] = [];
        foreach ($rows as $r) {
            $t = strtoupper($r['item_type'] ?? '');
            if ($t === 'FG')  $data['items_fg'][]  = $r;
            elseif ($t === 'SFG') $data['items_sfg'][] = $r;
            elseif ($t === 'RAW') $data['items_raw'][] = $r;
        }

        // Count per tab (optional for badges)
        $data['count_fg']  = count($data['items_fg']);
        $data['count_sfg'] = count($data['items_sfg']);
        $data['count_raw'] = count($data['items_raw']);

        $this->_render('stock/list', $data);
    }

    /**
     * ** Summary of stock (used to show check-in vs checkout)
     * Fetch summary data per item
     */
    public function ajax_item_stock_summary($item_id)
    {
        if (!ctype_digit((string)$item_id)) {
            echo json_encode(['ok' => false, 'rows' => []]);
            return;
        }

        // Total per WO untuk 1 item: total_in, total_out, dan total_stock
        $rows = $this->db->select("
            c.wo_l1_id,
            ch.no_wo,
            COALESCE(SUM(c.qty_in), 0)  AS total_in,
            COALESCE(SUM(c.qty_out), 0) AS total_out,
            COALESCE(SUM(c.qty_in), 0) - COALESCE(SUM(c.qty_out), 0) AS total_stock
        ", false)
            ->from($this->checkin_det . ' c')
            ->join($this->checkin_hdr . ' ch', 'ch.id = c.hdr_id', 'left')
            ->where('c.item_id', (int)$item_id)
            ->group_by('c.wo_l1_id, ch.no_wo')
            ->order_by('c.wo_l1_id', 'ASC')
            ->get()->result_array();

        echo json_encode(['ok' => true, 'rows' => $rows]);
    }

    /**
     * Detail all STR data for 1 item (group per STR/no_str)
     * Output JSON: [{no_str, arrival_date, wo_l1_id, qty_in, created_at},...]
     */
    public function ajax_item_checkin_detail($item_id)
    {
        if (!ctype_digit((string)$item_id)) {
            echo json_encode(['ok' => false, 'rows' => []]);
            return;
        }

        // Ambil no_wo dari HEADER (checkin_hdr.no_wo), total qty_in & qty_out per STR
        $rows = $this->db->select('
            ch.no_str,
            ch.arrival_date,
            ch.no_wo,          -- << WO Number
            c.wo_l1_id,        -- (opsional, jika masih mau dipakai)
            SUM(c.qty_in)  AS qty_in,
            SUM(c.qty_out) AS qty_out,   -- << tambahkan agregat qty_out
            MIN(c.created_at) AS created_at
        ', false)
            ->from($this->checkin_det . ' c')
            ->join($this->checkin_hdr . ' ch', 'ch.id = c.hdr_id', 'left')
            ->where('c.item_id', (int)$item_id)
            ->group_by(['ch.no_str', 'ch.arrival_date', 'ch.no_wo', 'c.wo_l1_id'])
            ->order_by('ch.arrival_date', 'DESC')
            ->order_by('ch.no_str', 'DESC')
            ->get()->result_array();

        echo json_encode(['ok' => true, 'rows' => $rows]);
    }

    private function _render($view, $data)
    {
        $data['user']    = $this->session->userdata('username');
        $data['role_id'] = $this->session->userdata('role_id');
        $this->load->view('templates/header', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view($view, $data);
        $this->load->view('templates/footer');
    }
}
