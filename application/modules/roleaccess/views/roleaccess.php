<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        Role Management
                        <span class="tools pull-right">
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                        </span>
                    </header>
                    <div class="card-body">
                        <!-- Add Role Form -->
                        <form action="<?php echo site_url('roleaccess/add_role'); ?>" method="POST">
                            <div class="form-group">
                                <label for="role_name">Role Name</label>
                                <input type="text" class="form-control" name="role_name" id="role_name" required>
                            </div>
                            <div class="form-group">
                                <label for="role_description">Role Description</label>
                                <textarea class="form-control" name="role_description" id="role_description" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Role</button>
                        </form>

                        <hr>

                        <div class="adv-table">
                            <table class="display table table-bordered table-striped" id="dynamic-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Role Name</th>
                                        <th>Role Description</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($roles as $role): ?>
                                        <tr>
                                            <td><?php echo $role['id']; ?></td>
                                            <td><?php echo $role['role_name']; ?></td>
                                            <td><?php echo $role['role_description']; ?></td>
                                            <td class="text-right">
                                                <a href="<?php echo site_url('roleaccess/delete_role/' . $role['id']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this role?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>