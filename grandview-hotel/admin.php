<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$pdo = getDBConnection();

// VULNERABILITY: Broken Access Control & Information Disclosure
// Admin panel exposes sensitive information and has weak access controls

// Get all bookings with admin notes
$bookings_query = "SELECT b.*, r.room_number, r.room_type, u.username, u.full_name, u.email 
                   FROM bookings b 
                   JOIN rooms r ON b.room_id = r.id 
                   JOIN users u ON b.user_id = u.id 
                   ORDER BY b.created_at DESC";
$stmt = $pdo->prepare($bookings_query);
$stmt->execute();
$all_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get admin notes for bookings
$notes_query = "SELECT an.*, b.id as booking_id, u.username as admin_username 
                FROM admin_notes an 
                JOIN bookings b ON an.booking_id = b.id 
                JOIN users u ON an.admin_id = u.id 
                ORDER BY an.created_at DESC";
$notes_stmt = $pdo->prepare($notes_query);
$notes_stmt->execute();
$admin_notes = $notes_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all users (potential information disclosure)
$users_query = "SELECT * FROM users ORDER BY created_at DESC";
$users_stmt = $pdo->prepare($users_query);
$users_stmt->execute();
$all_users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle admin note addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_note'])) {
    $booking_id = $_POST['booking_id'];
    $note_content = $_POST['note_content'];
    $is_private = isset($_POST['is_private']) ? 1 : 0;
    
    $insert_note = "INSERT INTO admin_notes (booking_id, admin_id, note_content, is_private) VALUES (?, ?, ?, ?)";
    $insert_stmt = $pdo->prepare($insert_note);
    $insert_stmt->execute([$booking_id, $_SESSION['user_id'], $note_content, $is_private]);
    
    $success_message = "Admin note added successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo SITE_NAME; ?></title>
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
                    <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                    <li><a href="admin.php" class="nav-link active">Admin Panel</a></li>
                    <li><a href="logout.php" class="nav-link">Logout</a></li>
                    <li><span class="user-greeting">Admin: <?php echo htmlspecialchars($_SESSION['username']); ?></span></li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            <h2>Hotel Administration Panel</h2>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <!-- Admin Statistics -->
            <div class="admin-stats">
                <h3>Quick Statistics</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <h4><?php echo count($all_bookings); ?></h4>
                        <p>Total Bookings</p>
                    </div>
                    <div class="stat-item">
                        <h4><?php echo count($all_users); ?></h4>
                        <p>Registered Users</p>
                    </div>
                    <div class="stat-item">
                        <h4><?php echo count($admin_notes); ?></h4>
                        <p>Admin Notes</p>
                    </div>
                </div>
            </div>

            <!-- All Bookings Management -->
            <div class="bookings-management">
                <h3>All Bookings</h3>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Customer</th>
                                <th>Room</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_bookings as $booking): ?>
                                <tr>
                                    <td><?php echo $booking['id']; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['full_name']); ?><br>
                                        <small><?php echo htmlspecialchars($booking['email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($booking['room_number']); ?> (<?php echo $booking['room_type']; ?>)</td>
                                    <td><?php echo $booking['check_in']; ?></td>
                                    <td><?php echo $booking['check_out']; ?></td>
                                    <td><span class="status status-<?php echo $booking['booking_status']; ?>"><?php echo ucfirst($booking['booking_status']); ?></span></td>
                                    <td>$<?php echo number_format($booking['total_price'], 2); ?></td>
                                    <td>
                                        <button onclick="showNoteForm(<?php echo $booking['id']; ?>)" class="btn btn-sm">Add Note</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Admin Notes Section -->
            <div class="admin-notes-section">
                <h3>Admin Notes (Contains Flags)</h3>
                <div class="notes-container">
                    <?php foreach ($admin_notes as $note): ?>
                        <div class="note-item">
                            <div class="note-header">
                                <strong>Booking #<?php echo $note['booking_id']; ?></strong> - 
                                Added by <?php echo htmlspecialchars($note['admin_username']); ?> 
                                on <?php echo date('M j, Y H:i', strtotime($note['created_at'])); ?>
                                <?php if ($note['is_private']): ?>
                                    <span class="private-badge">Private</span>
                                <?php endif; ?>
                            </div>
                            <div class="note-content">
                                <?php echo htmlspecialchars($note['note_content']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- User Management Section -->
            <div class="users-management">
                <h3>User Management</h3>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Phone</th>
                                <th>Created</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><span class="role-badge role-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td><?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="noteForm" class="note-form" style="display: none;">
                <h4>Add Admin Note</h4>
                <form method="POST">
                    <input type="hidden" id="noteBookingId" name="booking_id" value="">
                    <div class="form-group">
                        <label for="note_content">Note Content:</label>
                        <textarea name="note_content" id="note_content" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_private" checked> Private Note
                        </label>
                    </div>
                    <button type="submit" name="add_note" class="btn btn-primary">Add Note</button>
                    <button type="button" onclick="hideNoteForm()" class="btn btn-secondary">Cancel</button>
                </form>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
	<?php include 'flag_widget.php'; ?>

    <script>
        function showNoteForm(bookingId) {
            document.getElementById('noteBookingId').value = bookingId;
            document.getElementById('noteForm').style.display = 'block';
            document.getElementById('note_content').focus();
        }

        function hideNoteForm() {
            document.getElementById('noteForm').style.display = 'none';
            document.getElementById('note_content').value = '';
        }
    </script>
</body>
</html>


