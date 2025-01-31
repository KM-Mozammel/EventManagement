<?php
require_once ROOT_PATH . 'model/User.php';

class RegistrationController extends Controller
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
        $view->view('pages/registration', "hello");
    }

    public function submit()
    {
        $errors = [];
        if (!isset($_POST['name']) || empty(trim($_POST['name']))) {
            $errors['name'] = "Name is required.";
        }
        if (!isset($_POST['email']) || empty(trim($_POST['email']))) {
            $errors['email'] = "Email is required.";
        } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Invalid email format.";
        }
        if (!isset($_POST['password']) || empty(trim($_POST['password']))) {
            $errors['password'] = "Password is required.";
        } elseif (strlen($_POST['password']) < 6) {
            $errors['password'] = "Password must be at least 6 characters.";
        }

        if (!empty($errors)) {
            $view = new Template();
            $view->view('pages/registration', ['errors' => $errors, 'oldData' => $_POST]);
            return;
        }

        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = "Admin";
        $userRegistration = new UserModel($this->dbc);
        $result = $userRegistration->register($name, $email, $password, $role);
        
        if ($result != false) {
            $view = new Template();
            $msg['email']="Registration successful. Please login to continue.";
            $view->view('pages/login', ['errors' => $msg, 'oldData' => $_POST]);
            return;
        } else{
            $view = new Template();
            $view->view('pages/registration', ['errors' => $errors, 'oldData' => $_POST]);
            return;
        }
    }
}
