<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Production <?= htmlspecialchars($hdr['prod_code'] ?? '', ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
        }

        h2,
        h3 {
            margin: 0 0 8px 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 6px;
        }

        th {
            background: #eee;
        }

        .right {
            text-align: right;
        }

        .mt-8 {
            margin-top: 8px;
        }
    </style>
</head>

<body>
    <h2>Production Report</h2>
    <table style="width:100%; border:0; border-collapse:separate;">
        <tr>
            <td style="border:0; width:50%; vertical-align:top;">
                <strong>Kode Production:</strong> <?= htmlspecialchars($hdr['prod_code'] ?? '', ENT_QUOTES, 'UTF-8') ?><br>
                <strong>Kode RO:</strong> <?= htmlspecialchars($ro['no_ro'] ?? '', ENT_QUOTES, 'UTF-8') ?><br>
                <strong>No WO:</strong> <?= htmlspecialchars($wo_no ?? '', ENT_QUOTES, 'UTF-8') ?><br>
                <strong>Item (WO L2):</strong> <?= htmlspecialchars($sfg ?? '', ENT_QUOTES, 'UTF-8') ?><br>
            </td>
            <td style="border:0; width:50%; vertical-align:top;">
                <strong>Brand:</strong> <?= htmlspecialchars($brand ?? '', ENT_QUOTES, 'UTF-8') ?><br>
                <strong>Departement:</strong> <?= htmlspecialchars($dept ?? '', ENT_QUOTES, 'UTF-8') ?><br>
                <strong>Art & Color:</strong> <?= htmlspecialchars($hdr['art_color'] ?? '', ENT_QUOTES, 'UTF-8') ?><br>
                <strong>Total Qty:</strong> <?= (int)($hdr['total_qty'] ?? 0) ?><br>
            </td>
        </tr>
    </table>

    <h3 class="mt-8">Rincian Per Size</h3>
    <table>
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th>Size</th>
                <th class="right" style="width:90px;">Plan (WO)</th>
                <th class="right" style="width:90px;">Input Qty</th>
                <th class="right" style="width:90px;">Qty Kurang</th>
                <th style="width:180px;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($details)): $i = 1;
                foreach ($details as $d): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($d['size_name'] ?? ('#' . $d['size_id']), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="right"><?= (int)($d['plan_qty'] ?? 0) ?></td>
                        <td class="right"><?= (int)($d['input_qty'] ?? 0) ?></td>
                        <td class="right"><?= (int)($d['short_qty'] ?? 0) ?></td>
                        <td><?= htmlspecialchars($d['status_size'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach;
            else: ?>
                <tr>
                    <td colspan="6" style="text-align:center;">No data</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>