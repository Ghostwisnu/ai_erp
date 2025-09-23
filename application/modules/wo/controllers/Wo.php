<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Work Order Controller
 * - Buat WO dari BOM
 * - Size Run per Brand (brand_sizes)
 * - Kalkulasi required_qty = consumption × total_size_qty
 * - Export PDF per WO (Dompdf)
 */
class Wo extends MX_Controller
{
    // ---- Tabel BOM (referensi) ----
    private $bom_l1 = 'bom_l1'; // id, item_id, unit_id, brand_id, art_color, ...
    private $bom_l2 = 'bom_l2'; // id, bom_l1_id, item_id(SFG)
    private $bom_l3 = 'bom_l3'; // id, bom_l2_id, item_id(material), consumption

    // ---- Tabel WO (target) ----
    private $wo_l1 = 'wo_l1';     // id, bom_l1_id, item_id, unit_id, brand_id, art_color, date_order, x_factory_date, total_size_qty, notes, created_at
    private $wo_l2 = 'wo_l2';     // id, wo_l1_id, item_id(SFG)
    private $wo_l3 = 'wo_l3';     // id, wo_l2_id, item_id(material), consumption, required_qty
    private $wo_sz = 'wo_sizes';  // id, wo_l1_id, size_id, qty

    public function __construct()
    {
        parent::__construct();
        $this->_check_login();
        $this->load->model('Generic_model', 'gm');
        $this->load->library('form_validation');
    }

    private function _check_login()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
    }

    /* ==========================
     * INDEX: List WO
     * ========================== */
    public function index()
    {
        $data['title'] = 'Work Orders';

        // Ambil kata kunci pencarian jika ada
        $search = $this->input->get('search', TRUE);

        // Pagination Configuration
        $config['base_url'] = site_url('wo/index');
        $config['total_rows'] = $this->_get_search_count($search);
        $config['per_page'] = 10;  // Set jumlah data per halaman
        $config['uri_segment'] = 3;
        $this->pagination->initialize($config);

        // Ambil data dengan pagination
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $data['wo_list'] = $this->_get_search_results($search, $config['per_page'], $page);

        // Pass pagination links to the view
        $data['pagination'] = $this->pagination->create_links();

        $this->_render('wo/list', $data);
    }

    // Method untuk mengambil data berdasarkan pencarian dan pagination
    private function _get_search_results($search = '', $limit = 10, $start = 0)
    {
        $this->db->select('
        w1.id,
        w1.no_wo,
        w1.bom_l1_id,
        w1.date_order,
        w1.x_factory_date,
        w1.total_size_qty,
        i.item_code AS item_code,
        i.item_name AS item_name,
        b.name AS brand_name,
        u.name AS unit_name,
        w1.created_at,
        w1.kategori_wo 
    ');
        $this->db->from($this->wo_l1 . ' w1');
        $this->db->join('items i', 'i.id = w1.item_id', 'left');
        $this->db->join('brands b', 'b.id = w1.brand_id', 'left');
        $this->db->join('units u', 'u.id = w1.unit_id', 'left');

        if ($search) {
            $this->db->like('i.item_code', $search);
            $this->db->or_like('i.item_name', $search);
            $this->db->or_like('b.name', $search);
        }

        $kategori_wo = $this->input->get('kategori_wo');
        if ($kategori_wo) {
            $this->db->where('w1.kategori_wo', $kategori_wo);  // Filter berdasarkan kategori_wo
        }

        $this->db->limit($limit, $start);
        $this->db->order_by('w1.id', 'DESC');

        return $this->db->get()->result_array();
    }

    // Method untuk menghitung jumlah total berdasarkan pencarian
    private function _get_search_count($search = '')
    {
        $this->db->select('w1.id');
        $this->db->from($this->wo_l1 . ' w1');
        $this->db->join('items i', 'i.id = w1.item_id', 'left');
        $this->db->join('brands b', 'b.id = w1.brand_id', 'left');
        $this->db->join('units u', 'u.id = w1.unit_id', 'left');

        if ($search) {
            $this->db->like('i.item_code', $search);
            $this->db->or_like('i.item_name', $search);
            $this->db->or_like('b.name', $search);
        }

        return $this->db->count_all_results();
    }

    /* ==========================
     * CREATE: Form WO baru
     * ========================== */
    public function create()
    {
        // Dropdown BOM header
        $this->db->select('
            l1.id  AS bom_l1_id,
            i.id   AS item_id,
            i.item_code,
            i.item_name,
            b.id   AS brand_id,
            b.name AS brand_name,
            u.id   AS unit_id,
            u.name AS unit_name,
            l1.art_color
        ');
        $this->db->from($this->bom_l1 . ' l1');
        $this->db->join('items i', 'i.id = l1.item_id', 'left');
        $this->db->join('brands b', 'b.id = l1.brand_id', 'left');
        $this->db->join('units u', 'u.id = l1.unit_id', 'left');
        $this->db->order_by('l1.id', 'DESC');
        $bom_choices = $this->db->get()->result_array();

        $data = [
            'title'       => 'Buat Work Order',
            'bom_choices' => $bom_choices,
        ];

        $this->_render('wo/create', $data);
    }

    /* ==========================================================
     * AJAX: Ambil detail BOM tree (L1→L2→L3) untuk bom_l1_id
     * ========================================================== */
    public function get_bom_tree($bom_l1_id)
    {
        if (!ctype_digit((string)$bom_l1_id)) {
            echo json_encode(['ok' => false, 'message' => 'Invalid BOM ID', 'data' => null]);
            return;
        }

        $l1 = $this->gm->get_row_where($this->bom_l1, ['id' => (int)$bom_l1_id]);
        if (!$l1) {
            echo json_encode(['ok' => false, 'message' => 'BOM not found', 'data' => null]);
            return;
        }

        // Header FG
        $this->db->select('i.item_code, i.item_name, b.name AS brand_name, u.name AS unit_name');
        $this->db->from('items i');
        $this->db->join('brands b', 'b.id = i.brand_id', 'left');
        $this->db->join('units u', 'u.id = i.unit_id', 'left');
        $this->db->where('i.id', (int)$l1['item_id']);
        $info = $this->db->get()->row_array();

        // Semua L2
        $l2_rows = $this->db->where('bom_l1_id', (int)$bom_l1_id)->get($this->bom_l2)->result_array();

        // Map nama item (SFG + material)
        $itemIds = [];
        foreach ($l2_rows as $l2) {
            $itemIds[] = (int)$l2['item_id']; // SFG
            $mats = $this->db->where('bom_l2_id', (int)$l2['id'])->get($this->bom_l3)->result_array();
            foreach ($mats as $m) $itemIds[] = (int)$m['item_id']; // material
        }
        $itemIds = array_values(array_unique(array_filter($itemIds)));

        $nameMap = [];
        if ($itemIds) {
            $this->db->where_in('id', $itemIds);
            foreach ($this->db->get('items')->result_array() as $it) {
                $nameMap[(int)$it['id']] = $it['item_name'];
            }
        }

        $tree = [
            'l1' => [
                'bom_l1'     => $l1, // berisi brand_id, unit_id, art_color, dst
                'item_code'  => $info['item_code'] ?? '',
                'item_name'  => $info['item_name'] ?? '',
                'brand_name' => $info['brand_name'] ?? '',
                'unit_name'  => $info['unit_name'] ?? '',
            ],
            'l2' => []
        ];

        foreach ($l2_rows as $l2) {
            $mats = $this->db->where('bom_l2_id', (int)$l2['id'])->get($this->bom_l3)->result_array();
            $list = [];
            foreach ($mats as $m) {
                $list[] = [
                    'item_id'     => (int)$m['item_id'],
                    'item_name'   => $nameMap[(int)$m['item_id']] ?? ('ID:' . $m['item_id']),
                    'consumption' => (float)$m['consumption'],
                ];
            }
            $tree['l2'][] = [
                'item_id'   => (int)$l2['item_id'],
                'item_name' => $nameMap[(int)$l2['item_id']] ?? ('ID:' . $l2['item_id']),
                'materials' => $list,
            ];
        }

        echo json_encode(['ok' => true, 'message' => 'OK', 'data' => $tree]);
    }

    /* ==========================================================
     * AJAX: Ambil daftar size berdasar brand (brand_sizes)
     * ========================================================== */
    public function get_sizes_by_brand($brand_id)
    {
        if (!ctype_digit((string)$brand_id)) {
            echo json_encode(['ok' => false, 'sizes' => []]);
            return;
        }

        // Pakai brand_sizes (id, brand_id, size_name, note)
        $sizes = $this->gm->get_where('brand_sizes', ['brand_id' => (int)$brand_id]);

        echo json_encode(['ok' => true, 'sizes' => $sizes]);
    }

    /* ==========================================
     * STORE: Simpan WO baru berdasarkan BOM
     * ========================================== */
    public function store()
    {
        $this->form_validation->set_rules('kategori_wo', 'Kategori WO', 'required|in_list[Injection,Cementing,Stitchdown]');
        $this->form_validation->set_rules('bom_l1_id', 'BOM', 'required|trim|integer');
        $this->form_validation->set_rules('date_order', 'Date Order', 'required|trim');
        $this->form_validation->set_rules('x_factory_date', 'X-Factory Date', 'required|trim');
        $this->form_validation->set_rules('no_wo', 'No WO', 'required|trim'); // Added validation for No WO

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', validation_errors());
            return redirect('wo/create');
        }

        $kategori_wo = $this->input->post('kategori_wo');

        $bom_l1_id = (int)$this->input->post('bom_l1_id');
        $l1 = $this->gm->get_row_where($this->bom_l1, ['id' => $bom_l1_id]);
        if (!$l1) {
            $this->session->set_flashdata('error', 'BOM tidak ditemukan.');
            return redirect('wo/create');
        }

        $l2_rows = $this->db->where('bom_l1_id', $bom_l1_id)->get($this->bom_l2)->result_array();

        $sizes = $this->input->post('sizes');
        $total_size_qty = 0.0;
        if (is_array($sizes)) {
            foreach ($sizes as $s) {
                $q = isset($s['qty']) ? (float)$s['qty'] : 0.0;
                if ($q > 0) $total_size_qty += $q;
            }
        }

        $notes = trim((string)$this->input->post('notes'));
        $no_wo = trim((string)$this->input->post('no_wo')); // Capture No WO

        // Transaksi strict
        $this->db->trans_strict(true);
        $this->db->trans_start();

        // Insert WO L1
        $wo_l1_id = $this->gm->insert_data($this->wo_l1, [
            'bom_l1_id'      => $bom_l1_id,
            'item_id'        => (int)$l1['item_id'],
            'unit_id'        => (int)$l1['unit_id'],
            'brand_id'       => (int)$l1['brand_id'],
            'art_color'      => $l1['art_color'],
            'date_order'     => $this->input->post('date_order'),
            'x_factory_date' => $this->input->post('x_factory_date'),
            'total_size_qty' => $total_size_qty,
            'notes'          => $notes,
            'no_wo'          => $no_wo, // Store No WO
            'kategori_wo'    => $kategori_wo,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
        if (!$wo_l1_id || !is_numeric($wo_l1_id)) $wo_l1_id = $this->db->insert_id();

        // Insert size-run
        if (is_array($sizes)) {
            foreach ($sizes as $s) {
                $size_id = isset($s['size_id']) ? (int)$s['size_id'] : 0;
                $qty     = isset($s['qty']) ? (float)$s['qty'] : 0.0;
                if ($size_id > 0 && $qty > 0) {
                    $this->gm->insert_data($this->wo_sz, [
                        'wo_l1_id' => (int)$wo_l1_id,
                        'size_id'  => $size_id, // brand_sizes.id
                        'qty'      => $qty,
                    ]);
                }
            }
        }

        // Insert L2 & L3 (kalkulasi required_qty = cons × total_size_qty)
        foreach ($l2_rows as $l2) {
            $wo_l2_id = $this->gm->insert_data($this->wo_l2, [
                'wo_l1_id' => (int)$wo_l1_id,
                'item_id'  => (int)$l2['item_id'],
            ]);
            if (!$wo_l2_id || !is_numeric($wo_l2_id)) $wo_l2_id = $this->db->insert_id();

            $mats = $this->db->where('bom_l2_id', (int)$l2['id'])->get($this->bom_l3)->result_array();
            foreach ($mats as $m) {
                $consumption = (float)$m['consumption'];
                $required    = $consumption * (float)$total_size_qty;

                $this->gm->insert_data($this->wo_l3, [
                    'wo_l2_id'     => (int)$wo_l2_id,
                    'item_id'      => (int)$m['item_id'],
                    'consumption'  => $consumption,
                    'required_qty' => $required,
                ]);
            }
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Gagal menyimpan Work Order.');
            return redirect('wo/create');
        }

        $this->session->set_flashdata('message', 'Work Order berhasil dibuat.');
        redirect('wo');
    }


    /* ==========================
     * EDIT: Form WO existing
     * ========================== */
    public function edit($id)
    {
        if (!ctype_digit((string)$id)) show_404();

        $w1 = $this->gm->get_row_where($this->wo_l1, ['id' => (int)$id]);
        if (!$w1) show_404();

        // Header FG (read-only)
        $this->db->select('i.item_code, i.item_name, b.name AS brand_name, u.name AS unit_name');
        $this->db->from('items i');
        $this->db->join('brands b', 'b.id = i.brand_id', 'left');
        $this->db->join('units u', 'u.id = i.unit_id', 'left');
        $this->db->where('i.id', (int)$w1['item_id']);
        $info = $this->db->get()->row_array();

        // L2 (SFG) & L3 (material) from WO tables
        $l2_rows = $this->db
            ->select('w2.id, w2.item_id, it.item_name')
            ->from($this->wo_l2 . ' w2')
            ->join('items it', 'it.id = w2.item_id', 'left')
            ->where('w2.wo_l1_id', (int)$id)->get()->result_array();

        $l2_list = [];
        foreach ($l2_rows as $l2) {
            $mats = $this->db
                ->select('w3.id, w3.item_id, it.item_name, w3.consumption, w3.required_qty')
                ->from($this->wo_l3 . ' w3')
                ->join('items it', 'it.id = w3.item_id', 'left')
                ->where('w3.wo_l2_id', (int)$l2['id'])->get()->result_array();

            $l2_list[] = [
                'id'         => (int)$l2['id'],
                'item_id'    => (int)$l2['item_id'],
                'item_name'  => $l2['item_name'],
                'materials'  => $mats
            ];
        }

        // Ambil sizes dari brand_sizes
        $sizes_all = $this->gm->get_where('brand_sizes', ['brand_id' => (int)$w1['brand_id']]);

        // Existing size qty
        $sz_rows = $this->db->where('wo_l1_id', (int)$id)->get($this->wo_sz)->result_array();
        $qty_map = [];
        foreach ($sz_rows as $s) $qty_map[(int)$s['size_id']] = (float)$s['qty'];

        $data = [
            'title'   => 'Edit Work Order',
            'w1'      => $w1,
            'header'  => [
                'item_code'  => $info['item_code'] ?? '',
                'item_name'  => $info['item_name'] ?? '',
                'brand_name' => $info['brand_name'] ?? '',
                'unit_name'  => $info['unit_name'] ?? '',
            ],
            'l2_list'      => $l2_list,
            'sizes_all'    => $sizes_all,
            'size_qty_map' => $qty_map,
        ];

        $this->_render('wo/edit', $data);
    }

    /* ==========================
     * UPDATE: Simpan perubahan WO
     * ========================== */
    public function update($id)
    {
        if (!ctype_digit((string)$id)) show_404();

        $this->form_validation->set_rules('kategori_wo', 'Kategori WO', 'required|in_list[Injection,Cementing,Stitchdown]');
        $this->form_validation->set_rules('date_order', 'Date Order', 'required|trim');
        $this->form_validation->set_rules('x_factory_date', 'X-Factory Date', 'required|trim');

        if (!$this->form_validation->run()) {
            return $this->edit($id);
        }

        $kategori_wo = $this->input->post('kategori_wo');

        $w1 = $this->gm->get_row_where($this->wo_l1, ['id' => (int)$id]);
        if (!$w1) show_404();

        // Hitung ulang total size qty dari input
        $sizes = $this->input->post('sizes');
        $total_size_qty = 0.0;
        if (is_array($sizes)) {
            foreach ($sizes as $s) {
                $q = isset($s['qty']) ? (float)$s['qty'] : 0.0;
                if ($q > 0) $total_size_qty += $q;
            }
        }

        $notes = trim((string)$this->input->post('notes'));

        $this->db->trans_strict(true);
        $this->db->trans_start();

        // Update header
        $this->gm->update_data($this->wo_l1, [
            'kategori_wo'    => $kategori_wo, // Update kategori_wo
            'date_order'     => $this->input->post('date_order'),
            'x_factory_date' => $this->input->post('x_factory_date'),
            'total_size_qty' => $total_size_qty,
            'notes'          => $notes,
        ], ['id' => (int)$id]);

        // Replace size-run
        $this->gm->delete_data($this->wo_sz, ['wo_l1_id' => (int)$id]);
        if (is_array($sizes)) {
            foreach ($sizes as $s) {
                $sid = isset($s['size_id']) ? (int)$s['size_id'] : 0;
                $qty = isset($s['qty']) ? (float)$s['qty'] : 0.0;
                if ($sid > 0 && $qty > 0) {
                    $this->gm->insert_data($this->wo_sz, [
                        'wo_l1_id' => (int)$id,
                        'size_id'  => $sid, // brand_sizes.id
                        'qty'      => $qty,
                    ]);
                }
            }
        }

        // Recompute required_qty = consumption * total_size_qty untuk semua material WO ini
        $this->db->query("
            UPDATE {$this->wo_l3} w3
            JOIN {$this->wo_l2} w2 ON w3.wo_l2_id = w2.id
            SET w3.required_qty = w3.consumption * ?
            WHERE w2.wo_l1_id = ?
        ", [$total_size_qty, (int)$id]);

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Gagal memperbarui Work Order.');
            return redirect('wo/edit/' . (int)$id);
        }

        $this->session->set_flashdata('message', 'Work Order berhasil diperbarui.');
        redirect('wo');
    }

    /* ==========================
     * (Opsional) DELETE: Hapus WO
     * ========================== */
    public function delete($id)
    {
        if (!ctype_digit((string)$id)) show_404();

        $this->db->trans_strict(true);
        $this->db->trans_start();

        // Hapus detail (L3) via join L2
        $l2_rows = $this->db->where('wo_l1_id', (int)$id)->get($this->wo_l2)->result_array();
        foreach ($l2_rows as $l2) {
            $this->gm->delete_data($this->wo_l3, ['wo_l2_id' => (int)$l2['id']]);
        }
        // Hapus L2
        $this->gm->delete_data($this->wo_l2, ['wo_l1_id' => (int)$id]);
        // Hapus size-run
        $this->gm->delete_data($this->wo_sz, ['wo_l1_id' => (int)$id]);
        // Hapus header
        $this->gm->delete_data($this->wo_l1, ['id' => (int)$id]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Gagal menghapus Work Order.');
        } else {
            $this->session->set_flashdata('message', 'Work Order berhasil dihapus.');
        }
        redirect('wo');
    }

    /* ==========================
     * EXPORT PDF per WO (Dompdf)
     * ========================== */

    /**
     * Kumpulkan data lengkap WO untuk PDF: header FG, size-run, SFG+materials.
     */
    private function _get_wo_payload(int $id)
    {
        $w1 = $this->gm->get_row_where($this->wo_l1, ['id' => $id]);
        if (!$w1) return null;

        // Header FG
        $this->db->select('i.item_code, i.item_name, b.name AS brand_name, u.name AS unit_name');
        $this->db->from('items i');
        $this->db->join('brands b', 'b.id = i.brand_id', 'left');
        $this->db->join('units u', 'u.id = i.unit_id', 'left');
        $this->db->where('i.id', (int)$w1['item_id']);
        $info = $this->db->get()->row_array();

        // Size-run (join ke brand_sizes untuk nama size)
        $sizes = $this->db->select('ws.size_id, ws.qty, bs.size_name, bs.note')
            ->from($this->wo_sz . ' ws')
            ->join('brand_sizes bs', 'bs.id = ws.size_id', 'left')
            ->where('ws.wo_l1_id', (int)$id)
            ->order_by('bs.size_name', 'ASC')
            ->get()->result_array();

        // L2 & L3
        $l2_rows = $this->db
            ->select('w2.id, w2.item_id, it.item_name')
            ->from($this->wo_l2 . ' w2')
            ->join('items it', 'it.id = w2.item_id', 'left')
            ->where('w2.wo_l1_id', (int)$id)
            ->order_by('w2.id', 'ASC')
            ->get()->result_array();

        $l2_list = [];
        foreach ($l2_rows as $l2) {
            $mats = $this->db
                ->select('w3.item_id, itm.item_name, w3.consumption, w3.required_qty')
                ->from($this->wo_l3 . ' w3')
                ->join('items itm', 'itm.id = w3.item_id', 'left')
                ->where('w3.wo_l2_id', (int)$l2['id'])
                ->order_by('w3.id', 'ASC')
                ->get()->result_array();
            $l2_list[] = [
                'item_id'   => (int)$l2['item_id'],
                'item_name' => $l2['item_name'],
                'materials' => $mats,
            ];
        }

        // Hitung total size dan total required (untuk ringkasan)
        $total_size_qty = (float)($w1['total_size_qty'] ?? 0);
        $grand_required = 0.0;
        foreach ($l2_list as $sfg) {
            foreach ($sfg['materials'] as $m) {
                $grand_required += (float)$m['required_qty'];
            }
        }

        return [
            'w1' => $w1,
            'header' => [
                'item_code'  => $info['item_code'] ?? '',
                'item_name'  => $info['item_name'] ?? '',
                'brand_name' => $info['brand_name'] ?? '',
                'unit_name'  => $info['unit_name'] ?? '',
            ],
            'sizes'          => $sizes,
            'l2_list'        => $l2_list,
            'total_size_qty' => $total_size_qty,
            'grand_required' => $grand_required,
        ];
    }

    /**
     * Export PDF per WO menggunakan Dompdf.
     * URL: /wo/export/{id}
     */
    public function export($id)
    {
        if (!ctype_digit((string)$id)) show_404();

        $payload = $this->_get_wo_payload((int)$id);
        if (!$payload) {
            show_error('WO tidak ditemukan', 404);
            return;
        }

        // Render HTML dari view
        $html = $this->load->view('wo/pdf', $payload, true);

        // Pastikan Dompdf tersedia
        if (!class_exists('\\Dompdf\\Dompdf')) {
            show_error('Dompdf belum terpasang atau composer_autoload belum aktif.', 500);
            return;
        }

        // Generate PDF
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        // Nama file
        $fileName = sprintf('WO-%d_%s.pdf', (int)$payload['w1']['id'], date('Ymd_His'));

        // Stream ke browser (Attachment=false untuk preview; true untuk download)
        $dompdf->stream($fileName, ['Attachment' => false]);
    }

    /* ==========================
     * Helper render
     * ========================== */
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
