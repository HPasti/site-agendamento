<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sobre - Agendamento Escolar</title>
  <style>
    :root {
      --blue-dark: #1a365d;
      --blue: #2b6fb3;
      --blue-light: #e8f3ff;
      --white: #ffffff;
      --radius: 12px;
      --orange: #1a365d;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: "Inter", Arial, sans-serif;
      color: var(--blue-dark);
      background: var(--white);
      line-height: 1.5;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 18px 8%;
      background: var(--white);
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      position: sticky;
      top: 0;
      z-index: 100;
    }
   .logo img { 
  height: 120px; /* aumentei de 80px para 120px */
}

    nav a {
      margin-left: 24px;
      text-decoration: none;
      font-weight: 600;
      color: var(--blue-dark);
      transition: color 0.2s;
    }
    nav a:hover { color: var(--blue); }

    .hero {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 60px 8%;
      background: var(--blue-light);
      flex-wrap: wrap;
      gap: 40px;
    }
    .hero-text {
      flex: 1;
      max-width: 600px;
    }
    .hero-text h1 {
      font-size: 2.5rem;
      margin-bottom: 16px;
      color: var(--blue-dark);
    }
    .hero-text p {
      margin-bottom: 24px;
      font-size: 1.1rem;
      color: #23445f;
    }
    .hero-text span.orange { color: var(--orange); font-weight: bold; font-size: 2.2rem; }

    .hero img {
      max-width: 420px;
      width: 100%;
      border-radius: var(--radius);
    }

    section { padding: 60px 8%; }
    .section-title {
      text-align: center;
      font-size: 1.8rem;
      margin-bottom: 32px;
      color: var(--blue-dark);
    }

    .text-center { text-align: center; }
    .highlight { font-weight: bold; color: var(--blue-dark); }

    .circle-img {
      width: 200px;
      height: 200px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 20px;
    }

    .content-block {
      display: flex;
      flex-wrap: wrap;
      gap: 40px;
      align-items: center;
      margin-bottom: 60px;
    }
    .content-block img { flex: 0 0 200px; }
    .content-block .text { flex: 1; min-width: 280px; }

    .recognition {
      display: flex;
      flex-wrap: wrap;
      gap: 40px;
      justify-content: center;
      align-items: center;
      text-align: center;
    }
    .recognition img { width: 120px; }

    footer {
      background: var(--blue-dark);
      color: var(--white);
      text-align: center;
      padding: 20px;
      font-size: 0.9rem;
    }

    @media (max-width: 768px) {
      .hero { flex-direction: column; text-align: center; }
      .content-block { flex-direction: column; text-align: center; }
    }
  </style>
</head>
<body>

  <header>
    <div class="logo">
      <img src="imagens/AgendaEscolar-removebg.png" alt="Logo">
    </div>
    <nav>
      <a href="index.php">Início</a>
      <a href="contato.php">Contato</a>
    </nav>
  </header>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-text">
      <p>"Quem organiza um espaço de aprendizado tem o poder de transformar o dia a dia da escola."</p>
      <h1>
        Agendamento de <span class="orange">Recursos Escolares</span>
      </h1>
      <p>Facilitamos o agendamento de salas, equipamentos e materiais pedagógicos, tornando o planejamento escolar mais eficiente e organizado.</p>
    </div>
    <img src="imagens/pc.png" alt="Agendamento">
  </section>

  <!-- Connecting Section -->
  <section>
    <div class="section-title">Nosso propósito</div>
    <div class="text-center">
      <p>A plataforma conecta <span class="highlight">professores</span>, <span class="highlight">coordenadores</span> e <span class="highlight">diretores</span>, abrindo novas oportunidades de organização e colaboração na escola.</p>
    </div>
  </section>

  <!-- Content Block -->
  <section>
    <div class="content-block">
      <img src="imagens/ma.png" alt="Professor" class="circle-img">
      <div class="text">
        <h3>Como funciona</h3>
        <p>Os usuários podem agendar recursos escolares de forma simples e prática. Cada recurso possui disponibilidade atualizada e histórico de uso, garantindo planejamento eficiente.</p>
      </div>
    </div>

    <div class="content-block">
      <img src="imagens/canvaimg.png" alt="Sala" class="circle-img">
      <div class="text">
        <h3>Vantagens</h3>
        <p>Evita conflitos de horários, melhora a comunicação entre setores da escola e garante que todos os recursos sejam utilizados da melhor forma possível.</p>
      </div>
    </div>
  </section>

  <!-- Recognition Section -->
  

  <footer>
    &copy; 2025 Agendamento Escolar. Todos os direitos reservados.
  </footer>

</body>
</html>
