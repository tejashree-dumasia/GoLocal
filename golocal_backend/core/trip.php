<?php
class Trip {
    // Database connection and table name
    private $conn;
    private $table_name = "trips";
    private $participants_table = "trip_participants";

    // Object Properties
    public $trip_id;
    public $trip_name;
    public $location;
    public $description;
    public $estimated_cost;
    public $start_datetime;
    public $end_datetime;
    public $admin_id;

    // Constructor with $db as database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create a new trip.
     * The admin_id is set from the logged-in user.
     */
    function create() {
        // The SQL query to insert a new trip record
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    trip_name = :trip_name,
                    location = :location,
                    description = :description,
                    estimated_cost = :estimated_cost,
                    start_datetime = :start_datetime,
                    end_datetime = :end_datetime,
                    admin_id = :admin_id";

        // Prepare the query
        $stmt = $this->conn->prepare($query);

        // Sanitize the input data
        $this->trip_name = htmlspecialchars(strip_tags($this->trip_name));
        $this->location = htmlspecialchars(strip_tags($this->location));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->estimated_cost = htmlspecialchars(strip_tags($this->estimated_cost));
        $this->admin_id = htmlspecialchars(strip_tags($this->admin_id));
        
        // Bind the values from the object properties
        $stmt->bindParam(':trip_name', $this->trip_name);
        $stmt->bindParam(':location', $this->location);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':estimated_cost', $this->estimated_cost);
        $stmt->bindParam(':start_datetime', $this->start_datetime);
        $stmt->bindParam(':end_datetime', $this->end_datetime);
        $stmt->bindParam(':admin_id', $this->admin_id);

        // Execute the query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Read all trips for a given user.
     * This includes trips they created (as admin) and trips they were invited to.
     * @param int $user_id The ID of the logged-in user.
     * @return PDOStatement The statement object to be fetched.
     */
    function read($user_id) {
        // The SQL query to get all trips a user is associated with.
        // We use a LEFT JOIN to get trips where the user is a participant.
        // We use an OR clause to also get trips where the user is the admin.
        $query = "SELECT t.*
                  FROM " . $this->table_name . " t
                  LEFT JOIN " . $this->participants_table . " p
                    ON t.trip_id = p.trip_id
                  WHERE p.user_id = :user_id OR t.admin_id = :user_id
                  GROUP BY t.trip_id
                  ORDER BY t.start_datetime DESC";

        // Prepare the query
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind the user ID
        $user_id = htmlspecialchars(strip_tags($user_id));
        $stmt->bindParam(':user_id', $user_id);
        
        // Execute the query
        $stmt->execute();
        
        return $stmt;
    }
}
?>