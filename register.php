<?php
require_once 'config.php';

// Start session
SessionManager::startSession();

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get and sanitize input data
    $firstName = Security::sanitizeInput($_POST['firstName'] ?? '');
    $lastName = Security::sanitizeInput($_POST['lastName'] ?? '');
    $email = Security::sanitizeInput($_POST['email'] ?? '');
    $phone = Security::sanitizeInput($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $department = Security::sanitizeInput($_POST['department'] ?? '');
    $gender = Security::sanitizeInput($_POST['gender'] ?? '');
    $hobbies = $_POST['hobbies'] ?? [];
    $otherInfo = Security::sanitizeInput($_POST['otherInfo'] ?? '');

    // Server-side validation
    $errors = [];

    // Validate first name
    if (empty($firstName)) {
        $errors['firstName'] = 'First name is required';
    } elseif (!preg_match('/^[a-zA-Z\s]{2,}$/', $firstName)) {
        $errors['firstName'] = 'First name must contain only letters and be at least 2 characters';
    }

    // Validate last name
    if (empty($lastName)) {
        $errors['lastName'] = 'Last name is required';
    } elseif (!preg_match('/^[a-zA-Z\s]{2,}$/', $lastName)) {
        $errors['lastName'] = 'Last name must contain only letters and be at least 2 characters';
    }

    // Validate email
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!Security::validateEmail($email)) {
        $errors['email'] = 'Please enter a valid email address';
    }

    // Validate phone
    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required';
    } elseif (!Security::validatePhone($phone)) {
        $errors['phone'] = 'Phone number must be at least 10 digits';
    }

    // Validate password
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $password)) {
        $errors['password'] = 'Password must contain uppercase, lowercase, number, and special character';
    }

    // Validate department
    if (empty($department)) {
        $errors['department'] = 'Please select a department';
    }

    // Validate gender
    if (empty($gender)) {
        $errors['gender'] = 'Please select a gender';
    }

    // Validate hobbies
    if (empty($hobbies) || !is_array($hobbies)) {
        $errors['hobbies'] = 'Please select at least one hobby';
    } else {
        $validHobbies = ['reading', 'sport', 'music', 'travel'];
        foreach ($hobbies as $hobby) {
            if (!in_array($hobby, $validHobbies)) {
                $errors['hobbies'] = 'Invalid hobby selected';
                break;
            }
        }
    }

    // Validate other info (if provided)
    if (!empty($otherInfo) && strlen($otherInfo) < 10) {
        $errors['otherInfo'] = 'Other information must be at least 10 characters if provided';
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

        // Check if email already exists
        $checkEmailSql = "SELECT id FROM users WHERE email = :email";
        $checkStmt = $conn->prepare($checkEmailSql);
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            Response::error('Email already registered', 409);
        }

        // Hash password
        $hashedPassword = Security::hashPassword($password);

        // Convert hobbies array to string
        $hobbiesString = implode(',', $hobbies);

        // Insert new user
        $sql = "INSERT INTO users (first_name, last_name, email, phone, password, department, gender, hobbies, other_info) 
                VALUES (:first_name, :last_name, :email, :phone, :password, :department, :gender, :hobbies, :other_info)";
        
        $stmt = $conn->prepare($sql);
        
        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':last_name', $lastName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':department', $department);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':hobbies', $hobbiesString);
        $stmt->bindParam(':other_info', $otherInfo);

        if ($stmt->execute()) {
            $userId = $conn->lastInsertId();
            
            // Log successful registration
            error_log("New user registered: $email (ID: $userId)");
            
            // Set session for auto-login after registration
            SessionManager::setSession($userId, $email);
            
            Response::success('Registration successful! Redirecting to login...', [
                'userId' => $userId,
                'redirect' => 'LOGIN.html'
            ]);
        } else {
            Response::error('Registration failed. Please try again.');
        }

    } catch(PDOException $exception) {
        error_log("Registration Error: " . $exception->getMessage());
        Response::error('Database error. Please try again later.', 500);
    }

} else {
    // Handle GET request - show registration page
    Response::error('Method not allowed', 405);
}
?>
