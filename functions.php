<?php
include("database_connection.php");


//require_once 'database_connection.php';

// Get all staff members
function getAllStaff($pdo) {
    $stmt = $pdo->query("SELECT * FROM Staff ORDER BY StaffID");
    return $stmt->fetchAll();
}

 
// Get single staff member by ID
function getStaffById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM Staff WHERE StaffID = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Get modules led by a staff member
function getStaffModules($pdo, $staffId) {
    $stmt = $pdo->prepare("SELECT * FROM Modules WHERE ModuleLeaderID = ?");
    $stmt->execute([$staffId]);
    return $stmt->fetchAll();
}

// Get programmes led by a staff member
function getStaffProgrammes($pdo, $staffId) {
    $stmt = $pdo->prepare("SELECT * FROM Programmes WHERE ProgrammeLeaderID = ?");
    $stmt->execute([$staffId]);
    return $stmt->fetchAll();
}

?>