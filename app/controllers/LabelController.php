<?php

require_once "../app/core/Controller.php";
require_once "../app/core/Session.php";
require_once "../app/core/CSRF.php";
require_once "../app/models/Label.php";

class LabelController extends Controller {
    
    private $labelModel;
    
    public function __construct() {
        $this->labelModel = new Label();
        Session::start();
        
        if (!Session::has('user_id')) {
            header('Location: index.php?url=auth/login');
            exit;
        }
    }
    
    public function index() {
        $labels = $this->labelModel->getAll();
        $this->view("label/index", ['labels' => $labels]);
    }
    
    public function create() {
        $this->view("label/create");
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $name = $_POST['name'] ?? '';
            
            if (empty($name)) {
                $error = "Името на label-а е задължително!";
                $this->view("label/create", ['error' => $error]);
                return;
            }
            
            $id = $this->labelModel->create($name);
            
            if ($id) {
                header('Location: index.php?url=label/index');
                exit;
            } else {
                $error = "Грешка при създаване на label-а!";
                $this->view("label/create", ['error' => $error]);
            }
        } else {
            header('Location: index.php?url=label/index');
            exit;
        }
    }
    
    public function edit() {
        $id = $_GET['id'] ?? 0;
        $label = $this->labelModel->getById($id);
        
        if (!$label) {
            header('Location: index.php?url=label/index');
            exit;
        }
        
        $this->view("label/edit", ['label' => $label]);
    }
    
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            
            $label = $this->labelModel->getById($id);
            if (!$label) {
                header('Location: index.php?url=label/index');
                exit;
            }
            
            if (empty($name)) {
                $error = "Името на label-а е задължително!";
                $this->view("label/edit", ['label' => $label, 'error' => $error]);
                return;
            }
            
            if ($this->labelModel->update($id, $name)) {
                header('Location: index.php?url=label/index');
                exit;
            } else {
                $error = "Грешка при обновяване на label-а!";
                $this->view("label/edit", ['label' => $label, 'error' => $error]);
            }
        } else {
            header('Location: index.php?url=label/index');
            exit;
        }
    }
    
    public function delete() {
        $id = $_GET['id'] ?? 0;
        
        $label = $this->labelModel->getById($id);
        if (!$label) {
            header('Location: index.php?url=label/index');
            exit;
        }
        
        if ($this->labelModel->delete($id)) {
            header('Location: index.php?url=label/index');
            exit;
        } else {
            $error = "Грешка при изтриване на label-а!";
            $labels = $this->labelModel->getAll();
            $this->view("label/index", ['labels' => $labels, 'error' => $error]);
        }
    }
}

