<?php 
require_once "config.php";
include("session-checker.php");

$message = "";
$ticket = [];

// Retrieve ticket data based on ticketnumber passed via GET
if (isset($_GET['ticketnumber']) && !empty(trim($_GET['ticketnumber']))) {
    $sql = "SELECT * FROM tbltickets WHERE ticketnumber = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $_GET['ticketnumber']);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $ticket = mysqli_fetch_array($result, MYSQLI_ASSOC) ?? [];
        }
        mysqli_stmt_close($stmt);
    }
}

// Process form submission for updating the ticket
if (isset($_POST['btnsubmit'])) {
    $problem = $_POST['cmbproblem'] ?? "";
    $details = trim($_POST['txtdetails'] ?? "");
    $ticketnumber = $_GET['ticketnumber'];

    $sql = "UPDATE tbltickets SET problem = ?, details = ? WHERE ticketnumber = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "sss", $problem, $details, $ticketnumber);
        if (mysqli_stmt_execute($stmt)) {
            // Log the action using a different statement variable
            $sql_log = "INSERT INTO tblogs (datelog, timelog, module, action, performedto, performedby) VALUES (?, ?, ?, ?, ?, ?)";
            if ($stmt_log = mysqli_prepare($link, $sql_log)) {
                $date = date("d/m/Y");
                $time = date("h:i:sa");
                $action = "Update";
                $module = "Ticket Management";
                mysqli_stmt_bind_param($stmt_log, "ssssss", $date, $time, $module, $action, $ticketnumber, $_SESSION['username']);
                mysqli_stmt_execute($stmt_log);
                mysqli_stmt_close($stmt_log);
            }

            // Trigger success modal
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    let successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                });
            </script>";
        } else {
            $message = "<div class='alert alert-danger text-center'><strong style='color: red;'>Error updating ticket.</strong></div>";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update Ticket - AU Technical Support Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
          <a href="tickets-management.php">
              <img src="picture/Arellano_University_logo.png" style="height:70px; margin-right:10px;" alt="University Logo">
          </a>
          <a class="navbar-brand" href="#">Technical Support Management System</a>
      </div>
      <div>
        <a href="equipment-management-view.php" class="btn btn-success">Equipment Management(View version)</a>      
        <a href="tickets-management.php" class="btn btn-success">Ticket Management</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
      </div>
  </div>
</nav>

<!-- Main Form -->
 <main div class="container mt-5">
<div class="container mt-5">
  <div class="row justify-content-center" style="margin-top:80px;">
    <div class="col-md-6">
      <div class="card p-4 shadow">
        <h4 class="text-primary text-center mb-4">Update Ticket</h4>
        <?= $message; ?>
        <form id="updateForm" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST">
          <!-- Ticket Number (Display Only) -->
          <div class="mb-3">
            <label class="form-label">Ticket Number:</label>
            <span class="form-control-plaintext"><?= $ticket['ticketnumber'] ?? ''; ?></span>
          </div>
          <!-- Problem Dropdown -->
          <div class="mb-3">
            <label class="form-label">Problem:</label>
            <select name="cmbproblem" class="form-select" required>
              <option value="">--Select Problem Type--</option>
              <option value="Hardware" <?= (isset($ticket['problem']) && $ticket['problem'] == "Hardware") ? "selected" : ""; ?>>Hardware</option>
              <option value="Software" <?= (isset($ticket['problem']) && $ticket['problem'] == "Software") ? "selected" : ""; ?>>Software</option>
              <option value="Connection" <?= (isset($ticket['problem']) && $ticket['problem'] == "Connection") ? "selected" : ""; ?>>Connection</option>
            </select>
          </div>
          <!-- Details Textarea -->
          <div class="mb-3">
            <label class="form-label">Details:</label>
            <textarea name="txtdetails" class="form-control" rows="5" placeholder="Enter ticket details" required><?= $ticket['details'] ?? ''; ?></textarea>
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
        <h5 class="modal-title text-success">✅ Ticket Updated Successfully</h5>
      </div>
      <div class="modal-body">
        <p>The ticket has been updated successfully!</p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-primary" onclick="window.location.href='tickets-management.php'">OK</button>
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
</body>
</html>
