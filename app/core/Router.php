<?php

class Router {

    public function run() {
        // Прочитаме URL параметъра
        $url = isset($_GET['url']) ? $_GET['url'] : 'home/index';

        // Разделяме на части: controller/method
        $parts = explode("/", $url);

        $controllerName = ucfirst($parts[0]) . "Controller";
        $methodName = isset($parts[1]) ? $parts[1] : "index";

        // Път до контролера
        $controllerFile = "../app/controllers/" . $controllerName . ".php";

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
        } else {
            die("Controller $controllerName not found");
        }

        // Създаваме инстанция
        $controller = new $controllerName;

        // Проверяваме метода
        if (!method_exists($controller, $methodName)) {
            die("Method $methodName not found in controller $controllerName");
        }

        // Извикваме метода
        $controller->$methodName();
    }
}
