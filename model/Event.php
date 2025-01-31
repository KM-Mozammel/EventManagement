<?php
class EventModel
{
    private $conn;
    private $table = "users";

    public function __construct($dbc)
    {
        $this->conn = $dbc;
    }

    public function CreateEvent($formData)
    {
        try {

            $name = htmlspecialchars($formData['name'], ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars($formData['description'], ENT_QUOTES, 'UTF-8');
            $max_capacity = intval($formData['capacity']);

            $query = "INSERT INTO events (name, description, max_capacity, created_by) 
                  VALUES (:name, :description, :max_capacity, :created_by)";

            $stmt = $this->conn->prepare($query);

            if ($stmt) {
                $stmt->bindValue(':name', $name, PDO::PARAM_STR);
                $stmt->bindValue(':description', $description, PDO::PARAM_STR);
                $stmt->bindValue(':max_capacity', $max_capacity, PDO::PARAM_INT);
                $stmt->bindValue(':created_by', 1, PDO::PARAM_INT);
    
                if ($stmt->execute()) {
                    return true;
                } else {
                    $errorInfo = $stmt->errorInfo();
                    error_log("Error inserting event: " . print_r($errorInfo, true));
                    return ['status' => 'error', 'message' => 'Database error: ' . $errorInfo[2]];
                }
            } else {
                $errorInfo = $this->conn->errorInfo();
                error_log("Error preparing statement: " . print_r($errorInfo, true));
                return ['status' => 'error', 'message' => 'Error preparing statement: ' . $errorInfo[2]];
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
}
