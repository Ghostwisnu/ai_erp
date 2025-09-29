<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Dompdf\Dompdf;

class Defect extends MX_Controller
{
    private $table = 'data_defect';

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
        $sort_by = $this->input->get('sort_by', TRUE) ?? 'id_defect';
        $sort_order = $this->input->get('sort_order', TRUE) ?? 'asc';

        $per = 10;
        $offset = max(0, (int)$this->input->get('page'));

        if ($q !== '') {
            $this->db->like('nama_defect', $q);
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
            'title' => 'Data Defect',
            'q' => $q,
            'sort_by' => $sort_by,
            'sort_order' => $sort_order,
            'columns' => [
                ['key' => 'custom_id', 'label' => 'No'],
                ['key' => 'nama_defect', 'label' => 'Nama Defect'],
                ['key' => 'brand', 'label' => 'Brand'],
                ['key' => 'desc_database', 'label' => 'Deskripsi'],
                ['key' => 'created_at', 'label' => 'Created At'],
            ],
            'rows' => $custom_rows,
            'actions' => [
                ['label' => 'Edit', 'class' => 'warning', 'url' => 'defect/edit/{id_defect}'],
                ['label' => 'Delete', 'class' => 'danger', 'url' => 'defect/delete/{id_defect}', 'confirm' => 'Hapus data ini?'],
            ],
            'create_url' => site_url('defect/create'),
            'import_url' => site_url('defect/import_xlsx'),
            'export_xlsx_url' => site_url('defect/export_xlsx'),
            'export_pdf_url' => site_url('defect/export_pdf'),
            'pagination_links' => build_pagination($this, site_url('defect'), $total, $per),
        ];

        $this->_render('shared/list', $data);
    }

    public function download_template()
    {
        $ss = new Spreadsheet();
        $ws = $ss->getActiveSheet();
        $ws->fromArray([['Nama Defect', 'Brand', 'Deskripsi']], NULL, 'A1');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="defect_template.xlsx"');
        (new Xlsx($ss))->save('php://output');
        exit;
    }

    public function import_xlsx()
    {
        if (empty($_FILES['excel_file']['name'])) redirect('defect');

        $tmp = $_FILES['excel_file']['tmp_name'];
        $spread = IOFactory::load($tmp);
        $sheet = $spread->getActiveSheet()->toArray();

        $data = [];
        foreach ($sheet as $i => $r) {
            if ($i === 0) continue;
            if (empty($r[0])) continue;
            $data[] = [
                'nama_defect'   => $r[0],
                'brand'         => $r[1] ?? null,
                'desc_database' => $r[2] ?? null,
                'created_at'    => date('Y-m-d H:i:s'),
            ];
        }

        $this->session->set_userdata('import_preview', $data);
        redirect('defect/import_preview');
    }

    public function import_preview()
    {
        $preview = $this->session->userdata('import_preview');
        if (!$preview) redirect('defect');

        $data = [
            'title' => 'Preview Import Data Defect',
            'import_preview' => $preview,
            'import_preview_columns' => ['Nama Defect', 'Brand', 'Deskripsi'],
            'post_url' => site_url('defect/import_confirm'),
            'back_url' => site_url('defect'),
        ];

        $this->_render('shared/preview', $data);
    }

    public function import_confirm()
    {
        $data = $this->session->userdata('import_preview');
        if ($data) {
            $this->gm->insert_multiple_data($this->table, $data);
        }
        $this->session->unset_userdata('import_preview');
        $this->session->set_flashdata('message', 'Import data defect selesai.');
        redirect('defect');
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Defect',
            'post_url' => site_url('defect/store'),
            'back_url' => site_url('defect'),
            'fields' => [
                ['name' => 'nama_defect', 'label' => 'Nama Defect', 'type' => 'text', 'placeholder' => 'Nama defect', 'value' => set_value('nama_defect')],
                [
                    'name' => 'brand',
                    'label' => 'Brand',
                    'type' => 'select',
                    'options' => [
                        '' => '-- Pilih Brand --',
                        'ARIAT' => 'ARIAT',
                        'BLACKSTONE' => 'BLACKSTONE',
                        'ROSSI' => 'ROSSI',
                    ],
                    'value' => set_value('brand'),
                ],
                ['name' => 'desc_database', 'label' => 'Deskripsi', 'type' => 'text', 'value' => set_value('desc_database')],
            ],
        ];
        $this->_render('shared/form', $data);
    }

    public function edit($id)
    {
        $row = $this->gm->get_row_where($this->table, ['id_defect' => $id]);
        if (!$row) show_404();

        $data = [
            'title' => 'Edit Defect',
            'post_url' => site_url('defect/update/' . $id),
            'back_url' => site_url('defect'),
            'is_edit' => true,
            'fields' => [
                ['name' => 'nama_defect', 'label' => 'Nama Defect', 'type' => 'text', 'value' => set_value('nama_defect', $row['nama_defect'])],
                [
                    'name' => 'brand',
                    'label' => 'Brand',
                    'type' => 'select',
                    'options' => [
                        '' => '-- Pilih Brand --',
                        'ARIAT' => 'ARIAT',
                        'BLACKSTONE' => 'BLACKSTONE',
                        'ROSSI' => 'ROSSI',
                    ],
                    'value' => set_value('brand', $row['brand']),
                ],
                ['name' => 'desc_database', 'label' => 'Deskripsi', 'type' => 'text', 'value' => set_value('desc_database', $row['desc_database'])],
            ],
        ];
        $this->_render('shared/form', $data);
    }

    public function store()
    {
        $this->form_validation->set_rules('nama_defect', 'Nama Defect', 'required|trim|is_unique[data_defect.nama_defect]');
        $this->form_validation->set_rules('brand', 'Brand', 'required|trim');
        $this->form_validation->set_rules('desc_database', 'Deskripsi', 'required|trim');

        if (!$this->form_validation->run()) return $this->create();

        $this->gm->insert_data($this->table, [
            'nama_defect'   => $this->input->post('nama_defect', true),
            'brand'         => $this->input->post('brand', true),
            'desc_database' => $this->input->post('desc_database', true),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        $this->session->set_flashdata('message', 'Data defect berhasil ditambahkan.');
        redirect('defect');
    }

    public function update($id)
    {
        $row = $this->gm->get_row_where($this->table, ['id_defect' => $id]);
        if (!$row) show_404();

        $is_unique = ($row['nama_defect'] === $this->input->post('nama_defect')) ? '' : '|is_unique[data_defect.nama_defect]';
        $this->form_validation->set_rules('nama_defect', 'Nama Defect', 'required|trim' . $is_unique);
        $this->form_validation->set_rules('brand', 'Brand', 'required|trim');
        $this->form_validation->set_rules('desc_database', 'Deskripsi', 'required|trim');

        if (!$this->form_validation->run()) return $this->edit($id);

        $this->gm->update_data($this->table, [
            'nama_defect'   => $this->input->post('nama_defect', true),
            'brand'         => $this->input->post('brand', true),
            'desc_database' => $this->input->post('desc_database', true),
        ], ['id_defect' => $id]);

        $this->session->set_flashdata('message', 'Data defect berhasil diperbarui.');
        redirect('defect');
    }

    public function delete($id)
    {
        $this->gm->delete_data($this->table, ['id_defect' => $id]);
        $this->session->set_flashdata('message', 'Data defect berhasil dihapus.');
        redirect('defect');
    }

    public function export_xlsx()
    {
        $rows = $this->gm->get_all_data($this->table);
        $ss = new Spreadsheet();
        $ws = $ss->getActiveSheet();
        $ws->fromArray([['Nama Defect', 'Brand', 'Deskripsi', 'Created At']], NULL, 'A1');
        $i = 2;
        foreach ($rows as $r) {
            $ws->setCellValue("A$i", $r['nama_defect']);
            $ws->setCellValue("B$i", $r['brand']);
            $ws->setCellValue("C$i", $r['desc_database']);
            $ws->setCellValue("D$i", $r['created_at']);
            $i++;
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="data_defect.xlsx"');
        (new Xlsx($ss))->save('php://output');
        exit;
    }

    public function export_pdf()
    {
        $rows = $this->gm->get_all_data($this->table);
        $html = '<h3>Data Defect</h3><table border="1" cellspacing="0" cellpadding="6"><tr><th>Nama Defect</th><th>Brand</th><th>Deskripsi</th><th>Created At</th></tr>';
        foreach ($rows as $r) {
            $html .= '<tr><td>' . html_escape($r['nama_defect']) . '</td><td>' . html_escape($r['brand']) . '</td><td>' . html_escape($r['desc_database']) . '</td><td>' . $r['created_at'] . '</td></tr>';
        }
        $html .= '</table>';
        $pdf = new Dompdf();
        $pdf->loadHtml($html);
        $pdf->render();
        $pdf->stream('data_defect.pdf', ['Attachment' => 1]);
        exit;
    }

    private function _render($view, $data)
    {
        $this->load->view('templates/header', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view($view, $data);
        $this->load->view('templates/footer');
    }
}
