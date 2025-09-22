<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;
use Dompdf\Options;

class Checkin extends MX_Controller
{
    // Master
    private $wo_l1 = 'wo_l1';
    private $wo_l2 = 'wo_l2';
    private $wo_l3 = 'wo_l3';
    private $brand_sizes = 'brand_sizes';
    private $items = 'items';
    private $brands = 'brands';
    private $units  = 'units';

    // Check-in
    private $tbl_hdr = 'checkin_hdr';
    private $tbl_det = 'checkin_det';
    private $tbl_sz  = 'checkin_det_sizes';

    public function __construct()
    {
        parent::__construct();
        $this->_check_login();
        $this->load->model('Generic_model', 'gm');
        $this->load->library('form_validation');
        $this->load->helper(['url', 'form']);
        // Pastikan autoload composer untuk mPDF jika perlu:
        // require_once FCPATH.'vendor/autoload.php';
    }

    private function _check_login()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
    }

    /* ========= LIST ========= */
    public function index()
    {
        $data['title'] = 'Surat Tanda Terima (Check-In)';

        $this->db->select('id, no_str, no_sj, arrival_date, created_at');
        $this->db->from($this->tbl_hdr);
        $this->db->order_by('id', 'DESC');
        $data['rows'] = $this->db->get()->result_array();

        $this->_render('checkin/list', $data);
    }

    /* ========= CREATE ========= */
    public function create()
    {
        // Ambil daftar WO terbaru utk dipilih (id, item, brand, unit, date info)
        $this->db->select('w1.id, w1.date_order, w1.x_factory_date, i.item_code, i.item_name, b.name AS brand_name, b.id AS brand_id, u.name AS unit_name, w1.no_wo');
        $this->db->from($this->wo_l1 . ' w1');
        $this->db->join($this->items . ' i', 'i.id = w1.item_id', 'left');
        $this->db->join($this->brands . ' b', 'b.id = w1.brand_id', 'left');
        $this->db->join($this->units . ' u', 'u.id = w1.unit_id', 'left');
        $this->db->order_by('w1.id', 'DESC');
        $data['wo_list'] = $this->db->get()->result_array();

        // nomor STR auto
        $data['no_str_suggest'] = $this->generate_str_number();

        $this->_render('checkin/create', $data);
    }


    private function generate_str_number()
    {
        // Pola: STR-YYYYMM-XXXX
        $prefix = 'STR-' . date('Ym') . '-';

        // Cek jumlah nomor STR yang sudah ada di database dengan prefix yang sama
        $this->db->like('no_str', $prefix);
        $this->db->from($this->tbl_hdr);
        $count = $this->db->count_all_results();

        // Tentukan nomor urut berikutnya
        $next_number = $count + 1;

        // Generate nomor STR
        return $prefix . str_pad((string)$next_number, 4, '0', STR_PAD_LEFT);
    }


    /* ========= AJAX: Ambil MATERIAL utk 1 WO =========
       Return: [{wo_l1_id, brand_id, item_id, item_name, item_code, consumption, required_qty}]
    */
    public function ajax_wo_items($wo_l1_id)
    {
        if (!ctype_digit((string)$wo_l1_id)) {
            echo json_encode(['ok' => false, 'rows' => []]);
            return;
        }
        $w1 = $this->gm->get_row_where($this->wo_l1, ['id' => (int)$wo_l1_id]);
        if (!$w1) {
            echo json_encode(['ok' => false, 'rows' => []]);
            return;
        }

        $brand_id = (int)$w1['brand_id'];

        // Ambil L2
        $l2 = $this->db->select('id, item_id')->from($this->wo_l2)->where('wo_l1_id', (int)$wo_l1_id)->get()->result_array();
        $rows = [];
        foreach ($l2 as $sfg) {
            $mats = $this->db
                ->select('w3.item_id, w3.consumption, w3.required_qty, it.item_code, it.item_name')
                ->from($this->wo_l3 . ' w3')
                ->join($this->items . ' it', 'it.id=w3.item_id', 'left')
                ->where('w3.wo_l2_id', $sfg['id'])
                ->get()->result_array();
            foreach ($mats as $m) {
                $rows[] = [
                    'wo_l1_id'     => (int)$wo_l1_id,
                    'brand_id'     => $brand_id,
                    'item_id'      => (int)$m['item_id'],
                    'item_code'    => $m['item_code'],
                    'item_name'    => $m['item_name'],
                    'consumption'  => (float)$m['consumption'],
                    'required_qty' => (float)$m['required_qty'],
                ];
            }
        }
        echo json_encode(['ok' => true, 'rows' => $rows]);
    }

    /* ========= AJAX: Ambil SIZE utk brand di WO =========
       Return: sizes [{id, size_name}]
    */
    public function ajax_sizes_by_wo($wo_l1_id)
    {
        if (!ctype_digit((string)$wo_l1_id)) {
            echo json_encode(['ok' => false, 'sizes' => []]);
            return;
        }
        $w1 = $this->gm->get_row_where($this->wo_l1, ['id' => (int)$wo_l1_id]);
        if (!$w1) {
            echo json_encode(['ok' => false, 'sizes' => []]);
            return;
        }

        // Ambil data ukuran berdasarkan brand
        $sizes = $this->gm->get_where($this->brand_sizes, ['brand_id' => (int)$w1['brand_id']]);

        // Ambil qty dari wo_sizes untuk setiap size
        $sizes_with_qty = [];
        foreach ($sizes as $size) {
            $qty = $this->db->select('qty')
                ->from('wo_sizes')
                ->where('wo_l1_id', $wo_l1_id)
                ->where('size_id', $size['id'])
                ->get()
                ->row_array();
            $sizes_with_qty[] = [
                'size_id' => $size['id'],
                'size_name' => $size['size_name'],
                'qty' => $qty['qty'] ?? 0 // Menambahkan qty ke ukuran
            ];
        }

        echo json_encode(['ok' => true, 'sizes' => $sizes_with_qty]);
    }


    /* ========= STORE =========
       Expect:
       - no_str (optional; jika kosong → generate)
       - no_sj, arrival_date, notes
       - details[i][wo_l1_id], details[i][item_id], details[i][qty_in]
       - details[i][sizes][j][size_id], details[i][sizes][j][qty] (opsional)
    */
    public function store()
    {
        $this->form_validation->set_rules('arrival_date', 'Tanggal Kedatangan', 'required|trim');
        $this->form_validation->set_rules('no_wo', 'No WO', 'required|trim'); // Added validation for No WO

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', validation_errors());
            return redirect('checkin/create');
        }

        $no_str = trim((string)$this->input->post('no_str'));
        if ($no_str === '') $no_str = $this->generate_str_number();

        $hdr = [
            'no_str'       => $no_str,
            'no_sj'        => trim((string)$this->input->post('no_sj')),
            'no_wo'        => trim((string)$this->input->post('no_wo')), // No WO disimpan di header saja
            'arrival_date' => $this->input->post('arrival_date'),
            'notes'        => trim((string)$this->input->post('notes')),
            'created_at'   => date('Y-m-d H:i:s'),
        ];

        $details = $this->input->post('details');
        if (!is_array($details) || empty($details)) {
            $this->session->set_flashdata('error', 'Tidak ada item/material yang diinput.');
            return redirect('checkin/create');
        }

        $this->db->trans_start();

        // Insert the check-in header with no_wo
        $hdr_id = $this->gm->insert_data($this->tbl_hdr, $hdr);
        if (!$hdr_id || !is_numeric($hdr_id)) $hdr_id = $this->db->insert_id();

        foreach ($details as $row) {
            $wo_l1_id = isset($row['wo_l1_id']) ? (int)$row['wo_l1_id'] : 0;
            $item_id  = isset($row['item_id']) ? (int)$row['item_id'] : 0;
            $qty_in   = isset($row['qty_in'])  ? (float)$row['qty_in']  : 0;

            if ($wo_l1_id <= 0 || $item_id <= 0) continue;

            $det_id = $this->gm->insert_data($this->tbl_det, [
                'hdr_id'   => $hdr_id,
                'wo_l1_id' => $wo_l1_id,
                'item_id'  => $item_id,
                'qty_in'   => $qty_in,
                'qty_out'  => 0,
            ]);
            if (!$det_id || !is_numeric($det_id)) $det_id = $this->db->insert_id();

            // sizes (optional)
            if (!empty($row['sizes']) && is_array($row['sizes'])) {
                foreach ($row['sizes'] as $s) {
                    $sid = isset($s['size_id']) ? (int)$s['size_id'] : 0;
                    $q   = isset($s['qty'])     ? (float)$s['qty']     : 0;
                    if ($sid > 0 && $q > 0) {
                        $this->gm->insert_data($this->tbl_sz, [
                            'det_id'  => $det_id,
                            'size_id' => $sid,
                            'qty'     => $q
                        ]);
                    }
                }
            }
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Gagal menyimpan STR.');
            return redirect('checkin/create');
        }

        $this->session->set_flashdata('message', 'STR berhasil dibuat.');
        redirect('checkin/pdf/' . $hdr_id); // directly go to PDF (optional)
    }

    // Method untuk menghapus data checkin
    public function delete_checkin($id)
    {
        if (!ctype_digit((string)$id)) {
            show_404();
        }

        $this->db->trans_strict(true);
        $this->db->trans_start();

        // Hapus detail check-in sizes
        $this->gm->delete_data($this->tbl_sz, ['det_id' => $id]);

        // Hapus detail check-in
        $this->gm->delete_data($this->tbl_det, ['hdr_id' => $id]);

        // Hapus header check-in
        $this->gm->delete_data($this->tbl_hdr, ['id' => $id]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->session->set_flashdata('error', 'Gagal menghapus data check-in.');
        } else {
            $this->session->set_flashdata('message', 'Data check-in berhasil dihapus.');
        }

        redirect('checkin');
    }

    public function pdf($hdr_id)
    {
        if (!ctype_digit((string)$hdr_id)) show_404();

        // Ambil data header check-in dan No WO terkait
        $hdr = $this->gm->get_row_where('checkin_hdr', ['id' => (int)$hdr_id]);
        if (!$hdr) show_404();

        // Ambil data No WO terkait dengan hdr_id
        $this->db->select('no_wo');
        $this->db->from('checkin_hdr');
        $this->db->where('id', $hdr_id);
        $wo = $this->db->get()->row_array(); // Mengambil No WO dari tabel checkin_hdr

        // Pastikan No WO ditemukan
        if ($wo) {
            $hdr['no_wo'] = $wo['no_wo']; // Menambahkan No WO ke dalam data header
        } else {
            $hdr['no_wo'] = null; // Jika No WO tidak ditemukan
        }

        // Ambil detail + join item & wo (sama seperti sebelumnya)
        $rows = $this->db->query("SELECT d.id, d.wo_l1_id, d.item_id, d.qty_in, d.qty_out, i.item_code, i.item_name
                             FROM checkin_det d
                             LEFT JOIN items i ON i.id = d.item_id
                             WHERE d.hdr_id = ? ORDER BY d.id ASC", [(int)$hdr_id])->result_array();

        // Ambil sizes untuk setiap detail
        $sizes_map = [];
        if (!empty($rows)) {
            $det_ids = array_column($rows, 'id');
            $in = implode(',', array_map('intval', $det_ids));
            $szs = $this->db->query("SELECT s.det_id, s.size_id, bs.size_name, s.qty
                                FROM checkin_det_sizes s
                                LEFT JOIN brand_sizes bs ON bs.id = s.size_id
                                WHERE s.det_id IN ($in)")->result_array();
            foreach ($szs as $s) {
                $sizes_map[(int)$s['det_id']][] = $s;
            }
        }

        // Render HTML view jadi string
        $html = $this->load->view('checkin/pdf', [
            'hdr' => $hdr,
            'rows' => $rows,
            'sizes_map' => $sizes_map
        ], true);

        // ===== Dompdf =====
        $options = new Options();
        $options->set('isRemoteEnabled', true);      // allow external images/fonts if needed
        $options->set('isHtml5ParserEnabled', true); // better HTML5 parsing

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'STR_' . $hdr['no_str'] . '.pdf';
        // Attachment=false → tampil di browser, true → forced download
        $dompdf->stream($filename, ['Attachment' => false]);
    }

    /* ========= Helper render ========= */
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
