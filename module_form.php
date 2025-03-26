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
$module_id = '';
$module_name = '';
$module_leader = '';
$description = '';
$image = '';
$is_edit_mode = false;
$page_title = 'Add New Module';

// Check if we're in edit mode
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $module_id = $_GET['id'];
    $is_edit_mode = true;
    $page_title = 'Edit Module';
    
    // Get module data
    try {
        $stmt = $conn->prepare("SELECT * FROM Modules WHERE ModuleID = :id");
        $stmt->bindParam(':id', $module_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $module = $stmt->fetch(PDO::FETCH_ASSOC);
            $module_name = $module['ModuleName'];
            $module_leader = $module['ModuleLeaderID'];
            $description = $module['Description'];
            $image = $module['Image'];
        } else {
            // Module not found
            $_SESSION['error_message'] = "Module not found.";
            header("Location: index.php");
            exit();
        }
    } catch(PDOException $e) {
        die("Error fetching module data: " . $e->getMessage());
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $module_name = trim($_POST['module_name']);
    $module_leader = !empty($_POST['module_leader']) ? $_POST['module_leader'] : null;
    $description = trim($_POST['description']);
    
    // Initialize errors array
    $errors = [];
    
    // Validate inputs
    if (empty($module_name)) {
        $errors[] = "Module name is required.";
    }
    
    // Handle image upload if provided
    $image_path = $image; // Keep the existing image path by default
    
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $target_dir = "../uploads/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_filename = "module_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Check file type
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($file_extension, $allowed_types)) {
            $errors[] = "Only JPG, JPEG, PNG & GIF files are allowed.";
        }
        
        // Check file size (limit to 5MB)
        if ($_FILES["image"]["size"] > 5000000) {
            $errors[] = "File is too large. Maximum size is 5MB.";
        }
        
        // If no errors, upload the file
        if (empty($errors)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = $new_filename;
            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    }
    
    // If no errors, save data
    if (empty($errors)) {
        try {
            if ($is_edit_mode) {
                // Update existing module
                $stmt = $conn->prepare("
                    UPDATE Modules 
                    SET ModuleName = :name, 
                        ModuleLeaderID = :leader, 
                        Description = :description, 
                        Image = :image 
                    WHERE ModuleID = :id
                ");
                $stmt->bindParam(':id', $module_id);
            } else {
                // Create new module
                // If ModuleID is provided, use it; otherwise, let auto-increment handle it
                if (!empty($_POST['module_id'])) {
                    $module_id = $_POST['module_id'];
                    $stmt = $conn->prepare("
                        INSERT INTO Modules (ModuleID, ModuleName, ModuleLeaderID, Description, Image) 
                        VALUES (:id, :name, :leader, :description, :image)
                    ");
                    $stmt->bindParam(':id', $module_id);
                } else {
                    $stmt = $conn->prepare("
                        INSERT INTO Modules (ModuleName, ModuleLeaderID, Description, Image) 
                        VALUES (:name, :leader, :description, :image)
                    ");
                }
            }
            
            $stmt->bindParam(':name', $module_name);
            $stmt->bindParam(':leader', $module_leader);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':image', $image_path);
            $stmt->execute();
            
            // Redirect to dashboard with success message
            $_SESSION['success_message'] = $is_edit_mode ? "Module updated successfully." : "Module added successfully.";
            header("Location: index.php");
            exit();
        } catch(PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
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
            max-width: 800px;
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
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-control-file {
            padding: 5px 0;
        }
        
        textarea.form-control {
            min-height: 150px;
        }
        
        .error-message {
            color: #e74c3c;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #ffd5d5;
            border-radius: 4px;
        }
        
        .buttons-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .back-btn {
            padding: 10px 15px;
            background-color: #7f8c8d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
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
        
        .image-preview {
            margin-top: 10px;
            max-width: 300px;
        }
        
        .image-preview img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><?php echo $page_title; ?></h1>
            <div class="admin-user">
                <a href="index.php" class="back-btn">Return to Dashboard</a>
            </div>
        </div>
        
        <div class="form-container">
            <?php
            // Display error messages if any
            if (!empty($errors)) {
                echo '<div class="error-message">';
                foreach ($errors as $error) {
                    echo '<p>' . htmlspecialchars($error) . '</p>';
                }
                echo '</div>';
            }
            ?>
            
            <form method="POST" enctype="multipart/form-data">
                <?php if (!$is_edit_mode) { ?>
                <div class="form-group">
                    <label for="module_id">Module ID (Optional):</label>
                    <input type="number" id="module_id" name="module_id" class="form-control" value="<?php echo htmlspecialchars($module_id); ?>">
                    <small>Leave blank for auto-assignment</small>
                </div>
                <?php } ?>
                
                <div class="form-group">
                    <label for="module_name">Module Name:</label>
                    <input type="text" id="module_name" name="module_name" class="form-control" value="<?php echo htmlspecialchars($module_name); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="module_leader">Module Leader:</label>
                    <select id="module_leader" name="module_leader" class="form-control">
                        <option value="">Select Module Leader</option>
                        <?php
                        try {
                            $stmt = $conn->query("SELECT StaffID, Name FROM Staff ORDER BY Name");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $selected = ($row['StaffID'] == $module_leader) ? 'selected' : '';
                                echo "<option value='" . $row['StaffID'] . "' $selected>" . htmlspecialchars($row['Name']) . "</option>";
                            }
                        } catch(PDOException $e) {
                            echo "<option value=''>Error loading staff members</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" class="form-control"><?php echo htmlspecialchars($description); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Module Image:</label>
                    <input type="file" id="image" name="image" class="form-control-file">
                    <small>Recommended size: 800x400 pixels. Max file size: 5MB.</small>
                    
                    <?php if (!empty($image)) { ?>
                        <div class="image-preview">
                            <p>Current image:</p>
                            <img src="../uploads/<?php echo htmlspecialchars($image); ?>" alt="Module Image">
                        </div>
                    <?php } ?>
                </div>
                
                <div class="buttons-container">
                    <a href="admin_dashboard.php?section=modules-management" class="back-btn">Cancel</a>
                    <button type="submit" class="submit-btn"><a href="admin_dashboard.php?section=modules-management"></a><?php echo $is_edit_mode ? 'Update Module' : 'Add Module'; ?></button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>