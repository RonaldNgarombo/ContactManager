<?php
// Start the session
// session_start();
require_once './../../utilities/auth_check.php';

require_once './../../database/db.php';
require_once './../../utilities/activity_logger.php';

// Initialize error variables and form data
$errors = [];
$form_data = [];

log_action($pdo, "View profile", "User viewed their profile page.");

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user['id'];

// Get the user's current profile information and display it in the forms
if ($user_id) {
    // $contact_id = $_GET['contact_id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id); // Ensure user is the logged-in user
    $stmt->execute();
    $retrieved_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($retrieved_user) {
        $form_data = $retrieved_user;

        // $form_data['phone_number'] = $retrieved_user['phone'];
    } else {
        // Redirect or show an error if contact doesn't exist
        $errors[] = "User not found.";
    }
}

// Handle form submission for password change
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data and sanitize it
    $form_data['first_name'] = trim($_POST['first_name']);
    $form_data['last_name'] = trim($_POST['last_name']);
    $form_data['email'] = trim($_POST['email']);

    // Validate inputs
    if (empty($form_data['first_name'])) {
        $errors['first_name'] = 'First name is required.';
    }
    if (empty($form_data['last_name'])) {
        $errors['last_name'] = 'Last name is required.';
    }
    if (empty($form_data['email'])) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format.';
    }

    // If no errors, update user profile
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email WHERE id = :user_id");

            // Bind parameters using the correct array values
            $stmt->bindParam(':first_name', $form_data['first_name']);
            $stmt->bindParam(':last_name', $form_data['last_name']);
            $stmt->bindParam(':email', $form_data['email']);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Profile information updated successfully!";

                log_action($pdo, "Update profile", "Profile information updated successfully!");

                header("Location: ./update_profile.php");
                exit();
            } else {
                $errors[] = "Failed to update profile information.";

                log_action($pdo, "Update profile", "Failed to update profile information.", 2);
            }
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Dashboard | Change Password</title>
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

                    <div class="row">
                        <div class="grid-margin stretch-card col-12 col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">
                                        Update your profile
                                    </h4>

                                    <p class="card-description">Change your profile details.</p>

                                    <form class="forms-sample" method="POST" action="">
                                        <div class="row">

                                            <div class="form-group">
                                                <label for="name">First name</label>

                                                <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Type your first name" value="<?php echo isset($form_data['first_name']) ? htmlspecialchars($form_data['first_name']) : ''; ?>">
                                                <span class="text-danger validation-error"><?php echo isset($errors['first_name']) ? $errors['first_name'] : ''; ?></span>
                                            </div>

                                            <div class="form-group">
                                                <label for="name">Last name</label>

                                                <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Type your last name" value="<?php echo isset($form_data['last_name']) ? htmlspecialchars($form_data['last_name']) : ''; ?>">
                                                <span class="text-danger validation-error"><?php echo isset($errors['last_name']) ? $errors['last_name'] : ''; ?></span>
                                            </div>

                                            <div class="form-group">
                                                <label for="name">Email</label>

                                                <input type="text" class="form-control" id="email" name="email" placeholder="Type your email" value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>">
                                                <span class="text-danger validation-error"><?php echo isset($errors['email']) ? $errors['email'] : ''; ?></span>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary me-2">Update Profile</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="grid-margin stretch-card col-12 col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Update Profile</h4>
                                    <p class="card-description">Change your profile details.</p>

                                    <form class="forms-sample" method="POST" action="./handle_avatar_upload.php" enctype="multipart/form-data">
                                        <div class="text-center mb-4">
                                            <?php
                                            $avatar = isset($form_data['avatar']) && !empty($form_data['avatar']) ? $form_data['avatar'] : 'placeholder.png';
                                            ?>

                                            <img id="profile-pic" src="./../../avatars/<?php echo htmlspecialchars($avatar); ?>" class="rounded-circle" width="270" height="270" alt="Profile Picture" data-bs-toggle="modal" data-bs-target="#viewAvatarModal">

                                            <br>

                                            <label for="avatar" class="btn btn-sm btn-outline-primary mt-2">
                                                <i class="bi bi-camera"></i> Select Avatar
                                            </label>

                                            <input type="file" id="avatar" name="avatar" class="d-none" accept="image/*">
                                        </div>

                                        <button type="submit" class="btn btn-primary me-2">Change Avatar</button>
                                    </form>

                                    <div class="modal fade" id="viewAvatarModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h1 class="modal-title fs-5" id="staticBackdropLabel">User Avatar</h1>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    <div style="height: 80vh;">
                                                        <img id="profile-pic" src="./../../avatars/<?php echo htmlspecialchars($avatar); ?>"
                                                            class="img-fluid mx-auto d-block "
                                                            style="width: 100%; height: 100%; object-fit: contain;"
                                                            alt="Profile Picture" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            document.getElementById("avatar").addEventListener("change", function(event) {
                                const file = event.target.files[0];
                                if (file) {
                                    const reader = new FileReader();
                                    reader.onload = function(e) {
                                        document.getElementById("profile-pic").src = e.target.result;
                                    };
                                    reader.readAsDataURL(file);
                                }
                            });
                        </script>

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