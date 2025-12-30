<?php
require_once 'config.php';

$search_results = [];
$search_term = '';
$error_message = '';

// Handle search request
if (isset($_GET['q']) && $_GET['q'] !== '') {
    $search_term = $_GET['q'];
    $pdo = getDBConnection();
    
    $query = "SELECT r.*, 'room' as result_type FROM rooms r 
              WHERE r.room_type LIKE '%$search_term%' 
              OR r.description LIKE '%$search_term%' 
              OR r.amenities LIKE '%$search_term%'
              UNION ALL
              SELECT 
                     u.id as id,
                     CONCAT('User: ', u.username) as room_number, 
                     u.full_name as room_type, 
                     u.email as price, 
                     u.phone as description, 
                     u.role as amenities,
                     u.id as max_occupancy,
                     u.is_active as is_available,
                     'user' as image_url,
                     'user' as result_type
              FROM users u 
              WHERE u.full_name LIKE '%$search_term%' 
              OR u.username LIKE '%$search_term%'
              OR u.email LIKE '%$search_term%'";
    
    try {
        $stmt = $pdo->query($query);
        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Auto-flag if a user result is present (indicating UNION disclosure)
        $hasUserRow = false;
        foreach ($search_results as $r) { if (($r['result_type'] ?? '') === 'user') { $hasUserRow = true; break; } }
        if ($hasUserRow) {
            if (!isset($_SESSION['submitted_flags'])) { $_SESSION['submitted_flags'] = []; }
            $flag = 'flag{sqli_union_success}';
            if (!in_array($flag, $_SESSION['submitted_flags'], true)) { $_SESSION['submitted_flags'][] = $flag; }
            $_SESSION['last_flag_status'] = ['ok' => true, 'flag' => $flag, 'desc' => 'SQLi UNION'];
        }
    } catch (PDOException $e) {
        $error_message = 'An error occurred.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - <?php echo SITE_NAME; ?></title>
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
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                        <li><a href="logout.php" class="nav-link">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="nav-link">Login</a></li>
                        <li><a href="register.php" class="nav-link">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            <h2>Search Results</h2>
            
            <!-- Search Form -->
            <div class="search-container">
                <form method="GET" action="search.php" class="search-form">
                    <input type="text" name="q" placeholder="Search" 
                           value="<?php echo htmlspecialchars($search_term); ?>" class="search-input">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($search_term !== ''): ?>
                <?php if (empty($search_results) && empty($error_message)): ?>
                    <p>No results found.</p>
                <?php else: ?>
                    <div class="search-results">
                        <?php foreach ($search_results as $result): ?>
                            <div class="result-item">
                                <?php if (($result['result_type'] ?? '') === 'room'): ?>
                                    <h4>Room <?php echo htmlspecialchars($result['room_number']); ?></h4>
                                    <p><strong>Type:</strong> <?php echo htmlspecialchars($result['room_type']); ?></p>
                                    <p><strong>Price:</strong> $<?php echo htmlspecialchars($result['price']); ?>/night</p>
                                    <p><strong>Description:</strong> <?php echo htmlspecialchars($result['description']); ?></p>
                                    <p><strong>Amenities:</strong> <?php echo htmlspecialchars($result['amenities']); ?></p>
                                    <a href="booking.php?room_id=<?php echo $result['id']; ?>" class="btn btn-secondary">Book Now</a>
                                <?php else: ?>
                                    <div class="user-result" style="background: #fff3cd; padding: 10px; margin: 10px 0; border-radius: 5px;">
                                        <h4><?php echo htmlspecialchars($result['room_number']); ?></h4>
                                        <p><strong>Name:</strong> <?php echo htmlspecialchars($result['room_type']); ?></p>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($result['price']); ?></p>
                                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($result['description']); ?></p>
                                        <p><strong>Role:</strong> <?php echo htmlspecialchars($result['amenities']); ?></p>
                                        <p><small>User ID: <?php echo htmlspecialchars($result['max_occupancy']); ?></small></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
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


