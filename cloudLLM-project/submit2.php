<?php
session_start();
require_once 'config.php';

// Input validation and sanitization
$userInput = sanitizeInput($_POST['Name'] ?? '');

if (empty($userInput) || strlen($userInput) < 2) {
    die('<div style="color: red; font-size: 24px; font-weight: bold; text-align: center; margin-top: 100px; font-family: Segoe UI, Tahoma, sans-serif;">
        ❌ Please enter a valid name (minimum 2 characters).
        <br><br><a href="validation.html" style="color: #ffcc00; text-decoration: none;">← Try again</a>
    </div>');
}

try {
    $conn = getDbConnection();
    
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, name, email FROM customers WHERE name = ?");
    $stmt->bind_param("s", $userInput);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if the username exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['login_time'] = time();
        
        include 'thankyou.html';
    } else {
        echo '<div style="
            color: red;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin-top: 100px;
            font-family: Segoe UI, Tahoma, sans-serif;
            padding: 20px;
        ">
            ❌ User not found.<br>
            Please check your name and try again.
            <br><br>
            <div style="font-size: 16px; margin-top: 20px;">
                <a href="validation.html" style="color: #ffcc00; text-decoration: none; margin-right: 20px;">← Try again</a>
                <a href="newuser.html" style="color: #ff9900; text-decoration: none;">Sign up here →</a>
            </div>
        </div>';
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    if (DEBUG_MODE) {
        die('Error: ' . $e->getMessage());
    } else {
        die('<div style="color: red; text-align: center; margin-top: 100px; font-family: Arial;">❌ Login failed. Please try again later.</div>');
    }
}
?>
