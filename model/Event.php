<?php
class EventModel
{
    private $conn;
    private $table = "events";

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

            $query = "INSERT INTO $this->table (name, description, max_capacity, created_by) 
                  VALUES (:name, :description, :max_capacity, :created_by)";

            $stmt = $this->conn->prepare($query);

            if ($stmt) {
                $stmt->bindValue(':name', $name, PDO::PARAM_STR);
                $stmt->bindValue(':description', $description, PDO::PARAM_STR);
                $stmt->bindValue(':max_capacity', $max_capacity, PDO::PARAM_INT);
                $stmt->bindValue(':created_by', $_SESSION['user_id'], PDO::PARAM_INT);
                if ($stmt->execute()) {
                    return $this->conn->lastInsertId();
                } else {
                    $errorInfo = $stmt->errorInfo();
                    error_log("Error inserting event: " . print_r($errorInfo, true));
                    return ['status' => 'error', 'message' => 'Database error: ' . $errorInfo[2]];
                }
            } else {
                $errorInfo = $this->conn->errorInfo();
                error_log("Error preparing statement: " . print_r($errorInfo, true));
                return false;
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function getEventDetailsById($eventId)
    {
        try {
            $query = "SELECT * FROM $this->table WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $eventId, PDO::PARAM_INT);
            if ($stmt->execute()) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Error getting event details: " . print_r($errorInfo, true));
                return ['status' => 'error', 'message' => 'Database error: ' . $errorInfo[2]];
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function getAllEventhByUserId($userId)
    {
        try {
            $query = "SELECT * FROM $this->table WHERE created_by = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            if ($stmt->execute()) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Error getting event details: " . print_r($errorInfo, true));
                return ['status' => 'error', 'message' => 'Database error: ' . $errorInfo[2]];
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function getLatestEventsByUserId($userId)
    {
        try {
            $query = "SELECT * FROM $this->table WHERE created_by = :user_id ORDER BY created_at DESC LIMIT 6";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            if ($stmt->execute()) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Error getting latest events: " . print_r($errorInfo, true));
                return ['status' => 'error', 'message' => 'Database error: ' . $errorInfo[2]];
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    } 

    public function updateEvent($formData)
    {
        try {
            $name = htmlspecialchars($formData['name'], ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars($formData['description'], ENT_QUOTES, 'UTF-8');
            $max_capacity = intval($formData['capacity']);
            $eventId = intval($formData['eventId']);

            $query = "UPDATE $this->table SET name = :name, description = :description, max_capacity = :max_capacity WHERE id = :id";
            $stmt = $this->conn->prepare($query);

            if ($stmt) {

                $stmt->bindValue(':name', $name, PDO::PARAM_STR);
                $stmt->bindValue(':description', $description, PDO::PARAM_STR);
                $stmt->bindValue(':max_capacity', $max_capacity, PDO::PARAM_INT);
                $stmt->bindValue(':id', $eventId, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    return true;
                } else {
                    $errorInfo = $stmt->errorInfo();
                    error_log("Error updating event: " . print_r($errorInfo, true));
                    return ['status' => 'error', 'message' => 'Database error: ' . $errorInfo[2]];
                }

            } else {

                $errorInfo = $this->conn->errorInfo();
                error_log("Error preparing statement: " . print_r($errorInfo, true));
                return false;

            }
        } catch (PDOException $e) {

            error_log("Database error: " . $e->getMessage());
            return false;

        }
    }

    public function deleteEvent($eventId){
        try {
            $query = "DELETE FROM $this->table WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $eventId, PDO::PARAM_INT);
            if ($stmt->execute()) {
                return true;
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Error deleting event: " . print_r($errorInfo, true));
                return ['status' => 'error', 'message' => 'Database error: ' . $errorInfo[2]];
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function eventhRegistration($eventId, $userId){
        try {
            $query = "INSERT INTO registrations (event_id, user_id, registration_date) VALUES (:event_id, :user_id, NOW())";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':event_id', $eventId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            if ($stmt->execute()) {
                return true;
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Error registering for event: " . print_r($errorInfo, true));
                return ['status' => 'error', 'message' => 'Database error: ' . $errorInfo[2]];
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
}