<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>RO <?= htmlspecialchars($header['no_ro'] ?? '') ?></title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #111;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .meta {
            margin-bottom: 12px;
        }

        .meta table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .meta td.label {
            width: 160px;
            color: #444;
        }

        .divider {
            height: 1px;
            background: #ccc;
            margin: 10px 0 14px;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
        }

        table.items th,
        table.items td {
            border: 1px solid #333;
            padding: 6px 8px;
        }

        table.items th {
            background: #f0f0f0;
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .small {
            font-size: 11px;
            color: #444;
        }

        .mt-16 {
            margin-top: 16px;
        }
    </style>
</head>

<body>

    <div class="title">Request Order (RO)</div>
    <div class="meta">
        <table>
            <tr>
                <td class="label">No. RO</td>
                <td>: <?= htmlspecialchars($header['no_ro'] ?? '') ?></td>
                <td class="label">Tanggal</td>
                <td>: <?= htmlspecialchars($header['ro_date'] ?? '') ?></td>
            </tr>
            <tr>
                <td class="label">No. WO (L1)</td>
                <td>: <?= htmlspecialchars($wo_no ?? ($header['wo_l1_id'] ?? '')) ?></td>
                <td class="label">SFG (WO L2)</td>
                <td>: <?= htmlspecialchars($sfg_label ?? ($header['wo_l2_id'] ?? '')) ?></td>
            </tr>
            <tr>
                <td class="label">Brand</td>
                <td>: <?= htmlspecialchars($brand_name ?? ($header['brand_id'] ?? '')) ?></td>
                <td class="label">Art &amp; Color</td>
                <td>: <?= htmlspecialchars($header['art_color'] ?? '') ?></td>
            </tr>
            <tr>
                <td class="label">Departement</td>
                <td>: <?= htmlspecialchars($departement_name ?? ($header['departement_id'] ?? '')) ?></td>
                <td class="label">Status</td>
                <td>: <?= htmlspecialchars($header['status_ro'] ?? 'draft') ?></td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th>Item</th>
                <th style="width:140px;">Required Qty (WO)</th>
                <th style="width:140px;">Qty (Input)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($details)): $i = 1;
                foreach ($details as $dt): ?>
                    <?php
                    // Prefer item_code & item_name jika tersedia; fallback ke item_id
                    $itemLabel = '';
                    if (!empty($dt['item_code']) || !empty($dt['item_name'])) {
                        $itemLabel = trim(($dt['item_code'] ?? '') . ' - ' . ($dt['item_name'] ?? ''));
                    } else {
                        $itemLabel = 'Item ID ' . $dt['item_id'];
                    }
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($itemLabel) ?></td>
                        <td class="right"><?= number_format((float)($dt['required_qty'] ?? 0), 6) ?></td>
                        <td class="right"><?= number_format((float)($dt['qty'] ?? 0), 6) ?></td>
                    </tr>
                <?php endforeach;
            else: ?>
                <tr>
                    <td colspan="4" class="small">No items.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="mt-16 small">
        Dicetak pada: <?= date('Y-m-d H:i:s') ?>
    </div>

</body>

</html>