<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') - Inventory & Procurement System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    @yield('title', 'Inventory & Procurement System')
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    @yield('subtitle', '')
                </p>
            </div>
            <div id="alert-container"></div>
            @yield('content')
        </div>
    </div>
</body>
</html>
