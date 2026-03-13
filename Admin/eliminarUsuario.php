<?php
    session_start();
    $conexion = mysqli_connect('localhost', 'root', 'S3rvic10s.vtv', 'capturadora');
    
    if (!$conexion) {
        die("Error de conexión: " . mysqli_connect_error());
    }

    // 1. Verificar que se recibió un ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        echo "<script> alert('Error: No se especificó un ID de usuario válido.'); window.location='usuarios.php';</script>";
        exit;
    }

    $id_usuario = $_GET['id'];

    // 2. (Importante) Evitar que un admin se desactive a sí mismo
    // Asumo que guardas el ID en la sesión como 'id_usuario'
    if (isset($_SESSION['id_usuario']) && $id_usuario == $_SESSION['id_usuario']) { 
        echo "<script> alert('Error: No puedes desactivar tu propia cuenta.'); window.location='usuarios.php';</script>";
        exit;
    }

    // 3. Preparar y ejecutar la eliminación (lógica)
    //    ¡Aquí está el cambio! De DELETE a UPDATE
    //    Y usamos la columna correcta 'id'
    $sql_desactivar = "UPDATE usuario SET estatus = 0 WHERE id = ?";
    $stmt_update = mysqli_prepare($conexion, $sql_desactivar);
    mysqli_stmt_bind_param($stmt_update, "i", $id_usuario);

    if (mysqli_stmt_execute($stmt_update)) {
        // Mensaje actualizado
        echo "<script> alert('Usuario desactivado exitosamente'); window.location='usuarios.php';</script>";
    } else {
        // Mensaje actualizado
        echo "<script> alert('Error al desactivar el usuario: " . mysqli_stmt_error($stmt_update) . "'); window.location='usuarios.php';</script>";
    }

    mysqli_stmt_close($stmt_update);
    mysqli_close($conexion);
?>