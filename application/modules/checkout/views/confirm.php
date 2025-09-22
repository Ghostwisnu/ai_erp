<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header"><?= html_escape($title) ?></header>
                    <div class="card-body">
                        <p><strong>Request Order No:</strong> <?= html_escape($ro['no_ro']) ?></p>
                        <p><strong>Status:</strong> <?= html_escape($ro['status_ro']) ?></p>

                        <h5>Sizes and Quantities:</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Size</th>
                                    <th>Required Quantity</th>
                                    <th>Stock Available</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sizes as $size): ?>
                                    <tr>
                                        <td><?= html_escape($size['size_name']) ?></td>
                                        <td><?= html_escape($size['qty']) ?></td>
                                        <td>
                                            <?php
                                            // Get the stock data
                                            $this->db->select('SUM(qty_in) as total_qty_in, SUM(qty_out) as total_qty_out');
                                            $this->db->from('checkin_det');
                                            $this->db->join('checkin_hdr', 'checkin_hdr.id = checkin_det.hdr_id');
                                            $this->db->where('checkin_det.item_id', $size['brand_size_id']); // Assuming item_id corresponds to the size_id
                                            $stock_data = $this->db->get()->row_array();

                                            // Calculate available stock
                                            $available_stock = ($stock_data['total_qty_in'] ?? 0) - ($stock_data['total_qty_out'] ?? 0);
                                            echo ($available_stock >= $size['qty']) ? 'Sufficient' : 'Insufficient';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <?php if ($stock_available): ?>
                            <form method="POST" action="<?= site_url('checkout/process_checkout/' . $ro['id']) ?>">
                                <button type="submit" class="btn btn-success">Confirm Checkout</button>
                            </form>
                        <?php else: ?>
                            <p>There is insufficient stock. Please add more stock before confirming.</p>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>