<?php
require_once 'config.php';

$error_message = '';
$success_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error_message = 'Please fill in all fields.';
    } else {
        $pdo = getDBConnection();
        
        // VULNERABILITY 1: SQL Injection (OWASP A03:2021)
        // Using direct string concatenation instead of prepared statements
        $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password' AND is_active = 1";
        
        try {
            $result = $pdo->query($query);
            $user = $result->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Successful login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];

                // Auto-flag: SQLi auth bypass patterns detected in username
                if (strpos($username, "'") !== false || stripos($username, '--') !== false || stripos($username, ' or ') !== false) {
                    if (!isset($_SESSION['submitted_flags'])) { $_SESSION['submitted_flags'] = []; }
                    $flag = 'flag{weak_auth_bypass}';
                    if (!in_array($flag, $_SESSION['submitted_flags'], true)) { $_SESSION['submitted_flags'][] = $flag; }
                    $_SESSION['last_flag_status'] = ['ok' => true, 'flag' => $flag, 'desc' => 'SQLi auth bypass'];
                }
                
                // Redirect based on role
                if ($user['role'] == 'admin') {
                    header('Location: admin.php');
                } else {
                    header('Location: dashboard.php');
                }
                exit();
            } else {
                $error_message = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            // Show actual SQL error for demonstration purposes
            $error_message = 'Database Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <h1><a href="index.php"><?php echo SITE_NAME; ?></a></h1>
                </div>
                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-link">Home</a></li>
                    <li><a href="rooms.php" class="nav-link">Rooms</a></li>
                    <li><a href="booking.php" class="nav-link">Book Now</a></li>
                    <li><a href="login.php" class="nav-link active">Login</a></li>
                    <li><a href="register.php" class="nav-link">Register</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="auth-form-container">
                <h2>Login to Your Account</h2>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" class="auth-form">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
                
                <div class="auth-links">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
	<?php include 'flag_widget.php'; ?>
</body>
</html>


