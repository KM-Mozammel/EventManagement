<?php
class UserModel
{
    private $conn;
    private $table = "users";

    public function __construct($dbc)
    {
        $this->conn = $dbc;
    }

    public function register($name, $email, $password, $role = 'user') { // Added default role
        try {
            $query = "INSERT INTO " . $this->table . " (username, password, email, role) VALUES (:name, :password, :email, :role)";
            $stmt = $this->conn->prepare($query);
    
            if ($stmt) {
                $stmt->bindValue(':name', $name, PDO::PARAM_STR);
                $stmt->bindValue(':email', $email, PDO::PARAM_STR);
                $stmt->bindValue(':password', $password, PDO::PARAM_STR);
                $stmt->bindValue(':role', $role, PDO::PARAM_STR);
    
                if ($stmt->execute()) {
                    return true;
                } else {
                    $errorInfo = $stmt->errorInfo();
                    error_log("Error during registration: " . $errorInfo[2]);
                    return false;
                }
            } else {
                $errorInfo = $this->conn->errorInfo();
                error_log("Error preparing statement: " . $errorInfo[2]);
                return false;
            }
            if ($stmt) { $stmt->closeCursor(); }
        } catch (PDOException $e) {
            error_log("Database error during registration: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("General error during registration: " . $e->getMessage());
            return false;
        }
    }

    public function login($email, $password)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}
