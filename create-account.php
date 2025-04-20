<?php 
require_once "config.php";
include("session-checker.php");


$message = ""; // Store error/warning message
$accountCreated = false; // Flag to indicate success

if(isset($_POST['btnsubmit'])) {
    // Check if the username already exists
    $sql = "SELECT * FROM tblaccounts WHERE username = ?";
    if($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $_POST['txtusername']);
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if(mysqli_num_rows($result) == 0) {
                // Insert new account
                $sql = "INSERT INTO tblaccounts (username, password, usertype, status, createdby, datecreated) VALUES (?, ?, ?, ?, ?, ?)";
                if($stmt = mysqli_prepare($link, $sql)) {
                    $status = "ACTIVE";
                    $date = date("d/m/Y");
                    mysqli_stmt_bind_param($stmt, "ssssss", $_POST['txtusername'], $_POST['txtpassword'], $_POST['cmbtype'], $status, $_SESSION['username'], $date);
                    if(mysqli_stmt_execute($stmt)) {
                        $accountCreated = true;
                    }
                } else {
                    $message = "<div class='alert alert-danger text-center'><strong style='color: red;'>ERROR on INSERT statement</strong></div>";
                } 
            } else {
                $message = "<div class='alert alert-warning text-center'><strong style='color: red;'>Username is already in use</strong></div>";
            }
        } else {
            $message = "<div class='alert alert-danger text-center'><strong style='color: red;'>ERROR on SELECT statement</strong></div>";
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
  <title>Create New Account - AU Technical Support Management System</title>
  
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
              <h4 class="text-primary text-center">Create New Account</h4>
              
              <!-- Display error or warning message -->
              <?= $message; ?>

              <form id="createAccountForm" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                  <div class="mb-3">
                      <label class="form-label">Username:</label>
                      <input type="text" name="txtusername" class="form-control" required>
                  </div>
                  <div class="mb-3">
                      <label class="form-label">Password:</label>
                      <input type="password" name="txtpassword" class="form-control" required>
                  </div>
                  <div class="mb-3">
                      <label class="form-label">Account Type:</label>
                      <select name="cmbtype" class="form-select" required>
                          <option value="">--Select Account Type--</option>
                          <option value="ADMINISTRATOR">Administrator</option>
                          <option value="TECHNICAL">Technical</option>
                          <option value="STAFF">Staff</option>
                      </select>
                  </div>
                  <div class="d-flex justify-content-end gap-2">
                      <button type="submit" name="btnsubmit" class="btn btn-success btn-lg">Save</button>
                      <a href="accounts-management.php" class="btn btn-secondary btn-lg">Cancel</a>
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
              <h5 class="modal-title text-success">✅ Account Created Successfully</h5>
          </div>
          <div class="modal-body">
              <p>Account has been created successfully!</p>
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

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    <?php if($accountCreated): ?>
      var successModal = new bootstrap.Modal(document.getElementById('successModal'));
      successModal.show();
      document.getElementById("successOkBtn").addEventListener("click", function(){
          window.location.href = "accounts-management.php";
      });
    <?php endif; ?>
});
</script>
</body>
</html>
