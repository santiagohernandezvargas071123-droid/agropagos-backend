<?php
class Worker {
    private $conn;
    private $table_name = "workers";

    public $id;
    public $full_name;
    public $id_number;
    public $address;
    public $position;
    public $status = 'activo';
    public $lote;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll($role = null) {
        $query = "SELECT w.*, 
                  (SELECT SUM(amount_earned) FROM payments WHERE worker_id = w.id) as total_earned,
                  (SELECT SUM(amount_paid) FROM payments WHERE worker_id = w.id) as total_paid
                  FROM " . $this->table_name . " w ";
                  
        if ($role) {
            $query .= " WHERE w.position = :role ";
        }
        
        $query .= " ORDER BY w.full_name ASC";
        
        $stmt = $this->conn->prepare($query);
        
        if ($role) {
            $stmt->bindParam(':role', $role);
        }
        
        $stmt->execute();
        return $stmt;
    }
    
    public function getUniqueRoles() {
        $query = "SELECT DISTINCT position FROM " . $this->table_name . " WHERE position IS NOT NULL AND position != '' ORDER BY position ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $base_roles = ['Abonador', 'Desmalezador', 'Fumigador', 'Jornalero', 'Palero', 'Recolector'];
        $roles = $base_roles;
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!in_array($row['position'], $roles)) {
                $roles[] = $row['position'];
            }
        }
        
        sort($roles);
        return $roles;
    }
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->full_name = $row['full_name'];
            $this->id_number = $row['id_number'];
            $this->address = $row['address'];
            $this->position = $row['position'];
            $this->status = $row['status'] ?? 'activo';
            $this->lote = $row['lote'];
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET full_name=:full_name, id_number=:id_number, address=:address, position=:position, status=:status, lote=:lote";
        $stmt = $this->conn->prepare($query);

        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->id_number = htmlspecialchars(strip_tags($this->id_number));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->position = htmlspecialchars(strip_tags($this->position));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->lote = htmlspecialchars(strip_tags($this->lote ?? ''));

        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":id_number", $this->id_number);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":position", $this->position);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":lote", $this->lote);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET full_name=:full_name, id_number=:id_number, address=:address, position=:position, status=:status, lote=:lote WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->id_number = htmlspecialchars(strip_tags($this->id_number));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->position = htmlspecialchars(strip_tags($this->position));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->lote = htmlspecialchars(strip_tags($this->lote ?? ''));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":id_number", $this->id_number);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":position", $this->position);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":lote", $this->lote);
        $stmt->bindParam(":id", $this->id);

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
}
?>
