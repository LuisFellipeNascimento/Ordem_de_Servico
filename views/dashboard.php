<?php
/** @var array{name: string, id_user: int, email: string} $usuario Usuário logado, injetado pelo DashboardController */
/** @var array<int, array{id_service:int, description:string, status:string, price:float, nome:string}> $servicos Lista de serviços (já filtrada) exibida na tabela */
/** @var array<int, array{id_service:int, description:string, status:string, price:float, nome:string}> $pendentes Últimos serviços com status Pendente */
/** @var float $total Soma dos valores dos serviços exibidos */
/** @var array{msg:string, tipo:string}|null $msg Mensagem flash (ok/erro), injetada pelo DashboardController */
/** @var array{id_service:int, description:string, status:string, price:float}|null $editando Serviço em edição (null quando nenhum) */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container-fluid">
        <span class="navbar-brand">Sistema de Ordens de Serviço</span>
        <div class="d-flex align-items-center text-white">
            <span class="me-3">Olá, <strong><?= htmlspecialchars($usuario['name']) ?></strong></span>
            <a href="index.php?controller=login&action=sair" class="btn btn-outline-light btn-sm">Sair</a>
        </div>
    </div>
</nav>

<div class="container pb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h1 class="h3 mb-0">Dashboard</h1>
        <p class="text-muted mb-0">Data atual: <?= date("d/m/Y") ?></p>
        <a href="index.php?controller=dashboard&action=novo" class="btn btn-success">+ Adicionar Novo Serviço</a>
    </div>

    <?php if ($msg && isset($msg['msg'])): ?>
        <div class="alert <?= $msg['tipo'] === 'erro' ? 'alert-danger' : 'alert-success' ?>" role="alert">
            <?= htmlspecialchars($msg['msg']) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark">
            <strong><?= count($pendentes) ?> Serviço(s) Pendente(s)</strong>
        </div>
        <div class="card-body">
            <?php if (empty($pendentes)): ?>
                <p class="mb-0 text-muted">Nenhum serviço pendente no momento.</p>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($pendentes as $p): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>#<?= $p['id_service'] ?> – <?= htmlspecialchars($p['description']) ?></span>
                            <span>
                                <span class="badge bg-warning text-dark"><?= htmlspecialchars($p['nome']) ?></span>
                                <strong>R$ <?= number_format($p['price'], 2, ',', '.') ?></strong>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <h3 class="h5 mb-2">Filtros</h3>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="get" action="index.php" class="row g-3 align-items-end">
                <input type="hidden" name="controller" value="dashboard">
                <input type="hidden" name="action" value="index">
                <div class="col-md-2">
                    <label class="form-label">Data inicial</label>
                    <input type="date" class="form-control" name="filtro_data_inicio" value="<?= htmlspecialchars($_GET['filtro_data_inicio'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Data final</label>
                    <input type="date" class="form-control" name="filtro_data_fim" value="<?= htmlspecialchars($_GET['filtro_data_fim'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nome do serviço</label>
                    <input type="text" class="form-control" name="filtro_descricao" value="<?= htmlspecialchars($_GET['filtro_descricao'] ?? '') ?>" placeholder="Buscar">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="filtro_status" class="form-select">
                        <option value="">Todos</option>
                        <option value="Pendente" <?= (($_GET['filtro_status'] ?? '') === 'Pendente') ? 'selected' : '' ?>>Pendente</option>
                        <option value="Finalizado" <?= (($_GET['filtro_status'] ?? '') === 'Finalizado') ? 'selected' : '' ?>>Finalizado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Usuário do serviço</label>
                    <input type="text" class="form-control" name="filtro_usuario" value="<?= htmlspecialchars($_GET['filtro_usuario'] ?? '') ?>" placeholder="Nome">
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="index.php?controller=dashboard&action=index" class="btn btn-outline-secondary">Limpar</a>
                    
                </div>
            </form>
        </div>
    </div>

    <h3 class="h5 mb-2">Serviços Prestados</h3>
    <div class="card shadow-sm mb-4">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-primary">
                    <tr><th>ID</th><th>Descrição</th><th>Status</th><th>Valor</th><th>Comissão</th><th>Usuário</th><th>Finalizado em</th><th>Ações</th></tr>
                </thead>
                <tbody>
        <?php $servicos = $servicos ?? []; $total = 0; foreach ($servicos as $s): $total += $s['price']; ?>
        <tr>
            <td><?= htmlspecialchars($s['id_service']) ?></td>
            <td><?= htmlspecialchars($s['description']) ?></td>
            <td>
                <?php if (strtolower($s['status']) === 'finalizado'): ?>
                    <span class="badge bg-success"><?= htmlspecialchars($s['status']) ?></span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark"><?= htmlspecialchars($s['status']) ?></span>
                <?php endif; ?>
            </td>
            <td>R$ <?= number_format($s['price'], 2, ',', '.') ?></td>
            <td><?= ($s['commission_user'] !== null) ? 'R$ ' . number_format($s['commission_user'], 2, ',', '.') : '—'; ?></td>
            <td><?= htmlspecialchars($s['nome']) ?></td>
            <td><?php echo ($s['finished_at'] !== null) ? date('d/m/Y H:i', strtotime($s['finished_at'])) : '—'; ?></td>
            <td class="text-nowrap">
                <a class="btn btn-sm btn-outline-primary" href="index.php?controller=dashboard&action=editar&id=<?= $s['id_service'] ?>">Alterar</a>
                <a class="btn btn-sm btn-outline-danger" href="index.php?controller=dashboard&action=excluir&id=<?= $s['id_service'] ?>"
                   onclick="return confirm('Excluir este serviço?');">Excluir</a>
                <?php if (strtolower($s['status']) !== 'finalizado'): ?>
                    <a class="btn btn-sm btn-outline-success" href="index.php?controller=dashboard&action=finalizar&id=<?= $s['id_service'] ?>"
                       onclick="return confirm('Finalizar este serviço?');">Finalizar</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="h5 mb-0">Valor Total:
                <span class="text-primary fw-bold">R$ <?= number_format($total, 2, ',', '.') ?></span>
            </h4>
        </div>
    </div>

    <?php if ($editando !== null): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header"><h4 class="h5 mb-0">Alterar Serviço #<?= $editando['id_service'] ?></h4></div>
            <div class="card-body">
                <form method="post" action="index.php?controller=dashboard&action=atualizar">
                    <input type="hidden" name="id" value="<?= $editando['id_service'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <input type="text" class="form-control" name="descricao" value="<?= htmlspecialchars($editando['description']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Valor</label>
                        <input type="text" class="form-control" name="valor" value="<?= $editando['price'] ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    <a href="index.php?controller=dashboard&action=index" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    <?php endif; ?>

</div>
</body>
</html>
