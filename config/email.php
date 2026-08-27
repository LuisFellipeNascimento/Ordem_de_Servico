<?php
/**
 * Configuração de envio de e-mail via SMTP (PHPMailer).
 *
 * PREENCHA AQUI os dados do seu Gmail (SMTP). Para a senha, use uma
 * "senha de app" gerada em: Conta Google -> Segurança -> "Senhas de app"
 * (exige a verificação em 2 etapas ativada).
 */
return [
    'host'     => 'smtp.gmail.com',
    'port'     => 587,
    'secure'   => 'tls',
    'username' => 'luisfellipe13@gmail.com',
    'password' => 'rmzn zwvu czqx ohyo',
    'from'     => 'luisfellipe13@gmail.com',
    'from_name'=> 'Sistema Ordem de Serviço',
];