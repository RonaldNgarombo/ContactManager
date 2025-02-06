<?php
// register.php

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
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Sign up | Contact Manager</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="./../../assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="./../../assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="./../../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="./../../assets/vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="./../../assets/vendors/mdi/css/materialdesignicons.min.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="./../../assets/css/style.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="./../../assets/images/favicon.png" />

    <style>
        .validation-error {
            color: red;
            font-size: 0.8rem;
        }
    </style>
</head>

<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth px-0">
                <div class="row w-100 mx-0">
                    <div class="col-lg-4 mx-auto">
                        <div class="auth-form-light text-left py-5 px-4 px-sm-5">
                            <div class="brand-logo">
                                <img src="./../../assets/images/logo.png" alt="logo">
                            </div>

                            <h4>New here?</h4>

                            <h6 class="font-weight-light">Signing up is easy. It only takes a few steps</h6>

                            <form class="pt-3" action="./handle_register.php" method="POST">
                                <!-- First Name -->
                                <div class="form-group">
                                    <input type="text" class="form-control form-control-lg" id="first_name" name="first_name" placeholder="Type your first name" value="<?php echo isset($form_data['first_name']) ? htmlspecialchars($form_data['first_name']) : ''; ?>">
                                    <span class="text-danger validation-error"><?php echo isset($errors['first_name']) ? $errors['first_name'] : ''; ?></span>
                                </div>

                                <!-- Last Name -->
                                <div class="form-group">
                                    <input type="text" class="form-control form-control-lg" id="last_name" name="last_name" placeholder="Type your last name" value="<?php echo isset($form_data['last_name']) ? htmlspecialchars($form_data['last_name']) : ''; ?>">
                                    <span class="text-danger validation-error"><?php echo isset($errors['last_name']) ? $errors['last_name'] : ''; ?></span>
                                </div>

                                <!-- Email -->
                                <div class="form-group">
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="Type your email" value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>">
                                    <span class="text-danger validation-error"><?php echo isset($errors['email']) ? $errors['email'] : ''; ?></span>
                                </div>

                                <!-- Password -->
                                <div class="form-group">
                                    <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Password">
                                    <span class="text-danger validation-error"><?php echo isset($errors['password']) ? $errors['password'] : ''; ?></span>
                                </div>

                                <!-- Confirm Password -->
                                <div class="form-group">
                                    <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" placeholder="Confirm password">
                                    <span class="text-danger validation-error"><?php echo isset($errors['confirm_password']) ? $errors['confirm_password'] : ''; ?></span>
                                </div>

                                <!-- Terms & Conditions -->
                                <div class="mb-4">
                                    <div class="form-check">
                                        <label class="form-check-label text-muted">
                                            <input type="checkbox" class="form-check-input" name="terms" <?php echo isset($form_data['terms']) ? 'checked' : ''; ?>> I agree to all Terms & Conditions
                                        </label>
                                        <span class="text-danger validation-error"><?php echo isset($errors['terms']) ? $errors['terms'] : ''; ?></span>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="mt-3 d-grid gap-2">
                                    <button class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn" type="submit">SIGN UP</button>
                                </div>

                                <!-- Login Link -->
                                <div class="text-center mt-4 font-weight-light">
                                    Already have an account? <a href="./login.php" class="text-primary">Login</a>
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