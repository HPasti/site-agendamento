-- ============================================================
-- banco.sql - Script limpo para produção
-- Agenda Escolar - EEEFM Antonio dos Santos Neves
-- ============================================================

CREATE DATABASE IF NOT EXISTS agenda_escola
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE agenda_escola;

-- ============================================================
-- Tabela: usuarios
-- ============================================================
CREATE TABLE IF NOT EXISTS usuarios (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  nome            VARCHAR(120)  NOT NULL,
  email           VARCHAR(150)  NOT NULL UNIQUE,
  senha           VARCHAR(255)  NOT NULL,
  escola          VARCHAR(150)  NOT NULL,
  role            ENUM('professor','diretor') NOT NULL DEFAULT 'professor',
  primeiro_acesso TINYINT(1)    DEFAULT 1,
  criado          DATETIME      DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabela: recursos
-- ============================================================
CREATE TABLE IF NOT EXISTS recursos (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  nome              VARCHAR(120) NOT NULL,
  tipo              ENUM('ambiente','equipamento') NOT NULL,
  quantidade_total  INT NULL,
  materias_restritas TEXT NULL,
  criado_por        INT DEFAULT NULL,
  criado_em         DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabela: agendamentos
-- ============================================================
CREATE TABLE IF NOT EXISTS agendamentos (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabela: contatos
-- ============================================================
CREATE TABLE IF NOT EXISTS contatos (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nome        VARCHAR(120) NOT NULL,
  email       VARCHAR(120) NOT NULL,
  telefone    VARCHAR(30),
  instituicao VARCHAR(120),
  segmento    VARCHAR(50),
  mensagem    TEXT,
  lida        TINYINT(1) NOT NULL DEFAULT 0,
  data_envio  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabela: horarios
-- ============================================================
CREATE TABLE IF NOT EXISTS horarios (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  dia        VARCHAR(20) NOT NULL,
  turma      VARCHAR(10) NOT NULL,
  aula       VARCHAR(5)  NOT NULL,
  disciplina VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Usuário administrador padrão (diretor)
-- ATENÇÃO: Gere um hash real com password_hash('diretor123', PASSWORD_DEFAULT)
-- e substitua o valor abaixo antes de importar em produção.
-- O próprio login.php já cria este usuário automaticamente se não existir.
-- ============================================================
-- INSERT IGNORE INTO usuarios (nome, email, senha, escola, role, primeiro_acesso)
-- VALUES (
--   'Juliano Doná',
--   'juliano.dona@educador.edu.es.gov.br',
--   '$2y$10$SEU_HASH_AQUI',
--   'EEEFM Antonio dos Santos Neves',
--   'diretor',
--   1
-- );
