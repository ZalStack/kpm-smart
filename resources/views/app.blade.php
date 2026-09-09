<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#769826">
    <meta name="description" content="KPM SMART - Sistem Penilaian Harian">
    <link rel="manifest" href="/manifest.json">
    <title>KPM SMART</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="bg-background antialiased text-foreground min-h-screen">
    @inertia
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('SW registered:', registration.scope);
                    })
                    .catch(function(error) {
                        console.log('SW registration failed:', error);
                    });
            });
        }
    </script>
</body>
</html>
