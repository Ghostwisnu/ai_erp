<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        <?= html_escape($title ?? 'Form') ?>
                        <span class="tools pull-right">
                            <a href="<?= $back_url ?>" class="btn btn-sm btn-secondary">Kembali</a>
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                        </span>
                    </header>

                    <div class="card-body">
                        <?= validation_errors('<div class="alert alert-danger">', '</div>'); ?>

                        <form method="post" action="<?= $post_url ?>" enctype="multipart/form-data">
                            <?php foreach ($fields as $f):
                                $name  = $f['name'];
                                $type  = $f['type'] ?? 'text';
                                $value = isset($f['value']) ? $f['value'] : set_value($name);
                                $attrs = $f['attrs'] ?? '';
                                $placeholder = isset($f['placeholder']) ? 'placeholder="' . $f['placeholder'] . '"' : '';
                            ?>
                                <div class="form-group">
                                    <label><?= html_escape($f['label']) ?></label>
                                    <?php if ($type === 'select'):
                                        $opts = $f['options'] ?? [];
                                    ?>
                                        <select class="form-control" name="<?= $name ?>" <?= $attrs ?>>
                                            <?php foreach ($opts as $optVal => $optLabel):
                                                $sel = ((string)$optVal === (string)$value) ? 'selected' : '';
                                            ?>
                                                <option value="<?= html_escape($optVal) ?>" <?= $sel ?>><?= html_escape($optLabel) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php elseif ($type === 'textarea'): ?>
                                        <textarea class="form-control" name="<?= $name ?>" <?= $attrs ?>><?= html_escape($value) ?></textarea>
                                    <?php elseif ($type === 'file'): ?>
                                        <input type="file" class="form-control" name="<?= $name ?>" <?= $attrs ?>>
                                    <?php else: ?>
                                        <input type="<?= $type ?>" class="form-control" name="<?= $name ?>" value="<?= html_escape($value) ?>" <?= $placeholder ?> <?= $attrs ?>>
                                    <?php endif; ?>
                                </div>
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