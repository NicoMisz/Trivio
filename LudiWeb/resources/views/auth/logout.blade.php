@extends('layouts.app')

@section('title', 'Logout')

@section('content')
    <p>Cerrando sesión…</p>
@endsection

@push('scripts')
<script>
(async () => {
    try { await api.post('/logout'); } catch (e) { /* token caducado o inexistente */ }
    api.auth.clearToken();
    api.auth.clearUser();
    location.href = '/';
})();
</script>
@endpush
