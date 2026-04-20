<?php
require_once 'config.php';

// Start session
SessionManager::startSession();

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get and sanitize input data
    $username = Security::sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Server-side validation
    $errors = [];

    // Validate username (can be email or username)
    if (empty($username)) {
        $errors['username'] = 'Username/Email is required';
    }

    // Validate password
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }

    // If there are validation errors, return them
    if (!empty($errors)) {
        Response::error('Validation failed', 400);
        echo json_encode(['errors' => $errors]);
        exit;
    }

    try {
        // Database connection
        $database = new Database();
        $conn = $database->getConnection();

        // Check rate limiting
        if (!Security::checkRateLimit($username)) {
            Response::error('Too many login attempts. Please try again in 15 minutes.', 429);
        }

        // Find user by email or username (assuming username is first_name for this demo)
        $sql = "SELECT id, first_name, last_name, email, phone, password, department, gender, hobbies, other_info, created_at 
                FROM users 
                WHERE email = :username OR first_name = :username 
                AND is_active = 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            // Log failed attempt
            Security::logLoginAttempt($username, false);
            Response::error('Invalid username/email or password');
        }

        $user = $stmt->fetch();

        // Verify password
        if (!Security::verifyPassword($password, $user['password'])) {
            // Log failed attempt
            Security::logLoginAttempt($username, false);
            Response::error('Invalid username/email or password');
        }

        // Password is correct, log successful login
        Security::logLoginAttempt($username, true);

        // Set session
        SessionManager::setSession($user['id'], $user['email']);

        // Store additional user data in session
        $_SESSION['user_data'] = [
            'id' => $user['id'],
            'firstName' => $user['first_name'],
            'lastName' => $user['last_name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'department' => $user['department'],
            'gender' => $user['gender'],
            'hobbies' => $user['hobbies'],
            'otherInfo' => $user['other_info'],
            'createdAt' => $user['created_at']
        ];

        // Log successful login
        error_log("User logged in: {$user['email']} (ID: {$user['id']})");

        Response::success('Login successful!', [
            'user' => [
                'id' => $user['id'],
                'firstName' => $user['first_name'],
                'lastName' => $user['last_name'],
                'email' => $user['email'],
                'department' => $user['department']
            ],
            'redirect' => 'dashboard.html'
        ]);

    } catch(PDOException $exception) {
        error_log("Login Error: " . $exception->getMessage());
        Response::error('Database error. Please try again later.', 500);
    }

} else {
    // Handle GET request - show login page
    Response::error('Method not allowed', 405);
}
?>
