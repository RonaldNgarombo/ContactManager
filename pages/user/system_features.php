<?php

require_once './../../utilities/auth_check.php';

require_once './../../database/db.php';
require_once './../../utilities/activity_logger.php';
require_once './../../utilities/system_feature_check.php';

// Initialize error variables, form data and success messages
$errors = [];
$form_data = [];

// log_action("View create/update role", "User viewed the create/update role page.");

// userCan('create-roles', 'page');

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user['id'];

// $systemFeatures = [
//     "User Management",
//     "Contact Management",
//     'view-total-contacts',
//     'Profile Management',
// ];

// Get system features from the database
$stmt = $pdo->prepare("SELECT * FROM system_features");
$stmt->execute();
$systemFeatures = $stmt->fetchAll(PDO::FETCH_ASSOC);

$activeFeatures = [];

$roleId = null;

// If updating, fetch the existing data for repopulating the form
if (isset($_GET['role_id']) && !empty($_GET['role_id']) && !isset($_GET['permission'])) {
    $role_id = $_GET['role_id'];
    $roleId = $role_id;
    $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = :role_id");
    $stmt->bindParam(':role_id', $role_id);
    $stmt->execute();
    $existing_role = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_role) {
        $form_data = $existing_role;

        if ($existing_role['permissions']) {
            $activeFeatures = json_decode($existing_role['permissions']);
        }
    } else {
        // Redirect or show an error if role doesn't exist
        $errors[] = "Role not found.";
    }
}


// Add or remove the permission
if (isset($_GET['role_id']) && !empty($_GET['role_id']) && isset($_GET['feature']) && !empty($_GET['feature'])) {
    $role_id = $_GET['role_id'];
    $feature = $_GET['feature'];

    // Fetch the role from the database
    $stmt = $pdo->prepare("SELECT * FROM system_features");
    $stmt->execute();
    $existing_role = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Decode existing permissions from JSON
    $activeFeatures = json_decode($existing_role['permissions'], true);

    if (!is_array($activeFeatures)) {
        $activeFeatures = []; // Ensure it's an array
    }

    // Add the permission if it's not in the array, otherwise remove it
    if (in_array($permission, $activeFeatures)) {
        // Remove permission
        $activeFeatures = array_diff($activeFeatures, [$permission]);
    } else {
        // Add permission
        $activeFeatures[] = $permission;
    }

    // Encode back to JSON for storage
    $updatedPermissions = json_encode(array_values($activeFeatures));

    // Update the database
    $updateStmt = $pdo->prepare("UPDATE roles SET permissions = :permissions WHERE id = :role_id");
    $updateStmt->bindParam(':permissions', $updatedPermissions);
    $updateStmt->bindParam(':role_id', $role_id);

    if ($updateStmt->execute()) {
        $_SESSION['success_message'] = "Changes saved successfully!";

        header("Location: ./create_update_role.php?role_id={$role_id}");

        // echo json_encode(["status" => "success", "message" => "Permission updated.", "permissions" => $activeFeatures]);
    } else {
        $_SESSION['error_message'] = "Failed to update permission.!";

        // echo json_encode(["status" => "error", "message" => "Failed to update permission."]);
    }
}


// if (isset($_GET['role_id']) && !empty($_GET['role_id']) && isset($_GET['permission']) && !empty($_GET['permission'])) {
//     $role_id = $_GET['role_id'];
//     $roleId = $role_id;
//     $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = :role_id");
//     $stmt->bindParam(':role_id', $role_id);
//     $stmt->execute();
//     $existing_role = $stmt->fetch(PDO::FETCH_ASSOC);

//     // STEPS TO ADD/REMOVE the permission
//     // Get the role
//     // Add it if it doesn't exist
//     // And if it does, remove it
//     // Then save in the db

//     // if ($existing_role) {
//     //     $form_data = $existing_role;

//     //     $activeFeatures = json_decode($existing_role['permissions']);
//     // } else {
//     //     // Redirect or show an error if role doesn't exist
//     //     $errors[] = "Role not found.";
//     // }
// }

/**
 * Check if a system feature with the given id(feature_id) is active
 * Get the feature id from the URL.
 * If the feature is active, disable it, and vice versa.
 * Redirect back to the page with a success message.
 */

if (isset($_GET['feature_id']) && !empty($_GET['feature_id'])) {
    $feature_id = $_GET['feature_id'];

    // Fetch the feature from the database
    $stmt = $pdo->prepare("SELECT * FROM system_features WHERE id = :feature_id");
    $stmt->bindParam(':feature_id', $feature_id);
    $stmt->execute();
    $feature = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($feature) {
        // Disable the feature if it's active, and vice versa
        $is_active = $feature['is_active'] ? 0 : 1;

        // Update the feature in the database
        $updateStmt = $pdo->prepare("UPDATE system_features SET is_active = :is_active WHERE id = :feature_id");
        $updateStmt->bindParam(':is_active', $is_active);
        $updateStmt->bindParam(':feature_id', $feature_id);

        if ($updateStmt->execute()) {
            $_SESSION['success_message'] = "Feature updated successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to update feature!";
        }

        header("Location: ./system_features.php");
    } else {
        $_SESSION['error_message'] = "Feature not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Create Update Role | Role Manager</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="./../../assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="./../../assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="./../../assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="./../../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="./../../assets/vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="./../../assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="./../../assets/vendors/bootstrap-icons/bootstrap-icons.css">

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

                    <div class="">

                        <div class="mt-4">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Enable/Disable System Features</h4>

                                    <p class="card-description">The system features listed below are currently active. You can enable or disable them by clicking on the toggle button.</p>

                                    <form class="forms-sample" method="POST" action="">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

                                        <div class="row">
                                            <div class="form-group">
                                                <label for="permissions">Permissions</label>

                                                <div class="row">
                                                    <?php foreach ($systemFeatures as $feature): ?>
                                                        <div class="col-6 col-md-4 col-lg-3 mb-3">
                                                            <a href="system_features.php?feature_id=<?php echo $feature['id']; ?>" style="cursor:pointer;" class="text-black">
                                                                <i class="bi <?php echo $feature['is_active'] ? 'bi-check-circle' : 'bi-x-circle'; ?>" style="color: <?php echo $feature['is_active'] ? '#22c55e' : '#9ca3af'; ?>"></i>

                                                                <span><?= ucwords(str_replace("-", " ", $feature['name'])); ?></span>
                                                            </a>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>

                                            </div>
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