<?php

require_once './../../utilities/auth_check.php';

require_once './../../database/db.php';
require_once './../../utilities/activity_logger.php';
require_once './../../utilities/system_feature_check.php';

// Initialize error variables, form data and success messages
$errors = [];
$form_data = [];

// log_action("View update user role", "User viewed the update user role page.");

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user['id'];

// Handle form submission for creating or updating the user role
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die("CSRF validation failed!");
    }

    userCan('change-user-roles', 'page');

    // Get form data and sanitize it
    $form_data['role_id'] = trim($_POST['role_id']);

    // die(var_dump($form_data['role_id']));

    if (empty($form_data['role_id'])) {
        $errors['role_id'] = 'Role is required.';
    }

    // If no errors, insert or update the record in the database
    if (empty($errors)) {
        // Get the user from the session

        // Start a transaction
        $pdo->beginTransaction();

        try {
            // Check if we are updating an existing record
            if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
                // Update existing record
                $user_id = $_GET['user_id'];

                $sql = "UPDATE users SET role_id = :role_id WHERE id = :user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':role_id', $form_data['role_id']);
                $stmt->bindParam(':user_id', $user_id);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "User role updated successfully!";

                    log_action("Update user role", "User role updated successfully!");
                } else {
                    $errors[] = "An error occurred while updating the user role.";
                    log_action("Update user role", "An error occurred while updating the user role.", 2);

                    throw new Exception("Error updating user role.");
                }
            }

            // Commit the transaction if everything is successful
            $pdo->commit();

            // If successful, clear the form data
            $form_data = [];

            // Redirect to the contacts page
            header("Location: ./users.php");
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $pdo->rollBack();
            $errors[] = $e->getMessage();
        }
    }
}

// If updating, fetch the existing data for repopulating the form
if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $existing_contact = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_contact) {

        $form_data = $existing_contact;
    } else {
        // Redirect or show an error if user role doesn't exist
        $errors[] = "User role not found.";
    }
}



$sql = "SELECT * FROM roles";

$stmt = $pdo->prepare($sql);

// Execute the query
$stmt->execute();

// Fetch all results
$system_roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>




<!DOCTYPE html>
<html lang="en">

<head>
    <title>Update User Role | Contact Manager</title>

    <?php include './../../components/page_head_imports.php'; ?>
</head>

<body>
    <div class="container-scroller">

        <?php include './../../components/navigation/top_nav.php'; ?>

        <div class="container-fluid page-body-wrapper">

            <?php include './../../components/navigation/user_side_nav.php'; ?>

            <div class="main-panel">
                <div class="content-wrapper">

                    <div class="grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">
                                    Set user's role
                                </h4>

                                <p class="card-description">You can use this page to update the user's role. This will affect how they use the system.</p>

                                <form class="forms-sample" method="POST" action="">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

                                    <div class="row">

                                        <div class="form-group col-md-6">
                                            <label for="name">First name: <strong><?php echo $form_data['first_name'] ?></strong></label>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="name">Last name: <strong><?php echo $form_data['last_name'] ?></strong></label>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="name">Email: <strong><?php echo $form_data['email'] ?></strong></label>
                                        </div>

                                        <div class="form-group col-md-6">
                                        </div>

                                        <hr class="col-md-12">

                                        <div class="form-group col-md-6">
                                            <label for="role_id">Set role</label>

                                            <select class="form-select form-select-lg" id="role_id" name="role_id">
                                                <option value="">Select the user role</option>

                                                <?php foreach ($system_roles as $r): ?>
                                                    <option value="<?php echo $r['id']; ?>"
                                                        <?php echo (isset($form_data['role_id']) && $form_data['role_id'] == $r['id']) ? 'selected' : ''; ?>>

                                                        <?php echo htmlspecialchars($r['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                            <span class="text-danger validation-error"><?php echo isset($errors['role_id']) ? $errors['role_id'] : ''; ?></span>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary me-2">Set Role</button>
                                    <a href="./users.php" class="btn btn-light">Cancel</a>
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