<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

class Daily extends MX_Controller
{
    private $table = 'ariat_defect_production';

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

    /** ========== LIST DATA ========== */
    public function index()
    {
        $q          = trim($this->input->get('q'));
        $sort_by    = $this->input->get('sort_by', TRUE) ?? 'id_df';
        $sort_order = $this->input->get('sort_order', TRUE) ?? 'desc';
        $per        = 10;
        $offset     = max(0, (int)$this->input->get('page'));

        if ($q !== '') {
            $this->db->group_start()
                ->like('po_number', $q)
                ->or_like('brand', $q)
                ->or_like('dept_name', $q)
                ->group_end();
        }

        $total = $this->db->count_all_results($this->table, FALSE);
        $this->db->order_by($sort_by, $sort_order)->limit($per, $offset);
        $rows = $this->db->get()->result_array();

        $custom_rows = [];
        $counter     = 1;
        foreach ($rows as $row) {
            $row['custom_id'] = $counter++;
            $custom_rows[]    = $row;
        }

        $data = [
            'title'           => 'Ariat Defect Production (Daily)',
            'q'               => $q,
            'sort_by'         => $sort_by,
            'sort_order'      => $sort_order,
            'columns'         => [
                ['key' => 'custom_id', 'label' => 'No'],
                ['key' => 'po_number', 'label' => 'PO Number'],
                ['key' => 'brand', 'label' => 'Brand'],
                ['key' => 'artcolor_name', 'label' => 'Art Color'],
                ['key' => 'dept_name', 'label' => 'Departemen'],
                ['key' => 'total_qty', 'label' => 'Total Qty'],
                ['key' => 'tgl_input', 'label' => 'Tanggal Input'],
            ],
            'rows'            => $custom_rows,
            'actions'         => [
                ['label' => 'View', 'class' => 'primary', 'url' => 'defect_daily/detail/{id_df}'],
                ['label' => 'Delete', 'class' => 'danger', 'url' => 'daily/delete/{id_df}', 'confirm' => 'Hapus data ini?'],
            ],
            'create_url'      => site_url('daily/create'),
            'import_url'      => site_url('daily/import_xlsx'),
            'export_xlsx_url' => site_url('daily/export_xlsx'),
            'export_pdf_url'  => site_url('daily/export_pdf'),
            'pagination_links'=> build_pagination($this, site_url('daily'), $total, $per),
        ];

        $this->_render('daily/list', $data);
    }

    /** ========== CREATE ========== */
    public function create()
    {
        $spk = $this->gm->get_all_data('defect_spk');

        // generate auto no_dfariat
        $prefix = 'DF-' . date('Ymd');
        $last   = $this->db->like('no_dfariat', $prefix, 'after')
            ->order_by('id_df', 'desc')
            ->get($this->table)
            ->row_array();

        $lastNum = 1;
        if ($last) {
            $parts   = explode('-', $last['no_dfariat']);
            $lastNum = isset($parts[2]) ? (int)$parts[2] + 1 : 1;
        }
        $newDfNo = $prefix . '-' . str_pad($lastNum, 3, '0', STR_PAD_LEFT);

        $data = [
            'title'    => 'Tambah Daily Defect Production',
            'post_url' => site_url('daily/store'),
            'back_url' => site_url('daily'),
            'spk'      => $spk,
            'newDfNo'  => $newDfNo, // langsung dipakai di view
        ];

        $this->_render('daily/form', $data);
    }

    /** ========== STORE ========== */
    public function store()
    {
        $this->form_validation->set_rules('po_number', 'PO Number', 'required');
        if (!$this->form_validation->run()) return $this->create();

        $spk = $this->gm->get_row_where('defect_spk', ['po_number' => $this->input->post('po_number')]);
        if (!$spk) {
            $this->session->set_flashdata('message', 'SPK tidak ditemukan!');
            redirect('daily/create');
            return;
        }

        $this->gm->insert_data($this->table, [
            'id_spk'        => $spk['id_spk'],
            'po_number'     => $spk['po_number'],
            'brand'         => $spk['brand'],
            'artcolor_name' => $spk['artcolor_name'],
            'total_qty'     => $spk['total_qty'],
            'no_dfariat'    => $this->input->post('no_dfariat', true),
            'tgl_input'     => $this->input->post('tgl_input', true),
            'dept_name'     => $this->input->post('dept_name', true),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        $this->session->set_flashdata('message', 'Data berhasil ditambahkan.');
        redirect('daily');
    }

    /** ========== GET SPK INFO (AJAX) ========== */
    public function get_spk_info()
    {
        $po_number = $this->input->post('po_number');
        $row = $this->gm->get_row_where('defect_spk', ['po_number' => $po_number]);

        if ($row) {
            echo json_encode([
                'po_number'     => $row['po_number'],
                'artcolor_name' => $row['artcolor_name'],
                'brand'         => $row['brand'],
                'total_qty'     => $row['total_qty'],
            ]);
        } else {
            echo json_encode(null);
        }
        exit;
    }

    /** ... bagian edit, update, delete, export_xlsx, export_pdf tetap sama ... */

    /** ========== RENDER ========== */
    private function _render($view, $data)
    {
        $this->load->view('templates/header', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view($view, $data); // view di folder daily/
        $this->load->view('templates/footer');
    }
}
