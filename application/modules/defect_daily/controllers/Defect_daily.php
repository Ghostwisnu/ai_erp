<?php defined('BASEPATH') or exit('No direct script access allowed');

class Defect_daily extends MX_Controller
{
    private $table = 'ariat_defect_production';

    public function __construct()
    {
        parent::__construct();
        $this->_guard();
        $this->load->model('Generic_model', 'gm');
        $this->load->helper(['url', 'form']);
    }

    private function _guard()
    {
        if (!$this->session->userdata('logged_in')) redirect('login');
    }

    /** ========== LIST DATA ========== */
    public function index()
    {
        $rows = $this->gm->get_all_data($this->table);

        $data = [
            'title' => 'Daily Defect Production',
            'rows'  => $rows,
        ];

        $this->_render('defect_daily/list', $data);
    }

    /** ========== DETAIL VIEW ========== */
    public function detail($id_df)
    {
        // ambil data DF berdasarkan id_df
        $row = $this->gm->get_row_where($this->table, ['id_df' => $id_df]);
        if (!$row) show_404();

        // ambil defect dari master + qty dari data_count_defect
        $this->db->select('d.id_defect, d.nama_defect, d.brand, d.desc_database, IFNULL(c.qty,0) as qty');
        $this->db->from('data_defect d');
        $this->db->join('data_count_defect c', 'd.id_defect = c.id_defect AND c.id_df = '.$id_df, 'left');
        $defects = $this->db->get()->result_array();

        $data = [
            'title'   => 'Detail Daily Defect',
            'row'     => $row,
            'defects' => $defects
        ];

        $this->_render('defect_daily/detail', $data);
    }

    /** ========== TAMBAH DEFECT ========== */
    public function tambah_defect($id_df, $id_defect)
    {
        // cek data DF
        $df = $this->gm->get_row_where($this->table, ['id_df' => $id_df]);
        if (!$df) show_404();

        // cek defect master
        $defect = $this->gm->get_row_where('data_defect', ['id_defect' => $id_defect]);
        if (!$defect) show_404();

        // cek apakah sudah ada di count
        $count = $this->gm->get_row_where('data_count_defect', [
            'id_df'     => $id_df,
            'id_defect' => $id_defect
        ]);

        if ($count) {
            // update qty
            $this->gm->update_data('data_count_defect', [
                'qty' => (int)$count['qty'] + 1
            ], ['id' => $count['id']]);
        } else {
            // insert baru
            $this->gm->insert_data('data_count_defect', [
                'id_defect'   => $id_defect,
                'id_df'       => $id_df,
                'brand'       => $df['brand'],
                'nama_defect' => $defect['nama_defect'],
                'qty'         => 1
            ]);
        }

        // update total_defect di ariat_defect_production
        $new_total = (int)$df['total_defect'] + 1;
        $this->gm->update_data($this->table, [
            'total_defect' => $new_total
        ], ['id_df' => $id_df]);

        $this->session->set_flashdata('message', 'Defect berhasil ditambahkan.');
        redirect('defect_daily/detail/' . $id_df);
    }

    /** ========== SIMPAN QTY PRODUKSI (lasting/cementing/finishing) ========== */
    public function simpan_qty()
    {
        $id_df = $this->input->post('id_df');
        $field = $this->input->post('field'); // qty_lasting, qty_cementing, qty_finishing
        $qty   = $this->input->post('qty');

        $allowed_fields = ['qty_lasting', 'qty_cementing', 'qty_finishing'];
        if (!in_array($field, $allowed_fields)) {
            show_error('Field qty tidak valid.', 400);
        }

        $this->gm->update_data($this->table, [
            $field => (int)$qty
        ], ['id_df' => $id_df]);

        $this->session->set_flashdata('message', 'Qty berhasil diperbarui.');
        redirect('defect_daily/detail/' . $id_df);
    }

    /** ========== EXPORT PDF ========== */
    public function export_pdf($id_df)
    {
        $this->load->library('pdfgenerator');
        $row = $this->gm->get_row_where($this->table, ['id_df' => $id_df]);
        if (!$row) show_404();

        // ambil defect dengan qty
        $this->db->select('d.nama_defect, IFNULL(c.qty,0) as qty');
        $this->db->from('data_defect d');
        $this->db->join('data_count_defect c', 'd.id_defect = c.id_defect AND c.id_df = '.$id_df, 'left');
        $defects = $this->db->get()->result_array();

        $data = [
            'title'   => 'Defect Daily Report',
            'row'     => $row,
            'defects' => $defects
        ];

        $html = $this->load->view('defect_daily/pdf_template', $data, true);

        $this->pdfgenerator->generate($html, 'Daily_Defect_' . $row['no_dfariat']);
    }

    /** ========== RENDER WRAPPER ========== */
    private function _render($view, $data = [])
    {
        $this->load->view('templates/header', $data);
        $this->load->view($view, $data);
    }
}
