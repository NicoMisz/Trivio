@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <h2>Iniciar sesión</h2>
    <form id="f">
        <label>Email <input type="email" name="email" required></label>
        <label>Contraseña <input type="password" name="password" required></label>
        <button>Entrar</button>
        <p class="error" id="err"></p>
    </form>
    <p class="muted">¿No tienes cuenta? <a href="/register">Regístrate</a></p>
@endsection

@push('scripts')
<script>
document.getElementById('f').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    document.getElementById('err').textContent = '';
    try {
        const data = await api.post('/login', {
            email: fd.get('email'),
            password: fd.get('password'),
        });
        api.auth.setToken(data.token);
        api.auth.setUser(data.user);
        location.href = '/';
    } catch (err) {
        document.getElementById('err').textContent = err.message;
    }
});
</script>
@endpush
