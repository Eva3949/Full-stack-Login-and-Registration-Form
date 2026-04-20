<?php
// Database Configuration for XAMPP
class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "user_registration_db";
    private $conn;

    // Get database connection
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->database, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }

    // Create database if not exists
    public function createDatabase() {
        try {
            $conn = new PDO("mysql:host=" . $this->host, $this->username, $this->password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql = "CREATE DATABASE IF NOT EXISTS user_registration_db";
            $conn->exec($sql);
            
            return true;
        } catch(PDOException $exception) {
            echo "Database creation error: " . $exception->getMessage();
            return false;
        }
    }
}

// Session Management Class
class SessionManager {
    public static function startSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function setSession($user_id, $email) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['email'] = $email;
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();
    }

    public static function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public static function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    public static function getEmail() {
        return $_SESSION['email'] ?? null;
    }

    public static function logout() {
        session_unset();
        session_destroy();
    }

    public static function checkSessionTimeout() {
        $timeout = 1800; // 30 minutes
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            self::logout();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }
}

// Security Functions
class Security {
    public static function sanitizeInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function validatePhone($phone) {
        // Remove all non-digit characters
        $cleaned = preg_replace('/\D/', '', $phone);
        return strlen($cleaned) >= 10;
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function logLoginAttempt($email, $success) {
        $database = new Database();
        $conn = $database->getConnection();
        
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $sql = "INSERT INTO login_attempts (email, ip_address, success) VALUES (:email, :ip_address, :success)";
        $stmt = $conn->prepare($sql);
        
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':ip_address', $ip_address);
        $stmt->bindParam(':success', $success, PDO::PARAM_BOOL);
        
        return $stmt->execute();
    }

    public static function checkRateLimit($email) {
        $database = new Database();
        $conn = $database->getConnection();
        
        $sql = "SELECT COUNT(*) as attempts FROM login_attempts 
                WHERE email = :email AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND success = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['attempts'] < 5; // Allow max 5 failed attempts in 15 minutes
    }
}

// Response Helper
class Response {
    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function success($message, $data = null) {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    public static function error($message, $statusCode = 400) {
        self::json([
            'success' => false,
            'message' => $message
        ], $statusCode);
    }
}
?>
