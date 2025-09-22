<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        Edit Submenu
                        <span class="tools pull-right">
                            <a href="<?= site_url('submenu/' . $sub['menu_id']); ?>" class="btn btn-sm btn-secondary">Kembali</a>
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                        </span>
                    </header>
                    <div class="card-body">
                        <?= validation_errors('<div class="alert alert-danger">', '</div>'); ?>

                        <form method="post" action="<?= site_url('submenu/update/' . $sub['id']); ?>">
                            <div class="form-group">
                                <label>Menu ID</label>
                                <input type="number" name="menu_id" class="form-control" value="<?= (int)$sub['menu_id']; ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Nama</label>
                                <input type="text" name="name" class="form-control" value="<?= html_escape($sub['name']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Slug</label>
                                <input type="text" name="slug" class="form-control" value="<?= html_escape($sub['slug']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>URL</label>
                                <input type="text" name="url" class="form-control" value="<?= html_escape($sub['url']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Icon</label>
                                <input type="text" name="icon" class="form-control" value="<?= html_escape($sub['icon']); ?>">
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select name="is_active" class="form-control">
                                    <option value="1" <?= !empty($sub['is_active']) ? 'selected' : ''; ?>>Active</option>
                                    <option value="0" <?= empty($sub['is_active']) ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Urutan</label>
                                <input type="number" name="sort_order" class="form-control" value="<?= (int)$sub['sort_order']; ?>" min="1" required>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Update</button>
                                <a href="<?= site_url('submenu/' . $sub['menu_id']); ?>" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>

                    </div>
                </section>
            </div>
        </div>
    </section>
</section>