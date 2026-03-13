<?php
    session_start();
	$conexion = mysqli_connect('localhost', 'root', 'S3rvic10s.vtv', 'capturadora');
											
	if (isset($_SESSION['usuario'])) {
		//echo $_SESSION['usuario'];
	}
	else{ 
		header('Location:../index.php');
    }

?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capturadora Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <style>
        body {
            background: #f6f8fb;
        }
    </style>
</head>
<body>
    <div class="cont-admin">
        <div class="menu">
            <div class="menu-head">
                <img src="../img/logo.png" alt="Capturadora Admin" srcset="">
                <a href="#">Capturadora</a>
            </div>
            <div class="menu-options">
                <a href="agg-usuario.php">
                    <span class="material-symbols-outlined">arrow_forward_ios</span>Agregar Usuario
                </a>
                <a href="usuarios.php">
                    <span class="material-symbols-outlined" id="arrow">arrow_forward_ios</span>Usuarios
                </a>
                <a href="../User/material.php">
                    <span class="material-symbols-outlined" id="arrow">arrow_forward_ios</span>Panel Usuario
                </a>
            </div>
            <a href="../BD/cerrar.php" id="btn-salida">Salir</a>
        </div>
        <div class="cont-usuarios">
            <h1>Agregar Nuevo Usuario</h1>
            <form action="crearUsuario.php" method="POST">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" required>

                <label for="clave1">Contraseña:</label>
                <div style="position: relative; display: flex; align-items: center;">
                    <input type="password" id="clave1" name="clave" required style="width: 100%; padding-right: 40px;">
                    <span class="material-symbols-outlined" id="toggleClave1" style="position: absolute; right: 10px; cursor: pointer; color: #1e3c72; user-select: none;">visibility</span>
                </div>
                
                <label for="clave2">Confirmacion Contraseña:</label>
                <div style="position: relative; display: flex; align-items: center;">
                    <input type="password" id="clave2" name="clave2" required style="width: 100%; padding-right: 40px;">
                    <span class="material-symbols-outlined" id="toggleClave2" style="position: absolute; right: 10px; cursor: pointer; color: #1e3c72; user-select: none;">visibility</span>
                </div>

                <label for="rol">Rol:</label>
                <select id="rol" name="rol" required>
                    <option value="A">Administrador</option>
                    <option value="U">Usuario</option>
                </select>

                <button type="submit">Agregar Usuario</button>
            </form>
        </div>
    </div>
</body>

<script>
    // Función genérica para el toggle
    function setupPasswordToggle(inputId, toggleId) {
        const claveInput = document.getElementById(inputId);
        const toggleClave = document.getElementById(toggleId);
        
        if (claveInput && toggleClave) {
            toggleClave.addEventListener('click', function() {
                if (claveInput.type === 'password') {
                    claveInput.type = 'text';
                    toggleClave.textContent = 'visibility_off';
                } else {
                    claveInput.type = 'password';
                    toggleClave.textContent = 'visibility';
                }
            });
        }
    }

    // Configurar para el primer campo
    setupPasswordToggle('clave1', 'toggleClave1');
    
    // Configurar para el segundo campo
    setupPasswordToggle('clave2', 'toggleClave2');
</script>
</html>