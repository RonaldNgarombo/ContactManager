<?php
// Start the session
// session_start();

// require_once './../../database/db.php';

// $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
// $user_id = $user['id'];

// // Prepare the SELECT query
// $sql = "SELECT * FROM contacts WHERE user_id = :user_id ORDER BY id DESC";
// $stmt = $pdo->prepare($sql);

// // Bind the user ID parameter
// $stmt->bindParam(':user_id', $user_id);

// // Execute the query
// $stmt->execute();

// // Fetch all results
// $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// if ($contacts) {
//     // If contacts are found, process them (e.g., display in a table or list)
// } else {
//     // No contacts found
//     $errors[] = "No contacts found.";
// }


// Start the session
session_start();
require_once './../../database/db.php';

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user['id'];

// Initialize search and filter conditions
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$phone_type = isset($_GET['phone_type']) ? trim($_GET['phone_type']) : '';

$sql = "SELECT * FROM contacts WHERE user_id = :user_id";

// Apply search filter if provided
if (!empty($search)) {
    $sql .= " AND (name LIKE :search OR phone LIKE :search OR email LIKE :search)";
}

// Apply phone type filter if provided
if (!empty($phone_type)) {
    $sql .= " AND phone_type = :phone_type";
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);

// Bind parameters
$stmt->bindParam(':user_id', $user_id);

if (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bindParam(':search', $search_param);
}

if (!empty($phone_type)) {
    $stmt->bindParam(':phone_type', $phone_type);
}

// Execute the query
$stmt->execute();

// Fetch all results
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Dashboard | Contact Manager</title>
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

                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>Success!</strong> <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>

                    <div class="grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <p class="card-title mb-0">My Contacts</p>

                                    <a href="./add_user_contact.php" type="submit" class="btn btn-primary me-2">+ Add Contact</a>
                                </div>

                                <hr>

                                <form method="GET" action="" id="searchForm">
                                    <div class="row mb-3">
                                        <div class="form-group col-5">
                                            <input name="search" id="searchInput" type="text" class="form-control form-control-sm" placeholder="Search by name, phone, email..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                        </div>

                                        <div class="form-group col-4">
                                            <select class="form-select form-select-sm" name="phone_type" id="phoneType">
                                                <option value="">Filter by Type</option>
                                                <option value="Personal" <?php echo (isset($_GET['phone_type']) && $_GET['phone_type'] == 'Personal') ? 'selected' : ''; ?>>Personal</option>
                                                <option value="Family" <?php echo (isset($_GET['phone_type']) && $_GET['phone_type'] == 'Family') ? 'selected' : ''; ?>>Family</option>
                                                <option value="Business" <?php echo (isset($_GET['phone_type']) && $_GET['phone_type'] == 'Business') ? 'selected' : ''; ?>>Business</option>
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

                                <?php if (!empty($contacts)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-borderless">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Phone</th>
                                                    <th>Type</th>
                                                    <th>Email</th>
                                                    <th>Address</th>
                                                    <th>#Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <?php foreach ($contacts as $contact): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($contact['phone']); ?></td>
                                                        <td><?php echo htmlspecialchars($contact['phone_type']); ?></td>
                                                        <td><?php echo htmlspecialchars($contact['email']) ?: 'N/a'; ?></td>
                                                        <td><?php echo htmlspecialchars($contact['address']) ?: 'N/a'; ?></td>
                                                        <td>
                                                            <a href="add_user_contact.php?contact_id=<?php echo $contact['id']; ?>" class="badge badge-success">Edit</a>
                                                            <a href="delete_contact.php?contact_id=<?php echo $contact['id']; ?>" class="badge badge-danger" onclick="return confirm('Are you sure you want to delete this contact?')">Delete</a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>

                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p>No contacts found.</p>
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
        });
    </script>

</body>

</html>