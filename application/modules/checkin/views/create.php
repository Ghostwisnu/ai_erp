<section id="main-content">
    <section class="wrapper">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Buat STR (Multi-WO)</h3>
                <a href="<?= site_url('checkin') ?>" class="btn btn-secondary">Kembali</a>
            </div>

            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('message')): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($this->session->flashdata('message'), ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="post" action="<?= site_url('checkin/store') ?>" id="frmSTR">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
                        value="<?= $this->security->get_csrf_hash() ?>" />

                    <!-- Header STR -->
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>No STR</label>
                            <input type="text" name="no_str" class="form-control"
                                value="<?= htmlspecialchars($no_str_suggest ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                placeholder="(auto) bisa diganti" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label>No SJ (Supplier)</label>
                            <input type="text" name="no_sj" class="form-control">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Tanggal Kedatangan</label>
                            <input type="date" name="arrival_date" class="form-control" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Catatan</label>
                            <input type="text" name="notes" class="form-control">
                        </div>
                        <div class="form-group col-md-3">
                            <label>No WO</label>
                            <input type="text" id="no_wo" name="no_wo" class="form-control" readonly> <!-- Make it readonly if it's set automatically -->
                        </div>
                    </div>


                    <!-- Tambah items dari banyak WO -->
                    <div class="border rounded p-3 mb-3">
                        <div class="form-row align-items-end">
                            <div class="form-group col-md-6">
                                <label>Tambah Items dari WO</label>
                                <!-- Dropdown pilih WO -->
                                <select class="form-control" id="selWO">
                                    <option value="">- Pilih WO -</option>
                                    <?php if (!empty($wo_list)): foreach ($wo_list as $w): ?>
                                            <option value="<?= (int)$w['id'] ?>" data-no-wo="<?= htmlspecialchars($w['no_wo'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-brand-id="<?= (int)$w['brand_id'] ?>">
                                                #<?= (int)$w['id'] ?> - <?= htmlspecialchars(($w['no_wo'] ?? '') . ' - ' . ($w['item_name'] ?? '') . ' (' . ($w['brand_name'] ?? '') . ')', ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                    <?php endforeach;
                                    endif; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <button type="button" class="btn btn-primary" id="btnAddWO">Tambah Items WO</button>
                            </div>
                        </div>
                        <small class="text-muted">Anda dapat menambahkan items dari beberapa WO sekaligus.</small>
                    </div>

                    <!-- Tabel detail -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm" id="tblDetails">
                            <thead>
                                <tr>
                                    <th style="width:80px">WO</th>
                                    <th>Material</th>
                                    <th class="text-right" style="width:120px">Cons.</th>
                                    <th class="text-right" style="width:140px">Req. (WO)</th>
                                    <th class="text-right" style="width:140px">Qty In</th>
                                    <th style="width:260px">Ringkasan Size</th>
                                    <th style="width:220px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-success">Simpan STR</button>
                        <a href="<?= site_url('checkin') ?>" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</section>

<!-- MODAL GLOBAL QTY -->
<div class="modal fade" id="modalGlobal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set Global Qty</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="number" min="0" step="any" class="form-control" id="inpGlobalQty">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnSaveGlobal">Simpan</button>
            </div>
        </div>
    </div>
</div>


<!-- MODAL SIZE -->
<div class="modal fade" id="modalSize" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set Qty per Size</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>

            <div class="modal-body">

                <div id="sizeForm"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnSaveSize">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        let rowIndex = 0;
        let currentRow = null;

        function nf(x) {
            if (x === null || x === undefined) return '0';
            return Number(x).toFixed(6).replace(/\.?0+$/, '');
        }

        // Tambah items dari WO → generate baris-baris material (WO L3)
        document.getElementById('btnAddWO').addEventListener('click', function() {
            const sel = document.getElementById('selWO');
            const woId = sel.value;
            const noWo = sel.options[sel.selectedIndex].getAttribute('data-no-wo'); // Ambil No WO yang sesuai
            if (!woId) return;

            // Set No WO pada field input
            document.getElementById('no_wo').value = noWo;

            fetch('<?= site_url('checkin/ajax_wo_items') ?>/' + woId)
                .then(r => r.json())
                .then(p => {
                    if (!p.ok || !Array.isArray(p.rows)) return;
                    const tb = document.querySelector('#tblDetails tbody');

                    p.rows.forEach(m => {
                        const i = rowIndex++;
                        const tr = document.createElement('tr');
                        tr.dataset.rowIdx = String(i);
                        tr.dataset.woId = String(m.wo_l1_id);

                        tr.innerHTML = `
                    <td>#${m.wo_l1_id}
                        <input type="hidden" name="details[${i}][wo_l1_id]" value="${m.wo_l1_id}">
                        <input type="hidden" name="details[${i}][item_id]" value="${m.item_id}">
                    </td>
                    <td>${(m.item_code || '') + ' - ' + (m.item_name || '')}</td>
                    <td class="text-right">${nf(m.consumption || 0)}</td>
                    <td class="text-right">${nf(m.required_qty || 0)}</td>
                    <td class="text-right">
                        <input type="number" min="0" step="any" class="form-control form-control-sm text-right inpQtyIn"
                            name="details[${i}][qty_in]" value="0" readonly>
                        <div id="sizesContainer_${i}" style="display:none"></div>
                    </td>
                    <td id="sizeSummary_${i}" class="small text-muted"><em>—</em></td>
                    <td>
                        <button type="button" class="btn btn-outline-primary btn-sm btnGlobal">Global</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm btnSize ml-1">Size</button>
                        <button type="button" class="btn btn-danger btn-sm ml-1 btnDelRow">Hapus</button>
                    </td>
                `;
                        tb.appendChild(tr);
                    });
                });
        });

        // Delegasi tombol pada tabel
        document.querySelector('#tblDetails tbody').addEventListener('click', function(e) {
            const btn = e.target.closest('button');
            if (!btn) return;

            const tr = btn.closest('tr');
            const i = tr.dataset.rowIdx;

            // Hapus baris jika tombol hapus diklik
            if (btn.classList.contains('btnDelRow')) {
                tr.remove(); // Hapus baris
                return;
            }

            if (btn.classList.contains('btnGlobal')) {
                currentRow = tr; // Menyimpan referensi baris yang saat ini
                document.getElementById('inpGlobalQty').value = tr.querySelector('.inpQtyIn').value || ''; // Set input qty
                $('#modalGlobal').modal('show'); // Menampilkan modal
                return;
            }

            // Per Size
            if (btn.classList.contains('btnSize')) {
                currentRow = tr;
                const woId = tr.dataset.woId;

                fetch('<?= site_url('checkin/ajax_sizes_by_wo') ?>/' + woId)
                    .then(r => r.json())
                    .then(p => {
                        console.log(p); // Debugging response untuk memastikan data tersedia
                        const cont = document.getElementById('sizeForm');
                        cont.innerHTML = '';

                        const sizes_data = p.sizes || []; // Ambil data sizes_data dari response JSON

                        if (sizes_data.length) {
                            const frag = document.createDocumentFragment();
                            sizes_data.forEach(s => {
                                const qty = s.qty || 0; // Menampilkan qty yang tersedia untuk setiap size

                                const row = document.createElement('div');
                                row.className = 'form-row mb-2';
                                row.innerHTML = `
                            <div class="col-6"><label class="mb-0">${s.size_name} (Available: ${qty})</label></div>
                            <div class="col-6"><input type="number" min="0" step="any" class="form-control form-control-sm inpSize" data-size-id="${s.size_id}" placeholder="Qty"></div>
                        `;
                                frag.appendChild(row);
                            });
                            cont.appendChild(frag);

                            // preload jika sebelumnya sudah ada data size yang dipilih
                            const hidCont = document.getElementById('sizesContainer_' + i);
                            if (hidCont) {
                                const map = {};
                                hidCont.querySelectorAll('input[name^="details[' + i + '][sizes]"][name$="[size_id]"]').forEach(inp => {
                                    const nm = inp.getAttribute('name');
                                    const qn = nm.replace('[size_id]', '[qty]');
                                    const qv = hidCont.querySelector('input[name="' + qn + '"]');
                                    map[String(inp.value)] = qv ? qv.value : '';
                                });
                                document.querySelectorAll('#sizeForm .inpSize').forEach(inp => {
                                    const sid = String(inp.dataset.sizeId);
                                    if (map[sid] !== undefined) inp.value = map[sid];
                                });
                            }
                        } else {
                            cont.innerHTML = '<div class="text-muted">Brand belum memiliki data size.</div>';
                        }
                        $('#modalSize').modal('show');
                    });
                return;
            }
        });

        // Simpan Global
        document.getElementById('btnSaveGlobal').addEventListener('click', function() {
            if (!currentRow) return;
            const qty = document.getElementById('inpGlobalQty').value || '0';
            const i = currentRow.dataset.rowIdx;

            // override: hapus breakdown size & ringkasan
            const cont = document.getElementById('sizesContainer_' + i);
            if (cont) cont.innerHTML = '';
            const sum = document.getElementById('sizeSummary_' + i);
            if (sum) sum.innerHTML = '<em>—</em>';

            currentRow.querySelector('.inpQtyIn').value = qty;
            $('#modalGlobal').modal('hide');
        });

        document.getElementById('btnSaveSize').addEventListener('click', function() {
            if (!currentRow) return;
            const i = currentRow.dataset.rowIdx;
            const container = document.getElementById('sizesContainer_' + i);
            const summaryTd = document.getElementById('sizeSummary_' + i);
            container.innerHTML = ''; // Clear previous size entries

            let total = 0;
            let j = 0;
            const lines = [];

            // Loop through all size inputs in the modal
            document.querySelectorAll('#sizeForm .inpSize').forEach(inp => {
                const sid = inp.dataset.sizeId;
                const q = parseFloat(inp.value || '0') || 0; // Get the quantity entered for the size
                const label = inp.closest('.form-row').querySelector('label').textContent;

                // Check if the quantity is greater than zero before processing it
                if (q > 0) {
                    const a = document.createElement('input');
                    a.type = 'hidden';
                    a.name = `details[${i}][sizes][${j}][size_id]`;
                    a.value = sid;
                    const b = document.createElement('input');
                    b.type = 'hidden';
                    b.name = `details[${i}][sizes][${j}][qty]`;
                    b.value = q;
                    container.appendChild(a);
                    container.appendChild(b);

                    // Add the label and quantity to the summary
                    lines.push(`${label}: ${nf(q)}`);
                    total += q;
                    j++;
                }
            });

            currentRow.querySelector('.inpQtyIn').value = total.toString();
            container.style.display = (j > 0 ? 'block' : 'none'); // Show the size container only if there are sizes entered

            // Update the size summary
            summaryTd.innerHTML = j > 0 ?
                `<ul class="mb-1">${lines.map(s => `<li>${s}</li>`).join('')}</ul><div><strong>Total: ${nf(total)}</strong></div>` :
                '<em>—</em>';

            // Close the modal after saving
            $('#modalSize').modal('hide');
        });

    })();
</script>