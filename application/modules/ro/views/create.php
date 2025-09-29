<?php
$is_edit = !empty($header);
$action_url = $is_edit ? site_url('ro/update/' . (int)$header['id']) : site_url('ro/store');
?>
<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        <?= html_escape($title ?? 'Request Order') ?>
                        <span class="tools pull-right">
                            <a href="<?= site_url('ro') ?>" class="btn btn-sm btn-default" style="color: black;">Back</a>
                        </span>
                    </header>

                    <div class="card-body">
                        <?php if ($this->session->flashdata('message')): ?>
                            <div class="alert alert-success"><?= $this->session->flashdata('message') ?></div>
                        <?php endif; ?>
                        <?php if (!empty($flash_error)): ?>
                            <div class="alert alert-danger"><?= $flash_error ?></div>
                        <?php endif; ?>

                        <form method="post" action="<?= $action_url ?>" id="roForm" autocomplete="off">
                            <!-- Compact Header -->
                            <div class="container-fluid p-0">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <label>No. RO</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="no_ro" id="no_ro"
                                                value="<?= html_escape($header['no_ro'] ?? ($no_ro_suggest ?? '')) ?>" readonly>
                                            <?php if (!$is_edit): ?>
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary" type="button" id="btnGenRo">Auto</button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="col-md-3 mb-2">
                                        <label>WO (Level 1)</label>
                                        <div class="input-group">
                                            <input type="hidden" name="wo_l1_id" id="wo_l1_id" value="<?= (int)($header['wo_l1_id'] ?? 0) ?>">
                                            <input type="text" class="form-control" id="wo_display" placeholder="Select WO..." readonly
                                                value="<?= html_escape(($wo_info['no_wo'] ?? '')) ?>">
                                            <div class="input-group-append">
                                                <button class="btn btn-info" type="button" data-toggle="modal" data-target="#woModal">Pick</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3 mb-2">
                                        <label>SFG (WO Level 2)</label>
                                        <div class="input-group">
                                            <input type="hidden" name="wo_l2_id" id="wo_l2_id" value="<?= (int)($header['wo_l2_id'] ?? 0) ?>">
                                            <input type="text" class="form-control" id="sfg_display" placeholder="Select SFG..." readonly
                                                value="<?= !empty($header['wo_l2_id']) ? 'SFG ID ' . $header['wo_l2_id'] : '' ?>">
                                            <div class="input-group-append">
                                                <button class="btn btn-info" type="button" id="btnPickSfg" data-toggle="modal" data-target="#sfgModal" <?= empty($header['wo_l1_id']) ? 'disabled' : '' ?>>Pick</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3 mb-2">
                                        <label>Tanggal</label>
                                        <input type="date" class="form-control" name="ro_date" required
                                            value="<?= html_escape($header['ro_date'] ?? date('Y-m-d')) ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <label>Brand (auto)</label>
                                        <input type="text" class="form-control" id="brand_display" readonly
                                            value="<?= html_escape($header['brand_id'] ?? '') ?>">
                                        <input type="hidden" name="brand_id" id="brand_id" value="<?= html_escape($header['brand_id'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-3 mb-2">
                                        <label>Art & Color (auto)</label>
                                        <input type="text" class="form-control" name="art_color" id="art_color"
                                            readonly value="<?= html_escape($header['art_color'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-3 mb-2">
                                        <label>Departement</label>
                                        <select class="form-control" name="departement_id" required>
                                            <option value="">-- Select Departement --</option>
                                            <?php foreach (($departements ?? []) as $d): ?>
                                                <option value="<?= (int)$d['id'] ?>" <?= (!empty($header['departement_id']) && $header['departement_id'] == $d['id']) ? 'selected' : '' ?>>
                                                    <?= html_escape($d['code'] . ' - ' . $d['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-3 mb-2 d-flex align-items-end justify-content-end">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- BODY: Items -->
                            <h5 class="mb-2">Items</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="itemsTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Item</th> <!-- was Item ID -->
                                            <th>Required Qty (WO)</th>
                                            <th>Qty (Input)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($is_edit && !empty($details)): $i = 1;
                                            foreach ($details as $dt): ?>
                                                <tr>
                                                    <td><?= $i++ ?></td>
                                                    <td>
                                                        <?= html_escape(($dt['item_code'] ? $dt['item_code'] . ' - ' : '') . ($dt['item_name'] ?? $dt['item_id'])) ?>
                                                        <input type="hidden" name="wo_l3_id[]" value="<?= (int)$dt['wo_l3_id'] ?>">
                                                        <input type="hidden" name="item_id[]" value="<?= (int)$dt['item_id'] ?>">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm" name="required_qty[]" value="<?= html_escape($dt['required_qty']) ?>" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.000001" class="form-control form-control-sm" name="qty[]" value="<?= html_escape($dt['qty']) ?>">
                                                    </td>
                                                </tr>
                                        <?php endforeach;
                                        endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>

<!-- WO Modal -->
<div class="modal fade" id="woModal" tabindex="-1" role="dialog" aria-labelledby="woModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select WO (Level 1)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-2">
                    <input type="text" id="woSearch" class="form-control" placeholder="Search WO...">
                    <div class="input-group-append"><button class="btn btn-secondary" type="button" id="btnSearchWo">Search</button></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="woTable">
                        <thead>
                            <tr>
                                <th>No WO</th>
                                <th>Brand</th> <!-- was Brand ID -->
                                <th>Art & Color</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody><!-- filled by JS --></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SFG Modal -->
<div class="modal fade" id="sfgModal" tabindex="-1" role="dialog" aria-labelledby="sfgModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select SFG (WO Level 2)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <ul class="list-group" id="sfgList"><!-- filled by JS --></ul>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        function ajax(url, data, cb) {
            var xhr = new XMLHttpRequest();
            if (data && typeof data === 'object') {
                const qs = Object.keys(data).map(k => encodeURIComponent(k) + '=' + encodeURIComponent(data[k])).join('&');
                url += (url.indexOf('?') >= 0 ? '&' : '?') + qs;
            }
            xhr.open('GET', url, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    try {
                        cb(JSON.parse(xhr.responseText));
                    } catch (e) {
                        cb({
                            error: e.message
                        });
                    }
                }
            };
            xhr.send();
        }

        var btnGenRo = document.getElementById('btnGenRo');
        if (btnGenRo) {
            btnGenRo.addEventListener('click', function() {
                ajax('<?= site_url('ro/ajax_generate_no_ro') ?>', {}, function(res) {
                    if (res && res.no_ro) document.getElementById('no_ro').value = res.no_ro;
                });
            });
        }

        // WO search + pick
        var btnSearchWo = document.getElementById('btnSearchWo');
        var woSearch = document.getElementById('woSearch');

        function loadWo() {
            ajax('<?= site_url('ro/ajax_search_wo') ?>', {
                q: (woSearch.value || '')
            }, function(res) {
                var tb = document.querySelector('#woTable tbody');
                tb.innerHTML = '';
                (res.rows || []).forEach(function(r) {
                    var tr = document.createElement('tr');
                    tr.innerHTML =
                        '<td>' + (r.no_wo || '') + '</td>' +
                        '<td>' + (r.brand_name || '') + '</td>' + // brand name here
                        '<td>' + (r.art_color || '') + '</td>' +
                        '<td><button type="button" class="btn btn-sm btn-primary pick-wo" ' +
                        'data-id="' + r.id + '" data-no="' + (r.no_wo || '') + '">Pick</button></td>';
                    tb.appendChild(tr);
                });
            });
        }
        if (btnSearchWo) btnSearchWo.addEventListener('click', loadWo);
        loadWo(); // preload

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('pick-wo')) {
                var id = e.target.getAttribute('data-id');
                var no = e.target.getAttribute('data-no');
                document.getElementById('wo_l1_id').value = id;
                document.getElementById('wo_display').value = no;

                // fetch brand & art color
                ajax('<?= site_url('ro/ajax_get_wo_info') ?>/' + id, {}, function(res) {
                    if (res && !res.error) {
                        document.getElementById('brand_id').value = res.brand_id || '';
                        document.getElementById('brand_display').value = res.brand_name || ''; // show name
                        document.getElementById('art_color').value = res.art_color || '';
                        document.getElementById('btnPickSfg').removeAttribute('disabled');
                        // reset SFG & items
                        document.getElementById('wo_l2_id').value = '';
                        document.getElementById('sfg_display').value = '';
                        document.querySelector('#itemsTable tbody').innerHTML = '';
                    }
                });


                // load SFG list
                ajax('<?= site_url('ro/ajax_get_sfg_by_wo') ?>/' + id, {}, function(res) {
                    var ul = document.getElementById('sfgList');
                    ul.innerHTML = '';
                    (res.rows || []).forEach(function(sfg) {
                        var label = (sfg.item_code ? sfg.item_code + ' - ' : '') + (sfg.item_name || ('Item ID ' + sfg.item_id));
                        var li = document.createElement('li');
                        li.className = 'list-group-item d-flex justify-content-between align-items-center';
                        li.innerHTML = '<span>' + label + '</span>' +
                            ' <button type="button" class="btn btn-sm btn-primary pick-sfg" data-id="' + sfg.id + '" ' +
                            'data-label="' + label.replace(/"/g, '&quot;') + '">Pick</button>';
                        ul.appendChild(li);
                    });
                });

                $('#woModal').modal('hide');
            }
        });

        // Pick SFG
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('pick-sfg')) {
                var sfgId = e.target.getAttribute('data-id');
                document.getElementById('wo_l2_id').value = sfgId;
                document.getElementById('sfg_display').value = 'SFG ID ' + sfgId;

                // Load items by SFG
                ajax('<?= site_url('ro/ajax_get_items_by_sfg') ?>/' + sfgId, {}, function(res) {
                    var tb = document.querySelector('#itemsTable tbody');
                    tb.innerHTML = '';
                    (res.rows || []).forEach(function(r, idx) {
                        var itemLabel = (r.item_code ? r.item_code + ' - ' : '') + (r.item_name || ('Item ID ' + r.item_id));
                        var tr = document.createElement('tr');
                        tr.innerHTML =
                            '<td>' + (idx + 1) + '</td>' +
                            '<td>' + itemLabel +
                            '<input type="hidden" name="wo_l3_id[]" value="' + (r.wo_l3_id || '') + '">' +
                            '<input type="hidden" name="item_id[]" value="' + (r.item_id || '') + '">' +
                            '</td>' +
                            '<td><input type="text" class="form-control form-control-sm" name="required_qty[]" value="' + (r.required_qty || 0) + '" readonly></td>' +
                            '<td><input type="number" step="0.000001" class="form-control form-control-sm" name="qty[]" value="0"></td>';
                        tb.appendChild(tr);
                    });
                });

                $('#sfgModal').modal('hide');
            }
        });

    })();
</script>