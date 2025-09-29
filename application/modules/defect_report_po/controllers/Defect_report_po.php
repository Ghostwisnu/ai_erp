<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Dompdf\Dompdf;

class Defect_report_po extends MX_Controller
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

    public function index()
    {
        $q = trim($this->input->get('q'));
        $sort_by = $this->input->get('sort_by', TRUE) ?? 'id_df';
        $sort_order = $this->input->get('sort_order', TRUE) ?? 'desc';

        $per = 10;
        $offset = max(0, (int)$this->input->get('page'));

        if ($q !== '') {
            $this->db->group_start()
                ->like('po_number', $q)
                ->or_like('brand', $q)
                ->or_like('artcolor_name', $q)
                ->or_like('dept_name', $q)
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
            'title' => 'Defect Report PO',
            'q' => $q,
            'sort_by' => $sort_by,
            'sort_order' => $sort_order,
            'columns' => [
                ['key' => 'custom_id', 'label' => 'No'],
                ['key' => 'no_dfariat', 'label' => 'No DF Ariat'],
                ['key' => 'po_number', 'label' => 'PO Number'],
                ['key' => 'artcolor_name', 'label' => 'ArtColor'],
                ['key' => 'brand', 'label' => 'Brand'],
                ['key' => 'total_qty', 'label' => 'Total Qty'],
                ['key' => 'dept_name', 'label' => 'Departemen'],
                ['key' => 'total_defect', 'label' => 'Total Defect'],
                ['key' => 'created_at', 'label' => 'Created At'],
            ],
            'rows' => $custom_rows,
            'actions' => [
                ['label' => 'Edit', 'class' => 'warning', 'url' => 'defect_report_po/edit/{id_df}'],
                ['label' => 'Delete', 'class' => 'danger', 'url' => 'defect_report_po/delete/{id_df}', 'confirm' => 'Hapus data ini?'],
            ],
            'create_url' => site_url('defect_report_po/create'),
            'import_url' => site_url('defect_report_po/import_xlsx'),
            'export_xlsx_url' => site_url('defect_report_po/export_xlsx'),
            'export_pdf_url' => site_url('defect_report_po/export_pdf'),
            'pagination_links' => build_pagination($this, site_url('defect_report_po'), $total, $per),
        ];

        $this->_render('shared/list', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Defect Report PO',
            'post_url' => site_url('defect_report_po/store'),
            'back_url' => site_url('defect_report_po'),
            'fields' => [
                ['name' => 'po_number', 'label' => 'PO Number', 'type' => 'text'],
                ['name' => 'artcolor_name', 'label' => 'ArtColor Name', 'type' => 'text'],
                ['name' => 'brand', 'label' => 'Brand', 'type' => 'text'],
                ['name' => 'total_qty', 'label' => 'Total Qty', 'type' => 'number'],
                ['name' => 'no_dfariat', 'label' => 'No DF Ariat', 'type' => 'text'],
                ['name' => 'dept_name', 'label' => 'Departemen', 'type' => 'text'],
                ['name' => 'total_defect', 'label' => 'Total Defect', 'type' => 'number'],
            ],
        ];
        $this->_render('shared/form', $data);
    }

    public function store()
    {
        $this->form_validation->set_rules('po_number', 'PO Number', 'required|trim');
        $this->form_validation->set_rules('brand', 'Brand', 'required|trim');

        if (!$this->form_validation->run()) return $this->create();

        $this->gm->insert_data($this->table, [
            'po_number'     => $this->input->post('po_number', true),
            'artcolor_name' => $this->input->post('artcolor_name', true),
            'brand'         => $this->input->post('brand', true),
            'total_qty'     => $this->input->post('total_qty', true),
            'no_dfariat'    => $this->input->post('no_dfariat', true),
            'dept_name'     => $this->input->post('dept_name', true),
            'total_defect'  => $this->input->post('total_defect', true),
            'created_at'    => date('Y-m-d H:i:s'),
            'tgl_input'     => date('Y-m-d'),
        ]);

        $this->session->set_flashdata('message', 'Data berhasil ditambahkan.');
        redirect('defect_report_po');
    }

    public function edit($id)
    {
        $row = $this->gm->get_row_where($this->table, ['id_df' => $id]);
        if (!$row) show_404();

        $data = [
            'title' => 'Edit Defect Report PO',
            'post_url' => site_url('defect_report_po/update/' . $id),
            'back_url' => site_url('defect_report_po'),
            'is_edit' => true,
            'fields' => [
                ['name' => 'po_number', 'label' => 'PO Number', 'type' => 'text', 'value' => $row['po_number']],
                ['name' => 'artcolor_name', 'label' => 'ArtColor Name', 'type' => 'text', 'value' => $row['artcolor_name']],
                ['name' => 'brand', 'label' => 'Brand', 'type' => 'text', 'value' => $row['brand']],
                ['name' => 'total_qty', 'label' => 'Total Qty', 'type' => 'number', 'value' => $row['total_qty']],
                ['name' => 'no_dfariat', 'label' => 'No DF Ariat', 'type' => 'text', 'value' => $row['no_dfariat']],
                ['name' => 'dept_name', 'label' => 'Departemen', 'type' => 'text', 'value' => $row['dept_name']],
                ['name' => 'total_defect', 'label' => 'Total Defect', 'type' => 'number', 'value' => $row['total_defect']],
            ],
        ];
        $this->_render('shared/form', $data);
    }

    public function update($id)
    {
        $row = $this->gm->get_row_where($this->table, ['id_df' => $id]);
        if (!$row) show_404();

        $this->form_validation->set_rules('po_number', 'PO Number', 'required|trim');
        $this->form_validation->set_rules('brand', 'Brand', 'required|trim');

        if (!$this->form_validation->run()) return $this->edit($id);

        $this->gm->update_data($this->table, [
            'po_number'     => $this->input->post('po_number', true),
            'artcolor_name' => $this->input->post('artcolor_name', true),
            'brand'         => $this->input->post('brand', true),
            'total_qty'     => $this->input->post('total_qty', true),
            'no_dfariat'    => $this->input->post('no_dfariat', true),
            'dept_name'     => $this->input->post('dept_name', true),
            'total_defect'  => $this->input->post('total_defect', true),
        ], ['id_df' => $id]);

        $this->session->set_flashdata('message', 'Data berhasil diperbarui.');
        redirect('defect_report_po');
    }

    public function delete($id)
    {
        $this->gm->delete_data($this->table, ['id_df' => $id]);
        $this->session->set_flashdata('message', 'Data berhasil dihapus.');
        redirect('defect_report_po');
    }

    public function export_xlsx()
    {
        $rows = $this->gm->get_all_data($this->table);
        $ss = new Spreadsheet();
        $ws = $ss->getActiveSheet();
        $ws->fromArray([['No DF Ariat','PO Number','ArtColor','Brand','Total Qty','Dept','Total Defect','Created At']], NULL, 'A1');
        $i = 2;
        foreach ($rows as $r) {
            $ws->setCellValue("A$i", $r['no_dfariat']);
            $ws->setCellValue("B$i", $r['po_number']);
            $ws->setCellValue("C$i", $r['artcolor_name']);
            $ws->setCellValue("D$i", $r['brand']);
            $ws->setCellValue("E$i", $r['total_qty']);
            $ws->setCellValue("F$i", $r['dept_name']);
            $ws->setCellValue("G$i", $r['total_defect']);
            $ws->setCellValue("H$i", $r['created_at']);
            $i++;
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="defect_report_po.xlsx"');
        (new Xlsx($ss))->save('php://output');
        exit;
    }

    public function export_pdf()
    {
        $rows = $this->gm->get_all_data($this->table);
        $html = '<h3>Defect Report PO</h3><table border="1" cellspacing="0" cellpadding="6"><tr>
            <th>No DF Ariat</th><th>PO Number</th><th>ArtColor</th><th>Brand</th>
            <th>Total Qty</th><th>Dept</th><th>Total Defect</th><th>Created At</th></tr>';
        foreach ($rows as $r) {
            $html .= '<tr><td>'.$r['no_dfariat'].'</td><td>'.$r['po_number'].'</td><td>'.$r['artcolor_name'].'</td>
            <td>'.$r['brand'].'</td><td>'.$r['total_qty'].'</td><td>'.$r['dept_name'].'</td>
            <td>'.$r['total_defect'].'</td><td>'.$r['created_at'].'</td></tr>';
        }
        $html .= '</table>';
        $pdf = new Dompdf();
        $pdf->loadHtml($html);
        $pdf->render();
        $pdf->stream('defect_report_po.pdf', ['Attachment' => 1]);
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
