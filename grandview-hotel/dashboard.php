<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pdo = getDBConnection();
$user_id = $_SESSION['user_id'];

// Insecure Direct Object Reference (intentional)
$target_user_id = $_GET['user_id'] ?? $user_id;

// Auto-flag for IDOR if another user's profile is accessed
if ((string)$target_user_id !== (string)$user_id) {
    if (!isset($_SESSION['submitted_flags'])) { $_SESSION['submitted_flags'] = []; }
    $flag = 'flag{idor_booking_access}';
    if (!in_array($flag, $_SESSION['submitted_flags'], true)) { $_SESSION['submitted_flags'][] = $flag; }
    $_SESSION['last_flag_status'] = ['ok' => true, 'flag' => $flag, 'desc' => 'IDOR profile access'];
}

$bookings_query = "SELECT b.*, r.room_number, r.room_type, r.price 
                   FROM bookings b 
                   JOIN rooms r ON b.room_id = r.id 
                   WHERE b.user_id = ? 
                   ORDER BY b.created_at DESC";
$stmt = $pdo->prepare($bookings_query);
$stmt->execute([$target_user_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = $pdo->prepare($user_query);
$user_stmt->execute([$target_user_id]);
$viewed_user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Stored XSS remains intentional
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback'])) {
    $booking_id = $_POST['booking_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $feedback_query = "INSERT INTO feedback (user_id, booking_id, rating, comment, is_public) VALUES (?, ?, ?, ?, 1)";
    $feedback_stmt = $pdo->prepare($feedback_query);
    $feedback_stmt->execute([$user_id, $booking_id, $rating, $comment]);
    $success_message = "Feedback submitted.";
}

// NEW: allow any logged-in user to submit general feedback (no booking required)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback_general'])) {
    $rating = $_POST['rating'] ?? null;
    $comment = $_POST['comment'] ?? '';
    $feedback_query = "INSERT INTO feedback (user_id, booking_id, rating, comment, is_public) VALUES (?, NULL, ?, ?, 1)";
    $feedback_stmt = $pdo->prepare($feedback_query);
    $feedback_stmt->execute([$user_id, $rating, $comment]);
    $success_message = "Feedback submitted.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
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
                    <li><a href="search.php" class="nav-link">Search</a></li>
                    <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
                    <?php if($_SESSION['role'] == 'admin'): ?>
                        <li><a href="admin.php" class="nav-link">Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php" class="nav-link">Logout</a></li>
                    <li><span class="user-greeting">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span></li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            <h2>User Dashboard</h2>
            <div style="margin: 10px 0; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <a href="feedback.php" class="btn btn-primary">View Feedback Page</a>
                <form method="GET" action="dashboard.php" style="display: inline-flex; gap: 8px; align-items: center;">
                    <label for="user_id" style="font-weight: 600;">View user_id:</label>
                    <input type="number" min="1" id="user_id" name="user_id" value="<?php echo htmlspecialchars($target_user_id); ?>" style="width: 90px; padding: 6px; border: 2px solid #ecf0f1; border-radius: 6px;">
                    <button type="submit" class="btn btn-secondary">Go</button>
                </form>
                <span style="font-size: 0.9rem; color: #7f8c8d;">Current URL: dashboard.php<?php echo ($target_user_id ? ('?user_id='.(int)$target_user_id) : ''); ?></span>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <div class="profile-section">
                <h3>Profile Information</h3>
                <?php if ($viewed_user): ?>
                    <div class="profile-info">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($viewed_user['full_name']); ?></p>
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($viewed_user['username']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($viewed_user['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($viewed_user['phone']); ?></p>
                        <p><strong>Role:</strong> <?php echo htmlspecialchars($viewed_user['role']); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- NEW: Quick Feedback (no booking required) -->
            <div class="bookings-section">
                <h3>Quick Feedback</h3>
                <div class="feedback-section">
                    <form method="POST" class="feedback-form">
                        <div class="form-group">
                            <label>Rating:</label>
                            <select name="rating" required>
                                <option value="">Select Rating</option>
                                <option value="1">1 Star</option>
                                <option value="2">2 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="5">5 Stars</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Comment:</label>
                            <textarea name="comment" rows="3"></textarea>
                        </div>
                        <button type="submit" name="submit_feedback_general" class="btn btn-secondary">Submit Feedback</button>
                    </form>
                </div>
            </div>

            <div class="bookings-section">
                <h3>Your Bookings</h3>
                <?php if (empty($bookings)): ?>
                    <p>No bookings found.</p>
                    <a href="booking.php" class="btn btn-primary">Make a Booking</a>
                <?php else: ?>
                    <div class="bookings-list">
                        <?php foreach ($bookings as $booking): ?>
                            <div class="booking-item">
                                <div class="booking-header">
                                    <h4>Booking #<?php echo $booking['id']; ?></h4>
                                    <span class="status status-<?php echo $booking['booking_status']; ?>">
                                        <?php echo ucfirst($booking['booking_status']); ?>
                                    </span>
                                </div>
                                <div class="booking-details">
                                    <p><strong>Room:</strong> <?php echo htmlspecialchars($booking['room_number']); ?> 
                                       (<?php echo ucfirst($booking['room_type']); ?>)</p>
                                    <p><strong>Check-in:</strong> <?php echo $booking['check_in']; ?></p>
                                    <p><strong>Check-out:</strong> <?php echo $booking['check_out']; ?></p>
                                    <p><strong>Guests:</strong> <?php echo $booking['guests']; ?></p>
                                    <p><strong>Total Price:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                                    <?php if ($booking['special_requests']): ?>
                                        <p><strong>Special Requests:</strong> <?php echo htmlspecialchars($booking['special_requests']); ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Keep per-booking feedback as well (no completed restriction) -->
                                <div class="feedback-section">
                                    <h5>Leave Feedback for this Booking</h5>
                                    <form method="POST" class="feedback-form">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <div class="form-group">
                                            <label>Rating:</label>
                                            <select name="rating" required>
                                                <option value="">Select Rating</option>
                                                <option value="1">1 Star</option>
                                                <option value="2">2 Stars</option>
                                                <option value="3">3 Stars</option>
                                                <option value="4">4 Stars</option>
                                                <option value="5">5 Stars</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Comment:</label>
                                            <textarea name="comment" rows="3"></textarea>
                                        </div>
                                        <button type="submit" name="submit_feedback" class="btn btn-secondary">Submit Feedback</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
	<?php include 'flag_widget.php'; ?>
</body>
</html>


