<?php

require_once './../../utilities/auth_check.php';

require_once './../../database/db.php';
require_once './../../utilities/activity_logger.php';
require_once './../../utilities/system_feature_check.php';

// Log view users
// log_action("View users", "User viewed a list of users");

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user['id'];

// Initialize search and filter conditions
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT users.*, roles.name AS role_name 
        FROM users 
        LEFT JOIN roles ON users.role_id = roles.id";

// Apply search filter if provided
if (!empty($search)) {
    $sql .= " WHERE users.first_name LIKE :search 
              OR users.last_name LIKE :search 
              OR users.email LIKE :search";
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);

// Bind parameters
if (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bindParam(':search', $search_param, PDO::PARAM_STR);
}

// Execute the query
$stmt->execute();

// Fetch all results
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Users | Contact Manager</title>

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

                    <div class="grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <p class="card-title mb-0">Users</p>

                                    <div>
                                        <div class="modal fade" id="importContactsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Import Users</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>

                                                    <div class="modal-body">
                                                        <p class="card-description">You can easily import your personal, family, or business users.</p>

                                                        <form class="forms-sample" method="POST" action="import_contacts.php" enctype="multipart/form-data">
                                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

                                                            <div class="row">

                                                                <div class="form-group">
                                                                    <label for="name">Select CSV file</label>

                                                                    <input type="file" class="form-control" id="csv_file" name="csv_file" value="<?php echo isset($form_data['csv_file']) ? htmlspecialchars($form_data['csv_file']) : ''; ?>" accept=".csv">
                                                                    <span class="text-danger validation-error"><?php echo isset($errors['csv_file']) ? $errors['csv_file'] : ''; ?></span>
                                                                </div>
                                                            </div>

                                                            <hr>

                                                            <button type="submit" class="btn btn-primary me-2">Submit</button>
                                                            <a href="./user_contacts.php" class="btn btn-light">Cancel</a>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- <button id="import-btn" type="button" class="btn btn-success me-2 text-white" data-bs-toggle="modal" data-bs-target="#importContactsModal">+ Import Users</button> -->
                                        <!-- <button id="export-btn" type="button" class="btn btn-secondary me-2 text-white" style="background-color: #000000;">+ Export Users</button> -->
                                        <!-- <a href="./add_user_contact.php" type="submit" class="btn btn-primary me-2">+ Add User</a> -->
                                    </div>
                                </div>

                                <hr>

                                <form method="GET" action="" id="searchForm">
                                    <div class="row mb-3">
                                        <div class="form-group col-5">
                                            <input name="search" id="searchInput" type="text" class="form-control form-control-sm" placeholder="Search by name, phone, email..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                        </div>

                                        <div class="form-group col-4">

                                        </div>

                                        <div class="col-3">
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-primary">Search</button>
                                                <a href="./user_contacts.php" class="btn btn-secondary">Reset</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <hr>

                                <?php if (!empty($users)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-borderless">
                                            <thead>
                                                <tr>
                                                    <th>#ID</th>
                                                    <th>First name</th>
                                                    <th>Last name</th>
                                                    <th>Email</th>
                                                    <th>Role</th>
                                                    <th>#Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <?php foreach ($users as $usr): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($usr['id']); ?></td>
                                                        <td><?php echo htmlspecialchars($usr['first_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($usr['last_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($usr['email']) ?: 'N/a'; ?></td>
                                                        <td><?php echo htmlspecialchars($usr['role_name']) ?: 'N/a'; ?></td>
                                                        <td>
                                                            <a href="update_user_role.php?user_id=<?php echo $usr['id']; ?>" class="badge badge-success">Update role</a>
                                                            <!-- <a href="delete_contact.php?contact_id=<?php echo $usr['id']; ?>" class="badge badge-danger" onclick="return confirm('Are you sure you want to delete this usr?')">Delete</a> -->
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>

                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-center">No users found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>

                <?php include './../../components/navigation/footer.php'; ?>
            </div>
        </div>
    </div>

    <?php include './../../components/page_script_imports.php'; ?>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const searchInput = document.getElementById("searchInput");
            const searchForm = document.getElementById("searchForm");

            // Auto-submit when typing (with delay)
            let typingTimer;
            searchInput.addEventListener("keyup", function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    searchForm.submit();
                }, 500); // 500ms delay to avoid excessive requests
            });
        });
    </script>

</body>

</html>