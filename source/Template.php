<?php

class Template{
    public function view($template, $data){
        include VIEW_PATH . 'layout/default.html';
    }

    public function authView($template, $data){
        include VIEW_PATH . 'layout/dashboard.html';
    }

    public function validateRole($role){
        if($_SESSION['role'] == 'admin'){
            header("location: index.php?section=dashboard&action=default");
            // $view = new Template();
            // $view->authView("pages/event/home", "emp");
            // return false;
        }
        return true;
    }
}