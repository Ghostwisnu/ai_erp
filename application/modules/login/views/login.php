<body class="login-body">
    <?php if ($this->session->flashdata('message')): ?>
        <div class="alert alert-info">
            <?php echo $this->session->flashdata('message'); ?>
        </div>
    <?php endif; ?>
    <div class="container">

        <form class="form-signin" action="<?= base_url('login/login_user'); ?>" method="POST">
            <h2 class="form-signin-heading">Sign In Now</h2>
            <div class="login-wrap">
                <!-- Username Input -->
                <input type="text" class="form-control" name="username" placeholder="Username or email" required autofocus>
                <?= form_error('username', '<small class="text-danger">', '</small>'); ?>

                <!-- Password Input -->
                <input type="password" class="form-control" name="password" placeholder="Password" required>
                <?= form_error('password', '<small class="text-danger">', '</small>'); ?>

                <!-- Remember Me Checkbox -->
                <label class="checkbox">
                    <input type="checkbox" value="remember-me" name="remember-me" checked> Remember me
                    <span class="pull-right">
                        <a data-toggle="modal" href="#myModal">Forgot Password?</a>
                    </span>
                </label>

                <!-- Submit Button -->
                <button class="btn btn-lg btn-login btn-block" type="submit">Sign In</button>

                <!-- Registration Link -->
                <div class="registration">
                    Don't have an account yet?
                    <a class="" href="<?= base_url('register'); ?>">Create an account</a>
                </div>
            </div>

            <!-- Modal for Forgot Password -->
            <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Forgot Password?</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Enter your email address below to reset your password.</p>
                            <input type="text" name="email" placeholder="Email" autocomplete="off" class="form-control placeholder-no-fix">
                        </div>
                        <div class="modal-footer">
                            <button data-dismiss="modal" class="btn btn-default" type="button">Cancel</button>
                            <button class="btn btn-success" type="button">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Modal -->

        </form>

    </div>
</body>

<!-- ./ form -->