<?php 
	session_start(); 

	// --- 1. Conexión a la base de datos CORRECTA ---
	$conexion = mysqli_connect('localhost', 'root', 'S3rvic10s.vtv', 'capturadora');
			
	if (!$conexion) {
        // Usar die() es mejor aquí que un script de alerta
        die("Error de conexión: " . mysqli_connect_error());
    }

	// --- 2. Recibir los datos del formulario (de index.php) ---
	$nombre_login = $_POST['usuario'];
	$clave_login = $_POST['password']; 

	// --- 3. Consulta SEGURA (con Consultas Preparadas) ---
    // Buscamos en la tabla 'usuario' (singular)
    $sql = "SELECT clave, privilegio, estatus FROM usuario WHERE usuario = ?";
    
    $stmt = mysqli_prepare($conexion, $sql);
    
    if (!$stmt) {
        die("Error al preparar la consulta: " . mysqli_error($conexion));
    }
    
    mysqli_stmt_bind_param($stmt, "s", $nombre_login);
    mysqli_stmt_execute($stmt);
    
    // Guardamos los resultados que encontramos
    mysqli_stmt_bind_result($stmt, $clave_hash_db, $privilegio_db, $estatus_db);

	// --- 4. Verificar al usuario ---
	
	// mysqli_stmt_fetch() devuelve true si encontró un usuario
	if (mysqli_stmt_fetch($stmt)) {
		if ($estatus_db == 0) {
			// Usuario inactivo
			echo "<script> window.alert('*Usuario Inactivo. Contacte al Administrador'); window.location='../index.php';</script>";
			mysqli_stmt_close($stmt);
			mysqli_close($conexion);
			exit;
		}
		// Usuario encontrado. Ahora verificamos la contraseña
		if (password_verify($clave_login, $clave_hash_db)) {
			
			// ¡Contraseña correcta!
			// --- 5. Iniciar Sesión y Redirigir ---
			$_SESSION['usuario'] = $nombre_login; // Guardamos el nombre de usuario
			$_SESSION['privilegio'] = $privilegio_db; // Guardamos el rol

			mysqli_stmt_close($stmt);
    		mysqli_close($conexion);

			if($privilegio_db == 'A' || $privilegio_db == 'U') {
				header("location: ../User/material.php");
			} 
			exit; // Importante salir después de un header()

		} else {
			// Contraseña incorrecta
			echo "<script> window.alert('*Contraseña Inválida'); window.location='../index.php';</script>";
		}

	} else {
		// Usuario no encontrado
		echo "<script> window.alert('*Usuario Inválido'); window.location='../index.php';</script>";
	}

	mysqli_stmt_close($stmt);
    mysqli_close($conexion);

?>