<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        <?= html_escape($title ?? 'Preview Checkout') ?>
                        <span class="tools pull-right">
                            <a href="<?= site_url('checkout') ?>" class="btn btn-sm btn-default">Back</a>
                        </span>
                    </header>

                    <div class="card-body">
                        <?php if ($this->session->flashdata('message')): ?>
                            <div class="alert alert-success"><?= $this->session->flashdata('message') ?></div>
                        <?php endif; ?>
                        <?php if (!empty($flash_error)): ?>
                            <div class="alert alert-danger"><?= $flash_error ?></div>
                        <?php endif; ?>

                        <!-- Header Info -->
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <th style="width:160px;">RO No</th>
                                        <td><?= html_escape($header['no_ro'] ?? '') ?></td>
                                    </tr>
                                    <tr>
                                        <th>RO Date</th>
                                        <td><?= html_escape($header['ro_date'] ?? '') ?></td>
                                    </tr>
                                    <tr>
                                        <th>WO No</th>
                                        <td><?= html_escape($wo_no ?? $header['wo_l1_id'] ?? '') ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <th style="width:160px;">Brand</th>
                                        <td><?= html_escape($brand_name ?? $header['brand_id'] ?? '') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Art &amp; Color</th>
                                        <td><?= html_escape($header['art_color'] ?? '') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Departement</th>
                                        <td><?= html_escape($departement_name ?? $header['departement_id'] ?? '') ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <h5 class="mt-3">Kebutuhan vs Stok</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Item</th>
                                        <th class="text-right">Required (RO)</th>
                                        <th class="text-right">Qty In</th>
                                        <th class="text-right">Qty Out</th>
                                        <th class="text-right">Stock Balance</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($rows)): $i = 1;
                                        foreach ($rows as $r): ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= html_escape($r['item_label']) ?></td>
                                                <td class="text-right"><?= number_format($r['req_qty'], 6) ?></td>
                                                <td class="text-right"><?= number_format($r['total_in'], 6) ?></td>
                                                <td class="text-right"><?= number_format($r['total_out'], 6) ?></td>
                                                <td class="text-right"><?= number_format($r['stock_balance'], 6) ?></td>
                                                <td>
                                                    <?php if ($r['enough']): ?>
                                                        <span class="badge badge-success">OK</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Short</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach;
                                    else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No items</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 text-right">
                            <?php if (!empty($all_ok)): ?>
                                <form method="post" action="<?= site_url('checkout/confirm/' . (int)$header['id']) ?>" class="d-inline"
                                    onsubmit="return confirm('Lanjutkan checkout? Perubahan tidak bisa dibatalkan.');">
                                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>"
                                        value="<?= $this->security->get_csrf_hash(); ?>">
                                    <button class="btn btn-success">Confirm & Checkout</button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled>Stok tidak cukup</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>