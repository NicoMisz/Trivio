/*
 * auth-ui.js — pinta el área de login/logout en la cabecera.
 * Persona 2 puede ampliarlo cuando implemente el flujo de auth completo.
 */
(function () {
    const area = document.getElementById('auth-area');
    if (!area) return;
    const user = window.api.auth.getUser();
    if (window.api.auth.isLoggedIn()) {
        area.innerHTML = `<span class="muted">${user ? user.name : ''}</span>
            <a href="/logout">Salir</a>`;
    } else {
        area.innerHTML = `<a href="/login">Login</a> · <a href="/register">Registro</a>`;
    }
})();
