<?php
session_start();
require_once ROOT_PATH . 'model/Event.php';
class DashboardController extends Controller
{
    private $view;
    private $dbh;
    private $dbc;
    private $event;

    public function __construct()
    {
        $this->dbh = DatabaseConnection::getInstance();
        $this->dbc = $this->dbh->getConnection();
        $this->event = new EventModel($this->dbc);
        $this->view = new Template();
    }

    public function default()
    {
        $take = isset($_GET['take']) ? intval($_GET['take']) : 10;
        $skip = isset($_GET['skip']) ? intval($_GET['skip']) : 0;
        $latestEvents = $this->event->getLatestEventsByUserId($_SESSION['user_id'], $take, $skip);

        if ($_SESSION['role'] == 'admin') {
            $this->view->authView('pages/event/home', $latestEvents);
        } else {
            $this->view->authView('pages/event/userHome', $latestEvents);
        }
    }

    public function createEvent()
    {
        $this->view->authView('pages/event/eventCreation', "");
    }

    public function submitCreateEvent()
    {
        $errors = [];
        if (empty($_POST['name'])) {
            $errors[] = "Event name is required.";
        }
        if (empty($_POST['description'])) {
            $errors[] = "Description is required.";
        }
        if (empty($_POST['capacity'])) {
            $errors[] = "Capacity is required.";
        } elseif (!is_numeric($_POST['capacity']) || $_POST['capacity'] < 1) {
            $errors[] = "Capacity must be a number greater than 0.";
        }
        if (!empty($errors)) {
            return ['status' => 'error', 'errors' => $errors];
        }

        $result = $this->event->createEvent($_POST);

        if (!$result) {
            echo "Error creating event.";
        } else {
            $eventDetails = $this->event->getEventDetailsById($result);
            $this->view->authView('pages/event/eventDetails', $eventDetails);
        }
    }

    public function getEvents()
    {
        // if (!isset($_SESSION['id'])) {
        //     echo "You are not logged in.";
        //     return;
        // }

        $take = isset($_GET['take']) ? intval($_GET['take']) : 10;
        $skip = isset($_GET['skip']) ? intval($_GET['skip']) : 0;
        $events = $this->event->getEvents($take, $skip);

        foreach ($events as $event) {
            echo $this->renderEventCard($event);
        }
    }

    private function renderEventCard($event)
    {
        ob_start();
        ?>
                <div 
                    class="col-md-4 mb-4 event-card" 
                    data-date="<?php echo htmlspecialchars($event['event_date']); ?>" 
                    style="margin-bottom: 1.5rem; max-width: 380px; max-height: 360px;"
                >
                    <div class="card h-100" style="border: 1px solid #ddd; border-radius: 0.25rem; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                        <div class="card-header" style="background-color: #007bff; color: white; padding: 0.75rem 1.25rem;">
                            <h3 class="card-title" style="margin-bottom: 0;">
                                <?php echo htmlspecialchars($event['name']); ?>
                            </h3>
                        </div>
                        <div class="card-body" style="padding: 1.25rem;">
                            <p class="event-description" style="color: #6c757d;">
                                <?php
                                $description = htmlspecialchars($event['description']);
                                echo strlen($description) > 80 ? substr($description, 0, 80) . '...' : $description;
                                ?>
                            </p>
                            <p><strong>Capacity:</strong> <?php echo htmlspecialchars($event['max_capacity']); ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                            <p><strong>Happens Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
                            <a href="index.php?section=event&action=viewEvent&eventId=<?php echo $event['id']; ?>" style="display: inline-block; padding: 0.5rem 1rem; color: white; background-color: #007bff; border: none; border-radius: 0.25rem; text-decoration: none;">View Details</a>
                        </div>
                    </div>
                </div>
        <?php
        return ob_get_clean();
    }

    public function manageEvent()
    {
        $events = $this->event->getAllEventhByUserId($_SESSION['user_id']);
        $this->view->authView('pages/event/manageEvent', $events);
    }

    public function viewEvent()
    {
        $eventId = $_GET['eventId'];

        if (!empty($eventId)) {
            $eventDetails = $this->event->getEventDetailsById($eventId);
            $this->view->authView('pages/event/eventDetails', $eventDetails);
        } else {
            echo "Event not found.";
        }
    }

    public function viewAllRegisteredEvent()
    {
        if (!empty($_SESSION['user_id'])) {
            $eventDetails = $this->event->getRegisteredEventsByUserId($_SESSION['user_id']);
            $this->view->authView('pages/event/viewAllRegisteredEvent', $eventDetails);
        } else {
            echo "You have to log in";
        }
    }

    public function viewAllRegisteredEventPaged()
    {
        if (!empty($_SESSION['user_id'])) {
            $take = isset($_GET['take']) ? intval($_GET['take']) : 10;
            $skip = isset($_GET['skip']) ? intval($_GET['skip']) : 0;
            $eventDetails = $this->event->getRegisteredEventsByUserIdPaged($_SESSION['user_id'], $take, $skip);
            return $eventDetails;
        } else {
            echo "You have to log in";
        }
    }

    public function editEvent()
    {
        $eventId = $_GET['eventId'];

        if (!empty($eventId)) {
            $eventDetails = $this->event->getEventDetailsById($eventId);
            $this->view->authView('pages/event/editEvent', $eventDetails);
        } else {
            echo "Event not found.";
        }
    }

    public function deleteEvent()
    {
        $eventId = $_GET['eventId'];
        if (!empty($eventId)) {
            $result = $this->event->deleteEvent($eventId);
            if ($result) {
                $this->manageEvent();
            } else {
                echo "Error deleting event.";
            }
        } else {
            echo "Event not found.";
        }
    }

    public function submitEditEvent()
    {
        $errors = [];
        if (empty($_POST['name'])) {
            $errors[] = "Event name is required.";
        }
        if (empty($_POST['description'])) {
            $errors[] = "Description is required.";
        }
        if (empty($_POST['capacity'])) {
            $errors[] = "Capacity is required.";
        } elseif (!is_numeric($_POST['capacity']) || $_POST['capacity'] < 1) {
            $errors[] = "Capacity must be a number greater than 0.";
        }
        if (!empty($errors)) {
            return ['status' => 'error', 'errors' => $errors];
        }

        $result = $this->event->updateEvent($_POST);

        if (!$result) {
            echo "Error updating event.";
        } else {
            $eventDetails = $this->event->getEventDetailsById($_POST['id']);
            $this->view->authView('pages/event/eventDetails', $eventDetails);
        }
    }

    public function eventRegistration()
    {
        $eventId = $_GET['eventId'];
        if (!empty($eventId)) {
            $result = $this->event->eventRegistration($eventId, $_SESSION['user_id']);
            if ($result) {
                $this->view->authView('pages/event/registrationSuccess', "");
            } else {
                $this->view->authView('pages/event/alreadyRegistered', "Already registered for this event.");
            }
        } else {
            echo "Event not found.";
        }
    }

    public function reportGenerator()
    {
        $eventId = $_GET['eventId'];
        if (!empty($eventId)) {
            $eventDetails = $this->event->getEventDetailsForReportById($eventId);
            $this->view->authView('pages/event/reportGenerator', $eventDetails);
        } else {
            echo "Event not found.";
        }
    }

    public function downloadEventReport()
    {
        $eventId = $_GET['eventId'];
        $eventDetails = $this->event->downloadEventReport($eventId);
        $this->view->authView('pages/event/reportGenerator', $eventDetails);
    }
}
