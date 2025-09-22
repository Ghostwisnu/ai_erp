<section id="main-content">
    <section class="wrapper">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Buat Work Order</h3>
                <a href="<?= site_url('wo') ?>" class="btn btn-secondary">Kembali</a>
            </div>

            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="post" action="<?= site_url('wo/store') ?>">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
                        value="<?= $this->security->get_csrf_hash() ?>" />

                    <!-- Input No WO -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="no_wo">No WO</label>
                            <input type="text" class="form-control" id="no_wo" name="no_wo" value="<?= htmlspecialchars($no_wo ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                    </div>

                    <!-- Pilih BOM + Tanggal -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="bom_l1_id">Pilih BOM</label>
                            <select class="form-control" id="bom_l1_id" name="bom_l1_id" required>
                                <option value="">- Pilih BOM -</option>
                                <?php foreach ($bom_choices as $b): ?>
                                    <option value="<?= (int)$b['bom_l1_id'] ?>"
                                        data-art-color="<?= htmlspecialchars($b['art_color'], ENT_QUOTES, 'UTF-8') ?>">
                                        #<?= (int)$b['bom_l1_id'] ?> — <?= htmlspecialchars($b['art_color'], ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($b['item_name'], ENT_QUOTES, 'UTF-8') ?>
                                        (<?= htmlspecialchars($b['brand_name'], ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars($b['unit_name'], ENT_QUOTES, 'UTF-8') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_order">Date Order</label>
                            <input type="date" class="form-control" id="date_order" name="date_order" required>
                        </div>
                        <div class="col-md-3">
                            <label for="x_factory_date">X-Factory Date</label>
                            <input type="date" class="form-control" id="x_factory_date" name="x_factory_date" required>
                        </div>
                    </div>

                    <!-- Header FG readonly -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Item Code</label>
                            <input type="text" class="form-control" id="fg_code" readonly>
                        </div>
                        <div class="col-md-3">
                            <label>Item Name</label>
                            <input type="text" class="form-control" id="fg_name" readonly>
                        </div>
                        <div class="col-md-3">
                            <label>Brand</label>
                            <input type="text" class="form-control" id="fg_brand" readonly>
                        </div>
                        <div class="col-md-3">
                            <label>Unit</label>
                            <input type="text" class="form-control" id="fg_unit" readonly>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label>Art &amp; Color</label>
                            <input type="text" class="form-control" id="fg_art" readonly>
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
                        <!-- TAB BOM -->
                        <div class="tab-pane fade show active" id="tab-bom" role="tabpanel">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th style="width:25%">SFG</th>
                                        <th style="width:45%">Materials (Consumption)</th>
                                        <th style="width:30%">Required Qty = Cons × <span class="text-monospace" id="total_size_qty_tag">0</span></th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyBOM">
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Pilih BOM terlebih dahulu.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- TAB SIZE -->
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
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Pilih BOM untuk memuat size.</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Total Size Qty</th>
                                        <th>
                                            <input type="text" class="form-control" id="total_size_qty_display" readonly>
                                        </th>
                                        <th><em class="small text-muted">Digunakan untuk menghitung Required Qty.</em></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label for="notes">Catatan</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Opsional"></textarea>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-success">Simpan WO</button>
                        <a href="<?= site_url('wo') ?>" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</section>

<script>
    (function() {
        const bomSelect = document.getElementById('bom_l1_id');
        const tbodyBOM = document.getElementById('tbodyBOM');
        const tbodySz = document.getElementById('tbodySizes');
        const totalDisp = document.getElementById('total_size_qty_display');
        const totalTag = document.getElementById('total_size_qty_tag');

        const fgCode = document.getElementById('fg_code');
        const fgName = document.getElementById('fg_name');
        const fgBrand = document.getElementById('fg_brand');
        const fgUnit = document.getElementById('fg_unit');
        const fgArt = document.getElementById('fg_art');

        function esc(s) {
            return String(s).replace(/[&<>"']/g, m => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [m]));
        }

        function fmt(n) {
            if (!isFinite(n)) return '0';
            const x = Math.round(n * 1e6) / 1e6;
            return (x.toString().indexOf('.') >= 0) ? x.toString().replace(/0+$/, '').replace(/\.$/, '') : x.toString();
        }

        function getTotalSize() {
            let sum = 0;
            document.querySelectorAll('input[name^="sizes"][name$="[qty]"]').forEach(inp => {
                const v = parseFloat(inp.value || '0');
                if (!isNaN(v)) sum += v;
            });
            return sum;
        }

        function applyTotalToBOM() {
            const total = getTotalSize();
            totalDisp.value = fmt(total);
            totalTag.textContent = fmt(total);
            // hitung ulang required qty
            document.querySelectorAll('[data-consumption]').forEach(span => {
                const cons = parseFloat(span.getAttribute('data-consumption') || '0');
                const val = cons * total;
                span.textContent = fmt(val);
            });
        }

        // Saat BOM dipilih → muat tree dan sizes
        bomSelect.addEventListener('change', function() {
            const bomId = this.value;
            const artColor = this.options[this.selectedIndex].dataset.artColor;
            if (!bomId) return;

            // Set Art Color
            fgArt.value = artColor;

            fetch('<?= site_url('wo/get_bom_tree') ?>/' + bomId)
                .then(r => r.json())
                .then(js => {
                    if (!js.ok) throw new Error('Load BOM gagal');

                    // Header FG
                    fgCode.value = js.data.l1.item_code || '';
                    fgName.value = js.data.l1.item_name || '';
                    fgBrand.value = js.data.l1.brand_name || '';
                    fgUnit.value = js.data.l1.unit_name || '';

                    // Render BOM (3 kolom: SFG | Materials(cons) | Required(cons×total))
                    const l2 = js.data.l2 || [];
                    if (!l2.length) {
                        tbodyBOM.innerHTML = '<tr><td colspan="3" class="text-center text-muted">BOM ini kosong.</td></tr>';
                    } else {
                        tbodyBOM.innerHTML = '';
                        l2.forEach(sfg => {
                            const tr = document.createElement('tr');

                            // Kolom SFG
                            const td1 = document.createElement('td');
                            td1.innerHTML = '<strong>' + esc(sfg.item_name || ('ID:' + sfg.item_id)) + '</strong>';

                            // Kolom Materials (consumption)
                            const td2 = document.createElement('td');
                            if (Array.isArray(sfg.materials) && sfg.materials.length) {
                                const ul = document.createElement('ul');
                                ul.className = 'mb-0';
                                sfg.materials.forEach(m => {
                                    const li = document.createElement('li');
                                    li.textContent = (m.item_name || ('ID:' + m.item_id)) + ' — cons: ' + fmt(parseFloat(m.consumption || 0));
                                    ul.appendChild(li);
                                });
                                td2.appendChild(ul);
                            } else {
                                td2.innerHTML = '<em class="text-muted">Tidak ada material.</em>';
                            }

                            // Kolom Required Qty (cons×total)
                            const td3 = document.createElement('td');
                            if (Array.isArray(sfg.materials) && sfg.materials.length) {
                                const ulr = document.createElement('ul');
                                ulr.className = 'mb-0';
                                sfg.materials.forEach(m => {
                                    const li = document.createElement('li');
                                    li.innerHTML = esc(m.item_name || ('ID:' + m.item_id)) + ' — req: ' +
                                        '<span class="text-monospace req-calc" data-consumption="' + esc(m.consumption) + '">0</span>';
                                    ulr.appendChild(li);
                                });
                                td3.appendChild(ulr);
                            } else {
                                td3.innerHTML = '<em class="text-muted">—</em>';
                            }

                            tr.appendChild(td1);
                            tr.appendChild(td2);
                            tr.appendChild(td3);
                            tbodyBOM.appendChild(tr);
                        });
                    }

                    // Muat sizes by brand
                    const brandId = js.data.l1.bom_l1 ? js.data.l1.bom_l1.brand_id : null;
                    if (!brandId) {
                        tbodySz.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Brand tidak diketahui.</td></tr>';
                        applyTotalToBOM();
                        return;
                    }

                    fetch('<?= site_url('wo/get_sizes_by_brand') ?>/' + brandId)
                        .then(r => r.json())
                        .then(sz => {
                            const arr = Array.isArray(sz.sizes) ? sz.sizes : [];
                            if (!arr.length) {
                                tbodySz.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Brand ini belum memiliki size.</td></tr>';
                                applyTotalToBOM();
                                return;
                            }
                            tbodySz.innerHTML = '';
                            arr.forEach((s, idx) => {
                                const tr = document.createElement('tr');

                                const tdNm = document.createElement('td');
                                tdNm.textContent = s.size_name || ('Size ID:' + s.id);

                                const tdQty = document.createElement('td');
                                const inp = document.createElement('input');
                                inp.type = 'number';
                                inp.min = '0';
                                inp.step = 'any';
                                inp.className = 'form-control';
                                inp.name = `sizes[${idx}][qty]`;
                                inp.addEventListener('input', applyTotalToBOM);
                                tdQty.appendChild(inp);

                                const tdInfo = document.createElement('td');
                                tdInfo.innerHTML = `<input type="hidden" name="sizes[${idx}][size_id]" value="${s.id}">` +
                                    `<span class="text-muted">Brand ID: ${esc(String(brandId))}</span>`;

                                tr.appendChild(tdNm);
                                tr.appendChild(tdQty);
                                tr.appendChild(tdInfo);
                                tbodySz.appendChild(tr);
                            });

                            // Kalkulasi awal
                            applyTotalToBOM();
                        });
                })
                .catch(err => {
                    console.error(err);
                    tbodyBOM.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Gagal memuat BOM.</td></tr>';
                    tbodySz.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Gagal memuat size.</td></tr>';
                    applyTotalToBOM();
                });
        });
    })();
</script>