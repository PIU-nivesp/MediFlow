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
    if ($usuarioEncontrado && $senha === $usuarioEncontrado['password_hash']) {
        // Login bem-sucedido
        $_SESSION['usuario_id'] = $usuarioEncontrado['id'];
        $_SESSION['nome'] = $usuarioEncontrado['nome'];
        $_SESSION['cargo'] = $usuarioEncontrado['cargo'];

        header("Location: dashboard.php");
        exit;
    } else {
        echo "<script>alert('Usuário ou senha incorretos!'); window.location.href='index.php';</script>";
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

    input[type="text"], input[type="password"] {
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
    </form>
    <div class="forgot-password">
      Esqueceu sua senha? <a href="#">Clique aqui</a>
    </div>
  </div>
</body>
</html>
