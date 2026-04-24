<?php
require_once 'config.php';

// Database Setup Script - Creates database and tables automatically
class DatabaseSetup {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "user_registration_db";
    private $conn;

    public function __construct() {
        $this->createDatabase();
        $this->createTables();
        $this->checkSetup();
    }

    private function createDatabase() {
        try {
            // Connect without database first
            $this->conn = new PDO("mysql:host=" . $this->host, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database
            $sql = "CREATE DATABASE IF NOT EXISTS " . $this->database;
            $this->conn->exec($sql);
            
            echo "<div style='color: green; padding: 10px; margin: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px;'>";
            echo "✓ Database '{$this->database}' created successfully or already exists.";
            echo "</div>";
            
        } catch(PDOException $exception) {
            echo "<div style='color: red; padding: 10px; margin: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px;'>";
            echo "✗ Database creation error: " . $exception->getMessage();
            echo "</div>";
            die();
        }
    }

    private function createTables() {
        try {
            // Connect to the database
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->database, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create users table
            $this->createUsersTable();
            
            // Create login_attempts table
            $this->createLoginAttemptsTable();
            
            // Create user_sessions table
            $this->createUserSessionsTable();
            
            echo "<div style='color: green; padding: 10px; margin: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px;'>";
            echo "✓ All tables created successfully.";
            echo "</div>";
            
        } catch(PDOException $exception) {
            echo "<div style='color: red; padding: 10px; margin: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px;'>";
            echo "✗ Table creation error: " . $exception->getMessage();
            echo "</div>";
            die();
        }
    }

    private function createUsersTable() {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            phone VARCHAR(20) NOT NULL,
            password VARCHAR(255) NOT NULL,
            department ENUM('cs', 'se', 'civil', 'mech') NOT NULL,
            gender ENUM('male', 'female', 'other') NOT NULL,
            hobbies SET('reading', 'sport', 'music', 'travel') NOT NULL,
            other_info TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_active BOOLEAN DEFAULT TRUE
        )";
        
        $this->conn->exec($sql);
        
        // Create indexes
        $this->conn->exec("CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)");
        $this->conn->exec("CREATE INDEX IF NOT EXISTS idx_users_created_at ON users(created_at)");
        $this->conn->exec("CREATE INDEX IF NOT EXISTS idx_users_is_active ON users(is_active)");
        
        echo "<div style='color: blue; padding: 5px; margin: 5px;'>";
        echo "• Users table created/verified.";
        echo "</div>";
    }

    private function createLoginAttemptsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            success BOOLEAN DEFAULT FALSE
        )";
        
        $this->conn->exec($sql);
        
        // Create indexes
        $this->conn->exec("CREATE INDEX IF NOT EXISTS idx_login_email ON login_attempts(email)");
        $this->conn->exec("CREATE INDEX IF NOT EXISTS idx_login_attempt_time ON login_attempts(attempt_time)");
        
        echo "<div style='color: blue; padding: 5px; margin: 5px;'>";
        echo "• Login attempts table created/verified.";
        echo "</div>";
    }

    private function createUserSessionsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS user_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            session_id VARCHAR(255) UNIQUE NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP DEFAULT (DATE_ADD(NOW(), INTERVAL 30 MINUTE)),
            is_active BOOLEAN DEFAULT TRUE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        
        $this->conn->exec($sql);
        
        // Create indexes
        $this->conn->exec("CREATE INDEX IF NOT EXISTS idx_session_id ON user_sessions(session_id)");
        $this->conn->exec("CREATE INDEX IF NOT EXISTS idx_session_user_id ON user_sessions(user_id)");
        
        echo "<div style='color: blue; padding: 5px; margin: 5px;'>";
        echo "• User sessions table created/verified.";
        echo "</div>";
    }

    private function checkSetup() {
        try {
            // Test database connection
            $database = new Database();
            $conn = $database->getConnection();
            
            if ($conn) {
                echo "<div style='color: green; padding: 15px; margin: 15px; background: #d4edda; border: 2px solid #28a745; border-radius: 8px; font-weight: bold;'>";
                echo "🎉 SETUP COMPLETE! Database and tables are ready.";
                echo "</div>";
                
                echo "<div style='padding: 15px; margin: 15px; background: #e2e3e5; border: 1px solid #d6d8db; border-radius: 8px;'>";
                echo "<h3>Next Steps:</h3>";
                echo "<ol>";
                echo "<li><a href='registration.html'>Go to Registration Form</a></li>";
                echo "<li><a href='LOGIN.html'>Go to Login Form</a></li>";
                echo "<li>Delete this setup.php file for security</li>";
                echo "</ol>";
                echo "</div>";
                
                // Check if there are any users
                $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
                $result = $stmt->fetch();
                
                if ($result['count'] == 0) {
                    echo "<div style='color: orange; padding: 10px; margin: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px;'>";
                    echo "ℹ️ No users found. You should register a test user first.";
                    echo "</div>";
                } else {
                    echo "<div style='color: blue; padding: 10px; margin: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px;'>";
                    echo "ℹ️ Found " . $result['count'] . " user(s) in the database.";
                    echo "</div>";
                }
            }
            
        } catch(Exception $e) {
            echo "<div style='color: red; padding: 10px; margin: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px;'>";
            echo "✗ Setup verification failed: " . $e->getMessage();
            echo "</div>";
        }
    }
}

// Run the setup
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
if ($requestMethod === 'GET') {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Database Setup</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 800px;
                margin: 50px auto;
                padding: 20px;
                background: #f8f9fa;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            }
            h1 {
                color: #333;
                text-align: center;
                margin-bottom: 30px;
            }
            .btn {
                background: #007bff;
                color: white;
                padding: 12px 30px;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                font-size: 16px;
                display: block;
                margin: 20px auto;
            }
            .btn:hover {
                background: #0056b3;
            }
            .warning {
                background: #fff3cd;
                border: 1px solid #ffeaa7;
                color: #856404;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🗄️ Database Setup</h1>
            
            
            <p>This setup will:</p>
            <ul>
                <li>Create database: <strong>user_registration_db</strong></li>
                <li>Create users table with all necessary fields</li>
                <li>Create login_attempts table for security</li>
                <li>Create user_sessions table for session management</li>
                <li>Create proper indexes for performance</li>
            </ul>
            
            <form method="post">
                <button type="submit" class="btn" name="setup">Setup Database</button>
            </form>
        </div>
    </body>
    </html>
    <?php
} elseif ($requestMethod === 'POST' && isset($_POST['setup'])) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Database Setup Progress</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 800px;
                margin: 50px auto;
                padding: 20px;
                background: #f8f9fa;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            }
            h1 {
                color: #333;
                text-align: center;
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🔧 Database Setup in Progress...</h1>
            <?php
            new DatabaseSetup();
            ?>
        </div>
    </body>
    </html>
    <?php
} elseif ($requestMethod === 'CLI') {
    // CLI Mode - Run setup directly
    echo "=== Database Setup (CLI Mode) ===\n\n";
    new DatabaseSetup();
    echo "\n=== Setup Complete ===\n";
}
?>
