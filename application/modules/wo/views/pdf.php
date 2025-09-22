<?php
// Helper format
function nfmt($n)
{
    $n = (float)$n;
    $s = number_format($n, 6, '.', '');
    $s = rtrim(rtrim($s, '0'), '.');
    return $s === '' ? '0' : $s;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>WO #<?= (int)$w1['id'] ?></title>
    <style>
        @page {
            margin: 24mm 16mm;
        }

        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #111;
        }

        h1,
        h2,
        h3 {
            margin: 0;
            padding: 0;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        .mb-3 {
            margin-bottom: 12px;
        }

        .mb-4 {
            margin-bottom: 16px;
        }

        .small {
            font-size: 11px;
            color: #555;
        }

        .muted {
            color: #777;
        }

        .hr {
            height: 1px;
            background: #ddd;
            margin: 10px 0 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px 8px;
            vertical-align: top;
        }

        th {
            background: #f0f0f0;
        }

        .no-border th,
        .no-border td {
            border: none;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .w-25 {
            width: 25%;
        }

        .w-35 {
            width: 35%;
        }

        .w-40 {
            width: 40%;
        }

        .w-60 {
            width: 60%;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <table class="no-border">
        <tr>
            <td>
                <h2>Work Order</h2>
                <div class="small">Dokumen otomatis</div>
            </td>
            <td class="text-right">
                <h3>#<?= (int)$w1['id'] ?></h3>
                <div class="small">Dicetak: <?= date('Y-m-d H:i') ?></div>
            </td>
        </tr>
    </table>
    <div class="hr"></div>

    <!-- WO Header Info -->
    <table>
        <tr>
            <th class="w-25">Item Code</th>
            <td class="w-35"><?= htmlspecialchars($header['item_code'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <th class="w-25">Date Order</th>
            <td class="w-15"><?= htmlspecialchars($w1['date_order'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <th>Item Name</th>
            <td><?= htmlspecialchars($header['item_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <th>X-Factory</th>
            <td><?= htmlspecialchars($w1['x_factory_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <th>Brand</th>
            <td><?= htmlspecialchars($header['brand_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <th>Unit</th>
            <td><?= htmlspecialchars($header['unit_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <th>Art & Color</th>
            <td><?= htmlspecialchars($w1['art_color'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <th>Total Size Qty</th>
            <td class="text-right"><?= nfmt($total_size_qty) ?></td>
        </tr>
        <tr>
            <th>Catatan</th>
            <td colspan="3"><?= nl2br(htmlspecialchars($w1['notes'] ?? '', ENT_QUOTES, 'UTF-8')) ?></td>
        </tr>
    </table>

    <!-- Size Run -->
    <h3 class="mb-2" style="margin-top:14px;">Size Run</h3>
    <table>
        <thead>
            <tr>
                <th class="w-40">Size</th>
                <th class="w-20">Qty</th>
                <th class="w-40">Note</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($sizes)): ?>
                <?php foreach ($sizes as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['size_name'] ?? ('#' . $s['size_id']), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-right"><?= nfmt($s['qty'] ?? 0) ?></td>
                        <td><?= htmlspecialchars($s['note'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="text-center muted">Tidak ada data size.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- BOM Detail -->
    <h3 class="mb-2" style="margin-top:14px;">SFG & Materials</h3>
    <table>
        <thead>
            <tr>
                <th class="w-25">SFG</th>
                <th class="w-60">Materials</th>
                <th class="w-15 text-right">Required Qty</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($l2_list)): ?>
                <?php foreach ($l2_list as $sfg): ?>
                    <?php
                    $rowspan = max(1, count($sfg['materials']));
                    $first = true;
                    ?>
                    <?php if ($rowspan === 1): ?>
                        <tr>
                            <td><?= htmlspecialchars($sfg['item_name'] ?? ('ID:' . $sfg['item_id']), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if (!empty($sfg['materials'])): ?>
                                    <?= htmlspecialchars($sfg['materials'][0]['item_name'] ?? ('ID:' . $sfg['materials'][0]['item_id']), ENT_QUOTES, 'UTF-8') ?>
                                    <span class="small muted"> (cons: <?= nfmt($sfg['materials'][0]['consumption'] ?? 0) ?>)</span>
                                <?php else: ?>
                                    <span class="muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right"><?= nfmt($sfg['materials'][0]['required_qty'] ?? 0) ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sfg['materials'] as $k => $m): ?>
                            <tr>
                                <?php if ($first): ?>
                                    <td rowspan="<?= $rowspan ?>"><?= htmlspecialchars($sfg['item_name'] ?? ('ID:' . $sfg['item_id']), ENT_QUOTES, 'UTF-8') ?></td>
                                    <?php $first = false; ?>
                                <?php endif; ?>
                                <td>
                                    <?= htmlspecialchars($m['item_name'] ?? ('ID:' . $m['item_id']), ENT_QUOTES, 'UTF-8') ?>
                                    <span class="small muted"> (cons: <?= nfmt($m['consumption'] ?? 0) ?>)</span>
                                </td>
                                <td class="text-right"><?= nfmt($m['required_qty'] ?? 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="text-center muted">Tidak ada SFG/material.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-right">Grand Total Required</th>
                <th class="text-right"><?= nfmt($grand_required) ?></th>
            </tr>
        </tfoot>
    </table>

    <div class="small muted" style="margin-top:16px;">
        * Required Qty adalah nilai tersimpan saat WO dibuat/diupdate (consumption × total size qty).
    </div>

</body>

</html>