<?php

session_start();

if (isset($_POST['logout'])) {
    session_destroy(); // Encerra a sessão
    header("Location: index.php"); // Redireciona
    exit;
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$conn = new mysqli("localhost", "root", "usbw", "DB_MEDIFLOW");

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT nome FROM usuarios WHERE id = $usuario_id";
$result = $conn->query($sql);

$nome_usuario = "Usuário";
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $nome_usuario = htmlspecialchars($row['nome']);
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['dispensar'])) {
    $conn = new mysqli("localhost", "root", "usbw", "DB_MEDIFLOW");

    if ($conn->connect_error) {
        die("Erro de conexão: " . $conn->connect_error);
    }

    $nome_cliente = $conn->real_escape_string($_POST["nome_cliente"]);
    $retirar = $_POST["retirar"];

    foreach ($retirar as $medicamento_id => $quantidade) {
        $quantidade = (int)$quantidade;
        if ($quantidade > 0) {
            // Atualizar estoque
            $conn->query("UPDATE estoque SET quantidade = quantidade - $quantidade WHERE medicamento_id = $medicamento_id");

            // Inserir movimentação
            $stmt = $conn->prepare("
                INSERT INTO movimentacoes (medicamento_id, tipo, quantidade, observacao)
                VALUES (?, 'saida', ?, ?)
            ");
            $observacao = "Retirado por: " . $nome_cliente;
            $stmt->bind_param("iis", $medicamento_id, $quantidade, $observacao);
            $stmt->execute();
            $stmt->close();
        }
    }

    $conn->close();
    echo "<script>alert('Medicamento(s) dispensado(s) com sucesso!'); location.href=location.href;</script>";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['adicionar_estoque'])) {
    $conn = new mysqli("localhost", "root", "usbw", "DB_MEDIFLOW");

    if ($conn->connect_error) {
        die("Erro de conexão: " . $conn->connect_error);
    }

    $adicionar = $_POST["adicionar"];

    foreach ($adicionar as $medicamento_id => $quantidade) {
        $quantidade = (int)$quantidade;
        if ($quantidade > 0) {
            // Verifica se já há estoque
            $verifica = $conn->query("SELECT id FROM estoque WHERE medicamento_id = $medicamento_id");

            if ($verifica->num_rows > 0) {
                // Atualiza
                $conn->query("UPDATE estoque SET quantidade = quantidade + $quantidade WHERE medicamento_id = $medicamento_id");
            } else {
                // Insere novo registro de estoque
                $conn->query("INSERT INTO estoque (medicamento_id, quantidade) VALUES ($medicamento_id, $quantidade)");
            }

            // Log de movimentação
            $stmt = $conn->prepare("
                INSERT INTO movimentacoes (medicamento_id, tipo, quantidade, observacao)
                VALUES (?, 'entrada', ?, ?)
            ");
            $obs = "Entrada manual via popup";
            $stmt->bind_param("iis", $medicamento_id, $quantidade, $obs);
            $stmt->execute();
            $stmt->close();
        }
    }

    $conn->close();
    echo "<script>alert('Estoque atualizado com sucesso!'); location.href=location.href;</script>";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['excluir_remedio'])) {
    $conn = new mysqli("localhost", "root", "usbw", "DB_MEDIFLOW");

    if ($conn->connect_error) {
        die("Erro de conexão: " . $conn->connect_error);
    }

    $excluir = $_POST["excluir"];

    foreach ($excluir as $medicamento_id => $valor) {
        $medicamento_id = (int)$medicamento_id;

        // Buscar quantidade atual
        $res = $conn->query("SELECT quantidade FROM estoque WHERE medicamento_id = $medicamento_id");
        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $quantidade_atual = (int)$row["quantidade"];

            if ($quantidade_atual > 0) {
                // Zerar estoque
                $conn->query("UPDATE estoque SET quantidade = 0 WHERE medicamento_id = $medicamento_id");

                // Registrar movimentação
                $stmt = $conn->prepare("
                    INSERT INTO movimentacoes (medicamento_id, tipo, quantidade, observacao)
                    VALUES (?, 'saida', ?, ?)
                ");
                $obs = "Exclusão manual - estoque zerado";
                $stmt->bind_param("iis", $medicamento_id, $quantidade_atual, $obs);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    $conn->close();
    echo "<script>alert('Medicamentos excluídos com sucesso!'); location.href=location.href;</script>";
}


?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Controle de Estoque</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>

  /* Pop-up modal */
.overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.6);
  display: none;
  justify-content: center;
  align-items: center;
  z-index: 2000;
}

.popup-form {
  background: white;
  padding: 30px;
  border-radius: 10px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 0 20px rgba(0,0,0,0.3);
}

.btn-primary{
  background-color: #0F6181;
}
.btn-primary:hover{
  background-color: #08475f;
}

.btn-secondary{
  background-color: #8B0000;
}



    body {
      font-family: Arial, sans-serif;
      background-color: #effaff;
      padding-top: 100px;
    }

    .bottom-nav,
    .top-nav {
      background-color: #0F6181;
      padding: 10px 0;
    }

    .bottom-nav {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      justify-content: space-around;
    }

    .top-nav {
    background-color: #0F6181 !important;
    justify-content: center;
    align-items: center;
    height: 80px;
    width: 100vw;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    }

    .btn-dispensar {
    background-color: white;
    color: #0F6181;
    border: 2px solid #0F6181;
    border-radius: 8px;
    padding: 8px 16px;
    font-weight: bold;
    transition: background-color 0.3s ease;
    }

    .btn-dispensar:hover {
      background-color: #08475f;
      color: white;
    }


    .me-2{
      width: 45px;
    }

    .estoque-title{
        color: #12a73f !important;
        font-size: 40px;
    }

    .card-estoque {
      background-color: #0f618173;
      border-radius: 15px;
      padding: 15px;
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      justify-content: flex-start;
      margin-bottom: 10px;
      max-height: 250px;
      overflow-y: auto;
    }

    .sem-estoque-title{
        font-size: 40px;
    }

    .card-sem-estoque {
      background-color: #F8D7DA;
      border-radius: 15px;
      padding: 15px;
      display: flex;
      justify-content: flex-start;
      gap: 15px;
      margin-bottom: 10px;
      max-height: 250px;
      overflow-y: auto;
    }

    .remedio-card {
      background-color: white;
      border-radius: 10px;
      padding: 10px;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      width: 150px;
    }

    .remedio-img {
      width: 120px;
      height: 120px;
      margin-bottom: 5px;
    }

    .gerenciador-title{
        color: #0F6181;
        font-size: 40px;
    }

    .btn-cadastrar {
      background-color: #0F6181;
      color: white;
    }

    .btn-excluir {
      background-color: #8B0000;
      color: white;
    }

    .estoque-title, .sem-estoque-title, .gerenciador-title {
      margin-top: 20px;
      font-weight: bold;
    }

    .bem-vindo-text {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 300px;
    }

    .text-white {
      white-space: normal !important;
    }


  </style>
</head>

<!-- HTML DO POP UP -->
<!-- Modal Pop-up -->
<div id="popup" class="overlay">
  <div class="popup-form">
    <h4>Dispensar Remédio</h4>
    <form method="POST" action="">
      <div class="mb-3">
        <label for="nome" class="form-label">Nome do cliente</label>
        <input type="text" class="form-control" name="nome_cliente" required>
      </div>

      <?php
      $conn = new mysqli("localhost", "root", "usbw", "DB_MEDIFLOW");

      if ($conn->connect_error) {
          die("Erro na conexão: " . $conn->connect_error);
      }

      $sql = "
        SELECT m.id, m.nome, m.imagem, e.quantidade
        FROM medicamentos m
        INNER JOIN estoque e ON m.id = e.medicamento_id
        WHERE e.quantidade > 0
        ORDER BY m.nome ASC
      ";

      $result = $conn->query($sql);
      if ($result->num_rows > 0) {
          echo '<div class="mb-3"><label class="form-label">Medicamentos Disponíveis</label>';
          while ($row = $result->fetch_assoc()) {
              echo '<div class="d-flex align-items-center mb-2">';
              echo '<img src="img/' . htmlspecialchars($row["imagem"]) . '" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">';
              echo '<span class="me-2" style="min-width: 150px;">' . htmlspecialchars($row["nome"]) . ' (' . $row["quantidade"] . ' disponíveis)</span>';
              echo '<input type="number" name="retirar[' . $row["id"] . ']" class="form-control" min="0" max="' . $row["quantidade"] . '" placeholder="Qtd" style="width: 80px;">';
              echo '</div>';
          }
          echo '</div>';
      } else {
          echo "<p>Nenhum medicamento disponível.</p>";
      }

      $conn->close();
      ?>

      <button type="submit" name="dispensar" class="btn btn-primary">ENVIAR</button>
      <button type="button" class="btn btn-secondary ms-2" onclick="fecharPopup()">Cancelar</button>
    </form>
  </div>
</div>

<!-- Modal para Cadastro de Remédio no Estoque -->
<div id="popupCadastrar" class="overlay">
  <div class="popup-form">
    <h4>Adicionar ao Estoque</h4>
    <form method="POST" action="">
      <div class="mb-3">
        <label for="filtro_medicamento" class="form-label">Buscar Medicamento</label>
        <input type="text" class="form-control" id="filtro_medicamento" placeholder="Digite o nome do remédio...">
      </div>

      <div class="mb-3" id="lista-medicamentos">
        <!-- Os medicamentos serão preenchidos via PHP -->
        <?php
        $conn = new mysqli("localhost", "root", "usbw", "DB_MEDIFLOW");
        $sql = "SELECT id, nome, imagem FROM medicamentos ORDER BY nome ASC";
        $res = $conn->query($sql);
        if ($res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                echo '<div class="d-flex align-items-center mb-2 medicamento-item">';
                echo '<img src="img/' . htmlspecialchars($row["imagem"]) . '" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">';
                echo '<span class="me-2" style="min-width: 150px;">' . htmlspecialchars($row["nome"]) . '</span>';
                echo '<input type="number" name="adicionar[' . $row["id"] . ']" class="form-control" min="0" placeholder="Qtd" style="width: 80px;">';
                echo '</div>';
            }
        }
        $conn->close();
        ?>
      </div>

      <button type="submit" name="adicionar_estoque" class="btn btn-primary">Adicionar</button>
      <button type="button" class="btn btn-secondary ms-2" onclick="fecharPopupCadastrar()">Cancelar</button>
    </form>
  </div>
</div>

<!-- Modal para Excluir Remédio do Estoque -->
<div id="popupExcluir" class="overlay">
  <div class="popup-form">
    <h4>Excluir Remédio (Zerar Estoque)</h4>
    <form method="POST" action="">
      <div class="mb-3">
        <label class="form-label">Selecione os remédios a excluir</label>
        <?php
        $conn = new mysqli("localhost", "root", "usbw", "DB_MEDIFLOW");
        $sql = "SELECT m.id, m.nome, m.imagem, e.quantidade FROM medicamentos m INNER JOIN estoque e ON m.id = e.medicamento_id WHERE e.quantidade > 0 ORDER BY m.nome ASC";
        $res = $conn->query($sql);
        if ($res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                echo '<div class="d-flex align-items-center mb-2">';
                echo '<img src="img/' . htmlspecialchars($row["imagem"]) . '" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">';
                echo '<span class="me-2" style="min-width: 150px;">' . htmlspecialchars($row["nome"]) . ' (' . $row["quantidade"] . ' unidades)</span>';
                echo '<input type="checkbox" name="excluir[' . $row["id"] . ']" value="1">';
                echo '</div>';
            }
        } else {
            echo "<p>Sem medicamentos disponíveis para excluir.</p>";
        }
        $conn->close();
        ?>
      </div>
      <button type="submit" name="excluir_remedio" class="btn btn-primary">Excluir</button>
      <button type="button" class="btn btn-secondary ms-2" onclick="fecharPopupExcluir()">Cancelar</button>
    </form>
  </div>
</div>

<body class="pb-5">
  <!-- Barra superior - desktop -->
  <div class="top-nav d-none d-md-flex bg-primary text-white">
    <div class="container d-flex justify-content-between align-items-center position relative">
  
      <!-- Logo -->
      <div class="logo-wrapper">
        <img src="img/logo.png" alt="Logo" />
      </div>
          
      <!-- Ícones centralizados -->
      <div class="icons d-flex gap-5 position-absolute top-50 start-50 translate-middle">
        <img style="width: 45px;" src="img/medication.png" />
        <img style="width: 45px;" src="img/lifeline-in-a-heart-outline.png" />
        <img style="width: 45px;" src="img/first-aid-kit.png" />
        <img style="width: 45px;" src="img/medicine.png" />
      </div>
    
      <!-- Notificação + Botões -->
      <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end" style="max-width: 45%; overflow: visible;">
        <img src="img/notificacao.png" class="me-2">
        <span class="text-white me-2" style="min-width: 130px;">Bem-vindo, <?= $nome_usuario ?>!</span>
        <button class="btn-dispensar" onclick="abrirPopup()">Dispensar Remédio</button>
        <form method="POST" action="" style="margin: 0;">
          <button type="submit" name="logout" class="btn btn-secondary">Sair</button>
        </form>
      </div>


    
    </div>
  </div>
  

  <div class="container py-4">
    <!-- Estoque -->
<h5 class="estoque-title text-primary">Estoque</h5>
<div class="card-estoque">
  <?php
$conn = new mysqli("localhost", "root", "usbw", "DB_MEDIFLOW");

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$sql = "
    SELECT m.nome, m.imagem, e.quantidade, m.unidade_medida
    FROM medicamentos m
    INNER JOIN estoque e ON m.id = e.medicamento_id
    WHERE e.quantidade > 0
    ORDER BY m.nome ASC
";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="remedio-card text-dark">';
        echo '<img src="img/' . htmlspecialchars($row["imagem"]) . '" class="remedio-img">';
        echo '<span>' . htmlspecialchars($row["nome"]) . '</span>';
        echo '<span>' . $row["quantidade"] . ' unidade(s)</span>';
        echo '</div>';
    }
} else {
    echo "<p>Nenhum medicamento em estoque.</p>";
}

$conn->close();
?>

</div>


    <!-- Sem Estoque -->
    <h5 class="sem-estoque-title text-warning mt-4">Sem Estoque ⚠</h5>
    <div class="card-sem-estoque">
      <?php
      $conn = new mysqli("localhost", "root", "usbw", "DB_MEDIFLOW");

      if ($conn->connect_error) {
          die("Erro na conexão: " . $conn->connect_error);
      }

      $sql_sem_estoque = "
          SELECT m.nome, m.imagem
          FROM medicamentos m
          INNER JOIN estoque e ON m.id = e.medicamento_id
          WHERE e.quantidade = 0
          ORDER BY m.nome ASC
      ";
      $result_sem_estoque = $conn->query($sql_sem_estoque);

      if ($result_sem_estoque->num_rows > 0) {
          while ($row = $result_sem_estoque->fetch_assoc()) {
              echo '<div class="remedio-card text-dark">';
              echo '<img src="img/' . htmlspecialchars($row["imagem"]) . '" class="remedio-img">';
              echo '<span>' . htmlspecialchars($row["nome"]) . '</span>';
              echo '</div>';
          }
      } else {
          echo "<p>Todos os medicamentos estão em estoque.</p>";
      }

      $conn->close();
      ?>
    </div>


    <!-- Gerenciador -->
    <div class="gerenciador-title mt-4">Gerenciador</div>
    <div class="d-flex gap-2 my-2">
      <button class="btn-dispensar" id="btn-cadastrar-remedio">Adicionar Remédio</button>
      <button class="btn btn-excluir">EXCLUIR REMÉDIO</button>
    </div>
    <a href="#" class="text-primary">VER TODOS →</a>
  </div>

  <!-- Barra inferior - mobile -->
  <div class="bottom-nav text-white d-flex d-md-none">
    <div><img src="https://img.icons8.com/ios-filled/24/ffffff/pill.png" /></div>
    <div><img src="https://img.icons8.com/ios-filled/24/ffffff/hospital.png" /></div>
    <div><img src="https://img.icons8.com/ios-filled/24/ffffff/medical-doctor.png" /></div>
    <div><img src="https://img.icons8.com/ios-filled/24/ffffff/like.png" /></div>
  </div>

  <!-- Javascript do pop up -->
  <script>
    // Mostrar o pop-up ao clicar no botão
    document.addEventListener("DOMContentLoaded", function () {
      const botaoDispensar = document.querySelector('.btn-dispensar');
      const popup = document.getElementById('popup');

      botaoDispensar.addEventListener('click', function () {
        popup.style.display = 'flex';
      });
    });

    // Fechar o pop-up
    function fecharPopup() {
      document.getElementById('popup').style.display = 'none';
    }
  </script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const botaoCadastrar = document.getElementById("btn-cadastrar-remedio");
      const popupCadastrar = document.getElementById("popupCadastrar");

      botaoCadastrar.addEventListener("click", function () {
        popupCadastrar.style.display = "flex";
      });

      document.getElementById("filtro_medicamento").addEventListener("keyup", function () {
        const filtro = this.value.toLowerCase();
        document.querySelectorAll(".medicamento-item").forEach(function (item) {
          const texto = item.textContent.toLowerCase();
          item.style.display = texto.includes(filtro) ? "" : "none";
        });
      });
    });

    function fecharPopupCadastrar() {
      document.getElementById("popupCadastrar").style.display = "none";
    }
  </script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const botaoExcluir = document.querySelector(".btn-excluir");
      const popupExcluir = document.getElementById("popupExcluir");

      botaoExcluir.addEventListener("click", function () {
        popupExcluir.style.display = "flex";
      });
    });

    function fecharPopupExcluir() {
      document.getElementById("popupExcluir").style.display = "none";
    }
  </script>

</body>
</html>
