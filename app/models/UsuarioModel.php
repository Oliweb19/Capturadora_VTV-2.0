<?php
require_once __DIR__ . '/../config/database.php';

// ============================================================
// Model: Usuario
// Todas las operaciones de base de datos relacionadas a usuarios
// ============================================================

/**
 * Verifica credenciales de login.
 * Retorna array con 'privilegio' y 'estatus' si el usuario existe, o false.
 */
function usuario_validar_login(string $nombre){
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT clave, privilegio, estatus FROM usuario WHERE usuario = ?");
    $stmt->execute([$nombre]);
    return $stmt->fetch() ?: false;
}

/**
 * Obtiene la lista paginada de usuarios activos, con búsqueda opcional.
 * Retorna ['data' => [...], 'total' => int]
 */
function usuario_listar(int $limit, int $offset, string $search = ''){
    $pdo = get_db();

    $where  = "WHERE estatus = 1";
    $params = [];

    if ($search !== '') {
        $where   .= " AND usuario LIKE ?";
        $params[] = "%$search%";
    }

    // Total de registros
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM usuario $where");
    $stmt_count->execute($params);
    $total = (int) $stmt_count->fetchColumn();

    // Registros paginados
    $params[] = $limit;
    $params[] = $offset;
    $stmt_data = $pdo->prepare(
        "SELECT id, usuario, privilegio FROM usuario $where ORDER BY usuario ASC LIMIT ? OFFSET ?"
    );
    $stmt_data->execute($params);

    return [
        'data'  => $stmt_data->fetchAll(),
        'total' => $total,
    ];
}

/**
 * Crea un nuevo usuario.
 * Retorna true en éxito, o un string con el mensaje de error.
 */
function usuario_crear(string $nombre, string $clave_plain, string $rol){
    $pdo = get_db();

    // Verificar duplicado
    $stmt = $pdo->prepare("SELECT id FROM usuario WHERE usuario = ?");
    $stmt->execute([$nombre]);
    if ($stmt->fetch()) {
        return 'Ya existe un usuario con ese nombre.';
    }

    $hash = password_hash($clave_plain, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO usuario (usuario, clave, privilegio) VALUES (?, ?, ?)");
    $stmt->execute([$nombre, $hash, $rol]);
    return true;
}

/**
 * Edita nombre y opcionalmente contraseña de un usuario.
 * Retorna true en éxito, o string con error.
 */
function usuario_editar(int $id, string $nombre, string $clave_nueva = ''){
    $pdo = get_db();

    // Verificar nombre duplicado en otro usuario
    $stmt = $pdo->prepare("SELECT id FROM usuario WHERE usuario = ? AND id != ?");
    $stmt->execute([$nombre, $id]);
    if ($stmt->fetch()) {
        return "El nombre de usuario \"$nombre\" ya está en uso.";
    }

    if ($clave_nueva === '') {
        $stmt = $pdo->prepare("UPDATE usuario SET usuario = ? WHERE id = ?");
        $stmt->execute([$nombre, $id]);
    } else {
        $hash = password_hash($clave_nueva, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuario SET usuario = ?, clave = ? WHERE id = ?");
        $stmt->execute([$nombre, $hash, $id]);
    }

    return true;
}

/**
 * Desactiva (elimina lógicamente) un usuario.
 * Retorna true en éxito, o string con error.
 */
function usuario_desactivar(int $id, ?int $id_sesion = null) {
    if ($id === $id_sesion) {
        return 'No puedes desactivar tu propia cuenta.';
    }
    $pdo = get_db();
    $stmt = $pdo->prepare("UPDATE usuario SET estatus = 0 WHERE id = ?");
    $stmt->execute([$id]);
    return true;
}
