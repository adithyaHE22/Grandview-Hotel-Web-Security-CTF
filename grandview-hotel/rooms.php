<?php
require_once 'config.php';

$pdo = getDBConnection();

// Get all available rooms
$rooms_query = "SELECT * FROM rooms WHERE is_available = 1 ORDER BY price ASC";
$stmt = $pdo->prepare($rooms_query);
$stmt->execute();
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms - <?php echo SITE_NAME; ?></title>
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
                    <li><a href="rooms.php" class="nav-link active">Rooms</a></li>
                    <li><a href="booking.php" class="nav-link">Book Now</a></li>
                    <li><a href="search.php" class="nav-link">Search</a></li>
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
            <h2>Our Rooms & Suites</h2>
            <p class="page-subtitle">Discover comfort and luxury in every room</p>
            
            <div class="rooms-grid">
                <?php foreach($rooms as $room): ?>
                <div class="room-card detailed">
                    <div class="room-image">
                        <img src="images/<?php echo htmlspecialchars($room['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($room['room_type']); ?>" 
                             onerror="this.src='images/default-room.jpg'">
                    </div>
                    <div class="room-info">
                        <h3>Room <?php echo htmlspecialchars($room['room_number']); ?></h3>
                        <p class="room-type"><?php echo ucfirst(htmlspecialchars($room['room_type'])); ?> Room</p>
                        <p class="room-description"><?php echo htmlspecialchars($room['description']); ?></p>
                        
                        <div class="room-details">
                            <div class="detail-item">
                                <strong>Max Occupancy:</strong> <?php echo $room['max_occupancy']; ?> guests
                            </div>
                            <div class="detail-item">
                                <strong>Amenities:</strong> <?php echo htmlspecialchars($room['amenities']); ?>
                            </div>
                        </div>
                        
                        <div class="room-footer">
                            <div class="price-section">
                                <span class="price">$<?php echo number_format($room['price'], 2); ?></span>
                                <span class="price-unit">per night</span>
                            </div>
                            <a href="booking.php?room_id=<?php echo $room['id']; ?>" class="btn btn-primary">Book Now</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
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


