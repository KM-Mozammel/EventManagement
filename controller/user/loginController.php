<?php

class LoginController extends Controller
{
    private $dbh;
    private $dbc;

    public function __construct() {
        $this->dbh = DatabaseConnection::getInstance();
        $this->dbc = $this->dbh->getConnection();
    }

    public function default()
    {
        $view = new Template();
        $view->view('pages/login', "hello");
    }

    public function submit()
    {
        /** BackendValidation */ 
        $errors = [];
        if (!isset($_POST['email']) || empty(trim($_POST['email']))) {
            $errors['email'] = "Email is required.";
        } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Invalid email format.";
        }
        // $rememberMe = isset($_POST['rememberMe']) ? true : false;
        if (!isset($_POST['password']) || empty(trim($_POST['password']))) {
            $errors['password'] = "Password is required.";
        } elseif (strlen($_POST['password']) < 6) {
            $errors['password'] = "Password must be at least 6 characters.";
        }
        if (!empty($errors)) {
            $view = new Template();
            $view->view('login', ['errors' => $errors, 'oldData' => $_POST]);
            return;
        }

        /**--gettingformData */
        $email = $_POST['email'];
        $password = $_POST['password'];

        $userLogin = new UserModel($this->dbc);
        $result = $userLogin->login($email, $password);

        if ($result) {
            $view = new Template();
            $view->view('dashboard', $result);
        } else {
            $errors['login'] = "Invalid email or password.";
            $view = new Template();
            $view->view('login', ['errors' => $errors, 'oldData' => $_POST]);
        }
    }
}
