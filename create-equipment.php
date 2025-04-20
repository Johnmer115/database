<?php 
require_once "config.php";
include("session-checker.php");
$usertype = strtoupper($_SESSION['usertype']);
$username = $_SESSION['username'];

$message = ""; // Store error/warning message
$success = false; // Flag for success

// Default values to prevent undefined variable issues
$assetnumber = $serialnumber = $type = $manufacturer = "";
$yearmodel = $description = $branch = $department = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnsubmit'])) {
    $assetnumber = trim($_POST['txtassetnumber'] ?? "");
    $serialnumber = trim($_POST['txtserialnumber'] ?? "");
    $type = $_POST['cmbtype'] ?? "";
    $manufacturer = trim($_POST['txtmanufacturer'] ?? "");
    $yearmodel = trim($_POST['txtyearmodel'] ?? "");
    $description = trim($_POST['txtdescription'] ?? "");
    $branch = $_POST['cmbbranch'] ?? "";
    $department = $_POST['cmbdepartment'] ?? "";
    $status = "WORKING";
    $createdby = $_SESSION['username'] ?? "Unknown";
    $datecreated = date("Y-m-d"); // Standard MySQL date format

    // Validation checks
    if (empty($assetnumber) || empty($serialnumber) || empty($type) || empty($manufacturer) || 
        empty($yearmodel) || empty($branch) || empty($department) || empty($description)) {
        $message = "<div class='alert alert-danger text-center'><strong>All fields are required.</strong></div>";
    } 
    elseif (!preg_match("/^\d{4}$/", $yearmodel) || ($yearmodel < 1900 || $yearmodel > date("Y"))) {
        $message = "<div class='alert alert-danger text-center'><strong>Year model must be a 4-digit number between 1900 and " . date("Y") . ".</strong></div>";
    } 
    else {
        // Check if asset number or serial number already exists
        $sql = "SELECT * FROM tblequipments WHERE assetnumber = ? OR serialnumber = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $assetnumber, $serialnumber);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                $message = "<div class='alert alert-warning text-center'><strong>Asset Number or Serial Number is already in use.</strong></div>";
            } else {
                // Insert new equipment
                $sql = "INSERT INTO tblequipments (assetnumber, serialnumber, type, manufacturer, yearmodel, description, branch, department, status, createdby, datecreated) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                if ($stmt = mysqli_prepare($link, $sql)) {
                    mysqli_stmt_bind_param($stmt, "sssssssssss", 
                        $assetnumber, $serialnumber, $type, $manufacturer, $yearmodel, 
                        $description, $branch, $department, $status, $createdby, $datecreated);
                    if (mysqli_stmt_execute($stmt)) {
                        // Instead of redirecting immediately, set a flag to show the success modal.
                        $success = true;
                    } else {
                        $message = "<div class='alert alert-danger text-center'><strong>Error inserting record: " . mysqli_error($link) . "</strong></div>";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Equipment - AU Technical Support Management System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <a href="equipment-management.php">
                <img src="picture/Arellano_University_logo.png" style="height:70px; margin-right: 10px;" alt="University Logo">
            </a>
            <a class="navbar-brand highlight" href="#">Technical Support Management System</a>
        </div>
        <div>
        <?php if($usertype == 'ADMINISTRATOR'): ?>
            <a href="accounts-management.php" class="btn btn-success">Account Management</a>
            <a href="equipment-management.php" class="btn btn-success">Equipment Management</a>
            <a href="tickets-management.php" class="btn btn-success">Ticket Management</a>
        <?php elseif($usertype == 'TECHNICAL'): ?>
            <a href="equipment-management.php" class="btn btn-success">Equipment Management</a>
            <a href="tickets-management.php" class="btn btn-success">Ticket Management</a>
        <?php else: ?>
            <a href="equipment-management-view.php" class="btn btn-success">Equipment Management(View version)</a>
            <a href="tickets-management.php" class="btn btn-success">Ticket Management</a>
        <?php endif; ?>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
    </div>
</nav>


<main div class="container mt-5">
<div class="container mt-5">
    <div class="row justify-content-center" style="margin-top: 80px;">
        <div class="col-md-8"> <!-- Increased width to fit two inputs per row -->
            <div class="card p-4 shadow">
                <h4 class="text-primary text-center">Create New Equipment</h4>

                <?= $message; ?>

                <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <!-- First Row: Asset Number & Serial Number -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Asset Number:</label>
                            <input type="text" name="txtassetnumber" class="form-control" required value="<?= htmlspecialchars($assetnumber) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Serial Number:</label>
                            <input type="text" name="txtserialnumber" class="form-control" required value="<?= htmlspecialchars($serialnumber) ?>">
                        </div>
                    </div>

                    <!-- Second Row: Equipment Type & Manufacturer -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Equipment Type:</label>
                            <select name="cmbtype" class="form-select" required>
                                <option value="">--Select Equipment Type--</option>
                                <option value="Monitor" <?= ($type == "Monitor") ? "selected" : "" ?>>Monitor</option>
                                <option value="CPU" <?= ($type == "CPU") ? "selected" : "" ?>>CPU</option>
                                <option value="Keyboard" <?= ($type == "Keyboard") ? "selected" : "" ?>>Keyboard</option>
                                <option value="Mouse" <?= ($type == "Mouse") ? "selected" : "" ?>>Mouse</option>
                                <option value="AVR" <?= ($type == "AVR") ? "selected" : "" ?>>AVR</option>
                                <option value="MAC" <?= ($type == "MAC") ? "selected" : "" ?>>MAC</option>
                                <option value="Printer" <?= ($type == "Printer") ? "selected" : "" ?>>Printer</option>
                                <option value="Projector" <?= ($type == "Projector") ? "selected" : "" ?>>Projector</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Manufacturer:</label>
                            <input type="text" name="txtmanufacturer" class="form-control" required value="<?= htmlspecialchars($manufacturer) ?>">
                        </div>
                    </div>

                    <!-- Third Row: Year Model & Branch -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Year Model:</label>
                            <input type="number" name="txtyearmodel" class="form-control" required value="<?= htmlspecialchars($yearmodel) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Branch:</label>
                            <select name="cmbbranch" class="form-select" required>
                                <option value="">--Select Branch--</option>
                                <option value="AU Legarda/Main" <?= ($branch == "AU Legarda/Main") ? "selected" : "" ?>>Juan Sumulong Campus</option>
                                <option value="AU Pasay" <?= ($branch == "AU Pasay") ? "selected" : "" ?>>Jose Abad Santos Campus</option>
                                <option value="Arellano School of Law" <?= ($branch == "Arellano School of Law") ? "selected" : "" ?>>Arellano School of Law</option>
                                <option value="AU Pasig" <?= ($branch == "AU Pasig") ? "selected" : "" ?>>Andres Bonifacio Campus</option>
                                <option value="AU Malabon" <?= ($branch == "AU Malabon") ? "selected" : "" ?>>Jose Rizal Campus</option>
                                <option value="AU Mandaluyong" <?= ($branch == "AU Mandaluyong") ? "selected" : "" ?>>Plaridel Campus</option>
                                <option value="AU Pasay 2" <?= ($branch == "AU Pasay 2") ? "selected" : "" ?>>Apolinario Mabini Campus</option>
                                <option value="AU Malabon 2" <?= ($branch == "AU Malabon 2") ? "selected" : "" ?>>Elisa Esguerra Campus</option>
                            </select>
                        </div>
                    </div>

                    <!-- Fourth Row: Department & Description -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Department:</label>
                            <select name="cmbdepartment" class="form-select" required>
                                <option value="">--Select Department--</option>
                                <option value="Institute of Accountancy" <?= ($department == "Institute of Accountancy") ? "selected" : "" ?>>Institute of Accountancy</option>
                                <option value="School of Business Administration" <?= ($department == "School of Business Administration") ? "selected" : "" ?>>School of Business Administration</option>
                                <option value="College of General Education and Liberal Arts" <?= ($department == "College of General Education and Liberal Arts") ? "selected" : "" ?>>College of General Education and Liberal Arts</option>
                                <option value="College of Nursing" <?= ($department == "College of Nursing") ? "selected" : "" ?>>College of Nursing</option>
                                <option value="School of Computer Studies" <?= ($department == "School of Computer Studies") ? "selected" : "" ?>>School of Computer Studies</option>
                                <option value="College of Criminal Justice Education" <?= ($department == "College of Criminal Justice Education") ? "selected" : "" ?>>College of Criminal Justice Education</option>
                                <option value="School of Education" <?= ($department == "School of Education") ? "selected" : "" ?>>School of Education</option>
                                <option value="College of Medical Laboratory Science" <?= ($department == "College of Medical Laboratory Science") ? "selected" : "" ?>>College of Medical Laboratory Science</option>
                                <option value="School of Midwifery" <?= ($department == "School of Midwifery") ? "selected" : "" ?>>School of Midwifery</option>
                                <option value="College of Pharmacy" <?= ($department == "College of Pharmacy") ? "selected" : "" ?>>College of Pharmacy</option>
                                <option value="College of Physical Therapy" <?= ($department == "College of Physical Therapy") ? "selected" : "" ?>>College of Physical Therapy</option>
                                <option value="College of Radiologic Technology" <?= ($department == "College of Radiologic Technology") ? "selected" : "" ?>>College of Radiologic Technology</option>
                                <option value="School of Psychology" <?= ($department == "School of Psychology") ? "selected" : "" ?>>School of Psychology</option>
                                <option value="School of Hospitality and Tourism Management" <?= ($department == "School of Hospitality and Tourism Management") ? "selected" : "" ?>>School of Hospitality and Tourism Management</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description:</label>
                            <textarea name="txtdescription" class="form-control" rows="3" required><?= htmlspecialchars($description) ?></textarea>
                        </div>
                    </div>

                    <!-- Save and Cancel Buttons -->
                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" name="btnsubmit" class="btn btn-success btn-lg">Save</button>
                        <a href="equipment-management.php" class="btn btn-secondary btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content text-center">
          <div class="modal-header">
              <h5 class="modal-title text-success">✅ Ticket Created Successfully</h5>
          </div>
          <div class="modal-body">
              <p>Your ticket has been created successfully!</p>
          </div>
          <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="redirectToManagement();">OK</button>
          </div>
      </div>
  </div>
</div>
</main>

<footer class="custom-footer text-center border-top">
  &copy; CopyRight 2025, Tanqui-on, Johnmer
</footer>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// If the success flag is set, show the success modal.
document.addEventListener("DOMContentLoaded", function() {
    <?php if($success): ?>
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    <?php endif; ?>
});

// Function to redirect to equipment management page.
function redirectToManagement() {
    window.location.href = "equipment-management.php";
}
</script>
</body>
</html>
