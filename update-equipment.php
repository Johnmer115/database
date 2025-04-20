<?php
require_once "config.php";
include "session-checker.php";

$usertype = strtoupper($_SESSION['usertype']);
$username = $_SESSION['username'];

$message = "";
$equipment = [];

// Retrieve equipment record using assetnumber
if (isset($_GET['assetnumber']) && !empty(trim($_GET['assetnumber']))) {
    $sql = "SELECT * FROM tblequipments WHERE assetnumber = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $_GET['assetnumber']);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $equipment = mysqli_fetch_array($result, MYSQLI_ASSOC) ?? [];
        }
        mysqli_stmt_close($stmt);
    }
}

if (isset($_POST['btnsubmit'])) {
  $serialnumber   = trim($_POST['txtserialnumber']);
  $type           = $_POST['cmbtype'];
  $manufacturer   = trim($_POST['txtmanufacturer']);
  $yearmodel      = trim($_POST['txtyearmodel']);
  $description    = trim($_POST['txtdescription']);
  $branch         = $_POST['cmbbranch'];
  $department     = $_POST['cmbdepartment'];
  $status         = $_POST['rbstatus'];
  $currentAsset   = $_GET['assetnumber'];

  if (empty($serialnumber) || empty($type) || empty($manufacturer) || empty($yearmodel) || empty($branch) || empty($department)) {
      $message = "<div class='alert alert-danger text-center'><strong>All fields are required.</strong></div>";
  } elseif (!preg_match("/^\d{4}$/", $yearmodel) || ($yearmodel < 1900 || $yearmodel > date("Y"))) {
      $message = "<div class='alert alert-danger text-center'><strong>Year model must be a 4-digit number between 1900 and ".date("Y").".</strong></div>";
  } else {
      // 1) Check for duplicate serial number (exclude current asset)
      $sql_check = "SELECT 1 FROM tblequipments WHERE serialnumber = ? AND assetnumber != ?";
      if ($chk = mysqli_prepare($link, $sql_check)) {
          mysqli_stmt_bind_param($chk, "ss", $serialnumber, $currentAsset);
          mysqli_stmt_execute($chk);
          mysqli_stmt_store_result($chk);
          if (mysqli_stmt_num_rows($chk) > 0) {
              // Serial number already in use by another asset
              $message = "<div class='alert alert-warning text-center'><strong> Serial Number is already in use.</strong></div>";
              mysqli_stmt_close($chk);
          } else {
              mysqli_stmt_close($chk);
              // 2) Proceed with the update
              $sql = "UPDATE tblequipments
                      SET serialnumber = ?, type = ?, manufacturer = ?, yearmodel = ?, description = ?, branch = ?, department = ?, status = ?
                      WHERE assetnumber = ?";
              if ($stmt = mysqli_prepare($link, $sql)) {
                  mysqli_stmt_bind_param(
                      $stmt,
                      "sssssssss",
                      $serialnumber,
                      $type,
                      $manufacturer,
                      $yearmodel,
                      $description,
                      $branch,
                      $department,
                      $status,
                      $currentAsset
                  );
                  if (mysqli_stmt_execute($stmt)) {
                      // Log the update action in tblogs
                      $logSql = "INSERT INTO tblogs (datelog, timelog, module, action, performedto, performedby) VALUES (?, ?, ?, ?, ?, ?)";
                      if ($logStmt = mysqli_prepare($link, $logSql)) {
                          $date   = date("d/m/Y");
                          $time   = date("h:i:sa");
                          $action = "Update";
                          $module = "Equipments Management";
                          mysqli_stmt_bind_param(
                              $logStmt,
                              "ssssss",
                              $date,
                              $time,
                              $module,
                              $action,
                              $currentAsset,
                              $_SESSION['username']
                          );
                          mysqli_stmt_execute($logStmt);
                          mysqli_stmt_close($logStmt);
                      }

                      // Success modal
                      echo "<script>
                          document.addEventListener('DOMContentLoaded', function() {
                              let successModal = new bootstrap.Modal(document.getElementById('successModal'));
                              successModal.show();
                          });
                      </script>";
                  } else {
                      $message = "<div class='alert alert-danger text-center'><strong>Error updating record.</strong></div>";
                  }
                  mysqli_stmt_close($stmt);
              }
          }
      } else {
          // In case the check query itself fails
          $message = "<div class='alert alert-danger text-center'><strong>Unable to verify serial number uniqueness.</strong></div>";
      }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update Equipment - AU Technical Support Management System</title>
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
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card p-4 shadow">
        <h4 class="text-primary text-center">Update Equipment</h4>
        <?= $message; ?>
        <!-- Added id="updateForm" to link with confirmation modal -->
        <form id="updateForm" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Asset Number:</label>
              <input type="text" class="form-control" value="<?= $equipment['assetnumber'] ?? ''; ?>" disabled>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Serial Number:</label>
              <input type="text" name="txtserialnumber" class="form-control" value="<?= $equipment['serialnumber'] ?? ''; ?>" required>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Equipment Type:</label>
              <select name="cmbtype" class="form-select" required>
                <option value="Monitor" <?= ($equipment['type'] == 'Monitor') ? 'selected' : ''; ?>>Monitor</option>
                <option value="CPU" <?= ($equipment['type'] == 'CPU') ? 'selected' : ''; ?>>CPU</option>
                <option value="Keyboard" <?= ($equipment['type'] == 'Keyboard') ? 'selected' : ''; ?>>Keyboard</option>
                <option value="Mouse" <?= ($equipment['type'] == 'Mouse') ? 'selected' : ''; ?>>Mouse</option>
                <option value="AVR" <?= ($equipment['type'] == 'AVR') ? 'selected' : ''; ?>>AVR</option>
                <option value="MAC" <?= ($equipment['type'] == 'MAC') ? 'selected' : ''; ?>>MAC</option>
                <option value="Printer" <?= ($equipment['type'] == 'Printer') ? 'selected' : ''; ?>>Printer</option>
                <option value="Projector" <?= ($equipment['type'] == 'Projector') ? 'selected' : ''; ?>>Projector</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Manufacturer:</label>
              <input type="text" name="txtmanufacturer" class="form-control" value="<?= $equipment['manufacturer'] ?? ''; ?>" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Year Model:</label>
              <input type="number" name="txtyearmodel" class="form-control" value="<?= $equipment['yearmodel'] ?? ''; ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Branch:</label>
              <select name="cmbbranch" class="form-select" required>
                <option value="">--Select Branch--</option>
                <option value="AU Legarda/Main" <?= ($equipment['branch'] ?? '') == "AU Legarda/Main" ? "selected" : ""; ?>>Juan Sumulong Campus</option>
                <option value="AU Pasay" <?= ($equipment['branch'] ?? '') == "AU Pasay" ? "selected" : ""; ?>>Jose Abad Santos Campus</option>
                <option value="Arellano School of Law" <?= ($equipment['branch'] ?? '') == "Arellano School of Law" ? "selected" : ""; ?>>Arellano School of Law</option>
                <option value="AU Pasig" <?= ($equipment['branch'] ?? '') == "AU Pasig" ? "selected" : ""; ?>>Andres Bonifacio Campus</option>
                <option value="AU Malabon" <?= ($equipment['branch'] ?? '') == "AU Malabon" ? "selected" : ""; ?>>Jose Rizal Campus</option>
                <option value="AU Mandaluyong" <?= ($equipment['branch'] ?? '') == "AU Mandaluyong" ? "selected" : ""; ?>>Plaridel Campus</option>
                <option value="AU Pasay" <?= ($equipment['branch'] ?? '') == "AU Pasay" ? "selected" : ""; ?>>Apolinario Mabini</option>
                <option value="AU Malabon" <?= ($equipment['branch'] ?? '') == "AU Malabon" ? "selected" : ""; ?>>Elisa Esguerra Campus</option>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Department:</label>
                <select name="cmbdepartment" class="form-select" required>
                    <option value="">--Select Department--</option>
                    <option value="Institute of Accountancy" <?= ($equipment['department'] ?? '') == "Institute of Accountancy" ? "selected" : ""; ?>>Institute of Accountancy</option>
                    <option value="School of Business Administration" <?= ($equipment['department'] ?? '') == "School of Business Administration" ? "selected" : ""; ?>>School of Business Administration</option>
                    <option value="College of General Education and Liberal Arts" <?= ($equipment['department'] ?? '') == "College of General Education and Liberal Arts" ? "selected" : ""; ?>>College of General Education and Liberal Arts</option>
                    <option value="College of Nursing" <?= ($equipment['department'] ?? '') == "College of Nursing" ? "selected" : ""; ?>>College of Nursing</option>
                    <option value="School of Computer Studies" <?= ($equipment['department'] ?? '') == "School of Computer Studies" ? "selected" : ""; ?>>School of Computer Studies</option>
                    <option value="College of Criminal Justice Education" <?= ($equipment['department'] ?? '') == "College of Criminal Justice Education" ? "selected" : ""; ?>>College of Criminal Justice Education</option>
                    <option value="School of Education" <?= ($equipment['department'] ?? '') == "School of Education" ? "selected" : ""; ?>>School of Education</option>
                    <option value="College of Medical Laboratory Science" <?= ($equipment['department'] ?? '') == "College of Medical Laboratory Science" ? "selected" : ""; ?>>College of Medical Laboratory Science</option>
                    <option value="School of Midwifery" <?= ($equipment['department'] ?? '') == "School of Midwifery" ? "selected" : ""; ?>>School of Midwifery</option>
                    <option value="College of Pharmacy" <?= ($equipment['department'] ?? '') == "College of Pharmacy" ? "selected" : ""; ?>>College of Pharmacy</option>
                    <option value="College of Physical Therapy" <?= ($equipment['department'] ?? '') == "College of Physical Therapy" ? "selected" : ""; ?>>College of Physical Therapy</option>
                    <option value="College of Radiologic Technology" <?= ($equipment['department'] ?? '') == "College of Radiologic Technology" ? "selected" : ""; ?>>College of Radiologic Technology</option>
                    <option value="School of Psychology" <?= ($equipment['department'] ?? '') == "School of Psychology" ? "selected" : ""; ?>>School of Psychology</option>
                    <option value="School of Hospitality and Tourism Management" <?= ($equipment['department'] ?? '') == "School of Hospitality and Tourism Management" ? "selected" : ""; ?>>School of Hospitality and Tourism Management</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Status:</label>
              <div>
                <input type="radio" name="rbstatus" value="WORKING" <?= ($equipment['status'] == 'WORKING') ? 'checked' : ''; ?>> Working
                <input type="radio" name="rbstatus" value="ON-REPAIR" <?= ($equipment['status'] == 'ON-REPAIR') ? 'checked' : ''; ?>> On-repair
                <input type="radio" name="rbstatus" value="RETIRED" <?= ($equipment['status'] == 'RETIRED') ? 'checked' : ''; ?>> Retired
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Description:</label>
            <textarea name="txtdescription" class="form-control" rows="3" required><?= $equipment['description'] ?? ''; ?></textarea>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <!-- This button triggers the confirmation modal -->
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmUpdateModal">Update</button>
            <a href="equipment-management.php" class="btn btn-secondary">Cancel</a>
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
        <!-- This button submits the form with id "updateForm" -->
        <button type="submit" class="btn btn-success" form="updateForm" name="btnsubmit">Yes, Update</button>
      </div>
    </div>
  </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header">
        <h5 class="modal-title text-success">✅ Updating Account - Success</h5>
      </div>
      <div class="modal-body">
        <p>User account updated successfully!</p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-primary" onclick="window.location.href='equipment-management.php'">OK</button>
      </div>
    </div>
  </div>
</div>
</main>

<footer class="custom-footer text-center border-top">
  &copy; CopyRight 2025, Tanqui-on, Johnmer
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
