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
$username = "root"; 
$password = ""; 

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if type is specified
if (!isset($_GET['type']) || ($_GET['type'] !== 'admin' && $_GET['type'] !== 'staff')) {
    header("Location: dashboard.php?section=password-management");
    exit();
}

$type = $_GET['type'];
$page_title = ($type === 'admin') ? "Add New Admin User" : "Add New Staff User";

// Process form submission
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
    $staff_id = isset($_POST["staff_id"]) ? trim($_POST["staff_id"]) : null;
    
    // Validate form data
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $message = "Please fill in all required fields.";
    } else if ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            if ($type === 'admin') {
                // Add new admin user
                $stmt = $conn->prepare("INSERT INTO admin_users (Username, Password, Name) VALUES (:username, :password, :name)");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':name', $name);
                $stmt->execute();
            } else {
                // Check if staff ID exists
                if (empty($staff_id)) {
                    $message = "Staff ID is required.";
                } else {
                    // Check if staff exists
                    $stmt = $conn->prepare("SELECT StaffID FROM Staff WHERE StaffID = :staff_id");
                    $stmt->bindParam(':staff_id', $staff_id);
                    $stmt->execute();
                    
                    if ($stmt->rowCount() === 0) {
                        $message = "Staff ID does not exist.";
                    } else {
                        // Add new staff user
                        $stmt = $conn->prepare("INSERT INTO staff_users (Username, Password, id) VALUES (:username, :password, :staff_id)");
                        $stmt->bindParam(':username', $username);
                        $stmt->bindParam(':password', $hashed_password);
                        $stmt->bindParam(':staff_id', $staff_id);
                        $stmt->execute();
                        
                    }
                }
            }
            
            if (empty($message)) {
                header("Location: admin_dashboard.php?section=password-management");
                exit();
            }
        } catch(PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Get staff list for dropdown if adding staff user
$staff_list = [];
if ($type === 'staff') {
    try {
        $stmt = $conn->query("SELECT StaffID, Name FROM Staff ORDER BY Name");
        $staff_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $message = "Error fetching staff list: " . $e->getMessage();
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
    </style>
</head>
<body>
    <div class="admin-container">
        <h1><?php echo $page_title; ?></h1>
        
        <div class="form-container">
            <?php if (!empty($message)): ?>
                <div class="error-message"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?type=" . $type); ?>">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                
                <?php if ($type === 'admin'): ?>
                <div class="form-group">
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <?php else: ?>
                <div class="form-group">
                    <label for="staff_id">Staff Member:</label>
                    <select id="staff_id" name="staff_id" class="form-control" required>
                        <option value="">-- Select Staff Member --</option>
                        <?php foreach ($staff_list as $staff): ?>
                        <option value="<?php echo $staff['StaffID']; ?>"><?php echo htmlspecialchars($staff['Name'] . ' (ID: ' . $staff['StaffID'] . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="buttons">
                    <a href="dashboard.php?section=password-management" class="secondary-btn">Cancel</a>
                    <button type="submit" class="primary-btn">Add User</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>