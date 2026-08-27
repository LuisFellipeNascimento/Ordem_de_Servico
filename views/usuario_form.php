<?php
/** @var bool $cadastroForm IGUAL ao de login? Não precisamos pois é sempre form. */
// Consome a mensagem flash (erro) vinda de sessão, se houver.
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
    <title>Cadastro de Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .cadastro-card { max-width: 460px; margin: 10vh auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow-sm cadastro-card">
            <div class="card-body p-4">
                <h2 class="card-title text-center mb-4">Cadastro de Usuário</h2>

                <?php if ($flashMsg && isset($flashMsg['msg'])): ?>
                    <div class="alert <?= $flashMsg['tipo'] === 'erro' ? 'alert-danger' : 'alert-success' ?>" role="alert">
                        <?= htmlspecialchars($flashMsg['msg']) ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="index.php?controller=login&action=registrar">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required minlength="4">
                    </div>
                    <div class="mb-3">
                        <label for="confirma" class="form-label">Confirmar senha</label>
                        <input type="password" class="form-control" id="confirma" name="confirma" required minlength="4">
                    </div>
                    <button type="submit" class="btn btn-success w-100">Cadastrar</button>
                </form>

                <hr>
                <p class="text-center mb-0">
                    Já tem conta?
                    <a href="index.php?controller=login&action=form">Fazer login</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>