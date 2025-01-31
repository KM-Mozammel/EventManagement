<!-- fileImport -->
<?php
    define('ROOT_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
    define('VIEW_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR);
    require_once ROOT_PATH.'source/Controller.php';
    require_once ROOT_PATH.'controller/user/LoginController.php';
    require_once ROOT_PATH.'controller/user/RegistrationController.php';
    require_once ROOT_PATH.'controller/event/EventController.php';
    require_once ROOT_PATH . 'source/Template.php';
    require_once ROOT_PATH . 'source/DatabaseConnection.php';
?>

<!-- configuration -->
<?php DatabaseConnection::connect('localhost', 'event_management', 'root', ''); ?>

<!-- routing -->
<?php

    $section = $_GET['section'] ?? $_POST['section'] ?? 'home';
    $action = $_GET['action'] ?? $_POST['action'] ?? 'default';

    if($section == 'event'){

        $dashboard = new DashboardController();
        $dashboard->runAction($action);

    } else if($section == 'registration'){

        $login = new RegistrationController();
        $login->runAction($action);

    } else if($section=='login'){

        $login = new LoginController();
        $login->runAction($action);

    } else if($section == 'logout'){

        session_destroy();
        header("location: index.php?section=login&from=logout");

    } else{

        $view = new Template();
        $view->view("pages/status-pages/404", "emp");
        
    }
