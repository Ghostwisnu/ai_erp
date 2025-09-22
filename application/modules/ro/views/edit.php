<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        <?= html_escape($title ?? 'Edit Request Order (RO)') ?>
                    </header>

                    <div class="card-body">
                        <form method="post">
                            <div class="form-group">
                                <label>No RO</label>
                                <input type="text" name="no_ro" class="form-control" value="<?= html_escape($ro['no_ro']) ?>" readonly />
                            </div>

                            <div class="form-group">
                                <label for="wo_id">Work Order</label>
                                <select name="wo_id" id="wo_id" class="form-control">
                                    <option value="">-- Select WO --</option>
                                    <?php foreach ($wo as $w): ?>
                                        <option value="<?= $w['id'] ?>" <?= $w['id'] == $ro['wo_id'] ? 'selected' : '' ?>><?= $w['no_wo'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="item_sfg_id">SFG Item</label>
                                <select name="item_sfg_id" id="item_sfg_id" class="form-control" <?= empty($ro['item_sfg_id']) ? 'disabled' : '' ?>>
                                    <option value="">-- Select SFG --</option>
                                    <?php foreach ($wo as $w): ?>
                                        <option value="<?= $w['id'] ?>" <?= $w['id'] == $ro['item_sfg_id'] ? 'selected' : '' ?>><?= $w['item_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="department_id">Department</label>
                                <select name="department_id" id="department_id" class="form-control">
                                    <?php foreach ($departments as $department): ?>
                                        <option value="<?= $department['id'] ?>" <?= $department['id'] == $ro['department_id'] ? 'selected' : '' ?>><?= $department['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Date Order</label>
                                <input type="date" name="date_order" class="form-control" value="<?= html_escape($ro['date_order']) ?>" required />
                            </div>

                            <div class="form-group">
                                <label>Total Quantity</label>
                                <input type="text" name="total_qty" class="form-control" value="<?= html_escape($ro['total_qty']) ?>" readonly />
                            </div>

                            <div class="form-group">
                                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#woModal">Choose WO</button>
                                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#sfgModal" disabled>Choose SFG</button>
                            </div>

                            <div class="form-group">
                                <ul class="nav nav-tabs">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-toggle="tab" href="#itemTab">Item</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#sizeTab">Size</a>
                                    </li>
                                </ul>

                                <div class="tab-content">
                                    <div id="itemTab" class="tab-pane active">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Required Qty</th>
                                                    <th>Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Dynamically populated rows based on selected SFG -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div id="sizeTab" class="tab-pane">
                                        <!-- Size input fields -->
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>