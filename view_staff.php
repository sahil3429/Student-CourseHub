<?php
include ('database_connection.php');
include 'functions.php';

$staffId = $_GET['id'];
$staff = getStaffById($pdo, $staffId);
$modules = getStaffModules($pdo, $staffId);
$programmes = getStaffProgrammes($pdo, $staffId);

if (!$staff) {
    header("Location: index.php");
    exit();
}

$pageTitle = "Staff Details: " . $staff['Name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="styles_staff_dashboard.css">
    <Style>
        body { 
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0; 
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        h1 { 
            color: #333; 
        }
        .staff-info { 
            background-color: #f9f9f9; 
            padding: 20px; 
            border-radius: 5px; 
        }
        .section { 
            margin-top: 20px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        th, td { 
            padding: 10px; 
            text-align: left; 
            border-bottom: 1px solid #ddd; 
        }
        th { 
            background-color: #f2f2f2; 
        }
        .back-btn { 
            display: inline-block; 
            padding: 8px 15px; 
            background-color: #2196F3; 
            color: white; 
            text-decoration: none; 
            border-radius: 4px; 
            margin-top: 20px;
        }
    </Style>
</head>
<body>
    <header>
        <a href="index.php" style="text-decoration: none;"><h1 style="color: white;">University Course Hub</h1></a>
        <p>University of Excellence</p>
    </header>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="staff_dashboard.php">Dashboard</a></li>
            <!-- <li><a href="programmes.php">Programmes</a></li> -->
            <li><a href="staff.php" class="active">Staff</a></li>
            <!-- <li><a href="contact.php">Contact</a></li> -->
            <li style="margin-left: auto;"><a href="logout.php" class="logout-btn">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <h1><?= $pageTitle ?></h1>
        
        <div class="staff-info">
            <h2>Basic Information</h2>
            <p><strong>Staff ID:</strong> <?= $staff['StaffID'] ?></p>
            <p><strong>Name:</strong> <?= htmlspecialchars($staff['Name']) ?></p>
            
            <div class="section">
                <h2>Modules Led</h2>
                <?php if (!empty($modules)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Module ID</th>
                                <th>Module Name</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modules as $module): ?>
                            <tr>
                                <td><?= $module['ModuleID'] ?></td>
                                <td><?= htmlspecialchars($module['ModuleName']) ?></td>
                                <td><?= htmlspecialchars($module['Description']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>This staff member doesn't lead any modules.</p>
                <?php endif; ?>
            </div>
            
            <div class="section">
                <h2>Programmes Led</h2>
                <?php if (!empty($programmes)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Programme ID</th>
                                <th>Programme Name</th>
                                <th>Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($programmes as $programme): ?>
                            <tr>
                                <td><?= $programme['ProgrammeID'] ?></td>
                                <td><?= htmlspecialchars($programme['ProgrammeName']) ?></td>
                                <td><?= $programme['LevelID'] == 1 ? 'Undergraduate' : 'Postgraduate' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>This staff member doesn't lead any programmes.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <a href="staff.php" class="back-btn">Back to Staff List</a>
    </div>

    <footer>
        <p>© 2025 University of Excellence - University Course Hub</p>
        <p>Contact: <a href="mailto:admissions@university.ac.uk" style="color: white;">admissions@university.ac.uk</a></p>
    </footer>
</body>
</html>