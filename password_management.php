<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: ../login.php");
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
    die("Connection failed: " . $e->getMessage());
}

// Check if type and id are specified
if (!isset($_GET['type']) || ($_GET['type'] !== 'admin' && $_GET['type'] !== 'staff') || !isset($_GET['id'])) {
    header("Location: dashboard.php?section=password-management");
    exit();
}

$type = $_GET['type'];
$id = $_GET['id'];
$user_info = null;

// Get user information
try {
    if ($type === 'admin') {
        $stmt = $conn->prepare("SELECT ID, Username, Name FROM admin_users WHERE ID = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user_info = $stmt->fetch(PDO::FETCH_ASSOC);
        $page_title = "Change Password for Admin: " . $user_info['Username'];
    } else {
        $stmt = $conn->prepare("SELECT su.StaffUserID, su.Username, s.Name 
                              FROM staff_users su
                              JOIN Staff s ON su.StaffID = s.StaffID
                              WHERE su.StaffUserID = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user_info = $stmt->fetch(PDO::FETCH_ASSOC);
        $page_title = "Change Password for Staff: " . $user_info['Username'];
    }
    
    if (!$user_info) {
        header("Location: admin_dashboard.php?section=password-management");
        exit();
    }
} catch(PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}

// Process form submission
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = trim($_POST["new_password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    
    // Validate form data
    if (empty($new_password) || empty($confirm_password)) {
        $message = "Please fill in all required fields.";
    } else if ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        // Hash the password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        try {
            if ($type === 'admin') {
                // Update admin password
                $stmt = $conn->prepare("UPDATE admin_users SET Password = :password WHERE ID = :id");
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
            } else {
                // Update staff password
                $stmt = $conn->prepare("UPDATE staff_users SET Password = :password WHERE StaffUserID = :id");
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
            }
            
            $message = "Password updated successfully!";
            header("Location: dashboard.php?section=password-management");
            exit();
        } catch(PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - University Course Hub</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .error-message {
            color: #e74c3c;
            margin-bottom: 20px;
        }
        
        .success-message {
            color: #2ecc71;
            margin-bottom: 20px;
        }
        
        .user-info {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1><?php echo $page_title; ?></h1>
        
        <div class="form-container">
            <div class="user-info">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user_info['Username']); ?></p>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user_info['Name']); ?></p>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="<?php echo (strpos($message, "successfully") !== false) ? "success-message" : "error-message"; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?type=" . $type . "&id=" . $id); ?>">
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                
                <div class="buttons">
                    <a href="dashboard.php?section=password-management" class="secondary-btn">Cancel</a>
                    <button type="submit" class="primary-btn">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>