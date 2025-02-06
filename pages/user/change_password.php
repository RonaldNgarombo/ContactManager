<?php
// Start the session
session_start();

// Initialize error variables and form data
$errors = [];
$form_data = [];

require_once './../../database/db.php';
require_once './../../utilities/activity_logger.php';

log_action($pdo, "View change password", "User viewed the change password page.");

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user['id'];

// Handle form submission for password change
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $form_data['current_password'] = trim($_POST['current_password']);
    $form_data['new_password'] = trim($_POST['new_password']);
    $form_data['confirm_new_password'] = trim($_POST['confirm_new_password']);

    // Validate inputs
    if (empty($form_data['current_password'])) {
        $errors['current_password'] = 'Current password is required.';
    }
    if (empty($form_data['new_password'])) {
        $errors['new_password'] = 'New password is required.';
    }
    if (empty($form_data['confirm_new_password'])) {
        $errors['confirm_new_password'] = 'Confirm password is required.';
    }
    if ($form_data['new_password'] !== $form_data['confirm_new_password']) {
        $errors['confirm_new_password'] = 'New password and confirm password do not match.';
    }

    // If no errors, proceed with password update
    if (empty($errors)) {
        try {
            // Fetch user password from database
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($form_data['current_password'], $user['password'])) {
                $errors['current_password'] = 'Incorrect current password.';
            }

            if (empty($errors)) {
                // Hash new password
                $hashed_password = password_hash($form_data['new_password'], PASSWORD_DEFAULT);

                // Update password in database
                $stmt = $pdo->prepare("UPDATE users SET password = :new_password WHERE id = :user_id");
                $stmt->bindParam(':new_password', $hashed_password);
                $stmt->bindParam(':user_id', $user_id);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Password changed successfully!";

                    log_action($pdo, "Change password", "Password changed successfully!");

                    header("Location: ./change_password.php"); // Redirect to prevent form resubmission
                    exit();
                } else {
                    $errors[] = "Failed to change password.";

                    log_action($pdo, "Change password", "Failed to change password.", 2);
                }
            }
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Dashboard | Change Password</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="./../../assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="./../../assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="./../../assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="./../../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="./../../assets/vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="./../../assets/vendors/mdi/css/materialdesignicons.min.css">

    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- Not by me <link rel="stylesheet" href="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css"> -->
    <link rel="stylesheet" href="./../../assets/vendors/datatables.net-bs5/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="./../../assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" type="text/css" href="./../../assets/js/select.dataTables.min.css">

    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="./../../assets/css/style.css">

    <!-- endinject -->
    <link rel="shortcut icon" href="./../../assets/images/favicon.png" />
</head>

<body>
    <div class="container-scroller">

        <?php include './../../components/navigation/top_nav.php'; ?>

        <div class="container-fluid page-body-wrapper">

            <?php include './../../components/navigation/user_side_nav.php'; ?>

            <div class="main-panel">
                <div class="content-wrapper">

                    <?php include './../../components/show_alert_messages.php'; ?>

                    <div class="grid-margin stretch-card col-12 col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">
                                    Change your password
                                </h4>

                                <p class="card-description">Securely chang your password.</p>

                                <form class="forms-sample" method="POST" action="">
                                    <div class="row">

                                        <div class="form-group">
                                            <label for="name">Current password</label>

                                            <input type="password" class="form-control" id="current_password" name="current_password" placeholder="********" value="<?php echo isset($form_data['current_password']) ? htmlspecialchars($form_data['current_password']) : ''; ?>">
                                            <span class="text-danger validation-error"><?php echo isset($errors['current_password']) ? $errors['current_password'] : ''; ?></span>
                                        </div>

                                        <div class="form-group">
                                            <label for="name">New password</label>

                                            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="********" value="<?php echo isset($form_data['new_password']) ? htmlspecialchars($form_data['new_password']) : ''; ?>">
                                            <span class="text-danger validation-error"><?php echo isset($errors['new_password']) ? $errors['new_password'] : ''; ?></span>
                                        </div>

                                        <div class="form-group">
                                            <label for="name">Confirm new password</label>

                                            <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" placeholder="********" value="<?php echo isset($form_data['confirm_new_password']) ? htmlspecialchars($form_data['confirm_new_password']) : ''; ?>">
                                            <span class="text-danger validation-error"><?php echo isset($errors['confirm_new_password']) ? $errors['confirm_new_password'] : ''; ?></span>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary me-2">Change Password</button>
                                    <!-- <a href="./user_contacts.php" class="btn btn-light">Cancel</a> -->
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <?php include './../../components/navigation/footer.php'; ?>
            </div>
        </div>
    </div>

    <script src="./../../assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="./../../assets/vendors/chart.js/chart.umd.js"></script>
    <script src="./../../assets/vendors/datatables.net/jquery.dataTables.js"></script>
    <!-- <script src="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script> -->
    <script src="./../../assets/vendors/datatables.net-bs5/dataTables.bootstrap5.js"></script>
    <script src="./../../assets/js/dataTables.select.min.js"></script>
    <script src="./../../assets/js/off-canvas.js"></script>
    <script src="./../../assets/js/template.js"></script>
    <script src="./../../assets/js/settings.js"></script>
    <script src="./../../assets/js/todolist.js"></script>
    <script src="./../../assets/js/jquery.cookie.js" type="text/javascript"></script>
    <script src="./../../assets/js/dashboard.js"></script>
</body>

</html>