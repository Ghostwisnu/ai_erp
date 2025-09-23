<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        Tambah Menu
                        <span class="tools pull-right">
                            <a href="<?= site_url('menu'); ?>" style="color: black;" class="btn btn-sm btn-secondary">Kembali</a>
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                        </span>
                    </header>
                    <div class="card-body">
                        <?= validation_errors('<div class="alert alert-danger">', '</div>'); ?>

                        <form method="post" action="<?= site_url('menu/store'); ?>">
                            <div class="form-group">
                                <label>Nama</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Slug (unik)</label>
                                <input type="text" name="slug" class="form-control" placeholder="contoh: master-data" required>
                            </div>

                            <div class="form-group">
                                <label>URL (kosongkan jika parent)</label>
                                <input type="text" name="url" class="form-control" placeholder="contoh: dashboard">
                            </div>

                            <div class="form-group">
                                <label>Icon (FontAwesome class)</label>
                                <input type="text" name="icon" class="form-control" placeholder="fa fa-dashboard">
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select name="is_active" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Urutan</label>
                                <input type="number" name="sort_order" class="form-control" min="1" required>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="<?= site_url('menu'); ?>" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>

                    </div>
                </section>
            </div>
        </div>
    </section>
</section>