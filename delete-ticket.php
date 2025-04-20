<?php 
require_once "config.php";
session_start();

if (isset($_GET['ticketnumber'])) {
    $ticketnumber = trim($_GET['ticketnumber']);

    // Prepare delete statement for ticket
    $sql = "DELETE FROM tbltickets WHERE ticketnumber = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $ticketnumber);
        if (mysqli_stmt_execute($stmt)) {

            // Log the deletion action in tblogs using a different statement variable
            $sql_log = "INSERT INTO tblogs (datelog, timelog, module, action, performedto, performedby) VALUES (?, ?, ?, ?, ?, ?)";
            if ($stmt_log = mysqli_prepare($link, $sql_log)) {
                $date = date("d/m/Y");
                $time = date("h:i:sa");
                $action = "Delete";
                $module = "Ticket Management";
                mysqli_stmt_bind_param($stmt_log, "ssssss", $date, $time, $module, $action, $ticketnumber, $_SESSION['username']);
                mysqli_stmt_execute($stmt_log);
                mysqli_stmt_close($stmt_log);
            }

            $_SESSION['delete_success'] = true;  // Set session flag for success modal
            header("Location: tickets-management.php"); // Redirect back to tickets management page
            exit();
        }
        mysqli_stmt_close($stmt);
    }
}
?>
