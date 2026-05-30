<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#1A2634">
        <meta name="description" content="EME's Apartelle - Web-Based Reservation System. Book comfortable rooms with modern amenities in General Luna, Philippines.">

        <title>EME's Apartelle - Web-Based Reservation System</title>

        <!-- Favicons -->
        <link rel="icon" type="image/jpeg" href="/images/eme-logo.jpg">
        <link rel="shortcut icon" href="/images/eme-logo.jpg">

        <!-- Preload critical fonts for faster text rendering -->
        <link rel="preload" href="/fonts/inter-latin.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="/fonts/playfair-display-latin.woff2" as="font" type="font/woff2" crossorigin>

        <!-- Inline critical CSS for loading spinner (shows before JS loads) -->
        <style>
            .app-loader {
                position: fixed;
                inset: 0;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                background: #1A2634;
                z-index: 99999;
                transition: opacity 0.4s ease;
            }
            .app-loader.fade-out {
                opacity: 0;
                pointer-events: none;
            }
            .app-loader-logo {
                width: 60px;
                height: 60px;
                border-radius: 12px;
                background: #fff;
                padding: 8px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.3);
                margin-bottom: 24px;
            }
            .app-loader-spinner {
                width: 32px;
                height: 32px;
                border: 3px solid rgba(188, 145, 81, 0.2);
                border-top-color: #BC9151;
                border-radius: 50%;
                animation: app-spin 0.8s linear infinite;
            }
            .app-loader-text {
                margin-top: 16px;
                color: rgba(255,255,255,0.5);
                font-family: system-ui, -apple-system, sans-serif;
                font-size: 0.75rem;
                letter-spacing: 2px;
                text-transform: uppercase;
            }
            @keyframes app-spin {
                to { transform: rotate(360deg); }
            }
        </style>

        @vite(['resources/css/fonts.css', 'resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <!-- Loading Spinner (visible before Vue mounts) -->
        <div id="app-loader" class="app-loader">
            <img src="/images/EMES logo.png" alt="EME's" class="app-loader-logo">
            <div class="app-loader-spinner"></div>
            <div class="app-loader-text">Loading</div>
        </div>

        <div id="app"></div>

        <script>
            // Remove loading spinner once Vue app has mounted
            const observer = new MutationObserver(function(mutations) {
                const appEl = document.getElementById('app');
                if (appEl && appEl.children.length > 0) {
                    const loader = document.getElementById('app-loader');
                    if (loader) {
                        loader.classList.add('fade-out');
                        setTimeout(() => loader.remove(), 400);
                    }
                    observer.disconnect();
                }
            });
            observer.observe(document.getElementById('app'), { childList: true });
        </script>
    </body>
</html>
