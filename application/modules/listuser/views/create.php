<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="card">
                    <header class="card-header">
                        Create New User
                    </header>
                    <div class="card-body">
                        <form action="<?php echo site_url('listuser/store'); ?>" method="POST">
                            <!-- CSRF Protection -->
                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

                            <!-- Username -->
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" name="username" id="username" class="form-control" value="<?php echo set_value('username'); ?>" required>
                                <?php echo form_error('username', '<div class="text-danger">', '</div>'); ?>
                            </div>

                            <!-- Email -->
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" id="email" class="form-control" value="<?php echo set_value('email'); ?>" required>
                                <?php echo form_error('email', '<div class="text-danger">', '</div>'); ?>
                            </div>

                            <!-- Password -->
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                                <?php echo form_error('password', '<div class="text-danger">', '</div>'); ?>
                            </div>

                            <!-- Role -->
                            <div class="form-group">
                                <label for="role_id">Role</label>
                                <select name="role_id" id="role_id" class="form-control" required>
                                    <option value="">Select Role</option>
                                    <?php if (!empty($roles)): ?>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo $role['id']; ?>" <?php echo set_select('role_id', $role['id']); ?>>
                                                <?php echo $role['role_name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">No Roles Available</option>
                                    <?php endif; ?>
                                </select>
                                <?php echo form_error('role_id', '<div class="text-danger">', '</div>'); ?>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-success">Create User</button>
                            <a href="<?php echo $back_url; ?>" class="btn btn-danger">Back</a>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>