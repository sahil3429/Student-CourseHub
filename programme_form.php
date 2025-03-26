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
$programme_id = "";
$programme_name = "";
$level_id = "";
$programme_leader_id = "";
$description = "";
$image = "";
$message = "";
$is_edit_mode = false;

// Check if this is an edit operation
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $is_edit_mode = true;
    $programme_id = $_GET['id'];
    
    // Fetch programme details
    try {
        $stmt = $conn->prepare("SELECT * FROM Programmes WHERE ProgrammeID = :id");
        $stmt->bindParam(':id', $programme_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $programme = $stmt->fetch(PDO::FETCH_ASSOC);
            $programme_name = $programme['ProgrammeName'];
            $level_id = $programme['LevelID'];
            $programme_leader_id = $programme['ProgrammeLeaderID'];
            $description = $programme['Description'];
            $image = $programme['Image'];
        } else {
            $message = "Programme not found.";
        }
    } catch(PDOException $e) {
        $message = "Error fetching programme: " . $e->getMessage();
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $programme_name = $_POST['programme_name'];
    $level_id = $_POST['level_id'];
    $programme_leader_id = $_POST['programme_leader_id'];
    $description = $_POST['description'];
    
    // Handle image upload
    $image_path = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = "../uploads/programmes/";
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $temp_name = $_FILES['image']['tmp_name'];
        $filename = basename($_FILES['image']['name']);
        $file_extension = pathinfo($filename, PATHINFO_EXTENSION);
        $new_filename = uniqid() . "." . $file_extension;
        $image_path = "uploads/programmes/" . $new_filename;
        
        // Move uploaded file
        if (move_uploaded_file($temp_name, "../" . $image_path)) {
            // If editing and new image is uploaded, use new image
            if ($is_edit_mode) {
                // Delete old image if exists
                if (!empty($image) && file_exists("../" . $image)) {
                    unlink("../" . $image);
                }
            }
        } else {
            $message = "Failed to upload image.";
        }
    } else if ($is_edit_mode) {
        // If editing and no new image is uploaded, keep existing image
        $image_path = $image;
    }
    
    try {
        if ($is_edit_mode) {
            // Update existing programme
            $stmt = $conn->prepare("
                UPDATE Programmes 
                SET ProgrammeName = :name, 
                    LevelID = :level_id, 
                    ProgrammeLeaderID = :leader_id, 
                    Description = :description" . 
                    ($image_path ? ", Image = :image" : "") . "
                WHERE ProgrammeID = :id
            ");
            $stmt->bindParam(':id', $programme_id);
            $stmt->bindParam(':name', $programme_name);
            $stmt->bindParam(':level_id', $level_id);
            $stmt->bindParam(':leader_id', $programme_leader_id);
            $stmt->bindParam(':description', $description);
            if ($image_path) {
                $stmt->bindParam(':image', $image_path);
            }
            $stmt->execute();
            
            $message = "Programme updated successfully.";
        } else {
            // Insert new programme
            $stmt = $conn->prepare("
                INSERT INTO Programmes (ProgrammeName, LevelID, ProgrammeLeaderID, Description, Image)
                VALUES (:name, :level_id, :leader_id, :description, :image)
            ");
            $stmt->bindParam(':name', $programme_name);
            $stmt->bindParam(':level_id', $level_id);
            $stmt->bindParam(':leader_id', $programme_leader_id);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':image', $image_path);
            $stmt->execute();
            
            $message = "Programme added successfully.";
        }
        
        // Redirect to admin dashboard after 2 seconds
        header("refresh:2;url=admin_dashboard.php");
    } catch(PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Fetch levels for dropdown
try {
    $stmt = $conn->query("SELECT LevelID, LevelName FROM Levels ORDER BY LevelID");
    $levels = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $message = "Error fetching levels: " . $e->getMessage();
}

// Fetch staff for dropdown
try {
    $stmt = $conn->query("SELECT StaffID, Name FROM Staff ORDER BY Name");
    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $message = "Error fetching staff: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit_mode ? "Edit" : "Add"; ?> Programme - Admin Dashboard</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .admin-container {
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .admin-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .admin-header h1 {
            margin: 0;
            color: #333;
        }
        
        .form-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
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
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .primary-btn, .cancel-btn {
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            border: none;
        }
        
        .primary-btn {
            background-color: #3498db;
            color: white;
        }
        
        .cancel-btn {
            background-color: #e74c3c;
            color: white;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><?php echo $is_edit_mode ? "Edit" : "Add"; ?> Programme</h1>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, "successfully") !== false ? "success" : "error"; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . ($is_edit_mode ? "?id=$programme_id" : "")); ?>" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="programme_name">Programme Name</label>
                    <input type="text" id="programme_name" name="programme_name" class="form-control" value="<?php echo htmlspecialchars($programme_name); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="level_id">Level</label>
                    <select id="level_id" name="level_id" class="form-control" required>
                        <option value="">Select Level</option>
                        <?php foreach ($levels as $level): ?>
                            <option value="<?php echo $level['LevelID']; ?>" <?php echo $level_id == $level['LevelID'] ? "selected" : ""; ?>>
                                <?php echo htmlspecialchars($level['LevelName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="programme_leader_id">Programme Leader</label>
                    <select id="programme_leader_id" name="programme_leader_id" class="form-control" required>
                        <option value="">Select Programme Leader</option>
                        <?php foreach ($staff as $staff_member): ?>
                            <option value="<?php echo $staff_member['StaffID']; ?>" <?php echo $programme_leader_id == $staff_member['StaffID'] ? "selected" : ""; ?>>
                                <?php echo htmlspecialchars($staff_member['Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control"><?php echo htmlspecialchars($description); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Programme Image</label>
                    <input type="file" id="image" name="image" class="form-control" accept="image/*">
                    <?php if (!empty($image)): ?>
                        <p>Current image: <?php echo basename($image); ?></p>
                        <img src="../<?php echo htmlspecialchars($image); ?>" alt="Current image" style="max-width: 200px; max-height: 200px; margin-top: 10px;">
                    <?php endif; ?>
                    <img id="preview" class="preview-image" alt="Image preview">
                </div>
                
                <div class="form-actions">
                    <a href="admin_dashboard.php" class="cancel-btn">Cancel</a>
                    <button type="submit" class="primary-btn"><?php echo $is_edit_mode ? "Update" : "Add"; ?> Programme</button>
                </div>
            </form>
        </div>
    </div>
    
   
</body>
</html>