<?php
// Start the session
session_start();

require_once './../../database/db.php';
require_once './../../utilities/activity_logger.php';

// Log view activity logs
log_action($pdo, "View activity logs", "User viewed a list of activity logs");

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user['id'];

$sql = "SELECT COUNT(*) FROM users";
$stmt = $pdo->prepare($sql);
$stmt->execute();

// Fetch the count as an integer
$total_users = $stmt->fetchColumn();

$sql = "SELECT COUNT(*) FROM contacts";
$stmt = $pdo->prepare($sql);
$stmt->execute();

// Fetch the count as an integer
$total_contacts = $stmt->fetchColumn();


$sql = "SELECT activity_logs.*, users.first_name, users.last_name, users.email 
        FROM activity_logs
        JOIN users ON activity_logs.user_id = users.id
        ORDER BY id DESC LIMIT 8";

$stmt = $pdo->prepare($sql);

// Execute the query
$stmt->execute();

// Fetch all results
$activity_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);


$sql = "SELECT phone_type, COUNT(*) as count FROM contacts GROUP BY phone_type";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$contact_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$series = [];

foreach ($contact_counts as $row) {
    $labels[] = $row['phone_type'];
    $series[] = (int)$row['count']; // Ensure it's an integer
}

// Encode data as JSON for JavaScript
$labels_json = json_encode($labels);
$series_json = json_encode($series);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Dashboard | Contact Manager</title>
    <!-- plugins:css -->

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- <link rel="stylesheet" href="./../../assets/vendors/apexcharts/apexcharts.min.js"> -->

    <link rel="stylesheet" href="./../../assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="./../../assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="./../../assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="./../../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="./../../assets/vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="./../../assets/vendors/mdi/css/materialdesignicons.min.css">

    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- Not by me <link rel="stylesheet" href="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css"> -->
    <!-- <link rel="stylesheet" href="./../../assets/vendors/datatables.net-bs5/dataTables.bootstrap5.css"> -->
    <link rel="stylesheet" href="./../../assets/vendors/ti-icons/css/themify-icons.css">
    <!-- <link rel="stylesheet" type="text/css" href="./../../assets/js/select.dataTables.min.css"> -->

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
                    <div class="row">
                        <div class="col-md-12 grid-margin">
                            <div class="row">
                                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                                    <h3 class="font-weight-bold">Welcome <?php echo $user['first_name'] ?></h3>

                                    <h6 class="font-weight-normal mb-0">Below is a summary of the system.</h6>
                                </div>

                                <div class="col-12 col-xl-4">
                                    <!-- <div class="justify-content-end d-flex">
                                        <div class="dropdown flex-md-grow-1 flex-xl-grow-0">
                                            <button class="btn btn-sm btn-light bg-white dropdown-toggle" type="button" id="dropdownMenuDate2" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <i class="mdi mdi-calendar"></i> Today (10 Jan 2021) </button>
                                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuDate2">
                                                <a class="dropdown-item" href="#">January - March</a>
                                                <a class="dropdown-item" href="#">March - June</a>
                                                <a class="dropdown-item" href="#">June - August</a>
                                                <a class="dropdown-item" href="#">August - November</a>
                                            </div>
                                        </div>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <div id="contactCategoriesChart"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 stretch-card transparent">
                                            <div class="card card-dark-blue">
                                                <a href="./user_contacts.php">
                                                    <div class="card-body text-white">
                                                        <p class="mb-4">Total Contacts</p>
                                                        <p class="fs-30 mb-2"><?php echo number_format($total_contacts) ?></p>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>

                                        <div class="col-md-6 stretch-card transparent">
                                            <div class="card card-light-danger">
                                                <a href="#">
                                                    <div class="card-body text-white">
                                                        <p class="mb-4">Number of users</p>
                                                        <p class="fs-30 mb-2"><?php echo number_format($total_users) ?></p>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <p class="card-title mb-0">Recent activity logs</p>
                                    <p class="card-title mb-0"><a href="./view_activity_logs.php" style="color: #4f46e5;">View more</a></p>
                                </div>

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
                                    <!-- <p class="text-center">No activity logs found.</p> -->
                                <?php endif; ?>


                            </div>
                        </div>
                    </div>


                    <div class="col-md-5 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">To Do Lists</h4>
                                <div class="list-wrapper pt-2">
                                    <ul class="d-flex flex-column-reverse todo-list todo-list-custom">
                                        <li>
                                            <div class="form-check form-check-flat">
                                                <label class="form-check-label">
                                                    <input class="checkbox" type="checkbox"> Meeting with Urban Team </label>
                                            </div>
                                            <i class="remove ti-close"></i>
                                        </li>
                                        <li class="completed">
                                            <div class="form-check form-check-flat">
                                                <label class="form-check-label">
                                                    <input class="checkbox" type="checkbox" checked> Duplicate a project for new customer </label>
                                            </div>
                                            <i class="remove ti-close"></i>
                                        </li>
                                        <li>
                                            <div class="form-check form-check-flat">
                                                <label class="form-check-label">
                                                    <input class="checkbox" type="checkbox"> Project meeting with CEO </label>
                                            </div>
                                            <i class="remove ti-close"></i>
                                        </li>
                                        <li class="completed">
                                            <div class="form-check form-check-flat">
                                                <label class="form-check-label">
                                                    <input class="checkbox" type="checkbox" checked> Follow up of team zilla </label>
                                            </div>
                                            <i class="remove ti-close"></i>
                                        </li>
                                        <li>
                                            <div class="form-check form-check-flat">
                                                <label class="form-check-label">
                                                    <input class="checkbox" type="checkbox"> Level up for Antony </label>
                                            </div>
                                            <i class="remove ti-close"></i>
                                        </li>
                                    </ul>
                                </div>
                                <div class="add-items d-flex mb-0 mt-2">
                                    <input type="text" class="form-control todo-list-input" placeholder="Add new task">
                                    <button class="add btn btn-icon text-primary todo-list-add-btn bg-transparent"><i class="icon-circle-plus"></i></button>
                                </div>
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
    <!-- <script src="./../../assets/vendors/datatables.net/jquery.dataTables.js"></script> -->
    <!-- <script src="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script> -->
    <!-- <script src="./../../assets/vendors/datatables.net-bs5/dataTables.bootstrap5.js"></script> -->
    <!-- <script src="./../../assets/js/dataTables.select.min.js"></script> -->
    <script src="./../../assets/js/off-canvas.js"></script>
    <script src="./../../assets/js/template.js"></script>
    <script src="./../../assets/js/settings.js"></script>
    <script src="./../../assets/js/todolist.js"></script>
    <script src="./../../assets/js/jquery.cookie.js" type="text/javascript"></script>
    <script src="./../../assets/js/dashboard.js"></script>

    <script>
        let chart;

        // const labels = ['Personal', 'Family', 'Business'];
        // const series = [12, 18, 5];

        const labels = <?php echo $labels_json; ?>;
        const series = <?php echo $series_json; ?>;

        const donutOptions = {
            chart: {
                type: 'donut'
            },
            series: series,
            labels: labels,
            colors: ['#007AFF', '#c026d3', '#b91c1c', '#f59e0b', '#0284c7', '#84cc16',
                '#4f46e5'
            ],
            legend: {
                // show: false
            },
            plotOptions: {
                pie: {
                    // customScale: .6
                    // size: 10 #005A80
                    donut: {
                        size: '40%',
                        labels: {
                            show: true,
                            name: {
                                show: false
                            },
                            value: {
                                show: true
                            }
                        }
                    },

                }
            },
            dataLabels: {
                enabled: true,
                formatter: function(value, {
                    seriesIndex,
                    dataPointIndex,
                    w
                }) {
                    return ''
                },
                style: {
                    fontSize: '25px',
                    fontWeight: '500',
                },
                textAnchor: 'start',
            }
        };

        chart = new ApexCharts(document.querySelector("#contactCategoriesChart"), donutOptions);

        chart.render();
    </script>

</body>

</html>