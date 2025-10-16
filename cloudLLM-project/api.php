<?php
/**
 * Aviz Academy API Endpoint
 * Author: Avinash Reddy Thipparthi
 * 
 * Simple REST API for future mobile app integration
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($path);
            break;
        case 'POST':
            handlePostRequest($path);
            break;
        default:
            sendResponse(405, ['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    sendResponse(500, ['error' => 'Internal server error']);
}

function handleGetRequest($path) {
    switch ($path) {
        case '/stats':
            getStats();
            break;
        case '/courses':
            getCourses();
            break;
        default:
            sendResponse(404, ['error' => 'Endpoint not found']);
    }
}

function handlePostRequest($path) {
    switch ($path) {
        case '/register':
            registerUser();
            break;
        case '/login':
            loginUser();
            break;
        default:
            sendResponse(404, ['error' => 'Endpoint not found']);
    }
}

function getStats() {
    try {
        $conn = getDbConnection();
        
        $stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM customers WHERE status = 'active'");
        $stmt->execute();
        $result = $stmt->get_result();
        $totalUsers = $result->fetch_assoc()['total_users'];
        
        $stmt = $conn->prepare("SELECT COUNT(*) as total_progress FROM course_progress");
        $stmt->execute();
        $result = $stmt->get_result();
        $totalProgress = $result->fetch_assoc()['total_progress'];
        
        $stmt = $conn->prepare("SELECT AVG(progress_percentage) as avg_progress FROM course_progress");
        $stmt->execute();
        $result = $stmt->get_result();
        $avgProgress = round($result->fetch_assoc()['avg_progress'], 2);
        
        sendResponse(200, [
            'total_users' => (int)$totalUsers,
            'total_enrollments' => (int)$totalProgress,
            'average_progress' => (float)$avgProgress,
            'platform' => 'Aviz Academy',
            'instructor' => 'Avinash Reddy Thipparthi'
        ]);
        
        $stmt->close();
        $conn->close();
        
    } catch (Exception $e) {
        sendResponse(500, ['error' => 'Failed to fetch stats']);
    }
}

function getCourses() {
    $courses = [
        [
            'id' => 1,
            'name' => 'AWS Fundamentals',
            'description' => 'Master core AWS services including EC2, S3, VPC, and IAM',
            'duration' => '40 hours',
            'modules' => 12,
            'difficulty' => 'Beginner',
            'icon' => 'fab fa-aws',
            'color' => '#ff9900'
        ],
        [
            'id' => 2,
            'name' => 'DevOps & CI/CD',
            'description' => 'Build automated deployment pipelines with CodePipeline and Docker',
            'duration' => '35 hours',
            'modules' => 10,
            'difficulty' => 'Intermediate',
            'icon' => 'fas fa-code-branch',
            'color' => '#00d4ff'
        ],
        [
            'id' => 3,
            'name' => 'Cloud Security',
            'description' => 'Implement security best practices in AWS environments',
            'duration' => '30 hours',
            'modules' => 8,
            'difficulty' => 'Advanced',
            'icon' => 'fas fa-shield-alt',
            'color' => '#00ff88'
        ]
    ];
    
    sendResponse(200, [
        'courses' => $courses,
        'total_courses' => count($courses)
    ]);
}

function registerUser() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['name'], $input['email'], $input['gender'], $input['phone'])) {
        sendResponse(400, ['error' => 'Missing required fields']);
        return;
    }
    
    $name = sanitizeInput($input['name']);
    $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
    $gender = sanitizeInput($input['gender']);
    $phone = sanitizeInput($input['phone']);
    
    if (!$email || !in_array($gender, ['male', 'female', 'other'])) {
        sendResponse(400, ['error' => 'Invalid input data']);
        return;
    }
    
    try {
        $conn = getDbConnection();
        
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            sendResponse(409, ['error' => 'Email already registered']);
            return;
        }
        
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO customers (name, gender, email, phone, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $name, $gender, $email, $phone);
        
        if ($stmt->execute()) {
            $userId = $conn->insert_id;
            sendResponse(201, [
                'message' => 'User registered successfully',
                'user_id' => $userId,
                'name' => $name,
                'email' => $email
            ]);
        } else {
            sendResponse(500, ['error' => 'Registration failed']);
        }
        
        $stmt->close();
        $conn->close();
        
    } catch (Exception $e) {
        sendResponse(500, ['error' => 'Database error']);
    }
}

function loginUser() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['name'])) {
        sendResponse(400, ['error' => 'Name is required']);
        return;
    }
    
    $name = sanitizeInput($input['name']);
    
    try {
        $conn = getDbConnection();
        
        $stmt = $conn->prepare("SELECT id, name, email, created_at FROM customers WHERE name = ? AND status = 'active'");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            sendResponse(200, [
                'message' => 'Login successful',
                'user' => $user
            ]);
        } else {
            sendResponse(401, ['error' => 'User not found']);
        }
        
        $stmt->close();
        $conn->close();
        
    } catch (Exception $e) {
        sendResponse(500, ['error' => 'Database error']);
    }
}

function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit();
}
?>