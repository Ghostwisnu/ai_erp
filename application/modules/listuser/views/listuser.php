<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        User Management
                        <span class="tools pull-right">
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                        </span>
                    </header>
                    <div class="card-body">
                        <div class="adv-table">
                            <table class="display table table-bordered table-striped" id="dynamic-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo $user['username']; ?></td>
                                            <td><a href="mailto:<?php echo $user['email']; ?>"><?php echo $user['email']; ?></a></td>
                                            <td>
                                                <form action="<?php echo site_url('listuser/update_role'); ?>" method="POST">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <select name="role_id" class="form-control">
                                                        <?php foreach ($roles as $role): ?>
                                                            <option value="<?php echo $role['id']; ?>" <?php echo ($role['id'] == $user['role_id']) ? 'selected' : ''; ?>>
                                                                <?php echo $role['role_name']; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" class="btn btn-primary btn-sm">Update Role</button>
                                                </form>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo ($user['is_active'] == 1) ? 'success' : 'danger'; ?>">
                                                    <?php echo ($user['is_active'] == 1) ? 'Active' : 'Blocked'; ?>
                                                </span>
                                            </td>
                                            <td class="text-right">

                                                <!-- Delete Button -->
                                                <a href="<?php echo site_url('listuser/delete/' . $user['id']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">
                                                    Delete
                                                </a>
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