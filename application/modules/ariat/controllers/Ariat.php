<?php defined('BASEPATH') or exit('No direct script access allowed');

class Ariat extends MX_Controller
{
    private $table = 'ariat_defect_production';

    public function __construct()
    {
        parent::__construct();
        $this->_guard();
        $this->load->model('Generic_model', 'gm');
        $this->load->helper(['form', 'url', 'paging']);
        $this->load->library('form_validation');
    }

    private function _guard()
    {
        if (!$this->session->userdata('logged_in')) redirect('login');
    }

    public function index()
    {
        $q = trim($this->input->get('q'));
        $per = 10;
        $offset = max(0, (int)$this->input->get('page'));

        if ($q !== '') {
            $this->db->group_start()
                ->like('po_number', $q)
                ->or_like('brand', $q)
                ->or_like('dept_name', $q)
                ->group_end();
        }

        $total = $this->db->count_all_results($this->table, FALSE);

        $this->db->order_by('id', 'DESC')->limit($per, $offset);
        $rows = $this->db->get()->result_array();

        $custom_rows = [];
        $counter = 1 + $offset;
        foreach ($rows as $row) {
            $row['no'] = $counter++;
            $custom_rows[] = $row;
        }

        $data = [
            'title' => 'Ariat Defect Production',
            'rows'  => $custom_rows,
            'columns' => [
                ['key' => 'no', 'label' => 'No'],
                ['key' => 'po_number', 'label' => 'PO Number'],
                ['key' => 'brand', 'label' => 'Brand'],
                ['key' => 'dept_name', 'label' => 'Departemen'],
                ['key' => 'total_qty', 'label' => 'Total Qty'],
                ['key' => 'total_defect', 'label' => 'Total Defect'],
                ['key' => 'created_at', 'label' => 'Created At'],
            ],
            'actions' => [
                ['label' => 'Edit', 'class' => 'warning', 'url' => 'ariatdefectproduction/edit/{id}'],
                ['label' => 'Delete', 'class' => 'danger', 'url' => 'ariatdefectproduction/delete/{id}', 'confirm' => 'Hapus data ini?'],
            ],
            'create_url' => site_url('ariatdefectproduction/create'),
            'pagination_links' => build_pagination($this, site_url('ariatdefectproduction'), $total, $per),
        ];

        $this->_render('shared/list', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Ariat Defect',
            'post_url' => site_url('ariatdefectproduction/store'),
            'back_url' => site_url('ariatdefectproduction'),
            'fields' => [
                ['name' => 'id_df', 'label' => 'ID DF', 'type' => 'number'],
                ['name' => 'id_spk', 'label' => 'ID SPK', 'type' => 'number'],
                ['name' => 'po_number', 'label' => 'PO Number', 'type' => 'text'],
                ['name' => 'brand', 'label' => 'Brand', 'type' => 'text'],
                ['name' => 'total_qty', 'label' => 'Total Qty', 'type' => 'number'],
                ['name' => 'no_dfariat', 'label' => 'No DF Ariat', 'type' => 'text'],
                ['name' => 'tgl_input', 'label' => 'Tanggal Input', 'type' => 'date'],
                ['name' => 'dept_name', 'label' => 'Departemen', 'type' => 'text'],
                ['name' => 'qty_lasting', 'label' => 'Qty Lasting', 'type' => 'number'],
                ['name' => 'qty_cementing', 'label' => 'Qty Cementing', 'type' => 'number'],
                ['name' => 'qty_finishing', 'label' => 'Qty Finishing', 'type' => 'number'],
                ['name' => 'total_defect', 'label' => 'Total Defect', 'type' => 'number'],
            ],
        ];
        $this->_render('shared/form', $data);
    }

    public function store()
    {
        $this->form_validation->set_rules('po_number', 'PO Number', 'required|trim');
        $this->form_validation->set_rules('brand', 'Brand', 'required|trim');
        $this->form_validation->set_rules('dept_name', 'Departemen', 'required|trim');

        if (!$this->form_validation->run()) return $this->create();

        $insert = [
            'id_df'        => $this->input->post('id_df', true),
            'id_spk'       => $this->input->post('id_spk', true),
            'po_number'    => $this->input->post('po_number', true),
            'brand'        => $this->input->post('brand', true),
            'total_qty'    => $this->input->post('total_qty', true),
            'no_dfariat'   => $this->input->post('no_dfariat', true),
            'tgl_input'    => $this->input->post('tgl_input', true),
            'dept_name'    => $this->input->post('dept_name', true),
            'qty_lasting'  => $this->input->post('qty_lasting', true),
            'qty_cementing'=> $this->input->post('qty_cementing', true),
            'qty_finishing'=> $this->input->post('qty_finishing', true),
            'total_defect' => $this->input->post('total_defect', true),
            'created_at'   => date('Y-m-d H:i:s'),
        ];

        $this->gm->insert_data($this->table, $insert);
        $this->session->set_flashdata('message', 'Data berhasil ditambahkan.');
        redirect('ariatdefectproduction');
    }

    public function edit($id)
    {
        $row = $this->gm->get_row_where($this->table, ['id' => $id]);
        if (!$row) show_404();

        $data = [
            'title' => 'Edit Ariat Defect',
            'post_url' => site_url('ariatdefectproduction/update/' . $id),
            'back_url' => site_url('ariatdefectproduction'),
            'is_edit' => true,
            'fields' => [
                ['name' => 'id_df', 'label' => 'ID DF', 'type' => 'number', 'value' => $row['id_df']],
                ['name' => 'id_spk', 'label' => 'ID SPK', 'type' => 'number', 'value' => $row['id_spk']],
                ['name' => 'po_number', 'label' => 'PO Number', 'type' => 'text', 'value' => $row['po_number']],
                ['name' => 'brand', 'label' => 'Brand', 'type' => 'text', 'value' => $row['brand']],
                ['name' => 'total_qty', 'label' => 'Total Qty', 'type' => 'number', 'value' => $row['total_qty']],
                ['name' => 'no_dfariat', 'label' => 'No DF Ariat', 'type' => 'text', 'value' => $row['no_dfariat']],
                ['name' => 'tgl_input', 'label' => 'Tanggal Input', 'type' => 'date', 'value' => $row['tgl_input']],
                ['name' => 'dept_name', 'label' => 'Departemen', 'type' => 'text', 'value' => $row['dept_name']],
                ['name' => 'qty_lasting', 'label' => 'Qty Lasting', 'type' => 'number', 'value' => $row['qty_lasting']],
                ['name' => 'qty_cementing', 'label' => 'Qty Cementing', 'type' => 'number', 'value' => $row['qty_cementing']],
                ['name' => 'qty_finishing', 'label' => 'Qty Finishing', 'type' => 'number', 'value' => $row['qty_finishing']],
                ['name' => 'total_defect', 'label' => 'Total Defect', 'type' => 'number', 'value' => $row['total_defect']],
            ],
        ];
        $this->_render('shared/form', $data);
    }

    public function update($id)
    {
        $row = $this->gm->get_row_where($this->table, ['id' => $id]);
        if (!$row) show_404();

        $this->form_validation->set_rules('po_number', 'PO Number', 'required|trim');
        $this->form_validation->set_rules('brand', 'Brand', 'required|trim');
        $this->form_validation->set_rules('dept_name', 'Departemen', 'required|trim');

        if (!$this->form_validation->run()) return $this->edit($id);

        $update = [
            'id_df'        => $this->input->post('id_df', true),
            'id_spk'       => $this->input->post('id_spk', true),
            'po_number'    => $this->input->post('po_number', true),
            'brand'        => $this->input->post('brand', true),
            'total_qty'    => $this->input->post('total_qty', true),
            'no_dfariat'   => $this->input->post('no_dfariat', true),
            'tgl_input'    => $this->input->post('tgl_input', true),
            'dept_name'    => $this->input->post('dept_name', true),
            'qty_lasting'  => $this->input->post('qty_lasting', true),
            'qty_cementing'=> $this->input->post('qty_cementing', true),
            'qty_finishing'=> $this->input->post('qty_finishing', true),
            'total_defect' => $this->input->post('total_defect', true),
        ];

        $this->gm->update_data($this->table, $update, ['id' => $id]);
        $this->session->set_flashdata('message', 'Data berhasil diperbarui.');
        redirect('ariatdefectproduction');
    }

    public function delete($id)
    {
        $this->gm->delete_data($this->table, ['id' => $id]);
        $this->session->set_flashdata('message', 'Data berhasil dihapus.');
        redirect('ariatdefectproduction');
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
