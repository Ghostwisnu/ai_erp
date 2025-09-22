<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        <?= html_escape($title ?? 'Create Production Report') ?>
                        <span class="tools pull-right">
                            <a href="<?= site_url('production/ro_list') ?>" class="btn btn-sm btn-default">Back</a>
                        </span>
                    </header>

                    <div class="card-body">
                        <?php if ($this->session->flashdata('flash_error')): ?>
                            <div class="alert alert-danger"><?= $this->session->flashdata('flash_error') ?></div>
                        <?php endif; ?>

                        <?php
                        $action = !empty($is_update)
                            ? site_url('production/update_store/' . (int)($prod_id ?? 0))
                            : site_url('production/store');
                        ?>
                        <form method="post" action="<?= $action ?>">
                            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

                            <!-- Header -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kode Production</label>
                                        <input type="text" name="prod_code" class="form-control" value="<?= html_escape($prod_code_suggest ?? '') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Kode RO</label>
                                        <input type="text" class="form-control" value="<?= html_escape($ro['no_ro'] ?? '') ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>No WO</label>
                                        <input type="text" name="no_wo" class="form-control" value="<?= html_escape($wo_no ?? '') ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Item (WO L2)</label>
                                        <input type="text" class="form-control" value="<?= html_escape($sfg_label ?? '') ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Brand</label>
                                        <input type="text" class="form-control" value="<?= html_escape($brand_name ?? '') ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Art &amp; Color</label>
                                        <input type="text" class="form-control" name="art_color" value="<?= html_escape($ro['art_color'] ?? '') ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Departement</label>
                                        <input type="text" class="form-control" value="<?= html_escape($departement ?? '') ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Total Qty (auto)</label>
                                        <input type="number" step="1" min="0" class="form-control" name="total_qty" id="total_qty" value="0" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Hidden essentials -->
                            <input type="hidden" name="ro_id" value="<?= (int)($ro['id'] ?? 0) ?>">
                            <input type="hidden" name="wo_l1_id" value="<?= (int)($ro['wo_l1_id'] ?? 0) ?>">
                            <input type="hidden" name="wo_l2_id" value="<?= (int)($ro['wo_l2_id'] ?? 0) ?>">
                            <input type="hidden" name="brand_id" value="<?= (int)($ro['brand_id'] ?? 0) ?>">
                            <input type="hidden" name="departement_id" value="<?= (int)($ro['departement_id'] ?? 0) ?>">

                            <!-- Body: Tabel Size -->
                            <h5 class="mt-3">Input Per Size</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="tblSizes">
                                    <thead>
                                        <tr>
                                            <th style="width:50px;">#</th>
                                            <th>Size</th>
                                            <th style="width:150px;" class="text-right">Plan (WO)</th>
                                            <th style="width:150px;">Input Qty</th>
                                            <th style="width:150px;" class="text-right">
                                                <?php if (!empty($is_update)): ?>Reported Before<?php else: ?>Reported Before<?php endif; ?>
                                            </th>
                                            <th style="width:210px;">Status</th>
                                            <th style="width:150px;">Qty Kurang</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($sizes)): $i = 1;
                                            foreach ($sizes as $sz):
                                        ?>
                                                <?php
                                                $plan = isset($plan_map[$sz['id']]) ? (int)$plan_map[$sz['id']] : 0;
                                                $repb = isset($reported_map[$sz['id']]) ? (int)$reported_map[$sz['id']] : 0;
                                                ?>
                                                <tr>
                                                    <td><?= $i++ ?></td>
                                                    <td>
                                                        <?= html_escape($sz['size_name']) ?>
                                                        <input type="hidden" name="size_id[]" value="<?= (int)$sz['id'] ?>">
                                                    </td>
                                                    <td class="text-right">
                                                        <input type="number" step="1" min="0" class="form-control form-control-sm text-right plan"
                                                            value="<?= $plan ?>" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="1" min="0"
                                                            class="form-control form-control-sm input-qty"
                                                            name="input_qty[]" value="0">
                                                    </td>

                                                    <!-- NEW: Reported Before (readonly & tersebar ke JS lewat data-prev) -->
                                                    <td class="text-right">
                                                        <input type="number" step="1" min="0"
                                                            class="form-control form-control-sm reported-before"
                                                            value="<?= $repb ?>" readonly>
                                                        <input type="hidden" name="reported_before[]" value="<?= $repb ?>"> <!-- jika ingin kirim (server tetap re-check DB) -->
                                                    </td>

                                                    <td>
                                                        <select name="status_size[]" class="form-control form-control-sm">
                                                            <option value="ok">OK / Selesai</option>
                                                            <option value="belum_diproduksi">Belum Diproduksi</option>
                                                            <option value="defect_bisa_diperbaiki">Defect bisa diperbaiki</option>
                                                            <option value="defect_tidak_bisa_diperbaiki">Defect tidak bisa diperbaiki</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="1" min="0"
                                                            class="form-control form-control-sm short"
                                                            name="short_qty[]" value="0" readonly>
                                                    </td>
                                                </tr>
                                            <?php endforeach;
                                        else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center">Brand belum memiliki master size.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-right mt-2">
                                <button class="btn btn-primary">Confirm & Save</button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>

<script>
    (function() {
        const toInt = v => Number.isFinite(parseInt(v, 10)) ? parseInt(v, 10) : 0;

        const tbl = document.getElementById('tblSizes');
        const total = document.getElementById('total_qty');
        const form = tbl.closest('form');

        function allowedMax(tr) {
            const plan = toInt(tr.querySelector('.plan')?.value);
            const prev = toInt(tr.querySelector('.reported-before')?.value) || 0;
            return Math.max(0, plan - prev);
        }

        function recalcRow(tr) {
            const max = allowedMax(tr);
            const inpEl = tr.querySelector('.input-qty');
            const shortEl = tr.querySelector('.short');

            // clamp input ke batas maksimal
            let v = toInt(inpEl.value);
            if (v > max) {
                v = max;
                inpEl.value = max;
            }

            // hitung short = plan - (prev + input)
            const plan = toInt(tr.querySelector('.plan')?.value);
            const prev = toInt(tr.querySelector('.reported-before')?.value) || 0;
            const need = Math.max(0, plan - (prev + v));
            if (shortEl) shortEl.value = need;

            // tulis atribut max agar native validation juga jalan
            inpEl.setAttribute('max', max);
            inpEl.setAttribute('min', 0);

            // styling invalid kalau value > max (shouldn't happen krn di-clamp, tapi jaga2)
            if (toInt(inpEl.value) > max) {
                inpEl.classList.add('is-invalid');
            } else {
                inpEl.classList.remove('is-invalid');
            }

            return toInt(inpEl.value);
        }

        function recalcAll() {
            let sum = 0;
            tbl.querySelectorAll('tbody tr').forEach(tr => {
                sum += recalcRow(tr) || 0;
            });
            total.value = sum; // total input BARU
        }

        // Recalc & clamp saat user mengetik
        ['input', 'change'].forEach(ev => {
            tbl.addEventListener(ev, e => {
                if (e.target.classList.contains('input-qty')) {
                    const tr = e.target.closest('tr');
                    recalcRow(tr);
                    // re-hit total
                    let s = 0;
                    tbl.querySelectorAll('tbody tr .input-qty').forEach(el => s += toInt(el.value));
                    total.value = s;
                }
            });
        });

        // Cek hard saat submit (block confirm kalau melanggar)
        form.addEventListener('submit', function(e) {
            let ok = true;
            let messages = [];

            tbl.querySelectorAll('tbody tr').forEach((tr, idx) => {
                const plan = toInt(tr.querySelector('.plan')?.value);
                const prev = toInt(tr.querySelector('.reported-before')?.value) || 0;
                const add = toInt(tr.querySelector('.input-qty')?.value);
                const max = Math.max(0, plan - prev);

                if (add > max) {
                    ok = false;
                    const sizeName = (tr.querySelector('td:nth-child(2)')?.innerText || 'size').trim();
                    messages.push(`Baris ${idx+1} (${sizeName}): input ${add} > sisa Plan ${max}.`);
                    tr.querySelector('.input-qty')?.classList.add('is-invalid');
                }
            });

            if (!ok) {
                e.preventDefault();
                alert('Input qty melebihi Plan. Perbaiki dulu:\n\n' + messages.join('\n'));
            }
        });

        // set max awal & total
        recalcAll();
    })();
</script>