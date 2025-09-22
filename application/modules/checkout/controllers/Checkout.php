<?php defined('BASEPATH') or exit('No direct script access allowed');

class Checkout extends MX_Controller
{
    /* === TABLE ALIASES === */
    private $tbl_ro_hdr      = 'ro_headers';
    private $tbl_ro_det      = 'ro_details';
    private $tbl_checkin_hdr = 'checkin_hdr';
    private $tbl_checkin_det = 'checkin_det';
    private $tbl_wo_l1       = 'wo_l1';
    private $tbl_items       = 'items';
    private $tbl_brands      = 'brands';
    private $tbl_deps        = 'departements';

    /* === CONFIG === */
    private $allowed_ro_statuses = ['draft', 'submitted_pending']; // status yang boleh di-checkout
    private $status_after_checkout = 'submitted';                  // ubah sesuai flow kamu

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

    /* ============ LIST ============ */
    public function index()
    {
        $data['title'] = 'Checkout Request Orders';

        $q          = $this->input->get('q', TRUE);
        $sort_by    = $this->input->get('sort_by') ?: 'id';
        $sort_order = strtolower($this->input->get('sort_order') ?: 'desc');
        $allowed    = ['id', 'no_ro', 'ro_date', 'status_ro'];
        if (!in_array($sort_by, $allowed)) $sort_by = 'id';
        if (!in_array($sort_order, ['asc', 'desc'])) $sort_order = 'desc';

        $per_page   = 10;
        $page       = (int)($this->input->get('per_page') ?: 0);

        // Filter dasar: hanya status yang boleh diproses
        $status_in  = "'" . implode("','", array_map([$this->db, 'escape_str'], $this->allowed_ro_statuses)) . "'";

        // Count
        if ($q) {
            $q_esc = $this->db->escape_like_str($q);
            $sql_count = "
                SELECT COUNT(*) AS c
                FROM {$this->tbl_ro_hdr} h
                LEFT JOIN {$this->tbl_wo_l1} w1 ON w1.id = h.wo_l1_id
                WHERE h.status_ro IN ({$status_in})
                  AND (h.no_ro LIKE '%{$q_esc}%' OR w1.no_wo LIKE '%{$q_esc}%')
            ";
        } else {
            $sql_count = "
                SELECT COUNT(*) AS c
                FROM {$this->tbl_ro_hdr} h
                WHERE h.status_ro IN ({$status_in})
            ";
        }
        $total = (int)($this->gm->custom_query($sql_count)[0]['c'] ?? 0);

        // Rows
        if ($q) {
            $q_esc = $this->db->escape_like_str($q);
            $sql_rows = "
                SELECT h.id, h.no_ro, h.ro_date, h.status_ro, h.wo_l1_id, w1.no_wo
                FROM {$this->tbl_ro_hdr} h
                LEFT JOIN {$this->tbl_wo_l1} w1 ON w1.id = h.wo_l1_id
                WHERE h.status_ro IN ({$status_in})
                  AND (h.no_ro LIKE '%{$q_esc}%' OR w1.no_wo LIKE '%{$q_esc}%')
                ORDER BY {$sort_by} {$sort_order}
                LIMIT {$per_page} OFFSET {$page}
            ";
        } else {
            $sql_rows = "
                SELECT h.id, h.no_ro, h.ro_date, h.status_ro, h.wo_l1_id, w1.no_wo
                FROM {$this->tbl_ro_hdr} h
                LEFT JOIN {$this->tbl_wo_l1} w1 ON w1.id = h.wo_l1_id
                WHERE h.status_ro IN ({$status_in})
                ORDER BY {$sort_by} {$sort_order}
                LIMIT {$per_page} OFFSET {$page}
            ";
        }
        $rows = $this->gm->custom_query($sql_rows);

        // OPTIONAL: hitung readiness (stok cukup?) untuk badge di list
        foreach ($rows as &$r) {
            $r['ready'] = $this->_is_ro_stock_ready((int)$r['id'], (int)$r['wo_l1_id']);
        }

        // pagination
        $config['base_url']             = current_url() . '?' . http_build_query(array_merge($this->input->get(), ['per_page' => null]));
        $config['total_rows']           = $total;
        $config['page_query_string']    = TRUE;
        $config['query_string_segment'] = 'per_page';
        $config['per_page']             = $per_page;
        $this->pagination->initialize($config);

        $data['rows']             = $rows;
        $data['q']                = $q;
        $data['sort_by']          = $sort_by;
        $data['sort_order']       = $sort_order;
        $data['pagination_links'] = $this->pagination->create_links();
        $data['flash_error']      = $this->session->flashdata('flash_error');

        $this->_render('checkout/list', $data);
    }

    /* ============ PREVIEW (VIEW) ============ */
    public function preview($ro_id)
    {
        if (!ctype_digit((string)$ro_id)) show_404();

        $header = $this->gm->get_row_where($this->tbl_ro_hdr, ['id' => (int)$ro_id]);
        if (!$header) {
            $this->_fail('RO tidak ditemukan.', 'checkout');
            return;
        }

        $needs = $this->_get_ro_needs_grouped((int)$ro_id); // item_id, req_qty
        if (empty($needs)) {
            $this->_fail('Detail RO kosong.', 'checkout');
            return;
        }

        // Enrich: label & stock
        $rows = [];
        foreach ($needs as $n) {
            $item_id = (int)$n['item_id'];
            $req_qty = (float)$n['req_qty'];

            $label = 'Item ID ' . $item_id;
            $it = $this->gm->get_row_where($this->tbl_items, ['id' => $item_id]);
            if ($it) $label = trim(($it['item_code'] ?? '') . ' - ' . ($it['item_name'] ?? ''));

            $stock = $this->_get_stock_balance((int)$header['wo_l1_id'], $item_id); // [in,out,balance]
            $rows[] = [
                'item_id'       => $item_id,
                'item_label'    => $label,
                'req_qty'       => round($req_qty, 6),
                'total_in'      => round($stock['in'], 6),
                'total_out'     => round($stock['out'], 6),
                'stock_balance' => round($stock['balance'], 6),
                'enough'        => ($stock['balance'] + 1e-9) >= $req_qty
            ];
        }
        $all_ok = array_reduce($rows, fn($c, $r) => $c && $r['enough'], true);

        // Display info tambahan
        $brand_name = null;
        if (!empty($header['brand_id'])) {
            $b = $this->gm->get_row_where($this->tbl_brands, ['id' => (int)$header['brand_id']]);
            $brand_name = $b['name'] ?? null;
        }
        $wo_no = null;
        if (!empty($header['wo_l1_id'])) {
            $w1 = $this->gm->get_row_where($this->tbl_wo_l1, ['id' => (int)$header['wo_l1_id']]);
            $wo_no = $w1['no_wo'] ?? null;
        }
        $dep_name = null;
        if (!empty($header['departement_id'])) {
            $dep = $this->gm->get_row_where($this->tbl_deps, ['id' => (int)$header['departement_id']]);
            $dep_name = isset($dep['code']) ? ($dep['code'] . ' - ' . ($dep['name'] ?? '')) : ($dep['name'] ?? null);
        }

        $data = [
            'title'            => 'Preview Checkout — ' . $header['no_ro'],
            'header'           => $header,
            'rows'             => $rows,
            'all_ok'           => $all_ok,
            'brand_name'       => $brand_name,
            'wo_no'            => $wo_no,
            'departement_name' => $dep_name,
            'flash_error'      => $this->session->flashdata('flash_error'),
        ];

        $this->_render('checkout/preview', $data);
    }

    /* ============ CONFIRM (POST) ============ */
    public function confirm($ro_id)
    {
        if (!ctype_digit((string)$ro_id)) show_404();
        // idealnya POST + CSRF
        if (strtoupper($this->input->method(TRUE)) !== 'POST') {
            show_error('Invalid method', 405);
        }

        $ro = $this->gm->get_row_where($this->tbl_ro_hdr, ['id' => (int)$ro_id]);
        if (!$ro) {
            $this->_fail('RO tidak ditemukan.', 'checkout');
            return;
        }
        if (!in_array(($ro['status_ro'] ?? 'draft'), $this->allowed_ro_statuses)) {
            $this->_fail('Status RO tidak valid untuk checkout.', 'checkout/preview/' . $ro_id);
            return;
        }

        $needs = $this->_get_ro_needs_grouped((int)$ro_id);
        if (empty($needs)) {
            $this->_fail('Detail RO kosong.', 'checkout/preview/' . $ro_id);
            return;
        }

        // Validasi stok dahulu
        $errors = [];
        foreach ($needs as $n) {
            $item_id = (int)$n['item_id'];
            $req_qty = (float)$n['req_qty'];
            $stock   = $this->_get_stock_balance((int)$ro['wo_l1_id'], $item_id);
            if ($stock['balance'] + 1e-9 < $req_qty) {
                $label = 'Item ID ' . $item_id;
                $it = $this->gm->get_row_where($this->tbl_items, ['id' => $item_id]);
                if ($it) $label = trim(($it['item_code'] ?? '') . ' - ' . ($it['item_name'] ?? ''));
                $errors[] = "{$label}: stok {$stock['balance']} < kebutuhan {$req_qty}";
            }
        }
        if (!empty($errors)) {
            $this->_fail('Checkout dibatalkan. Stok tidak cukup:<br>- ' . implode('<br>- ', $errors), 'checkout/preview/' . $ro_id);
            return;
        }

        // Mulai transaksi + alokasi FIFO
        $this->db->trans_begin();
        try {
            foreach ($needs as $n) {
                $item_id  = (int)$n['item_id'];
                $to_issue = (float)$n['req_qty'];
                if ($to_issue <= 0) continue;

                // Kunci baris checkin_det terkait (FIFO)
                $sql_rows = "
                    SELECT id, qty_in, qty_out
                      FROM {$this->tbl_checkin_det}
                     WHERE wo_l1_id = " . (int)$ro['wo_l1_id'] . "
                       AND item_id  = " . (int)$item_id . "
                     ORDER BY created_at ASC, id ASC
                     FOR UPDATE
                ";
                $rows = $this->db->query($sql_rows)->result_array();

                foreach ($rows as $r) {
                    if ($to_issue <= 0) break;
                    $available = (float)$r['qty_in'] - (float)$r['qty_out'];
                    if ($available <= 0) continue;

                    $take = min($available, $to_issue);
                    if ($take > 0) {
                        $new_out = (float)$r['qty_out'] + $take;
                        $this->gm->update_data($this->tbl_checkin_det, [
                            'qty_out'    => $new_out,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ], ['id' => (int)$r['id']]);
                        $to_issue -= $take;
                    }
                }

                if ($to_issue > 0) {
                    throw new Exception("Stok berubah saat proses. Sisa kebutuhan {$to_issue} untuk item {$item_id}.");
                }
            }

            // Update status RO
            $this->gm->update_data($this->tbl_ro_hdr, [
                'status_ro'  => $this->status_after_checkout,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => (int)$ro_id]);

            $this->db->trans_commit();
            $this->session->set_flashdata('message', 'Checkout berhasil. Status RO diperbarui.');
            redirect('checkout');
        } catch (\Throwable $e) {
            $this->db->trans_rollback();
            $this->_fail('Checkout gagal: ' . $e->getMessage(), 'checkout/preview/' . $ro_id);
        }
    }

    /* ============ HELPERS ============ */

    private function _get_ro_needs_grouped(int $ro_id): array
    {
        $sql = "
            SELECT item_id, SUM(qty) AS req_qty
              FROM {$this->tbl_ro_det}
             WHERE ro_header_id = {$ro_id}
             GROUP BY item_id
             ORDER BY item_id
        ";
        return $this->gm->custom_query($sql);
    }

    private function _get_stock_balance(int $wo_l1_id, int $item_id): array
    {
        $sql = "
            SELECT COALESCE(SUM(qty_in),0) AS total_in,
                   COALESCE(SUM(qty_out),0) AS total_out
              FROM {$this->tbl_checkin_det}
             WHERE wo_l1_id = {$wo_l1_id}
               AND item_id  = {$item_id}
        ";
        $st = $this->gm->custom_query($sql);
        $in  = (float)($st[0]['total_in'] ?? 0);
        $out = (float)($st[0]['total_out'] ?? 0);
        return ['in' => $in, 'out' => $out, 'balance' => $in - $out];
    }

    private function _is_ro_stock_ready(int $ro_id, int $wo_l1_id): bool
    {
        $needs = $this->_get_ro_needs_grouped($ro_id);
        foreach ($needs as $n) {
            $bal = $this->_get_stock_balance($wo_l1_id, (int)$n['item_id']);
            if ($bal['balance'] + 1e-9 < (float)$n['req_qty']) return false;
        }
        return true;
    }

    private function _fail(string $msg, string $to = 'checkout')
    {
        $this->session->set_flashdata('flash_error', $msg);
        redirect($to);
    }

    /* ============ SHARED RENDER ============ */
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
