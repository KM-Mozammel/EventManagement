<!-- FileImport -->
<?php
    define('ROOT_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
    define('VIEW_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR);
    require_once ROOT_PATH.'source/Controller.php';
    require_once ROOT_PATH.'controller/user/loginController.php';
    require_once ROOT_PATH.'controller/user/registrationController.php';
    require_once ROOT_PATH.'controller/user/dashboardController.php';
    require_once ROOT_PATH . 'source/Template.php';
    require_once ROOT_PATH . 'source/DatabaseConnection.php';
?>

<!-- Configuration -->
<?php DatabaseConnection::connect('localhost', 'event_management', 'root', ''); ?>

<!-- Routing -->
<?php

    $section = $_GET['section'] ?? $_POST['section'] ?? 'home';
    $action = $_GET['action'] ?? $_POST['action'] ?? 'default';

    if($section == 'dashboard'){

        $dashboard = new DashboardController();
        $dashboard->runAction($action);

    } else if($section == 'registration'){

        $login = new RegistrationController();
        $login->runAction($action);

    } else if($section=='login'){

        $login = new LoginController();
        $login->runAction($action);

    } else{

        $view = new Template();
        $view->view("pages/status-pages/404", "emp");

    }
