<?php
session_start();
require_once 'config.php';

// Input validation and sanitization
$name = sanitizeInput($_POST['name'] ?? '');
$gender = sanitizeInput($_POST['gender'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$phone = sanitizeInput($_POST['phone'] ?? '');

// Validate required fields
if (empty($name) || empty($gender) || !$email || empty($phone)) {
    die('<div style="color: red; text-align: center; margin-top: 100px; font-family: Arial; font-size: 18px;">
        ❌ Invalid input data. Please check all fields and try again.
        <br><br><a href="newuser.html" style="color: #ffcc00;">← Go Back</a>
    </div>');
}

// Additional validation
if (strlen($name) < 2 || strlen($name) > 50) {
    die('<div style="color: red; text-align: center; margin-top: 100px; font-family: Arial;">❌ Name must be between 2 and 50 characters.</div>');
}

if (!in_array($gender, ['male', 'female', 'other'])) {
    die('<div style="color: red; text-align: center; margin-top: 100px; font-family: Arial;">❌ Please select a valid gender option.</div>');
}

try {
    $mysqli = getDbConnection();
    
    // Check if email already exists
    $checkStmt = $mysqli->prepare("SELECT id FROM customers WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        die('<div style="color: orange; text-align: center; margin-top: 100px; font-family: Arial; font-size: 18px;">
            ⚠️ This email is already registered. 
            <br><br><a href="validation.html" style="color: #ffcc00;">Login here</a>
        </div>');
    }
    
    // Insert new user
    $stmt = $mysqli->prepare("INSERT INTO customers (name, gender, email, phone, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $name, $gender, $email, $phone);
    
    if ($stmt->execute()) {
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        include 'thankyou.html';
    } else {
        throw new Exception('Failed to register user');
    }
    
    $stmt->close();
    $checkStmt->close();
    $mysqli->close();
    
} catch (Exception $e) {
    if (DEBUG_MODE) {
        die('Error: ' . $e->getMessage());
    } else {
        die('<div style="color: red; text-align: center; margin-top: 100px; font-family: Arial;">❌ Registration failed. Please try again later.</div>');
    }
}
?>