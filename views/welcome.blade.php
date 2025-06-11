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
    .container {
        text-align: center;
    }
    h1 {
        font-size: 3rem;
        margin-bottom: 0.5rem;
    }
    p {
        font-size: 1.25rem;
        color: #4a5568;
    }
</style>
<div class="container">
    <h1>Welcome to {{ config('APP_NAME', 'Framework') }}</h1>
    <p>Your application is up and running!</p>
</div>