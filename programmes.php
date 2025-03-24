<?php
include ("database_connection.php");


// fetching all programmes from the database
function getAllProgrammes($conn) {
    $sql = "SELECT p.ProgrammeID, p.ProgrammeName, p.Description, p.Image, 
            l.LevelName, l.LevelID, s.Name as LeaderName 
            FROM Programmes p
            JOIN Levels l ON p.LevelID = l.LevelID
            JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID
            ORDER BY p.ProgrammeName";
    
    $result = $conn->query($sql);
    $programmes = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $programmes[] = $row;
        }
    }
    return $programmes;
}

function getProgrammeModules($conn, $programmeId, $year) {
    $sql = "SELECT m.ModuleID, m.ModuleName, m.Description 
            FROM ProgrammeModules pm
            JOIN Modules m ON pm.ModuleID = m.ModuleID
            WHERE pm.ProgrammeID = ? AND pm.Year = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $programmeId, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $modules = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $modules[] = $row;
        }
    }
    return $modules;
}

// Get all programmes
$programmes = getAllProgrammes($conn);

$levelFilter = isset($_GET['level']) ? $_GET['level'] : "all";
$searchQuery = isset($_GET['search']) ? $_GET['search'] : "";

// pagination calculations
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Staff - Student Course Hub</title>
    <link rel="stylesheet" href="styles_staff_dashboard.css">
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
        <a href="index.php"><h1 style="color: white;">Student Course Hub</h1></a>
        <p>University of Excellence</p>
    </header>
    
    <nav>
        <ul>
            <li><a href="staff_dashboard.php" class="active">Dashboard</a></li>
            <li><a href="programmes.php">Programmes</a></li>
            <li><a href="staff.php">Staff</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li style="margin-left: auto;"><a href="logout.php" class="logout-btn">Logout</a></li>
        </ul>
    </nav>
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
                        <a href="programmes.php" class="view-details-btn">View Details</a>
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

</body>
</html> 