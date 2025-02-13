<?php

require_once './../../utilities/auth_check.php';

require_once './../../database/db.php';
require_once './../../utilities/activity_logger.php';
require_once './../../utilities/system_feature_check.php';

// Initialize error variables and form data
$errors = [];
$form_data = [];

// log_action("View change password", "User viewed the change password page.");

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user['id'];

// Handle form submission for password change
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die("CSRF validation failed!");
    }

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

                    log_action("Change password", "Password changed successfully!");

                    header("Location: ./change_password.php"); // Redirect to prevent form resubmission
                    exit();
                } else {
                    $errors[] = "Failed to change password.";

                    log_action("Change password", "Failed to change password.", 2);
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
    <title>Change Password | Contact Manager</title>

    <?php include './../../components/page_head_imports.php'; ?>
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
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

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

    <?php include './../../components/page_script_imports.php'; ?>

</body>

</html>