<?php
require_once 'config.php';

$error_message = '';
$success_message = '';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=booking.php');
    exit();
}

$pdo = getDBConnection();

// Get room details if room_id is provided
$selected_room = null;
if (isset($_GET['room_id'])) {
    $room_query = "SELECT * FROM rooms WHERE id = ? AND is_available = 1";
    $room_stmt = $pdo->prepare($room_query);
    $room_stmt->execute([$_GET['room_id']]);
    $selected_room = $room_stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all available rooms for dropdown
$rooms_query = "SELECT * FROM rooms WHERE is_available = 1 ORDER BY room_number";
$rooms_stmt = $pdo->prepare($rooms_query);
$rooms_stmt->execute();
$available_rooms = $rooms_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room_id = $_POST['room_id'] ?? '';
    $check_in = $_POST['check_in'] ?? '';
    $check_out = $_POST['check_out'] ?? '';
    $guests = $_POST['guests'] ?? 1;
    $special_requests = $_POST['special_requests'] ?? '';

    if (empty($room_id) || empty($check_in) || empty($check_out)) {
        $error_message = 'Please fill in all required fields.';
    } else {
        // Get room details for price calculation
        $room_query = "SELECT * FROM rooms WHERE id = ?";
        $room_stmt = $pdo->prepare($room_query);
        $room_stmt->execute([$room_id]);
        $room = $room_stmt->fetch(PDO::FETCH_ASSOC);

        if ($room) {
            // Calculate total price
            $check_in_date = new DateTime($check_in);
            $check_out_date = new DateTime($check_out);
            $nights = $check_in_date->diff($check_out_date)->days;
            $total_price = $nights * $room['price'];

            // Insert booking
            $booking_query = "INSERT INTO bookings (user_id, room_id, check_in, check_out, guests, total_price, special_requests) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)";
            $booking_stmt = $pdo->prepare($booking_query);
            
            if ($booking_stmt->execute([$_SESSION['user_id'], $room_id, $check_in, $check_out, $guests, $total_price, $special_requests])) {
                $booking_id = $pdo->lastInsertId();
                $success_message = "Booking confirmed! Your booking ID is: $booking_id";
            } else {
                $error_message = 'Booking failed. Please try again.';
            }
        } else {
            $error_message = 'Selected room is not available.';
        }
    }
}

// Set minimum date to today
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Room - <?php echo SITE_NAME; ?></title>
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
                    <li><a href="booking.php" class="nav-link active">Book Now</a></li>
                    <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                    <li><a href="logout.php" class="nav-link">Logout</a></li>
                    <li><span class="user-greeting">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span></li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            <h2>Book Your Stay</h2>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                    <p><a href="dashboard.php">View your bookings</a></p>
                </div>
            <?php endif; ?>

            <div class="booking-container">
                <?php if ($selected_room): ?>
                    <div class="selected-room-info">
                        <h3>Selected Room</h3>
                        <div class="room-preview">
                            <h4>Room <?php echo htmlspecialchars($selected_room['room_number']); ?></h4>
                            <p><strong>Type:</strong> <?php echo ucfirst($selected_room['room_type']); ?></p>
                            <p><strong>Price:</strong> $<?php echo number_format($selected_room['price'], 2); ?> per night</p>
                            <p><strong>Max Occupancy:</strong> <?php echo $selected_room['max_occupancy']; ?> guests</p>
                            <p><strong>Amenities:</strong> <?php echo htmlspecialchars($selected_room['amenities']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="booking.php" class="booking-form">
                    <div class="form-group">
                        <label for="room_id">Select Room:</label>
                        <select name="room_id" id="room_id" required onchange="updateRoomInfo()">
                            <option value="">Choose a room...</option>
                            <?php foreach ($available_rooms as $room): ?>
                                <option value="<?php echo $room['id']; ?>" 
                                        data-price="<?php echo $room['price']; ?>"
                                        data-max="<?php echo $room['max_occupancy']; ?>"
                                        <?php echo ($selected_room && $selected_room['id'] == $room['id']) ? 'selected' : ''; ?>>
                                    Room <?php echo $room['room_number']; ?> - <?php echo ucfirst($room['room_type']); ?> 
                                    ($<?php echo number_format($room['price'], 2); ?>/night)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="check_in">Check-in Date:</label>
                            <input type="date" id="check_in" name="check_in" required 
                                   min="<?php echo $today; ?>" onchange="calculateTotal()">
                        </div>
                        
                        <div class="form-group">
                            <label for="check_out">Check-out Date:</label>
                            <input type="date" id="check_out" name="check_out" required 
                                   min="<?php echo $today; ?>" onchange="calculateTotal()">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="guests">Number of Guests:</label>
                        <select name="guests" id="guests" required>
                            <option value="">Select guests...</option>
                            <option value="1">1 Guest</option>
                            <option value="2">2 Guests</option>
                            <option value="3">3 Guests</option>
                            <option value="4">4 Guests</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="special_requests">Special Requests:</label>
                        <textarea name="special_requests" id="special_requests" rows="3" 
                                  placeholder="Any special requests or preferences..."></textarea>
                    </div>

                    <div class="booking-summary">
                        <h4>Booking Summary</h4>
                        <div id="summary-content">
                            <p>Please select a room and dates to see the booking summary.</p>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-large">Confirm Booking</button>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function updateRoomInfo() {
            calculateTotal();
        }

        function calculateTotal() {
            const roomSelect = document.getElementById('room_id');
            const checkIn = document.getElementById('check_in').value;
            const checkOut = document.getElementById('check_out').value;
            const summaryContent = document.getElementById('summary-content');

            if (roomSelect.value && checkIn && checkOut) {
                const selectedOption = roomSelect.options[roomSelect.selectedIndex];
                const pricePerNight = parseFloat(selectedOption.dataset.price);
                const maxOccupancy = parseInt(selectedOption.dataset.max);
                
                const checkInDate = new Date(checkIn);
                const checkOutDate = new Date(checkOut);
                const nights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
                
                if (nights > 0) {
                    const totalPrice = nights * pricePerNight;
                    
                    summaryContent.innerHTML = `
                        <p><strong>Room:</strong> ${selectedOption.text}</p>
                        <p><strong>Check-in:</strong> ${checkIn}</p>
                        <p><strong>Check-out:</strong> ${checkOut}</p>
                        <p><strong>Nights:</strong> ${nights}</p>
                        <p><strong>Rate:</strong> $${pricePerNight.toFixed(2)} per night</p>
                        <p class="total-price"><strong>Total: $${totalPrice.toFixed(2)}</strong></p>
                    `;
                } else {
                    summaryContent.innerHTML = '<p class="error">Check-out date must be after check-in date.</p>';
                }
            }
        }

        // Set minimum checkout date when checkin changes
        document.getElementById('check_in').addEventListener('change', function() {
            const checkInDate = this.value;
            const checkOutInput = document.getElementById('check_out');
            
            if (checkInDate) {
                const minCheckOut = new Date(checkInDate);
                minCheckOut.setDate(minCheckOut.getDate() + 1);
                checkOutInput.min = minCheckOut.toISOString().split('T')[0];
            }
        });
    </script>
</body>
</html>









