# Ordem de Serviço

Sistema web (PHP + HTML + Javascript + MySQL) para gestão de serviços prestados por usuários. Inclui autenticação, dashboard com filtros, CRUD de serviços, finalização com cálculo de comissão e envio de e-mail.

## ⚙️ Tecnologias

- **PHP 8.x** (testado em PHP 8.4/8.5)
- **MySQL / MariaDB** (via WampServer)
- **PDO** para acesso ao banco
- **PHPMailer** (SMTP) para envio de e-mails — `vendor/phpmailer/`
- **Bootstrap 5.3** (CDN) para as telas

- Arquitetura simples em camadas: `controllers/`, `models/`, `views/`, `core/`

---

## 📁 Estrutura de pastas

```
Order_of_Service/
├── config/
│   ├── config.php          # Conexão com o banco (host, dbname, user, senha)
│   └── email.php           # Credenciais de SMTP para envio de e-mail
├── controllers/
│   ├── DashboardController.php
│   └── LoginController.php
├── core/
│   ├── Database.php        # Conexão PDO (singleton)
│   └── Mailer.php          # Envio de e-mail via PHPMailer/SMTP
├── models/
│   ├── Servico.php
│   └── Usuario.php
├── public/
│   └── index.php           # Front controller / roteador (ponto de entrada)
├── views/
│   ├── dashboard.php
│   ├── login.php
│   └── servico_form.php
└── vendor/phpmailer/       # Biblioteca PHPMailer
```

> O **documento raiz** da aplicação é a pasta `public/`. Todas as requisições passam por `public/index.php` usando o padrão `?controller=X&action=Y`.

---

## ✅ Requisitos

- [WampServer](https://www.wampserver.com/) (Apache + PHP + MySQL) comuns
- Navegador web

> O projeto foi desenvolvido/testado em **WampServer** no Windows. Qualquer ambiente com PHP 8+ e MySQL serve.

---

## 🚀 Passo a passo para rodar

### 1. Colocar o projeto no diretório do Wamp

Copie a pasta para a raiz `www` do Wamp:
```
c:\wamp64\www\Order_of_Service
```

### 2. Iniciar o WampServer

Clique no ícone do Wamp na bandeja do Windows e inicie. Espere até o ícone ficar **verde** (Apache + MySQL ativos).

### 3. Configurar o banco de dados

Crie o banco e as tabelas. No MySQL (ex.: via phpMyAdmin em `http://localhost/phpmyadmin`), execute:

```sql
CREATE DATABASE IF NOT EXISTS ordem_servicos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ordem_servicos;

CREATE TABLE usuarios (
    id_user    BIGINT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(150) NOT NULL,
    email      VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(45)  NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    update_at  DATETIME NULL,
    ativo      TINYINT(1) DEFAULT 1
);

CREATE TABLE servicos (
    id_service      BIGINT AUTO_INCREMENT PRIMARY KEY,
    description     VARCHAR(45)  NOT NULL,
    price           DECIMAL(11,3) NOT NULL,
    status          VARCHAR(20) DEFAULT 'Pendente',
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    update_at       DATETIME NULL,
    finished_at     DATETIME NULL,
    commission_user DECIMAL(11,3) NULL,
    user_id_user    BIGINT NOT NULL,
    CONSTRAINT fk_servico_usuario FOREIGN KEY (user_id_user) REFERENCES usuarios(id_user)
);
```

Insira um usuário de teste:

```sql
INSERT INTO usuarios (name, email, password) VALUES ('Luis', 'luisfellipe_rj@hotmail.com', '123');
```

> ⚠️ A senha é armazenada em **texto puro** neste projeto (apenas para fins de estudos/demo). Para produção, use `password_hash()` / `password_verify()`.

### 4. Ajustar a conexão com o banco

Em `config/config.php`, confira as credenciais (usuário padrão do Wamp: `root` sem senha):

```php
return [
    'host'     => 'localhost',
    'dbname'   => 'ordem_servicos',
    'user'     => 'root',
    'password' => ''
];
```
### 5. Configurar o envio de e-mail (PHPMailer/SMTP)

Em `config/email.php`, preencha com seus dados de SMTP. Para o **Gmail**, use uma **Senha de App** (Conta Google → Segurança → "Senhas de app", com verificação em duas etapas ativada):

```php
return [
    'host'      => 'smtp.gmail.com',
    'port'      => 587,
    'secure'    => 'tls',
    'username'  => 'seuemail@gmail.com',
    'password'  => 'SUA_SENHA_DE_APP_16_DIGITOS',
    'from'      => 'seuemail@gmail.com',
    'from_name' => 'Sistema Ordem de Serviço',
];
```

Outros serviços SMTP comuns:
| Provedor | Host | Porta | Segurança |
|----------|------|-------|-----------|
| Gmail | `smtp.gmail.com` | 587 | tls |
| Outlook/Hotmail | `smtp.office365.com` | 587 | tls |
| Mailtrap (teste) | `smtp.mailtrap.io` | 2525 | tls |

> A biblioteca PHPMailer já está incluída em `vendor/phpmailer/`. O `composer.json` referencia `^6.9` caso queira reinstalar via Composer.

### 6. Acessar o sistema

Abra no navegador:

```
http://localhost/Order_of_Service/public/
```

Ou, se tiver um **virtual host** configurado (recomendado):

```
http://ordemdeservico.local/
```

(veja o tópico extra abaixo sobre como criar o virtual host no Wamp).

---

## 🔑 Credenciais de acesso (exemplo)

| Email | Senha |
|-------|-------|
| `luis.fellipe_rj@hotmail.com` | `123` |

> Acertando o usuário inserido no passo 3.

---

## 🧩 Funcionalidades

### Login/Logout
- Autenticação por e-mail + senha com sessão PHP.
- Botão "Sair" encerra a sessão.

### Dashboard
- Tabela de serviços prestados (ID, descrição, status, valor, comissão, usuário, data de finalização).
- Lista destacada dos últimos serviços **pendentes**.
- Filtros por período (inicial/final), nome do serviço, status e usuário.
- Botões **Alterar**, **Excluir** e **Finalizar** por registro.
- Valor total dos serviços exibidos.

### Cadastro de serviço
- Tela dedicada para adicionar novo serviço (descrição + valor).
- Valida que descrição e valor (numérico > 0) sejam informados.
- Todo serviço nasce com status **Pendente**.
- Mensagens de sucesso/falha ao salvar ou ao redirecionar.

### Finalização de serviço
- Ao clicar em **Finalizar**, o sistema:
  1. Grava a **data de finalização** (`finished_at`);
  2. Calcula a **comissão**;
  3. Envia **e-mail** para o usuário logado na sessão com a comissão.
- Regra de status: **tem `finished_at` = Finalizado**; **não tem = Pendente**.

### Cálculo de comissão
| Valor do serviço | Comissão |
|------------------|-----------|
| ≤ R$ 1.000,00 | 5% |
| > R$ 1.000,00 e ≤ R$ 10.000,00 | 10% |
