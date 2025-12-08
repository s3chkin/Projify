<?php

require_once "Session.php";

class Controller {

    public function view($view, $data = []) {
        extract($data); // прави $data достъпно като променливи
        require "../app/views/layout/header.php";
        require "../app/views/" . $view . ".php";
        require "../app/views/layout/footer.php";
    }
}
