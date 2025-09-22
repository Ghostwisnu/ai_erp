<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Dompdf\Dompdf;

class Category extends MX_Controller
{
    private $table = 'categories';

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
            'title' => 'Category',
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
                ['label' => 'Edit', 'class' => 'warning', 'url' => 'category/edit/{id}'],
                ['label' => 'Delete', 'class' => 'danger', 'url' => 'category/delete/{id}', 'confirm' => 'Hapus data ini?'],
            ],
            'create_url' => site_url('category/create'),
            'import_url' => site_url('category/import_xlsx'),
            'export_xlsx_url' => site_url('category/export_xlsx'),
            'export_pdf_url' => site_url('category/export_pdf'),
            'pagination_links' => build_pagination($this, site_url('category'), $total, $per),
        ];

        // Render the view
        $this->_render('shared/list', $data);
    }


    public function download_template()
    {
        $ss = new Spreadsheet();
        $ws = $ss->getActiveSheet();
        $ws->fromArray([['Name', 'Description']], NULL, 'A1');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="category_template.xlsx"');
        (new Xlsx($ss))->save('php://output');
        exit;
    }

    public function import_xlsx()
    {
        if (empty($_FILES['excel_file']['name'])) redirect('category');

        $tmp = $_FILES['excel_file']['tmp_name'];
        $spread = IOFactory::load($tmp);
        $sheet = $spread->getActiveSheet()->toArray();
        $data = [];
        foreach ($sheet as $i => $r) {
            if ($i === 0) continue; // header
            if (empty($r[0])) continue;
            $data[] = ['name' => $r[0], 'description' => $r[1] ?? null, 'code' => $this->_generateCategoryCode($r[0]),];
        }

        // Store preview data in session
        $this->session->set_userdata('import_preview', $data);

        // Redirect to preview
        redirect('category/import_preview');
    }

    public function import_preview()
    {
        // Get the preview data from session
        $preview = $this->session->userdata('import_preview');
        if (!$preview) {
            // Jika preview kosong, alihkan ke halaman kategori atau beri pesan error
            redirect('category');
        }

        // Prepare data for the preview page
        $data = [
            'title' => 'Preview Import Data',
            'import_preview' => $preview,
            'import_preview_columns' => ['Name', 'Description'], // Pastikan ini berisi kolom yang sesuai
            'post_url' => site_url('category/import_confirm'), // URL untuk mengonfirmasi import
            'back_url' => site_url('category'), // URL kembali ke halaman utama
        ];

        // Render the preview view
        $this->_render('shared/preview', $data);
    }


    public function import_confirm()
    {
        $data = $this->session->userdata('import_preview');
        if ($data) {
            $this->gm->insert_multiple_data($this->table, $data);
        }

        // Clear preview data after import
        $this->session->unset_userdata('import_preview');

        $this->session->set_flashdata('message', 'Import selesai.');
        redirect('category');
    }


    public function create()
    {
        $data = [
            'title' => 'Tambah Category',
            'post_url' => site_url('category/store'),
            'back_url' => site_url('category'),
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'placeholder' => 'Category name'],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            ],
        ];
        $this->_render('shared/form', $data);
    }

    public function edit($id)
    {
        $row = $this->gm->get_row_where($this->table, ['id' => $id]);
        if (!$row) show_404();
        $data = [
            'title' => 'Edit Category',
            'post_url' => site_url('category/update/' . $id),
            'back_url' => site_url('category'),
            'is_edit' => true,
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'value' => $row['name']],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'value' => $row['description']],
            ],
        ];
        $this->_render('shared/form', $data);
    }

    public function store()
    {
        $this->form_validation->set_rules('name', 'Name', 'required|trim|is_unique[categories.name]');
        if (!$this->form_validation->run()) return $this->create();

        // Generate a code for the new category
        $code = $this->_generateCategoryCode($this->input->post('name', true));

        $this->gm->insert_data($this->table, [
            'name' => $this->input->post('name', true),
            'description' => $this->input->post('description', true),
            'code' => $code,  // Insert the generated code
        ]);
        $this->session->set_flashdata('message', 'Category ditambahkan.');
        redirect('category');
    }

    public function update($id)
    {
        $row = $this->gm->get_row_where($this->table, ['id' => $id]);
        if (!$row) show_404();

        $is_unique = ($row['name'] === $this->input->post('name')) ? '' : '|is_unique[categories.name]';
        $this->form_validation->set_rules('name', 'Name', 'required|trim' . $is_unique);
        if (!$this->form_validation->run()) return $this->edit($id);

        // Generate or update code
        $code = $this->_generateCategoryCode($this->input->post('name', true));

        $this->gm->update_data($this->table, [
            'name' => $this->input->post('name', true),
            'description' => $this->input->post('description', true),
            'code' => $code,  // Update the code
        ], ['id' => $id]);

        $this->session->set_flashdata('message', 'Category diperbarui.');
        redirect('category');
    }

    // Helper function to generate category code
    private function _generateCategoryCode($categoryName)
    {
        // Split the category name into words
        $words = explode(' ', $categoryName);

        // Extract the first letter of each word and convert it to uppercase
        $shortcode = '';
        foreach ($words as $word) {
            $shortcode .= strtoupper($word[0]);  // Take the first letter of each word
        }

        // Example: Code format: "CAT-CategoryName-ABC"
        $code = strtoupper('CAT-' . $shortcode);  // Prefix with 'CAT-' and use the first letter of each word as shortcode
        return $code;
    }



    public function delete($id)
    {
        $this->gm->delete_data($this->table, ['id' => $id]);
        $this->session->set_flashdata('message', 'Category dihapus.');
        redirect('category');
    }

    public function export_xlsx()
    {
        $rows = $this->gm->get_all_data($this->table);
        $ss = new Spreadsheet();
        $ws = $ss->getActiveSheet();
        $ws->fromArray([['Name', 'Description']], NULL, 'A1');
        $i = 2;
        foreach ($rows as $r) {
            $ws->setCellValue("A$i", $r['name']);
            $ws->setCellValue("B$i", $r['description']);
            $i++;
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="categories.xlsx"');
        (new Xlsx($ss))->save('php://output');
        exit;
    }

    public function export_pdf()
    {
        $rows = $this->gm->get_all_data($this->table);
        $html = '<h3>Categories</h3><table border="1" cellspacing="0" cellpadding="6"><tr><th>Name</th><th>Description</th></tr>';
        foreach ($rows as $r) {
            $html .= '<tr><td>' . html_escape($r['name']) . '</td><td>' . html_escape($r['description']) . '</td></tr>';
        }
        $html .= '</table>';
        $pdf = new Dompdf();
        $pdf->loadHtml($html);
        $pdf->render();
        $pdf->stream('categories.pdf', ['Attachment' => 1]);
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
