<?php
class Setting {
    private $conn;
    private $table_name = "settings";

    public $id;
    public $admin_name;
    public $admin_email;
    public $daily_rate;
    public $currency;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " LIMIT 1";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($row) {
                $this->id = $row['id'];
                $this->admin_name = $row['admin_name'];
                $this->admin_email = $row['admin_email'];
                $this->daily_rate = $row['daily_rate'];
                $this->currency = $row['currency'];
                return true;
            }
        } catch (PDOException $e) {
            // Table doesn't exist or other error, fallback to defaults
        }

        // Default fallback values
        $this->admin_name = "Admin Chucho";
        $this->admin_email = "admin@agropagos.com";
        $this->daily_rate = 60000.00;
        $this->currency = "Peso Colombiano (COP)";
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET admin_name=:admin_name, admin_email=:admin_email, daily_rate=:daily_rate, currency=:currency 
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);

        $this->admin_name = htmlspecialchars(strip_tags($this->admin_name));
        $this->admin_email = htmlspecialchars(strip_tags($this->admin_email));
        $this->daily_rate = htmlspecialchars(strip_tags($this->daily_rate));
        $this->currency = htmlspecialchars(strip_tags($this->currency));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":admin_name", $this->admin_name);
        $stmt->bindParam(":admin_email", $this->admin_email);
        $stmt->bindParam(":daily_rate", $this->daily_rate);
        $stmt->bindParam(":currency", $this->currency);
        $stmt->bindParam(":id", $this->id);

        try {
            if($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            return false;
        }
        return false;
    }
}
?>
