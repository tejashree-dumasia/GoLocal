<?php
class Checklist {
    private $conn;
    private $table_name = "checklists";

    // Object Properties
    public $item_id;
    public $trip_id;
    public $item_description;
    public $is_completed;
    public $created_by_user_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all checklist items for a specific trip
    function read() {
        $query = "SELECT
                    c.item_id,
                    c.item_description,
                    c.is_completed,
                    c.created_by_user_id,
                    u.username as created_by_username
                  FROM
                    " . $this->table_name . " c
                    LEFT JOIN users u ON c.created_by_user_id = u.user_id
                  WHERE
                    c.trip_id = ?
                  ORDER BY
                    c.created_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->trip_id);
        $stmt->execute();
        return $stmt;
    }

    // Create a new checklist item
    function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET
                    trip_id = :trip_id,
                    item_description = :item_description,
                    created_by_user_id = :created_by_user_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->trip_id = htmlspecialchars(strip_tags($this->trip_id));
        $this->item_description = htmlspecialchars(strip_tags($this->item_description));
        $this->created_by_user_id = htmlspecialchars(strip_tags($this->created_by_user_id));

        // Bind
        $stmt->bindParam(':trip_id', $this->trip_id);
        $stmt->bindParam(':item_description', $this->item_description);
        $stmt->bindParam(':created_by_user_id', $this->created_by_user_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Update an item's 'is_completed' status
    function updateStatus() {
        $query = "UPDATE " . $this->table_name . "
                  SET is_completed = :is_completed
                  WHERE item_id = :item_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->is_completed = htmlspecialchars(strip_tags($this->is_completed));
        $this->item_id = htmlspecialchars(strip_tags($this->item_id));

        // Bind
        $stmt->bindParam(':is_completed', $this->is_completed);
        $stmt->bindParam(':item_id', $this->item_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete a checklist item
    function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE item_id = :item_id";
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->item_id = htmlspecialchars(strip_tags($this->item_id));

        // Bind
        $stmt->bindParam(':item_id', $this->item_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>