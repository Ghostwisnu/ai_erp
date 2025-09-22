<body class="login-body">
    <div class="container">
        <form class="form-signin" action="<?= base_url('register/register_user'); ?>" method="POST" enctype="multipart/form-data">
            <h2 class="form-signin-heading">Registration Now</h2>
            <div class="login-wrap">

                <!-- Personal Details Section -->
                <!-- <p>Enter your personal details below</p> -->

                <!-- Full Name -->
                <!-- <input type="text" class="form-control" name="username" placeholder="Full Name" required autofocus>
                <?= form_error('username', '<small class="text-danger">', '</small>'); ?> -->

                <!-- Address -->
                <!-- <input type="text" class="form-control" name="address" placeholder="Address" required>
                <?= form_error('address', '<small class="text-danger">', '</small>'); ?> -->

                <!-- Email -->
                <input type="text" class="form-control" name="email" placeholder="Email" required>
                <?= form_error('email', '<small class="text-danger">', '</small>'); ?>

                <!-- City/Town -->
                <!-- <input type="text" class="form-control" name="city" placeholder="City/Town" required>
                <?= form_error('city', '<small class="text-danger">', '</small>'); ?> -->

                <!-- Gender -->
                <!-- <div class="radios">
                    <label class="label_radio col-lg-6 col-sm-6" for="radio-01">
                        <input name="gender" id="radio-01" value="male" type="radio" checked /> Male
                    </label>
                    <label class="label_radio col-lg-6 col-sm-6" for="radio-02">
                        <input name="gender" id="radio-02" value="female" type="radio" /> Female
                    </label>
                </div> -->

                <!-- Account Details Section -->
                <!-- <p>Enter your account details below</p> -->

                <!-- Username -->
                <input type="text" class="form-control" name="username" placeholder="Username" required>
                <?= form_error('username', '<small class="text-danger">', '</small>'); ?>

                <!-- Password -->
                <input type="password" class="form-control" name="password" placeholder="Password" required>
                <?= form_error('password', '<small class="text-danger">', '</small>'); ?>

                <!-- Re-type Password -->
                <input type="password" class="form-control" name="password_confirm" placeholder="Re-type Password" required>
                <?= form_error('password_confirm', '<small class="text-danger">', '</small>'); ?>

                <!-- Profile Image -->
                <div class="form-group">
                    <input type="file" class="form-control" name="image">
                    <small>Upload a profile image (optional)</small>
                </div>

                <!-- Terms and Conditions -->
                <!-- <label class="checkbox">
                    <input type="checkbox" value="agree" name="agree" required> I agree to the Terms of Service and Privacy Policy
                </label> -->

                <!-- Submit Button -->
                <button class="btn btn-lg btn-login btn-block" type="submit">Submit</button>

                <!-- Registration Link -->
                <div class="registration">
                    Already Registered?
                    <a class="" href="<?= base_url('login'); ?>">Login</a>
                </div>

            </div>
        </form>
    </div>
</body>