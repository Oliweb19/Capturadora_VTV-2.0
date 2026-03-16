/**
 * login.js – Toggle de visibilidad de contraseña en la pantalla de login
 */
document.addEventListener('DOMContentLoaded', () => {
    const loginPassword    = document.getElementById('login-password');
    const toggleLoginPass  = document.getElementById('toggleLoginPassword');

    if (loginPassword && toggleLoginPass) {
        toggleLoginPass.addEventListener('click', function () {
            const isPassword = loginPassword.type === 'password';
            loginPassword.type = isPassword ? 'text' : 'password';
            this.textContent  = isPassword ? 'visibility_off' : 'visibility';
        });
    }
});
