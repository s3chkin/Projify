<?php

class Controller {

    public function view($view, $data = []) {
        extract($data); // прави $data достъпно като променливи
        require "../app/views/" . $view . ".php";
    }
}
