<?php
/** @var array{name: string, id_user: int} $usuario Usuário logado, injetado pelo DashboardController */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Novo Serviço</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="card shadow-sm mx-auto" style="max-width: 600px;">
            <div class="card-header bg-primary text-white">
                <h2 class="h4 mb-0">Adicionar Novo Serviço</h2>
            </div>
            <div class="card-body">
                <p class="text-muted">Usuário logado: <strong><?= htmlspecialchars($usuario['name']) ?></strong></p>

                <form method="post" action="index.php?controller=dashboard&action=cadastrar">
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <input type="text" class="form-control" id="descricao" name="descricao" required maxlength="45">
                    </div>
                    <div class="mb-3">
                        <label for="valor" class="form-label">Valor</label>
                        <input type="text" class="form-control" id="valor" name="valor" required placeholder="Ex.: 250,00">
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Cadastrar</button>
                        <a class="btn btn-secondary" href="index.php?controller=dashboard&action=index">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>