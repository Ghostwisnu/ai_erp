<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

class Production extends MX_Controller
{
    /* master */
    private $ro_hdr = 'ro_headers';
    private $ro_det = 'ro_details';
    private $wo_l1  = 'wo_l1';
    private $wo_l2  = 'wo_l2';
    private $items  = 'items';
    private $brands = 'brands';
    private $deps   = 'departements';
    private $brand_sizes = 'brand_sizes';

    /* our tables */
    private $prod_hdr = 'production_hdr';
    private $prod_sz  = 'production_sizes';

    /* check-in (auto create on confirm) */
    private $checkin_hdr = 'checkin_hdr';
    private $checkin_det = 'checkin_det';
    private $checkin_det_sizes = 'checkin_det_sizes'; // jika ada

    public function __construct()
    {
        parent::__construct();
        $this->_check_login();
        $this->load->model('Generic_model', 'gm');
        $this->load->helper(['url', 'form', 'security']);
        $this->load->library(['form_validation', 'pagination']);
    }

    private function _check_login()
    {
        if (!$this->session->userdata('logged_in')) redirect('login');
    }

    /* ================= RO LIST (eligible) ================= */
    public function ro_list()
    {
        $data['title'] = 'RO Siap Dibuat Laporan Produksi';

        $q        = $this->input->get('q', TRUE);
        $per_page = 10;
        $page     = (int)($this->input->get('per_page') ?: 0);

        // TAMPILKAN submitted + belum_lengkap + sudah_lengkap
        $where_status = "h.status_ro IN ('submitted','belum lengkap','sudah lengkap')";

        // ---------- COUNT ----------
        if ($q) {
            $q_esc = $this->db->escape_like_str($q);
            $sqlc = "
            SELECT COUNT(DISTINCT h.id) AS c
            FROM {$this->ro_hdr} h
            LEFT JOIN {$this->wo_l1} w1 ON w1.id = h.wo_l1_id
            WHERE {$where_status}
              AND (h.no_ro LIKE '%{$q_esc}%' OR w1.no_wo LIKE '%{$q_esc}%')
        ";
        } else {
            $sqlc = "
            SELECT COUNT(*) AS c
            FROM {$this->ro_hdr} h
            WHERE {$where_status}
        ";
        }
        $total = (int)($this->gm->custom_query($sqlc)[0]['c'] ?? 0);

        // ---------- ROWS ----------
        if ($q) {
            $q_esc = $this->db->escape_like_str($q);
            $sql = "
            SELECT
                h.id, h.no_ro, h.status_ro, h.ro_date, h.wo_l1_id, h.wo_l2_id, h.brand_id, h.art_color,
                w1.no_wo,
                i2.item_code AS sfg_code, i2.item_name AS sfg_name,
                b.name AS brand_name,
                d.name AS departement_name,
                MAX(p.id) AS prod_id
            FROM {$this->ro_hdr} h
            LEFT JOIN {$this->wo_l1} w1 ON w1.id = h.wo_l1_id
            LEFT JOIN {$this->wo_l2} w2 ON w2.id = h.wo_l2_id
            LEFT JOIN {$this->items} i2 ON i2.id = w2.item_id
            LEFT JOIN {$this->brands} b ON b.id = h.brand_id
            LEFT JOIN {$this->deps}   d ON d.id = h.departement_id
            LEFT JOIN {$this->prod_hdr} p ON p.ro_id = h.id
            WHERE {$where_status}
              AND (h.no_ro LIKE '%{$q_esc}%' OR w1.no_wo LIKE '%{$q_esc}%')
            GROUP BY h.id
            ORDER BY h.id DESC
            LIMIT {$per_page} OFFSET {$page}
        ";
        } else {
            $sql = "
            SELECT
                h.id, h.no_ro, h.status_ro, h.ro_date, h.wo_l1_id, h.wo_l2_id, h.brand_id, h.art_color,
                w1.no_wo,
                i2.item_code AS sfg_code, i2.item_name AS sfg_name,
                b.name AS brand_name,
                d.name AS departement_name,
                MAX(p.id) AS prod_id
            FROM {$this->ro_hdr} h
            LEFT JOIN {$this->wo_l1} w1 ON w1.id = h.wo_l1_id
            LEFT JOIN {$this->wo_l2} w2 ON w2.id = h.wo_l2_id
            LEFT JOIN {$this->items} i2 ON i2.id = w2.item_id
            LEFT JOIN {$this->brands} b ON b.id = h.brand_id
            LEFT JOIN {$this->deps}   d ON d.id = h.departement_id
            LEFT JOIN {$this->prod_hdr} p ON p.ro_id = h.id
            WHERE {$where_status}
            GROUP BY h.id
            ORDER BY h.id DESC
            LIMIT {$per_page} OFFSET {$page}
        ";
        }
        $rows = $this->gm->custom_query($sql);

        // pagination
        $config['base_url']             = current_url() . '?' . http_build_query(array_merge($this->input->get(), ['per_page' => null]));
        $config['total_rows']           = $total;
        $config['page_query_string']    = TRUE;
        $config['query_string_segment'] = 'per_page';
        $config['per_page']             = $per_page;
        $this->pagination->initialize($config);

        $data['rows']             = $rows;
        $data['q']                = $q;
        $data['pagination_links'] = $this->pagination->create_links();

        $this->_render('production/ro_list', $data);
    }


    /* ================= LIST production ================= */
    public function index()
    {
        $data['title'] = 'Laporan Produksi';

        // Ambil parameter pencarian dari query string
        $q = $this->input->get('q', TRUE);  // Nilai pencarian (default NULL jika kosong)

        // Build base query
        $this->db->select('p.id, p.prod_code, p.total_qty, p.status_prod, p.created_at,
                       h.no_ro, w1.no_wo, i2.item_name AS sfg_name');
        $this->db->from($this->prod_hdr . ' p');
        $this->db->join($this->ro_hdr . ' h', 'h.id = p.ro_id', 'left');
        $this->db->join($this->wo_l1 . ' w1', 'w1.id = p.wo_l1_id', 'left');
        $this->db->join($this->wo_l2 . ' w2', 'w2.id = p.wo_l2_id', 'left');
        $this->db->join($this->items . ' i2', 'i2.id = w2.item_id', 'left');

        // Menambahkan kondisi pencarian jika ada
        if ($q) {
            $q_esc = $this->db->escape_like_str($q);  // Escape query string
            $this->db->like('h.no_ro', $q_esc);      // Pencarian pada no_ro
            $this->db->or_like('w1.no_wo', $q_esc);   // Pencarian pada no_wo
            $this->db->or_like('i2.item_name', $q_esc);  // Pencarian pada item_name (SFG)
        }

        // Order by prod_id (default DESC)
        $this->db->order_by('p.id', 'DESC');

        // Eksekusi query
        $rows = $this->db->get()->result_array();

        // Kirimkan data ke view
        $data['rows'] = $rows;
        $data['q'] = $q;  // Kirimkan nilai pencarian untuk dipakai di form pencarian di view

        // Render tampilan
        $this->_render('production/list', $data);
    }

    /* ================= CREATE ================= */
    public function create($ro_id)
    {
        if (!ctype_digit((string)$ro_id)) show_404();

        // Ambil header RO
        $hdr = $this->gm->get_row_where($this->ro_hdr, ['id' => (int)$ro_id]);
        if (!$hdr || ($hdr['status_ro'] ?? '') !== 'submitted') {
            $this->session->set_flashdata('flash_error', 'RO tidak valid/ belum submitted.');
            return redirect('production/ro_list');
        }

        // Ambil informasi WO L1 dan WO L2
        $w1 = $this->gm->get_row_where($this->wo_l1, ['id' => (int)$hdr['wo_l1_id']]);
        $w2 = $this->gm->get_row_where($this->wo_l2, ['id' => (int)$hdr['wo_l2_id']]);
        $sfg = $w2 ? $this->gm->get_row_where($this->items, ['id' => (int)$w2['item_id']]) : null;
        $brand = $this->gm->get_row_where($this->brands, ['id' => (int)$hdr['brand_id']]);
        $dep   = $this->gm->get_row_where($this->deps,   ['id' => (int)$hdr['departement_id']]);

        // 1) Ambil size_id dan qty dari wo_sizes (berdasarkan wo_l1_id)
        $sizes_from_wo = $this->db->select('ws.size_id, ws.qty AS plan_qty, bs.size_name')
            ->from('wo_sizes ws')
            ->join('brand_sizes bs', 'bs.id = ws.size_id', 'left') // Use join() with 'left' for left join
            ->where('ws.wo_l1_id', (int)$hdr['wo_l1_id'])
            ->order_by('ws.size_id', 'ASC')
            ->get()->result_array();

        // 2) Combine size data from wo_sizes with size_name and qty (plan_qty)
        $sizes = [];
        foreach ($sizes_from_wo as $size) {
            $sizes[] = [
                'id'        => (int)$size['size_id'],
                'size_name' => $size['size_name'],
                'plan_qty'  => (int)$size['plan_qty']
            ];
        }


        // Data yang akan dikirim ke view
        $data = [
            'title'             => 'Create Production Report',
            'prod_code_suggest' => $this->_generate_prod_code(),
            'ro'                => $hdr,
            'wo_no'             => $w1['no_wo'] ?? '',
            'sfg_label'         => trim(($sfg['item_code'] ?? '') . ' - ' . ($sfg['item_name'] ?? '')),
            'brand_name'        => $brand['name'] ?? '',
            'departement'       => $dep['name'] ?? '',
            'sizes'             => $sizes,  // Ukuran yang didapatkan dari tabel wo_sizes
            'plan_map'          => array_column($sizes, 'plan_qty', 'id') // Membuat map dari size_id ke qty
        ];

        $this->_render('production/create', $data);
    }


    private function _generate_prod_code(): string
    {
        $prefix = 'PRD-' . date('Ym') . '-';
        $this->db->like('prod_code', $prefix);
        $this->db->from($this->prod_hdr);
        $n = $this->db->count_all_results() + 1;
        return $prefix . str_pad((string)$n, 4, '0', STR_PAD_LEFT);
    }

    private function _generate_str_number(): string
    {
        $prefix = 'STR-' . date('Ym') . '-';
        $this->db->like('no_str', $prefix);
        $this->db->from($this->checkin_hdr);
        $n = $this->db->count_all_results() + 1;
        return $prefix . str_pad((string)$n, 4, '0', STR_PAD_LEFT);
    }

    private function _get_latest_production_by_ro($ro_id)
    {
        $row = $this->db->select('id')
            ->from($this->prod_hdr)
            ->where('ro_id', (int)$ro_id)
            ->order_by('id', 'DESC')
            ->limit(1)->get()->row_array();
        return $row ?: null;
    }

    /* ================= STORE =================
       Expect:
       - prod_code (optional)
       - ro_id (hidden), wo_l1_id, wo_l2_id, brand_id, departement_id, art_color
       - total_qty (computed on client, divalidasi server)
       - size_id[], plan_qty[], input_qty[], status_size[], short_qty[]
    */
    public function store()
    {
        $this->form_validation->set_rules('ro_id', 'RO', 'required|integer');
        $this->form_validation->set_rules('wo_l1_id', 'WO', 'required|integer');
        $this->form_validation->set_rules('wo_l2_id', 'SFG (WO L2)', 'required|integer');
        $this->form_validation->set_rules('brand_id', 'Brand', 'required|integer');
        $this->form_validation->set_rules('departement_id', 'Departement', 'required|integer');

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('flash_error', validation_errors());
            return redirect('production/ro_list');
        }

        $ro_id   = (int)$this->input->post('ro_id');
        $wo_l1_id = (int)$this->input->post('wo_l1_id');
        $wo_l2_id = (int)$this->input->post('wo_l2_id');
        $brand_id = (int)$this->input->post('brand_id');

        $ro = $this->gm->get_row_where($this->ro_hdr, ['id' => $ro_id]);
        if (!$ro || ($ro['status_ro'] ?? '') !== 'submitted') {
            $this->session->set_flashdata('flash_error', 'RO tidak valid/ belum submitted.');
            return redirect('production/ro_list');
        }

        $prod_code = trim((string)$this->input->post('prod_code'));
        if ($prod_code === '') $prod_code = $this->_generate_prod_code();

        $size_ids    = (array)$this->input->post('size_id');
        $input_qtys  = (array)$this->input->post('input_qty');
        $statuses    = (array)$this->input->post('status_size');
        $short_qtys  = (array)$this->input->post('short_qty');

        // === Ambil PLAN dari DB: wo_sizes (wo_l1_id + size_id) ===
        // Ambil PLAN dari DB (INT)
        $plan_rows = $this->db->select('size_id, qty AS plan_qty')
            ->from('wo_sizes')
            ->where('wo_l1_id', $wo_l1_id)
            ->get()->result_array();
        $plan_map = [];
        foreach ($plan_rows as $pr) $plan_map[(int)$pr['size_id']] = (int)$pr['plan_qty'];

        $incomplete   = false;
        $total_input  = 0;
        $sz_batch     = [];

        foreach ($size_ids as $i => $sid) {
            $sid  = (int)$sid;
            if ($sid <= 0) continue;

            $plan = (int)($plan_map[$sid] ?? 0);
            $inp  = max(0, (int)($input_qtys[$i] ?? 0));
            $st   = (string)($statuses[$i] ?? 'ok');
            $shrt = max(0, (int)($short_qtys[$i] ?? 0));

            if ($inp < $plan) {
                $need = $plan - $inp;
                if ($shrt < $need) {
                    $this->session->set_flashdata(
                        'flash_error',
                        "Qty kurang untuk size_id {$sid}. Wajib isi short_qty ≥ {$need} (plan {$plan}, input {$inp})."
                    );
                    return redirect('production/create/' . $ro_id);
                }
                $incomplete = true;
            }

            $total_input += $inp;
            $sz_batch[] = [
                'size_id'     => $sid,
                'plan_qty'    => $plan,
                'input_qty'   => $inp,
                'status_size' => in_array($st, ['belum_diproduksi', 'defect_bisa_diperbaiki', 'defect_tidak_bisa_diperbaiki', 'ok']) ? $st : 'ok',
                'short_qty'   => $shrt
            ];
        }

        // Validasi total header vs sum input (INT)
        $header_total = (int)$this->input->post('total_qty');
        if ($header_total !== $total_input) {
            $this->session->set_flashdata('flash_error', 'Total qty header tidak sama dengan total input per size.');
            return redirect('production/create/' . $ro_id);
        }

        $ro_new_status = $incomplete ? 'belum lengkap' : 'sudah lengkap';

        $this->db->trans_begin();
        try {
            // Insert production header
            $prod_id = $this->gm->insert_and_get_id($this->prod_hdr, [
                'prod_code'      => $prod_code,
                'ro_id'          => $ro_id,
                'wo_l1_id'       => $wo_l1_id,
                'wo_l2_id'       => $wo_l2_id,
                'brand_id'       => $brand_id,
                'departement_id' => (int)$this->input->post('departement_id'),
                'art_color'      => trim((string)$this->input->post('art_color')),
                'total_qty'      => $total_input,
                'status_prod'    => 'confirmed',
                'notes'          => trim((string)$this->input->post('notes')),
                'created_at'     => date('Y-m-d H:i:s')
            ]);
            if (!$prod_id) throw new Exception('Gagal simpan production_hdr');

            // Insert production_sizes (pakai plan dari DB)
            foreach ($sz_batch as &$r) $r['production_id'] = (int)$prod_id;
            unset($r);
            if (!empty($sz_batch)) {
                if (!$this->gm->insert_multiple_data($this->prod_sz, $sz_batch)) {
                    throw new Exception('Gagal simpan production_sizes');
                }
            }

            // Update status RO
            if (!$this->gm->update_data($this->ro_hdr, [
                'status_ro'  => $ro_new_status,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $ro_id])) {
                throw new Exception('Gagal update status RO');
            }

            // Auto-create CHECK-IN utk hasil produksi (SFG WO L2)
            // Auto-create CHECK-IN utk hasil produksi (SFG WO L2)
            $w2 = $this->gm->get_row_where($this->wo_l2, ['id' => $wo_l2_id]);
            $sfg_item_id = (int)($w2['item_id'] ?? 0);
            if ($sfg_item_id <= 0) throw new Exception('SFG item tidak ditemukan.');

            // 1) generate saran no_str (sesuai fungsi lama)
            $no_str_suggest = $this->_generate_str_number();
            // 2) pastikan unik (cek DB; bump kalau sudah ada)
            $no_str = $this->_ensure_unique_no_str($no_str_suggest);

            // Siapkan payload header
            $hdr_payload = [
                'no_str'       => $no_str,
                'no_sj'        => $prod_code,
                'no_wo'        => $this->input->post('no_wo') ?: ($this->gm->get_row_where($this->wo_l1, ['id' => $wo_l1_id])['no_wo'] ?? ''),
                'arrival_date' => date('Y-m-d'),
                'notes'        => 'Auto from PRODUCTION ' . $prod_code,
                'created_at'   => date('Y-m-d H:i:s')
            ];

            // 3) insert header dengan retry jika (sangat jarang) tetap bentrok unique
            $tries = 0;
            $str_hdr_id = null;
            do {
                $hdr_ok = $this->gm->insert_data($this->checkin_hdr, $hdr_payload);
                if ($hdr_ok) {
                    $str_hdr_id = $this->db->insert_id();
                    break;
                }
                $err = $this->db->error();
                if ((int)($err['code'] ?? 0) === 1062) { // duplicate key
                    // bump lagi dan coba ulang
                    $hdr_payload['no_str'] = $no_str = $this->_bump_code_suffix($hdr_payload['no_str']);
                    $tries++;
                    continue;
                }
                // error lain → lempar
                throw new Exception('Gagal membuat checkin header: ' . ($err['message'] ?? 'db error'));
            } while ($tries < 5);

            if (!$str_hdr_id) {
                throw new Exception('Gagal membuat checkin header (retry habis).');
            }

            // detail utama (INT)
            $det_ok = $this->gm->insert_data($this->checkin_det, [
                'hdr_id'     => (int)$str_hdr_id,
                'wo_l1_id'   => $wo_l1_id,
                'item_id'    => $sfg_item_id,
                'qty_in'     => (int)$total_input,
                'qty_out'    => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            if (!$det_ok) throw new Exception('Gagal membuat checkin detail');
            $str_det_id = $this->db->insert_id();

            // breakdown size (kalau tabelnya ada) — qty integer
            if ($this->db->table_exists($this->checkin_det_sizes)) {
                $ins_sz = [];
                foreach ($sz_batch as $s) {
                    $ins_sz[] = [
                        'det_id'  => (int)$str_det_id,
                        'size_id' => (int)$s['size_id'],
                        'qty'     => (int)$s['input_qty']
                    ];
                }
                if (!empty($ins_sz)) {
                    if (!$this->gm->insert_multiple_data($this->checkin_det_sizes, $ins_sz)) {
                        throw new Exception('Gagal membuat checkin size breakdown');
                    }
                }
            }

            $this->db->trans_commit();
            $this->session->set_flashdata('message', 'Production report tersimpan. RO diupdate ke: ' . $ro_new_status . ' & STR dibuat: ' . $no_str);
            redirect('production');
        } catch (\Throwable $e) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('flash_error', 'Simpan gagal: ' . $e->getMessage());
            redirect('production/create/' . $ro_id);
        }
    }

    /**
     * Naikkan angka di ujung kode: STR-202509-0001 -> STR-202509-0002
     */
    private function _bump_code_suffix(string $code): string
    {
        if (preg_match('/^(.*-)(\d+)$/', $code, $m)) {
            $pad = strlen($m[2]);
            $next = str_pad((string)(((int)$m[2]) + 1), $pad, '0', STR_PAD_LEFT);
            return $m[1] . $next;
        }
        // fallback kalau format tak terdeteksi
        return $code . '-0001';
    }

    /**
     * Pastikan no_str unik. Mulai dari $suggest (atau generate baru),
     * cek ke DB, kalau ada → bump, ulangi sampai unik.
     */
    private function _ensure_unique_no_str(?string $suggest = null): string
    {
        $no_str = $suggest ?: $this->_generate_str_number();
        // batasi loop agar aman
        for ($i = 0; $i < 20; $i++) {
            $dupe = $this->db->select('id')->from($this->checkin_hdr)
                ->where('no_str', $no_str)->limit(1)->get()->row_array();
            if (!$dupe) return $no_str;
            $no_str = $this->_bump_code_suffix($no_str);
        }
        // andai 20x tetap tabrakan, paksa waktu unik
        return $no_str . '-' . date('His');
    }

    /* ================= SHOW / DELETE (opsional) ================= */
    public function show($id)
    {
        if (!ctype_digit((string)$id)) show_404();

        $hdr = $this->gm->get_row_where($this->prod_hdr, ['id' => (int)$id]);
        if (!$hdr) show_404();

        // Ambil referensi RO, WO, brand, departement, item SFG
        $ro    = $this->gm->get_row_where($this->ro_hdr, ['id' => (int)$hdr['ro_id']]);
        $w1    = $this->gm->get_row_where($this->wo_l1,  ['id' => (int)$hdr['wo_l1_id']]);
        $w2    = $this->gm->get_row_where($this->wo_l2,  ['id' => (int)$hdr['wo_l2_id']]);
        $brand = $this->gm->get_row_where($this->brands, ['id' => (int)$hdr['brand_id']]);
        $dept  = $this->gm->get_row_where($this->deps, ['id' => (int)$hdr['departement_id']]);

        $sfg_item = null;
        if ($w2) $sfg_item = $this->gm->get_row_where($this->items, ['id' => (int)$w2['item_id']]);

        // Detail sizes
        $sql = "
      SELECT sz.*, bs.size_name
      FROM {$this->prod_sz} sz
      LEFT JOIN {$this->brand_sizes} bs ON bs.id = sz.size_id
      WHERE sz.production_id = " . (int)$id . "
      ORDER BY sz.id ASC
    ";
        $details = $this->gm->custom_query($sql);

        $data = [
            'title'   => 'Production Report Detail',
            'hdr'     => $hdr,
            'ro'      => $ro,
            'wo_no'   => $w1['no_wo'] ?? '',
            'brand'   => $brand['name'] ?? '',
            'dept'    => $dept['name'] ?? '',
            'sfg'     => $sfg_item ? (($sfg_item['item_code'] ?? '') . ' - ' . ($sfg_item['item_name'] ?? '')) : '',
            'details' => $details,
        ];
        $this->_render('production/show', $data);
    }

    public function pdf($id)
    {
        if (!ctype_digit((string)$id)) show_404();

        $hdr = $this->gm->get_row_where($this->prod_hdr, ['id' => (int)$id]);
        if (!$hdr) show_404();

        // Join2 referensi (sama seperti show)
        $ro    = $this->gm->get_row_where($this->ro_hdr, ['id' => (int)$hdr['ro_id']]);
        $w1    = $this->gm->get_row_where($this->wo_l1,  ['id' => (int)$hdr['wo_l1_id']]);
        $w2    = $this->gm->get_row_where($this->wo_l2,  ['id' => (int)$hdr['wo_l2_id']]);
        $brand = $this->gm->get_row_where($this->brands, ['id' => (int)$hdr['brand_id']]);
        $dept  = $this->gm->get_row_where($this->deps, ['id' => (int)$hdr['departement_id']]);
        $sfg_item = $w2 ? $this->gm->get_row_where($this->items, ['id' => (int)$w2['item_id']]) : null;

        $sql = "
      SELECT sz.*, bs.size_name
      FROM {$this->prod_sz} sz
      LEFT JOIN {$this->brand_sizes} bs ON bs.id = sz.size_id
      WHERE sz.production_id = " . (int)$id . "
      ORDER BY sz.id ASC
    ";
        $details = $this->gm->custom_query($sql);

        $html = $this->load->view('production/pdf', [
            'hdr'   => $hdr,
            'ro'    => $ro,
            'wo_no' => $w1['no_wo'] ?? '',
            'brand' => $brand['name'] ?? '',
            'dept'  => $dept['name'] ?? '',
            'sfg'   => $sfg_item ? (($sfg_item['item_code'] ?? '') . ' - ' . ($sfg_item['item_name'] ?? '')) : '',
            'details' => $details,
        ], true);

        // Dompdf langsung (tanpa wrapper)
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('PROD_' . $hdr['prod_code'] . '.pdf', ['Attachment' => false]);
    }

    public function edit_by_ro($ro_id)
    {
        if (!ctype_digit((string)$ro_id)) show_404();

        $hdr_ro = $this->gm->get_row_where($this->ro_hdr, ['id' => (int)$ro_id]);
        if (!$hdr_ro) {
            $this->session->set_flashdata('flash_error', 'RO tidak ditemukan.');
            return redirect('production/ro_list');
        }

        // Ambil production terakhir utk RO ini
        $prod = $this->_get_latest_production_by_ro((int)$ro_id);
        if (!$prod) {
            // kalau belum ada, lempar ke create agar UX konsisten
            return redirect('production/create/' . $ro_id);
        }
        $prod_id = (int)$prod['id'];

        // referensi WO/Brand/Dept/SFG sama seperti di create()
        $w1    = $this->gm->get_row_where($this->wo_l1, ['id' => (int)$hdr_ro['wo_l1_id']]);
        $w2    = $this->gm->get_row_where($this->wo_l2, ['id' => (int)$hdr_ro['wo_l2_id']]);
        $sfg   = $w2 ? $this->gm->get_row_where($this->items, ['id' => (int)$w2['item_id']]) : null;
        $brand = $this->gm->get_row_where($this->brands, ['id' => (int)$hdr_ro['brand_id']]);
        $dep   = $this->gm->get_row_where($this->deps, ['id' => (int)$hdr_ro['departement_id']]);

        // daftar size brand
        $sizes_from_wo = $this->db->select('ws.size_id, ws.qty AS plan_qty, bs.size_name')
            ->from('wo_sizes ws')
            ->join('brand_sizes bs', 'bs.id = ws.size_id', 'left') // Use join() with 'left' for left join
            ->where('ws.wo_l1_id', (int)$hdr_ro['wo_l1_id'])
            ->order_by('ws.size_id', 'ASC')
            ->get()->result_array();

        // 2) Combine size data from wo_sizes with size_name and qty (plan_qty)
        $sizes = [];
        foreach ($sizes_from_wo as $size) {
            $sizes[] = [
                'id'        => (int)$size['size_id'],
                'size_name' => $size['size_name'],
                'plan_qty'  => (int)$size['plan_qty']
            ];
        }

        // plan per size dari wo_sizes
        $plan_rows = $this->db->select('size_id, qty AS plan_qty')
            ->from('wo_sizes')->where('wo_l1_id', (int)$hdr_ro['wo_l1_id'])
            ->get()->result_array();
        $plan_map = [];
        foreach ($plan_rows as $r) $plan_map[(int)$r['size_id']] = (int)$r['plan_qty'];

        // reported_before: akumulasi input_qty yang sudah tercatat di production_sizes (semua produksi untuk RO ini)
        $rep_rows = $this->db->select('sz.size_id, SUM(sz.input_qty) AS reported_qty', false)
            ->from($this->prod_sz . ' sz')
            ->join($this->prod_hdr . ' ph', 'ph.id = sz.production_id', 'left')
            ->where('ph.ro_id', (int)$ro_id)
            ->group_by('sz.size_id')->get()->result_array();
        $reported_map = [];
        foreach ($rep_rows as $r) $reported_map[(int)$r['size_id']] = (int)$r['reported_qty'];

        $data = [
            'title'             => 'Update Production Report',
            'is_update'         => true,
            'prod_id'           => $prod_id,
            'prod_code_suggest' => $this->_generate_prod_code(), // boleh generate baru utk dok baru
            'ro'            => $hdr_ro,
            'wo_no'         => $w1['no_wo'] ?? '',
            'sfg_label'     => trim(($sfg['item_code'] ?? '') . ' - ' . ($sfg['item_name'] ?? '')),
            'brand_name'    => $brand['name'] ?? '',
            'departement'   => $dep['name'] ?? '',
            'sizes'         => $sizes,
            'plan_map'      => $plan_map,
            'reported_map'  => $reported_map, // NEW → dipakai di view
        ];
        $this->_render('production/create', $data); // reuse view
    }

    public function update_store($prod_id)
    {
        if (!ctype_digit((string)$prod_id)) show_404();

        $this->form_validation->set_rules('ro_id', 'RO', 'required|integer');
        $this->form_validation->set_rules('wo_l1_id', 'WO', 'required|integer');
        $this->form_validation->set_rules('wo_l2_id', 'SFG (WO L2)', 'required|integer');
        $this->form_validation->set_rules('brand_id', 'Brand', 'required|integer');
        $this->form_validation->set_rules('departement_id', 'Departement', 'required|integer');
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('flash_error', validation_errors());
            return redirect('production/ro_list');
        }

        $ro_id    = (int)$this->input->post('ro_id');
        $wo_l1_id = (int)$this->input->post('wo_l1_id');
        $wo_l2_id = (int)$this->input->post('wo_l2_id');
        $brand_id = (int)$this->input->post('brand_id');

        $ro = $this->gm->get_row_where($this->ro_hdr, ['id' => $ro_id]);
        if (!$ro || !in_array(($ro['status_ro'] ?? ''), ['submitted', 'belum lengkap'], true)) {
            $this->session->set_flashdata('flash_error', 'RO tidak valid untuk update report.');
            return redirect('production/ro_list');
        }

        $size_ids   = (array)$this->input->post('size_id');
        $input_new  = (array)$this->input->post('input_qty');   // input baru
        $statuses   = (array)$this->input->post('status_size');
        $short_post = (array)$this->input->post('short_qty');   // otomatis dari UI, TETAP validasi server

        // PLAN
        $plan_rows = $this->db->select('size_id, qty AS plan_qty')
            ->from('wo_sizes')->where('wo_l1_id', $wo_l1_id)->get()->result_array();
        $plan_map = [];
        foreach ($plan_rows as $r) $plan_map[(int)$r['size_id']] = (int)$r['plan_qty'];

        // REPORTED BEFORE (akumulasi semua produksi utk RO ini)
        $rep_rows = $this->db->select('sz.size_id, SUM(sz.input_qty) AS reported_qty', false)
            ->from($this->prod_sz . ' sz')
            ->join($this->prod_hdr . ' ph', 'ph.id = sz.production_id', 'left')
            ->where('ph.ro_id', (int)$ro_id)
            ->group_by('sz.size_id')->get()->result_array();
        $reported_map = [];
        foreach ($rep_rows as $r) $reported_map[(int)$r['size_id']] = (int)$r['reported_qty'];

        $incomplete  = false;
        $sum_new     = 0;
        $sz_batch    = [];

        foreach ($size_ids as $i => $sid) {
            $sid = (int)$sid;
            if ($sid <= 0) continue;
            $plan = (int)($plan_map[$sid] ?? 0);
            $prev = (int)($reported_map[$sid] ?? 0);
            $add  = max(0, (int)($input_new[$i] ?? 0));     // tambahan
            $st   = (string)($statuses[$i] ?? 'ok');
            $shrt = max(0, (int)($short_post[$i] ?? 0));

            $cum  = $prev + $add;
            if ($cum < $plan) {
                $need = $plan - $cum;
                if ($shrt < $need) {
                    $this->session->set_flashdata(
                        'flash_error',
                        "Qty kurang untuk size_id {$sid}. Wajib isi short_qty ≥ {$need} (plan {$plan}, reported {$prev}, input {$add})."
                    );
                    return redirect('production/edit_by_ro/' . $ro_id);
                }
                $incomplete = true;
            }

            $sum_new += $add;

            // pada update, kita catat baris baru (append) agar jejak history jelas
            $sz_batch[] = [
                'size_id'     => $sid,
                'plan_qty'    => $plan,     // simpan plan referensi
                'input_qty'   => $add,      // hanya tambahan
                'status_size' => in_array($st, ['belum_diproduksi', 'defect_bisa_diperbaiki', 'defect_tidak_bisa_diperbaiki', 'ok']) ? $st : 'ok',
                'short_qty'   => $shrt
            ];
        }

        $ro_new_status = $incomplete ? 'belum lengkap' : 'sudah lengkap';

        $this->db->trans_begin();
        try {
            // Header production: buat DOK BARU untuk batch update ini (biar STR & history rapi)
            $prod_code = trim((string)$this->input->post('prod_code')) ?: $this->_generate_prod_code();
            $prod_id_new = $this->gm->insert_and_get_id($this->prod_hdr, [
                'prod_code'      => $prod_code,
                'ro_id'          => $ro_id,
                'wo_l1_id'       => $wo_l1_id,
                'wo_l2_id'       => $wo_l2_id,
                'brand_id'       => $brand_id,
                'departement_id' => (int)$this->input->post('departement_id'),
                'art_color'      => trim((string)$this->input->post('art_color')),
                'total_qty'      => (int)$sum_new,
                'status_prod'    => 'confirmed',
                'notes'          => 'Update production',
                'created_at'     => date('Y-m-d H:i:s')
            ]);
            if (!$prod_id_new) throw new Exception('Gagal membuat dok produksi (update).');

            foreach ($sz_batch as &$r) $r['production_id'] = (int)$prod_id_new;
            unset($r);
            if (!empty($sz_batch)) {
                if (!$this->gm->insert_multiple_data($this->prod_sz, $sz_batch)) {
                    throw new Exception('Gagal simpan detail size (update).');
                }
            }

            // Update status RO
            if (!$this->gm->update_data($this->ro_hdr, [
                'status_ro'  => $ro_new_status,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $ro_id])) {
                throw new Exception('Gagal update status RO.');
            }

            // Auto STR utk tambahan qty
            if ($sum_new > 0) {
                $w2 = $this->gm->get_row_where($this->wo_l2, ['id' => $wo_l2_id]);
                $sfg_item_id = (int)($w2['item_id'] ?? 0);
                if ($sfg_item_id <= 0) throw new Exception('SFG item tidak ditemukan.');

                $no_str = $this->_ensure_unique_no_str($this->_generate_str_number());

                $ok_hdr = $this->gm->insert_data($this->checkin_hdr, [
                    'no_str'       => $no_str,
                    'no_sj'        => $prod_code,
                    'no_wo'        => $this->gm->get_row_where($this->wo_l1, ['id' => $wo_l1_id])['no_wo'] ?? '',
                    'arrival_date' => date('Y-m-d'),
                    'notes'        => 'Auto from PRODUCTION ' . $prod_code . ' (update)',
                    'created_at'   => date('Y-m-d H:i:s')
                ]);
                if (!$ok_hdr) throw new Exception('Gagal STR header(update).');
                $str_hdr_id = $this->db->insert_id();

                $ok_det = $this->gm->insert_data($this->checkin_det, [
                    'hdr_id'     => (int)$str_hdr_id,
                    'wo_l1_id'   => $wo_l1_id,
                    'item_id'    => $sfg_item_id,
                    'qty_in'     => (int)$sum_new,
                    'qty_out'    => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                if (!$ok_det) throw new Exception('Gagal STR detail(update).');
                $str_det_id = $this->db->insert_id();

                if ($this->db->table_exists($this->checkin_det_sizes)) {
                    $ins_sz = [];
                    foreach ($sz_batch as $s) {
                        $ins_sz[] = [
                            'det_id'  => (int)$str_det_id,
                            'size_id' => (int)$s['size_id'],
                            'qty'     => (int)$s['input_qty']
                        ];
                    }
                    if (!empty($ins_sz)) {
                        if (!$this->gm->insert_multiple_data($this->checkin_det_sizes, $ins_sz)) {
                            throw new Exception('Gagal STR size(update).');
                        }
                    }
                }
            }

            $this->db->trans_commit();
            $this->session->set_flashdata('message', 'Update production tersimpan. RO → ' . $ro_new_status);
            return redirect('production');
        } catch (\Throwable $e) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('flash_error', 'Gagal update: ' . $e->getMessage());
            return redirect('production/edit_by_ro/' . $ro_id);
        }
    }


    public function delete($id)
    {
        if (!ctype_digit((string)$id)) show_404();

        $this->db->trans_begin();
        $this->gm->delete_data($this->prod_sz, ['production_id' => (int)$id]);
        $this->gm->delete_data($this->prod_hdr, ['id' => (int)$id]);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('flash_error', 'Gagal menghapus production');
        } else {
            $this->session->set_flashdata('message', 'Production dihapus.');
        }
        redirect('production');
    }

    /* ============ shared render ============ */
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
