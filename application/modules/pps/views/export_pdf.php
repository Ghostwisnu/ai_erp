<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Summary</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h1>Production Summary</h1>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No WO</th>
                <th>Kategori WO</th>
                <th>Brand Name</th>
                <th>Art & Color</th>
                <th>Cutting (Qty)</th>
                <th>Sewing (Qty)</th>
                <th>Semi (Qty)</th>
                <th>Lasting (Qty)</th>
                <th>Finishing (Qty)</th>
                <th>Packaging (Qty)</th>
                <th>Finish Goods (Qty)</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($wo_summary as $wo): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= html_escape($wo['no_wo']) ?></td>
                    <td><?= html_escape($wo['kategori_wo']) ?></td>
                    <td><?= html_escape($wo['brand_name']) ?></td>
                    <td><?= html_escape($wo['art_color']) ?></td>
                    <td><?= $wo['cutting'] ?></td>
                    <td><?= $wo['sewing'] ?></td>
                    <td><?= $wo['semi'] ?></td>
                    <td><?= $wo['lasting'] ?></td>
                    <td><?= $wo['finishing'] ?></td>
                    <td><?= $wo['packaging'] ?></td>
                    <td><?= $wo['finish_goods'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>