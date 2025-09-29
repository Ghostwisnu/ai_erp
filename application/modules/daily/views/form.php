<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        <?= html_escape($title ?? 'Form Daily Defect Production') ?>
                        <span class="tools pull-right">
                            <a href="<?= $back_url ?>" class="btn btn-sm btn-secondary">Kembali</a>
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                        </span>
                    </header>

                    <div class="card-body">
                        <?= validation_errors('<div class="alert alert-danger">', '</div>'); ?>

                        <form method="post" action="<?= $post_url ?>">
                            <!-- PO Number -->
                            <div class="form-group">
                                <label>PO Number</label>
                                <select class="form-control" name="po_number" id="po_number" required>
    <option value="">-- Pilih PO Number --</option>
    <?php foreach ($spk as $s): ?>
        <option value="<?= $s['po_number'] ?>"><?= $s['po_number'] ?></option>
    <?php endforeach; ?>
</select>

                            </div>

                            <!-- Art Color (auto) -->
                            <div class="form-group">
                                <label>Art Color</label>
                                <input type="text" class="form-control" id="artcolor_name" name="artcolor_name" 
                                    value="<?= set_value('artcolor_name', $row['artcolor_name'] ?? '') ?>" readonly required>
                            </div>

                            <!-- Brand (auto) -->
                            <div class="form-group">
                                <label>Brand</label>
                                <input type="text" class="form-control" id="brand" name="brand" 
                                    value="<?= set_value('brand', $row['brand'] ?? '') ?>" readonly required>
                            </div>

                            <!-- Total Qty (auto) -->
                            <div class="form-group">
                                <label>Total Qty</label>
                                <input type="number" class="form-control" id="total_qty" name="total_qty" 
                                    value="<?= set_value('total_qty', $row['total_qty'] ?? '') ?>" readonly required>
                            </div>

                            <!-- No DF Ariat (auto-generate) -->
                            <div class="form-group">
                                <label>No DF Ariat</label>
                                <input type="text" class="form-control" name="no_dfariat" 
                                    value="<?= set_value('no_dfariat', $row['no_dfariat'] ?? ($newDfNo ?? '')) ?>" 
                                    readonly required>
                                <small class="form-text text-muted">Nomor ini digenerate otomatis oleh sistem</small>
                            </div>



                            <div class="form-group">
                                <label>Tanggal Input</label>
                                <input type="date" class="form-control" name="tgl_input" 
                                    value="<?= set_value('tgl_input', $row['tgl_input'] ?? date('Y-m-d')) ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Departemen</label>
                                <select class="form-control" name="dept_name" required>
                                    <option value="">-- Pilih Departemen --</option>
                                    <?php 
                                    $departemen = ['LASTING', 'CEMENTING', 'FINISHING'];
                                    foreach ($departemen as $dept): 
                                        $sel = (set_value('dept_name', $row['dept_name'] ?? '') === $dept) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $dept ?>" <?= $sel ?>><?= $dept ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">
                                    <?= !empty($is_edit) ? 'Update' : 'Simpan' ?>
                                </button>
                                <a href="<?= $back_url ?>" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>


<script>
document.addEventListener("DOMContentLoaded", function() {
    const poSelect     = document.getElementById("po_number");
    const artcolorInput= document.getElementById("artcolor_name"); 
    const brandInput   = document.getElementById("brand");
    const qtyInput     = document.getElementById("total_qty");

    poSelect.addEventListener("change", function() {
        let po_number = this.value;
        if (po_number) {
            fetch("<?= site_url('daily/get_spk_info') ?>", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "po_number=" + encodeURIComponent(po_number)
            })
            .then(res => res.json())
            .then(data => {
                if (data) {
                    artcolorInput.value = data.artcolor_name ?? "";
                    brandInput.value    = data.brand ?? "";
                    qtyInput.value      = data.total_qty ?? "";
                } else {
                    artcolorInput.value = "";
                    brandInput.value    = "";
                    qtyInput.value      = "";
                }
            })
            .catch(err => console.error(err));
        } else {
            artcolorInput.value = "";
            brandInput.value    = "";
            qtyInput.value      = "";
        }
    });
});
</script>

