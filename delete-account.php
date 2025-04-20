<?php 
require_once "config.php";
session_start();

if(isset($_GET['username'])) {
    $username = trim($_GET['username']);

    // Prepare delete statement
    $sql = "DELETE FROM tblaccounts WHERE username = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "s", $username);
        if(mysqli_stmt_execute($stmt)){

            // Log the deletion
            $sql = "INSERT INTO tblogs (datelog, timelog, module, action, performedto, performedby) VALUES (?, ?, ?, ?, ?, ?)";
            if($stmt = mysqli_prepare($link, $sql)){
                $date = date("d/m/Y");
                $time = date("h:i:sa");
                $action = "Delete";
                $module = "Accounts Management";
                mysqli_stmt_bind_param($stmt, "ssssss", $date, $time, $module, $action, $username, $_SESSION['username']);
                mysqli_stmt_execute($stmt);
            }

            $_SESSION['delete_success'] = true;  // ✅ Set session flag for success modal
            header("location: accounts-management.php"); // ✅ Redirect back
            exit();
        }
    }
}
?>
