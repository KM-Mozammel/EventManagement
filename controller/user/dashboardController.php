<?php
require_once ROOT_PATH . 'model/Event.php';
class DashboardController extends Controller
{
    function default()
    {
        $view = new Template();
        $view->view('dashboard', "");
    }

    function createEvent()
    {
        $view = new Template();
        $view->view('event/eventCreation', "");
    }

    function submitCreateEvent()
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

        $dbh = DatabaseConnection::getInstance();
        $dbc = $dbh->getConnection();

        $event = new Event($dbc);
        $result = $event->createEvent($_POST);

        if($result)
        {
            echo "Event created successfully";
        }
    }
}
