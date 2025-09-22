<style>
    /* Style for the user profile image */
    .user-avatar {
        width: 30px;
        /* Set a smaller width */
        height: 30px;
        /* Set a smaller height */
        border-radius: 50%;
        /* Makes the image circular */
        object-fit: cover;
        /* Ensures the image fits nicely within the circle */
    }
</style>

<body class="dark-sidebar-nav">
    <section id="container">
        <!--header start-->
        <header class="header dark-bg">
            <div class="sidebar-toggle-box">
                <i class="fa fa-bars"></i>
            </div>
            <!--logo start-->
            <a href="index.html" class="logo">Flat<span>lab</span></a>
            <!--logo end-->
            <div class="nav notify-row" id="top_menu">
                <!-- notification start -->
                <ul class="nav top-menu">
                    <!-- settings start -->
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="fa fa-tasks"></i>
                            <span class="badge badge-success">6</span>
                        </a>
                        <!-- Add your notification list here -->
                    </li>
                    <!-- settings end -->
                    <!-- inbox dropdown start-->
                    <li id="header_inbox_bar" class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="fa fa-envelope-o"></i>
                            <span class="badge badge-danger">5</span>
                        </a>
                        <!-- Add your inbox items here -->
                    </li>
                    <!-- inbox dropdown end -->
                    <!-- notification dropdown start-->
                    <li id="header_notification_bar" class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="fa fa-bell-o"></i>
                            <span class="badge badge-warning">7</span>
                        </a>
                        <!-- Add your notification list here -->
                    </li>
                    <!-- notification dropdown end -->
                </ul>
                <!-- notification end -->
            </div>
            <div class="top-nav">
                <!--search & user info start-->
                <ul class="nav pull-right top-menu">
                    <li>
                        <input type="text" class="form-control search" placeholder="Search">
                    </li>
                    <!-- user login dropdown start-->
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <!-- Retrieve the user's image from the session or use a default image if not available -->
                            <img alt="User Avatar" src="<?= base_url('assets/images/profiles/' . $this->session->userdata('image')); ?>" class="rounded-circle user-avatar">

                            <span class="username"><?= $this->session->userdata('username'); ?></span>
                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu extended logout dropdown-menu-right">
                            <div class="log-arrow-up"></div>
                            <li><a href="#"><i class="fa fa-suitcase"></i> Profile</a></li>
                            <li><a href="#"><i class="fa fa-cog"></i> Settings</a></li>
                            <li><a href="#"><i class="fa fa-bell-o"></i> Notification</a></li>
                            <li><a href="<?= site_url('login/logout'); ?>"><i class="fa fa-key"></i> Log Out</a></li>
                        </ul>
                    </li>
                    <!-- user login dropdown end -->
                </ul>
                <!--search & user info end-->
            </div>
        </header>
        <!--header end-->