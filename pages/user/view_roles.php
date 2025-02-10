<?php

require_once './../../utilities/auth_check.php';

require_once './../../database/db.php';
require_once './../../utilities/activity_logger.php';

// Log view roles
// log_action("View roles", "User viewed a list of roles");

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user['id'];

$sql = "SELECT * FROM roles ORDER BY id DESC";

$stmt = $pdo->prepare($sql);

// Execute the query
$stmt->execute();

// Fetch all results
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Roles | Contact Manager</title>

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
                                    <p class="card-title mb-0">System Roles</p>

                                    <div>
                                        <a href="./create_update_role.php" type="submit" class="btn btn-primary me-2">+ Add Role</a>
                                    </div>
                                </div>

                                <hr>

                                <?php if (!empty($roles)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-borderless">
                                            <thead>
                                                <tr>
                                                    <th>Role name</th>
                                                    <th>Description</th>
                                                    <!-- <th>Type</th> -->
                                                    <!-- <th>Email</th> -->
                                                    <!-- <th>Address</th> -->
                                                    <th>#Action</th>
                                                </tr>
                                            </thead>

                                            <tbody>

                                                <?php foreach ($roles as $role): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($role['name']); ?></td>
                                                        <td>
                                                            <?php
                                                            if ($role['description']) {
                                                                echo htmlspecialchars($role['description']);
                                                            } else {
                                                                echo 'N/a';
                                                            }
                                                            ?>
                                                        </td>

                                                        <td>
                                                            <a href="create_update_role.php?role_id=<?php echo $role['id']; ?>" class="badge badge-success">Edit</a>
                                                            <a href="delete_role.php?role_id=<?php echo $role['id']; ?>" class="badge badge-danger" onclick="return confirm('Are you sure you want to delete this role?')">Delete</a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>

                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-center">No roles found.</p>
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

</body>

</html>