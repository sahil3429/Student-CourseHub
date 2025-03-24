<?php
// Start session
session_start();

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

// Check if coming from logout
$show_logout_message = isset($_GET['logout']) && $_GET['logout'] === 'success';

// Initialize variables
$error = "";
$username = "";
$userType = ""; // To store which type of user is logging in

// Create necessary tables if they don't exist
try {
    // Admin users table
    $conn->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Staff users table
    $conn->exec("CREATE TABLE IF NOT EXISTS staff_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        department VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Student users table
    $conn->exec("CREATE TABLE IF NOT EXISTS student_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        student_id VARCHAR(20),
        major VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch(PDOException $e) {
    die("Database setup error: " . $e->getMessage());
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $userType = isset($_POST["user_type"]) ? $_POST["user_type"] : "student"; // Default to student if not set
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        // Authenticate based on user type
        try {
            $table = "";
            $redirectPage = "";
            
            switch ($userType) {
                case "admin":
                    $table = "admin_users";
                    $redirectPage = "admin_dashboard.php";
                    $sessionPrefix = "admin";
                    break;
                case "staff":
                    $table = "staff_users";
                    $redirectPage = "staff_dashboard.php";
                    $sessionPrefix = "staff";
                    break;
                case "student":
                    $table = "student_users";
                    $redirectPage = "student_dashboard.php";
                    $sessionPrefix = "student";
                    break;
                default:
                    $error = "Invalid user type";
                    break;
            }
            
            if (!empty($table)) {
                // Check user credentials
                $stmt = $conn->prepare("SELECT id, username, password, name FROM $table WHERE username = :username");
                $stmt->bindParam(':username', $username);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (password_verify($password, $user["password"])) {
                        // Authentication successful - create session
                        $_SESSION[$sessionPrefix . "_logged_in"] = true;
                        $_SESSION[$sessionPrefix . "_id"] = $user["id"];
                        $_SESSION[$sessionPrefix . "_username"] = $user["username"];
                        $_SESSION[$sessionPrefix . "_name"] = $user["name"];
                        $_SESSION["user_type"] = $userType; // Store user type in session
                        
                        // Redirect to appropriate dashboard
                        header("Location: $redirectPage");
                        exit();
                    } else {
                        $error = "Invalid username or password";
                    }
                } else {
                    $error = "Invalid username or password";
                }
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
    <title>Login - University Course Hub</title>
    <link rel="stylesheet" href="styles_stafflogin.css">
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        
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
            <div class="user-type-selector">
                <div class="user-type-option">
                    <input type="radio" id="student" name="user_type" value="student" <?php echo ($userType == "student" || empty($userType)) ? "checked" : ""; ?>>
                    <label for="student">Student</label>
                </div>
                <div class="user-type-option">
                    <input type="radio" id="staff" name="user_type" value="staff" <?php echo ($userType == "staff") ? "checked" : ""; ?>>
                    <label for="staff">Staff</label>
                </div>
                <div class="user-type-option">
                    <input type="radio" id="admin" name="user_type" value="admin" <?php echo ($userType == "admin") ? "checked" : ""; ?>>
                    <label for="admin">Admin</label>
                </div>
            </div>
            
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