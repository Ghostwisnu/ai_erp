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
                            <div class="row">
                                <!-- Button to create new user -->
                                <div class="col-md-12">
                                    <a href="<?php echo $create_url; ?>" class="btn btn-success mb-3">Create New User</a>
                                </div>
                            </div>
                            <table class="display table table-bordered table-striped" id="dynamic-table">
                                <thead>
                                    <tr>
                                        <th>Custom ID</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Attendance Status</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $row): ?>
                                        <tr>
                                            <td><?php echo $row['custom_id']; ?></td>
                                            <td><?php echo $row['username']; ?></td>
                                            <td><a href="mailto:<?php echo $row['email']; ?>"><?php echo $row['email']; ?></a></td>
                                            <td>
                                                <!-- Form to update Role -->
                                                <form action="<?php echo site_url('listuser/update_role'); ?>" method="POST">
                                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                                    <select name="role_id" class="form-control">
                                                        <?php foreach ($roles as $role): ?>
                                                            <option value="<?php echo $role['id']; ?>" <?php echo ($role['id'] == $row['role_id']) ? 'selected' : ''; ?>>
                                                                <?php echo $role['role_name']; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" class="btn btn-primary btn-sm">Update Role</button>
                                                </form>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo ($row['is_active'] == 1) ? 'success' : 'danger'; ?>">
                                                    <?php echo $row['status']; ?>
                                                </span>
                                                <a href="<?php echo site_url('listuser/update_status/' . $row['id'] . '/' . ($row['is_active'] == 1 ? 0 : 1)); ?>" class="btn btn-warning btn-sm">
                                                    <?php echo ($row['is_active'] == 1) ? 'Block' : 'Activate'; ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo ($row['attendance_status'] == 1) ? 'success' : 'warning'; ?>">
                                                    <?php echo $row['attendance_status']; ?>
                                                </span>
                                                <a href="<?php echo site_url('listuser/update_attendance/' . $row['id'] . '/' . ($row['attendance_status'] == 1 ? 0 : 1)); ?>" class="btn btn-info btn-sm">
                                                    <?php echo ($row['attendance_status'] == 1) ? 'Mark Absent' : 'Mark Present'; ?>
                                                </a>
                                            </td>
                                            <td class="text-right">
                                                <a href="<?php echo site_url(str_replace('{id}', $row['id'], $actions[0]['url'])); ?>" class="btn btn-warning">Edit</a>
                                                <a href="<?php echo site_url(str_replace('{id}', $row['id'], $actions[1]['url'])); ?>" class="btn btn-danger" onclick="return confirm('<?php echo $actions[1]['confirm']; ?>')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="pagination">
                                <?php echo $pagination_links; ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>