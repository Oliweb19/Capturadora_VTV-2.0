<?php
    session_start();
	$conexion = mysqli_connect('localhost', 'root', 'S3rvic10s.vtv', 'capturadora');
										

?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capturadora</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="shourt icon" href="img/favicon.ico">
</head>
<body>

    <div class="main">
        <div class="cont-login">
            <h1>Capturadora</h1>
            <form action="BD/validacion.php" method="POST">
                <div style="width: 80%; margin: 10px 0; position: relative;">
                    <input type="text" name="usuario" placeholder="Usuario" required style="width: 100%; padding-right: 40px; box-sizing: border-box;">
                </div>
                <div style="width: 80%; margin: 10px 0; position: relative;">
                    <input type="password" name="password" id="login-password" placeholder="Contraseña" required style="width: 100%; padding-right: 40px; box-sizing: border-box;">
                    <span class="material-symbols-outlined" id="toggleLoginPassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #101637; user-select: none;">visibility</span>
                </div>
                <input type="submit" value="Iniciar Sesión">
            </form>
        </div>

        <footer>
            <p>&copy; 2025 Capturadora VTV. Todos los derechos reservados.</p>
        </footer>
    </div>
    
</body>
<script>
const loginPassword = document.getElementById('login-password');
const toggleLoginPassword = document.getElementById('toggleLoginPassword');
if (loginPassword && toggleLoginPassword) {
    toggleLoginPassword.addEventListener('click', function() {
        if (loginPassword.type === 'password') {
            loginPassword.type = 'text';
            toggleLoginPassword.textContent = 'visibility_off';
        } else {
            loginPassword.type = 'password';
            toggleLoginPassword.textContent = 'visibility';
        }
    });
}
</script>
</html>