<?php 
include('session-checker.php');
require_once "config.php";
/////////////////////////////////
// Process AJAX actions for "complete" and "approve"
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
  $ticket = trim($_POST['ticket']);

  // COMPLETE action – only TECHNICAL can mark as complete
  if ($_POST['action'] === "complete") {
      if ($_SESSION['usertype'] !== 'TECHNICAL') {
          echo json_encode(["success" => false, "message" => "Access denied."]);
          exit();
      }
      $dateCompleted = date("d/m/Y");
      // Update status to "FOR APPROVAL" when technical completes
      $sql = "UPDATE tbltickets SET status = 'FOR APPROVAL', datecompleted = ? WHERE ticketnumber = ?";
      if ($stmt = mysqli_prepare($link, $sql)) {
          mysqli_stmt_bind_param($stmt, "ss", $dateCompleted, $ticket);
          if (mysqli_stmt_execute($stmt)) {
              mysqli_stmt_close($stmt);
              
              // Log the "Complete" action in tblogs
              $sql_log = "INSERT INTO tblogs (datelog, timelog, module, action, performedto, performedby) VALUES (?, ?, ?, ?, ?, ?)";
              if ($stmt_log = mysqli_prepare($link, $sql_log)) {
                  $log_date = date("d/m/Y");
                  $log_time = date("h:i:sa");
                  $log_action = "Complete";
                  $module = "Ticket Management";
                  mysqli_stmt_bind_param($stmt_log, "ssssss", $log_date, $log_time, $module, $log_action, $ticket, $_SESSION['username']);
                  mysqli_stmt_execute($stmt_log);
                  mysqli_stmt_close($stmt_log);
              }
              
              echo json_encode(["success" => true]);
              exit();
          } else {
              echo json_encode(["success" => false, "message" => "Error updating ticket."]);
              exit();
          }
      } else {
          echo json_encode(["success" => false, "message" => "Error preparing statement."]);
          exit();
      }
  } elseif ($_POST['action'] === "approve") {
      // APPROVE action – only ADMINISTRATOR can approve tickets
      if ($_SESSION['usertype'] !== 'ADMINISTRATOR') {
          echo json_encode(["success" => false, "message" => "Access denied."]);
          exit();
      }
      $dateApproved = date("d/m/Y");
      $approvedBy = $_SESSION['username'];
      $sql = "UPDATE tbltickets SET status = 'CLOSED', dateapproved = ?, approvedby = ? WHERE ticketnumber = ?";
      if ($stmt = mysqli_prepare($link, $sql)) {
          mysqli_stmt_bind_param($stmt, "sss", $dateApproved, $approvedBy, $ticket);
          if (mysqli_stmt_execute($stmt)) {
              mysqli_stmt_close($stmt);
              
              // Log the "Approve" action in tblogs
              $sql_log = "INSERT INTO tblogs (datelog, timelog, module, action, performedto, performedby) VALUES (?, ?, ?, ?, ?, ?)";
              if ($stmt_log = mysqli_prepare($link, $sql_log)) {
                  $log_date = date("d/m/Y");
                  $log_time = date("h:i:sa");
                  $log_action = "Approve";
                  $module = "Ticket Management";
                  mysqli_stmt_bind_param($stmt_log, "ssssss", $log_date, $log_time, $module, $log_action, $ticket, $_SESSION['username']);
                  mysqli_stmt_execute($stmt_log);
                  mysqli_stmt_close($stmt_log);
              }
              
              echo json_encode(["success" => true]);
              exit();
          } else {
              echo json_encode(["success" => false, "message" => "Error updating ticket."]);
              exit();
          }
      } else {
          echo json_encode(["success" => false, "message" => "Error preparing statement."]);
          exit();
      }
  }
}


// Get user session data
$username = $_SESSION['username'];
$usertype = $_SESSION['usertype'];

$searchTerm = "";
if (isset($_POST['btnsearch']) && !empty(trim($_POST['txtsearch']))) {
    $searchTerm = "%" . mysqli_real_escape_string($link, $_POST['txtsearch']) . "%";
}

// Prepare query based on user type and search term
if ($searchTerm) {
    if ($usertype === 'TECHNICAL') {
        // TECHNICAL: Can only see tickets assigned to them
        $sql = "SELECT * FROM tbltickets WHERE assignedto = ? AND (ticketnumber LIKE ? OR problem LIKE ? OR status LIKE ?) ORDER BY ticketnumber DESC";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $username, $searchTerm, $searchTerm, $searchTerm);
    } elseif ($usertype === 'STAFF' || $usertype === 'USER') {
        // STAFF & USER: Can only see tickets they created
        $sql = "SELECT * FROM tbltickets WHERE createdby = ? AND (ticketnumber LIKE ? OR problem LIKE ? OR status LIKE ?) ORDER BY ticketnumber DESC";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $username, $searchTerm, $searchTerm, $searchTerm);
    } else {
        // ADMINISTRATOR & other roles: Can see all tickets
        $sql = "SELECT * FROM tbltickets WHERE (ticketnumber LIKE ? OR problem LIKE ? OR status LIKE ?) ORDER BY ticketnumber DESC";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $searchTerm, $searchTerm, $searchTerm);
    }
} else {
    // No search term, default query
    if ($usertype === 'TECHNICAL') {
        $sql = "SELECT * FROM tbltickets WHERE assignedto = ? ORDER BY ticketnumber DESC";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
    } elseif ($usertype === 'STAFF' || $usertype === 'USER') {
        $sql = "SELECT * FROM tbltickets WHERE createdby = ? ORDER BY ticketnumber DESC";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
    } else {
        $sql = "SELECT * FROM tbltickets ORDER BY ticketnumber DESC";
        $stmt = mysqli_prepare($link, $sql);
    }
}

// Execute the query
if (mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);
} else {
    $error = "<div class='alert alert-danger text-center'>Error executing query.</div>";
}


// Function to build the tickets table with action buttons per role.
function buildTable($result, $usertype) {
    if (mysqli_num_rows($result) > 0) {
      echo "<table class='table table-striped'>";
      echo '<thead class="table-primary">
              <tr>
                <th>Ticket Number</th>
                <th>Problem</th>
                <th>Date Created</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>';
      while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
          // Convert the datetime string to separate date and time parts

            // Normalize status value for comparison and styling
            $status = strtoupper(trim($row['status']));
            $badgeClass = '';
            switch($status) {
                case 'PENDING':
                    $badgeClass = 'bg-warning text-dark'; // Yellow badge
                    break;
                case 'ONGOING':
                    $badgeClass = 'bg-info text-dark';    // Light-blue badge
                    break;
                case 'FOR APPROVAL':
                    $badgeClass = 'bg-primary';           // Dark-blue badge
                    break;
                case 'CLOSED':
                    $badgeClass = 'bg-danger';           // Green badge
                    break;
                default:
                    $badgeClass = 'bg-secondary';
            }

          echo "<tr>";
          echo "<td>" . $row['ticketnumber'] . "</td>";
          echo "<td>" . $row['problem'] . "</td>";
          echo "<td>" . $row['datecreated']. "</td>";
          echo "<td><span class='badge $badgeClass'>" . htmlspecialchars($row['status']) . "</span></td>";
          echo "<td>";
            // Action buttons container
            echo "<div class='d-flex gap-2 flex-wrap justify-content-center'>";
            // Details button (always available)
            $normalizedStatus = str_replace('-', '', strtoupper(trim($row['status'])));

            if ($usertype === 'USER' || $usertype === 'STAFF'){
                        // Edit button
                        if (in_array($normalizedStatus, ['PENDING',])){
                        echo "<a href='update-ticket.php?ticketnumber=" . $row['ticketnumber'] . "' 
                          class='edit-icon' 
                          onmouseover='this.style.backgroundColor = \"green\"; this.style.color = \"white\";' 
                          onmouseout='this.style.backgroundColor = \"transparent\"; this.style.color = \"green\";' 
                          style='font-size: 1.2rem; text-decoration: none; color: green; padding: 5px; border-radius: 4px; transition: background-color 0.3s, color 0.3s;' 
                          title='Edit'>"
                          . "<i class='ri-pencil-line'></i>"
                          . "</a>";
                        }

                  }
            echo "<a href='#' 
                    class='details-icon' 
                    data-bs-toggle='modal' 
                    data-bs-target='#detailsModal' 
                    data-ticket='" . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . "'
                    onmouseover='this.style.backgroundColor = \"blue\"; this.style.color = \"white\";' 
                    onmouseout='this.style.backgroundColor = \"transparent\"; this.style.color = \"blue\";' 
                    style='font-size: 1.2rem; text-decoration: none; color: blue; padding: 5px; border-radius: 4px; transition: background-color 0.3s, color 0.3s;'
                    title='View Ticket Details'>
                      <i class='ri-file-list-3-line'></i>
                  </a>";
                  $normalizedStatus = str_replace('-', '', strtoupper(trim($row['status'])));
                  
                  

                  if (in_array($usertype, ['STAFF', 'USER']) && $normalizedStatus === "PENDING") {
                    echo "<a href='#' 
                            class='delete-icon' 
                            data-bs-toggle='modal' 
                            data-bs-target='#confirmDeleteModal' 
                            onclick='setDeleteTicket(\"" . $row['ticketnumber'] . "\")'
                            onmouseover='this.style.backgroundColor = \"red\"; this.style.color = \"white\";' 
                            onmouseout='this.style.backgroundColor = \"transparent\"; this.style.color = \"red\";' 
                            style='font-size: 1.2rem; text-decoration: none; color: red; padding: 5px; border-radius: 4px; transition: background-color 0.3s, color 0.3s;'
                            title='Delete Ticket'>
                                  <i class='ri-delete-bin-line'></i>
                          </a>";
                  }

                  // ADMINISTRATOR actions
                  if ($usertype === 'ADMINISTRATOR') {
                      // Assign button for PENDING or ONGOING tickets
                      if (in_array($normalizedStatus, ['PENDING', 'ONGOING'])) {
                          echo "<a href='assign-ticket.php?ticket=" . $row['ticketnumber'] . "' 
                                  class='assign-icon' 
                                  onmouseover='this.style.backgroundColor = \"orange\"; this.style.color = \"white\";' 
                                  onmouseout='this.style.backgroundColor = \"transparent\"; this.style.color = \"orange\";' 
                                  style='font-size: 1.2rem; text-decoration: none; color: orange; padding: 5px; border-radius: 4px; transition: background-color 0.3s, color 0.3s;'
                                  title='Assign Ticket'>
                                        <i class='ri-user-add-line'></i>
                                </a>";
                      }
                      // Approve button for FOR APPROVAL tickets
                      if ($normalizedStatus === "FOR APPROVAL") {
                          echo "<a href='#' 
                                  class='approve-icon' 
                                  data-ticket='" . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . "'
                                  onmouseover='this.style.backgroundColor = \"green\"; this.style.color = \"white\";' 
                                  onmouseout='this.style.backgroundColor = \"transparent\"; this.style.color = \"green\";' 
                                  style='font-size: 1.2rem; text-decoration: none; color: green; padding: 5px; border-radius: 4px; transition: background-color 0.3s, color 0.3s;'
                                  title='Approve Ticket'>
                                        <i class='ri-check-line'></i>
                                </a>";
                      }
                      // Delete button for CLOSED tickets
                      if ($normalizedStatus === "CLOSED") {
                          echo "<a href='#' 
                                  class='delete-icon' 
                                  data-bs-toggle='modal' 
                                  data-bs-target='#confirmDeleteModal' 
                                  onclick='setDeleteTicket(\"" . $row['ticketnumber'] . "\")'
                                  onmouseover='this.style.backgroundColor = \"red\"; this.style.color = \"white\";' 
                                  onmouseout='this.style.backgroundColor = \"transparent\"; this.style.color = \"red\";' 
                                  style='font-size: 1.2rem; text-decoration: none; color: red; padding: 5px; border-radius: 4px; transition: background-color 0.3s, color 0.3s;'
                                  title='Delete Ticket'>
                                        <i class='ri-delete-bin-line'></i>
                                </a>";
                      }
                  }
                  // TECHNICAL actions: if status is ONGOING, allow complete
                  if ($usertype === 'TECHNICAL' && $normalizedStatus === "ONGOING") {
                      echo "<a href='#' 
                              class='complete-icon' 
                              data-ticket='" . $row['ticketnumber'] . "'
                              onmouseover='this.style.backgroundColor = \"green\"; this.style.color = \"white\";' 
                              onmouseout='this.style.backgroundColor = \"transparent\"; this.style.color = \"green\";' 
                              style='font-size: 1.2rem; text-decoration: none; color: green; padding: 5px; border-radius: 4px; transition: background-color 0.3s, color 0.3s;'
                              title='Complete Ticket'>
                                    <i class='ri-check-line'></i>
                            </a>";
                  }
                  // STAFF/USER actions: if ticket is CLOSED, allow deletion
                  if (in_array($usertype, ['STAFF', 'USER']) && $normalizedStatus === "CLOSED") {
                      echo "<a href='#' 
                              class='delete-icon' 
                              data-bs-toggle='modal' 
                              data-bs-target='#confirmDeleteModal' 
                              onclick='setDeleteTicket(\"" . $row['ticketnumber'] . "\")'
                              onmouseover='this.style.backgroundColor = \"red\"; this.style.color = \"white\";' 
                              onmouseout='this.style.backgroundColor = \"transparent\"; this.style.color = \"red\";' 
                              style='font-size: 1.2rem; text-decoration: none; color: red; padding: 5px; border-radius: 4px; transition: background-color 0.3s, color 0.3s;'
                              title='Delete Ticket'>
                                    <i class='ri-delete-bin-line'></i>
                            </a>";
                  }
                  echo "</div>"; // end action container
                  echo "</td>";
                  echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<div class='alert alert-warning text-center'>No tickets found</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ticket Management - AU Technical Support Management System</title>
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
          <a href="equipment-management-view.php" class="btn btn-success">Equipment Management - (View Version)</a>
          <a href="tickets-management.php" class="btn btn-success">Ticket Management</a>
      <?php endif; ?>
      <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
  </div>
</nav>

<main div class="container mt-5">
<div class="container mt-5">
  <div class="row" >
    <!-- Main Ticket List -->
    <div class="col-md-8">
      <div class="card p-4">
        <div class="d-flex">
          <img src="picture/Ticket-logo.png" alt="Ticket Logo" class="ticket-logo" style="height: 70px;">
          <h4 class="text-primary ms-3">Ticket Management - List</h4>
        </div>
        <div class="d-flex justify-content-end my-3">
          <a href="create-ticket.php" class="btn btn-success me-2">
            <i class="ri-add-circle-line"></i> Add New Ticket
          </a>
        </div>
        <?php 
          if(isset($error)){
            echo $error;
          } else {
            buildTable($result, $usertype);
          }
        ?>
      </div>
    </div>
    <!-- Sidebar: User Info and Search -->
    <div class="col-md-4">
      <div class="card card-custom p-3 text-center">
        <img src="picture/acc_icon.png" alt="User Icon" class="user-icon">
        <h2 class="text-primary mt-5">Welcome, <?= $_SESSION['username'] ?></h2>
        <h5 class="text-muted">User Type: <?= $_SESSION['usertype'] ?></h5>
      </div>
      <div class="card card-custom p-3 mt-3">
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
          <div class="input-group">
            <input type="text" name="txtsearch" class="form-control" placeholder="Search ticket...">
            <button type="submit" name="btnsearch" class="btn btn-primary">Search</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold fs-4 text-primary">Ticket Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="ticketDetailsContent">
          <!-- Ticket details will be populated here -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- Delete Success Modal -->
<div class="modal fade" id="deleteSuccessModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header">
        <h5 class="modal-title text-success">✅ Ticket Deleted Successfully</h5>
      </div>
      <div class="modal-body">
        <p>The ticket has been deleted successfully!</p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-primary" onclick="window.location.href='tickets-management.php'">OK</button>
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
      <div class="modal-body">Are you sure you want to delete this ticket?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <a id="confirmDeleteBtn" href="#" class="btn btn-danger">Yes, Delete</a>
      </div>
    </div>
  </div>
</div>

<!-- Approve Modal (for Admin) -->
<div class="modal fade" id="approveModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
       <div class="modal-header">
          <h5 class="modal-title">Approve Ticket</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
       </div>
       <div class="modal-body">Are you sure you want to approve this ticket?</div>
       <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-success" id="confirmApproveBtn">Approve</button>
       </div>
    </div>
  </div>
</div>

<!-- Success Modal (for Approve Action) -->
<div class="modal fade" id="successModal1" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header">
         <h5 class="modal-title text-success">Ticket Approved Successfully</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
         <p>The ticket has been approved.</p>
      </div>
      <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-primary" onclick="window.location.href='tickets-management.php'">OK</button>
      </div>
    </div>
  </div>
</div>


<!-- Complete Modal (for Technical) -->
<div class="modal fade" id="completeModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
       <div class="modal-header">
          <h5 class="modal-title">Complete Ticket</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
       </div>
       <div class="modal-body">
         Are you sure you want to mark this ticket as complete?
       </div>
       <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-success" id="confirmCompleteBtn">Complete</button>
       </div>
    </div>
  </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal2" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header">
         <h5 class="modal-title text-success">Ticket Completed Successfully</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
         <p>The ticket has been marked as complete.</p>
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

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Set delete URL in Delete Confirmation Modal
function setDeleteTicket(ticketnumber) {
    document.getElementById("confirmDeleteBtn").href = "delete-ticket.php?ticketnumber=" + ticketnumber;
}

// Check for delete success flag and show Delete Success Modal if set.
document.addEventListener("DOMContentLoaded", function () {
    <?php if(isset($_SESSION['delete_success'])): ?>
        var deleteSuccessModal = new bootstrap.Modal(document.getElementById('deleteSuccessModal'));
        deleteSuccessModal.show();
        <?php unset($_SESSION['delete_success']); ?>
    <?php endif; ?>
});

// Function to display ticket details in the modal (unchanged)
function setDetails(ticketData) {
  try {
    var ticket = JSON.parse(ticketData);
    var allowedKeys = [
      "ticketnumber", "problem", "details", "status", "createdby", 
      "datecreated", "assignedto", "dateassigned", "datecompleted", 
      "approvedby", "dateapproved"
    ];
    var displayNames = {
      ticketnumber: "Ticket Number",
      problem: "Problem",
      details: "Details",
      status: "Status",
      createdby: "Created By",
      datecreated: "Date Created",
      assignedto: "Assigned To",
      dateassigned: "Date Assigned",
      datecompleted: "Date Completed",
      approvedby: "Approved By",
      dateapproved: "Date Approved"
    };
    var detailsHtml = '<table class="table table-bordered table-sm">';
    allowedKeys.forEach(function(key) {
      detailsHtml += '<tr><th>' + displayNames[key] + '</th><td>' + (ticket[key] || '') + '</td></tr>';
    });
    detailsHtml += '</table>';
    document.getElementById('ticketDetailsContent').innerHTML = detailsHtml;
  } catch (e) {
    console.error("Error parsing ticket data:", e);
    document.getElementById('ticketDetailsContent').innerHTML = "Error loading ticket details.";
  }
}

// Attach event listeners once the DOM is loaded (other event listeners remain unchanged)
document.addEventListener("DOMContentLoaded", function() {
  // Details button events
  const detailsButtons = document.querySelectorAll('.details-icon');
  detailsButtons.forEach(button => {
    button.addEventListener('click', function() {
      const ticketData = this.getAttribute('data-ticket');
      setDetails(ticketData);
    });
  });

  // Approve button events (for ADMINISTRATOR)
  const approveButtons = document.querySelectorAll('.approve-icon');
  approveButtons.forEach(function(btn) {
    btn.addEventListener("click", function(e) {
      e.preventDefault();
      const ticketData = JSON.parse(btn.getAttribute("data-ticket"));
      document.getElementById("confirmApproveBtn").setAttribute("data-ticket", ticketData.ticketnumber);
      var approveModalInstance = new bootstrap.Modal(document.getElementById("approveModal"));
      approveModalInstance.show();
    });
  });

  document.getElementById("confirmApproveBtn").addEventListener("click", function() {
    const ticket = this.getAttribute("data-ticket");
    const formData = new FormData();
    formData.append("ticket", ticket);
    formData.append("action", "approve");

    fetch("<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>", {
      method: "POST",
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Hide the approve modal
        var approveModalEl = document.getElementById("approveModal");
        var approveModalInstance = bootstrap.Modal.getInstance(approveModalEl);
        approveModalInstance.hide();

        // Show the success modal
        var successModalInstance = new bootstrap.Modal(document.getElementById("successModal1"));
        successModalInstance.show();
      } else {
        alert("Error approving ticket: " + data.message);
      }
    })
    .catch(error => console.error("Error:", error));
  });


  // FOR TECHNICAL MODAL _COMPLETE
  const completeButtons = document.querySelectorAll('.complete-icon');
  completeButtons.forEach(function(btn) {
    btn.addEventListener("click", function(e) {
      e.preventDefault();
      const ticket = btn.getAttribute("data-ticket");
      document.getElementById("confirmCompleteBtn").setAttribute("data-ticket", ticket);
      var completeModalInstance = new bootstrap.Modal(document.getElementById("completeModal"));
      completeModalInstance.show();
    });
  });

  document.getElementById("confirmCompleteBtn").addEventListener("click", function() {
    const ticket = this.getAttribute("data-ticket");
    const formData = new FormData();
    formData.append("ticket", ticket);
    formData.append("action", "complete");

    fetch("<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>", {
      method: "POST",
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Hide the complete modal
        var completeModalEl = document.getElementById("completeModal");
        var completeModalInstance = bootstrap.Modal.getInstance(completeModalEl);
        completeModalInstance.hide();

        // Show the success modal after the complete modal has been hidden
        var successModalInstance = new bootstrap.Modal(document.getElementById("successModal2"));
        successModalInstance.show();
      } else {
        alert("Error completing ticket: " + data.message);
      }
    })
    .catch(error => console.error("Error:", error));
  });
});


</script>
</body>
</html>
