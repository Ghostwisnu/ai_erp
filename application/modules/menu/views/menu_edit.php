<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        Edit Menu
                        <span class="tools pull-right">
                            <a href="<?= site_url('menu'); ?>" style="color: black;" class="btn btn-sm btn-secondary">Kembali</a>
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                        </span>
                    </header>
                    <div class="card-body">
                        <?= validation_errors('<div class="alert alert-danger">', '</div>'); ?>

                        <form method="post" action="<?= site_url('menu/update/' . $menu['id']); ?>">
                            <div class="form-group">
                                <label>Nama</label>
                                <input type="text" name="name" class="form-control" value="<?= html_escape($menu['name']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Slug (unik)</label>
                                <input type="text" name="slug" class="form-control" value="<?= html_escape($menu['slug']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>URL (kosongkan jika parent)</label>
                                <input type="text" name="url" class="form-control" value="<?= html_escape($menu['url']); ?>">
                            </div>

                            <div class="form-group">
                                <label>Icon</label>
                                <input type="text" name="icon" class="form-control" value="<?= html_escape($menu['icon']); ?>">
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select name="is_active" class="form-control">
                                    <option value="1" <?= !empty($menu['is_active']) ? 'selected' : ''; ?>>Active</option>
                                    <option value="0" <?= empty($menu['is_active']) ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Urutan</label>
                                <input type="number" name="sort_order" class="form-control" value="<?= (int)$menu['sort_order']; ?>" min="1" required>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Update</button>
                                <a href="<?= site_url('menu'); ?>" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>

                    </div>
                </section>
            </div>
        </div>
    </section>
</section>