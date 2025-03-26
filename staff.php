<?php
include("database_connection.php");


require_once 'functions.php';

$pageTitle = "Staff Members";
$staffMembers = getAllStaff($pdo);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Staff - Student Course Hub</title>
    <link rel="stylesheet" href="styles_staff_dashboard.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        tr:hover { background-color: #f5f5f5; }
        .action-btns a { 
            display: inline-block; 
            padding: 5px 10px; 
            margin: 0 5px; 
            text-decoration: none; 
            border-radius: 4px; 
        }
        .view-btn { background-color: #4CAF50; color: white; }
        .edit-btn { background-color: #2196F3; color: white; }
        .delete-btn { background-color: #f44336; color: white; }
        .add-btn { 
            display: inline-block; 
            padding: 10px 15px; 
            background-color: #4CAF50; 
            color: white; 
            text-decoration: none; 
            border-radius: 4px; 
            margin-bottom: 20px;
        }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 12px;
            margin-right: 5px;
        }
        .module-leader { background-color: #FFC107; color: #000; }
        .programme-leader { background-color: #9C27B0; color: #fff; }
    </style>
</head>
<body>
    <header>
        <a href="index.php"><h1 style="color: white;">Student Course Hub</h1></a>
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
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Roles</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staffMembers as $staff): 
                    $modules = getStaffModules($pdo, $staff['StaffID']);
                    $programmes = getStaffProgrammes($pdo, $staff['StaffID']);
                ?>
                <tr>
                    <td><?= $staff['StaffID'] ?></td>
                    <td><?= htmlspecialchars($staff['Name']) ?></td>
                    <td>
                        <?php if (!empty($modules)): ?>
                            <span class="badge module-leader">Module Leader (<?= count($modules) ?>)</span>
                        <?php endif; ?>
                        <?php if (!empty($programmes)): ?>
                            <span class="badge programme-leader">Programme Leader (<?= count($programmes) ?>)</span>
                        <?php endif; ?>
                    </td>
                    <td class="action-btns">
                        <a href="view_staff.php?id=<?= $staff['StaffID'] ?>" class="view-btn">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
