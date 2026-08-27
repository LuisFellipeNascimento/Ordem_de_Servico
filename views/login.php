<?php
/** @var string|null $erro Mensagem de erro de autenticação, injetada pelo LoginController */
// Consome a mensagem flash (sucesso/erro) vinda de sessão, se houver.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$flashMsg = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .login-card { max-width: 400px; margin: 10vh auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow-sm login-card">
            <div class="card-body p-4">
                <h2 class="card-title text-center mb-4">Login</h2>

                <?php if (isset($erro)): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <?php if ($flashMsg && isset($flashMsg['msg'])): ?>
                    <div class="alert <?= $flashMsg['tipo'] === 'erro' ? 'alert-danger' : 'alert-success' ?>" role="alert">
                        <?= htmlspecialchars($flashMsg['msg']) ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="index.php?controller=login&action=autenticar">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="text" class="form-control" id="email" name="email" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                </form>

                <hr>
                <p class="text-center mb-0">
                    Não tem conta?
                    <a href="index.php?controller=login&action=novocadastro">Cadastre-se</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
