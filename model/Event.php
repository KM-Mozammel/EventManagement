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

    public function getEvents($take, $skip)
    {
        try {
            $query = "SELECT * FROM events ORDER BY created_at DESC LIMIT :take OFFSET :skip";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':take', $take, PDO::PARAM_INT);
            $stmt->bindValue(':skip', $skip, PDO::PARAM_INT);
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

    public function getRegisteredEventsByUserIdPaged($userId, $take, $skip)
    {
        try {
            $query = "SELECT e.id, e.name, e.description, e.event_date, e.location, e.max_capacity, u.username AS creator_username
                  FROM $this->table e
                  JOIN users u ON e.created_by = u.id
                  JOIN registrations r ON e.id = r.event_id
                  WHERE r.user_id = :user_id
                  ORDER BY e.created_at DESC LIMIT :take OFFSET :skip";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':take', $take, PDO::PARAM_INT);
            $stmt->bindValue(':skip', $skip, PDO::PARAM_INT);
            if ($stmt->execute()) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Error getting registered events: " . print_r($errorInfo, true));
                return ['status' => 'error', 'message' => 'Database error: ' . $errorInfo[2]];
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
    public function getRegisteredEventsByUserId($userId)
    { {
            try {
                $query = "SELECT e.id, e.name, e.description, e.event_date, e.location, e.max_capacity, u.username AS creator_username
                  FROM $this->table e
                  JOIN users u ON e.created_by = u.id
                  JOIN registrations r ON e.id = r.event_id
                  WHERE r.user_id = :user_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
                if ($stmt->execute()) {
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $errorInfo = $stmt->errorInfo();
                    error_log("Error getting registered events: " . print_r($errorInfo, true));
                    return ['status' => 'error', 'message' => 'Database error: ' . $errorInfo[2]];
                }
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                return false;
            }
        }
    }

    public function getLatestEvents($take, $skip)
    {
        try {
            $query = "SELECT * FROM $this->table ORDER BY created_at DESC LIMIT :take OFFSET :skip";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':take', $take, PDO::PARAM_INT);
            $stmt->bindValue(':skip', $skip, PDO::PARAM_INT);
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
            $datetime = htmlspecialchars($formData['datetime'], ENT_QUOTES, 'UTF-8');
            $location = htmlspecialchars($formData['location'], ENT_QUOTES, 'UTF-8');
            $eventId = intval($formData['id']);

            // Convert datetime to date format if necessary
            $event_date = date('Y-m-d', strtotime($datetime));

            $query = "UPDATE events SET name = :name, description = :description, max_capacity = :max_capacity, event_date = :event_date, location = :location WHERE id = :id";
            $stmt = $this->conn->prepare($query);

            if ($stmt) {
                $stmt->bindValue(':name', $name, PDO::PARAM_STR);
                $stmt->bindValue(':description', $description, PDO::PARAM_STR);
                $stmt->bindValue(':max_capacity', $max_capacity, PDO::PARAM_INT);
                $stmt->bindValue(':event_date', $event_date, PDO::PARAM_STR);
                $stmt->bindValue(':location', $location, PDO::PARAM_STR);
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
                    GROUP_CONCAT(r.user_id SEPARATOR ' ') AS attendee_ids,
                    GROUP_CONCAT(reg_user.username SEPARATOR ', ') AS attendee_usernames,
                    GROUP_CONCAT(r.registration_date SEPARATOR '; ') AS registration_dates
                  FROM $this->table e
                  JOIN users u ON e.created_by = u.id
                  LEFT JOIN registrations r ON e.id = r.event_id
                  LEFT JOIN users reg_user ON r.user_id = reg_user.id
                  WHERE e.id = :id
                  GROUP BY e.id, e.name, e.description, e.event_date, e.location, e.max_capacity, u.username";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $eventId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
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

        // Split concatenated values
        $attendee_ids = explode(' ', $eventDetails['attendee_ids']);
        $attendee_usernames = explode(' ', $eventDetails['attendee_usernames']);
        $registration_dates = explode(' ', $eventDetails['registration_dates']);

        // Write each attendee's details to the CSV
        for ($i = 0; $i < count($attendee_ids); $i++) {
            fputcsv($output, array(
                $eventDetails['id'],
                $eventDetails['event_name'],
                $eventDetails['description'],
                $eventDetails['event_date'],
                $eventDetails['location'],
                $eventDetails['max_capacity'],
                $eventDetails['creator_username'],
                $attendee_ids[$i],
                $attendee_usernames[$i],
                $registration_dates[$i]
            ));
        }

        fclose($output);
        exit();
    }
}
