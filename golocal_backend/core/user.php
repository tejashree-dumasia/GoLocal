<?php
// Make sure this is at the top of your user.php file
use Firebase\JWT\JWT;

class User {
    // Database connection and table name
    private $conn;
    private $table_name = "users";

    // Object properties
    public $user_id;
    public $username;
    public $email;
    public $password;

    // Constructor with $db as database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new user record
    function create() {
        // Check if email already exists first
        if ($this->emailExists()) {
            return false;
        }

        // Insert query
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    username = :username,
                    email = :email,
                    password_hash = :password_hash";

        // Prepare the query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        // Hash the password before saving
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);

        // Bind the values
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password_hash', $password_hash);

        // Execute the query, also check if it was successful
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // --- ADD THIS NEW LOGIN METHOD ---
    function login() {
        // Query to find user by email
        $query = "SELECT user_id, username, password_hash FROM " . $this->table_name . "
                  WHERE email = :email
                  LIMIT 0,1";

        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize email
        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(':email', $this->email);

        // Execute the query
        $stmt->execute();

        // Check if a user was found
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify the submitted password against the stored hash
            if (password_verify($this->password, $row['password_hash'])) {
                // Passwords match, set user properties
                $this->user_id = $row['user_id'];
                $this->username = $row['username'];
                return true;
            }
        }
        
        // User not found or password incorrect
        return false;
    }
    
    // Check if a given email exists in the database
    function emailExists() {
        $query = "SELECT user_id FROM " . $this->table_name . "
                WHERE email = ?
                LIMIT 0,1";
    
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();
    
        if ($stmt->rowCount() > 0) {
            return true; // Email found
        }
    
        return false; // Email not found
    }
function update() {
        // NOTE: We only allow updating username for now. Email updates are complex.
        // Password updates should ideally be a separate process.
        $query = "UPDATE " . $this->table_name . "
                  SET username = :username
                  WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        // Bind the new username and the user ID
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':user_id', $this->user_id);

        // Execute the query
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
function delete() {
        // Query to delete the user
        // NOTE: Depending on your database foreign key setup, you might need
        // to handle deleting related data (trips, photos) first or set up CASCADE rules.
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        // Bind the user ID
        $stmt->bindParam(':user_id', $this->user_id);

        // Execute the query
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}