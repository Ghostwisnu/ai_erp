<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Dompdf\Dompdf;

class Spk_defect extends MX_Controller
{
    private $table = 'defect_spk';

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

    public function index()
    {
        $q = trim($this->input->get('q'));
        $sort_by = $this->input->get('sort_by', TRUE) ?? 'id_spk';
        $sort_order = $this->input->get('sort_order', TRUE) ?? 'asc';

        $per = 10;
        $offset = max(0, (int)$this->input->get('page'));

        if ($q !== '') {
            $this->db->group_start()
                ->like('po_number', $q)
                ->or_like('brand', $q)
                ->or_like('artcolor_name', $q)
                ->group_end();
        }

        $total = $this->db->count_all_results($this->table, FALSE);

        $this->db->order_by($sort_by, $sort_order)->limit($per, $offset);
        $rows = $this->db->get()->result_array();

        $custom_rows = [];
        $counter = 1;
        foreach ($rows as $row) {
            $row['custom_id'] = $counter++;
            $custom_rows[] = $row;
        }

        $data = [
            'title' => 'SPK Defect',
            'q' => $q,
            'sort_by' => $sort_by,
            'sort_order' => $sort_order,
            'columns' => [
                ['key' => 'custom_id', 'label' => 'No'],
                ['key' => 'po_number', 'label' => 'PO Number'],
                ['key' => 'xfd', 'label' => 'XFD'],
                ['key' => 'brand', 'label' => 'Brand'],
                ['key' => 'artcolor_name', 'label' => 'Art/Color'],
                ['key' => 'total_qty', 'label' => 'Total Qty'],
            ],
            'rows' => $custom_rows,
            'actions' => [
                ['label' => 'Edit', 'class' => 'warning', 'url' => 'spk_defect/edit/{id_spk}'],
                ['label' => 'Delete', 'class' => 'danger', 'url' => 'spk_defect/delete/{id_spk}', 'confirm' => 'Hapus data ini?'],
            ],
            'create_url' => site_url('spk_defect/create'),
            'import_url' => site_url('spk_defect/import_xlsx'),
            'export_xlsx_url' => site_url('spk_defect/export_xlsx'),
            'export_pdf_url' => site_url('spk_defect/export_pdf'),
            'pagination_links' => build_pagination($this, site_url('spk_defect'), $total, $per),
        ];

        $this->_render('shared/list', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah SPK Defect',
            'post_url' => site_url('spk_defect/store'),
            'back_url' => site_url('spk_defect'),
            'fields' => [
                ['name' => 'po_number', 'label' => 'PO Number', 'type' => 'text'],
                ['name' => 'xfd', 'label' => 'XFD', 'type' => 'date'],
                [
                    'name' => 'brand',
                    'label' => 'Brand',
                    'type' => 'select',
                    'options' => [
                        'ARIAT'      => 'ARIAT',
                        'BLACKSTONE' => 'BLACKSTONE',
                        'ROSSI'      => 'ROSSI',
                    ]
                ],
                ['name' => 'artcolor_name', 'label' => 'Art/Color', 'type' => 'text'],
                ['name' => 'total_qty', 'label' => 'Total Qty', 'type' => 'number'],
            ],
        ];
        $this->_render('shared/form', $data);
    }

    public function edit($id)
    {
        $row = $this->gm->get_row_where($this->table, ['id_spk' => $id]);
        if (!$row) show_404();

        $data = [
            'title' => 'Edit SPK Defect',
            'post_url' => site_url('spk_defect/update/' . $id),
            'back_url' => site_url('spk_defect'),
            'is_edit' => true,
            'fields' => [
                ['name' => 'po_number', 'label' => 'PO Number', 'type' => 'text', 'value' => set_value('po_number', $row['po_number'])],
                ['name' => 'xfd', 'label' => 'XFD', 'type' => 'date', 'value' => set_value('xfd', $row['xfd'])],
                [
                    'name' => 'brand',
                    'label' => 'Brand',
                    'type' => 'select',
                    'options' => [
                        'ARIAT'      => 'ARIAT',
                        'BLACKSTONE' => 'BLACKSTONE',
                        'ROSSI'      => 'ROSSI',
                    ],
                    'value' => set_value('brand', $row['brand'])
                ],
                ['name' => 'artcolor_name', 'label' => 'Art/Color', 'type' => 'text', 'value' => set_value('artcolor_name', $row['artcolor_name'])],
                ['name' => 'total_qty', 'label' => 'Total Qty', 'type' => 'number', 'value' => set_value('total_qty', $row['total_qty'])],
            ],
        ];
        $this->_render('shared/form', $data);
    }

    public function store()
    {
        $this->form_validation->set_rules('po_number', 'PO Number', 'required|trim');
        $this->form_validation->set_rules('xfd', 'XFD', 'required');
        $this->form_validation->set_rules('brand', 'Brand', 'required|trim');
        if (!$this->form_validation->run()) return $this->create();

        $this->gm->insert_data($this->table, [
            'po_number'     => $this->input->post('po_number', true),
            'xfd'           => $this->input->post('xfd', true),
            'brand'         => $this->input->post('brand', true),
            'artcolor_name' => $this->input->post('artcolor_name', true),
            'total_qty'     => $this->input->post('total_qty', true),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
        $this->session->set_flashdata('message', 'Data SPK Defect ditambahkan.');
        redirect('spk_defect');
    }

    public function update($id)
    {
        $row = $this->gm->get_row_where($this->table, ['id_spk' => $id]);
        if (!$row) show_404();

        $this->form_validation->set_rules('po_number', 'PO Number', 'required|trim');
        $this->form_validation->set_rules('xfd', 'XFD', 'required');
        $this->form_validation->set_rules('brand', 'Brand', 'required|trim');
        if (!$this->form_validation->run()) return $this->edit($id);

        $this->gm->update_data($this->table, [
            'po_number'     => $this->input->post('po_number', true),
            'xfd'           => $this->input->post('xfd', true),
            'brand'         => $this->input->post('brand', true),
            'artcolor_name' => $this->input->post('artcolor_name', true),
            'total_qty'     => $this->input->post('total_qty', true),
        ], ['id_spk' => $id]);

        $this->session->set_flashdata('message', 'Data SPK Defect diperbarui.');
        redirect('spk_defect');
    }

    public function delete($id)
    {
        $this->gm->delete_data($this->table, ['id_spk' => $id]);
        $this->session->set_flashdata('message', 'Data SPK Defect dihapus.');
        redirect('spk_defect');
    }

    // fungsi import/export tetap sama, hanya hapus id_spk di insert jika mau otomatis
    // ...
    
    private function _render($view, $data)
    {
        $this->load->view('templates/header', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view($view, $data);
        $this->load->view('templates/footer');
    }
}
