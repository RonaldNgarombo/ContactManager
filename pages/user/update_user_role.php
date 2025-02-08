<?php
// Start the session
// session_start();

require_once './../../utilities/auth_check.php';

require_once './../../database/db.php';
require_once './../../utilities/activity_logger.php';

// Initialize error variables, form data and success messages
$errors = [];
$form_data = [];


log_action($pdo, "View update user role", "User viewed the update user role page.");

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user['id'];

// Handle form submission for creating or updating the user role
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

                    log_action($pdo, "Update user role", "User role updated successfully!");
                } else {
                    $errors[] = "An error occurred while updating the user role.";
                    log_action($pdo, "Update user role", "An error occurred while updating the user role.", 2);

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
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Set user role | Contact Manager</title>
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

                    <div class="grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">
                                    Set user's role
                                </h4>

                                <p class="card-description">You can use this page to update the user's role. This will affect how they use the system.</p>

                                <form class="forms-sample" method="POST" action="">
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