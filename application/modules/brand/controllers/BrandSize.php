<?php defined('BASEPATH') or exit('No direct script access allowed');

class BrandSize extends MX_Controller
{
    private $table = 'brand_sizes';

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

    public function index($brand_id)
    {
        $brand = $this->gm->get_row_where('brands', ['id' => $brand_id]);
        if (!$brand) show_404();
        $q = trim($this->input->get('q'));
        $per = 10;
        $offset = max(0, (int)$this->input->get('page'));

        $this->db->where('brand_id', $brand_id);
        if ($q !== '') $this->db->like('size_name', $q);
        $total = $this->db->count_all_results($this->table, FALSE);
        $this->db->order_by('id', 'DESC')->limit($per, $offset);
        $rows = $this->db->get()->result_array();

        $data = [
            'title' => "Sizes — {$brand['name']}",
            'q' => $q,
            'columns' => [
                ['key' => 'id', 'label' => 'ID'],
                ['key' => 'size_name', 'label' => 'Size'],
                ['key' => 'note', 'label' => 'Note'],
            ],
            'rows' => $rows,
            'actions' => [
                ['label' => 'Edit', 'class' => 'warning', 'url' => "brand/brandsize/edit/{id}?brand_id={$brand_id}"],
                ['label' => 'Delete', 'class' => 'danger', 'url' => "brand/brandsize/delete/{id}?brand_id={$brand_id}", 'confirm' => 'Hapus data ini?'],
            ],
            'create_url' => site_url("brand/brandsize/create/{$brand_id}"),
            'pagination_links' => build_pagination($this, site_url("brand/brandsize/index/{$brand_id}"), $total, $per),
        ];
        $this->_render('shared/list', $data);
    }

    public function create($brand_id)
    {
        $brand = $this->gm->get_row_where('brands', ['id' => $brand_id]);
        if (!$brand) show_404();
        $data = [
            'title' => "Tambah Size — {$brand['name']}",
            'post_url' => site_url('brand/brandsize/store?brand_id=' . $brand_id),
            'back_url' => site_url('brand/brandsize/index/' . $brand_id),
            'fields' => [
                ['name' => 'size_name', 'label' => 'Size', 'type' => 'text', 'placeholder' => 'e.g. S, M, L, 38'],
                ['name' => 'note', 'label' => 'Note', 'type' => 'textarea'],
            ],
        ];
        $this->_render('shared/form', $data);
    }

    public function store()
    {
        $brand_id = (int)$this->input->get('brand_id');
        if (!$brand_id) show_404();
        $this->form_validation->set_rules('size_name', 'Size', 'required|trim');
        if (!$this->form_validation->run()) return $this->create($brand_id);

        $this->gm->insert_data($this->table, [
            'brand_id' => $brand_id,
            'size_name' => $this->input->post('size_name', true),
            'note' => $this->input->post('note', true)
        ]);
        $this->session->set_flashdata('message', 'Size ditambahkan.');
        redirect('brand/brandsize/index/' . $brand_id);
    }

    public function edit($id)
    {
        $brand_id = (int)$this->input->get('brand_id');
        $row = $this->gm->get_row_where($this->table, ['id' => $id]);
        if (!$row || !$brand_id) show_404();
        $data = [
            'title' => 'Edit Size',
            'post_url' => site_url('brand/brandsize/update/' . $id . '?brand_id=' . $brand_id),
            'back_url' => site_url('brand/brandsize/index/' . $brand_id),
            'is_edit' => true,
            'fields' => [
                ['name' => 'size_name', 'label' => 'Size', 'type' => 'text', 'value' => $row['size_name']],
                ['name' => 'note', 'label' => 'Note', 'type' => 'textarea', 'value' => $row['note']],
            ],
        ];
        $this->_render('shared/form', $data);
    }

    public function update($id)
    {
        $brand_id = (int)$this->input->get('brand_id');
        $row = $this->gm->get_row_where($this->table, ['id' => $id]);
        if (!$row || !$brand_id) show_404();
        $this->form_validation->set_rules('size_name', 'Size', 'required|trim');
        if (!$this->form_validation->run()) return $this->edit($id);

        $this->gm->update_data($this->table, [
            'size_name' => $this->input->post('size_name', true),
            'note' => $this->input->post('note', true)
        ], ['id' => $id]);

        $this->session->set_flashdata('message', 'Size diperbarui.');
        redirect('brand/brandsize/index/' . $brand_id);
    }

    public function delete($id)
    {
        $brand_id = (int)$this->input->get('brand_id');
        $this->gm->delete_data($this->table, ['id' => $id]);
        $this->session->set_flashdata('message', 'Size dihapus.');
        redirect('brand/brandsize/index/' . $brand_id);
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
