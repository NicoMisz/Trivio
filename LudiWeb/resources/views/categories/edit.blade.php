@extends('layouts.app')

@section('title', 'Editar categoría')

@section('content')
    <h2>Editar categoría</h2>
    <form id="f">
        <label>Nombre <input type="text" name="nombre" required maxlength="100"></label>
        <label>Descripción <textarea name="descripcion" rows="3"></textarea></label>
        <div class="row">
            <button>Guardar</button>
            <a href="/categories">Cancelar</a>
        </div>
        <p class="error" id="err"></p>
    </form>
@endsection

@push('scripts')
<script>
if (!api.auth.isLoggedIn()) location.href = '/login';

const id = window.location.pathname.split('/')[2];

(async () => {
    try {
        const res = await api.get('/categories/' + id);
        const c = res.data || res;
        document.querySelector('[name=nombre]').value      = c.nombre || '';
        document.querySelector('[name=descripcion]').value = c.descripcion || '';
    } catch (e) {
        document.getElementById('err').textContent = 'No se pudo cargar: ' + e.message;
    }
})();

document.getElementById('f').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    document.getElementById('err').textContent = '';
    try {
        await api.put('/categories/' + id, {
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
