<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800 text-center">
        <?= $title ?>
    </h1>

    <?= $this->session->flashdata('message'); ?>

    <?php if (!empty($row)) : ?>
        <!-- Info Header DF -->
        <div class="card mb-4">
            <div class="card-body">
                <h5>No DF Ariat: <?= $row['no_dfariat']; ?></h5>
                <p>
                    PO Number: <?= $row['po_number']; ?> |
                    Artikel/Color: <?= $row['artcolor_name']; ?> |
                    Brand: <?= $row['brand']; ?> |
                    Total Qty: <?= $row['total_qty']; ?>
                </p>
            </div>
        </div>

        <!-- Tabel Defect -->
        <div class="card shadow mb-4">
           <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Defect Categories</h6>
                <a href="<?= site_url('daily'); ?>" class="btn btn-sm btn-secondary">
                    ← Back
                </a>
            </div>

            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Kategori Defect</th>
                            <th>Jumlah</th>
                            <th style="width:80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($defects)) : ?>
                            <?php foreach ($defects as $def) : ?>
                                <tr>
                                    <td><?= $def['nama_defect']; ?></td>
                                    <td><?= $def['qty']; ?></td>
                                    <td>
                                        <a href="<?= site_url('defect_daily/tambah_defect/' . $row['id_df'] . '/' . $def['id_defect']); ?>" 
   class="btn btn-primary btn-xs">
   + Tambah
</a>

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="3" class="text-center">Belum ada data defect.</td>
                            </tr>
                        <?php endif; ?>
                        <tr class="font-weight-bold bg-light">
                            <td>Total Defect</td>
                            <td colspan="2"><?= $row['total_defect']; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Qty Produksi -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-info">Production Quantity</h6>
            </div>
            <div class="card-body">
                <?php 
                $qtyFields = [
                    'qty_lasting'   => 'Qty Lasting',
                    'qty_cementing' => 'Qty Cementing',
                    'qty_finishing' => 'Qty Finishing'
                ];
                foreach ($qtyFields as $field => $label): ?>
                    <form action="<?= site_url('defect_daily/simpan_qty'); ?>" method="post" class="form-inline mb-2">
                        <input type="hidden" name="id_df" value="<?= $row['id_df']; ?>">
                        <input type="hidden" name="field" value="<?= $field; ?>">
                        <div class="form-group mr-2">
                            <label for="<?= $field; ?>" class="mr-2"><?= $label; ?></label>
                            <input type="number" class="form-control form-control-sm" 
                                   name="qty" value="<?= $row[$field]; ?>">
                        </div>
                        <button type="submit" class="btn btn-xs btn-success">Save</button>
                    </form>
                <?php endforeach; ?>
            </div>
        </div>


        <!-- Export PDF 
        <a href="<?= site_url('defect_daily/export_pdf/' . $row['id_df']); ?>" 
           class="btn btn-danger btn-sm">
            Export PDF
        </a> -->
    <?php endif; ?>
</div>
                
<style>
    .btn-xs {
    padding: 0.15rem 0.35rem;   /* lebih kecil dari btn-sm */
    font-size: 0.7rem;
    line-height: 1.2;
    border-radius: 0.2rem;
}
</style>