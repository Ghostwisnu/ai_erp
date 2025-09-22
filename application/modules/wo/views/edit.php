<section id="main-content">
    <section class="wrapper">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Edit Work Order</h3>
                <a href="<?= site_url('wo') ?>" class="btn btn-secondary">Kembali</a>
            </div>

            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="post" action="<?= site_url('wo/update/' . (int)$w1['id']) ?>">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
                        value="<?= $this->security->get_csrf_hash() ?>" />

                    <!-- Header: FG info (readonly) + tanggal -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Item Code</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($header['item_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly>
                        </div>
                        <div class="col-md-3">
                            <label>Item Name</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($header['item_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly>
                        </div>
                        <div class="col-md-3">
                            <label>Brand</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($header['brand_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly>
                        </div>
                        <div class="col-md-3">
                            <label>Unit</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($header['unit_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="date_order">Date Order</label>
                            <input type="date" class="form-control" id="date_order" name="date_order"
                                value="<?= htmlspecialchars($w1['date_order'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="x_factory_date">X-Factory Date</label>
                            <input type="date" class="form-control" id="x_factory_date" name="x_factory_date"
                                value="<?= htmlspecialchars($w1['x_factory_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                    </div>

                    <!-- Add a field for no_wo -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="no_wo">No WO</label>
                            <input type="text" class="form-control" id="no_wo" name="no_wo" value="<?= htmlspecialchars($w1['no_wo'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tab-bom" role="tab">BOM (SFG & Material)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-size" role="tab">Size Run</a>
                        </li>
                    </ul>

                    <div class="tab-content border-left border-right border-bottom p-3">
                        <!-- TAB BOM: 3 kolom -->
                        <div class="tab-pane fade show active" id="tab-bom" role="tabpanel">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th style="width:25%">SFG</th>
                                        <th style="width:45%">Materials (Consumption)</th>
                                        <th style="width:30%">
                                            Required Qty = Cons ×
                                            <span class="text-monospace" id="total_size_qty_tag"><?= htmlspecialchars(rtrim(rtrim(number_format((float)($w1['total_size_qty'] ?? 0), 6, '.', ''), '0'), '.'), ENT_QUOTES, 'UTF-8') ?></span>
                                            <div class="small text-muted">(disesuaikan live dari tab Size)</div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyBOM">
                                    <?php if (!empty($l2_list)): ?>
                                        <?php foreach ($l2_list as $sfg): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($sfg['item_name'] ?? ('ID:' . $sfg['item_id']), ENT_QUOTES, 'UTF-8') ?></strong></td>
                                                <td>
                                                    <?php if (!empty($sfg['materials'])): ?>
                                                        <ul class="mb-0">
                                                            <?php foreach ($sfg['materials'] as $m): ?>
                                                                <li>
                                                                    <?= htmlspecialchars($m['item_name'] ?? ('ID:' . $m['item_id']), ENT_QUOTES, 'UTF-8') ?>
                                                                    — cons:
                                                                    <span class="text-monospace">
                                                                        <?= htmlspecialchars(rtrim(rtrim(number_format((float)$m['consumption'], 6, '.', ''), '0'), '.'), ENT_QUOTES, 'UTF-8') ?>
                                                                    </span>
                                                                    <span class="text-muted"> | required (tersimpan): </span>
                                                                    <span class="text-monospace">
                                                                        <?= htmlspecialchars(rtrim(rtrim(number_format((float)$m['required_qty'], 6, '.', ''), '0'), '.'), ENT_QUOTES, 'UTF-8') ?>
                                                                    </span>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php else: ?>
                                                        <em class="text-muted">Tidak ada material.</em>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($sfg['materials'])): ?>
                                                        <ul class="mb-0">
                                                            <?php foreach ($sfg['materials'] as $m): ?>
                                                                <li>
                                                                    <?= htmlspecialchars($m['item_name'] ?? ('ID:' . $m['item_id']), ENT_QUOTES, 'UTF-8') ?>
                                                                    — req (live): <span class="text-monospace req-calc" data-consumption="<?= htmlspecialchars($m['consumption'], ENT_QUOTES, 'UTF-8') ?>">0</span>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php else: ?>
                                                        <em class="text-muted">—</em>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Tidak ada SFG/Material pada WO ini.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- TAB SIZE: daftar size by brand + qty existing -->
                        <div class="tab-pane fade" id="tab-size" role="tabpanel">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th style="width:40%">Size</th>
                                        <th style="width:30%">Qty</th>
                                        <th style="width:30%">Info</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodySizes">
                                    <?php if (!empty($sizes_all)): ?>
                                        <?php foreach ($sizes_all as $idx => $s): ?>
                                            <?php
                                            $sid = (int)$s['id'];
                                            $val = isset($size_qty_map[$sid]) ? (float)$size_qty_map[$sid] : 0.0;
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($s['size_name'] ?? ('Size ID:' . $sid), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td>
                                                    <input type="number" class="form-control sz-qty" min="0" step="any"
                                                        name="sizes[<?= $idx ?>][qty]"
                                                        value="<?= htmlspecialchars(rtrim(rtrim(number_format($val, 6, '.', ''), '0'), '.'), ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="sizes[<?= $idx ?>][size_id]" value="<?= $sid ?>">
                                                </td>
                                                <td><span class="text-muted">WO #<?= (int)$w1['id'] ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Brand ini belum memiliki size.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Total Size Qty</th>
                                        <th>
                                            <input type="text" class="form-control" id="total_size_qty_display"
                                                value="<?= htmlspecialchars(rtrim(rtrim(number_format((float)($w1['total_size_qty'] ?? 0), 6, '.', ''), '0'), '.'), ENT_QUOTES, 'UTF-8') ?>"
                                                readonly>
                                        </th>
                                        <th><em class="small text-muted">Dipakai untuk kalkulasi kolom "Required Qty".</em></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label for="notes">Catatan</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"
                                placeholder="Opsional"><?= htmlspecialchars($w1['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                        <a href="<?= site_url('wo') ?>" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</section>

<script>
    (function() {
        function fmt(n) {
            if (!isFinite(n)) return '0';
            const x = Math.round(n * 1e6) / 1e6;
            return (x.toString().indexOf('.') >= 0) ? x.toString().replace(/0+$/, '').replace(/\.$/, '') : x.toString();
        }

        function getTotal() {
            let sum = 0;
            document.querySelectorAll('.sz-qty').forEach(inp => {
                const v = parseFloat(inp.value || '0');
                if (!isNaN(v)) sum += v;
            });
            return sum;
        }

        function applyTotal() {
            const total = getTotal();
            document.getElementById('total_size_qty_display').value = fmt(total);
            document.getElementById('total_size_qty_tag').textContent = fmt(total);
            document.querySelectorAll('.req-calc[data-consumption]').forEach(span => {
                const cons = parseFloat(span.getAttribute('data-consumption') || '0');
                span.textContent = fmt(cons * total);
            });
        }

        // init
        applyTotal();

        // on change qty → update
        document.getElementById('tbodySizes').addEventListener('input', function(e) {
            if (e.target && e.target.classList.contains('sz-qty')) applyTotal();
        });
    })();
</script>