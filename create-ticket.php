<?php  
require_once "config.php";
include("session-checker.php");
$usertype = strtoupper($_SESSION['usertype']);
$username = $_SESSION['username'];

$message = ""; // To store error or warning messages
$ticketSuccess = false; // Flag to trigger success modal

// Auto-generate ticket number based on the current date and time.
$ticketnumber = date("YmdHis");

// Default values
$problem = "";
$details = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnsubmit'])) {
    // Retrieve and trim inputs
    $problem = $_POST['cmbproblem'] ?? "";
    $details = trim($_POST['txtdetails'] ?? "");
    
    // Set fixed values for ticket creation
    $status = "PENDING";
    $createdby = $_SESSION['username'] ?? "Unknown";
    $datecreated = date("Y-m-d H:i:s"); // Using full timestamp
    // Set empty strings instead of null values
    $assignedto = $dateassigned = $datecompleted = $approvedby = $dateapproved = "";

    // Validation: Ensure a problem is selected and details are provided
    if (empty($problem) || empty($details)) {
        $message = "<div class='alert alert-danger text-center'><strong>Please select a problem type and provide the details of the problem.</strong></div>";
    } else {
        // Check if the ticket number already exists (should be unique)
        $sql = "SELECT * FROM tbltickets WHERE ticketnumber = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $ticketnumber);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                // Although highly unlikely, regenerate if duplicate
                $ticketnumber = date("YmdHis");
            }
            mysqli_stmt_close($stmt);
        }

        // Insert new ticket record
        $sql = "INSERT INTO tbltickets (ticketnumber, problem, details, status, createdby, datecreated, assignedto, dateassigned, datecompleted, approvedby, dateapproved)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssssssssss", 
                $ticketnumber, $problem, $details, $status, $createdby, $datecreated,
                $assignedto, $dateassigned, $datecompleted, $approvedby, $dateapproved);
            
            if (mysqli_stmt_execute($stmt)) {
                // Set the success flag instead of redirecting immediately.
                $ticketSuccess = true;
            } else {
                $message = "<div class='alert alert-danger text-center'><strong>Error inserting record: " . mysqli_error($link) . "</strong></div>";
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create New Ticket - AU Technical Support Management System</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <a href="accounts-management.php">
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
      <div class="col-md-8">
          <div class="card p-4 shadow">
              <h4 class="text-primary text-center mb-4">Create New Ticket</h4>

              <?= $message; ?>

              <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                  <!-- Ticket Number (auto-generated and displayed as label) -->
                  <div class="mb-3">
                      <label class="form-label">Ticket Number:</label>
                      <span class="form-control-plaintext"><?= $ticketnumber; ?></span>
                      <!-- Hidden field to pass ticketnumber to POST -->
                      <input type="hidden" name="txtticketnumber" value="<?= $ticketnumber; ?>">
                  </div>

                  <!-- Problem Dropdown -->
                  <div class="mb-3">
                      <label class="form-label">Problem:</label>
                      <select name="cmbproblem" class="form-select" required>
                          <option value="">--Select Problem Type--</option>
                          <option value="Hardware" <?= ($problem == "Hardware") ? "selected" : "" ?>>Hardware</option>
                          <option value="Software" <?= ($problem == "Software") ? "selected" : "" ?>>Software</option>
                          <option value="Connection" <?= ($problem == "Connection") ? "selected" : "" ?>>Connection</option>
                      </select>
                  </div>

                  <!-- Details Textarea -->
                  <div class="mb-3">
                      <label class="form-label">Details:</label>
                      <textarea name="txtdetails" class="form-control" rows="5" placeholder="Describe your problem in detail..." required><?= htmlspecialchars($details); ?></textarea>
                  </div>

                  <!-- Save and Cancel Buttons -->
                  <div class="d-flex justify-content-end gap-2">
                      <button type="submit" name="btnsubmit" class="btn btn-success btn-lg">Save</button>
                      <a href="tickets-management.php" class="btn btn-secondary btn-lg">Cancel</a>
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
              <button type="button" class="btn btn-primary" id="successOkBtn">OK</button>
          </div>
      </div>
  </div>
</div>
</main>

<footer class="custom-footer text-center border-top">
  &copy; CopyRight 2025, Tanqui-on, Johnmer
</footer>

<!-- Bootstrap JS (Optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// If ticket creation was successful, show the success modal and redirect on OK click.
document.addEventListener("DOMContentLoaded", function() {
    <?php if($ticketSuccess): ?>
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();

        document.getElementById("successOkBtn").addEventListener("click", function() {
            window.location.href = "tickets-management.php";
        });
    <?php endif; ?>
});
</script>
</body>
</html>
