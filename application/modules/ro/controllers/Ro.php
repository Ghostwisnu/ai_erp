<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

class Ro extends MX_Controller
{
    /* ======== MASTER TABLES ======== */
    private $wo_l1 = 'wo_l1';
    private $wo_l2 = 'wo_l2';
    private $wo_l3 = 'wo_l3';
    private $items = 'items';
    private $brands = 'brands';
    private $departements = 'departements';

    /* ======== RO TABLES ======== */
    private $tbl_hdr = 'ro_headers';
    private $tbl_det = 'ro_details';

    public function __construct()
    {
        parent::__construct();
        $this->_check_login();
        $this->load->model('Generic_model', 'gm');
        $this->load->library(['form_validation', 'pagination']);
        $this->load->helper(['url', 'form', 'security']);
    }

    private function _check_login()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
    }

    /* ================= LIST ================= */
    public function index()
    {
        $data['title'] = 'Request Orders';

        $q          = $this->input->get('q', TRUE);
        $sort_by    = $this->input->get('sort_by') ?: 'id';
        $sort_order = strtolower($this->input->get('sort_order') ?: 'desc');
        $allowed    = ['id', 'no_ro', 'status_ro', 'ro_date'];
        if (!in_array($sort_by, $allowed)) $sort_by = 'id';
        if (!in_array($sort_order, ['asc', 'desc'])) $sort_order = 'desc';

        $per_page = 10;
        $page     = (int)($this->input->get('per_page') ?: 0);

        // total
        if ($q) {
            $q_esc = $this->db->escape_like_str($q);
            $sql_count = "
                SELECT COUNT(*) AS c
                FROM {$this->tbl_hdr}
                WHERE no_ro LIKE '%{$q_esc}%' OR status_ro LIKE '%{$q_esc}%'
            ";
        } else {
            $sql_count = "SELECT COUNT(*) AS c FROM {$this->tbl_hdr}";
        }
        $total = (int)($this->gm->custom_query($sql_count)[0]['c'] ?? 0);

        // rows
        if ($q) {
            $q_esc = $this->db->escape_like_str($q);
            $sql_rows = "
                SELECT id, no_ro, status_ro, ro_date
                FROM {$this->tbl_hdr}
                WHERE no_ro LIKE '%{$q_esc}%' OR status_ro LIKE '%{$q_esc}%'
                ORDER BY {$sort_by} {$sort_order}
                LIMIT {$per_page} OFFSET {$page}
            ";
        } else {
            $sql_rows = "
                SELECT id, no_ro, status_ro, ro_date
                FROM {$this->tbl_hdr}
                ORDER BY {$sort_by} {$sort_order}
                LIMIT {$per_page} OFFSET {$page}
            ";
        }
        $rows = $this->gm->custom_query($sql_rows);

        // pagination
        $config['base_url']             = current_url() . '?' . http_build_query(array_merge($this->input->get(), ['per_page' => null]));
        $config['total_rows']           = $total;
        $config['page_query_string']    = TRUE;
        $config['query_string_segment'] = 'per_page';
        $config['per_page']             = $per_page;
        $this->pagination->initialize($config);

        $data['rows'] = $rows;
        $data['q'] = $q;
        $data['sort_by'] = $sort_by;
        $data['sort_order'] = $sort_order;
        $data['pagination_links'] = $this->pagination->create_links();
        $data['flash_error'] = $this->session->flashdata('flash_error');

        $this->_render('ro/list', $data);
    }

    /* ================= CREATE ================= */
    public function create()
    {
        $data['title'] = 'Create Request Order';

        // departement dropdown
        $data['departements'] = $this->gm->get_all_data($this->departements);

        // Suggested number
        $data['no_ro_suggest'] = $this->generate_ro_number();

        $this->_render('ro/create', $data);
    }

    private function generate_ro_number()
    {
        // Pola: RO-YYYYMM-XXXX
        $prefix = 'RO-' . date('Ym') . '-';
        $this->db->like('no_ro', $prefix);
        $this->db->from($this->tbl_hdr);
        $count = $this->db->count_all_results() + 1;
        return $prefix . str_pad((string)$count, 4, '0', STR_PAD_LEFT);
    }

    /* ================= STORE =================
       Expect:
       - no_ro, ro_date, departement_id
       - wo_l1_id, wo_l2_id, brand_id (auto), art_color (auto)
       - detail arrays: wo_l3_id[], item_id[], required_qty[], qty[]
    */
    public function store()
    {
        // basic validation
        $this->form_validation->set_rules('ro_date', 'Tanggal', 'required|trim');
        $this->form_validation->set_rules('departement_id', 'Departement', 'required|integer');
        $this->form_validation->set_rules('wo_l1_id', 'WO L1', 'required|integer');
        $this->form_validation->set_rules('wo_l2_id', 'WO L2 (SFG)', 'required|integer');

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('flash_error', validation_errors());
            return redirect('ro/create');
        }

        $no_ro = trim((string)$this->input->post('no_ro'));
        if ($no_ro === '') $no_ro = $this->generate_ro_number();

        $hdr = [
            'no_ro'          => $no_ro,
            'wo_l1_id'       => (int)$this->input->post('wo_l1_id'),
            'wo_l2_id'       => (int)$this->input->post('wo_l2_id'),
            'brand_id'       => $this->input->post('brand_id') !== '' ? (int)$this->input->post('brand_id') : null,
            'art_color'      => trim((string)$this->input->post('art_color')),
            'departement_id' => (int)$this->input->post('departement_id'),
            'ro_date'        => $this->input->post('ro_date'),
            'status_ro'      => 'draft',
            'created_at'     => date('Y-m-d H:i:s'),
        ];

        $wo_l3_ids = $this->input->post('wo_l3_id') ?: [];
        $item_ids  = $this->input->post('item_id') ?: [];
        $reqs      = $this->input->post('required_qty') ?: [];
        $qtys      = $this->input->post('qty') ?: [];

        if (empty($wo_l3_ids)) {
            $this->session->set_flashdata('flash_error', 'Belum ada item pada tabel body.');
            return redirect('ro/create');
        }

        $this->db->trans_begin();

        // header
        $ok_ins = $this->gm->insert_data($this->tbl_hdr, $hdr);
        $hdr_id = $this->db->insert_id();
        if (!$ok_ins || !$hdr_id) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('flash_error', 'Gagal menyimpan header RO.');
            return redirect('ro/create');
        }

        // details
        $batch = [];
        foreach ($wo_l3_ids as $i => $l3id) {
            $batch[] = [
                'ro_header_id' => (int)$hdr_id,
                'wo_l3_id'     => (int)$l3id,
                'item_id'      => (int)($item_ids[$i] ?? 0),
                'required_qty' => (float)($reqs[$i] ?? 0),
                'qty'          => (float)($qtys[$i] ?? 0),
            ];
        }
        if (!empty($batch)) {
            $ok_det = $this->gm->insert_multiple_data($this->tbl_det, $batch);
            if (!$ok_det) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('flash_error', 'Gagal menyimpan detail RO.');
                return redirect('ro/create');
            }
        }

        $this->db->trans_commit();
        $this->session->set_flashdata('message', 'RO berhasil dibuat.');
        redirect('ro');
    }

    /* ================= EDIT ================= */
    public function edit($id)
    {
        if (!ctype_digit((string)$id)) show_404();

        $header = $this->gm->get_row_where($this->tbl_hdr, ['id' => (int)$id]);
        if (!$header) {
            $this->session->set_flashdata('flash_error', 'RO tidak ditemukan.');
            return redirect('ro');
        }

        // Get details with item names
        $sql_details = "
        SELECT d.*, it.item_code, it.item_name
        FROM {$this->tbl_det} d
        LEFT JOIN {$this->items} it ON it.id = d.item_id
        WHERE d.ro_header_id = " . (int)$id . "
        ORDER BY d.id ASC
    ";
        $details = $this->gm->custom_query($sql_details);

        $data = [
            'title'        => 'Edit Request Order',
            'header'       => $header,
            'details'      => $details,
            'departements' => $this->gm->get_all_data($this->departements),
            'wo_info'      => $this->gm->get_row_where($this->wo_l1, ['id' => (int)$header['wo_l1_id']]),
        ];

        $this->_render('ro/create', $data); // reuse form
    }

    /* ================= UPDATE ================= */
    public function update($id)
    {
        if (!ctype_digit((string)$id)) show_404();

        $this->form_validation->set_rules('ro_date', 'Tanggal', 'required|trim');
        $this->form_validation->set_rules('departement_id', 'Departement', 'required|integer');
        $this->form_validation->set_rules('wo_l1_id', 'WO L1', 'required|integer');
        $this->form_validation->set_rules('wo_l2_id', 'WO L2 (SFG)', 'required|integer');

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('flash_error', validation_errors());
            return redirect('ro/edit/' . $id);
        }

        $payload = [
            'no_ro'          => trim((string)$this->input->post('no_ro')),
            'wo_l1_id'       => (int)$this->input->post('wo_l1_id'),
            'wo_l2_id'       => (int)$this->input->post('wo_l2_id'),
            'brand_id'       => $this->input->post('brand_id') !== '' ? (int)$this->input->post('brand_id') : null,
            'art_color'      => trim((string)$this->input->post('art_color')),
            'departement_id' => (int)$this->input->post('departement_id'),
            'ro_date'        => $this->input->post('ro_date'),
        ];

        $wo_l3_ids = $this->input->post('wo_l3_id') ?: [];
        $item_ids  = $this->input->post('item_id') ?: [];
        $reqs      = $this->input->post('required_qty') ?: [];
        $qtys      = $this->input->post('qty') ?: [];

        $this->db->trans_begin();

        $ok_upd = $this->gm->update_data($this->tbl_hdr, $payload, ['id' => (int)$id]);
        if (!$ok_upd) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('flash_error', 'Gagal update header RO.');
            return redirect('ro/edit/' . $id);
        }

        // replace details
        $this->gm->delete_data($this->tbl_det, ['ro_header_id' => (int)$id]);

        $batch = [];
        foreach ($wo_l3_ids as $i => $l3id) {
            $batch[] = [
                'ro_header_id' => (int)$id,
                'wo_l3_id'     => (int)$l3id,
                'item_id'      => (int)($item_ids[$i] ?? 0),
                'required_qty' => (float)($reqs[$i] ?? 0),
                'qty'          => (float)($qtys[$i] ?? 0),
            ];
        }
        if (!empty($batch)) {
            $ok_det = $this->gm->insert_multiple_data($this->tbl_det, $batch);
            if (!$ok_det) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('flash_error', 'Gagal update detail RO.');
                return redirect('ro/edit/' . $id);
            }
        }

        $this->db->trans_commit();
        $this->session->set_flashdata('message', 'RO berhasil diupdate.');
        redirect('ro');
    }

    /* ================= DELETE ================= */
    public function delete($id)
    {
        if (!ctype_digit((string)$id)) show_404();

        $this->db->trans_begin();
        $this->gm->delete_data($this->tbl_det, ['ro_header_id' => (int)$id]);
        $this->gm->delete_data($this->tbl_hdr, ['id' => (int)$id]);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('flash_error', 'Gagal menghapus RO.');
        } else {
            $this->session->set_flashdata('message', 'RO berhasil dihapus.');
        }
        redirect('ro');
    }

    /* ================= AJAX ================= */

    // Auto-number RO
    public function ajax_generate_no_ro()
    {
        $this->output->set_content_type('application/json');
        echo json_encode(['no_ro' => $this->generate_ro_number()]);
    }

    public function ajax_search_wo()
    {
        $this->output->set_content_type('application/json');
        $q = $this->input->get('q', TRUE);
        $lim = 20;

        if ($q) {
            $q_esc = $this->db->escape_like_str($q);
            $sql = "
            SELECT w1.id, w1.no_wo, w1.art_color, w1.created_at,
                   b.id AS brand_id, b.name AS brand_name
            FROM {$this->wo_l1} w1
            LEFT JOIN {$this->brands} b ON b.id = w1.brand_id
            WHERE w1.no_wo LIKE '%{$q_esc}%' OR w1.art_color LIKE '%{$q_esc}%'
            ORDER BY w1.created_at DESC
            LIMIT {$lim}
        ";
        } else {
            $sql = "
            SELECT w1.id, w1.no_wo, w1.art_color, w1.created_at,
                   b.id AS brand_id, b.name AS brand_name
            FROM {$this->wo_l1} w1
            LEFT JOIN {$this->brands} b ON b.id = w1.brand_id
            ORDER BY w1.created_at DESC
            LIMIT {$lim}
        ";
        }
        $rows = $this->gm->custom_query($sql);
        echo json_encode(['rows' => $rows]);
    }

    public function ajax_get_wo_info($wo_l1_id)
    {
        $this->output->set_content_type('application/json');
        if (!ctype_digit((string)$wo_l1_id)) {
            echo json_encode(['error' => 'Invalid']);
            return;
        }

        $sql = "
        SELECT w1.brand_id, w1.art_color, b.name AS brand_name
        FROM {$this->wo_l1} w1
        LEFT JOIN {$this->brands} b ON b.id = w1.brand_id
        WHERE w1.id = " . (int)$wo_l1_id . "
        LIMIT 1
    ";
        $row = $this->gm->custom_query($sql);
        $row = $row[0] ?? null;
        if (!$row) {
            echo json_encode(['error' => 'WO not found']);
            return;
        }

        echo json_encode([
            'brand_id'    => $row['brand_id'] ?? null,
            'brand_name'  => $row['brand_name'] ?? null,
            'art_color'   => $row['art_color'] ?? null,
        ]);
    }

    public function ajax_get_sfg_by_wo($wo_l1_id)
    {
        $this->output->set_content_type('application/json');
        if (!ctype_digit((string)$wo_l1_id)) {
            echo json_encode(['rows' => []]);
            return;
        }

        $sql = "
        SELECT l2.id, l2.item_id, i.item_code, i.item_name
        FROM {$this->wo_l2} l2
        LEFT JOIN {$this->items} i ON i.id = l2.item_id
        WHERE l2.wo_l1_id = " . (int)$wo_l1_id . "
        ORDER BY l2.id ASC
    ";
        $rows = $this->gm->custom_query($sql);
        echo json_encode(['rows' => $rows]);
    }

    public function ajax_get_items_by_sfg($wo_l2_id)
    {
        $this->output->set_content_type('application/json');
        if (!ctype_digit((string)$wo_l2_id)) {
            echo json_encode(['rows' => []]);
            return;
        }

        $sql = "
        SELECT l3.id AS wo_l3_id,
               l3.item_id,
               l3.required_qty,
               i.item_code,
               i.item_name
        FROM {$this->wo_l3} l3
        LEFT JOIN {$this->items} i ON i.id = l3.item_id
        WHERE l3.wo_l2_id = " . (int)$wo_l2_id . "
        ORDER BY l3.id ASC
    ";
        $rows = $this->gm->custom_query($sql);
        echo json_encode(['rows' => $rows]);
    }

    public function pdf($id)
    {
        if (!ctype_digit((string)$id)) show_404();

        // 1) Pastikan Dompdf terload
        $autoload = FCPATH . 'vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        } else {
            show_error('Composer autoload (vendor/autoload.php) tidak ditemukan. Jalankan: composer require dompdf/dompdf', 500);
        }

        // 2) Ambil data
        $header = $this->gm->get_row_where($this->tbl_hdr, ['id' => (int)$id]);
        if (!$header) show_404();

        $sql = "
        SELECT d.*, it.item_code, it.item_name
        FROM {$this->tbl_det} d
        LEFT JOIN {$this->items} it ON it.id = d.item_id
        WHERE d.ro_header_id = " . (int)$id . "
        ORDER BY d.id ASC
    ";
        $details = $this->gm->custom_query($sql);

        // optional pretty labels
        $brand_name = null;
        if (!empty($header['brand_id'])) {
            $b = $this->gm->get_row_where($this->brands, ['id' => (int)$header['brand_id']]);
            $brand_name = $b['name'] ?? null;
        }
        $wo_no = null;
        if (!empty($header['wo_l1_id'])) {
            $w1 = $this->gm->get_row_where($this->wo_l1, ['id' => (int)$header['wo_l1_id']]);
            $wo_no = $w1['no_wo'] ?? null;
        }
        $sfg_label = null;
        if (!empty($header['wo_l2_id'])) {
            $sqlSfg = "SELECT i.item_code, i.item_name
                   FROM {$this->wo_l2} l2
                   LEFT JOIN {$this->items} i ON i.id = l2.item_id
                   WHERE l2.id = " . (int)$header['wo_l2_id'] . " LIMIT 1";
            $sfg = $this->gm->custom_query($sqlSfg);
            if (!empty($sfg)) {
                $sfg_label = trim(($sfg[0]['item_code'] ?? '') . ' - ' . ($sfg[0]['item_name'] ?? ''));
            }
        }
        $departement_name = null;
        if (!empty($header['departement_id'])) {
            $dep = $this->gm->get_row_where($this->departements, ['id' => (int)$header['departement_id']]);
            $departement_name = isset($dep['code']) ? ($dep['code'] . ' - ' . ($dep['name'] ?? '')) : ($dep['name'] ?? null);
        }

        // 3) Render HTML view
        $html = $this->load->view('ro/pdf', [
            'header'           => $header,
            'details'          => $details,
            'brand_name'       => $brand_name,
            'wo_no'            => $wo_no,
            'sfg_label'        => $sfg_label,
            'departement_name' => $departement_name
        ], true);

        // 4) Bersihkan buffer agar header Dompdf tidak terganggu
        if (ob_get_length()) {
            @ob_end_clean();
        }

        // 5) Build & kirim PDF
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // (opsional) atur metadata
        $dompdf->add_info('Title', 'RO ' . $header['no_ro']);

        $filename = 'RO_' . ($header['no_ro'] ?? $id) . '.pdf';

        // Untuk menghindari cache aneh di beberapa server/proxy
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        $dompdf->stream($filename, ['Attachment' => false]);
        exit; // 6) Penting: hentikan eksekusi setelah stream
    }

    /* ============== Helper render ============== */
    private function _render($view, $data)
    {
        $data['user']    = $this->session->userdata('username');
        $data['role_id'] = $this->session->userdata('role_id');
        $this->load->view('templates/header', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view($view, $data);
        $this->load->view('templates/footer');
    }
}
