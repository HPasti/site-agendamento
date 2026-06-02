<?php
session_start();
require_once 'auth.php';
$pdo = getPDO();
$message = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha_raw = $_POST['senha'] ?? '';
    $escola = $_POST['escola'] ?? '';

    if($nome && $email && $senha_raw && $escola){
        $senha = password_hash($senha_raw, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO usuarios (nome,email,senha,escola) VALUES (?,?,?,?)');
        try{
            $stmt->execute([$nome,$email,$senha,$escola]);

            // Sessão da nova conta
            $user_id = $pdo->lastInsertId();
            $_SESSION['user_id']     = $user_id;
            $_SESSION['user_nome']   = $nome;
            $_SESSION['user_email']  = $email;
            $_SESSION['user_escola'] = $escola;
            // CORREÇÃO #8: definir user_role na sessão
            $_SESSION['user_role']   = 'professor';

            header("Location: inicio.php"); // CORREÇÃO #8: redirecionar para painel correto
            exit;
        } catch(Exception $e){
            $message = 'Erro: provável email já cadastrado.';
        }
    } else {
        $message = 'Preencha todos os campos.';
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cadastro - Agenda Escolar</title>
<style>
:root{
  --blue:#2b6fb3;
  --blue-dark:#1a365d;
  --blue-light:#e8f3ff;
  --white:#ffffff;
  --red:#e74c3c;
  --radius:12px;
}

/* Base */
* { box-sizing:border-box; margin:0; padding:0; }
html, body { height:100%; font-family:"Inter",Arial,sans-serif; background:var(--white); color:var(--blue-dark); }
body { display:flex; flex-direction:column; min-height:100vh; }

/* Header */
header{
  display:flex; justify-content:space-between; align-items:center;
  padding:18px 8%; background:var(--white); box-shadow:0 4px 12px rgba(0,0,0,0.05); position:sticky; top:0; z-index:100;
}
.logo img { height:120px; }
nav a { margin-left:24px; text-decoration:none; font-weight:600; color:var(--blue-dark); transition:color 0.2s; }
nav a:hover { color:var(--blue); }

/* Hero */
main { flex:1; display:flex; justify-content:center; align-items:center; padding:20px; }
.hero { display:flex; flex-wrap:wrap; gap:40px; max-width:900px; width:100%; background:var(--blue-light); border-radius:var(--radius); padding:40px; box-shadow:0 10px 30px rgba(20,40,80,0.08); animation:fadeIn 0.6s ease both; }
.hero-left { flex:1; display:flex; justify-content:center; align-items:center; }
.hero-left img { width:100%; max-width:450px; border-radius:var(--radius); transform:translateY(8px); animation:fadeIn .8s .2s ease both; }
.hero-right { flex:1; background:var(--white); padding:30px; border-radius:var(--radius); box-shadow:0 10px 25px rgba(20,40,80,0.06); display:flex; flex-direction:column; align-items:center; animation:fadeUp .6s ease both; }
.hero-right h2 { margin-bottom:20px; color:var(--blue-dark); font-size:1.8rem; }
.hero-right form { width:100%; display:flex; flex-direction:column; gap:15px; }
.hero-right .input { width:100%; padding:12px; border-radius:10px; border:1px solid #ccc; }
.hero-right select.input { appearance:none; -webkit-appearance:none; -moz-appearance:none; background:url('data:image/svg+xml;utf8,<svg fill="gray" height="20" viewBox="0 0 24 24" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 10px center; background-size:12px; }
.hero-right .btn-primary { width:100%; padding:12px; border:none; border-radius:10px; background:var(--blue-dark); color:var(--white); font-weight:600; cursor:pointer; transition:all 0.2s; }
.hero-right .btn-primary:hover { background:#1a2a4c; }
.hero-right .login-link { margin-top:15px; font-size:0.95rem; color:var(--blue-dark); }
.hero-right .login-link a { text-decoration:none; font-weight:600; color:var(--blue-dark); }
.hero-right .login-link a:hover { text-decoration:underline; }
.error-msg { color:var(--red); font-weight:600; margin-bottom:12px; text-align:center; }

/* Footer */
footer { background:var(--blue-dark); color:var(--white); text-align:center; padding:20px; font-size:0.9rem; margin-top:auto; }

/* Animations */
@keyframes fadeUp{ from{opacity:0; transform:translateY(18px);} to{opacity:1; transform:translateY(0);} }
@keyframes fadeIn{ from{opacity:0; transform:translateY(8px);} to{opacity:1; transform:translateY(0);} }

/* Responsivo */
@media(max-width:900px){ .hero{flex-direction:column;} .hero-left img{max-width:350px; margin-bottom:20px;} }
</style>
</head>
<body>

<header>
  <div class="logo"><img src="imagens/AgendaEscolar-removebg.png" alt="Agenda Escolar"></div>
  <nav>
    <a href="index.php">Home</a>
    <a href="login1.php">Login</a>
  </nav>
</header>

<main>
<section class="hero">
  <div class="hero-left">
    <img src="assets/img.png" alt="Cadastro">
  </div>
  <div class="hero-right">
    <h2>Cadastre-se</h2>
    <?php if($message): ?>
      <p class="error-msg"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="POST">
      <input class="input" type="text" name="nome" placeholder="Nome completo" required>
      <input class="input" type="email" name="email" placeholder="Email" required>
      <input class="input" type="password" name="senha" placeholder="Senha" required>
      <select class="input" name="escola" required>
        <option value="">Selecione sua escola</option>
        <option value="EEEFM Antonio dos Santos Neves">EEEFM Antônio dos Santos Neves</option>
      </select>
      <button class="btn-primary" type="submit">Criar Conta</button>
    </form>
    <div class="login-link">
      Já possui conta? <a href="login1.php">Faça login</a>
    </div>
  </div>
</section>
</main>

<footer>
  © 2025 Agenda Escolar - Todos os direitos reservados
</footer>

</body>
</html>