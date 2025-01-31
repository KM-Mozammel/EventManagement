<?php 
class Controller {
    function runAction($actionName){
        if(method_exists($this, $actionName)){
            $this->$actionName();
        } else {
            include 'view/layout/default.html';
        }
    }
}