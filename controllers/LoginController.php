<?php

/**
 * LoginController — orquestra autenticação, logout e página de login.
 *
 * Fluxo típico: o usuário submete o formulário -> autenticar() confere com o
 * model Usuario. Se e-ok, grava o usuário em sessão e vai ao dashboard; senão,
 * renderiza a view de login com a $erro.
 */
require_once(__DIR__ . '/../models/Usuario.php');

class LoginController {

    /**
     * Encerra a sessão e volta para o formulário de login.
     * - session_unset(): limpa as variáveis de sessão.
     * - session_destroy(): remove a sessão no servidor.
     * Depois redireciona (header) e encerra com exit para garantir que nada
     * mais seja renderizado.
     */
    public function sair() {
        session_start();
        session_unset();
        session_destroy();
        header("Location: index.php?controller=login&action=form");
        exit;
    }

    /**
     * Tenta autenticar pelo e-mail e senha.
     * - se o model achar o usuário: cria sessão e redireciona para o dashboard;
     * - senão: inclui a view de login passando $erro (exibida pelo formulário).
     *
     */
    public function autenticar($email, $senha) {
        $usuario = Usuario::buscarPorEmailSenha($email, $senha);
        if ($usuario) {
            session_start();
            // Guarda o usuário inteiro na sessão — o dashboard usa $usuario['name'].
            $_SESSION['usuario'] = $usuario;
            header("Location: index.php?controller=dashboard&action=index");
        } else {
            $erro = "Ops, Email ou Senha inválido";
            include(__DIR__ . '/../views/login.php');
        }
    }

    /**
     * Exibe a tela de cadastro de novo usuário.
     */
    public function novoCadastro() {
        include(__DIR__ . '/../views/usuario_form.php');
    }

    /**
     * Processa o cadastro de um novo usuário.
     * Valida os campos obrigatórios, evita e-mail duplicado e, em sucesso,
     * redireciona ao login com mensagem de sucesso. Em falha, redireciona
     * ao formulário de cadastro mantendo a mensagem de erro via sessão.
     */
    public function registrar() {
        // Garante sessão para guardar a mensagem flash entre os redirects.
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $nome  = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $confirma = $_POST['confirma'] ?? '';

        // Validação
        if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['msg' => 'Informe nome e um e-mail válido.', 'tipo' => 'erro'];
            header("Location: index.php?controller=login&action=novocadastro");
            exit;
        }
        if (strlen($senha) < 4) {
            $_SESSION['flash'] = ['msg' => 'A senha deve ter ao menos 4 caracteres.', 'tipo' => 'erro'];
            header("Location: index.php?controller=login&action=novocadastro");
            exit;
        }
        if ($senha !== $confirma) {
            $_SESSION['flash'] = ['msg' => 'A confirmação de senha não confere.', 'tipo' => 'erro'];
            header("Location: index.php?controller=login&action=novocadastro");
            exit;
        }
        if (Usuario::buscarPorEmail($email)) {
            $_SESSION['flash'] = ['msg' => 'Já existe um usuário com este e-mail.', 'tipo' => 'erro'];
            header("Location: index.php?controller=login&action=novocadastro");
            exit;
        }

        $ok = Usuario::criar($nome, $email, $senha);
        $_SESSION['flash'] = $ok
            ? ['msg' => 'Usuário cadastrado com sucesso! Faça login.', 'tipo' => 'ok']
            : ['msg' => 'Erro ao cadastrar o usuário.', 'tipo' => 'erro'];

        header("Location: index.php?controller=login&action=form");
        exit;
    }
}
