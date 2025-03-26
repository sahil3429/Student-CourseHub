<?php
// test_connection.php
$conn = include 'database_connection.php';
if ($conn) {
    echo "Connected successfully!";
} else {
    echo "Connection failed!";
}
?>