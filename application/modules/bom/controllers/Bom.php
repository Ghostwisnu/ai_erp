<?php defined('BASEPATH') or exit('No direct script access allowed');

class Bom extends MX_Controller
{
    private $table_l1 = 'bom_l1'; // Level 1: Barang Jadi
    private $table_l2 = 'bom_l2'; // Level 2: Barang Setengah Jadi
    private $table_l3 = 'bom_l3'; // Level 3: Material

    public function __construct()
    {
        parent::__construct();
        $this->_check_login();
        $this->load->model('Generic_model', 'gm');
        $this->load->helper(['paging']);
    }

    private function _check_login()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
    }

    public function index()
    {
        $data['title'] = 'BOM - Bill of Materials';

        // JOIN lengkap untuk memenuhi kolom di view
        $this->db->select('
    l1.id,
    i.item_code AS item_code,
    i.item_name AS item_name,
    c.name      AS category_name,
    b.name      AS brand_name,
    l1.art_color AS art_color
');
        $this->db->from($this->table_l1 . ' AS l1');
        $this->db->join('items i', 'i.id = l1.item_id', 'left');
        $this->db->join('categories c', 'c.id = i.category_id', 'left');
        $this->db->join('brands b', 'b.id = l1.brand_id', 'left');
        // $this->db->join('units u', 'u.id = l1.unit_id', 'left'); // opsional, boleh dihapus
        $this->db->order_by('l1.id', 'DESC');


        $data['bom_l1'] = $this->db->get()->result_array();

        $this->_render('bom/list', $data);
    }


    public function create()
    {
        // Ambil data yang diperlukan untuk dropdown
        $categories = $this->gm->get_all_data('categories');
        $brands = $this->gm->get_all_data('brands');
        $units = $this->gm->get_all_data('units');

        // Ambil data items dengan item_type 'FG' (Finished Goods)
        $items = $this->gm->get_where('items', ['item_type' => 'FG']); // Level 1: Finished Goods

        // Kirimkan data ke view
        $data = [
            'title' => 'Tambah BOM',
            'categories' => $categories,
            'brands' => $brands,
            'units' => $units,
            'items' => $items, // Kirimkan data items dengan item_type 'FG'
        ];

        $this->_render('bom/create', $data);
    }

    // Mendapatkan Barang Setengah Jadi (Level 2) berdasarkan Brand dan membatasi jumlahnya
    public function get_level2_by_brand($brand_id)
    {
        // Validasi numeric
        if (!ctype_digit((string)$brand_id)) {
            echo json_encode(['level2_items' => []]);
            return;
        }

        $level2_items = $this->gm->get_where('items', ['item_type' => 'SFG', 'brand_id' => $brand_id]);
        $level2_items = array_slice($level2_items, 0, 6);

        echo json_encode(['level2_items' => $level2_items]);
    }


    // Mendapatkan data Raw dan SFG untuk Material berdasarkan SFG yang dipilih
    public function get_raw_or_sfg_for_material($sfg_id)
    {
        // Debugging to see the sfg_id received
        log_message('debug', 'SFG ID: ' . $sfg_id);

        // First, fetch the item details for the SFG selected
        $sfg_items = $this->db->where('id', $sfg_id)->get('items')->result_array();

        if (empty($sfg_items)) {
            // If no SFG item is found, return an empty result for raw_and_sfg_items
            echo json_encode(['raw_and_sfg_items' => []]);
            return;
        }

        // Now, fetch SFG items related to the brand of the selected SFG item
        $brand_id = $sfg_items[0]['brand_id'];

        // Fetch all SFG items of the same brand
        $this->db->where('item_type', 'SFG');
        $this->db->where('brand_id', $brand_id); // Only fetch SFG items with the same brand_id
        $sfg_related_items = $this->db->get('items')->result_array();

        // Fetch all RAW items (no brand restriction for RAW items)
        $this->db->where('item_type', 'RAW');
        $raw_items = $this->db->get('items')->result_array();

        // Combine SFG and RAW items
        $raw_and_sfg_items = array_merge($sfg_related_items, $raw_items);

        // Debugging the combined result
        log_message('debug', 'Raw and SFG Items: ' . print_r($raw_and_sfg_items, true));

        // Return the result as JSON
        echo json_encode(['raw_and_sfg_items' => $raw_and_sfg_items]);
    }


    public function store()
    {
        $this->form_validation->set_rules('item_id', 'Item', 'required|trim');
        $this->form_validation->set_rules('unit_id_hidden', 'Unit', 'required|trim');
        $this->form_validation->set_rules('brand_id_hidden', 'Brand', 'required|trim');
        $this->form_validation->set_rules('art_color', 'Art & Color', 'required|trim');

        if (!$this->form_validation->run()) {
            return $this->create();
        }

        log_message('debug', 'POST Data: ' . print_r($this->input->post(), true));

        $data_l1 = [
            'item_id'   => $this->input->post('item_id'),
            'unit_id'   => $this->input->post('unit_id_hidden'),
            'brand_id'  => $this->input->post('brand_id_hidden'),
            'art_color' => $this->input->post('art_color'),
        ];

        $bom_l1_id = $this->gm->insert_and_get_id($this->table_l1, $data_l1);

        $level2_items = $this->input->post('level2_items');
        if (!is_array($level2_items)) $level2_items = [];

        foreach ($level2_items as $i => $item) {
            if (empty($item['item_id'])) continue;

            $data_l2 = [
                'bom_l1_id' => $bom_l1_id,
                'item_id'   => $item['item_id'],
            ];
            $bom_l2_id = $this->gm->insert_and_get_id($this->table_l2, $data_l2);

            if (!empty($item['materials']) && is_array($item['materials'])) {
                foreach ($item['materials'] as $j => $material) {
                    $matId = $material['item_id'] ?? null;
                    $cons  = $material['consumption'] ?? null;
                    if ($matId === null || $cons === null || $cons === '') continue;

                    $data_l3 = [
                        'bom_l2_id'   => $bom_l2_id,
                        'item_id'     => $matId,
                        'consumption' => $cons,
                    ];
                    $this->gm->insert_data($this->table_l3, $data_l3);
                }
            }
        }

        $this->session->set_flashdata('message', 'BOM berhasil disimpan.');
        redirect('bom');
    }

    public function edit($id)
    {
        // --- Level 1 ---
        $bom_l1 = $this->gm->get_row_where($this->table_l1, ['id' => $id]);
        if (!$bom_l1) show_404();

        // --- Dropdowns yang dibutuhkan view ---
        $items  = $this->gm->get_where('items', ['item_type' => 'FG']);  // FG untuk select Item Jadi
        $brands = $this->gm->get_all_data('brands');
        $units  = $this->gm->get_all_data('units');

        // --- Ambil SEMUA L2 milik BOM ini ---
        $l2_rows = $this->db
            ->where('bom_l1_id', $id)
            ->get($this->table_l2)
            ->result_array();

        // Kumpulkan semua item_id yang perlu dilabelkan (SFG & material) → 1x query ke items
        $itemIds = [];
        foreach ($l2_rows as $l2) {
            $itemIds[] = (int)$l2['item_id']; // SFG id
            $l3_rows = $this->db->where('bom_l2_id', $l2['id'])->get($this->table_l3)->result_array();
            foreach ($l3_rows as $l3) {
                $itemIds[] = (int)$l3['item_id']; // material id
            }
        }
        $itemIds = array_values(array_unique(array_filter($itemIds)));

        // Map item_id -> item_name (untuk ringkasan di view)
        $itemsMap = [];
        if (!empty($itemIds)) {
            $this->db->where_in('id', $itemIds);
            $res = $this->db->get('items')->result_array();
            foreach ($res as $it) {
                // pastikan kolom nama sesuai skema Anda (item_name)
                $itemsMap[(int)$it['id']] = $it['item_name'];
            }
        }

        // Bentuk struktur existing_l2 yang dipakai view edit
        $existing_l2 = [];
        foreach ($l2_rows as $l2) {
            $mats = $this->db->where('bom_l2_id', $l2['id'])->get($this->table_l3)->result_array();

            $matList = [];
            foreach ($mats as $m) {
                $matList[] = [
                    'item_id'     => (int)$m['item_id'],
                    'item_name'   => $itemsMap[(int)$m['item_id']] ?? ('ID:' . $m['item_id']),
                    'consumption' => $m['consumption'],
                ];
            }

            $existing_l2[] = [
                'l2_id'     => (int)$l2['id'],
                'item_id'   => (int)$l2['item_id'], // SFG id
                'item_name' => $itemsMap[(int)$l2['item_id']] ?? ('ID:' . $l2['item_id']),
                'materials' => $matList,
            ];
        }

        // (Opsional) daftar SFG untuk brand BOM ini (dipakai saat tambah SFG baru)
        $semi_finished_goods = $this->gm->get_where('items', [
            'item_type' => 'SFG',
            'brand_id'  => $bom_l1['brand_id'],   // jika ingin tanpa filter brand, hapus baris ini
        ]);

        // --- Kirim ke view (perhatikan: view Anda memakai $existing_l2) ---
        $data = [
            'title'               => 'Edit BOM',
            'bom_l1'              => $bom_l1,
            'items'               => $items,
            'brands'              => $brands,
            'units'               => $units,
            'semi_finished_goods' => $semi_finished_goods,
            'existing_l2'         => $existing_l2,
        ];

        // (Opsional) logging untuk memastikan terisi
        log_message('debug', 'EDIT BOM existing_l2: ' . print_r($existing_l2, true));

        $this->_render('bom/edit', $data);
    }


    public function update($id)
    {
        $this->form_validation->set_rules('item_id', 'Item', 'required|trim');
        $this->form_validation->set_rules('unit_id', 'Unit', 'required|trim');
        $this->form_validation->set_rules('brand_id', 'Brand', 'required|trim');
        $this->form_validation->set_rules('art_color', 'Art & Color', 'required|trim');

        if (!$this->form_validation->run()) {
            return $this->edit($id);
        }

        // Update L1
        $data_l1 = [
            'item_id'   => $this->input->post('item_id'),
            'unit_id'   => $this->input->post('unit_id'),
            'brand_id'  => $this->input->post('brand_id'),
            'art_color' => $this->input->post('art_color'),
        ];
        $this->gm->update_data($this->table_l1, $data_l1, ['id' => $id]);

        // ----- Reset L2/L3 lama -----
        $oldL2 = $this->db->where('bom_l1_id', $id)->get($this->table_l2)->result_array();
        if (!empty($oldL2)) {
            $oldL2Ids = array_column($oldL2, 'id');
            // hapus L3 semua
            $this->db->where_in('bom_l2_id', $oldL2Ids)->delete($this->table_l3);
            // hapus L2 semua
            $this->db->where('bom_l1_id', $id)->delete($this->table_l2);
        }

        // ----- Insert ulang L2/L3 dari POST -----
        $level2_items = $this->input->post('level2_items');
        if (is_array($level2_items)) {
            foreach ($level2_items as $i => $item) {
                if (empty($item['item_id'])) continue;

                $data_l2 = [
                    'bom_l1_id' => $id,
                    'item_id'   => $item['item_id'], // SFG id
                ];
                $this->db->insert($this->table_l2, $data_l2);
                $bom_l2_id = $this->db->insert_id();

                if (!empty($item['materials']) && is_array($item['materials'])) {
                    foreach ($item['materials'] as $j => $m) {
                        $matId = $m['item_id'] ?? null;
                        $cons  = $m['consumption'] ?? null;
                        if ($matId === null || $cons === null || $cons === '') continue;

                        $this->db->insert($this->table_l3, [
                            'bom_l2_id'   => $bom_l2_id,
                            'item_id'     => $matId,
                            'consumption' => $cons,
                        ]);
                    }
                }
            }
        }

        $this->session->set_flashdata('message', 'BOM berhasil diperbarui.');
        redirect('bom');
    }


    public function get_unit_and_brand_by_item($item_id)
    {
        // Ambil data item berdasarkan item_id
        $item = $this->gm->get_row_where('items', ['id' => $item_id]);

        if ($item) {
            // Ambil unit dan brand yang terkait dengan item ini
            $unit_id = $item['unit_id'];
            $brand_id = $item['brand_id'];

            // Ambil data unit dan brand yang terkait dengan item
            $unit = $this->gm->get_row_where('units', ['id' => $unit_id]);
            $brand = $this->gm->get_row_where('brands', ['id' => $brand_id]);

            // Jika unit atau brand tidak ditemukan, kita kirimkan data kosong
            $units = $unit ? [$unit] : [];
            $brands = $brand ? [$brand] : [];

            // Kirimkan data dalam format JSON
            echo json_encode([
                'units' => $units,
                'brands' => $brands,
            ]);
        } else {
            // Jika item tidak ditemukan, kirimkan response kosong
            echo json_encode([
                'units' => [],
                'brands' => [],
            ]);
        }
    }

    public function delete($id)
    {
        // Ambil semua L2 id untuk bom_l1 ini
        $l2 = $this->db->where('bom_l1_id', $id)->get($this->table_l2)->result_array();
        if (!empty($l2)) {
            $l2Ids = array_column($l2, 'id');
            $this->db->where_in('bom_l2_id', $l2Ids)->delete($this->table_l3); // hapus semua L3
            $this->db->where('bom_l1_id', $id)->delete($this->table_l2);       // hapus semua L2
        }
        $this->db->where('id', $id)->delete($this->table_l1);                  // hapus L1

        $this->session->set_flashdata('message', 'BOM berhasil dihapus.');
        redirect('bom');
    }


    public function _render($view, $data)
    {
        $data['user'] = $this->session->userdata('username');
        $data['role_id'] = $this->session->userdata('role_id');
        $this->load->view('templates/header', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view($view, $data);
        $this->load->view('templates/footer');
    }
}
