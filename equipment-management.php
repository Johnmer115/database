<?php 
include ('session-checker.php');
require_once "config.php"; 
// Get usertype and username from session and convert usertype to uppercase for consistency
$usertype = strtoupper($_SESSION['usertype']);
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Equipment Management - AU Technical Support Management System</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center">
      <a href="index.php">
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
<main class="container mt-5">
<div class="container mt-5">
  <div class="row" style="margin-top: 70px;">
    <div class="col-md-8">
      <div class="card p-4">
        <div class="d-flex">
          <!-- Left: Logo -->
          <img src="picture/Equipment-logo.png" alt="Equipment Logo" class="equipment-logo" style="height: 70px;">
          <!-- Right: Title -->
          <h4 class="text-primary">Equipment Management - List</h4>
        </div>
        <div style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
          <a href="create-equipment.php" class="btn btn-success">
            <i class="ri-add-circle-line"></i> Add New Equipment
          </a>
        </div>
        <?php

        $firstEquipment = null;

        function buildtable($result) {
          global $firstEquipment;
          if(mysqli_num_rows($result) > 0) {
            echo "<table class='table table-striped'>";
            echo '<thead class="table-primary">
                    <tr>
                      <th>Asset Number</th>
                      <th>Serial Number</th>
                      <th>Type</th>
                      <th>Branch</th>
                      <th>Status</th>
                      <th>Created By</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>';
            
            while($row = mysqli_fetch_array($result)) {
              if(!$firstEquipment){
                $firstEquipment = $row;
              }
              echo "<tr>";
              echo "<td>" . $row['assetnumber'] . "</td>";
              echo "<td>" . $row['serialnumber'] . "</td>";
              echo "<td>" . $row['type'] . "</td>";
              echo "<td>" . $row['branch'] . "</td>";
              echo "<td>" . $row['status'] . "</td>";
              echo "<td>" . $row['createdby'] . "</td>";
              echo "<td>";
              echo "<a href='update-equipment.php?assetnumber=" . $row['assetnumber'] . "' 
                      class='edit-icon' 
                      onmouseover='this.style.backgroundColor = \"green\"; this.style.color = \"white\";' 
                      onmouseout='this.style.backgroundColor = \"transparent\"; this.style.color = \"green\";' 
                      style='font-size: 1.2rem; text-decoration: none; color: green; background-color: transparent; padding: 5px; border-radius: 4px; transition: background-color 0.3s, color 0.3s;' 
                      title='Edit'>
                      <i class='ri-pencil-line'></i>
                    </a>";
              echo "<a href='#' 
                      class='delete-icon' 
                      data-bs-toggle='modal' 
                      data-bs-target='#confirmDeleteModal' 
                      onclick='setDeleteEquipment(\"" . $row['assetnumber'] . "\")' 
                      onmouseover='this.style.backgroundColor = \"red\"; this.style.color = \"white\";' 
                      onmouseout='this.style.backgroundColor = \"transparent\"; this.style.color = \"red\";' 
                      style='font-size: 1.2rem; text-decoration: none; color: red; background-color: transparent; padding: 5px; border-radius: 4px; transition: background-color 0.3s, color 0.3s;' 
                      title='Delete'>
                      <i class='ri-delete-bin-line'></i>
                    </a>";
              echo "</td>";
              echo "</tr>";
            }
            echo "</tbody></table>";
          } else {
            echo "<div class='alert alert-warning text-center'>No records found</div>";
          }
        }

        require_once "config.php";
        if(isset($_POST['btnsearch'])) {
          // Sorting by creation time (latest first) in search results
          $sql = "SELECT * FROM tblequipments WHERE assetnumber LIKE ? OR serialnumber LIKE ? OR type LIKE ? OR department LIKE ? ORDER BY created_at DESC";
          if($stmt = mysqli_prepare($link, $sql)) {
            $searchvalue = '%' . $_POST['txtsearch'] . '%';
            mysqli_stmt_bind_param($stmt, "ssss", $searchvalue, $searchvalue, $searchvalue, $searchvalue);
            if(mysqli_stmt_execute($stmt)) {
              $result = mysqli_stmt_get_result($stmt);
              buildtable($result);
            }
          }
        } else {
          // Sorting by creation time (latest first)
          $sql = "SELECT * FROM tblequipments ORDER BY created_at DESC";
          if($stmt = mysqli_prepare($link, $sql)) {
            if(mysqli_stmt_execute($stmt)) {
              $result = mysqli_stmt_get_result($stmt);
              buildtable($result);
            }
          }
        }
        ?>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card card-custom p-3 text-center">
        <img src="picture/acc_icon.png" alt="User Icon" class="user-icon">
        <h2 class="text-primary mt-5">Welcome, <?= $_SESSION['username'] ?></h2>
        <h5 class="text-muted">User Type: <?= $_SESSION['usertype'] ?></h5>
      </div>
      <div class="card card-custom p-3 mt-3">
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
          <div class="input-group">
            <input type="text" name="txtsearch" class="form-control" placeholder="Search equipment...">
            <button type="submit" name="btnsearch" class="btn btn-primary">Search</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-danger">⚠️ Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">Are you sure you want to delete this equipment?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <a id="confirmDeleteBtn" href="#" class="btn btn-danger">Yes, Delete</a>
      </div>
    </div>
  </div>
</div>
</main>
<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header">
        <h5 class="modal-title text-success">✅ Delete Equipment - Success</h5>
      </div>
      <div class="modal-body">
        <p>Equipment deleted successfully!</p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<script>
function setDeleteEquipment(assetnumber) {
  document.getElementById("confirmDeleteBtn").href = "delete-equipment.php?assetnumber=" + assetnumber;
}

// Ensure the success modal appears if deletion was successful
document.addEventListener("DOMContentLoaded", function () {
  <?php if(isset($_SESSION['delete_success'])): ?>
    var successModal = new bootstrap.Modal(document.getElementById('successModal'));
    successModal.show();
    <?php unset($_SESSION['delete_success']); // Clear session after showing modal ?>
  <?php endif; ?>
});
</script>

<!-- Footer -->
<footer class="custom-footer text-center border-top">
  &copy; CopyRight 2025, Tanqui-on, Johnmer
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
