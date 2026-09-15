<?php
class Payment {
    private $conn;
    private $table_name = "payments";

    public $id;
    public $worker_id;
    public $payment_date;
    public $week_worked;
    public $days_worked;
    public $amount_earned;
    public $amount_paid;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET worker_id=:worker_id, payment_date=:payment_date, week_worked=:week_worked, days_worked=:days_worked, amount_earned=:amount_earned, amount_paid=:amount_paid";
        $stmt = $this->conn->prepare($query);

        $this->worker_id = htmlspecialchars(strip_tags($this->worker_id));
        $this->payment_date = htmlspecialchars(strip_tags($this->payment_date));
        $this->week_worked = htmlspecialchars(strip_tags($this->week_worked));
        $this->days_worked = htmlspecialchars(strip_tags($this->days_worked));
        $this->amount_earned = htmlspecialchars(strip_tags($this->amount_earned));
        $this->amount_paid = htmlspecialchars(strip_tags($this->amount_paid));

        $stmt->bindParam(":worker_id", $this->worker_id);
        $stmt->bindParam(":payment_date", $this->payment_date);
        $stmt->bindParam(":week_worked", $this->week_worked);
        $stmt->bindParam(":days_worked", $this->days_worked);
        $stmt->bindParam(":amount_earned", $this->amount_earned);
        $stmt->bindParam(":amount_paid", $this->amount_paid);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id = $row['id'];
            $this->worker_id = $row['worker_id'];
            $this->payment_date = $row['payment_date'];
            $this->week_worked = $row['week_worked'];
            $this->days_worked = $row['days_worked'];
            $this->amount_earned = $row['amount_earned'];
            $this->amount_paid = $row['amount_paid'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET payment_date=:payment_date, week_worked=:week_worked, days_worked=:days_worked, amount_earned=:amount_earned, amount_paid=:amount_paid WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->payment_date = htmlspecialchars(strip_tags($this->payment_date));
        $this->week_worked = htmlspecialchars(strip_tags($this->week_worked));
        $this->days_worked = htmlspecialchars(strip_tags($this->days_worked));
        $this->amount_earned = htmlspecialchars(strip_tags($this->amount_earned));
        $this->amount_paid = htmlspecialchars(strip_tags($this->amount_paid));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":payment_date", $this->payment_date);
        $stmt->bindParam(":week_worked", $this->week_worked);
        $stmt->bindParam(":days_worked", $this->days_worked);
        $stmt->bindParam(":amount_earned", $this->amount_earned);
        $stmt->bindParam(":amount_paid", $this->amount_paid);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readByWorker($worker_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE worker_id = ? ORDER BY payment_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $worker_id);
        $stmt->execute();
        return $stmt;
    }

    public function readAllGlobal($date = null, $role = null) {
        $query = "SELECT p.*, w.full_name, w.id_number, w.position 
                  FROM " . $this->table_name . " p 
                  INNER JOIN workers w ON p.worker_id = w.id ";
                  
        $conditions = [];
        if ($date) {
            $conditions[] = "DATE(p.payment_date) = :date";
        }
        if ($role) {
            $conditions[] = "w.position = :role";
        }
        
        if (count($conditions) > 0) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
                  
        $query .= " ORDER BY p.payment_date DESC, p.id DESC";
        $stmt = $this->conn->prepare($query);
        
        if ($date) {
            $stmt->bindParam(':date', $date);
        }
        if ($role) {
            $stmt->bindParam(':role', $role);
        }
        
        $stmt->execute();
        return $stmt;
    }

    public function getMonthlySummary() {
        $query = "SELECT 
                    w.id as worker_id, w.full_name, w.id_number, 
                    DATE_FORMAT(p.payment_date, '%Y-%m') as month,
                    SUM(p.amount_earned) as total_earned,
                    SUM(p.amount_paid) as total_paid,
                    (SUM(p.amount_earned) - SUM(p.amount_paid)) as balance
                  FROM workers w
                  INNER JOIN " . $this->table_name . " p ON w.id = p.worker_id
                  GROUP BY w.id, month
                  ORDER BY month DESC, w.full_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    public function getExpensesByMonth() {
        $query = "SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, 
                         SUM(amount_earned) as total_expense 
                  FROM " . $this->table_name . " 
                  GROUP BY month 
                  ORDER BY month ASC 
                  LIMIT 12";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getExpensesByRole() {
        $query = "SELECT w.position as role, 
                         SUM(p.amount_earned) as total_expense 
                  FROM " . $this->table_name . " p 
                  INNER JOIN workers w ON p.worker_id = w.id 
                  GROUP BY role";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
