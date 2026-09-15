<?php

class LotTask {
    private $conn;
    private $table_name = "lot_tasks";

    public $id;
    public $date_task;
    public $lot_name;
    public $task_name;
    public $workers_desc;
    public $calculated_value;
    public $original_value_notes;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists() {
        $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
            id INT AUTO_INCREMENT PRIMARY KEY,
            date_task DATE NOT NULL,
            lot_name VARCHAR(100) NOT NULL,
            task_name VARCHAR(255) NOT NULL,
            workers_desc VARCHAR(255) NOT NULL,
            calculated_value DECIMAL(12, 2) NOT NULL,
            original_value_notes VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->conn->exec($query);
    }

    // Read all lot tasks
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY date_task DESC, id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read single lot task
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->date_task = $row['date_task'];
            $this->lot_name = $row['lot_name'];
            $this->task_name = $row['task_name'];
            $this->workers_desc = $row['workers_desc'];
            $this->calculated_value = $row['calculated_value'];
            $this->original_value_notes = $row['original_value_notes'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    // Create lot task
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    date_task=:date_task,
                    lot_name=:lot_name,
                    task_name=:task_name,
                    workers_desc=:workers_desc,
                    calculated_value=:calculated_value,
                    original_value_notes=:original_value_notes";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->date_task = htmlspecialchars(strip_tags($this->date_task));
        $this->lot_name = htmlspecialchars(strip_tags($this->lot_name));
        $this->task_name = htmlspecialchars(strip_tags($this->task_name));
        $this->workers_desc = htmlspecialchars(strip_tags($this->workers_desc));
        $this->calculated_value = htmlspecialchars(strip_tags($this->calculated_value));
        $this->original_value_notes = htmlspecialchars(strip_tags($this->original_value_notes));

        // Bind
        $stmt->bindParam(":date_task", $this->date_task);
        $stmt->bindParam(":lot_name", $this->lot_name);
        $stmt->bindParam(":task_name", $this->task_name);
        $stmt->bindParam(":workers_desc", $this->workers_desc);
        $stmt->bindParam(":calculated_value", $this->calculated_value);
        $stmt->bindParam(":original_value_notes", $this->original_value_notes);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Update lot task
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    date_task=:date_task,
                    lot_name=:lot_name,
                    task_name=:task_name,
                    workers_desc=:workers_desc,
                    calculated_value=:calculated_value,
                    original_value_notes=:original_value_notes
                WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);

        $this->date_task = htmlspecialchars(strip_tags($this->date_task));
        $this->lot_name = htmlspecialchars(strip_tags($this->lot_name));
        $this->task_name = htmlspecialchars(strip_tags($this->task_name));
        $this->workers_desc = htmlspecialchars(strip_tags($this->workers_desc));
        $this->calculated_value = htmlspecialchars(strip_tags($this->calculated_value));
        $this->original_value_notes = htmlspecialchars(strip_tags($this->original_value_notes));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":date_task", $this->date_task);
        $stmt->bindParam(":lot_name", $this->lot_name);
        $stmt->bindParam(":task_name", $this->task_name);
        $stmt->bindParam(":workers_desc", $this->workers_desc);
        $stmt->bindParam(":calculated_value", $this->calculated_value);
        $stmt->bindParam(":original_value_notes", $this->original_value_notes);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete lot task
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
