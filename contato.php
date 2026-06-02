<?php
// CORREÇÃO #11: usar auth.php (padrão do projeto) em vez de config.php
require_once 'auth.php';
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nome = trim($_POST['nome'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $telefone = trim($_POST['telefone'] ?? '');
  $instituicao = trim($_POST['instituicao'] ?? '');
  $segmento = trim($_POST['segmento'] ?? '');
  $mensagem = trim($_POST['mensagem'] ?? '');

  if (!empty($nome) && !empty($email) && !empty($mensagem)) {
    try {
      $sql = "INSERT INTO contatos (nome, email, telefone, instituicao, segmento, mensagem)
              VALUES (:nome, :email, :telefone, :instituicao, :segmento, :mensagem)";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        ':nome' => $nome,
        ':email' => $email,
        ':telefone' => $telefone,
        ':instituicao' => $instituicao,
        ':segmento' => $segmento,
        ':mensagem' => $mensagem
      ]);
      echo "<script>alert('Mensagem enviada com sucesso!'); window.location='contato.php';</script>";
      exit;
    } catch (PDOException $e) {
      echo "<script>alert('Erro ao enviar mensagem: " . $e->getMessage() . "');</script>";
    }
  } else {
    echo "<script>alert('Por favor, preencha os campos obrigatórios.');</script>";
  }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contato - Agenda Escolar</title>
<style>
:root {
  --blue-dark: #1a365d;
  --blue: #2b6fb3;
  --blue-light: #e8f3ff;
  --white: #ffffff;
  --radius: 12px;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: "Inter", Arial, sans-serif;
  color: var(--blue-dark);
  background: var(--white);
  line-height: 1.5;
}

/* Header */
header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 8%;
  background: var(--white);
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  position: sticky;
  top: 0;
}
.logo img { height: 120px; }

nav a {
  margin-left: 24px;
  text-decoration: none;
  font-weight: 600;
  color: var(--blue-dark);
  transition: color 0.2s;
}
nav a:hover { color: var(--blue); }

/* Hero */
.hero {
  text-align: center;
  padding: 60px 8%;
  background: var(--blue-light);
  border-radius: var(--radius);
  margin: 40px 8%;
}
.hero h1 { font-size: 2.5rem; margin-bottom: 16px; }
.hero p { font-size: 1.1rem; color: #23445f; }

/* Formulário */
.contact-form {
  max-width: 700px;
  margin: 0 auto 60px auto;
  background: var(--white);
  padding: 40px;
  border-radius: var(--radius);
  box-shadow: 0 4px 16px rgba(0,0,0,0.05);
}

.contact-form label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
}

.contact-form input,
.contact-form select,
.contact-form textarea {
  width: 100%;
  padding: 12px 15px;
  margin-bottom: 20px;
  border: 1px solid #ccc;
  border-radius: var(--radius);
  font-size: 1rem;
}

.contact-form textarea { min-height: 120px; resize: vertical; }

.contact-form button {
  background-color: var(--blue);
  color: var(--white);
  padding: 14px 28px;
  border: none;
  border-radius: var(--radius);
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.contact-form button:hover { background-color: #1e549a; }

/* Footer */
footer {
  background: var(--blue-dark);
  color: var(--white);
  text-align: center;
  padding: 20px;
  font-size: 0.9rem;
}
</style>
</head>
<body>

<header>
  <div class="logo"><img src="imagens/AgendaEscolar-removebg.png" alt="Logo"></div>
  <nav>
    <a href="index.php">Início</a>
    <a href="sobre.php">Sobre</a>
  </nav>
</header>

<section class="hero">
  <h1>Fale Conosco</h1>
  <p>Envie uma mensagem sobre agendamentos escolares ou dúvidas sobre nossos serviços.</p>
</section>

<section class="contact-form">
  <form method="POST">
    <label for="nome">Nome completo</label>
    <input type="text" id="nome" name="nome" placeholder="Seu nome" required>

    <label for="email">E-mail</label>
    <input type="email" id="email" name="email" placeholder="Seu e-mail" required>

    <label for="telefone">Telefone</label>
    <input type="text" id="telefone" name="telefone" placeholder="(11) 99999-9999">

    <label for="instituicao">Instituição</label>
    <input type="text" id="instituicao" name="instituicao" placeholder="Nome da escola">

    <label for="segmento">Segmento</label>
    <select id="segmento" name="segmento">
      <option value="">Selecione</option>
      <option value="Infantil">Ensino Infantil</option>
      <option value="Fundamental I">Ensino Fundamental I</option>
      <option value="Fundamental II">Ensino Fundamental II</option>
      <option value="Médio">Ensino Médio</option>
      <option value="Superior">Ensino Superior</option>
    </select>

    <label for="mensagem">Mensagem</label>
    <textarea id="mensagem" name="mensagem" placeholder="Digite sua mensagem..." required></textarea>

    <button type="submit">Enviar</button>
  </form>
</section>

<footer>
  &copy; 2025 Agenda Escolar. Todos os direitos reservados.
</footer>

</body>
</html>
