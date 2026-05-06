@extends('layouts.app')

@section('title', 'Nueva categoría')

@section('content')
    <h2>Nueva categoría</h2>
    <form id="f">
        <label>Nombre <input type="text" name="nombre" required maxlength="100"></label>
        <label>Descripción <textarea name="descripcion" rows="3"></textarea></label>
        <div class="row">
            <button>Crear</button>
            <a href="/categories">Cancelar</a>
        </div>
        <p class="error" id="err"></p>
    </form>
@endsection

@push('scripts')
<script>
if (!api.auth.isLoggedIn()) location.href = '/login';

document.getElementById('f').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    document.getElementById('err').textContent = '';
    try {
        await api.post('/categories', {
            nombre:      fd.get('nombre'),
            descripcion: fd.get('descripcion') || null,
        });
        location.href = '/categories';
    } catch (err) {
        const detalle = err.data && err.data.errors
            ? Object.values(err.data.errors).flat().join(' · ')
            : err.message;
        document.getElementById('err').textContent = detalle;
    }
});
</script>
@endpush
