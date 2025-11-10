@extends('template')
@section('content')
<style>
    body {
        font-family: 'Inter', sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
        margin: 0;
        background: #f8fafc;
        color: #1a202c;
    }
    h1 {
        font-size: 3rem;
        margin-bottom: 0.5rem;
    }
    p {
        font-size: 1.25rem;
        color: #4a5568;
    }
    .text-center {
        text-align: center;
    }
    .font-bold {
        font-weight: bold;
    }
</style>
<div class="text-center">
    <h1 class="font-bold">Welcome to {{ env('APP_NAME', 'Framework') }}</h1>
    <p>Your application is up and running!</p>
</div>
@endsection
