<?php

class Template{
    public function view($template, $data){
        include VIEW_PATH . 'layout/default.html';
    }
}