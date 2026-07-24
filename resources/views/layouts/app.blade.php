<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Khai báo CSRF Token để hỗ trợ các request AJAX/Fetch/Axios bảo mật -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Sunburst' }}</title>

    <!-- Nạp các tệp tài nguyên CSS và JS toàn cục/trang chủ thông qua Laravel Vite -->
    @vite([
        'resources/shared/global.css',
        'resources/css/pages/index.css',
        'resources/css/pages/home.css',
        'resources/js/app.js'
    ])
    @stack('styles')
</head>
<body>

    <!-- Nội dung chính được nạp động từ các template con kế thừa layout này -->
    @yield('content')

    @stack('scripts')
</body>
</html>
