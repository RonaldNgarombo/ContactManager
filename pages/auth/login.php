<?php
// login.php

session_start(); // Start the session

// Retrieve errors and form data from the session
$errors = isset($_SESSION['errors']) ? $_SESSION['errors'] : [];
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];

// Clear the session data after retrieving it
unset($_SESSION['errors']);
unset($_SESSION['form_data']);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>Login | Contact Manager</title>

    <?php include './../../components/page_head_imports.php'; ?>
</head>

<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth px-0">
                <div class="row w-100 mx-0">
                    <div class="col-lg-4 mx-auto">
                        <div class="auth-form-light text-left py-5 px-4 px-sm-5">
                            <div class="brand-logo">
                                <img src="../../assets/images/logo.png" alt="logo">
                            </div>

                            <h4>Hello! let's get started</h4>

                            <h6 class="font-weight-light">Sign in to continue.</h6>

                            <form class="pt-3" action="./handle_login.php" method="POST">
                                <div class="form-group">
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="Type your email" value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>">
                                    <span class="text-danger validation-error"><?php echo isset($errors['email']) ? $errors['email'] : ''; ?></span>
                                </div>

                                <div class="form-group">
                                    <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Password">
                                    <span class="text-danger validation-error"><?php echo isset($errors['password']) ? $errors['password'] : ''; ?></span>
                                    <span class="text-danger validation-error"><?php echo isset($errors['login']) ? $errors['login'] : ''; ?></span>
                                </div>

                                <div class="mt-3 d-grid gap-2">
                                    <button class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn" type="submit">SIGN IN</button>
                                </div>

                                <div class="my-2 d-flex justify-content-between align-items-center">
                                    <!-- <div class="form-check">
                                        <label class="form-check-label text-muted">
                                            <input type="checkbox" class="form-check-input"> Keep me signed in </label>
                                    </div> -->

                                    <a href="#" class="auth-link text-black">Forgot password?</a>
                                </div>

                                <div class="text-center mt-4 font-weight-light"> Don't have an account? <a href="./register.php" class="text-primary">Create</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- content-wrapper ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="./../../assets/vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="./../../assets/js/off-canvas.js"></script>
    <script src="./../../assets/js/template.js"></script>
    <script src="./../../assets/js/settings.js"></script>
    <script src="./../../assets/js/todolist.js"></script>
    <!-- endinject -->
</body>

</html>