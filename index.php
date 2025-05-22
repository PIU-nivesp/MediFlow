<?php
session_start();

// Conexão com o banco de dados
$host = 'localhost';
$db = 'DB_MEDIFLOW'; // Nome do banco correto
$user = 'root';
$pass = 'usbw';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Cadastro de novo usuário
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $cargo = $_POST['cargo'];
    $senha_adm_digitada = $_POST['confirmar_senha_adm'];

    // Verifica se a senha do administrador está correta
    $stmt_adm = $pdo->prepare("SELECT senha_hash FROM usuarios WHERE nome = 'ADM' OR email = 'ADM'");
    $stmt_adm->execute();
    $adm = $stmt_adm->fetch(PDO::FETCH_ASSOC);

    if (!$adm || hash('sha256', $senha_adm_digitada) !== $adm['senha_hash']) {
        echo "<script>alert('Senha do administrador incorreta!'); window.history.back();</script>";
        exit;
    }

    // Verifica se e-mail já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    if ($stmt->fetch()) {
        echo "<script>alert('Este e-mail já está cadastrado!');</script>";
    } else {
        $senha_hash = hash('sha256', $senha);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, cargo) VALUES (:nome, :email, :senha_hash, :cargo)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha_hash', $senha_hash);
        $stmt->bindParam(':cargo', $cargo);
        $stmt->execute();
        echo "<script>alert('Usuário cadastrado com sucesso!'); window.location.href='index.php';</script>";
        exit;
    }
}


// Verifica se os dados foram enviados
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    // Busca por email ou nome no banco
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :usuario OR nome = :usuario LIMIT 1");
    $stmt->bindParam(':usuario', $usuario);
    $stmt->execute();

    $usuarioEncontrado = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica se encontrou usuário e se a senha bate
    if ($usuarioEncontrado && hash('sha256', $senha) === $usuarioEncontrado['senha_hash']) {
        // Login bem-sucedido
        $_SESSION['usuario_id'] = $usuarioEncontrado['id'];
        $_SESSION['nome'] = $usuarioEncontrado['nome'];
        $_SESSION['cargo'] = $usuarioEncontrado['cargo'];

        header("Location: dashboard.php");
        exit;
    } else {
        echo "<script>alert('Usuário ou senha incorretos!');</script>";
    }

}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <style>
    /* Seu CSS permanece igual */
    * {
      box-sizing: border-box;
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }

    body {
      background-color: #0c617c;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .login-container {
      background: white;
      padding: 40px 30px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      width: 320px;
      text-align: center;
    }

    .login-container img.logo {
      width: 60px;
      margin-bottom: 20px;
    }

    .login-container h2 {
      font-size: 18px;
      margin-bottom: 20px;
    }

    .social-buttons {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-bottom: 25px;
    }

    .social-button {
      width: 45px;
      height: 45px;
      background: white;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
      cursor: pointer;
    }

    .social-button img {
      width: 24px;
      height: 24px;
    }

    input[type="text"],
    input[type="password"],
    input[type="email"],
    select {
      width: 100%;
      padding: 10px;
      margin: 8px 0 16px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .login-button {
      width: 100%;
      background-color: #007bff;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 25px;
      font-weight: bold;
      cursor: pointer;
      margin-bottom: 15px;
    }

    .forgot-password {
      font-size: 14px;
    }

    .forgot-password a {
      color: #007bff;
      text-decoration: none;
    }

    .forgot-password a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <!-- Sugestão: coloque as imagens na pasta do projeto e use caminho relativo -->
    <img src="img/LOGO.png" alt="Logo" class="logo" />
    <h2>Efetue seu login</h2>
    <div class="social-buttons">
      <div class="social-button">
        <img src="img/goog.png" alt="Google" />
      </div>
      <div class="social-button">
        <img src="img/fac.png" alt="Facebook" />
      </div>
    </div>
    <form action="index.php" method="POST">
      <input type="text" name="usuario" placeholder="E-mail ou Usuario" required />
      <input type="password" name="senha" placeholder="Senha" required />
      <button class="login-button" type="submit">Acessar</button>
      
      <!-- Botão Cadastrar inserido aqui -->
      <button class="login-button" type="button" onclick="mostrarFormularioCadastro()">Cadastrar</button>
    </form>

    <!-- Agora vem a parte de "Esqueceu a senha?" -->
    <div class="forgot-password">
      Esqueceu sua senha? <a href="#" id="recuperarSenha">Clique aqui</a>
    </div>
  </div>

  <div class="login-container" id="formCadastro" style="display: none;">
    <h2>Cadastrar novo usuário</h2>
    <form action="index.php" method="POST">
      <input type="hidden" name="acao" value="cadastrar" />
      <input type="text" name="nome" placeholder="Nome completo" required />
      <input type="email" name="email" placeholder="E-mail" required />
      <input type="password" name="senha" placeholder="Senha" required />
      <input type="password" name="confirmar_senha_adm" placeholder="Senha do administrador" required />
      <select name="cargo" required>
        <option value="">Selecione o cargo</option>
        <option value="farmaceutico">Farmacêutico</option>
        <option value="tecnico">Técnico</option>
      </select>
      <button class="login-button" type="submit">Cadastrar</button>
      <button class="login-button" type="button" onclick="ocultarFormularioCadastro()">Cancelar</button>
    </form>
  </div>


  <script>
document.addEventListener("DOMContentLoaded", function () {
  const forgotLink = document.querySelector(".forgot-password a");

  forgotLink.addEventListener("click", function (e) {
    e.preventDefault();

    const usuario = document.querySelector('input[name="usuario"]').value.trim();
    if (!usuario) {
      alert("Por favor, preencha o campo de usuário (e-mail ou nome) antes de clicar.");
      return;
    }
  });
});
</script>

<script>
  function mostrarFormularioCadastro() {
    document.querySelector(".login-container").style.display = "none";
    document.getElementById("formCadastro").style.display = "block";
  }

  function ocultarFormularioCadastro() {
    document.getElementById("formCadastro").style.display = "none";
    document.querySelector(".login-container").style.display = "block";
  }
</script>

</body>
</html>
