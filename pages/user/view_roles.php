<?php
// Start the session
// session_start();
require_once './../../utilities/auth_check.php';

require_once './../../database/db.php';
require_once './../../utilities/activity_logger.php';

// Log view roles
log_action($pdo, "View roles", "User viewed a list of roles");

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user['id'];

$sql = "SELECT * FROM roles";

$stmt = $pdo->prepare($sql);

// Execute the query
$stmt->execute();

// Fetch all results
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Roles | Role Manager</title>
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

                    <div class="grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <p class="card-title mb-0">System Roles</p>

                                    <div>
                                        <a href="./add_user_contact.php" type="submit" class="btn btn-primary me-2">+ Add Role</a>
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
                                                        <!-- <td><?php echo htmlspecialchars($role['description']) ?: 'N/a'; ?></td> -->

                                                        <td><?php echo var_dump($role['description']) ?></td>

                                                        <td><a href="#">Permissions</a></td>
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

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const searchInput = document.getElementById("searchInput");
            const phoneType = document.getElementById("phoneType");
            const searchForm = document.getElementById("searchForm");

            // Auto-submit when typing (with delay)
            let typingTimer;
            searchInput.addEventListener("keyup", function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    searchForm.submit();
                }, 500); // 500ms delay to avoid excessive requests
            });

            // Auto-submit when phone type changes
            phoneType.addEventListener("change", function() {
                searchForm.submit();
            });

            /**
             * Export roles to CSV
             */
            document.getElementById("export-btn").addEventListener("click", function() {
                // return alert("Exporting roles is not implemented yet.");
                let searchQuery = document.getElementById("searchInput").value;
                let phoneType = document.getElementById("phoneType").value;

                // return console.log(searchQuery, phoneType);
                let params = new URLSearchParams({
                    search: searchQuery,
                    phone_type: phoneType
                });

                window.location.href = "export_contacts.php?" + params.toString();
            });

        });
    </script>

</body>

</html>