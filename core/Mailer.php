<?php

/**
 * Core/Mailer — envio de e-mail via SMTP usando PHPMailer.
 *
 * O mail() nativo do PHP não funciona no Wamp/Windows (não há SMTP local),
 * então usamos PHPMailer com SMTP autenticado (Gmail/Outlook/etc.).
 * As credenciais ficam em config/email.php.
 */
require_once(__DIR__ . '/../vendor/phpmailer/Exception.php');
require_once(__DIR__ . '/../vendor/phpmailer/PHPMailer.php');
require_once(__DIR__ . '/../vendor/phpmailer/SMTP.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {

    /**
     * Envia um e-mail em HTML via SMTP.
     *
     * Retorna true se enviou com sucesso, ou um array com o erro em debug.
     * @param string $para
     * @param string $assunto
     * @param string $mensagemHtml
     * @return bool
     */
    public static function enviar($para, $assunto, $mensagemHtml) {
        if (!$para || !filter_var($para, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $config = include(__DIR__ . '/../config/email.php');

        // Se o usuário ainda não preencheu as credenciais no config (placeholders),
        // aborta com aviso claro. IMPORTANTE: comparar contra os PLACEHOLDERS,
        // nunca contra os valores reais (senão o guard seria sempre verdadeiro).
        if (
            $config['username'] === 'SEU_EMAIL_GMAIL@gmail.com'
            || trim($config['password']) === ''
            || strpos((string)$config['password'], 'SUA_SENHA') !== false
        ) {
            error_log('[Mailer] Credenciais de SMTP não configuradas em config/email.php');
            return false;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
            $mail->SMTPSecure = $config['secure'];              // 'tls'
            $mail->Port = $config['port'];                       // 587
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($config['from'], $config['from_name']);
            $mail->addAddress($para);                            // destinatário

            $mail->isHTML(true);                                  // corpo em HTML
            $mail->Subject = $assunto;
            $mail->Body = $mensagemHtml;

            return $mail->send();
        } catch (Exception $e) {
            // Registra o erro (ex.: credenciais inválidas, host inacessível) mas
            // não quebra o fluxo do dashboard — o e-mail é opcional ao usuário.
            error_log('[Mailer] Falha no envio: ' . $mail->ErrorInfo);
            return false;
        }
    }
}