<?php
// Start session
session_start();

// Check if coming from logout
$show_logout_message = isset($_GET['logout']) && $_GET['logout'] === 'success';

// Database connection details
$host = "localhost";
$dbname = "student_course_hub";
$username = "root"; // Update with your database username
$password = ""; // Update with your database password

// Connect to database
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Initialize variables
$error = "";
$username = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        // For this example, we'll create a simple users table if it doesn't exist
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS admin_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                name VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Check if default admin exists, if not create one
            $stmt = $conn->prepare("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'");
            $stmt->execute();
            if ($stmt->fetchColumn() == 0) {
                // Create default admin user (username: admin, password: admin123)
                $default_password = password_hash("admin123", PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO admin_users (username, password, name) VALUES ('admin', :password, 'Administrator')");
                $stmt->bindParam(':password', $default_password);
                $stmt->execute();
            }
            
            // Check user credentials
            $stmt = $conn->prepare("SELECT id, username, password, name FROM admin_users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $user["password"])) {
                    // Authentication successful - create session
                    $_SESSION["admin_logged_in"] = true;
                    $_SESSION["admin_id"] = $user["id"];
                    $_SESSION["admin_username"] = $user["username"];
                    $_SESSION["admin_name"] = $user["name"];
                    
                    // Redirect to admin dashboard
                    header("Location: staff_dashboard.php");
                    exit();
                } else {
                    $error = "Invalid username or password";
                }
            } else {
                $error = "Invalid username or password";
            }
        } catch(PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - University Course Hub</title>
    <link rel="stylesheet" href="stayles_stafflogin.css">
    
</head>
<body>
    <div class="login-container">
        <h1>Staff Login</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($show_logout_message): ?>
    <div id="logout-notification" class="notification">
        You have been successfully logged out.
    </div>

    <script>
        // Show notification
        const notification = document.getElementById('logout-notification');
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        // Hide after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
        }, 3000);
        
        // Remove from DOM after fade out
        setTimeout(() => {
            notification.remove();
        }, 3300);
        
        // Remove the logout parameter from URL
        if (window.history && window.history.replaceState) {
            const url = window.location.href.split('?')[0];
            window.history.replaceState({}, document.title, url);
        }
    </script>
    <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-login">Login</button>
        </form>
        
        <a href="index.php" class="back-link">Back to Course Hub</a>
    </div>
</body>
</html>