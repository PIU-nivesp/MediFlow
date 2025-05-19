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
  max-width: 500px;
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
      justify-content: center;
      margin-bottom: 80px;
    }

    .sem-estoque-title{
        font-size: 40px;
    }

    .card-sem-estoque {
      background-color: #F8D7DA;
      border-radius: 15px;
      padding: 15px;
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-bottom: 80px;
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
      width: 100px;
      height: auto;
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
  </style>
</head>

<!-- HTML DO POP UP -->
<!-- Modal Pop-up -->
<div id="popup" class="overlay">
  <div class="popup-form">
    <h4>Dispensar Remédio</h4>
    <form>
      <div class="mb-3">
        <label for="nome" class="form-label">Nome do cliente</label>
        <input type="text" class="form-control" id="nome" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Remédios retirados</label><br>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="remedio1" value="Remédio 45mg">
          <label class="form-check-label" for="remedio1">Remédio 45mg</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="remedio2" value="Remédio 90mg">
          <label class="form-check-label" for="remedio2">Remédio 90mg</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="remedio3" value="Remédio 30mg">
          <label class="form-check-label" for="remedio3">Remédio 30mg</label>
        </div>
      </div>

      <div class="mb-3">
        <label for="quantidade" class="form-label">Quantidade</label>
        <input type="number" class="form-control" id="quantidade" min="1" required>
      </div>

      <button type="submit" class="btn btn-primary">ENVIAR</button>
      <button type="button" class="btn btn-secondary ms-2" onclick="fecharPopup()">Cancelar</button>
    </form>
  </div>
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



<body class="pb-5">

  <!-- Barra superior - desktop -->
  <div class="top-nav d-none d-md-flex bg-primary text-white">
    <div class="container d-flex justify-content-between align-items-center position relative">
  
      <!-- Logo -->
      <div class="d-flex align-items-center">
        <img style="width: 85px;" class="me-2" src="logo.png" />
      </div>
    
      <!-- Ícones centralizados -->
      <div class="icons d-flex gap-5 position-absolute top-50 start-50 translate-middle">
        <img style="width: 45px;" src="img/medication.png" />
        <img style="width: 45px;" src="img/lifeline-in-a-heart-outline.png" />
        <img style="width: 45px;" src="img/first-aid-kit.png" />
        <img style="width: 45px;" src="img/medicine.png" />
      </div>
    
      <!-- Notificação + Botão -->
      <div class="d-flex align-items-center gap-2">
        <img src="img/notificacao.png" class="me-2">
        <button class="btn-dispensar">Dispensar Remédio</button>
      </div>
    
    </div>
  </div>
  

  <div class="container py-4">
    <!-- Estoque -->
    <h5 class="estoque-title text-primary">Estoque</h5>
    <div class="card-estoque">
      <!-- 6 cards de exemplo -->
      <div class="remedio-card">
        <img src="img/remedio1.png"  class="remedio-img">
        <span>Remédio 45mg</span>
      </div>
      <div class="remedio-card">
        <img src="img/remedio2.png" class="remedio-img">
        <span>Remédio 45mg</span>
      </div>
      <div class="remedio-card">
        <img src="img/remedio3.png" class="remedio-img">
        <span>Remédio 90mg</span>
        <span>60 Cps</span>
      </div>
      <div class="remedio-card">
        <img src="img/remedio1.png" class="remedio-img">
        <span>Remédio 30mg</span>
      </div>
      <div class="remedio-card">
        <img src="img/remedio2.png" class="remedio-img">
        <span>Remédio 10mg</span>
      </div>
      <div class="remedio-card">
        <img src="img/remedio3.png" class="remedio-img">
        <span>Remédio 90mg</span>
        <span>90 Cps</span>
      </div>
    </div>

    <!-- Sem Estoque -->
    <h5 class="sem-estoque-title text-warning mt-4">Sem Estoque ⚠️</h5>
    <div class="card-sem-estoque">
      <div class="remedio-card text-dark">
        <img src="img/remedio1.png" class="remedio-img">
        <span>Remédio 45mg</span>
      </div>
      <div class="remedio-card text-dark">
        <img src="img/remedio2.png" class="remedio-img">
        <span>Remédio 45mg</span>
      </div>
      <div class="img/remedio-card text-dark">
        <img src="img/remedio3.png" class="remedio-img">
        <span>Remédio 45mg</span>
      </div>
    </div>

    <!-- Gerenciador -->
    <div class="gerenciador-title mt-4">Gerenciador</div>
    <div class="d-flex gap-2 my-2">
      <button class="btn btn-cadastrar">CADASTRAR REMÉDIO</button>
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

</body>
</html>
