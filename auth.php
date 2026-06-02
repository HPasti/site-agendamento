<?php
// auth.php - funções de autenticação e conexão

// -------------------------------------------------------
// PRODUÇÃO: substitua os valores abaixo pelos dados reais
// criados no cPanel antes de fazer o deploy.
// -------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'agenda_escola');   // nome do banco no cPanel
define('DB_USER', 'root');            // usuário do banco no cPanel
define('DB_PASS', '');                // senha do banco no cPanel
// -------------------------------------------------------

function getPDO(){
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (Exception $e) {
        error_log('[agenda_escola] Falha na conexão com o banco: ' . $e->getMessage());
        die('Erro de conexão com o banco de dados. Tente novamente mais tarde.');
    }
}

function isLoggedIn(){
    return isset($_SESSION['user_id']);
}

function requireLogin(){
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireDirector(){
    requireLogin();
    if (($_SESSION['user_role'] ?? '') !== 'diretor') {
        header('Location: inicio.php');
        exit;
    }
}
?>
