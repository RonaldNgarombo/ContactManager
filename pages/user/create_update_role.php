<?php

require_once './../../utilities/auth_check.php';

require_once './../../database/db.php';
require_once './../../utilities/activity_logger.php';

// Initialize error variables, form data and success messages
$errors = [];
$form_data = [];

// log_action("View create/update role", "User viewed the create/update role page.");

userCan('create-roles', 'page');

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user['id'];

// Handle form submission for creating or updating the role
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data and sanitize it
    $form_data['name'] = trim($_POST['name']);
    $form_data['description'] = trim($_POST['description']);

    // Validate the inputs
    if (empty($form_data['name'])) {
        $errors['name'] = 'Name is required.';
    }

    // If no errors, insert or update the record in the database
    if (empty($errors)) {
        // Start a transaction
        $pdo->beginTransaction();

        try {
            // Check if we are updating an existing record
            if (isset($_GET['role_id']) && !empty($_GET['role_id'])) {
                // Update existing record
                $role_id = $_GET['role_id'];
                $sql = "UPDATE roles SET name = :name, description = :description WHERE id = :role_id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $form_data['name']);
                $stmt->bindParam(':description', $form_data['description']);
                $stmt->bindParam(':role_id', $role_id);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Role updated successfully!";

                    log_action("Update role", "Role updated successfully!");
                } else {
                    $errors[] = "An error occurred while updating the role.";

                    log_action("Update role", "An error occurred while updating the role.", 2);

                    throw new Exception("Error updating role.");
                }
            } else {
                // Create a new role
                $sql = "INSERT INTO roles (name, description) VALUES (:name, :description)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $form_data['name']);
                $stmt->bindParam(':description', $form_data['description']);

                if ($stmt->execute()) {

                    $_SESSION['success_message'] = "Role created successfully!";

                    log_action("Create role", "New role created successfully!");
                } else {
                    $errors[] = "An error occurred while saving the role.";

                    log_action("Create role", "An error occurred while creating a new role.", 2);

                    throw new Exception("Error creating role.");
                }
            }

            // Commit the transaction if everything is successful
            $pdo->commit();

            // If successful, clear the form data
            $form_data = [];

            // Redirect to the roles page
            header("Location: ./view_roles.php");
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $pdo->rollBack();
            $errors[] = $e->getMessage();

            log_action("Create role", $e->getMessage(), 2);
        }
    }
}

$systemPermissions = [
    // Dashboard
    "view-dashboard",
    "view-contacts-by-category",
    'view-total-contacts',
    'view-total-users',
    'view-activity-logs',

    // Contacts
    "view-contacts",
    "create-contacts",
    // "update-contacts",
    "delete-contacts",

    // Roles
    "view-roles",
    "create-roles",
    "delete-roles",

    // Users
    "view-users",
    "change-user-roles",
    // "delete-users",
];

$grantedPermissions = [];

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
            $grantedPermissions = json_decode($existing_role['permissions']);
        }
    } else {
        // Redirect or show an error if role doesn't exist
        $errors[] = "Role not found.";
    }
}


// Add or remove the permission
if (isset($_GET['role_id']) && !empty($_GET['role_id']) && isset($_GET['permission']) && !empty($_GET['permission'])) {
    $role_id = $_GET['role_id'];
    $permission = $_GET['permission'];

    // Fetch the role from the database
    $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = :role_id");
    $stmt->bindParam(':role_id', $role_id);
    $stmt->execute();
    $existing_role = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing_role) {
        die(json_encode(["status" => "error", "message" => "Role not found."]));
    }

    // Decode existing permissions from JSON
    $grantedPermissions = json_decode($existing_role['permissions'], true);

    if (!is_array($grantedPermissions)) {
        $grantedPermissions = []; // Ensure it's an array
    }

    // Add the permission if it's not in the array, otherwise remove it
    if (in_array($permission, $grantedPermissions)) {
        // Remove permission
        $grantedPermissions = array_diff($grantedPermissions, [$permission]);
    } else {
        // Add permission
        $grantedPermissions[] = $permission;
    }

    // Encode back to JSON for storage
    $updatedPermissions = json_encode(array_values($grantedPermissions));

    // Update the database
    $updateStmt = $pdo->prepare("UPDATE roles SET permissions = :permissions WHERE id = :role_id");
    $updateStmt->bindParam(':permissions', $updatedPermissions);
    $updateStmt->bindParam(':role_id', $role_id);

    if ($updateStmt->execute()) {
        $_SESSION['success_message'] = "Changes saved successfully!";

        header("Location: ./create_update_role.php?role_id={$role_id}");

        // echo json_encode(["status" => "success", "message" => "Permission updated.", "permissions" => $grantedPermissions]);
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

//     //     $grantedPermissions = json_decode($existing_role['permissions']);
//     // } else {
//     //     // Redirect or show an error if role doesn't exist
//     //     $errors[] = "Role not found.";
//     // }
// }
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
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">
                                    <?php echo (isset($_GET['role_id']) && !empty($_GET['role_id'])) ? 'Edit Role' : 'Add a New Role'; ?>
                                </h4>

                                <p class="card-description">This role will include a set of permissions that determine what a user can do on the system.</p>

                                <form class="forms-sample" method="POST" action="">
                                    <div class="row">

                                        <div class="form-group col-md-6">
                                            <label for="name">Name</label>

                                            <input type="text" class="form-control" id="name" name="name" placeholder="Name" value="<?php echo isset($form_data['name']) ? htmlspecialchars($form_data['name']) : ''; ?>">
                                            <span class="text-danger validation-error"><?php echo isset($errors['name']) ? $errors['name'] : ''; ?></span>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="description">Description</label>

                                            <input type="tel" class="form-control" id="description" name="description" placeholder="Type the role description" value="<?php echo isset($form_data['description']) ? htmlspecialchars($form_data['description']) : ''; ?>">
                                            <span class="text-danger validation-error"><?php echo isset($errors['description']) ? $errors['description'] : ''; ?></span>
                                        </div>

                                        <div>
                                            <button type="submit" class="btn btn-primary me-2">Submit</button>
                                            <a href="./user_contacts.php" class="btn btn-light">Cancel</a>
                                        </div>
                                </form>
                            </div>
                        </div>
                    </div>


                    <div class="mt-4" style="display: <?= is_null($roleId) ? 'none' : 'block'; ?>">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">
                                    Set permissions on this role
                                </h4>

                                <p class="card-description">Click on a permission to set it. Users will only be able to access features whose permission has been added.</p>

                                <form class="forms-sample" method="POST" action="">
                                    <div class="row">
                                        <div class="form-group">
                                            <label for="permissions">Permissions</label>

                                            <div class="row">
                                                <?php foreach ($systemPermissions as $permission): ?>
                                                    <div class="col-6 col-md-4 col-lg-3 mb-3">
                                                        <a href="create_update_role.php?role_id=<?php echo $roleId; ?>&permission=<?php echo $permission; ?>" style="cursor:pointer;" class="text-black">
                                                            <i class="bi <?php echo in_array($permission, $grantedPermissions) ? 'bi-check-circle' : 'bi-x-circle'; ?>" style="color: <?php echo in_array($permission, $grantedPermissions) ? '#22c55e' : '#9ca3af'; ?>"></i>

                                                            <span><?= ucwords(str_replace("-", " ", $permission)); ?></span>
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