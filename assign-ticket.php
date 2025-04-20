<?php
require_once "config.php";
include("session-checker.php");

// Ensure only administrators can access this page.
if ($_SESSION['usertype'] !== 'ADMINISTRATOR') {
    header("location: index.php");
    exit();
}

$message = "";
$ticket = [];
$ticketnumber = $_GET['ticket'] ?? '';
$showModal = false;  // Initialize the flag

// Retrieve ticket details.
if (!empty($ticketnumber)) {
    $sql = "SELECT * FROM tbltickets WHERE ticketnumber = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $ticketnumber);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $ticket = mysqli_fetch_assoc($result) ?? [];
        mysqli_stmt_close($stmt);
    }
    // Set the currently assigned technician (if any)
    $assignedTech = $ticket['assignedto'] ?? '';
}

// Retrieve list of technicians.
$sqlTech = "SELECT username FROM tblaccounts WHERE usertype = 'TECHNICAL'";
$resTech = mysqli_query($link, $sqlTech);
$technicians = mysqli_fetch_all($resTech, MYSQLI_ASSOC);

// Process form submission for assigning technician.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assignedTo = $_POST['cmbtech'] ?? '';
    $dateAssigned = date("d/m/Y");
    
    if (!empty($assignedTo)) {
        $sqlUpdate = "UPDATE tbltickets SET status = 'ONGOING', dateassigned = ?, assignedto = ? WHERE ticketnumber = ?";
        if ($stmt = mysqli_prepare($link, $sqlUpdate)) {
            mysqli_stmt_bind_param($stmt, "sss", $dateAssigned, $assignedTo, $ticketnumber);
            if (mysqli_stmt_execute($stmt)) {
                // Log the action.
                $sqlLog = "INSERT INTO tblogs (datelog, timelog, module, action, performedto, performedby) VALUES (?, ?, ?, ?, ?, ?)";
                if ($stmtLog = mysqli_prepare($link, $sqlLog)) {
                    $date = date("d/m/Y");
                    $time = date("h:i:sa");
                    $module = "Ticket Management";
                    $action = "Assign";
                    mysqli_stmt_bind_param($stmtLog, "ssssss", $date, $time, $module, $action, $ticketnumber, $_SESSION['username']);
                    mysqli_stmt_execute($stmtLog);
                    mysqli_stmt_close($stmtLog);
                }
                // Set flag to trigger modal display
                $showModal = true;
            } else {
                $message = "<div class='alert alert-danger text-center'>Error updating ticket.</div>";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $message = "<div class='alert alert-warning text-center'>Please select a technician.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Assign Ticket - AU Technical Support</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    .btn-custom { width: 100%; }
  </style>
</head>
<body>
<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
          <a href="tickets-management.php">
              <img src="picture/Arellano_University_logo.png" style="height:70px; margin-right:10px;" alt="University Logo">
          </a>
          <a class="navbar-brand" href="#">Technical Support Management System</a>
      </div>
      <div>      
        <a href="tickets-management.php" class="btn btn-success">Ticket Management</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
      </div>
  </div>
</nav>
  
<!-- Main Form -->
<div class="container mt-5">
  <div class="row justify-content-center" style="margin-top:80px;">
    <div class="col-md-6">
      <div class="card p-4 shadow">
        <h4 class="text-primary text-center mb-4"><i class="fas fa-ticket-alt"></i> Assign Ticket</h4>
        <?= $message; ?>
        <form action="<?= htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST">
            <!-- Ticket Number (Display Only) -->
            <div class="mb-3">
                <label class="form-label text-primary fw-bold">Ticket Number:</label>
                <span class="form-control-plaintext"><?= htmlspecialchars($ticket['ticketnumber'] ?? 'N/A'); ?></span>
            </div>
            <!-- Problem (Display Only) -->
            <div class="mb-3">
                <label class="form-label text-primary fw-bold">Problem:</label>
                <span class="form-control-plaintext"><?= htmlspecialchars($ticket['problem'] ?? 'N/A'); ?></span>
            </div>
            <!-- Details (Display Only) -->
            <div class="mb-3">
                <label class="form-label text-primary fw-bold">Details:</label>
                <span class="form-control-plaintext"><?= htmlspecialchars($ticket['details'] ?? 'N/A'); ?></span>
            </div>
            <!-- Technician Assignment Dropdown -->
            <div class="mb-3">
                <label class="form-label text-primary fw-bold">Assign Technician:</label>
                <select name="cmbtech" class="form-select" required>
                    <option value="">-- Select Technician --</option>
                    <?php foreach ($technicians as $tech): ?>
                        <option value="<?= htmlspecialchars($tech['username']); ?>" 
                            <?= (isset($assignedTech) && trim($assignedTech) === trim($tech['username'])) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tech['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Save and Cancel Buttons -->
            <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-success btn-custom">Save</button>
                <a href="tickets-management.php" class="btn btn-secondary btn-custom">Cancel</a>
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
  
<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Trigger modal if update was successful -->
<script>
  <?php if (isset($showModal) && $showModal === true): ?>
    var successModal = new bootstrap.Modal(document.getElementById('successModal'));
    successModal.show();
  <?php endif; ?>
</script>
</body>
</html>
