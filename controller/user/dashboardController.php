<?php
session_start();
require_once ROOT_PATH . 'model/Event.php';
class DashboardController extends Controller
{
    private $dbh;
    private $dbc;

    public function __construct()
    {
        $this->dbh = DatabaseConnection::getInstance();
        $this->dbc = $this->dbh->getConnection();
    }

    public function default()
    {
        $view = new Template();
        if ($_SESSION['role'] == 'admin') {
            $view->authView('pages/event/home', "");
        } else {
            $view->authView('pages/event/userHome', "");
        }
    }

    public function createEvent()
    {
        $view = new Template();
        $view->authView('pages/event/eventCreation', "");
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

        $event = new EventModel($this->dbc);
        $result = $event->createEvent($_POST);

        if ($result) {
            $result = #event->getEventById($result);
        }
    }

    public function manageEvent()
    {
        echo "manage event";
        return;

        $view = new Template();
        $view->view('pages/event/manageEvent', "");
    }
}
