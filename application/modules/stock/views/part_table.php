<!-- application/modules/stock/views/part_table.php -->
<div class="table-responsive">
    <table class="table table-bordered table-striped table-sm">
        <thead>
            <tr>
                <th style="width:80px">ID</th>
                <th>Item Code</th>
                <th>Item Name</th>
                <th>Brand</th>
                <th>Unit</th>
                <th style="width:220px">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($rows)): foreach ($rows as $r): ?>
                    <tr>
                        <td>#<?= (int)$r['id'] ?></td>
                        <td><?= htmlspecialchars($r['item_code'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($r['item_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($r['brand_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($r['unit_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm" onclick="openCheckinDetail(<?= (int)$r['id'] ?>)">Detail Check-In</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openStockSummary(<?= (int)$r['id'] ?>)">Ringkasan Stok</button>
                        </td>
                    </tr>
                <?php endforeach;
            else: ?>
                <tr>
                    <td colspan="6" class="text-center text-muted">Tidak ada data.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>