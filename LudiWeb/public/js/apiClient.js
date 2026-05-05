/*
 * apiClient.js — cliente fetch común para llamar a BankApi.
 * Uso:
 *   await api.get('/categories');
 *   await api.post('/categories', { nombre: 'Geo' });
 * Maneja token Sanctum guardado en localStorage bajo la clave 'bankapi_token'.
 */
(function () {
    const TOKEN_KEY = 'bankapi_token';
    const USER_KEY  = 'bankapi_user';

    const baseUrl = () => (window.API_BASE_URL || 'http://localhost:8000').replace(/\/$/, '');

    const auth = {
        getToken: () => localStorage.getItem(TOKEN_KEY),
        setToken: (t) => localStorage.setItem(TOKEN_KEY, t),
        clearToken: () => localStorage.removeItem(TOKEN_KEY),
        getUser: () => {
            const raw = localStorage.getItem(USER_KEY);
            return raw ? JSON.parse(raw) : null;
        },
        setUser: (u) => localStorage.setItem(USER_KEY, JSON.stringify(u)),
        clearUser: () => localStorage.removeItem(USER_KEY),
        isLoggedIn: () => !!localStorage.getItem(TOKEN_KEY),
    };

    async function request(method, path, body) {
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        };
        const token = auth.getToken();
        if (token) headers['Authorization'] = 'Bearer ' + token;

        const res = await fetch(baseUrl() + '/api' + path, {
            method,
            headers,
            body: body !== undefined ? JSON.stringify(body) : undefined,
        });

        let data = null;
        const text = await res.text();
        if (text) {
            try { data = JSON.parse(text); } catch { data = text; }
        }

        if (!res.ok) {
            const err = new Error((data && data.message) || ('HTTP ' + res.status));
            err.status = res.status;
            err.data = data;
            throw err;
        }
        return data;
    }

    window.api = {
        get:  (p)     => request('GET',    p),
        post: (p, b)  => request('POST',   p, b),
        put:  (p, b)  => request('PUT',    p, b),
        patch:(p, b)  => request('PATCH',  p, b),
        del:  (p)     => request('DELETE', p),
        auth,
    };
})();
