<?php 
    $conexion = mysqli_connect('localhost', 'root', 'S3rvic10s.vtv', 'capturadora');
    
    if (!$conexion) {
        die("Error de conexión: " . mysqli_connect_error());
    }

    $nombre_nuevo = $_POST['usuario'];
    $clave = $_POST['clave'];
    $clave2 = $_POST['clave2'];
    $rol = $_POST['rol'];

    // --- 1. PRIMERO verificamos que las contraseñas en texto plano sean iguales ---
    if ($clave !== $clave2) {
        echo "<script> alert('*LAS CONTRASEÑAS SON DIFERENTES'); window.history.go(-1);</script>";
        exit; // Salimos del script si no coinciden
    }

    // --- 2. Verificamos si el usuario ya existe (USANDO CONSULTAS PREPARADAS) ---
    $sql_verificar = "SELECT usuario FROM usuario WHERE usuario = ?";
    $stmt = mysqli_prepare($conexion, $sql_verificar);
    
    mysqli_stmt_bind_param($stmt, "s", $nombre_nuevo); // "s" significa que es un string
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt); // Guardamos el resultado

    if (mysqli_stmt_num_rows($stmt) > 0) {
        // El usuario ya existe
        echo '<script> 
                window.alert("*YA EXISTE UN USUARIO CON ESTE NOMBRE"); 
                window.history.go(-1);
              </script>';
        mysqli_stmt_close($stmt); // Cerramos la consulta preparada
        mysqli_close($conexion); // Cerramos la conexión
        exit; // Salimos
    }
    
    // Cerramos la primera consulta
    mysqli_stmt_close($stmt);

    // --- 3. Si todo está bien, hasheamos la contraseña y la insertamos ---
    
    // HASH DE Contraseña (Ahora que sabemos que son iguales, hasheamos la primera)
    $clave_hash = password_hash($clave, PASSWORD_DEFAULT); 

    // (CORREGIDO) Usamos consultas preparadas para el INSERT
    $sql_agregar = "INSERT INTO usuario (usuario, clave, privilegio) VALUES (?, ?, ?)";
    
    $stmt_insert = mysqli_prepare($conexion, $sql_agregar);
    
    // "sss" significa que los tres parámetros son strings
    mysqli_stmt_bind_param($stmt_insert, "sss", $nombre_nuevo, $clave_hash, $rol);

    if (mysqli_stmt_execute($stmt_insert)) {
        // Éxito
        echo "<script> alert('USUARIO AGREGADO EXITOSAMENTE'); window.location='usuarios.php';</script>";
    } else {
        // Error
        echo "<script> alert('ERROR AL CREAR EL USUARIO: " . mysqli_error($conexion) . "'); window.history.go(-1);</script>";
    }

    // Cerramos todo
    mysqli_stmt_close($stmt_insert);
    mysqli_close($conexion);

?>