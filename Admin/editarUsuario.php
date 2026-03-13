<?php
    $conexion = mysqli_connect('localhost', 'root', 'S3rvic10s.vtv', 'capturadora');
    
    if (!$conexion) {
        die("Error de conexión: " . mysqli_connect_error());
    }

    // 1. Recibir los datos del formulario
    // YA NO recibimos 'privilegio' porque no lo vamos a tocar
    $id_usuario = $_POST['id_usuario']; 
    $nombre_usuario = $_POST['usuario'];
    $clave_nueva = $_POST['clave']; 

    // 2. Verificar si el nuevo nombre de usuario ya existe (para OTRO usuario)
    $sql_verificar = "SELECT id FROM usuario WHERE usuario = ? AND id != ?";
    $stmt_verif = mysqli_prepare($conexion, $sql_verificar);
    mysqli_stmt_bind_param($stmt_verif, "si", $nombre_usuario, $id_usuario);
    mysqli_stmt_execute($stmt_verif);
    mysqli_stmt_store_result($stmt_verif);

    if (mysqli_stmt_num_rows($stmt_verif) > 0) {
        echo "<script> alert('ERROR: El nombre de usuario \"$nombre_usuario\" ya está en uso por otra persona.'); window.history.go(-1);</script>";
        mysqli_stmt_close($stmt_verif);
        mysqli_close($conexion);
        exit;
    }
    mysqli_stmt_close($stmt_verif);


    // 3. Preparar la consulta SQL (SIN tocar el privilegio)
    if (empty($clave_nueva)) {
        // --- OPCIÓN A: Solo se actualiza el nombre de usuario ---
        // Eliminamos "privilegio = ?" de aquí
        $sql_actualizar = "UPDATE usuario SET usuario = ? WHERE id = ?";
        
        $stmt_update = mysqli_prepare($conexion, $sql_actualizar);
        // "si" -> string (usuario), integer (id)
        mysqli_stmt_bind_param($stmt_update, "si", $nombre_usuario, $id_usuario);
        
    } else {
        // --- OPCIÓN B: Se actualiza usuario y contraseña ---
        $clave_hash = password_hash($clave_nueva, PASSWORD_DEFAULT); 
        
        // Eliminamos "privilegio = ?" de aquí también
        $sql_actualizar = "UPDATE usuario SET usuario = ?, clave = ? WHERE id = ?";
        
        $stmt_update = mysqli_prepare($conexion, $sql_actualizar);
        // "ssi" -> string (usuario), string (clave), integer (id)
        mysqli_stmt_bind_param($stmt_update, "ssi", $nombre_usuario, $clave_hash, $id_usuario);
    }

    // 4. Ejecutar la consulta y mostrar resultado
    if (mysqli_stmt_execute($stmt_update)) {
        echo "<script> alert('Usuario actualizado exitosamente'); window.location='usuarios.php';</script>";
    } else {
        echo "<script> alert('Error al actualizar el usuario: " . mysqli_stmt_error($stmt_update) . "'); window.history.go(-1);</script>";
    }

    mysqli_stmt_close($stmt_update);
    mysqli_close($conexion);
?>