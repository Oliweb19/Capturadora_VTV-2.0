<?php
    session_start();
	$conexion = mysqli_connect('localhost', 'root', 'S3rvic10s.vtv', 'capturadora');
										
	if (isset($_SESSION['usuario'])) {
		// El usuario está logueado
	}
	else{ 
		header('Location:../index.php');
        exit; 
    }

    // --- LÓGICA DE PAGINACIÓN Y BÚSQUEDA ---
    $limit = 10; 
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    // Base de la consulta: solo mostrar usuarios activos (estatus = 1)
    $where_sql = " WHERE estatus = 1";
    $search_param = null;

    if (!empty($search_term)) {
        // Si hay búsqueda, la añadimos a la consulta
        $where_sql .= " AND usuario LIKE ?";
        $search_param = "%" . $search_term . "%";
    }

    // --- Conteo total de registros (filtrados) ---
    $sql_count = "SELECT COUNT(*) as total FROM usuario" . $where_sql;
    $stmt_count = mysqli_prepare($conexion, $sql_count);
    
    if ($search_param) {
        mysqli_stmt_bind_param($stmt_count, "s", $search_param);
    }
    mysqli_stmt_execute($stmt_count);
    $result_count = mysqli_stmt_get_result($stmt_count);
    $total_records = mysqli_fetch_assoc($result_count)['total'];
    $total_pages = ceil($total_records / $limit);

    mysqli_stmt_close($stmt_count);

    // --- Obtener los registros para la página actual (filtrados) ---
    // Usamos la columna correcta 'id'
    $sql = "SELECT id, usuario, privilegio FROM usuario" . $where_sql . " ORDER BY usuario ASC LIMIT ? OFFSET ?";
    $stmt_data = mysqli_prepare($conexion, $sql);

    // Bindeo de parámetros dinámico (para search + paginación)
    if ($search_param) {
        // "sii" = string (search), integer (limit), integer (offset)
        mysqli_stmt_bind_param($stmt_data, "sii", $search_param, $limit, $offset);
    } else {
        // "ii" = integer (limit), integer (offset)
        mysqli_stmt_bind_param($stmt_data, "ii", $limit, $offset);
    }
    
    mysqli_stmt_execute($stmt_data);
    $result_data = mysqli_stmt_get_result($stmt_data);
    // --- FIN DEL BLOQUE DE LÓGICA ---
?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capturadora Admin</title>
    <link rel="stylesheet" href="../css/style.css"> 
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="shourt icon" href="../img/favicon.ico">
    <style>
        body {
            background: #f6f8fb;
        }
    </style>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="cont-admin">
        <div class="menu">
            <div class="menu-head">
                <img src="../img/logo.png" alt="Capturadora Admin" srcset="">
                <a href="#">Capturadora</a>
            </div>
            <div class="menu-options">
                <a href="usuarios.php">
                    <span class="material-symbols-outlined">arrow_forward_ios</span>Usuarios
                </a>
                <a href="../User/material.php">
                    <span class="material-symbols-outlined" id="arrow">arrow_forward_ios</span>Panel Usuario
                </a>
            </div>
            <a href="../BD/cerrar.php" id="btn-salida">Salir</a>
        </div>
        <div class="cont-usuarios">
            <h1>Bienvenido al Panel de Administración</h1>

            <div class="controls-header">
                
                <form action="usuarios.php" method="GET" class="search-form-new">
                    <div class="search-input-wrapper">
                        <input type="text" name="search" placeholder="Buscar por usuario..." value="<?php echo htmlspecialchars($search_term); ?>">
                    </div>
                    
                    <?php if (!empty($search_term)): ?>
                        <a href="usuarios.php" class="btn-clear-search">Limpiar búsqueda</a>
                    <?php endif; ?>
                </form>

                <button id="openModalBtn"><span class="material-symbols-outlined">add</span> Agregar Usuario</button>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // CORRECCIÓN 1: Usamos $result_data en lugar de $resultado
                        if (mysqli_num_rows($result_data) == 0) {
                            $message = empty($search_term) ? 'No hay usuarios registrados.' : 'No se encontraron usuarios con ese nombre.';
                            echo '<tr><td colspan="3" style="text-align: center;">' . $message . '</td></tr>';
                        } else {
                            while ($usuario_data = mysqli_fetch_assoc($result_data)) {
                                // CORRECCIÓN 2: Eliminamos la línea incorrecta que usaba $fila
                                // $rol_texto = ($fila['privilegio'] == 'A') ? 'Administrador' : 'Usuario';
                    ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($usuario_data['usuario']); ?></td>
                                    <td><?php echo $usuario_data['privilegio'] == 'A' ? 'Administrador' : 'Usuario'; ?></td>
                                    <td>
                                        <a href="#" class="btn-edit" 
                                        data-id="<?php echo $usuario_data['id']; ?>" 
                                        data-usuario="<?php echo htmlspecialchars($usuario_data['usuario']); ?>">
                                        <span class="material-symbols-outlined">edit</span>
                                        </a>
                                        <a href="#" class="btn-tabla btn-eliminar" 
                                        data-id="<?php echo $usuario_data['id']; ?>" 
                                        data-usuario="<?php echo htmlspecialchars($usuario_data['usuario']); ?>">
                                            <span class="material-symbols-outlined">delete</span>
                                        </a>
                                    </td>
                                </tr>
                    <?php
                            } 
                        } 
                    ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php
                $search_query = !empty($search_term) ? '&search=' . urlencode($search_term) : '';
                for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i . $search_query; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>

        </div>
    </div>

    <div id="addUserModal" class="modal">
        <div class="modal-content cont-usuarios ajustar-modal">
            <span class="close-modal">&times;</span>
            <h2>Agregar Nuevo Usuario</h2> 
            <form action="crearUsuario.php" method="POST">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" required>

                <label for="clave1">Contraseña:</label>
                <div class="password-wrapper">
                    <input type="password" id="clave1" name="clave" required>
                    <span class="material-symbols-outlined" id="toggleClave1">visibility</span>
                </div>
                
                <label for="clave2">Confirmacion Contraseña:</label>
                <div class="password-wrapper">
                    <input type="password" id="clave2" name="clave2" required>
                    <span class="material-symbols-outlined" id="toggleClave2">visibility</span>
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

    <div id="editModal" class="modal">
        <div class="modal-content ajustar-modal-peque">
            <span class="close-modal close-edit-modal">&times;</span>
            <h2>Editar Usuario</h2>
            <form action="editarUsuario.php" method="POST" id="editUserForm">
                <input type="hidden" id="edit_user_id" name="id_usuario">

                <div class="form-group">
                    <label for="edit_usuario">Nombre de Usuario:</label>
                    <input type="text" id="edit_usuario" name="usuario" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_clave">Nueva Contraseña:</label>
                    <input type="password" id="edit_clave" name="clave" autocomplete="new-password">
                    <span class="form-hint">Dejar en blanco para no cambiar la contraseña.</span>
                </div>

                <button type="submit">Actualizar Usuario</button>
            </form>
        </div>
    </div>

<div id="deleteConfirmModal" class="modal">
    <div class="modal-content ajustar-modal-peque">
        <span class="close-modal close-delete-modal">&times;</span>
        <h2>Confirmar Eliminación</h2>
        <p>¿Estás seguro de que deseas eliminar al usuario <strong id="delete_user_name"></strong>?</p>
        <p>Esta acción no se puede deshacer.</p>
        
        <div class="modal-buttons">
            <button type="button" class="btn-cancelar close-delete-modal">Cancelar</button>
            <a href="#" id="btn_confirm_delete" class="btn-eliminar-confirmar">Aceptar</a>
        </div>
    </div>
</div>

<script>
    // --- Script para el Modal ---
    var modal = document.getElementById("addUserModal");
    var btn = document.getElementById("openModalBtn");
    var span = document.getElementsByClassName("close-modal")[0];

    if (btn) {
        btn.onclick = function() {
            modal.style.display = "block";
        }
    }
    if (span) {
        span.onclick = function() {
            modal.style.display = "none";
        }
    }
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // --- Script para el Toggle de Contraseña ---
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
    setupPasswordToggle('clave1', 'toggleClave1');
    setupPasswordToggle('clave2', 'toggleClave2');

    // ... Tu script existente para "addUserModal" ...

    // --- Script para el Modal de EDITAR USUARIO (Lápiz) ---
    var editModal = document.getElementById("editModal");
    var editCloseBtn = document.querySelector(".close-edit-modal"); 

    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // 1. Obtener los datos del botón
            var userId = this.getAttribute('data-id');
            var userName = this.getAttribute('data-usuario');
            
            // 2. Rellenar el formulario del modal
            // CORRECCIÓN: El ID en tu HTML es 'edit_user_id', no 'edit_id_usuario'
            document.getElementById('edit_user_id').value = userId; 
            document.getElementById('edit_usuario').value = userName;
            
            // 3. Mostrar el modal
            editModal.style.display = "block";
        });
    });

    // Cerrar el modal de editar
    if (editCloseBtn) {
        editCloseBtn.onclick = function() {
            editModal.style.display = "none";
        }
    }

    // --- Script para el Modal de ELIMINAR USUARIO (Papelera) ---
    var deleteModal = document.getElementById("deleteConfirmModal");
    var deleteCloseBtn = document.querySelector(".close-delete-modal");
    var deleteCancelBtn = document.querySelector(".btn-cancelar");

    // Abrir el modal de confirmar eliminación
    document.querySelectorAll('.btn-eliminar').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // 1. Obtener los datos
            var userId = this.getAttribute('data-id');
            var userName = this.getAttribute('data-usuario');
            
            // 2. Personalizar el modal
            document.getElementById('delete_user_name').textContent = userName;
            
            // 3. Poner el enlace de eliminación en el botón "Aceptar"
            //    Añadiremos el script "eliminarUsuario.php" en el Paso 5
            document.getElementById('btn_confirm_delete').href = 'eliminarUsuario.php?id=' + userId;
            
            // 4. Mostrar el modal
            deleteModal.style.display = "block";
        });
    });

    // Cerrar el modal de eliminar (con la X y el botón Cancelar)
    function closeDeleteModal() {
        deleteModal.style.display = "none";
    }
    if (deleteCloseBtn) {
        deleteCloseBtn.onclick = closeDeleteModal;
    }
    if (deleteCancelBtn) {
        deleteCancelBtn.onclick = closeDeleteModal;
    }


    // --- Cierre genérico de modales (como el que ya tienes) ---
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
        // Añadimos el cierre para los nuevos modales
        if (event.target == editModal) {
            editModal.style.display = "none";
        }
        if (event.target == deleteModal) {
            deleteModal.style.display = "none";
        }   
    }
</script>
</body>
</html>