<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: ../adminlogin.php");
    exit();
}

// Check if type and id parameters are set
if (!isset($_GET['type']) || !isset($_GET['id'])) {
    $_SESSION['error_message'] = "Missing required parameters";
    header("Location: index.php?section=password-management");
    exit();
}

$type = $_GET['type'];
$id = $_GET['id'];

// Validate type parameter
if ($type !== 'admin' && $type !== 'staff') {
    $_SESSION['error_message'] = "Invalid user type";
    header("Location: index.php?section=password-management");
    exit();
}

// Prevent admins from deleting themselves
if ($type === 'admin' && $_SESSION['admin_id'] == $id) {
    $_SESSION['error_message'] = "You cannot delete your own account";
    header("Location: index.php?section=password-management");
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

    // Delete the user based on type
    if ($type === 'admin') {
        // Check if this is the last admin account
        $stmt = $conn->query("SELECT COUNT(*) FROM admin_users");
        $count = $stmt->fetchColumn();
        
        if ($count <= 1) {
            $_SESSION['error_message'] = "Cannot delete the last admin account";
            header("Location: index.php?section=password-management");
            exit();
        }
        
        $stmt = $conn->prepare("DELETE FROM admin_users WHERE ID = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    } else {
        // Delete staff user
        $stmt = $conn->prepare("DELETE FROM staff_users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    $_SESSION['success_message'] = ucfirst($type) . " user deleted successfully";
} catch(PDOException $e) {
    $_SESSION['error_message'] = "Error deleting user: " . $e->getMessage();
}

// Redirect back to password management
header("Location: admin_dashboard.php?section=password-management");
exit();
?>