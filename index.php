<?php
// Database connection
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "student_course_hub";
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get all programmes
function getAllProgrammes($conn) {
    $sql = "SELECT p.ProgrammeID, p.ProgrammeName, p.Description, p.Image, 
            l.LevelName, s.Name as LeaderName 
            FROM Programmes p
            JOIN Levels l ON p.LevelID = l.LevelID
            JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID";
    $result = $conn->query($sql);
    
    $programmes = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $programmes[] = $row;
        }
    }
    return $programmes;
}

// Function to get programmes by level
function getProgrammesByLevel($conn, $levelId) {
    $sql = "SELECT p.ProgrammeID, p.ProgrammeName, p.Description, p.Image, 
            l.LevelName, s.Name as LeaderName 
            FROM Programmes p
            JOIN Levels l ON p.LevelID = l.LevelID
            JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID
            WHERE p.LevelID = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $levelId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $programmes = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $programmes[] = $row;
        }
    }
    return $programmes;
}

// Function to search programmes
function searchProgrammes($conn, $search) {
    $searchTerm = "%$search%";
    $sql = "SELECT p.ProgrammeID, p.ProgrammeName, p.Description, p.Image, 
            l.LevelName, s.Name as LeaderName 
            FROM Programmes p
            JOIN Levels l ON p.LevelID = l.LevelID
            JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID
            WHERE p.ProgrammeName LIKE ? 
            OR p.Description LIKE ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $programmes = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $programmes[] = $row;
        }
    }
    return $programmes;
}

// Function to get a single programme by ID
function getProgrammeById($conn, $id) {
    $sql = "SELECT p.ProgrammeID, p.ProgrammeName, p.Description, p.Image, 
            l.LevelName, l.LevelID, s.Name as LeaderName 
            FROM Programmes p
            JOIN Levels l ON p.LevelID = l.LevelID
            JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID
            WHERE p.ProgrammeID = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// Function to get modules for a programme
function getProgrammeModules($conn, $programmeId, $year) {
    $sql = "SELECT m.ModuleID, m.ModuleName, m.Description, s.Name as ModuleLeader
            FROM ProgrammeModules pm
            JOIN Modules m ON pm.ModuleID = m.ModuleID
            JOIN Staff s ON m.ModuleLeaderID = s.StaffID
            WHERE pm.ProgrammeID = ? AND pm.Year = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $programmeId, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $modules = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $modules[] = $row;
        }
    }
    return $modules;
}

// Function to get top 3 staff (based on programmes and modules led)
function getTopStaff($conn) {
    $sql = "SELECT s.StaffID, s.Name, 
            GROUP_CONCAT(DISTINCT p.ProgrammeName SEPARATOR ', ') as ProgrammesLed,
            GROUP_CONCAT(DISTINCT m.ModuleName SEPARATOR ', ') as ModulesLed,
            (COUNT(DISTINCT p.ProgrammeID) + COUNT(DISTINCT m.ModuleID)) as Responsibilities
            FROM Staff s
            LEFT JOIN Programmes p ON s.StaffID = p.ProgrammeLeaderID
            LEFT JOIN Modules m ON s.StaffID = m.ModuleLeaderID
            GROUP BY s.StaffID
            HAVING Responsibilities > 0
            ORDER BY Responsibilities DESC
            LIMIT 3";
    
    $result = $conn->query($sql);
    
    $staff = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $staff[] = $row;
        }
    }
    return $staff;
}

// Function to register interest
function registerInterest($conn, $programmeId, $name, $email, $comments) {
    // Change this line to match your table structure
    $sql = "INSERT INTO InterestedStudents (ProgrammeID, StudentName, Email) 
            VALUES (?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    // Modified to use only three parameters
    $stmt->bind_param("iss", $programmeId, $name, $email);
    
    if ($stmt->execute()) {
        return true;
    }
    
    error_log("Execute failed: " . $stmt->error);
    return false;
}
// Handle admin login
function verifyAdmin($username, $password) {
    // In a real application, this would check against a secure database
    // This is a simple example - use proper authentication in production
    if ($username === "admin" && $password === "password") {
        return true;
    }
    return false;
}

// Initialize variables
$programmes = [];
$filtered = false;
$searchQuery = "";
$levelFilter = "all";

// Handle filtering
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['level']) && $_GET['level'] != "all") {
        $levelFilter = $_GET['level'];
        $programmes = getProgrammesByLevel($conn, $levelFilter);
        $filtered = true;
    } elseif (isset($_GET['search']) && !empty($_GET['search'])) {
        $searchQuery = $_GET['search'];
        $programmes = searchProgrammes($conn, $searchQuery);
        $filtered = true;
    }
}

// If no filtering applied, get all programmes
if (!$filtered) {
    $programmes = getAllProgrammes($conn);
}

// Pagination for programmes
$programmesPerPage = 3;
$totalProgrammes = count($programmes);
$totalPages = ceil($totalProgrammes / $programmesPerPage);

// Get current page from URL parameter, default to 1
$currentProgrammePage = isset($_GET['programme_page']) ? (int)$_GET['programme_page'] : 1;

// Ensure page is within valid range
if ($currentProgrammePage < 1) {
    $currentProgrammePage = 1;
} elseif ($currentProgrammePage > $totalPages && $totalPages > 0) {
    $currentProgrammePage = $totalPages;
}

// Calculate starting index for this page
$startIndex = ($currentProgrammePage - 1) * $programmesPerPage;

// Get slice of programmes for current page
$currentPageProgrammes = array_slice($programmes, $startIndex, $programmesPerPage);

// Handle programme detail view
$programmeDetail = null;
$programmeModules = [];

if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $programmeId = $_GET['view'];
    $programmeDetail = getProgrammeById($conn, $programmeId);
    
    if ($programmeDetail) {
        // Get modules for each year
        $years = $programmeDetail['LevelID'] == 1 ? 3 : 1; // UG has 3 years, PG has 1
        
        for ($i = 1; $i <= $years; $i++) {
            $programmeModules[$i] = getProgrammeModules($conn, $programmeId, $i);
        }
    }
}

// Handle interest registration
$registrationSuccess = false;
$registrationError = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_interest'])) {
    $studentName = $_POST['student_name'];
    $studentEmail = $_POST['student_email'];
    $programmeId = $_POST['programme_id'];
    $comments = isset($_POST['student_comments']) ? $_POST['student_comments'] : "";
    
    if (registerInterest($conn, $programmeId, $studentName, $studentEmail, $comments)) {
        $registrationSuccess = true;
    } else {
        $registrationError = "Failed to register interest. Please try again.";
    }
}

// Handle admin login
$adminLoggedIn = false;
$loginError = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_login'])) {
    $username = $_POST['admin_username'];
    $password = $_POST['admin_password'];
    
    if (verifyAdmin($username, $password)) {
        session_start();
        $_SESSION['admin_logged_in'] = true;
        $adminLoggedIn = true;
    } else {
        $loginError = "Invalid username or password";
    }
}

// Check if admin is already logged in
session_start();
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $adminLoggedIn = true;
}

// Get top 3 staff for staff section
$staff = getTopStaff($conn);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Course Hub</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Additional Styles for Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin: 20px 0;
            list-style: none;
            padding: 0;
        }
        
        .pagination li {
            margin: 0 5px;
        }
        
        .pagination a, .pagination span {
            display: inline-block;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
            transition: background-color 0.3s;
        }
        
        .pagination a:hover {
            background-color: #f5f5f5;
        }
        
        .pagination .active span {
            background-color: #0056b3;
            color: white;
            border-color: #0056b3;
        }
        
        .pagination .disabled span {
            color: #aaa;
            cursor: not-allowed;
        }
        
        /* For the year tabs using PHP only */
        .year-tab {
            display: inline-block;
            padding: 10px 15px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px 4px 0 0;
            margin-right: 5px;
            text-decoration: none;
            color: #333;
        }
        
        .year-tab.active {
            background-color: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }

        /* Modal Styles */
        .modal {
            display: <?php echo ($registrationSuccess || isset($_GET['register']) && $_GET['register'] == 'show') ? 'block' : 'none'; ?>;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border-radius: 5px;
            width: 50%;
            max-width: 500px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <h1>University Course Hub</h1>
            </div>
            <div class="nav-links">
                <ul>
                    <li><a href="index.php#home">Home</a></li>
                    <li><a href="index.php#programmes">Programmes</a></li>
                    <li><a href="index.php#staff">Staff</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                    <li><a href="stafflogin.php" class="admin-btn">Login</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <?php if (!$programmeDetail): ?>
        <!-- Hero Section -->
        <section id="home" class="hero">
        <div class="hero-images">
               <img src="https://www.shutterstock.com/image-photo/asian-teenager-students-doing-robot-600nw-2221748207.jpg" alt="University">
            </div>
            <div class="hero-content">
                <h1>Discover Your Future</h1>
                <p>Explore our wide range of undergraduate and postgraduate programmes</p>
                <div class="hero-buttons">
                    <a href="#programmes" class="primary-btn">Browse Programmes</a>
                    <a href="#contact" class="secondary-btn">Contact Us</a>
                </div>
            </div>
        </section>

        <!-- Filter Section -->
        <section class="filter-section">
            <div class="filter-container">
                <h2>Find Your Perfect Programme</h2>
                <form method="GET" action="index.php#programmes" class="filters">
                    <div class="filter-group">
                        <label for="level-filter">Level:</label>
                        <select id="level-filter" name="level">
                            <option value="all" <?php echo $levelFilter == "all" ? "selected" : ""; ?>>All Levels</option>
                            <option value="1" <?php echo $levelFilter == "1" ? "selected" : ""; ?>>Undergraduate</option>
                            <option value="2" <?php echo $levelFilter == "2" ? "selected" : ""; ?>>Postgraduate</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="search-input">Search:</label>
                        <input type="text" id="search-input" name="search" placeholder="e.g., Computer Science, Cyber Security" value="<?php echo htmlspecialchars($searchQuery); ?>">
                    </div>
                    <button type="submit" class="primary-btn">Apply Filters</button>
                </form>
            </div>
        </section>

        <!-- Programmes Section with Pagination -->
        <section id="programmes" class="programmes">
            <h2>Our Programmes</h2>
            
            <?php if (empty($programmes)): ?>
                <div class="no-results">
                    <p>No programmes match your criteria. Please try different filters.</p>
                </div>
            <?php else: ?>
                <div class="programmes-list">
                    <?php foreach ($currentPageProgrammes as $programme): ?>
                        <div class="programme-item">
                            <div class="programme-header">
                                <h3><?php echo htmlspecialchars($programme['ProgrammeName']); ?></h3>
                                <span class="level-tag"><?php echo htmlspecialchars($programme['LevelName']); ?></span>
                            </div>
                            <p class="programme-description">
                                <?php 
                                    // Show truncated description
                                    echo htmlspecialchars(substr($programme['Description'], 0, 150)) . 
                                         (strlen($programme['Description']) > 150 ? '...' : '');
                                ?>
                            </p>
                            <p class="programme-leader">Programme Leader: <?php echo htmlspecialchars($programme['LeaderName']); ?></p>
                            <a href="index.php?view=<?php echo $programme['ProgrammeID']; ?>" class="view-details-btn">View Details</a>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <ul class="pagination">
                        <?php 
                            // Preserve any existing filters in pagination links
                            $queryParams = [];
                            if ($levelFilter != "all") {
                                $queryParams['level'] = $levelFilter;
                            }
                            if (!empty($searchQuery)) {
                                $queryParams['search'] = $searchQuery;
                            }
                            
                            // Previous button
                            echo '<li class="' . ($currentProgrammePage == 1 ? 'disabled' : '') . '">';
                            if ($currentProgrammePage > 1) {
                                $queryParams['programme_page'] = $currentProgrammePage - 1;
                                echo '<a href="index.php?' . http_build_query($queryParams) . '#programmes">&laquo; Previous</a>';
                            } else {
                                echo '<span>&laquo; Previous</span>';
                            }
                            echo '</li>';
                            
                            // Page numbers
                            for ($i = 1; $i <= $totalPages; $i++) {
                                echo '<li class="' . ($i == $currentProgrammePage ? 'active' : '') . '">';
                                if ($i == $currentProgrammePage) {
                                    echo '<span>' . $i . '</span>';
                                } else {
                                    $queryParams['programme_page'] = $i;
                                    echo '<a href="index.php?' . http_build_query($queryParams) . '#programmes">' . $i . '</a>';
                                }
                                echo '</li>';
                            }
                            
                            // Next button
                            echo '<li class="' . ($currentProgrammePage == $totalPages ? 'disabled' : '') . '">';
                            if ($currentProgrammePage < $totalPages) {
                                $queryParams['programme_page'] = $currentProgrammePage + 1;
                                echo '<a href="index.php?' . http_build_query($queryParams) . '#programmes">Next &raquo;</a>';
                            } else {
                                echo '<span>Next &raquo;</span>';
                            }
                            echo '</li>';
                        ?>
                    </ul>
                <?php endif; ?>
            <?php endif; ?>
        </section>
        
        <!-- Staff Section (Top 3 Only) -->
        <section id="staff" class="staff">
            <h2>Our Top Academic Staff</h2>
            <div class="staff-grid">
                <?php foreach ($staff as $member): ?>
                    <div class="staff-card">
                        <div class="staff-content">
                            <h3><?php echo htmlspecialchars($member['Name']); ?></h3>
                            <?php if (!empty($member['ProgrammesLed'])): ?>
                                <p class="staff-role">Programme Leader: <?php echo htmlspecialchars($member['ProgrammesLed']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($member['ModulesLed'])): ?>
                                <p class="staff-modules">Modules: <?php echo htmlspecialchars($member['ModulesLed']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        
        <?php else: ?>
        <!-- Programme Detail View -->
        <section class="programme-detail">
            <div class="programme-header">
                <h1><?php echo htmlspecialchars($programmeDetail['ProgrammeName']); ?></h1>
                <span class="level-tag"><?php echo htmlspecialchars($programmeDetail['LevelName']); ?></span>
            </div>
            
            <div class="programme-image">
                <img src="<?php echo !empty($programmeDetail['Image']) ? htmlspecialchars($programmeDetail['Image']) : '/api/placeholder/800/400'; ?>" alt="<?php echo htmlspecialchars($programmeDetail['ProgrammeName']); ?>">
            </div>
            
            <div class="programme-description">
                <h2>Description</h2>
                <p><?php echo htmlspecialchars($programmeDetail['Description']); ?></p>
            </div>
            
            <div class="programme-leader">
                <h2>Programme Leader</h2>
                <p><?php echo htmlspecialchars($programmeDetail['LeaderName']); ?></p>
            </div>
            
            <div class="programme-modules">
                <h2>Modules</h2>
                
                <?php
                // Get the current year tab from GET parameter, default to 1
                $currentYearTab = isset($_GET['year']) ? (int)$_GET['year'] : 1;
                // Ensure it's within valid range
                if ($currentYearTab < 1 || $currentYearTab > count($programmeModules)) {
                    $currentYearTab = 1;
                }
                ?>
                
                <!-- Year tabs using PHP links instead of JavaScript -->
                <div class="module-year-tabs">
                    <?php for ($i = 1; $i <= count($programmeModules); $i++): ?>
                        <a href="index.php?view=<?php echo $programmeDetail['ProgrammeID']; ?>&year=<?php echo $i; ?>" 
                           class="year-tab <?php echo $i === $currentYearTab ? 'active' : ''; ?>">
                            Year <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                
                <!-- Display only the modules for the selected year -->
                <div class="year-modules">
                    <?php if (empty($programmeModules[$currentYearTab])): ?>
                        <p>No modules found for Year <?php echo $currentYearTab; ?>.</p>
                    <?php else: ?>
                        <?php foreach ($programmeModules[$currentYearTab] as $module): ?>
                            <div class="module-card">
                                <h3><?php echo htmlspecialchars($module['ModuleName']); ?></h3>
                                <p><?php echo htmlspecialchars($module['Description']); ?></p>
                                <p class="module-leader">Module Leader: <?php echo htmlspecialchars($module['ModuleLeader']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="programme-actions">
                <a href="index.php?view=<?php echo $programmeDetail['ProgrammeID']; ?>&register=show" class="primary-btn">
                    Register Interest
                </a>
                <a href="index.php#programmes" class="secondary-btn">Back to Programmes</a>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- Register Interest Form Modal (PHP controlled) -->
        <div id="register-interest-modal" class="modal">
            <div class="modal-content">
                <div class="register-interest-form">
                    <h2>Register Your Interest</h2>
                    <?php if (isset($_GET['view'])): ?>
                        <p id="interest-programme-name"><?php echo htmlspecialchars($programmeDetail['ProgrammeName']); ?></p>
                    <?php else: ?>
                        <p id="interest-programme-name">Select a programme</p>
                    <?php endif; ?>
                    
                    <?php if ($registrationSuccess): ?>
                        <div class="success-message">
                            <p>Thank you! Your interest has been registered successfully.</p>
                            <p>You will receive updates about this programme via email.</p>
                            <a href="index.php<?php echo isset($_GET['view']) ? '?view=' . $_GET['view'] : ''; ?>" class="primary-btn">Close</a>
                        </div>
                    <?php else: ?>
                        <?php if (!empty($registrationError)): ?>
                            <div class="error-message">
                                <p><?php echo htmlspecialchars($registrationError); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <form id="interest-form" method="POST" action="<?php echo isset($_GET['view']) ? 'index.php?view=' . $_GET['view'] : 'index.php'; ?>">
                            <input type="hidden" name="programme_id" value="<?php echo isset($_GET['view']) ? $_GET['view'] : ''; ?>">
                            <input type="hidden" name="register_interest" value="1">
                            
                            <div class="form-group">
                                <label for="student-name">Your Name:</label>
                                <input type="text" id="student-name" name="student_name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="student-email">Email Address:</label>
                                <input type="email" id="student-email" name="student_email" required>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="primary-btn">Submit</button>
                                <a href="index.php<?php echo isset($_GET['view']) ? '?view=' . $_GET['view'] : ''; ?>" class="secondary-btn">Cancel</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>University Course Hub</h3>
                <p>Helping you find the perfect course for your future</p>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php#home">Home</a></li>
                    <li><a href="index.php#programmes">Programmes</a></li>
                    <li><a href="index.php#staff">Staff</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3 class="contact" id="contact">Contact Us</h3>
                <p>Email: admissions@university.ac.uk</p>
                <p>Phone: +44 (0)123 456 7890</p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 University Course Hub. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>