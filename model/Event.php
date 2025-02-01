<?php
class EventModel
{
    private $conn;
    private $table = "events";

    public function __construct($dbc)
    {
        $this->conn = $dbc;
    }

    public function createEvent($formData)
    {
        try {
            $name = htmlspecialchars($formData['name'], ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars($formData['description'], ENT_QUOTES, 'UTF-8');
            $location = htmlspecialchars($formData['location'], ENT_QUOTES, 'UTF-8');
            $event_date = htmlspecialchars($formData['datetime'], ENT_QUOTES, 'UTF-8');
            $max_capacity = intval($formData['capacity']);

            $query = "INSERT INTO $this->table (name, description, event_date, location, max_capacity, created_by) 
                  VALUES (:name, :description, :event_date, :location, :max_capacity, :created_by)";

            $stmt = $this->conn->prepare($query);

            if ($stmt) {
                $stmt->bindValue(':name', $name, PDO::PARAM_STR);
                $stmt->bindValue(':description', $description, PDO::PARAM_STR);
                $stmt->bindValue(':event_date', $event_date, PDO::PARAM_STR);
                $stmt->bindValue(':location', $location, PDO::PARAM_STR);
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
                return ['status' => 'error', 'message' => 'Error preparing statement: ' . $errorInfo[2]];
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
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

    public function deleteEvent($eventId)
    {
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

    public function eventRegistration($eventId, $userId)
    {
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

    public function getEventDetailsForReportById($eventId)
    {
        try {
            $query = "SELECT 
                    e.id,
                    e.name AS event_name, 
                    e.description, 
                    e.event_date, 
                    e.location, 
                    e.max_capacity, 
                    u.username AS creator_username, 
                    r.user_id AS attendee_id,
                    reg_user.username AS attendee_username,
                    r.registration_date
                  FROM $this->table e
                  JOIN users u ON e.created_by = u.id
                  LEFT JOIN registrations r ON e.id = r.event_id
                  LEFT JOIN users reg_user ON r.user_id = reg_user.id
                  WHERE e.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $eventId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Error getting event details for report: " . print_r($errorInfo, true));
                return ['status' => 'error', 'message' => 'Database error: ' . $errorInfo[2]];
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function downloadEventReport($eventId)
    {
        $eventDetails = $this->getEventDetailsForReportById($eventId);

        if (empty($eventDetails)) {
            die("No event details found.");
        }

        $filename = "event_report_" . $eventId . ".csv";

        if (ob_get_length()) {
            ob_clean();
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen("php://output", "w");
        fputcsv($output, array('Event ID', 'Event Name', 'Description', 'Event Date', 'Location', 'Max Capacity', 'Creator Username', 'Attendee ID', 'Attendee Username', 'Registration Date'));

        foreach ($eventDetails as $row) {
            fputcsv($output, array(
                $row['id'],
                $row['event_name'],
                $row['description'],
                $row['event_date'],
                $row['location'],
                $row['max_capacity'],
                $row['creator_username'],
                $row['attendee_id'],
                $row['attendee_username'],
                $row['registration_date']
            ));
        }

        fclose($output);
        exit();
    }
}
