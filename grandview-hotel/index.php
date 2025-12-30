<?php
require_once 'config.php';

// Get available rooms for display
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE is_available = 1 ORDER BY price ASC");
$stmt->execute();
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent feedback for homepage
$feedback_stmt = $pdo->prepare("SELECT f.rating, f.comment, u.full_name FROM feedback f 
                               JOIN users u ON f.user_id = u.id 
                               WHERE f.is_public = 1 
                               ORDER BY f.created_at DESC LIMIT 3");
$feedback_stmt->execute();
$reviews = $feedback_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Luxury Accommodations</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <h1><?php echo SITE_NAME; ?></h1>
                </div>
                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-link active">Home</a></li>
                    <li><a href="rooms.php" class="nav-link">Rooms</a></li>
                    <li><a href="booking.php" class="nav-link">Book Now</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                        <li><a href="logout.php" class="nav-link">Logout</a></li>
                        <li><span class="user-greeting">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span></li>
                    <?php else: ?>
                        <li><a href="login.php" class="nav-link">Login</a></li>
                        <li><a href="register.php" class="nav-link">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h2>Experience Luxury at Grandview Hotel</h2>
            <p>Discover unparalleled comfort and elegance in the heart of the city</p>
            <a href="booking.php" class="cta-button">Book Your Stay</a>
        </div>
    </section>

    <!-- Featured Rooms Section -->
    <section class="featured-rooms">
        <div class="container">
            <h2 class="section-title">Our Featured Rooms</h2>
            <div class="rooms-grid">
                <?php foreach(array_slice($rooms, 0, 3) as $room): ?>
                <div class="room-card">
                    <div class="room-image">
                        <img src="images/<?php echo htmlspecialchars($room['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($room['room_type']); ?>" 
                             onerror="this.src='images/default-room.jpg'">
                    </div>
                    <div class="room-info">
                        <h3>Room <?php echo htmlspecialchars($room['room_number']); ?></h3>
                        <p class="room-type"><?php echo ucfirst(htmlspecialchars($room['room_type'])); ?></p>
                        <p class="room-description"><?php echo htmlspecialchars($room['description']); ?></p>
                        <div class="room-amenities">
                            <small><?php echo htmlspecialchars($room['amenities']); ?></small>
                        </div>
                        <div class="room-footer">
                            <span class="price">$<?php echo number_format($room['price'], 2); ?>/night</span>
                            <a href="booking.php?room_id=<?php echo $room['id']; ?>" class="book-button">Book Now</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Customer Reviews Section -->
    <section class="reviews">
        <div class="container">
            <h2 class="section-title">What Our Guests Say</h2>
            <div class="reviews-grid">
                <?php foreach($reviews as $review): ?>
                <div class="review-card">
                    <div class="rating">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?php echo $i <= $review['rating'] ? 'filled' : ''; ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <p class="review-text"><?php echo htmlspecialchars($review['comment']); ?></p>
                    <p class="reviewer-name">- <?php echo htmlspecialchars($review['full_name']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact">
        <div class="container">
            <h2 class="section-title">Contact Us</h2>
            <div class="contact-info">
                <div class="contact-item">
                    <h3>Address</h3>
                    <p>123 Luxury Avenue<br>Downtown District<br>City, State 12345</p>
                </div>
                <div class="contact-item">
                    <h3>Phone</h3>
                    <p>+1 (555) 123-4567</p>
                </div>
                <div class="contact-item">
                    <h3>Email</h3>
                    <p>info@grandviewhotel.com</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 <?php echo SITE_NAME; ?>. All rights reserved.</p>
            <!-- Hidden flag in HTML comment for source code analysis -->
            <!-- flag{source_code_analysis} -->
        </div>
    </footer>
	<?php include 'flag_widget.php'; ?>
</body>
</html>


