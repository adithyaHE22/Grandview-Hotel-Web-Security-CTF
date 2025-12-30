-- Grandview Hotel Database Setup
-- Run this in phpMyAdmin or MySQL command line

CREATE DATABASE IF NOT EXISTS grandview_hotel;
USE grandview_hotel;

-- Users table (contains admin and customer accounts)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Rooms table
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(10) NOT NULL UNIQUE,
    room_type ENUM('single', 'double', 'suite', 'deluxe') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    amenities TEXT,
    max_occupancy INT DEFAULT 2,
    is_available BOOLEAN DEFAULT TRUE,
    image_url VARCHAR(255)
);

-- Bookings table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests INT DEFAULT 1,
    total_price DECIMAL(10,2) NOT NULL,
    booking_status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    special_requests TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (room_id) REFERENCES rooms(id)
);

-- Admin notes table (for internal hotel management)
CREATE TABLE admin_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    admin_id INT NOT NULL,
    note_content TEXT NOT NULL,
    is_private BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

-- Feedback table
CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    booking_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    is_public BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (booking_id) REFERENCES bookings(id)
);

-- Insert sample data
INSERT INTO users (username, password, email, role, full_name, phone) VALUES
('admin', 'admin123', 'admin@grandviewhotel.com', 'admin', 'Hotel Administrator', '+1-555-0100'),
('john_doe', 'password123', 'john@email.com', 'customer', 'John Doe', '+1-555-0101'),
('jane_smith', 'qwerty456', 'jane@email.com', 'customer', 'Jane Smith', '+1-555-0102'),
('mike_wilson', 'letmein789', 'mike@email.com', 'customer', 'Mike Wilson', '+1-555-0103'),
('flag_user', 'flag{user_enum_success}', 'flag@hidden.com', 'customer', 'Flag Hunter', '+1-555-FLAG');

INSERT INTO rooms (room_number, room_type, price, description, amenities, max_occupancy, image_url) VALUES
('101', 'single', 89.99, 'Cozy single room with city view', 'WiFi, TV, Mini-fridge', 1, 'room101.jpg'),
('102', 'double', 129.99, 'Spacious double room with garden view', 'WiFi, TV, Mini-fridge, Balcony', 2, 'room102.jpg'),
('201', 'suite', 199.99, 'Luxury suite with separate living area', 'WiFi, TV, Mini-bar, Jacuzzi, Room service', 4, 'room201.jpg'),
('202', 'deluxe', 299.99, 'Premium deluxe room with ocean view', 'WiFi, TV, Mini-bar, Balcony, Premium amenities', 2, 'room202.jpg'),
('301', 'suite', 249.99, 'Executive suite with conference area', 'WiFi, TV, Mini-bar, Work desk, Premium amenities', 4, 'room301.jpg');

INSERT INTO bookings (user_id, room_id, check_in, check_out, guests, total_price, booking_status, special_requests) VALUES
(2, 1, '2024-11-01', '2024-11-03', 1, 179.98, 'confirmed', 'Late check-in requested'),
(3, 2, '2024-11-05', '2024-11-07', 2, 259.98, 'confirmed', 'Vegetarian breakfast'),
(4, 3, '2024-11-10', '2024-11-12', 3, 399.98, 'pending', 'Anniversary celebration'),
(5, 4, '2024-11-15', '2024-11-17', 2, 599.98, 'confirmed', 'flag{idor_booking_access}');

INSERT INTO admin_notes (booking_id, admin_id, note_content, is_private) VALUES
(1, 1, 'Guest requested extra towels - provided', TRUE),
(2, 1, 'VIP guest - ensure premium service', TRUE),
(3, 1, 'flag{admin_notes_exposed} - Hidden flag in admin notes', TRUE),
(4, 1, 'Special flag booking - contains CTF flag', TRUE);

INSERT INTO feedback (user_id, booking_id, rating, comment, is_public) VALUES
(2, 1, 5, 'Excellent service and clean rooms!', TRUE),
(3, 2, 4, 'Great location but WiFi was slow', TRUE),
(5, 4, 5, 'Perfect stay! flag{xss_feedback_stored}', TRUE);


