<?php 
include('session-checker.php');
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
  <title>User Dashboard - AU Technical Support Management System</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.css">
  <style>
    /* Flexbox layout to push footer to the bottom */
    html, body {
      height: 100%;
      margin: 0;
      display: flex;
      flex-direction: column;
    }
    main {
      flex: 1;
    }
    
    /* Footer styling: gray background, small padding and font */
    .custom-footer {
      background-color:rgb(69, 96, 229); /* gray */
      color: #fff;
      padding: 10px 0;
      font-size: 0.8rem;
    }
    
    .summary-card {
       border-radius: 10px;
       overflow: hidden;
    }
    .summary-header {
       padding: 10px 15px;
       font-weight: bold;
       font-size: 1.1rem;
       display: flex;
       align-items: center;
    }
    .summary-body {
       padding: 20px;
       text-align: center;
    }
    .summary-number1 {
       display: inline-block;
       background: lightgreen;
       padding: 15px 25px;
       border-radius: 50px;
       font-size: 2.5rem;
       font-weight: bold;
       box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .summary-number2 {
       display: inline-block;
       background: rgb(254, 70, 70);
       padding: 15px 25px;
       border-radius: 50px;
       font-size: 2.5rem;
       font-weight: bold;
       box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
  </style>  
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

<!-- Main Content -->
<main class="container mt-5">
  <div class="row" style="margin-top: 50px;">
    <!-- Dashboard Summary -->
    <div class="col-md-8">
      <div class="card p-4 border-0 shadow-sm">
        <h4 class="text-primary">Dashboard Overview</h4>
        <p class="text-muted">Quickly access system management and check the status of your records.</p>
        <div class="row">
          <?php 
          // Adjust ticket queries based on user type
          if($usertype == 'ADMINISTRATOR'){
              $queryTotal   = "SELECT COUNT(*) AS total FROM tbltickets";
              $queryPending = "SELECT COUNT(*) AS pending FROM tbltickets WHERE status = 'Pending'";
              $pendingLabel = "Pending Tickets";
              $pendingColor = "bg-warning";
          } elseif($usertype == 'TECHNICAL'){
              $queryTotal   = "SELECT COUNT(*) AS total FROM tbltickets WHERE assignedto = '$username'";
              $queryPending = "SELECT COUNT(*) AS pending FROM tbltickets WHERE assignedto = '$username' AND status = 'Ongoing'";
              $pendingLabel = "Ongoing Tickets";
              $pendingColor = "bg-warning";
          } else {
              // For regular user, only count their created tickets
              $queryTotal   = "SELECT COUNT(*) AS total FROM tbltickets WHERE createdby = '$username'";
              $queryPending = "SELECT COUNT(*) AS pending FROM tbltickets WHERE createdby = '$username' AND status = 'Pending'";
              $pendingLabel = "Pending Tickets";
              $pendingColor = "bg-warning";
          }
          $resultTotal   = mysqli_query($link, $queryTotal);
          $dataTotal     = mysqli_fetch_assoc($resultTotal);
          $resultPending = mysqli_query($link, $queryPending);
          $dataPending   = mysqli_fetch_assoc($resultPending);
          ?>
          <!-- Total Tickets -->
          <div class="col-md-6 mb-3">
            <div class="card summary-card">
              <div class="summary-header bg-info text-white">
                <i class="ri-list-check-2-line me-2"></i> Total Tickets
              </div>
              <div class="summary-body">
                <span class="summary-number1"><?php echo $dataTotal['total']; ?></span>
              </div>
            </div>
          </div>

          <!-- Pending / Ongoing Tickets -->
          <div class="col-md-6 mb-3">
            <div class="card summary-card">
              <div class="summary-header <?php echo $pendingColor; ?> text-white">
                <i class="ri-time-line me-2"></i> <?php echo $pendingLabel; ?>
              </div>
              <div class="summary-body">
                <span class="summary-number2"><?php echo $dataPending['pending']; ?></span>
              </div>
            </div>
          </div>
        </div>

        <?php if($usertype == 'ADMINISTRATOR' || $usertype == 'TECHNICAL'): ?>
        <div class="row mt-4">
          <?php if($usertype == 'ADMINISTRATOR'): ?>
          <!-- Total Accounts -->
          <div class="col-md-6 mb-3">
            <div class="card summary-card">
              <div class="summary-header bg-primary text-white">
                <i class="ri-user-line me-2"></i> Total Accounts
              </div>
              <div class="summary-body">
                <?php 
                  $queryAccounts = "SELECT COUNT(*) AS totalAccounts FROM tblaccounts";
                  $resultAccounts = mysqli_query($link, $queryAccounts);
                  $dataAccounts = mysqli_fetch_assoc($resultAccounts);
                  echo "<span class='summary-number1'>" . $dataAccounts['totalAccounts'] . "</span>";
                ?>
              </div>
            </div>
          </div>
          <?php elseif($usertype == 'TECHNICAL'): ?>
          <!-- Centered Equipment Card -->
          <div class="col-md-6 offset-md-3 mb-3">
            <div class="card summary-card">
              <div class="summary-header bg-primary text-white">
                <i class="ri-device-line me-2"></i> Total Equipments
              </div>
              <div class="summary-body">
                <?php 
                  $queryEquip = "SELECT COUNT(*) AS totalEquip FROM tblequipments";
                  $resultEquip = mysqli_query($link, $queryEquip);
                  $dataEquip = mysqli_fetch_assoc($resultEquip);
                  echo "<span class='summary-number1'>" . $dataEquip['totalEquip'] . "</span>";
                ?>
              </div>
            </div>
          </div>
          <?php endif; ?>
          <?php if($usertype == 'ADMINISTRATOR'): ?>
          <!-- Total Equipments for Admin -->
          <div class="col-md-6 mb-3">
            <div class="card summary-card">
              <div class="summary-header bg-info text-white">
                <i class="ri-device-line me-2"></i> Total Equipments
              </div>
              <div class="summary-body">
                <?php 
                  $queryEquip = "SELECT COUNT(*) AS totalEquip FROM tblequipments";
                  $resultEquip = mysqli_query($link, $queryEquip);
                  $dataEquip = mysqli_fetch_assoc($resultEquip);
                  echo "<span class='summary-number1'>" . $dataEquip['totalEquip'] . "</span>";
                ?>
              </div>
            </div>
          </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="text-center mt-4">
          <a href="tickets-management.php" class="btn btn-primary btn-lg">Go to Ticket Management</a>
        </div>
      </div>
    </div>

    <!-- User Info Card -->
    <div class="col-md-4">
      <div class="card card-custom p-3 text-center">
        <img src="picture/acc_icon.png" alt="User Icon" class="user-icon">
        <h2 class="text-primary mt-5">Welcome, <?= $username ?></h2>
        <h5 class="text-muted">User Type: <?= $_SESSION['usertype'] ?></h5>
      </div>
    </div>
  </div>
</main>

<!-- Footer -->
<footer class="custom-footer text-center border-top">
  &copy; CopyRight 2025, Tanqui-on, Johnmer
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
