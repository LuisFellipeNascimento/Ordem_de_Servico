<?php
/**
 * Front Controller (front-end único).
 *
 * Todas as requisições chegam aqui via index.php?controller=X&action=Y e são
 * desviadas para o controller/action adequado. Centralizar o roteamento facilita
 * proteção de acesso, logs e futura restrição por método HTTP.
 */

// Parâmetros com default garantem uma página sempre (não quebra sem query string).
$controller = $_GET['controller'] ?? 'login';
$action = $_GET['action'] ?? 'form';

// Roteamento manual: login (form/autenticar/sair) e dashboard (index/novo/…).
switch ($controller) {
    case 'login':
        require_once(__DIR__ . '/../controllers/LoginController.php');
        $ctrl = new LoginController();
        if ($action == 'form') {
            // Apenas renderiza o formulário — não passa por um método.
            include(__DIR__ . '/../views/login.php');
        } elseif ($action == 'autenticar') {
            $ctrl->autenticar($_POST['email'], $_POST['senha']);
        } elseif ($action == 'sair') {
            $ctrl->sair();
        } elseif ($action == 'novocadastro') {
            $ctrl->novoCadastro();
        } elseif ($action == 'registrar') {
            $ctrl->registrar();
        }
        break;

    case 'dashboard':
        require_once(__DIR__ . '/../controllers/DashboardController.php');
        $ctrl = new DashboardController();
        if ($action == 'index') {
            $ctrl->index();
        } elseif ($action == 'novo') {
            $ctrl->novo();
        } elseif ($action == 'cadastrar') {
            $ctrl->cadastrar();
        } elseif ($action == 'excluir') {
            $ctrl->excluir();
        } elseif ($action == 'finalizar') {
            $ctrl->finalizar();
        } elseif ($action == 'editar') {
            $ctrl->editar();
        } elseif ($action == 'atualizar') {
            $ctrl->atualizar();
        }
        break;
}
