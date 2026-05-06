@extends('layouts.app')

@section('title', 'Categorías')

@section('content')
    <h2>Categorías</h2>
    <p><a href="/categories/create">+ Nueva categoría</a></p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="rows"><tr><td colspan="4">Cargando…</td></tr></tbody>
    </table>
@endsection

@push('scripts')
<script>
(async () => {
    if (!api.auth.isLoggedIn()) {
        location.href = '/login';
        return;
    }
    try {
        const res = await api.get('/categories');
        const items = res.data || res;
        const rows = document.getElementById('rows');
        if (items.length === 0) {
            rows.innerHTML = '<tr><td colspan="4" class="muted">Sin categorías. Crea la primera.</td></tr>';
            return;
        }
        rows.innerHTML = items.map(c => `
            <tr>
                <td>${c.id}</td>
                <td>${c.nombre}</td>
                <td>${c.descripcion || ''}</td>
                <td>
                    <a href="/categories/${c.id}/edit">Editar</a>
                    <button onclick="del(${c.id})">Borrar</button>
                </td>
            </tr>`).join('');
    } catch (e) {
        document.getElementById('rows').innerHTML =
            `<tr><td colspan="4" class="error">Error: ${e.message}</td></tr>`;
    }
})();

async function del(id) {
    if (!confirm('¿Borrar esta categoría?')) return;
    try {
        await api.del('/categories/' + id);
        location.reload();
    } catch (e) {
        alert('No se pudo borrar: ' + e.message);
    }
}
</script>
@endpush
