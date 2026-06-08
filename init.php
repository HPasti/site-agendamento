<?php
// init.php - Criação automática das tabelas
// ATENÇÃO: Delete ou renomeie este arquivo após executar!

require_once 'auth.php';

$token = $_GET['token'] ?? '';
if ($token !== 'agenda2025') {
    die('Acesso negado.');
}

try {
    $pdo = getPDO();

    $sqls = [
        "usuarios" => "CREATE TABLE IF NOT EXISTS usuarios (
            id              INT AUTO_INCREMENT PRIMARY KEY,
            nome            VARCHAR(120)  NOT NULL,
            email           VARCHAR(150)  NOT NULL UNIQUE,
            senha           VARCHAR(255)  NOT NULL,
            escola          VARCHAR(150)  NOT NULL,
            role            ENUM('professor','diretor') NOT NULL DEFAULT 'professor',
            primeiro_acesso TINYINT(1)    DEFAULT 1,
            criado          DATETIME      DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "recursos" => "CREATE TABLE IF NOT EXISTS recursos (
            id                INT AUTO_INCREMENT PRIMARY KEY,
            nome              VARCHAR(120) NOT NULL,
            tipo              ENUM('ambiente','equipamento') NOT NULL,
            quantidade_total  INT NULL,
            materias_restritas TEXT NULL,
            criado_por        INT DEFAULT NULL,
            criado_em         DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "agendamentos" => "CREATE TABLE IF NOT EXISTS agendamentos (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            user_id     INT NOT NULL,
            recurso     VARCHAR(120) NOT NULL,
            data        DATE NOT NULL,
            horario     TINYINT NOT NULL DEFAULT 1,
            ilha        VARCHAR(10) NULL,
            quantidade  INT NULL,
            criado      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            expires_at  DATETIME NULL,
            FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            INDEX idx_recurso_data_horario (recurso, data, horario),
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "contatos" => "CREATE TABLE IF NOT EXISTS contatos (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            nome        VARCHAR(120) NOT NULL,
            email       VARCHAR(120) NOT NULL,
            telefone    VARCHAR(30),
            instituicao VARCHAR(120),
            segmento    VARCHAR(50),
            mensagem    TEXT,
            lida        TINYINT(1) NOT NULL DEFAULT 0,
            data_envio  DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "horarios" => "CREATE TABLE IF NOT EXISTS horarios (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            dia        VARCHAR(20) NOT NULL,
            turma      VARCHAR(10) NOT NULL,
            aula       VARCHAR(5)  NOT NULL,
            disciplina VARCHAR(50) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];

    $criadas = [];
    foreach ($sqls as $tabela => $sql) {
        $pdo->exec($sql);
        $criadas[] = $tabela;
    }

    echo "<h2>✅ Tabelas criadas com sucesso!</h2><ul>";
    foreach ($criadas as $t) {
        echo "<li>✅ $t</li>";
    }
    echo "</ul>";
    echo "<p><strong>⚠️ Delete o arquivo init.php do repositório agora!</strong></p>";

} catch (Exception $e) {
    echo "<h2>❌ Erro</h2><pre>" . $e->getMessage() . "</pre>";
}
?>
