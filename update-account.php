<?php 
require_once "config.php";
include("session-checker.php");

$message = ""; // To store error/warning messages
$accountUpdated = false; // Flag to indicate successful update

// Retrieve account data based on username passed via GET
if (isset($_GET['username']) && !empty(trim($_GET['username']))) {
    $username = $_GET['username'];
    $sql = "SELECT * FROM tblaccounts WHERE username = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $account = mysqli_fetch_array($result, MYSQLI_ASSOC) ?? [];
        }
        mysqli_stmt_close($stmt);
    }
} else {
    $message = "<div class='alert alert-danger text-center'>No account specified to update.</div>";
}

// Process form submission for updating the account
if (isset($_POST['btnsubmit'])) {
    // Retrieve form values
    $newPassword = $_POST['txtpassword'] ?? "";
    $newUsertype = $_POST['cmbtype'] ?? "";
    $newStatus   = $_POST['rbstatus'] ?? "";
    
    $sql = "UPDATE tblaccounts SET password = ?, usertype = ?, status = ? WHERE username = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssss", $newPassword, $newUsertype, $newStatus, $username);
        if (mysqli_stmt_execute($stmt)) {
            $accountUpdated = true;
            // Optionally, you can log the update action in a logs table here if needed
        } else {
            $message = "<div class='alert alert-danger text-center'><strong style='color: red;'>Error updating account.</strong></div>";
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
  <title>Update Account - AU Technical Support Management System</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
          <a href="accounts-management.php">
              <img src="picture/Arellano_University_logo.png" style="height:70px; margin-right:10px;" alt="University Logo">
          </a>
          <a class="navbar-brand highlight" href="#">Technical Support Management System</a>
      </div>
      <div>
            <a href="accounts-management.php" class="btn btn-success">Account Management</a>
            <a href="equipment-management.php" class="btn btn-success">Equipment Management</a>
            <a href="tickets-management.php" class="btn btn-success">Ticket Management</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
      </div>
  </div>
</nav>


<main div class="container mt-5">
<div class="container mt-5">
  <div class="row justify-content-center" style="margin-top:150px;">
      <div class="col-md-6">
          <div class="card p-4 shadow">
              <h4 class="text-primary text-center">Update Account</h4>
              <!-- Display error or warning message -->
              <?= $message; ?>
              <form id="updateAccountForm" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST">
                  <div class="mb-3">
                      <label class="form-label">Username:</label>
                      <input type="text" class="form-control" value="<?= $account['username'] ?? ''; ?>" disabled>
                  </div>
                  <div class="mb-3">
                      <label class="form-label">Password:</label>
                      <input type="password" name="txtpassword" class="form-control" value="<?= $account['password'] ?? ''; ?>" required>
                  </div>
                  <div class="mb-3">
                      <label class="form-label">Account Type:</label>
                      <select name="cmbtype" class="form-select" required>
                          <option value="ADMINISTRATOR" <?= (isset($account['usertype']) && $account['usertype'] == 'ADMINISTRATOR') ? 'selected' : ''; ?>>Administrator</option>
                          <option value="TECHNICAL" <?= (isset($account['usertype']) && $account['usertype'] == 'TECHNICAL') ? 'selected' : ''; ?>>Technical</option>
                          <option value="STAFF" <?= (isset($account['usertype']) && $account['usertype'] == 'STAFF') ? 'selected' : ''; ?>>Staff</option>
                          <option value="USER" <?= (isset($account['usertype']) && $account['usertype'] == 'USER') ? 'selected' : ''; ?>>User</option>
                      </select>
                  </div>
                  <div class="mb-3">
                      <label class="form-label">Status:</label>
                      <div>
                          <input type="radio" name="rbstatus" value="ACTIVE" <?= (isset($account['status']) && $account['status'] == 'ACTIVE') ? 'checked' : ''; ?>> Active
                          <input type="radio" name="rbstatus" value="INACTIVE" <?= (isset($account['status']) && $account['status'] == 'INACTIVE') ? 'checked' : ''; ?>> Inactive
                      </div>
                  </div>
                  <div class="d-flex justify-content-end gap-2">
                      <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmUpdateModal">Update</button>
                      <a href="accounts-management.php" class="btn btn-secondary">Cancel</a>
                  </div>
              </form>
          </div>
      </div>
  </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmUpdateModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Update</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">Are you sure you want to update this account?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success" form="updateAccountForm" name="btnsubmit">Yes, Update</button>
      </div>
    </div>
  </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header">
        <h5 class="modal-title text-success">✅ Account Updated Successfully</h5>
      </div>
      <div class="modal-body">
        <p>Account has been updated successfully!</p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-primary" onclick="window.location.href='accounts-management.php'">OK</button>
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
document.addEventListener("DOMContentLoaded", function () {
    <?php if($accountUpdated): ?>
      var successModal = new bootstrap.Modal(document.getElementById('successModal'));
      successModal.show();
    <?php endif; ?>
});
</script>
</body>
</html>
