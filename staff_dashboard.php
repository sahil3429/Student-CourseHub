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

// Function to get all staff members with their modules
function getStaffWithModules($pdo) {
    // Query to get staff information and their modules
    $query = "
        SELECT 
            s.StaffID, 
            s.Name, 
            m.ModuleName, 
            m.ModuleID,
            p.ProgrammeName,
            p.ProgrammeID
        FROM 
            Staff s
        LEFT JOIN 
            Modules m ON s.StaffID = m.ModuleLeaderID
        LEFT JOIN 
            Programmes p ON s.StaffID = p.ProgrammeLeaderID
        ORDER BY 
            s.Name
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    // Group results by staff member
    $staffMembers = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $staffId = $row['StaffID'];
        
        if (!isset($staffMembers[$staffId])) {
            $staffMembers[$staffId] = [
                'id' => $staffId,
                'name' => $row['Name'],
                'modules' => [],
                'programmes' => []
            ];
        }
        
        // Add module if one exists
        if ($row['ModuleID']) {
            $staffMembers[$staffId]['modules'][$row['ModuleID']] = $row['ModuleName'];
        }
        
        // Add programme if one exists
        if ($row['ProgrammeID']) {
            $staffMembers[$staffId]['programmes'][$row['ProgrammeID']] = $row['ProgrammeName'];
        }
    }
    
    return $staffMembers;
}

// Get staff filter parameters if any
$departmentFilter = isset($_GET['department']) ? $_GET['department'] : '';
$roleFilter = isset($_GET['role']) ? $_GET['role'] : '';
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Get all staff members with their modules
$staffMembers = getStaffWithModules($pdo);

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
    <title>Academic Staff - Student Course Hub</title>
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
            <li><a href="programmes.php">Programmes</a></li>
            <li><a href="staff_dashboard.php" class="active">Staff</a></li>
            <li><a href="modules.php">Modules</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li style="margin-left: auto;"><a href="logout.php" class="logout-btn">Logout</a></li>
        </ul>
    </nav>
    
    <main id="main-content">
        <div class="page-header">
            <h2>Academic Staff</h2>
            <form action="" method="GET" class="search-container">
                <input type="text" name="search" placeholder="Search staff..." aria-label="Search for staff" value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        
        
        
        <form action="staff.php" method="GET" class="filters">
            <select name="department" aria-label="Filter by department">
                <option value="">All Departments</option>
                <option value="comp-sci" <?php echo $departmentFilter == 'comp-sci' ? 'selected' : ''; ?>>Computer Science</option>
                <option value="cyber" <?php echo $departmentFilter == 'cyber' ? 'selected' : ''; ?>>Cyber Security</option>
                <option value="ai" <?php echo $departmentFilter == 'ai' ? 'selected' : ''; ?>>Artificial Intelligence</option>
                <option value="software" <?php echo $departmentFilter == 'software' ? 'selected' : ''; ?>>Software Engineering</option>
            </select>
            
            <select name="role" aria-label="Filter by role">
                <option value="">All Roles</option>
                <option value="programme-leader" <?php echo $roleFilter == 'programme-leader' ? 'selected' : ''; ?>>Programme Leaders</option>
                <option value="module-leader" <?php echo $roleFilter == 'module-leader' ? 'selected' : ''; ?>>Module Leaders</option>
                <option value="professor" <?php echo $roleFilter == 'professor' ? 'selected' : ''; ?>>Professors</option>
                <option value="lecturer" <?php echo $roleFilter == 'lecturer' ? 'selected' : ''; ?>>Lecturers</option>
            </select>
            
            <button type="submit">Apply Filters</button>
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
                    <a href="?page=<?php echo $currentPage - 1; ?>&department=<?php echo urlencode($departmentFilter); ?>&role=<?php echo urlencode($roleFilter); ?>&search=<?php echo urlencode($searchTerm); ?>" aria-label="Previous page">←</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&department=<?php echo urlencode($departmentFilter); ?>&role=<?php echo urlencode($roleFilter); ?>&search=<?php echo urlencode($searchTerm); ?>" 
                       <?php echo $i == $currentPage ? 'class="current" aria-current="page"' : ''; ?>>
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?php echo $currentPage + 1; ?>&department=<?php echo urlencode($departmentFilter); ?>&role=<?php echo urlencode($roleFilter); ?>&search=<?php echo urlencode($searchTerm); ?>" aria-label="Next page">→</a>
                <?php endif; ?>
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