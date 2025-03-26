<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: ../adminlogin.php");
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

// Initialize variables
$staffID = "";
$name = "";
$isEditing = false;
$formTitle = "Add New Staff Member";
$successMessage = "";
$errorMessage = "";

// Check if we're editing an existing staff member
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $staffID = $_GET['id'];
    $isEditing = true;
    $formTitle = "Edit Staff Member";
    
    // Fetch staff details
    try {
        $stmt = $conn->prepare("SELECT * FROM Staff WHERE StaffID = :staffID");
        $stmt->bindParam(':staffID', $staffID);
        $stmt->execute();
        
        if ($staff = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $name = $staff['Name'];
        } else {
            $errorMessage = "Staff member not found.";
        }
    } catch(PDOException $e) {
        $errorMessage = "Error fetching staff details: " . $e->getMessage();
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = trim($_POST['name']);
    
    if ($isEditing) {
        // We already have the staffID from the URL
    } else {
        // Get the staff ID for a new staff member
        $staffID = isset($_POST['staffid']) ? trim($_POST['staffid']) : "";
    }
    
    // Basic validation
    if (empty($name)) {
        $errorMessage = "Staff name is required.";
    } elseif (!$isEditing && empty($staffID)) {
        $errorMessage = "Staff ID is required.";
    }
    elseif ($staffID <=0) {
        $errorMessage = "Invalid Staff ID.";
    }
    else {
        try {
            if ($isEditing) {
                // Update existing staff
                $stmt = $conn->prepare("UPDATE Staff SET Name = :name WHERE StaffID = :staffID");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':staffID', $staffID);
                $stmt->execute();
                $successMessage = "Staff member updated successfully.";
            } else {
                // Check if the Staff ID already exists
                $checkStmt = $conn->prepare("SELECT COUNT(*) FROM Staff WHERE StaffID = :staffID");
                $checkStmt->bindParam(':staffID', $staffID);
                $checkStmt->execute();
                
                if ($checkStmt->fetchColumn() > 0) {
                    $errorMessage = "Staff ID already exists. Please use a different ID.";
                } else {
                    // Add new staff with specified ID
                    $stmt = $conn->prepare("INSERT INTO Staff (StaffID, Name) VALUES (:staffID, :name)");
                    $stmt->bindParam(':staffID', $staffID);
                    $stmt->bindParam(':name', $name);
                    $stmt->execute();
                    $successMessage = "Staff member added successfully.";
                    
                    // Clear form after successful submission
                    $staffID = "";
                    $name = "";
                }
            }
        } catch(PDOException $e) {
            $errorMessage = "Error saving staff member: " . $e->getMessage();
        }
    }
}

// Fetch staff members to show what's available
try {
    $stmt = $conn->query("SELECT StaffID, Name FROM Staff ORDER BY Name");
    $allStaff = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $errorMessage = "Error fetching staff list: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $formTitle; ?> - University Course Hub</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .admin-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .form-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .form-title {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input[type="text"],
        .form-group input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .submit-btn {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .cancel-btn {
            padding: 10px 20px;
            background-color: #95a5a6;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        
        .success-message {
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .error-message {
            padding: 10px;
            background-color: #f8d7da;
            color: #721c24;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .staff-list {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .staff-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .staff-table th, .staff-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .staff-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .staff-table tr:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><?php echo $formTitle; ?></h1>
            <a href="admin_dashboard.php" class="cancel-btn">Back to Dashboard</a>
        </div>
        
        <div class="form-container">
            <?php if (!empty($successMessage)): ?>
                <div class="success-message">
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="error-message">
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . ($isEditing ? "?id=$staffID" : "")); ?>">
                <?php if (!$isEditing): ?>
                <div class="form-group">
                    <label for="staffid">Staff ID:</label>
                    <input type="number" id="staffid" name="staffid" value="<?php echo htmlspecialchars($staffID); ?>" required>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Staff Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="submit-btn"><?php echo $isEditing ? 'Update Staff' : 'Add Staff'; ?></button>
                    <a href="index.php" class="cancel-btn">Cancel</a>
                </div>
            </form>
        </div>
        
        <div class="staff-list">
            <h2>Current Staff Members</h2>
            <?php if (!empty($allStaff)): ?>
                <table class="staff-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allStaff as $staff): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($staff['StaffID']); ?></td>
                                <td><?php echo htmlspecialchars($staff['Name']); ?></td>
                                <td>
                                    <a href="staff_form.php?id=<?php echo $staff['StaffID']; ?>" class="edit-btn">Edit</a>
                                    <a href="delete.php?type=staff&id=<?php echo $staff['StaffID']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this staff member?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No staff members found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>