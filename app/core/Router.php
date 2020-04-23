<?php

namespace side\app\core;
use http\QueryString;

class Router
{
private $routes;

public function __construct()
{
    $routesPath = ROOT.'/app/config/routes.php';
    $this->routes = include($routesPath);
}

private function getURI(){
    if (!empty($_SERVER['REQUEST_URI'])){
        return explode('?',$_SERVER['REQUEST_URI'] )[0];
    }
}



public function run()
{// Получить
    $uri = $this->getURI();
//    print_r($_SERVER);
//    print_r($_GET);
foreach($this->routes as $route){

    if (preg_match("~$route[0]~", $uri)){
        $controller_name = "\\side\\app\\controllers\\".$route[1];
        $controller = new $controller_name;
        $result = $controller->actionIndex();
        break;
    }
}

}

}