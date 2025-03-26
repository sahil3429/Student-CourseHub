<?php
// Database connection settings
$host = "localhost";
$dbname = "student_course_hub";
$username = "root";
$password = ""; // Set your database password here if needed

// Create database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

/*
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_SESSION["staff_id"])) {
        $staff_id = $_SESSION["staff_id"];

        $stmt = $pdo->prepare("SELECT StaffID, Name FROM staff WHERE StaffID = ?");
        $stmt->execute([$staff_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $staff_name = $row ? $row["Name"] : "Staff";
        $staff_id = $row ? $row["StaffID"] : null;
    } else {
        $staff_name = "Staff";
        $staff_id = null;
    }
    
    echo "Welcome, " . htmlspecialchars($staff_name);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
*/

// Later added for showing staff user Name becuase pdo wan't working.

session_start(); // Start the session

// Database connection
$servername = "localhost"; // Change to your server details
$username = "root"; // Database username
$password = ""; // Database password
$dbname = "student_course_hub"; // Database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if session contains staff_id
if (isset($_SESSION["staff_id"])) {
    $staff_id = $_SESSION["staff_id"];

    // Prepare and execute query
    $stmt = $conn->prepare("SELECT StaffID, Name FROM staff WHERE StaffID = ?");
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch data
    if ($row = $result->fetch_assoc()) {
        $staff_name = $row["Name"];
        $staff_id = $row["StaffID"];
    } else {
        $staff_name = "Staff"; // Default if no record is found
        $staff_id = null;
    }

    $stmt->close();
} else {
    $staff_name = "Staff";
    $staff_id = null;
}

// Close the connection
$conn->close();

// Function to get all staff members with their modules
function getStaffWithModules($pdo, $departmentFilter = '', $roleFilter = '', $searchTerm = '') {
    $query = "
        SELECT 
            s.StaffID, 
            s.Name, 
            m.ModuleName, 
            m.ModuleID,
            p.ProgrammeName,
            p.ProgrammeID,
            l.LevelName
        FROM 
            Staff s
        LEFT JOIN 
            Modules m ON s.StaffID = m.ModuleLeaderID
        LEFT JOIN 
            Programmes p ON s.StaffID = p.ProgrammeLeaderID
        LEFT JOIN
            Levels l ON p.LevelID = l.LevelID
        WHERE 1=1
    ";
    
    $params = [];
    
    // Apply search filter
    if (!empty($searchTerm)) {
        $query .= " AND s.Name LIKE :searchTerm";
        $params[':searchTerm'] = "%$searchTerm%";
    }
    
    // Apply department filter
    if (!empty($departmentFilter)) {
        switch($departmentFilter) {
            case 'comp-sci':
                $query .= " AND (p.ProgrammeName LIKE '%Computer Science%' OR m.ModuleName LIKE '%Computer%')";
                break;
            case 'cyber':
                $query .= " AND (p.ProgrammeName LIKE '%Cyber%' OR m.ModuleName LIKE '%Security%' OR m.ModuleName LIKE '%Forensics%')";
                break;
            case 'ai':
                $query .= " AND (p.ProgrammeName LIKE '%Artificial%' OR m.ModuleName LIKE '%AI%' OR m.ModuleName LIKE '%Machine Learning%')";
                break;
            case 'software':
                $query .= " AND (p.ProgrammeName LIKE '%Software%' OR m.ModuleName LIKE '%Engineering%')";
                break;
        }
    }
    
    // Apply role filter
    if (!empty($roleFilter)) {
        switch($roleFilter) {
            case 'programme-leader':
                $query .= " AND p.ProgrammeLeaderID IS NOT NULL";
                break;
            case 'module-leader':
                $query .= " AND m.ModuleLeaderID IS NOT NULL";
                break;
            case 'professor':
                $query .= " AND p.ProgrammeLeaderID IS NOT NULL";
                break;
            case 'lecturer':
                $query .= " AND m.ModuleLeaderID IS NOT NULL AND p.ProgrammeLeaderID IS NULL";
                break;
        }
    }
    
    $query .= " ORDER BY s.Name";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    // Rest of the function remains the same...
    $staffMembers = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $staffId = $row['StaffID'];
        
        if (!isset($staffMembers[$staffId])) {
            $staffMembers[$staffId] = [
                'id' => $staffId,
                'name' => $row['Name'],
                'modules' => [],
                'programmes' => [],
                'level' => $row['LevelName'] ?? null
            ];
        }
        
        if ($row['ModuleID']) {
            $staffMembers[$staffId]['modules'][$row['ModuleID']] = $row['ModuleName'];
        }
        
        if ($row['ProgrammeID']) {
            $staffMembers[$staffId]['programmes'][$row['ProgrammeID']] = $row['ProgrammeName'];
        }
    }
    
    return $staffMembers;
}

// Get staff filter parameters if any
$departmentFilter = $_GET['department'] ?? '';
$roleFilter = $_GET['role'] ?? '';
$searchTerm = $_GET['search'] ?? '';

// Get all staff members with their modules
$staffMembers = getStaffWithModules($pdo, $departmentFilter, $roleFilter, $searchTerm);

// Function to determine staff title based on roles
function getStaffTitle($staff) {
    if (count($staff['programmes']) > 0) {
        return "Professor";
    } elseif (count($staff['modules']) > 2) {
        return "Associate Professor";
    } else {
        return "Senior Lecturer";
    }
}

// Function to get department based on modules/programmes (simplified version)
function getStaffDepartment($staff) {
    // This is a simplified implementation
    // In a real app, you'd have a departments table or more logic
    $programmeName = reset($staff['programmes']);
    if (strpos($programmeName, 'Cyber') !== false) {
        return 'Cyber Security';
    } elseif (strpos($programmeName, 'Artificial') !== false) {
        return 'Artificial Intelligence';
    } elseif (strpos($programmeName, 'Software') !== false) {
        return 'Software Engineering';
    } elseif (strpos($programmeName, 'Data') !== false) {
        return 'Data Science';
    } else {
        return 'Computer Science';
    }
}

// Get pagination parameters
$itemsPerPage = 6;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$totalItems = count($staffMembers);
$totalPages = ceil($totalItems / $itemsPerPage);

// Ensure current page is within valid range
if ($currentPage < 1) $currentPage = 1;
if ($currentPage > $totalPages) $currentPage = $totalPages;

// Calculate the slice of staff members for the current page
$startIndex = ($currentPage - 1) * $itemsPerPage;
$staffMembersPage = array_slice($staffMembers, $startIndex, $itemsPerPage, true);

 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Staff - Student Course Hub <br> <?= $pageTitle ?> </title>
    <link rel="stylesheet" href="styles_staff_dashboard.css">
</head>
<body>
    <header>
        <a href="index.php"><h1 style="color: white;">Student Course Hub</h1></a>
        <p>University of Excellence</p>
    </header>
    
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="staff_dashboard.php" class="active">Dashboard</a></li>
            <!-- <li><a href="programmes.php">Programmes</a></li> -->
            <li><a href="staff.php">Staff</a></li>
            <!-- <li><a href="contact.php">Contact</a></li> -->

            <li style="margin-left: auto;"><a href="logout.php" class="logout-btn">Logout</a></li>
        </ul>
    </nav>

    <div class="staff-container">
        <div style="text-align: center;">
            <h1>Staff Dashboard</h1>
            <div>
                <span><strong>Welcome, <?php echo htmlspecialchars($staff_name); ?></strong></span>
        </div>
    
    <main id="main-content">
        
        <div class="page-header">
            <h2>Academic Staff</h2>
        </div>
        <form action="" method="GET" class="filters">
            <div class="filter-group">
                <label for="department">Department:</label>
                <select name="department" id="department">
                    <option value="">All Departments</option>
                    <option value="comp-sci" <?= $departmentFilter == 'comp-sci' ? 'selected' : '' ?>>Computer Science</option>
                    <option value="cyber" <?= $departmentFilter == 'cyber' ? 'selected' : '' ?>>Cyber Security</option>
                    <option value="ai" <?= $departmentFilter == 'ai' ? 'selected' : '' ?>>Artificial Intelligence</option>
                    <option value="software" <?= $departmentFilter == 'software' ? 'selected' : '' ?>>Software Engineering</option>
                    <option value="data" <?= $departmentFilter == 'data' ? 'selected' : '' ?>>Data Science</option>
                </select>
            </div>
        
            <div class="filter-group">
                <label for="role">Role:</label>
                <select name="role" id="role">
                    <option value="">All Roles</option>
                    <option value="programme-leader" <?= $roleFilter == 'programme-leader' ? 'selected' : '' ?>>Programme Leaders</option>
                    <option value="module-leader" <?= $roleFilter == 'module-leader' ? 'selected' : '' ?>>Module Leaders</option>
                    <option value="professor" <?= $roleFilter == 'professor' ? 'selected' : '' ?>>Professors</option>
                    <option value="lecturer" <?= $roleFilter == 'lecturer' ? 'selected' : '' ?>>Lecturers</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="level">Level:</label>
                <select name="level" id="level">
                    <option value="">All Levels</option>
                    <option value="undergrad" <?= ($_GET['level'] ?? '') == 'undergrad' ? 'selected' : '' ?>>Undergraduate</option>
                    <option value="postgrad" <?= ($_GET['level'] ?? '') == 'postgrad' ? 'selected' : '' ?>>Postgraduate</option>
                </select>
            </div>

            <button type="submit" class="apply-filters">Apply Filters</button>
            <?php if ($departmentFilter || $roleFilter || $searchTerm): ?>
                <a href="staff.php" class="reset-filters">Reset Filters</a>
            <?php endif; ?>
        </form>
        
        <div class="staff-grid">
            <?php foreach ($staffMembersPage as $staffId => $staff): ?>
                <div class="staff-card">
                    <div class="staff-info">
                        <div class="badges">
                            <?php if (count($staff['programmes']) > 0): ?>
                                <span class="badge programme-leader">Programme Leader</span>
                            <?php endif; ?>
                            
                            <?php if (count($staff['modules']) > 0): ?>
                                <span class="badge module-leader">Module Leader</span>
                            <?php endif; ?>
                        </div>
                        
                        <h3 class="staff-name"><?php echo htmlspecialchars($staff['name']); ?></h3>
                        <p class="staff-title"><?php echo htmlspecialchars(getStaffTitle($staff)); ?></p>
                        
                        <?php if (count($staff['programmes']) > 0): ?>
                            <p><strong>Programme Leader for:</strong></p>
                            <ul>
                                <?php foreach ($staff['programmes'] as $programme): ?>
                                    <li><?php echo htmlspecialchars($programme); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        
                        <?php if (count($staff['modules']) > 0): ?>
                            <div class="staff-modules">
                                <p><strong>Modules:</strong></p>
                                <div>
                                    <?php foreach ($staff['modules'] as $module): ?>
                                        <span class="module-pill"><?php echo htmlspecialchars($module); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?= $currentPage - 1 ?>&department=<?= urlencode($departmentFilter) ?>&role=<?= urlencode($roleFilter) ?>&search=<?= urlencode($searchTerm) ?>">←</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>&department=<?= urlencode($departmentFilter) ?>&role=<?= urlencode($roleFilter) ?>&search=<?= urlencode($searchTerm) ?>"
                    <?= $i == $currentPage ? 'class="current"' : '' ?>>
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?= $currentPage + 1 ?>&department=<?= urlencode($departmentFilter) ?>&role=<?= urlencode($roleFilter) ?>&search=<?= urlencode($searchTerm) ?>">→</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($staffMembersPage)): ?>
            <div class="no-results">
                <p>No staff members found matching your criteria.</p>
                <a href="staff_dashboard.php">Reset filters</a>
            </div>
        <?php endif; ?>
    </main>
    
    <footer>
        <p>© 2025 University of Excellence - Student Course Hub</p>
        <p>Contact: <a href="mailto:admissions@example.ac.uk" style="color: white;">admissions@example.ac.uk</a></p>
    </footer>

    <script>
        // Simple scripts for demo functionality
        document.getElementById('toggle-contrast').addEventListener('click', function() {
            document.body.classList.toggle('high-contrast');
        });
        
        document.getElementById('text-size').addEventListener('change', function() {
            document.body.className = '';
            document.body.classList.add(this.value);
        });
    </script>
</body>
</html>