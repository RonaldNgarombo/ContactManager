<?php

require_once './../../utilities/auth_check.php';

require_once './../../database/db.php';
require_once './../../utilities/activity_logger.php';
require_once './../../utilities/system_feature_check.php';

// Initialize error variables, form data and success messages
$errors = [];
$form_data = [];

// log_action("View create/update contact", "User viewed the create/update contact page.");

userCan('create-contacts', 'page');

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user['id'];

// Handle form submission for creating or updating the contact
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die("CSRF validation failed!");
    }

    // Get form data and sanitize it
    $form_data['name'] = trim($_POST['name']);
    $form_data['phone_number'] = trim($_POST['phone_number']);
    $form_data['phone_type'] = trim($_POST['phone_type']);
    $form_data['email'] = trim($_POST['email']);
    $form_data['address'] = trim($_POST['address']);

    // Validate the inputs
    if (empty($form_data['name'])) {
        $errors['name'] = 'Name is required.';
    }

    if (empty($form_data['phone_number'])) {
        $errors['phone_number'] = 'Phone number is required.';
    }

    if (empty($form_data['phone_type'])) {
        $errors['phone_type'] = 'Phone type is required.';
    }

    if (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL) && !empty($form_data['email'])) {
        $errors['email'] = 'Invalid email format.';
    }

    // If no errors, insert or update the record in the database
    if (empty($errors)) {
        // Get the user from the session

        // Start a transaction
        $pdo->beginTransaction();

        try {
            // Check if we are updating an existing record
            if (isset($_GET['contact_id']) && !empty($_GET['contact_id'])) {
                // Update existing record
                $contact_id = $_GET['contact_id'];
                $sql = "UPDATE contacts SET name = :name, phone = :phone, phone_type = :phone_type, email = :email, address = :address WHERE id = :contact_id AND user_id = :user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $form_data['name']);
                $stmt->bindParam(':phone', $form_data['phone_number']);
                $stmt->bindParam(':phone_type', $form_data['phone_type']);
                $stmt->bindParam(':email', $form_data['email']);
                $stmt->bindParam(':address', $form_data['address']);
                $stmt->bindParam(':contact_id', $contact_id);
                $stmt->bindParam(':user_id', $user_id); // Assume you have $user_id based on your session or authentication system

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Contact updated successfully!";

                    log_action("Update contact", "Contact information updated successfully!");
                } else {
                    $errors[] = "An error occurred while updating the contact.";

                    log_action("Update contact", "An error occurred while updating the contact.", 2);

                    throw new Exception("Error updating contact.");
                }
            } else {
                // Create a new contact
                $sql = "INSERT INTO contacts (user_id, name, phone, phone_type, email, address) VALUES (:user_id, :name, :phone_number, :phone_type, :email, :address)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':user_id', $user_id); // Assume you have $user_id based on your session or authentication system
                $stmt->bindParam(':name', $form_data['name']);
                $stmt->bindParam(':phone_number', $form_data['phone_number']);
                $stmt->bindParam(':phone_type', $form_data['phone_type']);
                $stmt->bindParam(':email', $form_data['email']);
                $stmt->bindParam(':address', $form_data['address']);

                if ($stmt->execute()) {

                    $_SESSION['success_message'] = "Contact created successfully!";

                    log_action("Create contact", "New contact created successfully!");
                } else {
                    $errors[] = "An error occurred while saving the contact.";

                    log_action("Create contact", "An error occurred while creating a new contact.", 2);

                    throw new Exception("Error creating contact.");
                }
            }

            // Commit the transaction if everything is successful
            $pdo->commit();

            // If successful, clear the form data
            $form_data = [];

            // Redirect to the contacts page
            header("Location: ./user_contacts.php");
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $pdo->rollBack();
            $errors[] = $e->getMessage();

            log_action("Create contact", $e->getMessage(), 2);
        }
    }
}

// If updating, fetch the existing data for repopulating the form
if (isset($_GET['contact_id']) && !empty($_GET['contact_id'])) {
    $contact_id = $_GET['contact_id'];
    $stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = :contact_id AND user_id = :user_id");
    $stmt->bindParam(':contact_id', $contact_id);
    $stmt->bindParam(':user_id', $user_id); // Ensure user is the logged-in user
    $stmt->execute();
    $existing_contact = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_contact) {
        $form_data = $existing_contact;

        $form_data['phone_number'] = $existing_contact['phone'];
    } else {
        // Redirect or show an error if contact doesn't exist
        $errors[] = "Contact not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Add User Contact | Contact Manager</title>

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
                                    <?php echo (isset($_GET['contact_id']) && !empty($_GET['contact_id'])) ? 'Edit Contact' : 'Add a New Contact'; ?>
                                </h4>

                                <p class="card-description">Add personal, family, or business contacts.</p>

                                <form class="forms-sample" method="POST" action="">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

                                    <div class="row">

                                        <div class="form-group col-md-6">
                                            <label for="name">Name</label>

                                            <input type="text" class="form-control" id="name" name="name" placeholder="Name" value="<?php echo isset($form_data['name']) ? htmlspecialchars($form_data['name']) : ''; ?>">
                                            <span class="text-danger validation-error"><?php echo isset($errors['name']) ? $errors['name'] : ''; ?></span>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="phone_number">Phone number</label>

                                            <input type="tel" class="form-control" id="phone_number" name="phone_number" placeholder="Phone number E.g. +256704031764" value="<?php echo isset($form_data['phone_number']) ? htmlspecialchars($form_data['phone_number']) : ''; ?>">
                                            <span class="text-danger validation-error"><?php echo isset($errors['phone_number']) ? $errors['phone_number'] : ''; ?></span>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="phone_type">Type</label>
                                            <select class="form-select form-select-lg" id="phone_type" name="phone_type">
                                                <option value="">Select the type of contact</option>
                                                <option value="Personal" <?php echo (isset($form_data['phone_type']) && $form_data['phone_type'] == 'Personal') ? 'selected' : ''; ?>>Personal</option>
                                                <option value="Family" <?php echo (isset($form_data['phone_type']) && $form_data['phone_type'] == 'Family') ? 'selected' : ''; ?>>Family</option>
                                                <option value="Business" <?php echo (isset($form_data['phone_type']) && $form_data['phone_type'] == 'Business') ? 'selected' : ''; ?>>Business</option>
                                            </select>
                                            <span class="text-danger validation-error"><?php echo isset($errors['phone_type']) ? $errors['phone_type'] : ''; ?></span>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="email">Email</label>

                                            <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>">
                                            <span class="text-danger validation-error"><?php echo isset($errors['email']) ? $errors['email'] : ''; ?></span>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="address">Address</label>

                                            <input type="text" class="form-control" id="address" name="address" placeholder="Address" value="<?php echo isset($form_data['address']) ? htmlspecialchars($form_data['address']) : ''; ?>">
                                            <span class="text-danger validation-error"><?php echo isset($errors['address']) ? $errors['address'] : ''; ?></span>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary me-2">Submit</button>
                                    <a href="./user_contacts.php" class="btn btn-light">Cancel</a>
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