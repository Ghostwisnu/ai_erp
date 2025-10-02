<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        <?= html_escape($title ?? 'Preview Import Data') ?>
                        <span class="tools pull-right">
                            <a href="<?= $back_url ?>" class="btn btn-sm btn-secondary" style="color: black;">Kembali</a>
                        </span>
                    </header>
                    <div class="card-body">
                        <h4>Data yang Akan Diimpor:</h4>
                        <div class="adv-table">
                            <table class="display table table-bordered table-striped" id="preview-table">
                                <thead>
                                    <tr>
                                        <?php foreach ($import_preview_columns as $col): ?>
                                            <th><?= html_escape($col) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($import_preview as $row): ?>
                                        <tr>
                                            <?php foreach ($row as $cell): ?>
                                                <td><?= html_escape($cell) ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <form method="post" action="<?= $post_url ?>">
                                <button class="btn btn-sm btn-success mt-3" type="submit">Confirm Import</button>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>