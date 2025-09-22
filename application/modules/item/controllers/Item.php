<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Dompdf\Dompdf;

class Item extends MX_Controller
{
    private $table = 'items';

    public function __construct()
    {
        parent::__construct();
        $this->_guard();
        $this->load->model('Generic_model', 'gm');
        $this->load->helper(['paging']);
        $this->load->database();
        $this->load->library(['form_validation', 'session']);
    }

    private function _guard()
    {
        if (!$this->session->userdata('logged_in')) redirect('login');
    }

    /* =========================
       LIST
       ========================= */
    public function index()
    {
        $q          = trim((string)$this->input->get('q', true));
        $cat        = (int)$this->input->get('category_id');
        $brand      = (int)$this->input->get('brand_id');
        $unit       = (int)$this->input->get('unit_id');
        $type       = strtoupper(trim((string)$this->input->get('item_type', true))); // FG, SFG, RAW
        $active     = $this->input->get('is_active', true); // 0 atau 1

        $sort_by    = $this->input->get('sort_by', true) ?: 'id';
        $sort_order = strtolower($this->input->get('sort_order', true) ?: 'asc');
        if (!in_array($sort_order, ['asc', 'desc'])) $sort_order = 'asc';

        $per    = 10;
        $page   = max(1, (int)$this->input->get('page'));
        $offset = ($page - 1) * $per;

        // filter
        if ($q !== '') {
            $this->db->group_start()
                ->like('item_code', $q)
                ->or_like('item_name', $q)
                ->group_end();
        }
        if ($cat)   $this->db->where('category_id', $cat);
        if ($brand) $this->db->where('brand_id', $brand);
        if ($unit)  $this->db->where('unit_id', $unit);
        if (in_array($type, ['FG', 'SFG', 'RAW'])) $this->db->where('item_type', $type);
        if ($active !== null && $active !== '') $this->db->where('is_active', (int)$active);

        // total
        $total = $this->db->count_all_results($this->table, false);

        // order + limit
        $allowedSort = ['id', 'item_code', 'item_name', 'item_type', 'category_id', 'brand_id', 'unit_id', 'is_active', 'created_at', 'updated_at'];
        if (!in_array($sort_by, $allowedSort)) $sort_by = 'id';
        $this->db->order_by($sort_by, $sort_order)->limit($per, $offset);

        $rows = $this->db->get()->result_array();

        // mapping label
        $custom_rows = [];
        foreach ($rows as $i => $row) {
            $row['no']            = $offset + $i + 1;
            $row['category_name'] = $this->_getCategoryName($row['category_id']);
            $row['brand_name']    = $this->_getBrandName($row['brand_id']);
            $row['unit_name']     = $this->_getUnitName($row['unit_id']);
            $row['is_active_text'] = ((int)$row['is_active'] === 1) ? 'Active' : 'Inactive';
            $custom_rows[] = $row;
        }

        $data = [
            'title'       => 'Items',
            'q'           => $q,
            'sort_by'     => $sort_by,
            'sort_order'  => $sort_order,
            'columns'     => [
                ['key' => 'no', 'label' => '#'],
                ['key' => 'item_code', 'label' => 'Item Code'],
                ['key' => 'item_name', 'label' => 'Item Name'],
                ['key' => 'item_type', 'label' => 'Type'],
                ['key' => 'category_name', 'label' => 'Category'],
                ['key' => 'brand_name', 'label' => 'Brand'],
                ['key' => 'unit_name', 'label' => 'Unit'],
                ['key' => 'is_active_text', 'label' => 'Status'],
            ],
            'rows'        => $custom_rows,
            'actions'     => [
                ['label' => 'Edit', 'class' => 'warning', 'url' => 'item/edit/{id}'],
                ['label' => 'Delete', 'class' => 'danger', 'url' => 'item/delete/{id}', 'confirm' => 'Hapus data ini?'],
            ],
            'create_url'       => site_url('item/create'),
            'import_url'       => site_url('item/import_xlsx'),
            'export_xlsx_url'  => site_url('item/export_xlsx'),
            'export_pdf_url'   => site_url('item/export_pdf'),
            'pagination_links' => build_pagination($this, site_url('item'), $total, $per),
        ];

        $this->_render('shared/list', $data);
    }

    /* =========================
       CREATE FORM
       ========================= */
    public function create()
    {
        $cats  = $this->gm->get_all_data('categories');
        $brs   = $this->gm->get_all_data('brands');
        $units = $this->gm->get_all_data('units');

        $data = [
            'title'    => 'Tambah Item',
            'post_url' => site_url('item/store'),
            'back_url' => site_url('item'),
            'fields'   => [
                ['name' => 'item_code', 'label' => 'Item Code', 'type' => 'text', 'placeholder' => 'Unique code'],
                ['name' => 'item_name', 'label' => 'Item Name', 'type' => 'text', 'placeholder' => 'Item name'],
                ['name' => 'item_type', 'label' => 'Item Type', 'type' => 'select', 'options' => ['FG' => 'FG', 'SFG' => 'SFG', 'RAW' => 'RAW']],
                ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'options' => $this->_options($cats)],
                ['name' => 'brand_id', 'label' => 'Brand', 'type' => 'select', 'options' => $this->_options($brs)],
                ['name' => 'unit_id', 'label' => 'Unit', 'type' => 'select', 'options' => $this->_options($units)],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'select', 'options' => ['1' => 'Active', '0' => 'Inactive']],
            ],
        ];
        $this->_render('shared/form', $data);
    }

    /* =========================
       STORE
       ========================= */
    public function store()
    {
        $this->form_validation->set_rules('item_code', 'Item Code', 'required|trim|is_unique[items.item_code]');
        $this->form_validation->set_rules('item_name', 'Item Name', 'required|trim');
        $this->form_validation->set_rules('item_type', 'Item Type', 'required|in_list[FG,SFG,RAW]');
        $this->form_validation->set_rules('unit_id', 'Unit', 'required|integer');

        if (!$this->form_validation->run()) return $this->create();

        $data = [
            'item_code'   => $this->input->post('item_code', true),
            'item_name'   => $this->input->post('item_name', true),
            'item_type'   => strtoupper($this->input->post('item_type', true)),
            'category_id' => (int)$this->input->post('category_id', true) ?: null,
            'unit_id'     => (int)$this->input->post('unit_id', true),
            'brand_id'    => (int)$this->input->post('brand_id', true) ?: null,
            'is_active'   => (int)$this->input->post('is_active', true),
        ];

        $this->gm->insert_data($this->table, $data);
        $this->session->set_flashdata('message', 'Item ditambahkan.');
        redirect('item');
    }

    /* =========================
       EDIT FORM
       ========================= */
    public function edit($id)
    {
        $row = $this->gm->get_row_where($this->table, ['id' => $id]);
        if (!$row) show_404();

        $cats  = $this->gm->get_all_data('categories');
        $brs   = $this->gm->get_all_data('brands');
        $units = $this->gm->get_all_data('units');

        $data = [
            'title'    => 'Edit Item',
            'post_url' => site_url('item/update/' . $id),
            'back_url' => site_url('item'),
            'is_edit'  => true,
            'fields'   => [
                ['name' => 'item_code', 'label' => 'Item Code', 'type' => 'text', 'value' => $row['item_code']],
                ['name' => 'item_name', 'label' => 'Item Name', 'type' => 'text', 'value' => $row['item_name']],
                ['name' => 'item_type', 'label' => 'Item Type', 'type' => 'select', 'options' => ['FG' => 'FG', 'SFG' => 'SFG', 'RAW' => 'RAW'], 'value' => $row['item_type']],
                ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'options' => $this->_options($cats), 'value' => $row['category_id']],
                ['name' => 'brand_id', 'label' => 'Brand', 'type' => 'select', 'options' => $this->_options($brs), 'value' => $row['brand_id']],
                ['name' => 'unit_id', 'label' => 'Unit', 'type' => 'select', 'options' => $this->_options($units), 'value' => $row['unit_id']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'select', 'options' => ['1' => 'Active', '0' => 'Inactive'], 'value' => $row['is_active']],
            ],
        ];
        $this->_render('shared/form', $data);
    }

    /* =========================
       UPDATE
       ========================= */
    public function update($id)
    {
        $row = $this->gm->get_row_where($this->table, ['id' => $id]);
        if (!$row) show_404();

        // Unique item_code kecuali dirinya sendiri
        $this->form_validation->set_rules('item_code', 'Item Code', 'required|trim|callback__unique_item_code[' . $id . ']');
        $this->form_validation->set_rules('item_name', 'Item Name', 'required|trim');
        $this->form_validation->set_rules('item_type', 'Item Type', 'required|in_list[FG,SFG,RAW]');
        $this->form_validation->set_rules('unit_id', 'Unit', 'required|integer');

        if (!$this->form_validation->run()) return $this->edit($id);

        $data = [
            'item_code'   => $this->input->post('item_code', true),
            'item_name'   => $this->input->post('item_name', true),
            'item_type'   => strtoupper($this->input->post('item_type', true)),
            'category_id' => (int)$this->input->post('category_id', true) ?: null,
            'unit_id'     => (int)$this->input->post('unit_id', true),
            'brand_id'    => (int)$this->input->post('brand_id', true) ?: null,
            'is_active'   => (int)$this->input->post('is_active', true),
        ];

        $this->gm->update_data($this->table, $data, ['id' => $id]);
        $this->session->set_flashdata('message', 'Item diperbarui.');
        redirect('item');
    }

    public function _unique_item_code($item_code, $id)
    {
        $dup = $this->gm->get_row_where($this->table, ['item_code' => $item_code, 'id !=' => (int)$id]);
        if ($dup) {
            $this->form_validation->set_message('_unique_item_code', 'Item Code sudah dipakai.');
            return false;
        }
        return true;
    }

    /* =========================
       DELETE
       ========================= */
    public function delete($id)
    {
        $this->gm->delete_data($this->table, ['id' => $id]);
        $this->session->set_flashdata('message', 'Item dihapus.');
        redirect('item');
    }

    /* =========================
       DOWNLOAD TEMPLATE
       ========================= */
    public function download_template()
    {
        $ss = new Spreadsheet();
        $ws = $ss->getActiveSheet();

        $headers = ['Item Code', 'Item Name', 'Item Type', 'Category', 'Unit', 'Brand', 'Is Active'];
        $ws->fromArray($headers, null, 'A1');

        // wajib: Item Code, Item Name, Item Type, Unit
        foreach (['A', 'B', 'C', 'E'] as $col) {
            $ws->getStyle($col . '1')->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFF99'],
                ]
            ]);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="item_template.xlsx"');
        (new Xlsx($ss))->save('php://output');
        exit;
    }

    /* =========================
       IMPORT XLSX
       ========================= */
    public function import_xlsx()
    {
        if (empty($_FILES['excel_file']['name'])) redirect('item');
        $tmp   = $_FILES['excel_file']['tmp_name'];
        $sheet = IOFactory::load($tmp)->getActiveSheet()->toArray();

        $preview = [];
        foreach ($sheet as $i => $r) {
            if ($i === 0) continue;
            if (empty(array_filter($r))) continue;

            // map kolom
            $item_code = trim((string)($r[0] ?? ''));
            $item_name = trim((string)($r[1] ?? ''));
            $item_type = strtoupper(trim((string)($r[2] ?? '')));
            $cat_name  = trim((string)($r[3] ?? ''));
            $unit_name = trim((string)($r[4] ?? ''));
            $brand_name = trim((string)($r[5] ?? ''));
            $is_active = trim((string)($r[6] ?? '1'));

            if ($item_code === '' || $item_name === '' || !in_array($item_type, ['FG', 'SFG', 'RAW']) || $unit_name === '') {
                continue;
            }

            $preview[] = [
                'item_code'   => $item_code,
                'item_name'   => $item_name,
                'item_type'   => $item_type,
                'category'    => $cat_name,
                'unit'        => $unit_name,
                'brand'       => $brand_name,
                'is_active'   => ($is_active === '0') ? 0 : 1,
            ];
        }

        $this->session->set_userdata('import_preview', $preview);
        redirect('item/import_preview');
    }

    public function import_preview()
    {
        $preview = $this->session->userdata('import_preview');
        if (!$preview) redirect('item');

        $data = [
            'title' => 'Preview Import Data',
            'import_preview' => $preview,
            'import_preview_columns' => ['Item Code', 'Item Name', 'Item Type', 'Category', 'Unit', 'Brand', 'Is Active'],
            'post_url' => site_url('item/import_confirm'),
            'back_url' => site_url('item'),
        ];
        $this->_render('shared/preview', $data);
    }

    public function import_confirm()
    {
        $preview = $this->session->userdata('import_preview');
        if (!$preview) redirect('item');

        $data = [];
        foreach ($preview as $r) {
            $data[] = [
                'item_code'   => $r['item_code'],
                'item_name'   => $r['item_name'],
                'item_type'   => $r['item_type'],
                'category_id' => $this->_idBy('categories', 'name', $r['category']),
                'unit_id'     => $this->_idBy('units', 'name', $r['unit']),
                'brand_id'    => $this->_idBy('brands', 'name', $r['brand']),
                'is_active'   => (int)$r['is_active'],
            ];
        }
        if ($data) $this->gm->insert_multiple_data($this->table, $data);

        $this->session->unset_userdata('import_preview');
        $this->session->set_flashdata('message', 'Import selesai.');
        redirect('item');
    }

    /* =========================
       EXPORTS
       ========================= */
    public function export_xlsx()
    {
        $rows  = $this->gm->get_all_data($this->table);
        $cats  = $this->gm->get_all_data('categories');
        $brs   = $this->gm->get_all_data('brands');
        $units = $this->gm->get_all_data('units');

        $ss = new Spreadsheet();
        $ws = $ss->getActiveSheet();
        $ws->fromArray([['Item Code', 'Item Name', 'Item Type', 'Category', 'Unit', 'Brand', 'Is Active', 'Created', 'Updated']], null, 'A1');

        $i = 2;
        foreach ($rows as $r) {
            $ws->fromArray([[
                $r['item_code'],
                $r['item_name'],
                $r['item_type'],
                $this->_labelById($cats, $r['category_id']),
                $this->_labelById($units, $r['unit_id']),
                $this->_labelById($brs, $r['brand_id']),
                ((int)$r['is_active'] === 1) ? 'Active' : 'Inactive',
                $r['created_at'] ?? '',
                $r['updated_at'] ?? '',
            ]], null, "A$i");
            $i++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="items.xlsx"');
        (new Xlsx($ss))->save('php://output');
        exit;
    }

    public function export_pdf()
    {
        $rows  = $this->gm->get_all_data($this->table);
        $cats  = $this->gm->get_all_data('categories');
        $brs   = $this->gm->get_all_data('brands');
        $units = $this->gm->get_all_data('units');

        $html = '<h3>Items</h3><table border="1" cellspacing="0" cellpadding="6"><tr>'
            . '<th>Item Code</th><th>Item Name</th><th>Type</th><th>Category</th><th>Unit</th><th>Brand</th><th>Status</th></tr>';
        foreach ($rows as $r) {
            $html .= '<tr>'
                . '<td>' . html_escape($r['item_code']) . '</td>'
                . '<td>' . html_escape($r['item_name']) . '</td>'
                . '<td>' . html_escape($r['item_type']) . '</td>'
                . '<td>' . html_escape($this->_labelById($cats, $r['category_id'])) . '</td>'
                . '<td>' . html_escape($this->_labelById($units, $r['unit_id'])) . '</td>'
                . '<td>' . html_escape($this->_labelById($brs, $r['brand_id'])) . '</td>'
                . '<td>' . (((int)$r['is_active'] === 1) ? 'Active' : 'Inactive') . '</td>'
                . '</tr>';
        }
        $html .= '</table>';

        $pdf = new Dompdf();
        $pdf->loadHtml($html);
        $pdf->render();
        $pdf->stream('items.pdf', ['Attachment' => 1]);
        exit;
    }

    /* =========================
       HELPERS
       ========================= */
    private function _getCategoryName($categoryId)
    {
        $category = $this->gm->get_row_where('categories', ['id' => $categoryId]);
        return $category['name'] ?? '';
    }

    private function _getBrandName($brandId)
    {
        $brand = $this->gm->get_row_where('brands', ['id' => $brandId]);
        return $brand['name'] ?? '';
    }

    private function _getUnitName($unitId)
    {
        $unit = $this->gm->get_row_where('units', ['id' => $unitId]);
        return $unit['name'] ?? '';
    }

    private function _options($rows, $key = 'id', $val = 'name')
    {
        $o = [];
        foreach ($rows as $r) $o[$r[$key]] = $r[$val];
        return $o;
    }

    private function _labelById($tableRows, $id, $val = 'name')
    {
        if (empty($tableRows)) return '';
        foreach ($tableRows as $row) {
            if ((int)$row['id'] === (int)$id) return $row[$val] ?? '';
        }
        return '';
    }

    private function _idBy($table, $col, $val)
    {
        if (!$val) return null;
        $row = $this->gm->get_row_where($table, [$col => $val]);
        return $row['id'] ?? null;
    }

    // Optional helper select HTML, tanpa karakter aneh
    private function _selectControl($name, $rows, $placeholder, $selected)
    {
        $html = '<select class="form-control ml-2" name="' . $name . '"><option value="">' . $placeholder . '</option>';
        foreach ($rows as $r) {
            $label = isset($r['name']) ? $r['name'] : ($r['code'] . ' - ' . $r['name']);
            $sel = ((string)$selected === (string)$r['id']) ? 'selected' : '';
            $html .= '<option value="' . $r['id'] . '" ' . $sel . '>' . html_escape($label) . '</option>';
        }
        $html .= '</select>';
        return $html;
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
