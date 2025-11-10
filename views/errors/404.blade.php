<style>
    .error-page {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        background: #fff;
        font-family: "Inter", "Helvetica Neue", Arial, sans-serif;
        color: #1f2937;
    }

    .error-container {
        text-align: center;
        max-width: 500px;
        padding: 2rem;
    }

    .error-code {
        font-size: clamp(6rem, 15vw, 10rem);
        font-weight: 900;
        letter-spacing: -2px;
        color: #d1d5db;
        margin: 0 0 1rem;
    }

    .error-message {
        font-size: 1.5rem;
        font-weight: 400;
        color: #374151;
        margin: 0 0 2rem;
    }

    .error-button {
        display: inline-block;
        padding: 0.75rem 1.75rem;
        background: #111827;
        color: #fff;
        font-weight: 500;
        border-radius: 0.5rem;
        text-decoration: none;
        transition: background 0.25s ease;

        &:hover {
            background: #1f2937;
        }
    }

    .error-footer {
        margin-top: 2rem;
        font-size: 0.875rem;
        color: #9ca3af;
    }
</style>
<section class="error-page">
    <div class="error-container">
        <h1 class="error-code">404</h1>
        <p class="error-message">Sorry, the page you are looking for could not be found.</p>
        <a href="{{ route('homepage') }}" class="error-button">Go Home</a>
        <div class="error-footer">
            &copy; {{ date('Y') }} {{ env('APP_NAME') }}
        </div>
    </div>
</section>
