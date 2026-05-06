@extends('layouts.app')

@section('title', 'Registro')

@section('content')
    <h2>Crear cuenta</h2>
    <form id="f">
        <label>Nombre <input type="text" name="name" required></label>
        <label>Email <input type="email" name="email" required></label>
        <label>Contraseña <input type="password" name="password" minlength="6" required></label>
        <button>Registrarme</button>
        <p class="error" id="err"></p>
    </form>
    <p class="muted">¿Ya tienes cuenta? <a href="/login">Inicia sesión</a></p>
@endsection

@push('scripts')
<script>
document.getElementById('f').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    document.getElementById('err').textContent = '';
    try {
        const data = await api.post('/register', {
            name:     fd.get('name'),
            email:    fd.get('email'),
            password: fd.get('password'),
        });
        api.auth.setToken(data.token);
        api.auth.setUser(data.user);
        location.href = '/';
    } catch (err) {
        const detalle = err.data && err.data.errors
            ? Object.values(err.data.errors).flat().join(' · ')
            : err.message;
        document.getElementById('err').textContent = detalle;
    }
});
</script>
@endpush
