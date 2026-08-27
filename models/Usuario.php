<?php

/**
 * Model de Usuário — persistência associada ao login.
 *
 * NOTA DE SEGURANÇA (sênior): a senha atualmente é comparada em texto puro,
 * o que é aceitável apenas para um ambiente de estudo/demo. Para produção,
 * deve-se usar password_hash()/password_verify() e armazenar um hash bcrypt.
 */
require_once(__DIR__ . '/../core/Database.php');

class Usuario {

    /**
     * Busca usuário por e-mail E senha exata, em uma única query.
     * Retorna o array da linha (se achar) ou false.
     */
    public static function buscarPorEmailSenha($email, $senha) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ? AND password = ?");
        $stmt->execute([$email, $senha]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca usuário apenas pelo e-mail — usado para validar duplicidade
     * antes de criar. Retorna a linha ou false.
     */
    public static function buscarPorEmail($email) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id_user FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cria um novo usuário. A coluna `ativo` tem default 1 no banco.
     * Retorna true se inserido com sucesso, false caso contrário.
     */
    public static function criar($nome, $email, $senha) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO usuarios (name, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$nome, $email, $senha]);
    }
}
