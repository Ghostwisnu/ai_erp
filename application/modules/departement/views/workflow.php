<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        <?= html_escape($title ?? 'Workflow') ?>
                        <span class="tools pull-right">
                            <a href="<?= $back_url ?>" class="btn btn-sm btn-secondary">Kembali</a>
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                        </span>
                    </header>
                    <div class="card-body">
                        <?php if ($this->session->flashdata('message')): ?>
                            <div class="alert alert-success"><?= $this->session->flashdata('message') ?></div>
                        <?php endif; ?>

                        <p><strong>Departement awal:</strong> <?= html_escape($dept['code'] . ' — ' . $dept['name']); ?></p>

                        <form method="post" action="<?= $post_add ?>" class="form-inline mb-3">
                            <label class="mr-2">Lanjut ke:</label>
                            <select name="to_dept_id" class="form-control">
                                <?php foreach ($all_dept as $d): if ($d['id'] == $dept['id']) continue; ?>
                                    <option value="<?= $d['id'] ?>"><?= html_escape($d['code'] . ' — ' . $d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-primary ml-2" type="submit">Simpan Alur</button>
                            <a class="btn btn-danger ml-2" href="<?= $post_clear ?>" onclick="return confirm('Clear workflow?')">Clear</a>
                        </form>

                        <h4>Line Produksi (urut):</h4>
                        <ol>
                            <?php foreach ($sequence as $sid):
                                foreach ($all_dept as $d) {
                                    if ((int)$d['id'] === (int)$sid) {
                                        $curr = $d;
                                        break;
                                    }
                                }
                            ?>
                                <li><?= html_escape($curr['code'] . ' — ' . $curr['name']) ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>