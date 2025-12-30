<?php
require_once 'config.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Basic validation
    if (empty($username) || empty($password) || empty($email) || empty($full_name)) {
        $error_message = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters long.';
    } else {
        $pdo = getDBConnection();
        
        // Check if username or email already exists
        $check_query = "SELECT COUNT(*) FROM users WHERE username = ? OR email = ?";
        $check_stmt = $pdo->prepare($check_query);
        $check_stmt->execute([$username, $email]);
        
        if ($check_stmt->fetchColumn() > 0) {
            $error_message = 'Username or email already exists.';
        } else {
            // VULNERABILITY: Weak password storage - storing plain text passwords
            $insert_query = "INSERT INTO users (username, password, email, full_name, phone, role) VALUES (?, ?, ?, ?, ?, 'customer')";
            $insert_stmt = $pdo->prepare($insert_query);
            
            if ($insert_stmt->execute([$username, $password, $email, $full_name, $phone])) {
                $success_message = 'Registration successful! You can now log in.';
            } else {
                $error_message = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
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
                    <li><a href="login.php" class="nav-link">Login</a></li>
                    <li><a href="register.php" class="nav-link active">Register</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="auth-form-container">
                <h2>Create Your Account</h2>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success_message); ?>
                        <p><a href="login.php">Click here to login</a></p>
                    </div>
                <?php endif; ?>

                <?php if (!$success_message): ?>
                <form method="POST" action="register.php" class="auth-form">
                    <div class="form-group">
                        <label for="username">Username *:</label>
                        <input type="text" id="username" name="username" required 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name *:</label>
                        <input type="text" id="full_name" name="full_name" required 
                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *:</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone:</label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *:</label>
                        <input type="password" id="password" name="password" required>
                        <small>Minimum 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Register</button>
                </form>
                <?php endif; ?>
                
                <div class="auth-links">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
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
</body>
</html>









