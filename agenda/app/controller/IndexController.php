<?php

include_once 'app/view/IndexView.php';



class IndexController{

    public function handleRequest(){

        $view = new IndexView();
        $view->showHomePage();

    }
}