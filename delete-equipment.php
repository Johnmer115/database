<?php 
require_once "config.php";
session_start();

if (isset($_GET['assetnumber'])) {
    $assetnumber = trim($_GET['assetnumber']);

    // Prepare delete statement for equipment
    $sql = "DELETE FROM tblequipments WHERE assetnumber = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $assetnumber);
        if (mysqli_stmt_execute($stmt)) {

            // Log the deletion action in tblogs
            $sql = "INSERT INTO tblogs (datelog, timelog, module, action, performedto, performedby) VALUES (?, ?, ?, ?, ?, ?)";
            if ($stmt = mysqli_prepare($link, $sql)) {
                $date = date("d/m/Y");
                $time = date("h:i:sa");
                $action = "Delete";
                $module = "Equipments Management";
                mysqli_stmt_bind_param($stmt, "ssssss", $date, $time, $module, $action, $assetnumber, $_SESSION['username']);
                mysqli_stmt_execute($stmt);
            }

            $_SESSION['delete_success'] = true;  // Set session flag for success modal
            header("location: equipment-management.php"); // Redirect back to equipment management page
            exit();
        }
    }
}
?>