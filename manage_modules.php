<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: ../adminlogin.php");
    exit();
}

// Check if programme ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

// Get programme ID
$programmeID = $_GET['id'];

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

// Fetch programme details
try {
    $stmt = $conn->prepare("
        SELECT p.ProgrammeName, l.LevelName, s.Name as LeaderName 
        FROM Programmes p
        LEFT JOIN Levels l ON p.LevelID = l.LevelID
        LEFT JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID
        WHERE p.ProgrammeID = :programmeID
    ");
    $stmt->bindParam(':programmeID', $programmeID);
    $stmt->execute();
    $programme = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$programme) {
        header("Location: admin_dashboard.php");
        exit();
    }
} catch(PDOException $e) {
    die("Error fetching programme details: " . $e->getMessage());
}

// Handle adding a module to the programme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_module'])) {
    $moduleID = $_POST['module_id'];
    $year = $_POST['year'];
    
    try {
        // Check if the module is already in the programme
        $check_stmt = $conn->prepare("
            SELECT COUNT(*) FROM ProgrammeModules 
            WHERE ProgrammeID = :programmeID AND ModuleID = :moduleID
        ");
        $check_stmt->bindParam(':programmeID', $programmeID);
        $check_stmt->bindParam(':moduleID', $moduleID);
        $check_stmt->execute();
        
        if ($check_stmt->fetchColumn() > 0) {
            $error_message = "This module is already part of the programme.";
        } else {
            // Add the module to the programme
            $insert_stmt = $conn->prepare("
                INSERT INTO ProgrammeModules (ProgrammeID, ModuleID, Year) 
                VALUES (:programmeID, :moduleID, :year)
            ");
            $insert_stmt->bindParam(':programmeID', $programmeID);
            $insert_stmt->bindParam(':moduleID', $moduleID);
            $insert_stmt->bindParam(':year', $year);
            $insert_stmt->execute();
            
            $success_message = "Module added to the programme successfully.";
        }
    } catch(PDOException $e) {
        $error_message = "Error adding module: " . $e->getMessage();
    }
}

// Handle removing a module from the programme
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['module_id'])) {
    $moduleID = $_GET['module_id'];
    
    try {
        $delete_stmt = $conn->prepare("
            DELETE FROM ProgrammeModules 
            WHERE ProgrammeID = :programmeID AND ModuleID = :moduleID
        ");
        $delete_stmt->bindParam(':programmeID', $programmeID);
        $delete_stmt->bindParam(':moduleID', $moduleID);
        $delete_stmt->execute();
        
        $success_message = "Module removed from the programme successfully.";
        
        // Redirect to remove the GET parameters
        header("Location: manage_modules.php?id=$programmeID");
        exit();
    } catch(PDOException $e) {
        $error_message = "Error removing module: " . $e->getMessage();
    }
}

// Fetch modules in the programme
try {
    $stmt = $conn->prepare("
        SELECT pm.Year, m.ModuleID, m.ModuleName, s.Name as LeaderName
        FROM ProgrammeModules pm
        JOIN Modules m ON pm.ModuleID = m.ModuleID
        LEFT JOIN Staff s ON m.ModuleLeaderID = s.StaffID
        WHERE pm.ProgrammeID = :programmeID
        ORDER BY pm.Year, m.ModuleName
    ");
    $stmt->bindParam(':programmeID', $programmeID);
    $stmt->execute();
    $programme_modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error fetching programme modules: " . $e->getMessage());
}

// Fetch all available modules (not already in the programme)
try {
    $stmt = $conn->prepare("
        SELECT m.ModuleID, m.ModuleName, s.Name as LeaderName
        FROM Modules m
        LEFT JOIN Staff s ON m.ModuleLeaderID = s.StaffID
        WHERE m.ModuleID NOT IN (
            SELECT ModuleID FROM ProgrammeModules WHERE ProgrammeID = :programmeID
        )
        ORDER BY m.ModuleName
    ");
    $stmt->bindParam(':programmeID', $programmeID);
    $stmt->execute();
    $available_modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error fetching available modules: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Modules - <?php echo htmlspecialchars($programme['ProgrammeName']); ?></title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .manage-modules-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .module-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .module-card h2 {
            margin-top: 0;
            color: #3498db;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .module-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .module-item {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            position: relative;
        }
        
        .module-item h3 {
            margin-top: 0;
            color: #333;
        }
        
        .module-item p {
            margin: 5px 0;
            color: #666;
        }
        
        .module-action {
            position: absolute;
            top: 15px;
            right: 15px;
        }
        
        .year-heading {
            margin-top: 30px;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
        }
        
        .module-form {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 15px;
            align-items: center;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        
        .back-button:hover {
            background-color: #5a6268;
        }
        
        @media (max-width: 768px) {
            .module-form {
                grid-template-columns: 1fr;
            }
            
            .module-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="manage-modules-container">
        <a href="admin_dashboard.php" class="back-button">← Back to Dashboard</a>
        
        <div class="module-card">
            <h2>Manage Modules: <?php echo htmlspecialchars($programme['ProgrammeName']); ?></h2>
            <p><strong>Level:</strong> <?php echo htmlspecialchars($programme['LevelName']); ?></p>
            <p><strong>Programme Leader:</strong> <?php echo htmlspecialchars($programme['LeaderName']); ?></p>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <h3>Add Module to Programme</h3>
            
            <?php if (empty($available_modules)): ?>
                <p>All available modules are already part of this programme.</p>
            <?php else: ?>
                <form method="POST" action="" class="module-form">
                    <div>
                        <label for="module_id">Select Module:</label>
                        <select id="module_id" name="module_id" class="form-control" required>
                            <?php foreach ($available_modules as $module): ?>
                                <option value="<?php echo $module['ModuleID']; ?>">
                                    <?php echo htmlspecialchars($module['ModuleName']); ?>
                                    (Led by: <?php echo htmlspecialchars($module['LeaderName']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="year">Year:</label>
                        <select id="year" name="year" class="form-control" required>
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                <option value="<?php echo $i; ?>">Year <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div>
                        <button type="submit" name="add_module" class="primary-btn">Add Module</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="module-card">
            <h2>Current Modules</h2>
            
            <?php
            $current_year = null;
            foreach ($programme_modules as $module):
                if ($current_year !== $module['Year']):
                    if ($current_year !== null): 
                        // Close previous grid
                        echo '</div>';
                    endif;
                    $current_year = $module['Year'];
                    echo '<h3 class="year-heading">Year ' . $current_year . '</h3>';
                    echo '<div class="module-grid">';
                endif;
            ?>
                <div class="module-item">
                    <h3><?php echo htmlspecialchars($module['ModuleName']); ?></h3>
                    <p><strong>Module Leader:</strong> <?php echo htmlspecialchars($module['LeaderName']); ?></p>
                    <div class="module-action">
                        <a href="manage_modules.php?id=<?php echo $programmeID; ?>&action=remove&module_id=<?php echo $module['ModuleID']; ?>" 
                           class="delete-btn" 
                           onclick="return confirm('Are you sure you want to remove this module from the programme?')">
                            Remove
                        </a>
                    </div>
                </div>
            <?php
            endforeach;
            
            if (empty($programme_modules)):
                echo '<p>No modules have been added to this programme yet.</p>';
            else:
                // Close the last grid
                echo '</div>';
            endif;
            ?>
        </div>
    </div>
</body>
</html>