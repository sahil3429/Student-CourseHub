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

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="interested_students_export_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// CSV column headers
fputcsv($output, array('Student Name', 'Email', 'Programme', 'Registration Date'));

try {
    // Fetch interested students with programme information
    $stmt = $conn->query("
        SELECT i.StudentName, i.Email, p.ProgrammeName, i.RegisteredAt 
        FROM InterestedStudents i
        JOIN Programmes p ON i.ProgrammeID = p.ProgrammeID
        ORDER BY p.ProgrammeName, i.RegisteredAt DESC
    ");
    
    // Write each row to the CSV file
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Format date nicely
        $formattedDate = date('Y-m-d H:i', strtotime($row['RegisteredAt']));
        
        fputcsv($output, array(
            $row['StudentName'],
            $row['Email'],
            $row['ProgrammeName'],
            $formattedDate
        ));
    }
    
} catch(PDOException $e) {
    // In case of error, write error to CSV
    fputcsv($output, array('Error fetching student data: ' . $e->getMessage()));
} finally {
    // Close the file handle
    fclose($output);
}

// No need for any HTML content as we're generating a download
exit();
?>