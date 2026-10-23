<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Gym Master') }}</title>

    <!-- Fonts & Vite -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

    <script>
        (function () {
            try {
                document.documentElement.dataset.theme = localStorage.getItem('gym-theme') === 'light' ? 'light' : 'dark';
            } catch (error) {
                document.documentElement.dataset.theme = 'dark';
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 antialiased min-h-screen flex items-center justify-center p-4">
<div class="fixed top-4 left-4 z-50"><x-theme-toggle /></div>
<div class="w-full max-w-md">
    {{ $slot }}
</div>
@include('components.password-visibility')
</body>
</html>
