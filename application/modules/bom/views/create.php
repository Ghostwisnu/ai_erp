<section id="main-content">
    <section class="wrapper">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Formulir BOM Baru</h3>
                <a href="<?= site_url('bom') ?>" class="btn btn-secondary">Kembali</a>
            </div>

            <div class="card-body">
                <form method="post" action="<?= site_url('bom/store') ?>">
                    <!-- CSRF -->
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>" />

                    <!-- Level 1: Finished Goods -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="item_id">Item Barang Jadi</label>
                            <select class="form-control" name="item_id" id="item_id" required>
                                <option value="">- Pilih Item -</option>
                                <?php foreach ($items as $item): ?>
                                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="unit_id">Unit</label>
                            <input type="text" class="form-control" id="unit_id" value="" readonly required />
                            <input type="hidden" name="unit_id_hidden" id="unit_id_hidden" />
                        </div>

                        <div class="col-md-4">
                            <label for="brand_id">Brand</label>
                            <input type="text" class="form-control" id="brand_id" value="" readonly required />
                            <input type="hidden" name="brand_id_hidden" id="brand_id_hidden" />
                        </div>

                        <div class="col-md-12 mt-3">
                            <label for="art_color">Art &amp; Color</label>
                            <input type="text" class="form-control" name="art_color" id="art_color" required>
                        </div>
                    </div>

                    <!-- Level 2 -->
                    <div class="d-flex align-items-center mb-2">
                        <h5 class="mb-0 mr-3">Barang Setengah Jadi (SFG)</h5>
                        <button type="button" class="btn btn-primary btn-sm" id="btnAddSFG">Tambah SFG</button>
                    </div>

                    <div id="level2_items">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th style="width:35%">SFG</th>
                                    <th style="width:45%">Material (Ringkasan)</th>
                                    <th style="width:20%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyLevel2"><!-- baris SFG akan muncul di sini --></tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-success">Simpan</button>
                        <a href="<?= site_url('bom') ?>" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</section>

<!-- Modal Level 3 -->
<div class="modal fade" id="modalLevel3" tabindex="-1" role="dialog" aria-labelledby="modalLevel3Label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLevel3Label">Pilih Material untuk SFG</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeLevel3Modal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="level3ModalBody"></div>
                <button type="button" class="btn btn-outline-primary mt-2" id="btnAddMaterialRow">Tambah Baris Material</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeLevel3Modal()">Batal</button>
                <button type="button" class="btn btn-success" id="btnSaveMaterials">Simpan Material</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        // ====== State ======
        let level2Index = 0; // index baris SFG (i)
        let currentSfgContext = {
            i: null,
            sfgId: null,
            options: []
        };

        const itemSelect = document.getElementById('item_id');
        const unitInput = document.getElementById('unit_id');
        const unitHidden = document.getElementById('unit_id_hidden');
        const brandInput = document.getElementById('brand_id');
        const brandHidden = document.getElementById('brand_id_hidden');
        const tbodyLevel2 = document.getElementById('tbodyLevel2');

        // ====== Utils ======
        function el(tag, attrs = {}, html = '') {
            const e = document.createElement(tag);
            for (const [k, v] of Object.entries(attrs)) {
                if (k === 'class') e.className = v;
                else e.setAttribute(k, v);
            }
            if (html) e.innerHTML = html;
            return e;
        }

        function openLevel3Modal() {
            const modal = document.getElementById('modalLevel3');
            if (window.jQuery && jQuery(modal).modal) jQuery(modal).modal('show');
            else {
                modal.classList.add('show');
                modal.style.display = 'block';
                document.body.classList.add('modal-open');
                document.body.appendChild(el('div', {
                    class: 'modal-backdrop fade show'
                }));
            }
        }
        window.closeLevel3Modal = function() {
            const modal = document.getElementById('modalLevel3');
            if (window.jQuery && jQuery(modal).modal) jQuery(modal).modal('hide');
            else {
                modal.classList.remove('show');
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
            }
            document.getElementById('level3ModalBody').innerHTML = '';
            currentSfgContext = {
                i: null,
                sfgId: null,
                options: []
            };
        }

        function renderLevel3ModalTable() {
            const body = document.getElementById('level3ModalBody');
            body.innerHTML = `
      <table class="table table-sm table-bordered mb-2" id="tblLevel3">
        <thead><tr>
          <th style="width:55%">Material</th>
          <th style="width:30%">Consumption</th>
          <th style="width:15%">Aksi</th>
        </tr></thead>
        <tbody></tbody>
      </table>`;
        }

        function addMaterialRowToModal(itemId = '', consumption = '') {
            const tbody = document.querySelector('#tblLevel3 tbody');
            const tr = document.createElement('tr');

            const tdMat = document.createElement('td');
            const sel = document.createElement('select');
            sel.className = 'form-control selMaterial';
            sel.required = true;
            sel.innerHTML = `<option value="">- Pilih Material -</option>` +
                currentSfgContext.options.map(o => `<option value="${o.id}">${o.item_name}</option>`).join('');
            if (itemId) sel.value = String(itemId);
            tdMat.appendChild(sel);

            const tdCons = document.createElement('td');
            const inp = document.createElement('input');
            inp.type = 'number';
            inp.min = '0';
            inp.step = 'any';
            inp.required = true;
            inp.className = 'form-control inpConsumption';
            inp.value = consumption || '';
            tdCons.appendChild(inp);

            const tdAct = document.createElement('td');
            const btnDel = el('button', {
                type: 'button',
                class: 'btn btn-sm btn-danger'
            }, 'Hapus');
            btnDel.addEventListener('click', () => tr.remove());
            tdAct.appendChild(btnDel);

            tr.appendChild(tdMat);
            tr.appendChild(tdCons);
            tr.appendChild(tdAct);
            tbody.appendChild(tr);
        }

        // ====== FG dipilih → auto Unit & Brand; SFG dapat dari brand ======
        itemSelect.addEventListener('change', function() {
            const itemId = this.value;
            if (!itemId) return;

            // 1) Get unit & brand dari item FG
            fetch('<?= site_url('bom/get_unit_and_brand_by_item') ?>/' + itemId)
                .then(r => r.json())
                .then(data => {
                    unitInput.value = data.units[0]?.name || '';
                    unitHidden.value = data.units[0]?.id || '';
                    brandInput.value = data.brands[0]?.name || '';
                    brandHidden.value = data.brands[0]?.id || '';
                });
        });

        // ====== Tambah SFG (Level-2) ======
        document.getElementById('btnAddSFG').addEventListener('click', function() {
            const brandId = brandHidden.value;
            if (!brandId) {
                alert('Pilih Item (FG) terlebih dahulu agar Brand terisi.');
                return;
            }

            // Ambil SFG berdasarkan brand
            fetch('<?= site_url('bom/get_level2_by_brand') ?>/' + brandId)
                .then(r => r.json())
                .then(data => {
                    const sfgs = Array.isArray(data.level2_items) ? data.level2_items : [];
                    if (!sfgs.length) {
                        alert('Tidak ada SFG untuk brand ini.');
                        return;
                    }

                    // Pilih SFG sederhana via prompt (bisa diganti modal dropdown jika mau)
                    const pick = prompt('Ketik nomor urut SFG yang dipilih:\n' + sfgs.map((s, idx) => `${idx+1}. ${s.item_name}`).join('\n'));
                    const idx = parseInt(pick, 10) - 1;
                    if (isNaN(idx) || idx < 0 || idx >= sfgs.length) return;
                    const s = sfgs[idx];

                    const i = level2Index++;
                    const tr = document.createElement('tr');
                    tr.setAttribute('data-index', i);
                    tr.setAttribute('data-sfg-id', s.id);

                    // kolom SFG
                    const tdSfg = document.createElement('td');
                    tdSfg.innerHTML = `
          <input type="text" class="form-control" value="${s.item_name}" readonly>
          <input type="hidden" name="level2_items[${i}][item_id]" value="${s.id}">
        `;

                    // kolom Summary
                    const tdSum = document.createElement('td');
                    tdSum.id = `mat_summary_${i}`;
                    tdSum.innerHTML = `<em>Belum ada material.</em>`;

                    // kolom Aksi
                    const tdAct = document.createElement('td');
                    const btnEdit = el('button', {
                        type: 'button',
                        class: 'btn btn-primary btn-sm btnEditMaterials'
                    }, 'Tambah / Edit Material');
                    const btnDel = el('button', {
                        type: 'button',
                        class: 'btn btn-danger btn-sm ml-1 btnRemoveSFG'
                    }, 'Hapus SFG');
                    tdAct.appendChild(btnEdit);
                    tdAct.appendChild(btnDel);

                    tr.appendChild(tdSfg);
                    tr.appendChild(tdSum);
                    tr.appendChild(tdAct);
                    tbodyLevel2.appendChild(tr);
                });
        });

        // ====== Delegasi aksi pada tabel L2 ======
        tbodyLevel2.addEventListener('click', function(e) {
            const target = e.target;

            // Hapus SFG
            if (target.classList.contains('btnRemoveSFG')) {
                const tr = target.closest('tr');
                if (tr) tr.remove();
                return;
            }

            // Tambah/Edit Material (modal)
            if (target.classList.contains('btnEditMaterials')) {
                const tr = target.closest('tr');
                const i = parseInt(tr.getAttribute('data-index'), 10);
                const sfgId = tr.getAttribute('data-sfg-id');
                currentSfgContext = {
                    i,
                    sfgId,
                    options: []
                };

                // Ambil kandidat material (RAW + SFG sesama brand dari sfgId)
                fetch('<?= site_url('bom/get_raw_or_sfg_for_material') ?>/' + sfgId)
                    .then(r => r.json())
                    .then(payload => {
                        currentSfgContext.options = Array.isArray(payload.raw_and_sfg_items) ? payload.raw_and_sfg_items : [];
                        renderLevel3ModalTable();

                        // preload dari hidden (kalau sebelumnya sudah disimpan via modal)
                        const hidContainer = document.getElementById(`hidden_container_${i}`);
                        if (hidContainer) {
                            const itemInputs = hidContainer.querySelectorAll(`input[name^="level2_items[${i}][materials]"][name$="[item_id]"]`);
                            if (itemInputs.length) {
                                itemInputs.forEach(inpItem => {
                                    const name = inpItem.getAttribute('name'); // ...[item_id]
                                    const consName = name.replace('[item_id]', '[consumption]');
                                    const consInp = hidContainer.querySelector(`input[name="${consName}"]`);
                                    addMaterialRowToModal(inpItem.value, consInp ? consInp.value : '');
                                });
                            } else {
                                addMaterialRowToModal();
                            }
                        } else {
                            addMaterialRowToModal();
                        }
                        openLevel3Modal();
                    });
            }
        });

        // Tambah baris material di modal
        document.getElementById('btnAddMaterialRow').addEventListener('click', function() {
            addMaterialRowToModal();
        });

        // Simpan dari modal → tanam hidden + ringkasan
        document.getElementById('btnSaveMaterials').addEventListener('click', function() {
            const i = currentSfgContext.i;
            const sfgId = currentSfgContext.sfgId;
            if (i === null || !sfgId) return;

            const rows = Array.from(document.querySelectorAll('#tblLevel3 tbody tr'));
            const summary = [];
            let j = 0;

            // Hapus hidden lama untuk i
            const oldContainer = document.getElementById(`hidden_container_${i}`);
            if (oldContainer) oldContainer.remove();

            const container = document.createElement('div');
            container.id = `hidden_container_${i}`;
            container.style.display = 'none';

            for (const r of rows) {
                const sel = r.querySelector('.selMaterial');
                const inp = r.querySelector('.inpConsumption');
                const matId = sel && sel.value ? sel.value : '';
                const cons = inp && inp.value ? inp.value : '';

                if (!matId) continue;

                // hidden item_id
                const hid1 = document.createElement('input');
                hid1.type = 'hidden';
                hid1.name = `level2_items[${i}][materials][${j}][item_id]`;
                hid1.value = matId;
                container.appendChild(hid1);

                // hidden consumption
                const hid2 = document.createElement('input');
                hid2.type = 'hidden';
                hid2.name = `level2_items[${i}][materials][${j}][consumption]`;
                hid2.value = cons;
                container.appendChild(hid2);

                const m = currentSfgContext.options.find(o => String(o.id) === String(matId));
                summary.push(`${m ? m.item_name : ('ID:'+matId)} = ${cons}`);
                j++;
            }

            const tdSummary = document.getElementById(`mat_summary_${i}`);
            tdSummary.innerHTML = summary.length ? ('<ul>' + summary.map(s => `<li>${s}</li>`).join('') + '</ul>') : '<em>Belum ada material.</em>';
            tdSummary.appendChild(container);

            closeLevel3Modal();
        });

    })();
</script>