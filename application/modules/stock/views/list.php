<!-- application/modules/stock/views/list.php -->
<section id="main-content">
    <section class="wrapper">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Stok Items (FG / SFG / RAW)</h3>
            </div>

            <div class="card-body">
                <!-- Search bar -->
                <form method="get" action="<?= site_url('stock') ?>" class="form-inline mb-3">
                    <input type="text"
                        name="q"
                        value="<?= htmlspecialchars($q ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        class="form-control mr-2"
                        placeholder="Cari code / nama / brand / unit">
                    <input type="hidden" name="tab" value="<?= htmlspecialchars($active_tab ?? 'FG', ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-primary">Cari</button>
                    <?php if (!empty($q)): ?>
                        <a href="<?= site_url('stock') ?>" class="btn btn-light ml-2">Reset</a>
                    <?php endif; ?>
                </form>

                <!-- Tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link <?= ($active_tab === 'FG' ? 'active' : '') ?>" data-toggle="tab" href="#tabFG">FG
                            <span class="badge badge-secondary"><?= (int)($count_fg ?? 0) ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($active_tab === 'SFG' ? 'active' : '') ?>" data-toggle="tab" href="#tabSFG">SFG
                            <span class="badge badge-secondary"><?= (int)($count_sfg ?? 0) ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($active_tab === 'RAW' ? 'active' : '') ?>" data-toggle="tab" href="#tabRAW">RAW
                            <span class="badge badge-secondary"><?= (int)($count_raw ?? 0) ?></span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content pt-3">
                    <div class="tab-pane fade <?= ($active_tab === 'FG' ? 'show active' : '') ?>" id="tabFG">
                        <?php $this->load->view('stock/part_table', ['rows' => $items_fg]); ?>
                    </div>
                    <div class="tab-pane fade <?= ($active_tab === 'SFG' ? 'show active' : '') ?>" id="tabSFG">
                        <?php $this->load->view('stock/part_table', ['rows' => $items_sfg]); ?>
                    </div>
                    <div class="tab-pane fade <?= ($active_tab === 'RAW' ? 'show active' : '') ?>" id="tabRAW">
                        <?php $this->load->view('stock/part_table', ['rows' => $items_raw]); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>

<!-- Modal & scripts (tetap) -->
<div class="modal fade" id="modalCheckinDetail" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Check-In</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tblCheckinDetail">
                        <thead>
                            <tr>
                                <th>No STR</th>
                                <th>Tanggal</th>
                                <th>WO No</th> <!-- was: WO -->
                                <th class="text-right">Qty In</th>
                                <th class="text-right">Qty Out</th> <!-- NEW -->
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalStockSummary" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ringkasan Stok (per WO)</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tblStockSummary">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>WO</th>
                                <th class="text-right">Total Check-In</th>
                                <th class="text-right">Total Checkout</th>
                                <th class="text-right">Total Stok</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function nf(x) {
        if (x === null || x === undefined) return '0';
        const s = Number(x).toFixed(6).replace(/\.?0+$/, '');
        return s;
    }

    function openCheckinDetail(itemId) {
        fetch('<?= site_url('stock/ajax_item_checkin_detail') ?>/' + itemId)
            .then(r => r.json())
            .then(p => {
                const tb = document.querySelector('#tblCheckinDetail tbody');
                tb.innerHTML = '';
                if (p.ok && Array.isArray(p.rows)) {
                    p.rows.forEach(r => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
            <td>${r.no_str || ''}</td>
            <td>${r.arrival_date || ''}</td>
            <td>${r.no_wo || ''}</td>                          <!-- WO No -->
            <td class="text-right">${nf(r.qty_in)}</td>
            <td class="text-right">${nf(r.qty_out)}</td>       <!-- Qty Out -->
            <td>${r.created_at || ''}</td>
          `;
                        tb.appendChild(tr);
                    });
                } else {
                    tb.innerHTML = '<tr><td colspan="6" class="text-center">No data found</td></tr>';
                }
                $('#modalCheckinDetail').modal('show');
            })
            .catch(err => console.error('Error fetching checkin details:', err));
    }

    function openStockSummary(itemId) {
        fetch('<?= site_url('stock/ajax_item_stock_summary') ?>/' + itemId)
            .then(r => r.json()).then(p => {
                const tb = document.querySelector('#tblStockSummary tbody');
                tb.innerHTML = '';
                if (p.ok && Array.isArray(p.rows)) {
                    p.rows.forEach(r => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                        <td>${r.wo_l1_id || ''}</td>
                        <td>${r.no_wo || ''}</td>  <!-- Display no_wo -->
                        <td class="text-right">${nf(r.total_in)}</td>
                        <td class="text-right">${nf(r.total_out)}</td>
                        <td class="text-right">${nf(r.total_stock)}</td>`;
                        tb.appendChild(tr);
                    });
                }
                $('#modalStockSummary').modal('show');
            });
    }

    // Ubah tab aktif → update field hidden "tab" di form search supaya dipertahankan saat submit
    document.querySelectorAll('.nav-tabs .nav-link').forEach(a => {
        a.addEventListener('shown.bs.tab', function(e) {
            const form = document.querySelector('form[method="get"][action$="stock"]');
            if (!form) return;
            const id = e.target.getAttribute('href'); // #tabFG | #tabSFG | #tabRAW
            const map = {
                '#tabFG': 'FG',
                '#tabSFG': 'SFG',
                '#tabRAW': 'RAW'
            };
            const val = map[id] || 'FG';
            const hid = form.querySelector('input[name="tab"]');
            if (hid) hid.value = val;
        });
    });
</script>