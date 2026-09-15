<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password_hash;

    public function __construct($db) {
        $this->conn = $db;
        if ($this->conn !== null) {
            $this->ensureTableExists();
        }
    }

    private function ensureTableExists() {
        try {
            $this->conn->query("SELECT 1 FROM " . $this->table_name . " LIMIT 1");
        } catch (PDOException $e) {
            // Table doesn't exist, create it and seed it
            $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $this->conn->exec($query);

            $insert = "INSERT INTO " . $this->table_name . " (username, password_hash) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')";
            $this->conn->exec($insert);
        }
    }

    public function login($username, $password) {
        $query = "SELECT id, username, password_hash FROM " . $this->table_name . " WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password_hash'])) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                return true;
            }
        }
        return false;
    }
}
?>
