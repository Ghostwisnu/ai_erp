<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        <?= html_escape($title ?? 'Form') ?>
                        <span class="tools pull-right">
                            <a href="<?= $back_url ?>" class="btn btn-sm btn-secondary" style="color: white;">Kembali</a>
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                        </span>
                    </header>

                    <div class="card-body">
                        <?= validation_errors('<div class="alert alert-danger">', '</div>'); ?>

                        <form method="post" action="<?= $post_url ?>" enctype="multipart/form-data">
                            <?php 
                            foreach ($fields as $f):
                                $name  = $f['name'];
                                $type  = $f['type'] ?? 'text';
                                $value = isset($f['value']) ? $f['value'] : set_value($name);
                                $attrs = $f['attrs'] ?? '';
                                $placeholder = isset($f['placeholder']) ? 'placeholder="' . $f['placeholder'] . '"' : '';

                                // Tambahkan id agar bisa diakses JS
                                $idAttr = 'id="'.$name.'"';

                                // field autofill → readonly
                                $readonly = in_array($name, ['brand','artcolor_name','xfd','total_qty']) ? 'readonly' : '';

                                // jika ini field WO, render setelah PO Number
                                if ($name === 'po_number') {
                                    // render PO Number dulu
                            ?>
                                    <div class="form-group">
                                        <label><?= html_escape($f['label']) ?></label>
                                        <input type="<?= $type ?>" class="form-control" name="<?= $name ?>" id="<?= $name ?>" 
                                            value="<?= html_escape($value) ?>" <?= $placeholder ?> <?= $attrs ?>>
                                    </div>
                            <?php
                                    // cari field WO di $fields
                                    foreach ($fields as $wf) {
                                        if ($wf['name'] === 'wo_l1_ref') {
                                            $opts = $wf['options'] ?? [];
                            ?>
                                            <div class="form-group">
                                                <label><?= html_escape($wf['label']) ?></label>
                                                <select class="form-control" name="<?= $wf['name'] ?>" id="<?= $wf['name'] ?>">
                                                    <option value="">-- Pilih --</option>
                                                    <?php foreach ($opts as $optVal => $optLabel):
                                                        $sel = ((string)$optVal === (string)($wf['value'] ?? '')) ? 'selected' : '';
                                                    ?>
                                                        <option value='<?= html_escape($optVal) ?>' <?= $sel ?>><?= html_escape($optLabel) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                            <?php
                                        }
                                    }
                                    continue; // skip render normal untuk WO
                                }
                            ?>
                                <?php if ($name !== 'wo_l1_ref'): // WO sudah ditangani ?>
                                    <div class="form-group">
                                        <label><?= html_escape($f['label']) ?></label>
                                        <?php if ($type === 'select'): ?>
                                            <select class="form-control" name="<?= $name ?>" <?= $idAttr ?> <?= $attrs ?>>
                                                <?php foreach (($f['options'] ?? []) as $optVal => $optLabel):
                                                    $sel = ((string)$optVal === (string)$value) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= html_escape($optVal) ?>" <?= $sel ?>><?= html_escape($optLabel) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php elseif ($type === 'textarea'): ?>
                                            <textarea class="form-control" name="<?= $name ?>" <?= $idAttr ?> <?= $attrs ?>><?= html_escape($value) ?></textarea>
                                        <?php elseif ($type === 'file'): ?>
                                            <input type="file" class="form-control" name="<?= $name ?>" <?= $idAttr ?> <?= $attrs ?>>
                                        <?php else: ?>
                                            <input type="<?= $type ?>" class="form-control" name="<?= $name ?>" id="<?= $name ?>" 
                                                value="<?= html_escape($value) ?>" <?= $placeholder ?> <?= $attrs ?> <?= $readonly ?>>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary"><?= !empty($is_edit) ? 'Update' : 'Simpan' ?></button>
                                <a href="<?= $back_url ?>" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>

<!-- JavaScript untuk auto-fill -->
<script>
document.getElementById('wo_l1_ref').addEventListener('change', function() {
    let selected = this.value;
    if (selected) {
        try {
            let data = JSON.parse(selected);
            document.getElementById('artcolor_name').value = data.art_color;
            document.getElementById('xfd').value = data.xfd;
            document.getElementById('total_qty').value = parseInt(data.total_qty) || 0;
            document.getElementById('brand').value = data.brand; // isi brand juga
        } catch (e) {
            console.error("Parsing error: ", e);
        }
    } else {
        document.getElementById('artcolor_name').value = '';
        document.getElementById('xfd').value = '';
        document.getElementById('total_qty').value = '';
        document.getElementById('brand').value = '';
    }
});
</script>

