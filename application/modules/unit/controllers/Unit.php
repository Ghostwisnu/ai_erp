<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Dompdf\Dompdf;

class Unit extends MX_Controller
{
    private $table = 'units';

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
        // Ambil data query dari URL atau set default
        $q = trim($this->input->get('q'));
        $sort_by = $this->input->get('sort_by', TRUE) ?? 'id';  // Default sort by 'id'
        $sort_order = $this->input->get('sort_order', TRUE) ?? 'asc';  // Default ascending order

        $per = 10;
        $offset = max(0, (int)$this->input->get('page'));

        // Apply search filter
        if ($q !== '') {
            $this->db->like('name', $q);
        }

        // Get the total number of rows (for pagination)
        $total = $this->db->count_all_results($this->table, FALSE);

        // Order data based on the sorting parameters passed from the view
        $this->db->order_by($sort_by, $sort_order)->limit($per, $offset);
        $rows = $this->db->get()->result_array();

        // Custom ID array
        $custom_rows = [];
        $counter = 1;
        foreach ($rows as $row) {
            $row['custom_id'] = $counter++; // Set custom ID starting from 1
            $custom_rows[] = $row;
        }

        // Prepare data for the view
        $data = [
            'title' => 'Unit',
            'q' => $q,
            'sort_by' => $sort_by,
            'sort_order' => $sort_order,
            'columns' => [
                ['key' => 'custom_id', 'label' => 'Custom ID'],
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'description', 'label' => 'Description'],
            ],
            'rows' => $custom_rows,
            'actions' => [
                ['label' => 'Edit', 'class' => 'warning', 'url' => 'unit/edit/{id}'],
                ['label' => 'Delete', 'class' => 'danger', 'url' => 'unit/delete/{id}', 'confirm' => 'Hapus data ini?'],
            ],
            'create_url' => site_url('unit/create'),
            'import_url' => site_url('unit/import_xlsx'),
            'export_xlsx_url' => site_url('unit/export_xlsx'),
            'export_pdf_url' => site_url('unit/export_pdf'),
            'pagination_links' => build_pagination($this, site_url('unit'), $total, $per),
        ];

        // Render the view
        $this->_render('shared/list', $data);
    }

    public function download_template()
    {
        $ss = new Spreadsheet();
        $ws = $ss->getActiveSheet();
        $ws->fromArray([['Name', 'Description']], NULL, 'A1');  // Template untuk Unit

        // Menyiapkan header untuk Excel
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="unit_template.xlsx"');

        (new Xlsx($ss))->save('php://output');
        exit;
    }


    public function create()
    {
        $data = [
            'title' => 'Tambah Unit',
            'post_url' => site_url('unit/store'),
            'back_url' => site_url('unit'),
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'placeholder' => 'Unit name'],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            ],
        ];
        $this->_render('shared/form', $data);
    }
    public function store()
    {
        $this->form_validation->set_rules('name', 'Name', 'required|trim|is_unique[units.name]');
        if (!$this->form_validation->run()) return $this->create();
        $this->gm->insert_data($this->table, ['name' => $this->input->post('name', true), 'description' => $this->input->post('description', true)]);
        $this->session->set_flashdata('message', 'Unit ditambahkan.');
        redirect('unit');
    }
    public function edit($id)
    {
        $row = $this->gm->get_row_where($this->table, ['id' => $id]);
        if (!$row) show_404();
        $data = [
            'title' => 'Edit Unit',
            'post_url' => site_url('unit/update/' . $id),
            'back_url' => site_url('unit'),
            'is_edit' => true,
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'value' => $row['name']],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'value' => $row['description']],
            ],
        ];
        $this->_render('shared/form', $data);
    }
    public function update($id)
    {
        $row = $this->gm->get_row_where($this->table, ['id' => $id]);
        if (!$row) show_404();
        $is_unique = ($row['name'] === $this->input->post('name')) ? '' : '|is_unique[units.name]';
        $this->form_validation->set_rules('name', 'Name', 'required|trim' . $is_unique);
        if (!$this->form_validation->run()) return $this->edit($id);
        $this->gm->update_data($this->table, ['name' => $this->input->post('name', true), 'description' => $this->input->post('description', true)], ['id' => $id]);
        $this->session->set_flashdata('message', 'Unit diperbarui.');
        redirect('unit');
    }
    public function delete($id)
    {
        $this->gm->delete_data($this->table, ['id' => $id]);
        $this->session->set_flashdata('message', 'Unit dihapus.');
        redirect('unit');
    }

    public function import_xlsx()
    {
        if (empty($_FILES['excel_file']['name'])) {
            redirect('unit');
        }

        // Read the uploaded Excel file
        $sheet = IOFactory::load($_FILES['excel_file']['tmp_name'])->getActiveSheet()->toArray();
        $preview = [];

        foreach ($sheet as $i => $r) {
            if ($i === 0) continue; // Skip the header row
            if (empty($r[0])) continue;  // Skip empty rows

            // Prepare the preview data
            $preview[] = [
                'name' => $r[0],
                'description' => $r[1] ?? null,
            ];
        }

        // Store the preview data in session for later display
        $this->session->set_userdata('import_preview', $preview);

        // Redirect to the preview page
        redirect('unit/import_preview');
    }

    public function import_preview()
    {
        // Get the preview data from session
        $preview = $this->session->userdata('import_preview');

        // If no preview data exists, redirect to the list page
        if (!$preview) {
            redirect('unit');
        }

        // Prepare data for the preview page
        $data = [
            'title' => 'Preview Import Data',
            'import_preview' => $preview,
            'import_preview_columns' => ['Name', 'Description'],
            'post_url' => site_url('unit/import_confirm'), // URL to confirm import
            'back_url' => site_url('unit'), // Go back to unit list page
        ];

        // Render the preview view
        $this->_render('shared/preview', $data);
    }

    public function import_confirm()
    {
        // Fetch preview data from session
        $preview = $this->session->userdata('import_preview');

        // If no preview data exists, redirect to the list page
        if (!$preview) {
            redirect('unit');
        }

        // Insert the data into the database
        $data = [];
        foreach ($preview as $item) {
            $data[] = [
                'name' => $item['name'],
                'description' => $item['description'],
            ];
        }

        if ($data) {
            $this->gm->insert_multiple_data($this->table, $data);
        }

        // Clear preview data after import
        $this->session->unset_userdata('import_preview');

        // Set success message and redirect
        $this->session->set_flashdata('message', 'Import selesai.');
        redirect('unit');
    }

    public function export_xlsx()
    {
        $rows = $this->gm->get_all_data($this->table);
        $ss = new Spreadsheet();
        $ws = $ss->getActiveSheet();
        $ws->fromArray([['Name', 'Description']], NULL, 'A1');
        $i = 2;
        foreach ($rows as $r) {
            $ws->fromArray([[$r['name'], $r['description']]], NULL, "A$i");
            $i++;
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="units.xlsx"');
        (new Xlsx($ss))->save('php://output');
        exit;
    }
    public function export_pdf()
    {
        $rows = $this->gm->get_all_data($this->table);
        $html = '<h3>Units</h3><table border="1" cellspacing="0" cellpadding="6"><tr><th>Name</th><th>Description</th></tr>';
        foreach ($rows as $r) {
            $html .= '<tr><td>' . html_escape($r['name']) . '</td><td>' . html_escape($r['description']) . '</td></tr>';
        }
        $html .= '</table>';
        $pdf = new Dompdf();
        $pdf->loadHtml($html);
        $pdf->render();
        $pdf->stream('units.pdf', ['Attachment' => 1]);
        exit;
    }

    private function _render($v, $d)
    {
        $this->load->view('templates/header', $d);
        $this->load->view('templates/topbar', $d);
        $this->load->view('templates/sidebar', $d);
        $this->load->view($v, $d);
        $this->load->view('templates/footer');
    }
}
