<?php
/**
 * Vista: Login
 * Variables disponibles: $error (string|null)
 */
$head_data = [
    'title' => 'Capturadora VTV – Iniciar Sesión',
    'css'   => ['components/login.css'],
];
require __DIR__ . '/../layouts/head.php';
?>

<div class="login-wrapper">
    <div class="login-card">
        <h1 class="login-title">Capturadora</h1>

        <?php if (!empty($error)): ?>
            <div class="login-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form class="login-form" action="<?= BASE_URL ?>/public/api/auth.php" method="POST">
            <div class="input-group">
                <span class="material-symbols-outlined input-icon">person</span>
                <input type="text" name="usuario" placeholder="Usuario" required autocomplete="username">
            </div>

            <div class="input-group">
                <span class="material-symbols-outlined input-icon">lock</span>
                <input type="password" name="password" id="login-password" placeholder="Contraseña" required autocomplete="current-password">
                <span class="material-symbols-outlined toggle-password" id="toggleLoginPassword">visibility</span>
            </div>

            <button type="submit" class="btn-primary btn-full">Iniciar Sesión</button>
        </form>
    </div>

    <footer class="login-footer">
        <p>&copy; 2025 Capturadora VTV. Todos los derechos reservados.</p>
    </footer>
</div>

<script src="<?= BASE_URL ?>/public/js/auth/login.js"></script>
</body>
</html>
