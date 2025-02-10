<?php

require_once './../../utilities/auth_check.php';

require_once './../../database/db.php';
require_once './../../utilities/activity_logger.php';

// Log view activity logs
// log_action("View activity logs", "User viewed a list of activity logs");

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user['id'];

// Initialize search and filter conditions
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

$sql = "SELECT activity_logs.*, users.first_name, users.last_name, users.email 
        FROM activity_logs
        JOIN users ON activity_logs.user_id = users.id
        WHERE activity_logs.user_id = :user_id";

// Apply search filter if provided
if (!empty($search)) {
    $sql .= " AND (action LIKE :search OR details LIKE :search)";
}

// Apply phone type filter if provided
if (!empty($status)) {
    $sql .= " AND status = :status";
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);

// Bind parameters
$stmt->bindParam(':user_id', $user_id);

if (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bindParam(':search', $search_param);
}

if (!empty($status)) {
    $stmt->bindParam(':status', $status);
}

// Execute the query
$stmt->execute();

// Fetch all results
$activity_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Activity Logs | Contact Manager</title>

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
                                    <p class="card-title mb-0">System Activity Logs</p>

                                    <div>
                                        <button id="btnExportActivityLogs" type="button" class="btn btn-secondary me-2 text-white" style="background-color: #000000;">+ Export Activity Logs</button>
                                    </div>
                                </div>

                                <hr>

                                <form method="GET" action="" id="searchForm">
                                    <div class="row mb-3">
                                        <div class="form-group col-5">
                                            <input name="search" id="searchInput" type="text" class="form-control form-control-sm" placeholder="Search by name, phone, email..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                        </div>

                                        <div class="form-group col-4">
                                            <select class="form-select form-select-sm" name="status" id="statusType">
                                                <option value="">Filter by status</option>
                                                <option value="1" <?php echo (isset($_GET['status']) && $_GET['status'] == '1') ? 'selected' : ''; ?>>Successful</option>
                                                <option value="2" <?php echo (isset($_GET['status']) && $_GET['status'] == '2') ? 'selected' : ''; ?>>Failed</option>
                                            </select>
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

                                <?php if (!empty($activity_logs)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-borderless">
                                            <thead>
                                                <tr>
                                                    <th>Timestamp</th>
                                                    <th>User</th>
                                                    <th>Action</th>
                                                    <th>Type</th>
                                                    <th>Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <?php foreach ($activity_logs as $log): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
                                                        <!-- <td>John Doe</td> -->
                                                        <td>
                                                            <div>
                                                                <?php echo htmlspecialchars($log['first_name']); ?>
                                                                <?php echo htmlspecialchars($log['last_name']); ?>

                                                                <br>

                                                                <span class="text-secondary"><?php echo htmlspecialchars($log['email']); ?></span>
                                                            </div>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($log['action']); ?></td>
                                                        <td>
                                                            <?php
                                                            if ($log['status'] == 1) {
                                                                echo '<span class="text-success">Success</span>';
                                                            } else if ($log['status'] == 2) {
                                                                echo '<span class="text-danger">Failure</span>';
                                                            } else {
                                                                echo '<span class="text-muted">Unknown</span>';
                                                            }
                                                            ?>
                                                        </td>

                                                        <td><?php echo htmlspecialchars($log['details']); ?></td>
                                                        <!-- <td>
                                                            <a href="add_user_contact.php?contact_id=<?php echo $log['id']; ?>" class="badge badge-success">Edit</a>
                                                            <a href="delete_contact.php?contact_id=<?php echo $log['id']; ?>" class="badge badge-danger" onclick="return confirm('Are you sure you want to delete this log?')">Delete</a>
                                                        </td> -->
                                                    </tr>
                                                <?php endforeach; ?>

                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-center">No activity logs found.</p>
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
            const statusType = document.getElementById("statusType");
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
            statusType.addEventListener("change", function() {
                searchForm.submit();
            });

            /**
             * Export activity logs to CSV
             */
            document.getElementById("btnExportActivityLogs").addEventListener("click", function() {

                let searchQuery = document.getElementById("searchInput").value;
                let statusType = document.getElementById("statusType").value;

                // return console.log(searchQuery, statusType);
                let params = new URLSearchParams({
                    search: searchQuery,
                    status: statusType
                });

                window.location.href = "export_activity_logs.php?" + params.toString();
            });

        });
    </script>

</body>

</html>