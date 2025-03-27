<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: login.php");
    exit();
}

// Check if required parameters are set
if (!isset($_GET['type']) || !isset($_GET['id'])) {
    $_SESSION['error_message'] = "Missing required parameters.";
    header("Location: admin_dashboard.php?section=modules-management");
    exit();
}

$type = $_GET['type'];
$id = intval($_GET['id']);

// Validate ID is a number
if ($id <= 0) {
    $_SESSION['error_message'] = "Invalid ID parameter.";
    header("Location: admin_dashboard.php?section=modules-management");
    exit();
}

// Database connection
$host = "localhost";
$dbname = "student_course_hub";
$username = "root"; // Update with your database username
$password = ""; // Update with your database password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    $_SESSION['error_message'] = "Connection failed: " . $e->getMessage();
    header("Location: admin_dashboard.php?section=modules-management");
    exit();
}

// Process according to type
try {
    switch ($type) {
        case 'programme':
            // First remove any interested students (if the ON DELETE CASCADE is not working)
            $stmt = $conn->prepare("DELETE FROM InterestedStudents WHERE ProgrammeID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Then remove programme modules
            $stmt = $conn->prepare("DELETE FROM ProgrammeModules WHERE ProgrammeID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Finally delete the programme
            $stmt = $conn->prepare("DELETE FROM Programmes WHERE ProgrammeID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Programme successfully deleted.";
            } else {
                $_SESSION['error_message'] = "Failed to delete programme.";
            }
            break;
            
        case 'module':
            // First remove programme module assignments
            $stmt = $conn->prepare("DELETE FROM ProgrammeModules WHERE ModuleID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Now delete the module
            $stmt = $conn->prepare("DELETE FROM Modules WHERE ModuleID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Module successfully deleted.";
            } else {
                $_SESSION['error_message'] = "Failed to delete module.";
            }
            break;
            case 'staff':
                // Delete the staff member directly without checking assignments
                $stmt = $conn->prepare("DELETE FROM Staff WHERE StaffID = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);

                
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Staff member successfully deleted.";
                } else {
                    $_SESSION['error_message'] = "Failed to delete staff member.";
                }
                break;
    break;
            
        case 'student':
            // Delete the interested student
            $stmt = $conn->prepare("DELETE FROM InterestedStudents WHERE InterestID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Student successfully removed.";
            } else {
                $_SESSION['error_message'] = "Failed to remove student.";
            }
            break;
            
        default:
            $_SESSION['error_message'] = "Invalid action type: " . htmlspecialchars($type);
            break;
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
}

// Redirect back to the dashboard
header("Location: admin_dashboard.php?");
exit();
?>