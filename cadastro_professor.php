<?php
session_start();
require_once 'auth.php';
$pdo = getPDO();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $escola = trim($_POST['escola']);

    if ($nome && $email && $senha && $escola) {
        // Verifica se já existe o email
        $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $message = "❌ Já existe um usuário cadastrado com esse email.";
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            // Por padrão, role = professor, primeiro_acesso = 1
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, escola, role, primeiro_acesso) VALUES (?, ?, ?, ?, 'professor', 1)");
            $stmt->execute([$nome, $email, $hash, $escola]);
            $message = "✅ Cadastro realizado com sucesso! Você já pode fazer login.";
        }
    } else {
        $message = "⚠️ Preencha todos os campos.";
    }
}
?>

<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cadastro de Professor - Agenda Escolar</title>
<style>
:root{
  --blue-light:#e8f3ff;
  --blue:#2b6fb3;
  --blue-dark:#1a365d;
  --white:#ffffff;
  --red:#b00020;
  --radius:12px;
}

/* Layout base */
* { box-sizing: border-box; margin:0; padding:0; }
html, body { height:100%; overflow-x:hidden; }
body {
  display:flex;
  flex-direction:column;
  font-family: "Inter", Arial, sans-serif;
  background: var(--white);
  color: var(--blue-dark);
}

/* Header */
.header {
  display:flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 8%;
  background: var(--white);
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  position: sticky;
  top: 0;
  z-index: 100;
}
.header .logo img { height: 120px; }

nav a {
  margin-left: 24px;
  text-decoration: none;
  font-weight: 600;
  color: var(--blue-dark);
  transition: color 0.2s;
}
nav a:hover { color: var(--blue); }

/* Main */
main { flex:1; display:flex; justify-content:center; align-items:center; padding:20px; overflow:auto; }

.hero {
  display:flex;
  gap:40px;
  background: var(--blue-light);
  border-radius: var(--radius);
  box-shadow:0 10px 30px rgba(20,40,80,0.08);
  padding:40px;
  width:100%;
  max-width:900px;
  animation:fadeIn 0.6s ease both;
}
.hero-left { flex:1; display:flex; justify-content:center; align-items:center; }
.hero-left img { width:100%; max-width:400px; border-radius: var(--radius); }
.hero-right { flex:1; display:flex; flex-direction:column; }
.hero-right h2 { margin-bottom:16px; color: var(--blue-dark); }
.hero-right form { display:flex; flex-direction:column; gap:12px; }
.hero-right .input { padding:12px; border-radius:10px; border:1px solid #ccc; width:100%; }
.hero-right .btn-primary { padding:12px; background: var(--blue); color: var(--white); border:none; border-radius:10px; font-weight:600; cursor:pointer; transition: all 0.2s; }
.hero-right .btn-primary:hover { background:#1d4e7a; }
.error-msg { text-align:center; font-weight:600; margin-bottom:12px; }
.error-msg.ok { color:green; }
.error-msg.err { color: var(--red); }

/* Footer */
footer {
  background: var(--blue-dark);
  color: var(--white);
  text-align:center;
  padding:20px;
  font-size:0.9rem;
}

/* Responsivo */
@media(max-width:900px) { .hero { flex-direction:column; } .hero-left img { max-width:300px; margin-bottom:20px; } }

@keyframes fadeIn{ from{opacity:0; transform:translateY(8px);} to{opacity:1; transform:translateY(0);} }
</style>
</head>
<body>

<header class="header">
  <div class="logo"><img src="imagens/AgendaEscolar-removebg.png" alt="Agenda Escolar"></div>
  <nav>
    <a href="index.php">Home</a>
    <a href="login.php">Login</a>
  </nav>
</header>

<main>
  <section class="hero">
    <div class="hero-left">
      <img src="assets/calendario-removebg.png" alt="Imagem inicial">
    </div>
    <div class="hero-right">
      <h2>Cadastrar Novo Professor</h2>
      <?php if($message): ?>
        <p class="error-msg <?= strpos($message, '✅') !== false ? 'ok' : 'err' ?>">
          <?= htmlspecialchars($message) ?>
        </p>
      <?php endif; ?>
      <form method="POST">
        <input class="input" type="text" name="nome" placeholder="Nome completo" required>
        <input class="input" type="email" name="email" placeholder="Email institucional" required>
        <input class="input" type="password" name="senha" placeholder="Senha inicial" required>
        <input class="input" type="text" name="escola" placeholder="Nome da escola" required>
        <button class="btn-primary" type="submit">Cadastrar</button>
        <button class="btn-primary" type="button" onclick="window.location.href='login.php'">Voltar ao Login</button>
      </form>
    </div>
  </section>
</main>

<footer>
  &copy; <?= date('Y') ?> Agenda Escolar. Todos os direitos reservados.
</footer>

</body>
</html>
