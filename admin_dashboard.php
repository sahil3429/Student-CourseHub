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

// Admin name for display
$admin_name = isset($_SESSION["admin_name"]) ? $_SESSION["admin_name"] : "Administrator";

// Fetch counts for dashboard summary
try {
    // Count programmes
    $stmt = $conn->query("SELECT COUNT(*) FROM Programmes");
    $programme_count = $stmt->fetchColumn();
    
    // Count modules
    $stmt = $conn->query("SELECT COUNT(*) FROM Modules");
    $module_count = $stmt->fetchColumn();
    
    // Count staff
    $stmt = $conn->query("SELECT COUNT(*) FROM Staff");
    $staff_count = $stmt->fetchColumn();
    
    // Count interested students
    $stmt = $conn->query("SELECT COUNT(*) FROM InterestedStudents");
    $student_count = $stmt->fetchColumn();
} catch(PDOException $e) {
    echo "Error fetching dashboard data: " . $e->getMessage();
}

// Handle tab selection with PHP
$active_section = 'programmes-management'; // Default active section
$valid_sections = [
    'programmes-management',
    'modules-management',
    'staff-management',
    'student-management',
    'password-management'
];

// Check if a section is specified in the URL
if (isset($_GET['section']) && in_array($_GET['section'], $valid_sections)) {
    $active_section = $_GET['section'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - University Course Hub</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        /* Admin Dashboard Styles */
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
        
        .admin-header h1 {
            margin: 0;
            color: #333;
        }
        
        .admin-user {
            display: flex;
            align-items: center;
        }
        
        .admin-user span {
            margin-right: 15px;
            font-weight: 500;
        }
        
        .logout-btn {
            padding: 8px 15px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-card h2 {
            font-size: 36px;
            margin: 10px 0;
            color: #3498db;
        }
        
        .stat-card p {
            margin: 0;
            color: #777;
            font-size: 16px;
        }
        
        .admin-nav {
            margin-bottom: 30px;
            background-color: #f8f9fa;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .admin-nav ul {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .admin-nav li {
            flex: 1;
        }
        
        .admin-nav a {
            display: block;
            padding: 15px 20px;
            text-align: center;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .admin-nav a:hover {
            background-color: #e9ecef;
        }
        
        .admin-nav a.active {
            background-color: #3498db;
            color: white;
        }
        
        .admin-content {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .admin-section-content {
            display: none;
        }
        
        .admin-section-content.active {
            display: block;
        }
        
        .admin-section-content h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
        }
        
        .admin-actions {
            margin-bottom: 20px;
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th, .admin-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .admin-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .admin-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .edit-btn, .view-btn, .delete-btn, .toggle-publish-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 5px;
        }
        
        .edit-btn {
            background-color: #3498db;
            color: white;
        }
        
        .view-btn {
            background-color: #2ecc71;
            color: white;
        }
        
        .delete-btn {
            background-color: #e74c3c;
            color: white;
        }
        
        .toggle-publish-btn {
            background-color: #f39c12;
            color: white;
        }
        
        /* Form modal styles are already in your main CSS */
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Admin Dashboard</h1>
            <div class="admin-user">
                <span>Welcome, <?php echo htmlspecialchars($admin_name); ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <h2><?php echo $programme_count; ?></h2>
                <p>Programmes</p>
            </div>
            <div class="stat-card">
                <h2><?php echo $module_count; ?></h2>
                <p>Modules</p>
            </div>
            <div class="stat-card">
                <h2><?php echo $staff_count; ?></h2>
                <p>Staff Members</p>
            </div>
            <div class="stat-card">
                <h2><?php echo $student_count; ?></h2>
                <p>Interested Students</p>
            </div>
        </div>
        
        <nav class="admin-nav">
            <ul>
                <li><a href="?section=programmes-management" class="admin-nav-link <?php echo ($active_section == 'programmes-management') ? 'active' : ''; ?>">Programmes</a></li>
                <li><a href="?section=modules-management" class="admin-nav-link <?php echo ($active_section == 'modules-management') ? 'active' : ''; ?>">Modules</a></li>
                <li><a href="?section=staff-management" class="admin-nav-link <?php echo ($active_section == 'staff-management') ? 'active' : ''; ?>">Staff</a></li>
                <li><a href="?section=student-management" class="admin-nav-link <?php echo ($active_section == 'student-management') ? 'active' : ''; ?>">Interested Students</a></li>
                <li><a href="?section=password-management" class="admin-nav-link <?php echo ($active_section == 'password-management') ? 'active' : ''; ?>">Password Manager</a></li>
            </ul>
        </nav>
        
        <div class="admin-content">
            <!-- Programmes Management Section -->
            <section id="programmes-management" class="admin-section-content <?php echo ($active_section == 'programmes-management') ? 'active' : ''; ?>">
                <h2>Programmes Management</h2>
                <div class="admin-actions">
                    <button id="add-programme" class="primary-btn" onclick="location.href='programme_form.php';">Add New Programme</button>
                </div>
                <div class="admin-table-container">
                    <table class="admin-table" id="programmes-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Level</th>
                                <th>Programme Leader</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                // Join with Staff and Levels tables to get leader name and level name
                                $stmt = $conn->query("
                                    SELECT p.ProgrammeID, p.ProgrammeName, l.LevelName, s.Name as LeaderName
                                    FROM Programmes p
                                    LEFT JOIN Levels l ON p.LevelID = l.LevelID
                                    LEFT JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID
                                    ORDER BY p.ProgrammeID
                                ");
                                
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['ProgrammeID']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['ProgrammeName']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['LevelName']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['LeaderName']) . "</td>";
                                    echo "<td>";
                                    echo "<a href='programme_form.php?id=" . $row['ProgrammeID'] . "' class='edit-btn'>Edit</a>";
                                    echo "<a href='manage_modules.php?id=" . $row['ProgrammeID'] . "' class='view-btn'>Modules</a>";
                                    echo "<a href='delete.php?type=programme&id=" . $row['ProgrammeID'] . "' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this programme?\")'>Delete</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } catch(PDOException $e) {
                                echo "<tr><td colspan='5'>Error fetching programmes: " . $e->getMessage() . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Modules Management Section -->
            <section id="modules-management" class="admin-section-content <?php echo ($active_section == 'modules-management') ? 'active' : ''; ?>">
                <h2>Modules Management</h2>
                <div class="admin-actions">
                    <button id="add-module" class="primary-btn" onclick="location.href='module_form.php';">Add New Module</button>
                </div>
                <div class="admin-table-container">
                    <table class="admin-table" id="modules-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Module Leader</th>
                                <th>Programmes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                // Join with Staff table to get leader name
                                $stmt = $conn->query("
                                    SELECT m.ModuleID, m.ModuleName, s.Name as LeaderName
                                    FROM Modules m
                                    LEFT JOIN Staff s ON m.ModuleLeaderID = s.StaffID
                                    ORDER BY m.ModuleID
                                ");
                                
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    // Get programmes that use this module
                                    $prog_stmt = $conn->prepare("
                                        SELECT p.ProgrammeName, pm.Year
                                        FROM ProgrammeModules pm
                                        JOIN Programmes p ON pm.ProgrammeID = p.ProgrammeID
                                        WHERE pm.ModuleID = :moduleID
                                        ORDER BY p.ProgrammeName, pm.Year
                                    ");
                                    $prog_stmt->bindParam(':moduleID', $row['ModuleID']);
                                    $prog_stmt->execute();
                                    
                                    $programmes = [];
                                    while ($prog_row = $prog_stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $programmes[] = htmlspecialchars($prog_row['ProgrammeName']) . " (Year " . $prog_row['Year'] . ")";
                                    }
                                    
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['ModuleID']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['ModuleName']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['LeaderName']) . "</td>";
                                    echo "<td>" . (empty($programmes) ? "None" : implode("<br>", $programmes)) . "</td>";
                                    echo "<td>";
                                    echo "<a href='module_form.php?id=" . $row['ModuleID'] . "' class='edit-btn'>Edit</a>";
                                    echo "<a href='delete.php?type=module&id=" . $row['ModuleID'] . "' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this module?\")'>Delete</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } catch(PDOException $e) {
                                echo "<tr><td colspan='5'>Error fetching modules: " . $e->getMessage() . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Staff Management Section -->
            <section id="staff-management" class="admin-section-content <?php echo ($active_section == 'staff-management') ? 'active' : ''; ?>">
                <h2>Staff Management</h2>
                <div class="admin-actions">
                    <button id="add-staff" class="primary-btn" onclick="location.href='staff_form.php';">Add New Staff</button>
                </div>
                <div class="admin-table-container">
                    <table class="admin-table" id="staff-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Programmes Led</th>
                                <th>Modules Led</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $stmt = $conn->query("SELECT StaffID, Name FROM Staff ORDER BY StaffID");
                                
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    // Get programmes led by this staff member
                                    $prog_stmt = $conn->prepare("
                                        SELECT ProgrammeName
                                        FROM Programmes
                                        WHERE ProgrammeLeaderID = :staffID
                                        ORDER BY ProgrammeName
                                    ");
                                    $prog_stmt->bindParam(':staffID', $row['StaffID']);
                                    $prog_stmt->execute();
                                    
                                    $programmes = [];
                                    while ($prog_row = $prog_stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $programmes[] = htmlspecialchars($prog_row['ProgrammeName']);
                                    }
                                    
                                    // Get modules led by this staff member
                                    $mod_stmt = $conn->prepare("
                                        SELECT ModuleName
                                        FROM Modules
                                        WHERE ModuleLeaderID = :staffID
                                        ORDER BY ModuleName
                                    ");
                                    $mod_stmt->bindParam(':staffID', $row['StaffID']);
                                    $mod_stmt->execute();
                                    
                                    $modules = [];
                                    while ($mod_row = $mod_stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $modules[] = htmlspecialchars($mod_row['ModuleName']);
                                    }
                                    
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['StaffID']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                                    echo "<td>" . (empty($programmes) ? "None" : implode("<br>", $programmes)) . "</td>";
                                    echo "<td>" . (empty($modules) ? "None" : implode("<br>", $modules)) . "</td>";
                                    echo "<td>";
                                    echo "<a href='staff_form.php?id=" . $row['StaffID'] . "' class='edit-btn'>Edit</a>";
                                    echo "<a href='delete.php?type=staff&id=" . $row['StaffID'] . "' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this staff member?\")'>Delete</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } catch(PDOException $e) {
                                echo "<tr><td colspan='5'>Error fetching staff: " . $e->getMessage() . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Student Management Section -->
            <section id="student-management" class="admin-section-content <?php echo ($active_section == 'student-management') ? 'active' : ''; ?>">
                <h2>Interested Students</h2>
                <div class="admin-actions">
                    <button id="export-students" class="primary-btn" onclick="location.href='export_students.php';">Export Mailing List</button>
                </div>
                <div class="admin-table-container">
                    <table class="admin-table" id="students-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Programme</th>
                                <th>Registered Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                // Join with Programmes table to get programme name
                                $stmt = $conn->query("
                                    SELECT i.InterestID, i.StudentName, i.Email, p.ProgrammeName, i.RegisteredAt
                                    FROM InterestedStudents i
                                    JOIN Programmes p ON i.ProgrammeID = p.ProgrammeID
                                    ORDER BY i.RegisteredAt DESC
                                ");
                                
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['InterestID']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['StudentName']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Email']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['ProgrammeName']) . "</td>";
                                    echo "<td>" . htmlspecialchars(date('Y-m-d H:i', strtotime($row['RegisteredAt']))) . "</td>";
                                    echo "<td>";
                                    echo "<a href='delete.php?type=student&id=" . $row['InterestID'] . "' class='delete-btn' onclick='return confirm(\"Are you sure you want to remove this student?\")'>Remove</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } catch(PDOException $e) {
                                echo "<tr><td colspan='6'>Error fetching students: " . $e->getMessage() . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>
            
           <!-- Password Management Section -->
<section id="password-management" class="admin-section-content <?php echo ($active_section == 'password-management') ? 'active' : ''; ?>">
    <h2>Password Management</h2>
    
    <!-- Admin Users Section -->
    <div class="password-section">
        <h3>Admin Users</h3>
        <div class="admin-actions">
            <a href="add_user.php?type=admin" class="primary-btn">Add New Admin</a>
        </div>
        <div class="admin-table-container">
            <table class="admin-table" id="admin-users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        // First, check which columns exist in the admin_users table
                        $stmt = $conn->query("DESCRIBE admin_users");
                        $admin_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        
                        // Build query based on available columns
                        $select_columns = "ID, Username";
                        if (in_array('Name', $admin_columns)) {
                            $select_columns .= ", Name";
                        }
                        
                        $stmt = $conn->query("SELECT $select_columns FROM admin_users ORDER BY ID");
                        
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['ID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Username']) . "</td>";
                            
                            // Only show Name if it exists
                            if (isset($row['Name'])) {
                                echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                            } else {
                                echo "<td>N/A</td>";
                            }
                            
                            echo "<td>";
                            echo "<a href='change_password.php?type=admin&id=" . $row['ID'] . "' class='edit-btn'>Change Password</a>";
                            // Add delete button - prevent deletion of current user
                            if ($_SESSION["admin_id"] != $row['ID']) {
                                echo "<a href='delete_user.php?type=admin&id=" . $row['ID'] . "' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this admin user?\")'>Delete</a>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    } catch(PDOException $e) {
                        echo "<tr><td colspan='4'>Error fetching admin users: " . $e->getMessage() . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Staff Users Section -->
    
    
    <div class="password-section" style="margin-top: 30px;">
        <h3>Staff Users</h3>
        <div class="admin-actions">
            <a href="add_user.php?type=staff" class="primary-btn">Add New Staff User</a>
        </div>
        <div class="admin-table-container">
            <table class="admin-table" id="staff-users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Staff Member</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        // First, check which columns exist in the staff_users table
                        $stmt = $conn->query("DESCRIBE staff_users");
                        $staff_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        
                        // Determine staff ID column name
                        $staff_id_column = null;
                        $possible_staff_id_columns = ['StaffID', 'staff_id', 'staffid', 'staff_member_id'];
                        
                        foreach ($possible_staff_id_columns as $column) {
                            if (in_array($column, $staff_columns)) {
                                $staff_id_column = $column;
                                break;
                            }
                        }
                        
                        // If we found a staff ID column, join with Staff table
                        if ($staff_id_column) {
                            $stmt = $conn->prepare("SELECT su.*, s.Name 
                                                   FROM staff_users su
                                                   LEFT JOIN Staff s ON su.$staff_id_column = s.StaffID
                                                   ORDER BY su.Username");
                        } else {
                            // Otherwise just show staff users without joining
                            $stmt = $conn->prepare("SELECT * FROM staff_users ORDER BY username");
                        }
                        
                        $stmt->execute();
                        
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $user_id_column = array_keys($row)[0]; // Assume first column is ID
                            
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row[$user_id_column]) . "</td>";
                            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                            
                            // Show staff name if available from join
                            if (isset($row['Name'])) {
                                echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                            } else {
                                echo "<td>Unknown</td>";
                            }
                            
                            echo "<td>";
                            echo "<a href='change_password.php?type=staff&id=" . $row[$user_id_column] . "' class='edit-btn'>Change Password</a>";
                            // Add delete button for staff users
                            echo "<a href='delete_user.php?type=staff&id=" . $row[$user_id_column] . "' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this staff user?\")'>Delete</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } catch(PDOException $e) {
                        echo "<tr><td colspan='4'>Error fetching staff users: " . $e->getMessage() . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
                <!-- Password management content here -->
            </section>
        </div>
    </div>
</body>
</html>