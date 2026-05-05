@extends('layouts.app')

@section('title', 'Ludiweb · Inicio')

@section('content')
    <h2>Ludiweb</h2>
    <p>Cliente web del banco de preguntas BankApi.</p>
    <ul>
        <li><a href="{{ url('/categories') }}">Gestión de categorías</a></li>
        <li><a href="{{ url('/questions') }}">Gestión de preguntas y respuestas</a></li>
        <li><a href="{{ url('/games') }}">Jugar / mis partidas</a></li>
    </ul>
@endsection
