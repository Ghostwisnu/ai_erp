<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>STR <?= htmlspecialchars($hdr['no_str'], ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 6px;
        }

        th {
            background: #eee;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <h3>Surat Tanda Terima</h3>
    <table>
        <tr>
            <th style="width:25%">No STR</th>
            <td><?= htmlspecialchars($hdr['no_str'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <th>No SJ</th>
            <td><?= htmlspecialchars($hdr['no_sj'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <th>Tanggal</th>
            <td><?= htmlspecialchars($hdr['arrival_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <th>Catatan</th>
            <td><?= nl2br(htmlspecialchars($hdr['notes'] ?? '', ENT_QUOTES, 'UTF-8')) ?></td>
        </tr>
    </table>

    <h4 style="margin-top:18px">Detail Items</h4>
    <table>
        <thead>
            <tr>
                <th style="width:70px">WO</th>
                <th>Material</th>
                <th class="text-right" style="width:120px">Qty In</th>
                <th style="width:220px">Sizes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <!-- Display the No WO from the header -->
                    <td>#<?= htmlspecialchars($hdr['no_wo'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(($r['item_code'] ?? '') . ' - ' . ($r['item_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-right"><?= rtrim(rtrim(number_format((float)$r['qty_in'], 6, '.', ''), '0'), '.') ?></td>
                    <td>
                        <?php $list = $sizes_map[(int)$r['id']] ?? [];
                        if ($list): ?>
                            <?php foreach ($list as $s): ?>
                                <div><?= htmlspecialchars($s['size_name'] ?? ('ID:' . $s['size_id']), ENT_QUOTES, 'UTF-8') ?> = <?= rtrim(rtrim(number_format((float)$s['qty'], 6, '.', ''), '0'), '.') ?></div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <em>-</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p style="margin-top:18px">Dicetak pada: <?= date('Y-m-d H:i:s') ?></p>
</body>

</html>