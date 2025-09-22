<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Dompdf\Dompdf;


class Departement extends MX_Controller
{
    private $table = 'departements';
    private $wf_table = 'department_workflows'; // from_dept_id -> to_dept_id

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
        $sort_by = $this->input->get('sort_by', TRUE) ?? 'id';  // Default sort by 'custom_id'
        $sort_order = $this->input->get('sort_order', TRUE) ?? 'asc';  // Default ascending order

        $per = 10;
        $offset = max(0, (int)$this->input->get('page'));

        // Apply search filter
        if ($q !== '') {
            $this->db->group_start()->like('code', $q)->or_like('name', $q)->group_end();
        }

        // Get the total number of rows (for pagination)
        $total = $this->db->count_all_results($this->table, FALSE);

        // Apply sorting by selected column and order
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
            'title' => 'Departement',
            'q' => $q,
            'columns' => [
                ['key' => 'custom_id', 'label' => 'Custom ID'],
                ['key' => 'code', 'label' => 'Code'],
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'description', 'label' => 'Description'],
            ],
            'rows' => $custom_rows,
            'actions' => [
                ['label' => 'Workflow', 'class' => 'info', 'url' => 'departement/workflow/{id}'],
                ['label' => 'Edit', 'class' => 'warning', 'url' => 'departement/edit/{id}'],
                ['label' => 'Delete', 'class' => 'danger', 'url' => 'departement/delete/{id}', 'confirm' => 'Hapus data ini?'],
            ],
            'create_url' => site_url('departement/create'),
            'pagination_links' => build_pagination($this, site_url('departement'), $total, $per),
            'no_template' => true, // Menyembunyikan tombol Download Template pada halaman ini
        ];

        $this->_render('shared/list', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Departement',
            'post_url' => site_url('departement/store'),
            'back_url' => site_url('departement'),
            'fields' => [
                ['name' => 'code', 'label' => 'Code', 'type' => 'text', 'placeholder' => 'e.g. D01'],
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'placeholder' => 'Departement Name'],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            ],
        ];
        $this->_render('shared/form', $data);
    }

    public function store()
    {
        $this->form_validation->set_rules('code', 'Code', 'required|trim|is_unique[departements.code]');
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        if (!$this->form_validation->run()) return $this->create();
        $this->gm->insert_data($this->table, [
            'code' => $this->input->post('code', true),
            'name' => $this->input->post('name', true),
            'description' => $this->input->post('description', true),
        ]);
        $this->session->set_flashdata('message', 'Departement ditambahkan.');
        redirect('departement');
    }

    public function edit($id)
    {
        $row = $this->gm->get_row_where($this->table, ['id' => $id]);
        if (!$row) show_404();
        $data = [
            'title' => 'Edit Departement',
            'post_url' => site_url('departement/update/' . $id),
            'back_url' => site_url('departement'),
            'is_edit' => true,
            'fields' => [
                ['name' => 'code', 'label' => 'Code', 'type' => 'text', 'value' => $row['code']],
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
        $is_unique = ($row['code'] === $this->input->post('code')) ? '' : '|is_unique[departements.code]';
        $this->form_validation->set_rules('code', 'Code', 'required|trim' . $is_unique);
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        if (!$this->form_validation->run()) return $this->edit($id);
        $this->gm->update_data($this->table, [
            'code' => $this->input->post('code', true),
            'name' => $this->input->post('name', true),
            'description' => $this->input->post('description', true),
        ], ['id' => $id]);
        $this->session->set_flashdata('message', 'Departement diperbarui.');
        redirect('departement');
    }

    public function delete($id)
    {
        $this->gm->delete_data($this->wf_table, ['from_dept_id' => $id]);
        $this->gm->delete_data($this->wf_table, ['to_dept_id' => $id]);
        $this->gm->delete_data($this->table, ['id' => $id]);
        $this->session->set_flashdata('message', 'Departement dihapus.');
        redirect('departement');
    }

    /* ===== Workflow (alur kerja/line produksi) ===== */
    public function workflow($dept_id)
    {
        $dept = $this->gm->get_row_where($this->table, ['id' => $dept_id]);
        if (!$dept) show_404();
        $all = $this->gm->get_all_data($this->table);

        $seq = [];
        $visited = [];
        $current = $dept_id;
        while (true) {
            if (in_array($current, $visited, true)) break;
            $visited[] = $current;
            $seq[] = $current;
            $next = $this->db->select('to_dept_id')->where('from_dept_id', $current)->get($this->wf_table)->row_array();
            if (!$next) break;
            $current = (int)$next['to_dept_id'];
        }

        $data = [
            'title' => "Workflow — {$dept['name']}",
            'back_url' => site_url('departement'),
            'dept' => $dept,
            'all_dept' => $all,
            'sequence' => $seq,
            'post_add' => site_url('departement/wf_add/' . $dept_id),
            'post_clear' => site_url('departement/wf_clear/' . $dept_id),
        ];
        $this->_render('departement/workflow', $data);
    }

    public function wf_add($dept_id)
    {
        $to = (int)$this->input->post('to_dept_id');
        if ($to && $to != $dept_id) {
            $this->gm->delete_data($this->wf_table, ['from_dept_id' => $dept_id]);
            $this->gm->insert_data($this->wf_table, ['from_dept_id' => $dept_id, 'to_dept_id' => $to]);
            $this->session->set_flashdata('message', 'Workflow diperbarui.');
        }
        redirect('departement/workflow/' . $dept_id);
    }

    public function wf_clear($dept_id)
    {
        $this->gm->delete_data($this->wf_table, ['from_dept_id' => $dept_id]);
        $this->session->set_flashdata('message', 'Workflow dibersihkan.');
        redirect('departement/workflow/' . $dept_id);
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
