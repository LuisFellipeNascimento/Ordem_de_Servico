<?php
/**
 * DashboardController — orquestra as telas/ções do painel autenticado.
 *
 * Padrão: toda ação chama proteger() (garante sessão ativa) e só então executa
 * a lógica, encerrando com redirect + mensagem flash quando há escrita.
 * As views recebem as variáveis por include (nome, serviço, mensagens…).
 */
require_once(__DIR__ . '/../models/Servico.php');
require_once(__DIR__ . '/../core/Mailer.php');

class DashboardController {

    /**
     * Tela inicial do dashboard.
     * Carrega a lista filtrada e os últimos pendentes e consome a mensagem
     * flash (uma única exibição — é consumida e removida da sessão).
     */
    public function index() {
        $usuario = $this->proteger();

        $servicos  = Servico::listar($this->filtros());
        $pendentes = Servico::listarPendentes(5);
        $editando  = null;
        $msg       = $this->flashMessage();

        include(__DIR__ . '/../views/dashboard.php');
    }

    /**
     * Abre o formulário de novo serviço (separado do dashboard).
     */
    public function novo() {
        $usuario = $this->proteger();
        include(__DIR__ . '/../views/servico_form.php');
    }

    /**
     * Processa o POST de cadastro.
     * Converte ',' em '.' para o valor PODE vir formatado em pt-BR (ex.: 250,00).
     * Valida (descrição não vazia e valor numérico > 0) e usa flash em caso
     * de falha; em sucesso, também flash e redireciona ao dashboard.
     *
     * PONTO SÊNIOR: após redirect, o navegador faz nova requisição; a mensagem
     * não poderia ser repassada por variável de requisição, então usamos session
     * flash (setFlash + flashMessage) — padrão PRG (Post/Redirect/Get) recomendado.
     */
    public function cadastrar() {
        $usuario = $this->proteger();

        $descricao = trim($_POST['descricao'] ?? '');
        $valor     = trim(str_replace(',', '.', $_POST['valor'] ?? ''));

        if ($descricao === '' || !is_numeric($valor) || (float)$valor <= 0) {
            $this->setFlash('Informe obrigatoriamente a descrição e um valor válido para o serviço.', 'erro');
            header("Location: index.php?controller=dashboard&action=index");
            exit;
        }

        $ok = Servico::adicionar($descricao, (float)$valor, $usuario['id_user']);
        $this->setFlash(
            $ok ? 'Serviço cadastrado com sucesso!' : 'Erro ao cadastrar o serviço.',
            $ok ? 'ok' : 'erro'
        );
        header("Location: index.php?controller=dashboard&action=index");
        exit;
    }

    /**
     * Exclui um serviço pelo id (via GET).
     * Usa cast (int) por segurança e guarda o resultado por flash.
     */
    public function excluir() {
        $usuario = $this->proteger();
        $id = (int)($_GET['id'] ?? 0);
        $ok = Servico::excluir($id);
        $this->setFlash($ok ? 'Serviço excluído.' : 'Erro ao excluir o serviço.', $ok ? 'ok' : 'erro');
        header("Location: index.php?controller=dashboard&action=index");
        exit;
    }

    /**
     * Finalizar serviço: delega ao model (grava status/finished_at/comissão) e,
     * em caso de sucesso, envia e-mail de aviso ao usuário LOGADO (sessão).
     * A comissão é informada ao usuário na mensagem flash.
     */
    public function finalizar() {
        $usuario = $this->proteger();
        $id = (int)($_GET['id'] ?? 0);

        // Aqui não validamos se finalizou novamente: a idempotência está no model.
        $servico = Servico::finalizar($id);

        if ($servico) {
            $comissao = (float)$servico['commission_user'];
            $preco    = (float)$servico['price'];
            $msgEmail = $this->mensagemEmailFinalizacao($servico);

            // Envia para o usuário autenticado na sessão (regra de negócio).
            // Se a sessão não tiver e-mail, evita disparo vazio.
            $emailLogado = $usuario['email'] ?? '';
            if ($emailLogado) {
                Mailer::enviar($emailLogado, 'Serviço finalizado', $msgEmail);
            }

            $this->setFlash(
                'Serviço finalizado! Comissão de R$ ' . number_format($comissao, 2, ',', '.')
                . ' (sobre R$ ' . number_format($preco, 2, ',', '.') . ').',
                'ok'
            );
        } else {
            $this->setFlash('Não foi possível finalizar o serviço.', 'erro');
        }

        header("Location: index.php?controller=dashboard&action=index");
        exit;
    }

    /**
     * Modo edição: além da lista/pendentes, carrega o serviço alvo em $editando
     * para a view exibir o formulário preenchido.
     */
    public function editar() {
        $usuario = $this->proteger();
        $id = (int)($_GET['id'] ?? 0);

        $servicos  = Servico::listar($this->filtros());
        $pendentes = Servico::listarPendentes(5);
        $editando  = Servico::buscarPorId($id);
        $msg       = null;

        include(__DIR__ . '/../views/dashboard.php');
    }

    /**
     * Processa o POST de atualização (alterar).
     * Mesma validação do cadastro e mesmo padrão PRG com flash.
     */
    public function atualizar() {
        $usuario = $this->proteger();
        $id = (int)($_POST['id'] ?? 0);
        $descricao = trim($_POST['descricao'] ?? '');
        $valor = trim(str_replace(',', '.', $_POST['valor'] ?? ''));

        if ($descricao === '' || !is_numeric($valor) || (float)$valor <= 0) {
            $this->setFlash('Informe obrigatoriamente a descrição e um valor válido.', 'erro');
            header("Location: index.php?controller=dashboard&action=index");
            exit;
        }

        $ok = Servico::atualizar($id, $descricao, (float)$valor);
        $this->setFlash($ok ? 'Serviço alterado com sucesso!' : 'Erro ao alterar o serviço.', $ok ? 'ok' : 'erro');
        header("Location: index.php?controller=dashboard&action=index");
        exit;
    }

    /**
     * Extrai os filtros vindos da query string (GET), com valores padrão vazios.
     * Evita "undefined index" e centraliza os nomes dos parâmetros usados na view.
     */
    private function filtros() {
        return [
            'descricao'   => trim($_GET['filtro_descricao'] ?? ''),
            'status'      => trim($_GET['filtro_status'] ?? ''),
            'usuario'     => trim($_GET['filtro_usuario'] ?? ''),
            'data_inicio' => trim($_GET['filtro_data_inicio'] ?? ''),
            'data_fim'    => trim($_GET['filtro_data_fim'] ?? ''),
        ];
    }

    /**
     * Monta o corpo HTML do e-mail de finalização.
     * htmlspecialchars() nas entradas vindas do banco evita quebra do HTML do
     * e-mail/injeção; number_format em pt-BR para a leitura ficar amigável.
     */
    private function mensagemEmailFinalizacao($servico) {
        $nome  = htmlspecialchars($servico['nome'] ?? '');
        $desc  = htmlspecialchars($servico['description'] ?? '');
        $valor = number_format((float)$servico['price'], 2, ',', '.');
        $com   = number_format((float)$servico['commission_user'], 2, ',', '.');
        $data  = date('d/m/Y H:i');

        return "<h2>Serviço Finalizado</h2>"
            . "<p>Olá, <strong>{$nome}</strong>!</p>"
            . "<p>O serviço <strong>{$desc}</strong> foi finalizado em <strong>{$data}</strong>.</p>"
            . "<p>Valor do serviço: <strong>R$ {$valor}</strong></p>"
            . "<p>Sua comissão: <strong>R$ {$com}</strong></p>";
    }

    /**
     * Gatekeeper de autenticação: garante que há um usuário na sessão antes de
     * qualquer ação do dashboard; senão, redireciona ao login.
     * session_status() evita chamar session_start() duas vezes na mesma requisição
     * (o que geraria warning "headers already sent").
     */
    private function proteger() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $usuario = $_SESSION['usuario'] ?? null;
        if (!$usuario) {
            header("Location: index.php?controller=login&action=form");
            exit;
        }
        return $usuario;
    }

    /**
     * Grava uma mensagem flash na sessão (sobrevive ao redirect — padrão PRG).
     * $tipo controla a cor na view ('ok' verde / 'erro' vermelho).
     */
    private function setFlash($mensagem, $tipo = 'ok') {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['flash'] = ['msg' => $mensagem, 'tipo' => $tipo];
    }

    /**
     * Consome (lê e remove) a mensagem flash da sessão. Por ser consumida num
     * único READ, a mensagem só aparece UMA vez — evita repetição ao atualizar
     * a página.
     */
    private function flashMessage() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}