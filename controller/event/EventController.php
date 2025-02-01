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
        if ($_SESSION['role'] == 'admin') {
            $latestEvents = $this->event->getLatestEventsByUserId($_SESSION['user_id']);
            $this->view->authView('pages/event/home', $latestEvents);
        } else {
            $this->view->authView('pages/event/userHome', "");
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

    public function submitEditEvent (){
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
            if($result){
                $this->view->authView('pages/event/registrationSuccess', "");
            } else {
                echo "You are already in this event, go back to <a href='index.php?section=event&action=default'>Dashboard</a>";
            }
        } else {
            echo "Event not found.";
        }
    }

    public function reportGenerator(){
        $eventId = $_GET['eventId'];
        if (!empty($eventId)) {
            $eventDetails = $this->event->getEventDetailsForReportById($eventId);
            $this->view->authView('pages/event/reportGenerator', $eventDetails);
        } else {
            echo "Event not found.";
        }
    }

    public function downloadEventReport(){
        $eventId = $_GET['eventId'];
        $eventDetails = $this->event->downloadEventReport($eventId);
        $this->view->authView('pages/event/reportGenerator', $eventDetails);
    }
}
